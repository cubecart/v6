{*
 * CubeCart v6: Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * OPTIONAL, new in 6.9.0. Cubecart::_pay() draws this page for an order that is
 * paid exactly as it stands: from a payment link the merchant sent, or from
 * Complete Payment on an order that kept no basket (one raised in the admin).
 * A skin without this file still works: core shows that skin's
 * content.gateway.php with the amount due above it.
 *
 * $PAY.state decides what is drawn:
 *   payable   the order, the payment methods and the Pay button
 *   anything else (invalid, paid, cancelled, declined, nothing, unknown)
 *             $PAY.title and $PAY.message, and nothing about the order
 *
 * ⚠ THE FORM MUST POST BACK TO $PAY.action, which carries the link's key. Core
 * loads the order into the basket on that post and only then goes to the
 * gateway page. Posting anywhere else pays nothing.
 *
 * ⚠ NOTHING HERE IS EDITABLE. No quantities, coupons, shipping or addresses:
 * the customer pays the order as the merchant raised it.
 *
 * $SUM, $ITEMS, $TAXES and $DISCOUNT are shaped as content.receipt.php has
 * them. $GATEWAYS is shaped as content.gateway.php has it, and is FALSE when
 * no payment method is open to the order. $PAY.amount_due and
 * $gateway.description are HTML: print them raw.
 *}
<div class="mx-auto max-w-3xl">

{if $PAY.state != 'payable'}

   <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$PAY.title}</h1>
   <p class="mt-2 text-sm text-ink-600">{$PAY.message}</p>
   <div class="mt-6 flex flex-wrap gap-2">
      {if !empty($PAY.order_url)}
      <a href="{$PAY.order_url}" class="cc-btn cc-btn-primary">{$LANG.common.view_details}</a>
      {/if}
      <a href="{$STORE_URL}" class="cc-btn cc-btn-secondary">{$LANG.basket.continue_shopping}</a>
   </div>

{else}

   <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
         <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.orders.pay_title}</h1>
         <p class="mt-1 text-sm text-ink-600">{$PAY.amount_due}</p>
      </div>
      <span class="order_status order_status_{$SUM.status}">{$SUM.order_status}</span>
   </div>

   {if !empty($PAY.attempted)}
   <blockquote class="mt-6 border-s-3 border-brand-600 bg-brand-50 p-4 text-sm text-ink-800">{$PAY.attempted}</blockquote>
   {/if}

   <h2 class="mt-8 text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.basket.order_summary}</h2>
   <div class="mt-3 overflow-x-auto">
      <table class="w-full text-sm">
         <thead>
            <tr class="border-b border-ink-300 text-xs uppercase tracking-wider text-ink-600">
               <th class="py-2 text-start">{$LANG.common.product}</th>
               <th class="py-2 ps-4 text-end">{$LANG.catalogue.price_each}</th>
               <th class="py-2 ps-4 text-end">{$LANG.common.quantity}</th>
               <th class="py-2 ps-4 text-end">{$LANG.common.price}</th>
            </tr>
         </thead>
         <tbody class="divide-y divide-ink-200">
            {foreach from=$ITEMS item=item}
            <tr>
               <td class="py-3 pe-3 text-ink-800">
                  {$item.name}{if !empty($item.product_code)} <span class="text-ink-500">({$item.product_code})</span>{/if}
                  {if !empty($item.options)}
                  <div class="mt-1 text-xs text-ink-600">{foreach from=$item.options item=option}{$option}<br>{/foreach}</div>
                  {/if}
               </td>
               <td class="price py-3 ps-4 text-end tabular">{$item.price}</td>
               <td class="py-3 ps-4 text-end tabular">{$item.quantity}</td>
               <td class="price py-3 ps-4 text-end tabular font-medium text-ink-900">{$item.price_total}</td>
            </tr>
            {/foreach}
         </tbody>
      </table>
   </div>

   <div class="mt-6 flex justify-end">
      <dl class="w-full max-w-sm space-y-2 text-sm">
         <div class="flex justify-between"><dt class="text-ink-600">{$LANG.basket.total_sub}</dt><dd class="price tabular">{$SUM.subtotal}</dd></div>
         <div class="flex justify-between">
            <dt class="text-ink-600">{if !empty($SUM.ship_method)}{str_replace('_',' ',$SUM.ship_method)}{if !empty($SUM.ship_product)} ({$SUM.ship_product}){/if}{else}{$LANG.basket.shipping}{/if}</dt>
            <dd class="price tabular">{$SUM.shipping}</dd>
         </div>
         {foreach from=$TAXES item=tax}
         <div class="flex justify-between"><dt class="text-ink-600">{if $tax.included}{$LANG.common.includes} {/if}{$tax.name}</dt><dd class="price tabular">{$tax.value}</dd></div>
         {/foreach}
         {if $DISCOUNT}
         <div class="flex justify-between"><dt class="text-ink-600">{$LANG.basket.total_discount}</dt><dd class="price tabular text-success-700">{$SUM.discount}</dd></div>
         {/if}
         {if $SUM.show_credit}
         <div class="flex justify-between"><dt class="text-ink-600">{$LANG.common.credit}</dt><dd class="price tabular text-success-700">({$SUM.credit_used})</dd></div>
         {/if}
         <div class="flex justify-between border-t-2 border-ink-800 pt-2 text-base">
            <dt class="font-semibold text-ink-900">{$LANG.basket.total_grand}</dt>
            <dd class="price font-semibold tabular text-ink-900">{$SUM.total}</dd>
         </div>
      </dl>
   </div>

   <h2 class="mt-8 text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.basket.customer_info}</h2>
   <div class="mt-3 grid gap-6 sm:grid-cols-2">
      <section>
         <h3 class="text-sm font-medium text-ink-900">{$LANG.address.billing_address}</h3>
         <address class="mt-1 text-sm not-italic leading-relaxed text-ink-700">
            {$SUM.first_name|capitalize:true} {$SUM.last_name|capitalize:true}<br>
            {if $SUM.company_name}{$SUM.company_name}<br>{/if}
            {$SUM.line1|capitalize:true}<br>
            {if $SUM.line2}{$SUM.line2|capitalize:true}<br>{/if}
            {$SUM.town|upper}<br>
            {if !empty($SUM.state)}{$SUM.state|upper}, {/if}{$SUM.postcode}
            {if $CONFIG.store_country_name!==$SUM.country}<br>{$SUM.country}{/if}
         </address>
      </section>
      {if !empty($SUM.line1_d)}
      <section>
         <h3 class="text-sm font-medium text-ink-900">{$LANG.address.delivery_address}</h3>
         <address class="mt-1 text-sm not-italic leading-relaxed text-ink-700">
            {$SUM.first_name_d|capitalize:true} {$SUM.last_name_d|capitalize:true}<br>
            {if $SUM.company_name_d}{$SUM.company_name_d}<br>{/if}
            {$SUM.line1_d|capitalize:true}<br>
            {if $SUM.line2_d}{$SUM.line2_d|capitalize:true}<br>{/if}
            {$SUM.town_d|upper}<br>
            {if !empty($SUM.state_d)}{$SUM.state_d|upper}, {/if}{$SUM.postcode_d}
            {if $CONFIG.store_country_name!==$SUM.country_d}<br>{$SUM.country_d}{/if}
         </address>
      </section>
      {/if}
   </div>

   {if !empty($SUM.note_to_customer)}
   <blockquote class="mt-8 border-s-3 border-brand-600 bg-brand-50 p-4 text-sm text-ink-800">{$SUM.note_to_customer}</blockquote>
   {/if}

   <form action="{$PAY.action}" method="post" id="pay-order" class="mt-8 border-t border-ink-200 pt-6">
      {if $GATEWAYS}
         {if count($GATEWAYS) > 1}
         <h2 class="text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.gateway.select}</h2>
         <ul class="mt-4 space-y-2">
            {foreach from=$GATEWAYS item=gateway}
            <li class="flex items-center gap-2 rounded-cc border border-ink-200 p-3">
               <input name="gateway" type="radio" value="{$gateway.folder}" id="pay_{$gateway.folder}" required {$gateway.checked}>
               <label for="pay_{$gateway.folder}" class="flex-1 text-sm text-ink-800">{$gateway.description}</label>
               {if !empty($gateway.help)}
               <a href="{$gateway.help}" class="info text-ink-500" title="{$LANG.common.information}" target="_blank" rel="noopener">?</a>
               {/if}
            </li>
            {/foreach}
         </ul>
         {else}
            {* One payment method: no choice to offer, but core still wants its name. *}
            {foreach from=$GATEWAYS item=gateway}
            <input type="hidden" name="gateway" value="{$gateway.folder}">
            {/foreach}
         {/if}
      <button type="submit" class="cc-btn cc-btn-primary mt-6 w-full">{$LANG.checkout.pay_now}</button>
      {else}
      <p class="text-ink-600">{$LANG.gateway.none_defined}</p>
      {/if}
   </form>

{/if}
</div>
