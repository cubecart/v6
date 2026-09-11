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
            index: 0,        // which image the PAGE preview is showing
            lightboxIndex: 0, // which image the LIGHTBOX is showing
            images: [],
            _touchX: null,
            _touchY: null,
            _swiped: false,

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

            /* Move the LIGHTBOX only, wrapping. It used to carry the page
               preview and the thumbnail highlight with it, so arrowing through
               images animated the page behind the overlay and left the customer
               on a different image than the one they opened. The page keeps its
               own position; lightboxIndex is re-synced on every open. */
            step: function (delta) {
                if (this.images.length < 2) return;
                this.lightboxIndex = (this.lightboxIndex + delta + this.images.length) % this.images.length;
                var image = this.images[this.lightboxIndex];
                if (image && image.full) this.full = image.full;
            },

            /** Move the PAGE preview, wrapping. Dots and swipe both come here. */
            goTo: function (i) {
                if (this.images.length < 2) return;
                var n = (i + this.images.length) % this.images.length;
                var image = this.images[n];
                if (image) this.show(image.medium, image.full, n);
            },

            /* Swipe on the preview. Deliberately not scroll-snap: the preview is
               a single <img> whose src is also swapped by 20-product.js when an
               option carries its own image, and a track of slides would leave
               that writing to whichever slide happened to be first. */
            swipeStart: function (event) {
                // A second finger means a pinch-zoom, not a swipe.
                if (event.touches && event.touches.length > 1) {
                    this._touchX = null;
                    return;
                }
                var t = event.changedTouches[0];
                this._touchX = t.clientX;
                this._touchY = t.clientY;
                this._swiped = false;
            },

            /** -1, 1 or 0 for "that was not a swipe". Shared by both surfaces. */
            _swipeDelta: function (event) {
                if (this._touchX === null) return 0;
                var t = event.changedTouches[0];
                var dx = t.clientX - this._touchX;
                var dy = t.clientY - this._touchY;
                this._touchX = null;
                // Ignore a tap, and anything that is really a vertical scroll.
                if (Math.abs(dx) < 40 || Math.abs(dx) < Math.abs(dy)) return 0;
                return dx < 0 ? 1 : -1;
            },

            /** Swipe on the page preview. */
            swipeEnd: function (event) {
                var delta = this._swipeDelta(event);
                if (!delta) return;
                // Only the page preview sets this: a swipe there ends in a click
                // on the same element, which would otherwise open the lightbox.
                this._swiped = true;
                this.goTo(this.index + delta);
            },

            /** Swipe inside the lightbox — the same gesture as its arrows. */
            swipeLightbox: function (event) {
                var delta = this._swipeDelta(event);
                if (delta) this.step(delta);
            },

            /** Open the lightbox on whatever is currently previewed. */
            enlarge: function () {
                /* A swipe ends in a click on the same element. Without this the
                   lightbox opens every time the customer flicks through. */
                if (this._swiped) {
                    this._swiped = false;
                    return;
                }
                var preview = document.getElementById('img-preview');
                if (!this.full && preview) this.full = preview.src;
                this.lightboxIndex = this.index;
                this.open = true;
            },

            close: function () {
                this.open = false;
            }
        };
    });
});
