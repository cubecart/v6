<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
{literal}
	jQuery(document).ready(function($){
		$('#w2p-cs').change(function() {
			var value = $('#w2p-cs option:selected').val();
			$('input[name="pmethod"]').each(function(){
				$(this).attr('checked', false);
			});
			$('.w2p-table').each(function(){
		        var eid = $(this).attr('id');
		        if(eid == value) {
		        	$(this).css({'display' : 'block'});
		        } else {
		        	$(this).css({'display' : 'none'});
		        }
		    });
		});

		$('tr').click(function(){
			$(this).children('td').children('input').attr('checked', true);
		});
		
		
		$('.button_submit').click(function(event){
			event.preventDefault();
			//alert('ye');
			
			$('#gateway-transfer').submit();
		});
	});
{/literal}
</script>

<table>
	<tr>
		<td style="width:210px">select</td>
		<td>
			<select id="w2p-cs" style="margin:0">
			{foreach from=$payMethods item=country}
				{if $country->getCode() == $defaultCountry}
				<option selected="selected" value="{$country->getCode()}">{$country->getTitle()}</option>
				{else}
				<option value="{$country->getCode()}">{$country->getTitle()}</option>
				{/if}
			{/foreach}
			</select>
		</td>
	</tr>
</table>

<form action="" method="post" >
	<input type="hidden" value="{$gateway}" name="gateway" original="{$gateway}" />
	<input type="hidden" value="1" name="makeRequest" original="makeRequest" />
	{foreach from=$payMethods item=country}
		<table id="{$country->getCode()}" class="w2p-table" style="display:{if $country->getCode() == $defaultCountry}table{else}none{/if}">	
			{foreach from=$country->getGroups() item=group}
	        	<tr>
	            	<td colspan="2"><b>{$group->getTitle()}</b></td>
	            </tr>
	            {foreach from=$group->getPaymentMethods() item=paymentMethod}
	                <tr>
	                	<td style="width:30px"><input type="radio" class="radio" name="payment" value="{$paymentMethod->getKey()}" /></td>
	                    <td><img src="{$paymentMethod->getLogoUrl()}" title="{$paymentMethod->getTitle()}" alt="{$paymentMethod->getTitle()}" /></td>
	                </tr>
				{/foreach}
	    	{/foreach}
		</table>
	{/foreach}
</form>