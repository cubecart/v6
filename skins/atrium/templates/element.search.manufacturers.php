{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * One picker that adapts to the data: a checkbox grid up to 20 manufacturers,
 * a filterable scrolling list beyond that.
 *
 * ⚠ Field name search[manufacturer][] is a core contract.
 *}
{if $MANUFACTURERS}
{if count($MANUFACTURERS) > 20}
<div x-data="ccFilterList()" class="mt-2">
   <label for="manufacturer_filter" class="cc-sr-only">{$LANG.common.search}</label>
   <input type="search" id="manufacturer_filter" x-model="q" placeholder="{$LANG.common.search}&hellip;" autocomplete="off">
   <ul role="list" class="mt-2 max-h-64 space-y-1 overflow-y-auto rounded-cc border border-ink-200 p-3">
      {foreach from=$MANUFACTURERS item=manufacturer}
      <li class="flex items-center gap-2" x-show="match('{$manufacturer.name|escape:'javascript'}')">
         <input type="checkbox" value="{$manufacturer.id}" id="manufacturer_{$manufacturer.id}" name="search[manufacturer][]" {$manufacturer.selected}>
         <label for="manufacturer_{$manufacturer.id}" class="text-sm text-ink-800">{$manufacturer.name}</label>
      </li>
      {/foreach}
   </ul>
</div>
{else}
<ul role="list" class="mt-2 grid grid-cols-1 gap-x-6 gap-y-2 sm:grid-cols-2 lg:grid-cols-3">
   {foreach from=$MANUFACTURERS item=manufacturer}
   <li class="flex items-center gap-2">
      <input type="checkbox" value="{$manufacturer.id}" id="manufacturer_{$manufacturer.id}" name="search[manufacturer][]" {$manufacturer.selected}>
      <label for="manufacturer_{$manufacturer.id}" class="text-sm text-ink-800">{$manufacturer.name}</label>
   </li>
   {/foreach}
</ul>
{/if}
{/if}
