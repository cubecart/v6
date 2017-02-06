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
{if $POPULAR}
<div class="panel" id="box-popular">
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
{/if}