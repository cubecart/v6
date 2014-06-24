<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="Amazon_Checkout" class="tab_content">
	<h3>{$LANG.amazon_checkout.module_title}</h3>
	<fieldset><legend>{$LANG.module.config_settings}</legend>
	  <div><label for="amazon_status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="amazon_status" class="toggle" value="{$MODULE.status}" />&nbsp;</span></div>
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
	<div>
		<label for="country">{$LANG.amazon_checkout.country}</label>
		<span>
			<select name="module[country]">
				<option value="UK" {$SELECT_country_UK}>{$LANG.amazon_checkout.UK}</option>
				<option value="US" {$SELECT_country_US}>{$LANG.amazon_checkout.US}</option>
				<option value="DE" {$SELECT_country_DE}>{$LANG.amazon_checkout.DE}</option>
			</select>
		</span>
	</div>
	  <div><label for="amazon_merchId">{$LANG.amazon_checkout.merchant_id}</label><span><input name="module[merchId]" id="amazon_merchId" class="textbox" type="text" value="{$MODULE.merchId}" /></span></div>
	  <div><label for="amazon_access_key">{$LANG.amazon_checkout.access_key}</label><span><input name="module[access_key]" id="amazon_access_key" class="textbox" type="text" value="{$MODULE.access_key}" /></span></div>
	  <div><label for="amazon_secret_key">{$LANG.amazon_checkout.secret_key}</label><span><input name="module[secret_key]" id="amazon_secret_key" class="textbox" type="text" value="{$MODULE.secret_key}" /></span></div>
	  <div><label for="amazon_merchant_token">{$LANG.amazon_checkout.merchant_token}</label><span><input name="module[merchant_token]" id="amazon_merchant_token" class="textbox" type="text" value="{$MODULE.merchant_token}" /></span></div>
	  <div><label for="amazon_market_place_id">{$LANG.amazon_checkout.market_place_id}</label><span><input name="module[market_place_id]" id="amazon_market_place_id" class="textbox" type="text" value="{$MODULE.market_place_id}" /></span></div>
	  <div>
		<label for="amazon_buttonSize">{$LANG.amazon_checkout.button_size}</label>
		<span>
		  <select name="module[buttonSize]" id="amazon_buttonSize">
			  <option value="medium" {$SELECT_buttonSize_medium}>{$LANG.amazon_checkout.medium}</option>
			  <option value="large" {$SELECT_buttonSize_large}>{$LANG.amazon_checkout.large}</option>
			  <option value="x-large" {$SELECT_buttonSize_x_large}>{$LANG.amazon_checkout.x_large}</option>
		  </select>
		</span>
	  </div>
	  <div>
		<label for="amazon_buttonColor">{$LANG.amazon_checkout.button_color}</label>
		<span>
		  <select name="module[buttonColor]" id="amazon_buttonColor">
			  <option value="orange" {$SELECT_buttonColor_orange}>{$LANG.amazon_checkout.orange}</option>
			  <option value="tan" {$SELECT_buttonColor_tan}>{$LANG.amazon_checkout.tan}</option>
		  </select>
		</span>
	  </div>
	  <div>
		<label for="amazon_buttonBg">{$LANG.amazon_checkout.button_background}</label>
		<span>
		  <select name="module[buttonBg]" id="amazon_buttonBg">
			  <option value="white" {$SELECT_buttonBg_white}>{$LANG.amazon_checkout.white}</option>
			  <option value="dark" {$SELECT_buttonBg_dark}>{$LANG.amazon_checkout.dark}</option>
			  <option value="light" {$SELECT_buttonBg_light}>{$LANG.amazon_checkout.light}</option>
		  </select>
		</span>
	  </div>

	  <div>
		<label for="amazon_mode">{$LANG.amazon_checkout.mode}</label>
		<span>
		  <select name="module[mode]" id="amazon_mode">
			  <option value="production" {$SELECT_mode_production}>{$LANG.amazon_checkout.production}</option>
			  <option value="sandbox" {$SELECT_mode_sandbox}>{$LANG.amazon_checkout.sandbox}</option>
		  </select>
		</span>
	  </div>
	</fieldset>
	<!--
	<fieldset><legend>{$LANG.amazon_checkout.settings}</legend>
	<div><label for="callbackURL">{$LANG.amazon_checkout.merchant_url}</label><span><input name="callbackURL" id="callbackURL" class="textbox" type="text" value="{$STORE_URL}/index.php?_g=rm&type=gateway&cmd=call&module=Amazon_Checkout" readonly="readonly" /></span>
	</fieldset>
	-->
  </div>
  {$MODULE_ZONES}
  <div class="form_control">
	<input type="submit" name="save" value="{$LANG.common.save}" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>