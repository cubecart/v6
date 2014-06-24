<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  {if $DISPLAY_PLUGINS}
  <div id="plugins" class="tab_content">
	<h3>{$LANG.hooks.title_plugins_installed}</h3>
	<fieldset class="list">
	  {foreach from=$PLUGINS item=plugin}
	  <div>
		<span class="actions">&nbsp;</span>
		<img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/plugin.png" alt=""> <a href="{$plugin.edit}">{$plugin.name}</a>
	  </div>
	  {foreachelse}
	  <div>{$LANG.hooks.error_plugin_none}</div>
	  {/foreach}
	</fieldset>
  </div>
  <div id="snippets" class="tab_content">
	<h3>{$LANG.hooks.title_code_snippets}</h3>
	<p><a href="?_g=settings&node=hooks&add_snippet=1#snippets" class="delete">{$LANG.hooks.add_snippet}</a></p>
	<fieldset class="list">
	  {foreach from=$SNIPPETS item=snippet}
	  <div>
		<span class="actions"><a href="?_g=settings&node=hooks&snippet={$snippet.snippet_id}#snippets"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/edit.png" alt=""></a> <a href="?_g=settings&node=hooks&delete_snippet={$snippet.snippet_id}#snippets" class="delete" title="{$LANG.notification.confirm_continue}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt=""></a></span>
		<span class="toggle"><input type="hidden" id="snippet_status_{$snippet.snippet_id}" name="snippet_status[{$snippet.snippet_id}]" value="{$snippet.enabled}" class="toggle"></span> <img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/php.png" alt=""> <a href="?_g=settings&node=hooks&snippet={$snippet.snippet_id}#snippets">{$snippet.description}</a>
	  </div>
	  {foreachelse}
	  <div>{$LANG.hooks.error_snippet_none}</div>
	  {/foreach}
	</fieldset>
	
	
	{if $DISPLAY_SNIPPET_FORM}
	
	{if $SNIPPET}
	<h3>{$LANG.hooks.title_code_snippet_edit}</h3>
	{else}
	<h3>{$LANG.hooks.title_code_snippet_add}</h3>
	{/if}
	<p><a href="?_g=settings&node=hooks#snippets" class="delete">{$LANG.common.cancel}</a></p>
	
	<fieldset>
	  <legend>{$LANG.hooks.title_code_snippet}</legend>
	  <div><label for="enabled">{$LANG.common.enabled}</label><span><input name="snippet[enabled]" id="enabled" type="hidden" class="toggle" value="{$SNIPPET.enabled}"></span></div>
	  <div>
		<label for="unique_id">{$LANG.hooks.unique_id}</label>
		<span>
		  <input type="text" name="snippet[unique_id]" id="unique_id" class="textbox required" value="{$SNIPPET.unique_id}">
		</span>
	  </div>
	  <div>
		<label for="priority">{$LANG.hooks.priority}</label>
		<span>
		  <input type="text" name="snippet[priority]" id="priority" class="textbox required number" value="{$SNIPPET.priority}">
		</span>
	  </div>
	  <div>
		<label for="description">{$LANG.common.description}</label>
		<span>
		  <input type="text" name="snippet[description]" id="description" class="textbox required" value="{$SNIPPET.description}">
		</span>
	  </div>
	  <div>
		<label for="trigger">{$LANG.hooks.trigger}</label>
		<span>
		  <select name="snippet[hook_trigger]" id="hook_trigger" class="required">
			<option value="">{$LANG.form.please_select}</option>
			{foreach from=$TRIGGERS item=trigger}<option value="{$trigger.trigger}"{$trigger.selected}>{$trigger.trigger}</option>{/foreach}
		  </select>
		</span>
	  </div>
	  <div>
		<label for="php_code">{$LANG.hooks.php_code}*</label>
		  <span><textarea name="snippet[php_code]" id="php_code" class="required" rows="12" cols="50">{$SNIPPET.php_code}</textarea></span>
	  </div>
	  <div>
		<label for="version">{$LANG.hooks.version}</label>
		<span>
		  <input type="text" name="snippet[version]" id="version" class="textbox" value="{$SNIPPET.version}">
		</span>
	  </div>
	  <div>
		<label for="author">{$LANG.hooks.author}</label>
		<span>
		  <input type="text" name="snippet[author]" id="author" class="textbox" value="{$SNIPPET.author}">
		</span>
	  </div>
	  <input type="hidden" name="snippet[snippet_id]" id="snippet_id" class="textbox" value="{$SNIPPET.snippet_id}">
	</fieldset>
	* {$LANG.hooks.php_tag_replace}
	{/if}
  </div>
  
  <div id="snippets_import" class="tab_content">
  <h3>{$LANG.hooks.title_import_code_snippets}</h3>
	<p>{$LANG.hooks.example_code_snippet_xml}</p>
	<fieldset><legend>{$LANG.hooks.browse_for_file}</legend>
	<input type="file" name="code_snippet_import" id="code_snippet_import">
	</fieldset>
  </div>
  {/if}

  {if $DISPLAY_HOOKS}
  <div id="hooks" class="tab_content">
	<h3>{$PLUGIN}</h3>
	<fieldset class="list"><legend>{$LANG.hooks.title_hook_available}</legend>
	  {foreach from=$HOOKS item=hook}
	  <div>
		<span class="actions">
		  <a href="{$hook.edit}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/edit.png" alt="{$LANG.common.edit}"></a>
		</span>
		<input type="hidden" name="status[{$hook.hook_id}]" value="{$hook.enabled}" id="status_{$hook.hook_id}" class="toggle">
		<a href="{$hook.edit}">{$hook.hook_name}</a>
	  </div>
	  {foreachelse}
	  <div>{$LANG.hooks.error_hook_none}</div>
	  {/foreach}
	</fieldset>
	<p>{$LANG.hooks.notify_hook_magic}</p>
  </div>
  {/if}

  {if $DISPLAY_FORM}
  <div id="hook_edit" class="tab_content">
	<h3>{$LANG.hooks.title_hook_configure}</h3>
	<fieldset><legend>{$LANG.hooks.title_hook_required}</legend>
	  <div><label for="hook_name">{$LANG.hooks.name}</label><span><input type="text" name="hook[hook_name]" id="hook_name" class="textbox required" value="{$HOOK.hook_name}"></span></div>
	  {if isset($PLUGINS)}
	  <div>
		<label for="plugin">{$LANG.hooks.title_plugin}</label>
		<span>
		  <select name="hook[plugin]" id="plugin" class="required">
			<option value="">{$LANG.form.please_select}</option>
			{foreach from=$PLUGINS item=plugin}<option value="{$plugin.plugin}"{$plugin.selected}>{$plugin.name}</option>{/foreach}
		  </select>
		</span>
	  </div>
	  {/if}
	  <div>
		<label for="trigger">{$LANG.hooks.trigger}</label>
		<span>
		  <select name="hook[trigger]" id="trigger" class="required">
			<option value="">{$LANG.form.please_select}</option>
			{foreach from=$TRIGGERS item=trigger}<option value="{$trigger.trigger}"{$trigger.selected}>{$trigger.trigger}</option>{/foreach}
		  </select>
		</span>
	  </div>
	</fieldset>
	<fieldset><legend>{$LANG.hooks.title_hook_optional}</legend>
	  <div><label for="filepath">{$LANG.hooks.path_to_file}</label><span><input type="text" name="hook[filepath]" id="filepath" class="textbox" value="{$HOOK.filepath}"></span></div>
	  <input type="hidden" name="hook[hook_id]" value="{$HOOK.hook_id}">
	</fieldset>
  </div>
  {/if}

  {include file='templates/element.hook_form_content.php'}

  <div class="form_control"><input type="submit" value="{$LANG.common.save}"></div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
</form>