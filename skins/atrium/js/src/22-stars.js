/**
 * Atrium — star rating chooser for the review form.
 *
 * HOUSE RULE: no ES6 template literals in this folder. See 00-boot.js.
 *
 * The five radios in element.product_reviews.php remain the actual control —
 * this only paints them. They are hidden with .cc-sr-only, which keeps them in
 * the tab order, so the native radio group still provides:
 *   - arrow-key selection within the group
 *   - a single tab stop
 *   - the value core reads from $_POST['rating']
 * Nothing here handles keys; doing so would fight the browser.
 *
 * `hover` is a transient preview and `value` is the committed choice. Fill is
 * `i <= (hover || value)`, so leaving the row falls back to what is actually
 * selected rather than emptying the stars.
 */
document.addEventListener('alpine:init', function () {
    window.Alpine.data('ccStarRating', function () {
        return {
            value: 0,
            hover: 0,

            init: function () {
                // Core pre-checks a radio when the form is redisplayed after a
                // failed post ($RATING_STARS[].checked), so read the truth from
                // the DOM instead of threading it through the template.
                var checked = this.$el.querySelector('input[name="rating"]:checked');
                if (checked) this.value = parseInt(checked.value, 10) || 0;

                // Keep in step if anything else moves the selection — arrow
                // keys fire change on the radio, and the validator may reset it.
                var self = this;
                this.$el.addEventListener('change', function (e) {
                    if (e.target && e.target.name === 'rating') {
                        self.value = parseInt(e.target.value, 10) || 0;
                    }
                });
            }
        };
    });
});
