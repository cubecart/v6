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
  <p>[<a href="?_g=maintenance&emptyRequestLogs=true&redir=viewlog">{$LANG.maintain.logs_request}</a>]</p>
  {/if}
  <form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	  <table>
		<thead>
		  <tr>
			<td nowrap="nowrap">{$LANG.common.date}</td>
			<td>&nbsp;</td>
		  </tr>
		</thead>
		<tbody>
		{foreach from=$REQUEST_LOG item=log}
		  <tr {if $log.error}class="request_error"{/if}>
			<td valign="top" nowrap="nowrap">{$log.time}</td>
			<td>
			<div class="request">
			  <strong>{$LANG.common.request} {if $log.is_curl==='1'}(cURL){elseif $log.is_curl==='0'}(fsock){/if} - {$log.request_url}</strong>
			  {$log.request}
			</div>
			{if $log.error && !is_bool($log.error)}
			<div class="request">
			  <strong>Error:</strong>
			  {$log.error}
			</div>
			{/if}
			<div class="received">
			  <strong>{$LANG.common.received}{if !empty($log.response_code)} ({$log.response_code} - {$log.response_code_description}){/if}</strong>
			  {$log.result}
			</div>
			</td>
		  </tr>
		{foreachelse}
		  <tr>
			<td colspan="3" align="center" width="650"><strong>{$LANG.form.none}</strong></td>
		  </tr>
		{/foreach}
		</tbody>
	  </table>
  
  </form>
  <div>{$PAGINATION_REQUEST_LOG}</div>
</div>