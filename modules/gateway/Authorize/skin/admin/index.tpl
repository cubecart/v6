<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="Authorize" class="tab_content">
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
			<div><label for="acNo">{$LANG.authorize.merchant_id}</label><span><input name="module[acNo]" id="acNo" class="textbox" type="text" value="{$MODULE.acNo}" /></span></div>
			<div>
				<label for="mode">{$LANG.authorize.mode}</label>
					<span>
						<select name="module[mode]">
      						<option value="sim" {$SELECT_mode_sim}>{$LANG.authorize.sim}</option>
      						<option value="aim" {$SELECT_mode_aim}>{$LANG.authorize.aim}</option>
    					</select>
    				</span>
    		</div>
    		<div>
				<label for="mode">{$LANG.authorize.payment_type}</label>
					<span>
						<select name="module[payment_type]">
      						<option value="AUTH_CAPTURE" {$SELECT_payment_type_AUTH_CAPTURE}>{$LANG.authorize.auth_capture}</option>
      						<option value="AUTH_ONLY" {$SELECT_payment_type_AUTH_ONLY}>{$LANG.authorize.auth_only}</option>
    					</select>
    				</span>
    		</div>
			<!--<div><strong>{$LANG.authorize.info_trans_pass}</strong></div>-->
			<div><label for="txnkey">{$LANG.authorize.transaction_key}</label><span><input name="module[txnkey]" id="txnkey" class="textbox" type="text" value="{$MODULE.txnkey}" /></span></div>
			<!--<div><label for="password">{$LANG.account.password}</label><span><input name="module[password]" id="password" class="textbox" type="password" value="{$MODULE.password}" autocomplete="off" /></span></div>-->
			<div>
				<label for="testMode">{$LANG.module.mode_test}</label>
					<span>
						<input type="hidden" name="module[testMode]" id="testMode" class="toggle" value="{$MODULE.testMode}" />
    				</span>
    		</div>
    		</fieldset>
    		<fieldset><legend>{$LANG.authorize.settings}</legend>
    			<p>{$LANG.module.3rd_party_settings_desc}</p>
    			<div><label for="delimeter">{$LANG.authorize.delimeter}</label><span><input name="delimeter" id="delimeter" class="textbox" type="text" value="{$LANG.authorize.delimeter_value}" readonly="readonly" /></span></div>
    			<div><label for="encapsulation_char">{$LANG.authorize.encapsulation_char}</label><span><input name="encapsulation_char" id="encapsulation_char" class="textbox" type="text" value="{$LANG.authorize.encapsulation_char_value}" readonly="readonly" /></span></div>
    			<div><label for="password_mode">{$LANG.authorize.password_mode}</label><span><input name="password_mode" id="password_mode" class="textbox" type="text" value="{$LANG.authorize.password_mode_value}" readonly="readonly" /></span></div>
    		</fieldset>
    		<p>{$LANG.module.description_options}</p>
  		</div>
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  	
  	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>