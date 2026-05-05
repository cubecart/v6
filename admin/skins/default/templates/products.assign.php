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
            <div class="product-search">
               <input type="text" id="product_search" class="textbox" rel="product" placeholder="{$LANG.common.type_to_search}" autocomplete="off">
            </div>
            <table width="100%" id="selected_products_table">
               <thead>
                  <tr>
                     <th>{$LANG.catalogue.title_products}</th>
                     <th nowrap="nowrap" width="150">{$LANG.catalogue.product_code}</th>
                     <th width="20">&nbsp;</th>
                  </tr>
               </thead>
               <tbody id="selected_products"></tbody>
               <tfoot>
                  <tr id="no_products_selected"><td colspan="3" class="text-center">{$LANG.catalogue.no_products}</td></tr>
               </tfoot>
            </table>
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
<script>window.CC_PRESELECTED = {$PRESELECTED_PRODUCTS_JSON nofilter};</script>
<script>
(function(){
   /* Different STORE_KEY per mode so the bulk-price-change running list and
      the category-assign running list don't share state. */
   var STORE_KEY = '{if $MODE=="prices"}cc_bulk_price_products{else}cc_assign_category_products{/if}';

   function loadList(){ try { return JSON.parse(localStorage.getItem(STORE_KEY) || '[]') || []; } catch(e){ return []; } }
   function saveList(l){ localStorage.setItem(STORE_KEY, JSON.stringify(l)); }

   function init(){
      if (typeof jQuery === 'undefined' || !jQuery.fn.autocomplete) { setTimeout(init, 50); return; }
      var $   = jQuery;
      var $in = $('#product_search');
      var $tb = $('#selected_products');
      var $em = $('#no_products_selected');
      if (!$in.length) return;

      function makeRow(p){
         var $row = $('<tr></tr>');
         $row.append($('<td></td>').text(p.name || '').prepend('<input type="hidden" name="product[]" value="'+p.id+'">'));
         $row.append($('<td nowrap="nowrap" width="150"></td>').text(p.code || ''));
         $row.append('<td width="20" class="text-center"><a href="#" class="remove-product" data-id="'+p.id+'" title="{$LANG.common.remove}"><i class="fa fa-times"></i></a></td>');
         return $row;
      }

      function renderAll(){
         var list = loadList();
         $tb.empty();
         list.forEach(function(p){ $tb.append(makeRow(p)); });
         $em.toggle(list.length === 0);
         if (typeof bulkPriceValidate === 'function') bulkPriceValidate();
      }

      // Merge any one-shot server-delivered preselection into the running localStorage list.
      var list = loadList();
      var fresh = (window.CC_PRESELECTED || []);
      var changed = false;
      fresh.forEach(function(p){
         if (!list.some(function(x){ return x.id === p.id; })) { list.push(p); changed = true; }
      });
      if (changed) saveList(list);
      renderAll();

      $in.autocomplete({
         timeout: 5e3,
         minchars: 2,
         ajax_get: ajaxSuggest,
         callback: function(item){
            if (!item || !item.id) return;
            var d = item.data || {};
            var p = { id: parseInt(item.id, 10), name: d.name || item.value || '', code: d.product_code || '' };
            var list = loadList();
            if (list.some(function(x){ return x.id === p.id; })) { $in.val(''); return; }
            list.push(p);
            saveList(list);
            $tb.append(makeRow(p));
            $em.hide();
            $in.val('');
            if (typeof bulkPriceValidate === 'function') bulkPriceValidate();
         }
      });

      $tb.on('click', '.remove-product', function(e){
         e.preventDefault();
         var id = parseInt($(this).data('id'), 10);
         saveList(loadList().filter(function(x){ return x.id !== id; }));
         $(this).closest('tr').remove();
         if (!$tb.children().length) $em.show();
         if (typeof bulkPriceValidate === 'function') bulkPriceValidate();
      });

      // Clear the running list when the form is actually submitted.
      $tb.closest('form').on('submit', function(){ localStorage.removeItem(STORE_KEY); });
   }
   if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', init);
   } else {
      init();
   }
})();
</script>