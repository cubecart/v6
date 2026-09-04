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
<div id="dashboard" class="tab_content">
   {if !empty($NEWS.items) && $NEWS_TOP_HASH != $NEWS_DISMISSED_LINK}
   <div id="news-banner" class="news-banner" data-link="{$NEWS.items[0].link}" data-token="{$SESSION_TOKEN}">
      <span class="news-banner__label">{$NEWS.title|default:"News"}</span>
      <a href="{$NEWS.items[0].link}" target="_blank" class="news-banner__link">{$NEWS.items[0].title}</a>
      <button type="button" class="news-banner__close" aria-label="Dismiss">&times;</button>
   </div>
   {literal}
   <script>
   (function() {
      var banner = document.getElementById('news-banner');
      if (!banner) return;
      var btn = banner.querySelector('.news-banner__close');
      if (!btn) return;
      btn.addEventListener('click', function() {
         var fd = new FormData();
         fd.append('dismiss_news', '1');
         fd.append('news_link', banner.getAttribute('data-link') || '');
         fd.append('token', banner.getAttribute('data-token') || '');
         fetch('?_g=dashboard', { method: 'POST', body: fd, credentials: 'same-origin' });
         banner.parentNode.removeChild(banner);
      });
   })();
   </script>
   {/literal}
   {/if}
   <h3>{$LANG.dashboard.title_dashboard}</h3>
   <div class="dashboard_content">
      {if isset($QUICK_STATS)}
      <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
      <script type="text/javascript">
         var dashChartSeed = {
            data: [{$CHART.data}],
            colors: {$CHART.colors},
            title: '{$CHART.title}',
            yAxisTitle: '{$CONFIG.default_currency}'
         };
         {literal}
         (function() {
            var masterData = null;
            var chart = null;
            var visible = {};

            function computeYMax() {
               var yMax = 0, nRows = masterData.getNumberOfRows(), nCols = masterData.getNumberOfColumns();
               for (var c = 1; c < nCols; c++) {
                  if (visible[c] === false) continue;
                  for (var r = 0; r < nRows; r++) {
                     var v = masterData.getValue(r, c);
                     if (typeof v === 'number') yMax = Math.max(v, yMax);
                  }
               }
               if (yMax < 20 || !isFinite(yMax)) return 20;
               var exp = Math.floor(Math.log10(yMax));
               return Math.ceil(yMax / Math.pow(10, exp)) * Math.pow(10, exp);
            }

            function drawChart() {
               if (!masterData) return;
               var cols = [0], colors = [];
               for (var c = 1; c < masterData.getNumberOfColumns(); c++) {
                  if (visible[c] !== false) {
                     cols.push(c);
                     colors.push(dashChartSeed.colors[c - 1]);
                  }
               }
               var view = new google.visualization.DataView(masterData);
               view.setColumns(cols);

               var isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
               var theme = isDark
                  ? { backgroundColor: '#171b1f', chartAreaBg: '#171b1f', text: '#e6e6e6', gridline: '#2c343b' }
                  : { backgroundColor: '#ffffff', chartAreaBg: '#ffffff', text: '#555555', gridline: '#ddd' };

               var options = {
                  titleTextStyle: { color: theme.text },
                  backgroundColor: theme.backgroundColor,
                  chartArea: { backgroundColor: theme.chartAreaBg },
                  legend: { position: 'none' },
                  hAxis: { textStyle: { color: theme.text }, gridlines: { color: theme.gridline } },
                  title: dashChartSeed.title,
                  width: '100%', height: 300,
                  colors: colors.length ? colors : undefined,
                  vAxis: {
                     title: dashChartSeed.yAxisTitle,
                     textStyle: { color: theme.text },
                     gridlines: { color: theme.gridline },
                     viewWindowMode: 'explicit',
                     viewWindow: { min: 0, max: computeYMax() }
                  }
               };
               chart.draw(view, options);
            }

            function toggleYear(colIdx, card) {
               var willHide = visible[colIdx] !== false;
               if (willHide) {
                  // Refuse if this is the last visible year — Google Charts errors without at least one series.
                  var remaining = 0;
                  for (var c = 1; c < masterData.getNumberOfColumns(); c++) {
                     if (c !== colIdx && visible[c] !== false) remaining++;
                  }
                  if (remaining === 0) return;
               }
               visible[colIdx] = visible[colIdx] === false;
               card.classList.toggle('annual-sales__card--hidden', visible[colIdx] === false);
               drawChart();
            }

            function initChart() {
               // Skip render if we only have the header row (no real years with sales).
               if (!dashChartSeed.data || dashChartSeed.data.length < 2 || !dashChartSeed.data[0] || dashChartSeed.data[0].length < 2) {
                  return;
               }
               masterData = google.visualization.arrayToDataTable(dashChartSeed.data);
               chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
               document.querySelectorAll('.annual-sales__card').forEach(function(card) {
                  card.addEventListener('click', function() {
                     var c = parseInt(card.getAttribute('data-col'), 10);
                     if (!isNaN(c)) toggleYear(c, card);
                  });
               });
               drawChart();
            }

            google.charts.load('current', { packages: ['corechart'] });
            google.charts.setOnLoadCallback(initChart);

            // Google Charts picks up container width at draw time, and on first paint
            // the dashboard layout isn't always settled (fonts, CSS, async content).
            // Observe the container and redraw whenever its width changes — covers
            // initial layout settling, window resize, sidebar collapse, font-swap etc.
            var debounce;
            function scheduleRedraw() {
               clearTimeout(debounce);
               debounce = setTimeout(function() { if (chart) drawChart(); }, 60);
            }
            window.addEventListener('load', scheduleRedraw);
            window.addEventListener('resize', scheduleRedraw);
            var chartDiv = document.getElementById('chart_div');
            if (chartDiv && 'ResizeObserver' in window) {
               new ResizeObserver(scheduleRedraw).observe(chartDiv);
            }
         })();
         {/literal}
      </script>
      {if !empty($ANNUAL_SALES)}
      <div class="annual-sales">
         {foreach from=$ANNUAL_SALES item=a name=years}
         <div class="annual-sales__card{if $a.empty} annual-sales__card--ghost{/if}"{if !$a.empty} data-col="{$a.chart_col}" style="border-top: 3px solid hsl({$a.hue}, 60%, 50%)"{/if}>
            <div class="annual-sales__year">{$a.year}</div>
            <div class="annual-sales__total">{if $a.empty}&mdash;{else}{$a.total}{/if}</div>
            <div class="annual-sales__bar">{if !$a.empty}<span style="width: {$a.percent}%; background: linear-gradient(90deg, hsl({$a.hue}, 45%, 70%), hsl({$a.hue}, 65%, 45%))"></span>{/if}</div>
         </div>
         {/foreach}
      </div>
      {/if}
      <div id="chart_div"></div>
      {/if}
      <div class="dash-grid">
         <div class="dash-card">
            <h4>{$LANG.dashboard.title_tasks}</h4>
            <ul class="tile-list">
               {foreach from=$CUSTOM_QUICK_TASKS key=k item=task}
               <li><a href="{$task.url}">{$task.name}</a></li>
               {/foreach}
               {foreach from=$DEFAULT_QUICK_TASKS key=url item=name}
               <li><a href="{$url}">{$name}</a></li>
               {/foreach}
            </ul>
         </div>
         <div class="dash-card">
            <h4>{$LANG.dashboard.title_last_orders}<a href="?_g=orders">{$LANG.common.view_all}</a></h4>
            {if isset($LAST_ORDERS)}
            <ul class="tile-list">
               {foreach from=$LAST_ORDERS item=order}
               <li><a href="?_g=orders&action=edit&order_id={$order.cart_order_id}" title="{$LANG.common.edit}">{$order.{$CONFIG.oid_col}|default:$order.cart_order_id}</a> &mdash; {if empty($order.first_name) && empty($order.last_name)}{$order.name}{else}<span class="capitalize">{$order.first_name} {$order.last_name}</span>{/if}</li>
               {/foreach}
            </ul>
            {else}
            <div class="muted">{$LANG.form.none}</div>
            {/if}
         </div>
         {if isset($QUICK_STATS)}
         <div class="dash-card">
            <h4>{$LANG.dashboard.title_store_overview}</h4>
            <div class="stat-grid">
               <div class="stat-cell">
                  <div class="stat-label">{$LANG.dashboard.title_sales_total}</div>
                  <div class="stat-value">{$QUICK_STATS.total_sales}</div>
               </div>
               <div class="stat-cell">
                  <div class="stat-label">{$LANG.dashboard.title_sales_average}</div>
                  <div class="stat-value">{$QUICK_STATS.ave_order}</div>
               </div>
               <div class="stat-cell">
                  <div class="stat-label">{$LANG.dashboard.title_month_this}</div>
                  <div class="stat-value">{$QUICK_STATS.this_month}</div>
                  {if isset($QUICK_STATS.this_month_delta)}<span class="delta delta-{$QUICK_STATS.this_month_delta.direction}" title="{$LANG.dashboard.vs_last_month}">{if $QUICK_STATS.this_month_delta.direction=='up'}&uarr;{else}&darr;{/if} {$QUICK_STATS.this_month_delta.value}</span>{/if}
               </div>
               <div class="stat-cell">
                  <div class="stat-label">{$LANG.dashboard.title_month_last}</div>
                  <div class="stat-value">{$QUICK_STATS.last_month}</div>
               </div>
            </div>
         </div>
         {/if}
         <div class="dash-card">
            <h4>{$LANG.dashboard.title_top_searches}<a href="?_g=statistics#stats_search">{$LANG.common.view_all}</a></h4>
            {if !empty($TOP_SEARCHES)}
            <ul class="tile-list tile-list--search">
               {foreach from=$TOP_SEARCHES item=s}
               <li><span class="search-term">{$s.searchstr}</span><span class="search-count">{number_format($s.hits)}</span></li>
               {/foreach}
            </ul>
            {else}
            <div class="muted">{$LANG.form.none}</div>
            {/if}
         </div>
      </div>
      <div class="dash-bottom">
         <form class="note" id="dash-notes-form" action="?" method="post" data-saved="{$LANG.dashboard.notice_notes_save}" data-error="{$LANG.dashboard.error_notes_save}" data-token="{$SESSION_TOKEN}">
            <p><i class="fa fa-sticky-note" title="{$LANG.common.notes}" aria-hidden="true"></i> {$LANG.dashboard.title_my_notes} <span id="dash-notes-status" class="note-status" aria-live="polite"></span></p>
            <textarea name="notes[dashboard_notes]" id="dash-notes-textarea">{$DASH_NOTES}</textarea>
            <input type="hidden" name="token" value="{$SESSION_TOKEN}">
         </form>
         {literal}
         <script>
         (function() {
            var form = document.getElementById('dash-notes-form');
            if (!form) return;
            var ta = document.getElementById('dash-notes-textarea');
            var status = document.getElementById('dash-notes-status');
            var lastSaved = ta.value;

            function setStatus(text, cls) {
               status.textContent = text;
               status.className = 'note-status' + (cls ? ' note-status--' + cls : '');
               if (cls === 'ok') {
                  setTimeout(function() {
                     if (status.textContent === text) {
                        status.textContent = '';
                        status.className = 'note-status';
                     }
                  }, 2000);
               }
            }

            function save() {
               if (ta.value === lastSaved) return;
               var value = ta.value;
               var fd = new FormData();
               fd.append('notes[dashboard_notes]', value);
               fd.append('ajax', '1');
               fd.append('token', form.dataset.token || '');
               fetch('?_g=dashboard', { method: 'POST', body: fd, credentials: 'same-origin' })
                  .then(function(r) { return r.json(); })
                  .then(function(j) {
                     if (j && j.status === 'ok') {
                        lastSaved = value;
                        setStatus(form.dataset.saved || 'Saved', 'ok');
                     } else {
                        setStatus(form.dataset.error || 'Save failed', 'err');
                     }
                  })
                  .catch(function() { setStatus(form.dataset.error || 'Save failed', 'err'); });
            }

            ta.addEventListener('blur', save);
            form.addEventListener('submit', function(e) { e.preventDefault(); save(); });
            window.addEventListener('beforeunload', function() {
               if (ta.value !== lastSaved && navigator.sendBeacon) {
                  var fd = new FormData();
                  fd.append('notes[dashboard_notes]', ta.value);
                  fd.append('ajax', '1');
                  fd.append('token', form.dataset.token || '');
                  navigator.sendBeacon('?_g=dashboard', fd);
               }
            });
         })();
         </script>
         {/literal}
         <div class="dash-card">
            <h4>{$LANG.dashboard.title_abandoned_carts}<span class="h4-sub">14d</span></h4>
            <div class="abandon-body">
               <div class="abandon-spark">
                  {foreach from=$ABANDON_SPARK item=d}
                  <div class="abandon-spark__col" title="{$d.date} — {$d.sent} sent, {$d.clicked} clicked, {$d.recovered} recovered">
                     <span class="abandon-spark__bar"><span style="height: {$d.percent}%"></span></span>
                     <span class="abandon-spark__day">{$d.label}</span>
                  </div>
                  {/foreach}
               </div>
               <div class="abandon-funnel">
                  <div class="abandon-stat">
                     <div class="abandon-stat__value">{$ABANDON_TOTALS.sent}</div>
                     <div class="abandon-stat__label">Sent</div>
                  </div>
                  <div class="abandon-stat">
                     <div class="abandon-stat__value">{$ABANDON_TOTALS.clicked}</div>
                     <div class="abandon-stat__label">Clicked</div>
                  </div>
                  <div class="abandon-stat">
                     <div class="abandon-stat__value">{$ABANDON_TOTALS.recovered}</div>
                     <div class="abandon-stat__label">Recovered</div>
                  </div>
                  <div class="abandon-stat">
                     <div class="abandon-stat__value">{$ABANDON_TOTALS.rate}%</div>
                     <div class="abandon-stat__label">Rate</div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
{if isset($ORDERS)}
<div id="orders" class="tab_content">
   <h3>{$LANG.dashboard.title_orders_unsettled}</h3>
   <table width="100%" class="filter">
      <tr>
         <td>
            <select class="select_url textbox">
               {foreach from=$PAGE_BREAKS  item=p}
               <option value="{$PAGE_BREAK_URL}&items={$p}#orders"{if $p == $PAGE_BREAK} selected="selected"{/if}>{$p} {$LANG.common.item_plural}</option> 
               {/foreach} 
            </select>
            {$LANG.common.per_page}
         </td>
      </tr>
   </table>
   <form action="?_g=orders&amp;redirect=dashboard" method="post" enctype="multipart/form-data">
   <div>
      <table>
         <thead>
            <tr>
               <td>&nbsp;</td>
               <td>{$THEAD_ORDERS.cart_order_id}</td>
               <td>&nbsp;</td>
               <td>{$THEAD_ORDERS.first_name}</td>
               <td nowrap="nowrap">{$THEAD_ORDERS.status}</td>
               <td>{$THEAD_ORDERS.order_date}</td>
               <td>{$THEAD_ORDERS.total}</td>
               <td width="70">&nbsp;</td>
            </tr>
         </thead>
         <tbody>
            {foreach from=$ORDERS item=order}
            <tr>
               <td style="text-align:center"><input type="checkbox" name="multi-order[]" value="{$order.cart_order_id}" class="all-orders"></td>
               <td><a href="?_g=orders&action=edit&order_id={$order.cart_order_id}&source=dashboard" title="{$LANG.common.edit}">{$order.{$CONFIG.oid_col}|default:$order.cart_order_id}</a>
               {if !empty($order.customer_comments)}<a href="?_g=orders&action=edit&order_id={$order.cart_order_id}&source=dashboard"><i class="fa fa fa-comment" title="{$LANG.email.customer_comments}: {$order.customer_comments|strip_tags}" aria-hidden="true"></i></a>{/if}</td>
               <td style="text-align:center">
                  {append "cust_type" "registered" index="1"}
                  {append "cust_type" "unregistered" index="2"}
                  <i class="fa fa-user {$cust_type[$order.type]}" title="{$LANG.customer[$order.cust_type[$order.type]]}"></i>
               </td>
               <td>
                  <a href="?_g=customers&action=edit&customer_id={$order.customer_id}">{if empty($order.first_name) && empty($order.last_name)}
                  {$order.name}
                  {else}
                  <span class="capitalize">{$order.first_name} {$order.last_name}</span>
                  {/if}</a>
               </td>
               <td class="{$order.status_class}">{$order.status}</td>
               <td>{$order.date}</td>
               <td>{$order.total}</td>
               <td style="text-align:right">
                  {if isset($order.notes)}
                  <a href="?_g=orders&action=edit&order_id={$order.cart_order_id}&source=dashboard#order_notes" title="{foreach $order.notes as $note}{$note.time} {$note.content}{"\r\n"}{/foreach}"><i class="fa fa-sticky-note" title="{$LANG.common.notes}" aria-hidden="true"></i></a>
                  {/if}
                  <a href="{$order.link_print}" class="print" target="_blank" title="{$LANG.common.print}"><i class="fa fa-print" title="{$LANG.common.print}"></i></a>
                  <a href="?_g=orders&action=edit&order_id={$order.cart_order_id}&source=dashboard" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
                  <a href="?_g=orders&delete={$order.cart_order_id}&source=dashboard&token={$SESSION_TOKEN}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
                  
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
                     {foreach $LIST_ORDER_TASKS as $tasks}
                     {if $tasks.opt_group_name}
                     <optgroup label="{$tasks.opt_group_name}">
                     {/if}
                     {foreach $tasks.selections as $task}
                       <option value="{$task.value}" style="{$task.style}">{$task.string}</option>
                     {/foreach}
                     {if $tasks.opt_group_name}
                     </optgroup>
                     {/if}
                     {/foreach}
                  </select>
                  <input type="submit" value="{$LANG.common.go}" name="go" class="tiny">
               </td>
            </tr>
            <tr>
               <td colspan="8">
                  <div class="pagination">
                     {$PAGINATION}&nbsp;
                  </div>
               </td>
            </tr>
         </tfoot>
      </table>
      <div class="pagination">{$ORDER_PAGINATION}</div>
   </div>
   
   </form>
</div>
{/if}
{if isset($REVIEWS)}
<div id="product_reviews" class="tab_content">
   <h3>{$LANG.dashboard.title_reviews_pending}</h3>
   <form action="?_g=products&node=reviews&origin=dashboard" method="post" enctype="multipart/form-data">
      <table width="100%">
         <thead>
            <tr>
               <th>{$LANG.documents.document_title}</th>
               <th>{$LANG.common.product}</th>
               <th>{$LANG.documents.rating}</th>
               <th>{$LANG.common.name}</th>
               <th>{$LANG.common.date}</th>
               <th>{$LANG.common.approved}</th>
               <th>&nbsp;</th>
            </tr>
         </thead>
         <tbody>
            {foreach from=$REVIEWS item=review}
            <tr>
               <td>
                  <a href="{$review.edit}"><strong>{$review.title}</strong></a>
                  <div class="review-excerpt">{$review.review|truncate:120:"&hellip;"}</div>
               </td>
               <td><a href="?_g=products&amp;product_id={$review.product_id}&amp;action=edit">{$review.product.name}</a></td>
               <td style="text-align:center;white-space:nowrap">{section name=i start=1 loop=6 step=1}<i class="fa {if $review.rating >= $smarty.section.i.index}fa-star{else}fa-star-o{/if}" style="color:#e88e22"></i>{/section}</td>
               <td>
                  {$review.name}{if $review.anon=='1'} ({$LANG.catalogue.review_anon}){/if}
                  <div class="review-meta"><a href="mailto:{$review.email}">{$review.email}</a> &middot; {$review.ip_address}</div>
               </td>
               <td nowrap="nowrap">{$review.date}</td>
               <td style="text-align:center"><input type="hidden" class="toggle" name="approve[{$review.id}]" id="approve_{$review.id}" value="{$review.approved}"></td>
               <td style="text-align:center" nowrap="nowrap">
                  <a href="{$review.edit}" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o"></i></a>
                  <a href="{$review.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash"></i></a>
               </td>
            </tr>
            {/foreach}
         </tbody>
      </table>
      <div class="pagination">{$REVIEW_PAGINATION}</div>
      <div>
         <input class="submit" type="submit" value="{$LANG.common.update}">
      </div>
   </form>
   
</div>
{/if}
{if isset($STOCK)}
<div id="stock_warnings" class="tab_content">
   <h3>{$LANG.dashboard.title_stock_warnings}</h3>
   <table width="100%" class="filter">
      <tr>
         <td>
            <select class="select_url textbox">
               {foreach from=$PAGE_BREAKS_STOCK  item=p}
               <option value="{$PAGE_BREAK_URL_STOCK}&items_stock={$p}#stock_warnings"{if $p == $PAGE_BREAK_STOCK} selected="selected"{/if}>{$p} {$LANG.common.item_plural}</option> 
               {/foreach} 
            </select>
            {$LANG.common.per_page}
         </td>
      </tr>
   </table>
   <table width="70%">
      <thead>
         <tr>
            <th>{$THEAD_STOCK.name}</th>
            <th>{$THEAD_STOCK.product_code}</th>
            <th width="85" nowrap="nowrap">{$THEAD_STOCK.stock_level}</th>
            <th width="10">&nbsp;</th>
         </tr>
      </thead>
      <tbody>
         {foreach from=$STOCK item=warn}
         <tr>
            <td><a href="?_g=products&action=edit&product_id={$warn.product_id}{if $warn.M_use_stock==1}#Options{/if}">{$warn.name}</a></td>
            <td>{$warn.product_code}</td>
            <td style="text-align:center"  width="65" nowrap="nowrap">{if $warn.M_use_stock==1}{$warn.M_stock_level}{else}{$warn.I_stock_level}{/if}{if $warn.cached_name}
               - {$warn.cached_name}
               {/if}
            </td>
            <td width="30">
            <a href="?_g=products&action=edit&product_id={$warn.product_id}{if $warn.M_use_stock==1}#Options{/if}" class="edit"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a><a href="?_g=products&delete={$warn.product_id}&dashboard=1" class="delete" title="{sprintf($LANG.notification.confirm_delete_product,$warn.name)}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
            </td>
         </tr>
         {/foreach}
      </tbody>
   </table>
   <div class="pagination">{$STOCK_PAGINATION}</div>
</div>
{/if}
{if isset($RECENT_EXTENSIONS)}
<div id="extensions_recent" class="tab_content">
   <h3>{$LANG.dashboard.title_extensions_recent}</h3>
   <table width="100%">
      <thead>
         <tr>
            <th>{$LANG.common.hide}</th>
            <th>{$LANG.common.name}</th>
            <th>{$LANG.common.details}</th>
            <th>{$LANG.common.developer}</th>
            <th>{$LANG.common.version}</th>
            <th>{$LANG.common.price}</th>
            <th>{$LANG.common.date}</th>
         </tr>
      </thead>
      <tbody>
      {foreach from=$RECENT_EXTENSIONS item=ext}
         <tr>
            <td nowrap="nowrap" style="text-align:center"><a href="?_g=dashboard&amp;ack=extension&amp;name={$ext.name|escape:'url'}#extensions_recent" title="{$LANG.common.hide|default:'Hide'}"><i class="fa fa-times"></i></a></td>
            <td>
               {if $ext.recommended}<i class="fa fa-star" style="color:#e88e22" title="{$LANG.common.recommended|default:'Recommended'}"></i> {/if}
               <strong>{$ext.name}</strong>
               {if $ext.category} <small style="color:#888">&middot; {$ext.category}</small>{/if}
               {if $ext.is_installed} <span class="ext-badge ext-badge-installed" style="margin-left:4px"><i class="fa fa-check"></i> {$LANG.module.ext_installed|default:'Installed'}</span>{/if}
            </td>
            <td>{if $ext.description}{$ext.description|truncate:140:"&hellip;"}{/if}</td>
            <td nowrap="nowrap">{$ext.creator}</td>
            <td nowrap="nowrap">v{$ext.version}</td>
            <td nowrap="nowrap">{if $ext.price}{$ext.price}{else}{$LANG.common.free}{/if}</td>
            <td nowrap="nowrap">{$ext.date}</td>
         </tr>
      {/foreach}
      </tbody>
   </table>
   <p style="text-align:right;margin-top:8px">
      <a href="?_g=dashboard&amp;ack=extensions#extensions_recent" style="margin-right:12px"><i class="fa fa-check"></i> {$LANG.common.mark_seen|default:'Mark all as seen'}</a>
      <a href="?_g=plugins">{$LANG.module.ext_marketplace|default:'View marketplace'} &rarr;</a>
   </p>
</div>
{/if}
{if isset($PLUGIN_TABS)}
	{foreach from=$PLUGIN_TABS item=tab}
		{$tab}
	{/foreach}
{/if}
<div id="advanced" class="tab_content">
   <h3>{$LANG.dashboard.title_store_overview}</h3>
   <div>
      <fieldset>
         <legend>{$LANG.dashboard.title_inventory_data}</legend>
         <dl>
            <dt>{$LANG.dashboard.inv_customers}</dt>
            <dd>{$COUNT.customers}</dd>
            <dt>{$LANG.dashboard.inv_orders}</dt>
            <dd>{$COUNT.orders}</dd>
            <dt>{$LANG.dashboard.inv_products}</dt>
            <dd>{$COUNT.products}</dd>
            <dt>{$LANG.settings.title_category}</dt>
            <dd>{$COUNT.categories}</dd>
         </dl>
      </fieldset>
      <fieldset>
         <legend>{$LANG.dashboard.title_technical_data}</legend>
         <dl>
            <dt>{$LANG.dashboard.tech_version_cc}</dt>
            <dd>{$SYS.cc_version} {$SYS.cc_build}</dd>
            <dt>{$LANG.dashboard.tech_version_php}</dt>
            <dd>{$SYS.php_version}</dd>
            <dt>{$LANG.dashboard.tech_version_mysql}</dt>
            <dd>{$SYS.mysql_version}</dd>
            <dt>{$LANG.dashboard.tech_size_image}</dt>
            <dd><a href="#" class="getFileSize" data-path="images">{$LANG.common.calculate}</a></dd>
            <dt>{$LANG.dashboard.tech_size_download}</dt>
            <dd><a href="#" class="getFileSize" data-path="files">{$LANG.common.calculate}</a></dd>
            <dt>{$LANG.dashboard.tech_upload_max}</dt>
            <dd>{$PHP.upload_max_filesize.local_value}</dd>
            <dt>{$LANG.dashboard.tech_browser}</dt>
            <dd>{$SYS.client}</dd>
            <dt>{$LANG.dashboard.tech_server}</dt>
            <dd>{$SYS.server}</dd>
         </dl>
      </fieldset>
      <div style="display: none" id="val_time_out_text">{$LANG.common.error_time_out}</div>
   </div>
</div>