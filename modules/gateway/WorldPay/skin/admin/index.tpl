<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="WorldPay" class="tab_content">
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
			<div><label for="acNo">{$LANG.worldpay.install_id}</label><span><input name="module[acNo]" id="acNo" class="textbox" type="text" value="{$MODULE.acNo}" /></span></div>
			<div><label for="testmode">{$LANG.module.mode_test}</label>
				<span>
					<select name="module[testMode]" id="testmode">
        				<option value="100" {$SELECT_testMode_100}>{$LANG.worldpay.mode_success}</option>
        				<option value="101" {$SELECT_testMode_101}>{$LANG.worldpay.mode_decline}</option>
        				<option value="0" {$SELECT_testMode_0}>{$LANG.common.disabled}</option>
    				</select>
				</span>
			</div>
			</fieldset>
			<fieldset><legend>{$LANG.worldpay.settings}</legend>
			<p>{$LANG.module.3rd_party_settings_desc}</p>
			<div><label for="callbackURL">{$LANG.worldpay.callback_url}</label><span><input name="callbackURL" id="callbackURL" class="textbox" type="text" value="{$STORE_URL}/modules/gateway/WorldPay/return.php" readonly="readonly" /></span>
			<div><label for="callback_enabled">{$LANG.worldpay.callback_enabled}</label><span><input name="callback_enabled" id="callback_enabled" class="textbox" type="text" value="{$LANG.common.yes}" readonly="readonly" /></span>
			<div><label for="callback_response">{$LANG.worldpay.callback_response}</label><span><input name="callback_response" id="callback_response" class="textbox" type="text" value="{$LANG.common.yes}" readonly="readonly" /></span>
			</fieldset>
			<p>{$LANG.module.description_options}</p>
  		</div>
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  	
  	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>