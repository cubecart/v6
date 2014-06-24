<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="PayPoint" class="tab_content">
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
	  <div><label for="merchant">{$LANG.paypoint.vendor_name}</label><span><input name="module[merchant]" id="merchant" class="textbox" type="text" value="{$MODULE.merchant}" /></span></div>
	  <div>
		<label for="testmode">{$LANG.module.mode_test}</label><span><input type="hidden" name="module[testmode]" id="testmode" class="toggle" value="{$MODULE.testmode}" /></span></div>
	</fieldset>
	<fieldset><legend>{$LANG.paypoint.settings}</legend>
	  <p>{$LANG.module.3rd_party_settings_desc}</p>
	  <div><label for="remote_password">{$LANG.paypoint.remote_password}</label><span><input name="module[remote_password]" id="remote_password" class="textbox" type="text" value="{$MODULE.remote_password}" /></span></div>
	  <div><label for="digest_key">{$LANG.paypoint.digest_key}</label><span><input name="module[digest_key]" id="digest_key" class="textbox" type="text" value="{$MODULE.digest_key}" /></span></div>
	</fieldset>
	<p>{$LANG.module.description_options}</p>
  </div>
  {$MODULE_ZONES}
  <div class="form_control"><input type="submit" name="save" value="{$LANG.common.save}" /></div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>