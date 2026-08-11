{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Cross-sell strip below the basket. $RELATED: {url, name, img_src, ctrl_sale,
 * price, sale_price}.
 *
 * ⚠ The image key is img_src, not image — unlike $PRODUCTS everywhere else in
 * the skin. Copying a product card in from another template renders a broken
 * image with no error.
 *}
{if $RELATED}
<section class="mt-12 border-t border-ink-200 pt-8">
   <h2 class="text-lg font-semibold tracking-tight text-ink-900">{$LANG.catalogue.related_products}</h2>
   <ul role="list" class="mt-4 grid grid-cols-2 gap-x-6 gap-y-8 sm:grid-cols-3 lg:grid-cols-5">
      {foreach from=$RELATED item=product}
      <li>
         <a href="{$product.url}" title="{$product.name}" class="block overflow-hidden rounded-cc-lg border border-ink-200 bg-ink-100">
            <img src="{$product.img_src}" alt="{$product.name}" loading="lazy" class="aspect-square w-full object-cover">
         </a>
         <h3 class="mt-2 text-sm font-medium">
            <a href="{$product.url}" title="{$product.name}" class="text-ink-900 hover:underline">{$product.name}</a>
         </h3>
         <p class="mt-1">
            {if $product.ctrl_sale}
            <span class="price text-xs text-ink-500 line-through">{$product.price}</span>
            <span class="price ms-1 text-sm font-semibold text-danger-600">{$product.sale_price}</span>
            {else}
            <span class="price text-sm font-semibold text-ink-900">{$product.price}</span>
            {/if}
         </p>
      </li>
      {/foreach}
   </ul>
</section>
{/if}
