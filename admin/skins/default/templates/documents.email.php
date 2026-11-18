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
				<td><a href="{$template.edit}">{$template.title}</a></td>
				<td width="10"><a href="#" class="preview-template" data-template-id="{$template.template_id}" data-template-title="{$template.title|escape:'html'}" title="{$LANG.common.preview}"><i class="fa fa-search"></i></a></td>
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
	<table class="nostripe">
	  <tr>
		<td><label for="content_subject">{$LANG.common.subject}</label></td>
		<td><input type="text" name="content[subject]" id="content_subject" value="{$CONTENT.subject|escape:'html'}" class="textbox email-line-field" maxlength="250"></td>
	  </tr>
	  <tr>
		<td><label for="content_preheader">{$LANG.email.preview_text}</label></td>
		<td><input type="text" name="content[preheader]" id="content_preheader" value="{$CONTENT.preheader|escape:'html'}" class="textbox email-line-field" maxlength="255" aria-describedby="content_preheader_help"><span id="content_preheader_help" class="email-field-help">{$LANG.email.preview_text_help}</span></td>
	  </tr>
		<tr>
			<td colspan="2">
				<textarea name="content[content_html]" id="content_html" class="textbox fck fck-keep-image-size" data-fck-height="500">{$CONTENT.content_html|escape:'html'}</textarea>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<div class="email-tools" id="email_tools" data-scope="content" data-content-id="{$CONTENT.content_id}" data-content-type="{$CONTENT.content_type}" data-label-desktop="{$LANG.email.preview_desktop}" data-label-mobile="{$LANG.email.preview_mobile}" data-label-text="{$LANG.email.preview_plain_text}" data-label-loading="{$LANG.common.loading}&hellip;" data-label-failed="{$LANG.email.preview_failed}">
					<button type="button" class="button" id="email_preview_button">{$LANG.common.preview}</button>
					{if $EMAIL_PREVIEW.latest_order}
					<span class="email-tools__group">
						<label for="email_sample">{$LANG.email.sample_data}</label>
						<select id="email_sample" class="textbox">
							<option value="latest">{$LANG.email.sample_latest_order|sprintf:$EMAIL_PREVIEW.latest_order}</option>
							<option value="example">{$LANG.email.sample_example_order}</option>
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
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="text-right">
				<input type="submit" value="{$LANG.common.save}">{if isset($DISPLAY_DELETE_LINK)} <a href="{$LINK_DELETE}" class="button delete" title="{$LANG.notification.confirm_delete}">{$LANG.common.delete}</a>{/if}
			</td>
		</tr>
	</table>
	<script type="text/javascript" defer src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/admin.email.js?{$VERSION_HASH}"></script>
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
		<td><input type="text" name="template[title]" id="template_desc" value="{$TEMPLATE.title}" class="textbox required"></td>
	  </tr>
	</table>
	{* A whole HTML document with Smarty in it is edited as code. A visual editor cannot show
	   its <head> and has to rebuild the document to display it, which is how #4738 happened. *}
	<div class="email-template">
		<div class="email-template__code">
			<div id="template_ace" class="ace_email_editor" hidden></div>
			<textarea name="template[content_html]" id="template_content_html" class="textbox email-template__source" spellcheck="false" aria-label="{$LANG.email.title_content_html}">{$TEMPLATE.content_html|escape:'html'}</textarea>
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
			<input type="submit" value="{$LANG.common.save}">{if isset($DISPLAY_DELETE_LINK)} <a href="{$LINK_DELETE}" class="button delete" title="{$LANG.notification.confirm_delete}">{$LANG.common.delete}</a>{/if}
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
  
    {if isset($PLUGIN_TABS)}
  {foreach from=$PLUGIN_TABS item=tab}
  {$tab}
  {/foreach}
  {/if}
{include file='templates/element.hook_form_content.php'}

  <div class="form_control">
	<input id="previous-tab" type="hidden" value="" name="previous-tab">
	{if !isset($DISPLAY_CONTENT_FORM) && !isset($DISPLAY_TEMPLATE_FORM)}
	<input type="submit" value="{$LANG.common.save}">{if isset($DISPLAY_DELETE_LINK)} <a href="{$LINK_DELETE}" class="delete" title="{$LANG.notification.confirm_delete}">{$LANG.common.delete}</a>{/if}
	{/if}
  </div>
</form>