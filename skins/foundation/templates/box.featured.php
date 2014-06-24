{if $featured}
<div class="panel">
  <h3>{$LANG.catalogue.title_feature}</h3>
	<a class="th" href="{$featured.url}" title="{$featured.name}">
	  <img src="{$featured.image}" alt="{$featured.name}">
	</a>
  <h4><a href="{$featured.url}" title="{$featured.name}">{$featured.name}</a></h4>
  {if $featured.ctrl_sale}
         <span class="old_price">{$featured.price}</span> <span class="sale_price">{$featured.sale_price}</span>
    {else}
         {$featured.price}
    {/if}
</div>
{/if}