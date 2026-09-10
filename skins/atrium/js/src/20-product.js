/**
 * Atrium — product page: option-driven price recalculation and image swapping.
 *
 * HOUSE RULE: no ES6 template literals in this folder. See 00-boot.js.
 *
 * ENDPOINT CONTRACT (core, not skin):
 *   GET <form action> + "&_g=ajax_price_format&price[0]=<n>[&price[1]=<n>]"
 *   handled by the 'ajax_price_format' case in Cubecart::loadPage(), which returns a JSON ARRAY
 *   of currency-formatted strings in the same order.
 *
 *   Prices must be formatted server-side: only PHP knows the store's currency,
 *   decimal places, tax mode and rounding. Never format money in JS.
 *
 * DOM CONTRACT (set by element.product.call_to_action.php):
 *   #ptp  data-price = price_to_pay      the price actually charged
 *   #fbp  data-price = full_base_price   the "was" price; present only on sale
 *
 * OPTION PRICING (element.product.options.php):
 *   [name^=productOptions] carry data-price and data-price-original.
 *   class="absolute" means the option REPLACES the base price rather than
 *   adding to it — subtract the base before adding the option's own price.
 *
 * COMBINATION STOCK (#cc-option-stock, from Catalogue::optionStockMap):
 *   {participants:[assign_id...], combinations:{"46|53":{ok:false,note:""}}}
 *   Keyed on assign_id because that is what the form posts. Only participants
 *   count towards the key — a product can carry options that are not part of
 *   the stock matrix, and including one would never match. An unknown
 *   combination means "no opinion": leave the button alone and let the server
 *   decide, exactly as before.
 *
 *   ⚠ A STORE, not component state. The option fields and the add-to-basket
 *   button sit inside ccAddToBasket, so @change on a select is evaluated in
 *   THAT scope: reads resolve up to ccProduct, but a write lands on the child
 *   scope and the parent never sees it. Held here once, read by both.
 */

document.addEventListener('alpine:init', function () {
    window.Alpine.store('optionStock', {
        available: true,
        note: '',
        // Stock of the SELECTED combination, or 0 when unknown or not low.
        // Core only publishes it up to Catalogue::LOW_STOCK_DISCLOSE_MAX.
        stock: 0,
        _map: null,
        _one: '',
        _many: '',

        load: function () {
            var el = document.getElementById('cc-option-stock');
            if (!el) return;
            // Translated on the server; %d is substituted here.
            this._one = el.getAttribute('data-low-one') || '';
            this._many = el.getAttribute('data-low-many') || '';
            try {
                var data = JSON.parse(el.textContent);
                if (data && data.combinations) this._map = data;
            } catch (e) {
                // A malformed payload must not take the page down with it.
                this._map = null;
            }
        },

        /** "Only 2 left in stock" for the current combination, or ''. */
        lowText: function (threshold) {
            var n = this.stock;
            if (!n || !threshold || n > threshold) return '';
            var s = (n === 1 && this._one) ? this._one : this._many;
            return s ? s.replace('%d', n) : '';
        },

        /* Judge the combination currently selected on the page.
           Scans the DOCUMENT rather than taking an element: called from a
           @change handler, Alpine's $el is the SELECT that fired, not the
           component root, so searching "inside" it finds nothing. Only one
           product form carries productOptions fields, and `participants`
           filters out anything not in the stock matrix anyway. */
        check: function () {
            if (!this._map) return;

            var participants = this._map.participants || [];
            var chosen = [];
            var fields = document.querySelectorAll('[name^=productOptions]');
            for (var i = 0; i < fields.length; i++) {
                var field = fields[i];
                if ((field.type === 'radio' || field.type === 'checkbox') && !field.checked) continue;
                var id = parseInt(field.value, 10);
                if (participants.indexOf(id) !== -1) chosen.push(id);
            }

            // Nothing from the matrix chosen yet, so there is nothing to judge.
            if (!chosen.length) {
                this.available = true;
                this.note = '';
                this.stock = 0;
                return;
            }

            chosen.sort(function (a, b) { return a - b; });
            var entry = this._map.combinations[chosen.join('|')];
            this.available = entry ? !!entry.ok : true;
            this.note = (entry && entry.note) ? entry.note : '';
            this.stock = (entry && entry.stock) ? entry.stock : 0;
        }
    });

    window.Alpine.data('ccProduct', function () {
        return {
            _seq: 0,

            init: function () {
                window.Alpine.store('optionStock').load();
                window.Alpine.store('optionStock').check();
                this.watchBuyButton();

                // Only meaningful when there is a price element AND options.
                if (!document.getElementById('ptp')) return;
                if (!this.$el.querySelector('[name^=productOptions]')) return;
                this.recalc();
            },

            /** Sum the selected options, then ask the server to format. */
            async recalc() {
                var ptpEl = document.getElementById('ptp');
                var fbpEl = document.getElementById('fbp');
                if (!ptpEl) return;

                var base = parseFloat(ptpEl.getAttribute('data-price'));
                var baseOriginal = fbpEl ? parseFloat(fbpEl.getAttribute('data-price')) : null;
                if (isNaN(base)) return;

                var delta = 0;
                var deltaOriginal = 0;
                var fields = this.$el.querySelectorAll('[name^=productOptions]');

                fields.forEach(function (el) {
                    var price, priceOriginal, absolute;

                    if (el.tagName === 'SELECT') {
                        var opt = el.options[el.selectedIndex];
                        if (!opt || !el.value) return;
                        price = parseFloat(opt.getAttribute('data-price'));
                        priceOriginal = parseFloat(opt.getAttribute('data-price-original'));
                        absolute = opt.classList.contains('absolute');
                    } else if (el.type === 'radio' || el.type === 'checkbox') {
                        if (!el.checked) return;
                        price = parseFloat(el.getAttribute('data-price'));
                        priceOriginal = parseFloat(el.getAttribute('data-price-original'));
                        absolute = el.classList.contains('absolute');
                    } else if (el.type === 'hidden') {
                        price = parseFloat(el.getAttribute('data-price'));
                        priceOriginal = parseFloat(el.getAttribute('data-price-original'));
                        absolute = el.classList.contains('absolute');
                    } else {
                        // text / textarea: only charged when the customer typed something
                        if (!el.value) return;
                        price = parseFloat(el.getAttribute('data-price'));
                        priceOriginal = parseFloat(el.getAttribute('data-price-original'));
                        absolute = el.classList.contains('absolute');
                    }

                    if (isNaN(price)) price = 0;
                    if (isNaN(priceOriginal)) priceOriginal = price;

                    if (absolute) {
                        delta -= base;
                        if (baseOriginal !== null) deltaOriginal -= baseOriginal;
                    }
                    delta += price;
                    deltaOriginal += priceOriginal;
                });

                var total = base + delta;
                var query = '_g=ajax_price_format&price[0]=' + encodeURIComponent(total);
                if (baseOriginal !== null) {
                    query += '&price[1]=' + encodeURIComponent(baseOriginal + deltaOriginal);
                }

                var action = (this.$el.getAttribute('action') || window.location.href);
                var url = action + (action.indexOf('?') > -1 ? '&' : '?') + query;

                var seq = ++this._seq;
                try {
                    var res = await fetch(url, { credentials: 'same-origin' });
                    var prices = await res.json();
                    if (seq !== this._seq) return;      // a newer change won
                    if (!Array.isArray(prices)) return;

                    ptpEl.innerHTML = prices[0];
                    // Keep the sticky bar's price in step with the options.
                    var mirrors = document.querySelectorAll('[data-cc-price-mirror]');
                    for (var m = 0; m < mirrors.length; m++) {
                        mirrors[m].innerHTML = prices[0];
                    }
                    if (fbpEl && prices.length > 1) {
                        fbpEl.innerHTML = prices[1];
                        // Hide the "was" price when the option choice has made
                        // it no cheaper than what is actually being charged.
                        var num = function (s) {
                            return parseFloat(String(s).replace(/[^0-9.-]/g, ''));
                        };
                        fbpEl.style.display = num(prices[0]) <= num(prices[1]) ? 'none' : '';
                    }
                } catch (e) {
                    // Leave the server-rendered price in place: a stale-but-real
                    // price is safer than a blank or a guess.
                }
            },

            /** Swap the main image when an option carries its own picture. */
            swapImage: function (event) {
                var el = event.target;
                var src = '';

                if (el.tagName === 'SELECT') {
                    var opt = el.options[el.selectedIndex];
                    src = opt ? opt.getAttribute('data-image') : '';
                } else if (el.type === 'radio' || el.type === 'checkbox') {
                    src = el.checked ? el.getAttribute('data-image') : '';
                } else {
                    src = el.getAttribute('data-image');
                }

                if (!src) return;
                var preview = document.getElementById('img-preview');
                if (preview) preview.src = src;
            },

            /* Reveal the sticky bar once the real button scrolls away.
               IntersectionObserver rather than a scroll handler: no listener
               running on every frame, and it reports the state on registration
               so the bar is correct if the customer lands mid-page on a
               #fragment. Browsers without it simply never show the bar, which
               is the behaviour this skin had until now. */
            watchBuyButton: function () {
                var target = document.getElementById('cc-main-buy');
                if (!target || !('IntersectionObserver' in window)) return;
                new window.IntersectionObserver(function (entries) {
                    /* isIntersecting alone is not enough: it is false both when
                       the button has scrolled off the TOP and when it is still
                       below the fold, and showing a duplicate buy button before
                       the customer has even reached the real one is just noise.
                       boundingClientRect.top < 0 distinguishes the two. */
                    var entry = entries[0];
                    var scrolledPast = entry.boundingClientRect.top < 0;
                    window.Alpine.store('ui').stickyBuy = !entry.isIntersecting && scrolledPast;
                }, { threshold: 0 }).observe(target);
            },

            onOptionChange: function (event) {
                this.recalc();
                this.swapImage(event);
                window.Alpine.store('optionStock').check();

                /* A failed add-to-basket reloads the PDP with a server-side
                   error, typically "that option combination is out of stock".
                   Picking a different option makes that error a statement about
                   a selection the customer is no longer making, so retire it.
                   box.errors.php listens for this on window. */
                window.dispatchEvent(new CustomEvent('cc-stale-errors'));
            }
        };
    });
});
