<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="ebs" class="tab_content">
  		<h3>{$TITLE}</h3>
		<p class="copyText">{$LANG.ebs.module_description}</p>
  		<fieldset><legend>{$LANG.module.cubecart_settings}</legend>
			<div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
			<div><label for="default">{$LANG.common.default}</label><span><input type="hidden" name="module[default]" id="default" class="toggle" value="{$MODULE.default}" /></span></div>
			<div><label for="acno">{$LANG.ebs.account_id}</label><span><input name="module[account_id]" id="acno" class="textbox" type="text" value="{$MODULE.account_id}" /></span></div>
			<div><label for="secretkey">{$LANG.ebs.secretkey}</label><span><input name="module[secret_key]" id="acno" class="textbox" type="text" value="{$MODULE.secret_key}" /></span></div>
			<div>
				<label for="testMode">{$LANG.module.mode_test}</label>
					<span>
						<select name="module[testMode]">
      						<option value="Y" {$SELECT_testMode_Y}>{$LANG.common.on}</option>
      						<option value="N" {$SELECT_testMode_N}>{$LANG.common.off}</option>
    					</select>
    				</span>
    		</div>
  		</fieldset>  		
  		</div>
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>