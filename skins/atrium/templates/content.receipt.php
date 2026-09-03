{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by core. Serves both the post-payment page and
 * order history.
 *
 * ⚠ {$SUM.{$CONFIG.oid_col}} is a DYNAMIC KEY LOOKUP — the store chooses which
 * column is the customer-facing order number. Keep the nested braces and the
 * |default fallback; flattening it to $SUM.cart_order_id shows the wrong number
 * on stores using a custom order-id scheme.
 *
 * Delivery-address keys are the billing keys with a _d suffix ($SUM.line1_d).
 * The print view is print.receipt.php — a separate standalone document.
 *}
<div class="mx-auto max-w-3xl">

   <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
         <h1 class="text-xl font-semibold tracking-tight text-ink-900">
            {$LANG.orders.order_number}: {$SUM.{$CONFIG.oid_col}|default:$SUM.cart_order_id}
         </h1>
         <p class="mt-1 text-sm text-ink-600">
            {$LANG.basket.order_date}: {$SUM.order_date_formatted}
         </p>
      </div>
      <span class="order_status order_status_{$SUM.status} rounded-full bg-ink-200 px-3 py-1 text-sm font-medium text-ink-800">
         {$SUM.order_status}
      </span>
   </div>

   {if $SUM.withdrawal_eligible}
   <a href="{$SUM.withdrawal_url}" class="cc-btn cc-btn-secondary mt-4" title="{$LANG.withdrawal.button}">{$LANG.withdrawal.button}</a>
   {/if}

   <h2 class="mt-8 text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.basket.customer_info}</h2>
   <div class="mt-3 grid gap-6 sm:grid-cols-2">
      <section>
         <h3 class="text-sm font-medium text-ink-900">{$LANG.address.billing_address}</h3>
         <address class="mt-1 text-sm not-italic leading-relaxed text-ink-700">
            {$SUM.first_name|capitalize} {$SUM.last_name|capitalize}<br>
            {if $SUM.company_name}{$SUM.company_name}<br>{/if}
            {$SUM.line1|capitalize}<br>
            {if $SUM.line2}{$SUM.line2|capitalize}<br>{/if}
            {$SUM.town|upper}<br>
            {if !empty($SUM.state)}{$SUM.state|upper}, {/if}{$SUM.postcode}
            {if $CONFIG.store_country_name!==$SUM.country}<br>{$SUM.country}{/if}
            {if !empty($SUM.w3w)}<div class="w3w mt-1">///<a href="https://what3words.com/{$SUM.w3w}" class="underline">{$SUM.w3w}</a></div>{/if}
         </address>
      </section>
      <section>
         <h3 class="text-sm font-medium text-ink-900">{$LANG.address.delivery_address}</h3>
         <address class="mt-1 text-sm not-italic leading-relaxed text-ink-700">
            {$SUM.first_name_d} {$SUM.last_name_d}<br>
            {if $SUM.company_name_d}{$SUM.company_name_d}<br>{/if}
            {$SUM.line1_d|capitalize}<br>
            {if $SUM.line2_d}{$SUM.line2_d|capitalize}<br>{/if}
            {$SUM.town_d|upper}<br>
            {if !empty($SUM.state_d)}{$SUM.state_d|upper}, {/if}{$SUM.postcode_d}
            {if $CONFIG.store_country_name!==$SUM.country_d}<br>{$SUM.country_d}{/if}
            {if !empty($SUM.w3w_d)}<div class="w3w mt-1">///<a href="https://what3words.com/{$SUM.w3w_d}" class="underline">{$SUM.w3w_d}</a></div>{/if}
         </address>
      </section>
   </div>

   {if $DELIVERY}
   <h2 class="mt-8 text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.common.delivery}</h2>
   <dl class="mt-3 space-y-1 text-sm">
      {if !empty($DELIVERY.date)}
      <div class="flex gap-3"><dt class="w-40 shrink-0 text-ink-600">{$LANG.orders.shipping_date}</dt><dd class="text-ink-800">{$DELIVERY.date}</dd></div>
      {/if}
      {if !empty($DELIVERY.method)}
      <div class="flex gap-3"><dt class="w-40 shrink-0 text-ink-600">{$LANG.catalogue.delivery_method}</dt><dd class="text-ink-800">{str_replace('_',' ',$DELIVERY.method)}{if !empty($DELIVERY.product)} ({str_replace('_',' ',$DELIVERY.product)}){/if}</dd></div>
      {/if}
      {if !empty($DELIVERY.url)}
      <div class="flex gap-3"><dt class="w-40 shrink-0 text-ink-600">{$LANG.orders.shipping_tracking}</dt><dd><a href="{$DELIVERY.url}" target="_blank" rel="noopener" class="underline">{$DELIVERY.tracking}</a></dd></div>
      {elseif !empty($DELIVERY.tracking)}
      <div class="flex gap-3"><dt class="w-40 shrink-0 text-ink-600">{$LANG.orders.shipping_tracking}</dt><dd class="text-ink-800">{$DELIVERY.tracking}</dd></div>
      {/if}
   </dl>
   {/if}

   <h2 class="mt-8 text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.basket.order_summary}</h2>
   <div class="mt-3 overflow-x-auto">
      <table class="w-full text-sm">
         <thead>
            <tr class="border-b border-ink-300 text-xs uppercase tracking-wider text-ink-600">
               <th class="py-2 text-start">{$LANG.common.product}</th>
               <th class="py-2 text-end">{$LANG.catalogue.price_each}</th>
               <th class="py-2 text-end">{$LANG.common.quantity}</th>
               <th class="py-2 text-end">{$LANG.common.price}</th>
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
               <td class="price py-3 text-end tabular">{$item.price}</td>
               <td class="py-3 text-end tabular">{$item.quantity}</td>
               <td class="price py-3 text-end tabular font-medium text-ink-900">{$item.price_total}</td>
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

   {if !empty($SUM.note_to_customer)}
   <blockquote class="mt-8 border-s-3 border-brand-600 bg-brand-50 p-4 text-sm text-ink-800">{$SUM.note_to_customer}</blockquote>
   {/if}

   {if !empty($SUM.customer_comments)}
   <h2 class="mt-8 text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.common.comments}</h2>
   <p class="mt-2 text-sm italic text-ink-700">&quot;{$SUM.customer_comments}&quot;</p>
   {/if}

   <p class="mt-8">
      <a href="{$STORE_URL}/index.php?_a=receipt&cart_order_id={$SUM.cart_order_id}{if !$IS_USER}&email={$SUM.email}{/if}"
         target="_blank" rel="noopener" class="cc-btn cc-btn-secondary">{$LANG.confirm.print}</a>
   </p>

   {foreach from=$AFFILIATES item=affiliate}{$affiliate}{/foreach}
</div>
