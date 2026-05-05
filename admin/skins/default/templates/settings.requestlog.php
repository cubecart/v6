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
   </form>
   {if $REQUEST_LOG}
   <table>
      <thead>
         <tr>
            <th class="text-center">{$LANG.maintain.response_code}</th>
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
