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
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
   {if isset($DISPLAY_ORDER_LIST)}
   <div id="orders" class="tab_content">
      <h3>{$LANG.orders.title_orders}</h3>
      {if $ORDER_LIST}
      <table width="100%">
         <thead>
            <tr>
               <td>&nbsp;</td>
               <td nowrap="nowrap">{$THEAD.cart_order_id}</td>
               <td>&nbsp;</td>
               <td>{$THEAD.customer}</td>
               <td nowrap="nowrap">{$THEAD.status}</td>
               <td>{$THEAD.date}</td>
               <td>{$THEAD.total}</td>
               <td>&nbsp;</td>
            </tr>
         </thead>
         <tbody>
            {foreach from=$ORDER_LIST item=order}
            <tr>
               <td align="center"><input type="checkbox" name="multi-order[]" value="{$order.cart_order_id}" class="all-orders"></td>
               <td><a href="{$order.link_edit}">{if $CONFIG.oid_mode=='i'}{$order.{$CONFIG.oid_col}}{else}{$order.cart_order_id}{/if}</a></td>
               <td align="center">
                  {append "cust_type" "registered" index="1"}
                  {append "cust_type" "unregistered" index="2"}
                  <i class="fa fa-user {$cust_type[$order.type]}" title="{$LANG.customer[$order.cust_type[$order.type]]}"></i>
               </td>
               <td>
               {if $order.customer_id}
                  <a href="{$order.link_customer}" title="{$order.name}">{$order.name}</a>
               {else}
                  {$order.name}
               {/if}
               </td>
               <td class="{$order.status_class}">{$order.status}</td>
               <td>{$order.date}</td>
               <td align="right">{$order.prod_total}</td>
               <td align="center">
                  <a href="{$order.link_print}" class="print" target="_blank" title="{$LANG.common.print}"><i class="fa fa-print" title="{$LANG.common.print}"></i></a>
                  <a href="{$order.link_edit}" class="edit" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
                  <a href="{$order.link_delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
               </td>
            </tr>
            {/foreach}
         </tbody>
         <tfoot>
            <tr>
               <td><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/select_all.gif" alt=""></td>
               <td><a href="#" class="check-all" rel="all-orders">{$LANG.form.check_uncheck}</a></td>
               <td colspan="6">
                  {$LANG.orders.with_selected}:
                  <select name="multi-status" class="textbox">
                     <option value="">{$LANG.orders.option_status_no_change}</option>
                     <optgroup label="{$LANG.orders.change_order_status}">
                        {foreach from=$LIST_ORDER_STATUS item=status}<option value="{$status.id}"{$status.selected}>{$status.string}</option>{/foreach}
                     </optgroup>
                  </select>
                  {$LANG.common.then}
                  <select name="multi-action" class="textbox">
                     <option value="">{$LANG.orders.option_nothing}</option>
                     <option value="print">{$LANG.orders.option_print}</option>
                     <option value="delete" style="color: red;">{$LANG.orders.option_delete}</option>
                  </select>
                  <input type="submit" value="{$LANG.common.go}" name="go" data-confirm="{$LANG.notification.confirm_delete}" id="submit_multi" class="tiny">
               </td>
            </tr>
            <tr>
               <td colspan="8">
                  <div class="pagination">
                     <span>{$LANG.common.total}: {$TOTAL_RESULTS}</span>
                     {$PAGINATION}&nbsp;
                  </div>
               </td>
            </tr>
         </tfoot>
      </table>
      {else}
      <p align="center"><strong>{$LANG.orders.notify_orders_none}</strong></p>
      {/if}
   </div>
   <div id="search" class="tab_content">
      <fieldset>
         <legend>{$LANG.orders.title_search}</legend>
         <div><label for="order_no">{$LANG.orders.order_number}</label><span><input type="text" name="search[order_number]" class="textbox"></span></div>
         <div>
            <label for="customer_id">{$LANG.orders.customer_name}</label>
            <span><input type="text" id="search_customer_id" class="textbox ajax" rel="user"><input type="hidden" id="result_search_customer_id" name="search[search_customer_id]" autocomplete="off" value=""></span>
         </div>
         <div>
            <label for="search_status">{$LANG.orders.title_order_status}</label>
            <span>
               <select name="search[status]" id="search_status" class="textbox">
                  <option value="">{$LANG.common.all}</option>
                  {foreach from=$LIST_ORDER_STATUS item=status}<option value="{$status.id}"{$status.selected}>{$status.string}</option>{/foreach}
               </select>
            </span>
         </div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.search.title_by_date}</legend>
         <div><label for="date_range">{$LANG.search.date_range}</label><span><input type="text" name="search[date][from]" class="textbox number date"> - <input type="text" name="search[date][to]" class="textbox number date"></span></div>
      </fieldset>
      <input type="submit" value="{$LANG.common.search}">
   </div>
   <div id="gdpr" class="tab_content">
   <h3>{$LANG.search.gdpr_tools}</h3>
   <p>{$LANG.orders.delete_older_than|replace:'%s':'<input  type="number" min="1" value="" class="number-center" name="month_purge">'} <input type="submit" class="delete submit_confirm tiny" title="{$LANG.notification.confirm_continue}" value="{$LANG.common.go}"></p>
   </div>
   {/if}
   {if isset($DISPLAY_FORM)}
   <div>
      <div id="order_summary" class="tab_content" style="width: 660px;">
         <h3>{$LANG.orders.title_order_summary}</h3>
         <p><a href="?_g=orders&node=index&print[]={$SUMMARY.cart_order_id}" class="print" target="_blank"><i class="fa fa-print" title="{$LANG.common.print}"></i></a></p>
         {if $CUSTOMER_NOTES}
            <p>&quot;{$CUSTOMER_NOTES}&quot;</p>
         {/if}
         <fieldset>
            <legend>{$LANG.orders.title_status_change}</legend>
            <div><label for="o_status">{$LANG.orders.title_order_status}</label><span><select name="order[status]" id="o_status">
               {foreach from=$LIST_ORDER_STATUS item=status}<option value="{$status.id}"{$status.selected}>{$status.string}</option>{/foreach}
               </select></span>
            </div>
            {if isset($DISPLAY_DASHBOARD)}
            <div>
               <label for="dashboard">{$LANG.orders.dashboard_show}</label>
               <span><input type="hidden" name="dashboard" id="dashboard" class="toggle" value="{$SUMMARY.dashboard}"></span>
            </div>
            {/if}
         </fieldset>
         {if isset($DISPLAY_OVERVIEW)}
         {if isset($DISPLAY_COMMENTS)}
         <div class="note">
            <span class="actions"></span>
            <div class="note-header">{$LANG.orders.note_from} {$OVERVIEW_SUMMARY.first_name|capitalize} {$OVERVIEW_SUMMARY.last_name|capitalize}</div>
            &quot;{$OVERVIEW_SUMMARY.customer_comments}&quot;
         </div>
         {/if}
         <div id="order_overview">
            <fieldset class="order_address" id="shipping_address">
               <legend>{$LANG.address.delivery_address}</legend>
               {$OVERVIEW_SUMMARY.name_d}<br>
               {if !empty($OVERVIEW_SUMMARY.company_name_d)}{$OVERVIEW_SUMMARY.company_name_d}<br>{/if}
               {$OVERVIEW_SUMMARY.line1_d|capitalize}<br>
               {if !empty($OVERVIEW_SUMMARY.line2_d)}{$OVERVIEW_SUMMARY.line2_d|capitalize}<br>{/if}
               {$OVERVIEW_SUMMARY.town_d|upper}<br>
               {if !empty($OVERVIEW_SUMMARY.state_d)}{$OVERVIEW_SUMMARY.state_d|upper}, {/if}{$OVERVIEW_SUMMARY.postcode_d}<br>
               {$OVERVIEW_SUMMARY.country_d}
               {if !empty($OVERVIEW_SUMMARY.w3w_d)}<span class="w3w">///<a href="https://what3words.com/{$OVERVIEW_SUMMARY.w3w_d}" target="_blank">{$OVERVIEW_SUMMARY.w3w_d}</a></span>{/if}
            </fieldset>
            <fieldset class="order_address">
               <legend>{$LANG.address.billing_address}</legend>
               {$OVERVIEW_SUMMARY.name}<br>
               {if !empty($OVERVIEW_SUMMARY.company_name)}{$OVERVIEW_SUMMARY.company_name}<br>{/if}
               {$OVERVIEW_SUMMARY.line1|capitalize}<br>
               {if !empty($OVERVIEW_SUMMARY.line2)}{$OVERVIEW_SUMMARY.line2|capitalize}<br>{/if}
               {$OVERVIEW_SUMMARY.town|upper}<br>
               {if !empty($OVERVIEW_SUMMARY.state)}{$OVERVIEW_SUMMARY.state|upper}, {/if}{$OVERVIEW_SUMMARY.postcode}<br>
               {$OVERVIEW_SUMMARY.country}
               {if !empty($OVERVIEW_SUMMARY.w3w)}<span class="w3w">///<a href="https://what3words.com/{$OVERVIEW_SUMMARY.w3w}" target="_blank">{$OVERVIEW_SUMMARY.w3w}</a></span>{/if}
            </fieldset>
            <p>
                <strong>{$LANG.basket.order_date}:</strong> {$OVERVIEW_SUMMARY.order_date}
                {if !empty($OVERVIEW_SUMMARY.currency)}<br><strong>{$LANG.catalogue.guide_currency}:</strong> {$OVERVIEW_SUMMARY.currency}{/if}
            </p>
            <fieldset id="items">
               <legend>{$LANG.catalogue.title_items}</legend>
               {foreach from=$PRODUCTS item=product}
               <div id="item">
                  <strong>{$product.quantity} x <a href="?_g=products&action=edit&product_id={$product.product_id}" title="{$product.name}">{$product.name|truncate:60:"&hellip;"}</a></strong> - {$product.product_code} ({$product.line_formatted})<span>{$product.price_total_formatted}</span>
                  {if $product.accesskey}
                  <div class="download_info"><i class="fa fa-download"></i>{$STORE_URL}/index.php?_a=download&amp;s={$product.stream}&amp;accesskey={$product.accesskey}<br>
                  <a href="{$VAL_SELF}&reset_id={$product.id}"><i class="fa fa-recycle"></i>{$LANG.orders.reset_download_link}</a> <font class="{if $product.expired}link_expired{else}link_active{/if}">({$LANG.common.downloads}: {$product.downloads}/{$CONFIG.download_count} {$LANG.account.download_expires}: {$product.expire})</font></div>
                  {/if}
                  {if $product.options_text}
                  <br>{$product.options_text}
                  {/if}
                  {if $product.custom}
                  {foreach from=$product.custom key=k item=v}
                  <br>{$k|capitalize}: {$v}
                  {/foreach}
                  {/if}
               </div>
               {/foreach}
               <div>{$LANG.basket.total_sub}:<span>{$OVERVIEW_SUMMARY.subtotal}</span></div>
               <div>{$LANG.basket.total_discount}  {if !empty($OVERVIEW_SUMMARY.percent)}({$OVERVIEW_SUMMARY.percent}){/if}:<span>{$OVERVIEW_SUMMARY.discount}</span></div>
               <div>{$LANG.basket.shipping}:<span>{$OVERVIEW_SUMMARY.shipping}</span></div>
               {foreach from=$TAX_SUMMARY item=tax}
               <div>{$tax.tax_name}:<span>{$tax.tax_amount}</span></div>
               {foreachelse}
               <div>{$LANG.basket.total_tax}:<span>{$OVERVIEW_SUMMARY.total_tax}</span></div>
               {/foreach}
               <div><strong>{$LANG.basket.total}:<span>{$OVERVIEW_SUMMARY.total}</span></strong></div>
            </fieldset>
            <fieldset class="other">
               <legend>{$LANG.account.contact_details}</legend>
               <div><label>{$LANG.common.email}</label><span><a href="mailto:{$OVERVIEW_SUMMARY.email}">{$OVERVIEW_SUMMARY.email}</a></span></div>
               <div><label>{$LANG.address.phone}</label><span>{$OVERVIEW_SUMMARY.phone}</span></div>
               {if !empty($OVERVIEW_SUMMARY.mobile)}
               <div><label>{$LANG.address.mobile}</label><span>{$OVERVIEW_SUMMARY.mobile}</span></div>
               {/if}
               <div><label>{$LANG.common.ip_address}</label><span>{$OVERVIEW_SUMMARY.ip_address}</span></div>
               <div><label>{$LANG.common.language}</label><span><img src="language/flags/{$OVERVIEW_SUMMARY.lang}.png" title="{$OVERVIEW_SUMMARY.lang}"></span></div>
               
            </fieldset>
            <fieldset class="other">
               <legend>{$LANG.orders.title_shipping}</legend>
               {if !empty($OVERVIEW_SUMMARY.ship_date)}
               <div><label>{$LANG.orders.shipping_date}</label><span>{$OVERVIEW_SUMMARY.ship_date}</span></div>
               {/if}
               {if !empty($OVERVIEW_SUMMARY.ship_method)}
               <div><label>{$LANG.orders.shipping_method}</label><span>{$OVERVIEW_SUMMARY.ship_method}</span></div>
               {/if}
               {if !empty($OVERVIEW_SUMMARY.ship_product)}
               <div><label>{$LANG.orders.shipping_product}</label><span>{$OVERVIEW_SUMMARY.ship_product}</span></div>
               {/if}
               {if !empty($OVERVIEW_SUMMARY.ship_tracking)}
               <div><label>{$LANG.orders.shipping_tracking}</label><span>{$OVERVIEW_SUMMARY.ship_tracking}</span></div>
               {/if}
               {if !empty($OVERVIEW_SUMMARY.gateway)}
               <div><label>{$LANG.orders.gateway_name}</label><span class="editable number" name="summary[gateway]">{$OVERVIEW_SUMMARY.gateway}</span></div>
               {/if}
            </fieldset>
         </div>
         {/if}
      </div>
      <div id="order_billing" class="tab_content">
         <h3>{$LANG.address.billing_address}</h3>
         <fieldset>
            <legend>{$LANG.address.billing_address}</legend>
            <div>
               <label for="sum_name">{$LANG.orders.title_find_customers}</label>
               <span>
               <input type="hidden" id="ajax_customer_id" name="customer[customer_id]" value="{$SUMMARY.customer_id}">
               <input type="text" id="sum_name" class="textbox ajax" rel="user">
               </span>
            </div>
            <div>
               <label for="addresses">{$LANG.address.title_address}</label>
               <span>
                  <select class="address-list textbox" rel="sum">
                     <option value="0">{$LANG.address.form_address_select}</option>
                     {if isset($LIST_ADDRESS)}{foreach from=$LIST_ADDRESS item=address}
                     <option value="{$address.key}" class="temporary">{$address.description}</option>
                     {/foreach}{/if}
                  </select>
               </span>
            </div>
            <div><label for="ajax_title">{$LANG.user.title}</label><span><input type="text" id="ajax_title" name="customer[title]" value="{$SUMMARY.title}" class="textbox billing"></span></div>
            <div><label for="ajax_first_name">{$LANG.user.name_first}</label><span><input type="text" id="ajax_first_name" name="customer[first_name]" value="{$SUMMARY.first_name|capitalize}" class="textbox billing required"></span></div>
            <div><label for="ajax_last_name">{$LANG.user.name_last}</label><span><input type="text" id="ajax_last_name" name="customer[last_name]" value="{$SUMMARY.last_name|capitalize}" class="textbox billing required"></span></div>
            <div><label for="sum_company_name">{$LANG.address.company_name}</label><span><input type="text" id="sum_company_name" name="customer[company_name]" value="{$SUMMARY.company_name}" class="textbox billing"></span></div>
            <div><label for="sum_line1">{$LANG.address.line1}</label><span><input type="text" id="sum_line1" name="customer[line1]" value="{$SUMMARY.line1|capitalize}" class="textbox billing required"></span></div>
            <div><label for="sum_line2">{$LANG.address.line2}</label><span><input type="text" id="sum_line2" name="customer[line2]" value="{$SUMMARY.line2|capitalize}" class="textbox billing"></span></div>
            <div><label for="sum_town">{$LANG.address.town}</label><span><input type="text" id="sum_town" name="customer[town]" value="{$SUMMARY.town}" class="textbox billing required"></span></div>
            <div>
               <label for="sum_country">{$LANG.address.country}</label>
               <span>
               <select name="customer[country]" id="sum_country" class="textbox billing country-list required" rel="sum_state">
               {foreach from=$LIST_COUNTRY item=country}<option value="{$country.numcode}"{$country.is_billing} {$country.selected}>{$country.name}</option>{/foreach}
               </select>
               </span>
            </div>
            <div><label for="sum_state">{$LANG.address.state}</label><span><input type="text" id="sum_state" name="customer[state]" value="{$SUMMARY.state}" class="textbox billing state-list"></span></div>
            <div><label for="sum_postcode">{$LANG.address.postcode}</label><span><input type="text" id="sum_postcode" name="customer[postcode]" value="{$SUMMARY.postcode}" class="textbox billing"></span></div>
            {if !empty($CONFIG.w3w)}<div><label for="w3w">what3words</label><span><input type="text" id="sum_w3w" name="customer[w3w]" value="{$SUMMARY.w3w}" class="textbox billing"></span></div>{/if}
         </fieldset>
         <fieldset>
            <legend>{$LANG.account.contact_details}</legend>
            <div><label for="ajax_email">{$LANG.common.email}</label><span><input type="text" id="ajax_email" name="customer[email]" value="{$SUMMARY.email}" class="textbox billing required"></span></div>
            <div><label for="ajax_phone">{$LANG.address.phone}</label><span><input type="text" id="ajax_phone" name="customer[phone]" value="{$SUMMARY.phone}" class="textbox billing required"></span></div>
            <div><label for="ajax_mobile">{$LANG.address.mobile}</label><span><input type="text" id="ajax_mobile" name="customer[mobile]" value="{$SUMMARY.mobile}" class="textbox billing"></span></div>
         </fieldset>
      </div>
      <div id="order_delivery" class="tab_content">
         <h3>{$LANG.address.delivery_address}</h3>
         <fieldset>
            <legend>{$LANG.address.delivery_address}</legend>
            <div>
               <label for="d_addresses">{$LANG.address.title_address}</label>
               <span>
                  <select id="d_addresses" class="address-list textbox" rel="d_sum:d_ajax">
                     <option value="0">{$LANG.address.form_address_select}</option>
                     {if isset($LIST_ADDRESS)}{foreach from=$LIST_ADDRESS item=address}
                     <option value="{$address.key}" class="temporary">{$address.description}</option>
                     {/foreach}{/if}
                  </select>
               </span>
               <a href="#" class="duplicate" rel="billing" target="d_">{$LANG.address.copy_from_billing}</a>
            </div>
            <div><label for="d_ajax_title">{$LANG.user.title}</label><span><input type="text" id="d_ajax_title" name="customer[title_d]" value="{$SUMMARY.title_d}" class="textbox"></span></div>
            <div><label for="d_ajax_first_name">{$LANG.user.name_first}</label><span><input type="text" id="d_ajax_first_name" name="customer[first_name_d]" value="{$SUMMARY.first_name_d}" class="textbox required"></span></div>
            <div><label for="d_ajax_last_name">{$LANG.user.name_last}</label><span><input type="text" id="d_ajax_last_name" name="customer[last_name_d]" value="{$SUMMARY.last_name_d}" class="textbox required"></span></div>
            <div><label for="d_sum_company_name">{$LANG.address.company_name}</label><span><input type="text" id="d_sum_company_name" name="customer[company_name_d]" value="{$SUMMARY.company_name_d}" class="textbox"></span></div>
            <div><label for="d_sum_line1">{$LANG.address.line1}</label><span><input type="text" id="d_sum_line1" name="customer[line1_d]" value="{$SUMMARY.line1_d|capitalize}" class="textbox required"></span></div>
            <div><label for="d_sum_line2">{$LANG.address.line2}</label><span><input type="text" id="d_sum_line2" name="customer[line2_d]" value="{$SUMMARY.line2_d|capitalize}" class="textbox"></span></div>
            <div><label for="d_sum_town">{$LANG.address.town}</label><span><input type="text" id="d_sum_town" name="customer[town_d]" value="{$SUMMARY.town_d}" class="textbox required"></span></div>
            <div>
               <label for="d_sum_country">{$LANG.address.country}</label>
               <span>
               <select name="customer[country_d]" id="d_sum_country" class="textbox country-list required" rel="d_sum_state">
               {foreach from=$LIST_COUNTRY item=country}<option value="{$country.numcode}"{$country.is_delivery} {$country.selected}>{$country.name}</option>{/foreach}
               </select>
               </span>
            </div>
            <div><label for="d_sum_state">{$LANG.address.state}</label><span><input type="text" id="d_sum_state" name="customer[state_d]" value="{$SUMMARY.state_d}" class="textbox state-list"></span></div>
            <div><label for="d_sum_postcode">{$LANG.address.postcode}</label><span><input type="text" id="d_sum_postcode" name="customer[postcode_d]" value="{$SUMMARY.postcode_d}" class="textbox required"></span></div>
            {if !empty($CONFIG.w3w)}<div><label for="w3w_d">what3words</label><span><input type="text" id="sum_w3w_d" name="customer[w3w_d]" value="{$SUMMARY.w3w_d}" class="textbox billing"></span></div>{/if}
         </fieldset>
         <fieldset>
            <legend>{$LANG.orders.title_shipping}</legend>
            <div><label for="sum_ship_date">{$LANG.orders.shipping_date}</label><span><input type="text" id="sum_ship_date" name="summary[ship_date]" value="{$SUMMARY.ship_date}" class="textbox date"></span></div>
            <div><label for="sum_ship_method">{$LANG.orders.shipping_method}</label><span><input type="text" id="sum_ship_method" name="summary[ship_method]" placeholder="{$LANG.orders.shipping_method_eg}" value="{$SUMMARY.ship_method}" class="textbox"></span></div>
            <div><label for="sum_ship_product">{$LANG.orders.shipping_product}</label><span><input type="text" id="sum_ship_product" name="summary[ship_product]" placeholder="{$LANG.orders.shipping_product_eg}" value="{$SUMMARY.ship_product}" class="textbox"></span></div>
            <div><label for="sum_ship_tracking">{$LANG.orders.shipping_tracking} {$LANG.orders.shipping_url_or_code}</label><span><input type="text" id="sum_ship_tracking" name="summary[ship_tracking]" value="{$SUMMARY.ship_tracking}" class="textbox"></span></div>
            <div><label for="sum_weight">{$LANG.common.weight} ({$WEIGHT_UNIT})</label><span><input type="text" id="sum_weight" name="summary[weight]" value="{$SUMMARY.weight}" class="textbox"></span></div>
         </fieldset>
      </div>
      <div id="order_inventory" class="tab_content">
         <h3>{$LANG.orders.title_order_inventory}</h3>
         <div style="display: none;">
            <span class="actions">
            <i class="fa fa-trash" title="{$LANG.common.delete}"></i>
            </span>
         </div>
         <table id="order-builder">
            <thead>
               <tr>
                  <th>{$LANG.common.quantity}</th>
                  <th>{$LANG.catalogue.product_name}</th>
                  <th>{$LANG.common.price_unit}</th>
                  <th>{$LANG.common.price}</th>
                  <th width="20">&nbsp;</th>
               </tr>
            </thead>
            <tbody id="inventory-list">
               {if isset($PRODUCTS)}
               {foreach from=$PRODUCTS item=product}
               <tr class="update-subtotal">
                  <td>
                     <input type="hidden" name="inv[{$product.id}][id]" class="saved" value="{$product.id}">
                     <input type="text" name="inv[{$product.id}][quantity]" class="textbox number quantity" value="{$product.quantity}">
                     {if $product.product_id>0}
                     <input type="hidden" name="inv[{$product.id}][product_id]" value="{$product.product_id}">
                     {/if}
                  </td>
                  <td>
                     <span class="editable" name="inv[{$product.id}][name]">{$product.name}</span>
                     {include file='templates/element.product_options.php'}
                  </td>
                  <td align="right">
                     <input type="text" name="inv[{$product.id}][price]" id="{$product.id}_price" class="textbox number-right lineprice original-fix" original="{$product.line_price_less_options}" value="{$product.line}">
                  </td>
                  <td align="right"><input type="text" name="inv[{$product.id}][line_price]" class="textbox number-right subtotal goods" value="{$product.price_total}"></td>
                  <td align="center"><a href="#{$product.id}" class="remove" title="{$LANG.notification.confirm_delete}" name="inv_remove" rel="{$PRODUCT.id}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></td>
               </tr>
               {/foreach}
               {/if}
            </tbody>
            <tfoot>
               <tr class="update-subtotal inline-add">
                  <td><input type="text" class="textbox number quantity" rel="product_quantity" value="1"></td>
                  <td><input type="hidden" id="ajax_product_id" rel="product_id"><input type="text" id="ajax_name" placeholder="{$LANG.common.type_to_search}" class="textbox ajax not-empty" rel="product"></td>
                  <td><input type="text" id="ajax_price" class="textbox number-right lineprice" rel="price" value="0.00"></td>
                  <td  align="right"><input type="text" rel="line_price" class="textbox number-right subtotal goods" value="0.00"></td>
                  <td align="center" class="action"><a href="#" title="{$LANG.common.add}" class="add" target="inventory-list"><i class="fa fa-plus-circle" title="{$LANG.common.add}"></i></a></td>
               </tr>
               <!-- Source for inline adding -->
               <tr class="update-subtotal inline-source" name="inv_add">
                  <td><input type="text" rel="product_quantity" class="textbox number quantity"></td>
                  <td><input type="hidden" rel="product_id"><input type="hidden" rel="product"><span rel="product"></span> <span rel="product_options"></span></td>
                  <td>
                     <input type="text" rel="price" class="textbox number-right lineprice">
                  </td>
                  <td><input type="text" rel="line_price" class="textbox number-right subtotal goods"></td>
                  <td align="center" class="action"><a href="#" class="remove dynamic" title="{$LANG.common.decision_remove}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></td>
               </tr>
               <tr>
                  <th colspan="3">{$LANG.basket.total_sub}</th>
                  <td><input type="text" id="subtotal" name="summary[subtotal]" class="textbox number-right" value="{$SUMMARY.subtotal}"></td>
                  <td>&nbsp;</td>
               </tr>
               <tr class="update-subtotal">
                  <th colspan="3">
                     <select name="summary[discount_type]" id="discount_type">
                     <option value="f" {if $SUMMARY.discount_type == "f"}selected="selected"{/if}>{$LANG.catalogue.discount_price}</option>
                     <option value="p" {if $SUMMARY.discount_type == "p"}selected="selected"{/if}>{$LANG.catalogue.discount_percent}</option>
                     </select>
                  </th>
                  <td><input type="text" id="discount" name="summary[discount]" class="textbox number-right lineprice" value="{$SUMMARY.discount_form}"></td>
                  <td><span id="discount_percent">{if $SUMMARY.discount_type == "p"}%{/if}</span></td>
               </tr>
               <tr class="update-subtotal">
                  <th colspan="3">{$LANG.basket.shipping}</th>
                  <td><input type="text" id="shipping" name="summary[shipping]" class="textbox number-right lineprice shipping" value="{$SUMMARY.shipping}"></td>
                  <td>&nbsp;</td>
               </tr>
               {if isset($LIST_TAXES)}
               {foreach from=$LIST_TAXES item=tax}
               <tr class="update-subtotal">
                  <th colspan="3">{$tax.type_name}: {$tax.display}</th>
                  <td><input type="text" name="tax[{$tax.id}]" class="textbox number-right tax" value="{$tax.amount}"></td>
                  <td><a href="#" class="remove" name="tax_remove" rel="{$tax.id}" title=""><i class="fa fa-trash" title="{$LANG.common.remove}"></i></a></td>
               </tr>
               {/foreach}
               {/if}
               <tr class="inline-source">
                  <th colspan="3">{$tax.type_name}: {$tax.display}</th>
                  <td><input type="text" name="tax[{$tax.id}]" class="textbox number-right tax" value="{$tax.amount}"></td>
                  <td><a href="#" class="remove" name="tax_remove" rel="{$tax.id}" title=""><i class="fa fa-trash" title="{$LANG.common.remove}"></i></a></td>
               </tr>
               <tr class="inline-add">
                  <th colspan="3">
                     <select class="not-empty tax-chooser" rel="tax_id">
                        <option value="">{$LANG.form.please_select}</option>
                        {if isset($SELECT_TAX)}
                        {foreach from=$SELECT_TAX item=country key=taxes}
                        <optgroup label="{$taxes}">
                           {foreach from=$country item=tax}
                           <option value="{$tax.id}" data-percent="{$tax.tax_percent}" data-shipping="{$tax.shipping}" data-goods="{$tax.goods}">{$tax.type_name}: {$tax.display}</option>
                           {/foreach}
                        </optgroup>
                        {/foreach}
                        {/if}
                     </select>
                  </th>
                  <td><input type="text" rel="amount" class="textbox number-right tax not-empty" ></td>
                  <td align="center"><a href="#" class="add" target="tax-list"><i class="fa fa-plus-circle" title="{$LANG.common.add}"></i></a></td>
               </tr>
               <!-- Source for inline adding -->
               <tr class="update-subtotal inline-source" name="tax_add">
                  <th colspan="3"><input type="hidden" rel="tax_id"><span rel="tax_id"></span></th>
                  <td><input type="text" rel="amount" class="textbox number-right tax"></td>
                  <td align="center"><a href="#" class="remove dynamic" title=""><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></td>
               </tr>
               <tr>
                  <th colspan="3">{$LANG.basket.total_tax}</th>
                  <td><input type="text" id="total_tax" name="summary[total_tax]" class="textbox number-right" value="{$SUMMARY.total_tax}"></td>
                  <td>&nbsp;</td>
               </tr>
               <!-- Add total tax: read only -->
               <tr>
                  <th colspan="3">{$LANG.basket.total}</th>
                  <td><input type="text" id="total" name="summary[total]" class="textbox number-right" value="{$SUMMARY.total}"></td>
                  <td align="center"><a href="#" class="refresh"><i class="fa fa-refresh" title="{$LANG.common.refresh}"></i></a></td>
               </tr>
            </tfoot>
         </table>
      </div>
      <div id="order_notes" class="tab_content">
         <h3>{$LANG.orders.title_order_notes}</h3>
         {if isset($LIST_NOTES)}{foreach from=$LIST_NOTES item=note}
         <div class="note">
            <span class="actions">
            <a href="{$note.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
            </span>
            <p class="note-header"><i class="fa fa-sticky-note" title="{$LANG.common.notes}" aria-hidden="true"></i> {if !empty($note.author)}{$LANG.dashboard.note_by} {$note.author} - {/if}{$note.time}</p>
            {$note.content|nl2br}
         </div>
         {/foreach}
         {/if}
         <fieldset>
            <legend>{$LANG.orders.title_note_add}</legend>
            <div>
               <div><label for="note">{$LANG.orders.note_content}<br>({$LANG.orders.note_explain_viewable})</label><span><textarea name="note" class="textbox"></textarea></span></div>
               <div><label class="spacer">&nbsp;</label></div>
            </div>
            <div>
               <div><label for="note">{$LANG.orders.note_content_public}<br>({$LANG.orders.note_explain_email})</label><span><textarea name="summary[note_to_customer]" class="textbox">{$SUMMARY.note_to_customer}</textarea></span></div>
               <div><label class="spacer">&nbsp;</label></div>
            </div>
         </fieldset>
      </div>
      <div id="order_history" class="tab_content">
         <h3>{$LANG.orders.title_order_history}</h3>
         <table>
            <thead>
               <tr>
                  <td width="150">{$LANG.common.status}</td>
                  <td>{$LANG.common.date_time}</td>
                  <td>{$LANG.common.initiator}</td>
               </tr>
            </thead>
            <tbody>
               {if isset($LIST_HISTORY)}
               {foreach from=$LIST_HISTORY item=history}
               <tr>
                  <td width="200">{$history.status}</td>
                  <td>{$history.updated}</td>
                  <td>{$history.initiator}</td>
               </tr>
               {/foreach}
               {/if}
            </tbody>
         </table>
      </div>
      {if isset($DISPLAY_TRANSACTIONS)}
      <div id="order_transactions" class="tab_content">
         <h3>{$LANG.orders.title_transaction_logs}</h3>
         <table>
            <thead>
               <tr>
                  <td>{$LANG.orders.transaction_id}</td>
                  <td>{$LANG.common.status}</td>
                  <td>{$LANG.common.amount}</td>
                  <td>{$LANG.orders.gateway_name}</td>
                  <td>{$LANG.common.date_time}</td>
                  <td>{$LANG.common.notes}</td>
                  {if $DISPLAY_ACTIONS}
                  <td width="60">{$LANG.common.action}</td>
                  {/if}
               </tr>
            </thead>
            <tbody>
               {foreach from=$TRANSACTIONS item=transaction}
               <tr>
                  <td>{$transaction.trans_id}</td>
                  <td align="center">{$transaction.status}</td>
                  <td align="center">{$transaction.amount}</td>
                  <td align="center">{$transaction.gateway}</td>
                  <td align="center">{$transaction.time}</td>
                  <td>{$transaction.notes}</td>
                  {if isset($DISPLAY_ACTIONS)}
                  <td align="center">
                     {foreach from=$transaction.actions item=action}
                     <a href="{$action.url}" title="{$action.title}" class="delete"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/{$action.icon}" alt="{$action.title}"></a>
                     {/foreach}
                  </td>
                  {/if}
               </tr>
               {/foreach}
            </tbody>
         </table>
      </div>
      {/if}
      {if isset($DISPLAY_CARD)}
      <div id="credit_card" class="tab_content">
         <h3>{$LANG.orders.title_card_details}</h3>
         <fieldset>
            <legend>{$LANG.orders.title_card_details}</legend>
            {foreach from=$CARD_DATA key=k item=data}
            <div><label for="{$k}">{$data.name}</label><span><input type="text" name="card[{$k}]" id="{$k}" value="{$data.value}" class="textbox"></span></div>
            {/foreach}
            <div><label for="delete">{$LANG.orders.card_delete}</label><span><a href="{$CARD_DELETE}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></span></div>
         </fieldset>
      </div>
      {/if}
      {if isset($PLUGIN_TABS)}
      {foreach from=$PLUGIN_TABS item=tab}
      {$tab}
      {/foreach}
      {/if}
      {include file='templates/element.hook_form_content.php'}
      <div class="form_control">
         <input type="hidden" name="previous-tab" id="previous-tab">
         <input type="hidden" name="cart_order_id" value="{$SUMMARY.cart_order_id}">
         <input type="submit" value="{$LANG.common.save}"> <input type="submit" name="submit_cont" value="{$LANG.common.save_reload}">
      </div>
   </div>
   <script type="text/javascript">
      var county_list	= {if !empty($STATE_JSON)}{$STATE_JSON}{else}false{/if};
      {if $ADDRESS_JSON}var addresses	= {$ADDRESS_JSON};{/if}
   </script>
   {/if}
   
</form>