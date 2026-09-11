/**
 * Atrium — controls the templates cannot write themselves. No template literals
 * (00-boot.js).
 *
 * Password reveal, quantity steppers and back-to-top. All three are progressive
 * enhancement: the page is complete and usable before this runs, and every
 * label comes from element.ui_strings.php so nothing is hardcoded English.
 */
(function () {
    'use strict';

    var S = {};

    function label(el, text) {
        el.setAttribute('aria-label', text);
        el.title = text;
    }

    function icon(path, size) {
        return '<svg class="size-' + (size || 5) + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
               'stroke-width="1.75" aria-hidden="true">' + path + '</svg>';
    }

    var EYE = '<path d="M2.036 12.322a1 1 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178a1 1 0 0 1 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"/>';
    var EYE_OFF = '<path d="M3.98 8.223A10.5 10.5 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.774 3.162 10.066 7.5a10.5 10.5 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243" stroke-linecap="round" stroke-linejoin="round"/>';

    /* ── Password reveal ─────────────────────────────────────────────────
       Applied to every password field in the skin rather than templated into
       four of them, so a new form gets it for free. */
    function password(input) {
        if (input.getAttribute('data-cc-reveal')) return;
        input.setAttribute('data-cc-reveal', '1');

        var wrap = document.createElement('div');
        wrap.className = 'relative';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        input.classList.add('pe-11');

        var button = document.createElement('button');
        button.type = 'button';          // never submit: these sit inside forms
        button.className = 'absolute inset-y-0 end-0 flex w-11 items-center justify-center text-ink-500 hover:text-ink-800';
        button.setAttribute('aria-pressed', 'false');
        button.innerHTML = icon(EYE);
        label(button, S.show_password || 'Show password');

        button.addEventListener('click', function () {
            var reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            button.setAttribute('aria-pressed', reveal ? 'true' : 'false');
            button.innerHTML = icon(reveal ? EYE_OFF : EYE);
            label(button, reveal ? (S.hide_password || 'Hide password') : (S.show_password || 'Show password'));
            input.focus();
        });
        wrap.appendChild(button);
    }

    /* ── Quantity steppers ───────────────────────────────────────────────
       OPT-IN with data-cc-stepper, not applied to every quantity field: a
       listing tile in a two-column phone grid has no room for two more
       buttons beside the quantity and Add to Basket. */
    function stepper(input) {
        if (input.getAttribute('data-cc-stepped')) return;
        input.setAttribute('data-cc-stepped', '1');

        var group = document.createElement('span');
        group.className = 'inline-flex items-center gap-1';
        input.parentNode.insertBefore(group, input);

        group.appendChild(button(-1, input));
        group.appendChild(input);
        group.appendChild(button(1, input));
    }

    function button(delta, input) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'cc-btn cc-btn-secondary size-10 shrink-0 p-0 text-lg leading-none';
        b.textContent = delta < 0 ? '−' : '+';   // real minus sign, not a hyphen
        label(b, delta < 0 ? (S.quantity_decrease || 'Decrease quantity') : (S.quantity_increase || 'Increase quantity'));
        b.addEventListener('click', function () {
            var step = parseFloat(input.step) || 1;
            var min = input.min === '' ? null : parseFloat(input.min);
            var max = input.max === '' ? null : parseFloat(input.max);
            var value = (parseFloat(input.value) || 0) + (delta * step);
            if (min !== null && value < min) value = min;
            if (max !== null && !isNaN(max) && value > max) value = max;
            input.value = value;
            /* Both events: `input` is what Alpine's x-model listens for, and
               `change` is what the basket's auto-submit binds to. A value set
               from script fires neither on its own. */
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
        return b;
    }

    /* ── Back to top ─────────────────────────────────────────────────────
       A category page is 48 products by default, which is a long way back on a
       phone. Created here rather than templated so it exists on every layout,
       checkout included. */
    function backToTop() {
        var button = document.createElement('button');
        button.type = 'button';
        /* Bottom-LEFT and lifted on small screens, for two collisions: chat
           widgets live bottom-right almost universally, and the sticky buy bar
           (element.product.sticky_buy.php) is a full-width bar at bottom-0
           below lg. */
        button.className = 'cc-to-top cc-btn cc-btn-secondary fixed bottom-20 start-4 z-40 size-11 p-0 shadow-lg lg:bottom-4';
        button.hidden = true;
        button.innerHTML = icon('<path d="M12 19V5m0 0-7 7m7-7 7 7" stroke-linecap="round" stroke-linejoin="round"/>');
        label(button, S.back_to_top || 'Back to top');

        button.addEventListener('click', function () {
            var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            window.scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' });
            // Send focus somewhere sensible, or it stays on a button that has
            // just hidden itself.
            var main = document.getElementById('main_content');
            if (main) {
                main.setAttribute('tabindex', '-1');
                main.focus({ preventScroll: true });
            }
        });
        document.body.appendChild(button);

        var ticking = false;
        window.addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;
            window.requestAnimationFrame(function () {
                button.hidden = window.scrollY < 800;
                ticking = false;
            });
        }, { passive: true });
    }

    function init() {
        var el = document.getElementById('cc-ui-strings');
        if (el) {
            try { S = JSON.parse(el.textContent) || {}; } catch (e) { S = {}; }
        }

        var i;
        var fields = document.querySelectorAll('input[type="password"]');
        for (i = 0; i < fields.length; i++) password(fields[i]);

        var steppers = document.querySelectorAll('input[data-cc-stepper]');
        for (i = 0; i < steppers.length; i++) stepper(steppers[i]);

        backToTop();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
