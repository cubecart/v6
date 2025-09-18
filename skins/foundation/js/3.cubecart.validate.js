;/* 3.cubecart.validate.js — refactored */
jQuery(function($){
  "use strict";

  // ---------- i18n cache ----------
  var i18n = {
    requiredField: $('#validate_field_required').text(),
    email: $('#validate_email').text(),
    emailExit: $('#validate_email_exit').text(),
    emailInUse: $('#validate_email_in_use').text(),
    emailMismatch: $('#validate_email_mismatch').text(),
    phone: $('#validate_phone').text(),
    mobile: $('#validate_mobile').text(),
    password: $('#validate_password').text(),
    passwordLen: $('#validate_password_length').text(),
    passwordLenMax: $('#validate_password_length_max').text(),
    passwordMismatch: $('#validate_password_mismatch').text(),
    termsAgree: $('#validate_terms_agree').text(),
    gatewayRequired: $('#validate_gateway_required').text(),
    emptyPassword: $('#empty_password').text(),
    // Newsletter
    alreadySub: $('#validate_already_subscribed').text(),
    alreadySubExit: $('#validate_already_subscribed_exit').text(),
    subscribeText: $('#validate_subscribe').text(),
    unsubscribeText: $('#validate_unsubscribe').text(),
    subscribeExitText: $('#validate_subscribe_exit').text(),
    unsubscribeExitText: $('#validate_unsubscribe_exit').text(),
    // Search
    searchRequired: $('.validate_search').first().text()
  };

  // ---------- Defaults & Custom rules ----------
  $.validator.setDefaults({
    errorElement: 'small',
    errorPlacement: function(error, element) {
      if (element.is(":radio,:checkbox")) {
        var rel = element.attr('rel');
        if (rel && $('#'+rel).length) {
          error.insertAfter('#' + rel);
        } else {
          element.removeClass("error");
          alert(error.text());
        }
      } else {
        error.insertAfter(element);
      }
    }
  });

  $.validator.addMethod("phone", function(val, el) {
    val = (val || '').replace(/\s+/g, '');
    return this.optional(el) || /^[0-9-+().]+$/.test(val);
  }, i18n.phone);

  $.extend($.validator.messages, { required: i18n.requiredField });

  // ---------- Small helpers ----------
  function makeEmailRemote(getValueSelector) {
    return {
      url: "?_g=ajax_email",
      type: "post",
      data: {
        username: function(){ return $(getValueSelector).val(); },
        token: function(){ return $("input[name=token]").val(); }
      },
      dataFilter: function(data) {
        try {
          var json = JSON.parse(data);
          return !!json.result;
        } catch(e) { return false; }
      }
    };
  }

  function applyValidation(selector, config) {
    var $f = $(selector);
    if ($f.length) { $f.validate(config); }
  }

  function msgRequiredEmail(useExit) {
    return { required: useExit ? i18n.emailExit : i18n.email, email: useExit ? i18n.emailExit : i18n.email };
  }

  // Newsletter validator builder (subscribe/unsubscribe flip)
  function newsletterValidator(formSel, opts) {
    var $email = $(opts.emailInput);
    if (!$email.length) return;

    applyValidation(formSel, {
      onkeyup: false,
      rules: {
        subscribe: {
          required: true,
          email: true,
          remote: {
            url: "?_g=ajax_email&source=newsletter",
            type: "post",
            data: {
              username: function(){ return $email.val(); },
              token: function(){ return $("input[name=token]").val(); }
            },
            dataFilter: function(data) {
              var ok = true;
              try {
                var json = JSON.parse(data);
                if (json.result) {
                  // not subscribed -> show "Subscribe", force_unsubscribe = 0
                  $(opts.button).val(opts.subscribeLabel);
                  $(opts.forceField).val('0');
                } else {
                  alert(opts.alreadyLabel);
                  $(opts.button).val(opts.unsubscribeLabel);
                  $(opts.forceField).val('1');
                }
              } catch(e) {}
              return ok; // always true to allow submit; UX matches original
            }
          }
        }
      },
      messages: {
        subscribe: {
          required: opts.requiredLabel,
          email: opts.requiredLabel,
          remote: opts.alreadyLabel
        }
      },
      submitHandler: function(form){ form.submit(); }
    });
  }

  // ---------- Shared rule/message snippets ----------
  var rules = {
    emailRequired: { required: true, email: true },
    phoneRequired: { required: true, phone: true },
    phoneOptional: { phone: true },
    passRequired: { required: true, minlength: 6, maxlength: 64 },
    passNew:      { minlength: 6, maxlength: 64 },
    equalTo: function(sel){ return { equalTo: sel }; }
  };

  var msgs = {
    emailRequired: { required: i18n.email, email: i18n.email },
    emailRequiredExit: { required: i18n.emailExit, email: i18n.emailExit },
    emailRemote: { required: i18n.email, email: i18n.email, remote: i18n.emailInUse },
    phoneRequired: { required: i18n.phone, phone: i18n.phone },
    phoneOptional: { phone: i18n.mobile },
    passRequired: { required: i18n.password, minlength: i18n.passwordLen, maxlength: i18n.passwordLenMax },
    passMaxOnly: { required: i18n.emptyPassword, maxlength: i18n.passwordLenMax },
    passMismatch: { required: i18n.passwordMismatch, equalTo: i18n.passwordMismatch },
    emailMismatch: { equalTo: i18n.emailMismatch },
    termsAgree: { required: i18n.termsAgree },
    gatewayRequired: { required: i18n.gatewayRequired }
  };

  // Keep parity with your other file that defines init_add_to_basket()
  if (typeof init_add_to_basket === 'function') { init_add_to_basket(); }

  // ---------- Recover password ----------
  applyValidation("#recover_password", {
    rules: { email: rules.emailRequired },
    messages: { email: msgRequiredEmail(false) }
  });

  // ---------- Review form ----------
  applyValidation("#review_form", {
    rules: {
      'review[name]':   { required: true },
      'review[review]': { required: true },
      'review[title]':  { required: true },
      'review[email]':  rules.emailRequired
    },
    messages: {
      'review[email]': msgRequiredEmail(false)
    }
  });

  // ---------- Contact form ----------
  applyValidation("#contact_form", {
    rules: {
      'contact[subject]': { required: true },
      'contact[dept]':    { required: true },
      'contact[enquiry]': { required: true },
      'contact[name]':    { required: true },
      'contact[email]':   rules.emailRequired,
      'contact[phone]':   rules.phoneOptional
    },
    messages: {
      'contact[email]': msgs.emailRequired,
      'contact[phone]': { phone: i18n.phone }
    }
  });

  // ---------- Gift certificate ----------
  applyValidation("#gc_form", {
    rules: { 'gc[email]': rules.emailRequired },
    messages: { 'gc[email]': msgs.emailRequired }
  });

  // ---------- Newsletter (exit) ----------
  newsletterValidator("#newsletter_exit", {
    emailInput: "#newsletter_email_exit",
    button: "#subscribe_button_exit",
    forceField: "#force_unsubscribe_exit",
    requiredLabel: i18n.emailExit,
    alreadyLabel: i18n.alreadySubExit,
    subscribeLabel: i18n.subscribeExitText,
    unsubscribeLabel: i18n.unsubscribeExitText
  });

  // ---------- Newsletter (header/box) ----------
  newsletterValidator("#newsletter_form, #newsletter_form_box", {
    emailInput: "#newsletter_email",
    button: "#subscribe_button",
    forceField: "#force_unsubscribe",
    requiredLabel: i18n.email,
    alreadyLabel: i18n.alreadySub,
    subscribeLabel: i18n.subscribeText,
    unsubscribeLabel: i18n.unsubscribeText
  });

  // ---------- Newsletter unsubscribe ----------
  applyValidation("#newsletter_form_unsubscribe", {
    onkeyup: false,
    rules: { unsubscribe: rules.emailRequired },
    messages: { unsubscribe: msgs.emailRequired }
  });

  // ---------- Checkout ----------
  applyValidation("#checkout_form", {
    rules: {
      username:           rules.emailRequired,
      'user[first_name]': { required: true },
      'user[last_name]':  { required: true },
      'user[email]':      $.extend({}, rules.emailRequired, { remote: makeEmailRemote("#user_email") }),
      'user[phone]':      rules.phoneRequired,
      'user[mobile]':     rules.phoneOptional,
      'billing[line1]':   { required: true },
      'billing[town]':    { required: true },
      'billing[country]': { required: true },
      'billing[state]':   { required: (window.validation_ini || {})['#state-list'] === true },
      'billing[postcode]':{ required: true },
      'delivery[line1]':  { required: true },
      'delivery[town]':   { required: true },
      'delivery[country]':{ required: true },
      'delivery[state]':  { required: (window.validation_ini || {})['#delivery_state'] === true },
      'delivery[postcode]':{ required: true },
      password:           rules.passRequired,
      passconf:           rules.equalTo("#reg_password"),
      emailconf:          rules.equalTo("#user_email"),
      terms_agree:        { required: true },
      gateway:            { required: true }
    },
    messages: {
      username: msgs.emailRequired,
      'user[email]': msgs.emailRemote,
      'user[phone]': msgs.phoneRequired,
      'user[mobile]': msgs.phoneOptional,
      password: { required: i18n.password },
      passconf: msgs.passMismatch,
      emailconf: msgs.emailMismatch,
      terms_agree: msgs.termsAgree,
      gateway: msgs.gatewayRequired
    },
    submitHandler: function(form){ form.submit(); }
  });

  // Toggle extra password length rules when switching register/login on checkout
  $("#checkout_form")
    .on("click", "#checkout_register", function(){
      $("#reg_password").rules("add", {
        minlength: 6, maxlength: 64,
        messages: { minlength: i18n.passwordLen, maxlength: i18n.passwordLenMax }
      });
    })
    .on("click", "#checkout_login", function(){
      $("#reg_password").rules("remove", "minlength maxlength");
    });

  // ---------- Address book ----------
  applyValidation("#addressbook_form", {
    rules: {
      first_name: { required: true },
      last_name:  { required: true },
      line1:      { required: true },
      town:       { required: true },
      country:    { required: true },
      state:      { required: (window.validation_ini || {})['#state-list'] === true },
      postcode:   { required: true }
    }
  });

  // ---------- Order lookup ----------
  applyValidation("#lookup_order", {
    rules: {
      cart_order_id: { required: true },
      email: rules.emailRequired
    },
    messages: { email: msgs.emailRequired }
  });

  // ---------- Search (simple & advanced) ----------
  applyValidation(".search_form", {
    rules: { 'search[keywords]': { required: true } },
    messages: { 'search[keywords]': { required: i18n.searchRequired } }
  });
  applyValidation("#advanced_search_form", {
    rules: { 'search[keywords]': { required: true } },
    messages: { 'search[keywords]': { required: i18n.searchRequired } }
  });

  // ---------- Login ----------
  applyValidation("#login_form", {
    rules: {
      username: rules.emailRequired,
      password: { required: true, maxlength: 64 }
    },
    messages: {
      username: msgs.emailRequired,
      password: msgs.passMaxOnly
    }
  });

  // ---------- Password recovery (reset page) ----------
  applyValidation("#password_recovery", {
    rules: {
      email: rules.emailRequired,
      validate: { required: true },
      'password[password]': rules.passRequired,
      'password[passconf]': rules.equalTo("#password")
    },
    messages: {
      email: msgs.emailRequired,
      'password[password]': msgs.passRequired,
      'password[passconf]': msgs.passMismatch
    }
  });

  // ---------- Registration ----------
  applyValidation("#registration_form", {
    rules: {
      first_name: { required: true },
      last_name:  { required: true },
      email:      $.extend({}, rules.emailRequired, { remote: makeEmailRemote("#email") }),
      emailconf:  rules.equalTo("#email"),
      phone:      rules.phoneRequired,
      mobile:     rules.phoneOptional,
      password:   rules.passRequired,
      passconf:   rules.equalTo("#password"),
      terms_agree:{ required: true }
    },
    messages: {
      first_name: { required: $('#validate_firstname').text() },
      last_name:  { required: $('#validate_lastname').text()  },
      email:      msgs.emailRemote,
      emailconf:  msgs.emailMismatch,
      phone:      msgs.phoneRequired,
      mobile:     { phone: i18n.mobile },
      password:   msgs.passRequired,
      passconf:   msgs.passMismatch,
      terms_agree: msgs.termsAgree
    },
    submitHandler: function(form){ form.submit(); }
  });

  // ---------- Profile ----------
  applyValidation("#profile_form", {
    rules: {
      first_name: { required: true },
      last_name:  { required: true },
      email:      rules.emailRequired,
      phone:      rules.phoneRequired,
      mobile:     rules.phoneOptional,
      passnew:    rules.passNew,
      passconf:   rules.equalTo("#passnew"),
      emailconf:  rules.equalTo("#acc_email")
    },
    messages: {
      first_name: { required: $('#validate_firstname').text() },
      last_name:  { required: $('#validate_lastname').text()  },
      email:      msgs.emailRequired,
      phone:      msgs.phoneRequired,
      mobile:     { phone: i18n.mobile },
      passnew:    { minlength: i18n.passwordLen, maxlength: i18n.passwordLenMax },
      passconf:   msgs.passMismatch,
      emailconf:  msgs.emailMismatch
    }
  });

  // ---------- Reset buttons clear validation ----------
  $(document).on('click', 'input:reset', function(){
    var v = $(this).closest('form').validate();
    if (v) v.resetForm();
  });
});