<!DOCTYPE html>
<html lang="{$HTML_LANG}">
<head>
    <title>{$PAGE_TITLE}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <style media="screen,print">
        html,
        body {
            margin: 0.5em;
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

        /* Product row layout */
        div.product {
            padding: 5px 0;
            clear: both;
            border-bottom: 1px dashed #e7e7e7;
            display: flex;
            font-size: .9em;
        }

        .product span {
            display: block;
        }

        /* Column widths */
        .product .col-qty {
            width: 20%;
			text-align: left;
        }

        .product .col-name {
            width: 20%;
			text-align: left;
        }

        .product .col-code {
            width: 20%;
			text-align: left;
        }

        .product .col-price-each {
            width: 20%;
            text-align: left;
        }

        .product .col-total {
            width: 20%;
            text-align: left;
        }

        /* Header row */
        .product-header span {
            font-weight: bold;
        }

        span.options {
            font-style: italic;
        }

        #totals {
            margin-top: 5px;
			
        }

		.total {
			display: flex;
		}
		
		.total div.spacer {
			width: 60%;
        }

		.total div.label {
            text-align: left;
			width: 20%;
        }

        .total div.amount {
            text-align: left;
			width: 20%;
        }

        fieldset {
            border: 1px solid #c7c7c7;
            padding: 5px;
            margin: 10px 30px 0 0;
            border-radius: 6px;
            font-size: 10px;
        }

        fieldset > legend {
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
            max-width: 100% !important;
            display: block;
        }

        a.noprint {
            margin: 3px 0 0 3px;
            display: inline-block;
            font-size: 12px;
            text-decoration: none !important;
            font-family: 'Open Sans', sans-serif;
            padding: 8px 12px;
            border-radius: 3px;
            box-shadow: inset 0 0 2px #fff;
            color: #444;
            border: 1px solid #d0d0d0;
            background-image: -webkit-linear-gradient(#ededed, #e1e1e1);
            text-shadow: 1px 1px 1px #fff;
            background-color: #e1e1e1;
        }

        a.noprint:hover {
            border: 1px solid #b0b0b0;
            background-color: #ededed;
        }

        .w3w {
            color: #E11F26;
            display: block;
        }

        .w3w a {
            color: #333333;
            text-decoration: none;
        }

        .capitalize {
            text-transform: capitalize;
        }

        .lowercase {
            text-transform: lowercase;
        }

        .uppercase {
            text-transform: uppercase;
        }
		.double-underline {
			text-decoration-line: underline;
			text-decoration-style: double;
		}
        @media print {
            .noprint,
            .noprint * {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <a href="../{$SKIN_VARS.admin_file}?_g=documents&node=invoice" class="noprint uppercase">
        {$LANG.common.customise_layout}
    </a>

    {if isset($ORDER_LIST)}
    {foreach from=$ORDER_LIST item=order}

    <div class="page-break">

        <!-- Header -->
        <div id="header">

            <div id="printLabel">
                <div>
                    {if !empty($order.name_d) && empty($order.last_name_d)}
                        {$order.name_d}
                    {else}
                        {$order.first_name_d} {$order.last_name_d}
                    {/if}
                    <br>

                    {if !empty($order.company_name_d)}
                        {$order.company_name_d}<br>
                    {/if}

                    <span class="capitalize">
                        {$order.line1_d}<br>
                        {if !empty($order.line2_d)}{$order.line2_d}<br>{/if}
                    </span>

                    <span class="uppercase">
                        {$order.town_d}<br>
                        {if !empty($order.state_d)}{$order.state_d}, {/if}
                    </span>

                    {$order.postcode_d}

                    {if $CONFIG.store_country_name !== $order.country_d}
                        <br>{$order.country_d}
                    {/if}

                    {if !empty($order.w3w_d)}
                        <br>
                        <div class="w3w">/// <a href="https://what3words.com/{$order.w3w_d}">{$order.w3w_d}</a></div>
                    {/if}
                </div>

                <div class="sender">
                    {if !empty($STORE.address)}
                        {$LANG.address.return_address}<br>{$STORE.address},
                    {/if}
                    {if !empty($STORE.county)}
                        <span class="uppercase">{$STORE.county}</span>,
                    {/if}
                    {if !empty($STORE.postcode)}
                        {$STORE.postcode}
                    {/if}
                    {if $CONFIG.store_country_name !== $order.country_d}
                        {$STORE.country}
                    {/if}
                </div>
            </div>

            <div id="storeLabel">
                <img src="{$STORE_LOGO}" alt="">
            </div>
        </div>

        <!-- Order info -->
        <div class="info">
            <span class="orderid">
                <strong>{$LANG.common.order_id}</strong>
                {$order.{$CONFIG.oid_col}|default:$order.order_id}
            </span>

            <strong>{$LANG.orders.title_receipt_for}</strong>
            {$order.order_date}
        </div>

        <!-- Product header -->
        <div class="product product-header">
			<span class="col-name">{$LANG.common.product}</span>
            <span class="col-code">{$LANG.catalogue.product_code}</span>
            <span class="col-price-each">{$LANG.common.price}</span>
			<span class="col-qty">{$LANG.common.quantity}</span>
            <span class="col-total">{$LANG.common.total}</span>
        </div>

        <!-- Product rows -->
        {foreach from=$order.items item=item}
        <div class="product">

            <span class="col-name">
                {$item.name}
                {if isset($item.options)}
                    <ul>
                        <!--{foreach from=$item.options item=option}-->
                        <li>{$option}</li>
                        <!--{/foreach}-->
                    </ul>
                {/if}
            </span>

            <span class="col-code">
                {if !empty($item.product_code)}
                    {$item.product_code}
                {/if}
            </span>

            <span class="col-price-each">
                {$item.item_price}
            </span>

			<span class="col-qty">
                {$item.quantity}
            </span>

            <span class="col-total">
                {$item.price}
            </span>

        </div>
        {/foreach}

        <!-- Totals -->
        <div id="totals">
			
            <div class="total">
				<div class="spacer"></div>
				<div class="label">{$LANG.basket.total_sub}</div>
                <div class="amount">{$order.subtotal}</div>
            </div>

            <div class="total">
				<div class="spacer"></div>
				<div class="label">{$LANG.basket.total_discount} {if !empty($order.percent)}({$order.percent}){/if}</div>
                <div class="amount">{$order.discount}</div>
            </div>

            <div class="total">
				<div class="spacer"></div>
				<div class="label">{$LANG.basket.shipping}</div>
                <div class="amount">{$order.shipping}</div>
            </div>

            {if isset($order.taxes)}
                {foreach from=$order.taxes item=tax}
                    <div class="total">
						<div class="spacer"></div>
						<div class="label">{$tax.display}</div>
                        <div class="amount">{if $tax.included}({$tax.value}){else}{$tax.value}{/if}</div>
                    </div>
                {/foreach}
            {/if}

            {if $order.show_credit}
                <div class="total">
					<div class="spacer"></div>
					<div class="label">{$LANG.common.credit}</div>
                    <div class="amount">({$order.credit_used})</div>
                </div>
            {/if}

            <div class="total">
				<div class="spacer"></div>
				<div class="label"><strong>{$LANG.basket.total_grand}</strong></div>
                <div class="amount double-underline"><strong>{$order.total}</strong></div>
            </div>
        </div>

        <!-- Customer comments -->
        {if !empty($order.customer_comments)}
        <div id="customer_comments">
            <strong>{$LANG.orders.title_notes_extra}</strong>
            - {$order.customer_comments}
        </div>
        {/if}

        <!-- Contact details -->
        <fieldset class="other">
            <legend>{$LANG.account.contact_details}</legend>

            <div>
                <label>{$LANG.common.email}</label>
                <span><a href="mailto:{$order.email}">{$order.email}</a></span>
            </div>

            <div>
                <label>{$LANG.address.phone}</label>
                <span>{$order.phone}</span>
            </div>

            <div>
                <label>{$LANG.address.mobile}</label>
                <span>{$order.mobile}</span>
            </div>
        </fieldset>

        <!-- Shipping details -->
        <fieldset class="other">
            <legend>{$LANG.orders.title_shipping}</legend>

            {if !empty($order.gateway)}
                <div><label>{$LANG.orders.gateway_name}</label><span>{ucwords($order.gateway)}</span></div>
            {/if}

            {if !empty($order.ship_date)}
                <div><label>{$LANG.orders.shipping_date}</label><span>{$order.ship_date}</span></div>
            {/if}

            {if !empty($order.ship_method)}
                <div><label>{$LANG.orders.shipping_method}</label><span>{str_replace('_',' ',$order.ship_method)}</span></div>
            {/if}

            {if !empty($order.ship_product)}
                <div><label>{$LANG.orders.shipping_product}</label><span>{str_replace('_',' ',$order.ship_product)}</span></div>
            {/if}

            {if !empty($order.ship_tracking)}
                <div><label>{$LANG.orders.shipping_tracking}</label><span>{$order.ship_tracking}</span></div>
            {/if}

            {if $order.weight > 0}
                <div><label>{$LANG.common.weight}</label><span>{$order.weight}{$CONFIG.product_weight_unit}</span></div>
            {/if}
        </fieldset>

        <div id="thanks">{$LANG.orders.title_thanks}</div>

        <div id="footer">
            <p>
                {$STORE.address},
                {if !empty($STORE.county)}{$STORE.county}, {/if}
                {if !empty($STORE.postcode)}{$STORE.postcode} {/if}
                {if !empty($STORE.country)}{$STORE.country}{/if}
            </p>
        </div>

    </div> <!-- /page-break -->

    <!-- Optional Notes Page -->
    {if !empty($order.notes)}
    <div class="page-break">

        <div id="header">

            <div id="printLabel">
                <div>{$order.address}</div>

                <div class="sender">
                    {$LANG.address.return_address}<br>
                    {$STORE.address}, {$STORE.county}, {$STORE.postcode} {$STORE.country}
                </div>
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