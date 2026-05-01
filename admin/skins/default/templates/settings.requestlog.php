{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<style>
   .reqlog-filter { display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin:8px 0 12px; padding:8px 12px; background:var(--highlight); border-radius:4px; }
   .reqlog-filter input.q { width:280px; }
   .reqlog-filter label { display:inline-flex; align-items:center; gap:4px; }
   .reqlog-filter .reqlog-clear { margin-left:auto; }
   .reqlog-status { display:inline-block; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600; letter-spacing:0.4px; min-width:34px; text-align:center; }
   .reqlog-status--2xx { background: rgba(60,160,80,0.15);  color:#2c8a40; }
   .reqlog-status--3xx { background: rgba(60,120,200,0.15); color:#2a5fb0; }
   .reqlog-status--4xx { background: rgba(220,130,30,0.18); color:#b56a13; }
   .reqlog-status--5xx { background: rgba(200,60,60,0.15);  color:#b03030; }
   .reqlog-status--unknown { background: rgba(120,130,150,0.15); color:#5a6478; }
   .reqlog-occurrences { display:inline-block; min-width:22px; padding:1px 6px; background:var(--panel-muted); border-radius:10px; font-size:11px; font-weight:600; text-align:center; }
   .reqlog-row td.reqlog-url { cursor:pointer; word-break:break-all; }
   .reqlog-row td.reqlog-url:hover { background:var(--panel-muted); }
   .reqlog-detail { padding:18px 22px; font-size:13px; }
   .reqlog-detail__head { display:flex; align-items:center; gap:10px; margin-bottom:8px; }
   .reqlog-detail__head .reqlog-status { font-size:13px; padding:4px 12px; min-width:50px; }
   .reqlog-detail__head .reqlog-detail__desc { color:var(--text); font-weight:600; }
   .reqlog-detail__url { font-family:Menlo,Monaco,Consolas,monospace; font-size:12px; padding:8px 10px; background:#f3f4f6; border:1px solid #e1e3e7; border-radius:4px; word-break:break-all; margin-bottom:8px; }
   .reqlog-detail__meta { font-size:12px; color:var(--text-muted); margin-bottom:14px; display:flex; flex-wrap:wrap; gap:10px; align-items:center; }
   .reqlog-detail__meta strong { color:var(--text); font-weight:600; }
   .reqlog-detail__meta .reqlog-occurrences { font-size:12px; }
   .reqlog-detail__cols { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
   @media (max-width: 900px) { .reqlog-detail__cols { grid-template-columns:1fr; } }
   .reqlog-detail__col h5 { margin:0 0 6px; font-size:11px; font-weight:700; letter-spacing:0.6px; text-transform:uppercase; color:var(--text-muted); border-bottom:2px solid; padding-bottom:4px; }
   .reqlog-detail__col--req h5 { border-color:#5b8def; color:#3b6ec4; }
   .reqlog-detail__col--res h5 { border-color:#3da66e; color:#2c8a40; }
   .reqlog-section { margin-bottom:10px; }
   .reqlog-section__label { display:flex; justify-content:space-between; align-items:center; font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.4px; padding:0 2px 4px; }
   .reqlog-section__label .copy-btn { font-size:10px; padding:1px 8px; line-height:1.4; }
   .reqlog-section pre { white-space:pre-wrap; word-break:break-word; background:#1e2329; color:#d4d4d4; padding:10px 12px; border-radius:4px; font-family:Menlo,Monaco,Consolas,monospace; font-size:12px; line-height:1.5; max-height:42vh; overflow:auto; margin:0; border:1px solid #2a2f37; }
   .reqlog-section pre .jsx-key { color:#9cdcfe; }
   .reqlog-section pre .jsx-str { color:#ce9178; }
   .reqlog-section pre .jsx-num { color:#b5cea8; }
   .reqlog-section pre .jsx-kw  { color:#569cd6; }
   .reqlog-section pre .jsx-hdr { color:#dcdcaa; font-weight:600; }
   .reqlog-section pre .jsx-status { color:#4ec9b0; font-weight:600; }
   .reqlog-section pre .jsx-tag { color:#569cd6; }
   .reqlog-section pre .jsx-attr { color:#9cdcfe; }
   .reqlog-section--error { margin-top:14px; }
   .reqlog-section--error .reqlog-section__label { color:#b03030; }
   .reqlog-section--error pre { background:#3a1a1a; border-color:#5a2a2a; color:#f4d4d4; }
   .reqlog-banner { font-size:12px; color:var(--text-muted); margin-bottom:8px; }
</style>
<div id="request_log" class="tab_content">
   <h3>{$LANG.navigation.nav_request_log}</h3>
   {if $REQUEST_LOG_RETENTION_DAYS > 0}
   <div class="reqlog-banner">{sprintf($LANG.maintain.request_log_retention_note, $REQUEST_LOG_RETENTION_DAYS)}</div>
   {/if}
   <form action="{$VAL_SELF}" method="get" class="reqlog-filter ignore-dirty">
      <input type="hidden" name="_g" value="settings">
      <input type="hidden" name="node" value="requestlog">
      <input type="hidden" name="filter_submit" value="1">
      <input type="text" name="q" class="textbox q" placeholder="{$LANG.common.search}..." value="{if isset($SEARCH_QUERY)}{$SEARCH_QUERY}{/if}">
      <select name="status" class="textbox">
         <option value="">{$LANG.maintain.response_code} &mdash; {$LANG.common.all|lower}</option>
         <option value="2"{if $FILTER_STATUS eq '2'} selected="selected"{/if}>2xx Success</option>
         <option value="3"{if $FILTER_STATUS eq '3'} selected="selected"{/if}>3xx Redirect</option>
         <option value="4"{if $FILTER_STATUS eq '4'} selected="selected"{/if}>4xx Client</option>
         <option value="5"{if $FILTER_STATUS eq '5'} selected="selected"{/if}>5xx Server</option>
      </select>
      <label><input type="checkbox" name="errors_only" value="1"{if $FILTER_ERRORS_ONLY} checked="checked"{/if}> {$LANG.common.errors_only}</label>
      <input type="submit" value="{$LANG.common.go}" class="button tiny">
      {if $FILTER_ACTIVE}<a href="?_g=settings&node=requestlog&reset_filter=1">{$LANG.common.reset}</a>{/if}
      {if $REQUEST_LOG}<a href="?_g=maintenance&emptyRequestLogs=true&redir=viewlog" class="button delete tiny reqlog-clear" title="{$LANG.notification.confirm_continue}">{$LANG.maintain.clear_log}</a>{/if}
   </form>
   {if $REQUEST_LOG}
   <table>
      <thead>
         <tr>
            <th width="60" class="text-center">{$LANG.maintain.response_code}</th>
            <th width="160">{$LANG.maintain.request_time}</th>
            <th>{$LANG.maintain.request_url}</th>
            <th width="40" class="text-center">{$LANG.common.count}</th>
         </tr>
      </thead>
      <tbody>
      {foreach from=$REQUEST_LOG item=log}
         <tr class="reqlog-row{if $log.is_error} error{/if}"
             data-id="{$log.request_id}"
             data-url="{$log.request_url|escape:'html'}"
             data-time="{$log.time}"
             data-first="{$log.first_time}"
             data-status="{$log.response_code|escape:'html'}"
             data-status-desc="{$log.response_code_description|escape:'html'}"
             data-bucket="{$log.response_bucket}"
             data-occurrences="{$log.occurrences}"
             data-request-headers="{$log.request_headers|escape:'html'}"
             data-request="{$log.request|escape:'html'}"
             data-response-headers="{$log.response_headers|escape:'html'}"
             data-result="{$log.result|escape:'html'}"
             data-error="{$log.error|escape:'html'}">
            <td class="text-center"><span class="reqlog-status reqlog-status--{if $log.response_bucket}{$log.response_bucket}xx{else}unknown{/if}">{if $log.response_code}{$log.response_code}{else}&mdash;{/if}</span></td>
            <td>{$log.time}</td>
            <td class="reqlog-url">{$log.request_url|escape}</td>
            <td class="text-center"><span class="reqlog-occurrences">{$log.occurrences}</span></td>
         </tr>
      {/foreach}
      </tbody>
   </table>
   {if $PAGINATION_REQUEST_LOG}
   <div class="pagination">
      <span><strong>{$LANG.common.total}:</strong> {number_format($TOTAL_RESULTS)}</span>{$PAGINATION_REQUEST_LOG}
   </div>
   {/if}
   {else}
   <p>{$LANG.form.none}</p>
   {/if}
</div>

<script>{literal}
document.addEventListener('DOMContentLoaded', function(){
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
      // HTTP header blocks tend to start with "HTTP/" or contain "Header-Name: value" lines.
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
      var bucket = d.bucket ? d.bucket : 'unknown';
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
});
{/literal}</script>
