{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
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
		{if $FILMANAGER_MODE == FileManager::FM_FILETYPE_IMG}<span class="toggle"><span class="list"><i class="fa fa-list" aria-hidden="true"></i></span><span class="small"></span><span class="medium"></span><span class="large"></span><span class="xlarge"></span></span>{/if}
		{$FILMANAGER_TITLE}</h3>
		<div>
			<div class="fm-sort">
				{$LANG.form.sort_by}
				<select name="fm-sort" onchange="this.form.submit()">
					<option value="filename-asc"{if !isset($FM_SORT) || $FM_SORT=='filename-asc'} selected="selected"{/if}>{$LANG.common.name} (A-Z)</option>
					<option value="filename-desc"{if isset($FM_SORT) && $FM_SORT=='filename-desc'} selected="selected"{/if}>{$LANG.common.name} (Z-A)</option>
					<option value="filesize-asc"{if isset($FM_SORT) && $FM_SORT=='filesize-asc'} selected="selected"{/if}>{$LANG.common.size} ({$LANG.category.sort_low_high})</option>
					<option value="filesize-desc"{if isset($FM_SORT) && $FM_SORT=='filesize-desc'} selected="selected"{/if}>{$LANG.common.size} ({$LANG.category.sort_high_low})</option>
					<option value="date_added-asc"{if isset($FM_SORT) && $FM_SORT=='date_added-asc'} selected="selected"{/if}>{$LANG.category.sort_date} ({$LANG.category.sort_date_added_desc})</option>
					<option value="date_added-desc"{if isset($FM_SORT) && $FM_SORT=='date_added-desc'} selected="selected"{/if}>{$LANG.category.sort_date} ({$LANG.category.sort_date_added_asc})</option>
				</select>
			</div>
			{if $FILMANAGER_MODE == FileManager::FM_FILETYPE_IMG}<input type="text" name="fm-search-term" id="fm-search-term" placeholder="{$LANG.common.search}..."><button type="button" class="button tiny" id="fm-search-button" data-mode="{if $FILMANAGER_MODE == FileManager::FM_FILETYPE_IMG}images{else}digital{/if}" data-action="show">{$LANG.common.go}</button>
			
		</div>
		<hr>
		{/if}
		{if $FILMANAGER_MODE == FileManager::FM_FILETYPE_DL && !$SELECT_BUTTON}
		<p>{$LANG.filemanager.public}</p>
		{/if}
		{if $SELECT_BUTTON}
		<p>{$LANG.filemanager.how_to_select}</p>
		{/if}
		<div id="fm-wrapper" class="{if $FILMANAGER_MODE == FileManager::FM_FILETYPE_IMG}images{else}digital{/if}" style="overflow:hidden;">
			{if $FOLDER_PARENT}
			<div>
				<a href="{$FOLDER_PARENT}" class="fm_location"><i class="fa fa-arrow-left" aria-hidden="true"></i> {$LANG.filemanager.parent_directory}</a>
			</div>
			{/if}
			{if isset($FOLDERS)}
			{foreach from=$FOLDERS item=folder}
			<div {if $FILMANAGER_MODE == FileManager::FM_FILETYPE_IMG}class="fm-item folder {$FM_SIZE}"{/if}>
			{if $FILMANAGER_MODE == FileManager::FM_FILETYPE_IMG}<a href="{$folder.link}" class="thumbnail fm_folder fm_location item_link"><img src="{$SKIN_VARS.admin_folder}/skins/default/images/folder_large.png" /></a>{/if}
			<span class="actions">
			{if NOT is_null($folder.delete)}
			{if $FILMANAGER_MODE == FileManager::FM_FILETYPE_IMG}<input type="checkbox" value="{$folder.value}" class="multi_delete" name="multi_delete[]">{/if}
			<a href="{$folder.delete}" class="delete right" title="{sprintf($LANG.notification.confirm_delete_folder,$folder.name)}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
			{/if}
			</span>
			{if $FILMANAGER_MODE == FileManager::FM_FILETYPE_DL}<input type="checkbox" value="{$folder.value}" class="multi_delete" name="multi_delete[]"> <i class="fa fa-folder" aria-hidden="true" alt="{$folder.name}"></i>{/if}
			<a href="{$folder.link}" class="item_link">{$folder.name}</a>
		</div>
		{/foreach}
		{/if}
		{if isset($FILES)}
		{foreach from=$FILES item=file}
		<div {if $FILMANAGER_MODE == FileManager::FM_FILETYPE_IMG}class="fm-item {$FM_SIZE}{if $file.file_name_hash==$HILIGHTED_FILE} hilighted{/if}"{/if} id="{$file.file_name_hash}">
		{if $FILMANAGER_MODE == FileManager::FM_FILETYPE_IMG}
		<a href="{$file.master_filepath}?{$file.random}" class="{$file.class} thumbnail" title="{$file.description}" target="_self">
		<img class="lazyload item_link" data-src="{$file.filepath}" src="data:image/gif;base64,R0lGODdhAQABAPAAAMPDwwAAACwAAAAAAQABAAACAkQBADs=">
		</a>
		<span class="actions">
		<input type="checkbox" value="{$file.value}" class="multi_delete right" name="multi_delete[]">
		<span class="filesize">{$file.filesize}</span>
		{if $file.select_button}
		<a href="{$file.master_filepath}" class="select{if $SOURCE=='options'} options{/if}" rel="{$file.file_id}"><i class="fa fa-plus-circle" title="{$LANG.common.add}"></i></a>
		{else}
		<a href="{$file.delete}" class="delete right" title="{sprintf($LANG.notification.confirm_delete_file,$file.filename)}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
		<a href="{$file.edit}" class="edit right" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
		{/if}
		</span>
		{/if}
		{if $FILMANAGER_MODE == FileManager::FM_FILETYPE_DL}
		<input type="checkbox" value="{$file.value}" class="multi_delete" name="multi_delete[]"> <i class="fa fa-{$file.icon}" aria-hidden="true" alt="{$file.mimetype}"></i>
		{if $file.select_button}
		<span class="actions">
		<a href="{$file.master_filepath}" class="select"><i class="fa fa-plus-circle" title="{$LANG.common.add}"></i></a>
		</span>
		{/if}
		{/if}
		<a class="item_link" href="{if $file.class}{$file.filepath}?{$file.random}{else}?_g=filemanager&download_file={base64_encode($file.filepath)}{/if}" class="{$file.class}" title="{$file.description}" target="_self">{$file.filename} <span class="list-filesize">({$file.filesize})</span></a>
	</div>
	{/foreach}
	{else}
	<p class="center clear">{$LANG.filemanager.file_none}</p>
	{/if}
	</div>
	{if isset($FILES) || isset($FOLDERS)}
	<div class="form_control">
		<img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/select_all.gif" alt=""> <a href="#" class="check-all" rel="multi_delete">{$LANG.form.check_uncheck}</a>
		<hr>
		<input type="submit" class="delete submit_confirm" title="{$LANG.notification.confirm_delete}" value="{$LANG.common.delete_selected}">
		<input type="hidden" id="ckfuncnum" value="{$CK_FUNC_NUM}">
	</div>
	{/if}
	</div>
	<div id="upload" class="tab_content">
		<h3>{$FILMANAGER_TITLE}</h3>
		{if $FILMANAGER_MODE == FileManager::FM_FILETYPE_IMG}
		<div class="dropzone">
			<div class="dz-default dz-message"><span>{$LANG.filemanager.file_upload_note}</span></div>
		</div>
		<div id="dropzone_url" style="display: none;">{$VAL_SELF}</div>
		{else}
		<div>
			<span><input name="file" id="uploader" type="file" class="multiple"></span>
		</div>
		<p>{$UPLOAD_LIMIT_DESC}</p>
		<div class="form_control">
			<input type="submit" value="{$LANG.common.save}">
			<input type="hidden" id="ckfuncnum" value="{$CK_FUNC_NUM}">
		</div>
		{/if}
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
	{include file='templates/element.hook_form_content.php'}
	<div class="form_control">
		<input type="hidden" name="file_id" value="{$FILE.file_id}">
		<input type="hidden" name="previous-tab" id="previous-tab" value="">
		<input type="submit" value="{$LANG.common.save}">
		<input type="submit" name="cancel" value="{$LANG.common.cancel}">
	</div>
	{/if}
</form>