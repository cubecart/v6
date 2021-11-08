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
<h2>{$category.cat_name}</h2>
{if isset($category.image)}
<div class="row">
   <div class="small-12 columns"><img src="{$category.image}" alt="{$category.cat_name}" class="marg-bottom"></div>
</div>
{/if}
{if !empty($category.cat_desc)}
<div class="row">
   <div class="small-12 columns">{$category.cat_desc}</div>
</div>
{/if}
{if isset($SUBCATS) && $SUBCATS}
<ul class="medium-block-grid-6 text-center small-block-grid-3" data-equalizer>
   {foreach from=$SUBCATS item=subcat}
   <li data-equalizer-watch>
      <a href="{$subcat.url}" title="{$subcat.cat_name}">
        <img class="th" src="{$subcat.cat_image}" alt="{$subcat.cat_name}">
      </a>
      <br>
      <a href="{$subcat.url}" title="{$subcat.cat_name}"><small>{$subcat.cat_name}</small></a>
   </li>
   {/foreach}
</ul>
{/if}
{if $PRODUCTS}
<div class="row">
   <div class="small-12 medium-8 columns">
      {if isset($SORTING)}
      <form action="{$VAL_SELF}" class="autosubmit" method="post">
         <div class="row">
            <div class="small-3 medium-2 columns">
               <label for="product_sort">{$LANG.form.sort_by}</label>
            </div>
            <div class="small-9 medium-5 columns left">
               <select name="sort" id="product_sort">
                  <option value="" disabled>{$LANG.form.please_select}</option>
                  {foreach from=$SORTING item=sort}
                  <option value="{$sort.field}|{$sort.order}" {$sort.selected}>{$sort.name} ({$sort.direction})</option>
                  {/foreach}
               </select>
               <input type="submit" value="{$LANG.form.sort}" class="hide">
            </div>
         </div>
      </form>
      {/if}
   </div>
   <div class="medium-4 columns show-for-medium-up">
      <dl class="sub-nav right" id="layout_toggle">
         <dd><a href="#" class="grid_view"><svg class="icon"><use xlink:href="#icon-th-large"></use></svg></a></dd>
         <dd class="active"><a href="#" class="list_view"><svg class="icon"><use xlink:href="#icon-th-list"></use></svg></a></dd>
      </dl>
   </div>
</div>
{/if}
<div id="ccScroll">
   <ul class="small-block-grid-1 product_list" data-equalizer>
      {foreach from=$PRODUCTS item=product}
      <li>
         <form action="{$VAL_SELF}" method="post" class="panel add_to_basket">
            <div class="row product_list_view">
               <div class="small-3 columns">
                  <a href="{$product.url}" class="th" title="{$product.name}">
                  <img src="{$product.thumbnail}" alt="{$product.name}">
                  </a>
               </div>
               <div class="small-6 columns">
                  <h3>
                     <a href="{$product.url}" title="{$product.name}">{$product.name}</a> 
                  </h3>
                  {if $product.review_score}
                  <div>
                     {for $i = 1; $i <= 5; $i++}
                     {if $product.review_score >= $i}
                     <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/star.png" alt="">
                     {elseif $product.review_score > ($i - 1) && $product.review_score < $i}
                     <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/star_half.png" alt="">
                     {else}
                     <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/star_off.png" alt="">
                     {/if}
                     {/for}
                  </div>
                  {*
                  <p class="rating-info">{$product.review_info}</p>
                  *}
                  {/if}
                  {$product.description_short}
               </div>
               <div class="small-3 columns">
                  <h3>
                     {if $product.ctrl_sale}<span class="old_price">{$product.price}</span> <span class="sale_price">{$product.sale_price}</span>
                     {else}
                     {$product.price}
                     {/if}
                  </h3>
                  {if $product.available <= 0}
                  <div class="row collapse">
                     <div class="small-12 columns">
                        <input type="submit" value="{$LANG.common.unavailable}" class="button small postfix disabled expand marg-top" disabled>
                     </div>
                  </div>
                  {* ctrl_stock True when a product is considered 'in stock' for purposes of allowing a purchase, either by actually being in stock or via certain settings *}
                  {elseif $product.ctrl_stock && !$CATALOGUE_MODE}
                  <div class="row collapse">
                     <div class="small-4 columns">
                        <input type="text" name="quantity" value="{$product.minimum_quantity|default:'1'}" maxlength="3" class="quantity text-center">
                     </div>
                     <div class="small-8 columns">
                        <button type="submit" value="{$LANG.catalogue.add_to_basket}" class="button small postfix">{$LANG.catalogue.add_to_basket}</button>
                        <input type="hidden" name="add" value="{$product.product_id}">
                     </div>
                  </div>
                  {elseif !$CATALOGUE_MODE}
                  <div class="row collapse">
                     <div class="small-12 columns">
                        <input type="submit" value="{$LANG.catalogue.out_of_stock_short}" disabled class="button disabled expand small">
                     </div>
                  </div>
                  {/if}
               </div>
            </div>
            <div class="product_grid_view hide">
               <div data-equalizer-watch>
                  <div class="text-center">
                     <a href="{$product.url}" title="{$product.name}"><img class="th" src="{$product.thumbnail}" alt="{$product.name}"></a>
                  </div>
                  <h3><a href="{$product.url}" title="{$product.name}">{$product.name|truncate:38:"&hellip;"}</a></h3>
                  {if $product.review_score}
                  <div class="rating">
                     <div>
                        {for $i = 1; $i <= 5; $i++}
                        {if $product.review_score >= $i}
                        <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/star.png" alt="">
                        {elseif $product.review_score > ($i - 1) && $product.review_score < $i}
                        <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/star_half.png" alt="">
                        {else}
                        <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/star_off.png" alt="">
                        {/if}
                        {/for}
                     </div>
                     {*
                     <p class="rating-info">{$product.review_info}</p>
                     *}
                  </div>
                  {/if}
               </div>
               <h3>
                  {if $product.ctrl_sale}<span class="old_price">{$product.price}</span> <span class="sale_price">{$product.sale_price}</span>
                  {else}
                  {$product.price}
                  {/if}
               </h3>
               {* Uncomment this if you want to show a more info link
               <a href="{$product.url}" title="{$product.name}" class="button tiny secondary left">{$LANG.common.info}</a>
               *}
               {if $product.available <= 0}
               <div class="row collapse marg-top">
                  <div class="small-12 columns">
                     <input type="submit" value="{$LANG.common.unavailable}" class="button small postfix disabled expand" disabled>
                  </div>
               </div>
               {* ctrl_stock True when a product is considered 'in stock' for purposes of allowing a purchase, either by actually being in stock or via certain settings *}
               {elseif $product.ctrl_stock && !$CATALOGUE_MODE}
               <div class="row collapse marg-top">
                  <div class="small-3 columns">
                     <input type="number" name="quantity" value="{$product.minimum_quantity|default:'1'}" min="{$product.minimum_quantity}" maxlength="3" class="quantity text-center" disabled>
                  </div>
                  <div class="small-9 columns ">
                     <button type="submit" value="{$LANG.catalogue.add_to_basket}" class="button small postfix">{$LANG.catalogue.add_to_basket}</button>
                     <input type="hidden" name="add" value="{$product.product_id}">
                  </div>
               </div>
                {elseif !$CATALOGUE_MODE}
               <div class="row collapse marg-top">
                  <div class="small-12 columns">
                     <input type="submit" value="{$LANG.catalogue.out_of_stock_short}" class="button small postfix disabled expand marg-top" disabled>
                  </div>
               </div>
               {/if}
            </div>
         </form>
      </li>
      {foreachelse}
      {if !isset($SUBCATS) || !$SUBCATS}
      <li>{$LANG.category.no_products}</li>
      {/if}
      {/foreach}
   </ul>
   {* Uncomment for traditional pagination *}
   {*
   <div class="row">
      <div class="small-12 large-8 columns">
         {$PAGINATION}
      </div>
      <div class="large-4 columns show-for-medium-up">
         <dl>
            <dd>
               <select class="url_select">
               {foreach from=$PAGE_SPLITS item=page_split}
               <option value="{$page_split.url}"{if $page_split.selected} selected{/if}>{$LANG.common.show} {$page_split.amount} {$LANG.settings.product_per_page|lower}</option>
               {/foreach}
               </select>
            </dd>
         </dl>
      </div>
   </div>
   *}
   <div class="hide" id="ccScrollCat">{$category.cat_id}</div>
   {if $page!=='all' && ($page < $total)}
   {$params[$var_name] = $page + 1}
   {* Add "hide-for-medium-up" to the class attribute to not display the more button *}
   <a href="{$current}{http_build_query($params)}{$anchor}" data-next-page="{$params[$var_name]}" data-cat="{$category.cat_id}" class="button tiny expand ccScroll-next">{$LANG.common.more} <svg class="icon"><use xlink:href="#icon-angle-down"></use></svg></a>
   {/if}
   <div class="text-center hide" id="loading"><svg class="icon-x3"><use xlink:href="#icon-spinner"></use></svg></div>
</div>