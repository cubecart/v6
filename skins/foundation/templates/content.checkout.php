<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */
?>
{if isset($ITEMS)}
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data" class="autosubmit" id="checkout_form">
   {if $INCLUDE_CHECKOUT}
   {include file='templates/content.checkout.confirm.php'}
   {/if}
   <h2>{$LANG.checkout.your_basket}</h2>
   <div class="show-for-medium-up">
      <table class="expand">
         <thead>
            <tr>
               <td></td>
               <td>{$LANG.common.name}</td>
               <td>{$LANG.common.price_unit}</td>
               <td>{$LANG.common.quantity}</td>
               <td>{$LANG.common.price}</td>
            </tr>
         </thead>
         <tbody>
            {foreach from=$ITEMS key=hash item=item}
            <tr>
               <td class="text-center"><a href="{$STORE_URL}/index.php?_a=basket&remove-item={$hash}"><i class="fa fa-trash-o"></i></a></td>
               <td>
                  <a href="{$item.link}" class="th" title="{$item.name}"><img src="{$item.image}" width="80" alt="{$item.name}"></a>
                  <a href="{$item.link}" class="txtDefault"><strong>{$item.name}</strong></a>
                  {if $item.options}
                  <ul class="no-bullet">
                     {foreach from=$item.options item=option}
                     <li><strong>{$option.option_name}</strong>: {$option.value_name|truncate:45:"&hellip;":true}{if !empty($option.price_display)} ({$option.price_display}){/if}</li>
                     {/foreach}
                  </ul>
                  {/if}
                  <p>
               </td>
               <td class="text-right">{$item.line_price_display}</td>
               <td>
                  <input name="quan[{$hash}]" type="text" value="{$item.quantity}" maxlength="3" class="quantity" {$QUAN_READ_ONLY}>
               </td>
               <td class="text-right">{$item.price_display}</td>
            </tr>
            {/foreach}
         </tbody>
         <tfoot>
            <tr>
               <td colspan="3">{if $BASKET_WEIGHT}
                  {$LANG.basket.weight}: {$BASKET_WEIGHT}
                  {/if}
               </td>
               <td>{$LANG.basket.total_sub}</td>
               <td class="text-right">{$SUBTOTAL}</td>
            </tr>
            {if isset($SHIPPING)}
            <tr>
               <td colspan="3">
                  {$LANG.basket.shipping_select}:
                  <select name="shipping">
                     <option value="">{$LANG.form.please_select}</option>
                     {foreach from=$SHIPPING key=group item=methods}
                     {if $HIDE_OPTION_GROUPS ne '1'}
                     <optgroup label="{$group}">{/if}
                        {foreach from=$methods item=method}
                        <option value="{$method.value}" {$method.selected}>{$CUSTOMER_LOCALE.mark} {$method.display}</option>
                        {/foreach}
                        {if $HIDE_OPTION_GROUPS ne '1'}
                     </optgroup>
                     {/if}
                     {/foreach}
                  </select>
               </td>
               <td>{$LANG.basket.shipping}{$CUSTOMER_LOCALE.mark}</td>
               <td class="text-right">{$SHIPPING_VALUE}</td>
            </tr>
            {/if}
            {foreach from=$TAXES item=tax}
            <tr>
               <td colspan="3"></td>
               <td>{$tax.name}{$CUSTOMER_LOCALE.mark}</td>
               <td class="text-right">{$tax.value}</td>
            </tr>
            {/foreach}
            {foreach from=$COUPONS item=coupon}
            <tr>
               <td colspan="3"></td>
               <td><a href="{$VAL_SELF}&remove_code={$coupon.remove_code}" title="{$LANG.common.remove}">{$coupon.voucher}</a></td>
               <td class="text-right">{$coupon.value}</td>
            </tr>
            {/foreach}
            {if isset($DISCOUNT)}
            <tr>
               <td colspan="3"></td>
               <td>{$LANG.basket.total_discount}</td>
               <td class="text-right">{$DISCOUNT}</td>
            </tr>
            {/if}
            <tr>
               <td colspan="3"></td>
               <td>{$LANG.basket.total_grand}</td>
               <td class="text-right">{$TOTAL}</td>
            </tr>
         </tfoot>
      </table>
   </div>
   <div class="show-for-small-only">
      {foreach from=$ITEMS key=hash item=item}
      <div class="panel" id="basket_item_{$hash}">
         <div class="row">
            <div class="small-4 columns">
               <a href="{$item.link}" class="th" title="{$item.name}"><img src="{$item.image}" alt="{$item.name}"></a>
               
            </div>
            <div class="small-8 columns">
               <a href="{$item.link}" class="txtDefault"><strong>{$item.name}</strong></a>
               {if $item.options}
               <ul class="no-bullet">
                  {foreach from=$item.options item=option}
                  <li><strong>{$option.option_name}</strong>: {$option.value_name|truncate:45:"&hellip;":true}{if !empty($option.price_display)} ({$option.price_display}){/if}</li>
                  {/foreach}
               </ul>
               {/if}
               {$item.line_price_display}
            </div>
         </div>
         <hr>
         <div class="row">
            <div class="small-2 columns">
               {$LANG.common.quantity_abbreviated}
            </div>
            <div class="small-4 columns">
               <a href="#" class="quan subtract" rel="{$hash}"><i class="fa fa-minus-circle"></i></a>
               <span class="disp_quan_{$hash}">{$item.quantity}</span>
               <input name="quan[{$hash}]" type="hidden" value="{$item.quantity}">
               <span id="original_val_{$hash}" class="hide">{$item.quantity}</span>
               <a href="#" class="quan add" rel="{$hash}"><i class="fa fa-plus-circle"></i></a>
            </div>
            <div class="small-3 columns">
               {$LANG.basket.total}
            </div>
            <div class="small-3 columns">
               {$item.price_display}
            </div>
         </div>
         <div class="row hide" id="quick_update_{$hash}">
            <div class="small-offset-3 small-9 columns">
               <button type="submit" name="update" class="button secondary tiny marg-top" value="{$LANG.basket.basket_update}">{$LANG.common.update}</button>
            </div>
         </div>
      </div>
      {/foreach}
      <table class="expand">
      
      <tr>
         <td>
            {$LANG.basket.total_sub}
         </td>
         <td width="10%" class="text-right">
            {$SUBTOTAL}
         </td>
      </tr>
      {if isset($SHIPPING)}
      <tr>
         <td>
            <select name="shipping">
               <option value="">{$LANG.form.please_select}</option>
               {foreach from=$SHIPPING key=group item=methods}
               {if $HIDE_OPTION_GROUPS ne '1'}
               <optgroup label="{$group}">{/if}
                  {foreach from=$methods item=method}
                  <option value="{$method.value}" {$method.selected}>{$CUSTOMER_LOCALE.mark} {$method.display}</option>
                  {/foreach}
                  {if $HIDE_OPTION_GROUPS ne '1'}
               </optgroup>
               {/if}
               {/foreach}
            </select>
         </td>
         <td width="10%" class="text-right">
            {$CUSTOMER_LOCALE.mark}{$SHIPPING_VALUE}
         </td>
      </tr>
      {/if}
      {foreach from=$TAXES item=tax}
      <tr>
         <td>
            {$tax.name}
         </td>
         <td width="10%" class="text-right">
            {$CUSTOMER_LOCALE.mark}{$tax.value}
         </td>
      </tr>
      {/foreach}
      {foreach from=$COUPONS item=coupon}
      <tr>
         <td>
            <a href="{$VAL_SELF}&remove_code={$coupon.remove_code}" title="{$LANG.common.remove}">{$coupon.voucher}</a>
         </td>
         <td width="10%" class="text-right">
            {$coupon.value}
         </td>
      </td>
      {/foreach}
      {if isset($DISCOUNT)}
      <tr>
         <td>
            {$LANG.basket.total_discount}
         </td>
         <td width="10%" class="text-right">
            {$DISCOUNT}
         </td>
      </tr>
      {/if}
      <tr>
         <td>
            {$LANG.basket.total_grand}
         </td>
         <td width="10%" class="text-right">
            {$TOTAL}
         </td>
      </tr>
      </table>
   </div>
   
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
                  <a href="{$gateway.help}" class="info" title="{$LANG.common.information}"><img src="images/icons/information.png" alt="{$LANG.common.information}"></a>
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
      <button type="submit" name="proceed" class="button right">{$CHECKOUT_BUTTON} <i class="fa fa-chevron-right"></i></button>
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