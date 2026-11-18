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
  {if isset($DISPLAY_EMAIL_LIST)}
  <div id="email_contents" class="tab_content">
	<h3>{$LANG.email.title_contents}</h3>
	<table>
	  <thead>
		<tr>
		  <td width="300">{$LANG.email.email_type}</td>
		  <td>{$LANG.translate.title_translations}</td>
		  <td width="80" style="text-align:center">{$LANG.common.status}</td>
		  {if $CAN_TRANSLATE}<td>&nbsp;</td>{/if}
		</tr>
	  </thead>
	  <tbody>
		{foreach from=$EMAIL_CONTENTS item=content name=email_loop}
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
		  <td width="80" style="text-align:center"><input type="hidden" name="email_types[{$smarty.foreach.email_loop.index}]" value="{$content.content_type}"><input type="hidden" name="email_enabled[{$smarty.foreach.email_loop.index}]" id="email_enabled_{$smarty.foreach.email_loop.index}" value="{$content.enabled}" class="toggle"></td>
		  {if $CAN_TRANSLATE}
		  <td width="30" align="center">
			{if $content.translate!==false}
			<a href="{$content.translate}" title="{$LANG.translate.trans_add}"><i class="fa fa-plus-circle" title="{$LANG.translate.trans_add}"></i></a>
			{/if}
		  </td>
		  {/if}
		</tr>
		{/foreach}
	  </tbody>
	</table>
  </div>

  <div id="email_templates" class="tab_content">
	<h3>{$LANG.email.design_title}</h3>
	<p>{$LANG.email.design_intro} <a href="{$DESIGN_LINK}" class="button">{$LANG.email.design_title}</a></p>
	<h3>{$LANG.email.title_templates}</h3>
	  <fieldset>
	  {if isset($EMAIL_TEMPLATES)}
	  <table width="70%">
	  	<thead>
			<tr>
				<th width="20">{$LANG.common.default}</th>
				<th>{$LANG.email.template_name}</th>
				<th colspan="4">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$EMAIL_TEMPLATES item=template}
			<tr>
				<td style="text-align:center">
					<input type="radio" name="template_default" id="template_default_{$template.template_id}" value="{$template.template_id}"{if $template.template_default==1} checked="checked"{/if}>
				</td>
				<td><a href="{$template.edit}">{$template.title}</a>{if $template.managed} <span class="email-field-help">{$LANG.email.design_title}</span>{/if}</td>
				<td width="10"><a href="#" class="preview-template" data-template-id="{$template.template_id}" data-template-title="{$template.title}" title="{$LANG.common.preview}"><i class="fa fa-search"></i></a></td>
				<td width="10"><a href="{$template.clone}"><i class="fa fa-files-o" title="{$LANG.common.clone}"></i></a></td>
				<td width="10"><a href="{$template.edit}" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a></td>
				<td width="10"><a href="{$template.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></td>
			</tr>
			{/foreach}
		</tbody>
	  </table>
	  <div id="email_tools" data-scope="template" data-label-desktop="{$LANG.email.preview_desktop}" data-label-mobile="{$LANG.email.preview_mobile}" data-label-text="{$LANG.email.preview_plain_text}" data-label-loading="{$LANG.common.loading}&hellip;" data-label-failed="{$LANG.email.preview_failed}" hidden></div>
	  <script type="text/javascript" defer src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/admin.email.js?{$VERSION_HASH}"></script>
	  {else}
	  <div>{$EMAIL.email.templates_none}</div>
	  {/if}
	  </fieldset>
	<div><a href="{$TEMPLATE_CREATE}">{$LANG.email.template_create}</a></div>
  </div>

  {/if}

  {if isset($DISPLAY_CONTENT_FORM)}
  <div id="general" class="tab_content">
	<h3>{$ADD_EDIT_CONTENT}</h3>
	{if $LANGUAGES}
	{* Without its scripts this is a plain form: the subject, the preview text and the body as code.
	   admin.email.editor.js turns it into the visual editor and saves it unit by unit (#4738). *}
	<div class="email-editor" id="email_editor">
	  <div class="email-editor__main">
		<div class="email-editor__bar" id="email_editor_bar" hidden>
		  <span class="email-editor__group" role="group">
			<button type="button" class="button is-on" data-view="names" aria-pressed="true">{$LANG.email.editor_view_names}</button><button type="button" class="button" data-view="sample" aria-pressed="false">{$LANG.email.editor_view_sample}</button>
		  </span>
		  <span class="email-editor__group" role="group">
			<button type="button" class="button is-on" data-width="100%" aria-pressed="true">{$LANG.email.preview_desktop}</button><button type="button" class="button" data-width="375px" aria-pressed="false">{$LANG.email.preview_mobile}</button>
		  </span>
		  <button type="button" class="button" id="email_code_toggle" aria-pressed="false">{$LANG.email.editor_code_view}</button>
		</div>
		<div class="email-editor__visual" id="email_visual_wrap" hidden>
		  <textarea id="email_visual" aria-label="{$LANG.email.title_content_html}"></textarea>
		  <p class="email-field-help">{$LANG.email.editor_design_note}</p>
		</div>
		<div class="email-editor__code" id="email_code_wrap">
		  <p class="email-field-help">{$LANG.email.editor_code_help}</p>
		  <div id="email_code_ace" class="ace_email_editor" hidden></div>
		  <textarea name="content[content_html]" id="content_html" class="textbox email-template__source" spellcheck="false" aria-label="{$LANG.email.title_content_html}"{if !$EMAIL_PREVIEW.can_edit} readonly{/if}>{$CONTENT.content_html|escape:'html'}</textarea>
		</div>
	  </div>
	  <div class="email-editor__side">
		<div class="email-editor__field">
		  <label for="content_language">{$LANG.common.language}</label>
		  {if empty($CONTENT.content_id)}
		  <select name="content[language]" id="content_language" class="textbox">
			{foreach from=$LANGUAGES item=language}<option value="{$language.code}"{$language.selected}>{$language.title}</option>{/foreach}
		  </select>
		  {else}
		  <span id="content_language_name">{if isset($ASSIGNED_LANG)}<img src="language/flags/{$ASSIGNED_LANG.code}.png" alt="" class="flag"> {$ASSIGNED_LANG.name}{else}{$CONTENT.language}{/if}</span>
		  {/if}
		</div>
		<div class="email-editor__field">
		  <label for="content_subject">{$LANG.common.subject}</label>
		  <input type="text" name="content[subject]" id="content_subject" value="{$CONTENT.subject|escape:'html'}" class="textbox email-line-field" maxlength="250"{if !$EMAIL_PREVIEW.can_edit} readonly{/if}>
		</div>
		<div class="email-editor__field">
		  <label for="content_preheader">{$LANG.email.preview_text}</label>
		  <input type="text" name="content[preheader]" id="content_preheader" value="{$CONTENT.preheader|escape:'html'}" class="textbox email-line-field" maxlength="255" aria-describedby="content_preheader_help"{if !$EMAIL_PREVIEW.can_edit} readonly{/if}>
		  <span id="content_preheader_help" class="email-field-help">{$LANG.email.preview_text_help}</span>
		</div>
		<div class="email-editor__field" id="email_placeholders" hidden>
		  <h4>{$LANG.email.editor_placeholders}</h4>
		  <p class="email-field-help">{$LANG.email.editor_placeholders_help}</p>
		  <div id="email_placeholders_list"></div>
		</div>
	  </div>
	</div>
	<div class="email-tools" id="email_tools" data-scope="content" data-content-id="{$CONTENT.content_id}" data-content-type="{$CONTENT.content_type}" data-label-desktop="{$LANG.email.preview_desktop}" data-label-mobile="{$LANG.email.preview_mobile}" data-label-text="{$LANG.email.preview_plain_text}" data-label-loading="{$LANG.common.loading}&hellip;" data-label-failed="{$LANG.email.preview_failed}">
		<button type="button" class="button" id="email_preview_button">{$LANG.common.preview}</button>
		{if $EMAIL_PREVIEW.latest_order}
		<span class="email-tools__group">
			<label for="email_sample">{$LANG.email.sample_data}</label>
			<select id="email_sample" class="textbox">
				<option value="latest"{if $EMAIL_PREVIEW.sample == 'latest'} selected="selected"{/if}>{$LANG.email.sample_latest_order|sprintf:$EMAIL_PREVIEW.latest_order}</option>
				<option value="example"{if $EMAIL_PREVIEW.sample == 'example'} selected="selected"{/if}>{$LANG.email.sample_example_order}</option>
			</select>
		</span>
		{/if}
		{if $EMAIL_PREVIEW.can_edit}
		<span class="email-tools__group">
			<label for="email_test_to">{$LANG.email.title_send_test}</label>
			<input type="text" inputmode="email" autocomplete="email" id="email_test_to" class="textbox" value="{$EMAIL_PREVIEW.test_to}">
			<button type="button" class="button" id="email_test_button">{$LANG.common.send}</button>
		</span>
		{/if}
		<span class="email-tools__status" id="email_tools_status" role="status"></span>
		<span class="email-tools__group">
			{* Save comes first: the first submit button of a form is the one Enter presses *}
			{if $EMAIL_PREVIEW.can_edit}<input type="submit" value="{$LANG.common.save}">{/if}
			{if $EMAIL_PREVIEW.can_edit && $CONTENT.content_id}<button type="submit" name="email_reset" value="{$CONTENT.content_id}" class="button submit_confirm" title="{$LANG.email.confirm_content_reset}">{$LANG.common.restore_default}</button>{/if}{if isset($DISPLAY_DELETE_LINK)} <a href="{$LINK_DELETE}" class="button delete" title="{$LANG.notification.confirm_delete}">{$LANG.common.delete}</a>{/if}
		</span>
	</div>
	{if isset($EMAIL_EDITOR_JSON)}<script type="application/json" id="email_editor_data">{$EMAIL_EDITOR_JSON}</script>{/if}
	<script type="text/javascript" defer src="includes/ace/src-min-noconflict/ace.js?{$VERSION_HASH}" charset="utf-8"></script>
	<script type="text/javascript" defer src="includes/tinymce/tinymce.min.js?{$VERSION_HASH}"></script>
	<script type="text/javascript" defer src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/admin.email.js?{$VERSION_HASH}"></script>
	<script type="text/javascript" defer src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/admin.email.editor.js?{$VERSION_HASH}"></script>
	{else}
	<p>{$LANG.email.install_master_lang}</p>
	{/if}
  </div>
  {if $LANGUAGES && isset($CONTENT_MACROS)}
  <div id="macros" class="tab_content">
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
  </div>
  {/if}
  <input type="hidden" name="content[content_type]" value="{$CONTENT.content_type}">
  <input type="hidden" name="content[content_id]" value="{$CONTENT.content_id}">
  {/if}

  {if isset($DISPLAY_TEMPLATE_FORM)}
  <div id="general" class="tab_content">
	<h3>{$ADD_EDIT_TEMPLATE}</h3>
	<table class="nostripe">
	  <tr>
		<td><label for="template_desc">{$LANG.email.template_name}</label></td>
		<td><input type="text" name="template[title]" id="template_desc" value="{$TEMPLATE.title}" class="textbox required"{if !$EMAIL_PREVIEW.can_edit} readonly{/if}></td>
	  </tr>
	</table>
	{if $TEMPLATE_MANAGED}<p class="email-note">{$LANG.email.design_managed}</p>{/if}
	{* A whole HTML document with Smarty in it is edited as code. A visual editor cannot show
	   its <head> and has to rebuild the document to display it, which is how #4738 happened. *}
	<div class="email-template">
		<div class="email-template__code">
			<div id="template_ace" class="ace_email_editor" hidden></div>
			<textarea name="template[content_html]" id="template_content_html" class="textbox email-template__source" spellcheck="false" aria-label="{$LANG.email.title_content_html}"{if !$EMAIL_PREVIEW.can_edit} readonly{/if}>{$TEMPLATE.content_html|escape:'html'}</textarea>
		</div>
		<div class="email-template__preview">
			<div class="email-preview__bar">
				<button type="button" class="button email-preview__size is-on" data-width="100%" aria-pressed="true">{$LANG.email.preview_desktop}</button>
				<button type="button" class="button email-preview__size" data-width="375px" aria-pressed="false">{$LANG.email.preview_mobile}</button>
				{if $EMAIL_PREVIEW.latest_order}
				<span class="email-tools__group">
					<label for="email_sample">{$LANG.email.sample_data}</label>
					<select id="email_sample" class="textbox">
						<option value="latest">{$LANG.email.sample_latest_order|sprintf:$EMAIL_PREVIEW.latest_order}</option>
						<option value="example">{$LANG.email.sample_example_order}</option>
					</select>
				</span>
				{/if}
			</div>
			<div class="email-preview__stage" id="email_template_preview"></div>
		</div>
	</div>
	<div class="email-tools" id="email_tools" data-scope="template" data-live="1" data-template-id="{$TEMPLATE.template_id}" data-label-desktop="{$LANG.email.preview_desktop}" data-label-mobile="{$LANG.email.preview_mobile}" data-label-text="{$LANG.email.preview_plain_text}" data-label-loading="{$LANG.common.loading}&hellip;" data-label-failed="{$LANG.email.preview_failed}">
		<span class="email-tools__status" id="email_tools_status" role="status"></span>
		<span class="email-tools__group">
			{if $EMAIL_PREVIEW.can_edit}<input type="submit" value="{$LANG.common.save}">{/if}{if isset($DISPLAY_DELETE_LINK)} <a href="{$LINK_DELETE}" class="button delete" title="{$LANG.notification.confirm_delete}">{$LANG.common.delete}</a>{/if}
		</span>
	</div>
	<script type="text/javascript" defer src="includes/ace/src-min-noconflict/ace.js?{$VERSION_HASH}" charset="utf-8"></script>
	<script type="text/javascript" defer src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/admin.email.js?{$VERSION_HASH}"></script>
  </div>
  {if isset($TEMPLATE_MACROS)}
  <div id="macros" class="tab_content">
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
  {/if}
  <input type="hidden" name="template[template_id]" value="{$TEMPLATE.template_id}">
  {/if}
  
  {if isset($DISPLAY_DESIGN_FORM)}
  <div id="general" class="tab_content">
	<h3>{$LANG.email.design_title}</h3>
	<p>{$LANG.email.design_intro}</p>
	<div class="email-template">
		<div class="email-design">
			<fieldset{if !$EMAIL_PREVIEW.can_edit} disabled{/if}>
				<legend>{$LANG.email.design_logo}</legend>
				<div class="email-design__choice">
					<label><input type="radio" name="design[logo]" value="image"{if $DESIGN.logo == 'image'} checked="checked"{/if}> {$LANG.email.design_logo_image}</label>
					<label><input type="radio" name="design[logo]" value="text"{if $DESIGN.logo == 'text'} checked="checked"{/if}> {$LANG.email.design_logo_text}</label>
				</div>
				<div class="email-design__choice">
					<label><input type="radio" name="design[align]" value="left"{if $DESIGN.align == 'left'} checked="checked"{/if}> {$LANG.email.design_left}</label>
					<label><input type="radio" name="design[align]" value="center"{if $DESIGN.align == 'center'} checked="checked"{/if}> {$LANG.email.design_centre}</label>
				</div>
				<span class="email-field-help">{$LANG.email.design_logo_help}</span>
			</fieldset>
			<fieldset{if !$EMAIL_PREVIEW.can_edit} disabled{/if}>
				<legend>{$LANG.email.design_colours}</legend>
				<div class="email-design__colour"><input type="color" name="design[accent]" id="design_accent" value="{$DESIGN.accent}"> <label for="design_accent">{$LANG.email.design_accent}</label></div>
				<div class="email-design__colour"><input type="color" name="design[background]" id="design_background" value="{$DESIGN.background}"> <label for="design_background">{$LANG.email.design_background}</label></div>
				<div class="email-design__colour"><input type="color" name="design[panel]" id="design_panel" value="{$DESIGN.panel}"> <label for="design_panel">{$LANG.email.design_panel}</label></div>
				<div class="email-design__colour"><input type="color" name="design[text]" id="design_text" value="{$DESIGN.text}"> <label for="design_text">{$LANG.email.design_text}</label></div>
			</fieldset>
			<fieldset{if !$EMAIL_PREVIEW.can_edit} disabled{/if}>
				<legend>{$LANG.email.design_shape}</legend>
				<div class="email-design__choice">
					<label><input type="radio" name="design[corners]" value="rounded"{if $DESIGN.corners == 'rounded'} checked="checked"{/if}> {$LANG.email.design_rounded}</label>
					<label><input type="radio" name="design[corners]" value="square"{if $DESIGN.corners == 'square'} checked="checked"{/if}> {$LANG.email.design_square}</label>
				</div>
				<div class="email-design__choice">
					<label><input type="radio" name="design[font]" value="sans"{if $DESIGN.font == 'sans'} checked="checked"{/if}> {$LANG.email.design_sans}</label>
					<label><input type="radio" name="design[font]" value="serif"{if $DESIGN.font == 'serif'} checked="checked"{/if}> {$LANG.email.design_serif}</label>
				</div>
			</fieldset>
			<fieldset{if !$EMAIL_PREVIEW.can_edit} disabled{/if}>
				<legend><label for="design_footer">{$LANG.email.design_footer}</label></legend>
				<textarea name="design[footer]" id="design_footer" class="textbox" rows="3" maxlength="500">{$DESIGN.footer|escape:'html'}</textarea>
				<span class="email-field-help">{$LANG.email.design_footer_help}</span>
			</fieldset>
			<fieldset{if !$EMAIL_PREVIEW.can_edit} disabled{/if}>
				<legend>{$LANG.email.design_use}</legend>
				<div class="email-design__choice">
					<input type="hidden" name="design[active]" id="design_active" class="toggle" value="{if $DESIGN_ACTIVE}1{else}0{/if}">
					<label for="design_active">{$LANG.email.design_active}</label>
				</div>
				<span class="email-field-help">{if $DESIGN_ACTIVE}{$LANG.email.design_active_on}{else}{$LANG.email.design_active_off|sprintf:$DESIGN_CURRENT}{/if}</span>
			</fieldset>
		</div>
		<div class="email-template__preview">
			<div class="email-preview__bar">
				<button type="button" class="button email-preview__size is-on" data-width="100%" aria-pressed="true">{$LANG.email.preview_desktop}</button>
				<button type="button" class="button email-preview__size" data-width="375px" aria-pressed="false">{$LANG.email.preview_mobile}</button>
				{if $EMAIL_PREVIEW.latest_order}
				<span class="email-tools__group">
					<label for="email_sample">{$LANG.email.sample_data}</label>
					<select id="email_sample" class="textbox">
						<option value="latest">{$LANG.email.sample_latest_order|sprintf:$EMAIL_PREVIEW.latest_order}</option>
						<option value="example">{$LANG.email.sample_example_order}</option>
					</select>
				</span>
				{/if}
			</div>
			<div class="email-preview__stage" id="email_template_preview"></div>
		</div>
	</div>
	<div class="email-tools" id="email_tools" data-scope="design" data-label-desktop="{$LANG.email.preview_desktop}" data-label-mobile="{$LANG.email.preview_mobile}" data-label-text="{$LANG.email.preview_plain_text}" data-label-loading="{$LANG.common.loading}&hellip;" data-label-failed="{$LANG.email.preview_failed}">
		<span class="email-tools__status" id="email_tools_status" role="status"></span>
		{if $EMAIL_PREVIEW.can_edit}
		<span class="email-tools__group">
			<input type="submit" value="{$LANG.common.save}">
		</span>
		{/if}
	</div>
	<script type="text/javascript" defer src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/admin.email.js?{$VERSION_HASH}"></script>
  </div>
  {/if}

    {if isset($PLUGIN_TABS)}
  {foreach from=$PLUGIN_TABS item=tab}
  {$tab}
  {/foreach}
  {/if}
{include file='templates/element.hook_form_content.php'}

  <div class="form_control">
	<input id="previous-tab" type="hidden" value="" name="previous-tab">
	{if !isset($DISPLAY_CONTENT_FORM) && !isset($DISPLAY_TEMPLATE_FORM) && !isset($DISPLAY_DESIGN_FORM)}
	<input type="submit" value="{$LANG.common.save}">{if isset($DISPLAY_DELETE_LINK)} <a href="{$LINK_DELETE}" class="delete" title="{$LANG.notification.confirm_delete}">{$LANG.common.delete}</a>{/if}
	{/if}
  </div>
</form>