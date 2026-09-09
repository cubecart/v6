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
            index: 0,
            images: [],

            /* The thumbnails are the only list of images, so read it back off
               them rather than serialising $GALLERY into the x-data attribute.
               A single-image product renders no thumbnails, so images stays
               empty and the lightbox arrows never appear. */
            init: function () {
                var nodes = this.$el.querySelectorAll('[data-full]');
                for (var i = 0; i < nodes.length; i++) {
                    this.images.push({
                        medium: nodes[i].getAttribute('data-medium'),
                        full: nodes[i].getAttribute('data-full')
                    });
                }
            },

            /** Show a thumbnail's larger version in the main preview. */
            show: function (medium, full, index) {
                var preview = document.getElementById('img-preview');
                if (preview && medium) preview.src = medium;
                if (full) this.full = full;
                if (typeof index === 'number') this.index = index;
            },

            /** Move the lightbox and the page preview together, wrapping. */
            step: function (delta) {
                if (this.images.length < 2) return;
                this.index = (this.index + delta + this.images.length) % this.images.length;
                var image = this.images[this.index];
                this.show(image.medium, image.full, this.index);
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
