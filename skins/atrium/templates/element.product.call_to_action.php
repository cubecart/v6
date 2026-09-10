{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ ELEMENT IDS ARE A CONTRACT with 20-product.js:
 *   #ptp  data-price = $PRODUCT.price_to_pay      what is actually charged
 *   #fbp  data-price = $PRODUCT.full_base_price   the "was" price, sale only
 * Both are rewritten in place as options change. The displayed text is
 * currency-formatted by PHP; the data-price attributes are raw decimals. Never
 * swap the two.
 *
 * Control flags:
 *   $CTRL_ALLOW_PURCHASE  purchasing permitted at all
 *   $CATALOGUE_MODE       store is a catalogue only, no basket
 *   $CTRL_HIDE_PRICES     prices hidden until login
 *   $CTRL_OUT_OF_STOCK    out of stock and not purchasable
 *}
{* id="call_to_action_block" is where paypal_commerce injects its PDP button and
   pay-later message. Placement is per-skin
   (modules/plugins/paypal_commerce/config.<skin>.json); without a
   config.atrium.json it falls back to config.foundation.json, whose product
   selector matches nothing here and the button vanishes with NO console error —
   the module's guard tests the length of the selector STRING, never zero.
   config.dillion.json or config.kurouto.json can be copied; that file lives
   under modules/ so it cannot ship with the skin — see README. *}
<div class="mt-4" id="call_to_action_block">
   <p class="flex flex-wrap items-baseline gap-2">
      {if $PRODUCT.ctrl_sale}
      <span class="price text-lg text-ink-500 line-through" id="fbp"{if !$CTRL_HIDE_PRICES} data-price="{$PRODUCT.full_base_price}"{/if}>{$PRODUCT.price}</span>
      <span class="price text-3xl font-semibold text-danger-600" id="ptp"{if !$CTRL_HIDE_PRICES} data-price="{$PRODUCT.price_to_pay}"{/if}>{$PRODUCT.sale_price}</span>
      {else}
      <span class="price text-3xl font-semibold text-ink-900" id="ptp"{if !$CTRL_HIDE_PRICES} data-price="{$PRODUCT.price_to_pay}"{/if}>{$PRODUCT.price}</span>
      {/if}
   </p>

   {if !empty($PRODUCT.discounts)}
   <p class="mt-1 text-sm"><a href="#quantity_discounts" class="underline">{$LANG.catalogue.bulk_discount}</a></p>
   {/if}
</div>

{if ($CTRL_ALLOW_PURCHASE) && (!$CATALOGUE_MODE)}
<div class="mt-6">
   {if $PRODUCT.available <= 0}
   <button type="submit" class="cc-btn cc-btn-secondary w-full" disabled>{$LANG.common.unavailable}</button>
   {else}
   <div class="flex flex-wrap gap-3">
      <div>
         <label for="product_quantity" class="cc-sr-only">{$LANG.common.quantity}</label>
         <input type="number" id="product_quantity" name="quantity"
                value="{$PRODUCT.minimum_quantity|default:'1'}"
                min="{$PRODUCT.minimum_quantity|default:'1'}"
                {if $PRODUCT.maximum_quantity gte $PRODUCT.minimum_quantity}max="{$PRODUCT.maximum_quantity}"{/if}
                maxlength="3" class="quantity required w-20 text-center">
         <input type="hidden" name="add" value="{$PRODUCT.product_id}">
      </div>
      {* id is the sticky bar's observer target (20-product.js): the bar appears
         once this button leaves the viewport. *}
      <button type="submit" id="cc-main-buy" class="cc-btn cc-btn-primary min-w-48 flex-1"
              :class="{ 'cc-btn-added': added }" :disabled="busy || !$store.optionStock.available">
         <span x-show="!added">{$LANG.catalogue.add_to_basket}</span>
         <span x-show="added" x-cloak class="inline-flex items-center gap-2">
            {$LANG.catalogue.added_to_basket}
            {include file='templates/element.icon.check.php'}
         </span>
      </button>
   </div>

   {* The real figure or nothing — no invented counts, no countdown timers.
      ⚠ unsuppressed_stock_level, NOT stock_level: core blanks the latter unless
      "Display Stock Levels" is on, which is a different decision from "warn me
      when one is nearly gone".
      ⚠ is_numeric guard: option-matrix products give a range like "2 - 9". *}
   {if !empty($SKIN_SETTINGS.low_stock_threshold) && $PRODUCT.use_stock_level
       && is_numeric($PRODUCT.unsuppressed_stock_level)
       && $PRODUCT.unsuppressed_stock_level > 0
       && $PRODUCT.unsuppressed_stock_level <= $SKIN_SETTINGS.low_stock_threshold}
   <p class="mt-3 text-sm font-medium text-warn-700" role="status">
      {if $PRODUCT.unsuppressed_stock_level == 1}{$LANG.catalogue.stock_low_one|default:'Only 1 left in stock'}{else}{sprintf($LANG.catalogue.stock_low|default:'Only %d left in stock', $PRODUCT.unsuppressed_stock_level)}{/if}
   </p>
   {/if}

   {* Per-combination availability, from core (Catalogue::optionStockMap): says
      an option combination is sold out BEFORE the customer submits and gets
      bounced back. Absent or empty means "no opinion" and nothing changes. *}
   {if !empty($OPTION_STOCK)}
   {* data-low-* carry the translated strings; 20-product.js substitutes %d. *}
   <script type="application/json" id="cc-option-stock"
           data-low-one="{$LANG.catalogue.stock_low_one|default:'Only 1 left in stock'|escape}"
           data-low-many="{$LANG.catalogue.stock_low|default:'Only %d left in stock'|escape}">{$OPTION_STOCK nofilter}</script>
   {* Hidden by default and revealed by removing the class, rather than x-show:
      the class binding is what the button beside it uses and it demonstrably
      re-runs on a store change, where x-show's effect on this element did not.
      Starting hidden also means no flash of an out-of-stock line before JS. *}
   <p class="mt-2 hidden text-sm font-medium text-danger-700" :class="{ 'hidden': $store.optionStock.available }" role="status">
      {$LANG.catalogue.out_of_stock_short}<span class="hidden font-normal" :class="{ 'hidden': !$store.optionStock.note }" x-text="$store.optionStock.note"></span>
   </p>
   {* Low stock for the SELECTED combination. The product-level line above can
      never fire on an option-matrix product, because core reports its stock as
      a range like "2 - 9"; this follows the customer's choice instead. *}
   {if !empty($SKIN_SETTINGS.low_stock_threshold)}
   <p class="mt-3 text-sm font-medium text-warn-700" role="status"
      x-show="$store.optionStock.lowText({$SKIN_SETTINGS.low_stock_threshold})" x-cloak
      x-text="$store.optionStock.lowText({$SKIN_SETTINGS.low_stock_threshold})"></p>
   {/if}
   {/if}

   {if $PRODUCT.minimum_quantity>1}
   <p class="mt-2 text-sm text-ink-500">{sprintf($LANG.catalogue.min_purchase_quantity,$PRODUCT.minimum_quantity)}</p>
   {/if}
   {if $PRODUCT.maximum_quantity gte $PRODUCT.minimum_quantity}
   <p class="mt-1 text-sm text-ink-500">{sprintf($LANG.catalogue.max_purchase_quantity,$PRODUCT.maximum_quantity)}</p>
   {/if}
   {/if}
</div>
{else}
   {if $CTRL_HIDE_PRICES}
   <p class="buy_button mt-6 rounded-cc bg-ink-100 p-4 text-sm font-semibold text-ink-800">{$LANG.catalogue.login_to_view}</p>
   {elseif $CTRL_OUT_OF_STOCK}
   <p class="buy_button mt-6 rounded-cc bg-warn-50 p-4 text-sm font-semibold text-warn-700">{$LANG.catalogue.out_of_stock}</p>
   {/if}
{/if}
