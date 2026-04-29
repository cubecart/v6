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
<form action="{$VAL_SELF}" class="ignore-dirty" method="post">
  <div id="results" class="tab_content">
	<h3>{$REPORT_TITLE}</h3>
	<div class="report-toggle">
	  <label><input type="checkbox" id="toggle_report_columns"> {$LANG.reports.show_all_columns}</label>
	</div>
	<div class="report-scroll">
	<table class="report-table hide-extra">
	  <thead>
		<tr>
		  <td>{$LANG.orders.order_number}</td>
		  <td class="text-right">{$LANG.basket.total_sub}</td>
		  <td class="text-right">{$LANG.common.discount}</td>
		  <td class="text-right">{$LANG.basket.shipping}</td>
		  <td class="text-right">{$LANG.common.tax}</td>
		  <td class="text-right">{$LANG.common.total}</td>
		  <td>{$LANG.common.name}</td>
		  <td class="col-extra">{$LANG.address.company_name}</td>
		  <td class="col-extra">{$LANG.address.line1}</td>
		  <td class="col-extra">{$LANG.address.line2}</td>
		  <td class="col-extra">{$LANG.address.town}</td>
		  <td>{$LANG.address.state}</td>
		  <td>{$LANG.address.country}</td>
		  <td class="col-extra">{$LANG.address.postcode}</td>
		  <td class="col-extra">{$LANG.address.phone}</td>
		  <td class="col-extra">{$LANG.address.mobile}</td>
		  <td>{$LANG.common.email}</td>
		  <td class="col-extra">{$LANG.orders.gateway_name}</td>
		  <td class="col-extra">{$LANG.common.status}</td>
		  <td>{$LANG.common.date_time}</td>
		</tr>
	  </thead>
	  <tbody>
		{foreach from=$REPORT_DATE item=data}
		<tr>{$data.value}
		  <td nowrap="nowrap"><a href="?_g=orders&action=edit&order_id={$data.cart_order_id}" title="{$LANG.common.edit}">{$data.{$CONFIG.oid_col}|default:$data.order_id}</a></td>
		  <td class="text-right">{$data.subtotal}</td>
		  <td class="text-right">{$data.discount}</td>
		  <td class="text-right">{$data.shipping}</td>
		  <td class="text-right">{$data.total_tax}</td>
		  <td class="text-right">{$data.total}</td>
		  <td><a href="?_g=customers&action=edit&customer_id={$data.customer_id}" class="capitalize">{$data.first_name} {$data.last_name}</a></td>
		  <td class="col-extra">{$data.company_name}</td>
		  <td class="col-extra">{$data.line1}</td>
		  <td class="col-extra">{$data.line2}</td>
		  <td class="col-extra">{$data.town}</td>
		  <td>{$data.state}</td>
		  <td>{$data.country}</td>
		  <td class="col-extra">{$data.postcode}</td>
		  <td class="col-extra" nowrap="nowrap">{$data.phone}</td>
		  <td class="col-extra" nowrap="nowrap">{$data.mobile}</td>
		  <td>{$data.email}</td>
		  <td class="col-extra">{$data.gateway}</td>
		  <td class="col-extra">{$data.status}</td>
		  <td class="text-center" nowrap="nowrap">{$data.date}</td>
		</tr>
		{foreachelse}
		<tr><td colspan="20" class="text-center"><strong>{$LANG.common.error_no_results}</strong></td></tr>
		{/foreach}
	  </tbody>
	  {if $REPORT_DATE}
	  <tfoot>
		<tr class="foot">
		  <td class="text-right">{$TALLY.orders} {if $TALLY.orders==1}{$LANG.customer.order_count_single}{else}{$LANG.customer.order_count}{/if}</td>
		  <td class="text-right">{$TALLY.subtotal}</td>
		  <td class="text-right">{$TALLY.discount}</td>
		  <td class="text-right">{$TALLY.shipping}</td>
		  <td class="text-right">{$TALLY.total_tax}</td>
		  <td class="text-right">{$TALLY.total}</td>
		  <td class="text-center" colspan="14">&nbsp;</td>
		</tr>
	  </tfoot>
	  {/if}
	</table>
	</div>
	<div class="pagination">{$PAGINATION}</div>
  	<div>
	{if $DOWNLOAD}
		<input type="submit" name="download" class="submit" value="{$LANG.common.export} (CSV)">
		<input type="submit" name="download_xls" class="submit" value="{$LANG.common.export} (XLSX)">
	{/if}
	{foreach from=$EXPORT item=module}
		<input type="submit" name="external_report[{$module.folder}]" class="submit" value="{$LANG.customer.export_to} {$module.description}">
	{/foreach}
	</div>
  </div>

  <div id="search" class="tab_content">
	<h3>{$LANG.search.title_filter}</h3>
	<fieldset>
		<div>
		  <label for="date_range_from">{$LANG.search.date_range}</label>
		  <span>
			<input type="text" id="date_range_from" name="report[date][from]" class="textbox number date" value="{$POST.date.from|default:''}"> -
			<input type="text" id="date_range_to" name="report[date][to]" class="textbox number date" value="{$POST.date.to|default:''}">
		  </span>
		</div>
		<div>
			<label for="report_status">{$LANG.orders.title_order_status}</label>
			<span>
				<select id="report_status" multiple="multiple" name="report[status][]" class="textbox">
					{foreach from=$STATUS item=status}
					<option value="{$status.value}" {$status.selected}>{$status.name}</option>
					{/foreach}
				</select>
			</span>
		</div>
	</fieldset>
	<div><input type="submit" class="button" value="{$LANG.common.display}"></div>
  </div>
  {if isset($PLUGIN_TABS)}
      {foreach from=$PLUGIN_TABS item=tab}
		{$tab}
      {/foreach}
   {/if}   
   {include file='templates/element.hook_form_content.php'}
</form>
<script>
(function(){
	function init(){
		var key = 'cc_report_show_all_cols';
		var cb = document.getElementById('toggle_report_columns');
		var tbl = document.querySelector('.report-table');
		if (!cb || !tbl) return;
		if (localStorage.getItem(key) === '1') {
			cb.checked = true;
			tbl.classList.remove('hide-extra');
		}
		cb.addEventListener('change', function(){
			if (cb.checked) {
				tbl.classList.remove('hide-extra');
				localStorage.setItem(key, '1');
			} else {
				tbl.classList.add('hide-extra');
				localStorage.removeItem(key);
			}
		});
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
</script>