{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Address / login / register block, included by content.checkout.php when
 * $INCLUDE_CHECKOUT is set.
 *
 * ⚠ EVERY FIELD NAME BELOW IS A CORE CONTRACT. Renaming one silently drops that
 * value from the order:
 *   user[first_name] user[last_name] user[email] user[phone] user[mobile] emailconf
 *   billing[company_name] billing[line1] billing[line2] billing[town]
 *   billing[postcode] billing[country] billing[state] billing[w3w]
 *   delivery[...] same shape          delivery_address  (registered customers)
 *   delivery_is_billing  register  password  passconf  username
 *   mailing_list  terms_agree  comments
 *
 * ⚠ The login and register field sets live inside the ONE #checkout_form, so
 * both would post together. 40-checkout.js keeps exactly one set enabled —
 * disabled controls are neither submitted nor validated. Do not "tidy" the
 * disabled attributes away.
 *
 * ⚠ Each country <select> needs rel="<state field id>" and its options need
 * data-status; the state field needs a sibling <select> with id
 * "<id>_select" and a wrapper "<id>_wrapper". See 40-checkout.js.
 *
 * ⚠ CTRL_DELIVERY is recomputed by walking $ITEMS for a non-digital line: a
 * basket of downloads needs no delivery address. Core does not do this for us.
 *}

{if $IS_USER}
{* ---------------- Registered customer ---------------- *}
<div class="grid gap-8 lg:grid-cols-2">
   <section>
      <h2 class="text-lg font-semibold text-ink-900">{if $CTRL_DELIVERY}{$LANG.address.billing_address}{else}{$LANG.address.billing_delivery_address}{/if}</h2>
      <address class="mt-3 text-sm not-italic leading-relaxed text-ink-700">
         {$DATA.first_name|capitalize} {$DATA.last_name|capitalize}<br>
         {if $DATA.company_name}{$DATA.company_name}<br>{/if}
         {$DATA.line1|capitalize}<br>
         {if $DATA.line2}{$DATA.line2|capitalize}<br>{/if}
         {$DATA.town|upper}<br>
         {if !empty($DATA.state)}{$DATA.state|upper}, {/if}{$DATA.postcode}
         {if $CONFIG.store_country_name!==$DATA.country}<br>{$DATA.country}{/if}
      </address>
      <a href="{$STORE_URL}/index.php?_a=addressbook&action=edit&address_id={$DATA.address_id}&redir=confirm" class="cc-btn cc-btn-secondary mt-3">{$LANG.address.address_edit}</a>
   </section>

   {* See the CTRL_DELIVERY note in the header. *}
   {if $CTRL_DELIVERY}
      {assign var=CTRL_DELIVERY value=false}
      {foreach from=$ITEMS key=hash item=item}{if $item.digital=='0'}{assign var=CTRL_DELIVERY value=true}{/if}{/foreach}
   {/if}

   {if $CTRL_DELIVERY}
   <section>
      <h2 class="text-lg font-semibold text-ink-900">{$LANG.address.delivery_address}</h2>
      <label class="cc-sr-only" for="delivery_address">{$LANG.address.delivery_address}</label>
      <select name="delivery_address" id="delivery_address" class="mt-3 capitalize">
         {foreach from=$ADDRESSES item=address}
         <option value="{$address.address_id}" {$address.selected}>{$address.description} ({$address.town|upper}, {$address.postcode})</option>
         {/foreach}
      </select>
      <a href="{$STORE_URL}/index.php?_a=addressbook&action=add&redir=confirm" class="cc-btn cc-btn-secondary mt-3">{$LANG.address.address_add}</a>
   </section>
   {/if}
</div>

{if !isset($CONFIG.newsletter_status) || $CONFIG.newsletter_status=='1'}
   {if !$USER_SUBSCRIBED}
   <div class="mt-6 flex items-center gap-2">
      <input type="checkbox" id="mailing_list" name="mailing_list" value="1">
      <label for="mailing_list" class="text-sm text-ink-800">{$LANG.account.register_mailing}</label>
   </div>
   {/if}
{/if}

{else}
{* ---------------- Guest ---------------- *}

{* Summary of an address already entered this session; "make changes" reopens
   the register form. *}
<div id="register_false_address" class="grid gap-8 lg:grid-cols-2{if empty($BILLING.line1)} hidden{/if}">
   <section>
      <h2 class="text-lg font-semibold text-ink-900">{$LANG.address.billing_address}</h2>
      <address class="mt-3 text-sm not-italic leading-relaxed text-ink-700">
         {$BILLING.first_name|capitalize} {$BILLING.last_name|capitalize}<br>
         {if $BILLING.company_name}{$BILLING.company_name}<br>{/if}
         {$BILLING.line1|capitalize}<br>
         {if $BILLING.line2}{$BILLING.line2|capitalize}<br>{/if}
         {$BILLING.town|upper}<br>
         {if !empty($BILLING.state)}{$BILLING.state|upper}, {/if}{$BILLING.postcode}<br>
         {$BILLING.country_name}
      </address>
      <h3 class="mt-4 text-sm font-semibold text-ink-900">{$LANG.account.contact_details}</h3>
      <ul class="mt-2 space-y-1 text-sm text-ink-700">
         <li>{$BILLING.first_name|capitalize} {$BILLING.last_name|capitalize} &lt;{$USER.email}&gt;</li>
         <li>{$USER.phone}</li>
         {if !empty($USER.mobile)}<li>{$USER.mobile}</li>{/if}
      </ul>
   </section>

   {assign var=CTRL_DELIVERY value=false}
   {foreach from=$ITEMS key=hash item=item}{if $item.digital=='0'}{assign var=CTRL_DELIVERY value=true}{/if}{/foreach}
   {if $CTRL_DELIVERY}
   <section>
      <h2 class="text-lg font-semibold text-ink-900">{$LANG.address.delivery_address}</h2>
      <address class="mt-3 text-sm not-italic leading-relaxed text-ink-700">
         {$DELIVERY.first_name|capitalize} {$DELIVERY.last_name|capitalize}<br>
         {if $DELIVERY.company_name}{$DELIVERY.company_name}<br>{/if}
         {$DELIVERY.line1|capitalize}<br>
         {if $DELIVERY.line2}{$DELIVERY.line2|capitalize}<br>{/if}
         {$DELIVERY.town|upper}<br>
         {if !empty($DELIVERY.state)}{$DELIVERY.state|upper}, {/if}{$DELIVERY.postcode}<br>
         {$DELIVERY.country_name}
      </address>
      <button type="button" class="show_address_form cc-btn cc-btn-secondary mt-3" @click="setMode('register'); document.getElementById('register_false_address').classList.add('hidden')">{$LANG.form.make_changes}</button>
   </section>
   {/if}
</div>

{* ---- Login ---- *}
<div id="checkout_login_form" x-show="mode === 'login'" x-cloak class="mx-auto max-w-md">
   <h2 class="text-lg font-semibold text-ink-900">{$LANG.account.login}</h2>
   <p class="mt-1 text-sm text-ink-600">
      {$LANG.account.return_register_form}
      <button type="button" id="checkout_register" class="underline" @click="setMode('register')">{$LANG.common.signup}</button>
   </p>
   <div class="mt-4 space-y-4">
      <div>
         <label for="login-username" class="cc-label">{$LANG.user.email_address}</label>
         <input type="text" name="username" id="login-username" autocomplete="username" value="{$USERNAME}" required disabled>
      </div>
      <div>
         <label for="login-password" class="cc-label">{$LANG.account.password}</label>
         <input type="password" maxlength="64" name="password" id="login-password" autocomplete="current-password" required disabled>
      </div>
      <p class="text-sm"><a href="{$STORE_URL}/index.php?_a=recover" class="underline">{$LANG.account.forgotten_password}</a></p>
      {* proceed() injects a hidden proceed=1: under invisible captcha
         grecaptcha submits the form programmatically, which drops this
         button's own name/value. *}
      <button type="submit" name="proceed" id="checkout_login_btn" class="g-recaptcha cc-btn cc-btn-primary w-full" @click="proceed()">{$LANG.account.login}</button>
   </div>
</div>

{* ---- Register / guest details ---- *}
<div id="checkout_register_form" x-show="mode === 'register'" x-cloak>
   <h2 class="text-lg font-semibold text-ink-900">{$LANG.account.your_details}</h2>
   <p class="mt-1 text-sm text-ink-600">
      {$LANG.account.already_registered}
      <button type="button" id="checkout_login" class="underline" @click="setMode('login')">{$LANG.account.log_in}</button>
   </p>

   <h3 class="mt-6 text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.account.contact_details}</h3>
   <div class="mt-3 grid gap-4 sm:grid-cols-2">
      <div>
         <label for="user_first" class="cc-label">{$LANG.user.name_first}</label>
         <input type="text" name="user[first_name]" id="user_first" required value="{$USER.first_name|capitalize}" autocomplete="given-name" maxlength="32">
      </div>
      <div>
         <label for="user_last" class="cc-label">{$LANG.user.name_last}</label>
         <input type="text" name="user[last_name]" id="user_last" required value="{$USER.last_name|capitalize}" autocomplete="family-name" maxlength="32">
      </div>
      <div>
         <label for="user_email" class="cc-label">{$LANG.common.email}</label>
         <input type="email" name="user[email]" id="user_email" required value="{$USER.email}" autocomplete="email" maxlength="96">
      </div>
      {if $CONFIG.emailconf=='1'}
      <div>
         <label for="emailconf" class="cc-label">{$LANG.account.email_confirm}</label>
         {* .nopaste is a contract: core JS blocks paste so the confirmation is typed. *}
         <input type="email" name="emailconf" id="emailconf" class="nopaste" required maxlength="96">
      </div>
      {/if}
      <div>
         <label for="user_phone" class="cc-label">{$LANG.address.phone}</label>
         <input type="tel" name="user[phone]" id="user_phone" required value="{$USER.phone}" autocomplete="tel">
      </div>
      <div>
         <label for="user_mobile" class="cc-label">{$LANG.address.mobile}</label>
         <input type="tel" name="user[mobile]" id="user_mobile" value="{$USER.mobile}" autocomplete="tel">
      </div>
   </div>

   <h3 class="mt-8 text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.address.billing_address}</h3>
   {if !$ALLOW_DELIVERY_ADDRESS}<p class="mt-1 text-sm text-ink-600">{$LANG.address.ship_to_billing_only}</p>{/if}

   <div class="mt-3 space-y-4">
      <div>
         <label for="addr_company" class="cc-label">{$LANG.address.company_name}</label>
         <input type="text" name="billing[company_name]" id="addr_company" value="{$BILLING.company_name}" autocomplete="organization">
      </div>
      <div>
         <label for="addr_line1" class="cc-label">{$LANG.address.line1}</label>
         <input type="text" name="billing[line1]" id="addr_line1" value="{$BILLING.line1|capitalize}" autocomplete="off" autocorrect="off" class="address_lookup" placeholder="{if $ADDRESS_LOOKUP}{$LANG.address.address_lookup}{/if}">
      </div>
      {if $ADDRESS_LOOKUP}
      <p id="lookup_fail"><a href="#" class="text-sm underline">{$LANG.address.address_not_found}</a></p>
      {/if}

      <div id="address_form"{if $ADDRESS_LOOKUP} class="hidden"{/if}>
         <div class="space-y-4">
            <div>
               <label for="addr_line2" class="cc-label">{$LANG.address.line2}</label>
               <input type="text" name="billing[line2]" id="addr_line2" value="{$BILLING.line2|capitalize}" autocomplete="address-line2">
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
               <div>
                  <label for="addr_town" class="cc-label">{$LANG.address.town}</label>
                  <input type="text" name="billing[town]" id="addr_town" required value="{$BILLING.town|upper}" autocomplete="address-level2">
               </div>
               <div>
                  <label for="addr_postcode" class="cc-label">{$LANG.address.postcode}</label>
                  <input type="text" name="billing[postcode]" id="addr_postcode" class="uppercase required" value="{$BILLING.postcode}" autocomplete="postal-code">
               </div>
               <div>
                  <label for="country-list" class="cc-label">{$LANG.address.country}</label>
                  <select name="billing[country]" class="nosubmit country-list" rel="state-list" id="country-list" autocomplete="country-name">
                     {foreach from=$COUNTRIES item=country}
                     <option value="{$country.numcode}" data-status="{$country.status}" data-iso="{$country.iso}" {$country.selected}>{$country.name}</option>
                     {/foreach}
                  </select>
               </div>
               <div id="state-list_wrapper">
                  <label for="state-list" class="cc-label">{$LANG.address.state}</label>
                  <input type="text" name="billing[state]" id="state-list" value="{$BILLING.state|upper}" autocomplete="address-level1">
                  <select name="billing[state]" id="state-list_select" hidden disabled></select>
               </div>
            </div>
            {if !empty($CONFIG.w3w)}
            <div>
               <label for="w3w_billing" class="cc-label">{$LANG.address.w3w_address} {$LANG.common.optional}</label>
               {include file='templates/element.w3w.php' value=$BILLING.w3w as_id="w3w_as_billing" input_id="w3w_billing" input_name="billing[w3w]" country_id="country-list"}
            </div>
            {/if}
         </div>
      </div>
   </div>

   {if $TERMS_CONDITIONS}
   <div class="mt-6 flex items-start gap-2" id="error_terms_agree">
      <input type="checkbox" id="reg_terms" name="terms_agree" value="1" {$TERMS_CONDITIONS_CHECKED} rel="error_terms_agree" class="mt-1">
      <label for="reg_terms" class="text-sm text-ink-800">{sprintf($LANG.account.register_terms_agree_link,$TERMS_CONDITIONS)}</label>
   </div>
   {/if}

   {if !isset($CONFIG.newsletter_status) || $CONFIG.newsletter_status=='1'}
   <div class="mt-3 flex items-center gap-2">
      <input type="checkbox" id="mailing_list" name="mailing_list" value="1" {$MAILING_LIST_SUBSCRIBE}>
      <label for="mailing_list" class="text-sm text-ink-800">{$LANG.account.register_mailing}</label>
   </div>
   {/if}

   {assign var=CTRL_DELIVERY value=false}
   {foreach from=$ITEMS key=hash item=item}{if $item.digital=='0'}{assign var=CTRL_DELIVERY value=true}{/if}{/foreach}

   {if !$CTRL_DELIVERY}
   {* Downloads only: force delivery = billing, no address to collect. *}
   <input type="hidden" name="delivery_is_billing" id="delivery_is_billing" value="1">
   {elseif $ALLOW_DELIVERY_ADDRESS}
   <div class="mt-6 flex items-center gap-2">
      <input type="checkbox" name="delivery_is_billing" id="delivery_is_billing" {$DELIVERY_CHECKED} @change="deliveryIsBilling = $event.target.checked">
      <label for="delivery_is_billing" class="text-sm text-ink-800">{$LANG.address.delivery_is_billing}</label>
   </div>
   {/if}

   {if $ALLOW_DELIVERY_ADDRESS}
   <div id="address_delivery" x-show="!deliveryIsBilling" x-cloak x-collapse class="mt-6">
      <h3 class="text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.address.delivery_address}</h3>
      <div class="mt-3 grid gap-4 sm:grid-cols-2">
         <div>
            <label for="del_first" class="cc-label">{$LANG.user.name_first}</label>
            <input type="text" name="delivery[first_name]" id="del_first" required value="{$DELIVERY.first_name|capitalize}" autocomplete="given-name">
         </div>
         <div>
            <label for="del_last" class="cc-label">{$LANG.user.name_last}</label>
            <input type="text" name="delivery[last_name]" id="del_last" required value="{$DELIVERY.last_name|capitalize}" autocomplete="family-name">
         </div>
         <div class="sm:col-span-2">
            <label for="del_company" class="cc-label">{$LANG.address.company_name}</label>
            <input type="text" name="delivery[company_name]" id="del_company" value="{$DELIVERY.company_name}" autocomplete="organization">
         </div>
         <div class="sm:col-span-2">
            <label for="del_line1" class="cc-label">{$LANG.address.line1}</label>
            <input type="text" name="delivery[line1]" id="del_line1" required value="{$DELIVERY.line1|capitalize}" autocomplete="address-line1">
         </div>
         <div class="sm:col-span-2">
            <label for="del_line2" class="cc-label">{$LANG.address.line2}</label>
            <input type="text" name="delivery[line2]" id="del_line2" value="{$DELIVERY.line2|capitalize}" autocomplete="address-line2">
         </div>
         <div>
            <label for="del_town" class="cc-label">{$LANG.address.town}</label>
            <input type="text" name="delivery[town]" id="del_town" required value="{$DELIVERY.town|upper}" autocomplete="address-level2">
         </div>
         <div>
            <label for="del_postcode" class="cc-label">{$LANG.address.postcode}</label>
            <input type="text" name="delivery[postcode]" id="del_postcode" class="uppercase required" value="{$DELIVERY.postcode}" autocomplete="postal-code">
         </div>
         <div>
            <label for="delivery_country" class="cc-label">{$LANG.address.country}</label>
            <select name="delivery[country]" id="delivery_country" class="nosubmit country-list" rel="delivery_state" autocomplete="country-name">
               {foreach from=$COUNTRIES item=country}
               <option value="{$country.numcode}" data-status="{$country.status}" data-iso="{$country.iso}" {$country.selected_d}>{$country.name}</option>
               {/foreach}
            </select>
         </div>
         <div id="delivery_state_wrapper">
            <label for="delivery_state" class="cc-label">{$LANG.address.state}</label>
            <input type="text" name="delivery[state]" id="delivery_state" value="{$DELIVERY.state|upper}" autocomplete="address-level1">
            <select name="delivery[state]" id="delivery_state_select" hidden disabled></select>
         </div>
         {if !empty($CONFIG.w3w)}
         <div class="sm:col-span-2">
            <label for="w3w_delivery" class="cc-label">{$LANG.address.w3w_address} {$LANG.common.optional}</label>
            {include file='templates/element.w3w.php' value=$DELIVERY.w3w as_id="w3w_as_delivery" input_id="w3w_delivery" input_name="delivery[w3w]" country_id="delivery_country"}
         </div>
         {/if}
      </div>
   </div>
   {/if}

   {* county_list drives the country -> state dependency in 40-checkout.js.
      Must appear AFTER the selects so the fields exist when it initialises. *}
   <script>var county_list = {if !empty($STATE_JSON)}{$STATE_JSON}{else}false{/if};</script>

   <div class="mt-6 flex items-center gap-2">
      <input type="checkbox" name="register" id="show-reg" value="1" {$REGISTER_CHECKED} @change="toggleRegister($event)">
      <label for="show-reg" class="text-sm text-ink-800">{$LANG.account.create_account}</label>
   </div>

   <div id="account-reg" x-show="showRegister" x-cloak x-collapse class="mt-4">
      <h3 class="text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.account.password}</h3>
      <div class="mt-3 grid gap-4 sm:grid-cols-2">
         <div>
            <label for="reg_password" class="cc-label">{$LANG.account.password}</label>
            <input type="password" maxlength="64" name="password" id="reg_password" autocomplete="new-password">
         </div>
         <div>
            <label for="reg_passconf" class="cc-label">{$LANG.user.password_confirm}</label>
            <input type="password" maxlength="64" name="passconf" id="reg_passconf" autocomplete="new-password">
         </div>
      </div>
   </div>

   {include file='templates/content.recaptcha.php' ga_fid='checkout'}
</div>
{/if}

<div class="mt-8">
   <label for="delivery_comments" class="cc-label">{$LANG.basket.your_comments}</label>
   <textarea name="comments" id="delivery_comments" rows="3">{$VAL_CUSTOMER_COMMENTS}</textarea>
</div>

{* Validation strings looked up BY ID by core JS and by plugins. Keep the ids. *}
<div class="hidden" id="validate_required">{$LANG.form.required}</div>
<div class="hidden" id="validate_field_required">{$LANG.form.field_required}</div>
<div class="hidden" id="validate_email">{$LANG.common.error_email_invalid}</div>
<div class="hidden" id="validate_email_in_use">{$LANG.account.error_email_in_use}</div>
<div class="hidden" id="validate_phone">{$LANG.account.error_valid_phone}</div>
<div class="hidden" id="validate_mobile">{$LANG.account.error_valid_mobile_phone}</div>
<div class="hidden" id="validate_password">{$LANG.account.error_password_empty}</div>
<div class="hidden" id="validate_password_length">{$LANG.account.error_password_length}</div>
<div class="hidden" id="validate_password_length_max">{$LANG.account.error_password_length_max}</div>
<div class="hidden" id="validate_password_mismatch">{$LANG.account.error_password_mismatch}</div>
{if $CONFIG.emailconf=='1'}<div class="hidden" id="validate_email_mismatch">{$LANG.account.error_email_mismatch}</div>{/if}
<div class="hidden" id="validate_terms_agree">{$LANG.account.error_terms_agree}</div>
