jQuery(document).ready(function () {
    jQuery.fn.confirmPassword = function (e) {
        var t = jQuery.extend({updateOn: "keyup"}, e);
        this.bind(t.updateOn, function () {
            jQuery(this).removeClass("ps-match ps-nomatch error");
            if (jQuery(this).val() != "") {
                var e = jQuery(this).attr("rel");
                if (jQuery("#" + e).val() === jQuery(this).val() && jQuery(this).val() != "") {
                    jQuery(this).addClass("ps-match")
                } else {
                    jQuery(this).addClass("ps-nomatch error")
                }
            }
        })
    };
    $("div.click-select input:radio").hide();
    $("div.click-select").click(function () {
        $("div.selected").removeClass("selected");
        $(this).addClass("selected");
        $(this).children("input:radio").attr("checked", "checked");
        $(this).removeClass("faded")
    });
    if ($("div.click-select").size() == 1)$("div.click-select").click();
    $("input.cancel:submit").click(function () {
        $(".required:input").removeClass("required")
    });
    jQuery.fn.getinfo = function () {
        return this
    };
    $("input:password.strength").pstrength(false);
    $("input:password.confirm").confirmPassword();
    $("form").submit(function () {
        var e = true;
        $(".required:input").removeClass("required-error");
        $(this).find(".required:input").each(function () {
            if ($(this).val().replace(/\s/i, "") == "") {
                $(this).addClass("required-error").change(function () {
                    $(this).removeClass("required-error")
                });
                e = false
            }
        });
        $(this).find(".error:input").each(function () {
            $(this).addClass("required-error");
            e = false
        });
        return e ? true : false
    });
    $("label.help").click(function () {
    })
})