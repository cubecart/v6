<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="ccNow" class="tab_content">
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
			<div><label for="testMode">{$LANG.module.mode_test}</label><span><input type="hidden" name="module[testMode]" id="testMode" class="toggle" value="{$MODULE.testMode}" /></span></div>
			<div><label for="description">{$LANG.common.description} *</label><span><input name="module[desc]" id="description" class="textbox" type="text" value="{$MODULE.desc}" /></span></div>
			<div><label for="acName">{$LANG.module.merchant_id}</label><span><input name="module[acName]" id="acName" class="textbox" type="text" value="{$MODULE.acName}" /></span></div>
			<div><label for="actKey">{$LANG.ccnow.activation_key}</label><span><input name="module[actKey]" id="actKey" class="textbox" type="text" value="{$MODULE.actKey}" /></span></div>
		</fieldset>
		<fieldset><legend>{$LANG.ccnow.settings}</legend>
		<p>{$LANG.module.3rd_party_settings_desc}</p>
		<div><label for="thankyou_url">{$LANG.ccnow.thankyou_url}</label><span><input name="thankyou_url" id="thankyou_url" class="textbox" type="text" value="{$MODULE.processURL}" readonly="readonly" /></span></div>
		<p>{$LANG.ccnow.thankyou_url_info}</p>
		<div><label for="push_url">{$LANG.ccnow.push_url}</label><span><input name="push_url" id="push_url" class="textbox" type="text" value="{$MODULE.callURL}" readonly="readonly" /></span></div>
  		<p>{$LANG.ccnow.push_url_info}</p>
  		</fieldset>
  		<p>{$LANG.module.description_options}</p>
	</div>
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>