/**
 * Atrium — discount code celebration. No template literals (00-boot.js).
 *
 * Applying a coupon redirects, and core sets a notice only on FAILURE, so there
 * is no success signal to read after the reload. Instead: stash the typed code
 * on submit, and fire only if it now appears as applied. Read once and cleared,
 * so a refresh or back-button cannot replay it.
 *
 * Opt-out: templates emit data-cc-coupon only when the setting is on.
 */
(function () {
    'use strict';

    var KEY = 'cc_coupon_pending';

    function readFlag() {
        try {
            var v = window.sessionStorage.getItem(KEY);
            window.sessionStorage.removeItem(KEY);
            return v;
        } catch (e) {
            return null;   // blocked storage throws on read too
        }
    }

    function writeFlag(value) {
        try {
            window.sessionStorage.setItem(KEY, value);
        } catch (e) { /* as above */ }
    }

    function normalise(code) {
        return String(code || '').replace(/\s+/g, '').toLowerCase();
    }

    // The Apply button, not form submit: shipping and Proceed post this form too.
    function arm() {
        var button = document.getElementById('apply_coupon');
        var input = document.getElementById('coupon');
        if (!button || !input) return;
        button.addEventListener('click', function () {
            var code = normalise(input.value);
            if (code) writeFlag(code);
        });
    }

    // From the live tokens, so it matches the store's sub-theme.
    function palette() {
        var style = window.getComputedStyle(document.documentElement);
        var names = ['--color-brand-400', '--color-brand-600', '--color-success-600', '--color-star', '--color-brand-300'];
        var out = [];
        for (var i = 0; i < names.length; i++) {
            var value = style.getPropertyValue(names[i]).trim();
            if (value) out.push(value);
        }
        return out.length ? out : ['#2563eb', '#16a34a', '#eab308'];
    }

    function confetti(origin) {
        var canvas = document.createElement('canvas');
        canvas.className = 'cc-confetti';
        canvas.setAttribute('aria-hidden', 'true');
        document.body.appendChild(canvas);

        var dpr = window.devicePixelRatio || 1;
        var width = window.innerWidth;
        var height = window.innerHeight;
        canvas.width = width * dpr;
        canvas.height = height * dpr;
        var ctx = canvas.getContext('2d');
        ctx.scale(dpr, dpr);

        var colours = palette();
        var pieces = [];
        var count = 80;
        for (var i = 0; i < count; i++) {
            // A fan out of the discount line, not a full-screen drop.
            var angle = (-Math.PI / 2) + (Math.random() - 0.5) * 1.7;
            var speed = 5 + Math.random() * 7;
            pieces.push({
                x: origin.x,
                y: origin.y,
                vx: Math.cos(angle) * speed,
                vy: Math.sin(angle) * speed,
                size: 4 + Math.random() * 5,
                colour: colours[(Math.random() * colours.length) | 0],
                spin: (Math.random() - 0.5) * 0.35,
                angle: Math.random() * Math.PI
            });
        }

        var started = null;
        var LIFE = 1500;

        function frame(now) {
            if (started === null) started = now;
            var elapsed = now - started;
            if (elapsed > LIFE) {
                canvas.remove();
                return;
            }
            ctx.clearRect(0, 0, width, height);
            var fade = 1 - (elapsed / LIFE);
            for (var i = 0; i < pieces.length; i++) {
                var p = pieces[i];
                p.vy += 0.28;          // gravity
                p.vx *= 0.995;         // drag, so the fan narrows as it falls
                p.x += p.vx;
                p.y += p.vy;
                p.angle += p.spin;
                ctx.save();
                ctx.globalAlpha = fade;
                ctx.translate(p.x, p.y);
                ctx.rotate(p.angle);
                ctx.fillStyle = p.colour;
                ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size * 0.6);
                ctx.restore();
            }
            window.requestAnimationFrame(frame);
        }
        window.requestAnimationFrame(frame);
    }

    function celebrate() {
        var code = readFlag();
        if (!code) return;

        var rows = document.querySelectorAll('[data-cc-coupon]');
        var row = null;
        for (var i = 0; i < rows.length; i++) {
            if (normalise(rows[i].getAttribute('data-cc-coupon')) === code) {
                row = rows[i];
                break;
            }
        }
        if (!row) return;   // rejected, or the setting is off

        row.classList.add('cc-coupon-won');
        // Lifted from the rendered row, so it stays translated.
        if (window.ccAnnounce) window.ccAnnounce(row.textContent);

        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        // Off-screen keeps the flash and skips the burst nobody would see.
        var box = row.getBoundingClientRect();
        var middle = box.top + box.height / 2;
        if (middle < 0 || middle > (window.innerHeight || document.documentElement.clientHeight)) return;

        confetti({ x: box.left + box.width / 2, y: middle });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { arm(); celebrate(); });
    } else {
        arm();
        celebrate();
    }
}());
