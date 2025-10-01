; var validation_ini = {};

(function ($) {
    //
    // Validator defaults
    //
    $.validator.setDefaults({
        errorElement: 'small',
        errorPlacement: function (error, element) {
            if (element.is(":radio") || element.is(":checkbox")) {
                const errorLocation = element.attr('rel');
                if ($('#' + errorLocation).length) {
                    error.insertAfter('#' + errorLocation);
                } else {
                    element.removeClass("error");
                    alert(error.text());
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
    }, $('#validate_phone').text());

    $.extend($.validator.messages, {
        required: $('#validate_field_required').text()
    });

    init_add_to_basket();

    //
    // Shared messages (cached once)
    //
    const msg = {
        email: $('#validate_email').text(),
        emailExit: $('#validate_email_exit').text(),
        phone: $('#validate_phone').text(),
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
    // Base rule/message templates
    //
    const baseEmailRule = { required: true, email: true };
    const baseEmailMsg = { required: msg.email, email: msg.email };

    //
    // Generic form configs
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
            rules: { cart_order_id: { required: true }, email: baseEmailRule },
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
                last_name: { required: true },
                line1: { required: true },
                town: { required: true },
                country: { required: true },
                state: { required: validation_ini['#state-list'] },
                postcode: { required: true }
            }
        },
        "#profile_form": {
            rules: {
                first_name: { required: true },
                last_name: { required: true },
                email: baseEmailRule,
                phone: { required: true, phone: true },
                mobile: { phone: true },
                passnew: { minlength: 6, maxlength: 64 },
                passconf: { equalTo: "#passnew" },
                emailconf: { equalTo: "#acc_email" }
            },
            messages: {
                first_name: { required: msg.firstname },
                last_name: { required: msg.lastname },
                email: baseEmailMsg,
                phone: { required: msg.phone, phone: msg.phone },
                mobile: { phone: msg.mobile },
                passnew: { minlength: msg.passwordLength, maxlength: msg.passwordMax },
                passconf: { equalTo: msg.passwordMismatch },
                emailconf: { equalTo: msg.emailMismatch }
            }
        }
    };

    //
    // Init forms that exist
    //
    Object.keys(forms).forEach(selector => {
        const $form = $(selector);
        if ($form.length) $form.validate(forms[selector]);
    });

    //
    // Newsletter forms (custom behaviour)
    //
    function initNewsletter(selector, emailInput, button, unsubscribeInput, labels) {
        $(selector).validate({
            onkeyup: false,
            rules: { subscribe: baseEmailRule },
            messages: { subscribe: { required: labels.email, email: labels.email, remote: labels.already } },
            submitHandler: form => form.submit(),
            remote: {
                url: "?_g=ajax_email&source=newsletter",
                type: "post",
                data: {
                    username: () => $(emailInput).val(),
                    token: () => $("input[name=token]").val()
                },
                dataFilter: function (data) {
                    const json = JSON.parse(data);
                    if (json.result) {
                        $(button).val(labels.subscribe).removeClass('alert');
                        $(unsubscribeInput).val('0');
                        $(emailInput).removeClass('alert');
                    } else {
                        alert(labels.already);
                        $(button).val(labels.unsubscribe).addClass('alert');
                        $(unsubscribeInput).val('1');
                        $(emailInput).addClass('alert');
                    }
                    return true;
                }
            }
        });
    }

    initNewsletter(
        "#newsletter_form, #newsletter_form_box",
        "#newsletter_email",
        "#subscribe_button",
        "#force_unsubscribe",
        { subscribe: msg.subscribe, unsubscribe: msg.unsubscribe, already: msg.alreadySubscribed, email: msg.email }
    );

    initNewsletter(
        "#newsletter_exit",
        "#newsletter_email_exit",
        "#subscribe_button_exit",
        "#force_unsubscribe_exit",
        { subscribe: msg.subscribeExit, unsubscribe: msg.unsubscribeExit, already: msg.alreadySubscribedExit, email: msg.emailExit }
    );

    //
    // Checkout form dynamic rules
    //
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

    //
    // Reset button
    //
    $('input:reset').on('click', function () {
        $(this).closest('form').validate().resetForm();
    });

})(jQuery);