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
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data" class="ignore-dirty">
  <div id="general" class="tab_content">
	<h3>{$LANG.orders.merge_title}</h3>

	{if $MERGE_STEP == 'review'}
	{* Review: the order being kept, the orders joining it, and what that changes *}
	<fieldset><legend>{$LANG.orders.merge_keep_order}</legend>
	  <div><label>{$LANG.orders.order_number}</label><span><a href="{$MERGE_KEEP.edit}">{$MERGE_KEEP.number}</a> ({$MERGE_KEEP.date})</span></div>
	  <div><label>{$LANG.common.name}</label><span>{$MERGE_KEEP.name}</span></div>
	  <div><label>{$LANG.orders.tab_delivery}</label><span>{$MERGE_KEEP.delivery}</span></div>
	</fieldset>

	<fieldset><legend>{$LANG.orders.merge_from_orders}</legend>
	  <table width="100%" class="merge-orders">
		<thead>
		  <tr>
			<td>{$LANG.orders.order_number}</td>
			<td>{$LANG.basket.order_date}</td>
			<td style="text-align:center">{$LANG.orders.merge_products}</td>
			<td style="text-align:right">{$LANG.basket.shipping}</td>
			<td style="text-align:right">{$LANG.basket.total}</td>
		  </tr>
		</thead>
		<tbody>
		  {foreach from=$MERGE_FROM item=order}
		  <tr>
			<td><a href="{$order.edit}">{$order.number}</a></td>
			<td>{$order.date}</td>
			<td style="text-align:center">{$order.items}</td>
			<td style="text-align:right">{$order.shipping}</td>
			<td style="text-align:right">{$order.total}</td>
		  </tr>
		  {/foreach}
		</tbody>
	  </table>
	</fieldset>

	{if !empty($MERGE_REFUSED)}
	<fieldset><legend>{$LANG.orders.merge_refused_title}</legend>
	  {foreach from=$MERGE_REFUSED item=order}
	  <div><label>{if $order.edit}<a href="{$order.edit}">{$order.number}</a>{else}{$order.number}{/if}</label><span>{$order.reason}</span></div>
	  {/foreach}
	</fieldset>
	{/if}

	<fieldset><legend>{$LANG.orders.merge_what}</legend>
	  {foreach from=$MERGE_MOVING item=row}
	  <div><label>{$row.label}</label><span style="display:block;overflow:hidden;text-indent:0;padding-left:5px">{$row.value}</span></div>
	  {/foreach}
	</fieldset>

	<fieldset><legend>{$MERGE_TOTALS_TITLE}</legend>
	  <table width="100%" class="merge-totals">
		<thead>
		  <tr>
			<td>&nbsp;</td>
			<td style="text-align:right">{$LANG.orders.merge_before}</td>
			<td style="text-align:right">{$LANG.orders.merge_after}</td>
		  </tr>
		</thead>
		<tbody>
		  {foreach from=$MERGE_TOTALS item=row}
		  <tr>
			<td>{if $row.strong}<strong>{$row.label}</strong>{else}{$row.label}{/if}</td>
			<td style="text-align:right">{$row.before}</td>
			<td style="text-align:right">{if $row.strong}<strong>{$row.after}</strong>{else}{$row.after}{/if}</td>
		  </tr>
		  {/foreach}
		</tbody>
	  </table>
	</fieldset>

	<fieldset><legend>{$LANG.orders.merge_customer}</legend>
	  <div><label for="merge_note">{$LANG.orders.merge_customer_note}</label><span><textarea name="merge[note]" id="merge_note" class="textbox" rows="2" style="width:60%">{$MERGE_NOTE}</textarea></span></div>
	  <div><label>&nbsp;</label><span style="display:block;overflow:hidden;text-indent:0;padding-left:5px">{$LANG.orders.merge_customer_note_help}</span></div>
	  <div><label for="merge_email">{$LANG.orders.merge_email}</label><span><input type="checkbox" name="merge[email]" id="merge_email" value="1"> {$MERGE_EMAIL}</span></div>
	</fieldset>

	<p class="important"><i class="fa fa-exclamation-triangle"></i> <strong>{$MERGE_WARNING}</strong></p>
	{foreach from=$MERGE_ORDERS item=order_id}
	<input type="hidden" name="merge[orders][]" value="{$order_id}">
	{/foreach}
	<input type="hidden" name="merge[keep]" value="{$MERGE_KEEP.id}">
	<input type="hidden" name="merge[confirm]" value="1">

	{else}
	{* Choose: this customer's orders that can become one *}
	<p>{$LANG.orders.merge_intro}</p>

	{if !empty($MERGE_PROBLEM)}
	<p class="important"><i class="fa fa-exclamation-triangle"></i> <strong>{$MERGE_PROBLEM}</strong></p>
	{/if}

	{if $MERGE_CAN_CHOOSE}
	<fieldset><legend>{$LANG.orders.merge_choose}</legend>
	  <table width="100%" class="merge-orders">
		<thead>
		  <tr>
			<td width="60" style="text-align:center">{$LANG.orders.merge_tick}</td>
			<td width="60" style="text-align:center">{$LANG.orders.merge_keep}</td>
			<td>{$LANG.orders.order_number}</td>
			<td>{$LANG.basket.order_date}</td>
			<td>{$LANG.orders.tab_delivery}</td>
			<td style="text-align:center">{$LANG.orders.merge_products}</td>
			<td style="text-align:right">{$LANG.basket.shipping}</td>
			<td style="text-align:right">{$LANG.basket.total}</td>
		  </tr>
		</thead>
		<tbody>
		  {foreach from=$MERGE_ROWS item=order}
		  <tr>
			<td style="text-align:center"><input type="checkbox" name="merge[orders][]" id="merge_order_{$order.id}" value="{$order.id}"{if $order.ticked} checked="checked"{/if}></td>
			<td style="text-align:center"><input type="radio" name="merge[keep]" id="merge_keep_{$order.id}" value="{$order.id}"{if $order.keep} checked="checked"{/if}></td>
			<td><label for="merge_order_{$order.id}"><a href="{$order.edit}">{$order.number}</a></label></td>
			<td>{$order.date}</td>
			<td>{$order.delivery}</td>
			<td style="text-align:center">{$order.items}</td>
			<td style="text-align:right">{$order.shipping}</td>
			<td style="text-align:right">{$order.total}</td>
		  </tr>
		  {/foreach}
		</tbody>
	  </table>
	</fieldset>
	<input type="hidden" name="merge[anchor][]" value="{$MERGE_ANCHOR.id}">
	{/if}

	{if !empty($MERGE_REFUSED)}
	<fieldset><legend>{$LANG.orders.merge_refused_title}</legend>
	  {foreach from=$MERGE_REFUSED item=order}
	  <div><label>{if $order.edit}<a href="{$order.edit}">{$order.number}</a>{else}{$order.number}{/if}</label><span>{$order.reason}</span></div>
	  {/foreach}
	</fieldset>
	{/if}
	{/if}
  </div>

  {include file='templates/element.hook_form_content.php'}

  <div class="form_control">
	{if $MERGE_STEP == 'review'}
	<input type="submit" name="save" value="{$LANG.orders.merge_title}">
	<a href="{$MERGE_CHOOSE}" class="button">{$LANG.orders.merge_change}</a>
	<a href="{$MERGE_BACK}" class="button">{$LANG.common.cancel}</a>
	{else}
	{if $MERGE_CAN_CHOOSE}<input type="submit" name="save" value="{$LANG.orders.merge_review}">{/if}
	<a href="{$MERGE_BACK}" class="button">{$LANG.common.cancel}</a>
	{/if}
  </div>
</form>
