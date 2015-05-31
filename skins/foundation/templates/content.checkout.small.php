<!-- START SMALL ONLY -->
<div class="show-for-small-only">
   {foreach from=$ITEMS key=hash item=item}
   <div class="panel" id="basket_item_{$hash}">
      <div class="row">
         <div class="small-3 columns">
            <a href="{$item.link}" class="th" title="{$item.name}"><img src="{$item.image}" alt="{$item.name}" width="50"></a>
         </div>
         <div class="small-6 columns">
            <a href="{$item.link}"><strong>{$item.name}</strong></a>
            {if $item.options}
            <ul class="no-bullet">
               {foreach from=$item.options item=option}
               <li><strong>{$option.option_name}</strong>: {$option.value_name|truncate:45:"&hellip;":true}{if !empty($option.price_display)} ({$option.price_display}){/if}</li>
               {/foreach}
            </ul>
            {/if}
         </div>
         <div class="small-3 columns">
         {$item.line_price_display}
         </div>
      </div>
      <hr>
      <div class="row">
         <div class="small-2 columns">
            {$LANG.common.quantity_abbreviated}
         </div>
         <div class="small-4 columns">
            <a href="#" class="quan subtract" rel="{$hash}"><i class="fa fa-minus-circle"></i></a>
            <span class="disp_quan_{$hash}">{$item.quantity}</span>
            <input name="quan[{$hash}]" class="field_small_only" type="hidden" value="{$item.quantity}">
            <span id="original_val_{$hash}" class="hide">{$item.quantity}</span>
            <a href="#" class="quan add" rel="{$hash}"><i class="fa fa-plus-circle"></i></a>
         </div>
         <div class="small-3 columns">
            {$LANG.basket.total}
         </div>
         <div class="small-3 columns">
            {$item.price_display}
         </div>
      </div>
      <div class="row hide" id="quick_update_{$hash}">
         <div class="small-offset-3 small-9 columns">
            <button type="submit" name="update" class="button secondary tiny marg-top" value="{$LANG.basket.basket_update}">{$LANG.common.update}</button>
         </div>
      </div>
   </div>
   {/foreach}
   <table class="expand">
      <tr>
         <td>
            {$LANG.basket.total_sub}
         </td>
         <td width="10%" class="text-right">
            {$SUBTOTAL}
         </td>
      </tr>
      {if isset($SHIPPING)}
      <tr>
         <td>
            <select name="shipping" class="field_small_only">
               <option value="">{$LANG.form.please_select}</option>
               {foreach from=$SHIPPING key=group item=methods}
               {if $HIDE_OPTION_GROUPS ne '1'}
               <optgroup label="{$group}">{/if}
                  {foreach from=$methods item=method}
                  <option value="{$method.value}" {$method.selected}>{$method.display}</option>
                  {/foreach}
                  {if $HIDE_OPTION_GROUPS ne '1'}
               </optgroup>
               {/if}
               {/foreach}
            </select>
         </td>
         <td width="10%" class="text-right">
            {$SHIPPING_VALUE}
            {if $ESTIMATE_SHIPPING}
               (<a href="#" onclick="$('#getEstimateSmall').slideToggle();">{$LANG.common.estimated}</a>)
               <div id="getEstimateSmall" class="hide panel callout">
                  <h4>{$LANG.basket.specify_shipping}</h4>
                  <label for="estimate_country_small" class="hide-for-small-only">{$LANG.address.country}</label>
                  <select name="estimate[country]" id="estimate_country_small" class="nosubmit country-list field_small_only" rel="estimate_state_small">
                     {foreach from=$COUNTRIES item=country}<option value="{$country.numcode}" {$country.selected}>{$country.name}</option>{/foreach}
                  </select>
                  <label for="estimate_state_small" class="hide-for-small-only">{$LANG.address.state}</label>
                  <input type="text" name="estimate[state]" id="estimate_state_small" value="{$ESTIMATES.state}" class="field_small_only" placeholder="{$LANG.address.state}">
                  <label for="estimate_postcode_small" class="hide-for-small-only">{$LANG.address.postcode}</label>
                  <input type="text" class="field_small_only" value="{$ESTIMATES.postcode}" placeholder="{$LANG.address.postcode}" id="estimate_postcode_small" name="estimate[postcode]">
                  <input type="submit" name="get-estimate" class="button expand field_small_only" value="{$LANG.basket.fetch_shipping_rates}">
                  <script type="text/javascript">
                  var county_list = {$STATE_JSON};
                  </script>
               </div>
               {/if}
         </td>
      </tr>
      {/if}
      {foreach from=$TAXES item=tax}
      <tr>
         <td>
            {$tax.name}
         </td>
         <td width="10%" class="text-right">
            {$CUSTOMER_LOCALE.mark}{$tax.value}
         </td>
      </tr>
      {/foreach}
      {foreach from=$COUPONS item=coupon}
      <tr>
         <td>
            <a href="{$VAL_SELF}&remove_code={$coupon.remove_code}" title="{$LANG.common.remove}">{$coupon.voucher}</a>
         </td>
         <td width="10%" class="text-right">
            {$coupon.value}
         </td>
         </td>
         {/foreach}
         {if isset($DISCOUNT)}
      <tr>
         <td>
            {$LANG.basket.total_discount}
         </td>
         <td width="10%" class="text-right">
            {$DISCOUNT}
         </td>
      </tr>
      {/if}
      <tr>
         <td>
            {$LANG.basket.total_grand}
         </td>
         <td width="10%" class="text-right">
            {$TOTAL}
         </td>
      </tr>
   </table>
</div>
<!-- END SMALL ONLY -->