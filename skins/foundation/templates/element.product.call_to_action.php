{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<h3>
{if $PRODUCT.ctrl_sale}
   <span class="old_price" id="fbp"{if !$CTRL_HIDE_PRICES} data-price="{$PRODUCT.full_base_price}"{/if}>{$PRODUCT.price}</span>
   <span class="sale_price" id="ptp"{if !$CTRL_HIDE_PRICES} data-price="{$PRODUCT.price_to_pay}"{/if}>{$PRODUCT.sale_price}</span>
{else}
   <span id="ptp"{if !$CTRL_HIDE_PRICES} data-price="{$PRODUCT.price_to_pay}"{/if}>{$PRODUCT.price}</span>
{/if}
</h3>
{if !empty($PRODUCT.discounts)}
<p>(<a href="#quantity_discounts">{$LANG.catalogue.bulk_discount}</a>)</p>
{/if}

<div>
{if ($CTRL_ALLOW_PURCHASE) && (!$CATALOGUE_MODE)}
<div class="row collapse">
   {if $PRODUCT.available <= 0}
   <div class="small-12 columns">
      <input type="submit" value="{$LANG.common.unavailable}" class="button small postfix disabled expand marg-top" disabled>
   </div>
   {else}
   <div class="medium-3 columns show-for-medium-up">
   <input type="number" name="quantity" aria-label="{$LANG.common.quantity}" value="{$PRODUCT.minimum_quantity|default:'1'}" min="{$PRODUCT.minimum_quantity}"{if $PRODUCT.maximum_quantity gte $PRODUCT.minimum_quantity} max="{$PRODUCT.maximum_quantity}"{/if} maxlength="3" class="quantity required text-center">
      <input type="hidden" name="add" value="{$PRODUCT.product_id}">
   </div>
   <div  class="small-12 medium-9 columns">
      <button type="submit" value="{$LANG.catalogue.add_to_basket}" class="button postfix" id="add_to_basket_button">{$LANG.catalogue.add_to_basket}</button>
   </div>
   {* Per-combination availability from core (Catalogue::optionStockMap): says an
      option combination is sold out BEFORE the customer submits and gets bounced
      back. Absent or empty means "no opinion" and nothing changes. *}
   {if !empty($OPTION_STOCK)}
   <script type="application/json" id="cc-option-stock">{$OPTION_STOCK nofilter}</script>
   <div class="small-12 columns"><div id="option_stock_note" class="alert-box alert" role="status" style="display:none" data-label="{$LANG.catalogue.out_of_stock_short}"></div></div>
   {/if}
   {if $PRODUCT.minimum_quantity>1}<div><small>{sprintf($LANG.catalogue.min_purchase_quantity,$PRODUCT.minimum_quantity)}</small></div>{/if}
   {if $PRODUCT.maximum_quantity gte $PRODUCT.minimum_quantity}<div><small>{sprintf($LANG.catalogue.max_purchase_quantity,$PRODUCT.maximum_quantity)}</small></div>{/if}
   {/if}
</div>
{else}
   {if $CTRL_HIDE_PRICES}
<p class="buy_button"><strong>{$LANG.catalogue.login_to_view}</strong></p>
   {else if $CTRL_OUT_OF_STOCK}
<p class="buy_button"><strong>{$LANG.catalogue.out_of_stock}</strong></p>
   {/if}
{/if}
</div>