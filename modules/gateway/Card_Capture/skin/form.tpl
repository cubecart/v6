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
  		  <td width="140">{$LANG.gateway.card_type}</td>              
  		  <td>
  		  	<select name="cardType">
  		    	{foreach from=$CARDS item=card}
  		    	<option value="{$card.value}" {$card.selected}>{$card.display}</option>
  		    	{/foreach}
		    </select>
		  </td>          
	    </tr>
  		<tr>
  		  <td width="140">{$LANG.gateway.card_number}</td>                  
  		  <td><input type="text" name="cardNumber" value="" size="18" maxlength="18" /></td>          
	  </tr>
  		<tr>
  		  <td width="140">{$LANG.gateway.card_expiry_date}</td>       
  		  <td>
  		  	<select name="expirationMonth">
  		    	{foreach from=$EXPIRE_MONTHS item=month}<option value="{$month.value}" {$month.selected}>{$month.display}</option>{/foreach}
		    </select>
			/
			<select name="expirationYear">
  				{foreach from=$EXPIRE_YEARS item=year}<option value="{$year.value}" {$year.selected}>{$year.value}</option>{/foreach}
			</select>
		  </td>
	  </tr>
	  
	  {if $CVV.enabled}
  		<tr>
			<td width="140">{$LANG.gateway.card_security}</td>
		    <td>
		    <input type="text" name="cvc2" value="" size="5" maxlength="{$CVV.length}" style="text-align: center" class="textbox_small" />
            <a href="images/general/cvv.gif" class="colorbox"> {$LANG.common.whats_this}</a> 
            </td>           
	    </tr>
    	{/if}
    	{if $START}
    	<tr>
    		<td width="140">{$LANG.gateway.card_issue_date}</td>
    		<td><select name="startMonth">
    		  {foreach from=$START.months item=month}<option value="{$month.value}" {$month.selected}>{$month.display}</option>{/foreach}
  		  </select>
			/
			<select name="startYear">
			  {foreach from=$START.years item=year}<option value="{$year.value}" {$year.selected}>{$year.value}</option>{/foreach}
			</select></td>
			<tr>
			<td>&nbsp;</td>
			<td>- or -</td>
			</tr>
			  <tr>
			  <td>
			  {$LANG.gateway.card_issue_no}</td> 
    		<td><input type="text" name="issue" value="{$CUSTOMER.issue}" size="3" maxlength="3" style="text-align: center" class="textbox_small" /></td>
		</tr>
		{/if}
		</table>
		
		<h2>{$LANG.basket.customer_info}</h2>
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
	<td width="140">{$LANG.address.city}</td>
	<td><input type="text" name="city" value="{$CUSTOMER.city}" /></td>
  </tr>
  <tr>
	<td width="140">{$LANG.address.postcode}</td>
	<td><input type="text" name="postcode" value="{$CUSTOMER.postcode}" size="10" maxlength="10" /></td>
  </tr>
  <tr>
	<td width="140">{$LANG.address.country}
	<td>
	  <select name="country">
		{foreach from=$COUNTRY item=country}<option value="{$country.iso}"{$country.selected}>{$country.name}</option>{/foreach}
		</select>
	</td>
  </tr>
  <tr>
	<td width="140">{$LANG.address.state}</td>
	<td><input type="text" name="state" value="{$CUSTOMER.state}" size="10" /></td>
  </tr>
</table>