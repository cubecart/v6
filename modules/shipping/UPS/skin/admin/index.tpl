<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="UPS" class="tab_content">
	<h3><a href="http://www.ups.com" target="_blank">{$TITLE}</a></h3>
	<p>{$LANG.ups.module_description}</p>

	<fieldset><legend>{$LANG.module.cubecart_settings}</legend>
	  <div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
	  <div><label for="packagingWeight">{$LANG.ups.package_weight}</label><span><input name="module[packagingWeight]" id="packagingWeight" class="textbox" type="text" value="{$MODULE.packagingWeight}" /></span></div>
	  <div><label for="postcode">{$LANG.ups.postcode_origin}</label><span><input type="text" name="module[postcode]" value="{$MODULE.postcode}" class="textbox" size="10" /></span></div>
	  <div><label for="handling">{$LANG.basket.shipping_handling}</label><span><input name="module[handling]" id="handling" class="textbox number" type="text" value="{$MODULE.handling}" /></span></div>
	  <div>
		<label for="tax">{$LANG.catalogue.tax_type}</label>
		<span>
		  <select name="module[tax]" id="tax">
			{foreach from=$TAXES item=tax}<option value="{$tax.id}" {$tax.selected}>{$tax.tax_name}</option>{/foreach}
		  </select>
		</span>
	  </div>
	  <div>
		<label for="container">{$LANG.ups.package_type}</label>
		<span>
		  <select name="module[container]">
			<option value="CP" {$SELECT_container_CP}>{$LANG.ups.pack_custom}</option>
			<option value="ULE" {$SELECT_container_ULE}>{$LANG.ups.pack_envelope}</option>
			<option value="UT" {$SELECT_container_UT}>{$LANG.ups.pack_tube}</option>
			<option value="UEB" {$SELECT_container_UEB}>{$LANG.ups.pack_box}</option>
			<option value="UW25" {$SELECT_container_UW25}>{$LANG.ups.pack_25k}</option>
			<option value="UW10" {$SELECT_container_UW10}>{$LANG.ups.pack_10k}</option>
		  </select>
		</span>
	  </div>
	  <div>
		<label for="rate">{$LANG.ups.title_rate}</label>
		<span>
		  <select name="module[rate]">
			<option value="RDP" {$SELECT_rate_RDP}>{$LANG.ups.rate_rdp}</option>
			<option value="OCA" {$SELECT_rate_OCA}>{$LANG.ups.rate_oca}</option>
			<option value="OTP" {$SELECT_rate_OTP}>{$LANG.ups.rate_otp}</option>
			<option value="LC" {$SELECT_rate_LC}>{$LANG.ups.rate_lc}</option>
			<option value="CC" {$SELECT_rate_CC}>{$LANG.ups.rate_cc}</option>
		  </select>
		</span>
	  </div>
	  <div>
		<label for="rescom">{$LANG.ups.title_address}</label>
		<span>
		  <select name="module[rescom]">
			<option value="RES" {$SELECT_rescom_RES}>{$LANG.ups.address_residential}</option>
			<option value="COM" {$SELECT_rescom_COM}>{$LANG.ups.address_commercial}</option>
		  </select>
		</span>
	  </div>
	</fieldset>

	<fieldset><legend>{$LANG.ups.title_products}</legend>
	<div>
		<label for="product1DM">{$LANG.ups.service_nextday_am}</label>
		<span>
			<input type="hidden" name="module[product1DM]" id="product1DM" class="toggle" value="{$MODULE.product1DM}" />
		</span>
	</div>

	<div>
		<label for="product1DA">{$LANG.ups.service_nextday_air}</label>
		<span>
			<input type="hidden" name="module[product1DA]" id="product1DA" class="toggle" value="{$MODULE.product1DA}" />
		</span>
	</div>

	<div>
		<label for="product1DP">{$LANG.ups.service_nextday_saver}</label>
		<span>
			<input type="hidden" name="module[product1DP]" id="product1DP" class="toggle" value="{$MODULE.product1DP}" />
		</span>
	</div>

	<div>
		<label for="product2DM">{$LANG.ups.service_day2_am}</label>
		<span>
			<input type="hidden" name="module[product2DM]" id="product2DM" class="toggle" value="{$MODULE.product2DM}" />
		</span>
	</div>

	<div>
		<label for="product2DA">{$LANG.ups.service_day2_air}</label>
		<span>
			<input type="hidden" name="module[product2DA]" id="product2DA" class="toggle" value="{$MODULE.product2DA}" />
		</span>
	</div>

	<div>
		<label for="product3DS">{$LANG.ups.service_day3_select}</label>
		<span>
			<input type="hidden" name="module[product3DS]" id="product3DS" class="toggle" value="{$MODULE.product3DS}" />
		</span>
	</div>

	<div>
		<label for="productGND">{$LANG.ups.service_ground}</label>
		<span>
			<input type="hidden" name="module[productGND]" id="productGND" class="toggle" value="{$MODULE.productGND}" />
		</span>
	</div>

	<div>
		<label for="productSTD">{$LANG.ups.service_canada_standard}</label>
		<span>
			<input type="hidden" name="module[productSTD]" id="productSTD" class="toggle" value="{$MODULE.productSTD}" />
		</span>
	</div>

	<div>
		<label for="productXPR">{$LANG.ups.service_worldwide_express}</label>
		<span>
			<input type="hidden" name="module[productXPR]" id="productXPR" class="toggle" value="{$MODULE.productXPR}" />
		</span>
	</div>

	<div>
		<label for="productXDM">{$LANG.ups.service_worldwide_express_plus}</label>
		<span>
			<input type="hidden" name="module[productXDM]" id="productXDM" class="toggle" value="{$MODULE.productXDM}" />
		</span>
	</div>

	<div>
		<label for="productXPD">{$LANG.ups.service_worldwide_expedited}</label>
		<span>
			<input type="hidden" name="module[productXPD]" id="productXPD" class="toggle" value="{$MODULE.productXPD}" />
		</span>
	</div>

  </fieldset>
  </div>
  {$MODULE_ZONES}
  <div class="form_control">
	<input type="submit" name="save" value="{$LANG.common.save}" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>