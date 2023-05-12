{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<div id="request_log" class="tab_content">
  <h3>{$LANG.navigation.nav_request_log}</h3>
  {if $REQUEST_LOG}
  <p class="right"><a href="?_g=maintenance&emptyRequestLogs=true&redir=viewlog" class="button">{$LANG.maintain.logs_request}</a></p>
  {/if}
  
  {if $REQUEST_LOG}
	{foreach from=$REQUEST_LOG item=log}
	<table class="request{if $log.error && !is_bool($log.error)} error{/if}" width="100%">
		<tr>
			<td width="125">{$LANG.maintain.request_time}</td><td>{$log.time}</td>
		</tr>
		<tr>
			<td width="125">{$LANG.maintain.request_url}</td><td>{$log.request_url}</td>
		</tr>
		<tr>
			<td width="125">{$LANG.maintain.request_headers}</td><td>{$log.request_headers}</td>
		</tr>
		<tr>
			<td width="125">{$LANG.maintain.request_body}</td><td>{$log.request}</td>
		</tr>
		<tr>
			<td width="125">{$LANG.maintain.response_code}</td><td>{if !empty($log.response_code)} {$log.response_code} - {$log.response_code_description}{/if}</td>
		</tr>
		<tr>
			<td width="125">{$LANG.maintain.response_headers}</td><td>{$log.response_headers}</td>
		</tr>
		<tr>
			<td width="125">{$LANG.maintain.response_body}</td><td>{$log.result}</td>
		</tr>
		{if $log.error && !is_bool($log.error)}
		<tr>
			<td width="125">{$LANG.common.error}</td><td>{$log.error}</td>
		</tr>
		{/if}
	</table>
	{/foreach}
	<div class="pagination">
		<span><strong>{$LANG.common.total}:</strong> {$TOTAL_RESULTS}</span>{$PAGINATION_REQUEST_LOG}
	</div>
  {else}
    <p><strong>{$LANG.form.none}</strong></p>
  {/if}  
</div>