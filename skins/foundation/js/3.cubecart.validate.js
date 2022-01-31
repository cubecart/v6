;
jQuery(document).ready(function() {
    $.validator.setDefaults({
        errorElement: 'small',
        errorPlacement: function(error, element) {
            if (element.is(":radio") || element.is(":checkbox")) {
                var errorLocation = element.attr('rel');
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
    $.validator.addMethod("phone", function(phone, element) {
        phone = phone.replace(/\s+/g, "");
        return this.optional(element) || phone.match(/^[0-9-+().]+$/);
    }, $('#validate_phone').text());
    $.extend(jQuery.validator.messages, {
        required: $('#validate_field_required').text()
    });

    init_add_to_basket();

    $("#recover_password").validate({
        rules: {
            'email': {
                required: true,
                email: true
            }
        },
        messages: {
            'email': {
                required: $('#validate_email').text(),
                review: $('#validate_email').text()
            }
        }
    });
    $("#review_form").validate({
        rules: {
            'review[name]': {
                required: true
            },
            'review[review]': {
                required: true
            },
            'review[title]': {
                required: true
            },
            'review[email]': {
                required: true,
                email: true
            }
        },
        messages: {
            'review[email]': {
                required: $('#validate_email').text(),
                review: $('#validate_email').text()
            }
        }
    });
    $("#contact_form").validate({
        rules: {
            'contact[subject]': {
                required: true
            },
            'contact[dept]': {
                required: true
            },
            'contact[enquiry]': {
                required: true
            },
            'contact[name]': {
                required: true
            },
            'contact[email]': {
                required: true,
                email: true
            },
            'contact[phone]': {
                phone: true
            }
        },
        messages: {
            'contact[email]': {
                required: $('#validate_email').text(),
                email: $('#validate_email').text()
            },
            'contact[phone]': {
                phone: $('#validate_phone').text()
            }
        }
    });
    $("#gc_form").validate({
        rules: {
            'gc[email]': {
                required: true,
                email: true
            }
        },
        messages: {
            'gc[email]': {
                required: $('#validate_email').text(),
                email: $('#validate_email').text()
            }
        }
    });
    $("#newsletter_exit").validate({
        onkeyup: false,
        rules: {
            subscribe: {
                required: true,
                email: true,
                remote: {
                    url: "?_g=ajax_email&source=newsletter",
                    type: "post",
                    data: {
                        username: function() {
                            return $("#newsletter_email_exit").val();
                        },
                        token: function() {
                            return $("input[name=token]").val();
                        }
                    },
                    dataFilter: function(data) {
                        var json = JSON.parse(data);
                        if(json.result) {
                            $("#subscribe_button_exit").val($('#validate_subscribe_exit').text());
                            $("#force_unsubscribe_exit").val('0');
                        } else {
                            alert($('#validate_already_subscribed_exit').text());
                            $("#subscribe_button_exit").val($('#validate_unsubscribe_exit').text());
                            $("#force_unsubscribe_exit").val('1');
                        }
                        return true;
                    }
                }
            },
        },
        messages: {
            subscribe: {
                required: $('#validate_email_exit').text(),
                email: $('#validate_email_exit').text(),
                remote: $('#validate_already_subscribed_exit').text()
            },
        },
        submitHandler: function(form) {
            form.submit();
        }
    });
    $("#newsletter_form, #newsletter_form_box").validate({
        onkeyup: false,
        rules: {
            subscribe: {
                required: true,
                email: true,
                remote: {
                    url: "?_g=ajax_email&source=newsletter",
                    type: "post",
                    data: {
                        username: function() {
                            return $("#newsletter_email").val();
                        },
                        token: function() {
                            return $("input[name=token]").val();
                        }
                    },
                    dataFilter: function(data) {
                        var json = JSON.parse(data);
                        if(json.result) {
                            $("#subscribe_button").val($('#validate_subscribe').text());
                            $("#force_unsubscribe").val('0');
                        } else {
                            alert($('#validate_already_subscribed').text());
                            $("#subscribe_button").val($('#validate_unsubscribe').text());
                            $("#force_unsubscribe").val('1');
                        }
                        return true;
                    }
                }
            },
        },
        messages: {
            subscribe: {
                required: $('#validate_email').text(),
                email: $('#validate_email').text(),
                remote: $('#validate_already_subscribed').text()
            },
        },
        submitHandler: function(form) {
            form.submit();
        }
    });
    $("#newsletter_form_unsubscribe").validate({
        onkeyup: false,
        rules: {
            unsubscribe: {
                required: true,
                email: true
            },
        },
        messages: {
            unsubscribe: {
                required: $('#validate_email').text(),
                email: $('#validate_email').text()
            },
        }
    });

    $("#checkout_form").validate({
        rules: {
            username: {
                required: true,
                email: true
            },
            'user[first_name]': {
                required: true
            },
            'user[last_name]': {
                required: true
            },
            'user[email]': {
                required: true,
                email: true,
                remote: {
                    url: "?_g=ajax_email",
                    type: "post",
                    data: {
                        username: function() {
                            return $("#user_email").val();
                        },
                        token: function() {
                            return $("input[name=token]").val();
                        }
                    },
                    dataFilter: function(data) {
                        var json = JSON.parse(data);
                        return json.result;
                    }
                }
            },
            'user[phone]': {
                required: true,
                phone: true
            },
            'user[mobile]': {
                phone: true
            },
            'billing[line1]': {
                required: true
            },
            'billing[town]': {
                required: true
            },
            'billing[country]': {
                required: true
            },
            'billing[state]': {
                required: validation_ini['#state-list']
            },
            'billing[postcode]': {
                required: true
            },
            'delivery[line1]': {
                required: true
            },
            'delivery[town]': {
                required: true
            },
            'delivery[country]': {
                required: true
            },
            'delivery[state]': {
                required: validation_ini['#delivery_state']
            },
            'delivery[postcode]': {
                required: true
            },
            password: {
                required: true,
                minlength: 6,
                maxlength: 64
            },
            passconf: {
                equalTo: "#reg_password"
            },
            terms_agree: {
                required: true
            },
            gateway: {
                required: true
            }
        },
        messages: {
            username: {
                required: $('#validate_email').text(),
                email: $('#validate_email').text()
            },
            'user[email]': {
                required: $('#validate_email').text(),
                email: $('#validate_email').text(),
                remote: $('#validate_email_in_use').text()
            },
            'user[phone]': {
                required: $('#validate_phone').text(),
                phone: $('#validate_phone').text()
            },
            'user[mobile]': {
                phone: $('#validate_mobile').text()
            },
            password: {
                required: $('#validate_password').text()
            },
            passconf: {
                required: $('#validate_password_mismatch').text(),
                equalTo: $('#validate_password_mismatch').text()
            },
            terms_agree: {
                required: $('#validate_terms_agree').text()
            },
            gateway: {
                required: $('#validate_gateway_required').text()
            }
        },
        submitHandler: function(form) {
            form.submit();
        }
    });

    $("#checkout_form").on("click", '#checkout_register', function() {
        $("#reg_password").rules("add", {
            minlength: 6,
            maxlength: 64,
            messages: {
                minlength: $('#validate_password_length').text(),
                maxlength: $('#validate_password_length_max').text()
            }
        });
    });

    $("#checkout_form").on("click", '#checkout_login', function() {
        $("#reg_password").rules("remove","minlength","maxlength");
    });

    $("#addressbook_form").validate({
        rules: {
            first_name: {
                required: true
            },
            last_name: {
                required: true
            },
            line1: {
                required: true
            },
            town: {
                required: true
            },
            country: {
                required: true
            },
            state: {
                required: validation_ini['#state-list']
            },
            postcode: {
                required: true
            }
        }
    });
    $("#lookup_order").validate({
        rules: {
            cart_order_id: {
                required: true
            },
            email: {
                required: true,
                email: true
            }
        },
        messages: {
            email: {
                required: $('#validate_email').text(),
                email: $('#validate_email').text()
            },
        }
    });
    $(".search_form").validate({
        rules: {
            'search[keywords]': {
                required: true
            }
        },
        messages: {
            'search[keywords]': {
                required: $('.validate_search').first().text()
            }
        }
    });
    $("#advanced_search_form").validate({
        rules: {
            'search[keywords]': {
                required: true
            }
        },
        messages: {
            'search[keywords]': {
                required: $('.validate_search').first().text()
            }
        }
    });
    $("#login_form").validate({
        rules: {
            username: {
                required: true,
                email: true
            },
            password: {
                required: true,
                maxlength: 64
            }
        },
        messages: {
            username: {
                required: $('#validate_email').text(),
                email: $('#validate_email').text()
            },
            password: {
                required: $('#empty_password').text(),
                maxlength: $('#validate_password_length_max').text()
            }
        }
    });
    $("#password_recovery").validate({
        rules: {
            email: {
                required: true,
                email: true
            },
            validate: {
                required: true
            },
            'password[password]': {
                required: true,
                minlength: 6,
                maxlength: 64
            },
            'password[passconf]': {
                equalTo: "#password"
            }
        },
        messages: {
            email: {
                required: $('#validate_email').text(),
                email: $('#validate_email').text()
            },
            'password[password]': {
                required: $('#validate_password').text(),
                minlength: $('#validate_password_length').text(),
                maxlength: $('#validate_password_length_max').text()
            },
            'password[passconf]': {
                required: $('#validate_password_mismatch').text(),
                equalTo: $('#validate_password_mismatch').text()
            }
        }
    });
    $("#registration_form").validate({
        rules: {
            first_name: {
                required: true
            },
            last_name: {
                required: true
            },
            email: {
                required: true,
                email: true,
                remote: {
                    url: "?_g=ajax_email",
                    type: "post",
                    data: {
                        username: function() {
                            return $("#email").val();
                        },
                        token: function() {
                            return $("input[name=token]").val();
                        }
                    },
                    dataFilter: function(data) {
                        var json = JSON.parse(data);
                        return json.result;
                    }
                }
            },
            phone: {
                required: true,
                phone: true
            },
            mobile: {
                required: false,
                phone: true
            },
            password: {
                required: true,
                minlength: 6,
                maxlength: 64
            },
            passconf: {
                equalTo: "#password"
            },
            terms_agree: {
                required: true
            }
        },
        messages: {
            first_name: {
                required: $('#validate_firstname').text()
            },
            last_name: {
                required: $('#validate_lastname').text()
            },
            email: {
                required: $('#validate_email').text(),
                email: $('#validate_email').text(),
                remote: $('#validate_email_in_use').text()
            },
            phone: {
                required: $('#validate_phone').text(),
                phone: $('#validate_phone').text()
            },
            mobile: {
                phone: $('#validate_mobile').text()
            },
            password: {
                required: $('#validate_password').text(),
                minlength: $('#validate_password_length').text(),
                maxlength: $('#validate_password_length_max').text()
            },
            passconf: {
                required: $('#validate_password_mismatch').text(),
                equalTo: $('#validate_password_mismatch').text()
            },
            terms_agree: {
                required: $('#validate_terms_agree').text()
            }
        },
        submitHandler: function(form) {
            form.submit();
        }
    });
    $("#profile_form").validate({
        rules: {
            first_name: {
                required: true
            },
            last_name: {
                required: true
            },
            email: {
                required: true,
                email: true
            },
            phone: {
                required: true,
                phone: true
            },
            mobile: {
                required: false,
                phone: true
            },
            passnew: {
                minlength: 6,
                maxlength: 64,
            },
            passconf: {
                equalTo: "#passnew",
            }
        },
        messages: {
            first_name: {
                required: $('#validate_firstname').text()
            },
            last_name: {
                required: $('#validate_lastname').text()
            },
            email: {
                required: $('#validate_email').text(),
                email: $('#validate_email').text()
            },
            phone: {
                required: $('#validate_phone').text(),
                phone: $('#validate_phone').text()
            },
            mobile: {
                phone: $('#validate_mobile').text()
            },
            passnew: {
                minlength: $('#validate_password_length').text(),
                maxlength: $('#validate_password_length_max').text()
            },
            passconf: {
                equalTo: $('#validate_password_mismatch').text()
            }
        }
    }); /* Reset Form */
    $('input:reset').click(function() {
        $(this).parents('form:first').validate().resetForm();
    });
});