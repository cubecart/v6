<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  {if isset($DISPLAY_EMAIL_LIST)}
  <div id="email_contents" class="tab_content">
	<h3>{$LANG.email.title_contents}</h3>
	<table class="list">
	  <thead>
		<tr>
		  <td width="300">{$LANG.email.email_type}</td>
		  <td colspan="2">{$LANG.translate.title_translations}</td>
		</tr>
	  </thead>
	  <tbody>
		{foreach from=$EMAIL_CONTENTS item=content}
		<tr>
		  <td><strong>{$content.type}</strong></td>
		  <td align="center">
		  	{if isset($content.translations)}
			{foreach from=$content.translations item=translation}
			<a href="{$translation.edit}"><img src="language/flags/{$translation.language}.png" alt="{$translation.language}"></a>
			{/foreach}
			{else}
			{$LANG.translate.trans_none}
			{/if}
		  </td>
		  <td width="30" align="center">
			<a href="{$content.translate}" title="{$LANG.translate.trans_add}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/add.png" alt="{$LANG.translate.trans_add}"></a>
		  </td>
		</tr>
		{/foreach}
	  </tbody>
	</table>
  </div>

  <div id="email_templates" class="tab_content">
	<h3>{$LANG.email.title_templates}</h3>
	  <fieldset class="list">
	  {if isset($EMAIL_TEMPLATES)}
	  {foreach from=$EMAIL_TEMPLATES item=template}
	  <div>
		<span class="actions">
		  <a href="{$template.clone}" title="{$LANG.common.clone}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/page_copy.png" alt="{$LANG.common.clone}"></a>
		  <a href="{$template.edit}" title="{$LANG.common.edit}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/edit.png" alt="{$LANG.common.edit}"></a>
		  <a href="{$template.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a>
		</span>
		<input type="hidden" name="template_default[{$template.template_id}]" id="template_default_{$template.template_id}" value="{$template.template_default}" class="toggle unique"> <a href="{$template.edit}">{$template.title}</a>
	  </div>
	  {/foreach}
	  {else}
	  <div>{$EMAIL.email.templates_none}</div>
	  {/if}
	  </fieldset>
	<div><a href="{$TEMPLATE_CREATE}">{$LANG.email.template_create}</a></div>
  </div>

  <div id="email_import" class="tab_content">
	{if isset($EMAIL_IMPORT)}
	<h3>{$LANG.email.title_content_manage}</h3>

	<fieldset><legend>{$LANG.common.import}</legend>
	  <p>{$LANG.email.help_email_import}</p>
	  <div>
		<select name="import">
		  <option value="">{$LANG.form.please_select}</option>
		  {foreach from=$EMAIL_IMPORT item=import}<option value="{$import.file}">{$import.code}</option>{/foreach}
		</select>
	  </div>
	</fieldset>
	{/if}

	{if isset($EMAIL_EXPORTS)}
	<fieldset><legend>{$LANG.common.export}</legend>
	  <p>{$LANG.email.help_email_export}</p>
	  <div>
		<select name="export">
		  <option value="">{$LANG.form.please_select}</option>
		  {foreach from=$EMAIL_EXPORTS item=export}
		  <option value="{$export}">{$export}</option>
		  {/foreach}
		  </select>
		<input type="checkbox" name="export_compress" value="1" checked="checked"> {$LANG.email.export_compress}
	  </div>
	</fieldset>
	{/if}
  </div>
  {/if}

  {if isset($DISPLAY_CONTENT_FORM)}
  <div id="general" class="tab_content">
	<h3>{$ADD_EDIT_CONTENT}</h3>
	<fieldset>
	  <div><label for="content_subject">{$LANG.common.subject}</label><span><input type="text" name="content[subject]" id="content_subject" value="{$CONTENT.subject}" class="textbox"></span></div>
	  <div><label for="content_language">{$LANG.common.language}</label><span><select name="content[language]" id="content_language" class="textbox">
	  {foreach from=$LANGUAGES item=language}<option value="{$language.code}"{$language.selected}>{$language.title}</option>{/foreach}
	  </select></span></div>
	</fieldset>
  </div>
  <div id="email_html" class="tab_content">
    <h3>{$LANG.email.title_content_html}</h3>
	<textarea name="content[content_html]" id="content_html" class="textbox fck">{$CONTENT.content_html}</textarea>
  	<script type="text/javascript">
	  //<![CDATA[
		CKEDITOR.replace( 'content_html',
			{
				fullPage : true
			});
	  //]]>
	</script>

  	<h3>{$LANG.email.title_macros}</h3>
  	<p>{$LANG.email.important}</p>
  	<table class="list">
  		<thead>
  		  <tr>
  			<td>{$LANG.email.email_macro}</td>
  			<td>{$LANG.common.description}</td>
  		  </tr>
  		</thead>
  		<tbody>
		  {foreach from=$CONTENT_MACROS item=macro}
  		  <tr>
  			<td>{$macro.name}</td>
  			<td>{$macro.description}</td>
  		  </tr>
  		  {/foreach}
  		</tbody>
  	</table>
  </div>
  <div id="email_text" class="tab_content">
  	<h3>{$LANG.email.title_content_text}</h3>
	<textarea name="content[content_text]" id="content_text" class="textbox" style="width: 100%; height: 480px">{$CONTENT.content_text}</textarea>
  	<h3>{$LANG.email.title_macros}</h3>
  	<p>{$LANG.email.important}</p>
  	<table class="list">
  		<thead>
  		  <tr>
  			<td>{$LANG.email.email_macro}</td>
  			<td>{$LANG.common.description}</td>
  		  </tr>
  		</thead>
  		<tbody>
		  {foreach from=$CONTENT_MACROS item=macro}
  		  <tr>
  			<td>{$macro.name}</td>
  			<td>{$macro.description}</td>
  		  </tr>
  		  {/foreach}
  		</tbody>
  	</table>
  </div>
  <input type="hidden" name="content[content_type]" value="{$CONTENT.content_type}">
  <input type="hidden" name="content[content_id]" value="{$CONTENT.content_id}">
  {/if}

  {if isset($DISPLAY_TEMPLATE_FORM)}
  <div id="general" class="tab_content">
  	<h3>{$ADD_EDIT_TEMPLATE}</h3>
  	<fieldset>
  	<div><label for="template_desc">{$LANG.email.template_name}</label><span><input type="text" name="template[title]" id="template_desc" value="{$TEMPLATE.title}" class="textbox required"></span></div>
  	</fieldset>
  </div>
  <div id="email_html" class="tab_content">
    <h3>{$LANG.email.title_content_html}</h3>
	<textarea name="template[content_html]" id="template_html" class="textbox fck fck-full">{$TEMPLATE.content_html}</textarea>
	<script type="text/javascript">
	  //<![CDATA[
		CKEDITOR.replace( 'template_html',
			{
				fullPage : true
			});
	  //]]>
	</script>
  	<h3>{$LANG.email.title_macros}</h3>
  	<table class="list">
  		<thead>
  			<tr>
  				<td>{$LANG.email.email_macro}</td>
  				<td>{$LANG.common.description}</td>
  				<td>{$LANG.common.required}</td>
  			</tr>
  		</thead>

  		<tbody>
		  {foreach from=$TEMPLATE_MACROS item=macro}
  		  <tr>
  			<td>{$macro.name}</td>
  			<td>{$macro.description}</td>
  			<td align="center">{$macro.required}</td>
  		  </tr>
  		  {/foreach}
  		</tbody>
  	</table>
  </div>
  <div id="email_text" class="tab_content">
    <h3>{$LANG.email.title_content_text}</h3>
	<textarea name="template[content_text]" id="template_text" class="textbox" style="width: 100%; height: 480px">{$TEMPLATE.content_text}</textarea>
  	<h3>{$LANG.email.title_macros}</h3>
  	<table class="list">
  		<thead>
		  <tr>
			<td>{$LANG.email.email_macro}</td>
			<td>{$LANG.common.description}</td>
			<td>{$LANG.common.required}</td>
		  </tr>
  		</thead>
  		<tbody>
		  {foreach from=$TEMPLATE_MACROS item=macro}
  		  <tr>
  			<td>{$macro.name}</td>
  			<td>{$macro.description}</td>
  			<td align="center">{$macro.required}</td>
  		  </tr>
  		  {/foreach}
  		</tbody>
  	</table>
  </div>

  <input type="hidden" name="template[template_id]" value="{$TEMPLATE.template_id}">
  {/if}
  
  {include file='templates/element.hook_form_content.php'}

  <div class="form_control">
	<input id="previous-tab" type="hidden" value="" name="previous-tab">
	<input type="submit" value="{$LANG.common.save}">{if isset($DISPLAY_DELETE_LINK)} <a href="{$LINK_DELETE}" class="delete" title="{$LANG.notification.confirm_delete}">{$LANG.common.delete}</a>{/if}
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
</form>