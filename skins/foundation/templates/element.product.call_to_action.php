{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
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

{if isset($PRODUCT.discounts)}
<p>(<a href="#quantity_discounts">{$LANG.catalogue.bulk_discount}</a>)</p>
{/if}

<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
   <meta itemprop="priceCurrency" content="{$CONFIG.default_currency}" />
   <meta itemprop="price" content="{$PRODUCT.price_to_pay|regex_replace:'/[^0-9.]+/':''}"/>
{if ($CTRL_ALLOW_PURCHASE) && (!$CATALOGUE_MODE)}
<div class="row collapse">
   {if $PRODUCT.available <= 0}
   <div class="small-12 columns">
      <input type="submit" value="{$LANG.common.unavailable}" class="button small postfix disabled expand marg-top" disabled>
   </div>
   {else}
   <div class="medium-2 columns show-for-medium-up">
      <input type="text" name="quantity" value="1" maxlength="3" class="quantity required text-center">
      <input type="hidden" name="add" value="{$PRODUCT.product_id}">
   </div>
   <div  class="small-12 medium-10 columns">
      <link itemprop="availability" href="http://schema.org/InStock" />
      <button type="submit" value="{$LANG.catalogue.add_to_basket}" class="button postfix">{$LANG.catalogue.add_to_basket}</button>
   </div>
   {/if}
</div>
{else}
   {if $CTRL_HIDE_PRICES}
<p class="buy_button"><strong>{$LANG.catalogue.login_to_view}</strong></p>
   {else if $CTRL_OUT_OF_STOCK}
   <link itemprop="availability" href="http://schema.org/OutOfStock" />
<p class="buy_button"><strong>{$LANG.catalogue.out_of_stock}</strong></p>
   {/if}
{/if}
</div>