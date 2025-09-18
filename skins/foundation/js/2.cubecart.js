;/* 2.cubecart.js — refactored for readability, performance, and maintainability */
(function($, window, document, undefined) {
  'use strict';

  var validation_ini = {}; // keeps initial validation state added by stateRequirements

  // ---------- Small utilities ----------
  function debounce(fn, wait) {
    var t;
    return function() {
      var ctx = this, args = arguments;
      clearTimeout(t);
      t = setTimeout(function(){ fn.apply(ctx, args); }, wait || 0);
    };
  }
  function toggleClass($el, className, on) {
    if (on) $el.addClass(className); else $el.removeClass(className);
  }
  function setAttr($el, key, val) {
    if (val === undefined || val === null) return $el.removeAttr(key);
    return $el.attr(key, val);
  }
  function matchCase(text, pattern) {
    var out = '', i;
    for(i=0;i<text.length;i++){
      var c = text.charAt(i), p = pattern.charCodeAt(i);
      out += (p >= 65 && p < 91) ? c.toUpperCase() : c.toLowerCase();
    }
    return out;
  }
  function toBold(text){
    return text.replace(/\*(.*?)\*/gm, '<strong>$1</strong>');
  }

  // ---------- Doc Ready ----------
  $(function() {
    var $win = $(window);
    var window_loc_hash = window.location.hash;

    // ===== Admin/ACP widget toggle =====
    $(document).on('click', '.acp_widget .close a', function(e){
      e.preventDefault();
      var show = show_acp_widget($.cookie('show_acp_widget'), true);
      $.cookie('show_acp_widget', show, {expires: 30});
    });
    show_acp_widget($.cookie('show_acp_widget'), false);

    function show_acp_widget(show, change) {
      var open = ((show == 0 && change) || (show == 1 && !change));
      if (open) {
        show = 1;
        $('.acp_widget').css("left","0");
        $('.acp_widget .close a').html('&laquo;');
      } else {
        show = 0;
        $('.acp_widget').css("left","-123px");
        $('.acp_widget .close a').html('&raquo;');
      }
      return show;
    }

    // ===== Coupon input animate =====
    var $applyCoupon = $('#apply_coupon');
    $(document).on('input', '#coupon', function(){
      toggleClass($applyCoupon, 'animate', this.value !== '');
    });

    // ===== No paste fields =====
    $(document).on('cut copy paste', '.nopaste', function(e){ e.preventDefault(); });

    // ===== Reviews: try Gravatar avatars via HEAD (non-blocking) =====
    $('#element-reviews .review_row').each(function() {
      var avatar_id = $(this).attr('rel');
      if(!avatar_id) return;
      var g_parts = avatar_id.split("_");
      if(!g_parts[1]) return;
      var img_url = 'https://gravatar.com/avatar/'+g_parts[1]+'?s=90&d=mp';
      $.ajax({
        url: img_url,
        type: "HEAD",
        crossDomain: true
      }).done(function(){
        $('#' + avatar_id).attr("src", img_url);
      });
    });

    // ===== Image preview sizing for clearing =====
    if($('a.open-clearing img#img-preview').length) {
      $('a.open-clearing img#img-preview').on('load', function() {
        var $ip = $('a.open-clearing img#img-preview');
        var ip_h = $ip.height(), ip_w = $ip.width();
        var min_h = ip_w * 0.7;
        if(ip_h < min_h) ip_h = min_h;
        $('a.open-clearing img').css({'max-height': ip_h+'px'});
        $('#open-clearing-wrapper').css({'min-height': ip_h+'px', 'max-height': ip_h+'px'});
      });
    }

    // ===== Custom scroller arrows =====
    (function initScroller(){
      var $scrollArea = $('#scrollContent');
      if(!$scrollArea.length) return;
      var scrolling = false;

      if($scrollArea[0].offsetHeight < $scrollArea[0].scrollHeight){
        $(".scroller").show();
      }

      function scrollContent(direction) {
        var amount = (direction === "up" ? "-=1px" : "+=1px");
        $scrollArea.animate({ scrollTop: amount }, 1, function() {
          if (scrolling) scrollContent(direction);
        });
      }

      $('#scrollUp').on('mouseover', function(){
        scrolling = true; scrollContent('up');
      }).on('mouseout', function(){ scrolling = false; });

      $('#scrollDown').on('mouseover', function(){
        scrolling = true; $("#scrollUp .icon").show(); scrollContent('down');
      }).on('mouseout', function(){ scrolling = false; });
    })();

    // ===== Colorbox -> Foundation reveal adapter in gateways =====
    (function initGatewayColorbox(){
      var $colorbox = $('.gateway_wrapper .colorbox');
      if(!$colorbox.length) return;

      var href = $colorbox.attr('href');
      var title = $colorbox.attr('title');

      $colorbox.attr({'href':'#','data-reveal-id':'colorbox'});

      $colorbox.after(
        $('<div>', {
          id: 'colorbox',
          class: 'reveal-modal tiny',
          'data-reveal': '',
          'aria-labelledby': title,
          'aria-hidden': 'true',
          role: 'dialog'
        }).html('<h3>'+title+'</h3><img src="'+href+'"><a class="close-reveal-modal">&#215;</a>')
      );

      $colorbox.on('click', function(){ $('#colorbox').foundation('reveal', 'open'); });
    })();

    // ===== EU cookie banner =====
    $(document).on('click', '.eu_cookie_button', function(e){
      e.preventDefault();
      var accept = $(this).attr('name') === 'accept_cookies_submit';
      var alertText = $(this).data('alert-text');
      if(alertText) alert(alertText);
      $('#eu_cookie_dialogue').slideUp();
      $.cookie('accept_cookies', accept, {expires: 365});
      $.ajax({ url: '?_g=ajax_cookie_consent&accept='+(accept ? '1' : '0'), cache: false });
    });

    // ===== Top bar label with rel as link =====
    $(document).on('click', '.top-bar label', function(){
      var link = $(this).attr('rel');
      if (link !== undefined && link !== false) { document.location.href = link; }
    });

    // ===== Auto-submit widgets =====
    $(document).on('change', '.autosubmit select, .autosubmit input[type=radio]', function(){
      if ($(this).hasClass('nosubmit')) return;
      $(this).closest('.autosubmit').submit();
    });
    $('.icon-submit').each(function(){ $(this).closest('form').submit(); });

    // ===== Category nav tidy =====
    $('.category-nav li').each(function(){
      if (!$(this).has("ul").length) $(this).removeClass('has-dropdown');
    });

    // ===== Review show/hide =====
    $(document)
      .on('click', '.review_hide', function(e){
        e.preventDefault(); $('#review_read').show(); $('#review_write').slideUp();
      })
      .on('click', '.review_show', function(e){
        e.preventDefault(); $('#review_read').hide(); $('#review_write').slideDown();
      });

    // ===== Small search toggle =====
    $(document).on('click', '.show-small-search', function(){
      $('#small-search').slideToggle();
      $('#small-search .search_input').focus();
    });
    $(document).on('click', '.hide_skin_selector', function(e){
      e.preventDefault(); $('.skin_selector').fadeOut();
    });

    // ===== Gallery hover swap =====
    $(document).on('mouseenter', '.image-gallery', function(){
      var $t = $(this);
      $('#img-preview')
        .attr('src', $t.data('image-swap'))
        .attr('title', $t.attr('title'))
        .attr('alt', $t.attr('alt'));
    });

    // ===== Open clearing from thumbnail index =====
    $(document).on('click', '.open-clearing', function(e){
      e.preventDefault();
      $('[data-clearing] li img').eq($(this).data('thumb-index')).trigger('click');
    });

    // ===== Stars (jquery.rating) =====
    $('input[type=radio].rating').rating({ required: true });

    // ===== Mini basket =====
    $(document).on('click', '#basket-summary', mini_basket_action);

    // ===== Quantity add/subtract =====
    $(document).on('click', 'a.quan', function(){
      var rel = $(this).attr('rel');
      var sign = $(this).hasClass('add') ? '+' : $(this).hasClass('subtract') ? '-' : null;
      if(!sign) { alert('No \'add\' or \'subtract\' class defined.'); return false; }
      return update_quantity(rel, sign);
    });

    // ===== Checkout proceed hidden flag =====
    $(document).on('click', '#checkout_proceed', function(){
      $('<input>', {type:'hidden', name:'proceed', value:'1'}).appendTo('form#checkout_form');
    });

    // ===== Country/State linked selects =====
    $('select#country-list, select.country-list').each(function() {
      hydrateStateForCountry($(this), false);
    }).on('change', function(){
      hydrateStateForCountry($(this), true);
    });

    function hydrateStateForCountry($country, change){
      if (typeof county_list !== 'object') return;

      var cid = $country.val();
      var list = county_list[cid];
      var target = ($country.attr('rel') && $country.attr('id') !== 'country-list') ? ('#' + $country.attr('rel')) : '#state-list';
      var zone_status = $('option:selected', $country).attr('data-status');
      var form_id = $country.closest('form').attr('id');

      validation_ini[target] = stateRequirements(zone_status, '#'+form_id, target, change);

      // build select if we have list data
      if (typeof list === 'object' && list && list.length >= 1) {
        var setting = $(target).val();
        var $select = $('<select>', {
          name: $(target).attr('name'),
          id: $(target).attr('id'),
          'class': $(target).attr('class')
        });

        if ($country.attr('title')) {
          $('<option>', { text: $country.attr('title') }).appendTo($select);
        }

        // populate
        for (var i=0; i<list.length; i++){
          $('<option>', {
            value: list[i].id > 0 ? list[i].id : '',
            text: list[i].name,
            selected: (String(setting).toLowerCase() === String(list[i].name).toLowerCase() || String(setting) === String(list[i].id))
          }).appendTo($select);
        }

        $(target).replaceWith($select);
      } else {
        // replace with text input
        var $label = $('label[for="' + $country.attr('rel') + '"]');
        var placeholder = $label.text() + ' ' + $('#validate_required').text();
        var $input = $('<input>', {
          type: 'text',
          placeholder: placeholder,
          id: $(target).attr('id'),
          name: $(target).attr('name'),
          'class': $(target).attr('class'),
          required: $(target).attr('required')
        });
        if ($country.hasClass('no-custom-zone')) { $input.prop('disabled', true).val($country.attr('title')); }
        $(target).replaceWith($input);
      }

      if ($country.hasClass('no-custom-zone')) $(target).prop('disabled', true).val($country.attr('title'));
    }

    // ===== Address form reveal when alerts exist =====
    $(document).on('click', '.show_address_form', show_address_form);
    if($('div.alert').length) show_address_form();

    // ===== Delivery == billing toggle =====
    var $dib = $('#delivery_is_billing:checkbox');
    if ($dib.length) { $('#address_delivery').toggle(!$dib.prop('checked')); }
    $dib.on('change', function(){ $('#address_delivery').toggle(!$(this).is(':checked')); });

    // ===== Register account toggle in checkout =====
    var $regToggle = $('input#show-reg:checkbox');
    if(!$regToggle.is(':checked')) $('#account-reg').hide();
    $regToggle.on('change', function(){
      var on = $(this).is(':checked');
      $('#account-reg').toggle(on);
      $('input#reg_password, input#reg_passconf').toggleClass('required', on);
    });

    // ===== View switchers =====
    $(document).on('click', '.grid_view', function(e){ switch_view('grid', 200, e); });
    $(document).on('click', '.list_view', function(e){ switch_view('list', 200, e); });
    set_product_view(0);

    function switch_view(type, duration, event){
      if (event) event.preventDefault();
      var isGrid = (type === 'grid');

      $.when($('.product_list').fadeOut(duration, function() {
        $('.product_list').toggleClass('medium-block-grid-3', isGrid);
        $('.grid_view').parent('dd').toggleClass('active', isGrid);
        $('.list_view').parent('dd').toggleClass('active', !isGrid);

        $('.product_grid_view').toggleClass('hide', !isGrid);
        $('.product_list_view').toggleClass('hide', isGrid);

        $('.product_grid_view .quantity').prop('disabled', !isGrid).val('1');
        $('.product_list_view .quantity').prop('disabled', isGrid).val('1');

        $('.product_list').fadeIn(duration, function() {
          $.cookie('product_view', type, {expires: 730});
        });
      })).done(function(){
        $(document).foundation('equalizer','reflow');
      });

      return false;
    }

    // backwards-compat wrappers
    window.grid_view = function(duration, event){ return switch_view('grid', duration, event); };
    window.list_view = function(duration, event){ return switch_view('list', duration, event); };

    // ===== URL select <select> =====
    $(document).on('change', '.url_select', function(){
      var url = $(this).val();
      if (url) window.location = url;
      return false;
    });

    // ===== SAYT (search-as-you-type) =====
    (function initSAYT(){
      var selector = ($win.width() < 640) ? '#small-search .search_input' : '.search_input';
      var $sayt = $(selector);

      $sayt.on('click', function(){ $.removeCookie('ccScroll', null); });

      var delay = $sayt.hasClass('es') ? 0 : 500;
      $sayt.on('keyup', debounce(function(){ saytGo($sayt); }, delay));

      $(document).on('keyup', function(e){
        if (e.key === 'Escape') {
          $sayt.val('');
          $('#sayt_results li').remove();
        }
      });

      function saytGo($input) {
        if (!$input.hasClass('es')) return false;
        var term = $input.val();

        if(!$('#sayt_results').length) $('<ul id="sayt_results">').insertAfter($input);

        if(term.length === 0) {
          $('#sayt_results li').remove();
          return;
        }

        var amount = $input.attr('data-amount');
        var url = '?_e=es&q=' + encodeURIComponent(term) + '&a=' + encodeURIComponent(amount);

        $.ajax({
          url: url,
          cache: true
        }).done(function(resp){
          $('#sayt_results li').remove();
          var products;
          try { products = $.parseJSON(resp); } catch(e){ products = null; }
          if(Array.isArray(products)) {
            var clean = term.replace('*','');
            var parts = clean.split(' ');
            for(var k=0;k<products.length;k++){
              var p = products[k], name = p.name;
              for(var i=0;i<parts.length;i++){
                var split = parts[i];
                if(!split || split === '*') continue;
                var re = new RegExp('('+split+')','ig');
                name = name.replace(re, function(m){ return '*'+matchCase(split, m)+'*'; });
              }
              var image = ($input.attr('data-image') === 'true' && p.thumbnail!=='')
                ? '<span><img src="'+p.thumbnail+'" title="'+p.name+'"></span>'
                : '<span>&nbsp;</span>';
              $('#sayt_results').append(
                "<li><a href='?_a=product&product_id="+p.product_id+"'>"+image+toBold(name)+"</a></li>"
              );
            }
          } else {
            $('#sayt_results').append('<li class="status">No results found</li>');
          }
        });
      }
    })();

    // ===== Category infinite scroll / pager recall (no sync AJAX) =====
    (function initCcScroll(){
      var $ccScroll = $('#ccScroll');
      if (!$ccScroll.length) return;

      $(document).on('click', '#ccScroll .ccScroll-next', function(event){
        event.preventDefault();

        var $btn = $(this);
        $btn.hide();
        $("#loading").show();
        var nextHash = $btn.attr('data-next-page');
        window.location.hash = nextHash;

        var loc = $win.scrollTop();
        var cat = parseInt($btn.attr('data-cat'), 10);
        var page = parseInt($btn.attr('data-next-page'), 10);
        var $product_list = $('.product_list');

        // Persist ccScroll state in cookie for 10 minutes
        var history = $.cookie('ccScroll') ? $.parseJSON($.cookie('ccScroll')) : {};
        history[cat] = page;
        if (loc > 0) history.loc = loc;

        var expires = new Date();
        expires.setTime(expires.getTime() + (10 * 60 * 1000));
        $.cookie('ccScroll', JSON.stringify(history), {expires: expires});

        $.ajax({
          url: $btn.attr('href'),
          cache: true
        }).done(function(html){
          var $page = $('<div>').html(html);
          var $list = $('.product_list li', $page);
          var $next = $('a.ccScroll-next', $page);

          $('.product_list li').removeClass('newTop');
          $list.eq(0).addClass('newTop');

          setTimeout(function(){
            $product_list.append($list);
            set_product_view(0);
            $btn.replaceWith($next);
            init_add_to_basket();
            $("#loading").hide();

            $('html, body').animate({ scrollTop: $('li.newTop').offset().top }, 500);

            var local = { catId: cat, html: $ccScroll.html() };
            localStorage.setItem('category', JSON.stringify(local));
          }, 1500);
        });
      });

      // Mobile: restore previous pages/content
      if ($win.width() < 640) {
        var $ccScrollCat = $('#ccScrollCat');
        if($ccScrollCat.length){
          var cat_pages = parseInt($ccScrollCat.text(), 10);
          if ($.cookie('ccScroll')) {
            var ccScrollHistory = $.parseJSON($.cookie('ccScroll'));
            var query = true, catLocal;

            if (ccScrollHistory.hasOwnProperty(cat_pages)) {
              if(localStorage.hasOwnProperty('category')) {
                catLocal = JSON.parse(localStorage.getItem('category') || '{}');
                if (catLocal.catId === cat_pages) query = false;
              }
              if (query) {
                // Load up to saved page-1
                var times = ccScrollHistory[cat_pages] - 1;
                var i = 0;
                (function clickNext(){
                  if (i >= times) {
                    $('html, body').animate({ scrollTop: ccScrollHistory.loc || 0 }, 'slow');
                    return;
                  }
                  var $nextBtn = $('.ccScroll-next:last');
                  if ($nextBtn.length) {
                    $nextBtn.trigger('click');
                    i++;
                    setTimeout(clickNext, 300); // stagger clicks to avoid overlap
                  }
                })();
              } else {
                $ccScroll.html(catLocal.html || $ccScroll.html());
                $('html, body').animate({ scrollTop: ccScrollHistory.loc || 0 }, 'slow');
              }
            }
          }
        }

        var duration = 500;
        $win.on('scroll', debounce(function(){
          $('.back-to-top').toggle($(this).scrollTop() > 400);
        }, 50));
      }

      $(document).on('click', '.back-to-top', function(e){
        e.preventDefault();
        $('html, body').animate({ scrollTop: 0 }, 500);
        return false;
      });

      $('#show-small-search').on('click', function(){ $("#small-search").slideDown(); });
    })();

    // ===== Checkout form toggle =====
    (function initCheckoutToggle(){
      if (!$("#checkout_form")[0]) return;

      if (window_loc_hash === '#login') {
        checkout_form_toggle(false);
      } else if (window_loc_hash === '#register') {
        checkout_form_toggle(true);
      }

      $('#checkout_login').on('click', function(){ checkout_form_toggle(false); });
      $('#checkout_register').on('click', function(){ checkout_form_toggle(true); });
    })();

    // ===== Product options -> price increments & image swapping =====
    (function initOptionPricing(){
      if(!$('#ptp').length || !$('[name^=productOptions]').length) return;

      price_inc_options();

      $(document).on('change', '[name^=productOptions]', function(){
        price_inc_options();

        var product_image = '';
        var $t = $(this);
        if ($t.is('input:radio, input:checkbox, input:hidden')) {
          product_image = $t.is(':checked') ? $t.data('image') : '';
        } else if ($t.is('select')) {
          product_image = $('option:selected', this).data('image');
        }

        if (product_image && product_image.length > 0) {
          if($('a.MagicZoom').length){
            var zoomNode = $('a.MagicZoom').attr('id');
            // swap cached/source forms
            MagicZoom.update(
              zoomNode,
              product_image.replace(".500.", ".").replace("/cache/", "/source/"),
              product_image
            );
          } else {
            $('img#img-preview').attr('src', product_image);
          }
        }
      });
    })();

    // ===== Newsletter reCAPTCHA reveal =====
    $('#newsletter_email').on('focus', function(){ $('#newsletter_recaptcha').slideDown(); });

    // ===== Foundation responsive specifics =====
    if(Foundation.utils.is_small_only()) {
      grid_view(0);
      $('#content_checkout_medium_up').remove();
      $("[checked]").prop("checked", true);
    }
    if(Foundation.utils.is_medium_up()) {
      $('#content_checkout_small').remove();
      $("[checked]").prop("checked", true);
    }

    // ===== Init add-to-basket validation on page load =====
    init_add_to_basket();
  });

  // ---------- Functions used globally / elsewhere ----------

  function init_add_to_basket() {
    $("form.add_to_basket").each(function(_, el){
      $(el).validate({
        submitHandler: function(form) { add_to_basket(form); }
      });
    });
  }

  function price_inc_options() {
    var $form = $('form.add_to_basket');
    if(!$form.length) return;

    var action = $form.attr('action');
    var total = 0;
    var ptp = parseFloat($('#ptp').attr("data-price"));
    var fbp = $('#fbp').length ? parseFloat($('#fbp').attr("data-price")) : null;

    var urlBase = action + (action.indexOf('?') > -1 ? '&' : '?') + '_g=ajax_price_format&price[0]=';

    $("[name^=productOptions]").each(function(){
      var $t = $(this);
      if($t.is('input:radio') && $t.is(':checked')) {
        if($t.hasClass('absolute')) { total -= ptp; }
        total += parseFloat($t.attr("data-price"));
      } else if ($t.is('select') && $t.val()) {
        var $opt = $("option:selected", $t);
        if($opt.hasClass('absolute')) { total -= ptp; }
        total += parseFloat($opt.attr("data-price"));
      } else if (($t.is('textarea') || $t.is('input:text')) && $t.val() !== '') {
        if($t.hasClass('absolute')) { total -= ptp; }
        total += parseFloat($t.attr("data-price"));
      }
    });

    ptp = ptp + total;

    if(fbp !== null) {
      fbp = fbp + total;
      $.ajax({
        url: urlBase + ptp + '&price[1]='+ fbp,
        cache: true
      }).done(function(resp){
        var prices = $.parseJSON(resp);
        $('#ptp').html(prices[0]);
        $('#fbp').html(prices[1]);

        // Keep sale price styling logic identical to original intent
        var absolute = false; // not used elsewhere, preserved for logic parity
        if (absolute && prices[0] <= prices[1]) {
          $('#fbp').hide();
          $('#ptp').removeClass('sale_price');
        } else {
          $('#fbp').show();
          $('#ptp').addClass('sale_price');
        }
      });
    } else {
      $.ajax({
        url: urlBase + ptp,
        cache: true
      }).done(function(resp){
        var prices = $.parseJSON(resp);
        $('#ptp').html(prices[0]);
      });
    }
  }

  function add_to_basket(form) {
    var add = $(form).serialize();
    var action = $(form).attr('action').replace(/\?.*/, '');
    var on_canvas_basket_content = '';
    var url = action + (action.indexOf('?')> -1 ? '&' : '?') + '_g=ajaxadd&t=' + new Date().getTime();

    $.ajax({
      url: url,
      type: 'POST',
      cache: false,
      data: add
    }).done(function(resp){
      if (/Redir:/.test(resp)) {
        var redir = resp.split('Redir:')[1];
        window.location = redir;
      } else {
        $('#mini-basket').replaceWith(resp);
        on_canvas_basket_content = $('#mini-basket .box-basket-content').html() || '';
        $(".right-off-canvas-menu .box-basket-content").html(on_canvas_basket_content);
        $(".alert-box").slideUp();
        mini_basket_action();
      }
    });

    return false;
  }

  function checkout_form_toggle(register) {
    if (register) {
      $("#checkout_login_form").hide();
      $("#checkout_register_form").slideDown();
      $("#reg_password").prop('disabled', false);
      $("#login-username, #login-password").prop('disabled', true);
      $("#checkout_login_btn").prop('disabled', true);
      $('#checkout_form').attr("action", '#register');
    } else {
      $("#checkout_login_form").slideDown();
      $("#checkout_register_form").hide();
      $("#reg_password").prop('disabled', true);
      $("#login-username, #login-password").prop('disabled', false);
      $("#checkout_login_btn").prop('disabled', false);
      $('#checkout_form').attr("action", '#login');
    }
  }

  function set_product_view(delay) {
    if ($.cookie('product_view') === 'grid') {
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
      if($('#basket-detail').height() > $(window).height()) {
        window.location.href = '?_a=basket';
      }
    });
  }

  function update_quantity(rel, sign) {
    var $target = $('input[name="quan[' + rel + ']"]');
    var $quick = $('#quick_update_' + rel);
    var original_val = parseInt($('#original_val_' + rel).text(), 10);
    var old_val = parseInt($target.val(), 10) || 0;
    var new_val = old_val;

    if (sign === '+') {
      if (old_val >= 999) return false;
      new_val = old_val + 1;
    } else if (sign === '-') {
      if (old_val < 1) return false;
      new_val = old_val - 1;
    }

    $target.val(new_val);
    $('span.disp_quan_' + rel).text(new_val);

    if (original_val === new_val) $quick.slideUp(); else $quick.slideDown();

    if (!$("#checkout_login_form")[0]) {
      $('#checkout_form').attr("action", '#basket_item_' + rel);
    }
    return false;
  }

  // Keep API identical for external callers:
  window.checkout_form_toggle = checkout_form_toggle;
  window.init_add_to_basket   = init_add_to_basket;
  window.price_inc_options    = price_inc_options;
  window.add_to_basket        = add_to_basket;
  window.set_product_view     = set_product_view;
  window.show_address_form    = show_address_form;
  window.mini_basket_action   = mini_basket_action;
  window.update_quantity      = update_quantity;

  // Zone/state validation helper (preserved logic)
  window.stateRequirements = function(zone_status, form_id, target, change) {
    var required = false, disabled = false;
    switch(zone_status){
      case '1': required = true; $(target+"_wrapper").show(); break; // Required
      case '2': $(target+"_wrapper").show(); break;                   // Optional
      case '3': disabled = true; $(target+"_wrapper").hide(); break;  // Hidden
    }
    $(target).prop('disabled', disabled);
    if (change) {
      $(target).rules("add", { required: required });
      $(form_id).validate();
    }
    return required;
  };

})(jQuery, window, document);