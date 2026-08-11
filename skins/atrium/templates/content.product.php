{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by core.
 *
 * ⚠ The whole page is ONE form: options, quantity and the add button post
 * together, and 20-product.js reads the option fields out of $el to recalculate
 * the price. Do not split it.
 *}
{if isset($PRODUCT) && $PRODUCT}
<div x-data="ccProduct()">
   <form action="{$VAL_SELF}" method="post" class="add_to_basket"
         x-data="ccAddToBasket()" @submit="submit($event)">

      <div class="lg:flex lg:items-start lg:gap-10">

         <div class="lg:w-1/2 lg:shrink-0">
            {include file='templates/element.product.gallery.php'}
         </div>

         <div class="mt-8 lg:mt-0 lg:min-w-0 lg:flex-1">
            <h1 class="text-2xl font-semibold tracking-tight text-ink-900 sm:text-3xl">{$PRODUCT.name}</h1>

            {if !empty($PRODUCT.product_code)}
            <p class="mt-1 text-sm text-ink-500">{$LANG.catalogue.product_code}: {$PRODUCT.product_code}</p>
            {/if}

            {include file='templates/element.product.review_score.php' score=$PRODUCT.review_score}

            {include file='templates/element.product.call_to_action.php'}

            {include file='templates/element.product.options.php'}
         </div>
      </div>

      {include file='templates/element.product.tabs.php'}
   </form>

   {if $SHARE}
   <div class="mt-10 flex flex-wrap gap-2 border-t border-ink-200 pt-6">
      {foreach from=$SHARE item=html}{$html}{/foreach}
   </div>
   {/if}

   <div class="mt-10 border-t border-ink-200 pt-8">
      {include file='templates/element.product_reviews.php'}
   </div>

   {foreach from=$COMMENTS item=html}{$html}{/foreach}
</div>
{else}
<div class="py-16 text-center">
   <p class="text-ink-600">{$LANG.catalogue.product_doesnt_exist}</p>
   <a href="{$STORE_URL}" class="cc-btn cc-btn-primary mt-6">{$LANG.common.home}</a>
</div>
{/if}
