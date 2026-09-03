/**
 * CubeCart admin image picker — folder-browser + drag-reorder selection.
 *
 * Auto-initialises every `<div data-cc-image-picker>` on the page. Each instance
 * is independent and reads its config from data-* attributes:
 *
 *   data-cc-image-picker         (presence = init me)
 *   data-cc-single="1"           single-select mode (no reorder; click replaces)
 *   data-cc-storage-key="cat_42" localStorage key suffix for path memory
 *   data-cc-initial-json='[...]' optional inline JSON of pre-selected items
 *                                (else read from window[data-cc-initial-global])
 *   data-cc-initial-global="X"   name of a window.X holding the initial array
 *   data-cc-folder-icon="..."    URL of the folder svg (defaults to admin folder)
 *
 * Markup contract (IDs scoped per-instance via the root element):
 *   .img-picker__sel-grid     <ol> for selected items
 *   .img-picker__empty        empty-state placeholder
 *   .img-picker__breadcrumb   <nav> for breadcrumb
 *   .img-picker__grid         folder/image grid
 *   .img-picker__loading      spinner overlay (hidden by default)
 *   .img-picker__newdir-name  text input for new-folder name (optional)
 *   .img-picker__newdir-btn   button to create folder (optional)
 *
 * Hidden inputs `<input name="imageset[FID]" value="N">` are emitted on the
 * selected list — N=1 is main, 2..N is drag order. The existing server-side
 * save handlers in products.index.inc.php and categories.index.inc.php read
 * this format unchanged.
 */
(function(){
    if (typeof window === 'undefined') return;

    function escHtml(s){
        return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c];
        });
    }
    function escAttr(s){ return escHtml(s); }
    function trimPath(p){ return (p || '').replace(/\/+$/, ''); }

    function adminFile(){
        if (typeof ADMIN_FILE !== 'undefined' && ADMIN_FILE) return ADMIN_FILE;
        var el = document.getElementById('val_admin_file');
        return (el && el.textContent) || 'admin.php';
    }

    function buildFolderIcon(rootEl){
        var explicit = rootEl.getAttribute('data-cc-folder-icon');
        if (explicit) return explicit;
        var af = (document.getElementById('val_admin_folder') || {}).innerText || 'admin';
        var sf = (document.getElementById('val_skin_folder')  || {}).innerText || 'default';
        return af + '/skins/' + sf + '/images/folder.svg';
    }

    // View preference is the admin's, not the product's, so it is stored once and
    // shared by every picker. Thumbnails stay the default (#4214).
    var VIEW_KEY = 'cc_img_picker_view';

    function readView(){
        try {
            return localStorage.getItem(VIEW_KEY) === 'list' ? 'list' : 'thumbs';
        } catch (e) {
            return 'thumbs';
        }
    }

    function writeView(mode){
        try { localStorage.setItem(VIEW_KEY, mode === 'list' ? 'list' : 'thumbs'); } catch (e) {}
    }

    function readInitial(rootEl){
        var inline = rootEl.getAttribute('data-cc-initial-json');
        if (inline) {
            try { return JSON.parse(inline); } catch (e) {}
        }
        var globalName = rootEl.getAttribute('data-cc-initial-global');
        if (globalName && Array.isArray(window[globalName])) return window[globalName];
        return [];
    }

    function Picker(rootEl){
        var self = this;
        self.root       = rootEl;
        self.single     = rootEl.getAttribute('data-cc-single') === '1';
        self.pickAndClose = rootEl.getAttribute('data-cc-pick-and-close') === '1';
        // 'images' or 'digital' — drives which CubeCart_filemanager.type is queried
        // and where uploads land on disk.
        self.mode       = rootEl.getAttribute('data-cc-mode') === 'digital' ? 'digital' : 'images';
        // Form input name for the hidden inputs on the selected list. Default
        // 'imageset'; digital pages typically use 'download' to match the
        // existing save handler.
        self.inputName  = rootEl.getAttribute('data-cc-input-name') || 'imageset';
        self.storageKey = 'cc_img_picker_path_' + (rootEl.getAttribute('data-cc-storage-key') || 'default');
        self.langNone   = window.CC_LANG_NONE || 'None';
        self.folderIcon = buildFolderIcon(rootEl);
        self.selected   = readInitial(rootEl).slice();
        self.currentPath = '';

        // Scope queries to the root so multiple instances don't collide.
        var $r = jQuery(rootEl);
        self.$sel     = $r.find('.img-picker__sel-grid');
        self.$bread   = $r.find('.img-picker__breadcrumb');
        self.$grid    = $r.find('.img-picker__grid');
        self.$empty   = $r.find('.img-picker__empty');
        self.$loading = $r.find('.img-picker__loading');
        self.$newdirIn  = $r.find('.img-picker__newdir-name');
        self.$newdirBtn = $r.find('.img-picker__newdir-btn');
        self.$valSubdir = $r.find('.img-picker__val-subdir');
        self.$viewBtns  = $r.find('.img-picker__view-btn');

        self.applyView(readView());

        self.bind();
        self.renderSelected();
        var lsPath = '';
        try { lsPath = localStorage.getItem(self.storageKey) || ''; } catch (e) {}
        self.loadFolder(lsPath);
    }

    Picker.prototype.bind = function(){
        var self = this;

        // Multi-select: jQuery UI sortable for drag reorder. Skipped in single mode.
        if (!self.single && jQuery.fn.sortable) {
            self.$sel.sortable({
                items: '> li',
                placeholder: 'img-picker__sel-placeholder',
                tolerance: 'pointer',
                stop: function(){
                    var ids = self.$sel.children('li').map(function(){
                        return parseInt(this.getAttribute('data-fid'), 10);
                    }).get();
                    self.selected.sort(function(a, b){ return ids.indexOf(a.file_id) - ids.indexOf(b.file_id); });
                    self.renderSelected();
                }
            }).disableSelection();
        }

        self.$sel.on('click', '.img-picker__sel-remove', function(e){
            e.preventDefault();
            var fid = parseInt(jQuery(this).closest('li').attr('data-fid'), 10);
            self.selected = self.selected.filter(function(p){ return p.file_id !== fid; });
            self.renderSelected();
            var $tile = self.$grid.find('.img-picker__tile[data-fid="' + fid + '"]');
            if ($tile.length) $tile.removeClass('is-selected');
        });

        self.$bread.on('click', 'a', function(e){
            e.preventDefault();
            self.loadFolder(jQuery(this).attr('data-path') || '');
        });

        self.$viewBtns.on('click', function(e){
            e.preventDefault();
            var mode = this.getAttribute('data-view');
            writeView(mode);
            // Every picker on the page follows, so the two tabs cannot disagree.
            jQuery('[data-cc-image-picker]').each(function(){
                if (this.ccPicker) this.ccPicker.applyView(mode);
            });
        });

        self.$grid.on('click', '.img-picker__folder', function(e){
            e.preventDefault();
            self.loadFolder(jQuery(this).attr('data-path'));
        });

        self.$grid.on('click', '.img-picker__tile', function(e){
            e.preventDefault();
            var $tile = jQuery(this);
            var fid = parseInt($tile.attr('data-fid'), 10);
            if (!fid) return;
            // Pick-and-close mode: don't update internal selection. Fire an event
            // on the root and let the host (e.g. an option-image dialog) handle
            // write-back + close.
            if (self.pickAndClose) {
                self.root.dispatchEvent(new CustomEvent('cc:image-picked', {
                    bubbles: true,
                    detail: self.tileToItem($tile, fid)
                }));
                return;
            }
            var idx = self.selected.findIndex(function(p){ return p.file_id === fid; });
            if (self.single) {
                if (idx >= 0) {
                    self.selected = [];
                    $tile.removeClass('is-selected');
                } else {
                    self.selected = [self.tileToItem($tile, fid)];
                    self.$grid.find('.img-picker__tile').removeClass('is-selected');
                    $tile.addClass('is-selected');
                }
            } else {
                if (idx >= 0) {
                    self.selected.splice(idx, 1);
                    $tile.removeClass('is-selected');
                } else {
                    self.selected.push(self.tileToItem($tile, fid));
                    $tile.addClass('is-selected');
                }
            }
            self.renderSelected();
        });

        // Create-folder controls (optional; absent from some markups).
        if (self.$newdirBtn.length) {
            self.$newdirBtn[0].addEventListener('click', function(e){
                e.preventDefault(); e.stopPropagation();
                self.createFolder();
            }, true);
        }
        if (self.$newdirIn.length) {
            self.$newdirIn[0].addEventListener('keydown', function(e){
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault(); e.stopPropagation();
                    self.createFolder();
                }
            }, true);
        }

        // Drop-zone refresh hook: admin.js calls window.ccImagePickerOnUploadComplete()
        // when a Dropzone queue empties. We chain into our own handler so the picker
        // refreshes the current folder + clears Dropzone previews. Newly-uploaded
        // file_ids are stashed on the picker root by admin.js's Dropzone success
        // handler; we drain them here and pass to loadFolder so the matching tiles
        // get auto-added to the selected strip.
        var prev = window.ccImagePickerOnUploadComplete;
        window.ccImagePickerOnUploadComplete = function(){
            if (typeof prev === 'function' && prev !== window.ccImagePickerOnUploadComplete) {
                try { prev(); } catch (e) {}
            }
            self.$loading.attr('hidden', 'hidden');
            var pendingIds = (self.root.__ccPendingUploadIds || []).slice();
            self.root.__ccPendingUploadIds = [];
            self.loadFolder(self.currentPath, pendingIds);
            try {
                var dzEl = document.querySelector('div.dropzone');
                if (dzEl && typeof Dropzone !== 'undefined') {
                    var dz = Dropzone.forElement(dzEl);
                    if (dz) dz.removeAllFiles(true);
                }
            } catch (e) {}
        };
    };

    Picker.prototype.tileToItem = function($tile, fid){
        return {
            file_id:  fid,
            filename: $tile.attr('data-filename') || '',
            filepath: $tile.attr('data-filepath') || '',
            thumb:    $tile.attr('data-thumb') || ''
        };
    };

    Picker.prototype.createFolder = function(){
        var self = this;
        var name = (self.$newdirIn.val() || '').trim();
        if (!name) return;
        self.$newdirBtn.prop('disabled', true);
        var af = adminFile();
        jQuery.ajax({ url: './' + af + '?response=token', dataType: 'text', cache: false }).done(function(token){
            token = (token || '').trim();
            jQuery.post('./' + af + '?_g=xml&function=fmCreateFolder', { path: self.currentPath, name: name, token: token, mode: self.mode }, function(res){
                self.$newdirBtn.prop('disabled', false);
                if (res && res.success) {
                    self.$newdirIn.val('');
                    self.loadFolder(self.currentPath);
                } else {
                    alert('Could not create folder' + (res && res.error ? ': ' + res.error : ''));
                }
            }, 'json').fail(function(jq){
                self.$newdirBtn.prop('disabled', false);
                alert('Folder creation failed (status: ' + (jq && jq.status) + ').');
            });
        }).fail(function(jq){
            self.$newdirBtn.prop('disabled', false);
            alert('Could not fetch session token (status: ' + (jq && jq.status) + ').');
        });
    };

    Picker.prototype.loadFolder = function(path, autoSelectIds){
        var self = this;
        self.currentPath = path || '';
        // Sync the upload target dir for Dropzone. admin.js's `processing`
        // handler now reads `.img-picker__val-subdir` scoped within the same
        // .img-picker as the dropzone, so multi-picker pages don't collide.
        if (self.$valSubdir.length) self.$valSubdir.text(trimPath(self.currentPath));
        try { localStorage.setItem(self.storageKey, self.currentPath); } catch (e) {}

        self.$loading.removeAttr('hidden');
        self.$grid.attr('aria-busy', 'true').empty();
        jQuery.getJSON('./' + adminFile(), { _g: 'xml', 'function': 'fmFolder', path: self.currentPath, mode: self.mode }, function(data){
            if (!data) data = { folders: [], images: [] };
            self.renderBreadcrumb(self.currentPath);
            self.renderGrid(data);
            if (autoSelectIds && autoSelectIds.length) {
                self.autoSelect(data.images || [], autoSelectIds);
            }
        }).always(function(){
            self.$loading.attr('hidden', 'hidden');
            self.$grid.attr('aria-busy', 'false');
        });
    };

    // Append the rows for `ids` from the just-rendered folder `images` to the
    // selected strip. Single-select mode replaces with the last id; multi-select
    // appends each unique id in order.
    Picker.prototype.autoSelect = function(images, ids){
        var self = this;
        var byId = {};
        images.forEach(function(img){ byId[img.file_id] = img; });
        var changed = false;
        ids.forEach(function(fid){
            fid = parseInt(fid, 10);
            if (!fid || !byId[fid]) return;
            if (self.selected.findIndex(function(p){ return p.file_id === fid; }) >= 0) return;
            var item = {
                file_id:  fid,
                filename: byId[fid].filename || '',
                filepath: byId[fid].filepath || '',
                thumb:    byId[fid].thumb || ''
            };
            if (self.single) {
                self.selected = [item];
                self.$grid.find('.img-picker__tile').removeClass('is-selected');
            } else {
                self.selected.push(item);
            }
            self.$grid.find('.img-picker__tile[data-fid="' + fid + '"]').addClass('is-selected');
            changed = true;
        });
        if (changed) self.renderSelected();
    };

    Picker.prototype.applyView = function(mode){
        var list = (mode === 'list');
        this.$grid.toggleClass('is-list', list);
        this.$viewBtns.each(function(){
            var active = (this.getAttribute('data-view') === (list ? 'list' : 'thumbs'));
            jQuery(this).toggleClass('is-active', active).attr('aria-pressed', active ? 'true' : 'false');
        });
    };

    Picker.prototype.renderBreadcrumb = function(path){
        var html = '<a href="#" data-path="" class="fm_location"><i class="fa fa-folder-open" aria-hidden="true"></i></a>';
        if (path) {
            var parts = trimPath(path).split('/');
            var acc = '';
            parts.forEach(function(seg){
                if (!seg) return;
                acc += seg + '/';
                html += '<span class="fm-breadcrumb__sep">&rsaquo;</span><a href="#" data-path="' + escAttr(acc) + '">' + escHtml(seg) + '</a>';
            });
        }
        this.$bread.html(html);
    };

    Picker.prototype.renderGrid = function(data){
        var self = this;
        var sel_ids = {};
        self.selected.forEach(function(p){ sel_ids[p.file_id] = true; });
        var html = '';
        (data.folders || []).forEach(function(name){
            html += '<div class="img-picker__folder" data-path="' + escAttr(self.currentPath + name + '/') + '" title="' + escAttr(name) + '">'
                 +     '<img src="' + self.folderIcon + '" class="img-picker__folder-icon" alt="">'
                 +     '<span>' + escHtml(name) + '</span>'
                 +  '</div>';
        });
        (data.images || []).forEach(function(img){
            var cls = sel_ids[img.file_id] ? ' is-selected' : '';
            // Image files: render the thumbnail. Digital files (no thumb URL):
            // render a generic file icon.
            var preview = img.thumb
                ? '<img src="' + escAttr(img.thumb) + '" alt="" loading="lazy" decoding="async">'
                : '<i class="fa fa-file-o img-picker__tile-fileicon" aria-hidden="true"></i>';
            html += '<div class="img-picker__tile' + cls + '"'
                 +     ' data-fid="' + img.file_id + '"'
                 +     ' data-filename="' + escAttr(img.filename) + '"'
                 +     ' data-filepath="' + escAttr(img.filepath) + '"'
                 +     ' data-thumb="' + escAttr(img.thumb) + '"'
                 +     ' title="' + escAttr(img.filename) + '">'
                 +     preview
                 +     '<span class="img-picker__tile-name">' + escHtml(img.filename) + '</span>'
                 +     '<span class="img-picker__tile-check"><i class="fa fa-check"></i></span>'
                 +  '</div>';
        });
        if (!html) {
            html = '<p class="img-picker__none">&mdash; ' + escHtml(self.langNone) + ' &mdash;</p>';
        }
        self.$grid.html(html);
    };

    Picker.prototype.renderSelected = function(){
        var self = this;
        if (!self.selected.length) {
            self.$sel.empty().hide();
            self.$empty.show();
            return;
        }
        self.$empty.hide();
        self.$sel.show();
        var html = '';
        self.selected.forEach(function(p, i){
            var pos = i + 1;
            var preview = p.thumb
                ? '<img src="' + escAttr(p.thumb) + '" alt="" loading="lazy" decoding="async">'
                : '<i class="fa fa-file-o img-picker__sel-fileicon" aria-hidden="true"></i>';
            html += '<li class="img-picker__sel" data-fid="' + p.file_id + '">'
                 +     '<input type="hidden" name="' + escAttr(self.inputName) + '[' + p.file_id + ']" value="' + pos + '">'
                 +     ((pos === 1 && !self.single) ? '<span class="img-picker__main-badge"><i class="fa fa-star"></i></span>' : '')
                 +     (self.single ? '' : '<span class="img-picker__sel-pos">' + pos + '</span>')
                 +     preview
                 +     '<span class="img-picker__sel-name">' + escHtml(p.filename) + '</span>'
                 +     '<a href="#" class="img-picker__sel-remove" title="Remove"><i class="fa fa-times"></i></a>'
                 +  '</li>';
        });
        self.$sel.html(html);
    };

    function autoInit(){
        if (typeof jQuery === 'undefined') { setTimeout(autoInit, 50); return; }
        var roots = document.querySelectorAll('[data-cc-image-picker]');
        for (var i = 0; i < roots.length; i++) {
            if (roots[i].__ccImagePickerInited) continue;
            roots[i].__ccImagePickerInited = true;
            roots[i].ccPicker = new Picker(roots[i]);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoInit);
    } else {
        autoInit();
    }

    // Public API: build a picker root + spawn an instance on demand. Used by
    // dialog-mode callers (e.g. the per-option image picker) that inject markup
    // after page load.
    window.CCImagePicker = {
        Picker: Picker,
        init: autoInit,
        /**
         * Build the picker markup as an HTML string. Caller is responsible for
         * injecting it into a container and then calling CCImagePicker.init()
         * (or new CCImagePicker.Picker(rootEl) directly).
         *
         * Options:
         *   single          bool   - single-select mode
         *   pickAndClose    bool   - on tile click, fire cc:image-picked event
         *   storageKey      string - localStorage suffix for path memory
         *   placeholder     string - URL of the empty-state placeholder image
         *   uploadNote      string - text inside the dropzone (optional)
         *   newDirLabel     string - placeholder for the new-folder input
         *   showDropzone    bool   - render the dropzone (default true)
         */
        renderHtml: function(opts){
            opts = opts || {};
            function attr(s){ return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]; }); }
            var html = '<div class="img-picker"'
                     + ' data-cc-image-picker'
                     + (opts.single ? ' data-cc-single="1"' : '')
                     + (opts.pickAndClose ? ' data-cc-pick-and-close="1"' : '')
                     + ' data-cc-storage-key="' + attr(opts.storageKey || 'dialog') + '"'
                     + ' data-cc-initial-json="[]">';
            html += '<div class="img-picker__top">'
                  + '<div class="img-picker__selected">'
                  +   '<ol class="img-picker__sel-grid"></ol>'
                  +   '<div class="img-picker__empty"><img src="' + attr(opts.placeholder || '') + '" alt=""></div>'
                  + '</div>';
            if (opts.showDropzone !== false) {
                html += '<div class="dropzone img-picker__upload">'
                      +   '<div class="dz-default dz-message"><span>' + attr(opts.uploadNote || '') + '</span></div>'
                      + '</div>';
            }
            html += '</div>';
            html += '<div class="img-picker__browser">'
                  +   '<div class="img-picker__bar">'
                  +     '<nav class="img-picker__breadcrumb fm-breadcrumb"></nav>'
                  +     '<div class="img-picker__newdir">'
                  +       '<input type="text" class="img-picker__newdir-name textbox" placeholder="' + attr(opts.newDirLabel || 'New folder') + '" autocomplete="off">'
                  +       '<button type="button" class="img-picker__newdir-btn button tiny"><i class="fa fa-plus"></i></button>'
                  +     '</div>'
                  +   '</div>'
                  +   '<div class="img-picker__panel">'
                  +     '<div class="img-picker__grid" aria-busy="false"></div>'
                  +     '<div class="img-picker__loading" hidden><i class="fa fa-spinner fa-spin"></i></div>'
                  +   '</div>'
                  + '</div>';
            html += '<div class="img-picker__val-subdir" hidden></div>';
            html += '</div>';
            return html;
        }
    };
})();
