{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
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
         Apply credit:
      </div>
      <div class="medium-4 columns text-right">
        {if $CREDIT_USED && $CREDIT_USED !== $AVAILABLE_CREDIT}{sprintf($LANG.basket.credit_use, $CREDIT_USED, $AVAILABLE_CREDIT)}{else}{$AVAILABLE_CREDIT}{/if}
        <input type="hidden" value="0" name="use_credit">
        <input type="checkbox" name="use_credit" value="1"{if $USE_CREDIT=='1'} checked="checked"{/if} />
      </div>
   </div>
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
               <button type="submit" name="update" class="postfix nomarg nopad secondary" value="{$LANG.common.apply}" id="apply_coupon"><svg class="icon"><use xlink:href="#icon-refresh"></use></svg> {$LANG.common.apply}</button>
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
   <p class="text-right"><label for="reg_terms">{sprintf($LANG.account.register_terms_agree_link,$TERMS_CONDITIONS)} <input type="checkbox" id="reg_terms" name="terms_agree" value="1" rel="error_terms_agree"></label></p>
   {/if}
   <div class="clearfix" id="checkout_actions">
      <div><a href="{$STORE_URL}/index.php" class="button left"><svg class="icon"><use xlink:href="#icon-home"></use></svg><span class="show-for-medium-up right">{$LANG.basket.continue_shopping}</span></a></div>
      <div><a href="{$STORE_URL}/index.php?_a=basket&empty-basket=true" class="button alert left"><svg class="icon"><use xlink:href="#icon-trash-o"></use></svg><span class="show-for-medium-up right">{$LANG.basket.basket_empty}</span></a></div>
      <div><button type="submit" name="update" class="button secondary left" value="{$LANG.basket.basket_update}"><svg class="icon"><use xlink:href="#icon-refresh"></use></svg><span class="show-for-medium-up right">{$LANG.basket.basket_update}</span></button></div>
      {if $DISABLE_CHECKOUT_BUTTON!==true}
      <button type="submit" name="proceed" id="checkout_proceed" class="button success right g-recaptcha"><svg class="icon"><use xlink:href="#icon-chevron-right"></use></svg><span class="left">{$CHECKOUT_BUTTON}</span></button>
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
{include file='templates/element.checkout.related_products.php'}
{else}
<h2>{$LANG.checkout.your_basket}</h2>
<div class="text-center">
<p class="thickpad-top">{$LANG.basket.basket_is_empty}</p>
<a href="?" class="button success">{$LANG.basket.continue_shopping}</a>
</div>
{/if}