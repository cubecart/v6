{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
{if is_array($CURRENCIES)}
<div class="right text-center show-for-medium-up" id="box-currency">
   {if count($CURRENCIES)>1}
   <a href="#" data-dropdown="currency-switch" class="button trans small" rel="nofollow">{$CURRENT_CURRENCY.symbol_left|escape:'htmlall'} {$CURRENT_CURRENCY.code} {$CURRENT_CURRENCY.symbol_right|escape:'htmlall'}</a>
   <ul id="currency-switch" data-dropdown-content class="f-dropdown">
      {foreach from=$CURRENCIES item=currency}
      {if $currency.code!==$CURRENT_CURRENCY.code}
      <li class="text-left"><a href="{$currency.url}" rel="nofollow">{$currency.symbol_left|escape:'htmlall'} {$currency.code} {$currency.symbol_right|escape:'htmlall'} ({$currency.name})</a></li>
      {/if}
      {/foreach}
   </ul>
   {else}
   	<span class="button trans small">{$CURRENT_CURRENCY.symbol_left|escape:'htmlall'} {$CURRENT_CURRENCY.code} {$CURRENT_CURRENCY.symbol_right|escape:'htmlall'}</span>
   {/if}
</div>
{/if}