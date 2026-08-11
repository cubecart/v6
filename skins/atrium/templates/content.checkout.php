{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by core. This is BOTH the basket page and the
 * checkout — $INCLUDE_CHECKOUT decides whether the address/login block appears.
 *
 * ⚠ RENDERED ONCE. Every quan[] and shipping control exists exactly once in
 * #checkout_form. Do not add a second, screen-size-specific copy of the table:
 * duplicate names in one form silently corrupt the posted basket.
 *
 * ⚠ FIELD NAMES ARE CORE CONTRACTS — do not rename any of:
 *   quan[<hash>]      per-line quantity        remove-item=<hash>   URL param
 *   coupon            discount code            update               submit name
 *   shipping          selected method          get-estimate         submit name
 *   estimate[country] estimate[state] estimate[postcode]
 *   use_credit        hidden 0 + checkbox 1 pair
 *   gateway           radio, or hidden when only one is enabled
 *   terms_agree       checkbox                 proceed              submit name
 *
 * ⚠ The hidden use_credit=0 BEFORE the checkbox is deliberate: an unchecked
 * checkbox posts nothing, so without it PHP cannot tell "unticked" from
 * "absent" and credit could never be switched off.
 *
 * ⚠ class="nosubmit" on the gateway radios and country selects opts them OUT of
 * the auto-submit behaviour. Only the shipping select re-submits the form.
 *}
{if isset($ITEMS)}
<div x-data="ccCheckout()">
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data" id="checkout_form" data-cc-validate>

   {if $INCLUDE_CHECKOUT}
   {include file='templates/content.checkout.confirm.php'}
   {/if}

   <h2 class="mt-8 text-xl font-semibold tracking-tight text-ink-900">{$LANG.checkout.your_basket}</h2>

   <div class="mt-4 overflow-x-auto">
      <table class="w-full text-sm">
         <thead class="hidden sm:table-header-group">
            <tr class="border-b border-ink-300 text-xs uppercase tracking-wider text-ink-600">
               <th class="py-2 text-start" colspan="2">{$LANG.common.product}</th>
               <th class="py-2 text-end">{$LANG.catalogue.price_each}</th>
               <th class="py-2 text-center">{$LANG.common.quantity}</th>
               <th class="py-2 text-end">{$LANG.common.price}</th>
               <th class="py-2"><span class="cc-sr-only">{$LANG.common.remove}</span></th>
            </tr>
         </thead>
         <tbody class="divide-y divide-ink-200">
            {foreach from=$ITEMS key=hash item=item}
            <tr class="block sm:table-row" x-data="ccBasketLine('{$item.quantity}')" id="basket_item_{$hash}">
               <td class="py-4 pe-3 align-top sm:w-24">
                  <a href="{$item.link}" title="{$item.name}" class="block w-20 overflow-hidden rounded-cc border border-ink-200 bg-ink-100">
                     <img src="{$item.image}" alt="{$item.name}" loading="lazy" class="aspect-square w-full object-cover">
                  </a>
               </td>
               <td class="py-4 pe-3 align-top">
                  <a href="{$item.link}" class="font-medium text-ink-900 hover:underline">{$item.name}</a>
                  {if $item.options}
                  <ul class="item_options mt-1 space-y-0.5 text-xs text-ink-600">
                     {foreach from=$item.options item=option}
                     <li><span class="font-medium">{$option.option_name}</span>: {if isset($option.value_name) && !empty($option.value_name)}{$option.value_name|truncate:45:"&hellip;":true}{/if}{if !empty($option.price_display)} ({$option.price_display}){/if}</li>
                     {/foreach}
                  </ul>
                  {/if}
               </td>
               <td class="price py-4 text-start align-top tabular sm:text-end">
                  <span class="text-ink-500 sm:hidden">{$LANG.catalogue.price_each}: </span>{$item.price_display}
               </td>
               <td class="py-4 align-top sm:text-center">
                  <label class="cc-sr-only" for="quan_{$hash}">{$LANG.common.quantity}</label>
                  <input name="quan[{$hash}]" id="quan_{$hash}" type="number" min="0" max="999"
                         x-model.number="qty" value="{$item.quantity}" maxlength="3"
                         class="quantity checkout w-20 text-center" {$QUAN_READ_ONLY}>
                  <span x-show="changed" x-cloak class="mt-1 block text-xs text-warn-700">{$LANG.basket.basket_update}</span>
               </td>
               <td class="price py-4 text-start align-top font-medium tabular text-ink-900 sm:text-end">
                  <span class="text-ink-500 sm:hidden">{$LANG.common.price}: </span>{$item.line_price_display}
               </td>
               <td class="py-4 ps-3 align-top text-end">
                  <a href="{$STORE_URL}/index.php?_a=basket&remove-item={$hash}"
                     class="delete inline-flex p-1" title="{$LANG.common.remove}">
                     <span class="cc-sr-only">{$LANG.common.remove}</span>
                     <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" stroke-linecap="round" stroke-linejoin="round"/>
                     </svg>
                  </a>
               </td>
            </tr>
            {/foreach}
         </tbody>
      </table>
   </div>

   {* ---- Totals ------------------------------------------------------- *}
   <div class="mt-6 flex justify-end">
      <dl class="w-full max-w-md space-y-2 text-sm">
         {if $BASKET_WEIGHT}
         <div class="flex justify-between text-ink-500">
            <dt>{$LANG.basket.weight}</dt><dd class="tabular">{$BASKET_WEIGHT}</dd>
         </div>
         {/if}

         <div class="flex justify-between">
            <dt class="text-ink-600">{$LANG.basket.total_sub}</dt>
            <dd class="price tabular font-medium text-ink-900">{$SUBTOTAL}</dd>
         </div>

         {if isset($SHIPPING)}
         <div class="border-t border-ink-200 pt-2">
            {if !isset($free_coupon_shipping)}
            <label for="shipping_method" class="cc-label">{$LANG.basket.shipping_select}</label>
            {* The only control that re-submits the form; totals must be
               recalculated server-side when the method changes. *}
            <select name="shipping" id="shipping_method" class="required" @change="$el.form.submit()">
               <option value="">{$LANG.form.please_select}</option>
               {foreach from=$SHIPPING key=group item=methods}
               {if $HIDE_OPTION_GROUPS ne '1'}<optgroup label="{$group}">{/if}
                  {foreach from=$methods item=method}
                  <option value="{$method.value}" {$method.selected}>{$method.display}</option>
                  {/foreach}
               {if $HIDE_OPTION_GROUPS ne '1'}</optgroup>{/if}
               {/foreach}
            </select>
            <div class="hidden" id="validate_shipping_required">{$LANG.checkout.shipping_required}</div>
            {/if}

            <div class="mt-2 flex justify-between">
               <dt class="text-ink-600">
                  {$LANG.basket.shipping}
                  {if $ESTIMATE_SHIPPING}
                  <button type="button" class="ms-1 text-xs underline" @click="toggleEstimate()">{$LANG.common.refine_estimate}</button>
                  {/if}
               </dt>
               <dd class="price tabular font-medium text-ink-900">{$SHIPPING_VALUE}</dd>
            </div>

            {if $ESTIMATE_SHIPPING}
            <div id="getEstimate" x-show="estimateOpen" x-cloak x-collapse class="mt-3 rounded-cc border border-ink-200 bg-ink-100 p-4">
               <p class="mb-3 text-sm font-medium text-ink-900">{$LANG.basket.specify_shipping}</p>

               <label for="estimate_country" class="cc-label">{$LANG.address.country}</label>
               <select name="estimate[country]" id="estimate_country" class="nosubmit country-list" rel="estimate_state">
                  {foreach from=$COUNTRIES item=country}<option value="{$country.numcode}" data-status="{$country.status}" {$country.selected}>{$country.name}</option>{/foreach}
               </select>

               <div id="estimate_state_wrapper" class="mt-3">
                  <label for="estimate_state" class="cc-label">{$LANG.address.state}</label>
                  {* Input and select both carry name estimate[state]; the JS
                     enables exactly one, so only one ever posts. *}
                  <input type="text" name="estimate[state]" id="estimate_state" value="{$ESTIMATES.state}" placeholder="{$LANG.address.state}">
                  <select name="estimate[state]" id="estimate_state_select" hidden disabled></select>
               </div>

               <div class="mt-3">
                  <label for="estimate_postcode" class="cc-label">{$LANG.address.postcode}</label>
                  <input type="text" name="estimate[postcode]" id="estimate_postcode" value="{$ESTIMATES.postcode}" placeholder="{$LANG.address.postcode}">
               </div>

               <button type="submit" name="get-estimate" class="cc-btn cc-btn-secondary mt-4 w-full">{$LANG.basket.fetch_shipping_rates}</button>
            </div>
            {/if}
         </div>
         {/if}

         {foreach from=$COUPONS item=coupon}
         <div class="flex justify-between">
            <dt class="text-ink-600">
               {$coupon.voucher}
               <a href="{$VAL_SELF}&remove_code={$coupon.remove_code}" title="{$LANG.common.remove}" class="remove-coupon ms-1 text-danger-600">&times;</a>
            </dt>
            <dd class="price tabular text-success-700">{$coupon.value}</dd>
         </div>
         {/foreach}

         {if isset($DISCOUNT) && count($COUPONS) > 1}
         <div class="flex justify-between">
            <dt class="text-ink-600">{$LANG.basket.total_discount}</dt>
            <dd class="price tabular text-success-700">{$DISCOUNT}</dd>
         </div>
         {/if}

         {foreach from=$TAXES item=tax}
         <div class="flex justify-between">
            <dt class="text-ink-600">{$tax.name}{$CUSTOMER_LOCALE.mark}</dt>
            <dd class="price tabular">{if $tax.included}({$tax.value}){else}{$tax.value}{/if}</dd>
         </div>
         {/foreach}

         {if !empty($CREDIT_USED)}
         <div class="flex justify-between">
            <dt class="text-ink-600">{$LANG.common.credit}</dt>
            <dd class="price tabular text-success-700">({$CREDIT_USED})</dd>
         </div>
         {/if}

         <div class="flex justify-between border-t-2 border-ink-800 pt-2 text-base">
            <dt class="font-semibold text-ink-900">{$LANG.basket.total_grand}</dt>
            <dd class="price font-semibold tabular text-ink-900">{$TOTAL}</dd>
         </div>

         {if $CREDIT_AVAILABLE>0}
         <div class="flex items-center justify-between border-t border-ink-200 pt-3">
            <label for="use_credit" class="text-ink-600">
               {$LANG.basket.apply_credit}:
               {if $CREDIT_USED && $CREDIT_USED !== $AVAILABLE_CREDIT}{sprintf($LANG.basket.credit_use, $CREDIT_USED, $AVAILABLE_CREDIT)}{else}{$AVAILABLE_CREDIT}{/if}
            </label>
            <span>
               <input type="hidden" value="0" name="use_credit">
               <input type="checkbox" id="use_credit" name="use_credit" value="1"{if isset($USE_CREDIT) && $USE_CREDIT=='1'} checked="checked"{/if}>
            </span>
         </div>
         {/if}

         <div class="border-t border-ink-200 pt-3">
            <label for="coupon" class="cc-label">{$LANG.basket.coupon_add}</label>
            <div class="flex gap-2">
               <input name="coupon" id="coupon" type="text" maxlength="25" class="min-w-0 flex-1">
               <button type="submit" name="update" id="apply_coupon" value="{$LANG.common.apply}" class="cc-btn cc-btn-secondary shrink-0" @click="clearProceed()">{$LANG.common.apply}</button>
            </div>
         </div>
      </dl>
   </div>

   {* ---- Payment method ------------------------------------------------ *}
   {if $INCLUDE_CHECKOUT && !$DISABLE_GATEWAYS}
      {if $GATEWAYS|@count > 1}
      <div id="payment_method" class="mt-10">
         <h3 class="text-lg font-semibold text-ink-900">{$LANG.gateway.select}</h3>
         <ul id="gateway_error" class="mt-4 space-y-2">
            {foreach from=$GATEWAYS item=gateway}
            <li class="flex items-center gap-2 rounded-cc border border-ink-200 p-3">
               <input name="gateway" type="radio" class="nosubmit" value="{$gateway.folder}" id="{$gateway.folder}" required {$gateway.checked} rel="gateway_error">
               <label for="{$gateway.folder}" class="flex-1 text-sm text-ink-800">{$gateway.description}</label>
               {if !empty($gateway.help)}
               <a href="{$gateway.help}" class="info text-ink-500" title="{$LANG.common.information}" target="_blank" rel="noopener">?</a>
               {/if}
            </li>
            {/foreach}
         </ul>
         <div class="hidden" id="validate_gateway_required">{$LANG.gateway.choose_payment}</div>
      </div>
      {else}
         {* Keep this branch: core still requires a gateway value even when
            there is only one and no choice is presented. *}
         {foreach from=$GATEWAYS item=gateway}
         <input type="hidden" name="gateway" value="{$gateway.folder}">
         {/foreach}
      {/if}
   {/if}

   {if $TERMS_CONDITIONS && isset($ALTERNATE_TERMS) && $ALTERNATE_TERMS=='0'}
   <div class="mt-6 flex items-start gap-2">
      <input type="checkbox" id="reg_terms" name="terms_agree" value="1" rel="error_terms_agree" class="mt-1">
      <label for="reg_terms" class="text-sm text-ink-800">{sprintf($LANG.account.register_terms_agree_link,$TERMS_CONDITIONS)}</label>
   </div>
   {/if}

   {* ---- Actions -------------------------------------------------------- *}
   <div id="checkout_actions" class="mt-8 flex flex-wrap items-center gap-3 border-t border-ink-200 pt-6">
      <a href="{$STORE_URL}/index.php" class="cc-btn cc-btn-secondary">{$LANG.basket.continue_shopping}</a>
      <a href="{$STORE_URL}/index.php?_a=basket&empty-basket=true" class="cc-btn cc-btn-ghost !text-danger-600">{$LANG.basket.basket_empty}</a>
      {* clearProceed() removes any hidden proceed=1 left by an earlier click on
         Proceed; without it an update would jump to the next checkout step. *}
      <button type="submit" name="update" value="{$LANG.basket.basket_update}" class="cc-btn cc-btn-secondary" @click="clearProceed()">{$LANG.basket.basket_update}</button>
      {if $DISABLE_CHECKOUT_BUTTON!==true}
      <button type="submit" name="proceed" id="checkout_proceed" class="g-recaptcha cc-btn cc-btn-primary ms-auto min-w-48" @click="proceed()">{$CHECKOUT_BUTTON}</button>
      {/if}
   </div>

   {if $CUSTOMER_LOCALE.description}
   <p class="mt-4 text-xs text-ink-500">&#185;{$LANG.basket.unconfirmed_locale}</p>
   {/if}
</form>

{* Express checkout buttons (PayPal etc) are self-contained forms; they must
   stay OUTSIDE #checkout_form. *}
{if $DISABLE_CHECKOUT_BUTTON!==true}
   {if $CHECKOUTS}
   <div class="mt-6 flex flex-col items-end gap-3">
      <span class="text-sm text-ink-500">&mdash; {$LANG.common.or} &mdash;</span>
      {foreach from=$CHECKOUTS item=checkout}
      <div>{$checkout}</div>
      {/foreach}
   </div>
   {/if}
{/if}

{include file='templates/element.checkout.related_products.php'}
</div>
{else}
<div class="py-16 text-center">
   <h1 class="text-xl font-semibold text-ink-900">{$LANG.checkout.your_basket}</h1>
   <p class="mt-3 text-ink-600">{$LANG.basket.basket_is_empty}</p>
   <a href="{$STORE_URL}" class="cc-btn cc-btn-primary mt-6">{$LANG.basket.continue_shopping}</a>
</div>
{/if}
