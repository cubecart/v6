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
	  {if isset($EMAIL_TEMPLATE_HTML_JSON)}
	  <script>
		var EMAIL_TEMPLATE_HTML = {$EMAIL_TEMPLATE_HTML_JSON};
		var EMAIL_PREVIEW_MACROS = {if isset($PREVIEW_MACROS_JSON)}{$PREVIEW_MACROS_JSON}{else}{ldelim}{rdelim}{/if};
	  </script>
	  <script>{literal}
		document.addEventListener('DOMContentLoaded', function() {
			$(document).on('click', '.preview-template', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var id = parseInt($btn.attr('data-template-id'), 10);
				var title = $btn.attr('data-template-title') || '';
				var rendered = (EMAIL_TEMPLATE_HTML && EMAIL_TEMPLATE_HTML[id]) ? EMAIL_TEMPLATE_HTML[id] : '';
				rendered = rendered.replace(/\{\$EMAIL_CONTENT\}/g, '<p style="padding:24px;text-align:center;color:#888;font-style:italic;">[Email body preview]</p>');
				Object.keys(EMAIL_PREVIEW_MACROS).forEach(function(key) {
					var pattern = new RegExp('\\{\\$DATA\\.' + key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\}', 'g');
					rendered = rendered.replace(pattern, EMAIL_PREVIEW_MACROS[key]);
				});
				$.colorbox({
					title: title,
					width: '90%',
					height: '90%',
					html: function(){
						return '<iframe width="100%" height="95%" frameBorder="0" srcdoc="<div style=&quot;margin:auto;width:50%;&quot;>'+rendered.replace(/&/g,'&amp;').replace(/"/g,'&quot;')+'</div>"></iframe>';
					}
				});
			});
		});
	  {/literal}</script>
	  {/if}
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
		<td><input type="text" name="content[subject]" id="content_subject" value="{$CONTENT.subject}" class="textbox"></td>
	  </tr>
		<tr>
			<td colspan="2">
				<textarea name="content[content_html]" id="content_html" class="textbox fck fck-full" data-fck-height="500">{$CONTENT.content_html|escape:'html'}</textarea>
			</td>
		</tr>
		<tr>
			<td>
				<button type="button" class="button" id="preview_email_template" onclick="previewEmailTemplate()">{$LANG.common.preview}</button>
			</td>
			<td class="text-right">
				<input type="submit" value="{$LANG.common.save}">{if isset($DISPLAY_DELETE_LINK)} <a href="{$LINK_DELETE}" class="button delete" title="{$LANG.notification.confirm_delete}">{$LANG.common.delete}</a>{/if}	
			</td>
		</tr>
	</table>
	{if isset($DEFAULT_TEMPLATE_JSON)}
	<script>var EMAIL_DEFAULT_TEMPLATE = {$DEFAULT_TEMPLATE_JSON};</script>
	{else}
	<script>var EMAIL_DEFAULT_TEMPLATE = null;</script>
	{/if}
	{if isset($PREVIEW_MACROS_JSON)}
	<script>var EMAIL_PREVIEW_MACROS = {$PREVIEW_MACROS_JSON};</script>
	{else}
	<script>var EMAIL_PREVIEW_MACROS = {};</script>
	{/if}
	<script>{literal}
		function previewEmailTemplate() {
			var content = CKEDITOR.instances.content_html.getData();
			var rendered = EMAIL_DEFAULT_TEMPLATE ? EMAIL_DEFAULT_TEMPLATE.replace(/\{\$EMAIL_CONTENT\}/g, content) : content;
			Object.keys(EMAIL_PREVIEW_MACROS).forEach(function(key) {
				var pattern = new RegExp('\\{\\$DATA\\.' + key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\}', 'g');
				rendered = rendered.replace(pattern, EMAIL_PREVIEW_MACROS[key]);
			});
			$.colorbox({
				title: document.getElementById('content_subject').value,
				width: '90%',
				height: '90%',
				html: function(){
					return '<iframe width="100%" height="95%" frameBorder="0" srcdoc="<div style=&quot;margin:auto;width:50%;&quot;>'+rendered.replace(/&/g,'&amp;').replace(/"/g,'&quot;')+'</div>"></iframe>';
				}
			});
		};
	{/literal}</script>
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
		<tr>
			<td colspan="2">
				<textarea name="template[content_html]" id="template_content_html" class="textbox fck fck-full" data-fck-height="500">{$TEMPLATE.content_html|escape:'html'}</textarea>
			</td>
		</tr>
		<tr>
			<td>
				<button type="button" class="button" id="preview_email_template" onclick="previewEmailTemplate()">{$LANG.common.preview}</button>
			</td>
			<td class="text-right">
				<input type="submit" value="{$LANG.common.save}">{if isset($DISPLAY_DELETE_LINK)} <a href="{$LINK_DELETE}" class="button delete" title="{$LANG.notification.confirm_delete}">{$LANG.common.delete}</a>{/if}
			</td>
		</tr>
	</table>
	{if isset($PREVIEW_MACROS_JSON)}
	<script>var EMAIL_PREVIEW_MACROS = {$PREVIEW_MACROS_JSON};</script>
	{else}
	<script>var EMAIL_PREVIEW_MACROS = {};</script>
	{/if}
	<script>{literal}
		function previewEmailTemplate() {
			var rendered = CKEDITOR.instances.template_content_html.getData();
			rendered = rendered.replace(/\{\$EMAIL_CONTENT\}/g, '<p style="padding:1em;border:2px dashed #999;color:#666;text-align:center;">[ Email content will appear here ]</p>');
			Object.keys(EMAIL_PREVIEW_MACROS).forEach(function(key) {
				var pattern = new RegExp('\\{\\$DATA\\.' + key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\}', 'g');
				rendered = rendered.replace(pattern, EMAIL_PREVIEW_MACROS[key]);
			});
			$.colorbox({
				title: document.getElementById('template_desc').value,
				width: '90%',
				height: '90%',
				html: function(){
					return '<iframe width="100%" height="95%" frameBorder="0" srcdoc="<div style=&quot;margin:auto;width:50%;&quot;>'+rendered.replace(/&/g,'&amp;').replace(/"/g,'&quot;')+'</div>"></iframe>';
				}
			});
		};
	{/literal}</script>
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