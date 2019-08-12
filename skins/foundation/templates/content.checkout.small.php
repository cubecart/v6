<div class="show-for-small-only" id="content_checkout_small">
   {foreach from=$ITEMS key=hash item=item}
   <div class="panel" id="basket_item_{$hash}">
      <div class="row">
         <div class="small-1 columns">
            <a href="{$STORE_URL}/index.php?_a=basket&remove-item={$hash}"><svg class="icon"><use xlink:href="#icon-trash-o"></use></svg></a>
         </div>
         <div class="small-3 columns">
            <a href="{$item.link}" class="th" title="{$item.name}"><img src="{$item.image}" alt="{$item.name}"></a>
         </div>
         <div class="small-7 columns text-right">
            <a href="{$item.link}"><strong>{$item.name}</strong></a>
            {if $item.options}
            <ul class="no-bullet">
               {foreach from=$item.options item=option}
               <li><strong>{if empty($option.option_description)}{$option.option_name}{else}{$option.option_description}{/if}</strong>: {$option.value_name|truncate:45:"&hellip;":true}{if !empty($option.price_display)} ({$option.price_display}){/if}</li>
               {/foreach}
            </ul>
            {/if}
            <br>
            {$item.line_price_display}
         </div>
      </div>
      <hr>
      <div class="row">
         <div class="small-6 columns">
            {$LANG.common.quantity_abbreviated}
            <a href="#" class="quan subtract" rel="{$hash}"><svg class="icon"><use xlink:href="#icon-minus-circle"></use></svg></a>
            <span class="disp_quan_{$hash}">{$item.quantity}</span>
            <input name="quan[{$hash}]" maxlength="3" type="hidden" value="{$item.quantity}">
            <span id="original_val_{$hash}" class="hide">{$item.quantity}</span>
            <a href="#" class="quan add" rel="{$hash}"><svg class="icon"><use xlink:href="#icon-plus-circle"></use></svg></a>
         </div>
         <div class="small-6 columns text-right">
            <span class="hide">{$LANG.basket.total}</span>{$item.price_display}
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
         <td colspan="2">
            {$LANG.basket.total_sub}
         </td>
         <td width="10%" class="text-right">
            {$SUBTOTAL}
         </td>
      </tr>
      {if isset($SHIPPING)}
      <tr>
         <td width="10%" nowrap="nowrap">{$LANG.basket.shipping}
         {if $ESTIMATE_SHIPPING}
            (<a href="#" onclick="$('#getEstimateSmall').slideToggle();">{$LANG.common.refine_estimate}</a>)
            <div id="getEstimateSmall" class="hide panel callout">
               <h4>
                  <svg class="icon right" id="getEstimateClose" onclick="$('#getEstimateSmall').slideUp();"><use xlink:href="#icon-times"></use></svg>{$LANG.basket.specify_shipping}</h4>
               <div>
                  <label for="estimate_country_small" class="hide-for-small-only">{$LANG.address.country}</label>
                  <select name="estimate[country]" id="estimate_country_small" class="nosubmit country-list" rel="estimate_state_small">
                     {foreach from=$COUNTRIES item=country}<option value="{$country.numcode}" data-status="{$country.status}" {$country.selected}>{$country.name}</option>{/foreach}
                  </select>
               </div>
               <div id="estimate_state_small_wrapper">
                  <label for="estimate_state_small" class="hide-for-small-only">{$LANG.address.state}</label>
                  <input type="text" name="estimate[state]" id="estimate_state_small" value="{$ESTIMATES.state}" placeholder="{$LANG.address.state}">
               </div>
               <div>
                  <label for="estimate_postcode_small" class="hide-for-small-only">{$LANG.address.postcode}</label>
                  <input type="text" value="{$ESTIMATES.postcode}" placeholder="{$LANG.address.postcode}" id="estimate_postcode_small" name="estimate[postcode]">
               </div>
               <div>
                  <input type="submit" name="get-estimate" class="button expand" value="{$LANG.basket.fetch_shipping_rates}">
               </div>
               <script type="text/javascript">
               var county_list = {if !empty($STATE_JSON)}{$STATE_JSON}{else}false{/if};
               </script>
            </div>
            {/if}
         </td>
         <td>
            {if !isset($free_coupon_shipping)}
            <select name="shipping" class="nomarg">
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
            {/if}
         </td>
         <td width="10%" class="text-right">
            {$SHIPPING_VALUE}
         </td>
      </tr>
      {/if}
      {foreach from=$TAXES item=tax}
      <tr>
         <td colspan="2">
            {$tax.name}
         </td>
         <td width="10%" class="text-right">
            {$CUSTOMER_LOCALE.mark}{$tax.value}
         </td>
      </tr>
      {/foreach}
      {foreach from=$COUPONS item=coupon}
      <tr>
         <td colspan="2">
            {$coupon.voucher} <a href="{$VAL_SELF}&remove_code={$coupon.remove_code}" title="{$LANG.common.remove}"><svg class="icon remove-coupon"><use xlink:href="#icon-times"></use></svg></a>
         </td>
         <td width="10%" class="text-right">
            {$coupon.value}
         </td>
         </td>
         {/foreach}
         {if isset($DISCOUNT)}
      <tr>
         <td colspan="2">
            {$LANG.basket.total_discount}
         </td>
         <td width="10%" class="text-right">
            {$DISCOUNT}
         </td>
      </tr>
      {/if}
      <tr>
         <td colspan="2">
            {$LANG.basket.total_grand}
         </td>
         <td width="10%" class="text-right">
            {$TOTAL}
         </td>
      </tr>
   </table>
</div>