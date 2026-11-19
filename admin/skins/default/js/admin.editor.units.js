/**
 * Units: the part of the visual editors that keeps logic out of reach of the keyboard
 *
 * A piece of HTML with Smarty in it is divided by the server into units: wording, and
 * parts that hold logic (classes/emailsplitter.class.php). This script draws them in a
 * TinyMCE editor (the wording to type into, each value as a named placeholder, the logic
 * as a section that is moved, removed or edited as code) and reads them back out.
 *
 * Reading back gives, for each unit, either "keep unit N" or its new source. A unit that
 * was not touched is never re-serialised: it is written from the bytes it arrived with.
 *
 * It is used by the email editor (admin.email.editor.js, #4738) and by the editor on
 * products, categories and documents when "Parse Smarty Tags" is on (admin.editor.js, #4742).
 *
 *   var units = ccEditorUnits({
 *       holder: function () {},          // the element the top level units sit in
 *       T: function (name) {},           // a label
 *       canEdit: function () {},         // whether the admin may change anything
 *       describe: function (source, done) {}, // ask the server to divide some source
 *       dirty: function () {},           // something was changed
 *       clean: function (html) {},       // optional: html made safe, as a <body>
 *       ignore: function (tag, attribute) {}, // optional: an attribute the editor is allowed to drop
 *       restore: function (html) {}      // optional: last word on a unit's serialised HTML
 *   });
 *   units.attach(tinymceInstance);
 */
(function ($) {
    'use strict';

    //=====[ Shared by every editor on the page ]===========

    function esc(text) {
        return String(text === undefined || text === null ? '' : text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    //=====[ Making HTML safe to show ]=====================

    /**
     * Everything that goes into the editor is first parsed in a document that runs
     * nothing, and stripped of what could run: the template and the emails are written
     * by other admins, and this page is the admin's own origin.
     */
    var UNSAFE = 'script,iframe,frame,frameset,object,embed,applet,link,meta,base,form,input,button,select,textarea,audio,video,svg,math,style,title,noscript';
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

    /**
     * The structure of some HTML as one string: its elements in order, the names of their
     * attributes, and its text with the white space left out. Two pieces of HTML with the
     * same shape differ only in ways that do not change what is shown.
     */
    function shapeOf(html, ignore) {
        var doc = new DOMParser().parseFromString('<!doctype html><html><body>' + html + '</body></html>', 'text/html');
        return (function walk(nodes) {
            var out = '', i, j, node, names;
            for (i = 0; i < nodes.length; i++) {
                node = nodes[i];
                if (node.nodeType === 3) {
                    out += node.nodeValue.replace(/[\s\u00a0\u200b\ufeff]+/g, '');
                } else if (node.nodeType === 1) {
                    names = [];
                    for (j = 0; j < node.attributes.length; j++) {
                        if (!ignore || !ignore(node.nodeName.toLowerCase(), node.attributes[j].name.toLowerCase())) {
                            names.push(node.attributes[j].name.toLowerCase());
                        }
                    }
                    out += '<' + node.nodeName.toLowerCase() + ' ' + names.sort().join(' ') + '>' + walk(node.childNodes) + '</>';
                }
            }
            return out;
        })(doc.body.childNodes);
    }

    window.ccEditorUnits = function (host) {

    var editor = null;      // TinyMCE, once attached
    var records = {};       // key => {key, id, kind, source, list, open, close, snapshot}
    var lists = {};         // list key => keys in stored order, gaps included
    var nextKey = 0;
    var view = 'names';     // or 'sample'
    var refs = true;        // whether units can still be sent back by reference
    // The editor's own labels read the way the admin does, whichever way the content reads
    var ADMIN_DIR = (document.documentElement.getAttribute('dir') === 'rtl') ? 'rtl' : 'ltr';

    function T(name) {
        return host.T(name) || '';
    }

    function body() {
        return host.holder();
    }

    function describe(source, done) {
        host.describe(source, done);
    }

    function setDirty() {
        host.dirty();
    }

    /** A unit's HTML made safe: the host may have its own way of doing that */
    function safeBody(html) {
        return host.clean ? host.clean(html) : cleanBody(html);
    }

    /**
     * A unit's HTML made safe, and without any of the editor's own marks it arrived with:
     * an email that carries them (saved by something else, or written to confuse) is not
     * allowed to pass one of its elements off as a unit.
     */
    function cleanUnit(html) {
        var body = safeBody(html), marked = body.querySelectorAll('[data-cc-u], [data-cc-bare], [data-cc-wrap]'), i;
        for (i = 0; i < marked.length; i++) {
            marked[i].removeAttribute('data-cc-u');
            marked[i].removeAttribute('data-cc-bare');
            marked[i].removeAttribute('data-cc-wrap');
        }
        return body;
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
            snapshot: null,
            unit: unit,     // as the server described it
            wrap: '',       // how its wording is held: 'self', 'bare' (a <p> put round a loose line) or 'wrap' (a <div> put round several things)
            forced: false   // wording shown locked because the editor could not hand it back as it is
        };
        records[record.key] = record;
        if (!lists[list]) {
            lists[list] = [];
        }
        lists[list].push(record.key);
        return record;
    }

    var INLINE = /^(a|abbr|b|bdi|bdo|big|br|cite|code|del|dfn|em|font|i|img|ins|kbd|mark|q|s|samp|small|span|strike|strong|sub|sup|time|tt|u|var|wbr)$/;
    var BLOCKS = 'address,article,aside,blockquote,center,dd,details,div,dl,dt,fieldset,figcaption,figure,footer,form,h1,h2,h3,h4,h5,h6,header,hr,li,main,nav,ol,p,pre,section,table,ul';

    /** The HTML for one unit of wording, marked with its key */
    function wordingHtml(unit, record) {
        var body = cleanUnit(unit.html), nodes = body.childNodes, elements = 0, loose = false, inline = true, i, node;
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
            record.wrap = 'self';
            return body.innerHTML;
        }
        if (inline && !body.querySelector(BLOCKS)) {
            // A line of text that was never put in a paragraph
            record.wrap = 'bare';
            return '<p data-cc-u="' + record.key + '" data-cc-bare="1">' + body.innerHTML + '</p>';
        }
        record.wrap = 'wrap';
        return '<div data-cc-u="' + record.key + '" data-cc-wrap="1">' + body.innerHTML + '</div>';
    }

    function lockedHtml(unit, record) {
        var note = (unit.note !== undefined) ? unit.note : T('editor_locked_note');
        return '<div class="cc-locked mceNonEditable" contenteditable="false" data-cc-u="' + record.key + '">' +
            '<div class="cc-bar" dir="' + ADMIN_DIR + '"><strong>' + esc(unit.title || T('editor_locked')) + '</strong>' + (host.canEdit() && note ? ' <span>' + esc(note) + '</span>' : '') + '</div>' +
            '<div class="cc-locked__body">' + cleanUnit(unit.render || '').innerHTML + '</div>' +
        '</div>';
    }

    function condHtml(unit, record) {
        return '<div class="cc-cond mceNonEditable' + (unit.shown ? '' : ' cc-cond--off') + '" contenteditable="false" data-cc-u="' + record.key + '">' +
            '<div class="cc-bar" dir="' + ADMIN_DIR + '"><strong>' + esc(unit.label) + '</strong></div>' +
            '<div class="cc-cond__body mceEditable" contenteditable="true">' + unitsHtml(unit.inner || [], record.key) + '</div>' +
        '</div>';
    }

    /** What a unit looks like when it is only there to be looked at */
    function showAs(unit) {
        if (unit.kind === 'cond') {
            return (unit.inner || []).map(showAs).join('');
        }
        return unit.html || unit.render || '';
    }

    /** A unit of wording, or a condition, drawn locked: kept as it is and edited as code */
    function forcedHtml(unit, record) {
        record.kind = 'locked';
        record.forced = true;
        return lockedHtml({title: T('editor_locked_markup'), note: T('editor_locked_markup_note'), render: showAs(unit)}, record);
    }

    function unitsHtml(units, list) {
        var html = '', i, unit, record;
        for (i = 0; i < units.length; i++) {
            unit = units[i];
            record = register(unit, list);
            if (unit.asLocked && (unit.kind === 'editable' || unit.kind === 'inline' || unit.kind === 'cond')) {
                html += forcedHtml(unit, record);
            } else if (unit.kind === 'editable' || unit.kind === 'inline') {
                html += wordingHtml(unit, record);
            } else if (unit.kind === 'locked') {
                html += lockedHtml(unit, record);
            } else if (unit.kind === 'cond') {
                html += condHtml(unit, record);
            }
        }
        return html;
    }

    /**
     * A unit as it would be stored: placeholders turned back into the Smarty they stand
     * for, and nothing of the editor's left in it.
     */
    function toSource(element) {
        var clone = element.cloneNode(true), sources = [], found, i, record;
        var swap = function (node, source) {
            var token = '\uE000' + sources.length + '\uE001';
            sources.push(source);
            node.parentNode.replaceChild(clone.ownerDocument.createTextNode(token), node);
        };
        // A locked section or a condition that has ended up inside this wording, dragged or
        // pasted there, goes back as its own source and never as the sample it was showing
        found = clone.querySelectorAll('.cc-locked, .cc-cond');
        for (i = 0; i < found.length; i++) {
            if (clone.contains(found[i])) {
                record = records[found[i].getAttribute('data-cc-u')];
                swap(found[i], record ? record.source : '');
            }
        }
        found = clone.querySelectorAll('[data-cc-s]');
        for (i = 0; i < found.length; i++) {
            swap(found[i], found[i].getAttribute('data-cc-s'));
        }
        var wrapped = clone.hasAttribute('data-cc-wrap');
        // The editor's own marks stay behind, on this element and on anything pasted into it
        found = clone.querySelectorAll('[data-cc-u], [data-cc-bare], [data-cc-wrap]');
        for (i = -1; i < found.length; i++) {
            record = (i < 0) ? clone : found[i];
            record.removeAttribute('data-cc-u');
            record.removeAttribute('data-cc-bare');
            record.removeAttribute('data-cc-wrap');
        }
        var html = editor.serializer.serialize(clone, {});
        if (wrapped) {
            html = html.replace(/^<div[^>]*>\s*/, '').replace(/\s*<\/div>$/, '');
        }
        if (host.restore) {
            html = host.restore(html);
        }
        return html.replace(/\uE000(\d+)\uE001/g, function (match, n) {
            return sources[parseInt(n, 10)];
        });
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

    /**
     * The units the editor cannot hand back as they are
     *
     * TinyMCE has its own idea of what may sit inside what. A link wrapped round a block,
     * the usual way to make a button in an email, is turned inside out when it is loaded;
     * a heading round a block loses the heading. Saving such a unit, even untouched, would
     * store something else, or store it twice. So what TinyMCE would write for each unit of
     * wording is compared with what is stored, and a unit whose structure differs is not
     * offered for typing in at all.
     */
    function unfaithful() {
        var doc = editor.getDoc(), bad = [];
        Object.keys(records).forEach(function (key) {
            var record = records[key], found, written;
            if (record.kind === 'gap') {
                return;
            }
            found = doc.querySelectorAll('[data-cc-u="' + key + '"]');
            if (found.length !== 1) {
                bad.push(record);
                return;
            }
            if (record.kind !== 'editable' && record.kind !== 'inline') {
                return;
            }
            written = (record.snapshot === null) ? toSource(found[0]) : record.snapshot;
            if (record.wrap === 'bare') {
                written = written.replace(/^\s*<p>/, '').replace(/<\/p>\s*$/, '');
            }
            if (shapeOf(record.source, host.ignore) !== shapeOf(written, host.ignore)) {
                bad.push(record);
            }
        });
        return bad;
    }

    /** Whether anything lies between the units that belongs to none of them */
    function strays() {
        var doc = editor.getDoc(), stray = false;
        Object.keys(lists).forEach(function (list) {
            var holder = (list === 'top') ? body() : doc.querySelector('[data-cc-u="' + list + '"] > .cc-cond__body'), nodes, i, node;
            if (!holder) {
                return; // a condition that was drawn locked has no parts of its own on show
            }
            nodes = holder.childNodes;
            for (i = 0; i < nodes.length; i++) {
                node = nodes[i];
                if (node.nodeType === 3 && node.nodeValue.replace(/[\s\u00a0\u200b\ufeff]+/g, '') !== '') {
                    stray = true;
                } else if (node.nodeType === 1 && !node.getAttribute('data-mce-bogus') && !records[node.getAttribute('data-cc-u')] && (node.textContent.replace(/[\s\u00a0\u200b\ufeff]+/g, '') !== '' || node.querySelector('img, hr, table, [data-cc-s]'))) {
                    stray = true;
                }
            }
        });
        return stray;
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

        // What is typed where there is no paragraph arrives as loose text, placeholders and
        // inline elements. Each unbroken run of those is one line, and is stored as one paragraph.
        var entries = [], run = null, line, filled;
        for (i = 0; i < nodes.length; i++) {
            node = nodes[i];
            if (node.nodeType === 1 && node.getAttribute('data-mce-bogus')) {
                continue;
            }
            if (node.nodeType === 3 || (node.nodeType === 1 && !node.hasAttribute('data-cc-u') && INLINE.test(node.nodeName.toLowerCase()))) {
                if (!run) {
                    run = [];
                    entries.push(run);
                }
                run.push(node);
            } else if (node.nodeType === 1) {
                run = null;
                entries.push(node);
            }
        }
        for (i = 0; i < entries.length; i++) {
            node = entries[i];
            item = null;
            record = null;
            kept = false;
            if (Array.isArray(node)) {
                line = container.ownerDocument.createElement('p');
                filled = false;
                node.forEach(function (part) {
                    if (part.nodeType === 3 ? part.nodeValue.replace(/[\s\u00a0\u200b\ufeff]+/g, '') !== '' : (part.hasAttribute('data-cc-s') || part.nodeName === 'IMG' || part.textContent.replace(/[\s\u00a0\u200b\ufeff]+/g, '') !== '' || part.querySelector('img, [data-cc-s]'))) {
                        filled = true;
                    }
                    line.appendChild(part.cloneNode(true));
                });
                if (!filled) {
                    continue;
                }
                item = {s: toSource(line)};
                node = line;
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
                    if (!node.getAttribute('data-cc-u') && node.textContent.replace(/[\s\u00a0\ufeff]+/g, '') === '' && !node.querySelector('img, hr, [data-cc-s]')) {
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

    //=====[ Sections that hold logic ]=====================

    var CSS = [
        '.cc-ph{display:inline-block;max-width:32ch;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;vertical-align:bottom;padding:0 5px;border:1px solid #a9cfdb;border-radius:4px;background:#e3f0f4;color:#1e3d45;font:600 11px/18px ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;letter-spacing:.02em;text-decoration:none;cursor:default;}',
        '.cc-ph--logic{max-width:none;white-space:normal;display:inline;padding:0 3px;border:1px dashed #b98900;border-radius:3px;background:#fff6dd;color:inherit;font:inherit;}',
        '.cc-ph--empty{color:#6b5a1f;font-style:italic;}',
        '.cc-island-sample,.cc-view-sample .cc-island-names{display:none;}',
        '.cc-view-sample .cc-island-sample{display:inline;}',
        '.cc-island-name{display:inline-block;padding:0 5px;border:1px solid #a9cfdb;border-radius:4px;background:#e3f0f4;color:#1e3d45;font:600 11px/18px ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;letter-spacing:.02em;vertical-align:bottom;}',
        '.cc-view-sample .cc-ph:not(.cc-ph--logic){display:inline;max-width:none;white-space:normal;padding:0;border:0;border-bottom:1px dotted #5d9fb2;border-radius:0;background:transparent;color:inherit;font:inherit;}',
        '.cc-view-sample .cc-ph--blank{color:#8a6d00;font-style:italic;border-bottom-style:dashed;}',
        '.cc-locked,.cc-cond{margin:12px 0;border:1px dashed #98a3a8;border-radius:5px;}',
        '.cc-cond{border-color:#b98900;}',
        '.cc-bar{text-align:start;padding:5px 10px;background:#eef0f1;border-bottom:1px dashed #98a3a8;border-radius:5px 5px 0 0;font:12px/1.5 Arial,Helvetica,sans-serif;color:#3c474c;cursor:default;}',
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
                api.block(T('loading'));
                describe(code, function (result) {
                    api.unblock();
                    if (result.status !== 'ok') {
                        editor.windowManager.alert(result.message || T('failed'));
                        return;
                    }
                    var wasRefs = refs;
                    refs = false; // what comes back is new text, not stored units
                    var html = unitsHtml(result.units, record.list);
                    refs = wasRefs;
                    editor.undoManager.transact(function () {
                        var holder = editor.getDoc().createElement('div'), doc = editor.getDoc();
                        holder.innerHTML = html;
                        // TinyMCE's parser does this for what it is given; this goes in directly
                        $(holder).find('.mceNonEditable').attr('contenteditable', 'false');
                        $(holder).find('.mceEditable').attr('contenteditable', 'true');
                        while (holder.firstChild) {
                            element.parentNode.insertBefore(holder.firstChild, element);
                        }
                        element.parentNode.removeChild(element);
                        snapshotAll();
                        // Anything in it the editor would hand back changed is locked where it stands
                        unfaithful().forEach(function (bad) {
                            var at = doc.querySelectorAll('[data-cc-u="' + bad.key + '"]'), drawn = doc.createElement('div');
                            if (at.length === 1 && bad.unit && bad.kind !== 'locked') {
                                bad.unit.asLocked = true;
                                drawn.innerHTML = forcedHtml(bad.unit, bad);
                                at[0].parentNode.replaceChild(drawn.firstChild, at[0]);
                            }
                        });
                    });
                    paintChips(editor.getBody());
                    setDirty();
                    api.close();
                });
            }
        });
    }

    /** The buttons a selected section gets: edit its code, move it, add a paragraph after it, remove it */
    function registerTools(instance) {
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
                        return host.canEdit() && node.nodeType === 1 && (node.classList.contains('cc-locked') || node.classList.contains('cc-cond'));
                    },
                    items: 'ccEditCode | ccMoveUp ccMoveDown | ccParagraph | ccRemove',
                    position: 'node',
                    scope: 'node'
                });
    }

    /**
     * Put units in the editor
     *
     * @param {Array} units As the server described them
     * @param {Function} build Makes the editor's whole content out of them (it calls reset() and unitsHtml())
     * @return {boolean} false when the editor cannot show them without changing them
     */
    function load(units, build) {
        var tries = 0, bad;
        do {
            editor.setContent(build(units));
            snapshotAll();
            bad = unfaithful();
            bad.forEach(function (record) {
                record.unit.asLocked = true;
            });
        } while (bad.length && ++tries < 3);
        return !(bad.length || strays());
    }

    return {
        attach: function (instance) {
            editor = instance;
        },
        reset: function () {
            records = {};
            lists = {};
        },
        records: function () {
            return records;
        },
        refs: function (value) {
            if (value !== undefined) {
                refs = !!value;
            }
            return refs;
        },
        view: function (value) {
            if (value !== undefined) {
                view = value;
            }
            return view;
        },
        unitsHtml: unitsHtml,
        toSource: toSource,
        collect: collect,
        load: load,
        chipHtml: chipHtml,
        paintChips: paintChips,
        registerTools: registerTools,
        CSS: CSS
    };

    };

    window.ccEditorUnits.esc = esc;
    window.ccEditorUnits.cleanBody = cleanBody;
    window.ccEditorUnits.shapeOf = shapeOf;
})(jQuery);
