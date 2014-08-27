<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="ccAvenue" class="tab_content">
  		<h3>{$TITLE}</h3>
		<p class="copyText">{$LANG.ccavenue.module_description}</p>
  		<fieldset><legend>{$LANG.module.cubecart_settings}</legend>
			<div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
			<div><label for="description">{$LANG.common.description} *</label><span><input name="module[desc]" id="description" class="textbox" type="text" value="{$MODULE.desc}" /></span></div>
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
        <label for="scope">{$LANG.ccavenue.mode}</label>
        <span>
          <select name="module[mode]">
                  <option value="test" {$SELECT_mode_test}>{$LANG.ccavenue.test_mode}</option>
                  <option value="live" {$SELECT_mode_live}>{$LANG.ccavenue.live_mode}</option>
              </select>
        </span>
      </div>
      <div><label for="merchant_id">{$LANG.ccavenue.merchant_id}</label><span><input name="module[merchant_id]" id="merchant_id" class="textbox" type="text" value="{$MODULE.merchant_id}" /></span></div>
      <div><label for="access_code">{$LANG.ccavenue.access_code}</label><span><input name="module[access_code]" id="access_code" class="textbox" type="text" value="{$MODULE.access_code}" /></span></div>
      <div><label for="encryption_key">{$LANG.ccavenue.encryption_key}</label><span><input name="module[encryption_key]" id="encryption_key" class="textbox" type="text" value="{$MODULE.encryption_key}" /></span></div>
  		</fieldset>
      <p>{$LANG.module.description_options}</p>
  		</div>
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>