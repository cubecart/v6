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
<form action="{$VAL_SELF}" method="post">
   <div id="assign" class="tab_content">
      
      {if $MODE!=='prices'}
      <h3>{$LANG.catalogue.title_category_assigned}</h3>
      <h4>{$LANG.catalogue.category_assign_1}</h4>
      {else}
      <h3>{$LANG.catalogue.title_bulk_prices}</h3>
      <fieldset>
         <legend>{$LANG.catalogue.title_prices_update}</legend>
         <div>
            <select name="price[what]" id="bulk_price_target">
               <option value="products">{$LANG.catalogue.update_checked_products}</option>
               <option value="categories">{$LANG.catalogue.update_checked_categories}</option>
            </select>
            <select name="price[method]" id="bulk_price_method">
               <option value="fixed">{$LANG.catalogue.update_by_amount}</option>
               <option value="percent">{$LANG.catalogue.update_by_percent}</option>
            </select>
            <select name="price[action]" id="bulk_price_action">
               <option value="0">{$LANG.common.subtract}</option>
               <option value="1">{$LANG.common.add}</option>
               <option value="2">{$LANG.common.set_to}</option>
            </select>
            <input type="text" name="price[value]" value="" class="textbox number">
            <span id="bulk_price_percent_symbol" style="display:none">%</span>
            <select name="price[field]">
               <option value="all">{$LANG.common.price_standard}, {$LANG.common.price_sale}, {$LANG.common.price_cost}, {$LANG.catalogue.quantity_discounts} &amp; {$LANG.catalogue.title_product_options}</option>
               <option value="price">{$LANG.common.price_standard}</option>
               <option value="sale_price">{$LANG.common.price_sale}</option>
               <option value="cost_price">{$LANG.common.price_cost}</option>
               <option value="quantity_discounts">{$LANG.catalogue.quantity_discounts}</option>
               <option value="product_options">{$LANG.catalogue.title_product_options}</option>
            </select>
         </div>
      </fieldset>
      {/if}
      <fieldset id="bulk_update_products">
         <div class="cat_product_assign">
            {if $PRODUCTS}
            <table width="100%">
               <thead>
                  <tr>
                     <th width="10"><input type="checkbox" name="" value="" class="check-all" rel="products"></th>
                     <th>{$LANG.catalogue.title_products}</th>
                     <th nowrap="nowrap" width="150">{$LANG.catalogue.product_code}</th>
                  </tr>
               </thead>
               <tbody>
                  {foreach from=$PRODUCTS item=product}
                  <tr>
                     <td width="10"><input type="checkbox" name="product[]" class="products" value="{$product.product_id}"></td>
                     <td>{$product.name}</td>
                     <td nowrap="nowrap" width="150">{$product.product_code}</td>
                  </tr>
                  {/foreach}
               </tbody>
            </table>
            {else}
            {$LANG.catalogue.notify_inv_empty}
            {/if}
         </div>
      </fieldset>
      {if $MODE!=='prices'}
      <h4>{$LANG.catalogue.category_assign_2}</h4>
      {/if}
      {if isset($CATEGORIES)}
      <fieldset id="bulk_update_categories"{if $MODE=='prices'} style="display:none"{/if}>
         <div class="cat_product_assign">
            <table width="100%">
               <thead>
                  <tr>
                     <th width="10">&nbsp;</th>
                     <th>{$LANG.settings.title_category}</th>
                  </tr>
               </thead>
               <tbody>
                  {foreach from=$CATEGORIES item=category}
                  <tr>
                     <td width="10"><input type="checkbox" name="category[]" value="{$category.id}"></td>
                     <td>{$category.name}</td>
                  </tr>
                  {/foreach}
               </tbody>
            </table>
         </div>
      </fieldset>
      {/if}
      {if $MODE!=='prices'}
      <h4>{$LANG.catalogue.category_assign_3}</h4>
      {/if}
   </div>
   {include file='templates/element.hook_form_content.php'}
   <div class="form_control">
      <input type="submit" value="{$LANG.common.save}">
   </div>
   
</form>