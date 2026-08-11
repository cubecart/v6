{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * $SALE_PRODUCTS: {url, name, price, sale_price, sale_price_unformatted, saving}
 *
 * ⚠ The $CONFIG.catalogue_sale_mode > 0 test is required, not decorative: sale
 * mode 0 means the store is not running sale pricing at all, and $SALE_PRODUCTS
 * can still be populated.
 *
 * sale_price_unformatted is the emptiness test — sale_price is a formatted
 * currency string and is never empty, so testing it would always be truthy.
 *}
{if $SALE_PRODUCTS && $CONFIG.catalogue_sale_mode > 0}
<section id="box-sale_items" class="cc-card p-4">
   <h3 class="text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.catalogue.title_saleitems}</h3>
   <ul class="mt-3 space-y-3 text-sm">
      {foreach from=$SALE_PRODUCTS item=product}
      <li>
         <a href="{$product.url}" title="{$product.name}{if $product.saving} ({$LANG.catalogue.saving} {$product.saving}){/if}"
            class="text-ink-800 hover:underline">{$product.name}</a>
         <span class="mt-0.5 block">
            {if empty($product.sale_price_unformatted)}
            <span class="price text-xs text-ink-600">{$product.price}</span>
            {else}
            <span class="price text-xs text-ink-500 line-through">{$product.price}</span>
            <span class="price ms-1 text-xs font-semibold text-danger-600">{$product.sale_price}</span>
            {if $product.saving}
            <span class="ms-1 rounded-xs bg-danger-50 px-1.5 py-0.5 text-[11px] font-medium text-danger-700">-{$product.saving}</span>
            {/if}
            {/if}
         </span>
      </li>
      {/foreach}
   </ul>
</section>
{/if}
