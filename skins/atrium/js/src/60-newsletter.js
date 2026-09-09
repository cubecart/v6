/**
 * Atrium — newsletter signup box.
 *
 * HOUSE RULE: no ES6 template literals in this folder. See 00-boot.js.
 *
 * The captcha block is hidden until the email field is focused, so an unused
 * footer widget never loads third-party captcha JS on every page view. Once
 * revealed it stays revealed — re-hiding it would tear down a widget the
 * customer may already have solved.
 */

document.addEventListener('alpine:init', function () {
    window.Alpine.data('ccNewsletter', function () {
        return {
            showCaptcha: false
        };
    });

    /* Exit-intent modal (templates/modal.exit.php). Shown at most once a
       month per browser, and never on a touch device: the pointer leaving the
       viewport top is the only trigger, so there is nothing to fire there. */
    window.Alpine.data('ccExitModal', function () {
        return {
            open: false,

            init: function () {
                if (document.cookie.indexOf('newsletter_exit=') !== -1) return;

                var self = this;
                // Armed late: a pointer that swings off the window while the
                // page is still painting is not an exit.
                var armed = false;
                setTimeout(function () { armed = true; }, 3000);

                document.addEventListener('mouseout', function (event) {
                    if (!armed || self.open) return;
                    // Only a real exit past the top edge. relatedTarget is
                    // null when the pointer leaves the document entirely.
                    if (event.relatedTarget || event.clientY > 0) return;
                    self.show();
                });
            },

            show: function () {
                this.open = true;
                document.cookie = 'newsletter_exit=1;path=/;max-age=2592000;SameSite=Lax';
            },

            close: function () {
                this.open = false;
            }
        };
    });
});
