/**
 * Atrium — controls for the homepage hero banners.
 *
 * HOUSE RULE: no ES6 template literals in this folder. See 00-boot.js.
 *
 * The .cc-hero markup arrives inside the merchant's homepage DOCUMENT (seeded
 * by the installer from the default_doc_content_welcome language string), so
 * the skin cannot template controls into it. This component attaches to a
 * wrapper in content.homepage.php, finds the .cc-hero scroller inside the
 * injected HTML, and drives it.
 *
 * The scroller is CSS scroll-snap and works on its own — swipe on touch,
 * shift+wheel on desktop. What it lacks is any SIGNAL that more banners exist,
 * because the scrollbar is hidden: on a 1400px desktop that left 3003px of
 * banners in a 993px window with nothing to click. These controls are that
 * signal, not the scrolling mechanism.
 *
 * DELIBERATELY NOT AUTO-ROTATING. Auto-advancing heroes engage poorly (clicks
 * concentrate on the first slide), and anything that moves for more than five
 * seconds needs a pause control to satisfy WCAG 2.2.2 plus a
 * prefers-reduced-motion path. Manual controls avoid all of it.
 */
document.addEventListener('alpine:init', function () {
    window.Alpine.data('ccHero', function () {
        return {
            count: 0,
            index: 0,
            _el: null,

            init: function () {
                this._el = this.$el.querySelector('.cc-hero');
                if (!this._el) return;
                this.count = this._el.children.length;
                if (this.count < 2) return;

                var self = this;
                var ticking = false;
                this._el.addEventListener('scroll', function () {
                    if (ticking) return;
                    ticking = true;
                    window.requestAnimationFrame(function () {
                        var w = self._el.clientWidth || 1;
                        self.index = Math.round(self._el.scrollLeft / w);
                        ticking = false;
                    });
                }, { passive: true });
            },

            go: function (i) {
                if (!this._el) return;
                var n = Math.max(0, Math.min(this.count - 1, i));
                this._el.scrollTo({ left: n * this._el.clientWidth, behavior: 'smooth' });
                this.index = n;
            },
            next: function () { this.go(this.index + 1); },
            prev: function () { this.go(this.index - 1); }
        };
    });
});
