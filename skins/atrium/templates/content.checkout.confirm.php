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
      {* Same card and Edit affordance as the delivery addresses opposite, minus
         the radio: there is only ever one billing address. The description is
         left off because core names it after its own role, so it would just
         repeat the heading. *}
      <div class="cc-card mt-3 flex items-start gap-3 p-4">
         <div class="min-w-0 flex-1">
            <address class="text-sm not-italic leading-relaxed text-ink-700">
               {$DATA.first_name|capitalize:true} {$DATA.last_name|capitalize:true}<br>
               {if $DATA.company_name}{$DATA.company_name}<br>{/if}
               {$DATA.line1|capitalize:true}<br>
               {if $DATA.line2}{$DATA.line2|capitalize:true}<br>{/if}
               {$DATA.town|upper}<br>
               {if !empty($DATA.state)}{$DATA.state|upper}, {/if}{$DATA.postcode}
               {if $CONFIG.store_country_name!==$DATA.country}<br>{$DATA.country}{/if}
            </address>
            {if !empty($DATA.w3w)}
            <span class="mt-1 block text-xs text-ink-600"><span class="w3w-slashes">///</span>{$DATA.w3w}</span>
            {/if}
         </div>
         <a href="{$STORE_URL}/index.php?_a=addressbook&action=edit&address_id={$DATA.address_id}&redir=confirm"
            class="cc-btn cc-btn-ghost shrink-0">{$LANG.common.edit}</a>
      </div>
   </section>

   {* See the CTRL_DELIVERY note in the header. *}
   {if $CTRL_DELIVERY}
      {assign var=CTRL_DELIVERY value=false}
      {foreach from=$ITEMS key=hash item=item}{if $item.digital=='0'}{assign var=CTRL_DELIVERY value=true}{/if}{/foreach}
   {/if}

   {if $CTRL_DELIVERY}
   <section>
      <h2 class="text-lg font-semibold text-ink-900">{$LANG.address.delivery_address}</h2>
      {* Was a <select> showing "Description (TOWN, POSTCODE)", which is not enough
         to tell two addresses apart and is easy to mis-read at a glance. Radios
         instead, one card per address, each showing the address in full.

         $ADDRESSES already carries every formatted field (Cubecart::_confirm
         builds it from User::getAddresses), so this needs no core change, and
         core reads $_POST['delivery_address'] the same from a radio as from a
         select. It even prepares $address.checked for exactly this.

         Collapsed, only the chosen card is shown; Change reveals the rest. With
         JavaScript off every card is visible and the radios work on their own,
         which is why there is no x-cloak here. *}
      <div class="mt-3" x-data="{ open: false, selected: '{foreach from=$ADDRESSES item=address}{if $address.checked}{$address.address_id}{/if}{/foreach}' }">
         <fieldset class="space-y-3">
            <legend class="cc-sr-only">{$LANG.address.delivery_address}</legend>
            {foreach from=$ADDRESSES item=address}
            {* Card is a div, not a label: the Edit link is a sibling of the
               label, because an <a> inside a <label> is invalid and clicking it
               would also toggle the radio. *}
            <div class="cc-card flex items-start gap-3 p-4"
                 x-show="open || selected === '{$address.address_id}'"
                 :class="selected === '{$address.address_id}' ? 'border-brand-600' : ''">
               <label class="flex min-w-0 flex-1 cursor-pointer gap-3">
                  <input type="radio" name="delivery_address" value="{$address.address_id}" {$address.checked}
                         class="mt-1 shrink-0" @change="selected = '{$address.address_id}'; open = false">
                  <span class="min-w-0">
                     {if $address.description}<span class="block text-sm font-semibold text-ink-900">{$address.description}</span>{/if}
                     <span class="mt-1 block text-sm not-italic leading-relaxed text-ink-700">
                        {$address.first_name|capitalize:true} {$address.last_name|capitalize:true}<br>
                        {if $address.company_name}{$address.company_name}<br>{/if}
                        {$address.line1|capitalize:true}<br>
                        {if !empty($address.line2)}{$address.line2|capitalize:true}<br>{/if}
                        {$address.town|upper}<br>
                        {if !empty($address.state)}{$address.state|upper}, {/if}{$address.postcode}
                        {if $CONFIG.store_country_name!==$address.country}<br>{$address.country}{/if}
                     </span>
                     {if !empty($address.w3w)}
                     <span class="mt-1 block text-xs text-ink-600"><span class="w3w-slashes">///</span>{$address.w3w}</span>
                     {/if}
                  </span>
               </label>
               <a href="{$STORE_URL}/index.php?_a=addressbook&action=edit&address_id={$address.address_id}&redir=confirm"
                  class="cc-btn cc-btn-ghost shrink-0">{$LANG.common.edit}</a>
            </div>
            {/foreach}
         </fieldset>

         <div class="mt-3 flex flex-wrap gap-2">
            {if $ADDRESSES|@count > 1}
            <button type="button" class="cc-btn cc-btn-secondary" x-show="!open" @click="open = true">{$LANG.common.change_address|default:'Change Address'}</button>
            {/if}
            <a href="{$STORE_URL}/index.php?_a=addressbook&action=add&redir=confirm" class="cc-btn cc-btn-secondary">{$LANG.address.address_add}</a>
         </div>
      </div>
   </section>
   {/if}
</div>

{* Two gates, and both must pass: the STORE switch ($CONFIG.newsletter_status,
   Store Settings) and the SKIN setting, which only hides the checkout opt-in and
   leaves the footer signup alone. *}
{if (!isset($CONFIG.newsletter_status) || $CONFIG.newsletter_status=='1') && (!isset($SKIN_SETTINGS.show_mailing_list) || $SKIN_SETTINGS.show_mailing_list)}
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
         {$BILLING.first_name|capitalize:true} {$BILLING.last_name|capitalize:true}<br>
         {if $BILLING.company_name}{$BILLING.company_name}<br>{/if}
         {$BILLING.line1|capitalize:true}<br>
         {if $BILLING.line2}{$BILLING.line2|capitalize:true}<br>{/if}
         {$BILLING.town|upper}<br>
         {if !empty($BILLING.state)}{$BILLING.state|upper}, {/if}{$BILLING.postcode}<br>
         {$BILLING.country_name}
      </address>
      <h3 class="mt-4 text-sm font-semibold text-ink-900">{$LANG.account.contact_details}</h3>
      <ul class="mt-2 space-y-1 text-sm text-ink-700">
         <li>{$BILLING.first_name|capitalize:true} {$BILLING.last_name|capitalize:true} &lt;{$USER.email}&gt;</li>
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
         {$DELIVERY.first_name|capitalize:true} {$DELIVERY.last_name|capitalize:true}<br>
         {if $DELIVERY.company_name}{$DELIVERY.company_name}<br>{/if}
         {$DELIVERY.line1|capitalize:true}<br>
         {if $DELIVERY.line2}{$DELIVERY.line2|capitalize:true}<br>{/if}
         {$DELIVERY.town|upper}<br>
         {if !empty($DELIVERY.state)}{$DELIVERY.state|upper}, {/if}{$DELIVERY.postcode}<br>
         {$DELIVERY.country_name}
      </address>
      <button type="button" class="show_address_form cc-btn cc-btn-secondary mt-3" @click="makeChanges()">{$LANG.form.make_changes}</button>
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
{* Exactly one of the summary above and this form shows, as in foundation
   (content.checkout.confirm.php:117): with an address already on file the
   customer sees the summary and reaches the fields through "Make Changes".
   Server-rendered rather than x-show so it is right before Alpine boots and
   with JS off; makeChanges() removes the class. *}
<div id="checkout_register_form" x-show="mode === 'register'" x-cloak{if !empty($BILLING.line1)} class="hidden"{/if}>
   <h2 class="text-lg font-semibold text-ink-900">{$LANG.account.your_details}</h2>
   <p class="mt-1 text-sm text-ink-600">
      {$LANG.account.already_registered}
      <button type="button" id="checkout_login" class="underline" @click="setMode('login')">{$LANG.account.log_in}</button>
   </p>

   <h3 class="mt-6 text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.account.contact_details}</h3>
   <div class="mt-3 grid gap-4 sm:grid-cols-2">
      <div>
         <label for="user_first" class="cc-label">{$LANG.user.name_first}</label>
         <input type="text" name="user[first_name]" id="user_first" required value="{$USER.first_name|capitalize:true}" autocomplete="given-name" maxlength="32">
      </div>
      <div>
         <label for="user_last" class="cc-label">{$LANG.user.name_last}</label>
         <input type="text" name="user[last_name]" id="user_last" required value="{$USER.last_name|capitalize:true}" autocomplete="family-name" maxlength="32">
      </div>
      <div>
         <label for="user_email" class="cc-label">{$LANG.common.email}</label>
         <input type="email" name="user[email]" id="user_email" data-remote="email" required value="{$USER.email}" autocomplete="email" maxlength="96">
      </div>
      {if $CONFIG.emailconf=='1'}
      <div>
         <label for="emailconf" class="cc-label">{$LANG.account.email_confirm}</label>
         {* .nopaste is a contract: core JS blocks paste so the confirmation is typed. *}
         <input type="email" name="emailconf" id="emailconf" data-match="#user_email" data-msg-match="{$LANG.account.error_email_mismatch}" class="nopaste" required maxlength="96">
      </div>
      {/if}
      <div>
         <label for="user_phone" class="cc-label">{$LANG.address.phone}</label>
         <input type="tel" name="user[phone]" id="user_phone" required value="{$USER.phone}" autocomplete="tel">
      </div>
      {* "Show Mobile Number" — a merchant setting (config.xml <settings>). The
         field is optional, so hiding it cannot block a checkout. *}
      {if !isset($SKIN_SETTINGS.show_mobile_field) || $SKIN_SETTINGS.show_mobile_field}
      <div>
         <label for="user_mobile" class="cc-label">{$LANG.address.mobile} <span class="font-normal text-ink-500">{$LANG.common.optional}</span></label>
         <input type="tel" name="user[mobile]" id="user_mobile" value="{$USER.mobile}" autocomplete="tel">
      </div>
      {/if}
   </div>

   <h3 class="mt-8 text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.address.billing_address}</h3>
   {if !$ALLOW_DELIVERY_ADDRESS}<p class="mt-1 text-sm text-ink-600">{$LANG.address.ship_to_billing_only}</p>{/if}

   <div class="mt-3 space-y-4">
         {* "Show Company Name" — a merchant setting declared in config.xml
            (<settings>) and edited from Manage Extensions. Defaults to showing
            if the value is missing, so a skin without the setting is unaffected. *}
         {if !isset($SKIN_SETTINGS.show_company_name) || $SKIN_SETTINGS.show_company_name}
      <div>
         <label for="addr_company" class="cc-label">{$LANG.address.company_name} <span class="font-normal text-ink-500">{$LANG.common.optional}</span></label>
         <input type="text" name="billing[company_name]" id="addr_company" value="{$BILLING.company_name}" autocomplete="organization">
      </div>
      {/if}
      <div>
         <label for="addr_line1" class="cc-label">{$LANG.address.line1}</label>
         <input type="text" name="billing[line1]" id="addr_line1" required value="{$BILLING.line1|capitalize:true}" autocomplete="off" autocorrect="off" class="address_lookup" placeholder="{if $ADDRESS_LOOKUP}{$LANG.address.address_lookup}{/if}">
      </div>
      {if $ADDRESS_LOOKUP}
      <p id="lookup_fail"><a href="#" class="text-sm underline">{$LANG.address.address_not_found}</a></p>
      {/if}

      <div id="address_form"{if $ADDRESS_LOOKUP} class="hidden"{/if}>
         <div class="space-y-4">
            <div>
               <label for="addr_line2" class="cc-label">{$LANG.address.line2}</label>
               <input type="text" name="billing[line2]" id="addr_line2" value="{$BILLING.line2|capitalize:true}" autocomplete="address-line2">
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
               <div>
                  <label for="addr_town" class="cc-label">{$LANG.address.town}</label>
                  <input type="text" name="billing[town]" id="addr_town" required value="{$BILLING.town|upper}" autocomplete="address-level2">
               </div>
               <div>
                  <label for="addr_postcode" class="cc-label">{$LANG.address.postcode}</label>
                  <input type="text" name="billing[postcode]" id="addr_postcode" required class="uppercase" value="{$BILLING.postcode}" autocapitalize="characters" autocomplete="postal-code">
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
                  <label for="state-list" class="cc-label">{$LANG.address.state} <span data-cc-optional hidden class="font-normal text-ink-500">{$LANG.common.optional}</span></label>
                  <input type="text" name="billing[state]" id="state-list" value="{$BILLING.state|upper}" autocomplete="address-level1">
                  <select name="billing[state]" id="state-list_select" hidden disabled></select>
               </div>
            </div>
            {if !empty($CONFIG.w3w)}
            <div>
               <label for="w3w_billing" class="cc-label">{$LANG.address.w3w_address} <span class="font-normal text-ink-500">{$LANG.common.optional}</span></label>
               {include file='templates/element.w3w.php' value=$BILLING.w3w as_id="w3w_as_billing" input_id="w3w_billing" input_name="billing[w3w]" country_id="country-list"}
            </div>
            {/if}
         </div>
      </div>
   </div>

   {if $TERMS_CONDITIONS}
   <div class="mt-6 flex items-start gap-2" id="error_terms_agree">
      <input type="checkbox" id="reg_terms" name="terms_agree" value="1" {$TERMS_CONDITIONS_CHECKED} required data-msg-required="{$LANG.account.error_terms_agree}" rel="error_terms_agree" class="mt-1">
      <label for="reg_terms" class="text-sm text-ink-800">{sprintf($LANG.account.register_terms_agree_link,$TERMS_CONDITIONS)}</label>
   </div>
   {/if}

   {* Same two gates as the logged-in branch above. *}
   {if (!isset($CONFIG.newsletter_status) || $CONFIG.newsletter_status=='1') && (!isset($SKIN_SETTINGS.show_mailing_list) || $SKIN_SETTINGS.show_mailing_list)}
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
      <input type="checkbox" name="delivery_is_billing" id="delivery_is_billing" {$DELIVERY_CHECKED} @change="toggleDelivery($event)">
      <label for="delivery_is_billing" class="text-sm text-ink-800">{$LANG.address.delivery_is_billing}</label>
   </div>
   {/if}

   {if $ALLOW_DELIVERY_ADDRESS}
   <div id="address_delivery" x-show="!deliveryIsBilling" x-cloak x-collapse class="mt-6">
      <h3 class="text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.address.delivery_address}</h3>
      <div class="mt-3 grid gap-4 sm:grid-cols-2">
         <div>
            <label for="del_first" class="cc-label">{$LANG.user.name_first}</label>
            <input type="text" name="delivery[first_name]" id="del_first" required value="{$DELIVERY.first_name|capitalize:true}" autocomplete="given-name">
         </div>
         <div>
            <label for="del_last" class="cc-label">{$LANG.user.name_last}</label>
            <input type="text" name="delivery[last_name]" id="del_last" required value="{$DELIVERY.last_name|capitalize:true}" autocomplete="family-name">
         </div>
         {if !isset($SKIN_SETTINGS.show_company_name) || $SKIN_SETTINGS.show_company_name}
         <div class="sm:col-span-2">
            <label for="del_company" class="cc-label">{$LANG.address.company_name} <span class="font-normal text-ink-500">{$LANG.common.optional}</span></label>
            <input type="text" name="delivery[company_name]" id="del_company" value="{$DELIVERY.company_name}" autocomplete="organization">
         </div>
         {/if}
         <div class="sm:col-span-2">
            <label for="del_line1" class="cc-label">{$LANG.address.line1}</label>
            <input type="text" name="delivery[line1]" id="del_line1" required value="{$DELIVERY.line1|capitalize:true}" autocomplete="address-line1">
         </div>
         <div class="sm:col-span-2">
            <label for="del_line2" class="cc-label">{$LANG.address.line2}</label>
            <input type="text" name="delivery[line2]" id="del_line2" value="{$DELIVERY.line2|capitalize:true}" autocomplete="address-line2">
         </div>
         <div>
            <label for="del_town" class="cc-label">{$LANG.address.town}</label>
            <input type="text" name="delivery[town]" id="del_town" required value="{$DELIVERY.town|upper}" autocomplete="address-level2">
         </div>
         <div>
            <label for="del_postcode" class="cc-label">{$LANG.address.postcode}</label>
            <input type="text" name="delivery[postcode]" id="del_postcode" required class="uppercase" value="{$DELIVERY.postcode}" autocapitalize="characters" autocomplete="postal-code">
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
            <label for="delivery_state" class="cc-label">{$LANG.address.state} <span data-cc-optional hidden class="font-normal text-ink-500">{$LANG.common.optional}</span></label>
            <input type="text" name="delivery[state]" id="delivery_state" value="{$DELIVERY.state|upper}" autocomplete="address-level1">
            <select name="delivery[state]" id="delivery_state_select" hidden disabled></select>
         </div>
         {if !empty($CONFIG.w3w)}
         <div class="sm:col-span-2">
            <label for="w3w_delivery" class="cc-label">{$LANG.address.w3w_address} <span class="font-normal text-ink-500">{$LANG.common.optional}</span></label>
            {include file='templates/element.w3w.php' value=$DELIVERY.w3w as_id="w3w_as_delivery" input_id="w3w_delivery" input_name="delivery[w3w]" country_id="delivery_country"}
         </div>
         {/if}
      </div>
   </div>
   {/if}

   {* county_list drives the country -> state dependency in 40-checkout.js.
      Must appear AFTER the selects so the fields exist when it initialises. *}
   <script>var county_list = {if !empty($STATE_JSON)}{$STATE_JSON}{else}false{/if};</script>

   {* "Checkout Registration" — a merchant setting (config.xml <settings>):
        choice    the opt-in checkbox, and the default
        guest     no account offered at all; neither the checkbox nor the
                  password fields render, so `register` never posts
        required  no opt-out, so the flag posts from a hidden input and the
                  password fields are always visible
      An existing customer can still log in under all three: that is the
      mode === 'login' panel above, which this does not touch. *}
   {assign var='cc_reg_mode' value=$SKIN_SETTINGS.registration_mode|default:'choice'}

   {if $cc_reg_mode == 'choice'}
   <div class="mt-6 flex items-center gap-2">
      <input type="checkbox" name="register" id="show-reg" value="1" {$REGISTER_CHECKED} @change="toggleRegister($event)">
      <label for="show-reg" class="text-sm text-ink-800">{$LANG.account.create_account}</label>
   </div>
   {elseif $cc_reg_mode == 'required'}
   <input type="hidden" name="register" value="1">
   {/if}

   {if $cc_reg_mode != 'guest'}
   {* minlength/data-match mirror content.register.php. Core enforces both
      server-side (cubecart.class.php:1190-1197) but the failure comes back as a
      banner at the top of a long page, which reads as "the button did nothing".
      `required` is not an attribute here: it is set by ccCheckout to track the
      checkbox, so an unticked box does not block submit on a hidden field.

      In `required` mode the block carries no x-show/x-collapse: it must be
      visible even before Alpine boots, and there is nothing to toggle. *}
   <div id="account-reg"{if $cc_reg_mode == 'choice'} x-show="showRegister" x-cloak x-collapse{/if} class="mt-4">
      <h3 class="text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.account.password}</h3>
      <div class="mt-3 grid gap-4 sm:grid-cols-2">
         <div>
            <label for="reg_password" class="cc-label">{$LANG.account.password}</label>
            <input type="password" minlength="6" maxlength="64" name="password" id="reg_password" autocomplete="new-password">
         </div>
         <div>
            <label for="reg_passconf" class="cc-label">{$LANG.user.password_confirm}</label>
            <input type="password" minlength="6" maxlength="64" name="passconf" id="reg_passconf" data-match="#reg_password" autocomplete="new-password">
         </div>
      </div>
   </div>
   {/if}

   {include file='templates/content.recaptcha.php' ga_fid='checkout'}
</div>
{/if}

{* "Show Delivery Notes or Additional Comments" — a merchant setting
   (config.xml <settings>). Only the INPUT is gated: comments already stored on
   an order still render on the receipt and in admin. *}
{if !isset($SKIN_SETTINGS.show_order_comments) || $SKIN_SETTINGS.show_order_comments}
<div class="mt-8">
   <label for="delivery_comments" class="cc-label">{$LANG.basket.your_comments} <span class="font-normal text-ink-500">{$LANG.common.optional}</span></label>
   <textarea name="comments" id="delivery_comments" rows="3">{$VAL_CUSTOMER_COMMENTS}</textarea>
</div>
{/if}

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
