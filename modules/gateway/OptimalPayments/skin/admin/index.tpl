<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="optimalpayments" class="tab_content">
	<h3>{$TITLE}</h3>
	<p>{$LANG.optimalpayments.description}</p>
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
	  <div><label for="test_mode">{$LANG.module.mode_test}</label><span><input type="hidden" name="module[test_mode]" id="test_mode" class="toggle" value="{$MODULE.test_mode}" /></span></div>
	  <div><label for="description">{$LANG.common.description} *</label><span><input name="module[desc]" id="description" class="textbox" type="text" value="{$MODULE.desc}" /></span></div>
	  <div>
	  	<label for="API_method">{$LANG.optimalpayments.api_method}</label>
	  		<span>
	  			<select name="module[API_method]">
	  				<option value="tradegard" {$SELECT_API_method_tradegard}>{$LANG.optimalpayments.api_tradegard}</option>
					<option value="standard" {$SELECT_API_method_standard}>{$LANG.optimalpayments.api_standard}</option>	
	  			</select>
	  		</span>
	  </div>
	  <div><label for="shopId">{$LANG.optimalpayments.shop_id}</label><span><input name="module[shopId]" id="shopId" class="textbox" type="text" value="{$MODULE.shopId}" /></span></div>
	  <div><label for="sharedKey">{$LANG.optimalpayments.shared_key}</label><span><input name="module[sharedKey]" id="sharedKey" class="textbox" type="text" value="{$MODULE.sharedKey}" /></span></div>
  	</fieldset>
  	<fieldset>
  		<legend>{$LANG.optimalpayments.settings}</legend>
  		<p>{$LANG.module.3rd_party_settings_desc}</p>
  		<div><label for="call_url">{$LANG.optimalpayments.call_url}</label><span><input name="call_url" id="call_url" class="textbox" type="text" value="{$MODULE.callURL}" readonly="readonly" /></span></div>
  	</fieldset>
  	<p>{$LANG.module.description_options}</p>
  </div>
  {$MODULE_ZONES}
  <div class="form_control"><input type="submit" name="save" value="{$LANG.common.save}" /></div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>