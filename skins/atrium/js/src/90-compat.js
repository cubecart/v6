/**
 * Atrium — behaviour shims for markup emitted by MODULES.
 *
 * HOUSE RULE: no ES6 template literals in this folder. See 00-boot.js.
 *
 * The CSS counterpart is css/src/compat.css. This file is for the cases CSS
 * cannot reach: module markup that depended on a Foundation JS behaviour the
 * reference skin provided and Atrium does not.
 *
 * Deliberately implemented as DELEGATED listeners on document, so they work on
 * markup that arrives after load (gateway forms are fetched into the checkout,
 * and the mini-basket is replaced wholesale by _g=ajaxadd).
 */

(function () {
    'use strict';

    /**
     * `.colorbox` — an inline image lightbox.
     *
     * modules/gateway/Card_Capture/skin/form.tpl emits, next to the CVV field:
     *     <a href="images/general/cvv.gif" class="colorbox">What's this?</a>
     * The reference skin rewrote that into a modal (2.cubecart.js:127-146).
     * Without a handler the link NAVIGATES AWAY from a part-completed card
     * form, mid-checkout, losing everything typed.
     *
     * Fixing it here rather than in a skin override of the module's template is
     * deliberate: an override forks the gateway's form permanently, so a future
     * module release that adds or renames a field would silently break payment
     * for Atrium stores only. The module keeps ownership of its markup; the
     * skin supplies the missing behaviour.
     */
    document.addEventListener('click', function (e) {
        var link = e.target.closest ? e.target.closest('a.colorbox') : null;
        if (!link) return;

        var href = link.getAttribute('href');
        if (!href) return;

        e.preventDefault();

        var overlay = document.createElement('div');
        overlay.className = 'cc-colorbox fixed inset-0 z-50 flex items-center justify-center bg-ink-950/80 p-4';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-label', link.getAttribute('title') || link.textContent || '');

        var img = document.createElement('img');
        img.src = href;
        img.alt = link.getAttribute('title') || '';
        img.className = 'max-h-full max-w-full rounded-cc bg-white object-contain p-2';
        overlay.appendChild(img);

        function close() {
            overlay.remove();
            document.removeEventListener('keydown', onKey);
            if (link.focus) link.focus();          // return focus where it came from
        }
        function onKey(ev) { if (ev.key === 'Escape') close(); }

        overlay.addEventListener('click', close);
        document.addEventListener('keydown', onKey);
        document.body.appendChild(overlay);
    });
})();
