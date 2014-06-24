<h2>Delivery Address</h2>
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data" id="basket">
	<div align="center" style="margin: 10px">
	<script type='text/javascript' src='{$AMAZON.widgetURL}'></script>
	<div id="AmazonAddressWidget"></div>
	<script type='text/javascript' >
	    new CBA.Widgets.AddressWidget({
	        merchantId: '{$AMAZON.merchId}',
	        displayMode: 'Edit',
	        design:{literal}{size: { width:'{/literal}{$AMAZON.width}{literal}', height:'{/literal}{$AMAZON.height}{literal}' } }{/literal},
	        onAddressSelect: function(widget) {   
	            document.getElementsByName("proceed")[0].setAttribute("style","display: inline; visibility:visible;");
	            document.getElementsByName("proceed")[0].removeAttribute("disabled");
	        }
	    }).render("AmazonAddressWidget") ;
	</script>
	Return to <a href="{$VAL_SELF}&amazon_action=cancel" style="size: 10px">Standard Checkout</a>
	</div>
	<div class="basket_actions clearfix">
	  <a href="index.php?_a=basket&empty-basket=true" class="button_submit button alert left">{$LANG.basket.basket_empty}</a>
	  <input type="submit" name="proceed" class="button_submit button right" disabled="disabled" style="display: none; visibility:hidden;" value="{$LANG.common.continue}" />
	</div>
</form>
