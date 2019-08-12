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
<form method="post" action="?_a=category" id="advanced_search_form" enctype="multipart/form-data">
   <h2>{$LANG.search.advanced}</h2>
   <div class="row">
      <div class="small-8 columns"><label for="keywords">{$LANG.search.keywords}</label>
      <input type="text" name="search[keywords]" placeholder="{$LANG.search.keywords}" class="search_input" id="keywords" required>
      </div>
   </div>
   <div class="row">
      <div class="small-8 columns">
         <label for="">{$LANG.search.price_range}</label>
      </div>
   </div>
   <div class="row">
      <div class="small-2 columns">
         <input type="text" name="search[priceMin]" placeholder="{$LANG.common.from}">
      </div>
      <div class="small-1 columns text-center">-</div>
      <div class="small-2 columns">
         <input type="text" name="search[priceMax]" placeholder="{$LANG.common.to}"> 
      </div>
      <div class="small-3 small-pull-4 columns">
         <input type="checkbox" name="search[priceVary]" value="1"> &plusmn;5%
      </div>
   </div>
   {if isset($MANUFACTURERS)}
   <div class="row">
      <div class="small-8 columns">
         <label for="">{$LANG.catalogue.manufacturer}</label>
      </div>
   </div>
   <div class="row">
      <div class="small-12 columns">
         {* Uncomment the following include to show a grid of checkboxes. Manufacturers names should be very short. *}
         {* include file='templates/element.search.manufacturers.checkbox.grid.php' *}

         {* Uncomment the following include to show a table of checkboxes. Manufacturers names can be very long. *}
         {* include file='templates/element.search.manufacturers.checkbox.table.php' *}

         {* Uncomment the following include to show a drop-down selector. Manufacturers names can be long and numerous. *}
         {include file='templates/element.search.manufacturers.select.chosen.php'}
      </div>
   </div>
   {/if}
   {if isset($SORTERS)}
   <div class="row">
      <div class="small-8 columns">
         <label for="sort">{$LANG.form.sort_by}</label>
         <select name="sort" id="sort">
         {foreach from=$SORTERS item=sort}
         <option value="{$sort.field}|{$sort.order}" {$sort.selected}>{$sort.name} ({$sort.direction})</option>
         {/foreach}
         </select>
      </div>
   </div>
   {/if}
   {if !isset($OUT_OF_STOCK)}
   <div class="row">
      <div class="small-8 columns"><input type="checkbox" name="search[inStock]" id="in_stock" value="1"><label for="in_stock">{$LANG.search.in_stock}</label></div>
   </div>
   {/if}
   <div class="row">
      <div class="small-8 columns"><input type="checkbox" name="search[featured]" id="featured_only" value="1"><label for="featured_only">{$LANG.search.featured_only}</label></div>
   </div>
   <div class="row">
      <div class="small-8 columns clearfix">
      <input type="submit" class="button" value="{$LANG.common.search}"> <button type="reset" class="button secondary right"><svg class="icon"><use xlink:href="#icon-refresh"></use></svg> {$LANG.common.reset}</button>
   </div></div>
</form>