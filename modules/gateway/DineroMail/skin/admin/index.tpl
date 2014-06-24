<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="DineroMail" class="tab_content">
	<h3>{$TITLE}</h3>
	<fieldset>
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
	  <div><label for="dm_account">{$LANG.dineromail.dm_account}</label><span><input name="module[dm_account]" id="dm_account" class="textbox" type="text" value="{$MODULE.dm_account}" /></span></div>
	   <div>
			<label for="dm_country">{$LANG.dineromail.dm_country}</label>
			<span>
				<select name="module[dm_country]">
  						<option value="AR" {$SELECT_dm_country_AR}>Argentina</option>
  						<option value="BR" {$SELECT_dm_country_BR}>Brasil</option>
  						<option value="CL" {$SELECT_dm_country_CL}>Chile</option>
  						<option value="MX" {$SELECT_dm_country_MX}>Mexico</option>
					</select>
			</span>
		</div>
		
		<div>
			<label for="dm_currency">{$LANG.dineromail.dm_currency}</label>
			<span>
				<select name="module[dm_currency]">
  						<option value="AR" {$SELECT_dm_currency_AR}>(ARS)Argentina Pesos</option>
  						<option value="BR" {$SELECT_dm_currency_BR}>(BRL)Brasil Reais</option>
  						<option value="CL" {$SELECT_dm_currency_CL}>(CLP)Chile Pesos</option>
  						<option value="MX" {$SELECT_dm_currency_MX}>(MXP)MŽxico Pesos</option>
  						<option value="US" {$SELECT_dm_currency_US}>(USD)United States Dollars</option>
					</select>
			</span>
		</div>
		<div><label for="dm_payment_methods">{$LANG.dineromail.dm_payment_methods}</label><span><input name="module[dm_payment_methods]" id="dm_payment_methods" class="textbox" type="text" value="{$MODULE.dm_payment_methods}" /></span></div>
		<div>{$LANG.dineromail.dm_payment_methods_desc}</div>
		<div><label for="dm_store_logo_url">{$LANG.dineromail.dm_store_logo_url}</label><span><input name="module[dm_store_logo_url]" id="dm_store_logo_url" class="textbox" type="text" value="{$MODULE.dm_store_logo_url}" /></span></div>
		
		<div>
			<label for="dm_country">{$LANG.dineromail.dm_message}</label>
			<span>
				<select name="module[dm_message]">
  						<option value="0" {$SELECT_dm_message_0}>{$LANG.common.no}</option>
  						<option value="1" {$SELECT_dm_message_1}>{$LANG.common.yes}</option>
					</select>
			</span>
		</div>
		<br />
		<div>
			<label for="dm_delivery_addres">{$LANG.dineromail.dm_delivery_addres}</label>
			<span>
				<select name="module[dm_delivery_addres]">
  						<option value="0" {$SELECT_dm_delivery_addres_0}>{$LANG.common.no}</option>
  						<option value="1" {$SELECT_dm_delivery_addres_1}>{$LANG.common.yes}</option>
					</select>
			</span>
		</div>
		<br />
		<div>
			<label for="pending_notes">{$LANG.dineromail.pending_notes}</label>
			<span>
				{$LANG.order_state.name_1}
			</span>
		</div>
		<br />
		<div>
			<label for="processing_notes">{$LANG.dineromail.processing_notes}</label>
			<span>
				{$LANG.order_state.name_2}
			</span>
		</div>
		<br />
		<div>
			<label for="cancelled_notes">{$LANG.dineromail.cancelled_notes}</label>
			<span>
				{$LANG.order_state.name_6}
			</span>
		</div>
	  
	</fieldset>
	<p>{$LANG.module.description_options}</p>
  </div>
  {$MODULE_ZONES}
  <div class="form_control"><input type="submit" name="save" value="{$LANG.common.save}" /></div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>