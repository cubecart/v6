{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
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
         <table class="bulk-price">
            <thead>
               <tr>
                  <th id="th_target">{$LANG.common.apply}</th>
                  <th id="th_method" class="bulk-price-inactive">{$LANG.common.method}</th>
                  <th id="th_action" class="bulk-price-inactive">{$LANG.form.action}</th>
                  <th id="th_value" class="bulk-price-inactive">{$LANG.common.value}</th>
                  <th id="th_field" class="bulk-price-inactive">{$LANG.common.price}</th>
               </tr>
            </thead>
            <tbody>
               <tr>
                  <td>
                     <select name="price[what]" id="bulk_price_target" class="textbox" size="2">
                        <option value="products">{$LANG.catalogue.update_checked_products}</option>
                        <option value="categories">{$LANG.catalogue.update_checked_categories}</option>
                     </select>
                  </td>
                  <td>
                     <select name="price[method]" id="bulk_price_method" class="textbox" size="2" disabled>
                        <option value="fixed">{$LANG.catalogue.update_by_amount|strtolower}</option>
                        <option value="percent">{$LANG.catalogue.update_by_percent|strtolower}</option>
                     </select>
                  </td>
                  <td>
                     <select name="price[action]" id="bulk_price_action" class="textbox" size="3" disabled>
                        <option value="0">{$LANG.common.subtract|strtolower}</option>
                        <option value="1">{$LANG.common.add|strtolower}</option>
                        <option value="2">{$LANG.common.set_to|strtolower}</option>
                     </select>
                  </td>
                  <td>
                     <input type="text" name="price[value]" id="bulk_price_value" value="" class="textbox number" disabled>
                     <span id="bulk_price_percent_symbol">&percnt;</span>
                  </td>
                  <td>
                     <select name="price[field][]" id="bulk_price_field" class="textbox" multiple size="6" disabled>
                        <option value="all">{$LANG.common.all}</option>
                        <option value="price">{$LANG.common.price_standard}</option>
                        <option value="sale_price">{$LANG.common.price_sale}</option>
                        <option value="cost_price">{$LANG.common.price_cost}</option>
                        <option value="quantity_discounts">{$LANG.catalogue.quantity_discounts}</option>
                        <option value="product_options">{$LANG.catalogue.title_product_options}</option>
                        {if $CUSTOMER_GROUPS}
                        <optgroup label="{$LANG.catalogue.customer_group_pricing}">
                           <option value="group_pricing">{$LANG.common.all} {$LANG.customer.title_groups}</option>
                           {foreach from=$CUSTOMER_GROUPS item=group}
                           <option value="group_pricing_{$group.group_id}">{$group.group_name}</option>
                           {/foreach}
                        </optgroup>
                        {/if}
                     </select>
                  </td>
               </tr>
            </tbody>
         </table>
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
      {if isset($PLUGIN_TABS)}
   {foreach from=$PLUGIN_TABS item=tab}
   {$tab}
   {/foreach}
   {/if}
{include file='templates/element.hook_form_content.php'}
   <div class="form_control">
      <input type="submit" {if $MODE=='prices'}name="save" id="bulk_price_save" disabled{/if} value="{if $MODE=='prices'}{$LANG.common.update}{else}{$LANG.common.save}{/if}">
   </div>
   
</form>