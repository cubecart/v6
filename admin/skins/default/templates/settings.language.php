<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  {if isset($LANGUAGES)}
  <div id="lang_list" class="tab_content">
	<h3>{$LANG.translate.title_languages}</h3>
	<div class="list">
	<div>
		<span class="actions">
		  <a href="?_g=settings&node=language&download=definitions" title="{$LANG.common.download}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/download.png" alt="{$LANG.common.download}"></a>
		</span>
		<img src="language/flags/globe.png" alt="{$LANG.translate.master_language}">
		{$LANG.translate.master_language}
	  </div>
	{foreach from=$LANGUAGES item=language}
	  <div>
		<span class="actions">
		  <input type="hidden" name="status[{$language.code}]" id="status_{$language.code}" value="{$language.status}" class="toggle">
		  <a href="{$language.edit}" title="{$LANG.common.edit}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/edit.png" alt="{$LANG.common.edit}"></a>
		  <a href="{$language.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a>
		  <a href="{$language.download}" title="{$LANG.common.download}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/download.png" alt="{$LANG.common.download}"></a>
		</span>
		<img src="{$language.flag}" alt="{$language.title}">
		<a href="{$language.edit}">{$language.title}</a>
	  </div>
	{/foreach}
	</div>
  </div>
  <div id="lang_create" class="tab_content">
	<h3>{$LANG.translate.title_language_create}</h3>
	<p><strong>{$LANG.common.advanced}</strong>: {$LANG.common.help_required}</p>
	<fieldset>
	  <div><label for="create_title">{$LANG.translate.language_name}</label><span><input id="create_title" type="text" name="create[title]" class="textbox required"></span></div>
	  <div><label for="create_code">{$LANG.translate.language_code}</label><span><input id="create_code" type="text" name="create[code]" class="textbox required"></span></div>
	  <div><label for="create_parent">{$LANG.translate.language_parent}</label>
	    <span>
	      <select id="create_parent" name="create[parent]">
	        <option value="">{$LANG.form.none}</option>
	          {foreach from=$LANGUAGES item=language}
	          	<option value="{$language.code}">{$language.title}</option>
	          {/foreach}
	      </select>
	    </span>
	  </div>
	</fieldset>
  </div>
  <div id="lang_import" class="tab_content">
	<h3>{$LANG.translate.title_language_import}</h3>
	<p><strong>{$LANG.common.advanced}</strong>: {$LANG.common.help_required}</p>
	<fieldset>
	  <div><label for="import_overwrite">{$LANG.filemanager.overwrite}</label><span><input id="import_overwrite" type="checkbox" name="import[overwrite]"></span></div>
	  <div><label for="import_file">{$LANG.filemanager.file_upload}</label><span><input id="import_file" type="file" name="import[file]" class="textbox"> {$LANG.translate.example_upload}</span></div>
	</fieldset>
  </div>
  
  {include file='templates/element.hook_form_content.php'}
  
  <div class="form_control">
	<input type="hidden" name="previous-tab" id="previous-tab" value="">
	<input type="submit" name="save" value="{$LANG.common.save}">
  </div>
  {/if}

  {if $DISPLAY_EDITOR}
  <div class="tab_content" id="general">
	<h3>{$LANG.translate.tab_string_edit}</h3>
	{if $SECTIONS}
	<fieldset><legend>{$LANG.translate.language_group_edit}</legend>
	  <div>
		<select name="type" class="textbox update_form required">
		  <option value="">{$LANG.form.please_select}</option>
		  {foreach from=$SECTIONS item=section}
		  <option value="{$section.name}" {$section.selected}>{$section.description}</option>
		  {/foreach}
		  <optgroup label="{$LANG.navigation.nav_modules}">
	        {foreach from=$MODULES item=module}
	        <option value="{$module.path}" {$module.selected}>{$module.name}</option>
	        {/foreach}
	      </optgroup>
		</select>
	  </div>
	</fieldset>
	{/if}

	{if isset($STRINGS)}
	<fieldset class="list"><legend>{$STRING_TYPE}</legend>
	  {foreach from=$STRINGS item=string}
	  <div id="row_{$string.name}">
		<span class="actions">
		  <input type="hidden" id="default_{$string.name}" value="{$string.default}">
		  <a href="#" class="revert" rel="{$string.name}" title="{$LANG.common.revert}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/time.png" alt="{$LANG.common.revert}"></a>
		</span>
		<label for="string_{$string.name}">{$string.name}</label>
		<span>
		  <input type="hidden" id="defined_{$string.name}" value="{$string.defined}">
		  {if isset($string.multiline)}
		  <textarea id="string_{$string.name}" name="string[{$string.type}][{$string.name}]" class="textbox">{$string.value}</textarea>
		  {else}
		  <input type="text" id="string_{$string.name}" name="string[{$string.type}][{$string.name}]" value="{$string.value}" class="textbox">
		  {/if}
		</span>
	  </div>
	  {/foreach}
	</fieldset>
	{/if}
	<div>
	  <input type="hidden" name="previous-tab" id="previous-tab" value="">
	  <input type="submit" name="save" value="{$LANG.common.save}">
	</div>
  </div>
  {/if}

  {if isset($DISPLAY_EXPORT)}
  <div class="tab_content" id="merge">
	<h3>{$LANG.translate.merge_db_file}</h3>
	<p><strong>{$LANG.common.advanced}</strong>: {$LANG.common.help_required}</p>
	<fieldset><legend>{$LANG.catalogue.title_import_options}</legend>
	  <div><input type="checkbox" name="export_opt[replace]" value="1"> {$LANG.translate.replace_original}</div>
	  {if $COMPRESSION}
	  <div><input type="checkbox" name="export_opt[compress]" value="1"> {$LANG.common.compress_file}</div>
	  {/if}
	</fieldset>
  </div>
  
  {include file='templates/element.hook_form_content.php'}
  
  <div class="form_control">
	<input type="submit" name="export" value="{$LANG.common.export}">
  </div>
  {/if}
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
</form>