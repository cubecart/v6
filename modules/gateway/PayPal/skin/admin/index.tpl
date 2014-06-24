<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="PayPal" class="tab_content">
  		<h3>{$TITLE}</h3>
  		<p>{$LANG.paypal.module_description}</p>
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
			<div><label for="email">{$LANG.paypal.email}</label><span><input name="module[email]" id="email" class="textbox" type="text" value="{$MODULE.email}" /></span></div>
			<div>
				<label for="email">{$LANG.paypal.mode}</label>
					<span>
						<select name="module[testMode]">
        					<option value="1" {$SELECT_testMode_1}>{$LANG.paypal.mode_sandbox}</option>
        					<option value="0" {$SELECT_testMode_0}>{$LANG.paypal.mode_live}</option>
    					</select>
    				</span>
    			</div>
            <div><label for="address_override">{$LANG.paypal.address_override}</label><span><input type="hidden" name="module[address_override]" id="address_override" class="toggle" value="{$MODULE.address_override}" /></span></div>
            </fieldset>
            <p>{$LANG.module.description_options}</p>
  		</div>
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  	
  	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>