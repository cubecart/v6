{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
{if isset($ITEMS)}
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data" class="autosubmit" id="checkout_form">
   {if $INCLUDE_CHECKOUT}
   {include file='templates/content.checkout.confirm.php'}
   {/if}
   <h2>{$LANG.checkout.your_basket}</h2>
   {include file='templates/content.checkout.medium-up.php'}
   {include file='templates/content.checkout.small.php'}
   <div class="row">
         <div class="small-8 columns text-right">
            {$LANG.basket.coupon_add}
         </div>
         <div class="small-4 columns">
            <input name="coupon" id="coupon" type="text" maxlength="25">
         </div>
      </div>
   
   {if $INCLUDE_CHECKOUT && !$DISABLE_GATEWAYS}
   <div id="payment_method">
      <h3>{$LANG.gateway.select}</h3>
      <hr>
      <div class="row">
         <div class="small-12 columns">
            <ul class="no-bullet center">
               {foreach from=$GATEWAYS item=gateway}
               <li>
                  <input name="gateway" type="radio" value="{$gateway.folder}" id="{$gateway.folder}" required {$gateway.checked}><label for="{$gateway.folder}">{$gateway.description}</label>
                  {if !empty($gateway.help)}
                  <a href="{$gateway.help}" class="info" title="{$LANG.common.information}"><i class="fa fa-info-circle"></i></a>
                  {/if}
               </li>
               {/foreach}
            </ul>
            <div class="hide" id="validate_gateway_required">{$LANG.gateway.choose_payment}</div>
         </div>
      </div>
   </div>
   {/if}
   <div class="clearfix">
      <div class="show-for-medium-up"><a href="{$STORE_URL}/index.php?_a=basket&empty-basket=true" class="button alert left">{$LANG.basket.basket_empty}</a></div>
      <div class="show-for-medium-up"><input type="submit" name="update" class="button secondary left" value="{$LANG.basket.basket_update}"></div>
      <div class="show-for-small-only"><button type="submit" name="update" class="button secondary left" value="{$LANG.basket.basket_update}">{$LANG.common.update}</button></div>
      {if $DISABLE_CHECKOUT_BUTTON!==true}
      <button type="submit" name="proceed" id="checkout_proceed" class="button right">{$CHECKOUT_BUTTON} <i class="fa fa-chevron-right"></i></button>
      {/if}
   </div>
</form>
{if $CUSTOMER_LOCALE.description}
<small>{$CUSTOMER_LOCALE.mark} {$LANG.basket.unconfirmed_locale}</small>
{/if}
{if $CHECKOUTS}
<div class="row">
   <div class="small-12 columns text-right">-- {$LANG.common.or} --</div>
</div>
{foreach from=$CHECKOUTS item=checkout}
<div class="row">
   <div class="small-12 columns text-right pad-topbottom">{$checkout}</div>
</div>
{/foreach}
{/if}
{if $RELATED}
<div class="show-for-medium-up">
   <h2>{$LANG.catalogue.related_products}</h2>
   <ul class="small-block-grid-5 no-bullet">
      {foreach from=$RELATED item=product}
      <li>
         <a href="{$product.url}" title="{$product.name}"><img src="{$product.img_src}" class="th" alt="{$product.name}"></a>
         <br>
         <a href="{$product.url}" title="{$product.name}">{$product.name}</a>
         <p>
            {if $product.ctrl_sale}
            <span class="old_price">{$product.price}</span> <span class="sale_price">{$product.sale_price}</span>
            {else}
            {$product.price}
            {/if}
         </p>
      </li>
      {/foreach}
   </ul>
</div>
{/if}
{else}
<h2>{$LANG.checkout.your_basket}</h2>
<p class="thickpad-top">{$LANG.basket.basket_is_empty}</p>
{/if}