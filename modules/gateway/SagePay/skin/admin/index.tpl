<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="SagePay" class="tab_content">
  		<h3>{$TITLE}</h3>
  		<fieldset><legend>{$LANG.module.cubecart_settings}</legend>
			<div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
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
			<div><label for="default">{$LANG.common.default}</label><span><input type="hidden" name="module[default]" id="default" class="toggle" value="{$MODULE.default}" /></span></div>
			<div><label for="description">{$LANG.common.description} *</label><span><input name="module[desc]" id="description" class="textbox" type="text" value="{$MODULE.desc}" /></span></div>
			<div><label for="acNo">{$LANG.module.merchant_id}</label><span><input name="module[acNo]" id="acNo" class="textbox" type="text" value="{$MODULE.acNo}" /></span></div>
			<div><label for="passphrase">{$LANG.sagepay.encryption_phrase}</label><span><input name="module[passphrase]" id="passphrase" class="textbox" type="text" value="{$MODULE.passphrase}" /></span></div>
			<div><label for="gate">{$LANG.sagepay.transaction_mode}</label>
				<span>
					<select name="module[gate]">
        				<option value="sim" {$SELECT_gate_sim}>{$LANG.sagepay.tx_simulate}</option>
        				<option value="test" {$SELECT_gate_test}>{$LANG.sagepay.tx_test}</option>
        				<option value="live" {$SELECT_gate_live}>{$LANG.sagepay.tx_live}</option>
    				</select>
				</span>
			</div>
			<div><label for="TxType">{$LANG.module.transaction_type}</label>
				<span>
					<select name="module[TxType]">
        				<option value="PAYMENT" {$SELECT_TxType_PAYMENT}>{$LANG.sagepay.type_payment}</option>
        				<option value="DEFERRED" {$SELECT_TxType_DEFERRED}>{$LANG.sagepay.type_deferred}</option>
        				<option value="AUTHENTICATE" {$SELECT_TxType_AUTHENTICATE}>{$LANG.sagepay.type_authenticate}</option>
    				</select> *
				</span>
			</div>
			<div><label for="gate">{$LANG.sagepay.encryption}</label>
				<span>
					<select name="module[encryption]">
        				<option value="XOR" {$SELECT_encryption_XOR}>XOR</option>
        				<option value="AES" {$SELECT_encryption_AES}>AES ({$LANG.sagepay.recommended})</option>
    				</select>
				</span>
			</div>
			<!--<div><label for="iframe">{$LANG.sagepay.iframe}</label><span><input type="hidden" name="module[iframe]" id="iframe" class="toggle" value="{$MODULE.iframe}" /></span></div>-->
			<p>{$LANG.sagepay.mode_warning}</p>
			<div><label for="VendorEMail">{$LANG.sagepay.vendor_email}</label><span><input name="module[VendorEMail]" id="VendorEMail" class="textbox" type="text" value="{$MODULE.VendorEMail}" /></span></div>
			</fieldset>
  		<p>{$LANG.module.description_options}</p>
  		</div>
  		
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  	
  	
  	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>