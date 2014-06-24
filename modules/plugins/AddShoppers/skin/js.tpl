{if $show_addshoppers_default_buttons}
<!-- AddShoppers Default Sharing Buttons -->
<div class="share-buttons share-buttons-tab" data-buttons="twitter,facebook,email,pinterest" data-style="medium" data-counter="true" data-hover="true" data-promo-callout="true" data-float="left"></div>
<!-- End AddShoppers Default Sharing Buttons -->
{/if}

{literal}
<!-- AddShoppers Social Analytics -->
<script type="text/javascript">
{/literal}
{if $SUM.cart_order_id}
  {literal}
    AddShoppersConversion = {
        order_id: '{/literal}{$SUM.cart_order_id}{literal}',
        value: '{/literal}{$SUM.total}{literal}'
    };
  {/literal}
  
  {if $show_addshoppers_purchase_sharing} 
       {literal}AddShoppersTracking = {
       	   auto: true,
       	   header: "{/literal}{$addshoppers_purchase_sharing_header}{literal}",
           image: "{/literal}{$addshoppers_purchase_sharing_image}{literal}",
       	   url: "{/literal}{$addshoppers_purchase_sharing_link}{literal}",
           name : "{/literal}{$addshoppers_purchase_sharing_title}{literal}",
           description: "{/literal}{$addshoppers_purchase_sharing_description}{literal}"
       };{/literal}
  {/if}   
{/if}
{if $PRODUCT}
  {literal}AddShoppersTracking = {};{/literal}
  {if $PRODUCT.name}{literal}AddShoppersTracking.name = '{/literal}{$PRODUCT.name}{literal}';{/literal}{/if}
  {if $PRODUCT.image!=''}{literal}AddShoppersTracking.image = '{/literal}{$PRODUCT.enlarge}{literal}';{/literal}{/if}
  {if $PRODUCT.product_code}{literal}AddShoppersTracking.productid = '{/literal}{$PRODUCT.product_code}{literal}';{/literal}{/if}
  {if $CTRL_OUT_OF_STOCK}{literal}AddShoppersTracking.stock = 'Out of Stock';{/literal}
  	{else}{literal}AddShoppersTracking.stock = 'In Stock';{/literal}
  {/if}
  {if $PRODUCT.ctrl_sale}{literal}AddShoppersTracking.price = '{/literal}{$PRODUCT.sale_price}{literal}';{/literal}
    {else}{literal}AddShoppersTracking.price = '{/literal}{$PRODUCT.price}{literal}';{/literal}
  {/if}
{/if}

{literal}
var js = document.createElement('script'); js.type = 'text/javascript'; js.async = true; js.id = 'AddShoppers';
js.src = ('https:' == document.location.protocol ? 'https://shop.pe/widget/' : 'http://cdn.shop.pe/widget/') + 'widget_async.js#{/literal}{$addshoppers_shop_id}{literal}';
document.getElementsByTagName("head")[0].appendChild(js);
</script>
{/literal}