{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
{if $SALE_PRODUCTS && $CONFIG['catalogue_sale_mode']>0}
<div class="panel" id="box-sale_items">
  <h3>{$LANG.catalogue.title_saleitems}</h3>
  <ul>
  {foreach from=$SALE_PRODUCTS item=product}
	<li>
	  <a href="{$product.url}" title="{$product.name} ({if {$product.saving}}{$LANG.catalogue.saving} {$product.saving}{/if})">{$product.name}</a><br>
	  {if empty($product.sale_price_unformatted)}
    {$product.price}
    {else}
    <span class="old_price">{$product.price}</span> <span class="sale_price">{$product.sale_price}</span>
    {/if}
	</li>
  {/foreach}
  </ul>
</div>
{/if}