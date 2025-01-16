{if $RELATED}
<div class="show-for-medium-up">
    <h2>{$LANG.catalogue.related_products}</h2>
    <ul class="small-block-grid-5 no-bullet" data-equalizer>
    {foreach from=$RELATED item=product}
        <li>
            <div class="panel">
                <div data-equalizer-watch>
                    <div class="text-center">
                        <a href="{$product.url}" title="{$product.name}"><img src="{$product.img_src}" class="th" alt="{$product.name}"></a>
                    </div>
                    <h3><a href="{$product.url}" title="{$product.name}">{$product.name}</a></h3>
                </div>
                <p>
                {if $product.ctrl_sale}
                    <span class="old_price">{$product.price}</span> <span class="sale_price">{$product.sale_price}</span>
                {else}
                    {$product.price}
                {/if}
                </p>
            </div>
      </li>
      {/foreach}
   </ul>
</div>
{/if}