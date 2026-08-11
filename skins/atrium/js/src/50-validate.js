/**
 * Atrium — client-side form validation.
 *
 * HOUSE RULE: no ES6 template literals in this folder. See 00-boot.js.
 *
 * WHAT THIS IS FOR
 * A UX layer only. Every rule here is also enforced server-side, and this file
 * never decides whether data is acceptable — it only tells the customer sooner.
 * Nothing here may be relied on for correctness or security.
 *
 * WHY NOT jQuery VALIDATE (what Foundation uses)
 * Foundation loads jquery.validate.js plus a 409-line rules file. Atrium ships
 * jQuery only as a compatibility shim for third-party plugins (see README) —
 * no skin code uses it, and adding a ~50KB jQuery plugin for this would undo
 * that. The markup already carries the constraint attributes (121 `required`,
 * 14 type=email, 7 type=tel, 31 maxlength), so the browser can do most of the
 * work and this file only supplies what the platform cannot:
 *
 *   - cross-field equality      passconf === password, emailconf === email
 *   - remote uniqueness         "that email already has an account"
 *   - message text              translated, from the store's language pack
 *   - presentation              inline messages instead of browser bubbles,
 *                               which are unstyleable and untranslatable
 *
 * NOVALIDATE IS SET FROM JS, DELIBERATELY
 * The form keeps native validation in the markup; init() adds `novalidate`
 * only once this script is running. With JS off or broken the browser still
 * blocks an empty required field, instead of the form silently losing its
 * only client-side check.
 *
 * MESSAGES
 * Read once from the JSON blob emitted by element.validation_messages.php.
 * Per-field override with data-msg-<rule>, e.g. data-msg-required="...".
 * Falls back to the browser's own localised validationMessage.
 *
 * WHY THIS IS NOT AN Alpine.data() COMPONENT
 * Alpine permits ONE x-data per element, and two forms already have their own
 * (box.newsletter.php uses ccNewsletter, content.certificates.php uses
 * ccGiftCertificate). A component here would collide with those and could not
 * be added to a plugin's form at all. Instead every form carrying
 * data-cc-validate is wired on load, and the instance is parked on the element
 * as form._ccValidator. That also means validation keeps working if Alpine
 * itself fails to load.
 *
 * ENDPOINT CONTRACT (core, not skin) — Cubecart::loadPage() 'ajax_email' case:
 *   POST ?_g=ajax_email            body: email=<addr>      (account lookup)
 *   POST ?_g=ajax_email&source=newsletter  body: subscribe=<addr>
 *   -> {"result":true|false,"token":"..."} when config.csrf=1, else bare true/false
 *   result TRUE  = address is FREE (no account / not subscribed)
 *   result FALSE = already taken
 * The token in the response is the SAME session token, not a rotated one
 * (Session::getToken() only regenerates when absent), so there is nothing to
 * write back into the form.
 */

(function () {
    'use strict';

    /* Message catalogue, parsed once. */
    var MESSAGES = (function () {
        var el = document.getElementById('cc-validation-messages');
        if (!el) return {};
        try { return JSON.parse(el.textContent || '{}'); } catch (e) { return {}; }
    })();

    /* Remote answers are cached per address so retyping the same value does
       not re-hit the server on every blur. */
    var remoteCache = {};

    function messageFor(field, rule) {
        var attr = field.getAttribute('data-msg-' + rule);
        if (attr) return attr;
        if (MESSAGES[rule]) return MESSAGES[rule];
        return field.validationMessage || 'Invalid';
    }

    /** Which built-in constraint failed, as a rule name we have a message for. */
    function nativeRule(field) {
        var v = field.validity;
        if (v.valid) return null;
        if (v.valueMissing) return 'required';
        if (v.typeMismatch) return field.type === 'email' ? 'email' : 'invalid';
        if (v.tooShort) return 'minlength';
        if (v.tooLong) return 'maxlength';
        if (v.patternMismatch) return field.type === 'tel' ? 'phone' : 'invalid';
        if (v.rangeUnderflow || v.rangeOverflow || v.stepMismatch) return 'invalid';
        return 'invalid';
    }

    /** Wire one form. Idempotent — a second call on the same form is a no-op. */
    function createValidator(form) {
        if (form._ccValidator) return form._ccValidator;

        var api = {
            $el: form,
            /* fieldName -> message. Only used to drive the DOM; the DOM is the
               source of truth for validity so a plugin that injects a field
               mid-life is picked up without re-initialising anything. */
            errors: {},
            busy: false,

            /* Set while re-dispatching an event we already validated, so the
               second pass is allowed straight through. */
            _passthrough: false,

            init: function () {
                var form = this.$el;
                // Only now that JS is confirmed running. See header.
                form.setAttribute('novalidate', 'novalidate');

                var self = this;

                /* ── Submit interception ──────────────────────────────────────
                   MUST be a capture-phase CLICK, not the submit event.

                   Invisible reCAPTCHA (content.recaptcha.head.php, config
                   recaptcha=3) binds its own click handler to each
                   .g-recaptcha button and, once it has a token, submits with
                   form.submit(). form.submit() does NOT dispatch a submit
                   event — so a @submit handler never runs on registration,
                   checkout or the newsletter, i.e. precisely the forms where
                   validation matters most. Capturing the click lets us run
                   first and stopPropagation() before grecaptcha ever sees it.

                   Validation is async (remote email lookup), so we cannot
                   decide inline: cancel the click, validate, then replay it. */
                form.addEventListener('click', function (e) {
                    var btn = e.target.closest('button, input[type="submit"], input[type="image"]');
                    if (!btn || !form.contains(btn)) return;
                    var type = (btn.getAttribute('type') || 'submit').toLowerCase();
                    if (type !== 'submit' && type !== 'image') return;

                    if (self._passthrough) { self._passthrough = false; return; }

                    e.preventDefault();
                    e.stopPropagation();          // keep grecaptcha out until we pass
                    self.validateAll().then(function (ok) {
                        if (!ok) return;
                        self._passthrough = true;
                        btn.click();              // now grecaptcha (or the browser) proceeds
                    });
                }, true);

                /* Enter-key submits dispatch a real submit event and never
                   touch the button, so they bypass the handler above. */
                form.addEventListener('submit', function (e) {
                    if (self._passthrough) { self._passthrough = false; return; }
                    e.preventDefault();
                    self.validateAll().then(function (ok) {
                        if (!ok) return;
                        self._passthrough = true;
                        // If the form is reCAPTCHA-protected, go through its
                        // button so a token is still minted; submitting the
                        // form directly would post without one and be rejected.
                        var captcha = form.querySelector('.g-recaptcha');
                        if (captcha) { captcha.click(); return; }
                        HTMLFormElement.prototype.submit.call(form);
                    });
                });

                form.addEventListener('blur', function (e) {
                    if (self._isField(e.target)) self.checkField(e.target);
                }, true);

                // Clear a message as soon as the customer fixes the field, but
                // never introduce one mid-typing — that is hostile on a field
                // being filled in for the first time.
                form.addEventListener('input', function (e) {
                    if (self._isField(e.target) && self._errorEl(e.target)) {
                        self.checkField(e.target);
                    }
                });
                form.addEventListener('change', function (e) {
                    if (self._isField(e.target)) self.checkField(e.target);
                });
            },

            _isField: function (el) {
                if (!el || !el.name) return false;
                var t = (el.tagName || '').toUpperCase();
                if (t !== 'INPUT' && t !== 'SELECT' && t !== 'TEXTAREA') return false;
                return el.type !== 'hidden' && el.type !== 'submit' && el.type !== 'button';
            },

            /* Every validatable control in the form, in DOM order. */
            _fields: function () {
                var self = this;
                return Array.prototype.filter.call(
                    this.$el.querySelectorAll('input, select, textarea'),
                    function (el) { return self._isField(el); }
                );
            },

            /* Where a message for this field should live. Radios and checkboxes
               are placed after their group wrapper so the message does not land
               between an input and its label. */
            _anchor: function (field) {
                if (field.type === 'radio' || field.type === 'checkbox') {
                    return field.closest('[data-field]') || field.closest('label') || field;
                }
                return field.closest('[data-field]') || field;
            },

            _errorId: function (field) {
                return 'ccerr-' + (field.id || field.name).replace(/[^a-zA-Z0-9_-]/g, '_');
            },

            _errorEl: function (field) {
                return this.$el.querySelector('#' + CSS.escape(this._errorId(field)));
            },

            showError: function (field, message) {
                var id = this._errorId(field);
                var el = this._errorEl(field);
                if (!el) {
                    el = document.createElement('p');
                    el.id = id;
                    el.className = 'cc-field-error';
                    // Announced without stealing focus.
                    el.setAttribute('role', 'alert');
                    var anchor = this._anchor(field);
                    anchor.parentNode.insertBefore(el, anchor.nextSibling);
                }
                el.textContent = message;
                field.setAttribute('aria-invalid', 'true');
                field.setAttribute('aria-describedby', id);
                this.errors[field.name] = message;
            },

            clearError: function (field) {
                var el = this._errorEl(field);
                if (el && el.parentNode) el.parentNode.removeChild(el);
                field.removeAttribute('aria-invalid');
                if (field.getAttribute('aria-describedby') === this._errorId(field)) {
                    field.removeAttribute('aria-describedby');
                }
                delete this.errors[field.name];
            },

            /**
             * Synchronous rules only. Returns true when the field passes.
             * Remote checks are deliberately excluded so blur feedback is
             * instant; they run in checkRemote() / validateAll().
             */
            checkField: function (field) {
                if (field.disabled || field.type === 'hidden') { this.clearError(field); return true; }

                if (!field.checkValidity()) {
                    var rule = nativeRule(field);
                    this.showError(field, messageFor(field, rule));
                    return false;
                }

                // Cross-field equality: data-match points at another control.
                var matchSel = field.getAttribute('data-match');
                if (matchSel) {
                    var other = this.$el.querySelector(matchSel);
                    if (other && other.value !== field.value) {
                        this.showError(field, messageFor(field, 'match'));
                        return false;
                    }
                }

                this.clearError(field);
                return true;
            },

            /**
             * Remote uniqueness check. data-remote="email" | "newsletter".
             * Only runs on an otherwise-valid, non-empty field.
             */
            checkRemote: async function (field) {
                var kind = field.getAttribute('data-remote');
                if (!kind || !field.value || !field.checkValidity()) return true;

                var key = kind + '|' + field.value.toLowerCase();
                if (Object.prototype.hasOwnProperty.call(remoteCache, key)) {
                    if (remoteCache[key]) { return true; }
                    this.showError(field, messageFor(field, kind === 'newsletter' ? 'subscribed' : 'emailInUse'));
                    return false;
                }

                var body = new FormData();
                var url = (this.$el.getAttribute('action') || window.location.href).replace(/\?.*/, '');
                url += '?_g=ajax_email' + (kind === 'newsletter' ? '&source=newsletter' : '');
                body.append(kind === 'newsletter' ? 'subscribe' : 'email', field.value);
                var token = window.ccToken();
                if (token) body.append('token', token);

                try {
                    var text = await window.ccPost(url, body);
                    var data = JSON.parse(text);
                    // Bare true/false when config.csrf is off; object when on.
                    var free = (data === true) || (data && data.result === true);
                    remoteCache[key] = free;
                    if (free) return true;
                    this.showError(field, messageFor(field, kind === 'newsletter' ? 'subscribed' : 'emailInUse'));
                    return false;
                } catch (e) {
                    // Never block submission on a failed availability check —
                    // the server revalidates and is the authority.
                    return true;
                }
            },

            /** Full pass. Returns true when the form may be submitted. */
            validateAll: async function () {
                var fields = this._fields();
                var ok = true;
                var i;

                for (i = 0; i < fields.length; i++) {
                    if (!this.checkField(fields[i])) ok = false;
                }

                if (ok) {
                    var remotes = fields.filter(function (f) { return f.getAttribute('data-remote'); });
                    for (i = 0; i < remotes.length; i++) {
                        if (!(await this.checkRemote(remotes[i]))) ok = false;
                    }
                }

                if (!ok) {
                    var first = this.$el.querySelector('[aria-invalid="true"]');
                    if (first) {
                        first.focus({ preventScroll: true });
                        first.scrollIntoView({ block: 'center', behavior: 'smooth' });
                    }
                }
                return ok;
            }
        };

        form._ccValidator = api;
        api.init();
        return api;
    }

    function attachAll(root) {
        var scope = root || document;
        var forms = scope.querySelectorAll ? scope.querySelectorAll('form[data-cc-validate]') : [];
        Array.prototype.forEach.call(forms, createValidator);
    }

    window.ccAttachValidation = createValidator;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { attachAll(); });
    } else {
        attachAll();
    }

    /* Forms injected later (a plugin, or an AJAX-swapped fragment) are picked
       up without anyone having to remember to call us. */
    if (window.MutationObserver) {
        new MutationObserver(function (records) {
            for (var i = 0; i < records.length; i++) {
                var added = records[i].addedNodes;
                for (var j = 0; j < added.length; j++) {
                    var n = added[j];
                    if (n.nodeType !== 1) continue;
                    if (n.matches && n.matches('form[data-cc-validate]')) createValidator(n);
                    else attachAll(n);
                }
            }
        }).observe(document.documentElement, { childList: true, subtree: true });
    }
})();
