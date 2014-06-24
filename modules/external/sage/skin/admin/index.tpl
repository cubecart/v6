<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="sage" class="tab_content">
  		<h3>Sage</h3>
  		<fieldset><legend>{$LANG.module.config_settings}</legend>
			<div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
			<div>
				<label for="exportCustomers">{$LANG.sage.export_customer_account}</label>
	      		<span>
	      		  <select name="module[exportCustomers]"  class="textbox">
	        	    <option value="prefix" {$SELECT_exportCustomers_prefix}>{$LANG.sage.prefix_plus_id}</option>
	        	    <option value="prefix8" {$SELECT_exportCustomers_prefix8}>{$LANG.sage.prefix_plus_id_8_char}</option>
	        		<option value="none" {$SELECT_exportCustomers_none}>{$LANG.sage.dont_export}</option>
	              </select>
	              {$LANG.sage.code_countries_unspecified}
				</span>
			</div>
	    	<div><label for="exportPayments">{$LANG.sage.export_customer_payments}</label><span><input type="hidden" name="module[exportPayments]" id="exportPayments" class="toggle" value="{$MODULE.exportPayments}" /></span></div>
	    	<div>
				<label for="accountPrefix">{$LANG.sage.customer_acc_prefix}</label>
	      		<span>
	      		  <input name="module[accountPrefix]" type="text" class="textbox" value="{$MODULE.accountPrefix}" />
				</span>
			</div>
			<div>
				<label for="customerAccount">{$LANG.sage.customer_acc_single}</label>
	      		<span>
	      		  <input name="module[customerAccount]" type="text" class="textbox" value="{$MODULE.customerAccount}" />
				  {$LANG.sage.customer_acc_code}
				</span>
			</div>
			<div>
				<label for="salesNominal">{$LANG.sage.sales_nominal}</label>
	      		<span>
	      		  <input name="module[salesNominal]" type="text" class="textbox" value="{$MODULE.salesNominal}" />
				</span>
			</div>
			<div>
				<label for="taxCode">{$LANG.sage.tax_code}</label>
	      		<span>
	      		  <select name="module[taxCode]" class="textbox">
	        	    <option value="T0" {$SELECT_taxCode_T0}>T0</option>
	        	    <option value="T1" {$SELECT_taxCode_T1}>T1</option>
	        		<option value="T9" {$SELECT_taxCode_T9}>T9</option>
	              </select>
	              {$LANG.sage.code_countries_unspecified}
				</span>
			</div>
			<div>
				<label for="exchangeRate">{$LANG.sage.exchange_rate}</label>
	      		<span><input name="module[exchangeRate]" type="text" class="textbox" value="{$MODULE.exchangeRate}" />
	        	{$LANG.sage.blank_disable_exchange_rate}
				</span>
			</div>
  		</fieldset>
  		<fieldset><legend>{$LANG.sage.gateway_nominal_values}</legend>
			<div class="list">
				{foreach from=$nominals item=nominal}
				<div><label for="{$nominal.folder}">{$nominal.desc}</label><span><input name="module[pymtNominal_{$nominal.folder}]" id="{$nominal.folder}" class="textbox" type="text" value="{$nominal.value}" /></span></div>
				{/foreach}
			</div>
  		</fieldset>

		<fieldset><legend>{$LANG.sage.country_tax_code_settings}</legend>
			<div class="list">
			{foreach from=$list_enabled item=enabled}
				<div>
					<label for="{$enabled.name}">{$enabled.name} ({$enabled.tax_code})</label>
					<span class="actions">  <a href="{$enabled.link}" class="delete" title="{$LANG.notification.confirm_delete}"><img src="images/icons/delete.png" alt="{$LANG.common.delete}" /></a>
					<input type="hidden" name="module[{$enabled.module_key}]" value="{$enabled.tax_code}" /></span>
				</div>
			{foreachelse}
			<div>{$LANG.form.none}</div>
			{/foreach}
			</div>
			<div><label>{$LANG.common.add_new}</label>
				<span>
					<select name="newTaxCodeCountry">
						<option value="">{$LANG.form.select}</option>
						{foreach from=$countries item=country}
						<option value="{$country.id}">{$country.name}</option>
						{/foreach}
					</select>
					<select name="newTaxCode">
						<option value="">{$LANG.form.select}</option>
						{foreach from=$tax_codes item=tax_code}
						<option value="{$tax_code}">{$tax_code}</option>
						{/foreach}
					</select>
					<input type="submit" name="save" value="{$LANG.common.add}" />
				</span>
			</div>
		</fieldset>
		<div class="form_control"><input type="submit" name="save" value="{$LANG.common.save}" /></div>
	</div>
	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>
<div id="zone-list" class="tab_content">
  <p>{$LANG.module.not_supported}</p>
</div>