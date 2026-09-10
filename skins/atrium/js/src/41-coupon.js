/**
 * Discount code celebration.
 *
 * Applying a coupon is POST -> discountAdd() -> httpredir(currentPage()), so by
 * the time anything could animate, the page has been thrown away and rebuilt.
 * The trigger therefore has to survive a navigation, and it cannot come from
 * the server: core sets an error notice when a code is rejected but nothing at
 * all when one is accepted.
 *
 * So: stash the typed code in sessionStorage on submit, and on the next load
 * fire only if that code is now listed as applied. That distinguishes the three
 * outcomes without a core change —
 *   accepted -> the code appears in $COUPONS, celebrate
 *   rejected -> it does not, stay quiet and let the error notice speak
 *   refresh  -> the flag was already consumed, stay quiet
 * The flag is read once and cleared immediately, so a reload, a back-button
 * restore or a second tab can never replay it.
 *
 * The whole thing is opt-out: templates only emit data-cc-coupon when the
 * merchant leaves "Celebrate Discount Codes" on, and with no attributes there
 * is nothing to match, so this file costs a querySelector and stops.
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
            /* Private mode and blocked storage both throw on access, not just
               on write. No flag simply means no celebration. */
            return null;
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

    /* Arm on the Apply button rather than on form submit: this form is also
       submitted by the shipping select and by Proceed, and neither should leave
       a pending code behind. */
    function arm() {
        var button = document.getElementById('apply_coupon');
        var input = document.getElementById('coupon');
        if (!button || !input) return;
        button.addEventListener('click', function () {
            var code = normalise(input.value);
            if (code) writeFlag(code);
        });
    }

    /* Colours come from the live tokens, so confetti matches whichever
       sub-theme or brand colour the store is running. */
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
            /* Fired upward in a fan, then gravity takes over: a burst out of the
               discount line itself reads as "this is what just happened",
               where a full-screen drop would read as a site-wide event. */
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
        /* Lifted from the row core already rendered, so it stays translated and
           says the real code and the real saving. */
        if (window.ccAnnounce) window.ccAnnounce(row.textContent);

        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        /* The redirect lands at the top of the page, and on a phone the order
           summary can be well below the fold. Firing anyway would burn the one
           moment this exists for on an empty patch of screen the customer is
           not looking at, so off-screen keeps the flash and skips the burst —
           the flash is still there when they scroll down to it. */
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
