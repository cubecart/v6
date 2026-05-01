/* ---------------------------------------------------------------------------
 * admin.modal.js
 *
 * Native <dialog> shim for jQuery Colorbox. Re-exposes the same public API
 * ($.colorbox / $.fn.colorbox / $.colorbox.close) so existing call sites and
 * <a class="colorbox*"> markup keep working without changes.
 *
 * Supported colorbox options:
 *   html, href, iframe, inline, photo, width, height, innerHeight,
 *   title, className, overlayClose, scrolling, onComplete, onCleanup, onClosed
 *
 * Loads AFTER plugins.php (which loads colorbox.min.js) and overrides the
 * exported symbols, so the dialog implementation is what actually runs.
 * ------------------------------------------------------------------------- */
(function($){
   if (!$ || !$.fn) return;
   if (typeof HTMLDialogElement === 'undefined') {
      // Fallback: leave colorbox in place if browser is too old to support <dialog>.
      // (All modern browsers since Chrome 37 / Firefox 98 / Safari 15.4 have it.)
      return;
   }

   var IMAGE_RE = /\.(gif|png|jpe?g|bmp|webp|svg|ico)(\?.*)?$/i;
   var activeDialog = null;

   function sizeValue(v) {
      if (v === undefined || v === null || v === false) return null;
      if (typeof v === 'number') return v + 'px';
      var s = String(v);
      return /[%a-z]/i.test(s) ? s : s + 'px';
   }

   function setSize(dlg, opts) {
      var w = sizeValue(opts.width);
      var h = sizeValue(opts.height || opts.innerHeight);
      if (w) dlg.style.width = w;
      if (h) dlg.style.height = h;
      dlg.style.maxWidth = '95vw';
      dlg.style.maxHeight = '95vh';
   }

   function build(opts) {
      var dlg = document.createElement('dialog');
      dlg.className = 'cc-modal' + (opts.className ? ' ' + opts.className : '');

      var header = document.createElement('div');
      header.className = 'cc-modal__header';
      var title = document.createElement('span');
      title.className = 'cc-modal__title';
      title.textContent = opts.title || '';
      var close = document.createElement('button');
      close.type = 'button';
      close.className = 'cc-modal__close';
      close.setAttribute('aria-label', 'Close');
      close.innerHTML = '&times;';
      header.appendChild(title);
      header.appendChild(close);

      var body = document.createElement('div');
      body.className = 'cc-modal__body';
      if (opts.scrolling === false) body.style.overflow = 'hidden';

      dlg.appendChild(header);
      dlg.appendChild(body);
      setSize(dlg, opts);

      return { dialog: dlg, body: body, closeBtn: close };
   }

   function populate(body, opts, done) {
      // Function-form html (used by email previews to defer iframe srcdoc build).
      var html = (typeof opts.html === 'function') ? opts.html() : opts.html;

      if (html !== undefined && html !== null && html !== false) {
         body.innerHTML = String(html);
         done();
         return;
      }

      var href = opts.href;
      if (!href) { done(); return; }

      if (opts.iframe) {
         body.classList.add('cc-modal__body--iframe');
         var iframe = document.createElement('iframe');
         iframe.src = href;
         iframe.frameBorder = 0;
         if (opts.scrolling === false) iframe.scrolling = 'no';
         body.appendChild(iframe);
         done();
         return;
      }

      if (opts.inline) {
         // href is a selector like "#address-form".
         var src = document.querySelector(href);
         if (src) {
            // Move the original into the dialog so any existing event bindings
            // (e.g. form submit handlers attached at page load) still fire.
            // Stash a placeholder so we can restore it on close.
            var placeholder = document.createComment('cc-modal-inline-' + href);
            src.parentNode.insertBefore(placeholder, src);
            body.appendChild(src);
            body.dataset.inlineSrc = href;
            body._inlinePlaceholder = placeholder;
         }
         done();
         return;
      }

      if (opts.photo || IMAGE_RE.test(href)) {
         body.classList.add('cc-modal__body--photo');
         var img = document.createElement('img');
         img.src = href;
         img.alt = opts.title || '';
         img.onload = done;
         img.onerror = done;
         body.appendChild(img);
         return;
      }

      // Default: AJAX-load the href into the body.
      $.ajax({
         url: href,
         dataType: 'html',
         data: opts.data,
         success: function(data){ body.innerHTML = data; done(); },
         error:   function(){ body.innerHTML = '<p style="padding:20px;">Failed to load content.</p>'; done(); }
      });
   }

   function open(opts) {
      closeActive();

      var built = build(opts);
      var dlg = built.dialog;
      var body = built.body;

      var fired = { complete: false, closed: false };
      var trigger = opts.el || dlg;

      // On close: fire onCleanup, restore inline content, remove from DOM, fire onClosed.
      dlg.addEventListener('close', function() {
         if (typeof opts.onCleanup === 'function' && !fired.closed) {
            try { opts.onCleanup.call(trigger); } catch(e){ if (window.console) console.error(e); }
         }
         // Restore inline content back to its original parent.
         if (body._inlinePlaceholder) {
            var moved = body.firstElementChild;
            if (moved && body._inlinePlaceholder.parentNode) {
               body._inlinePlaceholder.parentNode.replaceChild(moved, body._inlinePlaceholder);
            }
         }
         dlg.remove();
         if (activeDialog === dlg) activeDialog = null;
         if (typeof opts.onClosed === 'function' && !fired.closed) {
            try { opts.onClosed.call(trigger); } catch(e){ if (window.console) console.error(e); }
         }
         fired.closed = true;
      });

      built.closeBtn.addEventListener('click', function(){ dlg.close(); });

      // Backdrop click closes (matches colorbox default; opts.overlayClose:false disables).
      if (opts.overlayClose !== false) {
         dlg.addEventListener('click', function(e) {
            // The dialog element itself receives the click when the backdrop
            // (the area outside the dialog's content box) is clicked.
            if (e.target === dlg) dlg.close();
         });
      }

      document.body.appendChild(dlg);
      activeDialog = dlg;
      dlg.showModal();

      populate(body, opts, function() {
         if (typeof opts.onComplete === 'function' && !fired.complete) {
            try { opts.onComplete.call(trigger); } catch(e){ if (window.console) console.error(e); }
            fired.complete = true;
         }
      });
   }

   function closeActive() {
      if (activeDialog) {
         try { activeDialog.close(); } catch(e){}
         activeDialog = null;
      }
   }

   // ---- jQuery API surface (mirrors colorbox 1.6.4) ------------------------
   $.colorbox = function(opts) { open(opts || {}); return $; };
   $.colorbox.close = closeActive;

   // Plugin form binds a click handler on the matched anchors.
   $.fn.colorbox = function(opts) {
      opts = opts || {};
      this.each(function() {
         var el = this;
         $(el).off('click.ccmodal').on('click.ccmodal', function(e) {
            e.preventDefault();
            var localOpts = $.extend({}, opts, { el: el });
            if (!localOpts.href) localOpts.href = el.getAttribute('href') || '';
            if (!localOpts.title) localOpts.title = el.getAttribute('title') || '';
            open(localOpts);
         });
      });
      return this;
   };
   $.fn.colorbox.close = closeActive;

   // Some legacy code paths use $.fn.colorbox.close directly via $.fn — covered above.
   // Older colorbox also exposed $.colorbox.element/.next/.prev/.resize — not used in admin.
})(jQuery);
