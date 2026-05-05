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
  <div id="Certificates" class="tab_content">
	<h3>{$LANG.catalogue.gift_certificates}</h3>
	<fieldset><legend>{$LANG.settings.gc_settings_legend}</legend>
	<div><label for="status">{$LANG.common.status}</label><span><select name="gc[status]" id="status" class="textbox">
	  {foreach from=$OPT_STATUS item=option}<option value="{$option.value}" {$option.selected}>{$option.title}</option>{/foreach}
	</select></span></div>
	<div><label for="amount-min">{$LANG.settings.gc_value_min}</label><span><input type="text" name="gc[min]" id="amount-min" value="{$GC.min}" class="textbox number required"></span></div>
	<div><label for="amount-max">{$LANG.settings.gc_value_max}</label><span><input type="text" name="gc[max]" id="amount-max" value="{$GC.max}" class="textbox number required"></span></div>
	<div><label for="expires">{$LANG.settings.gc_expiry}</label><span><input type="text" name="gc[expires]" id="expires" value="{$GC.expires}" class="textbox number"> {$LANG.common.blank_for_no_expire}</span></div>
	<div><label for="delivery">{$LANG.catalogue.delivery_method}</label><span><select name="gc[delivery]" id="delivery" class="textbox">
	  {foreach from=$OPT_DELIVERY item=option}<option value="{$option.value}" {$option.selected}>{$option.title}</option>{/foreach}
	</select></span></div>
	<div><label for="weight">{$LANG.settings.gc_weight}</label><span><input type="text" name="gc[weight]" id="weight" value="{$GC.weight}" class="textbox number"></span></div>
	<div><label for="product-code">{$LANG.catalogue.product_code}</label><span><input type="text" name="gc[product_code]" id="product-code" value="{$GC.product_code}" class="textbox"></span></div>
	</fieldset>
  </div>
    <div id="gift_images" class="tab_content">
	<h3>{$LANG.settings.gc_images}</h3>
	{include file='templates/element.image_picker.php'
	   single=true
	   storage_key="gc"
	   dropzone_url="?_g=filemanager&gc=1"
	   initial_json=$IMAGE_PICKER_JSON
	   placeholder=$IMG_PICKER_PLACEHOLDER
	   hint="Click an image to select it as the gift-certificate image."}
  </div>
  <div id="seo" class="tab_content">
	<h3>{$LANG.settings.title_seo}</h3>
	<fieldset><legend>{$LANG.settings.title_seo_meta_data}</legend>
	  <div><label for="seo_meta_title">{$LANG.settings.seo_meta_title}</label><span><input type="text" name="gc[seo_meta_title]" id="seo_meta_title" value="{$GC.seo_meta_title|escape:'html'}" class="textbox strlen" rel="seo_meta_title_strlen"></span> <span id="seo_meta_title_strlen">{if $GC.seo_meta_title}{strlen($GC.seo_meta_title)}{/if}</span></div>
	  <div><label for="seo_meta_description">{$LANG.settings.seo_meta_description}</label><span><textarea name="gc[seo_meta_description]" id="seo_meta_description" class="textbox strlen" rel="seo_meta_description_strlen">{$GC.seo_meta_description|escape:'html'}</textarea></span> <span id="seo_meta_description_strlen">{if $GC.seo_meta_description}{strlen($GC.seo_meta_description)}{/if}</span></div>
	</fieldset>
  </div>
    {if isset($PLUGIN_TABS)}
  {foreach from=$PLUGIN_TABS item=tab}
  {$tab}
  {/foreach}
  {/if}
{include file='templates/element.hook_form_content.php'}
  
  <div class="form_control">
	<input type="hidden" name="previous-tab" id="previous-tab" value="">
	<input type="submit" value="{$LANG.common.save}">
  </div>
  
</form>