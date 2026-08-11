{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by core. Advanced search form.
 *
 * Field contract — all read by the category/search controller:
 *   search[keywords] search[priceMin] search[priceMax]
 *   search[inStock] search[featured] search[manufacturer][]
 * Posts to ?_a=category — the search results ARE the category listing.
 *}
<form method="post" action="?_a=category" id="advanced_search_form" enctype="multipart/form-data" class="mx-auto max-w-2xl">
   <h1 class="text-2xl font-semibold tracking-tight text-ink-900">{$LANG.search.advanced}</h1>

   <div class="cc-card mt-6 space-y-5 p-5">

      <div>
         <label for="keywords" class="cc-label">{$LANG.search.keywords}</label>
         <input type="text" name="search[keywords]" id="keywords" class="search_input" placeholder="{$LANG.search.keywords}" required>
      </div>

      <fieldset>
         <legend class="cc-label">{$LANG.search.price_range}</legend>
         <div class="flex items-center gap-3">
            <input type="number" name="search[priceMin]" placeholder="{$LANG.common.from}" step="0.01" min="0" class="w-32" aria-label="{$LANG.common.from}">
            <span class="text-ink-400" aria-hidden="true">&ndash;</span>
            <input type="number" name="search[priceMax]" placeholder="{$LANG.common.to}" step="0.01" min="0" class="w-32" aria-label="{$LANG.common.to}">
         </div>
      </fieldset>

      <div class="space-y-2">
         <div class="flex items-center gap-2">
            <input type="checkbox" name="search[inStock]" id="in_stock" value="1">
            <label for="in_stock" class="text-sm text-ink-800">{$LANG.search.in_stock}</label>
         </div>
         <div class="flex items-center gap-2">
            <input type="checkbox" name="search[featured]" id="featured_only" value="1">
            <label for="featured_only" class="text-sm text-ink-800">{$LANG.search.featured_only}</label>
         </div>
      </div>

      {if isset($MANUFACTURERS)}
      <div>
         <span class="cc-label">{$LANG.catalogue.manufacturer}</span>
         {include file='templates/element.search.manufacturers.php'}
      </div>
      {/if}
   </div>

   <div class="mt-6 text-center">
      <button type="submit" class="cc-btn cc-btn-primary px-8">{$LANG.common.search}</button>
   </div>
</form>
