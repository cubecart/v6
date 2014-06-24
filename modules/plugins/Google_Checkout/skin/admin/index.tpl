<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="Google_Checkout" class="tab_content">
	<h3>{$LANG.google_checkout.module_title}</h3>
	<fieldset><legend>{$LANG.module.config_settings}</legend>
	  <div><label for="google_status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="google_status" class="toggle" value="{$MODULE.status}" />&nbsp;</span></div>
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
	  <div><label for="google_merchid">{$LANG.module.merchant_id}</label><span><input name="module[merchId]" id="google_merchid" class="textbox" type="text" value="{$MODULE.merchId}" /></span></div>
	  <div><label for="google_merchkey">{$LANG.google_checkout.merchant_key}</label><span><input name="module[merchKey]" id="google_merchkey" class="textbox" type="text" value="{$MODULE.merchKey}" /></span></div>
	  <div>
		<label for="google_sizesize">{$LANG.google_checkout.button_size}</label>
		<span>
		  <select name="module[size]" id="google_size">
		    {foreach from=$buttons item=button}
			  <option value="{$button.value}"{$button.selected}>{$button.title}</option>
			{/foreach}
		  </select>
		</span>
	  </div>

	  <div>
		<label for="google_mode">{$LANG.google_checkout.mode}</label>
		<span>
		  <select name="module[mode]" id="google_mode">
			{foreach from=$modes item=mode}
			  <option value="{$mode.value}"{$mode.selected}>{$mode.title}</option>
			{/foreach}
		  </select>
		</span>
	  </div>
	</fieldset>

	<fieldset><legend>{$LANG.google_checkout.settings}</legend>
	  <p>{$LANG.google_checkout.settings_info}</p>
	  <p>{$LANG.google_checkout.advanced_info}</p>
	</fieldset>
	{if $show_ssl}
	<fieldset><legend>{$LANG.google_checkout.callback_url}</legend>
	{$API_URL}
	</fieldset>
	{/if}
  </div>
  {$MODULE_ZONES}
  <div class="form_control">
	<input type="submit" name="save" value="{$LANG.common.save}" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>