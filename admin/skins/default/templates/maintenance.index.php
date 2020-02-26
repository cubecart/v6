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
<div id="rebuild" class="tab_content">
  <h3>{$LANG.maintain.title_rebuild}</h3>
  <form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <fieldset><legend>{$LANG.maintain.title_rebuild_catalogue}</legend>
	<div><label for="prodViews">{$LANG.maintain.reset_views}</label><span><input type="checkbox" id="prodViews" name="prodViews" value="1"></span></div>
  </fieldset>
  <fieldset><legend>{$LANG.settings.title_seo_urls}</legend>
	<div><label for="truncate_seo_custom">{$LANG.maintain.truncate_seo_custom}</label><span><input type="checkbox" id="truncate_seo_custom" name="truncate_seo_custom" value="1"></span></div>
	<div><label for="truncate_seo_auto">{$LANG.maintain.truncate_seo_auto}</label><span><input type="checkbox" id="truncate_seo_auto" name="truncate_seo_auto" value="1"></span></div>
	<div class="clear important"><strong>{$LANG.maintain.truncate_seo_warning}</strong></div>
  </fieldset>
  <fieldset><legend>{$LANG.maintain.title_rebuild_cache}</legend>
	<div><label for="clearCache">{$LANG.maintain.cache_clear}</label><span><input type="checkbox" id="clearCache" name="clearCache" value="1"></span></div>
	<div><label for="clearSQLCache">{$LANG.maintain.cache_sql}</label><span><input type="checkbox" id="clearSQLCache" name="clearSQLCache" value="1"></span></div>
	<div><label for="clearLangCache">{$LANG.maintain.cache_language}</label><span><input type="checkbox" id="clearLangCache" name="clearLangCache" value="1"></span></div>
	<div><label for="clearImageCache">{$LANG.maintain.cache_image}</label><span><input type="checkbox" id="clearImageCache" name="clearImageCache" value="1"></span></div>
  </fieldset>

  <fieldset><legend>{$LANG.maintain.title_rebuild_logs}</legend>
	<div><label for="clearLogs">{$LANG.maintain.logs_admin}</label><span><input type="checkbox" id="clearLogs" name="clearLogs" value="1"></span></div>
	<div><label for="emptyErrorLogs">{$LANG.maintain.logs_error}</label><span><input type="checkbox" id="emptyErrorLogs" name="emptyErrorLogs" value="1"></span></div>
	<div><label for="emptyEmailLogs">{$LANG.maintain.logs_email}</label><span><input type="checkbox" id="emptyEmailLogs" name="emptyEmailLogs" value="1"></span></div>
	<div><label for="emptyRequestLogs">{$LANG.maintain.logs_request}</label><span><input type="checkbox" id="emptyRequestLogs" name="emptyRequestLogs" value="1"></span></div>
	<div><label for="emptyTransLogs">{$LANG.maintain.logs_transaction}</label><span><input type="checkbox" id="emptyTransLogs" name="emptyTransLogs" value="1"></span></div>
	<div><label for="clearSearch">{$LANG.maintain.clear_search}</label><span><input type="checkbox" id="clearSearch" name="clearSearch" value="1"></span></div>
	<div><label for="clearCookieConsent">{$LANG.maintain.clear_cookie_consent}</label><span><input type="checkbox" id="clearCookieConsent" name="clearCookieConsent" value="1"></span></div>
  </fieldset>
  <fieldset><legend>{$LANG.maintain.title_rebuild_misc}</legend>
	<div><label for="sitemap">{$LANG.maintain.sitemap}</label><span><input type="checkbox" id="sitemap" name="sitemap" value="1"></span></div>
  </fieldset>
	<div>
		<input type="hidden" name="previous-tab" id="previous-tab" value="rebuild">
		<input type="submit" name="rebuild" value="{$LANG.common.submit}">
	</div>
	
  </form>
</div>
<div id="backup" class="tab_content">
  {if !isset($CONFIG.cid)}
  <h3>Automated Backups</h3>
  <p>Looking for automated hourly backups or your files and database? Visit <a href="https://hosted.cubecart.com" target="_blank">https://hosted.cubecart.com</a>.</p>
  {/if}
  <h3>{$LANG.maintain.title_files_backup}</h3>
  <form action="?_g=maintenance&node=index&files_backup=1#backup" method="post">
	<p>{$LANG.maintain.files_backup_desc}</p>
	<fieldset><legend>{$LANG.maintain.backup_options}</legend>
	  <div>
		<label for="skip_images">{$LANG.maintain.skip_images}</label>
		<span><input type="hidden" name="skip_images" id="skip_images" class="toggle" value="0"></span>
	  </div>
	  <div>
		<label for="skip_downloads">{$LANG.maintain.skip_downloads}</label>
		<span><input type="hidden" name="skip_downloads" id="skip_downloads" class="toggle" value="0"></span>
	  </div>
	</fieldset>
	<div>
		<input type="submit" name="backup" value="{$LANG.maintain.tab_backup}">
		
	</div>
  </form>
  <br>
  <h3>{$LANG.maintain.title_db_backup}</h3>
  <p>{$LANG.maintain.db_backup_desc}</p> 
  <form action="{$VAL_SELF}#backup" method="post" enctype="multipart/form-data">
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
	  <div>
		<label for="db_data">{$LANG.maintain.db_3rdparty|replace:'%s':$CONFIG.dbprefix}</label>
		<span><input type="hidden" name="db_3rdparty" id="db_3rdparty" class="toggle" value="0"></span>
	  </div>
	</fieldset>
	<div>
		<input type="hidden" name="previous-tab" id="previous-tab" value="backup">
		<input type="submit" name="backup" value="{$LANG.maintain.tab_backup}">
	</div>
	
  </form>
  <br>
  <h3>{$LANG.maintain.title_existing_backups}</h3>
  <fieldset><legend>{$LANG.common.downloads}</legend>
	{if $EXISTING_BACKUPS}
	{foreach from=$EXISTING_BACKUPS item=backup}
	<div>
	  <label for="{$backup.filename}" class="wide"> <a href="{$backup.download_link}">{$backup.filename}</a> - {$backup.size}</label>
	  <span class="actions">
	    {if $backup.restore_link}
	    <a href="{$backup.restore_link}" class="delete" title="{$backup.warning}"><i class="fa fa-refresh" title="{$LANG.common.restore}"></i></a>
	    {/if}
	    {if $backup.compress}
	    <a href="{$backup.compress}"><i class="fa fa-compress" title="{$LANG.common.compress_file}"></i></a>
	    {/if}
	    <a href="{$backup.download_link}"><i class="fa fa-download" title="{$LANG.common.download}"></i></a>
	    <a href="{$backup.delete_link}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
	  </span>
	</div>
	{/foreach}
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
		
		<input type="hidden" name="force" value="{$FORCE}">
	</div>
  </form>
  {if $UPGRADE_ERROR_LOG}
  <h3>{$LANG.dashboard.title_error_log}</h3>
  <div><textarea rows="10" cols="70">{$UPGRADE_ERROR_LOG}</textarea></div>
  <a href="?_g=maintenance&node=index&delete=upgrade_error_log#upgrade" class="delete">{$LANG.maintain.delete_error_log}</a>
  {/if}
  <br>
  <h3>{$LANG.maintain.upgrade_history}</h3>
  <table>
  	<thead>
  		<tr>
  			<th>{$LANG.dashboard.tech_version_cc}</th>
  			<th>{$LANG.common.date}</th>
  		</tr>
  	</thead>
  	<tbody>
  	{foreach from=$VERSIONS item=version}
  		<tr>
  		  <td>{$version.version}</td>
  		  <td>{$version.time|date_format:"%A, %e %B %Y"}</td>
  		</tr>
  	{/foreach} 
  	</tbody>
  </table>
</div>
<div id="database" class="tab_content">
  <h3>{$LANG.maintain.title_db}</h3>
  <form action="{$VAL_SELF}#database" method="post" enctype="multipart/form-data">
  <fieldset>
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
	  	    <td>{$LANG.maintain.table_indexes}</td>
	  	  </tr>
	  	</thead>
	  	<tbody>
	  	  {foreach from=$TABLES item=table}
	  	  <tr>
	  		<td><input type="checkbox" id="{$table.Name}" name="tablename[]" value="{$table.Name}" class="table"></td>
	  		<td><label for="{$table.Name}">{$table.Name_Display}</label></td>
	  		<td>{$table.Rows}</td>
	  		<td>{$table.Engine}</td>
	  		<td>{$table.Collation}</td>
	  		<td>{$table.Data_length}</td>
	  		<td>{$table.Data_free}</td>
	  		<td align="center">{if $table.errors}
	  			<i class="fa fa-exclamation-triangle" aria-hidden="true" title="{$table.errors}"></i>
	  		{else}
	  			{$LANG.common.ok}
	  		{/if}</td>
	  	  </tr>
	  	  {if $table.errors}
	  	  <tr>
	  	  <td colspan="8" class="row_warn">{$table.errors}</td>
	  	  </tr>
	  	  {/if}
	  	  {/foreach}
	  	</tbody>
	  	<tfoot>
	  	  <tr>
	  		<td><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/select_all.gif" alt=""></td>
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
  		<tbody>
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
  
  </form>
</div>