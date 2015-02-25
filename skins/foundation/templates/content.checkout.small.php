<!-- START SMALL ONLY -->
<div class="show-for-small-only">
   {foreach from=$ITEMS key=hash item=item}
   <div class="panel" id="basket_item_{$hash}">
      <div class="row">
         <div class="small-4 columns">
            <a href="{$item.link}" class="th" title="{$item.name}"><img src="{$item.image}" alt="{$item.name}"></a>
         </div>
         <div class="small-8 columns">
            <a href="{$item.link}"><strong>{$item.name}</strong></a>
            {if $item.options}
            <ul class="no-bullet">
               {foreach from=$item.options item=option}
               <li><strong>{$option.option_name}</strong>: {$option.value_name|truncate:45:"&hellip;":true}{if !empty($option.price_display)} ({$option.price_display}){/if}</li>
               {/foreach}
            </ul>
            {/if}
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
                  <option value="{$method.value}" {$method.selected}>{$CUSTOMER_LOCALE.mark} {$method.display}</option>
                  {/foreach}
                  {if $HIDE_OPTION_GROUPS ne '1'}
               </optgroup>
               {/if}
               {/foreach}
            </select>
         </td>
         <td width="10%" class="text-right">
            {$CUSTOMER_LOCALE.mark}{$SHIPPING_VALUE}
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