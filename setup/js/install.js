jQuery(document).ready(function () {
    $("div.click-select input:radio").hide();
    $("div.click-select").click(function () {
        $("div.selected").removeClass("selected");
        $(this).addClass("selected");
        $(this).children("input:radio").attr("checked", "checked");
        $(this).removeClass("faded");
    });
    if ($("div.click-select").size() == 1) $("div.click-select").click();

    $("input.cancel:submit").click(function () {
        $(".required:input").removeClass("required");
    });

    // Show/hide password toggle. Each .password-toggle button carries its own
    // localised labels via data-text-show / data-text-hide so the JS stays language-neutral.
    $(document).on("click", ".password-toggle", function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $input = $("#" + $btn.data("target"));
        if (!$input.length) return;
        var isPwd = $input.attr("type") === "password";
        $input.attr("type", isPwd ? "text" : "password");
        $btn.text(isPwd ? $btn.data("text-hide") : $btn.data("text-show"));
    });

    // POST the install form's DB fields with test_connection=1 so setup/index.php
    // can short-circuit with a JSON reply. Shared by the manual button and the
    // pre-submit gate below.
    function runDbTest(onDone) {
        var $btn = $("#test-connection-btn");
        var $result = $("#test-connection-result");
        $result.removeClass("pass fail").text("…");
        $btn.prop("disabled", true);

        var data = {
            test_connection: "1",
            "global[dbhost]":     $("#form-dbhost").val(),
            "global[dbdatabase]": $("#form-dbdatabase").val(),
            "global[dbusername]": $("#form-dbusername").val(),
            "global[dbpassword]": $("#form-dbpassword").val(),
            "global[dbport]":     $("#form-dbport").val(),
            "global[dbsocket]":   $("#form-dbsocket").val()
        };

        $.post("index.php", data, null, "json").done(function (res) {
            $result.addClass(res.ok ? "pass" : "fail").text(res.message);
            if (onDone) onDone(res);
        }).fail(function () {
            $result.addClass("fail").text($btn.data("text-error") || "Test failed.");
        }).always(function () {
            $btn.prop("disabled", false);
        });
    }

    $("#test-connection-btn").on("click", function (e) {
        e.preventDefault();
        runDbTest();
    });

    // Single submit handler: required-field check first, then auto-run the DB test
    // before letting the install form go through. The auto-test only kicks in when
    // #test-connection-btn is present (i.e. install step, !PRESET_DB).
    $("form").on("submit", function (e) {
        var ok = true;
        $(".required:input").removeClass("required-error");
        $(this).find(".required:input").each(function () {
            if ($(this).val().replace(/\s/i, "") == "") {
                $(this).addClass("required-error").change(function () {
                    $(this).removeClass("required-error");
                });
                ok = false;
            }
        });
        $(this).find(".error:input").each(function () {
            $(this).addClass("required-error");
            ok = false;
        });
        if (!ok) return false;

        if (window._dbTestPassed) return;
        if (!$("#test-connection-btn").length) return;

        e.preventDefault();
        var $form = $(this);
        runDbTest(function (res) {
            if (res.ok) {
                window._dbTestPassed = true;
                $form[0].submit();
            }
        });
    });

    $("label.help").click(function () {});
});
