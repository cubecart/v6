<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="2Checkout" class="tab_content">
  		<h3>{$TITLE}</h3>
		<p class="copyText">{$LANG.2checkout.module_description}</p>
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
			<div><label for="acno">{$LANG.2checkout.account_no}</label><span><input name="module[acNo]" id="acno" class="textbox" type="text" value="{$MODULE.acNo}" /></span></div>
			<div>
				<label for="testMode">{$LANG.module.mode_test}</label>
					<span>
						<select name="module[testMode]">
      						<option value="Y" {$SELECT_testMode_Y}>{$LANG.common.on}</option>
      						<option value="N" {$SELECT_testMode_N}>{$LANG.common.off}</option>
    					</select>
    				</span>
    		</div>
    		<div>
				<label for="checkoutMode">{$LANG.2checkout.mode_checkout}</label>
					<span>
						<select name="module[checkoutMode]">
      						<option value="single" {$SELECT_checkoutMode_single}>{$LANG.2checkout.single_page}</option>
      						<option value="multi" {$SELECT_checkoutMode_multi}>{$LANG.2checkout.multi_page}</option>
    					</select>
    				</span>
    		</div>
  		</fieldset>
  		<fieldset><legend>{$LANG.2checkout.2checkout_settings}</legend>
  			<p>{$LANG.module.3rd_party_settings_desc}</p>
  			<div><label for="direct_return">{$LANG.2checkout.direct_return}</label><span><input name="direct_return" id="direct_return" class="textbox" type="text" value="{$LANG.2checkout.direct_return_value}" readonly="readonly" /></span></div>
  			<div><label for="approved_url">{$LANG.2checkout.approved_url}</label><span><input name="approved_url" id="approved_url" class="textbox" type="text" value="{$STORE_URL}/index.php?_g=rm&type=gateway&cmd=process&module=2Checkout" readonly="readonly" /></span></div>
  		</fieldset>
  		<p>{$LANG.module.description_options}</p>
  		</div>
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>