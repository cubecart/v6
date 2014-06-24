<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="CardSave_Redirect" class="tab_content">
		<h3><img src="modules/gateway/CardSave_Redirect/admin/cardsave_secure.jpg" /></h3>
		<h3>{$LANG.cardsave.module_title}</h3>
		<p><b>Module Version:</b> {$LANG.cardsave.module_version}<br /><b>Release Date:</b> {$LANG.cardsave.module_date}</p>
		<p><a href="http://www.cardsave.net/" target="_blank">CardSave Online Website</a><br />
		<a href="https://mms.cardsaveonlinepayments.com/" target="_blank">CardSave Merchant Management System (MMS)</a></p>
		<fieldset>
			<legend>{$LANG.module.cubecart_settings}</legend>
			<div>
				<label for="status">{$LANG.common.status}</label>
				<span>
				<input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" />
				</span>
			</div>
			<div><label for="position">{$LANG.module.position}</label><span><input type="text" name="module[position]" id="position" class="textbox number" value="{$MODULE.position}" /></span></div>
			<div>
				<label for="scope">{$LANG.module.scope}</label>
				<span>
					<select name="module[scope]">
      						<option value="both" {$SELECT_scope_both}>{$LANG.module.both}</option>
      						<option value="main" {$SELECT_scope_main}>{$LANG.module.main}</option>
      						<option value="mobile" {$SELECT_scope_mobile}>{$LANG.module.mobile}</option>
    					</select>
				</span>
			</div>
			<div>
				<label for="default">{$LANG.common.default}</label>
				<span>
				<input type="hidden" name="module[default]" id="default" class="toggle" value="{$MODULE.default}" />
			</span></div>
			<div>
				<label for="description">{$LANG.common.description} *</label>
				<span>
				<input name="module[desc]" id="description" class="textbox" type="text" value="{$MODULE.desc}" />
			</span></div>
		</fieldset>
		<fieldset>
			<legend>CardSave Gateway Settings</legend>
			<p><em>{$LANG.cardsave.merchantsettings_description}</em></p>
			<div>
				<label for="merchantID">{$LANG.cardsave.merchantID}</label>
				<span>
				<input name="module[merchantID]" id="merchantID" class="textbox" type="text" value="{$MODULE.merchantID}" />
			</span></div>
			<div>
				<label for="merchantPass">{$LANG.cardsave.merchantPass}</label>
				<span>
				<input name="module[merchantPass]" id="merchantPass" class="textbox" type="text" value="{$MODULE.merchantPass}" />
			</span></div>
			<div>
				<label for="merchantPSK">{$LANG.cardsave.merchantPSK}</label>
				<span>
				<input name="module[merchantPSK]" id="merchantPSK" class="textbox" type="text" value="{$MODULE.merchantPSK}" />
			</span></div>
			<div>
				<label for="txntype">{$LANG.module.transaction_type}</label>
				<span>
				<select name="module[charge_type]">
					<option value="SALE" {$SELECT_charge_type_Auth}>{$LANG.cardsave.mode_auth}</option>
					<option value="PREAUTH" {$SELECT_charge_type_PreAuth}>{$LANG.cardsave.mode_preauth}</option>
				</select>
				</span> </div>
			<p><b>{$LANG.cardsave.mandatoryfields_title}</b></p>
				<p><em>{$LANG.cardsave.mandatoryfields_description}</em></p>
				<div>
					<label for="CV2Mandatory">{$LANG.cardsave.mand_CV2}</label>
					<span>
					<input type="hidden" name="module[CV2Mandatory]" id="CV2Mandatory" class="toggle" value="{$MODULE.CV2Mandatory}" />
				</span></div>
				<div>
					<label for="Address1Mandatory">{$LANG.cardsave.mand_Address1}</label>
					<span>
					<input type="hidden" name="module[Address1Mandatory]" id="Address1Mandatory" class="toggle" value="{$MODULE.Address1Mandatory}" />
				</span></div>
				<div>
					<label for="CityMandatory">{$LANG.cardsave.mand_City}</label>
					<span>
					<input type="hidden" name="module[CityMandatory]" id="CityMandatory" class="toggle" value="{$MODULE.CityMandatory}" />
				</span></div>
				<div>
					<label for="PostCodeMandatory">{$LANG.cardsave.mand_PostCode}</label>
					<span>
					<input type="hidden" name="module[PostCodeMandatory]" id="PostCodeMandatory" class="toggle" value="{$MODULE.PostCodeMandatory}" />
				</span></div>
				<div>
					<label for="StateMandatory">{$LANG.cardsave.mand_State}</label>
					<span>
					<input type="hidden" name="module[StateMandatory]" id="StateMandatory" class="toggle" value="{$MODULE.StateMandatory}" />
				</span></div>
				<div>
					<label for="CountryMandatory">{$LANG.cardsave.mand_Country}</label>
					<span>
					<input type="hidden" name="module[CountryMandatory]" id="CountryMandatory" class="toggle" value="{$MODULE.CountryMandatory}" />
				</span></div>
		</fieldset>
		<p>{$LANG.module.description_options}</p>
	</div>
	{$MODULE_ZONES}
	<div class="form_control">
		<input type="submit" name="save" value="{$LANG.common.save}" />
	</div>
	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>
