<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="Certificates" class="tab_content">
	<h3>{$LANG.catalogue.gift_certificates}</h3>
	<fieldset><legend>{$LANG.settings.gc_settings_legend}</legend>
	<div><label for="status">{$LANG.common.status}</label><span><select name="gc[status]" id="status" class="textbox">
	  {foreach from=$OPT_STATUS item=option}<option value="{$option.value}" {$option.selected}>{$option.title}</option>{/foreach}
	</select></span></div>
	<div><label for="amount-min">{$LANG.settings.gc_value_min}</label><span><input type="text" name="gc[min]" id="amount-min" value="{$GC.min}" class="textbox number"></span></div>
	<div><label for="amount-max">{$LANG.settings.gc_value_max}</label><span><input type="text" name="gc[max]" id="amount-max" value="{$GC.max}" class="textbox number"></span></div>
	<div><label for="expires">{$LANG.settings.gc_expiry}</label><span><input type="text" name="gc[expires]" id="expires" value="{$GC.expires}" class="textbox number"> {$LANG.common.blank_for_no_expire}</span></div>
	<div><label for="delivery">{$LANG.catalogue.delivery_method}</label><span><select name="gc[delivery]" id="delivery" class="textbox">
	  {foreach from=$OPT_DELIVERY item=option}<option value="{$option.value}" {$option.selected}>{$option.title}</option>{/foreach}
	</select></span></div>
	<div><label for="weight">{$LANG.settings.gc_weight}</label><span><input type="text" name="gc[weight]" id="weight" value="{$GC.weight}" class="textbox number"></span></div>
	<div><label for="product-code">{$LANG.catalogue.product_code}</label><span><input type="text" name="gc[product_code]" id="product-code" value="{$GC.product_code}" class="textbox"></span></div>
	{if isset($list_tax)}
	<div><label for="tax-type">{$LANG.catalogue.tax_type}</label><span><select name="gc[taxType]" id="tax-type" class="textbox">
	  {foreach from=$TAXES item=tax}<option value="{$tax.id}" {$tax.selected}>{$tax.tax_name}</option>{/foreach}
	</select></span></div>
	{/if}
	</fieldset>
  </div>
    <div id="gift_images" class="tab_content">
	<h3>{$LANG.settings.gc_images}</h3>
	<div class="fm-container">
	  <div id="image" rel="1" class="fm-filelist unique"></div>
	</div>
	<p>{$LANG.filemanager.file_upload_note}</p>
	<div><label for="uploader">{$LANG.filemanager.file_upload}</label><span><input name="image" id="uploader" type="file"></span></div>
	<script type="text/javascript">
	var file_list = {$JSON_IMAGES}
	</script>
  </div>
  {include file='templates/element.hook_form_content.php'}
  
  <div class="form_control">
	<input type="hidden" name="save" value="{$FORM_HASH}">
	<input type="submit" value="{$LANG.common.save}">
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
</form>