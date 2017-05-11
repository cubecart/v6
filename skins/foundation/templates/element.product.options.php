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
{if is_array($OPTIONS)}
   {foreach from=$OPTIONS item=option}
      {if $option.type == Catalogue::OPTION_RADIO}
      <div class="row">
         <div class="small-12 columns">
            {* If we only have one required option replace with hidden field *}
            {if $option.required && count($option.values)===1}
            <label for="option_{$option.option_id}" class="return">{if empty($option.option_description)}{$option.option_name}{else}{$option.option_description}{/if}</label>
            {$option.values.0.value_name}{if $option.values.0.price} {$option.values.0.symbol}{$option.values.0.price}{/if}
            <input type="hidden" name="productOptions[{$option.option_id}]" id="option_{$option.option_id}" value="{$option.values.0.assign_id}"{if !$CTRL_HIDE_PRICES} data-price="{$option.values.0.decimal_price}"{/if}>
            {else}
            <div class="pseudo-label">{if empty($option.option_description)}{$option.option_name}{else}{$option.option_description}{/if}{if $option.required} ({$LANG.common.required}){/if}</div>
            <span id="error_option_{$option.option_id}">
               {foreach from=$option.values item=value name=options}
               <div><input type="radio" name="productOptions[{$option.option_id}]" id="rad_option_{$value.assign_id}" value="{$value.assign_id}" class="nomarg{if $value.absolute_price == '1'} absolute{/if}"{if empty($_POST) && !empty($value.option_default)} checked="checked"{/if}{if !$CTRL_HIDE_PRICES} data-price="{$value.decimal_price}"{/if}{if $smarty.foreach.options.first} rel="error_option_{$option.option_id}" {if $option.required}required{/if}{/if}>
                  <label for="rad_option_{$value.assign_id}" class="return">{$value.value_name}{if $value.price} {$value.symbol}{$value.price}{/if}</label>
               </div>
               {/foreach}
            </span>
            {/if}
         </div>
      </div>
      {elseif $option.type == Catalogue::OPTION_SELECT}
      <div class="row">
         <div class="small-12 columns">
            {* If we only have one required option replace with hidden field *}
            {if $option.required && count($option.values)===1}
            <label for="option_{$option.option_id}" class="return">{if empty($option.option_description)}{$option.option_name}{else}{$option.option_description}{/if}</label>
            {$option.values.0.value_name}{if $option.values.0.price} {$option.values.0.symbol}{$option.values.0.price}{/if}
            <input type="hidden" name="productOptions[{$option.option_id}]" id="option_{$option.option_id}" value="{$option.values.0.assign_id}"{if !$CTRL_HIDE_PRICES} data-price="{$option.values.0.decimal_price}"{/if}>
            {else}
            <label for="option_{$option.option_id}" class="return">{if empty($option.option_description)}{$option.option_name}{else}{$option.option_description}{/if}{if $option.required} ({$LANG.common.required}){/if}</label>
            <select name="productOptions[{$option.option_id}]" id="option_{$option.option_id}" class="nomarg" {if $option.required}required{/if}>
            <option value="">{$LANG.form.please_select}</option>
            {foreach from=$option.values item=value}
            <option value="{$value.assign_id}"{if $value.absolute_price == '1'}class="absolute"{/if}{if empty($_POST) && !empty($value.option_default)} selected="selected"{/if}{if !$CTRL_HIDE_PRICES} data-price="{$value.decimal_price}"{/if}>{$value.value_name}{if $value.price} {$value.symbol}{$value.price}{/if}</option>
            {/foreach}
            </select>
            {/if}
         </div>
      </div>
      {else}
      <div class="row">
         <div class="small-12 columns">
            <label for="option_{$option.option_id}" class="return">{if empty($option.option_description)}{$option.option_name}{else}{$option.option_description}{/if}{if $option.price} {$option.symbol}{$option.price}{/if}{if $option.required} ({$LANG.common.required}){/if}</label>
            {if $option.type == Catalogue::OPTION_TEXTBOX}
            <input type="text" name="productOptions[{$option.option_id}][{$option.assign_id}]" id="option_{$option.option_id}"{if $option.absolute_price == '1'} class="absolute"{/if}{if !$CTRL_HIDE_PRICES} data-price="{$option.decimal_price}"{/if} {if $option.required}required{/if}>
            {elseif $option.type == Catalogue::OPTION_TEXTAREA}
            <textarea name="productOptions[{$option.option_id}][{$option.assign_id}]" id="option_{$option.option_id}"{if $option.absolute_price == '1'} class="absolute"{/if}{if !$CTRL_HIDE_PRICES} data-price="{$option.decimal_price}"{/if} {if $option.required}required{/if}></textarea>
            {/if}
         </div>
      </div>
      {/if}
   {/foreach}
{/if}