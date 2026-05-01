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
 <form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	{if isset($mode_list)}
	<div id="filemanager" class="tab_content">
		<h3>
		{if $FILMANAGER_MODE == FileManager::FM_FILETYPE_IMG}<span class="toggle"><span class="list"><i class="fa fa-list" aria-hidden="true"></i></span><span class="small"></span><span class="medium"></span><span class="large"></span></span>{/if}
		{$FILMANAGER_TITLE}</h3>
		<div>
			<div class="fm-sort">
				{$LANG.form.sort_by}
				<select name="fm-sort" onchange="this.form.submit()" class="textbox">
					<option value="filename-asc"{if !isset($FM_SORT) || $FM_SORT=='filename-asc'} selected="selected"{/if}>{$LANG.common.name} (A-Z)</option>
					<option value="filename-desc"{if isset($FM_SORT) && $FM_SORT=='filename-desc'} selected="selected"{/if}>{$LANG.common.name} (Z-A)</option>
					<option value="filesize-asc"{if isset($FM_SORT) && $FM_SORT=='filesize-asc'} selected="selected"{/if}>{$LANG.common.size} ({$LANG.category.sort_low_high})</option>
					<option value="filesize-desc"{if isset($FM_SORT) && $FM_SORT=='filesize-desc'} selected="selected"{/if}>{$LANG.common.size} ({$LANG.category.sort_high_low})</option>
					<option value="date_added-asc"{if isset($FM_SORT) && $FM_SORT=='date_added-asc'} selected="selected"{/if}>{$LANG.category.sort_date} ({$LANG.category.sort_date_added_desc})</option>
					<option value="date_added-desc"{if isset($FM_SORT) && $FM_SORT=='date_added-desc'} selected="selected"{/if}>{$LANG.category.sort_date} ({$LANG.category.sort_date_added_asc})</option>
				</select>
			</div>
			{if $FILMANAGER_MODE == FileManager::FM_FILETYPE_IMG}<input type="text" name="fm-search-term" id="fm-search-term" placeholder="{$LANG.common.search}..."><button type="button" class="button tiny" id="fm-search-button" data-mode="{if $FILMANAGER_MODE == FileManager::FM_FILETYPE_IMG}images{else}digital{/if}" data-action="show">{$LANG.common.go}</button>
			{if isset($FM_UNUSED_FILTER)}<a href="?_g=filemanager{if isset($smarty.get.subdir)}&subdir={$smarty.get.subdir|escape:'url'}{/if}" class="button tiny"><i class="fa fa-times" aria-hidden="true"></i> {$LANG.filemanager.show_all|default:'Show all'}</a>{else}<a href="?_g=filemanager&fm-unused=1{if isset($smarty.get.subdir)}&subdir={$smarty.get.subdir|escape:'url'}{/if}" class="button tiny"><i class="fa fa-search-minus" aria-hidden="true"></i> {$LANG.filemanager.show_orphaned|default:'Show orphaned'}</a>{/if}
		{/if}
		</div>

		{if $FILMANAGER_MODE == FileManager::FM_FILETYPE_DL && !$SELECT_BUTTON}
		<p>{$LANG.filemanager.public}</p>
		{/if}
		{if $SELECT_BUTTON}
		<p>{$LANG.filemanager.how_to_select}</p>
		{/if}
		{if $FILMANAGER_MODE == FileManager::FM_FILETYPE_DL}
		<div id="fm-wrapper" class="digital">
			{if isset($FOLDER_BREADCRUMB)}
			<nav class="fm-breadcrumb">
				{foreach from=$FOLDER_BREADCRUMB item=crumb name=crumbs}
					{if !$smarty.foreach.crumbs.last}<a href="{$crumb.link}" class="fm_location">{if $smarty.foreach.crumbs.first}<i class="fa fa-folder-open" aria-hidden="true"></i>{else}{$crumb.name}{/if}</a><span class="fm-breadcrumb__sep">&rsaquo;</span>{else}<span class="fm-breadcrumb__current">{$crumb.name}</span>{/if}
				{/foreach}
			</nav>
			{/if}
			{if isset($FOLDERS) || isset($FILES)}
			<table class="fm-digital-table">
				<thead>
					<tr>
						<th width="12">&nbsp;</th>
						<th width="12">&nbsp;</th>
						<th>{$LANG.common.name}</th>
						<th width="100">{$LANG.common.size}</th>
						<th width="80" class="text-right">{$LANG.form.action}</th>
					</tr>
				</thead>
				<tbody>
				{if isset($FOLDERS)}
				{foreach from=$FOLDERS item=folder}
					<tr class="fm-row fm-row--folder">
						<td>{if NOT is_null($folder.delete)}<input type="checkbox" value="{$folder.value}" class="multi_delete" name="multi_delete[]">{/if}</td>
						<td><i class="fa fa-folder" aria-hidden="true"></i></td>
						<td><a href="{$folder.link}" class="item_link">{$folder.name}</a></td>
						<td>&mdash;</td>
						<td class="text-right">{if NOT is_null($folder.delete)}<a href="{$folder.delete}" class="delete" title="{sprintf($LANG.notification.confirm_delete_folder,$folder.name)}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>{/if}</td>
					</tr>
				{/foreach}
				{/if}
				{if isset($FILES)}
				{foreach from=$FILES item=file}
					<tr class="fm-row fm-row--file" id="{$file.file_name_hash}">
						<td><input type="checkbox" value="{$file.value}" class="multi_delete" name="multi_delete[]"></td>
						<td><i class="fa fa-{$file.icon}" aria-hidden="true" title="{$file.mimetype}"></i></td>
						<td><a class="item_link" href="{if $file.class}{$file.filepath}?{$file.random}{else}?_g=filemanager&download_file={base64_encode($file.filepath)}{/if}" title="{$file.description}" target="_self">{$file.filename}</a></td>
						<td>{$file.filesize}</td>
						<td class="text-right">
							{if $file.select_button}
							<a href="{$file.master_filepath}" class="select"><i class="fa fa-plus-circle" title="{$LANG.common.add}"></i></a>
							{else}
							<a href="{$file.edit}" class="edit" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
							<a href="{$file.delete}" class="delete" title="{sprintf($LANG.notification.confirm_delete_file,$file.filename)}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
							{/if}
						</td>
					</tr>
				{/foreach}
				{/if}
				</tbody>
				<tfoot>
					<tr class="fm-bulk-actions">
						<td colspan="5">
							<div class="fm-bulk-row">
								<a href="#" class="check-all" rel="multi_delete"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/select_all.gif" alt=""> {$LANG.form.check_uncheck}</a>
								{if isset($DIRS)}
								<span class="fm-bulk-move">
									<select name="move_to_folder" class="textbox">
										<option value="">{$LANG.filemanager.file_subfolder}&hellip;</option>
										{foreach from=$DIRS item=dir}<option value="{$dir.path}">{$dir.path}</option>{/foreach}
									</select>
									<input type="submit" name="bulk_move" value="{$LANG.filemanager.move_selected|default:'Move selected'}">
								</span>
								{/if}
								<input type="submit" name="bulk_delete" class="delete submit_confirm" title="{$LANG.notification.confirm_delete}" value="{$LANG.common.delete_selected}">
								<input type="hidden" id="ckfuncnum" value="{$CK_FUNC_NUM}">
							</div>
						</td>
					</tr>
				</tfoot>
			</table>
			{else}
			<p class="center clear">{$LANG.filemanager.file_none}</p>
			{/if}
		</div>
		{else}
		<div id="fm-wrapper" class="images" style="overflow:hidden;">
			{if isset($FOLDER_BREADCRUMB)}
			<nav class="fm-breadcrumb">
				{foreach from=$FOLDER_BREADCRUMB item=crumb name=crumbs}
					{if !$smarty.foreach.crumbs.last}<a href="{$crumb.link}" class="fm_location">{if $smarty.foreach.crumbs.first}<i class="fa fa-folder-open" aria-hidden="true"></i>{else}{$crumb.name}{/if}</a><span class="fm-breadcrumb__sep">&rsaquo;</span>{else}<span class="fm-breadcrumb__current">{$crumb.name}</span>{/if}
				{/foreach}
			</nav>
			{/if}
			{if isset($FM_UNUSED_FILTER)}
			<div class="fm-unused-disclaimer">
				<strong>&#9888; {$LANG.common.warning|default:'Warning'}:</strong>
				{$LANG.filemanager.unused_disclaimer|default:'Best-effort only — some images may still be in use. Review each before deleting.'}
			</div>
			{/if}
			{if $FM_SIZE eq 'fm-item-list' && (isset($FOLDERS) || isset($FILES))}
			<table class="fm-digital-table">
				<thead>
					<tr>
						<th width="12">&nbsp;</th>
						<th width="12">&nbsp;</th>
						<th>{$LANG.common.name}</th>
						<th width="100">{$LANG.common.size}</th>
						<th width="80" class="text-right">{$LANG.form.action}</th>
					</tr>
				</thead>
				<tbody>
				{if isset($FOLDERS)}
				{foreach from=$FOLDERS item=folder}
					<tr class="fm-row fm-row--folder">
						<td>{if NOT is_null($folder.delete)}<input type="checkbox" value="{$folder.value}" class="multi_delete" name="multi_delete[]">{/if}</td>
						<td><i class="fa fa-folder" aria-hidden="true"></i></td>
						<td><a href="{$folder.link}" class="item_link">{$folder.name}</a></td>
						<td>&mdash;</td>
						<td class="text-right">{if NOT is_null($folder.delete)}<a href="{$folder.delete}" class="delete" title="{sprintf($LANG.notification.confirm_delete_folder,$folder.name)}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>{/if}</td>
					</tr>
				{/foreach}
				{/if}
				{if isset($FILES)}
				{foreach from=$FILES item=file}
					<tr class="fm-row fm-row--file" id="{$file.file_name_hash}">
						<td><input type="checkbox" value="{$file.value}" class="multi_delete" name="multi_delete[]"></td>
						<td><a href="{$file.master_filepath}?{$file.random}" class="{$file.class}" title="{$file.filename} &middot; {$file.filesize}{if $file.dimensions} &middot; {$file.dimensions}{/if}" target="_self"><i class="fa fa-{$file.icon}" aria-hidden="true"></i></a></td>
						<td><a class="item_link" href="{$file.master_filepath}?{$file.random}" class="{$file.class}" title="{$file.filename} &middot; {$file.filesize}{if $file.dimensions} &middot; {$file.dimensions}{/if}" target="_self">{$file.filename}</a></td>
						<td>{$file.filesize}</td>
						<td class="text-right">
							{if $file.select_button}
							<a href="{$file.master_filepath}" class="select{if $SOURCE=='options'} options{/if}" rel="{$file.file_id}"><i class="fa fa-plus-circle" title="{$LANG.common.add}"></i></a>
							{else}
							<a href="{$file.edit}" class="edit" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
							<a href="{$file.delete}" class="delete" title="{sprintf($LANG.notification.confirm_delete_file,$file.filename)}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
							{/if}
						</td>
					</tr>
				{/foreach}
				{/if}
				</tbody>
			</table>
			{else}
			{if isset($FOLDERS)}
			{foreach from=$FOLDERS item=folder}
			<div class="fm-item folder {$FM_SIZE}">
				<a href="{$folder.link}" class="thumbnail fm_folder fm_location item_link"><img src="{$SKIN_VARS.admin_folder}/skins/default/images/folder_large.svg" alt="" /></a>
				<span class="actions">
				{if NOT is_null($folder.delete)}
				<input type="checkbox" value="{$folder.value}" class="multi_delete" name="multi_delete[]">
				<a href="{$folder.delete}" class="delete right" title="{sprintf($LANG.notification.confirm_delete_folder,$folder.name)}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
				{/if}
				</span>
				<a href="{$folder.link}" class="item_link">{$folder.name}</a>
			</div>
			{/foreach}
			{/if}
			{if isset($FILES)}
			{foreach from=$FILES item=file}
			<div class="fm-item {$FM_SIZE}{if $file.file_name_hash==$HILIGHTED_FILE} hilighted{/if}" id="{$file.file_name_hash}">
				<a href="{$file.master_filepath}?{$file.random}" class="{$file.class} thumbnail" title="{$file.filename} &middot; {$file.filesize}{if $file.dimensions} &middot; {$file.dimensions}{/if}" target="_self">
					<img class="lazyload item_link" data-src="{$file.filepath}" src="data:image/gif;base64,R0lGODdhAQABAPAAAMPDwwAAACwAAAAAAQABAAACAkQBADs=">
				</a>
				<span class="actions">
					<input type="checkbox" value="{$file.value}" class="multi_delete right" name="multi_delete[]">
					{if $file.select_button}
					<a href="{$file.master_filepath}" class="select{if $SOURCE=='options'} options{/if}" rel="{$file.file_id}"><i class="fa fa-plus-circle" title="{$LANG.common.add}"></i></a>
					{else}
					<a href="{$file.delete}" class="delete right" title="{sprintf($LANG.notification.confirm_delete_file,$file.filename)}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
					<a href="{$file.edit}" class="edit right" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
					{/if}
				</span>
				<a class="item_link" href="{if $file.class}{$file.filepath}?{$file.random}{else}?_g=filemanager&download_file={base64_encode($file.filepath)}{/if}" title="{$file.description}" target="_self">{$file.filename} <span class="list-filesize">({$file.filesize})</span></a>
			</div>
			{/foreach}
			{else}
			<p class="center clear">{$LANG.filemanager.file_none}</p>
			{/if}
			{/if}
		</div>
		{if isset($FILES) || isset($FOLDERS)}
		<div class="form_control fm-bulk-actions">
			<div class="fm-bulk-row">
				<a href="#" class="check-all" rel="multi_delete"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/select_all.gif" alt=""> {$LANG.form.check_uncheck}</a>
				{if isset($FM_STATS_COUNT) && $FM_STATS_COUNT > 0}<span class="fm-stats">{number_format($FM_STATS_COUNT)} {$LANG.common.files|default:'files'} &middot; {$FM_STATS_SIZE}</span>{/if}
				{if isset($DIRS)}
				<span class="fm-bulk-move">
					<select name="move_to_folder" class="textbox">
						<option value="">{$LANG.filemanager.file_subfolder}&hellip;</option>
						{foreach from=$DIRS item=dir}<option value="{$dir.path}">{$dir.path}</option>{/foreach}
					</select>
					<input type="submit" name="bulk_move" value="{$LANG.filemanager.move_selected|default:'Move selected'}">
				</span>
				{/if}
				<input type="submit" name="bulk_delete" class="delete submit_confirm" title="{$LANG.notification.confirm_delete}" value="{$LANG.common.delete_selected}">
				<input type="hidden" id="ckfuncnum" value="{$CK_FUNC_NUM}">
			</div>
		</div>
		{/if}
		{/if}
	</div>
	<div id="upload" class="tab_content">
		<h3>{$FILMANAGER_TITLE}</h3>
		<div class="dropzone">
			<div class="dz-default dz-message"><span>{$LANG.filemanager.file_upload_note}</span></div>
		</div>
		<div id="dropzone_url" style="display: none;">{$VAL_SELF}</div>
		<p>{$UPLOAD_LIMIT_DESC}</p>
	</div>
	<div id="folder" class="tab_content">
		<h3>{$FILMANAGER_TITLE}</h3>
		<fieldset>
			<legend>{$LANG.filemanager.folder_create}</legend>
			<div><label for="create-dir">{$LANG.common.name}</label><span><input name="fm[create-dir]" id="create-dir" type="text" class="textbox"></span></div>
		</fieldset>
		<div class="form_control">
			<input type="submit" value="{$LANG.common.save}">
			<input type="hidden" id="ckfuncnum" value="{$CK_FUNC_NUM}">
		</div>
	</div>
		{if isset($PLUGIN_TABS)}
	{foreach from=$PLUGIN_TABS item=tab}
	{$tab}
	{/foreach}
	{/if}
{include file='templates/element.hook_form_content.php'}
	{/if}
	{if isset($mode_form)}
	<div id="fm-details" class="tab_content">
		<h3>{$LANG.filemanager.title_file_edit}</h3>
		{if $FILE.type == 1}
		<a href="{$FILE.filepath}{$FILE.filename}?_={$smarty.now}" target="_blank" title="{$LANG.filemanager.opens_new_window}"><img src="{$FILE.filepath}{$FILE.filename}"{if !empty($FILE.alt)} alt="{$FILE.alt}"{/if} style="max-height:200px;" /></a>
		<div>{$LANG.common.size}: {$FILE.width}px x {$FILE.height}px</div>
		{/if}
		<fieldset>
			<div>
				<label for="filename">{$LANG.filemanager.file_name}</label>
				<span><input type="text" id="filename" name="details[filename]" class="textbox" value="{$FILE.filename}"></span>
			</div>
			<div>
				<label for="move">{$LANG.filemanager.file_subfolder}</label>
				<span>
					<select name="details[move]" id="move" class="textbox">
						<option value="">{$LANG.form.please_select}</option>
						{if isset($DIRS)}{foreach from=$DIRS item=dir}<option value="{$dir.path}"{$dir.selected}>{$dir.path}</option>{/foreach}{/if}
					</select>
				</span>
			</div>
			<div>
				<label for="replacement">{$LANG.filemanager.replace_file|default:'Replace file'}</label>
				<span>
					<input type="file" name="replacement" id="replacement">
					<br><small>{$LANG.filemanager.replace_file_desc|default:'Upload a new version with the same extension. Existing product/category links to this file are preserved.'}</small>
				</span>
			</div>
			{if $FILE.type == 1}
			<div>
				<label for="alt">{$LANG.filemanager.alt}</label>
				<span>
				<input type="text" id="alt" name="details[alt]" class="textbox" value="{$FILE.alt}">
				</span>
			</div>
			{/if}
			<div>
				<label for="title">{$LANG.filemanager.title}</label>
				<span>
				<input type="text" id="title" name="details[title]" class="textbox" value="{$FILE.title}">
				</span>
			</div>
			{if $STREAMABLE}
			<div>
				<label for="description">{$LANG.common.description}</label>
				<span>
				<textarea name="details[description]" id="description" class="textbox">{$FILE.description}</textarea>
				</span>
			</div>
			<div>
				<label for="stream">{$LANG.filemanager.stream}</label>
				<span>
				<input type="hidden" name="details[stream]" id="stream" value="{$FILE.stream}" class="toggle">
				</span>
			</div>
			{/if}
		</fieldset>
	</div>
	{if isset($SHOW_CROP)}
	<div id="fm-cropper" class="tab_content">
		<h3>{$LANG.filemanager.title_image_crop}</h3>
		<img id="resize" src="{$FILE.filepath}{$FILE.filename}?{$FILE.random}" alt="" class="cropper">
		<div class="dimensions hidden center"><span class="width">150</span> x <span class="height">150</span> px</div>
	</div>
	{/if}
		{if isset($PLUGIN_TABS)}
	{foreach from=$PLUGIN_TABS item=tab}
	{$tab}
	{/foreach}
	{/if}
{include file='templates/element.hook_form_content.php'}
	<div class="form_control">
		<input type="hidden" name="file_id" value="{$FILE.file_id}">
		<input type="hidden" name="previous-tab" id="previous-tab" value="">
		<input type="submit" value="{$LANG.common.save}">
		<input type="submit" name="cancel" value="{$LANG.common.cancel}">
	</div>
	{/if}
</form>