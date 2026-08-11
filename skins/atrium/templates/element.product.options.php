{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ FIELD NAMES ARE A CORE CONTRACT — Cart::add() parses them. Do not rename:
 *   radio / select / single-required-hidden :  productOptions[<option_id>]
 *   textbox / textarea                      :  productOptions[<option_id>][<assign_id>]
 *
 * ⚠ DATA ATTRIBUTES ARE A CONTRACT with 20-product.js:
 *   data-price           option's price delta in decimal (never formatted)
 *   data-price-original  the same before any sale discount
 *   data-image           swaps #img-preview when this value is chosen
 *   class="absolute"     the option REPLACES the base price instead of adding
 *                        to it; the JS subtracts the base first
 *
 * $option.type is compared against Catalogue class constants. Keep the
 * constant, do not inline its integer value, or an upstream renumbering breaks
 * silently.
 *
 * A required option with exactly one value renders as a hidden input: no choice
 * to make, but the value must still post.
 *
 * CHECKBOX, HIDDEN, PASSWORD, DATEPICKER and FILE types are configurable in
 * admin but fall through to $OTHER_CHOOSERS, rendering nothing unless a plugin
 * supplies markup.
 *}
{if is_array($OPTIONS)}
<div class="mt-6 space-y-5">
   {foreach from=$OPTIONS item=option}

   {if $option.type == Catalogue::OPTION_RADIO}
   <fieldset>
      {if $option.required && count($option.values)===1}
      <span class="cc-label">{$option.option_name}</span>
      <p class="text-sm text-ink-700">
         {$option.values.0.value_name}{if $option.values.0.price} <span class="price">{$option.values.0.symbol}{$option.values.0.price}</span>{/if}
      </p>
      <input type="hidden" name="productOptions[{$option.option_id}]" id="option_{$option.option_id}" value="{$option.values.0.assign_id}"{if !$CTRL_HIDE_PRICES} data-price="{$option.values.0.decimal_price}" data-price-original="{$option.values.0.decimal_price_original}"{/if}>
      {else}
      <legend class="cc-label" id="error_option_{$option.option_id}">{$option.option_name}{if $option.required} <span class="text-danger-600">({$LANG.common.required})</span>{/if}</legend>
      <div class="space-y-1.5">
         {foreach from=$option.values item=value name=options}
         <div class="flex items-center gap-2">
            <input type="radio" name="productOptions[{$option.option_id}]" id="rad_option_{$value.assign_id}" value="{$value.assign_id}"
                   class="{if $value.absolute_price == '1'}absolute{/if}"
                   @change="onOptionChange($event)"
                   {if empty($_POST) && !empty($value.option_default)}checked="checked"{/if}
                   {if !$CTRL_HIDE_PRICES}data-price="{$value.decimal_price}" data-price-original="{$value.decimal_price_original}"{/if}
                   {if $smarty.foreach.options.first}rel="error_option_{$option.option_id}" {if $option.required}required{/if}{/if}
                   data-image="{$value.image}">
            <label for="rad_option_{$value.assign_id}" class="text-sm text-ink-800">
               {$value.value_name}{if $value.price} <span class="price text-ink-600">{$value.symbol}{$value.price}</span>{/if}
            </label>
         </div>
         {/foreach}
      </div>
      {/if}
   </fieldset>

   {elseif $option.type == Catalogue::OPTION_SELECT}
   <div>
      {if $option.required && count($option.values)===1}
      <span class="cc-label">{$option.option_name}</span>
      <p class="text-sm text-ink-700">
         {$option.values.0.value_name}{if $option.values.0.price} <span class="price">{$option.values.0.symbol}{$option.values.0.price}</span>{/if}
      </p>
      <input type="hidden" name="productOptions[{$option.option_id}]" id="option_{$option.option_id}" value="{$option.values.0.assign_id}"{if !$CTRL_HIDE_PRICES} data-price="{$option.values.0.decimal_price}" data-price-original="{$option.values.0.decimal_price_original}"{/if}>
      {else}
      <label for="option_{$option.option_id}" class="cc-label">{$option.option_name}{if $option.required} <span class="text-danger-600">({$LANG.common.required})</span>{/if}</label>
      <select name="productOptions[{$option.option_id}]" id="option_{$option.option_id}" @change="onOptionChange($event)" {if $option.required}required{/if}>
         <option value="">{$LANG.form.please_select}</option>
         {foreach from=$option.values item=value}
         <option value="{$value.assign_id}"{if $value.absolute_price == '1'} class="absolute"{/if}{if empty($_POST) && !empty($value.option_default)} selected="selected"{/if}{if !$CTRL_HIDE_PRICES} data-price="{$value.decimal_price}" data-price-original="{$value.decimal_price_original}"{/if} data-image="{$value.image}">{$value.value_name}{if $value.price} {$value.symbol}{$value.price}{/if}</option>
         {/foreach}
      </select>
      {/if}
   </div>

   {elseif $option.type == Catalogue::OPTION_TEXTBOX || $option.type == Catalogue::OPTION_TEXTAREA}
   <div>
      <label for="option_{$option.option_id}" class="cc-label">
         {$option.option_name}{if $option.price} <span class="price text-ink-600">{$option.symbol}{$option.price}</span>{/if}{if $option.required} <span class="text-danger-600">({$LANG.common.required})</span>{/if}
      </label>
      {if $option.type == Catalogue::OPTION_TEXTBOX}
      <input type="text" name="productOptions[{$option.option_id}][{$option.assign_id}]" id="option_{$option.option_id}"
             @input.debounce.400ms="recalc()"
             {if $option.absolute_price == '1'}class="absolute"{/if}
             {if !$CTRL_HIDE_PRICES}data-price="{$option.decimal_price}" data-price-original="{$option.decimal_price_original}"{/if}
             {if $option.required}required{/if}>
      {else}
      <textarea name="productOptions[{$option.option_id}][{$option.assign_id}]" id="option_{$option.option_id}" rows="3"
                @input.debounce.400ms="recalc()"
                {if $option.absolute_price == '1'}class="absolute"{/if}
                {if !$CTRL_HIDE_PRICES}data-price="{$option.decimal_price}" data-price-original="{$option.decimal_price_original}"{/if}
                {if $option.required}required{/if}></textarea>
      {/if}
   </div>

   {elseif $OTHER_CHOOSERS}
   {include file='templates/element.product.other_choosers.php'}
   {/if}

   {/foreach}
</div>
{/if}
