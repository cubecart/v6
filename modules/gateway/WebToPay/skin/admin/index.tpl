<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="WebToPay" class="tab_content">
  		<h3>{$TITLE}</h3>
  		<p>{$LANG.webtopay.module_description}</p>
  		<fieldset><legend>{$LANG.module.cubecart_settings}</legend>
			<div>
				<label for="status">{$LANG.common.status}</label>
				<span>
					<input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" />
				</span>
			</div>
			<div><label for="position">{$LANG.module.position}</label><span><input type="text" name="module[position]" id="position" class="textbox number" value="{$MODULE.position}" /></span></div>
			<div>
				<label for="default">{$LANG.common.default}</label>
				<span>
					<input type="hidden" name="module[default]" id="default" class="toggle" value="{$MODULE.default}" />
				</span>
			</div>
			<div>
				<label for="description">{$LANG.common.description} *</label>
				<span>
					<input name="module[desc]" id="description" class="textbox" type="text" value="{$MODULE.desc}" />
				</span>
			</div>
			<div>
				<label for="projectid">{$LANG.webtopay.projectid}</label>
				<span>
					<input name="module[projectid]" id="projectid" class="textbox" type="text" value="{$MODULE.projectid}" />
				</span>
			</div>
			<div>
				<label for="projectid">{$LANG.webtopay.projectpass}</label>
				<span>
					<input name="module[projectpass]" id="projectpass" class="textbox" type="text" value="{$MODULE.projectpass}" />
				</span>
			</div>
			<div>
				<label for="email">{$LANG.webtopay.mode}</label>
				<span>
					<select name="module[testMode]">
    					<option value="1" {$SELECT_testMode_1}>{$LANG.webtopay.mode_sandbox}</option>
    					<option value="0" {$SELECT_testMode_0}>{$LANG.webtopay.mode_live}</option>
					</select>
				</span>
			</div>
			<div>
				<label for="paymentMethods">{$LANG.webtopay.module_paymethods}</label>
				<span>
					<select name="module[paymentMethods]">
    					<option value="1" {$SELECT_paymentMethods_1}>{$LANG.webtopay.yes}</option>
    					<option value="0" {$SELECT_paymentMethods_0}>{$LANG.webtopay.no}</option>
					</select>
				</span>
			</div>
			<p>{$LANG.module.description_options}</p>
  		</div>
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  	</fieldset>
  	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>