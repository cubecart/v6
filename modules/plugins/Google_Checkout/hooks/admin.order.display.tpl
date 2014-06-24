<div id="google_checkout" class="tab_content">
  <h3>{$LANG.google.module_title}</h3>
  <!--
  <fieldset><legend>Risk Information</legend></fieldset>
  -->
  <fieldset><legend>{$LANG.google.action}</legend>
  <div>
	<label for="google-method">{$LANG.google.method}</label>
	<span>
	  <select name="google[method]" id="google-method" class="section-select">
		<option value="">{$LANG.form.please_select}</option>
		<option value="Message">{$LANG.google.method_message}</option>
		<option value="Charge">{$LANG.google.method_charge}</option>
		<option value="Refund">{$LANG.google.method_refund}</option>
		<option value="Cancel">{$LANG.google.method_cancel}</option>
		<option value="Authorize">{$LANG.google.method_auth}</option>
		<option value="Deliver">{$LANG.google.method_deliver}</option>
	  </select>
	</span>
	<input type="hidden" name="google[google-order-id]" value="{GOOGLE_ORDER_ID}" />
  </div>
  </fieldset>
  
  <fieldset class="section-content" id="Message"><legend>{$LANG.google.title_message}</legend>
	<div><label for="message-comment">{$LANG.common.amount}</label><span><textarea name="google[message][comment]" id="message-comment" class="textbox"></textarea></span></div>
  </fieldset>
  
  <fieldset class="section-content" id="Deliver"><legend>{$LANG.google.title_deliver}</legend>
	<p>{$LANG.google.delivery_info}</p>
  </fieldset>
  
  <fieldset class="section-content" id="Authorize"><legend>{$LANG.google.title_authorize}</legend>
	<p>{$LANG.google.no_data_required}</p>
  </fieldset>
  
  <fieldset class="section-content" id="Charge"><legend>{$LANG.google.method_charge}</legend>
	<div><label for="charge-amount">{$LANG.common.amount}</label><span><input type="text" name="google[charge][amount]" id="charge-amount" class="textbox number" /></span></div>
  </fieldset>

  <fieldset class="section-content" id="Refund"><legend>{$LANG.google.method_refund}</legend>
	<div><label for="refund-amount">{$LANG.common.amount}</label><span><input type="text" name="google[refund][amount]" id="refund-amount" class="textbox number" /></span></div>
	<div><label for="refund-reason">{$LANG.google.reason}</label>
	  <span>
		<select name="google[refund][reason]" id="refund-reason" class="required">
		  <option value="">{$LANG.form.please_select}</option>
		  <option>{$LANG.google.reason_described}</option>
		  <option>{$LANG.google.reason_size}</option>
		  <option>{$LANG.google.reason_price}</option>
		  <option>{$LANG.google.reason_parts}</option>
		  <option>{$LANG.google.reason_damage}</option>
		  <option>{$LANG.google.reason_delivery}</option>
		  <option>{$LANG.google.reason_stock}</option>
		  <option>{$LANG.google.reason_customer}</option>
		  <option>{$LANG.google.reason_discontinued}</option>
		  <option>{$LANG.google.reason_other}</option>
		</select>
	  </span>
	</div>
	<div><label for="refund-comment">{$LANG.common.notes}</label><span><textarea name="google[refund][comment]" id="refund-comment"></textarea></span></div>
  </fieldset>

  <fieldset class="section-content" id="Cancel"><legend>{$LANG.common.cancel}</legend>
	<div>
	  <label for="cancel-reason">{$LANG.google.reason}</label>
	  <span>
		<select name="google[cancel][reason]" id="cancel-reason" class="required">
		  <option value="">{$LANG.form.please_select}</option>
		  <option>{$LANG.google.reason_described}</option>
		  <option>{$LANG.google.reason_size}</option>
		  <option>{$LANG.google.reason_price}</option>
		  <option>{$LANG.google.reason_parts}</option>
		  <option>{$LANG.google.reason_damage}</option>
		  <option>{$LANG.google.reason_delivery}</option>
		  <option>{$LANG.google.reason_stock}</option>
		  <option>{$LANG.google.reason_customer}</option>
		  <option>{$LANG.google.reason_discontinued}</option>
		  <option>{$LANG.google.reason_other}</option>
		</select>
	  </span>
	</div>
	<div><label for="cancel-comment">{$LANG.common.notes}</label><span><textarea name="google[cancel][comment]" id="cancel-comment"></textarea></span></div>
  </fieldset>
</div>