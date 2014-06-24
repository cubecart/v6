<div id="backup" class="tab_content">
  <h3>{$LANG.maintain.title_files_backup}</h3>
  <form action="?_g=maintenance&node=index&files_backup=1#backup" method="post">
	<p>{$LANG.maintain.files_backup_desc}</p>
	<div>
		<input type="submit" name="backup" class="delete" title="{$LANG.notification.confirm_continue}" value="{$LANG.maintain.tab_backup}">
		<input type="hidden" name="token" value="{$SESSION_TOKEN}">
	</div>
  </form>
  <br>
  <h3>{$LANG.maintain.title_db_backup}</h3>
  <p>{$LANG.maintain.db_backup_desc}</p> 
  <form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<fieldset><legend>{$LANG.maintain.backup_options}</legend>
	  <div>
		<label for="db_drop">{$LANG.maintain.db_drop_table}</label>
		<span><input type="hidden" name="drop" id="drop" class="toggle" value="1"></span>
	  </div>
	  <div>
		<label for="db_struct">{$LANG.maintain.db_structure}</label>
		<span><input type="hidden" name="structure" id="structure" class="toggle" value="1"></span>
	  </div>
	  <div>
		<label for="db_data">{$LANG.maintain.db_data}</label>
		<span><input type="hidden" name="data" id="data" class="toggle" value="1"></span>
	  </div>
	  <div>
		<label for="db_data">{$LANG.common.compress_file}</label>
		<span><input type="hidden" name="compress" id="compress" class="toggle" value="1"></span>
	  </div>
	</fieldset>
	<div>
		<input type="hidden" name="previous-tab" id="previous-tab" value="backup">
		<input type="submit" name="backup" value="{$LANG.maintain.tab_backup}">
	</div>
	<input type="hidden" name="token" value="{$SESSION_TOKEN}">
  </form>
  <br>
  <h3>{$LANG.maintain.title_existing_backups}</h3>
  <fieldset><legend>{$LANG.common.downloads}</legend>
	{if $EXISTING_BACKUPS}
	<div class="list">
		{foreach from=$EXISTING_BACKUPS item=backup}
		<div>
		  <label for="{$backup.filename}" class="wide"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/{$backup.type}.png" alt="{$LANG.common.download}"></a> <a href="{$backup.download_link}">{$backup.filename}</a> - {$backup.size}</label>
		  <span class="actions">
		    {if $backup.restore_link}
		    <a href="{$backup.restore_link}" class="delete" title="{$backup.warning}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/restore.png" alt="{$LANG.common.restore}"></a>
		    {/if}
		    <a href="{$backup.download_link}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/download.png" alt="{$LANG.common.download}"></a>
		    <a href="{$backup.delete_link}" class="delete" title="{$LANG.notification.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a>
		  </span>
		</div>
		{/foreach}
	</div>
	{else}
	<div class="center">{$LANG.filemanager.file_none}</div>
	{/if}
	</fieldset>
	{if $RESTORE_ERROR_LOG}
	<h3>{$LANG.dashboard.title_error_log}</h3>
  	<div><textarea rows="10" cols="70">{$RESTORE_ERROR_LOG}</textarea></div>
  	<a href="?_g=maintenance&node=index&delete=restore_error_log#backup" class="delete">{$LANG.maintain.delete_error_log}</a>
    {/if}
</div>

<div id="upgrade" class="tab_content">
  <h3>{$LANG.maintain.upgrade_to_latest}</h3>
  {if $OUT_OF_DATE}
  <p><strong>{$OUT_OF_DATE}</strong></p>
  <p>{$LANG.maintain.upgrade_to_latest_desc}</p>
  {else}
  <p>{$LANG.maintain.latest_installed}</p>
  {/if}
  <form action="?_g=maintenance&upgrade={$LATEST_VERSION}#upgrade" method="post">
    <div>
		<input type="submit" name="backup" class="submit_confirm" title="{$LANG.notification.confirm_continue}" value="{$UPGRADE_NOW}">
		<input type="hidden" name="token" value="{$SESSION_TOKEN}">
		<input type="hidden" name="force" value="{$FORCE}">
	</div>
  </form>
  {if $UPGRADE_ERROR_LOG}
  <h3>{$LANG.dashboard.title_error_log}</h3>
  <div><textarea rows="10" cols="70">{$UPGRADE_ERROR_LOG}</textarea></div>
  <a href="?_g=maintenance&node=index&delete=upgrade_error_log#upgrade" class="delete">{$LANG.maintain.delete_error_log}</a>
  {/if}
</div>


<div id="rebuild" class="tab_content">
  <h3>{$LANG.maintain.title_rebuild}</h3>
  <form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <fieldset><legend>{$LANG.maintain.title_rebuild_catalogue}</legend>
	<div style="height: 20px;"><label for="prodViews">{$LANG.maintain.reset_views}</label><span><input type="checkbox" id="prodViews" name="prodViews" value="1"></span></div>
  </fieldset>
  <fieldset><legend>{$LANG.maintain.title_rebuild_cache}</legend>
	<div style="height: 20px;"><label for="clearCache">{$LANG.maintain.cache_clear}</label><span><input type="checkbox" id="clearCache" name="clearCache" value="1"></span><!--{$LANG.maintain.cache_warning}--></div>
	<div style="height: 20px;"><label for="clearSQLCache">{$LANG.maintain.cache_sql}</label><span><input type="checkbox" id="clearSQLCache" name="clearSQLCache" value="1"></span></div>
	<div style="height: 20px;"><label for="clearLangCache">{$LANG.maintain.cache_language}</label><span><input type="checkbox" id="clearLangCache" name="clearLangCache" value="1"></span></div>
	<div style="height: 20px;"><label for="clearImageCache">{$LANG.maintain.cache_image}</label><span><input type="checkbox" id="clearImageCache" name="clearImageCache" value="1"></span></div>
  </fieldset>

  <fieldset><legend>{$LANG.maintain.title_rebuild_logs}</legend>
	<div style="height: 20px;"><label for="clearLogs">{$LANG.maintain.logs_admin}</label><span><input type="checkbox" id="clearLogs" name="clearLogs" value="1"></span></div>
	<div style="height: 20px;"><label for="emptyErrorLogs">{$LANG.maintain.logs_error}</label><span><input type="checkbox" id="emptyErrorLogs" name="emptyErrorLogs" value="1"></span></div>
	<div style="height: 20px;"><label for="emptyRequestLogs">{$LANG.maintain.logs_request}</label><span><input type="checkbox" id="emptyRequestLogs" name="emptyRequestLogs" value="1"></span></div>
	<div style="height: 20px;"><label for="emptyTransLogs">{$LANG.maintain.logs_transaction}</label><span><input type="checkbox" id="emptyTransLogs" name="emptyTransLogs" value="1"></span></div>
  </fieldset>
  <fieldset><legend>{$LANG.maintain.title_rebuild_misc}</legend>
	<div style="height: 20px;"><label for="sitemap">{$LANG.maintain.sitemap}</label><span><input type="checkbox" id="sitemap" name="sitemap" value="1"></span></div>
  </fieldset>
	<div>
		<input type="hidden" name="previous-tab" id="previous-tab" value="rebuild">
		<input type="submit" name="rebuild" value="{$LANG.common.submit}">
	</div>
	<input type="hidden" name="token" value="{$SESSION_TOKEN}">
  </form>
</div>
<div id="database" class="tab_content">
  <h3>{$LANG.maintain.title_db}</h3>
  <form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <fieldset><legend>{$LANG.maintain.title_db_tables}</legend>
	  {if $TABLES}
	  <table width="650">
	  	<thead>
	  	  <tr>
	  	    <td width="10">&nbsp;</td>
	  	    <td>{$LANG.maintain.table_name}</td>
	  	    <td>{$LANG.maintain.table_records}</td>
	  	    <td>{$LANG.maintain.table_engine}</td>
	  	    <td>{$LANG.maintain.table_collation}</td>
	  	    <td>{$LANG.maintain.table_size}</td>
	  	    <td>{$LANG.maintain.table_overhead}</td>
	  	  </tr>
	  	</thead>
	  	<tbody class="list">
	  	  {foreach from=$TABLES item=table}
	  	  <tr>
	  		<td><input type="checkbox" id="{$table.Name}" name="tablename[]" value="{$table.Name}" class="table"></td>
	  		<td><label for="{$table.Name}">{$table.Name_Display}</label></td>
	  		<td>{$table.Rows}</td>
	  		<td>{$table.Engine}</td>
	  		<td>{$table.Collation}</td>
	  		<td>{$table.Data_length}</td>
	  		<td>{$table.Data_free}</td>
	  	  </tr>
	  	  {/foreach}
	  	</tbody>
	  	<tfoot>
	  	  <tr>
	  		<td><span><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/select_all.gif" alt=""></td>
	  		<td><a href="#" class="check-all" rel="table">{$LANG.form.check_uncheck}</a></td>
	  	  </tr>
	  	  <tr>
	  		<td>&nbsp;</td>
	  		<td><strong>{$LANG.maintain.db_with_selected}</strong>
	  		  <select name="action" class="textbox">
	    	    <optgroup label="">
	      	      <option value="">{$LANG.form.please_select}</option>
		  		  <option value="OPTIMIZE">{$LANG.settings.optimize}</option>
	      		  <option value="REPAIR">{$LANG.settings.repair}</option>
	      		  <option value="CHECK">{$LANG.settings.check}</option>
	      		  <option value="ANALYZE">{$LANG.settings.analyze}</option>
	    	    </optgroup>
			  </select>
			</td>
	  	  </tr>
	  	</tfoot>
	  </table>
	  {elseif $TABLES_AFTER}
	  <table width="650">
  		<thead>
  		  <tr>
  			<td>{$LANG.maintain.table_name}</td>
  			<td>{$LANG.maintain.table_operation}</td>
  			<td>{$LANG.maintain.table_message_type}</td>
  			<td>{$LANG.maintain.table_message_text}</td>
  		  </tr>
  		</thead>
  		<tbody class="list">
	  	{foreach from=$TABLES_AFTER item=table}
	  		<tr>
	  		{foreach from=$table key=k item=v}
	  		  <td>{$v}</td>
	  		{/foreach}
	  		</tr>
	  	{/foreach}
	  	</tbody>
	  </table>
	  {/if}
  </fieldset>
  <div>
  	<input type="hidden" name="previous-tab" id="previous-tab" value="database">
  	<input type="submit" name="database" value="{$LANG.common.submit}">
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
  </form>
</div>