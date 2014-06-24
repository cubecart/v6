<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="Canada_Post" class="tab_content">
	<div><a href="http://www.canadapost.ca/" target="_blank">{$TITLE}</a></div>
	<p>{$LANG.canada_post.module_description}</p>
	<fieldset><legend>{$LANG.module.cubecart_settings}</legend>
	  <div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
	  <div><label for="merchant-id">{$LANG.module.merchant_id}</label><span><input name="module[merchant]" id="merchant-id" class="textbox" type="text" value="{$MODULE.merchant}" /></span></div>
	  <div><label for="postcode">{$LANG.canada_post.postcode_origin}</label><span><input name="module[postcode]" id="postcode" class="textbox" type="text" value="{$MODULE.postcode}" /></span></div>
	  <div><label>{$LANG.canada_post.package_size}</label>
		<span>
		  <input name="module[height]" id="height" class="textbox number" type="text" value="{$MODULE.height}" size="4" /> &times;
		  <input name="module[width]" id="width" class="textbox number" type="text" value="{$MODULE.width}" size="4" /> &times;
		  <input name="module[length]" id="length" class="textbox number" type="text" value="{$MODULE.length}" size="4" />
		  ({$LANG.common.height} &times; {$LANG.common.width} &times; {$LANG.common.length})
		</span>
	  </div>
	  <div><label for="handling">{$LANG.basket.shipping_handling}</label><span><input name="module[handling]" id="handling" class="textbox number" type="text" value="{$MODULE.handling}" /></span></div>
	  <div><label for="packagingWeight">{$LANG.canada_post.package_weight}</label><span><input name="module[packagingWeight]" id="packagingWeight" class="textbox number" type="text" value="{$MODULE.packagingWeight}" /></span></div>
	  <div><label for="handling">{$LANG.catalogue.tax_type}</label>
		<span>
		  <select name="module[tax]" id="tax">
			{foreach from=$TAXES item=tax}<option value="{$tax.id}" {$tax.selected}>{$tax.tax_name}</option>{/foreach}
		  </select>
		</span>
	  </div>
	</fieldset>

	<fieldset><legend>{$LANG.canada_post.title_service_domestic}</legend>

	<div><label for="SERVICE1010">{$LANG.canada_post.service_1010}</label><span><input type="hidden" name="module[SERVICE1010]" id="SERVICE1010" class="toggle" value="{$MODULE.SERVICE1010}" /></span></div>
	<div><label for="SERVICE1020">{$LANG.canada_post.service_1020}</label><span><input type="hidden" name="module[SERVICE1020]" id="SERVICE1020" class="toggle" value="{$MODULE.SERVICE1020}" /></span></div>
	<div><label for="SERVICE1030">{$LANG.canada_post.service_1030}</label><span><input type="hidden" name="module[SERVICE1030]" id="SERVICE1030" class="toggle" value="{$MODULE.SERVICE1030}" /></span></div>
	<div><label for="SERVICE1040">{$LANG.canada_post.service_1040}</label><span><input type="hidden" name="module[SERVICE1040]" id="SERVICE1040" class="toggle" value="{$MODULE.SERVICE1040}" /></span></div>
	</fieldset>
	<fieldset><legend>{$LANG.canada_post.title_service_us}</legend>
	<div><label for="SERVICE2005">{$LANG.canada_post.service_2005}</label><span><input type="hidden" name="module[SERVICE2005]" id="SERVICE2005" class="toggle" value="{$MODULE.SERVICE2005}" /></span></div>
	<div><label for="SERVICE2015">{$LANG.canada_post.service_2015}</label><span><input type="hidden" name="module[SERVICE2015]" id="SERVICE2015" class="toggle" value="{$MODULE.SERVICE2015}" /></span></div>
	<div><label for="SERVICE2020">{$LANG.canada_post.service_2020}</label><span><input type="hidden" name="module[SERVICE2020]" id="SERVICE2020" class="toggle" value="{$MODULE.SERVICE2020}" /></span></div>
	<div><label for="SERVICE2025">{$LANG.canada_post.service_2025}</label><span><input type="hidden" name="module[SERVICE2025]" id="SERVICE2025" class="toggle" value="{$MODULE.SERVICE2025}" /></span></div>
	<div><label for="SERVICE2030">{$LANG.canada_post.service_2030}</label><span><input type="hidden" name="module[SERVICE2030]" id="SERVICE2030" class="toggle" value="{$MODULE.SERVICE2030}" /></span></div>
	<div><label for="SERVICE2040">{$LANG.canada_post.service_2040}</label><span><input type="hidden" name="module[SERVICE2040]" id="SERVICE2040" class="toggle" value="{$MODULE.SERVICE2040}" /></span></div>
	<div><label for="SERVICE2050">{$LANG.canada_post.service_2050}</label><span><input type="hidden" name="module[SERVICE2050]" id="SERVICE2050" class="toggle" value="{$MODULE.SERVICE2050}" /></span></div>
	</fieldset>

	<fieldset><legend>{$LANG.canada_post.title_service_international}</legend>
	<div><label for="SERVICE3005">{$LANG.canada_post.service_3005}</label><span><input type="hidden" name="module[SERVICE3005]" id="SERVICE3005" class="toggle" value="{$MODULE.SERVICE3005}" /></span></div>
	<div><label for="SERVICE3010">{$LANG.canada_post.service_3010}</label><span><input type="hidden" name="module[SERVICE3010]" id="SERVICE3010" class="toggle" value="{$MODULE.SERVICE3010}" /></span></div>
	<div><label for="SERVICE3015">{$LANG.canada_post.service_3015}</label><span><input type="hidden" name="module[SERVICE3015]" id="SERVICE3015" class="toggle" value="{$MODULE.SERVICE3015}" /></span></div>
	<div><label for="SERVICE3020">{$LANG.canada_post.service_3020}</label><span><input type="hidden" name="module[SERVICE3020]" id="SERVICE3020" class="toggle" value="{$MODULE.SERVICE3020}" /></span></div>
	<div><label for="SERVICE3025">{$LANG.canada_post.service_3025}</label><span><input type="hidden" name="module[SERVICE3025]" id="SERVICE3025" class="toggle" value="{$MODULE.SERVICE3025}" /></span></div>
	<div><label for="SERVICE3040">{$LANG.canada_post.service_3040}</label><span><input type="hidden" name="module[SERVICE3040]" id="SERVICE3040" class="toggle" value="{$MODULE.SERVICE3040}" /></span></div>
	<div><label for="SERVICE3050">{$LANG.canada_post.service_3050}</label><span><input type="hidden" name="module[SERVICE3050]" id="SERVICE3050" class="toggle" value="{$MODULE.SERVICE3050}" /></span></div>
	</fieldset>
  </div>
  {$MODULE_ZONES}
  <div class="form_control">
	<input type="submit" name="save" value="{$LANG.common.save}" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>