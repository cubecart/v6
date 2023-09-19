{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
 <div id="general" class="tab_content">
   {if $PRODUCT}
   <h3>{$PRODUCT.name} ({$PRODUCT.product_code})</h3>
   <table width="700">
    <thead>
      <tr>
        <th colspan="3">{$LANG.common.overview}</th>
      </tr>
    </thead>
    <tbody>
    <tr>
      {if !empty($PRODUCT.image)}<td rowspan="6" width="230"><img src="{$PRODUCT.image}" class="border" style="margin-right: 20px" /></td>{/if}
      <td>{$LANG.common.created}</td>
      <td>{$PRODUCT.date_added}</td>
    </tr>
    <tr>
      <td>{$LANG.common.updated}</td>
      <td>{$PRODUCT.updated}</td>
    </tr>
    <tr>
      <td>{$LANG.statistics.first_sale}</td>
      <td>{$PRODUCT.first_sale}</td>
    </tr>
    <tr>
      <td>{$LANG.statistics.last_sale}</td>
      <td>{$PRODUCT.last_sale}</td>
    </tr>
    <tr>
      <td>{$LANG.statistics.total_sales}</td>
      <td>
      {if !empty($PRODUCT.order_ids)}
        <a href="?_g=orders&i={$PRODUCT.order_ids}">{number_format($PRODUCT.total_sales)}</a>
      {else}
        {number_format($PRODUCT.total_sales)}
      {/if}
      {if $PRODUCT.avg_per_order > 1}
      ({sprintf($LANG.orders.per_order,$PRODUCT.avg_per_order)})
      {/if}
      </td>
    </tr>
    <tr>
      <td>{$LANG.statistics.sale_interval}</td>
      <td>{$PRODUCT.sale_interval}</td>
    </tr>
</tbody>
   </table>
   {if $CUSTOMERS}
   <table width="700">
    <thead>
      <tr>
        <th>{$LANG.orders.customer_name}</th>
        <th>{$LANG.common.email}</th>
        <th class="text-center">{$LANG.common.purchases}</th>
      </tr>
    </thead>
    <tbody>
      {foreach from=$CUSTOMERS item=c}
      <tr>
        <td><a href="?_g=customers&action=edit&customer_id={$c.customer_id}">{$c.first_name} {$c.last_name}</a></td>
        <td><a href="mailto:{$c.email}">{$c.email}</a></td>
        <td class="text-center">{$c.purchases}</td>
      </tr>
      {/foreach}
    </tbody>
   </table>
   <div class="pagination">{$PAGINATION}</div>
   {/if}
   {else}
   <p>{$LANG.catalogue.product_not_found}</p>
   {/if}
</div>