{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * $CURRENCIES / $CURRENT_CURRENCY from GUI::_displayCurrencySwitch(). Symbols
 * are escaped with htmlall because they are raw config values that may already
 * contain entities.
 *}
{if is_array($CURRENCIES)}
<div id="box-currency" class="hidden md:block">
{if count($CURRENCIES)>1}
   <div x-data="ccDisclosure()" @click.outside="close()" @keydown.escape.window="close()" class="relative">
      <button type="button" @click="toggle()" :aria-expanded="open ? 'true' : 'false'"
              class="cc-btn cc-btn-ghost gap-1" rel="nofollow">
         <span class="tabular">{$CURRENT_CURRENCY.symbol_left|escape:'htmlall'} {$CURRENT_CURRENCY.code} {$CURRENT_CURRENCY.symbol_right|escape:'htmlall'}</span>
         <svg class="size-4 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m19.5 8.25-7.5 7.5-7.5-7.5" stroke-linecap="round" stroke-linejoin="round"/>
         </svg>
      </button>
      <ul x-show="open" x-cloak x-transition.opacity.duration.150ms
          class="absolute end-0 z-40 mt-2 w-56 rounded-cc-lg border border-ink-200 bg-ink-100 py-1.5 shadow-lg">
         {foreach from=$CURRENCIES item=currency}
         {if $currency.code!==$CURRENT_CURRENCY.code}
         <li><a href="{$currency.url}" rel="nofollow" class="block px-4 py-2 text-sm text-ink-700 hover:bg-ink-200">
            <span class="tabular">{$currency.symbol_left|escape:'htmlall'} {$currency.code} {$currency.symbol_right|escape:'htmlall'}</span>
            <span class="text-ink-500">({$currency.name})</span>
         </a></li>
         {/if}
         {/foreach}
      </ul>
   </div>
{else}
   <span class="px-2 text-sm text-ink-500 tabular">{$CURRENT_CURRENCY.symbol_left|escape:'htmlall'} {$CURRENT_CURRENCY.code} {$CURRENT_CURRENCY.symbol_right|escape:'htmlall'}</span>
{/if}
</div>
{/if}
