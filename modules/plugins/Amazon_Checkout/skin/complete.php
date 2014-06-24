{if empty($AMAZON.order_id)}
  <h2>Oops, something is wrong!</h2>
  	<p>It looks like there has been a problem with your order. Please contact a member of staff for assistance.
	<p>Return to <a href="{$VAL_SELF}&amazon_action=cancel" style="size: 10px">Standard Checkout</a></p>
{else}
  <h2>Order Confirmation</h2>
    <h3>Thank you!</h3>
    <p>Your Amazon order number is <a href="#" onclick="orderDetailsWidget.open('230px', '554px');">{$AMAZON.order_id}</a>. You will receive an email confirmation shortly with your order details.</p>
    <p>Return to <a href="{$AMAZON.order_url}" style="size: 10px">Review or edit this order on Amazon Payments</a></p>
    
    <script type='text/javascript' src='{$AMAZON.widgetURL}'></script>
	<script>
	    var orderDetailsWidget = new CBA.Widgets.OrderDetailsWidget({
	        merchantId: '{$AMAZON.merchId}',
	        orderID: '{$AMAZON.order_id}',
	        design:{literal}{size: { width:'392', height:'306' } }{/literal}
	    });
	</script>
{/if}
