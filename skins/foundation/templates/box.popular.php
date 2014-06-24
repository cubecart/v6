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