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
      <button type="submit" class="cc-btn cc-btn-primary min-w-48 flex-1" :disabled="busy">
         <span x-show="!added">{$LANG.catalogue.add_to_basket}</span>
         <span x-show="added" x-cloak>{$LANG.catalogue.added_to_basket}</span>
      </button>
   </div>

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
