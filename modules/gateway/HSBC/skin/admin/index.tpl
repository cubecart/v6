<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="HSBC" class="tab_content">
  		<h3>{$TITLE}</h3>
  		<p>{$LANG.hsbc.module_description}</p>
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
			<div><label for="userID">{$LANG.hsbc.user_id}</label><span><input name="module[userID]" id="userID" class="textbox" type="text" value="{$MODULE.userID}" /></span></div>
			<div><label for="passPhrase">{$LANG.hsbc.password}</label><span><input name="module[passPhrase]" id="passPhrase" class="textbox" type="password" value="{$MODULE.passPhrase}" autocomplete="off" /></span></div>
			<div><label for="acNo">{$LANG.hsbc.client_id}</label><span><input name="module[acNo]" id="acNo" class="textbox" type="text" value="{$MODULE.acNo}" /></span>
			{$LANG.hsbc.info_in_header}</div>
			<div><label for="alias">{$LANG.hsbc.alias_id}</label><span><input name="module[alias]" id="alias" class="textbox" type="text" value="{$MODULE.alias}" /></span>
			{$LANG.hsbc.info_in_header}</div>
			<div>
				<label for="test">{$LANG.module.mode_test}</label>
					<span>
						<select name="module[test]">
        					<option value="1" {$SELECT_test_1}>{$LANG.hsbc.mode_test_success}</option>
        					<option value="2" {$SELECT_test_2}>{$LANG.hsbc.mode_test_decline}</option>
        					<option value="0" {$SELECT_test_0}>{$LANG.common.off}</option>
    					</select>
    				</span>
    			</div>
    			<div>
				<label for="authmode">{$LANG.module.transaction_type}</label>
					<span>
						<select name="module[authmode]">
        					<option value="0" {$SELECT_authmode_0}>{$LANG.hsbc.txn_standard}</option>
        					<option value="1" {$SELECT_authmode_1}>{$LANG.hsbc.txn_preauth}</option>
    					</select>
    				</span>
    			</div>
			<div><label for="reqCvv">{$LANG.hsbc.require_cvv}</label><span><input type="hidden" name="module[reqCvv]" id="reqCvv" class="toggle" value="{$MODULE.reqCvv}" /></span></div>
			<div>
				<label for="amex">{$LANG.hsbc.accept_amex}</label>
					<span><input type="hidden" name="module[amex]" id="amex" class="toggle" value="{$MODULE.amex}" /></span>
    				&nbsp; {$LANG.hsbc.accept_amex_info}
    			</div>
    		<div>
				<label for="avs">{$LANG.hsbc.avs_check}</label>
					<span>
						<input type="hidden" name="module[avs]" id="avs" class="toggle" value="{$MODULE.avs}" />
    				</span>
    				&nbsp; {$LANG.hsbc.avs_check_info}
    		</div>
    		</fieldset>
    		<p>{$LANG.module.description_options}</p>
  		</div>
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  	
  	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>