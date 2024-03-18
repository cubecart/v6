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
   <a href="?_g=products&node=index&action=edit&product_id={$PRODUCT.product_id}" class="button right">{$LANG.catalogue.title_product_update}</a><h3>{$PRODUCT.name} ({$PRODUCT.product_code})</h3>
   <form action="{$VAL_SELF}" class="ignore-dirty" method="get">
      <div style="max-width: 700px">
         <fieldset>
            <legend>{$LANG.search.date_range}</legend>
            <div>
              {$LANG.common.from}
              <select name="from[day]">
              {foreach from=$DAYS item=day}
                <option value="{$day.value}"{$day.selected_from}>{$day.value}</option>
              {/foreach}
              </select>
              <select name="from[month]">
              {foreach from=$MONTHS item=month}
                <option value="{$month.value}"{$month.selected_from}>{$month.title}</option>
              {/foreach}
              </select>
              <select name="from[year]">
              {foreach from=$YEARS item=year}
                <option value="{$year.value}" {$year.selected_from}>{$year.value}</option>
              {/foreach}
              </select>
              {$LANG.common.to|lower}
              <select name="to[day]">
              {foreach from=$DAYS item=day}
                <option value="{$day.value}"{$day.selected_to}>{$day.value}</option>
              {/foreach}
              </select>
              <select name="to[month]">
              {foreach from=$MONTHS item=month}
                <option value="{$month.value}"{$month.selected_to}>{$month.title}</option>
              {/foreach}
              </select>
              <select name="to[year]">
              {foreach from=$YEARS item=year}
                <option value="{$year.value}" {$year.selected_to}>{$year.value}</option>
              {/foreach}
              </select>
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