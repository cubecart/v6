{if $IS_USER}
<div class="px-4 sm:px-6 lg:px-8">
  <div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
      <h1 class="text-base font-semibold leading-6 text-gray-900">{$LANG.account.your_orders}</h1>
      <p class="mt-2 text-sm text-gray-700">{$LANG.account.your_orders_explained}</p>
    </div>
  </div>
  <div class="mt-8 flow-root">
    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
      <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        {if $ORDERS}
          <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
              <tr>
              <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">{$LANG.customer.order_count_single}</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{$LANG.basket.total}</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{$LANG.common.status}</th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                  <span class="sr-only">Edit</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
            {foreach from=$ORDERS item=order} 
              <tr>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{$order.time}<br><a href="{$STORE_URL}/index.php?_a=vieworder&cart_order_id={$order.cart_order_id}" title="{$LANG.common.view_details}">{if $CONFIG.oid_mode=='i'}{$order.{$CONFIG.oid_col}}{else}{$order.cart_order_id}{/if}</a></td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{$order.total}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{$order.status.text}</td>
                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-center text-sm font-medium sm:pr-6">
                {if $order.make_payment}
                {/if}
                <a href="{$STORE_URL}/index.php?_a=vieworder&cart_order_id={$order.cart_order_id}" class="text-indigo-600 hover:text-indigo-900 block" title="{$LANG.common.view_details}">{$LANG.common.view}</a>
                {if  !$order.make_payment && !empty($order.basket)}
                <a href="{$STORE_URL}/index.php?_a=vieworder&reorder={$order.cart_order_id}" class="text-indigo-600 hover:text-indigo-900 block" title="{$LANG.common.reorder}">{$LANG.common.reorder}</a>
                {/if}
                {if $order.cancel}
                <a href="{$STORE_URL}/index.php?_a=vieworder&cancel={$order.cart_order_id}" class="text-indigo-600 hover:text-indigo-900 block" title="{$LANG.basket.cancel_order}">{$LANG.common.cancel}</a>
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
        </div>
      </div>
    </div>
  </div>
</div>
{else}
<h2>{$LANG.orders.search}</h2>
<form action="{$VAL_SELF}" id="lookup_order" method="post">
<fieldset>
  {include file='templates/element.form.textbox.php' label="{$LANG.basket.order_number}" value="{$ORDER_NUMBER}" name="cart_order_id"}
  {include file='templates/element.form.textbox.php' label="{$LANG.common.email}" value="{$ORDER_NUMBER}" name="email"}
  {include file='templates/element.form.submit.php' value="{$LANG.common.search}"}
  <div class="hidden" id="validate_field_required">{$LANG.form.field_required}</div>
  <div class="hidden" id="validate_email">{$LANG.common.error_email_invalid}</div>
</fieldset>
</form>
{/if}



