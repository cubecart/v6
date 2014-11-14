<!-- START MEDIUM-UP -->
<div class="show-for-medium-up">
   <table class="expand">
      <thead>
         <tr>
            <td></td>
            <td>{$LANG.common.name}</td>
            <td>{$LANG.common.price_unit}</td>
            <td>{$LANG.common.quantity}</td>
            <td>{$LANG.common.price}</td>
         </tr>
      </thead>
      <tbody>
         {foreach from=$ITEMS key=hash item=item}
         <tr>
            <td class="text-center"><a href="{$STORE_URL}/index.php?_a=basket&remove-item={$hash}"><i class="fa fa-trash-o"></i></a></td>
            <td>
               <a href="{$item.link}" class="th" title="{$item.name}"><img src="{$item.image}" width="80" alt="{$item.name}"></a>
               <a href="{$item.link}" class="txtDefault"><strong>{$item.name}</strong></a>
               {if $item.options}
               <ul class="no-bullet">
                  {foreach from=$item.options item=option}
                  <li><strong>{$option.option_name}</strong>: {$option.value_name|truncate:45:"&hellip;":true}{if !empty($option.price_display)} ({$option.price_display}){/if}</li>
                  {/foreach}
               </ul>
               {/if}
               <p>
            </td>
            <td class="text-right">{$item.line_price_display}</td>
            <td>
               <input name="quan[{$hash}]" type="text" value="{$item.quantity}" maxlength="3" class="quantity" {$QUAN_READ_ONLY}>
            </td>
            <td class="text-right">{$item.price_display}</td>
         </tr>
         {/foreach}
      </tbody>
      <tfoot>
         <tr>
            <td colspan="3">{if $BASKET_WEIGHT}
               {$LANG.basket.weight}: {$BASKET_WEIGHT}
               {/if}
            </td>
            <td>{$LANG.basket.total_sub}</td>
            <td class="text-right">{$SUBTOTAL}</td>
         </tr>
         {if isset($SHIPPING)}
         <tr>
            <td colspan="3">
               {$LANG.basket.shipping_select}:
               <select name="shipping">
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
            <td>{$LANG.basket.shipping}{$CUSTOMER_LOCALE.mark}</td>
            <td class="text-right">{$SHIPPING_VALUE}</td>
         </tr>
         {/if}
         {foreach from=$TAXES item=tax}
         <tr>
            <td colspan="3"></td>
            <td>{$tax.name}{$CUSTOMER_LOCALE.mark}</td>
            <td class="text-right">{$tax.value}</td>
         </tr>
         {/foreach}
         {foreach from=$COUPONS item=coupon}
         <tr>
            <td colspan="3"></td>
            <td><a href="{$VAL_SELF}&remove_code={$coupon.remove_code}" title="{$LANG.common.remove}">{$coupon.voucher}</a></td>
            <td class="text-right">{$coupon.value}</td>
         </tr>
         {/foreach}
         {if isset($DISCOUNT)}
         <tr>
            <td colspan="3"></td>
            <td>{$LANG.basket.total_discount}</td>
            <td class="text-right">{$DISCOUNT}</td>
         </tr>
         {/if}
         <tr>
            <td colspan="3"></td>
            <td>{$LANG.basket.total_grand}</td>
            <td class="text-right">{$TOTAL}</td>
         </tr>
      </tfoot>
   </table>
</div>
<!-- END MEDIUM-UP -->