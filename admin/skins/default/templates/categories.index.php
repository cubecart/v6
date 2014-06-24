<form action="{$VAL_SELF}" method="post" id="cat_form" name="cat_form" enctype="multipart/form-data">
  {if $LIST_CATEGORIES}
  <div id="categories" class="tab_content">
	<h3>{$LANG.settings.title_category}</h3>
	<table class="list">
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
	  {if isset($CATEGORIES)}
	  {foreach from=$CATEGORIES item=category}
	    <tr>
	      <td align="center">
	        <strong>{$category.cat_id}</strong>
	      </td>
	      <td align="center">
	        <a href="#" class="handle"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/updown.gif" title="{$LANG.ui.drag_reorder}"></a>
	        <input type="hidden" name="order[]" value="{$category.cat_id}">
	      </td>
	      <td align="center">
	        <input type="hidden" name="visible[{$category.cat_id}]" id="catv_{$category.cat_id}" value="{$category.visible}" class="toggle">
	      </td>
	      <td align="center">
	        <input type="hidden" name="status[{$category.cat_id}]" id="cat_{$category.cat_id}" value="{$category.status}" class="toggle">
	      </td>
	      <td>
			{if $category.no_children}
		    <a href="{$category.children}" title="{$category.alt_text}">{$category.cat_name}</a>
		    {else}
		    {$category.cat_name}
		    {/if}
	      </td>
	      <td align="center">
	    	{foreach from=$category.translations item=translation}
	  	    <a href="{$translation.edit}"><img src="language/flags/{$translation.language}.png" alt="{$translation.language}" title="{$translation.language}"></a>
	  	    {/foreach}
	      </td>
	      <td>
		    <a href="{$category.translate}" title="{$LANG.translate.trans_add}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/add.png" alt="{$LANG.translate.trans_add}"></a>
		    <a href="{$category.edit}" title="{$LANG.common.edit}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/edit.png" alt="{$LANG.common.edit}"></a>
		    <a href="{$category.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a>
	      </td>
	    </tr>
	    {/foreach}
	    {else}
	    <tr>
	      <td colspan="6" align="center"><strong>{$LANG.form.none}</strong></td>
	    </tr>
	    {/if}
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
	<textarea name="cat[cat_desc]" id="description" class="textbox fck">{$CATEGORY.cat_desc}</textarea>
  </div>
  <div id="cat_images" class="tab_content">
	<h3>{$LANG.settings.category_images}</h3>
	<div class="fm-container">
	  <div id="image" rel="1" class="fm-filelist unique"></div>
	</div>
	<p>{$LANG.filemanager.file_upload_note}</p>
	<div><label for="uploader">{$LANG.filemanager.file_upload}</label><span><input name="image" id="uploader" type="file"></span></div>
	<script type="text/javascript">
	var file_list = {$JSON_IMAGES}
	</script>
  </div>
  <div id="seo" class="tab_content">
  <h3>{$LANG.settings.title_seo}</h3>
    <fieldset>
	  <div><label for="seo_meta_title">{$LANG.settings.seo_meta_title}</label><span><input type="text" name="cat[seo_meta_title]" id="seo_meta_title" class="textbox" value="{$CATEGORY.seo_meta_title}"></span></div>
	  <div><label for="seo_path">{$LANG.settings.seo_path}</label><span><input type="text" name="seo_path" id="seo_path" class="textbox" value="{$CATEGORY.seo_path}"></span></div>
	  <div><label for="seo_meta_keywords">{$LANG.settings.seo_meta_keywords}</label><span><textarea name="cat[seo_meta_keywords]" id="seo_meta_keywords" class="textbox">{$CATEGORY.seo_meta_keywords}</textarea></div>
	  <div><label for="seo_meta_description">{$LANG.settings.seo_meta_description}</label><span><textarea name="cat[seo_meta_description]" id="seo_meta_description" class="textbox">{$CATEGORY.seo_meta_description}</textarea></span></div>
	</fieldset>
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
	<fieldset class="list"><legend>{$LANG.translate.title_translations}</legend>
	  {if isset($TRANSLATIONS)}
	  {foreach from=$TRANSLATIONS item=translation}
	  <div>
		<span class="actions">
		  <a href="{$translation.edit}" class="edit" title="{$LANG.common.edit}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/edit.png" alt="{$LANG.common.edit}"></a>
		  <a href="{$translation.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a>
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
  {/if}

  {if $MODE_TRANSLATE}
  <div id="general" class="tab_content">
	<fieldset><legend>{$LANG.common.general}</legend>
	  <div><label for="trans_name">{$LANG.settings.category_name}</label><span><input type="text" name="translate[cat_name]" id="trans_name" value="{$TRANS.cat_name}" class="textbox"></span></div>
	  <div><label for="trans_lang">{$LANG.common.language}</label><span><select name="translate[language]" id="trans_lang" class="textbox">
	  {foreach from=$LANGUAGES item=lang}<option value="{$lang.code}"{$lang.selected}>{$lang.title}</option>{/foreach}
	  </select></span></div>
	</fieldset>
  </div>
  <div id="description" class="tab_content">
	<textarea name="translate[cat_desc]" class="textbox fck">{$TRANS.cat_desc}</textarea>
  </div>
  <div id="seo" class="tab_content">
  <h3>{$LANG.settings.title_seo}</h3>
  <fieldset>
	  <div><label for="seo_meta_title">{$LANG.settings.seo_meta_title}</label><span><input type="text" name="translate[seo_meta_title]" id="seo_meta_title" class="textbox" value="{$TRANS.seo_meta_title}"></span></div>
	  <div><label for="seo_path">{$LANG.settings.seo_path}</label><span><input type="text" name="seo_path" id="seo_path" class="textbox" value="{$TRANS.seo_path}"></span></div>
	  <div><label for="seo_meta_keywords">{$LANG.settings.seo_meta_keywords}</label><span><textarea name="translate[cat_meta_keywords]" id="seo_meta_keywords" class="textbox">{$TRANS.seo_meta_keywords}</textarea></div>
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
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
  {if !empty($CATEGORY.cat_name)}
  <input type="hidden" name="gen_seo" id="gen_seo" value="0">
  <div id="dialog-seo" title="{$LANG.settings.seo_rebuild}" style="display:none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{$LANG.settings.seo_rebuild_description}</p>
  </div>
  {/if}
</form>