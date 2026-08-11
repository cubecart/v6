{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * $POPULAR: {url, name, ctrl_sale, price, sale_price}
 * Rendered as an ordered list — the order is the ranking, which is information.
 *}
{if $POPULAR}
<section id="box-popular" class="cc-card p-4">
   <h3 class="text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.catalogue.title_popular}</h3>
   <ol class="mt-3 space-y-3">
      {foreach from=$POPULAR item=product name=pop}
      <li class="flex gap-3 text-sm">
         <span class="shrink-0 tabular text-ink-400">{$smarty.foreach.pop.iteration}</span>
         <span class="min-w-0">
            <a href="{$product.url}" title="{$product.name}" class="text-ink-800 hover:underline">{$product.name}</a>
            <span class="mt-0.5 block">
               {if $product.ctrl_sale}
               <span class="price text-xs text-ink-500 line-through">{$product.price}</span>
               <span class="price ms-1 text-xs font-semibold text-danger-600">{$product.sale_price}</span>
               {else}
               <span class="price text-xs text-ink-600">{$product.price}</span>
               {/if}
            </span>
         </span>
      </li>
      {/foreach}
   </ol>
</section>
{/if}
