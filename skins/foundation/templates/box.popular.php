{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 *}
<div class="panel">
  <h3>{$LANG.catalogue.title_popular}</h3>
  <ol>
	{foreach from=$POPULAR item=product}
	<li><a href="{$product.url}" title="{$product.name}">{$product.name}</a><br>
	{if $product.ctrl_sale}
         <span class="old_price">{$product.price}</span> <span class="sale_price">{$product.sale_price}</span>
    {else}
         {$product.price}
    {/if}
	</li>
	{/foreach}
  </ol>
</div>