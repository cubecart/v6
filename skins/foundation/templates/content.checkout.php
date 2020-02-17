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
{if isset($ITEMS)}
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data" class="autosubmit" id="checkout_form">
   {if $INCLUDE_CHECKOUT}
   {include file='templates/content.checkout.confirm.php'}
   {/if}
   <h2>{$LANG.checkout.your_basket}</h2>
   {include file='templates/content.checkout.medium-up.php'}
   {include file='templates/content.checkout.small.php'}
   <div class="row">
         <div class="medium-8 columns text-right">
            {$LANG.basket.coupon_add}
         </div>
         <div class="medium-4 columns">
            <div class="row collapse">
               <div class="small-9 medium-8 columns">
                  <input name="coupon" id="coupon" type="text" maxlength="25">
               </div>
               <div class="small-3 medium-4 columns">
                  <button type="submit" name="update" class="postfix nomarg nopad secondary" value="{$LANG.common.apply}"><svg class="icon"><use xlink:href="#icon-refresh"></use></svg> {$LANG.common.apply}</button>
               </div>
            </div>
         </div>
      </div>
   
   {if $INCLUDE_CHECKOUT && !$DISABLE_GATEWAYS}
   <div id="payment_method">
      <a href="?" class="right">{$LANG.basket.continue_shopping}</a><h3>{$LANG.gateway.select}</h3>
      <hr>
      <div class="row">
         <div class="small-12 columns">
            <ul class="no-bullet center" id="gateway_error">
               {foreach from=$GATEWAYS item=gateway}
               <li>
                  <input name="gateway" type="radio" class="nosubmit" value="{$gateway.folder}" id="{$gateway.folder}" required {$gateway.checked} rel="gateway_error"><label for="{$gateway.folder}">{$gateway.description}</label>
                  {if !empty($gateway.help)}
                  <a href="{$gateway.help}" class="info" title="{$LANG.common.information}"><svg class="icon"><use xlink:href="#icon-info-circle"></use></svg></a>
                  {/if}
               </li>
               {/foreach}
            </ul>
            <div class="hide" id="validate_gateway_required">{$LANG.gateway.choose_payment}</div>
         </div>
      </div>
   </div>
   {/if}
   {if $TERMS_CONDITIONS && isset($ALTERNATE_TERMS) && $ALTERNATE_TERMS=='0'}
   <p class="text-right"><label for="reg_terms">{$LANG.account.register_terms_agree_link|replace:'%s':{$TERMS_CONDITIONS}} <input type="checkbox" id="reg_terms" name="terms_agree" value="1" rel="error_terms_agree"></label></p>
   {/if}
   <div class="clearfix">
      <div class="show-for-medium-up"><a href="{$STORE_URL}/index.php?_a=basket&empty-basket=true" class="button alert left"><svg class="icon"><use xlink:href="#icon-trash-o"></use></svg> {$LANG.basket.basket_empty}</a></div>
      <div class="show-for-medium-up"><button type="submit" name="update" class="button secondary left" value="{$LANG.basket.basket_update}"><svg class="icon"><use xlink:href="#icon-refresh"></use></svg> {$LANG.basket.basket_update}</button></div>
      <div class="show-for-small-only"><button type="submit" name="update" class="button secondary left" value="{$LANG.basket.basket_update}"><svg class="icon"><use xlink:href="#icon-refresh"></use></svg> {$LANG.common.update}</button></div>
      {if $DISABLE_CHECKOUT_BUTTON!==true}
      <button type="submit" name="proceed" id="checkout_proceed" class="button right g-recaptcha">{$CHECKOUT_BUTTON} <svg class="icon"><use xlink:href="#icon-chevron-right"></use></svg></button>
      {/if}
   </div>
</form>
{if $DISABLE_CHECKOUT_BUTTON!==true}
   {if $CHECKOUTS}
<div class="row">
   <div class="small-12 columns text-right">-- {$LANG.common.or} --</div>
</div>
      {foreach from=$CHECKOUTS item=checkout}
<div class="row">
   <div class="small-12 columns text-right pad">{$checkout}</div>
</div>
      {/foreach}
   {/if}
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
<div class="text-center">
<p class="thickpad-top">{$LANG.basket.basket_is_empty}</p>
<a href="?" class="button success">{$LANG.basket.continue_shopping}</a>
</div>
{/if}