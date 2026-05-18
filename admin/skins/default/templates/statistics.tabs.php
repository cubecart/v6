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
 *
 * Per-tab body. Used by initial render via {include} in statistics.index.php
 * and as the standalone AJAX fragment response.
 *}
{if $ACTIVE_TAB == 'stats_sales'}
   <h3>{$LANG.statistics.title_sales}</h3>

   <form action="{$VAL_SELF}" class="ignore-dirty stats-filter stats-status-filter" method="get">
      <span class="stats-status-label">{$LANG.statistics.order_status_label}</span>
      {foreach from=$SALES_STATUSES item=st}
      <label class="stats-status-option"><input type="checkbox" name="s[]" value="{$st.id}"{$st.checked}> {$st.name}</label>
      {/foreach}
      <input type="submit" class="tiny" value="{$LANG.common.go}">
      <input type="hidden" name="_g" value="statistics">
      <input type="hidden" name="tab" value="stats_sales">
      {if isset($SALES_FILTER)}
      <input type="hidden" name="m_year"  value="{$SALES_FILTER.m_year}">
      <input type="hidden" name="m_month" value="{$SALES_FILTER.m_month}">
      <input type="hidden" name="d_year"  value="{$SALES_FILTER.d_year}">
      <input type="hidden" name="d_month" value="{$SALES_FILTER.d_month}">
      <input type="hidden" name="h_year"  value="{$SALES_FILTER.h_year}">
      <input type="hidden" name="h_month" value="{$SALES_FILTER.h_month}">
      <input type="hidden" name="h_day"   value="{$SALES_FILTER.h_day}">
      {/if}
   </form>

   {if isset($DISPLAY_SALES) && $DISPLAY_SALES}

   <div class="stat-chart">
      <div class="stat-chart-header">
         <span class="stat-chart-title">{$GRAPH_DATA.1.title}</span>
      </div>
      <div class="stat-chart-body">
         <div id="chart1" class="google_chart"></div>
         <div id="chart1-title" class="chart-meta"></div>
         <div id="chart1-hAxis" class="chart-meta">{$GRAPH_DATA.1.hAxis}</div>
         <div id="chart1-vAxis" class="chart-meta">{$GRAPH_DATA.1.vAxis}</div>
      </div>
      <div class="stat-chart-footer">{$LANG.statistics.footer_orders|sprintf:$GRAPH_DATA.1.total_sum:$GRAPH_DATA.1.total_count}</div>
   </div>

   <div class="stat-chart">
      <div class="stat-chart-header">
         <span class="stat-chart-title">{$GRAPH_DATA.2.title}</span>
         <form action="{$VAL_SELF}" class="ignore-dirty stats-filter" method="get">
            <select name="m_year" class="textbox">
            {foreach from=$M_YEARS item=year}<option value="{$year.value}" {$year.selected}>{$year.label}</option>{/foreach}
            </select>
            <select name="m_month" class="textbox">
            {foreach from=$M_MONTHS item=month}<option value="{$month.value}"{$month.selected}{$month.disabled}>{$month.title}</option>{/foreach}
            </select>
            <input type="submit" class="tiny" value="{$LANG.common.go}">
            <input type="hidden" name="_g" value="statistics">
            <input type="hidden" name="tab" value="stats_sales">
            <input type="hidden" name="d_year"  value="{$SALES_FILTER.d_year}">
            <input type="hidden" name="d_month" value="{$SALES_FILTER.d_month}">
            <input type="hidden" name="h_year"  value="{$SALES_FILTER.h_year}">
            <input type="hidden" name="h_month" value="{$SALES_FILTER.h_month}">
            <input type="hidden" name="h_day"   value="{$SALES_FILTER.h_day}">
            {foreach from=$SALES_STATUSES item=st}{if $st.checked}<input type="hidden" name="s[]" value="{$st.id}">{/if}{/foreach}
         </form>
      </div>
      <div class="stat-chart-body">
         <div id="chart2" class="google_chart"></div>
         <div id="chart2-title" class="chart-meta"></div>
         <div id="chart2-hAxis" class="chart-meta">{$GRAPH_DATA.2.hAxis}</div>
         <div id="chart2-vAxis" class="chart-meta">{$GRAPH_DATA.2.vAxis}</div>
      </div>
      <div class="stat-chart-footer">{$LANG.statistics.footer_orders|sprintf:$GRAPH_DATA.2.total_sum:$GRAPH_DATA.2.total_count}</div>
   </div>

   <div class="stat-chart">
      <div class="stat-chart-header">
         <span class="stat-chart-title">{$GRAPH_DATA.3.title}</span>
         <form action="{$VAL_SELF}" class="ignore-dirty stats-filter" method="get">
            <select name="d_year" class="textbox">
            {foreach from=$D_YEARS item=year}<option value="{$year.value}" {$year.selected}>{$year.label}</option>{/foreach}
            </select>
            <select name="d_month" class="textbox">
            {foreach from=$D_MONTHS item=month}<option value="{$month.value}"{$month.selected}{$month.disabled}>{$month.title}</option>{/foreach}
            </select>
            <input type="submit" class="tiny" value="{$LANG.common.go}">
            <input type="hidden" name="_g" value="statistics">
            <input type="hidden" name="tab" value="stats_sales">
            <input type="hidden" name="m_year"  value="{$SALES_FILTER.m_year}">
            <input type="hidden" name="m_month" value="{$SALES_FILTER.m_month}">
            <input type="hidden" name="h_year"  value="{$SALES_FILTER.h_year}">
            <input type="hidden" name="h_month" value="{$SALES_FILTER.h_month}">
            <input type="hidden" name="h_day"   value="{$SALES_FILTER.h_day}">
            {foreach from=$SALES_STATUSES item=st}{if $st.checked}<input type="hidden" name="s[]" value="{$st.id}">{/if}{/foreach}
         </form>
      </div>
      <div class="stat-chart-body">
         <div id="chart3" class="google_chart"></div>
         <div id="chart3-title" class="chart-meta"></div>
         <div id="chart3-hAxis" class="chart-meta">{$GRAPH_DATA.3.hAxis}</div>
         <div id="chart3-vAxis" class="chart-meta">{$GRAPH_DATA.3.vAxis}</div>
      </div>
      <div class="stat-chart-footer">{$LANG.statistics.footer_orders|sprintf:$GRAPH_DATA.3.total_sum:$GRAPH_DATA.3.total_count}</div>
   </div>

   <div class="stat-chart">
      <div class="stat-chart-header">
         <span class="stat-chart-title">{$GRAPH_DATA.4.title}</span>
         <form action="{$VAL_SELF}" class="ignore-dirty stats-filter" method="get">
            <select name="h_year" class="textbox">
            {foreach from=$H_YEARS item=year}<option value="{$year.value}" {$year.selected}>{$year.label}</option>{/foreach}
            </select>
            <select name="h_month" class="textbox">
            {foreach from=$H_MONTHS item=month}<option value="{$month.value}"{$month.selected}{$month.disabled}>{$month.title}</option>{/foreach}
            </select>
            <select name="h_day" class="textbox">
            {foreach from=$H_DAYS item=day}<option value="{$day.value}"{$day.selected}{$day.disabled}>{$day.value}</option>{/foreach}
            </select>
            <input type="submit" class="tiny" value="{$LANG.common.go}">
            <input type="hidden" name="_g" value="statistics">
            <input type="hidden" name="tab" value="stats_sales">
            <input type="hidden" name="m_year"  value="{$SALES_FILTER.m_year}">
            <input type="hidden" name="m_month" value="{$SALES_FILTER.m_month}">
            <input type="hidden" name="d_year"  value="{$SALES_FILTER.d_year}">
            <input type="hidden" name="d_month" value="{$SALES_FILTER.d_month}">
            {foreach from=$SALES_STATUSES item=st}{if $st.checked}<input type="hidden" name="s[]" value="{$st.id}">{/if}{/foreach}
         </form>
      </div>
      <div class="stat-chart-body">
         <div id="chart4" class="google_chart"></div>
         <div id="chart4-title" class="chart-meta"></div>
         <div id="chart4-hAxis" class="chart-meta">{$GRAPH_DATA.4.hAxis}</div>
         <div id="chart4-vAxis" class="chart-meta">{$GRAPH_DATA.4.vAxis}</div>
      </div>
      <div class="stat-chart-footer">{$LANG.statistics.footer_orders|sprintf:$GRAPH_DATA.4.total_sum:$GRAPH_DATA.4.total_count}</div>
   </div>

   <script type="text/javascript">
      window.chart_data = window.chart_data || [];
      window.chart_options = window.chart_options || [];
      {foreach from=$GRAPH_DATA key=k item=v}
      window.chart_data[{$k}] = [{$v.data}];
      window.chart_options[{$k}] = {};
      {if isset($v.colors)}window.chart_options[{$k}].colors = {$v.colors};{/if}
      {if isset($v.legend)}window.chart_options[{$k}].legend = '{$v.legend}';{/if}
      {if isset($v.y_format)}window.chart_options[{$k}].yFormat = '{$v.y_format}';{/if}
      {if isset($v.drill)}window.chart_options[{$k}].drill = {$v.drill|json_encode};{/if}
      {/foreach}
      window.whenChartsReady(function() {
         {foreach from=$GRAPH_DATA key=k item=v}
         window.drawChart({$k}, window.chart_data);
         {/foreach}
      });
   </script>
   {else}
   <p>{$LANG.statistics.notify_sales_none}</p>
   {/if}
{elseif $ACTIVE_TAB == 'stats_funnel'}
   <h3>{$LANG.statistics.title_funnel}</h3>
   <div class="stat-chart">
      <div class="stat-chart-header">
         <span class="stat-chart-title">{$LANG.statistics.last_7_days}</span>
      </div>
      <div class="stat-chart-body">
         <table width="100%" class="stat-table funnel-table">
            <thead>
               <tr>
                  <td>{$LANG.statistics.funnel_stage}</td>
                  <td class="text-right" nowrap="nowrap">{$LANG.statistics.funnel_count}</td>
                  <td class="text-right" nowrap="nowrap">{$LANG.statistics.funnel_pct_total}</td>
                  <td class="text-right" nowrap="nowrap">{$LANG.statistics.funnel_pct_prev}</td>
                  <td class="funnel-bar-cell">&nbsp;</td>
               </tr>
            </thead>
            <tbody>
               {foreach from=$FUNNEL_STAGES item=stage}
               <tr>
                  <td><strong>{$stage.name}</strong></td>
                  <td class="text-right" nowrap="nowrap">{$stage.count|number_format}</td>
                  <td class="text-right" nowrap="nowrap">{$stage.pct_total}%</td>
                  <td class="text-right" nowrap="nowrap">{if $stage.pct_prev !== null}{$stage.pct_prev}%{else}&mdash;{/if}</td>
                  <td><div class="funnel-bar" style="width: {$stage.pct_total}%"></div></td>
               </tr>
               {/foreach}
            </tbody>
         </table>
      </div>
      <div class="stat-chart-footer">{$FUNNEL_SUB_VALUE} &middot; {$FUNNEL_PAID_VALUE}</div>
   </div>
{elseif $ACTIVE_TAB == 'stats_abandoned'}
   <h3>{$LANG.statistics.title_abandoned}</h3>
   <div class="stat-chart">
      <div class="stat-chart-header">
         <span class="stat-chart-title">{$LANG.statistics.abandoned_subtitle}</span>
      </div>
      {if $ABANDONED_CARTS}
      <div class="stat-chart-body">
         <table width="100%" class="stat-table">
            <thead>
               <tr>
                  <td>{$LANG.statistics.abandoned_customer}</td>
                  <td class="text-center" nowrap="nowrap">{$LANG.statistics.abandoned_items}</td>
                  <td class="text-right" nowrap="nowrap">{$LANG.statistics.abandoned_value}</td>
                  <td class="text-center" nowrap="nowrap">{$LANG.statistics.abandoned_idle}</td>
                  <td>{$LANG.common.ip_address}</td>
                  <td></td>
               </tr>
            </thead>
            <tbody>
               {foreach from=$ABANDONED_CARTS item=ab}
               <tr>
                  <td>
                     {if $ab.customer_id > 0}
                     <a href="?_g=customers&node=index&action=edit&customer_id={$ab.customer_id}">{$ab.name}</a>
                     {if $ab.email} <small>&lt;{$ab.email}&gt;</small>{/if}
                     {else}
                     {$ab.name}
                     {/if}
                  </td>
                  <td class="text-center">{$ab.item_count}</td>
                  <td class="text-right" nowrap="nowrap"><strong>{$ab.cart_value}</strong></td>
                  <td class="text-center" nowrap="nowrap">{$ab.last_idle}</td>
                  <td nowrap="nowrap"><a href="http://whois.domaintools.com/{$ab.ip_address}" target="_blank" rel="noopener">{$ab.ip_address}</a></td>
                  <td class="text-center">{if $ab.email}<a href="mailto:{$ab.email}" title="{$LANG.statistics.email_customer}"><i class="fa fa-envelope"></i></a>{/if}</td>
               </tr>
               {/foreach}
            </tbody>
         </table>
      </div>
      <div class="stat-chart-footer">{$LANG.statistics.abandoned_total|sprintf:$ABANDONED_TOTAL:$ABANDONED_COUNT}</div>
      {else}
      <div class="stat-chart-body stat-empty">{$LANG.statistics.abandoned_none}</div>
      {/if}
   </div>
{elseif $ACTIVE_TAB == 'stats_country'}
   <h3>{$LANG.statistics.title_country}</h3>
   <div class="stat-chart">
      <div class="stat-chart-header">
         <span class="stat-chart-title">{$LANG.statistics.title_country}</span>
         <form action="{$VAL_SELF}" class="ignore-dirty stats-filter" method="get">
            <select name="cn_year" class="textbox">
            {foreach from=$CN_YEAR_OPTIONS item=opt}<option value="{$opt.value}"{$opt.selected}>{$opt.label}</option>{/foreach}
            </select>
            <input type="submit" class="tiny" value="{$LANG.common.go}">
            <input type="hidden" name="_g" value="statistics">
            <input type="hidden" name="tab" value="stats_country">
         </form>
      </div>
      {if $COUNTRY_ROWS}
      <div class="stat-chart-body">
         <div id="chart11" class="google_chart"></div>
         <div id="chart11-title" class="chart-meta">{$GRAPH_DATA.11.title}</div>
         <div id="chart11-hAxis" class="chart-meta">{$GRAPH_DATA.11.hAxis}</div>
         <div id="chart11-vAxis" class="chart-meta">{$GRAPH_DATA.11.vAxis}</div>
         <table width="100%" class="stat-table">
            <thead>
               <tr>
                  <td></td>
                  <td>{$LANG.statistics.country_label}</td>
                  <td class="text-center" nowrap="nowrap">{$LANG.statistics.orders}</td>
                  <td class="text-right" nowrap="nowrap">{$LANG.statistics.revenue}</td>
                  <td class="text-center" nowrap="nowrap">{$LANG.statistics.country_pct_total}</td>
               </tr>
            </thead>
            <tbody>
               {foreach from=$COUNTRY_ROWS item=row}
               <tr>
                  <td class="text-center">{$row.rank}</td>
                  <td>{if $row.flag}<span class="country-flag">{$row.flag}</span> {/if}{$row.name}</td>
                  <td class="text-center">{$row.orders}</td>
                  <td class="text-right" nowrap="nowrap">{$row.revenue}</td>
                  <td class="text-center">{$row.percent}%</td>
               </tr>
               {/foreach}
            </tbody>
         </table>
      </div>
      <div class="stat-chart-footer">{$LANG.statistics.country_total|sprintf:$GRAPH_DATA.11.total_sum:$GRAPH_DATA.11.total_count}</div>
      <script type="text/javascript">
         window.chart_data = window.chart_data || [];
         window.chart_options = window.chart_options || [];
         window.chart_data[11] = [{$GRAPH_DATA.11.data}];
         window.chart_options[11] = {};
         window.chart_options[11].colors = {$GRAPH_DATA.11.colors};
         window.chart_options[11].legend = '{$GRAPH_DATA.11.legend}';
         window.whenChartsReady(function() { window.drawChart(11, window.chart_data); });
      </script>
      {else}
      <div class="stat-chart-body stat-empty">{$LANG.statistics.country_no_data}</div>
      {/if}
   </div>
{elseif $ACTIVE_TAB == 'stats_prod_sales'}
   <h3>{$LANG.statistics.title_popular}</h3>
   <div class="stat-chart">
      <div class="stat-chart-header">
         <span class="stat-chart-title">{$LANG.statistics.title_popular}</span>
         <form action="{$VAL_SELF}" class="ignore-dirty stats-filter" method="get">
            <select name="ps_year" class="textbox">
            {foreach from=$PS_YEAR_OPTIONS item=opt}<option value="{$opt.value}"{$opt.selected}>{$opt.label}</option>{/foreach}
            </select>
            <select name="ps_month" class="textbox">
            {foreach from=$PS_MONTH_OPTIONS item=opt}<option value="{$opt.value}"{$opt.selected}{$opt.disabled}>{$opt.title}</option>{/foreach}
            </select>
            <input type="submit" class="tiny" value="{$LANG.common.go}">
            <input type="hidden" name="_g" value="statistics">
            <input type="hidden" name="tab" value="stats_prod_sales">
         </form>
      </div>
      {if isset($PRODUCT_SALES) && $PRODUCT_SALES}
      <div class="stat-chart-body">
         <div id="chart5" class="google_chart"></div>
         <div id="chart5-title" class="chart-meta">{$GRAPH_DATA.5.title}</div>
         <div id="chart5-hAxis" class="chart-meta">{$GRAPH_DATA.5.hAxis}</div>
         <div id="chart5-vAxis" class="chart-meta">{$GRAPH_DATA.5.vAxis}</div>
         <div class="pagination">{$PAGINATION_SALES}</div>
         <table width="100%" class="stat-table">
            <thead>
               <tr>
                  <td></td>
                  <td>{$LANG.catalogue.product_name}</td>
                  <td class="text-center" nowrap="nowrap"><span title="{$LANG.statistics.quantity_sold}">{$LANG.common.quantity}</span></td>
                  <td class="text-center" nowrap="nowrap">{$LANG.statistics.revenue}</td>
                  <td class="text-center" nowrap="nowrap"><span title="{$LANG.statistics.percentage_of_total}">{$LANG.common.percentage}</span></td>
                  <td class="text-center" nowrap="nowrap">{$LANG.statistics.stock}</td>
                  {if $PS_HAS_TREND}<td class="text-center" nowrap="nowrap">{$LANG.statistics.trend}</td>{/if}
                  <td></td>
               </tr>
            </thead>
            <tbody>
               {foreach from=$PRODUCT_SALES item=sale}
               <tr>
                  <td class="text-center">{$sale.key}</td>
                  <td><a href="?_g=statistics&amp;node=product&amp;product_id={$sale.product_id}">{$sale.name}</a></td>
                  <td class="text-center">{$sale.quan}</td>
                  <td class="text-right" nowrap="nowrap">{$sale.revenue_formatted}</td>
                  <td class="text-center">{$sale.percent}</td>
                  <td class="text-center{if $sale.stock_low} stock-low{/if}">{$sale.stock_display}</td>
                  {if $PS_HAS_TREND}<td class="text-center" nowrap="nowrap">{if $sale.trend}<span class="stat-trend stat-trend--{$sale.trend.dir}"><i class="fa fa-caret-{$sale.trend.dir}"></i> {$sale.trend.label}</span>{else}&mdash;{/if}</td>{/if}
                  <td class="text-center"><a href="?_g=statistics&amp;node=product&amp;product_id={$sale.product_id}" title="{$LANG.statistics.title_popular}"><i class="fa fa-bar-chart"></i></a></td>
               </tr>
               {/foreach}
            </tbody>
         </table>
      </div>
      <div class="stat-chart-footer">{$LANG.statistics.footer_products|sprintf:$GRAPH_DATA.5.total_sum:$GRAPH_DATA.5.total_count}</div>
      <script type="text/javascript">
         window.chart_data = window.chart_data || [];
         window.chart_options = window.chart_options || [];
         window.chart_data[5] = [{$GRAPH_DATA.5.data}];
         window.chart_options[5] = {};
         window.chart_options[5].colors = {$GRAPH_DATA.5.colors};
         window.chart_options[5].drill = {$GRAPH_DATA.5.drill|json_encode};
         window.whenChartsReady(function() { window.drawChart(5, window.chart_data); });
      </script>
      {else}
      <div class="stat-chart-body stat-empty">{$LANG.statistics.notify_sales_none}</div>
      {/if}
   </div>
{elseif $ACTIVE_TAB == 'stats_prod_views'}
   <h3>{$LANG.statistics.title_viewed}</h3>
   <div class="stat-chart">
      <div class="stat-chart-header">
         <span class="stat-chart-title">{$LANG.statistics.title_viewed}</span>
      </div>
      {if isset($PRODUCT_VIEWS) && $PRODUCT_VIEWS}
      <div class="stat-chart-body">
         <div id="chart6" class="google_chart"></div>
         <div id="chart6-title" class="chart-meta">{$GRAPH_DATA.6.title}</div>
         <div id="chart6-hAxis" class="chart-meta">{$GRAPH_DATA.6.hAxis}</div>
         <div id="chart6-vAxis" class="chart-meta">{$GRAPH_DATA.6.vAxis}</div>
         <div class="pagination">{$PAGINATION_VIEWS}</div>
         <table width="100%" class="stat-table">
            <thead>
               <tr>
                  <td></td>
                  <td>{$LANG.catalogue.product_name}</td>
                  <td class="text-center" nowrap="nowrap">{$LANG.statistics.product_views}</td>
                  <td class="text-center" nowrap="nowrap"><span title="{$LANG.statistics.percentage_of_views}">{$LANG.common.percentage}</span></td>
                  <td class="text-center" nowrap="nowrap">{$LANG.statistics.stock}</td>
                  <td></td>
               </tr>
            </thead>
            <tbody>
               {foreach from=$PRODUCT_VIEWS item=view}
               <tr>
                  <td class="text-center">{$view.key}</td>
                  <td><a href="?_g=statistics&amp;node=product&amp;product_id={$view.product_id}">{$view.name}</a></td>
                  <td class="text-center">{$view.popularity}</td>
                  <td class="text-center">{$view.percent}</td>
                  <td class="text-center{if $view.stock_low} stock-low{/if}">{$view.stock_display}</td>
                  <td class="text-center"><a href="?_g=statistics&amp;node=product&amp;product_id={$view.product_id}" title="{$LANG.statistics.title_viewed}"><i class="fa fa-bar-chart"></i></a></td>
               </tr>
               {/foreach}
            </tbody>
         </table>
      </div>
      <div class="stat-chart-footer">{$LANG.statistics.footer_products|sprintf:$GRAPH_DATA.6.total_sum:$GRAPH_DATA.6.total_count}</div>
      <script type="text/javascript">
         window.chart_data = window.chart_data || [];
         window.chart_options = window.chart_options || [];
         window.chart_data[6] = [{$GRAPH_DATA.6.data}];
         window.chart_options[6] = {};
         window.chart_options[6].colors = {$GRAPH_DATA.6.colors};
         window.chart_options[6].drill = {$GRAPH_DATA.6.drill|json_encode};
         window.whenChartsReady(function() { window.drawChart(6, window.chart_data); });
      </script>
      {else}
      <div class="stat-chart-body stat-empty">{$LANG.form.none}</div>
      {/if}
   </div>
{elseif $ACTIVE_TAB == 'stats_search'}
   <h3>{$LANG.statistics.title_search}</h3>
   <div class="stat-chart">
      <div class="stat-chart-header">
         <span class="stat-chart-title">{$LANG.statistics.title_search}</span>
         {if isset($SEARCH_TERMS) && $SEARCH_TERMS}<a href="?_g=maintenance&clearSearch=true&redir=searchlog" class="button delete" title="{$LANG.notification.confirm_continue}">{$LANG.maintain.clear_log}</a>{/if}
      </div>
      {if isset($SEARCH_TERMS) && $SEARCH_TERMS}
      <div class="stat-chart-body">
         <div id="chart7" class="google_chart"></div>
         <div id="chart7-title" class="chart-meta">{$GRAPH_DATA.7.title}</div>
         <div id="chart7-hAxis" class="chart-meta">{$GRAPH_DATA.7.hAxis}</div>
         <div id="chart7-vAxis" class="chart-meta">{$GRAPH_DATA.7.vAxis}</div>
         <div class="pagination">{$PAGINATION_SEARCH}</div>
         <table width="100%" class="stat-table">
            <thead>
               <tr>
                  <td></td>
                  <td>{$LANG.statistics.search_term}</td>
                  <td class="text-center">{$LANG.statistics.product_hits}</td>
                  <td class="text-center"><span title="{$LANG.statistics.percentage_of_search}">{$LANG.common.percentage}</span></td>
                  <td></td>
               </tr>
            </thead>
            <tbody>
               {foreach from=$SEARCH_TERMS item=term}
               <tr>
                  <td class="text-center">{$term.key}</td>
                  <td><a href="{$term.search_url}" target="_blank" rel="noopener">{$term.searchstr}</a></td>
                  <td class="text-center">{$term.hits}</td>
                  <td class="text-center">{$term.percent}</td>
                  <td class="text-center"><a href="{$term.search_url}" target="_blank" rel="noopener" title="{$LANG.statistics.view_results}"><i class="fa fa-external-link"></i></a></td>
               </tr>
               {/foreach}
            </tbody>
         </table>
      </div>
      <div class="stat-chart-footer">{$LANG.statistics.footer_terms|sprintf:$GRAPH_DATA.7.total_sum:$GRAPH_DATA.7.total_count}</div>
      <script type="text/javascript">
         window.chart_data = window.chart_data || [];
         window.chart_options = window.chart_options || [];
         window.chart_data[7] = [{$GRAPH_DATA.7.data}];
         window.chart_options[7] = {};
         window.chart_options[7].colors = {$GRAPH_DATA.7.colors};
         window.chart_options[7].drill = {$GRAPH_DATA.7.drill|json_encode};
         window.whenChartsReady(function() { window.drawChart(7, window.chart_data); });
      </script>
      {else}
      <div class="stat-chart-body stat-empty">{$LANG.statistics.notify_searches_none}</div>
      {/if}
   </div>
{elseif $ACTIVE_TAB == 'stats_best_customers'}
   <h3>{$LANG.statistics.title_customers_best}</h3>
   <div class="stat-chart">
      <div class="stat-chart-header">
         <span class="stat-chart-title">{$LANG.statistics.title_customers_best}</span>
         <form action="{$VAL_SELF}" class="ignore-dirty stats-filter" method="get">
            <select name="bc_year" class="textbox">
            {foreach from=$BC_YEAR_OPTIONS item=opt}<option value="{$opt.value}"{$opt.selected}>{$opt.label}</option>{/foreach}
            </select>
            <input type="submit" class="tiny" value="{$LANG.common.go}">
            <input type="hidden" name="_g" value="statistics">
            <input type="hidden" name="tab" value="stats_best_customers">
         </form>
      </div>
      {if isset($BEST_CUSTOMERS) && $BEST_CUSTOMERS}
      <div class="stat-chart-body">
         <div id="chart8" class="google_chart"></div>
         <div id="chart8-title" class="chart-meta">{$GRAPH_DATA.8.title}</div>
         <div id="chart8-hAxis" class="chart-meta">{$GRAPH_DATA.8.hAxis}</div>
         <div id="chart8-vAxis" class="chart-meta">{$GRAPH_DATA.8.vAxis}</div>
         <div class="pagination">{$PAGINATION_BEST}</div>
         <table width="100%" class="stat-table">
            <thead>
               <tr>
                  <td></td>
                  <td>{$LANG.common.name}</td>
                  <td class="text-center" nowrap="nowrap">{$LANG.statistics.orders}</td>
                  <td class="text-right" nowrap="nowrap">{$LANG.statistics.total_expenditure}</td>
                  <td class="text-center" nowrap="nowrap">{$LANG.statistics.percentage_of_total}</td>
                  <td></td>
               </tr>
            </thead>
            <tbody>
               {foreach from=$BEST_CUSTOMERS item=customer}
               <tr>
                  <td class="text-center">{$customer.key}</td>
                  <td><a href="?_g=customers&node=index&action=edit&customer_id={$customer.customer_id}" class="capitalize">{$customer.last_name}, {$customer.first_name}</a></td>
                  <td class="text-center">{$customer.order_count}</td>
                  <td class="text-right" nowrap="nowrap">{$customer.expenditure}</td>
                  <td class="text-center">{$customer.percent}</td>
                  <td class="text-center"><a href="?_g=customers&node=index&action=edit&customer_id={$customer.customer_id}" title="{$LANG.common.edit}"><i class="fa fa-user"></i></a></td>
               </tr>
               {/foreach}
            </tbody>
         </table>
      </div>
      <div class="stat-chart-footer">{$LANG.statistics.footer_customers|sprintf:$GRAPH_DATA.8.total_sum:$GRAPH_DATA.8.total_count}</div>
      <script type="text/javascript">
         window.chart_data = window.chart_data || [];
         window.chart_options = window.chart_options || [];
         window.chart_data[8] = [{$GRAPH_DATA.8.data}];
         window.chart_options[8] = {};
         window.chart_options[8].colors = {$GRAPH_DATA.8.colors};
         window.chart_options[8].drill = {$GRAPH_DATA.8.drill|json_encode};
         window.whenChartsReady(function() { window.drawChart(8, window.chart_data); });
      </script>
      {else}
      <div class="stat-chart-body stat-empty">{$LANG.statistics.notify_customers_none}</div>
      {/if}
   </div>
{elseif $ACTIVE_TAB == 'stats_online'}
   <h3>{$LANG.statistics.title_customers_active}</h3>
   <div class="stat-chart">
      <div class="stat-chart-header">
         <span class="stat-chart-title">{$LANG.statistics.title_customers_active}</span>
         <a href="#" class="button tiny stats-refresh" title="{$LANG.statistics.refresh_now}"><i class="fa fa-refresh"></i></a>
         {if $BOTS==true}
         <a href="?_g=statistics&bots=false&tab=stats_online#stats_online" class="button tiny">{$LANG.statistics.display_customers_only}</a>
         {else}
         <a href="?_g=statistics&bots=true&tab=stats_online#stats_online" class="button tiny">{$LANG.statistics.display_bots_and_customers}</a>
         {/if}
      </div>
      <div class="stat-chart-body">
         <table width="100%" class="stat-table">
            <thead>
               <tr>
                  <td></td>
                  <td>{$LANG.statistics.session_user}</td>
                  <td>{$LANG.statistics.session_location}</td>
                  <td class="text-right" nowrap="nowrap">{$LANG.statistics.cart}</td>
                  <td class="text-center" nowrap="nowrap">{$LANG.statistics.online_active_for}</td>
                  <td class="text-center" nowrap="nowrap">{$LANG.statistics.online_last_seen}</td>
                  <td class="text-center">{$LANG.statistics.country_label}</td>
                  <td>{$LANG.common.ip_address}</td>
               </tr>
            </thead>
            <tbody>
               {foreach from=$USERS_ONLINE item=user}
               <tr{if $user.is_checkout} class="row-at-checkout"{/if}>
                  <td class="text-center" nowrap="nowrap">
                     {if $user.is_bot}<span class="stat-badge stat-badge--bot">{$user.bot_name}</span>{else}<img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/{$user.is_admin}.png" alt="">{/if}
                  </td>
                  <td>
                     <strong>
                     {if !empty($user.customer_id)}
                     <a href="{$CONFIG.adminFile}?_g=customers&action=edit&customer_id={$user.customer_id}">{$user.name}</a>
                     {else}
                     {$user.name}
                     {/if}
                     </strong>
                  </td>
                  <td>
                     {$user.location_label}
                     {if !empty($user.location) && strpos($user.location, "404") === false} <a href="{$STORE_URL}/{$user.location}" target="_blank" rel="noopener" title="{$LANG.common.open_page}"><i class="fa fa-external-link"></i></a>{/if}
                  </td>
                  <td class="text-right" nowrap="nowrap">{if $user.cart_value}<strong>{$user.cart_value}</strong>{else}&mdash;{/if}</td>
                  <td class="text-center" nowrap="nowrap">{$user.active_for}</td>
                  <td class="text-center" nowrap="nowrap">{$user.last_relative}</td>
                  <td class="text-center">{if $user.country}{$user.country}{else}&mdash;{/if}</td>
                  <td nowrap="nowrap">{if !empty($user.ip_address)}<a href="http://whois.domaintools.com/{$user.ip_address}" target="_blank" rel="noopener">{$user.ip_address}</a>{/if}</td>
               </tr>
               {foreachelse}
               <tr><td colspan="8" class="text-center">{$LANG.form.none}</td></tr>
               {/foreach}
            </tbody>
         </table>
      </div>
      <div class="stat-chart-footer">
         {$LANG.statistics.online_total_summary|sprintf:$USERS_SPLIT.total:$USERS_SPLIT.signed_in:$USERS_SPLIT.guests}
      </div>
   </div>
{/if}
