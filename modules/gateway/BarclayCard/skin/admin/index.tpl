<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="BarclayCard" class="tab_content">
	<h3>{$TITLE}</h3>
	<fieldset><legend>{$LANG.module.cubecart_settings}</legend>
	  <div><label for="txntype">{$LANG.barclaycard.integration_mode}</label>
		<span>
		  <select name="module[mode]">
			<option value="old" {$SELECT_mode_old}>{$LANG.barclaycard.mode_old}</option>
			<option value="new" {$SELECT_mode_new}>{$LANG.barclaycard.mode_new}</option>
		  </select>
		</span>
	  </div>
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

	  <div><label for="clientid">{$LANG.barclaycard.clientid}</label><span><input name="module[clientid]" id="clientid" class="textbox" type="text" value="{$MODULE.clientid}" /></span> ON</div>
	  <div><label for="passphrase">{$LANG.barclaycard.passphrase}</label><span><input name="module[passphrase]" id="passphrase" class="textbox" type="text" value="{$MODULE.passphrase}" /> ON</span></div>
	  <div><label for="passphrase_out">{$LANG.barclaycard.passphrase_out}</label><span><input name="module[passphrase_out]" id="passphrase_out" class="textbox" type="text" value="{$MODULE.passphrase_out}" /> N</span></div>
	  <div><label for="post_user">{$LANG.barclaycard.post_user}</label><span><input name="module[post_user]" id="post_user" class="textbox" type="text" value="{$MODULE.post_user}" /></span> O</div>
	  <div><label for="post_pass">{$LANG.barclaycard.post_pass}</label><span><input name="module[post_pass]" id="post_pass" class="textbox" type="text" value="{$MODULE.post_pass}" /></span> O</div>
	  <div><label for="txntype">{$LANG.module.transaction_type}</label>
		<span>
		  <select name="module[charge_type]">
			<option value="Auth" {$SELECT_charge_type_Auth}>{$LANG.barclaycard.mode_auth}</option>
			<option value="PreAuth" {$SELECT_charge_type_PreAuth}>{$LANG.barclaycard.mode_preauth}</option>
		  </select>
		</span>
	  </div>
	   <div><label for="logo_url">{$LANG.barclaycard.logo_url}</label><span><input name="module[logo_url]" id="logo_url" class="textbox" type="text" value="{$MODULE.logo_url}" /></span> N</div>
	  <div><label for="sandbox">{$LANG.module.mode_test}</label><span><input type="hidden" name="module[test_mode]" id="sandbox" class="toggle" value="{$MODULE.test_mode}" /></span></div>
	</fieldset>
	<p>{$LANG.barclaycard.old_integration_only}</p>
	<fieldset><legend>{$LANG.barclaycard.settings}</legend>
		<p>{$LANG.module.3rd_party_settings_desc}</p>
		<div><label for="allowed_url">{$LANG.barclaycard.post_url}</label><span><input name="allowed_url" id="allowed_url" class="textbox" type="text" value="{$MODULE.callURL}" readonly="readonly" /></span></div>
		<div><label for="post_url">{$LANG.barclaycard.allowed_url}</label><span><input name="post_url" id="allowed_url" class="textbox" type="text" value="{$MODULE.fromURL}" readonly="readonly" /></span></div>
  		</fieldset>
  		<fieldset><legend>{$LANG.barclaycard.settings_new}</legend>
  		<p>{$LANG.module.3rd_party_settings_desc}</p>
		<strong>{$LANG.barclaycard.global_security_params}</strong>
		<div><label for="hash_algorithm">{$LANG.barclaycard.hash_alogrithm}</label><span><input name="hash_algorithm" id="hash_algorithm" class="textbox" type="text" value="SHA-1" readonly="readonly" /></span></div>
		<div><label for="character_encoding">{$LANG.barclaycard.char_encoding}</label><span><input name="character_encoding" id="character_encoding" class="textbox" type="text" value="UTF8" readonly="readonly" /></span></div>
		<strong>{$LANG.barclaycard.payment_page_layout}</strong>
		<div><label for="back_url">{$LANG.barclaycard.back_redirect}</label><span><input name="back_url" id="back_url" class="textbox" type="text" value="{$MODULE.fromURL}" readonly="readonly" /></span></div>
		<strong>{$LANG.barclaycard.origin_verification}</strong>
		<div><label for="post_url">{$LANG.barclaycard.post_url_new}</label><span><input name="post_url" id="post_url" class="textbox" type="text" value="{$MODULE.fromURL}" readonly="readonly" /></span></div>
		<strong>{$LANG.barclaycard.transaction_feedback}</strong>
		<div><label for="timing">{$LANG.barclaycard.timing}</label><span><input name="timing" id="timing" class="textbox" type="text" value="Always online" readonly="readonly" /></span></div>
		<div><label for="call_url">{$LANG.barclaycard.response_url}</label><span><input name="call_url" id="call_url" class="textbox" type="text" value="{$MODULE.callURL}" readonly="readonly" /></span></div>
		<div><label for="request_method">{$LANG.barclaycard.request_method}</label><span><input name="request_method" id="request_method" class="textbox" type="text" value="POST" readonly="readonly" /></span></div>
  		</fieldset>
  		<p>{$LANG.module.description_options}</p>
  </div>
  {$MODULE_ZONES}
  <div class="form_control"><input type="submit" name="save" value="{$LANG.common.save}" /></div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>