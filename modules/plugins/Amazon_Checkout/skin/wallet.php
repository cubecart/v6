<h2>Payment Method</h2>
<div align="center" style="margin: 10px">
<script type='text/javascript' src='{$AMAZON.widgetURL}'></script>
<div id="AmazonWalletWidget"></div>
<script type='text/javascript'>
	window.onload=function() {
		// First hide the proceed button
		document.getElementsByName("proceed")[0].setAttribute("style","display: none; visibility:hidden;");
		document.getElementsByName("proceed")[0].setAttribute("disabled","disabled");
		
		new CBA.Widgets.WalletWidget({
		    merchantId: '{$AMAZON.merchId}',
		    displayMode: 'Edit',
		    design:{literal}{size: { width:'{/literal}{$AMAZON.width}{literal}', height:'{/literal}{$AMAZON.height}{literal}' } }{/literal},
	            onPaymentSelect: function(widget) {
	                // Show the proceed button once a payment method has been chosen
	                document.getElementsByName("proceed")[0].setAttribute("value","Place Your Order");
	                document.getElementsByName("proceed")[0].setAttribute("style","display: inline; visibility:visible;");
		            document.getElementsByName("proceed")[0].removeAttribute("disabled");
	            }
		}).render("AmazonWalletWidget");
	};
	
</script>
Delivering to {$AMAZON.delivery_address} (<a href="{$VAL_SELF}&amazon_action=address" style="size: 10px">Change</a>)
</div>