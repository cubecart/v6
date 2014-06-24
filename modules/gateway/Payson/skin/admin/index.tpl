<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="Payson" class="tab_content">
  		<h3>{$TITLE}</h3>
  		<p>{$LANG.payson.module_description}</p>
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
			<div><label for="email">{$LANG.common.email}</label><span><input name="module[email]" id="email" class="textbox" type="text" value="{$MODULE.email}" /></span></div>
			<div>
				<label for="mode">{$LANG.payson.mode}</label>
					<span>
						<select name="module[mode]">
							<option value="0" {$SELECT_mode_0}>{$LANG.payson.mode_live}</option>
        					<option value="1" {$SELECT_mode_1}>{$LANG.payson.mode_test}</option>
    					</select>
    				</span>
    		</div>
    		<div>
				<label for="paymethod">{$LANG.payson.paymethod}</label>
					<span>
						<select name="module[paymethod]">
        					<option value="0" {$SELECT_paymethod_0}>{$LANG.payson.paymethod_0}</option>
        					<option value="1" {$SELECT_paymethod_1}>{$LANG.payson.paymethod_1}</option>
        					<option value="2" {$SELECT_paymethod_2}>{$LANG.payson.paymethod_2}</option>
        					<option value="3" {$SELECT_paymethod_3}>{$LANG.payson.paymethod_3}</option>
        					<option value="4" {$SELECT_paymethod_4}>{$LANG.payson.paymethod_4}</option>	
    					</select>
    				</span>
    		</div>
    		<div><label for="agentid">{$LANG.payson.agentid}</label><span><input name="module[agentid]" id="agentid" class="textbox" type="text" value="{$MODULE.agentid}" /></span></div>
    		<div><label for="key">{$LANG.payson.key}</label><span><input name="module[key]" id="key" class="textbox" type="text" value="{$MODULE.key}" /></span></div>
    		</fieldset>
    		<p>{$LANG.module.description_options}</p>	
  		</div>
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  	
  	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>