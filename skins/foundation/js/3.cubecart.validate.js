;var validation_ini = {};

(function ($) {
  //
  // Cache phone validation message once
  //
  const phoneMsg = $('#validate_phone').text();

  //
  // Validator defaults
  //
  $.validator.setDefaults({
    errorElement: 'small',
    errorPlacement: function (error, element) {
      if (element.is(":radio") || element.is(":checkbox")) {
        const errorLocation = element.attr('rel');
        if (errorLocation) {
          const $errorTarget = $('#' + errorLocation);
          if ($errorTarget.length) {
            error.insertAfter($errorTarget);
          } else {
            element.removeClass("error");
            alert(error.text());
          }
        } else {
          error.insertAfter(element);
        }
      } else {
        error.insertAfter(element);
      }
    }
  });

  //
  // Extra methods
  //
  $.validator.addMethod("phone", function (phone, element) {
    phone = phone.replace(/\s+/g, "");
    return this.optional(element) || /^[0-9-+().]+$/.test(phone);
  }, phoneMsg);

  $.extend($.validator.messages, {
    required: $('#validate_field_required').text()
  });

  // Called by other code; keep for compatibility
  if (typeof init_add_to_basket === 'function') init_add_to_basket();

  //
  // Cache all message text once
  //
  const msg = {
    email: $('#validate_email').text(),
    emailExit: $('#validate_email_exit').text(),
    phone: phoneMsg,
    mobile: $('#validate_mobile').text(),
    password: $('#validate_password').text(),
    passwordLength: $('#validate_password_length').text(),
    passwordMax: $('#validate_password_length_max').text(),
    passwordMismatch: $('#validate_password_mismatch').text(),
    emailMismatch: $('#validate_email_mismatch').text(),
    terms: $('#validate_terms_agree').text(),
    shipping: $('#validate_shipping_required').text(),
    gateway: $('#validate_gateway_required').text(),
    emailInUse: $('#validate_email_in_use').text(),
    firstname: $('#validate_firstname').text(),
    lastname: $('#validate_lastname').text(),
    search: $('.validate_search').first().text(),
    emptyPassword: $('#empty_password').text(),
    subscribe: $('#validate_subscribe').text(),
    unsubscribe: $('#validate_unsubscribe').text(),
    alreadySubscribed: $('#validate_already_subscribed').text(),
    subscribeExit: $('#validate_subscribe_exit').text(),
    unsubscribeExit: $('#validate_unsubscribe_exit').text(),
    alreadySubscribedExit: $('#validate_already_subscribed_exit').text()
  };

  //
  // Base templates
  //
  const baseEmailRule = { required: true, email: true };
  const baseEmailMsg  = { required: msg.email, email: msg.email };

  //
  // All “simple” forms (no custom remote dataFilter)
  //
  const forms = {
    "#recover_password": {
      rules: { email: baseEmailRule },
      messages: { email: baseEmailMsg }
    },

    "#review_form": {
      rules: {
        'review[name]': { required: true },
        'review[review]': { required: true },
        'review[title]': { required: true },
        'review[email]': baseEmailRule
      },
      messages: { 'review[email]': baseEmailMsg }
    },

    "#contact_form": {
      rules: {
        'contact[subject]': { required: true },
        'contact[dept]': { required: true },
        'contact[enquiry]': { required: true },
        'contact[name]': { required: true },
        'contact[email]': baseEmailRule,
        'contact[phone]': { phone: true }
      },
      messages: {
        'contact[email]': baseEmailMsg,
        'contact[phone]': { phone: msg.phone }
      }
    },

    "#gc_form": {
      rules: { 'gc[email]': baseEmailRule },
      messages: { 'gc[email]': baseEmailMsg }
    },

    "#newsletter_form_unsubscribe": {
      onkeyup: false,
      rules: { unsubscribe: baseEmailRule },
      messages: { unsubscribe: baseEmailMsg }
    },

    "#lookup_order": {
      rules: {
        cart_order_id: { required: true },
        email: baseEmailRule
      },
      messages: { email: baseEmailMsg }
    },

    ".search_form": {
      rules: { 'search[keywords]': { required: true } },
      messages: { 'search[keywords]': { required: msg.search } }
    },

    "#advanced_search_form": {
      rules: { 'search[keywords]': { required: true } },
      messages: { 'search[keywords]': { required: msg.search } }
    },

    "#login_form": {
      rules: {
        username: baseEmailRule,
        password: { required: true, maxlength: 64 }
      },
      messages: {
        username: baseEmailMsg,
        password: { required: msg.emptyPassword, maxlength: msg.passwordMax }
      }
    },

    "#password_recovery": {
      rules: {
        email: baseEmailRule,
        validate: { required: true },
        'password[password]': { required: true, minlength: 6, maxlength: 64 },
        'password[passconf]': { equalTo: "#password" }
      },
      messages: {
        email: baseEmailMsg,
        'password[password]': {
          required: msg.password,
          minlength: msg.passwordLength,
          maxlength: msg.passwordMax
        },
        'password[passconf]': {
          required: msg.passwordMismatch,
          equalTo: msg.passwordMismatch
        }
      }
    },

    "#addressbook_form": {
      rules: {
        first_name: { required: true },
        last_name:  { required: true },
        line1:      { required: true },
        town:       { required: true },
        country:    { required: true },
        state:      { required: validation_ini['#state-list'] },
        postcode:   { required: true }
      }
    },

    "#profile_form": {
      rules: {
        first_name: { required: true },
        last_name:  { required: true },
        email:      baseEmailRule,
        phone:      { required: true, phone: true },
        mobile:     { phone: true },
        passnew:    { minlength: 6, maxlength: 64 },
        passconf:   { equalTo: "#passnew" },
        emailconf:  { equalTo: "#acc_email" }
      },
      messages: {
        first_name: { required: msg.firstname },
        last_name:  { required: msg.lastname },
        email:      baseEmailMsg,
        phone:      { required: msg.phone, phone: msg.phone },
        mobile:     { phone: msg.mobile },
        passnew:    { minlength: msg.passwordLength, maxlength: msg.passwordMax },
        passconf:   { equalTo: msg.passwordMismatch },
        emailconf:  { equalTo: msg.emailMismatch }
      }
    }
  };

  // Init only those that exist
  Object.keys(forms).forEach(selector => {
    const $el = $(selector);
    if ($el.length) $el.validate(forms[selector]);
  });

  //
  // Complex forms (custom remote + dataFilter)
  //

  // Checkout
  if ($('#checkout_form').length) {
    // Cache selectors for remote validation
    const $userEmail = $("#user_email");
    const $checkoutToken = $("#checkout_form input[name=token]");

    $('#checkout_form').validate({
      rules: {
        username: { required: true, email: true },
        shipping: { required: true },
        'user[first_name]': { required: true },
        'user[last_name]':  { required: true },
        'user[email]': {
          required: true,
          email: true,
          remote: {
            url: "?_g=ajax_email",
            type: "post",
            data: {
              username: () => $userEmail.val(),
              token:    () => $checkoutToken.val()
            },
            dataFilter: d => JSON.parse(d).result
          }
        },
        'user[phone]':   { required: true, phone: true },
        'user[mobile]':  { phone: true },
        'billing[line1]':   { required: true },
        'billing[town]':    { required: true },
        'billing[country]': { required: true },
        'billing[state]':   { required: validation_ini['#state-list'] },
        'billing[postcode]':{ required: true },
        'delivery[line1]':  { required: true },
        'delivery[town]':   { required: true },
        'delivery[country]':{ required: true },
        'delivery[state]':  { required: validation_ini['#delivery_state'] },
        'delivery[postcode]': { required: true },
        password: { required: true, minlength: 6, maxlength: 64 },
        passconf: { equalTo: "#reg_password" },
        emailconf:{ equalTo: "#user_email" },
        terms_agree: { required: true },
        gateway: { required: true }
      },
      messages: {
        username: { required: msg.email, email: msg.email },
        'user[email]': { required: msg.email, email: msg.email, remote: msg.emailInUse },
        'user[phone]': { required: msg.phone, phone: msg.phone },
        'user[mobile]': { phone: msg.mobile },
        password: { required: msg.password },
        passconf: { required: msg.passwordMismatch, equalTo: msg.passwordMismatch },
        emailconf: { equalTo: msg.emailMismatch },
        terms_agree: { required: msg.terms },
        gateway: { required: msg.gateway },
        shipping: { required: msg.shipping }
      },
      submitHandler: function (form) { form.submit(); }
    });

    // Checkout dynamic rules for register/login toggle
    $("#checkout_form").on("click", '#checkout_register', function () {
      $("#reg_password").rules("add", {
        minlength: 6,
        maxlength: 64,
        messages: { minlength: msg.passwordLength, maxlength: msg.passwordMax }
      });
    });

    $("#checkout_form").on("click", '#checkout_login', function () {
      $("#reg_password").rules("remove", "minlength", "maxlength");
    });
  }

  // Registration
  if ($('#registration_form').length) {
    // Cache selectors for remote validation
    const $regEmail = $("#email");
    const $regToken = $("#registration_form input[name=token]");

    $('#registration_form').validate({
      rules: {
        first_name: { required: true },
        last_name:  { required: true },
        email: {
          required: true, email: true,
          remote: {
            url: "?_g=ajax_email",
            type: "post",
            data: {
              username: () => $regEmail.val(),
              token:    () => $regToken.val()
            },
            dataFilter: d => JSON.parse(d).result
          }
        },
        emailconf: { equalTo: "#email" },
        phone: { required: true, phone: true },
        mobile: { phone: true },
        password: { required: true, minlength: 6, maxlength: 64 },
        passconf: { equalTo: "#password" },
        terms_agree: { required: true }
      },
      messages: {
        first_name: { required: msg.firstname },
        last_name:  { required: msg.lastname },
        email: { required: msg.email, email: msg.email, remote: msg.emailInUse },
        emailconf: { equalTo: msg.emailMismatch },
        phone: { required: msg.phone, phone: msg.phone },
        mobile:{ phone: msg.mobile },
        password: { required: msg.password, minlength: msg.passwordLength, maxlength: msg.passwordMax },
        passconf: { required: msg.passwordMismatch, equalTo: msg.passwordMismatch },
        terms_agree: { required: msg.terms }
      },
      submitHandler: function (form) { form.submit(); }
    });
  }

  //
  // Newsletter forms (two behaviours) - Factory function to reduce duplication
  //
  function createNewsletterValidator(config) {
    const {
      emailSelector,
      buttonSelector,
      forceUnsubscribeSelector,
      messages,
      toggleClasses = false
    } = config;

    // Cache selectors
    const $email = $(emailSelector);
    const $button = $(buttonSelector);
    const $forceUnsubscribe = $(forceUnsubscribeSelector);
    const $token = $("input[name=token]");

    return {
      onkeyup: false,
      rules: {
        subscribe: {
          required: true,
          email: true,
          remote: {
            url: "?_g=ajax_email&source=newsletter",
            type: "post",
            data: {
              username: () => $email.val(),
              token: () => $token.val()
            },
            dataFilter: function (data) {
              const json = JSON.parse(data);
              if (json.result) {
                $button.val(messages.subscribe);
                $forceUnsubscribe.val('0');
                if (toggleClasses) {
                  $button.removeClass('alert');
                  $email.removeClass('alert');
                }
              } else {
                alert(messages.alreadySubscribed);
                $button.val(messages.unsubscribe);
                $forceUnsubscribe.val('1');
                if (toggleClasses) {
                  $button.addClass('alert');
                  $email.addClass('alert');
                }
              }
              return true;
            }
          }
        }
      },
      messages: {
        subscribe: {
          required: messages.emailRequired,
          email: messages.emailRequired,
          remote: messages.alreadySubscribed
        }
      },
      submitHandler: function (form) { form.submit(); }
    };
  }

  // Standard newsletter (form + box): toggles classes on button/email + force_unsubscribe
  if ($("#newsletter_form, #newsletter_form_box").length) {
    $("#newsletter_form, #newsletter_form_box").validate(
      createNewsletterValidator({
        emailSelector: "#newsletter_email",
        buttonSelector: "#subscribe_button",
        forceUnsubscribeSelector: "#force_unsubscribe",
        messages: {
          subscribe: msg.subscribe,
          unsubscribe: msg.unsubscribe,
          alreadySubscribed: msg.alreadySubscribed,
          emailRequired: msg.email
        },
        toggleClasses: true
      })
    );
  }

  // Exit newsletter: does NOT toggle classes; only text + hidden field
  if ($("#newsletter_exit").length) {
    $("#newsletter_exit").validate(
      createNewsletterValidator({
        emailSelector: "#newsletter_email_exit",
        buttonSelector: "#subscribe_button_exit",
        forceUnsubscribeSelector: "#force_unsubscribe_exit",
        messages: {
          subscribe: msg.subscribeExit,
          unsubscribe: msg.unsubscribeExit,
          alreadySubscribed: msg.alreadySubscribedExit,
          emailRequired: msg.emailExit
        },
        toggleClasses: false
      })
    );
  }

  //
  // Reset buttons clear validator state - Use event delegation
  //
  $(document).on('click', 'input:reset', function () {
    const $form = $(this).closest('form');
    const validator = $form.data('validator');
    if (validator) {
      validator.resetForm();
    }
  });

})(jQuery);