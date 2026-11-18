/**
 * Email Templates screen (?_g=documents&node=email)
 *
 * Preview and test send. The server builds both with the mailer, so what is shown
 * here is what would be sent. The page describes what is being edited, and supplies
 * the labels, through data attributes on #email_tools.
 */
(function ($) {
    'use strict';

    var $tools = $();

    function label(name) {
        return $tools.attr('data-label-' + name) || '';
    }

    function escapeAttribute(text) {
        return String(text).replace(/&/g, '&amp;').replace(/"/g, '&quot;');
    }

    function escapeText(text) {
        return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // The editors on this page register how to read what they hold, unsaved
    var readers = {
        content_html: function () {
            if (window.CKEDITOR && CKEDITOR.instances.content_html) {
                return CKEDITOR.instances.content_html.getData();
            }
            return $('#content_html').val();
        },
        template_html: function () {
            return $('#template_content_html').val();
        }
    };

    function request(fields, done) {
        var data = {token: $('.cc_session_token').val()};
        $.each(fields, function (key, value) {
            data['email_preview[' + key + ']'] = (value === undefined || value === null) ? '' : value;
        });
        $.ajax({type: 'post', url: window.location.href.split('#')[0], data: data, dataType: 'json'})
            .done(function (result) {
                done(result && result.status ? result : {status: 'error', message: label('failed')});
            })
            .fail(function () {
                done({status: 'error', message: label('failed')});
            });
    }

    // What is on screen now, whether or not it has been saved
    function current() {
        var fields = {scope: $tools.attr('data-scope') || 'content'};
        if (fields.scope === 'template') {
            fields.template_id = $tools.attr('data-template-id') || '';
            fields.template_title = $('#template_desc').val();
            fields.template_html = readers.template_html();
        } else {
            fields.content_id = $tools.attr('data-content-id') || '';
            fields.content_type = $tools.attr('data-content-type') || '';
            fields.subject = $('#content_subject').val();
            fields.preheader = $('#content_preheader').val();
            fields.content_html = readers.content_html();
        }
        fields.sample = $('#email_sample').val() || 'latest';
        return fields;
    }

    function setStatus(text, ok) {
        var $status = $('#email_tools_status');
        $status.removeClass('is-ok is-error').text(text || '');
        if (ok === true) {
            $status.addClass('is-ok');
        } else if (ok === false) {
            $status.addClass('is-error');
        }
    }

    function frameHtml(result) {
        // No scripts, forms or navigation of this window: the email is only looked at
        return '<iframe class="email-preview__frame" sandbox="" title="' + escapeAttribute(result.subject) + '" srcdoc="' + escapeAttribute(result.html) + '"></iframe>';
    }

    function showPreview(result, title) {
        var html = '<div class="email-preview">' +
            '<div class="email-preview__bar">' +
                '<button type="button" class="button email-preview__size is-on" data-width="100%" aria-pressed="true">' + escapeText(label('desktop')) + '</button>' +
                '<button type="button" class="button email-preview__size" data-width="375px" aria-pressed="false">' + escapeText(label('mobile')) + '</button>' +
                '<button type="button" class="button email-preview__as-text" aria-pressed="false">' + escapeText(label('text')) + '</button>' +
            '</div>' +
            '<div class="email-preview__stage">' + frameHtml(result) + '<pre class="email-preview__text" hidden></pre></div>' +
        '</div>';
        $.colorbox({
            title: escapeText(title || result.subject),
            width: '90%',
            height: '90%',
            html: html,
            onComplete: function () {
                $('.email-preview__text').text(result.text || '');
            }
        });
    }

    function preview() {
        var fields = current();
        fields.action = 'preview';
        setStatus(label('loading'), null);
        request(fields, function (result) {
            if (result.status !== 'ok') {
                setStatus(result.message || label('failed'), false);
                return;
            }
            setStatus('', null);
            showPreview(result);
        });
    }

    function sendTest() {
        var fields = current();
        fields.action = 'test';
        fields.to = $.trim($('#email_test_to').val());
        setStatus(label('loading'), null);
        request(fields, function (result) {
            setStatus(result.message || (result.status === 'ok' ? '' : label('failed')), result.status === 'ok');
        });
    }

    /**
     * The main template is a whole HTML document, so it is edited as code (ACE) with the
     * rendered result beside it. The textarea stays the form field: ACE writes into it,
     * and it is all there is if ACE cannot load.
     */
    var liveTimer = null, liveRequest = 0, liveWidth = '100%';

    function refreshLive() {
        var $stage = $('#email_template_preview');
        if (!$stage.length) {
            return;
        }
        var fields = current(), mine = ++liveRequest;
        fields.action = 'preview';
        request(fields, function (result) {
            if (mine !== liveRequest) {
                return; // an answer to something typed over since
            }
            if (result.status !== 'ok') {
                // Keep the last good preview on screen while the code is mid-edit
                setStatus(result.message || label('failed'), false);
                return;
            }
            setStatus('', null);
            $stage.html(frameHtml(result)).find('.email-preview__frame').css('width', liveWidth);
        });
    }

    function scheduleLive() {
        window.clearTimeout(liveTimer);
        liveTimer = window.setTimeout(refreshLive, 900);
    }

    function templateEditor() {
        var $source = $('#template_content_html'), $holder = $('#template_ace');
        if (!$source.length) {
            return;
        }
        if ($holder.length && window.ace) {
            var dark = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
            var editor = ace.edit('template_ace');
            editor.session.setUseWrapMode(true);
            editor.setOptions({
                highlightActiveLine: true,
                showPrintMargin: false,
                theme: (dark && dark.matches) ? 'ace/theme/tomorrow_night' : 'ace/theme/github',
                mode: 'ace/mode/smarty'
            });
            if (dark && dark.addEventListener) {
                dark.addEventListener('change', function (event) {
                    editor.setTheme(event.matches ? 'ace/theme/tomorrow_night' : 'ace/theme/github');
                });
            }
            editor.setValue($source.val(), -1);
            editor.getSession().on('change', function () {
                $source.val(editor.getSession().getValue()).trigger('change');
                scheduleLive();
            });
            $source.prop('hidden', true);
            $holder.prop('hidden', false);
            editor.resize();
        } else {
            $source.on('input', scheduleLive);
        }
        $('#template_desc, #email_sample').on('change', scheduleLive);
        refreshLive();
    }

    $(document).on('click', '.email-preview__size', function () {
        var $bar = $(this).closest('.email-preview__bar'), $frames = $bar.parent().find('.email-preview__frame');
        $bar.find('.email-preview__size').removeClass('is-on').attr('aria-pressed', 'false');
        $(this).addClass('is-on').attr('aria-pressed', 'true');
        $frames.css('width', $(this).attr('data-width'));
        if ($bar.parent().is('.email-template__preview')) {
            liveWidth = $(this).attr('data-width');
        }
    });

    // Email Templates list: look at a saved template around a real email
    $(document).on('click', '.preview-template', function (event) {
        event.preventDefault();
        var title = $(this).attr('data-template-title') || '';
        request({action: 'preview', scope: 'template', template_id: $(this).attr('data-template-id')}, function (result) {
            if (result.status !== 'ok') {
                window.alert(result.message || label('failed'));
                return;
            }
            showPreview(result, title);
        });
    });

    $(document).on('click', '.email-preview__as-text', function () {
        var showText = $(this).attr('aria-pressed') !== 'true';
        $(this).toggleClass('is-on', showText).attr('aria-pressed', showText ? 'true' : 'false');
        $('.email-preview__text').prop('hidden', !showText);
        $('.email-preview__frame').prop('hidden', showText);
        $('.email-preview__size').prop('disabled', showText);
    });

    $(function () {
        $tools = $('#email_tools');
        if (!$tools.length) {
            return;
        }
        templateEditor();
        $('#email_preview_button').on('click', preview);
        $('#email_test_button').on('click', sendTest);
        // Enter in the address box sends the test; it must not save the form around it
        $('#email_test_to').on('keydown', function (event) {
            if (event.which === 13) {
                event.preventDefault();
                sendTest();
            }
        });
    });

    // For the other scripts on this screen
    window.ccEmail = {
        request: request,
        current: current,
        readers: readers,
        frameHtml: frameHtml,
        label: label,
        setStatus: setStatus
    };
})(jQuery);
