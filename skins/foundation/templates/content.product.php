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
{if isset($PRODUCT) && $PRODUCT}
<div itemscope itemtype="http://schema.org/Product">
   <form action="{$VAL_SELF}" method="post" class="add_to_basket">
      <div class="row">
         <div class="small-12 columns">
            <h1 itemprop="name">{$PRODUCT.name}</h1>
         </div>
      </div>
      {*
         Change include below to 'templates/element.product.horizontal_gallery.php'
         for horizontal gallery and vice versa.
      *}
      {include file='templates/element.product.vertical_gallery.php'}
      <hr>
      <dl class="tabs" data-tab data-options="scroll_to_content:false">
         {if !empty($PRODUCT.description)}
         <dd class="active"><a href="#product_info">{$LANG.catalogue.product_info}</a></dd>
         {/if}
         <dd><a href="#product_spec">{$LANG.common.specification}</a></dd>
         {if isset($PRODUCT.discounts)}
         <dd><a href="#quantity_discounts">{$LANG.catalogue.quantity_discounts}</a></dd>
         {/if}
         {foreach from=$PRODUCT_TABS_TITLES item=product_tab_title}
            {$product_tab_title}
         {/foreach}
      </dl>
      <div class="tabs-content">
         {if !empty($PRODUCT.description)}
         <div class="content active" id="product_info" itemprop="description">
            {$PRODUCT.description}
         </div>
         {/if}
         <div class="content{if empty($PRODUCT.description)} active{/if}" id="product_spec">
            <table>
               <tbody>
                  <tr>
                     <td>{$LANG.catalogue.product_code}</td>
                     <td>{$PRODUCT.product_code}</td>
                  </tr>
                  {if $PRODUCT.manufacturer}
                  <tr>
                     <td>{$LANG.catalogue.manufacturer}</td>
                     <td>{$MANUFACTURER}</td>
                  </tr>
                  {/if}
                  {if $PRODUCT.stock_level}
                  <tr>
                     <td>{$LANG.catalogue.stock_level}</td>
                     <td>{$PRODUCT.stock_level}</td>
                  </tr>
                  {/if}
                  <tr>
                     <td>{$LANG.common.condition}</td>
                     <td>{$PRODUCT.condition}</td>
                  </tr>
                  {if $PRODUCT.product_weight > 0}
                  <tr>
                     <td>{$LANG.common.weight}</td>
                     <td>{$PRODUCT.product_weight}{$CONFIG.product_weight_unit|lower}</td>
                  </tr>
                  {/if}
               </tbody>
            </table>
         </div>
         {if isset($PRODUCT.discounts)}
         <div class="content" id="quantity_discounts">
            <p>{$LANG.catalogue.quantity_discounts_explained}</p>
            <br>
            <table>
               <thead>
                  <tr>
                     <th>{$LANG.common.quantity}</th>
                     <th>{$LANG.catalogue.price_per_unit}</th>
                  </tr>
               </thead>
               <tbody>
                  {foreach from=$PRODUCT.discounts item=discount}
                  <tr>
                     <td>{$discount.quantity}+</td>
                     <td>{$discount.price}</td>
                  </tr>
                  {/foreach}
               </tbody>
            </table>
         </div>
         {/if}
         {if isset($PRODUCT_TABS_CONTENTS)}
            {foreach from=$PRODUCT_TABS_CONTENTS item=product_tab_content}
               {$product_tab_content}
            {/foreach}
         {/if}
      </div>
   </form>
   {if $SHARE}
   <hr>
   {foreach from=$SHARE item=html}
   {$html}
   {/foreach}
   {/if}
   <hr>
   {include file='templates/element.product_reviews.php'}
   {foreach from=$COMMENTS item=html}
   {$html}
   {/foreach}
</div>
<div class="hide" id="validate_field_required">{$LANG.form.field_required}</div>
{else}
<p>{$LANG.catalogue.product_doesnt_exist}</p>
{/if}