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
{if is_array($product.options)}
{foreach from=$product.options item=option}
{if $option.type == '0' || $option.type == '4'}
<div class="dymanic_options">
   <label for="option_{$option.option_id}" class="return">{$option.option_name}{if $option.price} ({$option.symbol}{$option.price}){/if}{if $option.required} ({$LANG.common.required}){/if}</label><br>
   <span rel="{$product.id}">
      <select name="inv[{$product.id}][productOptions][{$option.option_id}]" id="option_{$product.id}_{$option.option_id}" class="nomarg options_calc">
      <option value="">{$LANG.form.please_select}</option>
      {foreach from=$option.values item=value}
      <option value="{$value.assign_id}"{if $value.selected} selected="selected"{/if} rel="{$value.symbol}{$value.decimal_price}">{$value.value_name}{if $value.price} ({$value.symbol}{$value.price}){/if}</option>
      {/foreach}
      </select>
   </span>
</div>
{else}
<div class="dymanic_options">
   <label for="option_{$option.option_id}" class="return">{$option.option_name}{if $option.required}  ({$LANG.common.required}){/if}</label><br>
   <span rel="{$product.id}">
   {if $option.type == '1'}
   <input type="text" class="text_calc" placeholder="{if $option.price}({$option.symbol}{$option.price}){/if}" name="inv[{$product.id}][productOptions][{$option.option_id}][{$option.assign_id}]" id="option_{$product.id}_{$option.option_id}" value="{$option.value}" rel="{$option.symbol}{$option.decimal_price}">
   {elseif $option.type == '2'}
   <textarea  class="text_calc" name="inv[{$product.id}][productOptions][{$option.option_id}][{$option.assign_id}]" rel="{$option.symbol}{$option.decimal_price}" placeholder="{if $option.price}({$option.symbol}{$option.price}){/if}" id="option_{$product.id}_{$option.option_id}">{$option.value}</textarea>
   {/if}
   </span>
</div>
{/if}
{/foreach}
{/if}
{if is_array($product.custom)}
   {foreach from=$product.custom key=k item=v}
   {$k}<br>
   {if $k=='Message'}
   <textarea name="inv[{$product.id}][custom][{$k}]">{$v}</textarea>
   {elseif $k=='Method'}
   <select name="inv[{$product.id}][custom][{$k}]">
      <option{if $v=='Email'} selected="selected"{/if} value="Email">Email</option>
      <option{if $v=='Post'} selected="selected"{/if} value="Post">Post</option>
   </select>
   {else}
   <input type="text" name="inv[{$product.id}][custom][{$k}]" class="textbox" value="{$v}">
   {/if}
   <br>
   {/foreach}
{/if}