<h2>{$LANG.orders.order_number}: #{$SUM.cart_order_id}</h2>
<div class="order_status marg-top">{$LANG.orders.title_order_status}: <span class="order_status_{$SUM.status}">{$SUM.order_status}</span></div>
<hr>
<h3>{$LANG.basket.customer_info}</h3>
<div class="row">
   <div class="small-6 columns">
      <strong>{$LANG.address.billing_address}</strong><br>
      {$SUM.title} {$SUM.first_name} {$SUM.last_name}<br>
      {if $SUM.company_name}{$SUM.company_name}<br>{/if}
      {$SUM.line1}<br>
      {if $SUM.line2}{$SUM.line2}<br>{/if}
      {$SUM.town}<br>
      {$SUM.state}, {$SUM.postcode}<br>
      {$SUM.country}
   </div>
   <div class="small-6 columns">
      <strong>{$LANG.address.delivery_address}</strong><br>
      {$SUM.title_d} {$SUM.first_name_d} {$SUM.last_name_d}<br>
      {if $SUM.company_name_d}{$SUM.company_name_d}<br>{/if}
      {$SUM.line1_d}<br>
      {if $SUM.line2_d}{$SUM.line2_d}<br>{/if}
      {$SUM.town_d}<br>
      {$SUM.state_d}, {$SUM.postcode_d}<br>
      {$SUM.country_d}
   </div>
</div>
{if $DELIVERY}
<h3>{$LANG.common.delivery}</h3>
{if !empty($DELIVERY.date)}
<p>{$LANG.orders.shipping_date}: {$DELIVERY.date}</p>
{/if}
{if isset($DELIVERY.url)}
<p>{$LANG.basket.track}: <a href="{$DELIVERY.url}" target="_blank">{$DELIVERY.method}</a></p>
{else}
<p>{$DELIVERY.method} - {$LANG.orders.shipping_tracking}: {$DELIVERY.tracking}
   {/if}
   {/if}
<hr>
<h3>{$LANG.basket.order_summary}</h3>
<table class="expand">
   <thead>
      <tr>
         <th>{$LANG.common.product}</th>
         <th>{$LANG.catalogue.price_each}</th>
         <th>{$LANG.common.quantity}</th>
         <th>{$LANG.common.price}</th>
      </tr>
   </thead>
   <tbody>
      {foreach from=$ITEMS item=item}
      <tr>
         <td>
            {$item.name}{if !empty($item.product_code)} ({$item.product_code}){/if}
            {if !empty($item.options)}
            <p>{foreach from=$item.options item=option}{$option}<br>{/foreach}</p>
            {/if}
         </td>
         <td class="text-right">{$item.price}</td>
         <td class="text-center">{$item.quantity}</td>
         <td class="text-right">{$item.price_total}</td>
      </tr>
   </tbody>
   {/foreach}
   <tfoot>
   <tr>
      <td colspan="2"></td>
      <td>{$LANG.basket.total_sub}</td>
      <td class="text-right">{$SUM.subtotal}</td>
   </tr>
   <tr>
      <td colspan="2"></td>
      <td>{$LANG.basket.shipping}</td>
      <td class="text-right">{$SUM.shipping}</td>
   </tr>
   {foreach from=$TAXES item=tax}
   <tr>
      <td colspan="2"></td>
      <td>{$tax.name}</td>
      <td class="text-right">{$tax.value}</td>
   </tr>
   {/foreach}
   {if $DISCOUNT}
   <tr>
      <td colspan="2"></td>
      <td>{$LANG.basket.total_discount}</td>
      <td class="text-right">{$SUM.discount}</td>
   </tr>
   {/if}
   <tr>
      <td colspan="2"></td>
      <td>{$LANG.basket.total_grand}</td>
      <td class="text-right">{$SUM.total}</td>
   </tr>
   </tfoot>
</table>
{if !empty($SUM.customer_comments)}
<h3>{$LANG.common.comments}</h3>
<p>&quot;{$SUM.customer_comments}&quot;</p>
{/if}
<p><a href="{$STORE_URL}/index.php?_a=receipt&cart_order_id={$SUM.cart_order_id}" target="_blank"><i class="fa fa-print"></i> {$LANG.confirm.print}</a></p>
{foreach from=$AFFILIATES item=affiliate}{$affiliate}{/foreach}
{if $ANALYTICS}
<!-- Google Analytics for e-commerce -->
<script type="text/javascript">
   {literal}
   var _gaq = _gaq || [];
   _gaq.push(['_setAccount', '{/literal}{$GA_SUM.google_id}{literal}']);
   _gaq.push(['_trackPageview']);
   _gaq.push(['_addTrans',
     '{/literal}{$GA_SUM.cart_order_id}{literal}',           // order ID - required
     '{/literal}{$GA_SUM.store_name}{literal}',  // affiliation or store name
     '{/literal}{$GA_SUM.total}{literal}',          // total - required
     '{/literal}{$GA_SUM.total_tax}{literal}',           // tax
     '{/literal}{$GA_SUM.shipping}{literal}',              // shipping
     '{/literal}{$GA_SUM.town}{literal}',       // city
     '{/literal}{$GA_SUM.state}{literal}',     // state or province
     '{/literal}{$GA_SUM.country_iso}{literal}'             // country
   ]);
   {/literal}
   
    // add item might be called for every item in the shopping cart
    // where your ecommerce engine loops through each item in the cart and
    // prints out _addItem for each
   {foreach from=$GA_ITEMS item=item}
   {literal}
   _gaq.push(['_addItem',
     '{/literal}{$GA_SUM.cart_order_id}{literal}',           // order ID - required
     '{/literal}{$item.product_code}{literal}',           // SKU/code - required
     '{/literal}{$item.name}{literal}',        // product name
     '',   // category or variation
     '{/literal}{$item.price}{literal}',          // unit price - required
     '{/literal}{$item.quantity}{literal}'               // quantity - required
   ]);
   {/literal}
   {/foreach}
   {literal}
   _gaq.push(['_trackTrans']); //submits transaction to the Analytics servers
   
   (function() {
     var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
     ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
     var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
   })();
   {/literal}
</script>
{/if}