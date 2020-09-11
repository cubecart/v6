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
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  {if isset($mode_list)}
  <div id="filemanager" class="tab_content">
	<h3>{if $FILMANAGER_MODE == '1'}<span class="toggle"><span class="small"></span><span class="medium"></span><span class="large"></span><span class="xlarge"></span></span>{/if}{$FILMANAGER_TITLE}</h3>
	<input type="text" name="fm-search-term" id="fm-search-term" placeholder="{$LANG.common.search}..."><button type="button" class="button tiny" id="fm-search-button" data-mode="{if $FILMANAGER_MODE == '1'}images{else}digital{/if}">{$LANG.common.go}</button>
	<hr>
	{if $FILMANAGER_MODE == '2'}
	<p>{$LANG.filemanager.public}</p>
	{/if}
	<div id="fm-wrapper" style="overflow:hidden;">
	  {if $FOLDER_PARENT}
	  <div>
		<a href="{$FOLDER_PARENT}"><i class="fa fa-arrow-left" aria-hidden="true"></i> Parent Directory</a>
	  </div>
	  {/if}
	  {if isset($FOLDERS)}
	  {foreach from=$FOLDERS item=folder}
	  <div {if $FILMANAGER_MODE == '1'}class="fm-item folder {$FM_SIZE}"{/if}>
	  {if $FILMANAGER_MODE == '1'}<a href="{$folder.link}" class="thumbnail"><img src="{$SKIN_VARS.admin_folder}/skins/default/images/folder_large.png"/></a>{/if}
		<span class="actions">
		{if NOT is_null($folder.delete)}
		<input type="checkbox" value="{$folder.value}" name="multi_delete[]">
		<a href="{$folder.delete}" class="delete right" title="{$LANG.notification.confirm_delete_folder|replace:'%s':$folder.name}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
		{/if}
		</span>
		{if $FILMANAGER_MODE == '2'}<img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/folder.png" alt="{$folder.name}">{/if}
		<a href="{$folder.link}">{$folder.name}</a>
	  </div>
	  {/foreach}
	  {/if}

	  {if isset($FILES)}
	  {foreach from=$FILES item=file}
	  <div {if $FILMANAGER_MODE == '1'}class="fm-item {$FM_SIZE}{if $file.file_id==$FILE_ID} hilighted{/if}"{/if} id="{$file.file_id}">
	  {if $FILMANAGER_MODE == '1'}<a href="{$file.filepath}?{$file.random}" class="{$file.class} thumbnail" title="{$file.description}" target="_self"><img class="lazyload" data-src="{$file.filepath}" src="data:image/gif;base64,R0lGODdhAQABAPAAAMPDwwAAACwAAAAAAQABAAACAkQBADs="></a>{/if}
	 	 <span class="actions">
		  <input type="checkbox" value="{$file.value}" name="multi_delete[]"> {$file.filesize}
		  {if $file.select_button}
		  <a href="{$file.master_filepath}" class="select"><i class="fa fa-plus-circle" title="{$LANG.common.add}"></i></a>
		  {else}
		  <a href="{$file.delete}" class="delete right" title="{$LANG.notification.confirm_delete_file|replace:'%s':$file.filename}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
		  <a href="{$file.edit}" class="edit right" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
		  {/if}
		</span>
		{if $FILMANAGER_MODE == '2'}<img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/{$file.icon}.png" alt="{$file.mimetype}">{/if}
		<a href="{if $file.class}{$file.filepath}?{$file.random}{else}?_g=filemanager&download_file={$file.filepath|base64_encode}{/if}" class="{$file.class}" title="{$file.description}" target="_self">{$file.filename}</a>
	  </div>
	  {/foreach}
	  {else}
	  <p class="center clear">{$LANG.filemanager.file_none}</p>
	  {/if}
	</div>
	<div class="form_control">
	<hr>
		<input type="submit" class="delete submit_confirm" title="{$LANG.notification.confirm_delete}" value="{$LANG.common.delete_selected}">
		<input type="hidden" id="ckfuncnum" value="{$CK_FUNC_NUM}">
  	</div>
  </div>
  <div id="upload" class="tab_content">
	<h3>{$FILMANAGER_TITLE}</h3>
	{if $FILMANAGER_MODE == '1'}
	<div class="cc_dropzone">
		<div class="dz-default dz-message"><span>{$LANG.filemanager.file_upload_note}</span></div>
	</div>
	<div id="cc_dropzone_url" style="display: none;">{$VAL_SELF}</div>
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
	<fieldset><legend>{$LANG.filemanager.folder_create}</legend>
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
	<fieldset>
	<div><label for="filename">{$LANG.filemanager.file_name}</label><span><input type="text" id="filename" name="details[filename]" class="textbox" value="{$FILE.filename}"></span></div>
	<div><label for="move">{$LANG.filemanager.file_subfolder}</label><span><select name="details[move]" id="move" class="textbox">
	  <option value="">{$LANG.form.please_select}</option>
	  {if isset($DIRS)}{foreach from=$DIRS item=dir}<option value="{$dir.path}"{$dir.selected}>{$dir.path}</option>{/foreach}{/if}
	</select>
	</span></div>
	<div><label for="description">{$LANG.common.description}</label><span><textarea name="details[description]" id="description" class="textbox">{$FILE.description}</textarea></span></div>
	</fieldset>
  </div>
  {if isset($SHOW_CROP)}
  <div id="fm-cropper" class="tab_content">
	<h3>{$LANG.filemanager.title_image_crop}</h3>
	<img id="resize" src="{$FILE.filepath}{$FILE.filename}?{$FILE.random}" alt="" class="cropper">
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