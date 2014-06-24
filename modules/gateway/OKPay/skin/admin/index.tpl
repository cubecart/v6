<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="OKPAY" class="tab_content">
		<h3>{$TITLE}</h3>
		<p>{$LANG.okpay.module_description}</p>
		<fieldset>
			<legend>{$LANG.module.cubecart_settings}</legend>
			<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
			<div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
			<div><label for="position">{$LANG.module.position}</label><span><input type="text" name="module[position]" id="position" class="textbox number" value="{$MODULE.position}" /></span></div>
			<div><label for="default">{$LANG.common.default}</label><span><input type="hidden" name="module[default]" id="default" class="toggle" value="{$MODULE.default}" /></span></div>
			<div><label for="description">{$LANG.common.description} *</label><span><input name="module[desc]" id="description" class="textbox" type="text" value="{$MODULE.desc}" /></span></div>
			<div><label for="email">{$LANG.okpay.wallet_id}</label><span><input name="module[email]" id="email" class="textbox" type="text" value="{$MODULE.email}" /></span></div>
			<div>
				<label for="use_ssl">{$LANG.okpay.use_ssl}</label>
					<span>
						<select name="module[use_ssl]">
							<option value="Y" {$SELECT_use_ssl_Y}>{$LANG.common.on}</option>
							<option value="N" {$SELECT_use_ssl_N}>{$LANG.common.off}</option>
						</select>
					</span>
			</div>
			{$MODULE_ZONES}
		</fieldset>
		<p>{$LANG.module.description_options}</p>
		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
		</div>
	</div>
</form>