/**
 * Atrium — Alpine bootstrap, shared stores and helpers.
 *
 * ── HOUSE RULE: NO ES6 TEMPLATE LITERALS IN THIS FOLDER ──────────────────────
 * CubeCart minifies skin JS with JSMin (includes/smarty/plugins/minify/JSmin.php,
 * Crockford 2002), which predates backticks and does not understand them.
 * Verified by execution against this tree's own copy:
 *
 *   backtick string containing an apostrophe
 *       -> throws JSMin_UnterminatedStringException (PHP fatal, white screen
 *          for every customer on every page)
 *   backtick string containing a URL
 *       -> truncated at "http:" — the // is read as a line comment
 *   backtick string containing a block comment
 *       -> the commented span is deleted from inside the string
 *   backtick string with a space after an interpolation
 *       -> the space is silently eaten, corrupting the output
 *
 * Everything else in ES2020 is safe: arrow functions, classes, ?., ??=, spread,
 * async/await, destructuring, object shorthand.
 *
 * This bundle currently bypasses {combine}, so it would survive — but the rule
 * is kept folder-wide so nobody has to remember which files are exempt.
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Load order is handled for us: every registration below happens inside an
 * 'alpine:init' listener, and Alpine core fires that event after all deferred
 * scripts have run. So this file works regardless of where it lands in the
 * <script> order, which matters because CubeCart's auto-loader sorts js/*.js as
 * strings (10.foo.js sorts before 2.foo.js).
 */

(function () {
    'use strict';

    /** CubeCart's CSRF token. Core injects a hidden input before every </form>
     *  (GUI::display()) and rejects POSTs without it
     *  (Sanitize::checkToken()). Read it from the DOM rather than
     *  templating it in, so AJAX calls always use a current value. */
    function ccToken() {
        var el = document.querySelector('input.cc_session_token, input[name="token"]');
        return el ? el.value : '';
    }

    /** Build a storefront URL with CubeCart's query-string conventions. */
    function ccUrl(params) {
        var base = (window.CC_ROOT_PATH || '') + 'index.php';
        var qs = new URLSearchParams(params || {}).toString();
        return qs ? base + '?' + qs : base;
    }

    /** POST a form to CubeCart and return the raw response text.
     *  Endpoints such as _g=ajaxadd return HTML fragments, not JSON — that is a
     *  documented plugin API (GUI::displaySideBasket()). Do not "improve"
     *  it into JSON. */
    async function ccPost(url, formData) {
        var res = await fetch(url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) throw new Error('Request failed: ' + res.status);
        return res.text();
    }

    window.ccToken = ccToken;
    window.ccUrl = ccUrl;
    window.ccPost = ccPost;

    document.addEventListener('alpine:init', function () {
        var Alpine = window.Alpine;

        /* ── Stores ──────────────────────────────────────────────────────────
           Cross-fragment state lives here, never inside a component. The basket
           count appears in the header, the mobile drawer and the mini-basket at
           once; an AJAX call replaces only one of those fragments, so a
           component-local count would desync the other two. */
        Alpine.store('ui', {
            menuOpen: false,
            drawerOpen: false,
            searchOpen: false,

            closeAll: function () {
                this.menuOpen = false;
                this.drawerOpen = false;
                this.searchOpen = false;
            }
        });

        Alpine.store('basket', {
            /* Seeded from the server-rendered markup so the first paint is
               correct with JS disabled or slow. */
            count: 0,
            total: '',

            /** Adopt the authoritative values off a freshly injected fragment. */
            syncFrom: function (el) {
                if (!el) return;
                var c = el.getAttribute('data-basket-count');
                var t = el.getAttribute('data-basket-total');
                if (c !== null) this.count = parseInt(c, 10) || 0;
                if (t !== null) this.total = t;
            }
        });

        /* ── Components ──────────────────────────────────────────────────── */

        /** Dismissible flash message. Used by box.errors.php. */
        Alpine.data('ccAlert', function () {
            return {
                shown: true,
                dismiss: function () { this.shown = false; }
            };
        });

        /** Generic disclosure: dropdowns, accordions, the mobile nav.
         *  Closes on Escape and on click outside. */
        Alpine.data('ccDisclosure', function (initial) {
            return {
                open: initial === true,
                toggle: function () { this.open = !this.open; },
                close: function () { this.open = false; }
            };
        });

        /** Tab set. The active panel id must be declared here rather than
         *  bolted on with x-init: Alpine only makes properties reactive if they
         *  exist on the data object when the component initialises. */
        Alpine.data('ccTabs', function (initial) {
            return {
                tab: initial || '',
                select: function (id) { this.tab = id; },
                isActive: function (id) { return this.tab === id; }
            };
        });

        /** Off-canvas panel (mobile nav, mini-basket drawer).
         *  Locks body scroll while open and restores it on close — reference
         *  counted via a data attribute so two panels cannot fight over it. */
        Alpine.data('ccDrawer', function (storeKey) {
            return {
                get open() {
                    return Alpine.store('ui')[storeKey] === true;
                },
                set open(v) {
                    Alpine.store('ui')[storeKey] = v;
                },
                close: function () { this.open = false; },
                lock: function () {
                    var n = parseInt(document.body.dataset.ccLocks || '0', 10) + 1;
                    document.body.dataset.ccLocks = String(n);
                    document.body.style.overflow = 'hidden';
                },
                unlock: function () {
                    var n = Math.max(0, parseInt(document.body.dataset.ccLocks || '0', 10) - 1);
                    document.body.dataset.ccLocks = String(n);
                    if (n === 0) document.body.style.overflow = '';
                }
            };
        });
    });
})();
