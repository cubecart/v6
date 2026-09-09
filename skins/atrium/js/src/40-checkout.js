/**
 * Atrium — checkout behaviour.
 *
 * HOUSE RULE: no ES6 template literals in this folder. See 00-boot.js.
 *
 * Replaces the checkout half of foundation's 2.cubecart.js. Every DOM id and
 * field name below is a CORE CONTRACT read by PHP — see the comments in
 * content.checkout.php and content.checkout.confirm.php.
 *
 * COUNTRY -> STATE DEPENDENCY
 *   window.county_list is emitted by the template from $STATE_JSON: an object
 *   keyed by country numcode whose values are arrays of {id, name}.
 *   Each country <select> carries rel="<id of the state field>", and the
 *   selected <option> carries data-status:
 *       1 = state REQUIRED   2 = state optional   3 = state HIDDEN
 *   The wrapper to show/hide is "<target id>_wrapper".
 *
 *   Foundation replaced the state <input> with a <select> (and back) by
 *   destroying and rebuilding the element. That loses any value the customer
 *   already typed and any listener bound to it. Atrium keeps BOTH elements in
 *   the DOM and toggles which one is enabled, so the posted field name is
 *   always right and nothing is rebuilt.
 */

document.addEventListener('alpine:init', function () {
    window.Alpine.data('ccCheckout', function () {
        return {
            deliveryIsBilling: true,
            showRegister: false,
            mode: 'register',        // 'register' | 'login' — guest checkout only
            estimateOpen: false,

            init: function () {
                var dib = document.getElementById('delivery_is_billing');
                if (dib) {
                    this.deliveryIsBilling = dib.type === 'hidden' ? true : dib.checked;
                }
                this.syncDeliveryRequired();
                /* No checkbox means the merchant fixed the mode in the skin
                   settings: `required` renders the password block with no
                   toggle, `guest` renders neither. The block's presence is
                   therefore the answer in both cases. */
                var reg = document.getElementById('show-reg');
                this.showRegister = reg ? reg.checked : !!document.getElementById('account-reg');
                // Sync here too, not only on change: the server re-renders the
                // box already ticked ($REGISTER_CHECKED), and a change event
                // never fires on that path — so the password fields stayed
                // un-required and an empty password reached the server.
                this.syncRegisterRequired();

                // If the server came back with validation errors, the customer
                // was mid-edit — open the address form rather than hiding it.
                if (document.querySelector('.cc-alert-error')) {
                    this.mode = 'register';
                }

                // Deep links used by the login/register toggle.
                if (window.location.hash === '#login') this.mode = 'login';
                if (window.location.hash === '#register') this.mode = 'register';

                this.syncAuthFields();
                this.$nextTick(this.initCountries.bind(this));
            },

            /* ---- guest login / register ------------------------------------
               The login and register field sets live in ONE form, so both would
               post together. Foundation disabled the inactive set so the server
               only ever sees one. Same approach here — disabled fields are not
               submitted and are skipped by validation. */
            setMode: function (m) {
                this.mode = m;
                this.syncAuthFields();
            },

            syncAuthFields: function () {
                var login = this.mode === 'login';
                ['login-username', 'login-password', 'checkout_login_btn'].forEach(function (id) {
                    var el = document.getElementById(id);
                    if (el) el.disabled = !login;
                });
                var pw = document.getElementById('reg_password');
                var pc = document.getElementById('reg_passconf');
                if (pw) pw.disabled = login;
                if (pc) pc.disabled = login;
            },

            /** "Make Changes": swap the address summary for the editable form. */
            makeChanges: function () {
                this.setMode('register');
                var summary = document.getElementById('register_false_address');
                if (summary) summary.classList.add('hidden');
                var form = document.getElementById('checkout_register_form');
                if (form) form.classList.remove('hidden');
            },

            /* ---- delivery address ------------------------------------------ */
            toggleDelivery: function (event) {
                this.deliveryIsBilling = event.target.checked;
                this.syncDeliveryRequired();
            },

            /* required must track visibility. #address_delivery is hidden while
               delivery mirrors billing, but its fields ship with `required`, so
               validation failed on controls the customer could not see: the
               Checkout button did nothing and the messages sat inside the hidden
               block. Same trap as the register password fields. */
            syncDeliveryRequired: function () {
                var required = !this.deliveryIsBilling;
                ['del_first', 'del_last', 'del_line1', 'del_town', 'del_postcode'].forEach(function (id) {
                    var el = document.getElementById(id);
                    if (el) el.required = required;
                });
                /* The delivery state's requirement belongs to the chosen
                   country's zone status, so only ever CLEAR it here and let
                   ccApplyCountryState set it when the block is shown again. */
                if (!required) {
                    ['delivery_state', 'delivery_state_select'].forEach(function (id) {
                        var el = document.getElementById(id);
                        if (el) el.required = false;
                    });
                }
            },

            /* ---- create-an-account toggle ---------------------------------- */
            toggleRegister: function (event) {
                this.showRegister = event.target.checked;
                this.syncRegisterRequired();
            },

            /* required must track visibility, or the browser blocks submit on a
               field the customer cannot see. Called from init() as well. */
            syncRegisterRequired: function () {
                var pw = document.getElementById('reg_password');
                var pc = document.getElementById('reg_passconf');
                if (pw) pw.required = this.showRegister;
                if (pc) pc.required = this.showRegister;
            },

            /* ---- proceed ---------------------------------------------------
               Core distinguishes "update the basket" from "go to the next step"
               by the presence of a `proceed` field. The button is name="proceed"
               so a plain submit already carries it; this only adds it when the
               submit came from somewhere else. */
            proceed: function (event) {
                var form = document.getElementById('checkout_form');
                if (!form) return;

                // ⚠ Look for a HIDDEN input specifically. The earlier version
                // tested [name="proceed"], which ALWAYS matched — the submit
                // button itself is name="proceed" — so the compensator never
                // ran. Under invisible captcha (mode 3) grecaptcha submits the
                // form programmatically, and a programmatic submit does not
                // contribute the clicked button's name/value, so `proceed`
                // never posted and checkout silently never advanced.
                if (!form.querySelector('input[type="hidden"][name="proceed"]')) {
                    var i = document.createElement('input');
                    i.type = 'hidden';
                    i.name = 'proceed';
                    i.value = '1';
                    i.dataset.ccProceed = '1';
                    form.appendChild(i);
                }
            },

            /** Remove the compensator again — "Update basket" must NOT carry
             *  `proceed`, or updating a quantity would jump the customer to the
             *  next checkout step. */
            clearProceed: function () {
                var form = document.getElementById('checkout_form');
                if (!form) return;
                var i = form.querySelector('input[data-cc-proceed]');
                if (i) i.remove();
            },

            /* ---- country -> state ------------------------------------------
               Delegated to the page-level initialiser at the bottom of this
               file so the address book gets it too — that page has no
               ccCheckout component. */
            initCountries: function () {
                window.ccInitCountryState();
                // Runs after the zone status has been applied, so a hidden
                // delivery state cannot come back required.
                this.syncDeliveryRequired();
            },

            _unusedApplyState: function (sel) {
                if (typeof window.county_list !== 'object' || !window.county_list) return;

                var targetId = (sel.getAttribute('rel') && sel.id !== 'country-list')
                    ? sel.getAttribute('rel')
                    : 'state-list';

                var input   = document.getElementById(targetId);
                var select  = document.getElementById(targetId + '_select');
                var wrapper = document.getElementById(targetId + '_wrapper');
                if (!input) return;

                var opt = sel.options[sel.selectedIndex];
                var status = opt ? opt.getAttribute('data-status') : '2';
                var list = window.county_list[sel.value];
                var hasList = Array.isArray(list) && list.length > 0;

                // Show / hide / require, per the country's zone status.
                var hidden = status === '3';
                if (wrapper) wrapper.style.display = hidden ? 'none' : '';

                if (hasList) {
                    // Populate the paired <select> and use it instead of the text input.
                    if (select) {
                        var current = (input.value || select.value || '').toLowerCase();
                        select.innerHTML = '';
                        var blank = document.createElement('option');
                        blank.value = '';
                        blank.textContent = sel.getAttribute('title') || '';
                        select.appendChild(blank);
                        list.forEach(function (row) {
                            var o = document.createElement('option');
                            o.value = row.id > 0 ? row.id : '';
                            o.textContent = row.name;
                            if (current && (String(row.name).toLowerCase() === current || String(row.id) === current)) {
                                o.selected = true;
                            }
                            select.appendChild(o);
                        });
                        // Only ONE of the pair may be enabled: they share a name,
                        // and two enabled fields would post two values.
                        select.disabled = hidden;
                        select.required = !hidden && status === '1';
                        select.hidden = false;
                        input.disabled = true;
                        input.hidden = true;
                    }
                } else {
                    if (select) {
                        select.disabled = true;
                        select.hidden = true;
                        select.required = false;
                    }
                    input.disabled = hidden;
                    input.hidden = false;
                    input.required = !hidden && status === '1';
                }
            },

            /* ---- shipping estimate panel ----------------------------------- */
            toggleEstimate: function () {
                this.estimateOpen = !this.estimateOpen;
            }
        };
    });

    /** Basket line quantity stepper. Reveals the update button once a value
     *  actually differs from what the server currently holds. */
    window.Alpine.data('ccBasketLine', function (initial) {
        return {
            qty: parseInt(initial, 10) || 0,
            original: parseInt(initial, 10) || 0,

            get changed() {
                return this.qty !== this.original;
            },

            step: function (delta) {
                var next = this.qty + delta;
                if (next < 0 || next > 999) return;
                this.qty = next;
            }
        };
    });
});

/**
 * Gift certificate form: the recipient email field only applies to email
 * delivery. Kept out of the main ccCheckout component because this page has no
 * basket, no addresses and no gateways — nothing else in ccCheckout applies.
 */
document.addEventListener('alpine:init', function () {
    window.Alpine.data('ccGiftCertificate', function (initial) {
        return { method: initial || 'e' };
    });
});


/* ---------------------------------------------------------------------------
 * Country -> state, as a PAGE-LEVEL behaviour rather than a component method.
 *
 * Three pages need it — checkout, the shipping estimator and the address book —
 * and only checkout has a ccCheckout component. Binding it to the DOM instead
 * of to a component means a page just has to render the right markup:
 *
 *   <select class="country-list" rel="<state field id>">   options carry data-status
 *   <input  id="<state field id>"        name="x[state]">
 *   <select id="<state field id>_select" name="x[state]" hidden disabled>
 *   wrapper id="<state field id>_wrapper"
 *
 * data-status on the selected option: 1 = required, 2 = optional, 3 = hidden.
 * window.county_list maps country numcode -> [{id, name}].
 *
 * Input and select share a name and exactly ONE is ever enabled — a disabled
 * control is not submitted, so the posted value is unambiguous. Foundation
 * destroyed and rebuilt the element instead, losing anything already typed.
 * ------------------------------------------------------------------------- */
window.ccApplyCountryState = function (sel) {
    if (typeof window.county_list !== 'object' || !window.county_list) return;

    var targetId = (sel.getAttribute('rel') && sel.id !== 'country-list')
        ? sel.getAttribute('rel')
        : (sel.getAttribute('rel') || 'state-list');

    var input   = document.getElementById(targetId);
    var select  = document.getElementById(targetId + '_select');
    var wrapper = document.getElementById(targetId + '_wrapper');
    if (!input) return;

    var opt     = sel.options[sel.selectedIndex];
    var status  = opt ? opt.getAttribute('data-status') : '2';
    var list    = window.county_list[sel.value];
    var hasList = Array.isArray(list) && list.length > 0;
    var hidden  = status === '3';

    if (wrapper) wrapper.style.display = hidden ? 'none' : '';

    /* The state field is required for some countries and not others, so its
       "(Optional)" marker cannot be baked into the template. */
    var mark = document.querySelector('label[for="' + targetId + '"] [data-cc-optional]');
    if (mark) mark.hidden = (status === '1');

    if (hasList && select) {
        var current = (input.value || select.value || '').toLowerCase();
        select.innerHTML = '';
        var blank = document.createElement('option');
        blank.value = '';
        blank.textContent = sel.getAttribute('title') || '';
        select.appendChild(blank);
        list.forEach(function (row) {
            var o = document.createElement('option');
            o.value = row.id > 0 ? row.id : '';
            o.textContent = row.name;
            if (current && (String(row.name).toLowerCase() === current || String(row.id) === current)) {
                o.selected = true;
            }
            select.appendChild(o);
        });
        select.disabled = hidden;
        select.required = !hidden && status === '1';
        select.hidden   = false;
        input.disabled  = true;
        input.hidden    = true;
    } else {
        if (select) {
            select.disabled = true;
            select.hidden   = true;
            select.required = false;
        }
        input.disabled = hidden;
        input.hidden   = false;
        input.required = !hidden && status === '1';
    }
};

/* ---- Address lookup fallback ----------------------------------------------
 * With a postcode-lookup plugin active ($ADDRESS_LOOKUP) both address templates
 * render #address_form with class="hidden" — and town and postcode inside it
 * are `required`. Foundation revealed the block again from show_address_form()
 * (2.cubecart.js:313-314); Atrium shipped no equivalent, so a failed lookup left
 * required fields the customer could neither see nor fill, and Save/Checkout
 * silently refused to submit with its error messages rendered inside the hidden
 * block. Dormant while no lookup plugin is installed: the block renders visible.
 * ------------------------------------------------------------------------- */
window.ccRevealAddressForm = function () {
    var el = document.getElementById('address_form');
    if (el) el.classList.remove('hidden');
};

/* `invalid` does not bubble, hence capture. checkValidity() in 50-validate.js
   fires it at the field, so the block opens before the validator tries to focus
   and scroll to a field that is still display:none. */
document.addEventListener('invalid', function (e) {
    var block = document.getElementById('address_form');
    if (block && block.contains(e.target)) window.ccRevealAddressForm();
}, true);

window.ccInitCountryState = function () {
    document.querySelectorAll('select.country-list, select#country-list').forEach(function (sel) {
        if (sel.dataset.ccBound) return;      // idempotent: safe to call twice
        sel.dataset.ccBound = '1';
        window.ccApplyCountryState(sel);
        sel.addEventListener('change', function () { window.ccApplyCountryState(sel); });
    });
};

document.addEventListener('DOMContentLoaded', function () {
    window.ccInitCountryState();

    var block = document.getElementById('address_form');
    if (!block || !block.classList.contains('hidden')) return;

    // "Address not found" — the customer's way out of a lookup that missed.
    var fail = document.getElementById('lookup_fail');
    if (fail) {
        fail.addEventListener('click', function (e) {
            e.preventDefault();
            window.ccRevealAddressForm();
        });
    }

    // The server already rejected something, so the customer is mid-correction
    // and needs the manual fields regardless of what the lookup did.
    if (document.querySelector('.cc-alert-error')) window.ccRevealAddressForm();
});
