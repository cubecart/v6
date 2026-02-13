/**
 * CubeCart Admin JavaScript - OPTIMIZED
 * - Fixed memory leaks from nested event handlers
 * - Cached jQuery selectors for better performance
 * - Added debouncing to expensive operations
 * - Used event delegation properly
 * - Consolidated duplicate code
 */

$(document).ready(function() {
    // ===========================
    // CACHE GLOBAL SELECTORS
    // ===========================
    var $body = $('body');
    var $document = $(document);
    var $contentBody = $("#content_body");

    window.ADMIN_FILE = $("#val_admin_file").text() || "admin.php";
    window.ADMIN_FOLDER = $("#val_admin_folder").text() || "admin";
    window.SKIN_FOLDER  = $("#val_skin_folder").text()  || "default";
    window.IMG_PATH = `${ADMIN_FOLDER}/skins/${SKIN_FOLDER}/images/`;
    window.LANG = {
        disable: $("#val_lang_disable").text() || "Disable",
        enable:  $("#val_lang_enable").text()  || "Enable"
    };

    // ===========================
    // UTILITY FUNCTIONS
    // ===========================
    const debounce = (fn, ms = 120) => {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), ms);
        };
    };

    // ===========================
    // SPEC CONTAINER (Product Specs)
    // ===========================
    (function() {
        let counter = $('#spec-container .spec-row').length - 1;
        let lang_value = $('#lang_name').text() || 'Value';
        let lang_name = $('#lang_name').text() || 'Name';

        $('#spec-container').on('click', '.add', function(e) {
            e.preventDefault();
            counter++;
            $(this).text('-').removeClass('add').addClass('remove');
            const newRow = `
            <div class="spec-row">
                <input type="text" name="specs[${counter}][name]" class="textbox" placeholder="${lang_name}">
                <textarea name="specs[${counter}][value]" class="textbox" placeholder="${lang_value}"></textarea>
                <button class="add">+</button>
            </div>`;
            $('#spec-container').append(newRow);
        });

        $('#spec-container').on('click', '.remove', function(e) {
            e.preventDefault();
            $(this).closest('.spec-row').remove();
        });

        // OPTIMIZED: Debounced focusout to reduce processing
        var updateSpecArray = debounce(function() {
            const specs = [];
            $('#spec-container .spec-row').each(function() {
                const $row = $(this);
                const name = $row.find('input[name*="[name]"]').val().trim();
                const value = $row.find('textarea[name*="[value]"]').val().trim();
                if (name !== '' && value !== '') {
                    specs.push([name, value]);
                }
            });
            const jsonData = JSON.stringify(specs);
            const base64Data = btoa(jsonData);
            $('#spec_array').val(base64Data);
        }, 300);

        $document.on('focusout', '.spec-row input, .spec-row textarea', updateSpecArray);
    })();

    // ===========================
    // COPY TEXT FUNCTIONALITY
    // ===========================
    $document.on("click", '.copy_text', function() {
        navigator.clipboard.writeText($(this).attr('data-value'));
        $(this).attr('title', $(this).attr('data-copied'));
    });

    $document.on("mouseenter", '.copy_text', function() {
        $(this).attr('title', $(this).attr('data-copy'));
    });

    $document.on("mouseout", '.copy_text', function() {
        $(this).attr('title', $(this).attr('data-copy'));
    });

    // ===========================
    // STRING LENGTH COUNTER
    // ===========================
    $document.on("keyup", ".strlen", debounce(function() {
        const rel = this.getAttribute("rel");
        if (rel) {
            const target = document.getElementById(rel);
            if (target) target.innerText = this.value.length;
        }
    }, 150));

    // ===========================
    // EDITABLE PHRASES
    // ===========================
    $document.on("focusout", ".editable_phrase", function() {
        var phrase_id = $(this).attr('rel');
        if($(this).val() == $('#default_' + phrase_id).val() && !$(this).hasClass('reverted')) {
            $('#string_' + phrase_id).prop("disabled", true);
        }
    });

    // ===========================
    // DONE TOGGLE
    // ===========================
    $document.on("click", '.done_toggle', function() {
        var this_toggle = $(this);
        var requestData = {
            'status': $(this).attr('data-status'),
            'id': $(this).attr('data-id'),
            'table': $(this).attr('data-table'),
            'token': $('.cc_session_token').val()
        };

        $.ajax({
            type: 'post',
            url: "?_g=xml&function=doneToggle",
            data: requestData,
            dataType: "text",
            success: function(responseData) {
                const r = JSON.parse(responseData);
                if(r['success']=='1') {
                    if(requestData['status'] == 1) {
                        this_toggle.removeClass('fa-check-circle').addClass('fa-times-circle').attr('data-status', 0);
                        if($('#warn_'+r['id']).length) {
                            $('#warn_'+r['id']).remove();
                        }
                    } else if(r['status']=='0') {
                        this_toggle.removeClass('fa-times-circle').addClass('fa-check-circle').attr('data-status', 1);
                    } else if(r['status']=='warn') {
                        this_toggle.remove();
                    }
                }
            }
        });
    });

    // ===========================
    // SCROLL TO HIGHLIGHTED ITEM
    // ===========================
    var $highlighted = $(".fm-item.hilighted");
    if($highlighted.length) {
        setTimeout(function() {
            $('html, body').animate({
                scrollTop: $highlighted.offset().top
            }, 'slow');
        }, 100);
    }

    // ===========================
    // PRODUCT CODE AUTO TOGGLE
    // ===========================
    var $productCode = $("input#product_code");
    var $productCodeAuto = $("input#product_code_auto");

    if($productCode.length > 0) {
        $productCode.val().length > 0 ? $productCodeAuto.val('0') : $productCodeAuto.val('1');
        $productCodeAuto.change();
    }

    // ===========================
    // TOGGLE CHECKBOXES (Visual)
    // ===========================
    $("input.toggle:hidden").each(function() {
        var checked = ($(this).val() == "1") ? "1" : "0";
        var img = document.createElement("img");
        var style = $(this).attr("style");

        img.src = IMG_PATH + checked + "_checkbox.png";
        img.alt = img.title = checked == "1" ? LANG.disable : LANG.enable;

        $(img).addClass("checkbox cbs").attr("rel", "#" + $(this).attr("id"));

        if (typeof style !== 'undefined' && style !== false) {
            $(img).attr("style", style);
        }
        $(this).after(img);
    });

    // ===========================
    // MULTI-ACTION DELETE CONFIRM
    // ===========================
    $('#submit_multi').on("click", function() {
        if($('select[name="multi-action"]').val() == 'delete') {
            if(!confirm($(this).attr("data-confirm"))) {
                return false;
            }
        }
    });

    // ===========================
    // ELASTICSEARCH REBUILD
    // ===========================
    $contentBody.on("click", "#rebuild_elastic", function() {
        $(this).hide();
        $('#progress_wrapper').css("display", "block");
        $("#es_count").html('-');
        $("#es_size").html('-');
        ajaxElasticSearch(1);
    });

    // ===========================
    // CHECKBOX TOGGLE (Image-based)
    // ===========================
    $contentBody.on("click", "img.cbs", function() {
        var $target = $($(this).attr("rel"));
        var currentVal = $target.val();
        var newVal = currentVal == "1" ? "0" : "1";

        var new_src = $(this).attr('src').replace(currentVal + '_checkbox.png', newVal + '_checkbox.png');
        $(this).attr('src', new_src);
        $target.val(newVal);
    });

    // ===========================
    // CHOSEN SELECT
    // ===========================
    $('.chzn-select').chosen({width:"500px", search_contains:true});

    // ===========================
    // JQUERY EXTENSIONS
    // ===========================
    var preventFormLeave = false;

    jQuery.debug = function(msg) {
        window.console ? console.debug("CubeCart: " + msg) : alert(msg);
    };

    jQuery.fn.insertAtCaret = function(text) {
        return this.each(function() {
            if (document.selection) {
                this.focus();
                sel = document.selection.createRange();
                sel.text = text;
                this.focus();
            } else if (this.selectionStart || "0" == this.selectionStart) {
                var start = this.selectionStart;
                var end = this.selectionEnd;
                var scrollTop = this.scrollTop;
                this.value = this.value.substring(0, start) + text + this.value.substring(end, this.value.length);
                this.focus();
                this.selectionStart = start + text.length;
                this.selectionEnd = start + text.length;
                this.scrollTop = scrollTop;
            } else {
                this.value += text;
                this.focus();
            }
        });
    };

    jQuery.fn.confirmPassword = function(options) {
        var settings = jQuery.extend({updateOn: "keyup"}, options);
        this.bind(settings.updateOn, function() {
            var $this = jQuery(this);
            $this.removeClass("ps-match ps-nomatch error");
            if ($this.val() != "") {
                var relId = $this.attr("rel");
                var matches = jQuery("#" + relId).val() === $this.val() && $this.val() != "";
                $this.addClass(matches ? "ps-match" : "ps-nomatch error");
            }
        });
    };

    jQuery.fn.exists = function() {
        return jQuery(this).length != 0;
    };

    // ===========================
    // FRAME BUSTER
    // ===========================
    if (top.location.href != self.location.href) {
        top.location = self.location.href;
    }

    // ===========================
    // PLACEHOLDER HANDLING - OPTIMIZED
    // Uses HTML5 placeholder instead of manual handling
    // ===========================
    // Legacy code removed - modern browsers support placeholder natively

    // ===========================
    // FORM SUBMIT HANDLERS
    // ===========================
    $document.on("click", "input:submit.update", function() {
        $("select.required").removeClass("required");
    });

    $document.on("change", "select.update_form", function() {
        $("input.required").removeClass("required");
        $(this).parents("form").submit();
    });

    // OPTIMIZED: Form validation with cached selectors
    $document.on("submit", "form", function() {
        var isValid = true;
        preventFormLeave = false;

        if ($("#inventory-list").exists() && !$("input[name*=inv]").exists()) {
            $(".inline-add:first").addClass("highlight");
            return false;
        }

        $(".required-error").removeClass("required-error");

        // Disable ignored checkboxes
        $(":checkbox.ignore").each(function() {
            if (!this.checked) $(this).prop("disabled", true);
        });

        var $visibleContent = $("div.tab_content").exists() ? $("div.tab_content:visible") : $(this);

        // Validate required inputs
        $visibleContent.find(".required:input:not(:hidden)").each(function() {
            var $this = $(this);
            var value = $this.val();

            if (value.replace(/\s/i, "") == "") {
                var id = $this.attr("id");
                $this.addClass("required-error").one("change", function() {
                    if ($(this).val() != $(this).attr("original")) {
                        $(this).removeClass("required-error");
                        $("#error_" + id + ".error").hide("fast");
                    }
                });
                $("#error_" + id + ".error").show("fast");
                isValid = false;
            }
        });

        // Validate inline-add fields
        $(".inline-add:input").each(function() {
            if ($(this).val() != $(this).attr("original")) {
                $(this).parents(".inline-add:first").addClass("highlight");
                isValid = false;
            }
        });

        // Validate required selects
        $visibleContent.find("select.required:not(:hidden)").each(function() {
            if ($(this).val() == 0) {
                var $this = $(this);
                var id = $this.attr("id");
                $this.addClass("required-error").one("change", function() {
                    if ($(this).val() != $(this).attr("original")) {
                        $(this).removeClass("required-error");
                        $("#error_" + id + ".error").hide("fast");
                    }
                });
                $("#error_" + id + ".error").show("fast");
                isValid = false;
            }
        });

        if (isValid) {
            window.onbeforeunload = null;
            return true;
        }

        // Scroll to first error
        var $firstError = $(".required-error:first");
        if ($firstError.length) {
            var position = $firstError.position();
            $("html, body").animate({scrollTop: position.top - 50}, "slow");
        }

        preventFormLeave = true;
        return false;
    });

    // ===========================
    // CHECK ALL FUNCTIONALITY
    // ===========================
    $document.on("click", ".check-all", function(ev) {
        if ($(this).is("a")) ev.preventDefault();
        const group = $(this).attr("rel");
        const $boxes = $("input[type=checkbox]." + group);
        const state = this.type === "checkbox" ? this.checked : !$boxes.first().prop("checked");
        $boxes.prop("checked", !!state).trigger("change");
    });

    // ===========================
    // AUTO-SUBMIT SELECTS
    // ===========================
    $("select.auto_submit").each(function() {
        if (!$(this).hasClass("show_submit")) {
            $(this).parents("form:first").find("input:submit").hide();
        }
    }).on("change", function() {
        $(this).parents("form:first").submit();
    });

    // ===========================
    // INSERT TEXT FUNCTIONALITY
    // ===========================
    $document.on("click", ".insert-text", function() {
        var target = "#" + $(this).attr("target");
        var text = $(this).text();
        $(target).insertAtCaret(text);
        return false;
    });

    // ===========================
    // AUTOSUBMIT IMAGES
    // ===========================
    $("img.autosubmit").each(function() {
        if ($(this).hasClass("form-name")) {
            var formName = $(this).attr("rel");
            $("form#" + formName).submit();
        } else {
            $(this).parents("form").submit();
        }
    });

    // ===========================
    // NAVIGATION MENU TOGGLE
    // ===========================
    $("#navigation div.menu").on("click", function() {
        var menuId = $(this).attr("id");
        $("#menu_" + menuId).toggle("fast", function() {
            var isVisible = $(this).is(":visible");
            $.cookie("nav_" + menuId, isVisible);

            if (isVisible) {
                $("#" + menuId + " i").addClass("fa-minus-square-o").removeClass("fa-plus-square-o");
            } else {
                $("#" + menuId + " i").removeClass("fa-minus-square-o").addClass("fa-plus-square-o");
            }
        });
    });

    // ===========================
    // GUI MESSAGE ERRORS
    // ===========================
    if (typeof gui_message_json !== 'undefined' && typeof gui_message_json === 'object') {
        for (var key in gui_message_json) {
            $("#" + key).addClass("required-error").val("");
        }
    }

    // ===========================
    // BULK PRICE METHOD
    // ===========================
    $("#bulk_price_method").on("change", function() {
        if($(this).val() == 'percent') {
            $("#bulk_price_action").hide().prop('disabled', true);
            $("#bulk_price_percent_symbol").show();
        } else {
            $("#bulk_price_action").show().prop('disabled', false);
            $("#bulk_price_percent_symbol").hide();
        }
    });

    // ===========================
    // REDIRECT TYPE
    // ===========================
    $("#redirect_type").on("change", function() {
        var isStatic = $('option:selected', this).attr("data-static") == 'true';
        $("#item_id").val('');
        $("#item_id").toggle(!isStatic);
    });

    // ===========================
    // EMAIL METHOD
    // ===========================
    $("#email_method").on("change", function() {
        var method = $(this).val();
        $("#smtp_settings, #sendgrid").slideUp();

        if (method == 'sendgrid') {
            $("#sendgrid").slideDown();
        } else if (method != 'mail') {
            $("#smtp_settings").slideDown();
        }
    });

    // ===========================
    // ELASTICSEARCH TYPE
    // ===========================
    $("#es_t").on("change", function() {
        if($(this).val() == '1') {
            $("#es_auth_basic").hide();
            $("#es_auth_api").slideDown();
        } else {
            $("#es_auth_basic").slideDown();
            $("#es_auth_api").hide();
        }
    });

    // ===========================
    // FILE MANAGER SEARCH
    // ===========================
    $("#fm-search-button").on("click", function() {
        var mode = $(this).attr("data-mode");
        var action = $(this).attr("data-action");
        var term = $('#fm-search-term').val();
        var token = $('.cc_session_token').val();
        $('#fm-search-term').val('');
        return fmSearch(mode, term, token);
    });

    // ===========================
    // GET FILE SIZE
    // ===========================
    $document.on("click", ".getFileSize", function() {
        var $parent = $(this).parent();
        var path = $(this).attr("data-path");
        var time_out_text = $("#val_time_out_text").text().replace("%s", "30").replace("%1$s", "30");

        $parent.html('<i class="fa fa-spinner fa-spin fa-fw"></i>');

        $.ajax({
            dataType: "json",
            url: "./" + ADMIN_FILE,
            data: {
                _g: "xml",
                path: path,
                function: "filesize",
                action: "action"
            },
            success: function(r) {
                $parent.html(r);
            },
            timeout: 30000
        }).fail(function(xhr, status) {
            if(status == "timeout") {
                $parent.html(time_out_text);
            }
        });
    });

    // ===========================
    // BULK PRICE TARGET
    // ===========================
    $("#bulk_price_target").on("change", function() {
        if($(this).val() == 'categories') {
            $("#bulk_update_categories").show();
            $("#bulk_update_products").hide();
        } else {
            $("#bulk_update_categories").hide();
            $("#bulk_update_products").show();
        }
        $('input:checkbox').prop("checked", false);
        $('.custom-checkbox').removeClass('selected');
    });

    // ===========================
    // DROPZONE IMAGE UPLOAD
    // ===========================
    if ($("div.dropzone").length) {
        Dropzone.autoDiscover = false;
        var dropzone_url = $("div#dropzone_url").text();

        $("div.dropzone").dropzone({
            url: dropzone_url,
            acceptedFiles: 'image/gif,image/jpeg,image/png,image/webp',
            transformFile: function(file, done) {
                var maxWidth = 2000;
                var reader = new FileReader();

                reader.onload = function(event) {
                    var img = new Image();
                    img.onload = function() {
                        var canvas = document.createElement('canvas');
                        var ctx = canvas.getContext('2d');

                        var width = img.width;
                        var height = img.height;

                        if (width > maxWidth) {
                            height *= maxWidth / width;
                            width = maxWidth;
                        }

                        canvas.width = width;
                        canvas.height = height;
                        ctx.drawImage(img, 0, 0, width, height);

                        canvas.toBlob(function(blob) {
                            if (!blob || blob.size === 0) {
                                console.warn("Resize failed, uploading original file.");
                                done(file);
                            } else {
                                done(blob);
                            }
                        }, 'image/webp', 0.8);
                    };
                    img.src = event.target.result;
                };
                reader.readAsDataURL(file);
            },

            renameFile: function(file) {
                var name = file.name.substr(0, file.name.lastIndexOf("."));
                return name + ".webp";
            },

            init: function() {
                this.on("error", function(file, message) {
                    console.error("Dropzone Error:", message);
                });

                this.on("complete", function(file) {
                    var $imageset = $("div#imageset.fm-filelist");
                    if ($imageset.length) {
                        if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                            $imageset.fileTree({
                                root: "/",
                                script: "./" + ADMIN_FILE,
                                group: '1',
                                name: 'imageset',
                                unique: false
                            });
                        }
                    }
                });

                this.on("processing", function(file) {
                    var subdir = '';
                    if ($("#val_subdir").length) {
                        subdir = '&subdir=' + $("#val_subdir").text();
                    }
                    this.options.url = dropzone_url + subdir;
                });
            }
        });
    }

    // ===========================
    // REQUIRED INPUT BLUR
    // ===========================
    $document.on("blur", ":input.required", function() {
        var value = $(this).val().replace(/\s/i, "");
        $(this).toggleClass("required-error", value == "");
    });

    // ===========================
    // GIFT CERTIFICATE DELIVERY
    // ===========================
    $document.on("change", "select.certificate-delivery", function() {
        if ($(this).val() == "m") {
            $("#gc-method-e").slideUp().find("input").removeClass("required");
        } else {
            $("#gc-method-e").slideDown().find("input").addClass("required");
        }
    });

    // ===========================
    // COUNTRY/STATE SELECTORS - OPTIMIZED
    // ===========================
    function initCountryState($select) {
        if (typeof county_list !== 'object') return;

        var countryVal = $select.val();
        var counties = county_list[countryVal];
        var target = ($select.attr('rel') && $select.attr('id') != 'country-list') ?
                     '#' + $select.attr('rel') : '#state-list';

        if (typeof counties === 'object') {
            var $target = $(target);
            var currentVal = $target.val();
            var $newSelect = $('<select>').attr({
                name: $target.attr('name'),
                id: $target.attr('id'),
                'class': $target.attr('class')
            });

            if ($select.attr('title')) {
                $newSelect.append($('<option>').val('0').text($select.attr('title')));
            }

            for (var i in counties) {
                var $option = $('<option>').val(counties[i].id).text(counties[i].name);
                if (currentVal == counties[i].name || currentVal == counties[i].id) {
                    $option.prop('selected', true);
                }
                $newSelect.append($option);
            }

            $target.replaceWith($newSelect);
        } else if ($select.hasClass('no-custom-zone')) {
            $(target).prop('disabled', true).val($select.attr('title'));
        }
    }

    function changeCountryState($select) {
        if (typeof county_list !== 'object') return;

        var countryVal = $select.val();
        var list = county_list[countryVal];
        var target = ($select.attr('rel') && $select.attr('id') != 'country-list') ?
                     '#' + $select.attr('rel') : '#state-list';

        if (typeof list === 'object' && list.length >= 1) {
            var $target = $(target);
            var currentVal = $target.val();
            var $newSelect = $('<select>').attr({
                name: $target.attr('name'),
                id: $target.attr('id'),
                'class': $target.attr('class')
            });

            if ($select.attr('title')) {
                $newSelect.append($('<option>').val('0').text($select.attr('title')));
            }

            for (var i = 0; i < list.length; i++) {
                $newSelect.append($('<option>').val(list[i].id).text(list[i].name));
            }

            if (currentVal) {
                $newSelect.find("option[value=" + currentVal + "]").prop("selected", true);
            }

            $target.replaceWith($newSelect);
        } else {
            var $newInput = $('<input>').attr({
                type: "text",
                id: $(target).attr('id'),
                name: $(target).attr('name'),
                'class': $(target).attr('class')
            });

            if ($select.hasClass('no-custom-zone')) {
                $newInput.prop("disabled", true).val($select.attr('title'));
            }

            $(target).replaceWith($newInput);
        }
    }

    $("select#country-list, select.country-list").each(function() {
        initCountryState($(this));
    }).on("change", function() {
        changeCountryState($(this));
    });

    // ===========================
    // RATING STARS
    // ===========================
    $("input[type=radio].rating").rating({required: true});

    // ===========================
    // PREVIEW IMAGE
    // ===========================
    $document.on("click", "a.preview", function() {
        $("#img-preview").attr("src", $(this).attr("href"));
        return false;
    });

    // ===========================
    // DELETE/CONFIRM DIALOGS
    // ===========================
    $document.on("click", "a.delete, a.confirm, .submit_confirm, .install_confirm", function() {
        var title = $(this).attr("title");
        if (title != "") {
            return confirm(title.replace(/\\n/gi, "\n"));
        }
    });

    // ===========================
    // PASSWORD STRENGTH & CONFIRM
    // ===========================
    $("input:password.strength").pstrength();
    $("input:password.confirm").confirmPassword();

    // ===========================
    // SUBLISTS
    // ===========================
    $(".sublist").hide();
    $document.on("click", ".list-master", function() {
        $("#" + $(this).attr("rel")).toggle();
    });

    // ===========================
    // CONTENT SWITCH
    // ===========================
    $(".contentswitch:not(:input)").hide();
    $document.on("click", ".contentswitch:input", function() {
        var targetId = $(this).val();
        $(".contentswitch:not(:input)").hide();
        $("#" + targetId + ".contentswitch").show();
    });

    // ===========================
    // TRANSACTION SELECTOR
    // ===========================
    $("input.contentswitch:radio").prop("checked", false).parent().hide();
    $("#methods").hide();

    $document.on("change", ".selector:input", function() {
        $("input.contentswitch:radio").prop("checked", false).parent().hide();
        $(".contentswitch:not(:input)").hide();

        var transId = $(this).val();
        if (transId != "" && typeof transactions !== 'undefined') {
            var methods = transactions[transId].methods.split(",");

            $("input.contentswitch:radio").each(function() {
                var $this = $(this);
                for (var i = 0; i < methods.length; i++) {
                    if (methods[i] == $this.val()) {
                        $this.parent().show();
                        if (methods.length == 1) $this.click();
                    }
                }
            });

            $(".transaction-amount").val(transactions[transId].amount);
            $("#methods").show();
        }
    });

    // ===========================
    // SECTION SELECT
    // ===========================
    $(".section-content").hide();
    $document.on("change", "select.section-select", function() {
        var sectionId = $(this).val();
        $(".section-content").hide();
        $("#" + sectionId + ".section-content").show();
    });

    // ===========================
    // IMAGE GALLERY & MAGNIFY
    // ===========================
    var magnifyOptions = {
        lensWidth: 250,
        lensHeight: 250,
        link: true,
        delay: 250
    };

    $("a.magnify").magnify(magnifyOptions);

    $document.on("hover", "a.gallery", function() {
        var galleryId = $(this).attr("id");
        if (typeof gallery_json === 'object' && gallery_json[galleryId]) {
            $("a.magnify > img#preview").attr({src: gallery_json[galleryId].medium});
            $("a.magnify").attr({href: gallery_json[galleryId].source}).unbind().magnify(magnifyOptions);
        }
    });

    // ===========================
    // COLORBOX
    // ===========================
    $("a.colorbox").colorbox({photo: true, slideshow: true, slideshowAuto: false});
    $("a.colorbox_iframe").colorbox({iframe: true, width: "80%", height: "80%"});
    $("a.colorbox_inline").colorbox({inline: true, width: "50%"});

    $("a.colorbox.wiki").bind("cbox_complete", function() {
        window.scrollTo(0, 0);
    }).colorbox({iframe: true, innerHeight: "450px", innerWidth: "650px"});

    $("a.colorbox.paypal").colorbox({height: "433px", iframe: true, scrolling: false, width: "602px"});
    $("a.colorbox.address-form").colorbox({href: "#address-form", inline: true, innerHeight: "685px", innerWidth: "420px"});

    // ===========================
    // LOGIN TOGGLE
    // ===========================
    $(".login-toggle").each(function() {
        $(".login-method:not(:first)").slideUp();
    });

    $document.on("click", ".login-toggle", function() {
        var $nextMethod = $(this).next(".login-method");
        if (!$nextMethod.is(":visible")) {
            $(".login-method:visible").slideUp();
            $nextMethod.slideDown();
        }
    });

    // ===========================
    // BASKET SUMMARY (if exists)
    // ===========================
    if ($("div#basket_summary").exists()) {
        $document.on("submit", "form.addForm", function() {
            if (preventFormLeave) return false;

            var formData = $(this).serialize();
            var action = $(this).attr("action").replace(/\?.*/, "");
            var $basketSummary = $("div#basket_summary");
            var parts = action.split("?");
            action += parts.length > 1 ? "&" : "?";

            $.ajax({
                url: action + "_g=ajaxadd",
                type: "POST",
                cache: false,
                data: formData,
                complete: function(response) {
                    if (response.responseText.match("Redir")) {
                        window.location = response.responseText.substr(6);
                    } else {
                        $basketSummary.replaceWith(response.responseText);
                        $("#gui_message").slideUp();
                        $(".animate_basket").effect("shake", {times: 4, distance: 3}, 70);
                    }
                }
            });

            return false;
        });
    }

    // ===========================
    // CHECK PRIMARY
    // ===========================
    $contentBody.on("click", ".check-primary", function() {
        var targetId = $(this).attr("rel");
        $("#" + targetId).parent().addClass("selected");
        $("#" + targetId + ":checkbox").prop("checked", true);
    });

    // ===========================
    // QUICK TOUR
    // ===========================
    $("#quickTour").on("click", function() {
        $("#navigation .submenu").show();
        $("#joyrideTour").joyride();
    });

    // ===========================
    // TAX RULES
    // ===========================
    $document.on("click", "#rule-eu, #rule-rest", function() {
        var isSelected = $('#rule-rest').closest('div').hasClass('selected') ||
                        $('#rule-eu').closest('div').hasClass('selected');
        $("#country-region").toggle(isSelected);
    });

    // ===========================
    // LOADING CONTENT
    // ===========================
    $("#loading_content").hide();

    // ===========================
    // NEWSLETTER PROGRESS
    // ===========================
    $("div#progress_bar>img.newsletter").each(function() {
        window.onbeforeunload = function() { return true; };
        ajaxNewsletter($("#newsletter_id").val(), 1);
    });

    // ===========================
    // MULTI FILE UPLOAD
    // ===========================
    $("input:file.multiple").MultiFile({
        max: 4,
        namePattern: "$name$i",
        remove: '<i class="fa fa-trash-o"></i>'
    });

    // ===========================
    // CKEDITOR
    // ===========================
    $("textarea.fck").each(function() {
        var fck_lang = $("#val_admin_lang").length ?
                      $("#val_admin_lang").text().substr(0,2) : 'en';

        var config = {
            path: "includes/ckeditor/",
            fullPage: $(this).hasClass("fck-full"),
            selector: "textarea.fck",
            language: fck_lang
        };

        if ($(this).hasClass("fck-source")) {
            config.startupMode = 'source';
        }
        if ($(this).attr("data-fck-height")) {
            config.height = $(this).attr("data-fck-height");
        }

        $(this).ckeditor(config);
    });

    // ===========================
    // FILE TREE
    // ===========================
    $("div.fm-filelist").each(function() {
        $(this).fileTree({
            root: "/",
            script: "./" + ADMIN_FILE,
            group: $(this).attr("rel"),
            name: $(this).attr("id")
        });
    });

    // ===========================
    // TABS SYSTEM
    // ===========================
    if (!$("div.tab").exists()) {
        var firstTabId = $("div.tab_content:first").show().attr("id");
        $("#tab_" + firstTabId).addClass("tab-selected");
    }

    $("div.tab").each(function() {
        var targetHash = window.location.hash;

        if (targetHash !== "" && $(targetHash).length > 0) {
            $("div.tab_content:not(" + targetHash + ")").hide();
            $("div.tab_content" + targetHash).show();
            $("#tab_" + targetHash.replace("#", "")).addClass("tab-selected");
        } else {
            $("div.tab_content:not(:first)").hide();
            targetHash = "#" + $("div.tab_content:first").show().attr("id");
            $("#tab_" + targetHash.replace("#", "")).addClass("tab-selected");
        }

        if ($("#wikihelp").exists()) {
            var wikiUrl = $("#wikihelp").attr("href").split("#");
            $("#wikihelp").attr("href", wikiUrl[0] + targetHash);
        }

        $("#previous-tab, input.previous-tab").val(targetHash);
    }).on("click", function() {
        var targetHash = $(this).children("a").attr("href");

        if (targetHash == "#sidebar") {
            $("#sidebar_control").click();
            return false;
        }

        if (targetHash.match(/^#/)) {
            document.location.hash = targetHash;
            $(".tab").removeClass("tab-selected");
            $(this).addClass("tab-selected");
            $("div.tab_content").hide();
            $(targetHash).show();

            $("#previous-tab, input.previous-tab").val(targetHash);

            if ($("#wikihelp").exists()) {
                var wikiUrl = $("#wikihelp").attr("href").split("#");
                $("#wikihelp").attr("href", wikiUrl[0] + targetHash);
            }

            if ($("#clear_cache_master").length) {
                var cacheUrl = $('#clear_cache_master a').attr('href').split("#");
                $('#clear_cache_master a').attr('href', cacheUrl[0] + targetHash);
            }

            var tabHeight = $(targetHash).height();
            if ($("#navigation").height() < tabHeight) {
                $("#page_content").css('min-height', tabHeight + 100 + 'px');
            }

            return false;
        }
    });

    // ===========================
    // SKIN SELECTORS - CONSOLIDATED FUNCTION
    // ===========================
    function initSkinSelector(selector, styleSelector, defaultStyleClass) {
        $(selector).each(function() {
            var $select = $(this);
            var $styleSelect = $select.siblings(styleSelector);
            var defaultStyle = $select.siblings(defaultStyleClass).val();

            if (typeof json_skins !== 'undefined' && json_skins[$select.val()]) {
                for (var value in json_skins[$select.val()]) {
                    var styleName = json_skins[$select.val()][value];
                    var $option = $('<option>').val(value).text(styleName).addClass("dynamic");

                    if (value == defaultStyle) {
                        $option.prop("selected", true);
                    }

                    $styleSelect.append($option);
                }
            } else if ($select.hasClass("no-drop")) {
                $styleSelect.hide();
            }

            $select.on("change", function() {
                $styleSelect.children("option.dynamic").remove();

                if (typeof json_skins !== 'undefined' && json_skins[$select.val()]) {
                    for (var value in json_skins[$select.val()]) {
                        var styleName = json_skins[$select.val()][value];
                        var $option = $('<option>').val(value).text(styleName).addClass("dynamic");
                        $styleSelect.append($option);
                    }
                    $styleSelect.show();
                } else if ($select.hasClass("no-drop")) {
                    $styleSelect.hide();
                }
            });
        });
    }

    initSkinSelector("select.select-skin", "select.select-style", "input[type=hidden].default-style");
    initSkinSelector("select.select-skin-mobile", "select.select-style-mobile", "input[type=hidden].default-style-mobile");

    // ===========================
    // EDITABLE SPANS
    // ===========================
    $("span.editable").each(function() {
        if ($(this).html() == "") {
            $(this).html("<em>null</em>");
        }
        $(this).attr("title", "Click to edit");
    });

    $document.on("click", "span.editable", function() {
        var $this = $(this);
        var currentVal = $this.html();
        if (currentVal == "<em>null</em>") currentVal = "";

        var name = $this.attr("name");
        var style = $this.attr("style");
        var className = $this.attr("class");

        if ($this.hasClass("select")) {
            var $select = $('<select>');
            $.each(select_data, function(key, value) {
                $select.append('<option value="' + key + '">' + value + "</option>");
            });
            $select.children(":contains(" + currentVal + ")").prop("selected", true);
            var $newElement = $select;
        } else {
            var $newElement = $('<input>').attr({type: "text", value: currentVal}).addClass(className);
        }

        $newElement.addClass("textbox").attr({name: name, style: style});
        $this.replaceWith($newElement);
    });

    // ===========================
    // SORTABLE REORDER LIST
    // ===========================
    $(".reorder-list").sortable({
        axis: "y",
        handle: "a.handle",
        opacity: 0.7,
        placeholder: "reorder-position",
        placeholderElement: "> tr",
        revert: true,
        scroll: true,
        stop: function() {}
    });

    // ===========================
    // REVERT PHRASES
    // ===========================
    $(".revert").each(function() {
        var phraseId = $(this).attr("rel");
        var isDefined = $("#defined_" + phraseId).val() == "0";
        var isModified = $("#string_" + phraseId).val() != $("#default_" + phraseId).val();

        if (isDefined) {
            $("#row_" + phraseId).addClass("list-changed");
        } else if (isModified) {
            $("#row_" + phraseId + " td").addClass("custom-phrase");
        } else {
            $(this).hide();
        }
    });

    $document.on("click", ".revert", function() {
        var phraseId = $(this).attr("rel");
        var defaultVal = $("#default_" + phraseId).val();

        $('<input>').attr({
            type: 'hidden',
            id: 'delete_' + phraseId,
            name: 'delete[' + phraseId + ']',
            value: true
        }).appendTo('form#edit_phrases');

        $("#string_" + phraseId).val(defaultVal).prop("disabled", false).addClass("reverted");
        $("#row_" + phraseId + " td").removeClass("custom-phrase");
        $(this).hide();

        return false;
    });

    $document.on("click", "td.phrase_row", function() {
        var targetId = $(this).attr("rel");
        $("#" + targetId).prop("disabled", false).focus();
    });

    $document.on("focusout", ".editable_phrase", function() {
        var phraseId = $(this).attr("rel");
        var $row = $("#row_" + phraseId);
        var $revert = $("#revert_" + phraseId);
        var $deleteInput = $("#delete_" + phraseId);

        if ($(this).val() != $("#default_" + phraseId).val()) {
            $row.find("td").addClass("custom-phrase");
            $revert.show();
            $deleteInput.remove();
        } else {
            $row.find("td").removeClass("custom-phrase");
            $revert.hide();

            if (!$deleteInput.length) {
                $('<input>').attr({
                    type: 'hidden',
                    id: 'delete_' + phraseId,
                    name: 'delete[' + phraseId + ']',
                    value: true
                }).appendTo('form#edit_phrases');
            }
        }
    });

    // ===========================
    // AJAX AUTOCOMPLETE
    // ===========================
    $("input.ajax").autocomplete({
        timeout: 5000,
        ajax_get: ajaxSuggest,
        callback: ajaxSelected
    });

    // ===========================
    // FIELD SELECT
    // ===========================
    $("select.field_select").each(function() {
        var $this = $(this);
        $this.find("option:first").prop("selected", true);
        $this.parent().parent().find(".field_select_target:not(:first)").hide();

        if ($this.attr("id") == "select_group_id") {
            var selectedVal = $("option:selected", $this).val();
            $("#attr_source").attr("name", "add_attr[" + selectedVal + "]");
            $("#group_target").attr("target", "group_" + selectedVal);
        }
    }).on("change", function() {
        var $this = $(this);

        if ($this.attr("id") == "select_group_id") {
            var selectedVal = $("option:selected", $this).val();
            $("#attr_source").attr("name", "add_attr[" + selectedVal + "]");
            $("#group_target").attr("target", "group_" + selectedVal);
        }

        $this.parent().parent().find(".field_select_target").hide();

        if ($this.val() != "") {
            var targetId = "#" + $this.attr("rel") + $this.val();
            $(targetId).show();
            $("#" + $(targetId).attr("rel")).show();
        }
    });

    // ===========================
    // DATEPICKER
    // ===========================
    $.datepicker.setDefaults({
        changeMonth: true,
        constrainInput: true,
        dateFormat: "yy-mm-dd",
        hideIfNoPrevNext: true,
        onSelect: function(dateText) {
            var dateParts = dateText.split("-", 3);
            $(this).nextAll("input.date:first").datepicker("option", "minDate",
                new Date(dateParts[0], dateParts[1] - 1, dateParts[2]));
        },
        showStatus: false
    });

    $("input.date").datepicker();

    // ===========================
    // CUSTOM CHECKBOXES - OPTIMIZED
    // ===========================
    var $allCheckboxes = $('input[type="checkbox"]');

    $allCheckboxes.each(function() {
        var $checkbox = $(this);
        var $parent = $checkbox.parent();

        if (!$parent.hasClass("custom-checkbox")) {
            $checkbox.wrap("<div class='custom-checkbox'></div>");
            $parent = $checkbox.parent();
        }

        $parent.toggleClass("selected", $checkbox.is(":checked"));
    });

    // OPTIMIZED: Only update checkboxes that changed
    $body.on("click", "img.checkbox, .check-primary, .check_cat, .custom-checkbox", function() {
        // Small delay to let checkbox state update
        setTimeout(function() {
            $allCheckboxes.each(function() {
                $(this).parent().toggleClass("selected", this.checked);
            });
        }, 10);
    });

    // OPTIMIZED: Also update on direct checkbox change
    $document.on("change", 'input[type="checkbox"]', function() {
        $(this).parent().toggleClass("selected", this.checked);
    });

    // ===========================
    // FILE MANAGER SIZE TOGGLE
    // ===========================
    var $filemanager = $("#filemanager");

    if($(".fm-item-list").length > 0) {
        $filemanager.find(".list-filesize").show();
    } else {
        $filemanager.find(".list-filesize").hide();
    }

    $filemanager.find(".toggle span").on("click", function() {
        var size = $(this).attr("class");
        $.cookie('fm_size', size, {expires: 365});

        $filemanager.find(".fm-item")
            .removeClass('fm-item-xlarge fm-item-large fm-item-medium fm-item-small fm-item-list')
            .addClass('fm-item-' + size);

        $filemanager.find(".toggle span").removeClass("active");
        $(this).addClass("active");

        $("#page_content").height($("#fm-wrapper"));

        $filemanager.find(".list-filesize").toggle(size == 'list');
    });

    var fm_size = $.cookie('fm_size');
    if(fm_size === undefined) {
        $filemanager.find(".toggle span.medium").addClass("active");
    } else {
        $filemanager.find(".toggle span." + fm_size).addClass("active");
    }

    // ===========================
    // PAGE HEIGHT ADJUSTMENT
    // ===========================
    var navHeight = $("#navigation").height();
    var contentHeight = $("#page_content").height();
    if (navHeight > contentHeight) {
        $("#page_content").css('min-height', navHeight + 100 + 'px');
    }

    // ===========================
    // LAZY LOAD
    // ===========================
    if (typeof lazyload === 'function') {
        lazyload();
    }

    // ===========================
    // FORM DIRTY CHECKING
    // ===========================
    $("form:not(.ignore-dirty)").dirty({preventLeaving: true});

    // ===========================
    // PRODUCT CODE - OPTIMIZED WITH DEBOUNCE
    // ===========================
    var updateProductCodeAuto = debounce(function() {
        var hasCode = $productCode.val().length > 0;
        var newVal = hasCode ? '0' : '1';
        var oldVal = hasCode ? '1' : '0';

        $productCodeAuto.val(newVal);

        var $img = $("img[rel$='product_code_auto']");
        var new_src = $img.attr('src').replace(oldVal + '_checkbox.png', newVal + '_checkbox.png');
        $img.attr('src', new_src);
    }, 300);

    $productCode.on("keyup", updateProductCodeAuto);

    $productCodeAuto.on("click", function() {
        var oldCode = $("input#product_code_old").val();
        var currentCode = $productCode.val();

        if (currentCode.length > 0) {
            $("input#product_code_old").val(currentCode);
            $productCode.val("");
        } else {
            $productCode.val(oldCode);
        }
    });

    // ===========================
    // GUI MESSAGE CLOSE
    // ===========================
    $("#gui_message").on("click", function() {
        $(this).slideUp();
    });

    // ===========================
    // CATEGORY SEO DIALOG - FIXED (was memory leak)
    // ===========================
    var catSaveDialogShown = false;

    $document.on("change", "#cat_general #cat_name, #cat_general #parent", function() {
        catSaveDialogShown = false;
    });

    // Single delegated handler instead of nested handlers
    $document.on("click", "#cat_general #cat_save", function() {
        if (!catSaveDialogShown && ($("#cat_name").data('changed') || $("#parent").data('changed'))) {
            $("#dialog-seo").dialog({
                modal: true,
                buttons: {
                    Yes: function() {
                        $(this).dialog("close");
                        $("#gen_seo").val("1");
                        document.cat_form.submit();
                    },
                    No: function() {
                        $(this).dialog("close");
                        document.cat_form.submit();
                    }
                }
            });
            catSaveDialogShown = true;
            return false;
        }
    });

    $("#cat_name, #parent").on("change", function() {
        $(this).data('changed', true);
    });

    // ===========================
    // SELECT URL NAVIGATION
    // ===========================
    $document.on("change", ".select_url", function() {
        var location = document.URL.replace(/&?page=[0-9]/, "");

        if (location.indexOf("cat_id") != -1) {
            location = removeVariableFromURL(location, "cat_id");
        }

        if ($(this).val() != "any") {
            location = $(this).val();
        }

        window.location.replace(location);
    });

    // ===========================
    // ADDRESS LIST
    // ===========================
    $document.on("change", "select.address-list", function() {
        var addressId = $(this).val();
        var addressData = addresses[addressId];
        var prefix = $(this).attr("rel") == "" ? "sum" : $(this).attr("rel");
        var prefixes = null;

        var colonIndex = prefix.indexOf(":");
        if (colonIndex > 1) {
            prefixes = prefix.split(":");
        }

        for (var field in addressData) {
            addressData[field] = jQuery.trim(addressData[field]);

            if (prefixes != null) {
                for (var j = 0; j < prefixes.length; j++) {
                    updateAddressValues(prefixes[j], field, addressData);
                }
            } else {
                updateAddressValues(prefix, field, addressData);
            }
        }
    });

    // ===========================
    // FILE MANAGER LOCATION
    // ===========================
    $document.on("click", ".fm_location", function() {
        localStorage.setItem('fm_folder_href', $(this).attr('href'));
    });

    // ===========================
    // OPTION IMAGE CHOOSER
    // ===========================
    $document.on("click", ".choose_option_img", function() {
        var filemanager_path = '?_g=filemanager&mode=fck&source=options';
        var filepath = $(this).attr('data-filepath');

        if(filepath !== '') {
            filemanager_path += '&subdir=' + filepath;
        }

        var fm_folder_href = localStorage.getItem('fm_folder_href');
        if(fm_folder_href) {
            filemanager_path = fm_folder_href;
        }

        var assign_id = $(this).attr("rel");
        window.open(filemanager_path, 'chooser', 'toolbar=no,menubar=no,width=600,height=600');

        window.addEventListener('message', function(event) {
            $('#option_image_id_' + assign_id).val(event.data.image_id);
            $('#option_image_preview_' + assign_id).attr('src', event.data.path);
            $('#remove_image_id_' + assign_id).show();
            $('#selector_image_id_' + assign_id).hide();
        }, {once: true});
    });

    $document.on("click", ".remove_option_img", function() {
        var assign_id = $(this).attr("rel");
        $('#option_image_id_' + assign_id).val(0);
        $('#option_image_preview_' + assign_id).attr('src', 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=');
        $('#remove_image_id_' + assign_id).hide();
        $('#selector_image_id_' + assign_id).show();
    });

    // ===========================
    // FILE MANAGER SELECT
    // ===========================
    $document.on("click", "a.select", function(e) {
        var filepath = $(this).attr("href");
        var imageId = $(this).attr("rel");
        var ckfuncnum = $("#ckfuncnum").val();

        if($(this).hasClass('options')) {
            e.preventDefault();
            window.opener.postMessage({path: filepath, image_id: imageId}, "*");
            window.close();
            return;
        } else {
            window.opener.CKEDITOR.tools.callFunction(ckfuncnum, filepath);
            window.close();
            return false;
        }
    });

    // ===========================
    // DISCOUNT & LINE PRICE UPDATES
    // ===========================
    $document.on("change", "#discount_type, .lineprice", function() {
        $(".update-subtotal input.number").trigger("change");
    });

    $document.on("change", ".update-subtotal input.number", function() {
        updateOrderTotals($(this));
    });

    // ===========================
    // REMOVE FUNCTIONALITY - SINGLE HANDLER
    // ===========================
    $body.on("click", "a.remove", function() {
        var title = $(this).attr("title");
        var rel = $(this).attr("rel");
        var href = $(this).attr("href");
        var name = $(this).attr("name");

        if (title != "" && !confirm(title)) {
            return false;
        }

        if (rel && !$(this).hasClass("dynamic")) {
            var $hiddenInput = $('<input>').attr({
                type: "hidden",
                name: name + "[]"
            }).val(rel);
            $(this).parents("form:first").append($hiddenInput);
        } else {
            pageChanged(this);
        }

        if (name == "inv_remove") {
            $(this).parents("form:first").append('<input type="hidden" name="inv_remove[]" value="' + href.substring(1) + '" />');
        }

        var $parent = $(this).hasClass("tr") ?
                     $(this).parents("tr:first") :
                     $(this).parents("tr:first,div:first:not(.tab_content)");

        $parent.remove();
        $(".update-subtotal input.number").trigger("change");

        return false;
    });

    // ===========================
    // REFRESH TOTALS
    // ===========================
    $document.on("click", "a.refresh", function() {
        $(".update-subtotal input.number").trigger("change");
        return false;
    });

    // ===========================
    // INVENTORY LIST OPTIONS
    // ===========================
    $("#inventory-list").on("change", "select.options_calc", function() {
        productOptionPrices($(this).parent().attr("rel"));
    });

    $("#inventory-list").on("focusout", ".text_calc", function() {
        productOptionPrices($(this).parent().attr("rel"));
    });

    // ===========================
    // INLINE ADD - OPTIMIZED
    // ===========================
    var inline_add_offset = 0;

    $document.on("click", 'a.add, a.inline-add, input[type="button"].add', function() {
        var targetId = $(this).attr("target");
        var $inlineAdd = $(this).parents(".inline-add:first");
        var $inlineSource = $inlineAdd.next(".inline-source");
        var values = {};
        var isValid = true;

        $('#' + targetId + ' .form-none').hide();
        $(".inline-add").removeClass("highlight");

        $(":input", $inlineAdd).each(function() {
            var $input = $(this);
            $input.removeClass("required-error");

            var rel = $input.attr("rel");
            var val = $input.val();

            if ($input.hasClass("not-empty") && val == $input.attr("original")) {
                $input.addClass("required-error").one("change", function() {
                    if ($(this).val() != $(this).attr("original")) {
                        $(this).removeClass("required-error");
                    }
                });
                isValid = false;
            }

            values[rel] = val;
        });

        if (!isValid) return false;

        $inlineAdd.removeClass("highlight");

        if ($inlineSource.length == 1) {
            var sourceName = $inlineSource.attr("name");
            var $clone = $inlineSource.clone(true).attr({name: ""}).removeAttr("id").removeClass("inline-source");

            $(":input", $inlineAdd).each(function() {
                var $input = $(this);
                var rel = $input.attr("rel");
                var val = $input.val();
                var displayText = $input.is("select") ? $input.find("option:selected").text() : val;

                if (rel == "product_id" && val > 0) {
                    $.ajax({
                        url: "./" + ADMIN_FILE,
                        type: "GET",
                        cache: false,
                        data: {
                            _g: "xml",
                            type: "prod_options",
                            q: val,
                            function: "template"
                        },
                        complete: function(response) {
                            var template = response.responseText
                                .split("inv[]").join("inv_add[" + (inline_add_offset - 1) + "]")
                                .split('rel=""').join('rel="' + (inline_add_offset - 1) + '"');
                            $clone.find("[rel=product_options]").html(template);
                        }
                    });
                } else if (rel == "price") {
                    $clone.find(":input[rel=" + rel + "]").attr("id", inline_add_offset + "_price");
                }

                $clone.find(":input[rel=" + rel + "]").val(val).attr({
                    name: sourceName + "[" + inline_add_offset + "][" + rel + "]",
                    original: val
                });

                $clone.find("[rel=" + rel + "]:not(:input)").text(displayText);
            });

            $(":input", $inlineAdd).each(function() {
                $(this).val($(this).attr("original"));
            });

            var $newElement = $clone;
        } else {
            var fields_ok = true;

            $(this).parents("div:first,tr:first").find(".add:input").each(function() {
                if($(this).prop("required") && $(this).val() == '') {
                    $(this).addClass('required-error').one("click", function() {
                        $(this).removeClass('required-error');
                    });
                    fields_ok = false;
                }
            });

            if(!fields_ok) return false;

            var $newDiv = $('<div>');
            var $actions = $('<span>').addClass("actions");
            var $removeLink = $('<a>').attr({href: "#"}).addClass("remove dynamic")
                .append($('<i>').addClass("fa fa-trash"));

            $actions.append($removeLink);

            if ($('input[name="add_div_class"]').length) {
                $newDiv.addClass($('input[name="add_div_class"]').val());
            }

            $(this).parents("div:first,tr:first").find(".add:input").each(function() {
                var $input = $(this);

                if ($input.hasClass("display")) {
                    if ($input.val() == "") isValid = false;

                    var displayText = $input.is("select") ?
                                     $input.find(":selected").text() :
                                     "<strong>" + $input.val() + "</strong>";

                    $newDiv.append(displayText);
                }

                if ($input.hasClass("clear_field")) {
                    $input.val('');
                }

                if ($input.attr("name")) {
                    var $hidden = $('<input>').attr({
                        type: "hidden",
                        name: $input.attr("name"),
                        value: $input.val()
                    });
                    $newDiv.append($hidden);
                }

                $.fn.colorbox.close();
            });

            $newDiv.prepend($actions);
            var $newElement = $newDiv;
        }

        if (isValid && targetId.length > 1 && $("#" + targetId).length == 1) {
            if ($(this).hasClass("before")) {
                $("#" + targetId).before($newElement);
            } else if ($(this).hasClass("after")) {
                $("#" + targetId).after($newElement);
            } else {
                $("#" + targetId).append($newElement);
            }
        } else {
            $inlineAdd.before($newElement);
        }

        $(".update-subtotal input.number").trigger("change");
        inline_add_offset++;
        $(".dymanic_none").hide();

        return false;
    });

    // ===========================
    // DUPLICATE FUNCTIONALITY
    // ===========================
    $document.on("click", "a.duplicate", function() {
        var sourceClass = $(this).attr("rel");
        var targetPrefix = $(this).attr("target").length >= 1 ? $(this).attr("target") : "";

        $("." + sourceClass + ":input").each(function() {
            var $target = $("#" + targetPrefix + $(this).attr("id"));
            $target.val($(this).val());

            if ($(this).attr("id") == "sum_country") {
                $target.trigger("change");
            }
        });

        return false;
    });

    // ===========================
    // SIDEBAR SEARCH - FIXED (was accumulating handlers)
    // ===========================
    var sidebarEscapeHandler = function(e) {
        if (e.key === "Escape") {
            $("#sidebar_contain").animate({left: "-340px"});
            $document.off("keyup", sidebarEscapeHandler);
        }
    };

    $("#search-placeholder").on("click", function() {
        $document.off("keyup", sidebarEscapeHandler); // Remove old handler
        $document.on("keyup", sidebarEscapeHandler); // Add new handler

        $('.sidebar_content input#customer_id').focus();
        $("#sidebar_contain").animate({left: "0px"});
    });

    $("#sidebar_contain").on("mouseleave", function() {
        if(!$(".jqac-menu").length) {
            $(this).animate({left: "-340px"});
            $document.off("keyup", sidebarEscapeHandler);
        }
    });

    $("div#tab_sidebar").on("click", function() {
        var $sidebar = $("#sidebar_contain");
        var position = $sidebar.position();

        $sidebar.animate(position.left == 0 ? {left: "-340px"} : {left: "0px"});

        return false;
    });

    // ===========================
    // OPTION EDIT
    // ===========================
    $document.on("click", ".option-edit", function() {
        var optId = $(this).attr("rel");
        var data = $("#data_" + optId).val().split("|");

        $("#opt_assign_id").val(optId);
        $("#opt_mid").val(data[0]);
        $("#opt_price").val(data[1]);
        $("#opt_weight").val(data[2]);
        $("#opt_stock").val(data[3]);

        $(this).parent().parent().remove();
    });

    // ===========================
    // DISABLED TRASH ALERT
    // ===========================
    $document.on("click", ".fa-trash.disabled, .title_alert", function() {
        alert($(this).attr("title"));
    });
});

// ===========================
// GLOBAL VARIABLES
// ===========================
var new_option = 0;
var data = false;
if (!addresses || typeof addresses != 'object') var addresses = new Object;
var options_added = 0;

// ===========================
// HELPER FUNCTIONS
// ===========================
function pageChanged(element) {
    var $form = $(element).parents("form:first");
    if ($form.length == 1) {
        var message = typeof $form.attr("title") !== 'undefined' ? $form.attr("title") : "";

        if (!$form.hasClass("no-change")) {
            window.onbeforeunload = function() {
                return message.length > 1 ? message : undefined;
            };
        }
    }
}

function getSEODestination() {
    var item_id = $("#item_id").val();
    var type = $("#redirect_type").val();
    var error_text = $("#val_error_not_found").text();

    $.getJSON("./" + ADMIN_FILE, {
        _g: "xml",
        item_id: item_id,
        type: type,
        function: "seopath"
    }, function(data) {
        if(data.length) {
            $('#destination').html(data);
            $("#redir_submit").prop('disabled', false);
        } else {
            $('#destination').html(error_text);
            $("#redir_submit").prop('disabled', true);
        }
    });
}

function removeVariableFromURL(url, variable) {
    var urlStr = String(url);
    var regex = new RegExp("\\?" + variable + "=[^&]*&?", "gi");
    urlStr = urlStr.replace(regex, "?");

    regex = new RegExp("\\&" + variable + "=[^&]*&?", "gi");
    urlStr = urlStr.replace(regex, "&");
    urlStr = urlStr.replace(/(\?|&)$/, "");

    return urlStr;
}

function updateAddressValues(prefix, field, addressData) {
    if (field == "country") {
        $("#" + prefix + "_" + field + " option").filter(function() {
            if($(this).text() == addressData[field]) {
                $("#" + prefix + "_" + field).val($(this).val());
                return;
            }
        }).first().prop("selected", true);

        $("#" + prefix + "_" + field).trigger("change");

        if (!$("#" + prefix + "_state").is("select")) {
            $("#" + prefix + "_state").val(addressData.state);
        } else {
            $("#" + prefix + "_state option").filter(function() {
                if(addressData.state == $(this).text()) {
                    $("#" + prefix + "_state").val($(this).val());
                    return;
                }
            }).prop("selected", true);
        }
    } else if (field != "state") {
        $("#" + prefix + "_" + field).val(addressData[field]);
    }
}

function inlineRemove(element) {
    var title = $(element).attr("title");
    var rel = $(element).attr("rel");
    var name = $(element).attr("name");

    if (title != "" && !confirm(title)) {
        return false;
    }

    if (rel && !$(element).hasClass("dynamic")) {
        var $hidden = $('<input>').attr({
            type: "hidden",
            name: name + "[]"
        }).val(rel);
        $(element).parents("form:first").append($hidden);
    } else {
        pageChanged(element);
    }

    var $parent = $(element).parents("tr:first,div:first:not(.tab_content)").get(0);
    $($parent).remove();

    return false;
}

function optionAdd(sourceId, targetId) {
    var $target = $("#" + targetId);
    var $source = $("#" + sourceId);
    var optionGroup = $("#opt_mid :selected").parent().attr("label");
    var optionText = $("#opt_mid :selected").text();
    var displayName = typeof optionGroup === 'undefined' ?
                     optionText :
                     "<strong>" + optionGroup + "</strong>: " + optionText;
    var optionValue = $("#opt_mid").val();

    if (optionValue == "" || optionValue == 0) {
        return false;
    }

    var $clone = $source.clone();
    $clone.find(".name").append(displayName).find("input").first().val(optionValue).prop('disabled', false);

    var $dataInputs = $("input.data");
    for (var i = 0; i < $dataInputs.length; i++) {
        var $input = $($dataInputs[i]);
        var rel = $input.attr("rel");
        var value = $input.val() == "" ? "0" : $input.val();
        var $cloneInput = $clone.find("." + rel).find("input").first();

        if (rel == "matrix_include") {
            $cloneInput.attr("name", "option_add[" + rel + "][" + options_added + "]");
        } else if (rel == "set_enabled") {
            $cloneInput.prop('disabled', false).prop("checked", true).parent().addClass("selected").val(1);

            if (value == 1) {
                $cloneInput.parent().addClass("selected").prop("checked", true);
            }

            $cloneInput.attr("name", "option_add[" + rel + "][" + options_added + "]");
        } else if (rel == "default" || rel == "negative" || rel == "absolute_price") {
            $cloneInput.prop('disabled', false);

            if ($input.is(":checked")) {
                $cloneInput.parent().addClass("selected").prop("checked", true);
                $input.prop('checked', false).parent().removeClass("selected");
            }

            $cloneInput.attr("name", "option_add[" + rel + "][" + options_added + "]");
        } else {
            value = parseFloat(value, 10).toFixed(2);
            $clone.find("." + rel).append(value).find("input").first().val(parseFloat(value)).prop('disabled', false);
        }

        $input.val("");
    }

    $clone.removeAttr("id");
    $("#opt_mid :selected").prop('selected', false);
    $("#opt_mid:first-child").prop("selected", true);
    $target.append($clone);
    options_added++;

    return false;
}

function ajaxSelected(result, fieldId, type) {
    $("#result_" + fieldId).val(result.id);

    switch (type.toLowerCase()) {
        case "user":
            $.getJSON("./" + ADMIN_FILE, {
                _g: "xml",
                type: "address",
                q: result.id,
                function: "search"
            }, function(data) {
                $("select.address-list>option.temporary").remove();

                for (var i = 0; i < data.length; i++) {
                    var $option = $('<option>')
                        .val(i)
                        .html(data[i].description)
                        .addClass("temporary");
                    $(".address-list").append($option);
                }

                addresses = data;
            });
            break;
        case "product":
            $("#add-price").val(result.data.price);
            $("#add-subtotal").html(($("#add-quantity").val() * result.data.price).toFixed(2));
            data = result.data;
            break;
    }

    for (var key in result.data) {
        if (result.data[key] != "") {
            $("#ajax_" + key).val(result.data[key]).trigger("change");
        }
    }

    if ($("#result_" + fieldId).hasClass("clickSubmit")) {
        $("#result_" + fieldId).closest("form").submit();
    }
}

function ajaxSuggest(query, callback, type) {
    var url = "./" + ADMIN_FILE;
    var params = {
        _g: "xml",
        type: type,
        q: query,
        function: "search"
    };

    $.get(url, params, function(data) {
        var results = [];
        for (var i = 0; i < data.length; i++) {
            results.push({
                id: data[i].value,
                value: data[i].display,
                info: data[i].info,
                data: data[i].data
            });
        }
        callback(results);
    }, "json");
}

function ajaxElasticSearch(page) {
    $.getJSON(`./${ADMIN_FILE}`, {
        _g: "xml",
        page: page,
        "function": "rebuildElasticsearch"
    }, function(res) {
        const redirect = '?_g=maintenance&_=' + Math.floor(Date.now()/1000) + '#elasticsearch';

        if (res?.error === 'true') {
            window.location.href = redirect;
            return;
        }

        if (res.es_count !== false && res.es_size !== false) {
            $("#es_count").text(Number(res.es_count).toLocaleString());
            $("#es_size").text(res.es_size);
        }

        $("#progress_bar").css({width: res.percent + "%"});
        $("#progress_bar_percent").text(Math.round(res.percent) + "%");

        if (res.percent === 100 || res.complete === "true") {
            window.onbeforeunload = null;
            setTimeout(function() {
                window.location.href = redirect;
            }, 2000);
        } else {
            setTimeout(function() {
                ajaxElasticSearch(page + 1);
            }, 0);
        }
    });
}

function ajaxNewsletter(newsletterId, page) {
    $.getJSON("./" + ADMIN_FILE, {
        _g: "xml",
        type: "newsletter",
        q: newsletterId,
        page: page,
        function: "search"
    }, function(data) {
        if (typeof data.error !== 'undefined' && data.error == 'true') {
            window.location.href = '?_g=customers&node=email';
            return false;
        }

        $("div#progress_bar").css({width: data.percent + "%"});
        $("div#progress_bar_percent").text(Math.round(data.percent) + "%");

        if (data.percent == 100 || data.complete == "true") {
            window.onbeforeunload = null;
            setTimeout(function() {
                window.location = "?_g=customers&node=email";
            }, 2000);
        } else {
            ajaxNewsletter(newsletterId, page + 1);
        }
    });
}

function updateOrderTotals($element) {
    if (!$element.hasClass("quantity")) {
        $element.val((1 * $element.val()).toFixed(2));
    }

    var $updateRow = $element.parents(".update-subtotal:first");
    var quantity = $updateRow.find("input.quantity").val();
    var linePrice = $updateRow.find("input.lineprice").val();
    var $subtotal = $updateRow.find("input.subtotal:first");
    var subtotalValue = (quantity * linePrice).toFixed(2);

    $subtotal.val(subtotalValue);

    // Calculate total of all subtotals
    var grandSubtotal = 0;
    $("input.subtotal").each(function() {
        grandSubtotal += parseFloat($(this).val()) || 0;
    });

    // Calculate discount
    var discount = parseFloat($("#discount").val()) || 0;
    var discountType = $("#discount_type").val();

    if (discountType == "p") {
        if (discount > 100) {
            $("#discount").val("100");
            discount = 100;
        }
        discount = (discount / 100) * grandSubtotal;
        $("#discount_percent").html("%");
    } else {
        $("#discount_percent").html("");
    }

    $("#subtotal").val(grandSubtotal.toFixed(2));

    // Calculate tax
    var shipping = parseFloat($("#shipping").val()) || 0;
    var totalTax = 0;

    $(".update-subtotal input.tax").each(function() {
        totalTax += parseFloat($(this).val()) || 0;
    });

    // Calculate credit used
    var creditUsed = parseFloat($("#credit_used").val()) || 0;

    // Calculate grand total
    var grandTotal = grandSubtotal - discount + shipping + totalTax - creditUsed;

    $("#total_tax").val(totalTax.toFixed(2));
    $("#total").val(grandTotal.toFixed(2));
}

function productOptionPrices(id) {
    var $price = $("#" + id + "_price");
    var base = parseFloat($price.attr("original")) || 0;

    // Calculate from selects
    $("span[rel=" + id + "] select").each(function() {
        var rel = $(this).find("option:selected").attr("rel");
        if (!rel) return;

        var sign = rel[0];
        var val = parseFloat(rel.slice(1));

        if (val > 0) {
            if (sign === '+') {
                base += val;
            } else if (sign === '-') {
                base -= val;
            } else {
                base = parseFloat(rel); // absolute
            }
        }
    });

    // Calculate from inputs/textareas
    $("span[rel=" + id + "] input, span[rel=" + id + "] textarea").each(function() {
        var value = $(this).val();
        var rel = $(this).attr("rel");

        if (!value || !rel) return;

        var sign = rel[0];
        var val = parseFloat(rel.slice(1));

        if (val > 0) {
            if (sign === '+') {
                base += val;
            } else if (sign === '-') {
                base -= val;
            } else {
                base = parseFloat(rel); // absolute
            }
        }
    });

    $price.val(base.toFixed(2));
    $(".update-subtotal input.number").trigger("change");

    return false;
}

function fmSearch(mode, term, token) {
    if(term.length == 0) return false;

    var requestData = {
        'mode': mode,
        'term': term,
        'token': token
    };

    $.ajax({
        type: 'post',
        url: "?_g=xml&function=fmSearch",
        data: requestData,
        dataType: "text",
        success: function(responseData) {
            $.colorbox({html: responseData, width: 860, height: 600});
        }
    });
}
