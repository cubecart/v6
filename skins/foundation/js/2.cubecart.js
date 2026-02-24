;var validation_ini = {};

/**
 * CubeCart theme/ui behaviour
 * - Keeps public helper functions (init_add_to_basket, price_inc_options, etc.) for compatibility.
 * - Reduces repetition, improves readability, and preserves behaviour.
 */
jQuery(document).ready(function ($) {
    // -------------------------------
    // General small utilities
    // -------------------------------
    var DURATION = 500; // used for animations (back-to-top, view switch, etc.)

    function input_delay(callback, ms) {
        var timer = 0;
        return function () {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () { callback.apply(context, args); }, ms || 0);
        };
    }
    function matchCase(text, pattern) {
        var result = '';
        for (var i = 0; i < text.length; i++) {
            var c = text.charAt(i);
            var p = pattern.charCodeAt(i);
            result += (p >= 65 && p < 91) ? c.toUpperCase() : c.toLowerCase();
        }
        return result;
    }
    function toBold(text) {
        return text.replace(/\*(.*?)\*/gm, '<strong>$1</strong>');
    }

    // -------------------------------
    // ACP widget toggle
    // -------------------------------
    function show_acp_widget(show, change) {
        var method = change ? 'animate' : 'css';
        if ((show == 0 && change) || (show == 1 && !change)) {
            show = 1;
            $('.acp_widget')[method]({"left": "0"}, 300);
            $('.acp_widget .close a').html('&laquo;');
        } else {
            show = 0;
            $('.acp_widget')[method]({"left": "-123px"}, 300);
            $('.acp_widget .close a').html('&raquo;');
        }
        return show;
    }
    $('.acp_widget .close a').on('click', function (e) {
        e.preventDefault();
        var show = show_acp_widget($.cookie('show_acp_widget'), true);
        $.cookie('show_acp_widget', show, { expires: 30 });
    });
    show_acp_widget($.cookie('show_acp_widget'), false);

    // -------------------------------
    // Coupon input animation
    // -------------------------------
    $('input#coupon').on('input', function () {
        $('#apply_coupon').toggleClass('animate', $(this).val() !== '');
    });

    // -------------------------------
    // Disable copy/paste on elements with .nopaste
    // -------------------------------
    $('.nopaste').on('cut copy paste', function (e) { e.preventDefault(); });

    // -------------------------------
    // Gravatar for reviews
    // -------------------------------
    $('#element-reviews .review_row').each(function () {
        var avatar_id = $(this).attr('rel');
        var g_parts = avatar_id.split("_");
        var img_url = 'https://gravatar.com/avatar/' + g_parts[1] + '?s=90&d=mp';
        $.ajax({ url: img_url, type: "HEAD", crossDomain: true })
            .done(function () { $('#' + avatar_id).attr("src", img_url); });
    });

    // -------------------------------
    // Open-clearing preview sizing
    // -------------------------------
    if ($('a.open-clearing img#img-preview').length) {
        $('a.open-clearing img#img-preview').on('load', function () {
            var ip = $(this);
            var ip_height = ip.height();
            var ip_width = ip.width();
            var min_height = ip_width * 0.7;
            if (ip_height < min_height) ip_height = min_height;
            $('a.open-clearing img').css('max-height', ip_height + 'px');
            $('#open-clearing-wrapper').css({ 'min-height': ip_height + 'px', 'max-height': ip_height + 'px' });
        });
    }

    // -------------------------------
    // Small scroller (hover to auto-scroll)
    // -------------------------------
    if ($("#scrollContent").length > 0) {
        var scrolling = false;
        var scrollArea = document.querySelector('#scrollContent');
        if (scrollArea.offsetHeight < scrollArea.scrollHeight) {
            $(".scroller").show();
        }
        function scrollContent(direction) {
            var amount = (direction === "up" ? "-=1px" : "+=1px");
            $("#scrollContent").animate({ scrollTop: amount }, 1, function () {
                if (scrolling) scrollContent(direction);
            });
        }
        $("#scrollUp").hover(
            function () { scrolling = true; scrollContent("up"); },
            function () { scrolling = false; }
        );
        $("#scrollDown").hover(
            function () { scrolling = true; $("#scrollUp .icon").show(); scrollContent("down"); },
            function () { scrolling = false; }
        );
    }

    // -------------------------------
    // Colorbox -> Foundation reveal swap
    // -------------------------------
    if ($('.gateway_wrapper .colorbox').length) {
        var colorbox = $('.colorbox');
        var href = colorbox.attr('href');
        var title = colorbox.attr('title');
        colorbox.attr({ href: '#', 'data-reveal-id': 'colorbox' });
        $('.colorbox').after(
            $('<div>')
                .attr({
                    id: 'colorbox',
                    'data-reveal': '',
                    'aria-labelledby': title,
                    'aria-hidden': 'true',
                    role: 'dialog'
                })
                .addClass('reveal-modal tiny')
                .html('<h3>' + title + '</h3><img src="' + href + '"><a class="close-reveal-modal">&#215;</a>')
        );
        $(".gateway_wrapper .colorbox").on('click', function () {
            $('#colorbox').foundation('reveal', 'open');
        });
    }

    // -------------------------------
    // EU cookie consent
    // -------------------------------
    $(".eu_cookie_button").on('click', function () {
        var accept = ($(this).attr('name') == 'accept_cookies_submit');
        if ($(this).attr('data-alert-text')) {
            alert($(this).attr('data-alert-text'));
        }
        $('#eu_cookie_dialogue').slideUp();
        $.cookie('accept_cookies', accept, { expires: 365 });
        $.ajax({ url: '?_g=ajax_cookie_consent&accept=' + (accept ? '1' : '0'), cache: false });
        return false;
    });

    // -------------------------------
    // Top bar label -> link
    // -------------------------------
    $(".top-bar label").on('click', function () {
        var link = $(this).attr('rel');
        if (link) document.location.href = link;
    });

    // -------------------------------
    // Autosubmit helpers
    // -------------------------------
    $(".autosubmit select, .autosubmit input[type=radio]").not('.nosubmit').on('change', function () {
        $(this).closest(".autosubmit").submit();
    });
    $('.icon-submit').each(function () { $(this).closest('form').submit(); });

    // Category navigation: clean has-dropdown when no <ul>
    $(".category-nav li").each(function () {
        if (!$(this).has("ul").length) $(this).removeClass('has-dropdown');
    });

    // -------------------------------
    // Review write/read toggles
    // -------------------------------
    $(".review_hide").on('click', function () { $('#review_read').show(); $('#review_write').slideUp(); return false; });
    $(".review_show").on('click', function () { $('#review_read').hide(); $('#review_write').slideDown(); return false; });

    // -------------------------------
    // Small search toggle
    // -------------------------------
    $(".show-small-search").on('click', function () {
        $('#small-search').slideToggle();
        $('#small-search .search_input').focus();
    });
    $("#show-small-search").on('click', function () {
        $("#small-search").slideDown();
    });

    // Skin selector
    $(".hide_skin_selector").on('click', function (e) {
        e.preventDefault();
        $('.skin_selector').fadeOut();
    });

    // -------------------------------
    // Image gallery hover swap
    // -------------------------------
    $(".image-gallery").hover(function () {
        var src = $(this).attr("data-image-swap");
        $('#img-preview').attr({
            src: src,
            title: $(this).attr("title"),
            alt: $(this).attr("alt")
        });
    });

    // Clearing click forwarding
    $('.open-clearing').on('click', function (e) {
        e.preventDefault();
        $('[data-clearing] li img').eq($(this).data('thumb-index')).trigger('click');
    });

    // -------------------------------
    // Rating stars (required)
    // -------------------------------
    $('input[type=radio].rating').rating({ required: true });

    // -------------------------------
    // Basket summary pane + quantity buttons + checkout proceed
    // -------------------------------
    $('body').on('click', '#basket-summary', function () { mini_basket_action(); });
    $('a.quan').on('click', function () {
        var rel = $(this).attr('rel');
        var sign = $(this).hasClass('add') ? '+' : ($(this).hasClass('subtract') ? '-' : null);
        if (!sign) { alert("No 'add' or 'subtract' class defined."); return false; }
        return update_quantity(rel, sign);
    });
    $('#checkout_proceed').on('click', function () {
        $('<input>', { type: 'hidden', name: 'proceed', value: '1' }).appendTo('form#checkout_form');
    });

    // -------------------------------
    // Country/State dependent menus (initialise + change)
    // -------------------------------
    function buildStateSelect(target, items, placeholder) {
        // Replace target with <select> and populate options
        var setting = $(target).val();
        var select = document.createElement('select');
        $(target).replaceWith($(select).attr({
            'name': $(target).attr('name'),
            'id': $(target).attr('id'),
            'class': $(target).attr('class')
        }));
        if (placeholder) {
            var opt0 = document.createElement('option');
            $('select' + target).append($(opt0).text(placeholder));
        }
        for (var i = 0; i < items.length; i++) {
            var opt = document.createElement('option');
            var id = items[i].id;
            var name = items[i].name;
            if (setting && (String(setting).toLowerCase() == String(name).toLowerCase() || String(setting) == String(id))) {
                $('select' + target).append($(opt).val(id > 0 ? id : '').text(name).attr('selected', 'selected'));
            } else {
                $('select' + target).append($(opt).val(id > 0 ? id : '').text(name));
            }
        }
    }
    function buildStateInput(target, placeholder, disabled) {
        var input = document.createElement('input');
        var replacement = $(input).attr({
            'type': 'text',
            'placeholder': placeholder,
            'id': $(target).attr('id'),
            'name': $(target).attr('name'),
            'class': $(target).attr('class'),
            'required': $(target).attr('required')
        });
        if (disabled) $(replacement).attr('disabled', 'disabled').val(placeholder);
        $(target).replaceWith($(replacement));
    }
    function initCountryState($select) {
        if (typeof (county_list) !== 'object') return;
        var countryVal = $select.val();
        var counties = county_list[countryVal];
        var target = ($select.attr('rel') && $select.attr('id') != 'country-list') ? ('#' + $select.attr('rel')) : '#state-list';
        var zone_status = $('option:selected', $select).attr('data-status');
        var form_id = $select.closest("form").attr('id');

        validation_ini[target] = stateRequirements(zone_status, form_id, target, false);

        if (typeof (counties) === 'object') {
            buildStateSelect(target, counties, $select.attr('title'));
        } else {
            if ($select.hasClass('no-custom-zone')) {
                $(target).attr({ 'disabled': 'disabled' }).val($select.attr('title'));
            }
        }
    }
    function changeCountryState($select) {
        if (typeof (county_list) !== 'object') return;
        var countryVal = $select.val();
        var list = county_list[countryVal];
        var target = ($select.attr('rel') && $select.attr('id') != 'country-list') ? ('#' + $select.attr('rel')) : '#state-list';
        var zone_status = $('option:selected', $select).attr('data-status');
        var form_id = $select.closest("form").attr('id');

        validation_ini[target] = stateRequirements(zone_status, form_id, target, true);

        if (typeof (list) === 'object' && typeof (county_list[countryVal]) != 'undefined' && county_list[countryVal].length >= 1) {
            buildStateSelect(target, list, $select.attr('title'));
        } else {
            var placeholder = $('label[for="' + $select.attr('rel') + '"]').text() + ' ' + $('#validate_required').text();
            var disabled = $select.hasClass('no-custom-zone');
            buildStateInput(target, disabled ? $select.attr('title') : placeholder, disabled);
        }
    }

    $('select#country-list, select.country-list').each(function () { initCountryState($(this)); })
        .on('change', function () { changeCountryState($(this)); });

    // -------------------------------
    // Show address form on demand / on alert
    // -------------------------------
    $('.show_address_form').on('click', function () { show_address_form(); });
    if ($('div.alert').length) { show_address_form(); }

    // -------------------------------
    // Delivery address toggle (same as billing)
    // -------------------------------
    function toggleDeliveryAddress() {
        var show = !$('#delivery_is_billing:checkbox').prop('checked');
        $('#address_delivery').toggle(show);
    }
    if ($('#delivery_is_billing:checkbox').length) toggleDeliveryAddress();
    $('#delivery_is_billing:checkbox').on('change', toggleDeliveryAddress);

    // -------------------------------
    // Registration section toggle on checkout
    // -------------------------------
    if ($('input#show-reg:checkbox').is(':checked') === false) $('#account-reg').hide();
    $('input#show-reg:checkbox').on('change', function () {
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

    // -------------------------------
    // Grid/List view toggles
    // -------------------------------
    $('.grid_view').on('click', function (event) { grid_view(200, event); });
    $('.list_view').on('click', function (event) { list_view(200, event); });
    set_product_view(0);

    // -------------------------------
    // URL select redirector
    // -------------------------------
    $('.url_select').on('change', function () {
        var url = $(this).val();
        if (url) window.location = url;
        return false;
    });

    // -------------------------------
    // SAYT (search as you type)
    // -------------------------------
    var selector = ($(window).width() < 640) ? '#small-search .search_input' : '.search_input';
    var sayt = $(selector);
    sayt.on('click', function () { $.removeCookie('ccScroll', null); });
    var keyDelay = sayt.hasClass("es") ? 0 : 500;
    sayt.on('keyup', input_delay(function () { saytGo(); }, keyDelay));

    $(document).on('keyup', function (e) {
        if (e.key === "Escape") {
            sayt.val('');
            $('#sayt_results li').remove();
        }
    });

    function saytGo() {
        if (!sayt.hasClass("es")) return false;
        var search_term = sayt.val();
        if (!$('#sayt_results').length) $('<ul id="sayt_results">').insertAfter(sayt);
        if (search_term.length === 0) {
            $('#sayt_results li').remove();
            return;
        }
        var amount = sayt.attr("data-amount");
        var url = '?_e=es&q=' + search_term + '&a=' + amount;
        $.ajax({
            async: true,
            url: url,
            cache: true,
            complete: function (response) {
                $('#sayt_results li').remove();
                var products = $.parseJSON(response.responseText);
                if (Array.isArray(products)) {
                    search_term.replace('*', '');
                    var splitted = search_term.split(' ');
                    for (var k in products) {
                        var product_name = products[k]['name'];
                        for (var s = 0; s < splitted.length; s++) {
                            var split = splitted[s];
                            if (!split || split === '*') continue;
                            var regexp = new RegExp('(' + split + ')', 'ig');
                            product_name = product_name.replace(regexp, function (match) { return '*' + matchCase(split, match) + '*'; });
                        }
                        var image = (sayt.attr("data-image") == 'true' && products[k]['thumbnail'] !== '')
                            ? "<span><img src=\"" + products[k]['thumbnail'] + "\" title=\"" + products[k]['name'] + "\"></span>"
                            : "<span>&nbsp;</span>";
                        $("#sayt_results").append("<li><a href='?_a=product&product_id=" + products[k]['product_id'] + "'>" + image + toBold(product_name) + "</a></li>");
                    }
                } else {
                    $('#sayt_results').append('<li class="status">No results found</li>');
                }
            }
        });
    }

    // -------------------------------
    // Infinite scroll (category pages)
    // -------------------------------
    $("#ccScroll").on("click", ".ccScroll-next", function (event) {
        event.preventDefault();
        $(this).hide();
        $("#loading").show();
        window.location.hash = $(this).attr("data-next-page");

        var loc = $(window).scrollTop();
        var cat = parseInt($(this).attr("data-cat"), 10);
        var page = parseInt($(this).attr("data-next-page"), 10);
        var product_list = $('.product_list');
        var next_link = $('a.ccScroll-next');

        // Keep history to load on back button
        var ccScrollHistory;
        if ($.cookie('ccScroll')) {
            ccScrollHistory = $.parseJSON($.cookie("ccScroll"));
            ccScrollHistory[cat] = page;
        } else {
            ccScrollHistory = {};
            ccScrollHistory[cat] = page;
        }
        if (loc > 0) {
            ccScrollHistory['loc'] = loc;
        }
        // Set cookie for 10 mins
        var date = new Date();
        date.setTime(date.getTime() + (10 * 60 * 1000));
        $.cookie("ccScroll", JSON.stringify(ccScrollHistory), { expires: date });

        var href = $(this).attr('href');
        $.ajax({
            async: false,
            url: href,
            cache: true,
            complete: function (returned) {
                var page_html = returned.responseText;
                var list = $('.product_list li', page_html);
                var next = $('a.ccScroll-next', page_html);
                $('.product_list li').removeClass("newTop");
                $(list[0]).addClass('newTop');
                setTimeout(function () {
                    product_list.append(list);
                    set_product_view(0);
                    $(next_link).replaceWith(next);
                    init_add_to_basket();
                    $("#loading").hide();
                    $('html, body').animate({ scrollTop: $("li.newTop").offset().top }, 500);
                    var local = { catId: cat, html: $('#ccScroll').html() };
                    localStorage.setItem('category', JSON.stringify(local));
                }, 1500);
            }
        });
    });

    // Small screens: restore scroll history / preload pages
    if ($(window).width() < 640) {
        if ($('#ccScrollCat').length > 0) {
            var cat_pages = parseInt($('#ccScrollCat').text(), 10);
            if ($.cookie('ccScroll')) {
                var ccScrollHistory = $.parseJSON($.cookie("ccScroll"));
                var query = true;
                if (cat_pages in ccScrollHistory) {
                    if (localStorage.hasOwnProperty('category')) {
                        var cat = JSON.parse(localStorage.getItem('category') || '{}');
                        if (cat.catId == cat_pages) {
                            query = false;
                        }
                    }
                    if (query) {
                        for (var i = 1; i < ccScrollHistory[cat_pages]; i++) {
                            $('.ccScroll-next:last').trigger("click");
                        }
                    } else {
                        $('#ccScroll').html(cat.html);
                    }
                    $('html, body').animate({ scrollTop: ccScrollHistory['loc'] }, 'slow');
                }
            }
        }

        // Back-to-top visibility on small screens
        $(window).on('scroll', function () {
            if ($(this).scrollTop() > 400) {
                $('.back-to-top').fadeIn(DURATION);
            } else {
                $('.back-to-top').fadeOut(DURATION);
            }
        });
    }

    // Back-to-top click (all screens)
    $('.back-to-top').on('click', function (event) {
        event.preventDefault();
        $('html, body').animate({ scrollTop: 0 }, DURATION);
        return false;
    });

    // -------------------------------
    // Checkout login/register hash controls
    // -------------------------------
    var window_loc_hash = window.location.hash;
    if ($("#checkout_form")[0]) {
        if (window_loc_hash == '#login') {
            checkout_form_toggle(false);
        } else if (window_loc_hash == '#register') {
            checkout_form_toggle(true);
        }
        $('#checkout_login').on('click', function () { checkout_form_toggle(false); });
        $('#checkout_register').on('click', function () { checkout_form_toggle(true); });
    }

    // -------------------------------
    // Product options -> price + image update
    // -------------------------------
    if ($('#ptp').length > 0 && $('[name^=productOptions]').length > 0) {
        price_inc_options();
        $("[name^=productOptions]").on('change', function () {
            price_inc_options();
            var product_image = '';
            if ($(this).is('input:radio, input:checkbox, input:hidden')) {
                product_image = $(this).is(':checked') ? $(this).attr('data-image') : '';
            } else if ($(this).is('select')) {
                product_image = $('option:selected', this).attr('data-image');
            }
            if (product_image.length > 0) {
                if ($('a.MagicZoom').length > 0) {
                    var magicZoomNode = $('a.MagicZoom').attr('id');
                    MagicZoom.update(
                        magicZoomNode,
                        product_image.replace(".500.", ".").replace("/cache/", "/source/"),
                        product_image
                    );
                } else {
                    $('img#img-preview').attr('src', product_image);
                }
            }
        });
    }

    // -------------------------------
    // Newsletter recaptcha reveal
    // -------------------------------
    $("#newsletter_email").on('focus', function () {
        $("#newsletter_recaptcha").slideDown();
    });

    // -------------------------------
    // Responsive product view init (Foundation)
    // -------------------------------
    if (window.Foundation && Foundation.utils && Foundation.utils.is_small_only && Foundation.utils.is_small_only()) {
        grid_view(0);
        $('#content_checkout_medium_up').remove();
        $("[checked]").prop("checked", true);
    }
    if (window.Foundation && Foundation.utils && Foundation.utils.is_medium_up && Foundation.utils.is_medium_up()) {
        $('#content_checkout_small').remove();
        $("[checked]").prop("checked", true);
    }
});


/* ===========================
   Public helper functions
   (kept for compatibility)
   =========================== */

function init_add_to_basket() {
    $("form.add_to_basket").each(function (index, el) {
        $(el).validate({
            submitHandler: function (form) {
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
    var parts = action.split("?");

    action += (parts.length > 1 ? "&" : "?") + '_g=ajax_price_format&price[0]=';

    $("[name^=productOptions]").each(function () {
        if ($(this).is('input:radio') && $(this).is(':checked')) {
            if ($(this).hasClass('absolute')) { total -= ptp; }
            total += parseFloat($(this).attr("data-price"));
        } else if ($(this).is('select') && $(this).val()) {
            if ($("option:selected", this).hasClass('absolute')) { total -= ptp; }
            total += parseFloat($(this).find("option:selected").attr("data-price"));
        } else if (($(this).is('textarea') || $(this).is('input:text')) && $(this).val() !== '') {
            if ($(this).hasClass('absolute')) { total -= ptp; }
            total += parseFloat($(this).attr("data-price"));
        }
    });

    ptp = ptp + total;

    if ($('#fbp').length > 0) {
        fbp = fbp + total;
        $.ajax({
            url: action + ptp + '&price[1]=' + fbp,
            cache: true,
            complete: function (returned) {
                var prices = $.parseJSON(returned.responseText);
                $('#ptp').html(prices[0]);
                $('#fbp').html(prices[1]);
                // Maintain original behaviour: show/hide sale price by comparison
                prices = prices.map(function (p) {
                    return parseFloat(String(p).replace(/[^0-9.-]/g, ""));
                });
                if (prices[0] <= prices[1]) {
                    $('#fbp').hide();
                    $('#ptp').removeClass('sale_price');
                } else {
                    $('#fbp').show();
                    $('#ptp').addClass('sale_price');
                }
            }
        });
    } else {
        $.ajax({
            url: action + ptp,
            cache: true,
            complete: function (returned) {
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
    action += (parts.length > 1 ? "&" : "?");
    $.ajax({
        url: action + '_g=ajaxadd&t=' + new Date().getTime(),
        type: 'POST',
        cache: false,
        data: add,
        complete: function (returned) {
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
        $("#reg_password").prop('disabled', false);
        $("#login-username").prop('disabled', true);
        $("#login-password").prop('disabled', true);
        $("#checkout_login_btn").prop('disabled', true);
        $('#checkout_form').removeAttr("action").attr("action", '#register');
    } else {
        $("#checkout_login_form").slideDown();
        $("#checkout_register_form").hide();
        $("#reg_password").prop('disabled', true);
        $("#login-username").prop('disabled', false);
        $("#login-password").prop('disabled', false);
        $("#checkout_login_btn").prop('disabled', false);
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
    $('#basket-detail, #small-basket-detail').fadeIn('fast', function () {
        $(this).delay(4000).fadeOut('slow');
        if ($('#basket-detail').height() > $(window).height()) {
            window.location.href = '?_a=basket';
        }
    });
}

function grid_view(duration, event) {
    if (event != null) { event.preventDefault(); }
    $.when($('.product_list').fadeOut(duration, function () {
        if (window.Foundation && Foundation.utils && Foundation.utils.is_medium_up && Foundation.utils.is_medium_up()) {
            $('.product_list').addClass('medium-block-grid-3');
        }
        $('.grid_view').parent('dd').addClass('active');
        $('.list_view').parent('dd').removeClass('active');
        $('.product_list_view').addClass('hide');
        $('.product_grid_view').removeClass('hide');
        $('.product_grid_view .quantity').prop('disabled', false);
        $('.product_list_view .quantity').prop('disabled', true).val('1');
        $('.product_list').fadeIn(duration, function () {
            $.cookie('product_view', 'grid', { expires: 730 });
        });
    })).done(function () {
        $(document).foundation('equalizer', 'reflow');
    });
    return false;
}

function list_view(duration, event) {
    if (event != null) { event.preventDefault(); }
    $.when($('.product_list').fadeOut(duration, function () {
        $('.product_list').removeClass('medium-block-grid-3');
        $('.list_view').parent('dd').addClass('active');
        $('.grid_view').parent('dd').removeClass('active');
        $('.product_grid_view').addClass('hide');
        $('.product_list_view').removeClass('hide');
        $('.product_grid_view .quantity').prop('disabled', true).val('1');
        $('.product_list_view .quantity').prop('disabled', false);
        $('.product_list').fadeIn(duration, function () {
            $.cookie('product_view', 'list', { expires: 730 });
        });
    })).done(function () {
        $(document).foundation('equalizer', 'reflow');
    });
    return false;
}

function update_quantity(rel, sign) {
    var target = $('input[name="quan[' + rel + ']"]');
    var quick_update = $('#quick_update_' + rel);
    var original_val = $('#original_val_' + rel).text();
    var old_val = parseInt($(target).val(), 10) || 0;
    var new_val = old_val;

    if (sign == '+') {
        if (old_val < 999) new_val = old_val + 1; else return false;
    } else if (sign == '-') {
        if (old_val < 1) return false;
        new_val = old_val - 1;
    }

    $(target).val(new_val);
    $('span.disp_quan_' + rel).text(new_val);
    if (String(original_val) == String(new_val)) { quick_update.slideUp(); } else { quick_update.slideDown(); }

    if (!$("#checkout_login_form")[0]) { // disable jump for basket page
        $('#checkout_form').removeAttr("action").attr("action", '#basket_item_' + rel);
    }
    return false;
}

var stateRequirements = function (zone_status, form_id, target, change) {
    var val = false;
    var disabled = false;
    switch (zone_status) {
        case '1': // Required
            val = true;
            $(target + "_wrapper").show();
            break;
        case '2': // Optional
            $(target + "_wrapper").show();
            break;
        case '3': // Hidden
            disabled = true;
            $(target + "_wrapper").hide();
            break;
    }
    $(target).prop('disabled', disabled);
    if (change) {
        $(target).rules("add", { required: val });
        $(form_id).validate();
    }
    return val;
};