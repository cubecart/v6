<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>{$PAGE_TITLE}</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <style media="screen,print">
		html,
		body {
				margin: 0;
				padding: 0;
				font-family: Verdana, Arial, Helvetica, sans-serif;
				font-size: .9em;
				border: 0;
		}
		hr {
				height: 1px;
				border: 0;
				color: #000;
				background-color: #000;
		}
		#info {
				display: none;
				visibility: hidden;
		}
		#header {
				padding-bottom: 7px;
				border-bottom: 1px solid #666;
				background-repeat: no-repeat;
				background-position: top left;
				overflow: visible;
		}
		#printLabel {
				width: 80mm;
				height: 50mm;
				float: right;
				padding: 4mm;
				z-index: 100;
		}
		.sender {
				border-top: 1px solid #ccc;
				margin-top: 20px;
				padding-top: 4px;
				font-size: .7em;
		}
		#storeLabel {
				width: 80mm;
				height: 50mm;
				padding-top: 4mm;
				overflow: hidden;
				z-index: -1;
		}
		#storeLabel h3 {
				text-align: center;
				margin: 0;
				padding: 0;
		}
		div.info {
				margin: 10px 0 30px;
		}
		span.orderid {
				width: 230px;
				float: right;
		}
		div.product {
				padding: 5px 0;
				clear: both;
				border-bottom: 1px dashed #e7e7e7;
		}
		span.price {
				float: right;
				font-weight: bold;
		}
		span.options {
				font-style: italic;
		}
		#totals {
				margin-top: 5px;
		}
		#totals div.total {
				text-align: right;
		}
		fieldset {
				border: 1px solid #c7c7c7;
				padding: 5px;
				margin: 10px 30px 0 0;
				-moz-border-radius: 6px;
				-webkit-border-radius: 6px;
				font-size: 10px;
		}
		fieldset>legend {
				padding: 0 7px;
				font-weight: bold;
		}
		.other {
				width: 400px;
		}
		.other label {
				float: left;
				width: 180px;
				margin: 0;
				padding: 0;
		}
		#thanks {
				margin-top: 20px;
				text-align: center;
				font-weight: bold;
		}
		#footer {
				margin: 10px 0 0;
				padding-top: 5px;
				border-top: 1px solid #666;
				text-align: center;
				font-size: .8em;
		}
		#footer p {
				margin: 0;
		}
		.page-break {
				page-break-after: always;
		}
		#storeLabel img {
				max-width: 100%!important;
				display: block;
		}
		a.noprint {
				margin: 3px 0 0 3px;
				display: inline-block;
				font-size: 12px;
				text-decoration: none!important;
				font-family: 'Open Sans', sans-serif;
				padding: 8px 12px;
				border-radius: 3px;
				-moz-border-radius: 3px;
				box-shadow: inset 0 0 2px #fff;
				-o-box-shadow: inset 0 0 2px #fff;
				-webkit-box-shadow: inset 0 0 2px #fff;
				-moz-box-shadow: inset 0 0 2px #fff;
				color: #444;
				border: 1px solid #d0d0d0;
				background-image: -moz-linear-gradient(#ededed, #e1e1e1);
				background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#e1e1e1), to(#ededed));
				background-image: -webkit-linear-gradient(#ededed, #e1e1e1);
				background-image: -o-linear-gradient(#ededed, #e1e1e1);
				text-shadow: 1px 1px 1px #fff;
				background-color: #e1e1e1;
		}
		a.noprint:hover {
				border: 1px solid #b0b0b0;
				background-image: -moz-linear-gradient(#e1e1e1, #ededed);
				background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#ededed), to(#e1e1e1));
				background-image: -webkit-linear-gradient(#e1e1e1, #ededed);
				background-image: -o-linear-gradient(#e1e1e1, #ededed);
				background-color: #ededed
		}
		@media print
		{
				.noprint, .noprint *
				{
						display: none !important;
				}
		}
	</style>
</head>
<body>
<a href="../{$SKIN_VARS.admin_file}?_g=documents&node=invoice" class="noprint">{$LANG.common.customise_layout|upper}</a>
  {if isset($ORDER_LIST)}
  {foreach from=$ORDER_LIST item=order}
  <div class="page-break">
	  <div id="header">
		<div id="printLabel">
		  <div>
		  	{if !empty($order.name_d) && empty($order.last_name_d)}{$order.name_d}{else}{$order.title_d} {$order.first_name_d} {$order.last_name_d}{/if}<br>
	  		{if !empty($order.company_name_d)}{$order.company_name_d}<br>{/if}
	  		{$order.line1_d|capitalize} <br>
	  		{if !empty($order.line2_d)}{$order.line2_d|capitalize}<br>{/if}
	  		{$order.town_d|upper}<br>
	  		{if !empty($order.state_d)}{$order.state_d|upper}, {/if}{$order.postcode_d}{if $CONFIG.store_country_name!==$order.country_d}<br>
	  		{$order.country_d}{/if}
		  </div>
		  <div class="sender">
				{if !empty($STORE.address)}{$LANG.address.return_address}<br>{$STORE.address},{/if}
				{if !empty($STORE.county)}{$STORE.county|upper},{/if}
				{if !empty($STORE.postcode)}{$STORE.postcode}{/if}
				{if $CONFIG.store_country_name!==$order.country_d}{$STORE.country}{/if}
			</div>
		</div>
		<div id="storeLabel">
		  <img src="{$STORE_LOGO}" alt="">
		</div>
	  </div>

	  <div class="info">
		<span class="orderid"><strong>{$LANG.common.order_id}</strong> &nbsp; {if $CONFIG.oid_mode=='i'}{$order.{$CONFIG.oid_col}}{else}{$order.cart_order_id}{/if}</span>
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
		<ul>
		<!--{foreach from=$item.options item=option}-->
		<li>{$option}</li>
		<!--{/foreach}-->
		</ul>
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
		{if !empty($order.gateway)}
		<div><label>{$LANG.orders.gateway_name}</label><span>{$order.gateway}</span></div>
		{/if}
		{if !empty($order.ship_date)}
		<div><label>{$LANG.orders.shipping_date}</label><span>{$order.ship_date}</span></div>
		{/if}
		{if !empty($order.ship_method)}
		<div><label>{$LANG.orders.shipping_method}</label><span>{$order.ship_method|replace:'_':' '}</span></div>
		{/if}
		{if !empty($order.ship_product)}
		<div><label>{$LANG.orders.shipping_product}</label><span>{$order.ship_product|replace:'_':' '}</span></div>
		{/if}
		{if !empty($order.ship_tracking)}
		<div><label>{$LANG.orders.shipping_tracking}</label><span>{$order.ship_tracking}</span></div>
		{/if}
		{if $order.weight>0}
		<div><label>{$LANG.common.weight}</label><span>{$order.weight}{$CONFIG.product_weight_unit}</span></div>
		{/if}
	  </fieldset>
	  <div id="thanks">{$LANG.orders.title_thanks}</div>
	  <div id="footer">
		<p>{$STORE.address}, {if !empty($STORE.county)}{$STORE.county}, {/if}{if !empty($STORE.postcode)}{$STORE.postcode} {/if}{if !empty($STORE.country)}{$STORE.country}{/if}</p>
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
	<script>
	setTimeout(function(){ window.print(); }, 2000);
	</script>
</body>
</html>