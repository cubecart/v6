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
<form action="{$VAL_SELF}" method="post">
  {if isset($DISPLAY_DOCUMENT_LIST)}
  <div id="overview" class="tab_content">
	<h3>{$LANG.documents.title_documents}</h3>
	<table>
	  <thead>
		<tr>
		  <td>{$LANG.common.arrange}</td>
		  <td>{$LANG.common.status}</td>
		  <td>{$LANG.documents.language_primary}</td>
		  <td width="200">{$LANG.documents.document_title}</td>
		  <td>{$LANG.translate.title_translations}</td>
		  <td>{$LANG.documents.document_terms}</td>
		  <td>{$LANG.documents.document_homepage}</td>
			<td>{$LANG.documents.document_privacy}</td>
		  <td>&nbsp;</td>
		</tr>
	  </thead>
	  <tbody class="reorder-list">
	  {if isset($DOCUMENTS)}
	  {foreach from=$DOCUMENTS item=document}
		<tr>
		  <td style="text-align:center"><a href="#" class="handle"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/updown.gif" title="{$LANG.ui.drag_reorder}"></a></td>
		  <td style="text-align:center">
			<input type="hidden" name="order[]" value="{$document.doc_id}">
			<input type="hidden" id="status-{$document.doc_id}" name="status[{$document.doc_id}]" value="{$document.doc_status}" class="toggle">
		  </td>
		  <td style="text-align:center"><img src="{$document.flag}"></td>
		  <td><a href="{$document.link.edit}"{if $document.hide_title==1} class="line-through"{/if}>{$document.doc_name}</a></td>
		  <td style="text-align:center" nowrap="nowrap">
			{if isset($document.translations)}
			{foreach from=$document.translations item=translation}
			<a href="{$translation.link.edit}" class="language_list"><img src="language/flags/{$translation.doc_lang}.png" alt="{$translation.doc_lang}" title="{$translation.doc_lang}" class="flag"></a>
			{/foreach}
			{/if}
		  </td>
		  <td style="text-align:center"><input type="radio" name="terms" value="{$document.doc_id}" {$document.terms}></td>
		  <td style="text-align:center"><input type="radio" name="home" value="{$document.doc_id}" {$document.homepage}></td>
			<td style="text-align:center"><input type="radio" name="privacy" value="{$document.doc_id}" {$document.privacy}></td>
		  <td style="text-align:center">
			<a href="{$document.link.translate}" title="{$LANG.translate.trans_add}"><i class="fa fa-plus-circle" title="{$LANG.translate.trans_add}"></i></a>
			<a href="{$document.link.edit}" title="{$LANG.common.edit}" class="edit"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
			<a href="{$document.link.delete}" title="{$LANG.notification.confirm_delete}" class="delete"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
		  </td>
		</tr>
	  {/foreach}
	  {/if}
	  </tbody>
	</table>
  </div>
  {/if}

  {if isset($DISPLAY_FORM)}
  <div id="general" class="tab_content">
	<h3>{$ADD_EDIT_DOCUMENT}</h3>
	<fieldset><legend>{$LANG.common.general}</legend>
	  <div><label for="doc-name">{$LANG.documents.document_title}</label><span><input type="text" name="document[doc_name]" id="doc-name" value="{$DOCUMENT.doc_name}" class="textbox required"></span></div>
	  <div><label for="doc-lang">{$LANG.common.language}</label><span><select name="document[doc_lang]" id="doc-lang" class="textbox">
		{foreach from=$LANGUAGES item=language}<option value="{$language.code}"{$language.selected}>{$language.title}</option>{/foreach}
	  </select></span></div>
		<div><label for="doc-status">{$LANG.common.status}</label><span><input type="hidden" id="doc_status" name="document[doc_status]" value="{$DOCUMENT.doc_status}" class="toggle"></span></div>
		<div><label for="doc-hide_title">{$LANG.documents.hide_title}</label><span><input type="hidden" id="doc-hide_title" name="document[hide_title]" value="{$DOCUMENT.hide_title}" class="toggle"></span></div>
	  <div><label for="doc-url">{$LANG.documents.document_url}</label><span><input type="text" name="document[doc_url]" id="doc-url" value="{$DOCUMENT.doc_url}" class="textbox"></span></div>
	  <div><label for="doc-url-openin">{$LANG.documents.document_url_open}</label><span><select name="document[doc_url_openin]" id="doc-url-openin" class="textbox">
		{foreach from=$TARGETS item=target}<option value="{$target.value}"{$target.selected}>{$target.title}</option>{/foreach}
	  </select></span></div>
	  <div><label for="doc-navigation_link">{$LANG.documents.navigation_link}</label><span><input type="hidden" id="doc_navigation_link" name="document[navigation_link]" value="{$DOCUMENT.navigation_link}" class="toggle"></span></div>
	  <div><label for="doc_parse">{$LANG.documents.smarty_parse}</label><span><input type="hidden" id="doc_parse" name="document[doc_parse]" value="{if !isset($DOCUMENT.doc_parse)}0{else}{$DOCUMENT.doc_parse}{/if}" class="toggle"></span></div>
	  <input type="hidden" name="document[doc_parent_id]" value="{$DOCUMENT.doc_parent_id}">
	  <input type="hidden" name="document[doc_id]" value="{$DOCUMENT.doc_id}">
	</fieldset>
  </div>

  <div id="article" class="tab_content">
	<h3>{$ADD_EDIT_DOCUMENT}</h3>
	<textarea name="document[doc_content]" id="doc-content" class="textbox fck">{$DOCUMENT.doc_content|escape:"html"}</textarea>
  </div>

  <div id="seo" class="tab_content">
	<h3>{$LANG.settings.title_seo}</h3>
	<fieldset><legend>{$LANG.settings.title_seo_meta_data}</legend>
	  <div><label for="seo_meta_title">{$LANG.settings.seo_meta_title}</label><span><input type="text" name="document[seo_meta_title]" id="seo_meta_title" value="{$DOCUMENT.seo_meta_title}" class="textbox"></span></div>
	  <div><label for="seo_path">{$LANG.settings.seo_path} *</label><span><input name="seo_path" id="seo_path" class="textbox" type="text" value="{$DOCUMENT.seo_path}"></span></div>
	  <div><label for="seo_meta_description">{$LANG.settings.seo_meta_description}</label><span><textarea name="document[seo_meta_description]" id="seo_meta_description" class="textbox">{$DOCUMENT.seo_meta_description}</textarea></span></div>
	</fieldset>
	<p>* {$LANG.settings.seo_path_auto}</p>
	{include file='templates/element.redirects.php'}
  </div>
  {/if}
  {if isset($PLUGIN_TABS)}
    {foreach from=$PLUGIN_TABS item=tab}
      {$tab}
    {/foreach}
  {/if}
  {include file='templates/element.hook_form_content.php'}

  <div class="form_control">
	<input type="submit" value="{$LANG.common.save}">
	{if $DISPLAY_DELETE}&nbsp; <a href="{$DOCUMENT.link.delete}" class="delete" title="{$LANG.notification.confirm_delete}">{$LANG.documents.document_delete}</a>{/if}
  </div>
  
</form>