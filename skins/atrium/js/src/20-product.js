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
 */

document.addEventListener('alpine:init', function () {
    window.Alpine.data('ccProduct', function () {
        return {
            _seq: 0,

            init: function () {
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

            onOptionChange: function (event) {
                this.recalc();
                this.swapImage(event);
            }
        };
    });
});
