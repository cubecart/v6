{if $DISPLAY_3DS}
<div id="3dsecure" style="width:100%; height:450px; margin: 0px auto;">
  <iframe width="100%" height="400" style="border: 1px solid #CCCCCC;" scrolling="auto" src="{$STORE_URL}/modules/plugins/PayPal_Pro/3dsecure.php"></iframe>
</div>
{else}
  <fieldset>
	<div><label for="card_type">{$LANG.gateway.card_type}</label>
	  <span>
		<select name="direct[card_type]" id="card_type" class="textbox required">
		  {foreach from=$CARD.types item=type}
		  <option value="{$type.type}"{$type.selected}>{$type.name}</option>
		  {/foreach}
		</select>
	  </span>
	</div>
	<div><label for="card_number">{$LANG.gateway.card_number}</label><span><input type="text" id="card_number" name="direct[card_number]" value="{$CUSTOMER.card_number}" class="textbox required" /></span></div>
	{if $CARD.display_issue}
	<div id="issue_data">
	  <div><label for="card_issue_no">{$LANG.gateway.card_issue_no}</label><span><input type="text" id="card_issue_no" name="direct[issue_no]" value="{$CUSTOMER.issue_no}" size="2" maxlength="2" class="textbox_small" style="text-align: center" /></span></div>
	  <div><label for="card_">{$LANG.gateway.card_issue_date}</label>
		<span>
		  <select name="direct[issue_month]" class="textbox">
			{foreach from=$CARD.issue.months item=month}
			<option value="{$month.value}"{$month.selected}>{$month.display}</option>
			{/foreach}
		  </select>
		  <select name="direct[issue_year]" class="textbox">
			{foreach from=$CARD.issue.years item=year}<option value="{$year.value}"{$year.selected}>{$year.value}</option>{/foreach}
		  </select>
		</span>
	  </div>
	</div>
	{/if}
	<div><label for="card_">{$LANG.gateway.card_expiry_date}</label>
	  <span>
		<select name="direct[exp_month]" class="textbox required">
		  {foreach from=$CARD.expire.months item=month}
		  <option value="{$month.value}"{$month.selected}>{$month.display}</option>
		  {/foreach}
		</select>
		<select name="direct[exp_year]" class="textbox required">
		  {foreach from=$CARD.expire.years item=year}<option value="{$year.value}"{$year.selected}>{$year.value}</option>{/foreach}
		</select>
	  </span>
	</div>
	<div><label for="card_security">{$LANG.gateway.card_security}</label><span><input type="text" id="card_security" name="direct[cvv2]" value="" size="4" maxlength="4" class="textbox_small" style="text-align: center" /></span> <a href="images/general/cvv.gif" class="colorbox" title="{$LANG.gateway.card_security}" /> {$LANG.common.whats_this}</a></div>
  </fieldset>
  
  <fieldset>
	<div><label for="card_first_name">{$LANG.user.name_first}</label><span><input type="text" id="card_first_name" name="direct[first_name]" value="{$CUSTOMER.first_name}" class="textbox required" /></span></div>
	<div><label for="card_last_name">{$LANG.user.name_last}</label><span><input type="text" id="card_last_name" name="direct[last_name]" value="{$CUSTOMER.last_name}" class="textbox required" /></span></div>
	
	<div><label for="card_line1">{$LANG.address.line1}</label><span><input type="text" id="card_line1" name="direct[line1]" value="{$CUSTOMER.line1}" class="textbox required" /></span></div>
	<div><label for="card_line2">{$LANG.address.line2}</label><span><input type="text" id="card_line2" name="direct[line2]" value="{$CUSTOMER.line2}" class="textbox" /></span></div>
	<div><label for="card_town">{$LANG.address.town}</label><span><input type="text" id="card_city" name="direct[town]" value="{$CUSTOMER.town}" class="textbox required" /></span></div>
	<div><label for="country-list">{$LANG.address.country}</label>
	  <span>
		<select name="direct[country_id]" id="country-list" class="textbox required">
		{foreach from=$COUNTRIES item=country}
		  <option value="{$country.numcode}"{$country.selected}>{$country.name}</option>
		{/foreach}
  		</select>
	  </span>
	</div>
	<div><label for="state-list">{$LANG.address.state}</label><span><input type="text" id="state-list" name="direct[state_id]" value="{$CUSTOMER.state_id}" class="textbox required" /></span></div>
	<div><label for="card_postcode">{$LANG.address.postcode}</label><span><input type="text" id="card_postcode" name="direct[postcode]" value="{$CUSTOMER.postcode}" class="textbox required" /></span></div>
	
	<div><label for="card_email">{$LANG.common.email}</label><span><input type="text" id="card_email" name="direct[email]" value="{$CUSTOMER.email}" class="textbox required" /></span></div>
	<div><label for="card_phone">{$LANG.address.phone}</label><span><input type="text" id="card_phone" name="direct[phone]" value="{$CUSTOMER.phone}" class="textbox" /></span></div>
  </fieldset>
  <script type="text/javascript">
	var county_list = {$VAL_JSON_STATE}
  </script>
{/if}