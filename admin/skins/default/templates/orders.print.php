<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>{$PAGE_TITLE}</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="../{$SKIN_VARS.admin_folder}/styles/print.css" media="screen,print">
</head>
<body onload="window.print();">
  {if isset($ORDER_LIST)}
  {foreach from=$ORDER_LIST item=order}
  <div class="page-break">
	  <div id="header">
		<div id="printLabel">
		  <div>
		  	{if !empty($order.name_d) && empty($order.last_name_d)}{$order.name_d}{else}{$order.title_d} {$order.first_name_d} {$order.last_name_d}{/if}<br>
	  		{if !empty($order.company_name_d)}{$order.company_name_d}<br>{/if}
	  		{$order.line1_d} <br>
	  		{if !empty($order.line2_d)}{$order.line2_d}<br>{/if}
	  		{$order.town_d}<br>
	  		{$order.state_d}, {$order.postcode_d}<br>
	  		{$order.country_d}
		  </div>
		  <div class="sender">{$LANG.address.return_address}<br>{$STORE.address}, {$STORE.county}, {$STORE.postcode} {$STORE.country}</div>
		</div>
		<div id="storeLabel">
		  <img src="{$STORE_LOGO}" alt="">
		</div>
	  </div>

	  <div class="info">
		<span class="orderid"><strong>{$LANG.common.order_id}</strong> &nbsp; {$order.cart_order_id}</span>
		<strong>{$LANG.orders.title_receipt_for}</strong> {$order.order_date}
	  </div>

	  <div class="product">
		<span class="price">{$LANG.common.price}</span>
		<strong>{$LANG.common.product}</strong>
	  </div>
	  {foreach from=$order.items item=item}
	  <div class="product">
		<span class="price">{$item.price}</span>{$item.quantity} &times; {$item.name} {if !empty($item.product_code)}({$item.product_code}){/if}
		{if isset($item.options)}
		<br>{$LANG.catalogue.title_options} {foreach from=$item.options item=option}&raquo; {$option}{/foreach}
		{/if}
	  </div>
	  {/foreach}
	  <div id="totals">
		<div class="total">{$LANG.basket.total_sub} <strong>{$order.subtotal}</strong></div>
		<div class="total">{$LANG.basket.total_discount} {if !empty($order.percent)}({$order.percent}){/if} <strong>{$order.discount}</strong></div>
		<div class="total">{$LANG.basket.shipping} <strong>{$order.shipping}</strong></div>
		{if isset($order.taxes)} {foreach from=$order.taxes item=tax}
		<div class="total">{$tax.name} <strong>{$tax.value}</strong></div>
		{/foreach}{/if}
		<br>
		<div class="total"><strong>{$LANG.basket.total_grand} {$order.total}</strong></div>
	  </div>
	  {if !empty($order.customer_comments)}
	  <div id=" "><strong>{$LANG.orders.title_notes_extra}</strong> - {$order.customer_comments}</div>
	  {/if}
	  <fieldset class="other"><legend>{$LANG.account.contact_details}</legend>
		<div><label>{$LANG.common.email}</label><span><a href="mailto:{$order.email}">{$order.email}</a></span></div>
		<div><label>{$LANG.address.phone}</label><span>{$order.phone}</span></div>
		<div><label>{$LANG.address.mobile}</label><span>{$order.mobile}</span></div>
	  </fieldset>
	  <fieldset class="other"><legend>{$LANG.orders.title_shipping}</legend>
		<div><label>{$LANG.orders.gateway_name}</label><span>{$order.gateway}</span></div>
		<div><label>{$LANG.orders.shipping_date}</label><span>{$order.ship_date}</span></div>
		<div><label>{$LANG.orders.shipping_method}</label><span>{$order.ship_method}</span></div>
		<div><label>{$LANG.orders.shipping_tracking}</label><span>{$order.ship_tracking}</span></div>
	  </fieldset>
	  <div id="thanks">{$LANG.orders.title_thanks}</div>
	  <div id="footer">
		<p>{$STORE.address}, {$STORE.county}, {$STORE.postcode} {$STORE.country}</p>
	  </div>
  </div>
  {if !empty($order.notes)}
  <div class="page-break">
  	<div id="header">
		<div id="printLabel">
		  <div>{$order.address}</div>
		  <div class="sender">{$LANG.address.return_address}<br>{$STORE.address}, {$STORE.county}, {$STORE.postcode} {$STORE.country}</div>
		</div>
		<div id="storeLabel">
		  <img src="{$STORE_LOGO}" alt="">
		</div>
	</div>
	<div class="info">
		{foreach from=$order.notes item=note}
			{$note}
		{/foreach}
	</div>
  </div>
  {/if}
  {/foreach}
  {/if}
</body>
</html>