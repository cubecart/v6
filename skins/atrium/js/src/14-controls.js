/**
 * Atrium — controls the templates cannot write themselves. No template literals
 * (00-boot.js).
 *
 * Password reveal + strength, quantity steppers, back-to-top. All progressive
 * enhancement; labels come from element.ui_strings.php so none are hardcoded.
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

    /* Every password field, rather than templated into four forms. */
    function password(input) {
        if (input.getAttribute('data-cc-reveal')) return;
        input.setAttribute('data-cc-reveal', '1');

        var wrap = document.createElement('div');
        wrap.className = 'relative';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        input.classList.add('pe-11');

        var button = document.createElement('button');
        button.type = 'button';          // these sit inside forms
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

    /* Opt-in with data-cc-stepper: a listing tile in a two-column phone grid
       has no room for two more buttons beside Add to Basket. */
    function stepper(input) {
        if (input.getAttribute('data-cc-stepped')) return;
        input.setAttribute('data-cc-stepped', '1');

        /* The buttons honour min/max, but typing does not. Clamp on change so a
           typed 50 becomes the 3 that are actually in stock, rather than being
           trimmed by core after the customer has already submitted. */
        input.addEventListener('change', function () {
            var max = parseFloat(input.max);
            var min = parseFloat(input.min);
            var value = parseFloat(input.value);
            if (isNaN(value)) return;
            if (!isNaN(max) && value > max) input.value = max;
            else if (!isNaN(min) && value < min) input.value = min;
        });

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
            // Both: x-model listens for input, the basket's auto-submit for
            // change, and a scripted value fires neither.
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
        return b;
    }


    /* ── Password strength ───────────────────────────────────────────────
       Length-first on purpose: 3 of 4 points are length, 1 is variety. NIST
       800-63B warns composition rules push people to "Password1!", weaker than
       three plain words. No library and no network: a breach-list lookup would
       be a third-party request on registration and checkout. */
    var COMMON = ('123456 password 123456789 12345678 12345 qwerty abc123 111111 123123 1234567890 ' +
                  '1234567 letmein monkey dragon baseball iloveyou trustno1 sunshine master welcome ' +
                  'shadow ashley football jesus michael ninja mustang password1 qwerty123 admin ' +
                  'login starwars passw0rd freedom whatever princess qazwsx superman hello charlie ' +
                  'donald aa123456 access flower hottie loveme zaq1zaq1 password123 querty').split(' ');

    function isCommon(value) {
        var v = value.toLowerCase();
        for (var i = 0; i < COMMON.length; i++) {
            if (COMMON[i] === v) return true;
        }
        return false;
    }

    /** -1 empty, 0 weak, 1 fair, 2 good, 3 strong. */
    function score(value, min) {
        if (!value) return -1;
        // Anything the form will reject reads Weak, or the meter and the
        // browser's "too short" message contradict each other.
        if (value.length < min) return 0;
        if (isCommon(value)) return 0;
        var s = 0;
        if (value.length >= 8) s++;
        if (value.length >= 12) s++;
        if (value.length >= 16) s++;
        var variety = (/[a-z]/.test(value) ? 1 : 0) + (/[A-Z]/.test(value) ? 1 : 0) +
                      (/[0-9]/.test(value) ? 1 : 0) + (/[^A-Za-z0-9]/.test(value) ? 1 : 0);
        if (variety >= 3) s++;
        return Math.min(s, 3);
    }

    var BAR = ['bg-danger-600', 'bg-warn-600', 'bg-brand-600', 'bg-success-600'];

    function meter(input) {
        if (input.getAttribute('data-cc-meter')) return;
        input.setAttribute('data-cc-meter', '1');

        var wrap = document.createElement('div');
        wrap.className = 'mt-2';
        wrap.hidden = true;

        var track = document.createElement('div');
        track.className = 'h-1 w-full overflow-hidden rounded-full bg-ink-200';
        var fill = document.createElement('div');
        fill.className = 'h-full w-0 rounded-full transition-all duration-200';
        track.appendChild(fill);

        var text = document.createElement('p');
        // polite: this updates on every keystroke.
        text.className = 'mt-1 text-xs text-ink-600';
        text.setAttribute('aria-live', 'polite');

        wrap.appendChild(track);
        wrap.appendChild(text);
        // After the reveal wrapper, so it sits under the field.
        (input.parentNode.parentNode || input.parentNode).insertBefore(wrap, input.parentNode.nextSibling);

        var min = parseInt(input.getAttribute('minlength'), 10) || 8;   // tracks core

        input.addEventListener('input', function () {
            var value = input.value;
            var s = score(value, min);
            if (s < 0) {
                wrap.hidden = true;
                return;
            }
            wrap.hidden = false;
            fill.className = 'h-full rounded-full transition-all duration-200 ' + BAR[s];
            fill.style.width = ((s + 1) * 25) + '%';

            var levels = S.pw_levels || ['Weak', 'Fair', 'Good', 'Strong'];
            var hint = '';
            if (isCommon(value)) hint = S.pw_hint_common || '';
            else if (value.length < 12) hint = S.pw_hint_length || '';
            text.textContent = (S.pw_strength || 'Password strength') + ': ' + levels[s] + (hint ? ' — ' + hint : '');
        });
    }

    /* Created here, not templated, so it exists on every layout. */
    function backToTop() {
        var button = document.createElement('button');
        button.type = 'button';
        /* Bottom-left and lifted below lg: chat widgets sit bottom-right, and
           the sticky buy bar is full-width at bottom-0. */
        button.className = 'cc-to-top cc-btn cc-btn-secondary fixed bottom-20 start-4 z-40 size-11 p-0 shadow-lg lg:bottom-4';
        button.hidden = true;
        button.innerHTML = icon('<path d="M12 19V5m0 0-7 7m7-7 7 7" stroke-linecap="round" stroke-linejoin="round"/>');
        label(button, S.back_to_top || 'Back to top');

        button.addEventListener('click', function () {
            var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            window.scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' });
            // Or focus stays on a button that just hid itself.
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

        // Only where a password is being CHOSEN, never on the login field.
        var setting = document.querySelectorAll('input[type="password"][autocomplete="new-password"]');
        for (i = 0; i < setting.length; i++) meter(setting[i]);

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
