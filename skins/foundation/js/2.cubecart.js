;var validation_ini = {};

jQuery(function ($) {
  const DURATION = 500;

  // -------------------------------
  // Small utility helpers
  // -------------------------------
  const delay = (fn, ms = 0) => {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(this, args), ms);
    };
  };

  const matchCase = (text, pattern) =>
    [...text].map((c, i) =>
      pattern.charCodeAt(i) >= 65 && pattern.charCodeAt(i) < 91 ? c.toUpperCase() : c.toLowerCase()
    ).join("");

  const toBold = text => text.replace(/\*(.*?)\*/gm, "<strong>$1</strong>");

  const toggleEl = (sel, show) => $(sel).toggle(!!show);

  // -------------------------------
  // ACP widget toggle
  // -------------------------------
  function showAcpWidget(show, change) {
    const open = (show == 0 && change) || (show == 1 && !change);
    $('.acp_widget').css("left", open ? "0" : "-123px");
    $('.acp_widget .close a').html(open ? "&laquo;" : "&raquo;");
    return open ? 1 : 0;
  }
  $(".acp_widget .close a").on("click", e => {
    e.preventDefault();
    $.cookie("show_acp_widget", showAcpWidget($.cookie("show_acp_widget"), true), { expires: 30 });
  });
  showAcpWidget($.cookie("show_acp_widget"), false);

  // -------------------------------
  // Inputs
  // -------------------------------
  $("#coupon").on("input", function () {
    $("#apply_coupon").toggleClass("animate", !!this.value);
  });
  $(".nopaste").on("cut copy paste", e => e.preventDefault());

  // -------------------------------
  // Reviews (Gravatar + show/hide)
  // -------------------------------
  $("#element-reviews .review_row").each(function () {
    const avatarId = $(this).attr("rel");
    const hash = avatarId.split("_")[1];
    const imgUrl = `https://gravatar.com/avatar/${hash}?s=90&d=mp`;
    $.ajax({ url: imgUrl, type: "HEAD", crossDomain: true })
      .done(() => $(`#${avatarId}`).attr("src", imgUrl));
  });
  $(".review_hide").on("click", () => { $("#review_read").show(); $("#review_write").slideUp(); return false; });
  $(".review_show").on("click", () => { $("#review_read").hide(); $("#review_write").slideDown(); return false; });

  // -------------------------------
  // Open-clearing preview sizing
  // -------------------------------
  $("a.open-clearing img#img-preview").on("load", function () {
    const ip = $(this), h = ip.height(), w = ip.width();
    const minH = Math.max(h, w * 0.7);
    $("a.open-clearing img").css("max-height", `${minH}px`);
    $("#open-clearing-wrapper").css({ "min-height": `${minH}px`, "max-height": `${minH}px` });
  });

  // -------------------------------
  // Small scroller
  // -------------------------------
  if ($("#scrollContent").length) {
    let scrolling = false;
    const scrollContent = dir => {
      $("#scrollContent").animate({ scrollTop: (dir === "up" ? "-=1px" : "+=1px") }, 1, () => {
        if (scrolling) scrollContent(dir);
      });
    };
    if ($("#scrollContent")[0].scrollHeight > $("#scrollContent")[0].offsetHeight) $(".scroller").show();
    $("#scrollUp").hover(() => { scrolling = true; scrollContent("up"); }, () => { scrolling = false; });
    $("#scrollDown").hover(() => { scrolling = true; scrollContent("down"); $("#scrollUp .icon").show(); }, () => { scrolling = false; });
  }

  // -------------------------------
  // Foundation reveal (colorbox swap)
  // -------------------------------
  const $cb = $(".gateway_wrapper .colorbox");
  if ($cb.length) {
    const href = $cb.attr("href"), title = $cb.attr("title");
    $cb.attr({ href: "#", "data-reveal-id": "colorbox" })
      .after(`<div id="colorbox" data-reveal aria-labelledby="${title}" aria-hidden="true" role="dialog" class="reveal-modal tiny"><h3>${title}</h3><img src="${href}"><a class="close-reveal-modal">&#215;</a></div>`);
    $cb.on("click", () => $("#colorbox").foundation("reveal", "open"));
  }

  // -------------------------------
  // EU cookie consent
  // -------------------------------
  $(".eu_cookie_button").on("click", function () {
    const accept = this.name === "accept_cookies_submit";
    if (this.dataset.alertText) alert(this.dataset.alertText);
    $("#eu_cookie_dialogue").slideUp();
    $.cookie("accept_cookies", accept, { expires: 365 });
    $.get(`?_g=ajax_cookie_consent&accept=${accept ? 1 : 0}`);
    return false;
  });

  // -------------------------------
  // General nav + autosubmit
  // -------------------------------
  $(".top-bar label").on("click", function () { if (this.rel) location.href = this.rel; });
  $(".autosubmit").on("change", "select, input[type=radio]:not(.nosubmit)", function () { $(this).closest("form").submit(); });
  $(".icon-submit").each(function () { $(this).closest("form").submit(); });
  $(".category-nav li").has("ul").end().removeClass("has-dropdown");

  // -------------------------------
  // Search toggles
  // -------------------------------
  $(".show-small-search").on("click", () => { $("#small-search").slideToggle().find(".search_input").focus(); });
  $("#show-small-search").on("click", () => $("#small-search").slideDown());
  $(".hide_skin_selector").on("click", e => { e.preventDefault(); $(".skin_selector").fadeOut(); });

  // -------------------------------
  // Image gallery swap
  // -------------------------------
  $(".image-gallery").hover(function () {
    $("#img-preview").attr({
      src: $(this).data("image-swap"),
      title: $(this).attr("title"),
      alt: $(this).attr("alt")
    });
  });
  $(".open-clearing").on("click", e => { e.preventDefault(); $("[data-clearing] li img").eq($(e.currentTarget).data("thumb-index")).trigger("click"); });

  // -------------------------------
  // Basket + checkout
  // -------------------------------
  $("body").on("click", "#basket-summary", mini_basket_action);
  $("a.quan").on("click", function () { return update_quantity($(this).attr("rel"), $(this).hasClass("add") ? "+" : "-"); });
  $("#checkout_proceed").on("click", () => $("<input>", { type: "hidden", name: "proceed", value: "1" }).appendTo("#checkout_form"));

  // -------------------------------
  // Country/state dynamic menus
  // -------------------------------
  const buildStateSelect = (target, items, placeholder) => {
    const $target = $(target), setting = $target.val();
    const $select = $("<select>", { id: $target.attr("id"), name: $target.attr("name"), class: $target.attr("class") }).insertAfter($target).val(setting);
    $target.remove();
    if (placeholder) $("<option>").text(placeholder).appendTo($select);
    items.forEach(({ id, name }) =>
      $("<option>").val(id > 0 ? id : "").text(name)
        .prop("selected", setting && (setting.toLowerCase() === name.toLowerCase() || setting == id))
        .appendTo($select)
    );
  };
  const buildStateInput = (target, placeholder, disabled) => {
    const $target = $(target);
    $("<input>", {
      type: "text", placeholder, id: $target.attr("id"), name: $target.attr("name"),
      class: $target.attr("class"), required: $target.attr("required")
    }).prop("disabled", disabled).val(disabled ? placeholder : "").insertAfter($target);
    $target.remove();
  };

  const handleCountryState = ($select, change) => {
    if (typeof county_list !== "object") return;
    const country = $select.val(), list = county_list[country];
    const target = ($select.attr("rel") && $select.attr("id") !== "country-list") ? "#" + $select.attr("rel") : "#state-list";
    const zoneStatus = $("option:selected", $select).data("status");
    const formId = $select.closest("form").attr("id");
    validation_ini[target] = stateRequirements(zoneStatus, formId, target, change);

    if (Array.isArray(list) && list.length) buildStateSelect(target, list, $select.attr("title"));
    else {
      const disabled = $select.hasClass("no-custom-zone");
      const placeholder = disabled ? $select.attr("title") : `${$("label[for='" + $select.attr("rel") + "']").text()} ${$("#validate_required").text()}`;
      buildStateInput(target, placeholder, disabled);
    }
  };

  $("select#country-list, select.country-list").each(function () { handleCountryState($(this), false); })
    .on("change", function () { handleCountryState($(this), true); });

  // -------------------------------
  // Checkout + register toggles
  // -------------------------------
  $(".show_address_form").on("click", show_address_form);
  if ($("div.alert").length) show_address_form();
  const toggleDelivery = () => toggleEl("#address_delivery", !$("#delivery_is_billing").prop("checked"));
  $("#delivery_is_billing").on("change", toggleDelivery); if ($("#delivery_is_billing").length) toggleDelivery();
  if (!$("#show-reg").prop("checked")) $("#account-reg").hide();
  $("#show-reg").on("change", function () {
    $("#account-reg").toggle(this.checked);
    $("#reg_password,#reg_passconf").toggleClass("required", this.checked);
  });

  // -------------------------------
  // Grid/list view
  // -------------------------------
  $(".grid_view").on("click", e => grid_view(200, e));
  $(".list_view").on("click", e => list_view(200, e));
  set_product_view(0);

  // -------------------------------
  // URL select redirect
  // -------------------------------
  $(".url_select").on("change", function () { if (this.value) location = this.value; return false; });

  // -------------------------------
  // SAYT
  // -------------------------------
  const sayt = $(window).width() < 640 ? $("#small-search .search_input") : $(".search_input");
  sayt.on("click", () => $.removeCookie("ccScroll"));
  sayt.on("keyup", delay(saytGo, sayt.hasClass("es") ? 0 : 500));
  $(document).on("keyup", e => { if (e.key === "Escape") { sayt.val(""); $("#sayt_results li").remove(); } });

  function saytGo() {
    if (!sayt.hasClass("es")) return;
    const term = sayt.val();
    if (!$("#sayt_results").length) $("<ul id='sayt_results'>").insertAfter(sayt);
    if (!term) return $("#sayt_results").empty();
    $.getJSON(`?_e=es&q=${term}&a=${sayt.data("amount")}`, products => {
      $("#sayt_results").empty();
      if (!Array.isArray(products)) return $("#sayt_results").append("<li class='status'>No results found</li>");
      const terms = term.split(" ").filter(Boolean);
      products.forEach(p => {
        let name = p.name;
        terms.forEach(t => name = name.replace(new RegExp(`(${t})`, "ig"), m => "*" + matchCase(t, m) + "*"));
        const img = sayt.data("image") && p.thumbnail ? `<span><img src="${p.thumbnail}" title="${p.name}"></span>` : "<span>&nbsp;</span>";
        $("#sayt_results").append(`<li><a href='?_a=product&product_id=${p.product_id}'>${img}${toBold(name)}</a></li>`);
      });
    });
  }

  // -------------------------------
  // Back to top
  // -------------------------------
  if ($(window).width() < 640) {
    $(window).on("scroll", () => $(".back-to-top").fadeToggle(DURATION, $(window).scrollTop() > 400));
  }
  $(".back-to-top").on("click", e => { e.preventDefault(); $("html,body").animate({ scrollTop: 0 }, DURATION); });

  // -------------------------------
  // Checkout login/register hash
  // -------------------------------
  if ($("#checkout_form").length) {
    const hash = location.hash;
    if (hash === "#login") checkout_form_toggle(false);
    if (hash === "#register") checkout_form_toggle(true);
    $("#checkout_login").on("click", () => checkout_form_toggle(false));
    $("#checkout_register").on("click", () => checkout_form_toggle(true));
  }

  // -------------------------------
  // Product option price/image
  // -------------------------------
  if ($("#ptp").length && $("[name^=productOptions]").length) {
    price_inc_options();
    $("[name^=productOptions]").on("change", function () {
      price_inc_options();
      const img = $(this).is(":checked,select") ? ($(this).is("select") ? $("option:selected", this).data("image") : $(this).data("image")) : "";
      if (img) {
        if ($("a.MagicZoom").length) {
          const id = $("a.MagicZoom").attr("id");
          MagicZoom.update(id, img.replace(".500.", ".").replace("/cache/", "/source/"), img);
        } else {
          $("#img-preview").attr("src", img);
        }
      }
    });
  }

  // -------------------------------
  // Newsletter recaptcha
  // -------------------------------
  $("#newsletter_email").on("focus", () => $("#newsletter_recaptcha").slideDown());

  // -------------------------------
  // Foundation responsive tweaks
  // -------------------------------
  if (Foundation?.utils?.is_small_only?.()) { grid_view(0); $("#content_checkout_medium_up").remove(); $("[checked]").prop("checked", true); }
  if (Foundation?.utils?.is_medium_up?.()) { $("#content_checkout_small").remove(); $("[checked]").prop("checked", true); }
});