if (!String.prototype.startsWith) {
  String.prototype.startsWith = function(searchString, position) {
    position = position || 0;
    return this.indexOf(searchString, position) === position;
  };
}

function updateStriping() {
    $(".list-even,.list-odd").removeClass("list-even list-odd"), $(".list,.reorder-list").find(">div,tbody>tr").hover(function() {
        $(this).addClass("list-hover")
    }, function() {
        $(this).removeClass("list-hover")
    }), $(".list,.reorder-list").find(">div:nth-child(even),tbody>tr:nth-child(even)").addClass("list-even"), $(".list,.reorder-list").find(">div:nth-child(odd),tbody>tr:nth-child(odd)").addClass("list-odd")
}

function pageChanged(t) {
    var e = $(t).parents("form:first");
    if (1 == e.length) {
        if (void 0 !== e.attr("title")) var i = e.attr("title"),
            a = i.length > 1 ? i : "";
        e.hasClass("no-change") || (window.onbeforeunload = function() {
            return a
        })
    }
}

function removeVariableFromURL(t, e) {
    var i = String(t),
        a = new RegExp("\\?" + e + "=[^&]*&?", "gi");
    return i = i.replace(a, "?"), a = new RegExp("\\&" + e + "=[^&]*&?", "gi"), i = i.replace(a, "&"), i = i.replace(/(\?|&)$/, ""), a = null, i
}

function updateAddressValues(t, e, i) {
    "country" == e ? ($("#" + t + "_" + e + " option").filter(function() {
        if($(this).text()==i[e]) {
            $("#" + t + "_" + e).val($(this).val());
            return;
        }
    }).first().attr("selected", "selected"), $("#" + t + "_" + e).trigger("change"), !$("#" + t + "_state").is("select") ? $("#" + t + "_state").val(i.state) : $("#" + t + "_state option").filter(function() {
        if(i.state == $(this).text()) {
            $("#" + t + "_" + "state").val($(this).val());
            return;
        }
    }).attr("selected", "selected")) : "state" != e && $("#" + t + "_" + e).val(i[e])
}

function inlineRemove(t) {
    var e = $(t).attr("title"),
        i = $(t).attr("rel"),
        a = ($(t).attr("href"), $(t).attr("name"));
    if ("" != e && !confirm(e)) return !1;
    if (i && !$(t).hasClass("dynamic")) {
        var n = document.createElement("input");
        $(n).attr({
            type: "hidden",
            name: a + "[]"
        }).val(i), $(t).parents("form:first").append(n)
    } else pageChanged(t);
    var s = $(t).parents("tr:first,div:first:not(.tab_content)").get(0);
    return $(s).remove(), updateStriping(), !1
}

function optionAdd(t, e) {
    var e = $("#" + e),
        t = $("#" + t),
        a = $("#opt_mid :selected").parent().attr("label"),
        n = $("#opt_mid :selected").text(),
        s = "undefined" == typeof a ? n : "<strong>" + a + "</strong>: " + n,
        r = $("#opt_mid").val();
    if ("" != r && 0 != r) {
        var o = $(t).clone();
        $(o).find(".name").append(s).find("input:first").val(r).removeAttr("disabled");
        var l = $("input.data");
        for (i = 0; i < l.length; i++) {
            var c = $(l[i]).attr("rel"),
                d = "" == $(l[i]).val() ? "0" : $(l[i]).val(),
                h = $(o).find("." + c).find("input:first");
            "matrix_include" == c ? h.attr("name", "option_add[" + c + "][" + options_added + "]") : "set_enabled" == c ? (h.removeAttr("disabled"), h.attr("checked", "checked"), h.parent().addClass("selected"), h.val(1), 1 == d && (h.parent().addClass("selected"), h.attr("checked", "checked")), h.attr("name", "option_add[" + c + "][" + options_added + "]")) : "default" == c || "negative" == c || "absolute_price" == c ? (h.removeAttr("disabled"), $(l[i]).is(":checked") && (h.parent().addClass("selected"), h.attr("checked", "checked"), $(l[i]).removeAttr("checked").parent().removeClass("selected")), h.attr("name", "option_add[" + c + "][" + options_added + "]")) : (d = parseFloat(d, 10).toFixed(2), $(o).find("." + c).append(d).find("input:first").val(parseFloat(d)).removeAttr("disabled")), $(l[i]).val("")
        }
        $(o).find("a.remove").on("click", function() {
            inlineRemove(this)
        }), $(o).removeAttr("id"), $("#opt_mid :selected").removeAttr("selected"), $("#opt_mid:first-child").attr("selected", "selected"), $(e).append($(o)), options_added++
    }
    return !1
}

function ajaxSelected(t, e, i) {
    var a = $("#val_admin_file").text();
    switch ($("#result_" + e).val(t.id), i.toLowerCase()) {
        case "user":
            $.getJSON("./" + a, {
                _g: "xml",
                type: "address",
                q: t.id,
                function: "search"
            }, function(t) {
                $("select.address-list>option.temporary").remove();
                for (var e = 0; e < t.length; e++) {
                    var i = document.createElement("option");
                    $(i).val(e), $(i).html(t[e].description), $(i).addClass("temporary"), $(".address-list").append(i)
                }
                addresses = t
            });
            break;
        case "product":
            $("#add-price").val(t.data.price), $("#add-subtotal").html(($("#add-quantity").val() * t.data.price).toFixed(2)), data = t.data
    }
    for (key in t.data) "" != t.data[key] && $("#ajax_" + key).val(t.data[key]).trigger("change");
    $("#result_" + e).hasClass("clickSubmit") && $("#result_" + e).closest("form").submit()
}

function ajaxSuggest(t, e, i) {
    var a = "./" + $("#val_admin_file").text(),
        n = {
            _g: "xml",
            type: i,
            q: t,
            function: "search"
        };
    $.get(a, n, function(t) {
        for (var i = [], a = 0; a < t.length; a++) i.push({
            id: t[a].value,
            value: t[a].display,
            info: t[a].info,
            data: t[a].data
        });
        e(i)
    }, "json")
}

function ajaxNewsletter(t, e) {
    var i = $("#val_admin_file").text();
    $.getJSON("./" + i, {
        _g: "xml",
        type: "newsletter",
        q: t,
        page: e,
        function: "search"
    }, function(i) {
        if (typeof i.error !== 'undefined' && i.error=='true') {
            window.location.href = '?_g=customers&node=email';
            return false;
        }
        $("div#progress_bar").css({
            width: i.percent + "%"
        }), $("div#progress_bar_percent").text(Math.round(i.percent) + "%"), 100 == i.percent || "true" == i.complete ? (window.onbeforeunload = null, setTimeout(function(){ window.location = "?_g=customers&node=email"; }, 2000)) : ajaxNewsletter(t, e + 1)
    })
}

function updateOrderTotals(t) {
    t.hasClass("quantity") || t.val((1 * $(this).val()).toFixed(2));
    var e = t.parents(".update-subtotal:first"),
        i = $(e).find("input.quantity").val(),
        a = $(e).find("input.lineprice").val(),
        n = $(e).find("input.subtotal:first"),
        s = (i * a).toFixed(2);
    $(n).val(s);
    var r = 0;
    $("input.subtotal").each(function() {
        var t = 1 * $(this).val();
        r += t
    });
    var o = $("#discount").val();
    o = 1 * o;
    var l = $("#discount_type").val();
    "p" == l ? (o > 100 && ($("#discount").val("100"), o = 100), o = o / 100 * r, $("#discount_percent").html("%")) : $("#discount_percent").html(""), $("#subtotal").val(r.toFixed(2));
    var c = $("#shipping").val(),
        d = 0;
    $(".update-subtotal input.tax").each(function() {
        var t = $(this).val();
        d += 1 * t
    });
    var h = 1 * r - o + 1 * c + 1 * d;
    $("#total_tax").val(d.toFixed(2)), $("#total").val(h.toFixed(2))
}

function productOptionPrices(t) {
    var e = $("#" + t + "_price"),
        i = +e.attr("original"),
        a = "";
    return $("span[rel=" + t + "] select").each(function() {
        option_price = $(this).find("option:selected").attr("rel"), option_price && (a = option_price.substr(0, 1), value = +option_price.substr(1), value > 0 && ("+" == a ? i = +i + +value : "-" == a && (i = +i - +value)))
    }), $("span[rel=" + t + "] input, span[rel=" + t + "] textarea").each(function() {
        var t = $(this).val(),
            e = $(this).attr("rel");
        "" != t && e && (a = e.substr(0, 1), value = +e.substr(1), value > 0 && ("+" == a ? i = +i + +value : "-" == a && (i = +i - +value)))
    }), e.val(i.toFixed(2)), $(".update-subtotal input.number").trigger("change"), !1
}
$(document).ready(function() {

    setTimeout(function() {
        window.scrollTo(-81, 0)
    }, 1);

    $('.chzn-select').chosen({width:"50%",search_contains:true});

    var t = !1;
    if (jQuery.debug = function(t) {
            window.console ? console.debug("CubeCart: " + t) : alert(t)
        }, jQuery.fn.insertAtCaret = function(t) {
            return this.each(function() {
                if (document.selection) this.focus(), sel = document.selection.createRange(), sel.text = t, this.focus();
                else if (this.selectionStart || "0" == this.selectionStart) {
                    var e = this.selectionStart,
                        i = this.selectionEnd,
                        a = this.scrollTop;
                    this.value = this.value.substring(0, e) + t + this.value.substring(i, this.value.length), this.focus(), this.selectionStart = e + t.length, this.selectionEnd = e + t.length, this.scrollTop = a
                } else this.value += t, this.focus()
            })
        }, jQuery.fn.confirmPassword = function(t) {
            var e = jQuery.extend({
                updateOn: "keyup"
            }, t);
            this.bind(e.updateOn, function() {
                if (jQuery(this).removeClass("ps-match ps-nomatch error"), "" != jQuery(this).val()) {
                    var t = jQuery(this).attr("rel");
                    jQuery(this).addClass(jQuery("#" + t).val() === jQuery(this).val() && "" != jQuery(this).val() ? "ps-match" : "ps-nomatch error")
                }
            })
        }, jQuery.fn.exists = function() {
            return 0 != jQuery(this).length
        }, top.location.href != self.location.href && (top.location = self.location.href), $("input:text,input:password,textarea").each(function() {
            var t = $(this).attr("title");
            "undefined" != typeof t && t.length >= 1 && ("" == $(this).val() && $(this).val(t), $(this).focus(function() {
                $(this).val() == t && $(this).val("")
            }).blur(function() {
                "" == $(this).val() && $(this).val(t)
            }).parents("form:first").submit(function() {
                $("input:text,input:password,textarea", this).each(function() {
                    $(this).val() == t && $(this).val("")
                })
            }))
        }), $(":input, :input:hidden").each(function() {
            $(this).hasClass("original-fix") || $(this).attr("original", $(this).val())
        }).change(function() {
            pageChanged(this)
        }), $("input:submit.update").click(function() {
            $("select.required").removeClass("required")
        }), $("select.update_form").change(function() {
            $("input.required").removeClass("required"), $(this).parents("form").submit()
        }), $("form").submit(function() {
            var e = !0;
            if (t = !1, $("#inventory-list").exists() && !$("input[name*=inv]").exists()) return $(".inline-add:first").addClass("highlight"), !1;
            $(".required-error").removeClass("required-error"), $(":checkbox.ignore").each(function() {
                $(this).not(":checked") && $(this).attr("disabled", "disabled")
            });
            var i = $($("div.tab_content").exists() ? "div.tab_content:visible" : this);
            if ($(i).find(".required:input:not(:hidden)").each(function() {
                    var t = $(this).val();
                    if ($(this).attr("original"), "" == t.replace(/\s/i, "")) {
                        var i = $(this).attr("id");
                        $(this).addClass("required-error").change(function() {
                            $(this).val() != $(this).attr("original") && ($(this).removeClass("required-error"), $("#error_" + i + ".error").hide("fast"))
                        }), $("#error_" + i + ".error").show("fast"), e = !1
                    }
                }), $(".inline-add:input").each(function() {
                    $(this).hasClass("not-empty"), $(this).val() != $(this).attr("original") && ($(this).parents(".inline-add:first").addClass("highlight"), e = !1)
                }), $(i).find("select.required:not(:hidden)").each(function() {
                    if (0 == $(this).val()) {
                        var t = $(this).attr("id");
                        $(this).addClass("required-error").change(function() {
                            $(this).val() != $(this).attr("original") && ($(this).removeClass("required-error"), $("#error_" + t + ".error").hide("fast"))
                        }), $("#error_" + t + ".error").show("fast"), e = !1
                    }
                }), e) return window.onbeforeunload = null, !0;
            var a = $(".required-error:first"),
                n = a.position();
            return $("html, body").animate({
                scrollTop: n.top - 50
            }, "slow"), t = !0, !1
        }), $(".check-all").click(function(t) {
            $(this).is("a") && t.preventDefault();
            var e = $(this).attr("rel"),
                i = $("input[type=checkbox]." + e);
            i.prop("checked", !i.prop("checked"))
        }), $("select.auto_submit").each(function() {
            $(this).hasClass("show_submit") || $(this).parents("form:first").find("input:submit").hide()
        }).change(function() {
            $(this).parents("form:first").submit()
        }), $(".insert-text").on("click", function() {
            var t = "#" + $(this).attr("target"),
                e = $(this).text();
            return $(t).insertAtCaret(e), !1
        }), $("img.autosubmit").each(function() {
            if ($(this).hasClass("form-name")) {
                var t = $(this).attr("rel");
                $("form#" + t).submit()
            } else $(this).parents("form").submit()
        }), $("#navigation div.menu").click(function() {
            var t = $(this).attr("id");
            $("#menu_" + t).toggle("fast", function() {
                var e = $(this).is(":visible");
                $.cookie("nav_" + t, e), e ? $("#" + t + " i").addClass("fa-minus-square-o").removeClass("fa-plus-square-o") : $("#" + t + " i").removeClass("fa-minus-square-o").addClass("fa-plus-square-o")
            })
        }), $(".duplicate").click(function() {
            $(this).attr("rel")
        }), "undefined" != typeof gui_message_json && "object" == typeof gui_message_json)
        for (var e in gui_message_json) $("#" + e).addClass("required-error").val("");

        $("#bulk_price_method").change(function() {
            if($(this).val()=='percent') {
                $("#bulk_price_action").hide().attr('disabled', true);
                $("#bulk_price_percent_symbol").show();
            } else {
                $("#bulk_price_action").show().attr('disabled', false);
                $("#bulk_price_percent_symbol").hide();
            }
        });

    $("#email_method").change(function() {
        if($(this).val()=='mail') {
            $("#smtp_settings").slideUp();
        } else {
            $("#smtp_settings").slideDown();
        }
    });

    $(".getFileSize").on("click",function() {
        var i = $("#val_admin_file").text();
        var time_out_text = $("#val_time_out_text").text();
        var parent = $(this).parent();
        var path = $(this).attr("data-path");

        time_out_text = time_out_text.replace("%s", "30");
        parent.html('<i class="fa fa-spinner fa-spin fa-fw"></i>');

        $.ajax({
            dataType: "json",
            url: "./" + i,
            data: {
                _g: "xml",
                path: path,
                function: "filesize"
            },
            success: function(r) {
                parent.html(r);
            },
            timeout: 30000
        }).fail( function(xhr, status ) {
            if(status == "timeout") {
                parent.html(time_out_text);
            }
        });
    });

    $("#bulk_price_target").change(function() {
        if($(this).val()=='categories') {
            $("#bulk_update_categories").show();
            $("#bulk_update_products").hide();
        } else {
            $("#bulk_update_categories").hide();
            $("#bulk_update_products").show();
        }
        $('input:checkbox').removeAttr('checked');
        $('.custom-checkbox').removeClass('selected');
    });
    if($("div.cc_dropzone").length) {
        var cc_dropzone_url = $("div#cc_dropzone_url").text();
        $("div.cc_dropzone").dropzone({url: cc_dropzone_url, maxFilesize: '0.35', init: function () {
                this.on("complete", function (file) {
                    if($("div#imageset.fm-filelist").length) {
                        if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                            var t = $("#val_admin_file").text();
                            $("div#imageset.fm-filelist").fileTree({
                                root: "/",
                                script: "./" + t,
                                group: '1',
                                name: 'imageset',
                                unique: false
                            });
                        }
                    }
                });
            }
        });
    }

    $(":input.required").blur(function() {
        $(this).attr("id"), "" == $(this).val().replace(/\s/i, "") ? $(this).addClass("required-error") : $(this).removeClass("required-error")
    }), $("select.certificate-delivery").change(function() {
        "m" == $(this).val() ? $("#gc-method-e").slideUp().find("input").removeClass("required") : $("#gc-method-e").slideDown().find("input").addClass("required")
    }), $("select#country-list, select.country-list").each(function() {
        if ("object" == typeof county_list) {
            var t = county_list[$(this).val()],
                e = $(this).attr("rel") && "country-list" != $(this).attr("id") ? "#" + $(this).attr("rel") : "#state-list";
            if ("object" == typeof t) {
                var a = $(e).val(),
                    n = document.createElement("select");
                if ($(e).replaceWith($(n).attr({
                        name: $(e).attr("name"),
                        id: $(e).attr("id"),
                        "class": $(e).attr("class")
                    })), $(this).attr("title")) {
                    var s = document.createElement("option");
                    $("select" + e).append($(s).val("0").text($(this).attr("title")))
                }
                for (i in t) {
                    var s = document.createElement("option");
                    $("select" + e).append(a == t[i].name || a == t[i].id ? $(s).val(t[i].id).text(t[i].name).attr("selected", "selected") : $(s).val(t[i].id).text(t[i].name))
                }
            } else $(this).hasClass("no-custom-zone") && $(e).attr({
                disabled: "disabled"
            }).val($(this).attr("title"))
        }
    }).change(function() {
        if ("object" == typeof county_list) {
            var t = county_list[$(this).val()],
                e = $(this).attr("rel") && "country-list" != $(this).attr("id") ? "#" + $(this).attr("rel") : "#state-list";
            if ("object" == typeof t && "undefined" != typeof county_list[$(this).val()] && county_list[$(this).val()].length >= 1) {
                var i = $(e).val(),
                    a = document.createElement("select");
                if ($(e).replaceWith($(a).attr({
                        name: $(e).attr("name"),
                        id: $(e).attr("id"),
                        "class": $(e).attr("class")
                    })), $(this).attr("title")) {
                    var n = document.createElement("option");
                    $("select" + e).append($(n).val("0").text($(this).attr("title")))
                }
                for (var s = 0; s < t.length; s++) {
                    var n = document.createElement("option");
                    $("select" + e).append($(n).val(t[s].id).text(t[s].name))
                }
                if(i) {
                    $("select" + e + " > option[value=" + i + "]").attr("selected", "selected");
                }
            } else {
                var r = document.createElement("input"),
                    o = $(r).attr({
                        type: "text",
                        id: $(e).attr("id"),
                        name: $(e).attr("name"),
                        "class": $(e).attr("class")
                    });
                $(this).hasClass("no-custom-zone") && $(o).attr("disabled", "disabled").val($(this).attr("title")), $(e).replaceWith($(o))
            }
        }
    }), $("input[type=radio].rating").rating({
        required: !0
    }), updateStriping(".list,table,.reorder-list"), $("a.preview").click(function() {
        return $("#img-preview").attr("src", $(this).attr("href")), !1
    }), $("a.delete, a.confirm, .submit_confirm, .install_confirm").click(function() {
        var t = $(this).attr("title");
        return "" != t ? confirm(t.replace(/\\n/gi, "\n")) : void 0
    }), $("input:password.strength").pstrength(), $("input:password.confirm").confirmPassword(), $(".sublist").hide(), $(".list-master").click(function() {
        $("#" + $(this).attr("rel")).toggle()
    }), $(".contentswitch:not(:input)").hide(), $(".contentswitch:input").click(function() {
        var t = $(this).val();
        $(".contentswitch:not(:input)").hide(), $("#" + t + ".contentswitch").show()
    }), $("input.contentswitch:radio").attr("checked", !1).parent().hide(), $("#methods").hide(), $(".selector:input").change(function() {
        $("input.contentswitch:radio").attr("checked", !1).parent().hide(), $(".contentswitch:not(:input)").hide();
        var t = $(this).val();
        if ("" != t) {
            var e = transactions[t].methods.split(",");
            $("input.contentswitch:radio").each(function() {
                for (i = 0; i < e.length; i++) e[i] == $(this).val() && ($(this).parent().show(), 1 == e.length && $(this).click()), $(".transaction-amount").val(transactions[t].amount), $("#methods").show()
            })
        }
    }), $(".section-content").hide(), $("select.section-select").change(function() {
        var t = $(this).val();
        $(".section-content").hide(), $("#" + t + ".section-content").show()
    });
    var a = {
        lensWidth: 250,
        lensHeight: 250,
        link: !0,
        delay: 250
    };
    if ($("a.magnify").magnify(a), $("a.gallery").hover(function() {
            var t = $(this).attr("id");
            "object" == typeof gallery_json && ($("a.magnify > img#preview").attr({
                src: gallery_json[t].medium
            }), $("a.magnify").attr({
                href: gallery_json[t].source
            }).unbind().magnify(a))
        }), $("a.colorbox").colorbox({
            photo: !0,
            slideshow: !0,
            slideshowAuto: !1
        }), $("a.colorbox_iframe").colorbox({
            iframe: !0,
            width: "80%",
            height: "80%"
        }), $("a.colorbox_inline").colorbox({
            inline: !0,
            width: "50%"
        }), $(".login-toggle").each(function() {
            $(".login-method:not(:first)").slideUp()
        }).click(function() {
            $(this).next(".login-method").is(":visible") || ($(".login-method:visible").slideUp(), $(this).next(".login-method").slideDown())
        }), $("div#basket_summary").exists() && $("form.addForm").submit(function() {
            if (t) return !1;
            var e = $(this).serialize(),
                i = $(this).attr("action").replace(/\?.*/, ""),
                a = $("div#basket_summary"),
                n = i.split("?");
            return i += n.length > 1 ? "&" : "?", $.ajax({
                url: i + "_g=ajaxadd",
                type: "POST",
                cache: !1,
                data: e,
                complete: function(t) {
                    t.responseText.match("Redir") ? window.location = t.responseText.substr(6) : (a.replaceWith(t.responseText), $("#gui_message").slideUp(), $(".animate_basket").effect("shake", {
                        times: 4,
                        distance: 3
                    }, 70))
                }
            }), !1
        }), $("#content_body").on("click", ".check-primary", function() {
            var t = $(this).attr("rel");
            $("#" + t).parent().addClass("selected"), $("#" + t + ":checkbox").attr("checked", "checked")
        }), $("#quickTour").on("click", function() {
            $("#navigation .submenu").show(), $("#joyrideTour").joyride()
        }), $("#rule-eu").click(function() {
            $("#country-region").toggle("slow", function() {})
        }), $("a.colorbox.wiki").bind("cbox_complete", function() {
            window.scrollTo(0, 0)
        }).colorbox({
            iframe: !0,
            innerHeight: "450px",
            innerWidth: "650px"
        }), $("a.colorbox.paypal").colorbox({
            height: "433px",
            iframe: !0,
            scrolling: !1,
            width: "602px"
        }), $("a.colorbox.address-form").colorbox({
            href: "#address-form",
            inline: !0,
            innerHeight: "685px",
            innerWidth: "420px"
        }), $("#loading_content").hide(), $("div#progress_bar>img.newsletter").each(function() {
            window.onbeforeunload = function() {
                return !0
            }, ajaxNewsletter($("#newsletter_id").val(), 1)
        }), $("input:file.multiple").MultiFile({
            max: 4,
            namePattern: "$name$i",
            remove: '<i class="fa fa-trash-o"></i>'
        }), $("textarea.fck").each(function() {
            var fck_lang = 'en';
            if($("#val_admin_lang").length) {
                fck_lang = $("#val_admin_lang").text().substr(0,2);
            }
            if ($(this).hasClass("fck-full")) var t = {
                path: "includes/ckeditor/",
                fullPage: !0,
                selector: "textarea.fck",
                language: fck_lang
            };
            else var t = {
                path: "includes/ckeditor/",
                fullPage: !1,
                selector: "textarea.fck",
                language: fck_lang
            };
            if ($(this).hasClass("fck-source")) {
                t.startupMode = 'source';
            }
            if ($(this).attr("data-fck-height")) {
                t.height = $(this).attr("data-fck-height");
            }
            $(this).ckeditor(t)
        }), $("div.fm-filelist").each(function() {
            var t = $("#val_admin_file").text();
            $(this).fileTree({
                root: "/",
                script: "./" + t,
                group: $(this).attr("rel"),
                name: $(this).attr("id")
            })
        }), !$("div.tab").exists()) {
        var n = $("div.tab_content:first").show().attr("id");
        $("#tab_" + n).addClass("tab-selected")
    }
    $("div.tab").each(function() {
        if ("" !== window.location.hash && $(window.location.hash).length > 0) {
            var t = window.location.hash,
                e = t;
            $("div.tab_content:not(" + t + ")").hide(), $("div.tab_content" + t).show(), $("#tab_" + t.replace("#", "")).addClass("tab-selected")
        } else {
            $("div.tab_content:not(:first)").hide();
            var t = $("div.tab_content:first").show().attr("id"),
                e = "#" + t;
            $("#tab_" + t).addClass("tab-selected")
        }
        if ($("#wikihelp").exists()) {
            var i = $("#wikihelp").attr("href"),
                a = i.split("#");
            $("#wikihelp").attr("href", a[0] + e)
        }
        $("#previous-tab").val(e), $("input.previous-tab").val(e), window.scrollTo(-81, 0)
    }).on("click", function() {
        var t = $(this).children("a").attr("href");
        var e = 0;
        if(t.startsWith("#")) {
            e = $(t).height();
        }

        if ($("#navigation").height() < e && $("#page_content").css('min-height',e + 100 +'px'), "#sidebar" == t) return $("#sidebar_control").click(), !1;
        if (t.match(/^#/)) {
            if (document.location.hash = t, $(".tab").removeClass("tab-selected"), $(this).addClass("tab-selected"), $("div.tab_content").hide(), $(t).show(), window.scrollTo(-81, 0), $("#previous-tab").val(t), $("input.previous-tab").val(t), $("#wikihelp").exists()) {
                var i = $("#wikihelp").attr("href"),
                    a = i.split("#");
                $("#wikihelp").attr("href", a[0] + t)
            }
            return !1
        }
    }), $("select.select-skin").each(function() {
        var t = $(this).siblings("select.select-style"),
            e = $(this).siblings("input[type=hidden].default-style").val();
        if (json_skins[$(this).val()])
            for (value in json_skins[$(this).val()]) {
                var i = json_skins[$(this).val()][value],
                    a = document.createElement("option");
                $(a).val(value).text(i).addClass("dynamic"), value == e && $(a).attr("selected", "selected"), $(t).append(a)
            } else $(this).hasClass("no-drop") && $(t).hide();
        $(this).on("change", function() {
            if ($(t).children("option.dynamic").remove(), json_skins[$(this).val()]) {
                for (value in json_skins[$(this).val()]) {
                    var e = json_skins[$(this).val()][value],
                        i = document.createElement("option");
                    $(i).val(value).text(e).addClass("dynamic"), $(t).append(i)
                }
                $(t).show()
            } else $(this).hasClass("no-drop") && $(t).hide()
        })
    }), $("select.select-skin-mobile").each(function() {
        var t = $(this).siblings("select.select-style-mobile"),
            e = $(this).siblings("input[type=hidden].default-style-mobile").val();
        if (json_skins[$(this).val()])
            for (value in json_skins[$(this).val()]) {
                var i = json_skins[$(this).val()][value],
                    a = document.createElement("option");
                $(a).val(value).text(i).addClass("dynamic"), value == e && $(a).attr("selected", "selected"), $(t).append(a)
            } else $(this).hasClass("no-drop") && $(t).hide();
        $(this).on("change", function() {
            if ($(t).children("option.dynamic").remove(), json_skins[$(this).val()]) {
                for (value in json_skins[$(this).val()]) {
                    var e = json_skins[$(this).val()][value],
                        i = document.createElement("option");
                    $(i).val(value).text(e).addClass("dynamic"), $(t).append(i)
                }
                $(t).show()
            } else $(this).hasClass("no-drop") && $(t).hide()
        })
    }), $("span.editable").each(function() {
        "" == $(this).html() && $(this).html("<em>null</em>")
    }), $("span.editable").each(function() {
        $(this).attr("title", "Click to edit")
    }).on("click", function() {
        var t = $(this).html();
        "<em>null</em>" == t && (t = "");
        var e = $(this).attr("name"),
            i = $(this).attr("class");
        if ($(this).hasClass("select")) {
            var a = document.createElement("select");
            $.each(select_data, function(t, e) {
                $(a).append('<option value="' + t + '">' + e + "</option>")
            }), $(a).children(":contains(" + t + ")").attr("selected", "selected")
        } else {
            var a = document.createElement("input");
            $(a).attr({
                type: "text",
                value: t
            }).addClass(i)
        }
        $(a).addClass("textbox"), $(a).attr("name", e), $(this).replaceWith(a)
    }), $(".reorder-list").sortable({
        axis: "y",
        handle: "a.handle",
        opacity: .7,
        placeholder: "reorder-position",
        placeholderElement: "> tr",
        revert: !0,
        scroll: !0,
        stop: function() {
            updateStriping()
        }
    }), $(".revert").each(function() {
        var t = $(this).attr("rel");
        "0" == $("#defined_" + t).val() ? $("#row_" + t).addClass("list-changed") : $("#string_" + t).val() != $("#default_" + t).val() ? $("#row_" + t).addClass("custom-phrase") : $(this).hide()
    }).on("click", function() {

        var t = $(this).attr("rel"),
            e = $("#default_" + t).val();
        $('<input>').attr({
                    type: 'hidden',
                    id: 'delete_' + t,
                    name: 'delete['+t+']',
                    value: true
        }).appendTo('form#edit_phrases');
        $("#string_"+t).prop("disabled", true)
        return $("#string_" + t).val(e), $("#row_" + t).removeClass("custom-phrase"), $(this).hide(), !1
    }), $("td.phrase_row").click(function() {
        var t = $(this).attr("rel");
        $("#"+t).prop("disabled", false).focus();
    }), $(".editable_phrase").focusout(function() {
        var t = $(this).attr("rel");
        $(this).val() != $("#default_" + t).val() ? ($("#row_" + t).addClass("custom-phrase"), $("#revert_" + t).show(), $("#delete_" + t).remove()) : ($("#row_" + t).removeClass("custom-phrase"), $(this).prop("disabled", true), $("#revert_" + t).hide(),$('<input>').attr({type: 'hidden',id: 'delete_' + t,name: 'delete['+t+']',value: true}).appendTo('form#edit_phrases'))
    }), $("input.ajax").autocomplete({
        timeout: 5e3,
        ajax_get: ajaxSuggest,
        callback: ajaxSelected
    }), $("select.field_select").each(function() {
        if ($(this).find("option:first").attr("selected", "selected"), $(this).parent().parent().find(".field_select_target:not(:first)").hide(), "select_group_id" == $(this).attr("id")) {
            var t = $("option:selected", $(this)).val();
            $("#attr_source").attr("name", "add_attr[" + t + "]"), $("#group_target").attr("target", "group_" + t)
        }
    }).on("change", function() {
        if ("select_group_id" == $(this).attr("id")) {
            var t = $("option:selected", $(this)).val();
            $("#attr_source").attr("name", "add_attr[" + t + "]"), $("#group_target").attr("target", "group_" + t)
        }
        if ($(this).parent().parent().find(".field_select_target").hide(), "" != $(this).val()) {
            var e = "#" + $(this).attr("rel") + $(this).val();
            $(e).show(), $("#" + $(e).attr("rel")).show()
        }
    }), $.datepicker.setDefaults({
        changeMonth: !0,
        constrainInput: !0,
        dateFormat: "yy-mm-dd",
        hideIfNoPrevNext: !0,
        onSelect: function(t) {
            var e = t.split("-", 3);
            $(this).nextAll("input.date:first").datepicker("option", "minDate", new Date(e[0], e[1] - 1, e[2]))
        },
        showStatus: !1
    }), $("input.date").datepicker(), window.scrollTo(0, 0);
    var s = $("#navigation").height(),
        r = $("#page_content").height();
    s > r && $("#page_content").css('min-height',s + 100 +'px'), $('input[type="checkbox"]').each(function() {
        $(this).parent().hasClass("custom-checkbox") || $(this).wrap("<div class='custom-checkbox'></div>"), $(this).is(":checked") ? $(this).parent().addClass("selected") : $(this).parent().removeClass("selected")
    }), $("body").on("click", "img.checkbox, .check-primary, .check_cat, .check-all, .custom-checkbox", function() {
        $('input[type="checkbox"]').each(function() {
            $(this).is(":checked") ? $(this).parent().addClass("selected") : $(this).parent().removeClass("selected")
        })
    })
});
var new_option = 0,
    data = !1;
if (!addresses || "object" != typeof addresses) var addresses = new Object;
var options_added = 0;
$("#inventory-list").on("change", "select.options_calc", function() {
    productOptionPrices($(this).parent().attr("rel"))
}), $("#inventory-list").on("focusout", ".text_calc", function() {
    productOptionPrices($(this).parent().attr("rel"))
});
var inline_add_offset = 0;
$('a.add, a.inline-add, input[type="button"].add').on("click", function() {
    function t(t, e, i) {
        t.hasClass("before") ? e.before(i) : t.hasClass("after") ? e.after(i) : e.append(i)
    }
    var e = $(this).attr("target"),
        i = $(this).parents(".inline-add:first"),
        a = $(i).next(".inline-source"),
        n = new Array,
        s = !0;
    $('#'+e+' .form-none').hide();    
    if ($(".inline-add").removeClass("highlight"), $(":input", i).each(function() {
            $(this).removeClass("required-error");
            var t = $(this).attr("rel"),
                e = $(this).val();
            $(this).hasClass("not-empty") && e == $(this).attr("original") && ($(this).on("change", function() {
                $(this).val() != $(this).attr("original") && $(this).removeClass("required-error")
            }).addClass("required-error"), s = !1), n[t] = e
        }), 0 == s) return !1;
    if ($(i).removeClass("highlight"), 1 == $(a).length) {
        var r = $(a).attr("name"),
            o = $(a).clone(!0).attr({
                name: ""
            }).removeAttr("id").removeClass("inline-source"),
            l = $("#val_admin_file").text();
        $(i).find(":input").each(function() {
            var t, e = $(this).attr("rel"),
                i = $(this).val();
            if ($(this).is("select")) var a = $(this).find("option:selected").text();
            else var a = $(this).val();
            "product_id" == e && i > 0 ? $.ajax({
                url: "./" + l,
                type: "GET",
                cache: !1,
                data: {
                    _g: "xml",
                    type: "prod_options",
                    q: i,
                    function: "template"
                },
                complete: function(e) {
                    t = e.responseText.split("inv[]").join("inv_add[" + (inline_add_offset - 1) + "]"), t = t.split('rel=""').join('rel="' + (inline_add_offset - 1) + '"'), $(o).find("[rel=product_options]").html(t)
                }
            }) : "price" == e && $(o).find(":input[rel=" + e + "]").attr("id", inline_add_offset + "_price"), $(o).find(":input[rel=" + e + "]").val(i).attr({
                name: r + "[" + inline_add_offset + "][" + e + "]",
                original: i
            }), $(o).find("[rel=" + e + "]:not(:input)").text(a)
        }), $(i).find(":input").each(function() {
            $(this).val($(this).attr("original"))
        })
    } else {

        var fields_ok = true;
        $(this).parents("div:first,tr:first").find(".add:input").each(function() {
            if($(this).attr("required")=='required' && $(this).val()=='') {
                $(this).addClass('required-error');
                $(this).click(function(){$(this).removeClass('required-error')});
                fields_ok = false;  
            }
        });

        if(!fields_ok) {
            return false;
        }

        var o = document.createElement("div"),
            c = document.createElement("span"),
            d = document.createElement("a"),
            h = document.createElement("i");
    
        $('input[name="add_div_class"]') && $(o).addClass($('input[name="add_div_class"]').val()), $(h).attr({
            "class": "fa fa-trash"
        }), $(d).attr({
            href: "#"
        }).addClass("remove dynamic").append(h), $(c).addClass("actions").append(d), $(this).parents("div:first,tr:first").find(".add:input").each(function() {

            if ($(this).hasClass("display")) {
                "" == $(this).val() && (s = !1);
                var t = $(this).is("select") ? $(this).find(":selected").text() : "<strong>" + $(this).val() + "</strong>";
                $(o).append(t)
            }
            if ($(this).attr("name")) {
                var e = document.createElement("input");
                $(e).attr({
                    type: "hidden",
                    name: $(this).attr("name"),
                    value: $(this).val()
                }), $(o).append(e)
            }

            $.fn.colorbox.close()
            
            //$(this).val("")
        }), $(o).prepend(c)
    }
    return 1 == s && e.length > 1 && 1 == $("#" + e).length ? t($(this), $("#" + e), o) : $(i).before(o), $(".update-subtotal input.number").trigger("change"), inline_add_offset++, updateStriping(), $(".dymanic_none").hide(), !1
}), $("a.duplicate").on("click", function() {
    var t = $(this).attr("rel"),
        e = $(this).attr("target").length >= 1 ? $(this).attr("target") : "";
    return $("." + t + ":input").each(function() {
        var t = $("#" + e + $(this).attr("id"));
        $(t).val($(this).val()), "sum_country" == $(this).attr("id") && $(t).trigger("change")
    }), !1
}), $("#search-placeholder").on("click", function() {
    return $("#sidebar_contain").animate({
        left: "0px"
    }), !1
}), $("#sidebar_contain").on("mouseleave", function() {
    if(!$(".jqac-menu").length) {
        return $(this).animate({
            left: "-340px"
        })
    , !1
    }
}), $("div#tab_sidebar").on("click", function() {
    var t = $("#sidebar_contain"),
        e = t.position();
    return t.animate(0 == e.left ? {
        left: "-340px"
    } : {
        left: "0px"
    }), !1
}), $(".option-edit").on("click", function() {
    var t = $(this).attr("rel"),
        e = $("#data_" + t).val().split("|");
    $("#opt_assign_id").val(t), $("#opt_mid").val(e[0]), $("#opt_price").val(e[1]), $("#opt_weight").val(e[2]), $("#opt_stock").val(e[3]), $(this).parent().parent().remove()
}), $(".fa-trash.disabled, .title_alert").on("click", function() {
    alert($(this).attr("title"))
}), $("input#product_code").on("keyup", function() {
    $("input#product_code").val().length > 0 ? $("input#product_code_auto").attr("checked", !1) : $("input#product_code_auto").attr("checked", !0)
}), $("input#product_code_auto").on("click", function() {
    var t = $("input#product_code_old").val(),
        e = $("input#product_code").val();
    e.length > 0 ? ($("input#product_code_old").val(e), $("input#product_code").val("")) : $("input#product_code").val(t)
}), $("#gui_message").on("click", function() {
    $(this).slideUp()
}), $("#seo").on("change", function() {
    var t = $("#val_admin_file").text();
    seo = 1 == $("#seo").val() ? "seo_code" : "no_seo_code", $.getJSON("./" + t, {
        _g: "xml",
        type: seo,
        function: "get"
    }, function(t) {
        $("#htaccess").val(t.content)
    })
}), $("#cat_general").on("change", "#cat_name", function() {
    $("#cat_general").on("click", "#cat_save", function() {
        return $("#dialog-seo").dialog({
            modal: !0,
            buttons: {
                Yes: function() {
                    $(this).dialog("close"), $("#gen_seo").val("1"), document.cat_form.submit()
                },
                No: function() {
                    $(this).dialog("close"), document.cat_form.submit()
                }
            }
        }), !1
    })
}), $("#cat_general").on("change", "#parent", function() {
    $("#cat_general").on("click", "#cat_save", function() {
        return $("#dialog-seo").dialog({
            modal: !0,
            buttons: {
                Yes: function() {
                    $(this).dialog("close"), $("#gen_seo").val("1"), document.cat_form.submit()
                },
                No: function() {
                    $(this).dialog("close"), document.cat_form.submit()
                }
            }
        }), !1
    })
}), $("#cat_subset").on("change", function() {
    $location = document.URL.replace(/&?page=[0-9]/, ""), -1 != $location.indexOf("cat_id") && ($location = removeVariableFromURL($location, "cat_id")), "any" != $(this).val() && ($location = $(this).val()), window.location.replace($location)
}), $("select.address-list").on("change", function() {
    var t = $(this).val(),
        e = addresses[t],
        i = "" == $(this).attr("rel") ? "sum" : $(this).attr("rel"),
        a = null,
        n = i.indexOf(":");
    n > 1 && (a = i.split(":"));
    for (var s in e)
        if (e[s] = jQuery.trim(e[s]), null != a)
            for (j = 0; j < a.length; j++) updateAddressValues(a[j], s, e);
        else updateAddressValues(i, s, e)
}), $("a.select").on("click", function() {
    var t = $(this).attr("href"),
        e = $("#ckfuncnum").val();
    return window.opener.CKEDITOR.tools.callFunction(e, t), window.close(), !1
}), $("#discount_type, .lineprice").on("change", function() {
    $(".update-subtotal input.number").trigger("change")
}), $(".update-subtotal input.number").on("change", function() {
    updateOrderTotals($(this))
}), $("body").on("click", "a.remove", function() {
    var t = $(this).attr("title"),
        e = $(this).attr("rel"),
        i = $(this).attr("href"),
        a = $(this).attr("name");
    if ("" != t && !confirm(t)) return !1;
    if (e && !$(this).hasClass("dynamic")) {
        var n = document.createElement("input");
        $(n).attr({
            type: "hidden",
            name: a + "[]"
        }).val(e), $(this).parents("form:first").append(n)
    } else pageChanged(this);
    if ("inv_remove" == a && $(this).parents("form:first").append('<input type="hidden" name="inv_remove[]" value="' + i.substring(1) + '" />'), $(this).hasClass("tr")) var s = $(this).parents("tr:first");
    else var s = $(this).parents("tr:first,div:first:not(.tab_content)");
    return $(s).remove(), $(".update-subtotal input.number").trigger("change"), !1
}), $("a.refresh").on("click", function() {
    return $(".update-subtotal input.number").trigger("change"), !1
});
/* Work in progress relating to #2097
$('#order-builder').on('change', '.tax-chooser', function() {
    var goods_items = $(".goods");
    var tax_percent = parseFloat($(this).find(':selected').attr('data-percent'))/100;
    var goods = $(this).find(':selected').attr('data-goods');
    var shipping = $(this).find(':selected').attr('data-shipping');
    var total_tax = 0;
    if(goods=='1') {
        for(var i = 0; i < goods_items.length; i++){
            var item = parseFloat($(goods_items[i]).val());
            if(item>0) {
                total_tax += item*tax_percent;
            }
        };
    }
    if(shipping=='1') {
        var shipping_total = parseFloat($(".shipping").val())*tax_percent;
        if(shipping_total>0) {
            total_tax += shipping_total*tax_percent;
        }
    }
    $(".tax").val(total_tax.toFixed(2));
});
*/