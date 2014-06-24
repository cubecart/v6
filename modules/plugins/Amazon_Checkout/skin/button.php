<noscript>
<div style="color: red; font-weight: bold; width: 75%;">Your browser does not support JavaScript which is required to pay with Amazon. Please enable it or use a different browser.</div>
</noscript>
<script type='text/javascript' src='{$AMAZON.widgetURL}'></script>
<div id="AmazonInlineWidget" align="right"><img src="https://{$AMAZON.redirectDomain}/gp/cba/button?cartOwnerId={$AMAZON.merchId}&size={$AMAZON.buttonSize}&color={$AMAZON.buttonColor}&background={$AMAZON.buttonBg}&type=inlineCheckout" style="cursor: pointer;"/></div>

<script type='text/javascript' >
	new CBA.Widgets.InlineCheckoutWidget({
	    merchantId: '{$AMAZON.merchId}',
	    onAuthorize: function(widget) {
                window.location = '{$AMAZON.returnURL}&purchaseContractId=' + widget.getPurchaseContractId() ;	
            }
        }).render("AmazonInlineWidget");
</script>