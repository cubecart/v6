<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="Parcel2Go" class="tab_content">
	<h3>{$TITLE}</h3>
	<fieldset><legend>{$LANG.module.cubecart_settings}</legend>
	  <div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
	  <div>
		<label for="mod_handling">{$LANG.parcel2go.api_key}</label>
		<span><input name="module[api_key]" id="api_key" type="text" class="textbox" value="{$MODULE.api_key}" /></span>
	  </div>
	  <div>
		<label for="mod_handling">{$LANG.basket.shipping_handling}</label>
		<span><input name="module[handling]" id="mod_handling" type="text" class="textbox number" value="{$MODULE.handling}" /></span>
	  </div>
	  <div><label for="handling">{$LANG.parcel2go.tax_type}</label>
	  <span>
		<select name="module[tax]" id="tax">
		{foreach from=$TAXES item=tax}<option value="{$tax.id}" {$tax.selected}>{$tax.tax_name}</option>{/foreach}
		</select>
	  </span>
	</div>
	<div><label for="status">{$LANG.catalogue.tax_included}</label><span><input type="hidden" name="module[tax_included]" id="tax_included" class="toggle" value="{$MODULE.tax_included}" /></span></div>
	 <div><label>{$LANG.parcel2go.package_size}</label>
		<span>
		  <input name="module[height]" id="height" class="textbox number" type="text" value="{$MODULE.height}" size="4" /> &times;
		  <input name="module[width]" id="width" class="textbox number" type="text" value="{$MODULE.width}" size="4" /> &times;
		  <input name="module[length]" id="length" class="textbox number" type="text" value="{$MODULE.length}" size="4" />
		  ({$LANG.common.height} &times; {$LANG.common.width} &times; {$LANG.common.length})
		</span>
	  </div>
	</fieldset>
	<fieldset><legend>{$LANG.parcel2go.collection_title}</legend>
	  <div><label for="collection_addressee">{$LANG.parcel2go.collection_addressee}</label>
		<span><input type="text" name="module[collection_addressee]" id="collection_addressee" value="{$MODULE.collection_addressee}" class="textbox" /></span>
	  </div>
	  <div><label for="collection_telephone">{$LANG.parcel2go.collection_telephone}</label>
		<span><input type="text" name="module[collection_telephone]" id="collection_telephone" value="{$MODULE.collection_telephone}" class="textbox" /></span>
	  </div>
	  <div><label for="collection_company_name">{$LANG.parcel2go.collection_company_name}</label>
		<span><input type="text" name="module[collection_company_name]" id="collection_company_name" value="{$MODULE.collection_company_name}" class="textbox" /></span>
	  </div>
	  <div><label for="collection_property">{$LANG.parcel2go.collection_property}</label>
		<span><input type="text" name="module[collection_property]" id="collection_property" value="{$MODULE.collection_property}" class="textbox" /></span>
	  </div>
	  <div><label for="collection_street">{$LANG.parcel2go.collection_street}</label>
		<span><input type="text" name="module[collection_street]" id="collection_street" value="{$MODULE.collection_street}" class="textbox" /></span>
	  </div>
	  <div><label for="collection_locality">{$LANG.parcel2go.collection_locality}</label>
		<span><input type="text" name="module[collection_locality]" id="collection_locality" value="{$MODULE.collection_locality}" class="textbox" /></span>
	  </div>
	  <div><label for="collection_town">{$LANG.parcel2go.collection_town}</label>
		<span><input type="text" name="module[collection_town]" id="collection_town" value="{$MODULE.collection_town}" class="textbox" /></span>
	  </div>
	  <div><label for="collection_county">{$LANG.parcel2go.collection_county}</label>
		<span><input type="text" name="module[collection_county]" id="collection_county" value="{$MODULE.collection_county}" class="textbox" /></span>
	  </div>
	  <div><label for="collection_country">{$LANG.parcel2go.collection_country}</label>
		<span><input type="text" name="module[collection_country]" id="collection_country" value="{$MODULE.collection_country}" class="textbox" /></span>
	  </div>
	  <div><label for="collection_postcode">{$LANG.parcel2go.collection_postcode}</label>
		<span><input type="text" name="module[collection_postcode]" id="collection_postcode" value="{$MODULE.collection_postcode}" class="textbox" /></span>
	  </div>
	</fieldset>
  </div>
  {$MODULE_ZONES}
  <div class="form_control">
	<input type="submit" name="save" value="{$LANG.common.save}" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>