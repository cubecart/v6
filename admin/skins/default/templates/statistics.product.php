{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
 <div id="general" class="tab_content">
   {if $PRODUCT}
   <h3>{$PRODUCT.name} ({$PRODUCT.product_code})</h3>
   <a href="#" onclick="history.back();return false;">&larr; {$LANG.common.back}</a>
   <form action="{$VAL_SELF}" class="ignore-dirty" method="get">
      <div style="max-width: 700px">
         <fieldset class="date-range-filter">
            <style>.date-range-filter label { width: 10em !important; }</style>
            <legend>{$LANG.search.date_range}</legend>
            <div>
              <label for="date_from">{$LANG.common.from}</label>
              <input type="text" id="date_from" name="from" class="textbox date" value="{$FROM_DATE}" size="12" autocomplete="off">
            </div>
            <div>
              <label for="date_to">{$LANG.common.to|lower}</label>
              <input type="text" id="date_to" name="to" class="textbox date" value="{$TO_DATE}" size="12" autocomplete="off">
              <span style="float: right">
              {if $RESET}
              <a href="?_g=statistics&node=product&product_id={$PRODUCT.product_id}">{$LANG.common.reset}</a>
              {/if}
              <input type="submit" class="tiny" value="{$LANG.common.go}">
              </span>
            </div>
         </fieldset>
      </div>
      <input type="hidden" name="_g" value="statistics">
      <input type="hidden" name="node" value="product">
      <input type="hidden" name="product_id" value="{$PRODUCT.product_id}">
   </form>
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