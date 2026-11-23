/**
 * The rich text editor of the admin: <textarea class="cc-editor"> (#4742)
 *
 * Products, categories, documents and the store settings use it. It is TinyMCE, set up
 * once here: the store's image picker, the admin's language, and the two things a
 * description needs that an ordinary editor gets wrong.
 *
 * 1. What is stored is kept. A description may hold a script, a form or an inline
 *    handler that was put there on purpose. None of it may run in the admin, and none
 *    of it may be lost by opening and saving: such an element is shown as a small
 *    marker that moves and deletes as one piece and goes back as it was, and such an
 *    attribute is put aside under another name while the editor is open.
 *
 * 2. Smarty is understood where it is used. With "Parse Smarty Tags" on, the content is
 *    a template: the server divides it into wording and logic (admin.editor.units.js),
 *    values are shown as macros, logic is kept out of reach of the keyboard and edited
 *    as code, and what was not touched is written back exactly as it was. It is checked
 *    before the form is sent, because a description that is not valid Smarty takes its
 *    page down with it. With the toggle off, a brace is a brace.
 *
 * Options, on the textarea:
 *   data-cc-smarty="#id"            the "Parse Smarty Tags" toggle that goes with it, or "1" for always
 *   data-cc-height="400"            the height to start at
 *   data-cc-keep-image-size="1"     leave the width and height of images alone
 *   data-cc-email="1"               the content is sent as an email: an image gets its whole address, and a size
 *   data-cc-macros='[{"tag":"{$DATA.store_name}","label":"[STORE_NAME]","description":"","sample":""}]'
 *                                   values to offer in a Macros menu, and to show by name
 *   data-cc-frame="#id"             a list or field whose value names what the content sits inside (an
 *                                   email template, say). It is drawn round the content, which is then
 *                                   the only part that can be changed. Needs data-cc-smarty="1".
 *   data-cc-frames='{"3":{"html":"...<div id=\"cc-email-slot\"></div>...","css":"","body":{}}}'
 *                                   what to draw for each value: its HTML with a slot where the content
 *                                   goes, its style rules, and the attributes of its <body>
 *
 * class="fck" is still CKEditor, for extensions, and is deprecated.
 *
 * For other scripts on the page (an extension that used to reach for CKEDITOR.instances):
 *   ccEditor.get(textarea)          the content as it would be stored
 *   ccEditor.set(textarea, html)    replace the content
 *   ccEditor.of(textarea)           the editor itself, or null; .editor is the TinyMCE instance
 */
(function ($) {
    'use strict';

    var data = null;        // what the page was given: where TinyMCE is, the labels, the language
    var fields = [];
    var NONCE = Math.random().toString(36).slice(2, 10) + Math.random().toString(36).slice(2, 10);
    var PARK = 'data-cc-pa-' + NONCE + '-';
    var parked = {};        // the stored elements shown as markers, by the number on the marker
    var parkedCount = 0;

    // Elements that could run something, or that an editor has no business rearranging.
    // In stored content they are kept and shown as a marker; in pasted content they go.
    var PARKED = 'script,style,noscript,object,embed,applet,link,meta,base,frame,frameset,form,input,button,select,textarea,svg,math,template,title';
    var URL_ATTRIBUTES = ['href', 'src', 'action', 'formaction', 'xlink:href', 'background', 'poster', 'data'];

    function T(name) {
        return (data && data.strings && data.strings[name]) || '';
    }

    function esc(text) {
        return window.ccEditorUnits.esc(text);
    }

    function lf(text) {
        return String(text).replace(/\r\n?/g, '\n');
    }

    //=====[ Keeping what is stored, without letting it run ]=====

    /**
     * Stored content, made safe to show without losing any of it
     *
     * @param {string} html
     * @param {boolean} plain True when braces are text: nothing in it is a macro
     * @return {HTMLElement} a <body> in a document that runs nothing
     */
    function park(html, plain) {
        var doc = new DOMParser().parseFromString('<!doctype html><html><body>' + html + '</body></html>', 'text/html');
        var body = doc.body, found, i, j, node, chip, block, all, attrs, name, value;
        // Marks of ours that the content arrived with mean nothing, and must not be taken for ours
        found = body.querySelectorAll('[data-cc-p]' + (plain ? ', [data-cc-s]' : ''));
        for (i = 0; i < found.length; i++) {
            found[i].removeAttribute('data-cc-p');
            if (plain) {
                found[i].removeAttribute('data-cc-s');
            }
        }
        found = body.querySelectorAll(PARKED);
        for (i = 0; i < found.length; i++) {
            node = found[i];
            if (!body.contains(node)) {
                continue; // inside one that has been dealt with
            }
            block = (node.parentNode === body);
            chip = doc.createElement(block ? 'div' : 'span');
            chip.className = 'cc-ph cc-parked mceNonEditable' + (block ? ' cc-parked--block' : '');
            chip.setAttribute('contenteditable', 'false');
            // Numbered across the page, so a marker copied from one editor to another still means what it meant
            chip.setAttribute('data-cc-p', NONCE + (++parkedCount));
            chip.setAttribute('title', T('editor_parked'));
            chip.textContent = '<' + node.nodeName.toLowerCase() + '>';
            parked[NONCE + parkedCount] = node.outerHTML;
            node.parentNode.replaceChild(chip, node);
        }
        all = body.getElementsByTagName('*');
        for (i = 0; i < all.length; i++) {
            node = all[i];
            attrs = node.attributes;
            for (j = attrs.length - 1; j >= 0; j--) {
                name = attrs[j].name.toLowerCase();
                if (name.indexOf('data-cc-pa-') === 0) {
                    node.removeAttribute(attrs[j].name);
                    continue;
                }
                value = String(attrs[j].value).replace(/[\u0000- ]+/g, '').toLowerCase();
                if (name.indexOf('on') === 0 || name === 'srcdoc' || (URL_ATTRIBUTES.indexOf(name) !== -1 && /^(javascript|vbscript|data:text\/html)/.test(value))) {
                    // Put aside under a name that does nothing, and given back when it is saved
                    try {
                        node.setAttribute(PARK + name, attrs[j].value);
                    } catch (e) { /* a name that cannot be an attribute: it goes */ }
                    node.removeAttribute(attrs[j].name);
                }
            }
            if (node.nodeName === 'IFRAME' && !node.hasAttribute('sandbox')) {
                // Shown, but not run, while the editor is open: the page it frames could be anything
                node.setAttribute('sandbox', '');
                node.setAttribute(PARK + 'unboxed', '1');
            }
        }
        return body;
    }

    /**
     * Pasted or inserted content: what could run is removed, not kept. Undo and redo come
     * this way too, so the editor's own marks are left as they are.
     */
    function strict(html) {
        var body = window.ccEditorUnits.cleanBody(html, PARKED), all = body.getElementsByTagName('*'), i, j, attrs, name;
        for (i = 0; i < all.length; i++) {
            attrs = all[i].attributes;
            for (j = attrs.length - 1; j >= 0; j--) {
                name = attrs[j].name.toLowerCase();
                // Something put aside by an editor on another page: it cannot be given back here
                if (name.indexOf('data-cc-pa-') === 0 && name.indexOf(PARK) !== 0) {
                    all[i].removeAttribute(attrs[j].name);
                }
            }
        }
        return body;
    }

    /**
     * Serialised HTML: the sandbox this script put on a frame comes off again, and the
     * attributes that were put aside get their own names back. The names carry a number
     * made up for this page, so nothing that was typed can be taken for one.
     */
    var RESTORE = new RegExp('(\\s)' + PARK + '([a-z0-9:_.-]+)=', 'gi');
    var UNBOXED = new RegExp('\\s' + PARK + 'unboxed="1"', 'g');

    function restore(html) {
        return html.replace(/<iframe\b[^>]*>/gi, function (tag) {
            return (tag.indexOf(PARK + 'unboxed') === -1) ? tag : tag.replace(UNBOXED, '').replace(/\ssandbox(?:="")?(?=[\s>])/g, '');
        }).replace(RESTORE, '$1$2=');
    }

    //=====[ The server's part ]============================

    /** Have some source checked as Smarty and, unless only the check is wanted, divided into units */
    function describe(source, checkOnly, done) {
        var post = {token: $('.cc_session_token').val(), source: source};
        if (checkOnly) {
            post.check = 1;
        }
        $.ajax({type: 'post', url: data.endpoint, dataType: 'json', data: post}).done(function (result) {
            if (result && result.error === 'unauthorised') {
                // Someone who may look but not edit: the content is shown as it is, and that is not a fault
                done({status: 'error', message: '', denied: true});
                return;
            }
            done(result && result.status ? result : {status: 'error', message: T('failed')});
        }).fail(function () {
            done({status: 'error', message: T('failed')});
        });
    }

    //=====[ One editor ]===================================

    function Field(textarea) {
        var self = this;
        this.textarea = textarea;
        this.form = textarea.form;
        this.editor = null;
        this.original = textarea.value; // what to save while the editor cannot say better
        this.ready = false;
        this.touched = false;       // whether anything was changed since it was shown
        this.trusted = false;       // content being set is this script's own, made safe already
        this.checked = null;        // the source last known to be valid Smarty
        this.turn = 0;              // which load is the current one
        this.email = textarea.hasAttribute('data-cc-email');
        this.keepSizes = this.email || textarea.hasAttribute('data-cc-keep-image-size');
        this.macros = [];           // the values this content is known to have
        try {
            this.macros = JSON.parse(textarea.getAttribute('data-cc-macros') || '[]') || [];
        } catch (e) { /* none */ }
        this.toggle = null;
        var smarty = textarea.getAttribute('data-cc-smarty') || '';
        if (smarty !== '' && smarty !== '1' && smarty !== '0') {
            try {
                this.toggle = document.querySelector(smarty);
            } catch (e) { /* not a selector */ }
        }
        this.smarty = this.toggle ? (this.toggle.value === '1') : (smarty === '1');
        this.units = false;         // whether what is in the editor is units: a template, or content shown as code
        this.frames = {};           // what the content can be shown inside, by the value that chooses it
        this.frameBy = null;        // the list that chooses
        this.frame = null;          // the one chosen now
        this.inFrame = false;       // whether what is in the editor is drawn inside it
        var frameBy = textarea.getAttribute('data-cc-frame') || '';
        if (frameBy !== '') {
            try {
                this.frameBy = document.querySelector(frameBy);
                this.frames = JSON.parse(textarea.getAttribute('data-cc-frames') || '{}') || {};
            } catch (e) { /* no frames */ }
            this.frame = this.chosenFrame();
        }
        this.core = window.ccEditorUnits({
            holder: function () {
                return self.host();
            },
            T: T,
            canEdit: function () {
                return true;
            },
            describe: function (source, done) {
                if (self.smarty) {
                    describe(source, false, done);
                } else {
                    done({status: 'ok', units: self.asCode(source)}); // code that is not a template stays code
                }
            },
            dirty: function () {
                self.changed();
            },
            clean: function (html) {
                return self.named(park(html, false));
            },
            ignore: function (tag, attribute) {
                return !self.keepSizes && tag === 'img' && (attribute === 'width' || attribute === 'height');
            },
            chips: {
                selector: '[data-cc-p]',
                source: function (node) {
                    var source = parked[node.getAttribute('data-cc-p')];
                    return (source === undefined) ? '' : source;
                }
            },
            restore: restore
        });
        this.core.refs(false); // the whole source is sent back with the form, never references
    }

    /** A value this content is known to have is shown by its name, not as its tag */
    Field.prototype.named = function (body) {
        var chips, i, j, tag;
        if (!this.macros.length) {
            return body;
        }
        chips = body.querySelectorAll('.cc-ph[data-cc-l]');
        for (i = 0; i < chips.length; i++) {
            tag = (chips[i].getAttribute('data-cc-s') || '').replace(/\|[^{}]*\}$/, '}'); // without its modifiers
            for (j = 0; j < this.macros.length; j++) {
                if (this.macros[j].tag === tag) {
                    chips[i].setAttribute('data-cc-l', this.macros[j].label);
                    chips[i].setAttribute('data-cc-v', this.macros[j].sample || '');
                    chips[i].setAttribute('title', this.macros[j].description || tag);
                    chips[i].textContent = this.macros[j].label;
                    break;
                }
            }
        }
        return body;
    };

    //=====[ A frame round the content ]====================

    /** What the list that chooses a frame is set to, or null */
    Field.prototype.chosenFrame = function () {
        var frame = this.frameBy ? this.frames[String(this.frameBy.value)] : null;
        return (frame && typeof frame.html === 'string' && frame.html !== '') ? frame : null;
    };

    /** The frame's markup with nothing in it that could run, or null if it has nowhere for the content */
    Field.prototype.frameBody = function () {
        var body = this.frame ? window.ccEditorUnits.cleanBody(this.frame.html) : null;
        return (body && body.querySelector('#cc-email-slot')) ? body : null;
    };

    /** Where the content is in the editor: the whole of it, or the part of a frame that holds it */
    Field.prototype.host = function () {
        var editor = this.editor, inner;
        if (!editor) {
            return null;
        }
        inner = this.inFrame ? editor.getDoc().getElementById('cc-frame-body') : null;
        return inner || editor.getBody();
    };

    /** The content as the editor is to show it: inside the frame, where there is one */
    Field.prototype.framed = function (content) {
        var body = this.inFrame ? this.frameBody() : null, slot, holder;
        if (!body) {
            return content;
        }
        slot = body.querySelector('#cc-email-slot');
        holder = body.ownerDocument.createElement('div');
        holder.id = 'cc-frame-body';
        holder.className = 'mceEditable'; // the one part TinyMCE lets the keyboard into
        // Somewhere for the caret to go when there is nothing written yet
        holder.innerHTML = (content === '') ? '<p><br data-mce-bogus="1"></p>' : content;
        slot.parentNode.replaceChild(holder, slot);
        return body.innerHTML;
    };

    /**
     * Make the editor's page suit what is about to be shown. In a frame the page is the
     * frame's: its colours and style rules, not the editor's own, and nothing outside the
     * content can be typed in. Out of one it is an ordinary editor again.
     */
    Field.prototype.dress = function () {
        var editor = this.editor, doc = editor.getDoc(), framed = this.inFrame, style = doc.getElementById('cc-frame-style'), links, i, probe, attributes, background, colour;
        if (typeof editor.setEditableRoot === 'function') {
            editor.setEditableRoot(!framed);
        }
        // The editor's own typography would restyle the frame's tables and text
        links = doc.querySelectorAll('link[rel="stylesheet"]');
        for (i = 0; i < links.length; i++) {
            if (/\/skins\/content\//.test(links[i].getAttribute('href') || '')) {
                links[i].disabled = framed;
            }
        }
        if (!style) {
            style = doc.createElement('style');
            style.id = 'cc-frame-style';
            doc.head.appendChild(style);
        }
        if (framed) {
            attributes = this.frame.body || {};
            probe = document.createElement('div');
            probe.style.cssText = String(attributes.style || '');
            background = probe.style.backgroundColor || String(attributes.bgcolor || '').replace(/[^#\w(),.% -]/g, '') || '#ffffff';
            colour = probe.style.color || String(attributes.text || '').replace(/[^#\w(),.% -]/g, '') || '#000000';
            style.textContent = String(this.frame.css || '') + '\nbody{background:' + background + ';color:' + colour + ';margin:0;padding:12px;}\n' + FRAME_CSS;
        } else {
            style.textContent = '';
        }
        // No dashed guides round the tables a frame is laid out with
        editor.hasVisual = !framed;
        editor.addVisual();
    };

    /** The list that chooses the frame was changed: the same content, shown inside the other one */
    Field.prototype.reframe = function () {
        var self = this, frame = this.chosenFrame();
        if (frame === this.frame) {
            return;
        }
        this.frame = frame;
        if (!this.editor || !this.smarty) {
            return; // it is drawn when the content is next shown as a template
        }
        this.showSmarty(this.source(), function () {
            self.sync();
        });
    };

    /**
     * The content as it is to be stored. What nobody changed is what was there: an editor
     * that is only opened never rewrites what it was given.
     */
    Field.prototype.source = function () {
        if (!this.editor || !this.ready || !this.touched) {
            return this.original;
        }
        return this.serialise();
    };

    /** What is in the editor, as source */
    Field.prototype.serialise = function () {
        var editor = this.editor, body, wrap, i;
        if (this.units) {
            return this.core.collect(this.host(), 'top').source;
        }
        body = editor.getBody();
        if (editor.getContent() === '' && !body.querySelector('[data-cc-p]')) {
            return ''; // TinyMCE's own idea of empty
        }
        wrap = body.ownerDocument.createElement('div');
        wrap.setAttribute('data-cc-wrap', '1');
        for (i = 0; i < body.childNodes.length; i++) {
            wrap.appendChild(body.childNodes[i].cloneNode(true));
        }
        return this.core.toSource(wrap);
    };

    /** Something was changed by the person at the keyboard */
    Field.prototype.changed = function () {
        if (!this.ready) {
            return;
        }
        this.touched = true;
        this.say('', null);
        this.tell();
    };

    /** The form has something to save, and its text box is brought up to date */
    Field.prototype.tell = function () {
        var self = this;
        if (this.form && $.fn.dirty) {
            try {
                $(this.form).dirty('setAsDirty');
            } catch (e) { /* the plugin was not started on this form */ }
        }
        // Whatever sends the form, and however, finds the text box up to date
        clearTimeout(this.timer);
        this.timer = setTimeout(function () {
            self.sync();
        }, 400);
    };

    Field.prototype.sync = function () {
        clearTimeout(this.timer);
        if (this.editor && this.ready) {
            this.textarea.value = this.source();
        }
    };

    /** A line under the editor: an error (false), good news (true) or a plain remark (null); '' clears it */
    Field.prototype.say = function (message, good) {
        var container = this.editor ? this.editor.getContainer() : null, note = this.note;
        if (!container) {
            return;
        }
        if (!note) {
            if (message === '') {
                return;
            }
            note = this.note = document.createElement('div');
            note.setAttribute('role', 'status');
            container.parentNode.insertBefore(note, container.nextSibling);
        }
        note.className = 'cc-editor-note' + (good === false ? ' is-error' : (good === true ? ' is-ok' : ''));
        note.textContent = message;
        note.hidden = (message === '');
    };

    /** The whole content as one section that is edited as code */
    Field.prototype.asCode = function (source) {
        return [{id: '0', kind: 'locked', source: source, title: T('editor_locked_markup'), note: T('editor_logic_note'), render: '<pre class="cc-code">' + esc(source) + '</pre>'}];
    };

    Field.prototype.set = function (html) {
        this.trusted = true;
        try {
            this.editor.setContent(html);
        } finally {
            this.trusted = false;
        }
    };

    /**
     * A whole HTML document (the page a closed store shows, say). An editor works on what
     * is inside a <body>: given a document, a browser drops its <html>, <head> and <body>
     * and what they said. So it is shown as code, and stays exactly as it is.
     */
    var DOCUMENT = /<!doctype|<\/?(?:html|head|body)[\s>]/i;

    /** Show some source with braces as text */
    Field.prototype.showPlain = function (source) {
        var self = this, whole = DOCUMENT.test(source);
        this.turn++;
        this.smarty = false;
        this.units = whole;
        this.original = source;
        this.core.reset();
        this.inFrame = false; // a frame goes round a template's wording, and this is not one
        this.dress();
        if (whole) {
            this.trusted = true;
            try {
                this.core.load(this.asCode(source), function (units) {
                    self.core.reset();
                    return self.core.unitsHtml(units, 'top');
                });
            } finally {
                this.trusted = false;
            }
        } else {
            this.set(park(source, true).innerHTML);
        }
        this.settle();
    };

    /** Show some source as a template: wording to type in, macros, and logic kept as code */
    Field.prototype.showSmarty = function (source, done) {
        var self = this, turn = ++this.turn, editor = this.editor;
        this.smarty = true;
        this.units = true;
        this.ready = false;
        this.original = source;
        editor.mode.set('readonly');
        describe(source, false, function (result) {
            var good = (result.status === 'ok' && Array.isArray(result.units)), message = good ? '' : (result.message || (result.denied ? '' : T('failed')));
            if (turn !== self.turn || !self.editor) {
                return; // something else has been shown since
            }
            var build = function (units) {
                self.core.reset();
                return self.framed(self.core.unitsHtml(units, 'top'));
            };
            // Only where the editor can keep the keyboard out of the frame
            self.inFrame = (typeof editor.setEditableRoot === 'function') && !!self.frameBody();
            self.dress();
            self.trusted = true;
            try {
                if (!good || !self.core.load(result.units, build)) {
                    // Not something that can be divided, or not something the editor can hand back unchanged
                    self.core.load(self.asCode(source), build);
                    message = message || (result.denied ? '' : T('editor_content_code_only'));
                }
            } finally {
                self.trusted = false;
            }
            self.checked = good ? lf(source) : null;
            self.settle();
            self.say(message, good ? null : false);
            if (done) {
                done(good, message);
            }
        });
    };

    /** What was just shown is the starting point: nothing to undo, nothing unsaved */
    Field.prototype.settle = function () {
        var editor = this.editor;
        this.ready = true;
        this.touched = false;
        editor.mode.set('design');
        editor.undoManager.clear();
        editor.setDirty(false);
        this.say('', null);
    };

    /** The code view: the whole content as it is stored */
    Field.prototype.openCode = function () {
        var self = this, editor = this.editor, source = this.source();
        if (!this.ready) {
            return;
        }
        editor.windowManager.open({
            title: T('editor_code_view'),
            size: 'large',
            body: {
                type: 'panel',
                items: [
                    {type: 'htmlpanel', html: '<p>' + esc(this.smarty ? T('editor_source_help_smarty') : T('editor_source_help')) + '</p>'},
                    {type: 'textarea', name: 'code', maximized: true}
                ]
            },
            initialData: {code: source},
            buttons: [
                {type: 'cancel', text: T('cancel')},
                {type: 'submit', text: T('editor_apply'), buttonType: 'primary'}
            ],
            onSubmit: function (api) {
                var code = api.getData().code;
                if (lf(code) === lf(source)) {
                    api.close();
                    return;
                }
                // What was typed here is what is stored, as typed
                if (!self.smarty) {
                    self.showPlain(code);
                    self.tell();
                    api.close();
                    return;
                }
                api.block(T('loading'));
                // Checked before it is shown: code that is not valid Smarty stays in the box to be put right
                describe(code, true, function (result) {
                    if (result.status !== 'ok') {
                        api.unblock();
                        editor.windowManager.alert(result.message || T('failed'));
                        return;
                    }
                    self.showSmarty(code, function () {
                        api.unblock();
                        self.tell();
                        api.close();
                    });
                });
            }
        });
    };

    /** Replace the content, from outside: it is shown the way the box is set, and counts as a change to save */
    Field.prototype.put = function (source) {
        var self = this;
        source = String(source === undefined || source === null ? '' : source);
        if (!this.editor) {
            this.original = source; // not up yet: it starts with this
            this.textarea.value = source;
        } else if (this.smarty) {
            this.showSmarty(source, function () {
                self.tell();
            });
        } else {
            this.showPlain(source);
            this.tell();
        }
    };

    /** "Parse Smarty Tags" was switched */
    Field.prototype.switchTo = function (smarty) {
        var self = this, source;
        if (!this.editor || smarty === this.smarty) {
            return;
        }
        // What is in the box is the same content, looked at the other way: nothing is rewritten by the switch
        source = this.source();
        if (smarty) {
            this.showSmarty(source, function () {
                self.sync();
            });
        } else {
            this.showPlain(source);
            this.sync();
        }
    };

    Field.prototype.start = function () {
        var self = this, textarea = this.textarea, dark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        var height = parseInt(textarea.getAttribute('data-cc-height'), 10) || 380;
        var options = {
            target: textarea,
            license_key: 'gpl',
            base_url: data.tinymce,
            suffix: '.min',
            skin: dark ? 'oxide-dark' : 'oxide',
            content_css: dark ? 'dark' : 'default',
            content_style: this.core.CSS + '\n' + CSS,
            document_base_url: data.storeUrl,
            height: height,
            min_height: 200,
            resize: true,
            menubar: false,
            statusbar: true,
            elementpath: false,
            branding: false,
            promotion: false,
            contextmenu: false,         // the browser's own menu, with its spelling suggestions
            browser_spellcheck: true,
            toolbar_mode: 'sliding',
            // No media plugin: it gives a stored <video> or <iframe> a fixed width and height when it
            // writes it back, which beats a responsive skin. An embed is pasted into the code view.
            plugins: 'link lists autolink image table charmap anchor searchreplace fullscreen',
            // The macros come early: beside a column of fields the editor is narrow, and what does not fit slides out of sight
            toolbar: 'undo redo | blocks | ' + (this.macros.length ? 'ccMacros | ' : '') + 'bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | ccCode fullscreen | anchor charmap hr removeformat searchreplace',
            forced_root_block: 'p',
            // The content's own markup is left alone
            valid_elements: '*[*]',
            verify_html: false,
            convert_fonts_to_spans: false,
            remove_trailing_brs: false,
            convert_urls: false,
            relative_urls: false,
            remove_script_host: false,
            element_format: 'html',
            entity_encoding: 'named',
            entities: '160,nbsp',
            // A frame is not rewritten for the storefront; what it may do in here is seen to by park()
            sandbox_iframes: false,
            paste_data_images: false,
            link_title: false,
            image_description: true,
            // Storefronts are responsive: a fixed width and height on an image would beat the skin's own sizing
            image_dimensions: this.keepSizes,
            object_resizing: this.keepSizes ? true : 'table',
            file_picker_types: 'image',
            file_picker_callback: function (callback) {
                // The store's own image picker, the one the rest of the admin uses
                if (typeof window.ccOpenImagePickerDialog !== 'function') {
                    return;
                }
                window.ccOpenImagePickerDialog({
                    storageKey: 'editor_dialog',
                    onPick: function (file) {
                        var path = 'images/source/' + (file.filepath || '') + (file.filename || ''), address = path, probe;
                        $.colorbox.close();
                        if (!self.email) {
                            callback(path, {});
                            return;
                        }
                        // A mail client needs the whole address, and a size: and no wider than an
                        // email is, or a photo straight from a camera pushes the layout out
                        try {
                            address = new URL(path, data.storeUrl).href;
                        } catch (e) {}
                        probe = new Image();
                        probe.onload = function () {
                            var width = probe.naturalWidth, height = probe.naturalHeight;
                            if (width > 600) {
                                height = Math.round(height * 600 / width);
                                width = 600;
                            }
                            callback(address, width && height ? {width: String(width), height: String(height)} : {});
                        };
                        probe.onerror = function () {
                            callback(address, {});
                        };
                        probe.src = address;
                    }
                });
            },
            setup: function (instance) {
                // Nothing gets into the editor without being made safe first. What this script
                // sets has been through park(); the stored content the editor starts with goes
                // through it here; anything else was pasted, dropped or put in by a dialog, and
                // loses what could run. These run before TinyMCE's own listeners.
                instance.on('BeforeSetContent', function (event) {
                    if (self.trusted || typeof event.content !== 'string') {
                        return;
                    }
                    if (!self.editor) {
                        // The content the editor starts with: put in once the editor is up
                        event.content = '';
                    } else if (event.content !== '') {
                        event.content = strict(event.content).innerHTML;
                    }
                }, true);
                instance.on('PastePreProcess', function (event) {
                    if (typeof event.content === 'string' && event.content !== '') {
                        event.content = strict(event.content).innerHTML;
                    }
                }, true);
                // What goes back to the text box is the source, not the editor's own HTML. TinyMCE
                // saves itself when its form is sent, and asks for its content to do it.
                instance.on('GetContent', function (event) {
                    if (event.save) {
                        event.content = self.source();
                    }
                });
                instance.on('PreInit', function () {
                    if (!self.keepSizes) {
                        instance.serializer.addNodeFilter('img', function (nodes) {
                            for (var i = 0; i < nodes.length; i++) {
                                nodes[i].attr('width', null);
                                nodes[i].attr('height', null);
                            }
                        });
                    }
                });
                instance.ui.registry.addButton('ccCode', {
                    icon: 'sourcecode',
                    tooltip: T('editor_code_view'),
                    onAction: function () {
                        self.openCode();
                    }
                });
                if (self.macros.length) {
                    instance.ui.registry.addMenuButton('ccMacros', {
                        text: T('editor_placeholders'),
                        fetch: function (callback) {
                            callback(self.macros.map(function (macro) {
                                return {
                                    type: 'menuitem',
                                    text: macro.label + (macro.description ? '  ' + macro.description : ''),
                                    onAction: function () {
                                        // Where the caret is, unless that is inside something that is kept as it is
                                        var node = instance.selection.getNode(), host = self.host();
                                        if (self.ready && node && host && (node === host || host.contains(node)) && !$(node).closest('.cc-locked, .cc-ph').length) {
                                            instance.insertContent(self.core.chipHtml(macro));
                                            self.changed();
                                        }
                                    }
                                };
                            }));
                        }
                    });
                }
                self.core.registerTools(instance);
                instance.on('input change undo redo', function () {
                    if (self.ready && instance.isDirty()) {
                        self.changed();
                    }
                });
                instance.on('blur', function () {
                    if (self.touched) {
                        self.sync();
                    }
                });
                instance.on('init', function () {
                    self.editor = instance;
                    self.core.attach(instance);
                    if (self.smarty) {
                        self.showSmarty(self.original);
                    } else {
                        self.showPlain(self.original);
                    }
                });
                instance.on('remove', function () {
                    self.editor = null;
                    self.core.attach(null);
                });
            }
        };
        if (data.language) {
            options.language = data.language;
        }
        if (this.toggle) {
            $(this.toggle).on('change', function () {
                self.switchTo(this.value === '1');
            });
        }
        if (this.frameBy) {
            $(this.frameBy).on('change', function () {
                self.reframe();
            });
        }
        window.tinymce.init(options);
    };

    // On top of the units' own styles: a marker for a kept element, and logic shown as its code
    var CSS = [
        '.cc-ph{max-width:48ch;}',
        '.cc-ph--code{display:inline-block;max-width:48ch;white-space:nowrap;padding:0 5px;border:1px dashed #b98900;border-radius:4px;background:#fff6dd;color:#5c4400;font:600 11px/18px ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;}',
        '.cc-parked{border:1px dashed #98a3a8;background:#eef0f1;color:#3c474c;}',
        '.cc-parked--block{display:block;max-width:none;margin:8px 0;padding:3px 8px;}',
        '.cc-cond__body{padding:4px 10px;}',
        '.cc-code{margin:0;white-space:pre-wrap;word-break:break-word;font:12px/1.5 ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;color:inherit;}'
    ].join('\n');

    // Inside a frame: where the content goes is marked out, and has some height before anything is written
    var FRAME_CSS = [
        '#cc-frame-body{outline:1px dashed #9fc6d2;outline-offset:4px;min-height:120px;}',
        '#cc-frame-body:focus,#cc-frame-body:focus-within{outline-color:#5ebdd5;}'
    ].join('\n');

    //=====[ Sending the form ]=============================

    /**
     * A description that is not valid Smarty takes its storefront page down, so one that
     * was changed is checked before the form goes. The form is held, the server compiles
     * each one (it runs nothing), and the form is then sent as it was going to be.
     */
    function guard(form) {
        form.addEventListener('submit', function (event) {
            var mine = fields.filter(function (field) {
                return field.form === form && field.editor && field.ready;
            }), waiting;
            mine.forEach(function (field) {
                field.sync();
            });
            waiting = mine.filter(function (field) {
                return field.smarty && field.checked !== lf(field.textarea.value);
            });
            if (!waiting.length) {
                return;
            }
            event.preventDefault();
            event.stopImmediatePropagation();
            if (form.getAttribute('data-cc-checking')) {
                return; // already asking
            }
            form.setAttribute('data-cc-checking', '1');
            var submitter = event.submitter || null, left = waiting.length, failed = null;
            waiting.forEach(function (field) {
                var source = field.textarea.value;
                field.say(T('loading'), null);
                describe(source, true, function (result) {
                    if (result.status === 'ok' || result.denied) {
                        // Checked; or not this admin's to have checked, and the server has its own say when it saves
                        field.checked = lf(source);
                        field.say('', null);
                    } else {
                        field.say(T('editor_smarty_refused').replace('%s', result.message || T('failed')), false);
                        failed = failed || field;
                    }
                    if (--left > 0) {
                        return;
                    }
                    form.removeAttribute('data-cc-checking');
                    if (failed) {
                        show(failed);
                        return;
                    }
                    try {
                        form.requestSubmit(submitter && submitter.form === form ? submitter : undefined);
                    } catch (e) {
                        form.requestSubmit();
                    }
                });
            });
        }, true);
    }

    /** Bring an editor into view, opening the tab it is on */
    function show(field) {
        var container = field.editor.getContainer(), $tab = $(container).closest('div.tab_content');
        if ($tab.length && !$tab.is(':visible') && $tab.attr('id')) {
            $('#tab_' + $tab.attr('id')).trigger('click');
        }
        if (field.note && field.note.scrollIntoView) {
            field.note.scrollIntoView({block: 'center'});
        }
    }

    //=====[ Start ]========================================

    $(function () {
        var holder = document.getElementById('cc_editor_data'), boxes = document.querySelectorAll('textarea.cc-editor'), forms = [], i, field;
        if (!holder || !boxes.length || !window.tinymce || !window.ccEditorUnits) {
            return; // the text boxes still work as text boxes
        }
        try {
            data = JSON.parse(holder.textContent);
        } catch (e) {
            return;
        }
        for (i = 0; i < boxes.length; i++) {
            field = new Field(boxes[i]);
            fields.push(field);
            if (field.form && forms.indexOf(field.form) === -1) {
                forms.push(field.form);
                guard(field.form);
            }
            field.start();
        }
    });

    // For tests, and for other scripts on the page
    window.ccEditor = {
        fields: function () {
            return fields;
        },
        /** The editor on a text box, or null */
        of: function (textarea) {
            for (var i = 0; i < fields.length; i++) {
                if (fields[i].textarea === textarea) {
                    return fields[i];
                }
            }
            return null;
        },
        /** The content of a text box as it would be stored, whether or not an editor is on it */
        get: function (textarea) {
            var field = this.of(textarea);
            return field ? field.source() : textarea.value;
        },
        /** Replace the content of a text box, whether or not an editor is on it */
        set: function (textarea, source) {
            var field = this.of(textarea);
            if (field) {
                field.put(source);
            } else {
                textarea.value = source;
            }
        }
    };
})(jQuery);
