<h2>{$LANG.orders.title_card_details}</h2>
<table width="100%" cellpadding="3" cellspacing="10" border="0">
	<tr>
		<td width="140">{$LANG.user.name_first}</td>
		<td><input type="text" name="firstName" value="{$CUSTOMER.first_name}" /></td>
	</tr>
	<tr>
		<td width="140">{$LANG.user.name_last}</td>
		<td><input type="text" name="lastName" value="{$CUSTOMER.last_name}" /></td>
  	</tr>
  	<tr>
		<td width="140">{$LANG.gateway.card_number}
		<td><input type="text" name="cardNumber" value="" size="16" maxlength="16" /></td>
  	</tr>
    <tr>
		<td width="140">{$LANG.gateway.card_expiry_date}</td>
		<td>
			<select name="expirationMonth" >
			{foreach from=$CARD.months item=month}<option value="{$month.value}" {$month.selected}>{$month.display}</option>{/foreach}
    		</select> 
				/ 
			<select name="expirationYear" >
			{foreach from=$CARD.years item=year}<option value="{$year.value}" {$year.selected}>{$year.value}</option>{/foreach}
			</select>
		</td>
  	</tr>
  	<tr>
		<td width="140">{$LANG.gateway.card_security}
		<td><input type="text" name="cvc2" value="" size="5" maxlength="4" class="textbox_small" style="text-align: center" />
		<a href="images/general/cvv.gif" class="colorbox" title="{$LANG.gateway.card_security}" /> {$LANG.common.whats_this}</a>
		</td>
	</tr>
</table>
  
<h2>{$LANG.basket.customer_info}</h3>
<table width="100%" cellpadding="3" cellspacing="10" border="0">				
	<tr>
		<td width="140">{$LANG.common.email}</td>
		<td><input type="text" name="emailAddress" value="{$CUSTOMER.email}" size="50" /></td>
  	</tr>
  	<tr>
		<td width="140">{$LANG.address.line1}</td>
		<td><input type="text" name="addr1" value="{$CUSTOMER.add1}" size="50" /></td>
  	</tr>
  	<tr>
		<td width="140">{$LANG.address.line2}</td>
		<td><input type="text" name="addr2" value="{$CUSTOMER.add2}" size="50" /> {$LANG.common.optional}</td>
  	</tr>
  	<tr>
		<td width="140">{$LANG.address.town}</td>
		<td><input type="text" name="city" value="{$CUSTOMER.city}" /></td>
  	</tr>
  	<tr>
		<td width="140">{$LANG.address.country}
		<td>
	  	<select name="country" >
			{foreach from=$COUNTRIES item=country}<option value="{$country.iso}"{$country.selected}>{$country.name}</option>{/foreach}
		</select>
		</td>
  	</tr>
  	<tr>
		<td width="140">{$LANG.address.state}</td>
		<td><input type="text" name="state" value="{$CUSTOMER.state}" size="10" /></td>
  	</tr>
  	<tr>
		<td width="140">{$LANG.address.postcode}</td>
		<td><input type="text" name="postcode" value="{$CUSTOMER.postcode}" size="10" maxlength="10" /></td>
  	</tr>
</table>