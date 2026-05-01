/* ---------------------------------------------------------------------------
 * admin.components.js
 *
 * Page-component scripts that used to live inline in template files. All
 * handlers are delegated against `document`, so they are inert on pages where
 * the matching elements don't exist. Loading on every admin page is cheap.
 *
 * Sections (one IIFE each):
 *   - settings.errorlog.php — system error log modal + bulk-action mirror
 *   - settings.requestlog.php — outbound request log modal w/ JSON/HTTP/XML
 *                                 syntax highlighting and copy buttons
 * ------------------------------------------------------------------------- */

/* ---------- settings.errorlog.php ---------- */
(function(){
  // Open the colorbox detail modal when an admin clicks the message cell.
  $(document).on('click', '.errorlog-row .errorlog-msg', function() {
    var $row = $(this).closest('.errorlog-row');
    var msg = $row.attr('data-message') || '';
    var url = $row.attr('data-url') || '';
    var backtrace = $row.attr('data-backtrace') || '';
    var severity = $row.attr('data-severity') || '';
    var time = $row.attr('data-time') || '';
    var first = $row.attr('data-first') || '';
    var occurrences = parseInt($row.attr('data-occurrences') || '1', 10);

    var html = '<div class="errorlog-detail">';
    html += '<button type="button" class="copy-btn tiny" data-copy="error">Copy</button>';
    html += '<h4>' + severity.toUpperCase() + ' &middot; ' + (occurrences > 1 ? (occurrences + ' occurrences') : '1 occurrence') + '</h4>';
    html += '<p><strong>Last seen:</strong> ' + time + (occurrences > 1 ? ' &middot; <strong>First seen:</strong> ' + first : '') + '</p>';
    html += '<h4>Message</h4><pre id="errorlog-detail-msg">' + msg + '</pre>';
    if (url) {
      html += '<h4>URL</h4><p><a href="' + url + '" target="_blank" rel="noopener">' + url + '</a></p>';
    }
    if (backtrace) {
      html += '<h4>Backtrace</h4><pre id="errorlog-detail-backtrace">' + backtrace + '</pre>';
    }
    html += '</div>';
    $.colorbox({ title: 'Error detail', width: '85%', height: '80%', html: html });
  });

  // Copy the visible detail (message + backtrace) to the clipboard.
  $(document).on('click', '.errorlog-detail .copy-btn', function() {
    var msg = ($('#errorlog-detail-msg').text() || '');
    var bt  = ($('#errorlog-detail-backtrace').text() || '');
    var text = msg + (bt ? ('\n\n' + bt) : '');
    if (navigator.clipboard) navigator.clipboard.writeText(text);
  });

  // The bulk-action select carries either status=1/0 or delete; mirror the
  // status into the appropriate hidden field so the server gets a clean value.
  $(document).on('change', '.errorlog-row select[name=bulk_action], form select[name=bulk_action]', function() {
    var $sel = $(this);
    var $opt = $sel.find('option:selected');
    var $form = $sel.closest('form');
    var status = $opt.attr('data-status');
    if (typeof status !== 'undefined') {
      $form.find('.bulk-status-mirror').val(status);
    }
  });
})();

/* ---------- settings.requestlog.php ---------- */
(function(){
  function escapeHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }
  // Pretty-print JSON / XML where possible. jQuery's $.data() may have already
  // parsed JSON-shaped values into objects, so handle that too.
  function prettyMaybe(text) {
    if (text === '' || text === null || typeof text === 'undefined') return { text:'', kind:'' };
    if (typeof text === 'object') {
      try { return { text: JSON.stringify(text, null, 2), kind:'json' }; }
      catch(e) { return { text: String(text), kind:'' }; }
    }
    if (typeof text !== 'string') text = String(text);
    var t = text.replace(/^\s+|\s+$/g, '');
    if ((t.charAt(0) === '{' && t.slice(-1) === '}') || (t.charAt(0) === '[' && t.slice(-1) === ']')) {
      try { return { text: JSON.stringify(JSON.parse(t), null, 2), kind:'json' }; } catch(e) {}
    }
    if (t.charAt(0) === '<' && window.DOMParser) {
      try {
        var doc = (new DOMParser()).parseFromString(t, 'text/xml');
        if (!doc.querySelector('parsererror')) {
          return { text: new XMLSerializer().serializeToString(doc).replace(/></g, '>\n<'), kind:'xml' };
        }
      } catch(e) {}
    }
    if (/^HTTP\/[0-9.]+\s+\d{3}/m.test(t) || /^[A-Za-z0-9-]+:\s/m.test(t)) {
      return { text: text, kind:'headers' };
    }
    return { text: text, kind:'' };
  }
  // Tiny syntax highlighters — operate on already-escaped HTML text.
  function highlightJson(escaped) {
    return escaped.replace(
      /("(?:\\.|[^"\\])*")(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d+)?(?:[eE][+\-]?\d+)?/g,
      function(m, str, colon, kw) {
        if (str)  { return colon ? '<span class="jsx-key">'+str+'</span>'+colon : '<span class="jsx-str">'+str+'</span>'; }
        if (kw)   { return '<span class="jsx-kw">'+kw+'</span>'; }
        return '<span class="jsx-num">'+m+'</span>';
      }
    );
  }
  function highlightHeaders(escaped) {
    return escaped.replace(/^(HTTP\/[0-9.]+)\s+(\d{3})(.*)$/gm, '<span class="jsx-tag">$1</span> <span class="jsx-status">$2</span>$3')
                  .replace(/^([A-Za-z][A-Za-z0-9-]*):/gm, '<span class="jsx-hdr">$1</span>:');
  }
  function highlightXml(escaped) {
    return escaped.replace(/(&lt;\/?)([A-Za-z][A-Za-z0-9:_-]*)/g, '$1<span class="jsx-tag">$2</span>')
                  .replace(/([A-Za-z-]+)=(&quot;[^&]*?&quot;)/g, '<span class="jsx-attr">$1</span>=<span class="jsx-str">$2</span>');
  }
  function copyBtn(target) {
    return '<button type="button" class="button tiny copy-btn" data-copy-target="'+target+'">Copy</button>';
  }
  function section(label, target, raw, modifier) {
    if (raw === '' || raw === null || typeof raw === 'undefined') return '';
    var p = prettyMaybe(raw);
    var escaped = escapeHtml(p.text);
    if (p.kind === 'json')         escaped = highlightJson(escaped);
    else if (p.kind === 'headers') escaped = highlightHeaders(escaped);
    else if (p.kind === 'xml')     escaped = highlightXml(escaped);
    return '<div class="reqlog-section'+(modifier?' '+modifier:'')+'">'
         +    '<div class="reqlog-section__label"><span>'+label+'</span>'+copyBtn(target)+'</div>'
         +    '<pre id="reqlog-'+target+'">'+escaped+'</pre>'
         + '</div>';
  }
  function copyText(text) {
    // Prefer the async API; fall back to execCommand for non-secure contexts (http://).
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(text).then(function(){ return true; }, function(){ return false; });
    }
    try {
      var ta = document.createElement('textarea');
      ta.value = text; ta.setAttribute('readonly','');
      ta.style.position = 'fixed'; ta.style.top = 0; ta.style.left = 0; ta.style.opacity = 0;
      document.body.appendChild(ta); ta.select();
      var ok = document.execCommand('copy');
      document.body.removeChild(ta);
      return Promise.resolve(ok);
    } catch(e) { return Promise.resolve(false); }
  }
  $(document).on('click', '.reqlog-row .reqlog-url', function() {
    var $row = $(this).closest('.reqlog-row');
    var d = $row.data();
    var statusBadge = '<span class="reqlog-status reqlog-status--'+(d.bucket?d.bucket+'xx':'unknown')+'">'+(d.status||'—')+'</span>';
    var statusDesc  = d.statusDesc ? '<span class="reqlog-detail__desc">'+escapeHtml(String(d.statusDesc))+'</span>' : '';

    var html  = '<div class="reqlog-detail">';
    html += '<div class="reqlog-detail__head">'+statusBadge+statusDesc+'</div>';
    html += '<div class="reqlog-detail__url">'+escapeHtml(String(d.url||''))+'</div>';
    html += '<div class="reqlog-detail__meta">';
    html += '<span><strong>Last seen:</strong> '+escapeHtml(String(d.time||''))+'</span>';
    if (d.occurrences > 1) {
      html += '<span>&middot;</span><span><strong>First seen:</strong> '+escapeHtml(String(d.first||''))+'</span>';
      html += '<span>&middot;</span><span class="reqlog-occurrences">'+d.occurrences+' &times;</span>';
    }
    html += '</div>';

    var hasReq = (d.requestHeaders || d.request);
    var hasRes = (d.responseHeaders || d.result);
    if (hasReq || hasRes) {
      html += '<div class="reqlog-detail__cols">';
      html += '<div class="reqlog-detail__col reqlog-detail__col--req"><h5>Request</h5>';
      html += section('Headers', 'reqhead', d.requestHeaders);
      html += section('Body',    'req',     d.request);
      if (!d.requestHeaders && !d.request) html += '<p style="color:var(--text-muted);font-size:12px;">&mdash;</p>';
      html += '</div>';
      html += '<div class="reqlog-detail__col reqlog-detail__col--res"><h5>Response</h5>';
      html += section('Headers', 'reshead', d.responseHeaders);
      html += section('Body',    'res',     d.result);
      if (!d.responseHeaders && !d.result) html += '<p style="color:var(--text-muted);font-size:12px;">&mdash;</p>';
      html += '</div>';
      html += '</div>';
    }
    if (d.error) html += section('Error', 'err', d.error, 'reqlog-section--error');
    html += '</div>';

    $.colorbox({ title: 'Request detail', width: '90%', height: '85%', html: html });
  });
  $(document).on('click', '.reqlog-detail .copy-btn', function(e) {
    e.preventDefault();
    var $btn = $(this);
    var t = $btn.attr('data-copy-target');
    var el = document.getElementById('reqlog-' + t);
    if (!el) return;
    var orig = $btn.text();
    copyText(el.textContent || '').then(function(ok){
      $btn.text(ok ? 'Copied' : 'Failed');
      setTimeout(function(){ $btn.text(orig); }, 1200);
    });
  });
})();
