<?php
$stage = $GLOBALS['session']->get('stage', 'PayPal_Pro');
switch ($stage) {
	case "GetExpressCheckoutDetails":
		$status = 1;
	break;
	case "DoExpressCheckoutPayment":
		$status = 2;
	break;
}