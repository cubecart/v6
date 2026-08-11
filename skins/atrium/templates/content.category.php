{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by core.
 *
 * Grid and list are the same markup with different layout classes, so each
 * product form must contain exactly ONE name="quantity" — two would post twice.
 *
 * Form contract per product — do not rename:
 *   class="add_to_basket"  read by the paypal_commerce module (renderSmartButton)
 *   name="add"             product id
 *   name="quantity"        quantity
 * The CSRF token is injected before </form> by GUI::display().
 *}
<div x-data="ccProductList()">

   <h1 class="text-2xl font-semibold tracking-tight text-ink-900 sm:text-3xl">{$category.cat_name}</h1>

   {if isset($category.image)}
   <img src="{$category.image}"
        alt="{if isset($category.image_tags.alt)}{$category.image_tags.alt}{else}{$category.cat_name}{/if}"
        {if isset($category.image_tags.title)}title="{$category.image_tags.title}"{/if}
        class="mt-4 w-full rounded-cc-lg object-cover">
   {/if}

   {if !empty($category.cat_desc)}
   <div class="prose-cc mt-4">{$category.cat_desc}</div>
   {/if}

   {if isset($SUBCATS) && $SUBCATS}
   <ul role="list" class="mt-8 grid grid-cols-3 gap-4 sm:grid-cols-4 lg:grid-cols-6">
      {foreach from=$SUBCATS item=subcat}
      <li class="text-center">
         <a href="{$subcat.url}" title="{$subcat.cat_name}" class="group block">
            <img src="{$subcat.cat_image}"
                 alt="{if isset($subcat.image_tags.alt)}{$subcat.image_tags.alt}{else}{$subcat.cat_name}{/if}"
                 {if isset($subcat.image_tags.title)}title="{$subcat.image_tags.title}"{/if}
                 loading="lazy"
                 class="aspect-square w-full rounded-cc border border-ink-200 bg-ink-100 object-cover">
            <span class="mt-2 block text-xs text-ink-700 group-hover:text-ink-900">{$subcat.cat_name}</span>
         </a>
      </li>
      {/foreach}
   </ul>
   {/if}

   {if $PRODUCTS}
   <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border-b border-ink-200 pb-4">
      {if isset($SORTING)}
      <form action="{$VAL_SELF}" method="post" class="flex items-center gap-2">
         <label for="product_sort" class="shrink-0 text-sm text-ink-600">{$LANG.form.sort_by}</label>
         <select name="sort" id="product_sort" class="w-auto py-1.5 text-sm" @change="$el.form.submit()">
            <option value="" disabled>{$LANG.form.please_select}</option>
            {foreach from=$SORTING item=sort}
            <option value="{$sort.field}|{$sort.order}" {$sort.selected}>{$sort.name} ({$sort.direction})</option>
            {/foreach}
         </select>
         {* No-JS fallback; the select auto-submits when JS is on. *}
         <noscript><button type="submit" class="cc-btn cc-btn-secondary py-1.5">{$LANG.form.sort}</button></noscript>
      </form>
      {/if}

      <div id="layout_toggle" class="ms-auto hidden items-center gap-1 sm:flex" role="group" aria-label="{$LANG.common.view|default:'View'}">
         <button type="button" class="grid_view cc-btn cc-btn-ghost p-2"
                 :class="isGrid() ? 'bg-ink-200 text-ink-900' : ''"
                 :aria-pressed="isGrid() ? 'true' : 'false'"
                 @click="setView('grid')">
            <span class="cc-sr-only">{$LANG.common.grid|default:'Grid view'}</span>
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
               <path d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
         </button>
         <button type="button" class="list_view cc-btn cc-btn-ghost p-2"
                 :class="!isGrid() ? 'bg-ink-200 text-ink-900' : ''"
                 :aria-pressed="!isGrid() ? 'true' : 'false'"
                 @click="setView('list')">
            <span class="cc-sr-only">{$LANG.common.list|default:'List view'}</span>
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
               <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
         </button>
      </div>
   </div>
   {/if}

   <div id="ccScroll" class="mt-6">
      <ul role="list" class="product_list"
          :class="isGrid()
             ? 'grid grid-cols-2 gap-x-6 gap-y-10 lg:grid-cols-3 xl:grid-cols-4'
             : 'flex flex-col divide-y divide-ink-200'">
         {foreach from=$PRODUCTS item=product}
         <li :class="isGrid() ? '' : 'py-5 first:pt-0'">
            <form action="{$VAL_SELF}" method="post" class="add_to_basket h-full"
                  x-data="ccAddToBasket()" @submit="submit($event)"
                  :class="isGrid() ? 'flex flex-col' : 'flex gap-5'">

               <a href="{$product.url}" title="{$product.name}"
                  class="block shrink-0 overflow-hidden rounded-cc-lg border border-ink-200 bg-ink-100"
                  :class="isGrid() ? '' : 'w-28 sm:w-40'">
                  <img src="{$product.thumbnail}"
                       alt="{if isset($product.image_tags.thumbnail.alt) && !empty($product.image_tags.thumbnail.alt)}{$product.image_tags.thumbnail.alt}{else}{$product.name}{/if}"
                       {if isset($product.image_tags.thumbnail.title)}title="{$product.image_tags.thumbnail.title}"{/if}
                       loading="lazy"
                       class="aspect-square w-full object-cover">
               </a>

               <div class="min-w-0 flex-1" :class="isGrid() ? 'mt-3 flex flex-col' : ''">
                  <h2 class="text-sm font-medium">
                     <a href="{$product.url}" title="{$product.name}" class="text-ink-900 hover:underline">{$product.name}</a>
                  </h2>

                  {if $product.review_score}
                  {include file='templates/element.product.review_score.php' score=$product.review_score}
                  {/if}

                  {* List view only; in a grid it makes card heights uneven. *}
                  {if !empty($product.description_short)}
                  <div class="mt-1 text-sm text-ink-600" x-show="!isGrid()" x-cloak>{$product.description_short}</div>
                  {/if}

                  <p class="mt-2">
                     {if $product.ctrl_sale}
                     <span class="price text-sm text-ink-500 line-through">{$product.price}</span>
                     <span class="price ms-1 text-base font-semibold text-danger-600">{$product.sale_price}</span>
                     {else}
                     <span class="price text-base font-semibold text-ink-900">{$product.price}</span>
                     {/if}
                  </p>

                  <div :class="isGrid() ? 'mt-auto pt-3' : 'mt-3'">
                     {if $product.available <= 0}
                     <button type="submit" class="cc-btn cc-btn-secondary w-full sm:w-auto" disabled>{$LANG.common.unavailable}</button>

                     {* ctrl_stock: purchasable — in stock, or allowed out of stock by store settings. *}
                     {elseif $product.ctrl_stock && !$CATALOGUE_MODE}
                     <div class="flex gap-2">
                        <label class="cc-sr-only" for="qty-cat-{$product.product_id}">{$LANG.common.quantity}</label>
                        <input type="number" id="qty-cat-{$product.product_id}" name="quantity"
                               value="{$product.minimum_quantity|default:'1'}"
                               min="{$product.minimum_quantity|default:'1'}" maxlength="3"
                               class="quantity w-16 text-center">
                        <button type="submit" class="cc-btn cc-btn-primary flex-1 sm:flex-none" :disabled="busy">
                           <span x-show="!added">{$LANG.catalogue.add_to_basket}</span>
                           <span x-show="added" x-cloak>{$LANG.catalogue.added_to_basket}</span>
                        </button>
                        <input type="hidden" name="add" value="{$product.product_id}">
                     </div>

                     {elseif !$CATALOGUE_MODE}
                     <button type="submit" class="cc-btn cc-btn-secondary w-full sm:w-auto" disabled>{$LANG.catalogue.out_of_stock_short}</button>
                     {/if}
                  </div>
               </div>
            </form>
         </li>
         {foreachelse}
         {if !isset($SUBCATS) || !$SUBCATS}
         <li class="py-12 text-center text-ink-500">{$LANG.category.no_products}</li>
         {/if}
         {/foreach}
      </ul>

      {include file='templates/element.category.pagination.traditional.php'}
   </div>
</div>
