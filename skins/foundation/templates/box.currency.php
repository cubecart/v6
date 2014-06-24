<a href="#" data-dropdown="currency-switch" class="button white small">{$CURRENT_CURRENCY.symbol_left} {$CURRENT_CURRENCY.code} {$CURRENT_CURRENCY.symbol_right}</a><br>
<ul id="currency-switch" data-dropdown-content class="f-dropdown">
  {foreach from=$CURRENCIES item=currency}
  {if $currency.code!==$CURRENT_CURRENCY.code}
  <li class="text-left"><a href="{$currency.url}">{$currency.symbol_left} {$currency.code} {$currency.symbol_right} ({$currency.name})</a></li>
  {/if}
  {/foreach}
</ul>