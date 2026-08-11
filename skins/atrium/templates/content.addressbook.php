{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by Cubecart::_addressbook().
 *
 * TWO MODES IN ONE TEMPLATE:
 *   $ADDRESSES set -> LIST mode (saved addresses + bulk delete)
 *   $CTRL_FORM  set -> FORM mode (add/edit one address)
 * Core can set both, so both blocks render independently — never {else} them.
 *
 * ⚠⚠ THE DELETE FORM AND THE EDIT FORM MUST STAY SEPARATE <form> ELEMENTS.
 * Cubecart::_addressbook() feeds raw $_POST to User::saveAddress(), which does
 * `array_map('trim', $array)`. delete[] is an ARRAY, so if it posted alongside
 * `save` the trim() would hit an array and TypeError on PHP 8 — the address
 * silently never saves. Do not merge the two forms when re-laying-out the page.
 *
 * ⚠ The edit form must post FLAT SCALAR names only, for the same reason.
 * ⚠ No `token` field; close each form with a literal </form>.
 *
 * List fields: delete[]  ·  Edit fields: description first_name last_name
 * company_name line1 line2 town state postcode country w3w billing default
 * address_id save
 * #addressbook_form is a key in the validator's `forms` map
 * (3.cubecart.validate.js).
 *}

{if isset($ADDRESSES)}
<div class="mx-auto max-w-3xl">
   <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.account.your_addressbook}</h1>

   {* FORM 1 — delete only. Keep it isolated from the edit form. *}
   <form action="{$VAL_SELF}" method="post" class="mt-6">
      <ul role="list" class="space-y-4">
         {foreach from=$ADDRESSES item=address}
         <li class="cc-card p-5{if $address.billing} border-brand-600{/if}">
            <div class="flex items-start justify-between gap-4">
               <div class="min-w-0">
                  {if $address.description}<p class="text-sm font-semibold text-ink-900">{$address.description}</p>{/if}
                  <address class="mt-1 text-sm not-italic leading-relaxed text-ink-700">
                     {$address.first_name|capitalize} {$address.last_name|capitalize}<br>
                     {if $address.company_name}{$address.company_name}<br>{/if}
                     {$address.line1|capitalize}<br>
                     {if !empty($address.line2)}{$address.line2|capitalize}<br>{/if}
                     {$address.town|upper}<br>
                     {if !empty($address.state)}{$address.state|upper}<br>{/if}
                     {$address.postcode}
                     {if $CONFIG.store_country_name!==$address.country}<br>{$address.country}{/if}
                  </address>
                  <p class="mt-2 flex flex-wrap gap-2 text-xs">
                     {if $address.billing}<span class="rounded-full bg-brand-50 px-2 py-0.5 text-brand-800">{$LANG.address.billing_address}</span>{/if}
                     {if $address.default}<span class="rounded-full bg-ink-200 px-2 py-0.5 text-ink-700">{$LANG.address.default_address|default:$LANG.common.default}</span>{/if}
                  </p>
               </div>
               <div class="flex shrink-0 flex-col items-end gap-2">
                  <a href="{$VAL_SELF}&action=edit&address_id={$address.address_id}" class="cc-btn cc-btn-secondary">{$LANG.common.edit}</a>
                  {* A billing address cannot be deleted — core disables it. *}
                  <label class="flex items-center gap-1.5 text-xs text-ink-600">
                     <input type="checkbox" name="delete[]" value="{$address.address_id}"{if $address.billing} disabled{/if}>
                     {$LANG.common.delete}
                  </label>
               </div>
            </div>
         </li>
         {/foreach}
      </ul>

      <div class="mt-6 flex flex-wrap items-center gap-3">
         <a href="{$VAL_SELF}&action=add" class="cc-btn cc-btn-secondary">{$LANG.address.address_add}</a>
         <button type="submit" class="cc-btn cc-btn-ghost !text-danger-600">{$LANG.common.delete}</button>
         {if $CHECKOUT_BUTTON}
         <a href="?_a=basket" class="cc-btn cc-btn-primary ms-auto">{$LANG.basket.basket_secure_checkout}</a>
         {else}
         <a href="?" class="cc-btn cc-btn-primary ms-auto">{$LANG.basket.continue_shopping}</a>
         {/if}
      </div>
   </form>
</div>
{/if}

{if isset($CTRL_FORM)}
<div class="mx-auto mt-10 max-w-2xl">
   <h2 class="text-lg font-semibold tracking-tight text-ink-900">
      {if $DATA.address_id>0}{$LANG.address.edit_address}{else}{$LANG.address.add_address}{/if}
   </h2>

   {* FORM 2 — add/edit. Physically separate from the delete form above. *}
   <form action="{$VAL_SELF}" method="post" id="addressbook_form" data-cc-validate class="mt-4">
      <div class="cc-card space-y-4 p-6">
         <div>
            <label for="addr_description" class="cc-label">{$LANG.common.description}</label>
            <input type="text" name="description" id="addr_description" value="{$DATA.description}" placeholder="{$LANG.address.example_address_description}">
         </div>

         <div class="grid gap-4 sm:grid-cols-2">
            <div>
               <label for="addr_first_name" class="cc-label">{$LANG.user.name_first}</label>
               <input type="text" name="first_name" id="addr_first_name" value="{$DATA.first_name|capitalize}" maxlength="32" autocomplete="given-name" required>
            </div>
            <div>
               <label for="addr_last_name" class="cc-label">{$LANG.user.name_last}</label>
               <input type="text" name="last_name" id="addr_last_name" value="{$DATA.last_name|capitalize}" maxlength="32" autocomplete="family-name" required>
            </div>
         </div>

         <div>
            <label for="addr_company_name" class="cc-label">{$LANG.address.company_name}</label>
            <input type="text" name="company_name" id="addr_company_name" value="{$DATA.company_name}" autocomplete="organization">
         </div>

         <div>
            <label for="addr_line1" class="cc-label">{$LANG.address.line1}</label>
            <input type="text" name="line1" id="addr_line1" value="{$DATA.line1|capitalize}" class="address_lookup" autocomplete="off" autocorrect="off" required
                   placeholder="{if $ADDRESS_LOOKUP}{$LANG.address.address_lookup}{/if}">
         </div>
         {if $ADDRESS_LOOKUP}
         <p id="lookup_fail"><a href="#" class="text-sm underline">{$LANG.address.address_not_found}</a></p>
         {/if}

         <div id="address_form"{if $ADDRESS_LOOKUP} class="hidden"{/if}>
            <div class="space-y-4">
               <div>
                  <label for="addr_line2" class="cc-label">{$LANG.address.line2}</label>
                  <input type="text" name="line2" id="addr_line2" value="{$DATA.line2|capitalize}" autocomplete="address-line2">
               </div>
               <div class="grid gap-4 sm:grid-cols-2">
                  <div>
                     <label for="addr_town" class="cc-label">{$LANG.address.town}</label>
                     <input type="text" name="town" id="addr_town" value="{$DATA.town|upper}" autocomplete="address-level2" required>
                  </div>
                  <div>
                     <label for="addr_postcode" class="cc-label">{$LANG.address.postcode}</label>
                     <input type="text" name="postcode" id="addr_postcode" value="{$DATA.postcode}" class="uppercase" autocomplete="postal-code" required>
                  </div>
                  <div>
                     {* rel="state-list" makes 40-checkout.js resolve the target
                        the same way it does on checkout. *}
                     <label for="country-list" class="cc-label">{$LANG.address.country}</label>
                     <select name="country" id="country-list" class="country-list" rel="state-list" autocomplete="country-name">
                        {foreach from=$COUNTRIES item=country}
                        <option value="{$country.numcode}" data-status="{$country.status}" data-iso="{$country.iso}" {$country.selected}>{$country.name}</option>
                        {/foreach}
                     </select>
                  </div>
                  <div id="state-list_wrapper">
                     <label for="state-list" class="cc-label">{$LANG.address.state}</label>
                     {* Input and select share the name; the JS enables exactly one. *}
                     <input type="text" name="state" id="state-list" value="{$DATA.state|upper}" autocomplete="address-level1">
                     <select name="state" id="state-list_select" hidden disabled></select>
                  </div>
               </div>
               {if !empty($CONFIG.w3w)}
               <div>
                  <label for="w3w" class="cc-label">{$LANG.address.w3w_address} {$LANG.common.optional}</label>
                  {include file='templates/element.w3w.php' value=$DATA.w3w as_id="w3w_as" input_id="w3w" input_name="w3w" country_id="country-list"}
               </div>
               {/if}
            </div>
         </div>

         <div class="space-y-2 border-t border-ink-200 pt-4">
            <div class="flex items-center gap-2">
               <input type="checkbox" name="billing" id="addr_billing" value="1"{if $DATA.billing} checked{/if}>
               <label for="addr_billing" class="text-sm text-ink-800">{$LANG.address.billing_address}</label>
            </div>
            <div class="flex items-center gap-2">
               <input type="checkbox" name="default" id="addr_default" value="1"{if $DATA.default} checked{/if}>
               <label for="addr_default" class="text-sm text-ink-800">{$LANG.address.default_address|default:$LANG.common.default}</label>
            </div>
         </div>
      </div>

      <input type="hidden" name="address_id" value="{$DATA.address_id}">
      <div class="mt-6 flex gap-3">
         <button type="submit" name="save" value="1" class="cc-btn cc-btn-primary">{$LANG.common.save}</button>
         <a href="{$VAL_SELF}" class="cc-btn cc-btn-secondary">{$LANG.common.cancel}</a>
      </div>
   </form>

   {* county_list drives the country -> state swap (40-checkout.js). *}
   <script>var county_list = {if !empty($VAL_JSON_STATE)}{$VAL_JSON_STATE}{else}false{/if};</script>
</div>
{/if}

<div class="hidden" id="validate_field_required">{$LANG.form.field_required}</div>
{* Read by 40-checkout.js when it builds the state placeholder. *}
<div class="hidden" id="validate_required">{$LANG.form.required}</div>
