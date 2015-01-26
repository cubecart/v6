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
<form action="{$VAL_SELF}" method="post">
   <div id="assign" class="tab_content">
      <h3>{$LANG.catalogue.title_category_assign_to}</h3>
      <fieldset>
         <legend>{$LANG.catalogue.title_products}</legend>
         <div class="cat_product_assign">
            {if $PRODUCTS}
            <table width="700">
               <thead>
                  <tr>
                     <th width="10"><input type="checkbox" name="" value="" class="check-all" rel="products"></th>
                     <th>{$LANG.catalogue.product_name}</th>
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
      <fieldset>
         <legend>{$LANG.catalogue.title_prices_update}</legend>
         <div>
            <select name="price[what]">
               <option value="products">{$LANG.catalogue.update_checked_products}</option>
               <option value="categories">{$LANG.catalogue.update_checked_categories}</option>
            </select>
            <select name="price[method]">
               <option value="fixed">{$LANG.catalogue.update_by_amount}</option>
               <option value="percent">{$LANG.catalogue.update_by_percent}</option>
            </select>
            <select name="price[action]">
               <option value="0">{$LANG.common.subtract}</option>
               <option value="1">{$LANG.common.add}</option>
            </select>
            <input type="text" name="price[value]" value="" class="textbox number">
            <select name="price[field]">
               <option value="price">{$LANG.common.price_standard}</option>
               <option value="sale_price">{$LANG.common.price_sale}</option>
               <option value="cost_price">{$LANG.common.price_cost}</option>
            </select>
         </div>
      </fieldset>
      {if isset($CATEGORIES)}
      <fieldset>
         <legend>{$LANG.settings.title_category}</legend>
         <div class="cat_product_assign">
            <table width="700">
               <thead>
                  <tr>
                     <th width="10">&nbsp;</th>
                     <th>{$LANG.settings.category_name}</th>
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
   </div>
   {include file='templates/element.hook_form_content.php'}
   <div class="form_control">
      <input type="submit" value="{$LANG.common.save}">
   </div>
   <input type="hidden" name="token" value="{$SESSION_TOKEN}">
</form>