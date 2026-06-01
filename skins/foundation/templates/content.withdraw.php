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
<h2>{$LANG.withdrawal.page_title}</h2>
<p>{$LANG.withdrawal.page_intro}</p>

{if $WITHDRAW_ERRORS}
<div data-alert class="alert-box alert">
   <ul style="margin:0">
   {foreach from=$WITHDRAW_ERRORS item=err}
      <li>{$err}</li>
   {/foreach}
   </ul>
</div>
{/if}

<form action="{$VAL_SELF}" method="post" id="withdraw_form" autocomplete="off">
   <div style="position:absolute;left:-9999px" aria-hidden="true">
      <label for="withdraw_website">Website (leave blank)</label>
      <input type="text" name="website" id="withdraw_website" tabindex="-1" autocomplete="off">
   </div>

   <div class="row">
      <div class="small-12 large-8 columns">
         <label for="withdraw_name">{$LANG.withdrawal.field_name} {$LANG.form.required}</label>
         <input type="text" name="name" id="withdraw_name" value="{$WITHDRAW.name|escape}" required>
      </div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns">
         <label for="withdraw_email">{$LANG.withdrawal.field_email} {$LANG.form.required}</label>
         <input type="email" name="email" id="withdraw_email" value="{$WITHDRAW.email|escape}" required>
      </div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns">
         <label for="withdraw_address">{$LANG.withdrawal.field_address} {$LANG.form.required}</label>
         <textarea name="address" id="withdraw_address" rows="3" required>{$WITHDRAW.address|escape}</textarea>
      </div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns">
         <label for="withdraw_order">{$LANG.withdrawal.field_order}</label>
         {if $WITHDRAW_USER_ORDERS}
         <select name="cart_order_id" id="withdraw_order">
            <option value="">{$LANG.form.none}</option>
            {foreach from=$WITHDRAW_USER_ORDERS item=o}
            <option value="{$o.cart_order_id}"{if $o.cart_order_id == $WITHDRAW.cart_order_id} selected{/if}>{$o.cart_order_id} — {$o.order_date}</option>
            {/foreach}
         </select>
         {else}
         <input type="text" name="cart_order_id" id="withdraw_order" value="{$WITHDRAW.cart_order_id|escape}">
         {/if}
         <small>{$LANG.withdrawal.field_order_help}</small>
      </div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns">
         <label for="withdraw_reported_delivery">{$LANG.withdrawal.field_reported_delivery}</label>
         <input type="date" name="reported_delivery" id="withdraw_reported_delivery" value="{$WITHDRAW.reported_delivery|escape}">
         <small>{$LANG.withdrawal.field_reported_delivery_help}</small>
      </div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns">
         <label for="withdraw_statement">{$LANG.withdrawal.field_statement} {$LANG.form.required}</label>
         <textarea name="statement" id="withdraw_statement" rows="3" required>{$WITHDRAW.statement|escape}</textarea>
      </div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns">
         <label for="withdraw_reason">{$LANG.withdrawal.field_reason}</label>
         <textarea name="reason" id="withdraw_reason" rows="3">{$WITHDRAW.reason|escape}</textarea>
         <small>{$LANG.withdrawal.field_reason_help}</small>
      </div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns">
         <button type="submit" name="withdraw_submit" value="1" class="button">{$LANG.withdrawal.submit}</button>
      </div>
   </div>
</form>
