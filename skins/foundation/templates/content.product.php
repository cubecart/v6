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
{if isset($PRODUCT) && $PRODUCT}
<div>
   <form action="{$VAL_SELF}" method="post" class="add_to_basket">
      <div class="row">
         <div class="small-12 columns">
            <h1>{$PRODUCT.name}</h1>
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
         {if !empty($PRODUCT.discounts)}
         <dd><a href="#quantity_discounts">{$LANG.catalogue.quantity_discounts}</a></dd>
         {/if}
         {foreach from=$PRODUCT_TABS_TITLES item=product_tab_title}
            {if isset($product_tab_title.content_id) && isset($product_tab_title.title)}
         <dd><a href="#{$product_tab_title.content_id}">{$product_tab_title.title}</a></dd>
            {else}
         {$product_tab_title}
            {/if}
         {/foreach}
      </dl>
      <div class="tabs-content">
         {if !empty($PRODUCT.description)}
         <div class="content active" id="product_info">
            {$PRODUCT.description}
         </div>
         {/if}
         {include file='templates/element.product.specs.php'}
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
                  <tr>
                     <td class="text-center">1</td>
                     <td class="text-center">{if $PRODUCT.ctrl_sale}{$PRODUCT.sale_price}{else}{$PRODUCT.price}{/if}</td>
                  </tr>
                  {foreach from=$PRODUCT.discounts item=discount}
                  <tr>
                     <td class="text-center">{$discount.quantity}+</td>
                     <td class="text-center">{$discount.price}</td>
                  </tr>
                  {/foreach}
               </tbody>
            </table>
         </div>
         {/if}
        {foreach from=$PRODUCT_TABS_CONTENTS item=product_tab_content}
            {if isset($product_tab_content.content_id) && isset($product_tab_content.content)}
        <div class="{if !empty($product_tab_content.content_class)}{$product_tab_content.content_class}{else}content{/if}" id="{$product_tab_content.content_id}">{$product_tab_content.content}</div>
            {else}
        {$product_tab_content}
            {/if}
        {/foreach}
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