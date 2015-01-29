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
{if isset($DOCUMENT)}
<div id="content_homepage">
   <h1>{$DOCUMENT.title}</h1>
   {$DOCUMENT.content}
</div>
{/if}
{if isset($LATEST_PRODUCTS)}
<div id="content_latest_products">
   <h2>{$LANG.catalogue.latest_products}</h2>
   <ul class="small-block-grid-1 medium-block-grid-3 large-block-grid-3" data-equalizer>
      {foreach from=$LATEST_PRODUCTS item=product}
      <li>
         <form action="{$VAL_SELF}" method="post" class="panel add_to_basket" data-equalizer-watch>
            <div class="text-center">
               <a class="th" href="{$product.url}" title="{$product.name}"><img src="{$product.image}" alt="{$product.name}"></a>
            </div>
            <h3><a href="{$product.url}" title="{$product.name}">{$product.name|truncate:38:"&hellip;"}</a></h3>
            {if $product.ctrl_sale}
            <span class="old_price">{$product.price}</span> <span class="sale_price">{$product.sale_price}</span>
            {else}
            {$product.price}
            {/if}
            {if $product.review_score && $CTRL_REVIEW}
            <div class="rating"> {for $i = 1; $i <= 5; $i++}
               {if $product.review_score >= $i} <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/star.png" alt=""> {elseif $product.review_score > ($i - 1) && $product.review_score < $i} <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/star_half.png" alt=""> {else} <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/star_off.png" alt=""> {/if}
               {/for} 
            </div>
            {/if}
            <!--<a href="{$product.url}" title="{$product.name}" class="button tiny secondary left">{$LANG.common.info}</a>-->
            {if $product.available == '0'}
               <input type="submit" value="{$LANG.common.unavailable}" class="button small disabled expand marg-top" disabled>
            {elseif $product.ctrl_stock && !$CATALOGUE_MODE}
            <div class="marg-top">
               <div class="row collapse marg-top">
                  <div class="small-3 columns">
                     <input type="text" name="quantity" value="1" class="quantity required text-center">
                  </div>
                  <div class="small-9 columns ">
                     <button type="submit" value="{$LANG.catalogue.add_to_basket}" class="button small postfix">{$LANG.catalogue.add_to_basket}</button>
                  </div>
               </div>
            </div>
            {elseif !$CATALOGUE_MODE}
            <input type="submit" value="{$LANG.catalogue.out_of_stock_short}" class="button small disabled expand marg-top" disabled>
            {/if}
            <input type="hidden" name="add" value="{$product.product_id}">
         </form>
      </li>
      {/foreach}
   </ul>
</div>
{/if}