/**
 * Visual email editor (admin: Documents > Email Templates > an email)
 *
 * The server divides the stored email into units: wording, and parts that hold logic
 * (see classes/emailsplitter.class.php). This script shows the wording inside the
 * store's own template for the merchant to type into, draws each value as a named
 * placeholder, and shows the logic as it will look, out of reach of the keyboard.
 *
 * Saving sends back, for each unit, either "keep unit N" or its new source. A unit the
 * merchant did not touch is never re-serialised: the server writes it from the bytes it
 * already has. That is the difference from an ordinary rich text editor, which rewrites
 * the whole document on every save.
 *
 * Without this script, or without TinyMCE, the page is still a working form: the
 * subject, the preview text and the body as code, saved by the server as before.
 */
(function ($) {
    'use strict';

    var state = null;       // what the server described
    var editor = null;      // TinyMCE
    var view = 'names';     // or 'sample'
    var dirty = false;
    var codeMode = false;
    var codeAtEntry = '';
    var ace_editor = null;
    var lastTarget = null;  // where a placeholder goes: null for the email, or a line field
    var everFocused = false;
    var lastRange = null;
    var crlf = false;       // whether the email's own line endings are CRLF, which a text box does not keep
    var saving = false;     // a save is on its way to the server
    var checkTimer = null;
    var roomWide = 0;       // the width of the wording at full size, kept while the narrow view is on

    // CKEditor is loaded on every admin page and turns anything with contenteditable into
    // one of its inline editors when the page is ready. The subject and preview text lines
    // on this screen are ours, so it is told to leave them alone.
    if (window.CKEDITOR) {
        CKEDITOR.disableAutoInline = true;
        CKEDITOR.on('inline', function (event) {
            var element = event.data && event.data.element && event.data.element.$;
            if (element && element.classList && element.classList.contains('cc-line')) {
                event.cancel();
            }
        });
    }

    function T(name) {
        return (state && state.strings && state.strings[name]) || '';
    }

    // The units themselves (drawing them, reading them back, the tools a section gets)
    // are shared with the editor on products, categories and documents: admin.editor.units.js
    var core = window.ccEditorUnits({
        holder: function () {
            return body();
        },
        T: function (name) {
            return (name === 'loading' || name === 'failed') ? ccEmail.label(name) : T(name);
        },
        canEdit: function () {
            return !!(state && state.canEdit);
        },
        describe: function (source, done) {
            describe(source, done);
        },
        dirty: function () {
            setDirty();
        }
    });
    var cleanBody = window.ccEditorUnits.cleanBody;
    var collect = core.collect, chipHtml = core.chipHtml, paintChips = core.paintChips;

    /** The whole editing document: the template with the wording where its slot is */
    function documentHtml(units) {
        core.reset();
        var wording = '<div id="cc-email-body" class="mceEditable">' + core.unitsHtml(units, 'top') + '</div>';
        if (state.frame && state.frame.html) {
            var frame = cleanBody(state.frame.html), slot = frame.querySelector('#cc-email-slot');
            if (slot) {
                var holder = frame.ownerDocument.createElement('div');
                holder.innerHTML = wording;
                slot.parentNode.replaceChild(holder.firstChild, slot);
                return frame.innerHTML;
            }
        }
        return wording;
    }

    function body() {
        return editor ? editor.getDoc().getElementById('cc-email-body') : null;
    }

    /** The email as it stands in the editor, as source */
    function currentSource() {
        if (codeMode || !editor) {
            var code = ace_editor ? ace_editor.getValue() : $('#content_html').val();
            // A text box hands back LF whatever it was given: the email keeps its own line endings
            return crlf ? String(code).replace(/\r\n?/g, '\n').replace(/\n/g, '\r\n') : code;
        }
        return collect(body(), 'top').source;
    }

    /** Whether two pieces of code are the same but for their line endings */
    function sameCode(a, b) {
        return String(a).replace(/\r\n?/g, '\n') === String(b).replace(/\r\n?/g, '\n');
    }

    /** Whether the wording in the editor differs from what is stored */
    function wordingChanged() {
        return !!editor && !codeMode && !collect(body(), 'top').same;
    }

    //=====[ Placeholders ]=================================

    function setView(next) {
        view = next;
        core.view(next);
        $('#email_editor [data-view]').each(function () {
            var on = $(this).attr('data-view') === view;
            $(this).toggleClass('is-on', on).attr('aria-pressed', on ? 'true' : 'false');
        });
        if (editor) {
            editor.undoManager.ignore(function () {
                paintChips(editor.getBody());
                editor.getBody().classList.toggle('cc-view-sample', view === 'sample');
            });
        }
        $('.cc-line').each(function () {
            paintChips(this);
            $(this).toggleClass('cc-view-sample', view === 'sample');
        });
    }

    /**
     * Put a macro where the caret was last left: in the email, the subject or the preview text
     *
     * @param {object} placeholder
     * @param {boolean} [away] The focus has left that place: the macro was chosen with the
     *                         keyboard, or from the toolbar's menu
     */
    function insertPlaceholder(placeholder, away) {
        if (lastTarget) {
            insertInLine(lastTarget, placeholder, away);
            return;
        }
        if (!editor || codeMode) {
            return;
        }
        if (away && everFocused) {
            editor.focus(); // back to where the caret was left
        }
        var node = editor.selection.getNode(), inside = node && $(node).closest('#cc-email-body').length && !$(node).closest('.cc-locked, .cc-ph').length;
        if (!inside || !editor.hasFocus()) {
            ccEmail.setStatus(T('editor_placeholders_first'), false);
            return;
        }
        ccEmail.setStatus('', null);
        editor.insertContent(chipHtml(placeholder));
    }

    /** The macros this email can use, in the groups they are offered in: [{title, items}] */
    function placeholderGroups() {
        var groups = {}, order = [];
        (state.placeholders || []).forEach(function (placeholder) {
            var match = /^\{\$([A-Za-z_]\w*)/.exec(placeholder.tag), name = match ? match[1] : '';
            if (!groups[name]) {
                groups[name] = [];
                order.push(name);
            }
            groups[name].push(placeholder);
        });
        // The email's own values first, then the addresses, then whatever an extension added
        var rank = {DATA: 0, BILLING: 1, SHIPPING: 2};
        order.sort(function (a, b) {
            return (rank[a] === undefined ? 9 : rank[a]) - (rank[b] === undefined ? 9 : rank[b]);
        });
        return order.map(function (name) {
            return {title: state.groups && state.groups[name] ? state.groups[name] : name, items: groups[name]};
        });
    }

    function buildPlaceholderList() {
        var $box = $('#email_placeholders_list').empty(), list = placeholderGroups();
        list.forEach(function (group, index) {
            var $group = $('<details class="email-ph-group"></details>').prop('open', index === 0);
            $group.append($('<summary></summary>').text(group.title));
            var $buttons = $('<div class="email-ph-buttons"></div>');
            group.items.forEach(function (placeholder) {
                $('<button type="button" class="email-ph-button"></button>')
                    .text(placeholder.label)
                    .attr('title', placeholder.description + (placeholder.sample ? ' (' + placeholder.sample + ')' : ''))
                    // mousedown, and no focus change: the caret has to stay where the placeholder is going
                    .on('mousedown', function (event) {
                        event.preventDefault();
                        insertPlaceholder(placeholder);
                    })
                    .on('click', function (event) {
                        event.preventDefault();
                        if (event.detail === 0) {
                            insertPlaceholder(placeholder, true); // pressed with the keyboard
                        }
                    })
                    .appendTo($buttons);
            });
            $box.append($group.append($buttons));
        });
        $('#email_placeholders').prop('hidden', !list.length);
    }

    /** The same macros as a menu, for the editor's toolbar: the first group as it is, each of the others under its own name */
    function placeholderMenu() {
        var entry = function (placeholder) {
            return {
                type: 'menuitem',
                text: placeholder.label + (placeholder.description ? '  ' + placeholder.description : ''),
                onAction: function () {
                    insertPlaceholder(placeholder, true); // the menu has the focus while it is open
                }
            };
        };
        var items = [];
        placeholderGroups().forEach(function (group, index) {
            if (index === 0) {
                items = group.items.map(entry);
                return;
            }
            items.push({
                type: 'nestedmenuitem',
                text: group.title,
                getSubmenuItems: function () {
                    return group.items.map(entry);
                }
            });
        });
        return items;
    }

    //=====[ Subject and preview text: one line of plain text with placeholders ]=====

    function lineText(element) {
        var text = '', nodes = element.childNodes, i, node;
        for (i = 0; i < nodes.length; i++) {
            node = nodes[i];
            if (node.nodeType === 3) {
                text += node.nodeValue;
            } else if (node.nodeType === 1 && node.hasAttribute('data-cc-s')) {
                text += node.getAttribute('data-cc-s');
            } else if (node.nodeType === 1 && node.nodeName !== 'BR') {
                text += node.textContent;
            }
        }
        return text.replace(/[\r\n\u00a0]+/g, ' ');
    }

    function buildLine(description, $input, label) {
        if (!description || description.code) {
            return; // logic in the line: it stays a plain text box
        }
        var $line = $('<div class="cc-line textbox" role="textbox" spellcheck="true"></div>').attr('aria-label', label);
        // Someone who may only look gets the same line, with nothing in it to type
        $line.attr('contenteditable', state.canEdit ? 'true' : 'false').attr('aria-readonly', state.canEdit ? 'false' : 'true');
        (description.parts || []).forEach(function (part) {
            if (part.kind === 'value') {
                $line.append(chipHtml(part));
            } else {
                $line.append(document.createTextNode(part.text));
            }
        });
        var sync = function () {
            $input.val(lineText($line[0]));
        };
        $line.on('input', function () {
            sync();
            setDirty();
        }).on('keydown', function (event) {
            if (event.which === 13) {
                event.preventDefault();
            }
        }).on('paste', function (event) {
            event.preventDefault();
            var data = (event.originalEvent.clipboardData || window.clipboardData), text = data ? data.getData('text') : '';
            document.execCommand('insertText', false, text.replace(/\s+/g, ' '));
        }).on('drop', function (event) {
            event.preventDefault();
        }).on('input keyup mouseup', function (event) {
            if (event.type === 'keyup' && (event.which === 9 || event.which === 16)) {
                return; // Tab, on its way through
            }
            lastTarget = $line[0];
            var selection = window.getSelection();
            if (selection.rangeCount && $line[0].contains(selection.anchorNode)) {
                lastRange = selection.getRangeAt(0).cloneRange();
            }
        });
        $input.prop('hidden', true).after($line);
        $line.data('sync', sync);
    }

    function insertInLine(line, placeholder, away) {
        var holder = document.createElement('div'), range = lastRange, selection = window.getSelection();
        holder.innerHTML = chipHtml(placeholder);
        var chip = holder.firstChild;
        if (!range || !line.contains(range.startContainer)) {
            range = document.createRange();
            range.selectNodeContents(line);
            range.collapse(false);
        }
        // A click on a macro leaves the caret inside it: the new one goes after it, not into it
        var at = (range.startContainer.nodeType === 1) ? range.startContainer : range.startContainer.parentNode;
        at = (at && at.closest) ? at.closest('.cc-ph') : null;
        if (at && line.contains(at)) {
            range = document.createRange();
            range.setStartAfter(at);
            range.collapse(true);
        }
        range.deleteContents();
        range.insertNode(chip);
        range.setStartAfter(chip);
        range.collapse(true);
        if (away) {
            line.focus(); // typing carries on after the macro
        }
        selection.removeAllRanges();
        selection.addRange(range);
        lastRange = range.cloneRange();
        $(line).data('sync')();
        setDirty();
    }

    //=====[ Dirty state ]==================================

    function setDirty() {
        dirty = true;
        ccEmail.setStatus('', null);
    }

    function setClean() {
        dirty = false;
        if (editor) {
            editor.setDirty(false);
        }
        var $form = $('#email_editor').closest('form');
        if ($form.length && $.fn.dirty) {
            try {
                $form.dirty('setAsClean');
            } catch (e) { /* the plugin was not started on this form */ }
        }
    }

    $(window).on('beforeunload', function (event) {
        if (dirty || wordingChanged()) {
            event.preventDefault();
            event.returnValue = T('editor_unsaved');
            return T('editor_unsaved');
        }
    });

    //=====[ The editor ]===================================

    var EDITOR_CSS = [
        'body{margin:0;padding:16px;}',
        '#cc-email-body{outline:1px dashed transparent;outline-offset:6px;min-height:40px;}',
        '#cc-email-body:hover{outline-color:#9fc6d2;}',
        core.CSS
    ].join('\n');

    /** Ask the server to divide some source into units, as it does for the stored email */
    function describe(source, done) {
        $.ajax({
            type: 'post',
            url: window.location.href.split('#')[0],
            dataType: 'json',
            data: {
                token: $('.cc_session_token').val(),
                'email_editor[action]': 'units',
                'email_editor[content_type]': state.contentType,
                'email_editor[sample]': $('#email_sample').val() || state.sample.source,
                'email_editor[content_html]': source
            }
        }).done(function (result) {
            done(result && result.status ? result : {status: 'error', message: ccEmail.label('failed')});
        }).fail(function () {
            done({status: 'error', message: ccEmail.label('failed')});
        });
    }

    function startEditor(done) {
        var dark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        tinymce.init({
            target: document.getElementById('email_visual'),
            license_key: 'gpl',
            base_url: state.tinymce,
            suffix: '.min',
            skin: dark ? 'oxide-dark' : 'oxide',
            content_css: false,
            content_style: (state.frame && state.frame.css ? state.frame.css + '\n' : '') + (state.frame && state.frame.body && state.frame.body.bgcolor ? 'body{background-color:' + String(state.frame.body.bgcolor).replace(/[^#\w(),.% -]/g, '') + ';}\n' : '') + EDITOR_CSS,
            document_base_url: state.storeUrl,
            height: 680,
            menubar: false,
            statusbar: false,
            branding: false,
            promotion: false,
            contextmenu: false,
            visual: false, // no dashed guides round the tables an email is laid out with
            plugins: 'link lists autolink image',
            // Nothing to format for someone who may only look
            toolbar: state.canEdit ? 'undo redo | bold italic underline | link image | bullist numlist | alignleft aligncenter alignright | removeformat' + ((state.placeholders || []).length ? ' | ccMacros' : '') : false,
            // Only the wording can be typed in; the template around it is there to be seen
            editable_root: false,
            forced_root_block: 'p',
            // Leave the email's own markup alone
            valid_elements: '*[*]',
            convert_fonts_to_spans: false,
            remove_trailing_brs: false,
            // Tab leaves the email, as it leaves any other field: the template's layout is a table,
            // and TinyMCE would otherwise keep Tab for moving between its cells
            table_tab_navigation: false,
            valid_children: '+body[style]',
            invalid_elements: 'script,iframe,object,embed,applet,form,meta,link,base',
            verify_html: false,
            convert_urls: false,
            relative_urls: false,
            remove_script_host: false,
            element_format: 'html',
            entity_encoding: 'named',
            entities: '160,nbsp',
            object_resizing: false,
            paste_data_images: false,
            link_title: false,
            link_target_list: false,
            image_dimensions: true,
            image_description: true,
            file_picker_types: 'image',
            file_picker_callback: function (callback) {
                // The store's own image picker, the one the rest of the admin uses
                if (typeof window.ccOpenImagePickerDialog !== 'function') {
                    return;
                }
                var picked = function (url) {
                    // It answers with a path on this site; a mail client needs the whole address
                    var address = url, probe = new Image();
                    try {
                        address = new URL(url, state.storeUrl).href;
                    } catch (e) {}
                    probe.onload = function () {
                        // No wider than the email: a photo straight from a camera would push the layout out
                        var narrow = !!document.getElementById('email_visual_wrap').style.maxWidth;
                        var room = (narrow ? roomWide : (body() && body().clientWidth)) || 560, width = probe.naturalWidth, height = probe.naturalHeight;
                        if (width > room) {
                            height = Math.round(height * room / width);
                            width = room;
                        }
                        callback(address, width && height ? {width: String(width), height: String(height)} : {});
                    };
                    probe.onerror = function () {
                        callback(address, {});
                    };
                    probe.src = address;
                };
                window.ccOpenImagePickerDialog({
                    storageKey: 'editor_dialog',
                    onPick: function (file) {
                        $.colorbox.close();
                        picked('images/source/' + (file.filepath || '') + (file.filename || ''));
                    }
                });
            },
            setup: function (instance) {
                // Nothing gets into the editor without being made safe first. What is pasted
                // or dropped was written by another page, and TinyMCE is told above to keep
                // every element and attribute, handlers included. These run before TinyMCE's
                // own listeners, which build elements out of this HTML in the admin page itself.
                var makeSafe = function (event) {
                    if (typeof event.content === 'string' && event.content !== '') {
                        event.content = cleanBody(event.content).innerHTML;
                    }
                };
                instance.on('BeforeSetContent', makeSafe, true);
                instance.on('PastePreProcess', makeSafe, true);
                core.registerTools(instance);
                // The macros of the list beside the email, in the toolbar too, as the editor has them elsewhere
                instance.ui.registry.addMenuButton('ccMacros', {
                    text: T('editor_placeholders'),
                    fetch: function (callback) {
                        callback(placeholderMenu());
                    }
                });
                // The caret is in the email. The toolbar is the editor's too, and the editor reports
                // focus for it as it does for the email, so this asks which of the two it was: opening
                // the menu must not forget that the subject was the last place clicked.
                instance.on('focusin', function (event) {
                    if (event.target && event.target.ownerDocument === instance.getDoc()) {
                        lastTarget = null;
                        everFocused = true;
                    }
                });
                // An emptied email keeps one empty paragraph. Without it the wording has no
                // height and no place for the caret, which lands in the template, where
                // nothing can be typed.
                instance.on('keyup input cut undo redo ExecCommand', function () {
                    var holder = body(), kids, i;
                    if (!holder || !state.canEdit || instance.mode.get() !== 'design') {
                        return;
                    }
                    kids = holder.childNodes;
                    for (i = 0; i < kids.length; i++) {
                        if (kids[i].nodeType === 3 ? kids[i].nodeValue.replace(/[\s\ufeff]+/g, '') !== '' : kids[i].nodeName !== 'BR') {
                            return;
                        }
                    }
                    instance.undoManager.ignore(function () {
                        holder.innerHTML = '<p><br data-mce-bogus="1"></p>';
                    });
                    instance.selection.setCursorLocation(holder.firstChild, 0);
                });
                // Something happened in the editor. Whether it is an unsaved change is decided by
                // looking at the wording, because the editor also reports its own redrawing
                // (a switch between names and sample data) as a change.
                instance.on('input change undo redo', function (event) {
                    if (event.type === 'undo' || event.type === 'redo') {
                        instance.undoManager.ignore(function () {
                            paintChips(instance.getBody());
                        });
                    }
                    clearTimeout(checkTimer);
                    checkTimer = setTimeout(function () {
                        if (!dirty && wordingChanged()) {
                            setDirty();
                        }
                    }, 250);
                });
                instance.on('init', function () {
                    editor = instance;
                    core.attach(instance);
                    done();
                });
            }
        });
    }

    /**
     * Put an email in the editor
     *
     * @return {boolean} false when the editor cannot show it without changing it, and the
     *                   code is all there is to edit
     */
    function loadUnits(units) {
        if (!core.load(units, documentHtml)) {
            codeOnly();
            return false;
        }
        editor.undoManager.clear();
        setView(view);
        editor.setDirty(false);
        return true;
    }

    /** Leave the visual editor: the code is the editor from here on */
    function codeOnly() {
        var gone = editor;
        editor = null;
        core.attach(null);
        if (gone) {
            // Taken down once whatever called this has finished with it
            setTimeout(function () {
                try {
                    gone.remove();
                } catch (e) { /* already gone */ }
            }, 0);
        }
        codeMode = false;
        $('#email_editor_bar, #email_visual_wrap, #email_placeholders').prop('hidden', true);
        $('#email_code_wrap').prop('hidden', false);
        codeAtEntry = $('#content_html').val();
        startAce();
        if (ace_editor) {
            ace_editor.resize();
        }
        ccEmail.setStatus(T('editor_code_only'), null);
    }

    //=====[ Code view ]====================================

    function setCodeValue(source) {
        $('#content_html').val(source);
        if (ace_editor) {
            ace_editor.setValue(source, -1);
        }
    }

    function startAce() {
        if (ace_editor || !window.ace) {
            return;
        }
        var dark = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null, $source = $('#content_html');
        ace_editor = ace.edit('email_code_ace');
        ace_editor.session.setUseWrapMode(true);
        ace_editor.setOptions({
            highlightActiveLine: true,
            showPrintMargin: false,
            theme: (dark && dark.matches) ? 'ace/theme/tomorrow_night' : 'ace/theme/github',
            mode: 'ace/mode/smarty'
        });
        ace_editor.setReadOnly(!state.canEdit);
        ace_editor.setValue($source.val(), -1);
        ace_editor.getSession().on('change', function () {
            $source.val(ace_editor.getValue());
            if ((codeMode || !editor) && !sameCode(ace_editor.getValue(), codeAtEntry)) {
                setDirty();
            }
        });
        $source.prop('hidden', true);
        $('#email_code_ace').prop('hidden', false);
        ace_editor.resize();
    }

    function showCode(on) {
        codeMode = on;
        $('#email_code_wrap').prop('hidden', !on);
        $('#email_visual_wrap').prop('hidden', on);
        $('#email_code_toggle').toggleClass('is-on', on).attr('aria-pressed', on ? 'true' : 'false');
        $('#email_editor [data-view], #email_editor [data-width]').prop('disabled', on);
        $('#email_placeholders').toggleClass('is-off', on);
        if (on && ace_editor) {
            ace_editor.resize();
            ace_editor.focus();
        }
    }

    function toggleCode() {
        if (!editor) {
            return; // there is only the code
        }
        if (!codeMode) {
            codeAtEntry = collect(body(), 'top').source;
            crlf = codeAtEntry.indexOf('\r\n') !== -1;
            setCodeValue(codeAtEntry);
            startAce();
            showCode(true);
            return;
        }
        var source = currentSource();
        if (sameCode(source, codeAtEntry)) {
            showCode(false);
            return;
        }
        ccEmail.setStatus(ccEmail.label('loading'), null);
        describe(source, function (result) {
            if (result.status !== 'ok') {
                ccEmail.setStatus(result.message || ccEmail.label('failed'), false);
                return;
            }
            ccEmail.setStatus('', null);
            core.refs(false); // the body is now what was typed, not the stored units
            state.placeholders = result.placeholders || state.placeholders;
            showCode(false);
            var shown = loadUnits(result.units);
            setDirty();
            if (!shown) {
                ccEmail.setStatus(T('editor_code_only'), null);
            }
        });
    }

    //=====[ Saving ]=======================================

    /** While a save is on its way nothing more is typed, and Save does nothing */
    function busy(on) {
        saving = on;
        $('#email_tools input[type=submit]').prop('disabled', on);
        if (state.canEdit) {
            $('.cc-line').attr('contenteditable', on ? 'false' : 'true');
            $('#content_subject, #content_preheader').prop('readOnly', on);
            if (editor) {
                editor.mode.set(on ? 'readonly' : 'design');
            }
            if (ace_editor) {
                ace_editor.setReadOnly(on);
            }
        }
    }

    function save() {
        if (saving) {
            return;
        }
        var fields = {
            token: $('.cc_session_token').val(),
            'email_editor[action]': 'save',
            'email_editor[content_id]': state.contentId || '',
            'email_editor[content_type]': state.contentType,
            'email_editor[language]': $('#content_language').val() || state.language || '',
            'email_editor[base]': state.base || '',
            'email_editor[subject]': $('#content_subject').val(),
            'email_editor[preheader]': $('#content_preheader').val(),
            'email_editor[sample]': $('#email_sample').val() || state.sample.source
        };
        var counted = null;
        if (!codeMode && editor) {
            counted = collect(body(), 'top');
            if (core.refs() && state.contentId) {
                fields['email_editor[items]'] = JSON.stringify(counted.items);
            } else {
                fields['email_editor[content_html]'] = counted.source;
            }
        } else {
            fields['email_editor[content_html]'] = currentSource();
        }
        ccEmail.setStatus(ccEmail.label('loading'), null);
        busy(true);
        $.ajax({type: 'post', url: window.location.href.split('#')[0], dataType: 'json', data: fields}).always(function () {
            busy(false);
        }).done(function (result) {
            if (!result || result.status !== 'ok') {
                ccEmail.setStatus((result && result.message) || ccEmail.label('failed'), false);
                return;
            }
            var wasNew = !state.contentId;
            state.contentId = result.content_id;
            state.base = result.base;
            state.placeholders = result.placeholders || state.placeholders;
            core.refs(true);
            setCodeValue(result.source);
            codeAtEntry = result.source;
            if (editor) {
                loadUnits(result.units);
            }
            setClean();
            var message = T('editor_saved_same');
            if (counted && counted.changed > 0) {
                message = T('editor_saved').replace('%1$d', counted.changed).replace('%2$d', counted.total);
            } else if (!counted || result.changed) {
                message = result.message || T('editor_saved_same');
            }
            ccEmail.setStatus(message, true);
            if (wasNew && result.edit_url) {
                window.location.href = result.edit_url; // a new translation now has a page of its own
            }
        }).fail(function () {
            ccEmail.setStatus(ccEmail.label('failed'), false);
        });
    }

    //=====[ Start ]========================================

    $(function () {
        var $root = $('#email_editor'), $data = $('#email_editor_data');
        if (!$root.length || !$data.length || !window.ccEmail) {
            return;
        }
        try {
            state = JSON.parse($data.text());
        } catch (e) {
            return; // the plain form still works
        }
        var $form = $root.closest('form');
        // The admin's unsaved-changes check watches every field of the form, and looks again
        // whenever one of them is used. The sample chooser and the test address are tools, and
        // the editor keeps a copy of itself in the text box it was started on: none of the three
        // is part of what is saved, so each one's starting value is kept in step with it. This
        // runs first, on the way down to the field.
        if ($form.length) {
            ['change', 'click', 'keyup', 'keydown', 'blur'].forEach(function (type) {
                $form[0].addEventListener(type, function () {
                    ['email_sample', 'email_test_to', 'email_visual'].forEach(function (id) {
                        var field = document.getElementById(id);
                        if (field) {
                            $(field).data('dirtyInitialValue', $(field).val() || '');
                        }
                    });
                }, true);
            });
        }

        // Preview and test send read the email as it stands, saved or not
        ccEmail.readers.content_html = currentSource;

        buildLine(state.subject, $('#content_subject'), $('label[for="content_subject"]').text());
        buildLine(state.preheader, $('#content_preheader'), $('label[for="content_preheader"]').text());
        $('#content_subject, #content_preheader').on('input', setDirty).on('focus', function () {
            lastTarget = null;
        });
        $('#content_html').on('input', function () {
            if (!ace_editor) {
                setDirty();
            }
        });

        if (state.canEdit) {
            $form.on('submit', function (event) {
                var submitter = event.originalEvent && event.originalEvent.submitter;
                if (submitter && submitter.name === 'email_reset') {
                    dirty = false; // the reset replaces everything, on purpose
                    if (editor) {
                        editor.setDirty(false);
                    }
                    return;
                }
                event.preventDefault();
                event.stopImmediatePropagation();
                save();
            });
        }

        var visual = state.visual && window.tinymce && state.units && state.units.length;
        if (!visual) {
            // No visual editor: the code is the editor
            codeAtEntry = $('#content_html').val();
            startAce();
            if (state.units && state.units.length && !state.visual) {
                ccEmail.setStatus(T('editor_code_only'), null);
            }
            return;
        }
        // By reference only where what was described is what is stored: a body the page had
        // to show differently, or a refused save shown again, is sent back whole
        core.refs(!!(state.base && state.refs));
        crlf = JSON.stringify(state.units || []).indexOf('\\r\\n') !== -1;
        startEditor(function () {
            if (!loadUnits(state.units)) {
                return; // the editor could not show it as it is: the code is shown instead
            }
            if (state.canEdit) {
                buildPlaceholderList();
            } else {
                // May look but not change: the same email, with nothing in it to type in
                editor.mode.set('readonly');
            }
            $('#email_editor_bar').prop('hidden', false);
            showCode(false);
            setClean();
        });

        $root.on('click', '[data-view]', function () {
            setView($(this).attr('data-view'));
        });
        $root.on('click', '[data-width]', function () {
            var width = $(this).attr('data-width');
            if (!document.getElementById('email_visual_wrap').style.maxWidth && body()) {
                roomWide = body().clientWidth; // measured at full width, before the view narrows
            }
            $root.find('[data-width]').each(function () {
                var on = $(this).attr('data-width') === width;
                $(this).toggleClass('is-on', on).attr('aria-pressed', on ? 'true' : 'false');
            });
            $('#email_visual_wrap').css('max-width', width === '100%' ? '' : width);
        });
        $('#email_code_toggle').on('click', toggleCode);
        // A different sample means different renders: look at the saved email again with it
        $('#email_sample').on('change', function () {
            if (dirty || wordingChanged()) {
                return; // previews and tests use it straight away; the editor picks it up after a save
            }
            var url = new URL(window.location.href);
            url.searchParams.set('sample', $(this).val());
            window.location.href = url.toString();
        });
    });

    // For tests and for other scripts on the page
    window.ccEmailEditor = {
        collect: function () {
            return editor ? collect(body(), 'top') : null;
        },
        source: currentSource,
        editor: function () {
            return editor;
        },
        records: function () {
            return core.records();
        },
        save: save,
        setView: setView,
        toggleCode: toggleCode,
        codeOnly: codeOnly
    };
})(jQuery);
