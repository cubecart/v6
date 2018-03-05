{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
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
            {$STORE.country}
            <div class="thickpad-top">
            {if !empty($CONFIG.tax_number)}{$LANG.settings.tax_vat_number}: {$CONFIG.tax_number}<br>{/if}
            {$CONFIG.email_address}
            </div>
         </div>
      </div>
      <div class="row">
         <div class="small-6 columns thickmarg-topbottom">
            {if !empty($order.company_name)}<strong>{$order.company_name}</strong><br>{/if}
            {$order.title} {$order.first_name|capitalize} {$order.last_name|capitalize}<br>
            {$order.line1|capitalize} <br>
            {if !empty($order.line2)}{$order.line2|capitalize}<br>{/if}
            {$order.town|upper}<br>
            {if !empty($order.state)}{$order.state|upper}<br>{/if}
            {$order.postcode}{if $CONFIG['store_country']!==$address['country_id']}<br>
            {$order.country}{/if}
         </div>
         <div class="small-6 columns text-right thickmarg-topbottom">
            <strong>{$LANG.common.invoice}: {if $CONFIG.oid_mode=='i'}{$order.{$CONFIG.oid_col}}{else}{$order.cart_order_id}{/if}<br>	
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
                     <th class="text-center">{$LANG.catalogue.price_each}</th>
                     <th class="text-center">{$LANG.common.quantity}</th>
                     <th class="text-center">{$LANG.common.price}</th>
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
                     <td class="text-center">{$item.price}</td>
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
                     <td>{if !empty($order.ship_method)}{$order.ship_method|replace:'_':' '}{if !empty($order.ship_product)} ({$order.ship_product}){/if}{else}{$LANG.basket.shipping}{/if}</td>
                     <td class="text-right">{$order.shipping}</td>
                  </tr>
                  {foreach from=$order.taxes item=tax}
                  <tr>
                     <td colspan="2"></td>
                     <td>{$tax.name}</td>
                     <td class="text-right">{$tax.value}</td>
                  </tr>
                  {/foreach}
                  {if isset($order.discount_type)}
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
      {if !empty($order.customer_comments)}
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
               <small>
               {$LANG.address.return_address}: 
               {if !empty($STORE.address)}{$STORE.address}, {/if}
               {if !empty($STORE.county)}{$STORE.county}, {/if}
               {if !empty($STORE.postcode)}{$STORE.postcode} {/if}
               {if !empty($STORE.country)}{$STORE.country} {/if}</small>
            </div>
         </div>
      </footer>
      {/foreach}
   </body>
</html>