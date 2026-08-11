/**
 * Atrium — category listing: grid/list view toggle.
 *
 * HOUSE RULE: no ES6 template literals in this folder. See 00-boot.js.
 *
 * Foundation rendered EVERY product twice — a .product_list_view block and a
 * .product_grid_view block — and toggled which was hidden, disabling the
 * quantity input in whichever was inactive so the form did not submit two
 * values for the same name. Atrium renders each product ONCE and changes the
 * container's layout classes, so there is no duplicate markup and no disabled-
 * input bookkeeping.
 *
 * The choice persists in localStorage via @alpinejs/persist. Foundation used a
 * two-year `product_view` cookie; localStorage keeps it off every HTTP request.
 */

document.addEventListener('alpine:init', function () {
    window.Alpine.data('ccProductList', function () {
        return {
            view: window.Alpine.$persist('grid').as('cc_product_view'),

            isGrid: function () {
                return this.view === 'grid';
            },

            setView: function (v) {
                this.view = v;
            }
        };
    });
});

/**
 * Simple client-side filter for a long checkbox list (manufacturers).
 * Filtering only hides rows — it never unchecks them — so a selection made
 * before typing still posts.
 */
document.addEventListener('alpine:init', function () {
    window.Alpine.data('ccFilterList', function () {
        return {
            q: '',
            match: function (label) {
                if (!this.q) return true;
                return String(label).toLowerCase().indexOf(this.q.toLowerCase()) !== -1;
            }
        };
    });
});
