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
    var records = {};       // key => {key, id, kind, source, list, open, close, snapshot}
    var lists = {};         // list key => keys in stored order, gaps included
    var nextKey = 0;
    var view = 'names';     // or 'sample'
    var refs = true;        // whether units can still be sent back by reference
    var dirty = false;
    var codeMode = false;
    var codeAtEntry = '';
    var ace_editor = null;
    var lastTarget = null;  // where a placeholder goes: null for the email, or a line field
    var everFocused = false;
    var lastRange = null;

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

    function esc(text) {
        return String(text === undefined || text === null ? '' : text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    //=====[ Making HTML safe to show ]=====================

    /**
     * Everything that goes into the editor is first parsed in a document that runs
     * nothing, and stripped of what could run: the template and the emails are written
     * by other admins, and this page is the admin's own origin.
     */
    var UNSAFE = 'script,iframe,frame,frameset,object,embed,applet,link,meta,base,form,input,button,select,textarea,audio,video,source,track,svg,math,style,title,noscript';
    var URL_ATTRIBUTES = ['href', 'src', 'action', 'formaction', 'xlink:href', 'background', 'poster', 'data', 'srcdoc'];

    function cleanBody(html) {
        var doc = new DOMParser().parseFromString('<!doctype html><html><body>' + html + '</body></html>', 'text/html');
        var bad = doc.querySelectorAll(UNSAFE), i, j, all, attrs, name, value;
        for (i = 0; i < bad.length; i++) {
            if (bad[i].parentNode) {
                bad[i].parentNode.removeChild(bad[i]);
            }
        }
        all = doc.body.getElementsByTagName('*');
        for (i = 0; i < all.length; i++) {
            attrs = all[i].attributes;
            for (j = attrs.length - 1; j >= 0; j--) {
                name = attrs[j].name.toLowerCase();
                value = String(attrs[j].value).replace(/[\u0000- ]+/g, '').toLowerCase();
                if (name.indexOf('on') === 0 || name === 'srcdoc') {
                    all[i].removeAttribute(attrs[j].name);
                } else if (URL_ATTRIBUTES.indexOf(name) !== -1 && /^(javascript|vbscript|data:text\/html)/.test(value)) {
                    all[i].removeAttribute(attrs[j].name);
                }
            }
        }
        return doc.body;
    }

    //=====[ Units ]========================================

    function register(unit, list) {
        var record = {
            key: 'u' + (nextKey++),
            id: refs ? unit.id : null,
            kind: unit.kind,
            source: unit.source,
            list: list,
            open: unit.open || '',
            close: unit.close || '',
            snapshot: null
        };
        records[record.key] = record;
        if (!lists[list]) {
            lists[list] = [];
        }
        lists[list].push(record.key);
        return record;
    }

    var INLINE = /^(a|abbr|b|bdi|bdo|big|br|cite|code|del|dfn|em|font|i|img|ins|kbd|mark|q|s|samp|small|span|strike|strong|sub|sup|time|tt|u|var|wbr)$/;

    /** The HTML for one unit of wording, marked with its key */
    function wordingHtml(unit, record) {
        var body = cleanBody(unit.html), nodes = body.childNodes, elements = 0, loose = false, inline = true, i, node;
        for (i = 0; i < nodes.length; i++) {
            node = nodes[i];
            if (node.nodeType === 1) {
                elements++;
                if (!INLINE.test(node.nodeName.toLowerCase())) {
                    inline = false;
                }
            } else if (node.nodeType === 3 && node.nodeValue.replace(/\s+/g, '') !== '') {
                loose = true;
            }
        }
        if (elements === 1 && !loose && !inline) {
            // The usual case: the unit is one element, a paragraph or a table
            for (i = 0; i < nodes.length; i++) {
                if (nodes[i].nodeType === 1) {
                    nodes[i].setAttribute('data-cc-u', record.key);
                }
            }
            return body.innerHTML;
        }
        if (inline) {
            // A line of text that was never put in a paragraph
            return '<p data-cc-u="' + record.key + '" data-cc-bare="1">' + body.innerHTML + '</p>';
        }
        return '<div data-cc-u="' + record.key + '" data-cc-wrap="1">' + body.innerHTML + '</div>';
    }

    function lockedHtml(unit, record) {
        return '<div class="cc-locked mceNonEditable" data-cc-u="' + record.key + '">' +
            '<div class="cc-bar"><strong>' + esc(unit.title || T('editor_locked')) + '</strong>' + (state.canEdit ? ' <span>' + esc(T('editor_locked_note')) + '</span>' : '') + '</div>' +
            '<div class="cc-locked__body">' + cleanBody(unit.render || '').innerHTML + '</div>' +
        '</div>';
    }

    function condHtml(unit, record) {
        return '<div class="cc-cond mceNonEditable' + (unit.shown ? '' : ' cc-cond--off') + '" data-cc-u="' + record.key + '">' +
            '<div class="cc-bar"><strong>' + esc(unit.label) + '</strong></div>' +
            '<div class="cc-cond__body mceEditable">' + unitsHtml(unit.inner || [], record.key) + '</div>' +
        '</div>';
    }

    function unitsHtml(units, list) {
        var html = '', i, unit, record;
        for (i = 0; i < units.length; i++) {
            unit = units[i];
            record = register(unit, list);
            if (unit.kind === 'editable' || unit.kind === 'inline') {
                html += wordingHtml(unit, record);
            } else if (unit.kind === 'locked') {
                html += lockedHtml(unit, record);
            } else if (unit.kind === 'cond') {
                html += condHtml(unit, record);
            }
        }
        return html;
    }

    /** The whole editing document: the template with the wording where its slot is */
    function documentHtml(units) {
        records = {};
        lists = {};
        var wording = '<div id="cc-email-body" class="mceEditable">' + unitsHtml(units, 'top') + '</div>';
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

    /**
     * A unit as it would be stored: placeholders turned back into the Smarty they stand
     * for, and nothing of the editor's left in it.
     */
    function toSource(element) {
        var clone = element.cloneNode(true), sources = [], holders = clone.querySelectorAll('[data-cc-s]'), i, token;
        for (i = 0; i < holders.length; i++) {
            token = '' + sources.length + '';
            sources.push(holders[i].getAttribute('data-cc-s'));
            holders[i].parentNode.replaceChild(clone.ownerDocument.createTextNode(token), holders[i]);
        }
        var wrapped = clone.hasAttribute('data-cc-wrap');
        clone.removeAttribute('data-cc-u');
        clone.removeAttribute('data-cc-bare');
        clone.removeAttribute('data-cc-wrap');
        var html = editor.serializer.serialize(clone, {});
        if (wrapped) {
            html = html.replace(/^<div[^>]*>\s*/, '').replace(/\s*<\/div>$/, '');
        }
        return html.replace(/(\d+)/g, function (match, n) {
            return sources[parseInt(n, 10)];
        });
    }

    function body() {
        return editor ? editor.getDoc().getElementById('cc-email-body') : null;
    }

    function snapshotAll() {
        var marked = editor.getDoc().querySelectorAll('[data-cc-u]'), i, record;
        for (i = 0; i < marked.length; i++) {
            record = records[marked[i].getAttribute('data-cc-u')];
            if (record && (record.kind === 'editable' || record.kind === 'inline') && record.snapshot === null) {
                record.snapshot = toSource(marked[i]);
            }
        }
    }

    function keep(record) {
        return record.id !== null ? {k: record.id} : {s: record.source};
    }

    /** The stored white space between two units that still sit side by side */
    function gapsBetween(list, fromKey, toKey) {
        var keys = lists[list] || [], from = (fromKey === null) ? -1 : keys.indexOf(fromKey), to = (toKey === null) ? keys.length : keys.indexOf(toKey), gaps = [], i;
        if (to <= from) {
            return null;
        }
        for (i = from + 1; i < to; i++) {
            if (records[keys[i]].kind !== 'gap') {
                return null; // something that was between them has gone, or they have swapped
            }
            gaps.push(records[keys[i]]);
        }
        return gaps;
    }

    /**
     * Read a list of units back out of the editor
     *
     * @return {items, source, changed, total, same}
     */
    function collect(container, list) {
        var result = {items: [], source: '', changed: 0, total: 0, same: true}, seen = {}, previous = null, first = true;
        var nodes = container.childNodes, i, node, record, item, kept, text, inner, gaps, g, expected = [], met = [];
        (lists[list] || []).forEach(function (key) {
            if (records[key].kind !== 'gap') {
                expected.push(key);
            }
        });

        function emit(entry, source) {
            result.items.push(entry);
            result.source += source;
        }

        for (i = 0; i < nodes.length; i++) {
            node = nodes[i];
            item = null;
            record = null;
            kept = false;
            if (node.nodeType === 3) {
                text = node.nodeValue.replace(/\s+/g, ' ').replace(/^ | $/g, '');
                if (text === '') {
                    continue;
                }
                item = {s: '<p>' + esc(text).replace(/&quot;/g, '"') + '</p>'}; // text typed straight into the email
            } else if (node.nodeType !== 1 || node.getAttribute('data-mce-bogus')) {
                continue;
            } else {
                record = records[node.getAttribute('data-cc-u')] || null;
                if (record && (seen[record.key] || record.list !== list)) {
                    // A second copy made by splitting or pasting, or a unit dragged in from elsewhere
                    if (record.kind === 'locked' || record.kind === 'cond') {
                        item = {s: record.source};
                    }
                    record = null;
                }
                if (record) {
                    seen[record.key] = true;
                    if (record.kind === 'locked') {
                        item = keep(record);
                        kept = true;
                    } else if (record.kind === 'cond') {
                        // Its parts are counted, not the frame around them
                        inner = collect(node.querySelector('.cc-cond__body') || node, record.key);
                        result.total += inner.total - 1;
                        result.changed += inner.changed;
                        if (inner.same) {
                            item = keep(record);
                            kept = true;
                        } else if (record.id !== null) {
                            item = {c: record.id, inner: inner.items, source: record.open + inner.source + record.close};
                        } else {
                            item = {s: record.open + inner.source + record.close};
                        }
                    } else {
                        text = toSource(node);
                        kept = (text === record.snapshot);
                        item = kept ? keep(record) : {s: text};
                    }
                } else if (!item) {
                    if (!node.getAttribute('data-cc-u') && node.textContent.replace(/[\s ﻿]+/g, '') === '' && !node.querySelector('img, hr, [data-cc-s]')) {
                        continue; // a new paragraph nobody typed in
                    }
                    item = {s: toSource(node)};
                }
            }
            result.total++;
            // What goes between this unit and the one before
            gaps = (record && (previous || first)) ? gapsBetween(list, previous ? previous.key : null, record.key) : null;
            if (gaps) {
                for (g = 0; g < gaps.length; g++) {
                    emit(keep(gaps[g]), gaps[g].source);
                }
            } else if (!first) {
                emit({s: '\n'}, '\n');
            }
            if (kept) {
                emit(item, record.source);
                met.push(record.key);
            } else if (item.c !== undefined) {
                emit({c: item.c, inner: item.inner}, item.source);
                result.same = false;
            } else {
                emit(item, item.s);
                result.same = false;
                if (!(record && record.kind === 'cond')) {
                    result.changed++;
                }
            }
            previous = record;
            first = false;
        }
        // And after the last one
        gaps = previous ? gapsBetween(list, previous.key, null) : null;
        if (gaps) {
            for (g = 0; g < gaps.length; g++) {
                emit(keep(gaps[g]), gaps[g].source);
            }
        }
        // Units that were removed count as changes, and so does a new order
        expected.forEach(function (key) {
            if (!seen[key]) {
                result.changed++;
                result.same = false;
            }
        });
        if (met.join(',') !== expected.join(',')) {
            result.same = false;
        }
        return result;
    }

    /** The email as it stands in the editor, as source */
    function currentSource() {
        if (codeMode || !editor) {
            return ace_editor ? ace_editor.getValue() : $('#content_html').val();
        }
        return collect(body(), 'top').source;
    }

    //=====[ Placeholders ]=================================

    function chipHtml(placeholder) {
        return '<span class="cc-ph mceNonEditable" contenteditable="false" data-cc-s="' + esc(placeholder.tag) + '" data-cc-l="' + esc(placeholder.label) + '" data-cc-v="' + esc(placeholder.sample) + '" title="' + esc(placeholder.description) + '">' + esc(chipText(placeholder.label, placeholder.sample)) + '</span>';
    }

    function chipText(label, sample) {
        return (view === 'sample' && sample !== '' && sample !== null && sample !== undefined) ? sample : label;
    }

    /** Show every value by its name or by what it comes to. Not an edit, so not undoable. */
    function paintChips(root) {
        var chips = root.querySelectorAll('.cc-ph[data-cc-l]'), i, chip, sample;
        for (i = 0; i < chips.length; i++) {
            chip = chips[i];
            sample = chip.getAttribute('data-cc-v');
            chip.textContent = chipText(chip.getAttribute('data-cc-l'), sample);
            chip.classList.toggle('cc-ph--blank', view === 'sample' && !sample);
        }
    }

    function setView(next) {
        view = next;
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

    function insertPlaceholder(placeholder, fromKeyboard) {
        if (lastTarget) {
            insertInLine(lastTarget, placeholder);
            return;
        }
        if (!editor || codeMode) {
            return;
        }
        if (fromKeyboard && everFocused) {
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

    function buildPlaceholderList() {
        var $box = $('#email_placeholders_list').empty(), groups = {}, order = [];
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
        order.forEach(function (name, index) {
            var $group = $('<details class="email-ph-group"></details>').prop('open', index === 0);
            $group.append($('<summary></summary>').text(state.groups && state.groups[name] ? state.groups[name] : name));
            var $buttons = $('<div class="email-ph-buttons"></div>');
            groups[name].forEach(function (placeholder) {
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
        $('#email_placeholders').prop('hidden', !order.length);
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
        return text.replace(/[\r\n ]+/g, ' ');
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
        }).on('focus keyup mouseup', function () {
            lastTarget = $line[0];
            var selection = window.getSelection();
            if (selection.rangeCount && $line[0].contains(selection.anchorNode)) {
                lastRange = selection.getRangeAt(0).cloneRange();
            }
        });
        $input.prop('hidden', true).after($line);
        $line.data('sync', sync);
    }

    function insertInLine(line, placeholder) {
        var holder = document.createElement('div'), range = lastRange, selection = window.getSelection();
        holder.innerHTML = chipHtml(placeholder);
        var chip = holder.firstChild;
        if (!range || !line.contains(range.startContainer)) {
            range = document.createRange();
            range.selectNodeContents(line);
            range.collapse(false);
        }
        range.deleteContents();
        range.insertNode(chip);
        range.setStartAfter(chip);
        range.collapse(true);
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
        if (dirty || (editor && editor.isDirty())) {
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
        '.cc-ph{display:inline-block;max-width:32ch;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;vertical-align:bottom;padding:0 7px;border:1px solid #a9cfdb;border-radius:10px;background:#e3f0f4;color:#1e3d45;font:600 12px/18px Arial,Helvetica,sans-serif;text-decoration:none;cursor:default;}',
        '.cc-ph--logic{max-width:none;white-space:normal;display:inline;padding:0 3px;border:1px dashed #b98900;border-radius:3px;background:#fff6dd;color:inherit;font:inherit;}',
        '.cc-ph--empty{color:#6b5a1f;font-style:italic;}',
        '.cc-view-sample .cc-ph:not(.cc-ph--logic){display:inline;max-width:none;white-space:normal;padding:0;border:0;border-bottom:1px dotted #5d9fb2;border-radius:0;background:transparent;color:inherit;font:inherit;}',
        '.cc-view-sample .cc-ph--blank{color:#8a6d00;font-style:italic;border-bottom-style:dashed;}',
        '.cc-locked,.cc-cond{margin:12px 0;border:1px dashed #98a3a8;border-radius:5px;}',
        '.cc-cond{border-color:#b98900;}',
        '.cc-bar{padding:5px 10px;background:#eef0f1;border-bottom:1px dashed #98a3a8;border-radius:5px 5px 0 0;font:12px/1.5 Arial,Helvetica,sans-serif;color:#3c474c;cursor:default;}',
        '.cc-cond>.cc-bar{background:#fff6dd;border-bottom-color:#b98900;color:#5c4400;}',
        '.cc-locked__body{padding:10px;cursor:default;}',
        '.cc-cond__body{padding:10px;min-height:24px;}',
        '.cc-view-sample .cc-cond--off>.cc-cond__body{opacity:.45;}',
        '.cc-locked[data-mce-selected],.cc-cond[data-mce-selected]{outline:2px solid #4f9aaf!important;}'
    ].join('\n');

    function moveUnit(element, direction) {
        var target = (direction < 0) ? element.previousElementSibling : element.nextElementSibling;
        if (!target) {
            return;
        }
        editor.undoManager.transact(function () {
            element.parentNode.insertBefore(element, (direction < 0) ? target : target.nextSibling);
        });
        editor.selection.select(element);
        editor.nodeChanged();
        setDirty();
    }

    function selectedUnit() {
        var node = editor.selection.getNode();
        return node ? $(node).closest('.cc-locked, .cc-cond')[0] : null;
    }

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

    function editCode(element) {
        var record = records[element.getAttribute('data-cc-u')];
        if (!record) {
            return;
        }
        var source = record.source;
        if (record.kind === 'cond') {
            // As it stands now, with any wording changed inside it
            source = record.open + collect(element.querySelector('.cc-cond__body') || element, record.key).source + record.close;
        }
        editor.windowManager.open({
            title: T('editor_edit_code'),
            size: 'large',
            body: {
                type: 'panel',
                items: [
                    {type: 'htmlpanel', html: '<p>' + esc(T('editor_locked_code_help')) + '</p>'},
                    {type: 'textarea', name: 'code', maximized: true}
                ]
            },
            initialData: {code: source},
            buttons: [
                {type: 'cancel', text: 'Cancel'},
                {type: 'submit', text: T('editor_apply'), buttonType: 'primary'}
            ],
            onSubmit: function (api) {
                var code = api.getData().code;
                if (code === source) {
                    api.close();
                    return;
                }
                api.block(ccEmail.label('loading'));
                describe(code, function (result) {
                    api.unblock();
                    if (result.status !== 'ok') {
                        editor.windowManager.alert(result.message || ccEmail.label('failed'));
                        return;
                    }
                    var wasRefs = refs;
                    refs = false; // what comes back is new text, not stored units
                    var html = unitsHtml(result.units, record.list);
                    refs = wasRefs;
                    editor.undoManager.transact(function () {
                        var holder = editor.getDoc().createElement('div');
                        holder.innerHTML = html;
                        while (holder.firstChild) {
                            element.parentNode.insertBefore(holder.firstChild, element);
                        }
                        element.parentNode.removeChild(element);
                    });
                    snapshotAll();
                    paintChips(editor.getBody());
                    setDirty();
                    api.close();
                });
            }
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
            toolbar: state.canEdit ? 'undo redo | bold italic underline | link image | bullist numlist | alignleft aligncenter alignright | removeformat' : false,
            // Only the wording can be typed in; the template around it is there to be seen
            editable_root: false,
            forced_root_block: 'p',
            // Leave the email's own markup alone
            valid_elements: '*[*]',
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
                // The store's own file manager, the way it answers CKEditor
                if (!window.CKEDITOR || !CKEDITOR.tools) {
                    return;
                }
                var number = CKEDITOR.tools.addFunction(function (url) {
                    callback(url, {});
                });
                window.open(window.location.pathname + '?_g=filemanager&mode=fck&CKEditorFuncNum=' + number, 'cc_filemanager', 'width=650,height=500,resizable=yes,scrollbars=yes');
            },
            setup: function (instance) {
                instance.ui.registry.addButton('ccEditCode', {
                    text: T('editor_edit_code'),
                    onAction: function () {
                        var element = selectedUnit();
                        if (element) {
                            editCode(element);
                        }
                    }
                });
                instance.ui.registry.addButton('ccMoveUp', {
                    icon: 'chevron-up',
                    tooltip: T('editor_move_up'),
                    onAction: function () {
                        var element = selectedUnit();
                        if (element) {
                            moveUnit(element, -1);
                        }
                    }
                });
                instance.ui.registry.addButton('ccMoveDown', {
                    icon: 'chevron-down',
                    tooltip: T('editor_move_down'),
                    onAction: function () {
                        var element = selectedUnit();
                        if (element) {
                            moveUnit(element, 1);
                        }
                    }
                });
                instance.ui.registry.addButton('ccRemove', {
                    icon: 'remove',
                    tooltip: T('editor_remove'),
                    onAction: function () {
                        var element = selectedUnit();
                        if (element) {
                            instance.undoManager.transact(function () {
                                element.parentNode.removeChild(element);
                            });
                            instance.nodeChanged();
                            setDirty();
                        }
                    }
                });
                instance.ui.registry.addButton('ccParagraph', {
                    text: T('editor_new_paragraph'),
                    onAction: function () {
                        // Somewhere to type straight after a section that cannot be typed in
                        var element = selectedUnit(), paragraph;
                        if (element) {
                            paragraph = instance.getDoc().createElement('p');
                            paragraph.innerHTML = '<br data-mce-bogus="1">';
                            instance.undoManager.transact(function () {
                                element.parentNode.insertBefore(paragraph, element.nextSibling);
                            });
                            instance.selection.setCursorLocation(paragraph, 0);
                            instance.focus();
                            instance.nodeChanged();
                        }
                    }
                });
                instance.ui.registry.addContextToolbar('ccUnit', {
                    predicate: function (node) {
                        return state.canEdit && node.nodeType === 1 && (node.classList.contains('cc-locked') || node.classList.contains('cc-cond'));
                    },
                    items: 'ccEditCode | ccMoveUp ccMoveDown | ccParagraph | ccRemove',
                    position: 'node',
                    scope: 'node'
                });
                instance.on('focus', function () {
                    lastTarget = null;
                    everFocused = true;
                });
                instance.on('input change undo redo', function () {
                    if (instance.isDirty()) {
                        setDirty();
                    }
                });
                instance.on('init', function () {
                    editor = instance;
                    done();
                });
            }
        });
    }

    function loadUnits(units) {
        editor.setContent(documentHtml(units));
        editor.undoManager.clear();
        snapshotAll();
        setView(view);
        editor.setDirty(false);
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
            if (codeMode && ace_editor.getValue() !== codeAtEntry) {
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
            setCodeValue(codeAtEntry);
            startAce();
            showCode(true);
            return;
        }
        var source = currentSource();
        if (source === codeAtEntry) {
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
            refs = false; // the body is now what was typed, not the stored units
            state.placeholders = result.placeholders || state.placeholders;
            showCode(false);
            loadUnits(result.units);
            setDirty();
        });
    }

    //=====[ Saving ]=======================================

    function save() {
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
            if (refs && state.contentId) {
                fields['email_editor[items]'] = JSON.stringify(counted.items);
            } else {
                fields['email_editor[content_html]'] = counted.source;
            }
        } else {
            fields['email_editor[content_html]'] = currentSource();
        }
        ccEmail.setStatus(ccEmail.label('loading'), null);
        $.ajax({type: 'post', url: window.location.href.split('#')[0], dataType: 'json', data: fields}).done(function (result) {
            if (!result || result.status !== 'ok') {
                ccEmail.setStatus((result && result.message) || ccEmail.label('failed'), false);
                return;
            }
            var wasNew = !state.contentId;
            state.contentId = result.content_id;
            state.base = result.base;
            state.placeholders = result.placeholders || state.placeholders;
            refs = true;
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
            startAce();
            if (state.units && state.units.length && !state.visual) {
                ccEmail.setStatus(T('editor_code_only'), null);
            }
            return;
        }
        refs = !!state.base;
        startEditor(function () {
            loadUnits(state.units);
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
            $root.find('[data-width]').each(function () {
                var on = $(this).attr('data-width') === width;
                $(this).toggleClass('is-on', on).attr('aria-pressed', on ? 'true' : 'false');
            });
            $('#email_visual_wrap').css('max-width', width === '100%' ? '' : width);
        });
        $('#email_code_toggle').on('click', toggleCode);
        // A different sample means different renders: look at the saved email again with it
        $('#email_sample').on('change', function () {
            if (dirty || (editor && editor.isDirty())) {
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
            return records;
        },
        save: save,
        setView: setView,
        toggleCode: toggleCode
    };
})(jQuery);
