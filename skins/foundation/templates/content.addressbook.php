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
{if isset($ADDRESSES)}
<h2>{$LANG.address.your_address_book}</h2>
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
   {foreach from=$ADDRESSES item=address}
   <div class="panel {if $address.billing}callout{/if}">
      <div class="row">
         <div class="small-12 columns">
            <h5><a href="?_a=addressbook&action=edit&address_id={$address.address_id}">{$address.description}</a></h5>
         </div>
      </div>
      <div class="row">
         <div class="small-6 medium-4 columns">
            {if !empty($address.title)}{$address.title|capitalize} {/if}{$address.first_name|capitalize} {$address.last_name|capitalize}<br/>
            {$address.line1|capitalize}<br/>
            {if !empty($address.line2)} {$address.line2|capitalize}<br/>{/if}
            {$address.town|upper}<br/>
            {if !empty($address.state)}{$address.state|upper}<br/>{/if}
            {$address.postcode}{if $CONFIG['store_country_name']!==$address['country']}<br>
            {$address.country}{/if}
         </div>
         <div class="small-6 columns">
            <table>
               <tr>
                  <td>{$LANG.address.billing_address}</td>
                  <td><svg class="icon"><use xlink:href="#icon-{if $address.billing}check{else}times{/if}"></use></svg></td>
               </tr>
               <tr>
                  <td>{$LANG.address.default_delivery_address}</td>
                  <td><svg class="icon"><use xlink:href="#icon-{if $address.default}check{else}times{/if}"></use></svg></td>
               </tr>
               <tr>
                  <td>{$LANG.address.delivery_address}</td>
                  <td><svg class="icon"><use xlink:href="#icon-check"></use></svg></td>
               </tr>
            </table>
         </div>
         <div class="medium-2 columns text-center">
            <a href="?_a=addressbook&action=edit&address_id={$address.address_id}" class="button tiny expand">{$LANG.common.edit}</a>
            <br><input type="checkbox" name="delete[]" value="{$address.address_id}"{if $address.billing} disabled{/if}>
         </div>
      </div>
   </div>
   {/foreach}
   <div class="clearfix">
      <button type="submit" class="button alert right">{$LANG.common.delete_selected}</button>
      {if $CHECKOUT_BUTTON}<a href="?_a=basket" class="button success right show-for-medium-up">{if $CONFIG.ssl == 1}{$LANG.basket.basket_secure_checkout}{else}{$LANG.basket.basket_checkout}{/if}</a>{else}<a href="?" class="button success right show-for-medium-up">{$LANG.basket.continue_shopping}</a>{/if}
      <div class="left"><a href="{$STORE_URL}/index.php?_a=addressbook&action=add" class="button">{$LANG.address.address_add}</a></div>
   </div>
</form>
{/if}
{if isset($CTRL_FORM)}
<h2>{if $DATA.address_id>0}{$LANG.address.edit_address}{else}{$LANG.address.add_address}{/if}</h2>
<form action="{$VAL_SELF}" method="post" id="addressbook_form" enctype="multipart/form-data">
   <div class="row">
      <div class="small-12 large-8 columns"><label for="addr_description">{$LANG.common.description}</label><input type="text" name="description" id="addr_description" value="{$DATA.description}" placeholder="{$LANG.address.example_address_description}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="addr_title">{$LANG.user.title}</label><input type="text" name="title" id="addr_title" value="{$DATA.title}" placeholder="{$LANG.user.title}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="addr_first_name">{$LANG.user.name_first}</label><input type="text" name="first_name" id="addr_first_name" value="{$DATA.first_name|capitalize}" required placeholder="{$LANG.user.name_first} {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="addr_last_name">{$LANG.user.name_last}</label><input type="text" name="last_name" id="addr_last_name" value="{$DATA.last_name|capitalize}" required placeholder="{$LANG.user.name_last} {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="addr_company_name">{$LANG.address.company_name}</label><input type="text" name="company_name" id="addr_company_name" value="{$DATA.company_name}" placeholder="{$LANG.address.company_name}"></div>
   </div>
   <address>
      <div class="row">
         <div class="small-12 large-8 columns"><label for="addr_line1">{$LANG.address.line1} </label><input type="text" name="line1" id="addr_line1" value="{$DATA.line1|capitalize}" required placeholder="{if $ADDRESS_LOOKUP}{$LANG.address.address_lookup}{else}{$LANG.address.line1} {$LANG.form.required}{/if}" autocomplete="off" autocorrect="off" class="address_lookup">
         </div>
      </div>
      {if $ADDRESS_LOOKUP}<p id="lookup_fail"><a href="#">{$LANG.address.address_not_found}</a></p>{/if}
      <div{if $ADDRESS_LOOKUP} class="hide"{/if} id="address_form">
      <div class="row">
         <div class="small-12 large-8 columns"><label for="addr_line2">{$LANG.address.line2}</label><input type="text" name="line2" id="addr_line2" value="{$DATA.line2|capitalize}" placeholder="{$LANG.address.line2}"></div>
      </div>
      <div class="row">
         <div class="small-12 large-8 columns"><label for="addr_town">{$LANG.address.town}</label><input type="text" name="town" id="addr_town" value="{$DATA.town|upper}" required placeholder="{$LANG.address.town} {$LANG.form.required}"></div>
      </div>
      <div class="row">
         <div class="small-12 large-8 columns">
            <label for="country-list">{$LANG.address.country}</label><select name="country" id="country-list">
            {foreach from=$COUNTRIES item=country}
            <option value="{$country.numcode}" data-status="{$country.status}" {$country.selected}>{$country.name}</option>
            {/foreach}</select>
         </div>
      </div>
      <div class="row" id="state-list_wrapper">
         <div class="small-12 large-8 columns"><label for="state-list">{$LANG.address.state}</label><input type="text" name="state" id="state-list" required value="{$DATA.state|upper}" placeholder="{$LANG.address.state} {$LANG.form.required}"></div>
      </div>
      <div class="row">
         <div class="small-12 large-8 columns"><label for="addr_postcode">{$LANG.address.postcode}</label><input type="text" name="postcode" id="addr_postcode" value="{$DATA.postcode}" required placeholder="{$LANG.address.postcode} {$LANG.form.required}"></div>
      </div>
      </div>
   </address>
   <div class="row">
      <div class="small-12 large-8 columns"><input name="billing" type="checkbox" id="addr_billing" value="1" {$DATA.billing}><label for="addr_billing">{$LANG.address.billing_address}</label> </div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><input name="default" type="checkbox" id="addr_default" value="1" {$DATA.default}> <label for="addr_default">{$LANG.address.default_delivery_address}</label></div>
   </div>
   <div class="row clearfix">
      <div class="small-12 large-8 columns">
         <input type="hidden" name="address_id" value="{$DATA.address_id}">
         <input type="submit" name="save" value="{$LANG.common.save}" class="button success left">
         <a href="index.php?_a={$REDIR}"class="button alert left">{$LANG.common.cancel}</a>
         <button type="reset" class="button secondary right"><svg class="icon"><use xlink:href="#icon-refresh"></use></svg> {$LANG.common.reset}</button>
      </div>
   </div>
   <div class="hide" id="validate_field_required">{$LANG.form.field_required}</div>
</form>
<script type="text/javascript">
   var county_list = {if !empty($VAL_JSON_STATE)}{$VAL_JSON_STATE}{else}false{/if};
</script>
{/if}