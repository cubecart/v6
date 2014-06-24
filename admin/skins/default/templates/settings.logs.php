{if isset($ADMIN_LOGS)}
<div id="logs_admin" class="tab_content">
  <h3>{$LANG.settings.title_logs_access_admin}</h3>
  <table class="list">
	<thead>
	  <tr>
		<td nowrap="nowrap">{$THEAD_ADMIN.username}</td>
		<td nowrap="nowrap">{$THEAD_ADMIN.date}</td>
		<td nowrap="nowrap">{$THEAD_ADMIN.ip_address}</td>
		<td nowrap="nowrap">{$THEAD_ADMIN.success}</td>
	  </tr>
	</thead>
	<tbody>
	{foreach from=$ADMIN_LOGS item=log}
	  <tr>
		<td>{$log.username}</td>
		<td>{$log.date}</td>
		<td><a href="http://api.hostip.info/get_html.php?ip={$log.ip_address}&position=true" class="colorbox hostip">{$log.ip_address}</a></td>
		<td align="center"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/{$log.img}.png" alt="{$log.success}"></td>
	  </tr>
	{/foreach}
	</tbody>
  </table>
  <div>{$PAGINATION_ADMIN_ACCESS}</div>
</div>
{/if}

{if isset($ADMIN_ACTIVITY)}
<div id="logs_activity" class="tab_content">
  <h3>{$LANG.settings.title_logs_activity_admin}</h3>
  <table class="list">
	<thead>
	  <tr>
		<td>{$THEAD_ACTIVITY.username}</td>
		<td>{$THEAD_ACTIVITY.description}</td>
		<td>{$THEAD_ACTIVITY.date}</td>
		<td width="100">{$THEAD_ACTIVITY.ip_address}</td>
	  </tr>
	</thead>
	<tbody>
	{foreach from=$ADMIN_ACTIVITY item=log}
	  <tr>
		<td>{$log.admin.username} ({$log.admin.name})</td>
		<td>{$log.description}</td>
		<td align="center">{$log.date}</td>
		<td><a href="http://api.hostip.info/get_html.php?ip={$log.ip_address}&position=true" class="colorbox hostip">{$log.ip_address}</a></td>
	  </tr>
	{/foreach}
	</tbody>
  </table>
  <div>{$PAGINATION_ADMIN_ACTIVITY}</div>
</div>
{/if}

{if isset($CUSTOMER_ACTIVITY)}
<div id="logs_customer" class="tab_content">
  <h3>{$LANG.settings.title_logs_access_customer}</h3>
  <table class="list">
	<thead>
	  <tr>
		<td nowrap="nowrap">{$THEAD_CUSTOMER.username}</td>
		<td nowrap="nowrap">{$THEAD_CUSTOMER.date}</td>
		<td nowrap="nowrap">{$THEAD_CUSTOMER.ip_address}</td>
		<td nowrap="nowrap">{$THEAD_CUSTOMER.success}</td>
	  </tr>
	</thead>
	<tbody>
	  {foreach from=$CUSTOMER_ACTIVITY item=log}
	  <tr>
		<td>{$log.username}</td>
		<td>{$log.date}</td>
		<td><a href="http://api.hostip.info/get_html.php?ip={$log.ip_address}&position=true" class="colorbox hostip">{$log.ip_address}</a></td>
		<td align="center"><img src="images/icons/{$log.img}.png" alt="{$log.success}"></td>
	  </tr>
	  {/foreach}
	</tbody>
  </table>
  <div>{$PAGINATION_CUSTOMER}</div>
</div>
{/if}