<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */
?>
<h2>{$category.cat_name}</h2>
{if isset($category.image)}
<div class="row">
   <div class="small-12 columns"><img src="{$category.image}" alt="{$category.cat_name}"></div>
</div>
{/if}
{if !empty($category.cat_desc)}
<div class="row">
   <div class="small-12 columns">{$category.cat_desc}</div>
</div>
{/if}
{if isset($SUBCATS) && $SUBCATS}
<ul class="small-block-grid-6 text-center show-for-medium-up" data-equalizer>
   {foreach from=$SUBCATS item=subcat}
   <li data-equalizer-watch>
      <a href="{$subcat.url}" title="{$subcat.cat_name}">
      <img class="th" src="{$subcat.cat_image}" alt="{$subcat.cat_name}">
      </a>
      <a href="{$subcat.url}" title="{$subcat.cat_name}"><small>{$subcat.cat_name}</small></a>
   </li>
   {/foreach}
</ul>
{/if}
{if $PRODUCTS}
<div class="row">
   <div class="small-12 large-8 columns">
      {if isset($SORTING)}
      <form action="{$VAL_SELF}" class="autosubmit" method="post">
         <div class="row">
            <div class="small-2 columns">
               <label for="product_sort">{$LANG.form.sort_by}</label>
            </div>
            <div class="small-5 columns left">
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
   <div class="large-4 columns show-for-medium-up">
      <dl class="sub-nav right" id="layout_toggle">
         <dd><a href="#" class="grid_view"><i class="fa fa-th-large"></i></a></dd>
         <dd class="active"><a href="#" class="list_view"><i class="fa fa-th-list"></i></a></dd>
      </dl>
   </div>
</div>
{/if}
<div id="jscroll">
	<ul class="small-block-grid-1 product_list" data-equalizer>
	   {foreach from=$PRODUCTS item=product}
	   <li data-equalizer-watch>
	      <form action="{$VAL_SELF}" method="post" class="panel" id="add_to_basket" >
	         <div class="row product_list_view">
	            <div class="small-3 columns">
	               <a href="{$product.url}" title="{$product.name}">
	               <img class="th" src="{$product.thumbnail}" alt="{$product.name}">
	               </a>
	            </div>
	            <div class="small-6 columns">
	               <h3>
	                  <a href="{$product.url}" title="{$product.name}">{$product.name}</a> 
	               </h3>
	               {$product.description_short}
	               {if $product.review_score}
	               <!--
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
	                  <p class="rating-info">{$product.review_info}</p>
	                  -->
	               {/if}
	            </div>
	            <div class="small-3 columns">
	               <h3>
	                  {if $product.ctrl_sale}<span class="old_price">{$product.price}</span> <span class="sale_price">{$product.sale_price}</span>
	                  {else}
	                  {$product.price}
	                  {/if}
	               </h3>
	               {if $product.ctrl_purchase && !$CATALOGUE_MODE}
	               <div class="row collapse">
	                  <div class="small-4 columns">
	                     <input type="text" name="add[{$product.product_id}][quantity]" value="1" class="quantity text-center">
	                  </div>
	                  <div class="small-8 columns">
	                     <button type="submit" value="{$LANG.catalogue.add_to_basket}" class="button small postfix">{$LANG.catalogue.add_to_basket}</button>
	                  </div>
	               </div>
	               {elseif $product.out}
	               <input type="submit" value="{$LANG.catalogue.out_of_stock_short}" disabled class="button disabled expand small">
	               {/if}
	            </div>
	         </div>
	         <div class="product_grid_view hide">
	            <div class="text-center">
	               <a href="{$product.url}" title="{$product.name}"><img class="th" src="{$product.thumbnail}" alt="{$product.name}"></a>
	            </div>
	            <h3><a href="{$product.url}" title="{$product.name}">{$product.name|truncate:38:"&hellip;"}</a></h3>
	            <h3>
	               {if $product.ctrl_sale}<span class="old_price">{$product.price}</span> <span class="sale_price">{$product.sale_price}</span>
	               {else}
	               {$product.price}
	               {/if}
	            </h3>
	            <div class="rating">
	               {if $product.review_score}
	               <!--
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
	                  <p class="rating-info">{$product.review_info}</p>
	                  -->
	               {/if}
	            </div>
	            <!--<a href="{$product.url}" title="{$product.name}" class="button tiny secondary left">{$LANG.common.info}</a>-->
	            {if $product.ctrl_purchase && !$CATALOGUE_MODE}
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
	   {foreachelse}
	   {if !isset($SUBCATS) || !$SUBCATS}
   			<li>{$LANG.category.no_products}</li>
   		{/if}
	   {/foreach}
	</ul>
	<div class="row hide">
	   <div class="small-12 large-9 columns">
	      {$PAGINATION}
	   </div>
	   <div class="large-3 columns show-for-medium-up">
	      <dl>
	         <dd>
	            <select class="url_select">
	            {foreach from=$PAGE_SPLITS item=page_split}
	            <option value="{$page_split.url}"{if $page_split.selected} selected{/if}>{$page_split.amount}</option>
	            {/foreach}
	            </select>
	         </dd>
	      </dl>
	   </div>
	</div>
	{if ($page < $total)}
	{$params[$var_name] = $page + 1}
	<a href="{$current}{http_build_query($params)}{$anchor}" class="button tiny expand" id="jscroll-next">{$LANG.common.more} <i class="fa fa-angle-down"></i></a>
	{/if}
</div>
<div class="hide" id="lang_loading">{$LANG.common.loading}</div>