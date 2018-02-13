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
{if $IS_USER}
<h2>{$LANG.account.your_orders}</h2>
{if $ORDERS}
<p>{$LANG.account.your_orders_explained}</p>
<div class="pagination">{$PAGINATION}</div>
<table class="expand">
   <thead>
      <tr>
         <th>{$LANG.customer.order_count_single}</th>
         <th>{$LANG.basket.total}</th>
         <th>{$LANG.common.status}</th>
         <th>&nbsp;</th>
      </tr>
   </thead>
   <tbody>
      {foreach from=$ORDERS item=order}
      <tr>
         <td>{$order.time}<br><a href="{$STORE_URL}/index.php?_a=vieworder&cart_order_id={$order.cart_order_id}" title="{$LANG.common.view_details}">{if $CONFIG.oid_mode=='i'}{$order.{$CONFIG.oid_col}}{else}{$order.cart_order_id}{/if}</a></td>
         <td>{$order.total}</td>
         <td>{$order.status.text}</td>
         <td width="120">
            {if $order.make_payment}
            <a href="{$STORE_URL}/index.php?_a=gateway&cart_order_id={$order.cart_order_id}&retrieve=1" class="button tiny expand thinmarg-bottom">{$LANG.basket.complete_payment}</a>
            {/if}
            <a href="{$STORE_URL}/index.php?_a=vieworder&cart_order_id={$order.cart_order_id}" class="button tiny expand thinmarg-bottom" title="{$LANG.common.view_details}">{$LANG.common.view_details}</a>
            {if  !$order.make_payment && !empty($order.basket)}
            <a href="{$STORE_URL}/index.php?_a=vieworder&reorder={$order.cart_order_id}" class="button tiny expand thinmarg-bottom" title="{$LANG.common.reorder}">{$LANG.common.reorder}</a>
            {/if}
            {if $order.cancel}
            <a href="{$STORE_URL}/index.php?_a=vieworder&cancel={$order.cart_order_id}" class="button tiny alert expand nomarg" title="{$LANG.basket.cancel_order}">{$LANG.basket.cancel_order}</a>
            {/if}

         </td>
      </tr>
      {/foreach}
   </tbody>
</table>
{$PAGINATION}
{else}
<p>{$LANG.account.no_orders_made}</p>
{/if}
{else}
<h2>{$LANG.account.lookup_order}</h2>
<form action="{$VAL_SELF}" id="lookup_order" method="post">
   <div class="row">
   <div class="small-12 large-8 columns">
   	<label for="lookup_order_id">{$LANG.basket.order_number}</label><input type="text" id="lookup_order_id" name="cart_order_id" value="{$ORDER_NUMBER}">
   	</div>
   	</div>
   <div class="row">
   <div class="small-12 large-8 columns"><label for="lookup_email">{$LANG.common.email}</label><input type="text" id="lookup_email" name="email" value=""></div></div>
   <div><input type="submit" value="{$LANG.common.search}" class="button"></div>
   <div class="hide" id="validate_field_required">{$LANG.form.field_required}</div>
   <div class="hide" id="validate_email">{$LANG.common.error_email_invalid}</div>
</form>
{/if}
