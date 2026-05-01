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
  .errorlog-badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; }
  .errorlog-badge--red    { background: rgba(200, 60, 60, 0.15);  color: #b03030; }
  .errorlog-badge--orange { background: rgba(220, 130, 30, 0.18); color: #b56a13; }
  .errorlog-badge--yellow { background: rgba(220, 190, 30, 0.20); color: #856900; }
  .errorlog-badge--gray   { background: rgba(120, 130, 150, 0.15); color: #5a6478; }
  .errorlog-occurrences { display: inline-block; min-width: 22px; padding: 1px 6px; background: var(--panel-muted); border-radius: 10px; font-size: 11px; font-weight: 600; text-align: center; }
  .errorlog-row td.errorlog-msg { cursor: pointer; }
  .errorlog-row td.errorlog-msg:hover { background: var(--panel-muted); }
  .errorlog-filter { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin: 8px 0 12px; padding: 8px 12px; background: var(--highlight); border-radius: 4px; }
  .errorlog-filter input.q { width: 280px; }
  .errorlog-detail { padding: 8px 4px; }
  .errorlog-detail h4 { margin: 12px 0 4px; }
  .errorlog-detail pre { white-space: pre-wrap; word-break: break-word; background: var(--panel-muted); padding: 10px; border-radius: 4px; font-size: 12px; max-height: 60vh; overflow: auto; }
  .errorlog-detail .copy-btn { float: right; }
</style>

<div id="admin_error_log" class="tab_content">
  <h3>{$LANG.settings.title_error_log}</h3>
  {if $ADMIN_ERROR_LOG}
  <div class="right"><a href="?_g=maintenance&clearLogs=true&redir=viewlog" class="button delete" title="{$LANG.notification.confirm_continue}">{$LANG.maintain.clear_log}</a></div>
  {/if}
  <form action="{$VAL_SELF}#admin_error_log" method="post" class="errorlog-filter ignore-dirty">
    <input type="hidden" name="filter_scope" value="admin">
    <input type="text" name="q" class="textbox q" placeholder="{$LANG.common.search}" value="{$ADMIN_FILTER.q|escape:'html'}">
    <select name="severity" class="textbox">
      {foreach from=$SEVERITY_OPTIONS item=opt}
      <option value="{$opt.value}"{if $opt.value eq $ADMIN_FILTER.severity} selected="selected"{/if}>{$opt.label}</option>
      {/foreach}
    </select>
    <input type="submit" value="{$LANG.common.go}" class="tiny">
    {if $ADMIN_FILTER.q || $ADMIN_FILTER.severity}<a href="?_g=settings&node=errorlog&reset_filter=admin">{$LANG.common.reset}</a>{/if}
  </form>
  {if isset($ADMIN_ERROR_LOG)}
  <form action="{$VAL_SELF}#admin_error_log" method="post" enctype="multipart/form-data">
    <table>
      <thead>
        <tr>
          <td>&nbsp;</td>
          <td width="110">{$LANG.common.severity}</td>
          <td width="150">{$LANG.common.date}</td>
          <td>{$LANG.common.message}</td>
          <td width="40" class="text-center">{$LANG.common.count}</td>
        </tr>
      </thead>
      <tbody>
      {foreach from=$ADMIN_ERROR_LOG item=log}
        <tr class="errorlog-row" data-log-id="{$log.log_id}" data-message="{$log.message|escape:'html'}" data-severity="{$log.severity}" data-time="{$log.time}" data-first="{$log.first_time}" data-occurrences="{$log.occurrences}">
          <td><input type="checkbox" name="adminread[]" value="{$log.log_id}" class="error"></td>
          <td><span class="errorlog-badge errorlog-badge--{$log.severity_class}">{$log.severity}</span></td>
          <td {$log.style}>{$log.time}</td>
          <td {$log.style} class="errorlog-msg">{$log.message|truncate:160|escape}</td>
          <td class="text-center"><span class="errorlog-occurrences">{$log.occurrences}</span></td>
        </tr>
      {/foreach}
      </tbody>
      <tfoot>
        <tr>
          <td><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/select_all.gif" alt=""></td>
          <td colspan="3"><a href="#" class="check-all" rel="error">{$LANG.form.check_uncheck}</a> &middot;
            {$LANG.orders.with_selected}:
            <select name="bulk_action" class="textbox">
              <option value="status" data-status="1">{$LANG.form.mark_read}</option>
              <option value="status" data-status="0">{$LANG.form.mark_unread}</option>
              <option value="delete">{$LANG.common.delete}</option>
            </select>
            <input type="hidden" name="admin_error_status" value="1" class="bulk-status-mirror">
            <input type="submit" value="{$LANG.common.go}" name="go" class="tiny">
          </td>
          <td>&nbsp;</td>
        </tr>
      </tfoot>
    </table>
  </form>
  <div class="pagination">{$PAGINATION_ADMIN_ERROR_LOG}</div>
  {else}
  <p>{$LANG.form.none}</p>
  {/if}
</div>

<div id="system_error_log" class="tab_content">
  {if $SYSTEM_ERROR_LOG}
  <div class="right"><a href="?_g=maintenance&emptyErrorLogs=true&redir=viewlog" class="delete button" title="{$LANG.notification.confirm_continue}">{$LANG.maintain.clear_log}</a></div>
  {/if}
  <h3>{$LANG.settings.title_system_error_log}</h3>
  <p>{$LANG.settings.error_general_desc}</p>
  <form action="{$VAL_SELF}#system_error_log" method="post" class="errorlog-filter ignore-dirty">
    <input type="hidden" name="filter_scope" value="system">
    <input type="text" name="q" class="textbox q" placeholder="{$LANG.common.search}" value="{$SYSTEM_FILTER.q|escape:'html'}">
    <select name="severity" class="textbox">
      {foreach from=$SEVERITY_OPTIONS item=opt}
      <option value="{$opt.value}"{if $opt.value eq $SYSTEM_FILTER.severity} selected="selected"{/if}>{$opt.label}</option>
      {/foreach}
    </select>
    <input type="submit" value="{$LANG.common.go}" class="tiny">
    {if $SYSTEM_FILTER.q || $SYSTEM_FILTER.severity}<a href="?_g=settings&node=errorlog&reset_filter=system">{$LANG.common.reset}</a>{/if}
  </form>
  {if isset($SYSTEM_ERROR_LOG)}
  <form action="{$VAL_SELF}#system_error_log" method="post" enctype="multipart/form-data">
    <table>
      <thead>
        <tr>
          <td>&nbsp;</td>
          <td width="110">{$LANG.common.severity}</td>
          <td width="150">{$LANG.common.date}</td>
          <td>{$LANG.common.message}</td>
          <td width="40" class="text-center">{$LANG.common.count}</td>
        </tr>
      </thead>
      <tbody>
      {foreach from=$SYSTEM_ERROR_LOG item=syslog}
        <tr class="errorlog-row" data-log-id="{$syslog.log_id}" data-message="{$syslog.message|escape:'html'}" data-url="{$syslog.url|escape:'html'}" data-backtrace="{$syslog.backtrace|escape:'html'}" data-severity="{$syslog.severity}" data-time="{$syslog.time}" data-first="{$syslog.first_time}" data-occurrences="{$syslog.occurrences}">
          <td><input type="checkbox" name="systemread[]" value="{$syslog.log_id}" class="systemerror"></td>
          <td><span class="errorlog-badge errorlog-badge--{$syslog.severity_class}">{$syslog.severity}</span></td>
          <td {$syslog.style}>{$syslog.time}</td>
          <td {$syslog.style} class="errorlog-msg">{$syslog.message|truncate:160|escape}</td>
          <td class="text-center"><span class="errorlog-occurrences">{$syslog.occurrences}</span></td>
        </tr>
      {/foreach}
      </tbody>
      <tfoot>
        <tr>
          <td><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/select_all.gif" alt=""></td>
          <td colspan="3"><a href="#" class="check-all" rel="systemerror">{$LANG.form.check_uncheck}</a> &middot;
            {$LANG.orders.with_selected}:
            <select name="bulk_action" class="textbox">
              <option value="status" data-status="1">{$LANG.form.mark_read}</option>
              <option value="status" data-status="0">{$LANG.form.mark_unread}</option>
              <option value="delete">{$LANG.common.delete}</option>
            </select>
            <input type="hidden" name="system_error_status" value="1" class="bulk-status-mirror">
            <input type="submit" value="{$LANG.common.go}" name="go" class="tiny">
          </td>
          <td>&nbsp;</td>
        </tr>
      </tfoot>
    </table>
  </form>
  <div class="pagination">{$PAGINATION_SYSTEM_ERROR_LOG}</div>
  {else}
  <p>&mdash; {$LANG.form.none} &mdash;</p>
  {/if}
</div>

<script>{literal}
(function(){
  // Open the colorbox detail modal when an admin clicks the message cell.
  $(document).on('click', '.errorlog-row .errorlog-msg', function(e) {
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
{/literal}</script>
