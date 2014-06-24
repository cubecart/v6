<form method="post" enctype="multipart/form-data">
  <div id="PostcodeAnywhere" class="tab_content">
	<h3>{$LANG.poscodeanywhere.module_title}</h3>
	<p>{$LANG.poscodeanywhere.sign_up}</p>
	  <fieldset><legend>{$LANG.module.config_settings}</legend>
	<div><label for="status">{$LANG.common.status}</label><span><select name="module[status]" id="status" class="textbox">
	  <option value="0" {$SELECT_status_0}>{$LANG.common.disabled}</option>
	  <option value="1" {$SELECT_status_1}>{$LANG.common.enabled}</option>
	</select>	
	</span></div>
	<div><label for="capture_key">{$LANG.poscodeanywhere.capture_key}</label><span><input name="module[capture_key]" id="capture_key" class="textbox" type="text" value="{$MODULE.capture_key}" /></span></div>
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
  </fieldset>
  </div>
  <div class="form_control">
	<input type="submit" value="{$LANG.common.save}" name="save" />
  </div>
  
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>