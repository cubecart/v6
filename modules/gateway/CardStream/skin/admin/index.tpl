<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="CardStream" class="tab_content">
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
			<div><label for="merchant_id">{$LANG.cardstream.merchant_id}</label><span><input name="module[merchant_id]" id="merchant_id" class="textbox" type="text" value="{$MODULE.merchant_id}" /></span></div>
			<div><label for="merchant_password">{$LANG.cardstream.merchant_password}</label><span><input name="module[merchant_password]" id="merchant_password" class="textbox" type="text" value="{$MODULE.merchant_password}" /></span></div>
			<div><label for="payment_page_url">{$LANG.cardstream.payment_page_url}</label><span><input name="module[payment_page_url]" id="payment_page_url" class="textbox" type="text" value="{$MODULE.payment_page_url}" /></span> {$LANG.cardstream.payment_page_url_default}</div>
		</fieldset>
		<p>{$LANG.module.description_options}</p>
		</div>
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>