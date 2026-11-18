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

    function showPreview(result) {
        var html = '<div class="email-preview">' +
            '<div class="email-preview__bar">' +
                '<button type="button" class="button email-preview__size is-on" data-width="100%" aria-pressed="true">' + escapeText(label('desktop')) + '</button>' +
                '<button type="button" class="button email-preview__size" data-width="375px" aria-pressed="false">' + escapeText(label('mobile')) + '</button>' +
                '<button type="button" class="button email-preview__as-text" aria-pressed="false">' + escapeText(label('text')) + '</button>' +
            '</div>' +
            '<div class="email-preview__stage">' + frameHtml(result) + '<pre class="email-preview__text" hidden></pre></div>' +
        '</div>';
        $.colorbox({
            title: escapeText(result.subject),
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

    $(document).on('click', '.email-preview__size', function () {
        $('.email-preview__size').removeClass('is-on').attr('aria-pressed', 'false');
        $(this).addClass('is-on').attr('aria-pressed', 'true');
        $('.email-preview__frame').css('width', $(this).attr('data-width'));
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
