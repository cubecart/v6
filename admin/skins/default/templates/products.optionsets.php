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
      <h3>{$LANG.catalogue.title_option_set_assign}</h3>
      <fieldset>
         <legend>{$LANG.catalogue.title_option_sets}</legend>
         <div class="stripe">
            {foreach from=$OPTION_SETS item=set}
            <div>
               <span><input type="checkbox" name="set[]" value="{$set.set_id}"></span>
               {$set.set_name}
            </div>
            {foreachelse}
            <div>{$LANG.catalogue.no_option_sets}</div>
            {/foreach}
         </div>
      </fieldset>

      <fieldset id="bulk_optionset_products">
         <legend>{$LANG.catalogue.title_products}</legend>
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
   </div>

   {if isset($PLUGIN_TABS)}
   {foreach from=$PLUGIN_TABS item=tab}
   {$tab}
   {/foreach}
   {/if}
   {include file='templates/element.hook_form_content.php'}

   <div class="form_control">
      <input type="submit" value="{$LANG.common.save}">
   </div>
</form>
<script>window.CC_PRESELECTED = {$PRESELECTED_PRODUCTS_JSON nofilter};</script>
<script>
(function(){
   /* Mirrors the bulk-price-change product picker: autocomplete -> running
      list persisted in localStorage so the cart of products survives reloads
      until the form is actually submitted. The XML search endpoint behind
      ajaxSuggest caps results at 15, so the page never tries to render the
      whole catalogue (which OOMs at scale). */
   var STORE_KEY = 'cc_optionset_products';
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
      }

      // Merge any one-shot server-delivered preselection into the running localStorage list.
      var list0 = loadList();
      var fresh = (window.CC_PRESELECTED || []);
      var changed = false;
      fresh.forEach(function(p){
         if (!list0.some(function(x){ return x.id === p.id; })) { list0.push(p); changed = true; }
      });
      if (changed) saveList(list0);
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
         }
      });

      $tb.on('click', '.remove-product', function(e){
         e.preventDefault();
         var id = parseInt($(this).data('id'), 10);
         saveList(loadList().filter(function(x){ return x.id !== id; }));
         $(this).closest('tr').remove();
         if (!$tb.children().length) $em.show();
      });

      // Drop the running list once the form has actually been submitted.
      $tb.closest('form').on('submit', function(){ localStorage.removeItem(STORE_KEY); });
   }
   if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', init);
   } else {
      init();
   }
})();
</script>
