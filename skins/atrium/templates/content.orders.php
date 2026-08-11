{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Cubecart::_orders() fetches it through a $template VARIABLE, not
 * a literal, swapping to content.receipt.php for a single order. A literal-
 * string grep will NOT find this file; it is still required.
 *
 * TWO MODES: logged in -> paginated order history; logged out -> guest lookup.
 *
 * ⚠ Use {if !empty($IS_USER)}, not {if $IS_USER}. The User constructor assigns
 * IS_USER only in its else branch, so on any request carrying
 * username+password it is UNDEFINED rather than false.
 *
 * $PAGINATION is PRE-RENDERED HTML and is FALSE (not '') when there is one
 * page — guard on it, print it raw. Page var is `page`, 15 per page.
 * Guest lookup fields: cart_order_id, email. #lookup_order is a key in the
 * validator's `forms` map (3.cubecart.validate.js).
 * $ORDER_NUMBER comes from $_GET only, so it renders empty after a failed POST.
 *}
{if !empty($IS_USER)}
<div class="mx-auto max-w-3xl">
   <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.account.your_orders}</h1>
   <p class="mt-2 text-sm text-ink-600">{$LANG.account.your_orders_explained}</p>

   {if $ORDERS}
   {if $PAGINATION}<div class="mt-4">{$PAGINATION}</div>{/if}

   <ul role="list" class="mt-6 space-y-4">
      {foreach from=$ORDERS item=order}
      <li class="cc-card p-5">
         <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
               <p class="text-sm font-semibold text-ink-900">
                  {$LANG.basket.order_number}:
                  {if $CONFIG.oid_mode=='i'}{$order.{$CONFIG.oid_col}|default:$order.cart_order_id}{else}{$order.cart_order_id}{/if}
               </p>
               <p class="mt-1 text-xs text-ink-500">{$order.time}</p>
            </div>
            <div class="text-end">
               <p class="price font-semibold tabular text-ink-900">{$order.total}</p>
               <p class="mt-1 text-xs text-ink-600">{$order.status.text}</p>
            </div>
         </div>

         <div class="mt-4 flex flex-wrap gap-2">
            <a href="{$VAL_SELF}&cart_order_id={$order.cart_order_id}" class="cc-btn cc-btn-secondary">{$LANG.common.view_details}</a>
            {if $order.make_payment}
            <a href="{$STORE_URL}/index.php?_a=basket&resume={$order.cart_order_id}" class="cc-btn cc-btn-primary">{$LANG.basket.complete_payment}</a>
            {/if}
            {if !$order.make_payment && !empty($order.basket)}
            <a href="{$STORE_URL}/index.php?_a=basket&reorder={$order.cart_order_id}" class="cc-btn cc-btn-secondary">{$LANG.common.reorder}</a>
            {/if}
            {if $order.cancel}
            <a href="{$VAL_SELF}&cancel={$order.cart_order_id}" class="cc-btn cc-btn-ghost !text-danger-600">{$LANG.basket.cancel_order}</a>
            {/if}
            {if $order.withdrawal_eligible}
            <a href="{$order.withdrawal_url}" class="cc-btn cc-btn-ghost">{$LANG.withdrawal.button}</a>
            {/if}
         </div>
      </li>
      {/foreach}
   </ul>

   {if $PAGINATION}<div class="mt-6">{$PAGINATION}</div>{/if}
   {else}
   <p class="mt-8 text-center text-ink-600">{$LANG.account.no_orders_made}</p>
   <div class="mt-4 text-center"><a href="{$STORE_URL}" class="cc-btn cc-btn-primary">{$LANG.basket.continue_shopping}</a></div>
   {/if}
</div>

{else}
<div class="mx-auto max-w-md">
   <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.account.lookup_order}</h1>

   <form action="{$VAL_SELF}" method="post" id="lookup_order" class="cc-card mt-6 space-y-4 p-6">
      <div>
         <label for="cart_order_id" class="cc-label">{$LANG.basket.order_number}</label>
         <input type="text" name="cart_order_id" id="cart_order_id" value="{$ORDER_NUMBER}" required>
      </div>
      <div>
         <label for="lookup_email" class="cc-label">{$LANG.common.email}</label>
         <input type="email" name="email" id="lookup_email" required autocomplete="email">
      </div>
      <button type="submit" class="cc-btn cc-btn-primary w-full">{$LANG.common.search}</button>
   </form>

   <p class="mt-6 text-center text-sm">
      <a href="{$STORE_URL}/login{$CONFIG.seo_ext}" class="underline">{$LANG.account.login}</a>
   </p>
</div>
{/if}

<div class="hidden" id="validate_field_required">{$LANG.form.field_required}</div>
<div class="hidden" id="validate_email">{$LANG.common.error_email_invalid}</div>
