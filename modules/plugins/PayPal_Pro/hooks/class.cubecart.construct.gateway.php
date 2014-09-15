<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */
if(!defined('CC_INI_SET')) die('Access Denied');
if (isset($_GET['module']) && $_GET['module'] == 'PayPal_Pro' || !$GLOBALS['session']->isEmpty('PayerID','PayPal_Pro')) {

	include_once (CC_ROOT_DIR.'/modules/plugins/PayPal_Pro/website_payments_pro.class.php');

	$wpp	= new Website_Payments_Pro($GLOBALS['config']->get('PayPal_Pro'));

	if ($GLOBALS['session']->get('stage', 'PayPal_Pro')=='SetExpressCheckout') {
		$bml = ($_GET['bml']==1) ? true : false;
		//$inline = ($_GET['inline']==1) ? true : false;
		$wpp->SetExpressCheckout($bml,$inline);
		exit;
	} else if ($GLOBALS['session']->get('stage', 'PayPal_Pro')=='DoExpressCheckoutPayment') {
		if ($response = $wpp->DoExpressCheckoutPayment()) {
			/*
			const ORDER_PENDING		= 1;
			const ORDER_PROCESS		= 2;
			const ORDER_COMPLETE	= 3;
			const ORDER_DECLINED	= 4;
			const ORDER_FAILED		= 5;	# Fraudulent
			const ORDER_CANCELLED	= 6;
			*/
			
			if($response['L_ERRORCODE0']=='10486') {
				$wpp->RecoverExpressCheckout();
			}
			
			switch ($response['PAYMENTINFO_0_PAYMENTSTATUS']) {
				case 'Canceled-Reversal':	## A reversal has been cancelled
					break;
				case 'Denied':				## The merchant has denied the payment
				case 'Failed':				## The payment has failed
					$pp_order_status	= 4;
					break;
				case 'Expired':				## The Authorization period for this payment has expired
					## DoReauthorization
					break;
				case 'In-Progress':			## The transaction has not terminated, e.g. authorization may be awaiting completion
					break;
				case 'Partially-Refunded':	## The payment has been partially refunded
					break;
				case 'Pending':				## The payment is pending
					switch ($response['PAYMENTINFO_0_PENDINGREASON']) {
						case 'address':			## The customer did not include a confirmed shipping address
							## Give options to Authorize or Deny the payment
							break;
						case 'authorization':	## The payment has been authorized, but not settled
							break;
						case 'echeck':			## The payment was by echeck
							break;
						case 'intl':			## Merchant has a non-US account, and does not have a withdrawl mechanism
							break;
						case 'multi-currency':	## You do not have a balance in the currency sent
							break;
						case 'order':			## Part of an order that has been authorized, but not settled
							break;
						case 'paymentreview':	## Under risk review by PayPal
							break;
						case 'unilateral':		## Made by an unregistered or unconfirmed email address
							break;
						case 'verify':			## Merchant is not yet verified
							break;
						case 'other':			## None of the above. Call PayPal customer services.
						case 'none':			## No pending reason
							break;
					}
					$pp_order_status	= 1;
					break;
				case 'Reversed':			## A payment was reversed due to a chargeback, or other type of reversal
					switch ($response['PAYMENTINFO_0_REASONCODE']) {
						case 'none':			## No reason code
							break;
						case 'chargeback':		## Chargeback by customer
							break;
						case 'guarantee':		## Customer triggered moneyback guarantee
							break;
						case 'buyer-complaint':	## Customer complained about transaction
							break;
						case 'refund':			## Merchant has refunded customer
							break;
						case 'other':			## None of the above.
							break;
					}
				case 'Refunded':			## The merchant refunded the payment
					$pp_order_status	= 5;
					break;
				case 'Voided':				## An authorization for this transaction has been voided
					break;
				case 'Completed':			## The payment has been completed
				case 'Processed':			## A payment has been accepted
					$pp_order_status	= 2;
			}
			
			$GLOBALS['session']->delete('', 'PayPal_Pro');
			
			$order	= Order::getInstance();
			$cart_order_id		= $GLOBALS['cart']->basket['cart_order_id'];
			$order_summary		= $order->getSummary($cart_order_id);
	
			$transData['gateway']		= 'PayPal Express Checkout';
			$transData['order_id']		= $cart_order_id;
			$transData['trans_id']		= $response['PAYMENTINFO_0_TRANSACTIONID'];
			$transData['amount']		= $response['PAYMENTINFO_0_AMT'];
			$transData['status']		= $response['PAYMENTINFO_0_PAYMENTSTATUS'];
			$transData['customer_id']	= $order_summary['customer_id'];
			$transData['extra']			= '';
			$transData['notes']			= '';
			$order->logTransaction($transData);
			
			$update_order['gateway'] = $transData['gateway'];
			
			$order->updateSummary($cart_order_id, $update_order);
			if (isset($pp_order_status)) {
				$order->orderStatus($pp_order_status,$GLOBALS['cart']->basket['cart_order_id']);
				## Redirect to receipt page
				switch ($pp_order_status) {
					case 1:
					case 2:
					case 3:
						httpredir('?_a=complete');
						break;
				}
			} else {
				$GLOBALS['gui']->setError("Payment failed. Please try again.");
			}
		}
	} else {
		$GLOBALS['session']->delete('', 'PayPal_Pro');
		httpredir('index.php?_a=gateway&module=PayPal_Pro');
	}
}