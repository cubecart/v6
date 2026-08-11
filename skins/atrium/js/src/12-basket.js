/**
 * Atrium — add to basket.
 *
 * HOUSE RULE: no ES6 template literals in this folder. See 00-boot.js.
 *
 * ENDPOINT CONTRACT (do not change — this is core behaviour, not skin behaviour):
 *   POST <form action> + "&_g=ajaxadd&t=<timestamp>"
 *   handled by the 'ajaxadd' case in Cubecart::loadPage() -> GUI::displaySideBasket()
 *
 *   Response is one of:
 *     "Redir:<url>"  -> the store is configured to jump to the basket
 *                       (config basket_jump_to, Cart::add())
 *     <html>         -> the rendered box.basket.php fragment, to be swapped
 *                       into #mini-basket
 *
 *   It returns HTML rather than JSON, and plugins hook
 *   class.gui.display_side_basket to modify that HTML. Do not "improve" this
 *   into a JSON API — it is a published extension point.
 *
 * The CSRF token is already inside the form: core injects a hidden input before
 * every </form> (GUI::display()), and FormData picks it up, so the
 * POST satisfies Sanitize::checkToken().
 */

document.addEventListener('alpine:init', function () {
    window.Alpine.data('ccAddToBasket', function () {
        return {
            busy: false,
            added: false,

            async submit(event) {
                var form = event.target;

                // Let the browser enforce required options (a required select
                // with no choice, a required text option left empty).
                if (!form.reportValidity()) return;

                event.preventDefault();
                if (this.busy) return;
                this.busy = true;

                var action = form.getAttribute('action') || window.location.href;
                action = action.replace(/\?.*/, '');
                var url = action + (action.indexOf('?') > -1 ? '&' : '?') +
                          '_g=ajaxadd&t=' + new Date().getTime();

                try {
                    var text = await window.ccPost(url, new FormData(form));

                    if (text.indexOf('Redir:') !== -1) {
                        window.location = text.split('Redir:')[1];
                        return;
                    }

                    // Swap the mini-basket. Alpine's MutationObserver
                    // initialises x-data on the inserted subtree automatically,
                    // so the replacement is live with no re-init call.
                    var host = document.getElementById('mini-basket');
                    if (host) {
                        host.outerHTML = text;
                        var fresh = document.getElementById('mini-basket');
                        if (fresh) window.Alpine.store('basket').syncFrom(fresh);
                    }

                    this.added = true;
                    var self = this;
                    setTimeout(function () { self.added = false; }, 2500);
                } catch (e) {
                    // Network failure: fall back to a normal form post rather
                    // than silently losing the customer's click.
                    form.submit();
                } finally {
                    this.busy = false;
                }
            }
        };
    });
});
