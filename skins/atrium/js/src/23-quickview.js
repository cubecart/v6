/**
 * Atrium — quick view.
 *
 * HOUSE RULE: no ES6 template literals in this folder. See 00-boot.js.
 *
 * ENDPOINT: index.php?_g=quickview&product_id=N returns the rendered
 * templates/content.quickview.php fragment (Cubecart::loadPage()). An empty
 * response means the store's skin has no such template, so we fall back to
 * simply opening the product page rather than showing an empty modal.
 *
 * ⚠ The fragment carries the product page's ids. Exactly one may exist at a
 * time, so close() clears the markup rather than hiding the modal, and the
 * cache holds markup strings, never live nodes.
 *
 * ⚠ The fragment is written with innerHTML on a plain container, NOT bound with
 * x-html. Alpine re-runs an x-html effect whenever its reactive dependency is
 * touched and reassigns innerHTML wholesale, which rebuilt the fragment mid-use
 * and silently reset every <select> to its server-rendered default. The
 * customer picked an in-stock option combination and the stock check kept
 * judging the original one. Alpine's MutationObserver initialises inserted
 * nodes either way, which is how the mini-basket swap in 12-basket.js works.
 */
document.addEventListener('alpine:init', function () {
    window.Alpine.store('quickView', {
        open: false,
        busy: false,
        title: '',
        _cache: {},

        /** Write the fragment into the modal body. Not reactive, by design. */
        _render: function (markup) {
            var host = document.getElementById('cc-quickview-body');
            if (host) host.innerHTML = markup;
        },

        show: async function (id, name, url) {
            this.title = name || '';
            this.open = true;
            this.busy = true;
            this._render('');

            if (this._cache[id]) {
                this._render(this._cache[id]);
                this.busy = false;
                return;
            }

            try {
                var base = window.CC_ROOT_PATH || '/';
                var res = await window.fetch(
                    base + 'index.php?_g=quickview&product_id=' + encodeURIComponent(id),
                    { credentials: 'same-origin' }
                );
                var text = await res.text();
                if (!text || !text.replace(/\s/g, '')) {
                    // Nothing to show: send them to the real page instead.
                    if (url) window.location = url;
                    this.open = false;
                    return;
                }
                this._cache[id] = text;
                this._render(text);
            } catch (e) {
                if (url) window.location = url;
                this.open = false;
            }

            this.busy = false;
        },

        close: function () {
            this.open = false;
            // Destroy the fragment: see the id warning above.
            this._render('');
        }
    });
});
