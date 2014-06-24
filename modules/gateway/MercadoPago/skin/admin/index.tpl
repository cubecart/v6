<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="MercadoPago" class="tab_content">
  		<h3>{$TITLE}</h3>
		<p class="copyText">{$LANG.MercadoPago.module_description}</p>
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
			<div><label for="acc_id">{$LANG.MercadoPago.acc_id}</label><span><input name="module[acc_id]" id="acc_id" class="textbox" type="text" value="{$MODULE.acc_id}" /></span></div>
            <div><label for="codigo">{$LANG.MercadoPago.codigo}</label><span><input name="module[codigo]" id="codigo" class="textbox" type="text" value="{$MODULE.codigo}" /></span></div>
            <div><label for="country">{$LANG.MercadoPago.country}</label><span><input name="module[country]" id="country" class="textbox" type="text" value="{$MODULE.country}" /></span></div>
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