{if isset($DISPLAY_ALL_TRANSACTIONS)}
<div id="logs" class="tab_content">
  <h3>{$LANG.orders.title_transaction_logs}</h3>
  <form action="{$VAL_SELF}" method="post">
	<div>
	  <input type="text" name="search" class="textbox"> <input type="submit" value="{$LANG.common.search}" class="mini_button">
	</div>
	<input type="hidden" name="token" value="{$SESSION_TOKEN}">
  </form>
  <table class="list">
	<thead>
	  <tr>
		<td width="120">{$THEAD.cart_order_id}</td>
		<td width="70">{$THEAD.amount}</td>
		<td width="120">{$THEAD.gateway}</td>
		<td>{$THEAD.date}</td>
	  </tr>
	</thead>
	<tbody>
	{if isset($ALL_TRANSACTIONS)}
	  {foreach from=$ALL_TRANSACTIONS item=transaction}
	  <tr>
		<td><a href="{$transaction.link}" title="{$LANG.orders.title_transaction_view}">{$transaction.order_id}</a></td>
		<td>{$transaction.amount}</td>
		<td>{$transaction.gateway}</td>
		<td>{$transaction.time}</td>
	  </tr>
	  {/foreach}
	{else}
	  <tr>
		<td colspan="4" align="center"><strong>{$LANG.form.none}</strong></td>
	  </tr>
	{/if}
	</tbody>
  </table>
  <div class="pagination">
	<span>{$TOTAL_RESULTS}</span>
	{$PAGINATION}
  </div>
</div>
{/if}

{if $DISPLAY_ORDER_TRANSACTIONS}
<div id="log" class="tab_content">
<h3>{$TRANSACTION_LOGS_TITLE}</h3>
  <table class="list">
	<thead>
	  <tr>
		<td nowrap="nowrap">{$LANG.orders.transaction_id}</td>
		<td nowrap="nowrap">{$LANG.common.status}</td>
		<td nowrap="nowrap">{$LANG.common.amount}</td>
		<td nowrap="nowrap">{$LANG.orders.gateway_name}</td>
		<td nowrap="nowrap">{$LANG.common.date_time}</td>
		<td>{$LANG.common.notes}</td>
	  </tr>
	</thead>
	<tbody>
	  {if $ORDER_TRANSACTIONS}{foreach from=$ORDER_TRANSACTIONS item=transaction}
	  <tr>
		<td><!--<a href="{$transaction.link}">{$transaction.order_id}</a><br>-->{$transaction.trans_id}</td>
		<td align="center">{$transaction.status}</td>
		<td align="center">{$transaction.amount}</td>
		<td align="center">{$transaction.gateway}</td>
		<td align="center">{$transaction.time}</td>
		<td>{$transaction.notes}</td>
	  </tr>
	  {/foreach}
	  {/if}
	</tbody>
  </table>
</div>
{/if}