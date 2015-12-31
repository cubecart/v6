;
jQuery(document).ready(function() {

    var window_loc_hash = window.location.hash;

    if($('.gateway_wrapper .colorbox').length) {
        var colorbox = $('.colorbox');
        var href = colorbox.attr('href');
        var title = colorbox.attr('title');

        colorbox.attr('href', '#').attr('data-reveal-id', 'colorbox');

        $('.colorbox').after(
            $('<div>').attr('id', 'colorbox').addClass('reveal-modal').addClass('tiny').attr('data-reveal','').attr('aria-labelledby',title).attr('aria-hidden','true').attr('role', 'dialog').html('<h3>'+title+'</h3><img src="'+href+'"><a class="close-reveal-modal">&#215;</a>')
        );
        $(".gateway_wrapper .colorbox").click(function() {
            $('#colorbox').foundation('reveal', 'open');
        });
    }
    $("#eu_cookie_button").click(function() {
        $('#eu_cookie_dialogue').slideUp();
        $.cookie('accept_cookies', 1, {
            expires: 730
        });
        return false;
    });

    $(".autosubmit select").not('.nosubmit').change(function() {
        $(this).parents(".autosubmit").submit();
    });
    $('i.fa-submit').each(function() {
        $(this).parents('form').submit();
    });
    $(".category-nav li").each(function(index) {
        if (!$(this).has("ul").length) {
            $(this).removeClass('has-dropdown');
        }
    });
    $(".review_hide").click(function() {
        $('#review_read').show();
        $('#review_write').slideUp();
        return false;
    });
    $(".review_show").click(function() {
        $('#review_read').hide();
        $('#review_write').slideDown();
        return false;
    });
    $(".show-small-search").click(function() { 
        $('#small-search').slideToggle();
        return;
    });
    $(".hide_skin_selector").click(function(e) {
        e.preventDefault();
        $('.skin_selector').fadeOut();
        return;
    });
    $(".image-gallery").hover(function() {
        var src = $(this).attr("data-image-swap");
        $('#img-preview').attr("src", src);
    });
    $('.open-clearing').on('click', function(e) {
        e.preventDefault();
        $('[data-clearing] li img').eq($(this).data('thumb-index')).trigger('click');
      });
    $('input[type=radio].rating').rating({
        required: true
    });
    $('body').on('click', '#basket-summary', function() {
        mini_basket_action();
    });
    $('a.quan').click(function() {
        var rel = $(this).attr('rel');
        var sign;
        if ($(this).hasClass('add')) {
            sign = '+';
        } else if ($(this).hasClass('subtract')) {
            sign = '-';
        } else {
            alert('No \'add\' or \'subtract\' class defined.');
        }
        return update_quantity(rel, sign);
    });
    $('#checkout_proceed').click(function() {
        $('<input>').attr({
            type: 'hidden',
            name: 'proceed',
            value: '1'
        }).appendTo('form#checkout_form');
    });
    /* Initial setup of country/state menu */
    $('select#country-list, select.country-list').each(function() {
        if (typeof(county_list) == 'object') {
            var counties = county_list[$(this).val()];
            var target = ($(this).attr('rel') && $(this).attr('id') != 'country-list') ? '#' + $(this).attr('rel') : '#state-list';
            if (typeof(counties) == 'object') {
                var setting = $(target).val();
                var select = document.createElement('select');
                $(target).replaceWith($(select).attr({
                    'name': $(target).attr('name'),
                    'id': $(target).attr('id'),
                    'class': $(target).attr('class'),
                    'required': $(target).attr('required')
                }));
                if ($(this).attr('title')) {
                    var option = document.createElement('option');
                    $('select' + target).append($(option).text($(this).attr('title')));
                }
                for (i in counties) {
                    var option = document.createElement('option');
                    if (setting == counties[i].name || setting == counties[i].id) {
                        $('select' + target).append($(option).val(counties[i].id).text(counties[i].name).attr('selected', 'selected'));
                    } else {
                        if(counties[i].id>0) {
                            $('select' + target).append($(option).val(counties[i].id).text(counties[i].name));
                        } else {
                            $('select' + target).append($(option).val('').text(counties[i].name));   
                        }
                    }
                }
            } else {
                if ($(this).hasClass('no-custom-zone')) $(target).attr({
                    'disabled': 'disabled'
                }).val($(this).attr('title'));
            }
        }
    }).change(function() {
        if (typeof(county_list) == 'object') {
            var list = county_list[$(this).val()];
            var target = ($(this).attr('rel') && $(this).attr('id') != 'country-list') ? '#' + $(this).attr('rel') : '#state-list';
            if (typeof(list) == 'object' && typeof(county_list[$(this).val()]) != 'undefined' && county_list[$(this).val()].length >= 1) {
                var setting = $(target).val();
                var select = document.createElement('select');
                $(target).replaceWith($(select).attr({
                    'name': $(target).attr('name'),
                    'id': $(target).attr('id'),
                    'class': $(target).attr('class'),
                    'required': $(target).attr('required')
                }));
                if ($(this).attr('title')) {
                    var option = document.createElement('option');
                    $('select' + target).append($(option).text($(this).attr('title')));
                }
                for (var i = 0; i < list.length; i++) {
                    var option = document.createElement('option');
                    if(list[i].id > 0) {
                        $('select' + target).append($(option).val(list[i].id).text(list[i].name));
                    } else {
                        $('select' + target).append($(option).val('').text(list[i].name));  
                    } 
                }
                if(setting>0) {
                    $('select' + target + ' > option[value=' + setting + ']').attr('selected', 'selected');
                }
            } else {
                var input = document.createElement('input');
                var placeholder = $('label[for="' + $(this).attr('rel') + '"]').text() + ' ' + $('#validate_required').text();
                var replacement = $(input).attr({
                    'type': 'text',
                    'placeholder': placeholder,
                    'id': $(target).attr('id'),
                    'name': $(target).attr('name'),
                    'class': $(target).attr('class'),
                    'required': $(target).attr('required')
                });
                if ($(this).hasClass('no-custom-zone')) $(replacement).attr('disabled', 'disabled').val($(this).attr('title'));
                $(target).replaceWith($(replacement));
            }
        }
    });
    
    $('.show_address_form').click(function() {
        show_address_form();
    });

    if($('div.alert').length) {
        show_address_form(); 
    }

    if($('#delivery_is_billing:checkbox').length) {
        if($('#delivery_is_billing:checkbox').prop('checked') == true) {
            $('#address_delivery').hide();
        } else {
            $('#address_delivery').show();
        }
    }
    $('#delivery_is_billing:checkbox').change(function() {
        if ($(this).is(':checked')) {
            $('#address_delivery').hide();
        } else {
            $('#address_delivery').show();
        }
    });
    if ($('input#show-reg:checkbox').is(':checked') == false) $('#account-reg').hide();
    $('input#show-reg:checkbox').change(function() {
        if ($(this).is(':checked')) {
            $('#account-reg').show();
            $('input#reg_password').addClass('required');
            $('input#reg_passconf').addClass('required');
        } else {
            $('#account-reg').hide();
            $('input#reg_password').removeClass('required');
            $('input#reg_passconf').removeClass('required');
        }
    });

    $('.grid_view').click(function(event) {
        grid_view(200, event);
    });
    $('.list_view').click(function(event) {
        list_view(200, event);
    });

    set_product_view(0);

    $('.url_select').bind('change', function() {
        var url = $(this).val(); // get selected value
        if (url) { // require a URL
            window.location = url; // redirect
        }
        return false;
    });
    
    $("#ccScroll").on( "click", "#ccScroll-next", function(event) {
        
        event.preventDefault();
        $(this).hide();
        window.location.hash = $(this).attr("data-next-page");

        var product_list = $('.product_list');
        var next_link = $('a#ccScroll-next');
        var loadingHtml = '<p class="text-center" id="loading"><i class="fa fa-spinner fa-spin thickpad-topbottom"></i> ' + $('#lang_loading').text() + '&hellip;<p>';

        $(this).after(function() {
            return loadingHtml;
        });

        $.ajax({
            url: $(this).attr('href'),
            cache: true,
            complete: function(returned) {
                
                $('p#loading').hide();

                var page = returned.responseText;
                var list = $('.product_list li', page);
                var next = $('a#ccScroll-next', page);
                
                product_list.append(list);
                set_product_view(0)
                $(next_link).replaceWith(next);
                init_add_to_basket();
            }
        });
    });

    var duration = 500;
    $(window).scroll(function() {
        if ($(this).scrollTop() > 400) {
            $('.back-to-top').fadeIn(duration);
        } else {
            $('.back-to-top').fadeOut(duration);
        }
    });

    $('.back-to-top').click(function(event) {
        event.preventDefault();
        $('html, body').animate({
            scrollTop: 0
        }, duration);
        return false;
    });
    $('#show-small-search').click(function() {
        $("#small-search").slideDown();
    });

    if ($("#checkout_form")[0]) {
        if (window_loc_hash == '#login') {
            checkout_form_toggle(false);
        } else if (window_loc_hash == '#register') {
            checkout_form_toggle(true);
        }

        $('#checkout_login').click(function() {
            checkout_form_toggle(false);
        });
        $('#checkout_register').click(function() {
            checkout_form_toggle(true);
        });
    }

    if($('#ptp').length > 0 && $('[name^=productOptions]').length > 0) {
        price_inc_options();
        $("[name^=productOptions]").change(function() {
            price_inc_options();
        });
    }

    /* We must only show grid view with 1 grid column for medium */
    if(Foundation.utils.is_small_only()) {
        grid_view(0);
    }
    if(Foundation.utils.is_medium_up()) {
        $('.field_small_only').attr('disabled', true);
    }
});

function init_add_to_basket() {
    $("form.add_to_basket").each(function(index, el)  {
        $(el).validate({
            submitHandler: function(form) {
                add_to_basket(form);
            }
        });
    });
}

function price_inc_options() {
    var action = $('form.add_to_basket').attr('action');
    var total = 0;
    var ptp = parseFloat($('#ptp').attr("data-price"));
    var fbp = parseFloat($('#fbp').attr("data-price"));
    var ptp_original = ptp;
    var fbp_original = fbp;
    var parts = action.split("?");
    
    if (parts.length > 1) {
        action += "&";
    } else {
        action += "?";
    }
    action += '_g=ajax_price_format&price[0]=';

    $("[name^=productOptions]").each(function () {
        
        if($(this).is('input:radio') && $(this).is(':checked')) {
            if($(this).hasClass('absolute')) { total = ptp = 0; }
            total += parseFloat($(this).attr("data-price"));
        } else if ($(this).is('select') && $(this).val()) {
            if($("option:selected", this).hasClass('absolute')) { total = ptp = 0; }
            total += parseFloat($(this).find("option:selected").attr("data-price"));
        } else if (($(this).is('textarea') || $(this).is('input:text')) && $(this).val() !== '') {
            if($(this).hasClass('absolute')) { total = ptp = 0; }
            total += parseFloat($(this).attr("data-price"));
        }
    });
    ptp += total;

    if($('#fbp').length > 0) {
        fbp += total;
        $.ajax({
            url: action + ptp + '&price[1]='+ fbp,
            cache: true,
            complete: function(returned) {
                var prices = $.parseJSON(returned.responseText);
                $('#ptp').html(prices[0]);
                $('#fbp').html(prices[1]);
            }
        });
    } else {
        $.ajax({
            url: action + ptp,
            cache: true,
            complete: function(returned) {
                var prices = $.parseJSON(returned.responseText);
                $('#ptp').html(prices[0]);
            }
        });
    }
}

function add_to_basket(form) {
    var add = $(form).serialize();
    var action = $(form).attr('action').replace(/\?.*/, '');
    var on_canvas_basket_content = '';
    var parts = action.split("?");
    if (parts.length > 1) {
        action += "&";
    } else {
        action += "?";
    }
    $.ajax({
        url: action + '_g=ajaxadd',
        type: 'POST',
        cache: false,
        data: add,
        complete: function(returned) {
            if (returned.responseText.match("Redir:")) {
                var redir = returned.responseText.split('Redir:');
                window.location = redir[1];
            } else {
                $('#mini-basket').replaceWith(returned.responseText);
                on_canvas_basket_content = $('#mini-basket .box-basket-content').html();
                $(".right-off-canvas-menu .box-basket-content").html(on_canvas_basket_content);
                $(".alert-box").slideUp();
                mini_basket_action();
            }
        }
    });
    return false;
}

function checkout_form_toggle(register) {
    if (register) {
        $("#checkout_login_form").hide();
        $("#checkout_register_form").slideDown();
        $("#payment_method").slideDown();
        $("#reg_password").prop('disabled', false);
        $("#login-username").prop('disabled', true);
        $("#login-password").prop('disabled', true);
        $('#checkout_form').removeAttr("action").attr("action", '#register');
    } else {
        $("#checkout_login_form").slideDown();
        $("#checkout_register_form").hide();
        $("#payment_method").hide();
        $("#reg_password").prop('disabled', true);
        $("#login-username").prop('disabled', false);
        $("#login-password").prop('disabled', false);
        $('#checkout_form').removeAttr("action").attr("action", '#login');
    }
}

function set_product_view(delay) {
    if ($.cookie('product_view') == 'grid') {
        grid_view(delay, null);
    }
}

function show_address_form() {
    $('#register_false_address').hide();
    $('#checkout_register_form').show(); 
}

function mini_basket_action() {
    $('#basket-detail, #small-basket-detail').fadeIn('fast', function() {
        $(this).delay(4000).fadeOut('slow');
    });
}

function grid_view(duration, event) {
    if (event != null) {
        event.preventDefault();
    }
    $.when($('.product_list').fadeOut(duration, function() {
        if(Foundation.utils.is_medium_up()) {
            $('.product_list').removeClass('small-block-grid-1');
            $('.product_list').addClass('small-block-grid-3');
        }
        $('.grid_view').parent('dd').addClass('active');
        $('.list_view').parent('dd').removeClass('active');
        $('.product_list_view').addClass('hide');
        $('.product_grid_view').removeClass('hide');
        $('.product_grid_view .quantity').prop('disabled', false);
        $('.product_list_view .quantity').prop('disabled', true);
        $('.product_list_view .quantity').val('1');
        $('.product_list').fadeIn(duration, function() {
            $.cookie('product_view', 'grid', {expires: 730});
        });
    })).done(function() {
        $(document).foundation('equalizer','reflow');
    });
    return false;
}

function list_view(duration, event) {
    if (event != null) {
        event.preventDefault();
    }
    $.when($('.product_list').fadeOut(duration, function() {
        $('.product_list').removeClass('small-block-grid-3');
        $('.product_list').addClass('small-block-grid-1');
        $('.list_view').parent('dd').addClass('active');
        $('.grid_view').parent('dd').removeClass('active');
        $('.product_grid_view').addClass('hide');
        $('.product_list_view').removeClass('hide');
        $('.product_grid_view .quantity').prop('disabled', true);
        $('.product_list_view .quantity').prop('disabled', false);
        $('.product_grid_view .quantity').val('1');
        $('.product_list').fadeIn(duration, function() {
            $.cookie('product_view', 'list', {expires: 730});
        });
    })).done(function() {
        $(document).foundation('equalizer','reflow');
    });
    return false;
}

function update_quantity(rel, sign) {
    var target = $('input[name="quan[' + rel + ']"]');
    var quick_update = $('#quick_update_' + rel);
    var original_val = $('#original_val_' + rel).text();
    var old_val = $(target).val();
    var new_val = 0;

    if (sign == '+') {
        if (old_val < 999) {
            var new_val = +old_val + +1;
        } else {
            return false;
        }
    } else if (sign == '-') {
        if (old_val < 1) {
            return false;
        }
        var new_val = (old_val - 1);
    }
    $(target).val(new_val);
    $('span.disp_quan_' + rel).text(new_val);
    if (original_val == new_val) {
        quick_update.slideUp();
    } else {
        quick_update.slideDown();
    }

    if (!$("#checkout_login_form")[0]) { // disable jump for basket page
        $('#checkout_form').removeAttr("action").attr("action", '#basket_item_' + rel);
    }
    return false;
}