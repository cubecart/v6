<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="Realex" class="tab_content">
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
	  <div><label for="merchant_id">{$LANG.realex.merchant_id}</label><span><input name="module[merchant_id]" id="merchant_id" class="textbox" type="text" value="{$MODULE.merchant_id}" /></span></div>
	  <div><label for="secret_word">{$LANG.realex.secret_word}</label><span><input name="module[secret_word]" id="secret_word" class="textbox" type="text" value="{$MODULE.secret_word}" /></span></div>
	  <div>
	</fieldset>
	<p>{$LANG.realex.contact_realex}</p>
	<strong>{$LANG.realex.referring_url}:</strong> {$URL.referring}<br />
	<strong>{$LANG.realex.response_url}:</strong> {$URL.response}
	<p>{$LANG.module.description_options}</p>
  </div>
  {$MODULE_ZONES}
  <div class="form_control"><input type="submit" name="save" value="{$LANG.common.save}" /></div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>