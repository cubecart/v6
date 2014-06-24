{if isset($ADDRESSES)}
<h2>{$LANG.address.your_address_book}</h2>
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
   {foreach from=$ADDRESSES item=address}
   <div class="panel">
      <div class="row">
         <div class="small-4 columns">
            <a href="?_a=addressbook&action=edit&address_id={$address.address_id}">{$address.description}</a><br>
            {$address.line1},<br/>{if !empty($address.line2)} {$address.line2},<br/>{/if} {$address.town},<br/> {$address.state},<br/> {$address.postcode}<br>{$address.country}
         </div>
         <div class="small-6 columns">
            <div class="row">
               <div class="small-4 columns text-center">
                  {$LANG.address.billing_address}
               </div>
               <div class="small-4 columns text-center">
                  {$LANG.address.delivery_address}
               </div>
               <div class="small-4 columns text-center">
                  {$LANG.address.delivery_address} ({$LANG.common.default})
               </div>
            </div>
            <div class="row pad-top">
               <div class="small-4 columns text-center">
                  <i class="fa fa-{if $address.billing}check{else}times{/if}"></i>
               </div>
               <div class="small-4 columns text-center">
                  <i class="fa fa-check"></i>
               </div>
               <div class="small-4 columns text-center">
                  <i class="fa fa-{if $address.default}check{else}times{/if}"></i>
               </div>
            </div>
         </div>
         <div class="small-2 columns text-center">
            <a href="?_a=addressbook&action=edit&address_id={$address.address_id}" class="button tiny expand">{$LANG.common.edit}</a>
            <br><input type="checkbox" name="delete[]" value="{$address.address_id}"{if $address.billing} disabled{/if}>
         </div>
      </div>
   </div>
   {/foreach}
   <div class="clearfix">
      <div class="right"><button type="submit" class="button alert"><i class="fa fa-trash-o"></i> {$LANG.common.delete_selected}</button></div>
      <div class="left"><a href="{$STORE_URL}/index.php?_a=addressbook&action=add" class="button"><i class="fa fa-plus"></i> {$LANG.address.address_add}</a></div>
   </div>
</form>
{/if}
{if isset($CTRL_FORM)}
<h2>{if $DATA.address_id>0}{$LANG.address.edit_address}{else}{$LANG.address.add_address}{/if}</h2>
<form action="{$VAL_SELF}" method="post" id="addressbook_form" enctype="multipart/form-data">
   <div class="row">
      <div class="small-12 large-8 columns"><label for="addr_description">{$LANG.common.description}</label><input type="text" name="description" id="addr_description" value="{$DATA.description}" required placeholder="{$LANG.address.example_address_description} {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="addr_title">{$LANG.user.title}</label><input type="text" name="title" id="addr_title" value="{$DATA.title}" placeholder="{$LANG.user.title}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="addr_first_name">{$LANG.user.name_first}</label><input type="text" name="first_name" id="addr_first_name" value="{$DATA.first_name}" required placeholder="{$LANG.user.name_first} {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="addr_last_name">{$LANG.user.name_last}</label><input type="text" name="last_name" id="addr_last_name" value="{$DATA.last_name}" required placeholder="{$LANG.user.name_last} {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="addr_company_name">{$LANG.address.company_name}</label><input type="text" name="company_name" id="addr_company_name" value="{$DATA.company_name}" placeholder="{$LANG.address.company_name}"></div>
   </div>
   <address>
      <div class="row">
         <div class="small-12 large-8 columns"><label for="addr_line1">{$LANG.address.line1} </label><input type="text" name="line1" id="addr_line1" value="{$DATA.line1}" required placeholder="{if $ADDRESS_LOOKUP}{$LANG.address.address_lookup}{else}{$LANG.address.line1} {$LANG.form.required}{/if}" autocomplete="off" autocorrect="off" class="address_lookup">
         </div>
      </div>
      {if $ADDRESS_LOOKUP}<p id="lookup_fail"><a href="#">{$LANG.address.address_not_found}</a></p>{/if}
      <div{if $ADDRESS_LOOKUP} class="hide"{/if} id="address_form">
      <div class="row">
         <div class="small-12 large-8 columns"><label for="addr_line2">{$LANG.address.line2}</label><input type="text" name="line2" id="addr_line2" value="{$DATA.line2}" placeholder="{$LANG.address.line2}"></div>
      </div>
      <div class="row">
         <div class="small-12 large-8 columns"><label for="addr_town">{$LANG.address.town}</label><input type="text" name="town" id="addr_town" value="{$DATA.town}" required placeholder="{$LANG.address.town} {$LANG.form.required}"></div>
      </div>
      <div class="row">
         <div class="small-12 large-8 columns">
            <label for="country-list">{$LANG.address.country}</label><select name="country" id="country-list">
            {foreach from=$COUNTRIES item=country}
            <option value="{$country.numcode}" {$country.selected}>{$country.name}</option>
            {/foreach}</select>
         </div>
      </div>
      <div class="row">
         <div class="small-12 large-8 columns"><label for="state-list">{$LANG.address.state}</label><input type="text" name="state" id="state-list" required value="{$DATA.state}" placeholder="{$LANG.address.state} {$LANG.form.required}"></div>
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
         <input type="submit" name="save" value="{$LANG.common.save}" class="button left"> <button type="reset" class="button secondary right"><i class="fa fa-refresh"></i> {$LANG.common.reset}</button>
      </div>
   </div>
   <div class="hide" id="validate_field_required">{$LANG.form.field_required}</div>
</form>
<script type="text/javascript">
   var county_list = {$VAL_JSON_STATE}
</script>
{/if}