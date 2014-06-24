<!DOCTYPE html>
<html class="no-js" xmlns="http://www.w3.org/1999/xhtml" dir="{$TEXT_DIRECTION}" lang="{$HTML_LANG}">
   <head>
      <title>{$PAGE_TITLE}</title>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <link rel="stylesheet" href="{$STORE_URL}/skins/{$SKIN_FOLDER}/css/cubecart.print.css">
      <link rel="stylesheet" href="{$STORE_URL}/skins/{$SKIN_FOLDER}/css/cubecart.helpers.css">
      <link rel="stylesheet" href="{$STORE_URL}/skins/{$SKIN_FOLDER}/css/cubecart.common.css">
      <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
   </head>
   <body onload="window.print();">
      {foreach from=$LIST_ORDERS item=order}
      <div class="row">
         <div class="small-6 columns">
            <img src="{$STORE_LOGO}" alt="">
         </div>
         <div class="small-6 columns text-right">
            <strong>{$CONFIG.store_name}</strong><br>
            {$STORE.address|nl2br}<br>
            {$STORE.county}<br>
            {$STORE.postcode}<br>
            {$STORE.country}<br>
            <div class="thickpad-top">
            {if !empty($CONFIG.tax_number)}{$LANG.settings.tax_vat_number}: {$CONFIG.tax_number}<br>{/if}
            {$CONFIG.email_address}
            </div>
         </div>
      </div>
      <div class="row">
         <div class="small-6 columns thickmarg-topbottom">
            {if !empty($order.company_name)}<strong>{$order.company_name}</strong><br>{/if}
            {$order.title} {$order.first_name} {$order.last_name}<br>
            {$order.line1} <br>
            {if !empty($order.line2)}{$order.line2}<br>{/if}
            {$order.town}<br>
            {$order.state}<br>
            {$order.postcode}<br>
            {$order.country}
            {if !empty({$order.vat_number})}<br>{$LANG.settings.tax_vat_number}: {$order.vat_number}{/if}
         </div>
         <div class="small-6 columns text-right thickmarg-topbottom">
            <strong>{$LANG.common.invoice}: {$order.cart_order_id}<br>	
            {$order.order_date}<br></strong>
            <div class="order_status order_status_{$order.status} marg-top">{$order.order_status}</div>
            <h1>{$order.total}</h1>
         </div>
      </div>
      <div class="row">
         <div class="large-12 columns">
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
                  {foreach from=$order.items item=item}
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
                     <td class="text-right">{$order.subtotal}</td>
                  </tr>
                  <tr>
                     <td colspan="2"></td>
                     <td>{$LANG.basket.shipping}</td>
                     <td class="text-right">{$order.shipping}</td>
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
                     <td class="text-right">{$order.discount}</td>
                  </tr>
                  {/if}
                  <tr>
                     <td colspan="2"></td>
                     <td>{$LANG.basket.total_grand}</td>
                     <td class="text-right">{$order.total}</td>
                  </tr>
               </tfoot>
            </table>
         </div>
      </div>
      {if isset($order.customer_comments)}
      <div class="row">
         <div class="small-12 columns"><h3>{$LANG.common.comments}</h3> &quot;{$order.customer_comments}&quot;</div>
      </div>
      {/if}
      <div class="row text-center">
         <div class="small-12 columns">{$LANG.orders.title_thanks}</div>
      </div>
      <footer>
         <div class="row">
            <div class="small-12 columns text-center">
               <hr>
               <small>{$LANG.address.return_address}: {$STORE.address}, {$STORE.county}, {$STORE.postcode} {$STORE.country}</small>
            </div>
         </div>
      </footer>
      {/foreach}
   </body>
</html>