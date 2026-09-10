{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Sticky buy bar, below lg only. On a phone the real Add to Basket button is
 * above the options, the tabs and the reviews, so a customer who reads the
 * description has to scroll back up to buy. This appears once that button
 * leaves the viewport.
 *
 * ⚠ MUST be inside the product <form>. It is a second submit button for the
 * same form, which is what makes it carry the chosen quantity and options and
 * go through ccAddToBasket's AJAX handler. position:fixed is unaffected by
 * living inside the form.
 *
 * $store.ui.stickyBuy is set by an IntersectionObserver in 20-product.js.
 * busy / added come from the surrounding ccAddToBasket scope.
 *
 * data-cc-price-mirror is rewritten by recalc() alongside #ptp, so the price
 * here tracks the chosen options rather than going stale.
 *}
{if ($CTRL_ALLOW_PURCHASE) && (!$CATALOGUE_MODE) && $PRODUCT.available > 0}
<div x-show="$store.ui.stickyBuy" x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="translate-y-full"
     x-transition:enter-end="translate-y-0"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="translate-y-0"
     x-transition:leave-end="translate-y-full"
     class="fixed inset-x-0 bottom-0 z-40 border-t border-ink-200 bg-ink-100/95 py-3 backdrop-blur lg:hidden">
   <div class="cc-container flex items-center gap-3">
      {if !$CTRL_HIDE_PRICES}
      <span class="price shrink-0 text-lg font-semibold text-ink-900" data-cc-price-mirror>{if $PRODUCT.ctrl_sale}{$PRODUCT.sale_price}{else}{$PRODUCT.price}{/if}</span>
      {/if}
      <button type="submit" class="cc-btn cc-btn-primary flex-1"
              :class="{ 'cc-btn-added': added }" :disabled="busy || !$store.optionStock.available">
         <span x-show="!added">{$LANG.catalogue.add_to_basket}</span>
         <span x-show="added" x-cloak class="inline-flex items-center gap-2">
            {$LANG.catalogue.added_to_basket}
            {include file='templates/element.icon.check.php' class='size-5'}
         </span>
      </button>
   </div>
</div>
{/if}
