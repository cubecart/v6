/**
 * Atrium — product image gallery and lightbox.
 *
 * HOUSE RULE: no ES6 template literals in this folder. See 00-boot.js.
 *
 * Replaces foundation's Clearing (Foundation 5) lightbox and its hover-swap
 * handler. #img-preview is a contract: 20-product.js swaps that element's src
 * when a product option carries its own image.
 */

document.addEventListener('alpine:init', function () {
    window.Alpine.data('ccGallery', function (initialFull) {
        return {
            full: initialFull || '',   // full-size src for the lightbox
            open: false,

            /** Show a thumbnail's larger version in the main preview. */
            show: function (medium, full) {
                var preview = document.getElementById('img-preview');
                if (preview && medium) preview.src = medium;
                if (full) this.full = full;
            },

            /** Open the lightbox on whatever is currently previewed. */
            enlarge: function () {
                var preview = document.getElementById('img-preview');
                if (!this.full && preview) this.full = preview.src;
                this.open = true;
            },

            close: function () {
                this.open = false;
            }
        };
    });
});
