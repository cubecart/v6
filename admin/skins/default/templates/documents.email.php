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
  {if isset($DISPLAY_EMAIL_LIST)}
  <div id="email_contents" class="tab_content">
	<h3>{$LANG.email.title_contents}</h3>
	<table>
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
		  <td style="text-align:center" class="language_list">
		  	{if isset($content.translations)}
			{foreach from=$content.translations item=translation}
			<a href="{$translation.edit}"><img src="language/flags/{$translation.language}.png" alt="{$translation.language}" class="flag"></a>
			{/foreach}
			{else}
			{$LANG.translate.trans_none}
			{/if}
		  </td>
		  <td width="30" align="center">
			{if $content.translate!==false}
			<a href="{$content.translate}" title="{$LANG.translate.trans_add}"><i class="fa fa-plus-circle" title="{$LANG.translate.trans_add}"></i></a>
			{/if}
		  </td>
		</tr>
		{/foreach}
	  </tbody>
	</table>
  </div>

  <div id="email_templates" class="tab_content">
	<h3>{$LANG.email.title_templates}</h3>
	  <fieldset>
	  {if isset($EMAIL_TEMPLATES)}
	  <table width="70%">
		<tr>
			<th width="20">{$LANG.common.default}</th>
			<th>{$LANG.email.template_name}</th>
			<th colspan="3">&nbsp;</th>
		</tr>
	  {foreach from=$EMAIL_TEMPLATES item=template}
	  <tr>
		<td style="text-align:center">
			<input type="radio" name="template_default" id="template_default_{$template.template_id}" value="{$template.template_id}"{if $template.template_default==1} checked="checked"{/if}>
		</td>
		<td><a href="{$template.edit}">{$template.title}</a></td>
		<td width="10"><a href="{$template.clone}"><i class="fa fa-files-o" title="{$LANG.common.clone}"></i></a></td>
		<td width="10"><a href="{$template.edit}" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a></td>
		<td width="10"><a href="{$template.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></td>
	  </tr>
	  {/foreach}
	  </table>
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
	{if $LANGUAGES}
	<fieldset>
	  <div><label for="content_subject">{$LANG.common.subject}</label><span><input type="text" name="content[subject]" id="content_subject" value="{$CONTENT.subject}" class="textbox"></span></div>
	  <div><label for="content_language">{$LANG.common.language}</label><span><select name="content[language]" id="content_language" class="textbox">
	  {foreach from=$LANGUAGES item=language}<option value="{$language.code}"{$language.selected}>{$language.title}</option>{/foreach}
	  </select></span></div>
	</fieldset>
	{else}
	<p>{$LANG.email.install_master_lang}</p> 
	{/if}
  </div>
  <div id="email_html" class="tab_content">
    <h3>{$LANG.email.title_content_html}</h3>
	{if $LANGUAGES}
	<textarea name="content[content_html]" id="content_html" class="fck">{$CONTENT.content_html|escape:"html"}</textarea>
  	<h3>{$LANG.email.title_macros}</h3>
  	<p>{$LANG.email.important|escape:'htmlall'}</p>
  	<table>
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
  	{else}
	<p>{$LANG.email.install_master_lang}</p> 
	{/if}
  </div>
  <div id="email_text" class="tab_content">
  	<h3>{$LANG.email.title_content_text}</h3>
  	{if $LANGUAGES}
	<textarea name="content[content_text]" id="content_text" class="textbox" style="width: 100%; height: 480px">{$CONTENT.content_text}</textarea>
  	<h3>{$LANG.email.title_macros}</h3>
  	<p>{$LANG.email.important}</p>
  	<table>
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
  	{else}
	<p>{$LANG.email.install_master_lang}</p> 
	{/if}
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
	<textarea name="template[content_html]" id="template_html" class="fck fck-full" data-fck-height="450">{$TEMPLATE.content_html|escape:"html"}</textarea>
  	<h3>{$LANG.email.title_macros}</h3>
  	<table>
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
  			<td style="text-align:center">{$macro.required}</td>
  		  </tr>
  		  {/foreach}
  		</tbody>
  	</table>
  </div>
  <div id="email_text" class="tab_content">
    <h3>{$LANG.email.title_content_text}</h3>
	<textarea name="template[content_text]" id="template_text" class="textbox" style="height: 550px; width: 100%">{$TEMPLATE.content_text}</textarea>
  	<h3>{$LANG.email.title_macros}</h3>
  	<table>
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
  			<td style="text-align:center">{$macro.required}</td>
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
  
</form>