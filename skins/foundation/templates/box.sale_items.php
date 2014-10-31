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
{if $PRODUCTS}
<div class="panel">
  <h3>{$LANG.catalogue.title_saleitems}</h3>
  <ul>
  {foreach from=$PRODUCTS item=product}
	<li>
	  <a href="{$product.url}" title="{$product.name} ({if {$product.saving}}{$LANG.catalogue.saving} {$product.saving}{/if})">{$product.name}</a><br>
	  <span class="old_price">{$product.price}</span> <span class="sale_price">{$product.sale_price}</span>
	</li>
  {/foreach}
  </ul>
</div>
{/if}