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
<form action="{$VAL_SELF}" method="post" id="cat_form" name="cat_form" enctype="multipart/form-data">
  {if $LIST_CATEGORIES}
  <div id="categories" class="tab_content">
	<h3>{$LANG.settings.title_category}</h3>
	<table>
	  <thead>
		<tr>
		  <td width="15" align="center">{$LANG.settings.category_id}</td>
		  <td>{$LANG.common.arrange}</td>
		  <td>{$LANG.common.visible}</td>
		  <td>{$LANG.common.status}</td>
		  <td>{$LANG.settings.category_name}</td>
		  <td>{$LANG.translate.title_translations}</td>
		  <td>&nbsp;</td>
		</tr>
	  </thead>
	  <tbody class="reorder-list">
	  
	  {foreach from=$CATEGORIES item=category}
	    <tr>
	      <td style="text-align:center">
	        <strong>{$category.cat_id}</strong>
	      </td>
	      <td style="text-align:center">
	        <a href="#" class="handle"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/updown.gif" title="{$LANG.ui.drag_reorder}"></a>
	        <input type="hidden" name="order[]" value="{$category.cat_id}">
	      </td>
	      <td style="text-align:center">
	        <input type="hidden" name="visible[{$category.cat_id}]" id="catv_{$category.cat_id}" value="{$category.visible}" class="toggle">
	      </td>
	      <td style="text-align:center">
	        <input type="hidden" name="status[{$category.cat_id}]" id="cat_{$category.cat_id}" value="{$category.status}" class="toggle">
	      </td>
	      <td>
			{if $category.no_children}
		    <a href="{$category.children}" title="{$category.alt_text}">{$category.cat_name}</a>
		    {else}
		    {$category.cat_name}
		    {/if}
	      </td>
	      <td style="text-align:center" class="language_list">
	    	{foreach from=$category.translations item=translation}
	  	    <a href="{$translation.edit}"><img src="language/flags/{$translation.language}.png" alt="{$translation.language}" title="{$translation.language}" class="flag"></a>
	  	    {/foreach}
	      </td>
	      <td>
	      	<a href="?_g=products&cat_id={$category.cat_id}" title="{$LANG.dashboard.inv_products}"><i class="fa fa-search" title="{$LANG.dashboard.inv_products}"></i></a>
		    <a href="{$category.translate}" title="{$LANG.translate.trans_add}"><i class="fa fa-plus-circle" title="{$LANG.translate.trans_add}"></i></a>
		    <a href="{$category.edit}" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
		    <a href="{$category.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
	      </td>
	    </tr>
	    {foreachelse}
	    <tr>
	      <td colspan="6" align="center"><strong>{$LANG.form.none}</strong></td>
	    </tr>
	    {/foreach}
	  </tbody>
    </table>
  </div>
  {/if}

  {if isset($MODE_ADDEDIT)}
  <div id="cat_general" class="tab_content">
	<h3>{$LANG.settings.title_category_details}</h3>
	<fieldset><legend>{$LANG.common.general}</legend>
	  <div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="cat[status]" id="status" value="{$CATEGORY.status}" class="toggle"></span></div>
	  <div><label for="visible">{$LANG.common.visible}</label><span><input type="hidden" name="cat[visible]" id="visible" value="{$CATEGORY.visible}" class="toggle"></span></div>
	  <div><label for="name">{$LANG.settings.category_name}</label><span><input type="text" name="cat[cat_name]" {if !empty($CATEGORY.cat_name)}id="cat_name"{else}id="name"{/if} class="textbox required" value="{$CATEGORY.cat_name}"></span></div>
	  <div><label for="parent">{$LANG.settings.category_parent}</label><span><select name="cat[cat_parent_id]" id="parent" class="textbox">
	  {foreach from=$SELECT_CATEGORIES item=category}
	  <option value="{$category.id}"{$category.selected}>{$category.display}</option>
	  {/foreach}
	  </select></span></div>
	</fieldset>
  </div>
  <div id="cat_description" class="tab_content">
	<h3>{$LANG.settings.title_description}</h3>
	<textarea name="cat[cat_desc]" id="description" class="textbox fck">{$CATEGORY.cat_desc|escape:"html"}</textarea>
	</div>
	<div id="cat_images" class="tab_content">
		<h3>{$LANG.settings.category_images}</h3>
		<div class="fm-container">
			<div class="loading">{$LANG.common.loading} <i class="fa fa-spinner fa-spin fa-fw"></i></div>
			<div id="imageset" rel="1" class="fm-filelist unique"></div>
			<div class="master_image">
				<span>{$LANG.catalogue.image_main}</span>:<br><br>
				<div id="master_image_block">
					<img src="{$CATEGORY.master_image}" id="master_image_preview"><div id="preview_image"><img src="{$CATEGORY.master_image}"></div>
				</div>
			</div>
		</div>
		<div class="dropzone">
			<div class="dz-default dz-message"><span>{$LANG.filemanager.file_upload_note}</span></div>
		</div>
		<div id="dropzone_url" style="display: none;">?_g=filemanager&amp;cat_id={$CATEGORY.cat_id}</div>
		<div id="val_cat_id" style="display: none;">{$CATEGORY.cat_id}</div>
		<div id="val_lang_go" style="display: none;">{$LANG.common.go}</div>
		<div id="val_lang_preview" style="display: none;">{$LANG.common.preview}</div>
		<div id="val_lang_main_image" style="display: none;">{$LANG.catalogue.image_main}</div>
		<div id="val_lang_show_assigned" style="display: none;">{$LANG.filemanager.show_assigned}</div>
		<div id="val_lang_show_all" style="display: none;">{$LANG.filemanager.show_all}</div>
		<div id="val_lang_folder_create" style="display: none;">{$LANG.filemanager.folder_create}:</div>
		<div id="val_lang_refresh_files" style="display: none;">{$LANG.filemanager.refresh_files}</div>
		<div id="val_lang_upload_destination" style="display: none;">{$LANG.filemanager.upload_destination}:</div>
		<div id="val_lang_enable" style="display: none;">{$LANG.common.enable}</div>
		<div id="val_lang_disable" style="display: none;">{$LANG.common.disable}</div>
	</div>
  <div id="seo" class="tab_content">
  <h3>{$LANG.settings.title_seo}</h3>
    <fieldset>
	  <div><label for="seo_meta_title">{$LANG.settings.seo_meta_title}</label><span><input type="text" name="cat[seo_meta_title]" id="seo_meta_title" class="textbox" value="{$CATEGORY.seo_meta_title}"></span></div>
	  <div><label for="seo_path">{$LANG.settings.seo_path} *</label><span><input type="text" name="seo_path" id="seo_path" class="textbox" value="{$CATEGORY.seo_path}"></span></div>
	  <div><label for="seo_meta_description">{$LANG.settings.seo_meta_description}</label><span><textarea name="cat[seo_meta_description]" id="seo_meta_description" class="textbox">{$CATEGORY.seo_meta_description}</textarea></span></div>
	</fieldset>
	<p>* {$LANG.settings.seo_path_auto}</p>
	{include file='templates/element.redirects.php'}
  </div>
	{if isset($DISPLAY_SHIPPING)}
  <div id="cat_shipping" class="tab_content">
	<h3>{$LANG.settings.title_shipping}</h3>
	<fieldset><legend>{$LANG.settings.title_shipping_costs}</legend>
	  <div><label for="per_ship">{$LANG.settings.ship_per_order}</label><span><input name="cat[per_ship]" value="{$CATEGORY.per_ship}" type="text" class="textbox" size="6"></span></div>
  	  <div><label for="item_ship">{$LANG.settings.ship_per_item}</label><span><input name="cat[item_ship]" value="{$CATEGORY.item_ship}" type="text" class="textbox" size="6"></span></div>
  	  <div><label for="per_int_ship">{$LANG.settings.ship_per_order_intl}</label><span><input name="cat[per_int_ship]" value="{$CATEGORY.per_int_ship}" type="text" class="textbox" size="6"></span></div>
      <div><label for="item_int_ship">{$LANG.settings.ship_per_item_intl}</label><span><input name="cat[item_int_ship]" value="{$CATEGORY.item_int_ship}" type="text" class="textbox" size="6"></span></div>
    </fieldset>
  </div>
	{/if}
  <input type="hidden" name="cat[cat_id]" value="{$CATEGORY.cat_id}">
	{if $DISPLAY_TRANSLATIONS}
  <div id="cat_translate" class="tab_content">
	<h3>{$LANG.translate.title_translate}</h3>
	<fieldset><legend>{$LANG.translate.title_translations}</legend>
	  {if isset($TRANSLATIONS)}
	  {foreach from=$TRANSLATIONS item=translation}
	  <div>
		<span class="actions">
		  <a href="{$translation.edit}" class="edit" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
		  <a href="{$translation.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
		</span>
		<input type="hidden" name="" id="">
		<a href="{$translation.edit}" title="{$translation.cat_name} - ({$translation.language})"><img src="language/flags/{$translation.language}.png" alt="{$translation.langauge}"></a> - <a href="{$translation.edit}">{$translation.cat_name}</a>
	  </div>
	  {/foreach}
	  {else}
	  <div>{$LANG.translate.trans_none}</div>
	  {/if}
	</fieldset>
	<div><a href="{$TRANSLATE}">{$LANG.translate.trans_add}</a></div>
  </div>
  {/if}
  {if isset($PLUGIN_TABS)}
	{foreach from=$PLUGIN_TABS item=tab}
		{$tab}
	{/foreach}
  {/if}  
  {/if}

  {if $MODE_TRANSLATE}
  <div id="general" class="tab_content">
	<fieldset><legend>{$LANG.common.general}</legend>
	  <div><label for="trans_name">{$LANG.settings.category_name}</label><span><input type="text" name="translate[cat_name]" id="trans_name" value="{$TRANS.cat_name}" class="textbox required" required="required"></span></div>
	  <div><label for="trans_lang">{$LANG.common.language}</label><span><select name="translate[language]" id="trans_lang" class="textbox">
	  {foreach from=$LANGUAGES item=lang}<option value="{$lang.code}"{$lang.selected}>{$lang.title}</option>{/foreach}
	  </select></span></div>
	</fieldset>
  </div>
  <div id="description" class="tab_content">
	<textarea name="translate[cat_desc]" class="textbox fck">{$TRANS.cat_desc|escape:"html"}</textarea>
  </div>
  <div id="seo" class="tab_content">
  <h3>{$LANG.settings.title_seo}</h3>
  <fieldset>
	  <div><label for="seo_meta_title">{$LANG.settings.seo_meta_title}</label><span><input type="text" name="translate[seo_meta_title]" id="seo_meta_title" class="textbox" value="{$TRANS.seo_meta_title}"></span></div>
	  <div><label for="seo_path">{$LANG.settings.seo_path}</label><span><input type="text" name="seo_path" id="seo_path" class="textbox" value="{$TRANS.seo_path}"></span></div>
	  <div><label for="seo_meta_description">{$LANG.settings.seo_meta_description}</label><span><textarea name="translate[seo_meta_description]" id="seo_meta_description" class="textbox">{$TRANS.seo_meta_description}</textarea></span></div>
	</fieldset>
  </div>
  <input type="hidden" name="cat_id" value="{$TRANS.cat_id}">
  <input type="hidden" name="translation_id" value="{$TRANS.translation_id}">
  {/if}

  {include file='templates/element.hook_form_content.php'}
  
  <div class="form_control">
	<input type="hidden" name="previous-tab" id="previous-tab" value="">
	<input type="submit" id="cat_save" value="{$LANG.common.save}" class="button">
  </div>
  
  {if !empty($CATEGORY.cat_name)}
  <input type="hidden" name="gen_seo" id="gen_seo" value="0">
  <div id="dialog-seo" title="{$LANG.settings.seo_rebuild}" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{$LANG.settings.seo_rebuild_description}</p>
  </div>
  {/if}
</form>
