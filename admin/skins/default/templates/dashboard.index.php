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
<div id="dashboard" class="tab_content">
   <h3>{$LANG.dashboard.title_dashboard}</h3>
   <div class="dashboard_content">
      {if isset($QUICK_STATS)}
      {literal}
      <script type="text/javascript" src="https://www.google.com/jsapi"></script>
      <script type="text/javascript">
         google.load("visualization", "1", {packages:["corechart"]});
         google.setOnLoadCallback(drawChart);
         function drawChart() {
           var data = google.visualization.arrayToDataTable([
             {/literal}{$CHART.data}{literal}
           ]);
         
           var options = {
             title: '{/literal}{$CHART.title}{literal}',
           };
         
           var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
           chart.draw(data, options);
         }
      </script>
      {/literal}	
      <div id="chart_div" style="width:100%; height: 300px;"></div>
      {/if}
      <table width="100%">
         <tr>
            <td valign="top" nowrap="nowrap" width="25%">
               <h4>{$LANG.dashboard.title_tasks}</h4>
               <ul>
                  {foreach from=$CUSTOM_QUICK_TASKS key=k item=task}
                  <li><a href="{$task.url}">{$task.name}</a></li>
                  {/foreach}
                  {foreach from=$DEFAULT_QUICK_TASKS key=url item=name}
                  <li><a href="{$url}">{$name}</a></li>
                  {/foreach}
               </ul>
            </td>
            <td valign="top" nowrap="nowrap" width="25%">
               <h4>{$LANG.dashboard.title_last_orders}</h4>
               {if isset($LAST_ORDERS)}
               {foreach from=$LAST_ORDERS item=order}
               <div><a href="?_g=orders&action=edit&order_id={$order.cart_order_id}">{if $CONFIG.oid_mode=='i'}{$order.{$CONFIG.oid_col}}{else}{$order.cart_order_id}{/if}</a> - {if empty($order.first_name) && empty($order.last_name)}
                  {$order.name}
                  {else}
                  {$order.first_name|capitalize} {$order.last_name|capitalize}
                  {/if}
               </div>
               {/foreach}
               {else}
               <div>{$LANG.form.none}</div>
               {/if}
            </td>
            {if isset($QUICK_STATS)}
            <td valign="top" nowrap="nowrap" width="25%">
               <table width="100%">
                  <tr>
                     <th align="center" width="50%" class="nostripe">{$LANG.dashboard.title_sales_total}</th>
                     <th align="center" width="50%" class="nostripe">{$LANG.dashboard.title_sales_average}</th>
                  </tr>
                  <tr>
                     <td style="text-align:center" width="50%" class="nostripe">{$QUICK_STATS.total_sales}</td>
                     <td style="text-align:center" width="50%" class="nostripe">{$QUICK_STATS.ave_order}</td>
                  </tr>
                  <tr>
                     <th align="center" width="50%" class="nostripe">{$LANG.dashboard.title_month_this}</th>
                     <th align="center" width="50%" class="nostripe">{$LANG.dashboard.title_month_last}</th>
                  </tr>
                  <tr>
                     <td style="text-align:center" width="50%" class="nostripe">{$QUICK_STATS.this_month}</td>
                     <td style="text-align:center" width="50%" class="nostripe">{$QUICK_STATS.last_month}</td>
                  </tr>
               </table>
            </td>
            {/if}
            <td valign="top" nowrap="nowrap" width="25%">
               {if isset($NEWS)}
               <h4><strong title="{$NEWS.description}">{$NEWS.title}</strong></h4>
               <ul>
                  {foreach from=$NEWS.items item=item}
                  <li><a href="{$item.link}" target="_blank">{$item.title}</a></li>
                  {/foreach}
                  <li><a href="{$NEWS.link}" target="_blank">{$LANG.common.more} &raquo;</a></li>
               </ul>
               {/if}
            </td>
         </tr>
      </table>
      <div>
         <form class="note" action="?" method="post">
            <span class="actions"><input type="submit" value="{$LANG.common.save}" name="notes" class="mini_button"></span>
            <p><i class="fa fa-sticky-note" title="{$LANG.common.notes}" aria-hidden="true"></i> {$LANG.dashboard.title_my_notes}</p>
            <textarea name="notes[dashboard_notes]">{$DASH_NOTES}</textarea>
         </form>
      </div>
      {if is_array($RECENT_EXTENSIONS)}
         <h2>{$LANG.dashboard.recent_extensions}</h2>
         <p>{$LANG.dashboard.more_extensions}</p>
         <div class="extension-container">
         {foreach from=$RECENT_EXTENSIONS item=extension name=extension}
            <div class="extension">
               <h4><span>{$extension.price}</span><a href="?_g=marketplace&eurl={$extension.url|escape:'url'}" title="{$extension.name}" target="_blank">{$extension.name|truncate:42:"&hellip;":true}</a></h4>
               <a href="?_g=marketplace&eurl={$extension.url|escape:'url'}" target="_blank"><img src="{$extension.image}" alt="{$extension.name}" width="150"></a>
               </div>
         {/foreach}
         </div>
      {/if}
   </div>
</div>
{if isset($ORDERS)}
<div id="orders" class="tab_content">
   <h3>{$LANG.dashboard.title_orders_unsettled}</h3>
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
               <td style="text-align:center"><input type="checkbox" id="" name="multi-order[]" value="{$order.cart_order_id}" class="all-orders"></td>
               <td><a href="?_g=orders&action=edit&order_id={$order.cart_order_id}&source=dashboard">{if $CONFIG.oid_mode=='i'}{$order.{$CONFIG.oid_col}}{else}{$order.cart_order_id}{/if}</a></td>
               <td style="text-align:center">
                  {append "cust_type" "registered" index="1"}
                  {append "cust_type" "unregistered" index="2"}
                  <i class="fa fa-user {$cust_type[$order.type]}" title="{$LANG.customer[$order.cust_type[$order.type]]}"></i>
               </td>
               <td>
                  <a href="?_g=customers&action=edit&customer_id={$order.customer_id}">{if empty($order.first_name) && empty($order.last_name)}
                  {$order.name}
                  {else}
                  {$order.first_name|capitalize} {$order.last_name|capitalize}
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
                     <option value="">{$LANG.orders.option_nothing}</option>
                     <option value="print">{$LANG.orders.option_print}</option>
                     <option value="delete" style="color: red;">{$LANG.orders.option_delete}</option>
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
      <div>{$ORDER_PAGINATION}</div>
   </div>
   
   </form>
</div>
{/if}
{if isset($REVIEWS)}
<div id="product_reviews" class="tab_content">
   <h3>{$LANG.dashboard.title_reviews_pending}</h3>
   <form action="?_g=products&node=reviews&origin=dashboard" method="post" enctype="multipart/form-data">
      {foreach from=$REVIEWS item=review}
      <div class="note">
         <span class="actions">
         <input type="hidden" class="toggle" name="approve[{$review.id}]" id="approve_{$review.id}" value="{$review.approved}">
         <a href="{$review.edit}" class="edit" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
         <a href="{$review.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
         </span>
         <div><strong>{$review.title}</strong></div>
         <p>{$review.review}</p>
         <div class="details">
            <span style="float: right;">
            {section name=i start=1 loop=6 step=1}
            <input type="radio" class="rating" name="rating_{$review.id}" value="{$smarty.section.i.index}" disabled="disabled" {if $review.rating == $smarty.section.i.index}checked="checked"{/if}>
            {/section}
            </span>
            <a href="?_g=products&product_id={$review.product_id}&action=edit">{$review.product.name}</a> &raquo; {$review.date} :: {$review.name} &lt;<a href="mailto:{$review.email}">{$review.email}</a>&gt;  {$review.ip_address}
         </div>
      </div>
      {/foreach}
      <div>
         <input class="submit" type="submit" value="{$LANG.common.update}">
      </div>
      
   </form>
   <div>{$REVIEW_PAGINATION}</div>
</div>
{/if}
{if isset($EXTENSION_UPDATES)}
<div id="extension_updates" class="tab_content">
   <h3>{$LANG.dashboard.title_extension_updates}</h3>
   <p>{$LANG.module.extensions_available_desc}</p>
   <table>
      <thead>
         <tr>
            <th>{$LANG.common.name}</th>
            <th colspan="2">&nbsp;</th>
         </tr>
      </thead>
      <tbody>
      {foreach from=$EXTENSION_UPDATES item=extension}
         <tr>
            <td>{$extension.name}</td>
            <td><a href="https://www.cubecart.com/extensions/id/{$extension.file_id}" target="_blank">{$LANG.common.update}</a></td>
            <td><a href="?ignore_update={$extension.file_id}">{$LANG.common.ignore}</a></td>
         </tr>
      {/foreach}
      </tbody>
   </table>
</div>
{/if}
{if isset($STOCK)}
<div id="stock_warnings" class="tab_content">
   <h3>{$LANG.dashboard.title_stock_warnings}</h3>
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
            <a href="?_g=products&action=edit&product_id={$warn.product_id}{if $warn.M_use_stock==1}#Options{/if}" class="edit"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a><a href="?_g=products&delete={$warn.product_id}&dashboard=1" class="delete" title="{$LANG.notification.confirm_delete_product|replace:'%s':{$warn.name}}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
            </td>
         </tr>
         {/foreach}
      </tbody>
   </table>
   <div>{$STOCK_PAGINATION}</div>
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
