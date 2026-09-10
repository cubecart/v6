/**
 * Atrium — search-as-you-type.
 *
 * HOUSE RULE: no ES6 template literals in this folder. See 00-boot.js.
 *
 * Endpoint contract, unchanged from foundation's 2.cubecart.js:
 *   GET ?_e=es&q=<term>&a=<amount>
 * returns a JSON array of {product_id, name, thumbnail}, or something that is
 * not an array when there are no matches. Only active when Elasticsearch is
 * enabled, which the template signals with the `es` class on the input —
 * exactly the flag foundation used.
 */

/* Header magnifier, below lg. The panel it reveals lives in main.php and the
   state is the shared ui store, so the drawer's closeAll() closes it too.
   Focus is moved on the next frame: x-show flips display, and an element that
   is still display:none cannot take focus. */
window.ccToggleSearch = function () {
    var store = window.Alpine.store('ui');
    store.searchOpen = !store.searchOpen;
    if (!store.searchOpen) return;
    // The drawer is positioned below a 4rem header; leaving it open while the
    // search row grows the header would leave a gap above it.
    store.menuOpen = false;
    window.requestAnimationFrame(function () {
        var el = document.getElementById('cc-search-input-mobile');
        if (el) el.focus();
    });
};

document.addEventListener('alpine:init', function () {
    window.Alpine.data('ccSearch', function () {
        return {
            term: '',
            results: [],
            open: false,
            searched: false,
            /* Mirrors data-image. The template needs it so it can reserve the
               thumbnail slot even when a product has no image — 308 of the
               indexed products carry no `thumbnail` field at all, and without a
               placeholder those rows lose their 40px leading box and the text
               jumps left, breaking the column. */
            showImages: false,
            /* A newer query is in flight. Dims the stale list rather than
               blanking it, so the panel does not collapse and reflow between
               keystrokes. */
            busy: false,
            _seq: 0,

            close: function () {
                this.open = false;
            },

            /** Escape user input before it is ever put back into the DOM. */
            _escape: function (s) {
                return String(s)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            },

            /** Bold each search word inside the (already escaped) product name. */
            _highlight: function (name, term) {
                var safe = this._escape(name);
                var words = term.split(/\s+/).filter(function (w) {
                    return w && w !== '*';
                });
                words.forEach(function (w) {
                    // Escape regex metacharacters in the user's own term.
                    var pattern = w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    safe = safe.replace(new RegExp('(' + pattern + ')', 'ig'), '<strong>$1</strong>');
                });
                return safe;
            },

            async go() {
                var input = this.$refs.input;
                // No Elasticsearch -> no live search. Plain form submit still works.
                if (!input || !input.classList.contains('es')) return;

                var term = this.term.trim();
                if (!term) {
                    this.results = [];
                    this.searched = false;
                    this.busy = false;
                    this.open = false;
                    return;
                }

                var amount = input.getAttribute('data-amount') || 15;
                var showImages = input.getAttribute('data-image') === 'true';
                this.showImages = showImages;

                // Guard against out-of-order responses: only the newest wins.
                var seq = ++this._seq;
                this.busy = true;

                try {
                    var res = await fetch('?_e=es&q=' + encodeURIComponent(term) + '&a=' + encodeURIComponent(amount), {
                        credentials: 'same-origin'
                    });
                    var data = await res.json();
                    if (seq !== this._seq) return;

                    var self = this;
                    this.results = Array.isArray(data) ? data.map(function (p) {
                        return {
                            product_id: p.product_id,
                            name: p.name,
                            url: '?_a=product&product_id=' + encodeURIComponent(p.product_id),
                            thumbnail: showImages && p.thumbnail ? p.thumbnail : '',
                            highlighted: self._highlight(p.name, term)
                        };
                    }) : [];
                } catch (e) {
                    if (seq !== this._seq) return;
                    this.results = [];
                }

                this.busy = false;
                this.searched = true;
                this.open = true;

                /* Announce only the empty case, and only from the markup the
                   server rendered: a bare result count would need a string this
                   skin does not have, and an untranslated one is worse than
                   silence. */
                if (!this.results.length) {
                    var empty = document.querySelector('#sayt_results, #sayt_results-mobile');
                    empty = empty && empty.parentNode ? empty.parentNode.querySelector('p') : null;
                    if (empty) window.ccAnnounce(empty.textContent);
                }
            }
        };
    });
});
