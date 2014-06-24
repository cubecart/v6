<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="Print_Order_Form" class="tab_content">
	<h3>{$TITLE}</h3>
	<fieldset><legend>{$LANG.module.config_settings}</legend>
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
	  <div><label for="multiCurrency">{$LANG.print_order_form.currency_multi}</label><span><input type="hidden" name="module[multiCurrency]" id="multiCurrency" class="toggle" value="{$MODULE.multiCurrency}" /></span></div>
	  <div><label for="cheque">{$LANG.print_order_form.cheque_allow}</label><span><input type="hidden" name="module[cheque]" id="cheque" class="toggle" value="{$MODULE.cheque}" /></span></div>
	  <div><label for="payableTo">{$LANG.print_order_form.cheque_payable}</label><span><input type="text" name="module[payableTo]" value="{$MODULE.payableTo}" class="textbox" size="30" /></span></div>
	  <div><label for="card">{$LANG.print_order_form.cards_allow}?</label><span><input type="hidden" name="module[card]" id="card" class="toggle" value="{$MODULE.card}" /></span></div>
	  <div><label for="cards">{$LANG.print_order_form.cards_accept}</label><span><input type="text" name="module[cards]" id="cards" class="textbox" value="{$MODULE.cards}" class="textbox" size="30" /> {$LANG.common.eg} {$LANG.print_order_form.cards_example}</span></div>
	  <div><label for="status">{$LANG.print_order_form.confirmation_email}</label><span><input type="hidden" name="module[confirmation_email]" id="confirmation_email" class="toggle" value="{$MODULE.confirmation_email}" /></span></div>
	</fieldset>

	<fieldset><legend>{$LANG.print_order_form.title_bank_transfer}</legend>
	  <div><label for="bank">{$LANG.print_order_form.bank_allow}</label><span><input type="hidden" name="module[bank]" id="bank" class="toggle" value="{$MODULE.bank}" /></span></div>
	  <div><label for="bankName">{$LANG.print_order_form.bank_name}</label><span><input name="module[bankName]" id="bankName" class="textbox" type="text" value="{$MODULE.bankName}" /></span></div>
	  <div><label for="accName">{$LANG.print_order_form.bank_account_name}</label><span><input type="text" name="module[accName]" value="{$MODULE.accName}" class="textbox" size="30" /></span></div>
	  <div><label for="sortCode">{$LANG.print_order_form.bank_sort}</label><span><input type="text" name="module[sortCode]" value="{$MODULE.sortCode}" class="textbox" size="30" /></span></div>
	  <div><label for="acNo">{$LANG.print_order_form.bank_account}</label><span><input type="text" name="module[acNo]" value="{$MODULE.acNo}" class="textbox" size="30" /></span></div>
	  <div><label for="swiftCode">{$LANG.print_order_form.bank_swift}</label><span><input type="text" name="module[swiftCode]" value="{$MODULE.swiftCode}" class="textbox" size="30" /></span></div>
	  <div><label for="address">{$LANG.print_order_form.address}</label><span><textarea name="module[address]" cols="30" rows="5">{$MODULE.address}</textarea></span></div>
	</fieldset>

	<fieldset><legend>{$LANG.common.notes}</legend>
	  <div><label for="notes">{$LANG.print_order_form.notes}</label><span><textarea name="module[notes]" cols="30" rows="5">{$MODULE.notes}</textarea></span></div>
	</fieldset>
	<p>{$LANG.module.description_options}</p>
  </div>
  {$MODULE_ZONES}
  <div class="form_control"><input type="submit" name="save" value="{$LANG.common.save}" /></div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>