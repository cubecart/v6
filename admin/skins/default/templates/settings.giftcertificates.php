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
	{if isset($TAXES)}
	<div><label for="tax-type">{$LANG.catalogue.tax_type}</label><span><select name="gc[taxType]" id="tax-type" class="textbox">
	  {foreach from=$TAXES item=tax}<option value="{$tax.id}" {$tax.selected}>{$tax.tax_name}</option>{/foreach}
	</select></span></div>
	{/if}
	</fieldset>
  </div>
    <div id="gift_images" class="tab_content">
	<h3>{$LANG.settings.gc_images}</h3>
	<div class="fm-container">
		<div class="loading">{$LANG.common.loading} <i class="fa fa-spinner fa-spin fa-fw"></i></div>
		<div id="imageset" rel="1" class="fm-filelist unique"></div>
		<div class="master_image">
			<span>{$LANG.catalogue.image_main}</span>:<br><br>
			<div id="master_image_block">
				<img src="{$CATEGORY.master_image}" id="master_image_preview"><div id="preview_image"><img src="{$GC.master_image}"></div>
			</div>
		</div>
		
	</div>
	<div class="cc_dropzone">
		<div class="dz-default dz-message"><span>{$LANG.filemanager.file_upload_note}</span></div>
	</div>
	<div id="cc_dropzone_url" style="display: none;">?_g=filemanager&amp;gc=1</div>
	<div id="val_unique_image" style="display: none;">{$GC.image}</div>
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
  {include file='templates/element.hook_form_content.php'}
  
  <div class="form_control">
	<input type="hidden" name="save" value="{$FORM_HASH}">
	<input type="hidden" name="previous-tab" id="previous-tab" value="">
	<input type="submit" value="{$LANG.common.save}">
  </div>
  
</form>