{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by Cubecart::_withdraw(). Added in CubeCart
 * 6.7.4 — one reason Atrium's <minVersion> is 6.7.6.
 *
 * EU right-of-withdrawal request form (Directive (EU) 2023/2673 Art. 11a): the
 * wording is legally prescribed, so do not drop or relabel fields.
 *
 * ⚠ FLAT SCALAR NAMES ONLY: name, email, address, cart_order_id, reason,
 * reported_delivery, statement, website, withdraw_submit.
 *
 * ⚠ `website` is a HONEYPOT. It must be present, empty, and invisible to
 * humans but NOT hidden with type="hidden" (bots fill hidden inputs happily).
 * Keep it off-screen, unlabelled, autocomplete off, tabindex -1.
 *
 * ⚠ No `token` field; close with a literal </form>.
 * $WITHDRAW_ERRORS is a plain array of message strings for a failed submit;
 * $WITHDRAW repopulates the form; $WITHDRAW_USER_ORDERS offers a picker to
 * logged-in customers.
 *}
<div class="mx-auto max-w-2xl">
   <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.withdrawal.page_title}</h1>
   <div class="prose-cc mt-3 text-sm">{$LANG.withdrawal.page_intro}</div>

   {if $WITHDRAW_ERRORS}
   <div class="cc-alert cc-alert-error mt-6" role="alert">
      <ul class="min-w-0 flex-1 space-y-1">
         {foreach from=$WITHDRAW_ERRORS item=err}<li>{$err}</li>{/foreach}
      </ul>
   </div>
   {/if}

   <form action="{$VAL_SELF}" method="post" id="withdraw_form" data-cc-validate class="mt-6">
      <div class="cc-card space-y-4 p-6">

         <div class="grid gap-4 sm:grid-cols-2">
            <div>
               <label for="w_name" class="cc-label">{$LANG.withdrawal.field_name}</label>
               <input type="text" name="name" id="w_name" value="{$WITHDRAW.name}" autocomplete="name" required>
            </div>
            <div>
               <label for="w_email" class="cc-label">{$LANG.withdrawal.field_email}</label>
               <input type="email" name="email" id="w_email" value="{$WITHDRAW.email}" autocomplete="email" required>
            </div>
         </div>

         <div>
            <label for="w_address" class="cc-label">{$LANG.withdrawal.field_address}</label>
            <textarea name="address" id="w_address" rows="3" autocomplete="street-address">{$WITHDRAW.address}</textarea>
         </div>

         <div>
            <label for="w_order" class="cc-label">{$LANG.withdrawal.field_order}</label>
            {if $WITHDRAW_USER_ORDERS}
            <select name="cart_order_id" id="w_order" required>
               <option value="">{$LANG.form.none}</option>
               {foreach from=$WITHDRAW_USER_ORDERS item=o}
               <option value="{$o.cart_order_id}"{if $o.cart_order_id == $WITHDRAW.cart_order_id} selected{/if}>{$o.cart_order_id} &mdash; {$o.order_date}</option>
               {/foreach}
            </select>
            {else}
            <input type="text" name="cart_order_id" id="w_order" value="{$WITHDRAW.cart_order_id}" required>
            {/if}
            <p class="mt-1 text-xs text-ink-500">{$LANG.withdrawal.field_order_help}</p>
         </div>

         <div>
            <label for="w_delivery" class="cc-label">{$LANG.withdrawal.field_reported_delivery}</label>
            <input type="date" name="reported_delivery" id="w_delivery" value="{$WITHDRAW.reported_delivery}">
            <p class="mt-1 text-xs text-ink-500">{$LANG.withdrawal.field_reported_delivery_help}</p>
         </div>

         <div>
            <label for="w_reason" class="cc-label">{$LANG.withdrawal.field_reason} <span class="font-normal text-ink-500">{$LANG.common.optional}</span></label>
            <textarea name="reason" id="w_reason" rows="3">{$WITHDRAW.reason}</textarea>
            <p class="mt-1 text-xs text-ink-500">{$LANG.withdrawal.field_reason_help}</p>
         </div>

         <div class="flex items-start gap-2">
            <input type="checkbox" name="statement" id="w_statement" value="1" class="mt-1"{if $WITHDRAW.statement} checked{/if} required>
            <label for="w_statement" class="text-sm text-ink-800">{$LANG.withdrawal.field_statement}</label>
         </div>

         {* Honeypot — see the header. Must stay visually hidden, not type=hidden. *}
         <div class="cc-sr-only" aria-hidden="true">
            <input type="text" name="website" value="" tabindex="-1" autocomplete="off">
         </div>
      </div>

      <button type="submit" name="withdraw_submit" value="1" class="cc-btn cc-btn-primary mt-6 w-full">{$LANG.withdrawal.submit}</button>
   </form>
</div>

<div class="hidden" id="validate_field_required">{$LANG.form.field_required}</div>
<div class="hidden" id="validate_email">{$LANG.common.error_email_invalid}</div>
