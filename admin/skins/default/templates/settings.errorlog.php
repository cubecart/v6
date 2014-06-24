<div id="admin_error_log" class="tab_content">
  <h3>{$LANG.settings.title_error_log}</h3>
  <p>[<a href="?_g=maintenance&clearLogs=true">{$LANG.maintain.logs_error}</a>]</p>
  <form action="{$VAL_SELF}#admin_error_log" method="post" enctype="multipart/form-data">
	  <table class="list">
		<thead>
		  <tr>
			<td>&nbsp;</td>
			<td width="150">{$LANG.common.date}</td>
			<td>{$LANG.common.message}</td>
		  </tr>
		</thead>
		<tbody>
		{foreach from=$ADMIN_ERROR_LOG item=log}
		  <tr>
			<td><input type="checkbox" name="adminread[]" value="{$log.log_id}" class="error"></td>
			<td {$log.style}>{$log.time}</td>
			<td {$log.style}>{$log.message}</td>
		  </tr>
		{foreachelse}
		  <tr>
			<td colspan="3" align="center" width="650"><strong>{$LANG.form.none}</strong></td>
		  </tr>
		{/foreach}
		</tbody>
		{if isset($ADMIN_ERROR_LOG)}
		  <tfoot>
			<tr>
			  <td><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/select_all.gif" alt=""></td>
			  <td><a href="#" class="check-all" rel="error">{$LANG.form.check_uncheck}</a></td>
			  <td>
			  {$LANG.orders.with_selected}:
				<select name="admin_error_status" class="textbox">
				  <option value="1">{$LANG.form.mark_read}</option>
				  <option value="0">{$LANG.form.mark_unread}</option>
			    </select>
				<input type="submit" value="{$LANG.common.go}" name="go">
			  </td>
			</tr>
		  </tfoot>
		  {/if}
	  </table>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
  </form>
  <div>{$PAGINATION_ADMIN_ERROR_LOG}</div>
</div>
<div id="system_error_log" class="tab_content">
  <h3>{$LANG.settings.title_system_error_log}</h3>

  <p>[<a href="?_g=maintenance&emptyErrorLogs=true">{$LANG.maintain.logs_error}</a>]</p>
  
  <form action="{$VAL_SELF}#system_error_log" method="post" enctype="multipart/form-data">
	  <table class="list">
		<thead>
		  <tr>
			<td>&nbsp;</td>
			<td width="150">{$LANG.common.date}</td>
			<td>{$LANG.common.message}</td>
		  </tr>
		</thead>
		<tbody>
		{foreach from=$SYSTEM_ERROR_LOG item=syslog}
		  <tr>
			<td><input type="checkbox" name="systemread[]" value="{$syslog.log_id}" class="systemerror"></td>
			<td {$syslog.style}>{$syslog.time}</td>
			<td {$syslog.style}>{$syslog.message}</td>
		  </tr>
		{foreachelse}
		  <tr>
			<td colspan="3" align="center" width="650"><strong>{$LANG.form.none}</strong></td>
		  </tr>
		{/foreach}
		</tbody>
		{if isset($SYSTEM_ERROR_LOG)}
		  <tfoot>
			<tr>
			  <td><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/select_all.gif" alt=""></td>
			  <td><a href="#" class="check-all" rel="systemerror">{$LANG.form.check_uncheck}</a></td>
			  <td>
			  {$LANG.orders.with_selected}:
				<select name="system_error_status" class="textbox">
				  <option value="1">{$LANG.form.mark_read}</option>
				  <option value="0">{$LANG.form.mark_unread}</option>
			    </select>
				<input type="submit" value="{$LANG.common.go}" name="go">
			  </td>
			</tr>
		  </tfoot>
		  {/if}
	  </table>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
  </form>
  <div>{$PAGINATION_SYSTEM_ERROR_LOG}</div>
</div>