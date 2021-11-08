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
<div class="row">
   <div class="large-6 columns">
      <h2>{if $CTRL_DELIVERY}{$LANG.address.billing_address}{else}{$LANG.address.billing_delivery_address}{/if}</h2>
      {$DATA.title} {$DATA.first_name|capitalize} {$DATA.last_name|capitalize}<br>
      {if $DATA.company_name}{$DATA.company_name}<br>{/if}
      {$DATA.line1|capitalize}<br>
      {if $DATA.line2|capitalize}{$DATA.line2|capitalize}<br>{/if}
      {$DATA.town|upper}<br>
      {if !empty($DATA.state)}{$DATA.state|upper}, {/if}{$DATA.postcode}{if $CONFIG['store_country_name']!==$DATA['country']}<br>
      {$DATA.country}{/if}
      <div class="pad-top"><a href="{$STORE_URL}/index.php?_a=addressbook&action=edit&address_id={$DATA.address_id}&redir=confirm" class="button tiny secondary">{$LANG.address.address_edit}</a></div>
   </div>
   <div class="large-6 columns">
      {if $CTRL_DELIVERY}
         {assign var=CTRL_DELIVERY value=false}
         {foreach from=$ITEMS key=hash item=item}
            {if $item.digital=='0'}
               {assign var=CTRL_DELIVERY value=true}
            {/if}
         {/foreach}
      {/if}
      {if $CTRL_DELIVERY}
      <h2>{$LANG.address.delivery_address}</h2>
      <select name="delivery_address" style="text-transform:capitalize;">
      {foreach from=$ADDRESSES item=address}
      <option value="{$address.address_id}" {$address.selected}>{$address.description} ({$address.town|upper}, {$address.postcode})</option>
      {/foreach}
      </select>
      <div class="pad-top"><a href="{$STORE_URL}/index.php?_a=addressbook&action=add&redir=confirm" class="button tiny secondary">{$LANG.address.address_add}</a></div>
      {/if}
   </div>
</div>
{if !$USER_SUBSCRIBED}
<div class="row">
   <div class="small-12 large-8 columns">
      <input type="checkbox" id="mailing_list" name="mailing_list" value="1"><label for="mailing_list">{$LANG.account.register_mailing}</label>
   </div>
</div>
{/if}
{else}
<div id="register_false_address" class="row{if empty($BILLING.line1)} hide{/if}">
   <div class="large-6 columns">
      <h2>{$LANG.address.billing_address}</h2>
      {$BILLING.title} {$BILLING.first_name|capitalize} {$BILLING.last_name|capitalize}<br>
      {if $BILLING.company_name}{$BILLING.company_name}<br>{/if}
      {$BILLING.line1|capitalize}<br>
      {if $BILLING.line2|capitalize}{$BILLING.line2|capitalize}<br>{/if}
      {$BILLING.town|upper}<br>
      {if !empty($BILLING.state)}{$BILLING.state|upper}, {/if}{$BILLING.postcode}<br>
      {$BILLING.country_name}
      <h3>{$LANG.account.contact_details}</h3>
      <table>
        <tr><td style="text-align:center"><svg class="icon"><use xlink:href="#icon-envelope"></use></svg></td><td>{$BILLING.first_name|capitalize} {$BILLING.last_name|capitalize} &lt;{$USER.email}&gt;</td></tr>
        <tr><td style="text-align:center"><svg class="icon"><use xlink:href="#icon-phone"></use></svg></td><td>{$USER.phone}</td></tr>
        {if !empty($USER.mobile)}<tr><td style="text-align:center"><svg class="icon"><use xlink:href="#icon-mobile"></use></svg></td><td>{$USER.mobile}</td></tr>{/if}
      </table>
   </div>
   <div class="large-6 columns">
   {assign var=CTRL_DELIVERY value=false}
      {foreach from=$ITEMS key=hash item=item}
         {if $item.digital=='0'}
            {assign var=CTRL_DELIVERY value=true}
         {/if}
      {/foreach}
      {if $CTRL_DELIVERY}
      <h2>{$LANG.address.delivery_address}</h2>
      {$DELIVERY.title} {$DELIVERY.first_name|capitalize} {$DELIVERY.last_name|capitalize}<br>
      {if $DELIVERY.company_name}{$DELIVERY.company_name}<br>{/if}
      {$DELIVERY.line1|capitalize}<br>
      {if $DELIVERY.line2|capitalize}{$DELIVERY.line2|capitalize}<br>{/if}
      {$DELIVERY.town|upper}<br>
      {if !empty($DELIVERY.state)}{$DELIVERY.state|upper}, {/if}{$DELIVERY.postcode}<br>
      {$DELIVERY.country_name}
      <div class="pad-top"><a href="#" class="button small show_address_form"><svg class="icon"><use xlink:href="#icon-reply"></use></svg> {$LANG.form.make_changes}</a></div>
      {/if}
   </div>
</div>
<div class="hide" id="checkout_login_form">
   <h2>{$LANG.account.login}</h2>
   <p>{$LANG.account.return_register_form} <a href="#" id="checkout_register">{$LANG.common.signup}</a></p>
   <div class="row">
      <div class="small-12 large-6 columns">
         <label for="login-username" class="show-for-medium-up">{$LANG.user.email_address}</label>
         <input type="text" name="username" id="login-username" placeholder="{$LANG.user.email_address} {$LANG.form.required}" autocomplete="username" value="{$USERNAME}" required disabled>
      </div>
   </div>
   <div class="row">
      <div class="small-12 large-6 columns">
         <label for="login-password" class="show-for-medium-up">{$LANG.account.password}</label><input type="password" name="password" id="login-password" placeholder="{$LANG.account.password} {$LANG.form.required}" autocomplete="current-password" required disabled>
      </div>
   </div>
   <div class="row">
      <div class="small-12 columns">
         <p><a href="{$STORE_URL}/index.php?_a=recover">{$LANG.account.forgotten_password}</a></p>
      </div>
   </div>
   <div class="row">
      <div class="small-12 columns">
      <button type="submit" name="proceed" id="checkout_login_btn" class="button g-recaptcha">{$LANG.account.login}</button>
      </div>
   </div>
</div>
<div id="checkout_register_form"{if !empty($BILLING.line1)} class="hide"{/if}>
   <h2>{$LANG.account.your_details}</h2>
   <p>{$LANG.account.already_registered} <a href="#" id="checkout_login">{$LANG.account.log_in}</a></p>
   <h3>{$LANG.account.contact_details}</h3>
   <div class="row">
      <div class="small-4 columns"><label for="user_title" class="show-for-medium-up">{$LANG.user.title}</label><input type="text" name="user[title]" id="user_title"  class="capitalize" value="{$USER.title}" placeholder="{$LANG.user.title}" autocomplete="honorific-prefix"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="user_first" class="show-for-medium-up">{$LANG.user.name_first}</label><input type="text" name="user[first_name]" id="user_first" required value="{$USER.first_name|capitalize}" placeholder="{$LANG.user.name_first}  {$LANG.form.required}" autocomplete="given-name"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="user_last" class="show-for-medium-up">{$LANG.user.name_last}</label><input type="text" name="user[last_name]" id="user_last" required value="{$USER.last_name|capitalize}" placeholder="{$LANG.user.name_last}  {$LANG.form.required}" autocomplete="family-name"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="user_email" class="show-for-medium-up">{$LANG.common.email}</label><input type="text" name="user[email]" id="user_email" required value="{$USER.email}" placeholder="{$LANG.common.email}  {$LANG.form.required}" autocomplete="email"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="user_phone" class="show-for-medium-up">{$LANG.address.phone}</label><input type="text" name="user[phone]" id="user_phone" required value="{$USER.phone}" placeholder="{$LANG.address.phone}  {$LANG.form.required}" autocomplete="tel"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="user_mobile" class="show-for-medium-up">{$LANG.address.mobile}</label><input type="text" name="user[mobile]" id="user_mobile" value="{$USER.mobile}" placeholder="{$LANG.address.mobile}" autocomplete="tel"></div>
   </div>
   <h3>{$LANG.address.billing_address}</h3>
   {if !$ALLOW_DELIVERY_ADDRESS}{$LANG.address.ship_to_billing_only}{/if}
   <div class="row">
      <div class="small-12 large-8 columns"><label for="addr_company" class="show-for-medium-up">{$LANG.address.company_name}</label><input type="text" name="billing[company_name]" id="addr_company"  value="{$BILLING.company_name}" placeholder="{$LANG.address.company_name}" autocomplete="organization"></div>
   </div>
   <address>
      <div class="row">
         <div class="small-12 large-8 columns"><label for="addr_line1" class="show-for-medium-up">{$LANG.address.line1}</label><input type="text" name="billing[line1]" id="addr_line1"   value="{$BILLING.line1|capitalize}" placeholder="{if $ADDRESS_LOOKUP}{$LANG.address.address_lookup}{else}{$LANG.address.line1} {$LANG.form.required}{/if}" autocomplete="off" autocorrect="off" class="address_lookup"></div>
      </div>
      {if $ADDRESS_LOOKUP}
      <p id="lookup_fail"><a href="#">{$LANG.address.address_not_found}</a></p>
      {/if}
      <div{if $ADDRESS_LOOKUP} class="hide"{/if} id="address_form">
      <div class="row">
         <div class="small-12 large-8 columns"><label for="addr_line2" class="show-for-medium-up">{$LANG.address.line2}</label><input type="text" name="billing[line2]" id="addr_line2"  value="{$BILLING.line2|capitalize}" placeholder="{$LANG.address.line2}" autocomplete="address-line2"></div>
      </div>
      <div class="row">
         <div class="small-12 large-8 columns"><label for="addr_town" class="show-for-medium-up">{$LANG.address.town}</label><input type="text" name="billing[town]" id="addr_town"  required value="{$BILLING.town|upper}" placeholder="{$LANG.address.town} {$LANG.form.required}" autocomplete="address-level2"></div>
      </div>
      <div class="row">
         <div class="small-12 large-8 columns"><label for="addr_postcode" class="show-for-medium-up">{$LANG.address.postcode}</label><input type="text" name="billing[postcode]" id="addr_postcode"  class="uppercase required" value="{$BILLING.postcode}" placeholder="{$LANG.address.postcode} {$LANG.form.required}" autocomplete="postal-code"></div>
      </div>
      <div class="row">
         <div class="small-12 large-8 columns"><label for="country-list" class="show-for-medium-up">{$LANG.address.country}</label>
            <select name="billing[country]" class="nosubmit" rel="state-list" id="country-list" autocomplete="country-name">
            {foreach from=$COUNTRIES item=country}
            <option value="{$country.numcode}" data-status="{$country.status}" data-iso="{$country.iso}" {$country.selected}>{$country.name}</option>
            {/foreach}
            </select>
         </div>
      </div>
      <div class="row" id="state-list_wrapper">
         <div class="small-12 large-8 columns"><label for="state-list" class="show-for-medium-up">{$LANG.address.state}</label><input type="text" name="billing[state]" id="state-list" value="{$BILLING.state|upper}" autocomplete="address-line1"></div>
      </div>
      {if !empty($CONFIG.w3w)}
      <div class="row">
         <div class="small-12 columns">
            <label for="w3w">{$LANG.address.w3w_address} {$LANG.common.optional}</label>
            {include file='templates/element.w3w.php' value="{$BILLING.w3w}" as_id="w3w_as_billing" input_id="w3w_billing" input_name="billing[w3w]" country_id="country-list"}
         </div>
      </div>
      {/if}
</div>
</address>
{if $TERMS_CONDITIONS}
<div class="row">
   <div class="small-12 large-8 columns"><span id="error_terms_agree"><input type="checkbox" id="reg_terms" name="terms_agree" value="1" {$TERMS_CONDITIONS_CHECKED} rel="error_terms_agree"><label for="reg_terms">{$LANG.account.register_terms_agree_link|replace:'%s':{$TERMS_CONDITIONS}}</label></span></div>
</div>
{/if}
<div class="row">
   <div class="small-12 large-8 columns">
      <input type="checkbox" id="mailing_list" name="mailing_list" value="1" {$MAILING_LIST_SUBSCRIBE}><label for="mailing_list">{$LANG.account.register_mailing}</label>
   </div>
</div>
{assign var=CTRL_DELIVERY value=false}
{foreach from=$ITEMS key=hash item=item}
   {if $item.digital=='0'}
      {assign var=CTRL_DELIVERY value=true}
   {/if}
{/foreach}
{if !$CTRL_DELIVERY}
<input type="hidden" name="delivery_is_billing" id="delivery_is_billing" value="1">
{else if $ALLOW_DELIVERY_ADDRESS}
<div class="row">
   <div class="small-12 large-8 columns"><input type="checkbox" name="delivery_is_billing" id="delivery_is_billing" {$DELIVERY_CHECKED}><label for="delivery_is_billing">{$LANG.address.delivery_is_billing}</label></div>
</div>
{/if}
{if $ALLOW_DELIVERY_ADDRESS}
<div class="hide" id="address_delivery">
   <h3>{$LANG.address.delivery_address}</h3>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="del_first" class="show-for-medium-up">{$LANG.user.name_first}</label><input type="text" name="delivery[first_name]" id="del_first"   required value="{$DELIVERY.first_name|capitalize}" placeholder="{$LANG.user.name_first} {$LANG.form.required}" autocomplete="given-name"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="del_last" class="show-for-medium-up">{$LANG.user.name_last}</label><input type="text" name="delivery[last_name]" id="del_last"   required value="{$DELIVERY.last_name|capitalize}" placeholder="{$LANG.user.name_last} {$LANG.form.required}" autocomplete="family-name"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="del_company" class="show-for-medium-up">{$LANG.address.company_name}</label><input type="text" name="delivery[company_name]" id="del_company"  value="{$DELIVERY.company_name}" placeholder="{$LANG.user.company_name}" autocomplete="organization"></div>
   </div>
   <address>
      <div class="row">
         <div class="small-12 large-8 columns"><label for="del_line1" class="show-for-medium-up">{$LANG.address.line1}</label><input type="text" name="delivery[line1]" id="del_line1"  required value="{$DELIVERY.line1|capitalize}" placeholder="{$LANG.address.line1} {$LANG.form.required}" autocomplete="address-line1"></div>
      </div>
      <div class="row">
         <div class="small-12 large-8 columns"><label for="del_line2" class="show-for-medium-up">{$LANG.address.line2}</label><input type="text" name="delivery[line2]" id="del_line2"  value="{$DELIVERY.line2|capitalize}" placeholder="{$LANG.address.line2}" autocomplete="address-line2"></div>
      </div>
      <div class="row">
         <div class="small-12 large-8 columns"><label for="del_town" class="show-for-medium-up">{$LANG.address.town}</label><input type="text" name="delivery[town]" id="del_town"  required value="{$DELIVERY.town|upper}" placeholder="{$LANG.address.town} {$LANG.form.required}" autocomplete="address-level2"></div>
      </div>
      <div class="row">
         <div class="small-12 large-8 columns"><label for="del_postcode" class="show-for-medium-up">{$LANG.address.postcode}</label><input type="text" name="delivery[postcode]" id="del_postcode"  class="uppercase required" value="{$DELIVERY.postcode}" placeholder="{$LANG.address.postcode} {$LANG.form.required}" autocomplete="postal-code"></div>
      </div>
      <div class="row">
         <div class="small-12 large-8 columns"><label for="delivery_country" class="show-for-medium-up">{$LANG.address.country}</label>
            <select name="delivery[country]" id="delivery_country"  class="nosubmit country-list" rel="delivery_state" autocomplete="country-name">
            {foreach from=$COUNTRIES item=country}
            <option value="{$country.numcode}" data-status="{$country.status}" data-iso="{$country.iso}" {$country.selected_d}>{$country.name}</option>
            {/foreach}
            </select>
         </div>
      </div>
      <div class="row" id="delivery_state_wrapper">
         <div class="small-12 large-8 columns"><label for="delivery_state" class="show-for-medium-up">{$LANG.address.state}</label><input type="text" name="delivery[state]" id="delivery_state" value="{$DELIVERY.state|upper}" placeholder="{$LANG.address.state} {$LANG.form.required}" autocomplete="address-level1"></div>
      </div>
      {if !empty($CONFIG.w3w)}
      <div class="row">
         <div class="small-12 columns">
            <label for="w3w">{$LANG.address.w3w_address} {$LANG.common.optional}</label>
            {include file='templates/element.w3w.php' value="{$DELIVERY.w3w}" as_id="w3w_as_delivery" input_id="w3w_delivery" input_name="delivery[w3w]" country_id="delivery_country"}
         </div>
      </div>
      {/if}
   </address>
</div>
{/if}
<script type="text/javascript">
   var county_list = {if !empty($STATE_JSON)}{$STATE_JSON}{else}false{/if};
</script>
<div class="row">
   <div class="small-12 large-8 columns"><input type="checkbox" name="register" id="show-reg" value="1" {$REGISTER_CHECKED}><label for="show-reg">{$LANG.account.create_account}</label></div>
</div>
<div id="account-reg">
   <h3>{$LANG.account.password}</h3>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="reg_password" class="show-for-medium-up">{$LANG.account.password}</label><input type="password" autocomplete="off" name="password" id="reg_password"  required  placeholder="{$LANG.account.password} {$LANG.form.required}" autocomplete="new-password"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="reg_passconf" class="show-for-medium-up">{$LANG.user.password_confirm}</label><input type="password" autocomplete="off" name="passconf" id="reg_passconf"  required  placeholder="{$LANG.user.password_confirm} {$LANG.form.required}" autocomplete="new-password"></div>
   </div>
</div>
{include file='templates/content.recaptcha.php'}
</div>
{/if}
<label for="delivery_comments" class="return"><strong>{$LANG.basket.your_comments}</strong></label>
<textarea name="comments" id="delivery_comments">{$VAL_CUSTOMER_COMMENTS}</textarea>
<div class="hide" id="validate_required">{$LANG.form.required}</div>
<div class="hide" id="validate_field_required">{$LANG.form.field_required}</div>
<div class="hide" id="validate_email">{$LANG.common.error_email_invalid}</div>
<div class="hide" id="validate_email_in_use">{$LANG.account.error_email_in_use}</div>
<div class="hide" id="validate_phone">{$LANG.account.error_valid_phone}</div>
<div class="hide" id="validate_mobile">{$LANG.account.error_valid_mobile_phone}</div>
<div class="hide" id="validate_password">{$LANG.account.error_password_empty}</div>
<div class="hide" id="validate_password_length">{$LANG.account.error_password_length}</div>
<div class="hide" id="validate_password_mismatch">{$LANG.account.error_password_mismatch}</div>
<div class="hide" id="validate_terms_agree">{$LANG.account.error_terms_agree}</div>
