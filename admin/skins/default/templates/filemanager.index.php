<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  {if isset($mode_list)}
  <div id="filemanager" class="tab_content">
	<h3>{$FILMANAGER_TITLE}</h3>
	<div class="list" style="height: 430px; overflow: auto; padding-right: 5px;">
	  {if isset($FOLDERS)}
	  {foreach from=$FOLDERS item=folder}
	  <div>
		<span class="actions">
		{if NOT is_null($folder.delete)}
		<a href="{$folder.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a>
		{/if}
		</span>
		<img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/folder.png" alt="{$folder.name}">
		<a href="{$folder.link}">{$folder.name}</a>
	  </div>
	  {/foreach}
	  {/if}

	  {if isset($FILES)}
	  {foreach from=$FILES item=file}
	  <div>
		<span class="actions">
		  {if $file.select_button}
		  <a href="{$file.master_filepath}" class="select"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/add.png" alt="{$LANG.common.add}"></a>
		  {else}
		  <a href="{$file.edit}" class="edit" title="{$LANG.common.edit}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/edit.png" alt="{$LANG.common.edit}"></a>
		  <a href="{$file.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a>
		  {/if}
		</span>
		<img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/{$file.icon}.png" alt="{$file.mimetype}">
		<a href="{$file.filepath}?{$file.random}" {$file.class} title="{$file.description}" target="_self">{$file.filename}</a>
	  </div>
	  {/foreach}
	  {else}
	  <div class="center">{$LANG.filemanager.file_none}</div>
	  {/if}
	</div>
  </div>
  <div id="upload" class="tab_content">
	<h3>{$FILMANAGER_TITLE}</h3>
	{if $FILMANAGER_MODE == '1'}
	<p>{$LANG.filemanager.file_upload_note}</p>
	{/if}
	<div>
	  <label for="uploader">{$LANG.filemanager.file_upload}</label><span><input name="file" id="uploader" type="file" class="multiple"></span>
	</div>
  </div>
  <div id="folder" class="tab_content">
	<h3>{$FILMANAGER_TITLE}</h3>
	<div><label for="create-dir">{$LANG.filemanager.folder_create}</label><span><input name="fm[create-dir]" id="create-dir" type="text" class="textbox"></span></div>
  </div>
  
  {include file='templates/element.hook_form_content.php'}
  
  <div class="form_control">
	<input type="hidden" name="previous-tab" id="previous-tab" value="">
	<input type="submit" value="{$LANG.common.save}">
	<input type="hidden" id="ckfuncnum" value="{$CK_FUNC_NUM}">
  </div>
  {/if}

  {if isset($mode_form)}
  <div id="fm-details" class="tab_content">
	<h3>{$LANG.filemanager.title_file_edit}</h3>
	<div><label for="filename">{$LANG.filemanager.file_name}</label><span><input type="text" id="filename" name="details[filename]" class="textbox" value="{$FILE.filename}"></span></div>
	<div><label for="move">{$LANG.filemanager.file_subfolder}</label><span><select name="details[move]" id="move" class="textbox">
	  <option value="">{$LANG.form.please_select}</option>
	  {if isset($DIRS)}{foreach from=$DIRS item=dir}<option value="{$dir.path}"{$dir.selected}>{$dir.path}</option>{/foreach}{/if}
	</select>
	</span></div>
	<div><label for="description">{$LANG.common.description}</label><span><textarea name="details[description]" id="description" class="textbox">{$FILE.description}</textarea></span></div>
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
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
</form>
