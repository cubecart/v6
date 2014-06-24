<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="AusPost" class="tab_content">
  <h3><a href="http://www.auspost.com.au" target="_blank">{$TITLE}</a></h3>
  <p>{$LANG.auspost.module_description}</p>

  <fieldset><legend>{$LANG.module.cubecart_settings}</legend>
	<div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
	<div><label for="percent">{$LANG.auspost.postcode_origin}</label><span><input name="module[postcode]" id="postcode" class="textbox" type="text" value="{$MODULE.postcode}" /></span></div>
	<div><label for="packagingWeight">{$LANG.auspost.package_weight}</label><span><input name="module[packagingWeight]" id="packagingWeight" class="textbox number" type="text" value="{$MODULE.packagingWeight}" /></span></div>
	<div><label for="handling">{$LANG.auspost.package_size}</label>
	  <span>
		<input type="text" class="textbox number" name="module[height]" value="{$MODULE.height}" size="4" /> &times;
		<input type="text" class="textbox number" name="module[width]" value="{$MODULE.width}" size="4" /> &times;
		<input type="text" class="textbox number" name="module[length]" value="{$MODULE.length}" size="4" />
		({$LANG.common.height} &times; {$LANG.common.width} &times; {$LANG.common.length})
	  </span>
	</div>
	<div><label for="handling">{$LANG.basket.shipping_handling}</label><span><input name="module[handling]" id="handling" class="textbox number" type="text" value="{$MODULE.handling}" /></span></div>
	<div><label for="handling">{$LANG.catalogue.tax_type}</label>
	  <span>
		<select name="module[tax]" id="tax">
		{foreach from=$TAXES item=tax}<option value="{$tax.id}" {$tax.selected}>{$tax.tax_name}</option>{/foreach}
		</select>
	  </span>
	</div>
	<div><label for="status">{$LANG.catalogue.tax_included}</label><span><input type="hidden" name="module[tax_included]" id="tax_included" class="toggle" value="{$MODULE.tax_included}" /></span></div>
  </fieldset>

  <fieldset><legend>{$LANG.auspost.title_service_domestic}</legend>
	<div><label for="SERVICE_STANDARD">{$LANG.auspost.service_standard}</label><span><input type="hidden" name="module[SERVICE_STANDARD]" id="SERVICE_STANDARD" class="toggle" value="{$MODULE.SERVICE_STANDARD}" /></span></div>
	<div><label for="SERVICE_EXPRESS">{$LANG.auspost.service_express}</label><span><input type="hidden" name="module[SERVICE_EXPRESS]" id="SERVICE_EXPRESS" class="toggle" value="{$MODULE.SERVICE_EXPRESS}" /></span></div>
  </fieldset>

  <fieldset><legend>{$LANG.auspost.title_service_international}</legend>
	<div>
			<label for="SERVICE_Air">{$LANG.auspost.service_air}</label>
			<span>
				<input type="hidden" name="module[SERVICE_Air]" id="SERVICE_Air" class="toggle" value="{$MODULE.SERVICE_Air}" />
			</span>
	</div>
	<div>
			<label for="SERVICE_Sea">{$LANG.auspost.service_sea}</label>
			<span>
				<input type="hidden" name="module[SERVICE_Sea]" id="SERVICE_Sea" class="toggle" value="{$MODULE.SERVICE_Sea}" />
			</span>
		</div>
		<div>
			<label for="SERVICE_ECI_D">{$LANG.auspost.service_eci_d}</label>
			<span>
				<input type="hidden" name="module[SERVICE_ECI_D]" id="SERVICE_ECI_D" class="toggle" value="{$MODULE.SERVICE_ECI_D}" />
			</span>
		</div>
		<div>
			<label for="SERVICE_ECI_M">{$LANG.auspost.service_eci_m}</label>
			<span>
				<input type="hidden" name="module[SERVICE_ECI_M]" id="SERVICE_ECI_M" class="toggle" value="{$MODULE.SERVICE_ECI_M}" />
			</span>
		</div>
		<div>
			<label for="SERVICE_EPI">{$LANG.auspost.service_epi}</label>
			<span>
				<input type="hidden" name="module[SERVICE_EPI]" id="SERVICE_EPI" class="toggle" value="{$MODULE.SERVICE_EPI}" />
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