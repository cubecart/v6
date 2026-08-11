{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Mini-basket contents.
 *
 * ⚠ ITERATE $CONTENTS, NOT $CART_ITEMS. GUI::displaySideBasket() assigns both
 * and the names mislead: $CONTENTS is the per-line array, $CART_ITEMS is an
 * INTEGER (the sum of line quantities). Smarty iterates a scalar zero times, so
 * {foreach from=$CART_ITEMS} still renders the wrapper, subtotal and button but
 * never a line — it fails silently and just looks like an empty basket. The
 * same output is the _g=ajaxadd response, so that mistake also empties every
 * add-to-basket update, and the class.gui.display_side_basket hook (which fires
 * just before the fetch) has no rows left to decorate.
 *
 * ⚠ Per-line money is $item.total, currency-formatted by
 * GUI::displaySideBasket(). There is no $item.price on these rows;
 * $item.price_display is the raw unformatted float it derives from.
 *
 * Available keys: name, name_abbrev, product_id, product_code, quantity,
 * price_display (raw), total (formatted), image, link.
 *}
{if !empty($CONTENTS)}
<ul class="divide-y divide-ink-200">
   {foreach from=$CONTENTS item=item name=items}
   {* Cap the dropdown; the full list is on the basket page. *}
   {if $smarty.foreach.items.index == 10}
   <li class="py-3 text-center text-xs text-ink-500">&hellip;</li>
   {break}
   {/if}
   <li class="flex gap-3 py-3">
      <div class="min-w-0 flex-1">
         <a href="{$item.link}" class="block truncate text-sm font-medium text-ink-900 hover:underline">{$item.name}</a>
         <p class="mt-0.5 text-xs text-ink-500">
            {$LANG.common.quantity}: <span class="tabular">{$item.quantity}</span>
         </p>
      </div>
      <p class="price shrink-0 text-sm font-medium text-ink-900">{$item.total}</p>
   </li>
   {/foreach}
</ul>
<div class="mt-3 flex items-center justify-between border-t border-ink-200 pt-3">
   <span class="text-sm text-ink-600">{$LANG.basket.subtotal|default:$LANG.common.total}</span>
   <span class="price text-base font-semibold text-ink-900">{$CART_TOTAL}</span>
</div>
{* $HIDE_CHECKOUT_BUTTON is set by the paypal_commerce module's
   class.gui.display_side_basket hook to suppress a direct-to-checkout link that
   would bypass its express flow. Atrium ships only the view-basket link below,
   so nothing is hidden today — but any "Checkout now" button added here MUST
   honour that flag. *}
<a href="{$BUTTON.link}" class="cc-btn cc-btn-primary mt-3 w-full">{$BUTTON.text}</a>
{else}
<p class="py-6 text-center text-sm text-ink-500">{$LANG.basket.basket_empty}</p>
{/if}
