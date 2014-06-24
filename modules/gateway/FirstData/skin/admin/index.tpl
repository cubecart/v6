<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="FirstData" class="tab_content">
  		<h3>{$TITLE}</h3>
  		<fieldset><legend>{$LANG.module.config_settings}</legend>
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
			<div><label for="storename">{$LANG.firstdata.store_number}</label><span><input name="module[storename]" id="storename" class="textbox" type="text" value="{$MODULE.storename}" /></span></div>
			<div><label for="txntype">{$LANG.module.transaction_type}</label>
				<span>
					<select name="module[txntype]">
        				<option value="preauth" {$SELECT_txntype_preauth}>{$LANG.firstdata.txn_authorize}</option>
        				<option value="sale" {$SELECT_txntype_sale}>{$LANG.firstdata.txn_sale}</option>
    				</select>
				</span>
			</div>
			<div><label for="gateway_solution">{$LANG.firstdata.gateway_solution}</label>
				<span>
					<select name="module[gateway_solution]">
        				<option value="connect" {$SELECT_gateway_solution_connect}>{$LANG.firstdata.solution_ggc}</option>
    				</select>
				</span>
			</div>
			<div><label for="gateway_mode">{$LANG.firstdata.gateway_mode}</label>
				<span>
					<select name="module[gateway_mode]">
        				<option value="live" {$SELECT_gateway_mode_live}>{$LANG.firstdata.mode_live}</option>
        				<option value="staging" {$SELECT_gateway_mode_staging}>{$LANG.firstdata.mode_staging}</option>
    				</select>
				</span>
			</div>
			</fieldset>
			<fieldset><legend>{$LANG.firstdata.connect_settings}</legend>
			<p>{$LANG.module.3rd_party_settings_desc}</p>
			<div><label for="submission_url">{$LANG.firstdata.submit_url}</label><span><input name="submission_url" id="submission_url" class="textbox" type="text" value="{$STORE_URL}/index.php?_a=gateway" readonly="readonly" /></span></div>
			<div><label for="confirm_url">{$LANG.firstdata.confirm_url}</label><span><input name="confirm_url" id="confirm_url" class="textbox" type="text" value="{$LANG.firstdata.leave_empty}" readonly="readonly" /></span></div>
			<div><label for="fail_url">{$LANG.firstdata.fail_url}</label><span><input name="fail_url" id="fail_url" class="textbox" type="text" value="{$LANG.firstdata.leave_empty}" readonly="readonly" /></span></div>
			<div><label for="use_cgi">{$LANG.firstdata.url_is_cgi}</label><span><input name="use_cgi" id="use_cgi" class="textbox" type="text" value="{$LANG.firstdata.check_both}" readonly="readonly" /></span></div>
  		</fieldset>
  		<p>{$LANG.module.description_options}</p>
  		</div>
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  		<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
  	</div>
</form>