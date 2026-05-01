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
<div id="general" class="tab_content product-stats">
   {if $PRODUCT}
   <div class="ps-header">
     <div class="ps-header-text">
       <h3>{$PRODUCT.name} <small>({$PRODUCT.product_code})</small></h3>
       <a href="#" onclick="history.back();return false;">&larr; {$LANG.common.back}</a>
     </div>
     {if !empty($PRODUCT.image)}<img src="{$PRODUCT.image}" class="border" alt="">{/if}
   </div>

   <form action="{$VAL_SELF}" class="ignore-dirty" method="get">
      <fieldset class="date-range-filter">
         <div>
           <label for="date_from">{$LANG.common.from}</label>
           <input type="text" id="date_from" name="from" class="textbox date" value="{$FROM_DATE|escape:'html'}" size="12" autocomplete="off">
         </div>
         <div>
           <label for="date_to">{$LANG.common.to|lower}</label>
           <input type="text" id="date_to" name="to" class="textbox date" value="{$TO_DATE|escape:'html'}" size="12" autocomplete="off">
         </div>
         <input type="submit" class="tiny" value="{$LANG.common.go}">
         <span class="ps-presets">
           <a href="?_g=statistics&node=product&product_id={$PRODUCT.product_id}&range=7d"  class="{if $RANGE eq '7d'}active{/if}">7d</a>
           <a href="?_g=statistics&node=product&product_id={$PRODUCT.product_id}&range=30d" class="{if $RANGE eq '30d'}active{/if}">30d</a>
           <a href="?_g=statistics&node=product&product_id={$PRODUCT.product_id}&range=90d" class="{if $RANGE eq '90d'}active{/if}">90d</a>
           <a href="?_g=statistics&node=product&product_id={$PRODUCT.product_id}&range=all" class="{if $RANGE eq 'all'}active{/if}">{$LANG.statistics.all_time}</a>
         </span>
      </fieldset>
      <input type="hidden" name="_g" value="statistics">
      <input type="hidden" name="node" value="product">
      <input type="hidden" name="product_id" value="{$PRODUCT.product_id}">
   </form>

   <div class="ps-kpis">
     <div class="ps-kpi">
       <div class="ps-kpi-label">{$LANG.statistics.revenue}</div>
       <div class="ps-kpi-value">{$PRODUCT.revenue}</div>
       <div class="ps-kpi-meta">
         {if $PRODUCT.revenue_delta_pct !== null}
           <span class="stat-trend stat-trend--{if $PRODUCT.revenue_delta_pct >= 0}up{else}down{/if}"><i class="fa fa-caret-{if $PRODUCT.revenue_delta_pct >= 0}up{else}down{/if}"></i> {$PRODUCT.revenue_delta_pct}%</span> {$LANG.statistics.vs_prior_period}
         {elseif $PRODUCT.profit !== null}
           {$LANG.statistics.profit}: {$PRODUCT.profit}
         {/if}
       </div>
     </div>
     <div class="ps-kpi">
       <div class="ps-kpi-label">{$LANG.statistics.quantity_sold}</div>
       <div class="ps-kpi-value">
         {if !empty($PRODUCT.order_ids)}<a href="?_g=orders&i={$PRODUCT.order_ids}">{number_format($PRODUCT.total_sales)}</a>{else}{number_format($PRODUCT.total_sales)}{/if}
       </div>
       <div class="ps-kpi-meta">
         {if $PRODUCT.units_delta_pct !== null}
           <span class="stat-trend stat-trend--{if $PRODUCT.units_delta_pct >= 0}up{else}down{/if}"><i class="fa fa-caret-{if $PRODUCT.units_delta_pct >= 0}up{else}down{/if}"></i> {$PRODUCT.units_delta_pct}%</span> {$LANG.statistics.vs_prior_period}
         {/if}
       </div>
     </div>
     <div class="ps-kpi">
       <div class="ps-kpi-label">{$LANG.statistics.orders}</div>
       <div class="ps-kpi-value">{number_format($PRODUCT.total_orders)}</div>
       <div class="ps-kpi-meta">{if $PRODUCT.avg_per_order > 0}{sprintf($LANG.orders.per_order,$PRODUCT.avg_per_order)}{/if}</div>
     </div>
     {if $PRODUCT.days_of_stock !== null}
     <div class="ps-kpi{if $PRODUCT.days_of_stock < 14} ps-kpi--warn{/if}">
       <div class="ps-kpi-label">{$LANG.statistics.days_of_stock}</div>
       <div class="ps-kpi-value">{$PRODUCT.days_of_stock}</div>
       <div class="ps-kpi-meta">{$LANG.statistics.stock}: {number_format($PRODUCT.stock_level)}</div>
     </div>
     {elseif $PRODUCT.stock_level !== null}
     <div class="ps-kpi">
       <div class="ps-kpi-label">{$LANG.statistics.stock}</div>
       <div class="ps-kpi-value">{number_format($PRODUCT.stock_level)}</div>
       <div class="ps-kpi-meta">&nbsp;</div>
     </div>
     {/if}
   </div>

   <div class="ps-grid">
     <table class="ps-section">
      <thead>
        <tr><th colspan="2">{$LANG.common.overview}</th></tr>
      </thead>
      <tbody>
       <tr><td>{$LANG.common.created}</td><td>{$PRODUCT.date_added_fmt}</td></tr>
       <tr><td>{$LANG.common.updated}</td><td>{$PRODUCT.updated_fmt}</td></tr>
       <tr><td>{$LANG.statistics.first_sale}</td><td>{$PRODUCT.first_sale}</td></tr>
       <tr><td>{$LANG.statistics.last_sale}</td><td>{$PRODUCT.last_sale}</td></tr>
       <tr><td>{$LANG.statistics.sale_interval}</td><td>{$PRODUCT.sale_interval}</td></tr>
       {if $PRODUCT.lost_orders > 0}
       <tr>
         <td>{$LANG.statistics.refunded_cancelled}</td>
         <td>{number_format($PRODUCT.lost_units)} &middot; {number_format($PRODUCT.lost_orders)} {$LANG.statistics.orders|lower} &middot; {$PRODUCT.lost_revenue}</td>
       </tr>
       {/if}
      </tbody>
     </table>

     {if $VARIANTS}
     <table class="ps-section">
      <thead>
        <tr>
          <th>{$LANG.statistics.top_variants}</th>
          <th class="text-center">{$LANG.statistics.quantity_sold}</th>
          <th class="text-center">{$LANG.statistics.revenue}</th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$VARIANTS item=v}
        <tr>
          <td>{$v.product_code}</td>
          <td class="text-center">{number_format($v.units)}</td>
          <td class="text-center">{$v.revenue_fmt}</td>
        </tr>
        {/foreach}
      </tbody>
     </table>
     {/if}
   </div>

   {if $CUSTOMERS}
   <table class="ps-section">
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
        <td class="text-center"><a href="?_g=orders&search[search_customer_id]={$c.customer_id}&search[product_id]={$PRODUCT.product_id}">{$c.purchases}</a></td>
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
