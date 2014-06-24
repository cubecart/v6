<?php
/**
* OKPay payment module for CubeCart [5.x.x]
*
* This module allows merchants to get payments via {@link https://www.okpay.com OKPAY}
* processing
*
* @author		Mike Iceman 
* @copyright	OKPAY Inc. 2012
* @version		1.1
* @package		CubeCart V
* @subpackage	Payment gateways
*/
class Gateway {
	private $_config;
	private $_module;
	private $_basket;

	public function __construct($module = false, $basket = false) {
		$this->_config	=& $GLOBALS['config'];
		$this->_session	=& $GLOBALS['user'];

		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
	}

	##################################################

	public function transfer() {
		$transfer	= array(
			'action'	=> 'https://www.okpay.com/process.html',
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> 'auto',
		);
		return $transfer;
	}

	public function repeatVariables() {
		/*
		## For future - if we wish to get detailed checkout
		## No taxes, shipping and handling now
		$i = 1;
		$hidden['ok_items_count'] = count($this->_basket['contents']);
		foreach ($this->_basket['contents'] as $key => $product) {
			$hidden['ok_item_'.$i.'_quantity']	= $product['quantity'];
			$hidden['ok_item_'.$i.'_name']		= substr($product['name'], 0, 127);
			$hidden['ok_item_'.$i.'_price']		= $product['price'];
			$i++;
		}
		return $hidden;
		*/
		return false;
	}

	public function fixedVariables() {
		$hidden	= array(
			# Payment config
			'ok_receiver'			=> $this->_module['email'],
			'ok_invoice'			=> $this->_basket['cart_order_id'],
			'ok_currency'			=> $GLOBALS['config']->get('config', 'default_currency'),
			'ok_item_1_name'		=> 'Order '.$this->_basket['cart_order_id'],
			'ok_item_1_price'		=> $this->_basket['total'],
			# Payer data
			'ok_payer_first_name'	=> $this->_basket['billing_address']['first_name'],
			'ok_payer_last_name'	=> $this->_basket['billing_address']['last_name'],
			'ok_payer_street'		=> $this->_basket['billing_address']['line1'].(!empty($this->_basket['billing_address']['line2']) ? " ".$this->_basket['billing_address']['line2'] : ""),
			'ok_payer_city'			=> $this->_basket['billing_address']['town'],
			'ok_payer_state'		=> ($this->_basket['billing_address']['country_iso']=='US' || $this->_basket['billing_address']['country_iso']=='CA') ? $this->_basket['billing_address']['state_abbrev'] : $this->_basket['billing_address']['state'],
			'ok_payer_zip'			=> $this->_basket['billing_address']['postcode'],
			'ok_payer_country'		=> $this->_basket['billing_address']['country_iso'],
			## IPN and Return URLs
			'ok_ipn'	=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=call&module=OKPay',
			'ok_return_success'		=> $GLOBALS['storeURL'].'/index.php?_a=complete',
			'ok_return_fail'	=> $GLOBALS['storeURL'].'/index.php?_a=gateway'
		);
		return $hidden;
	}

	##################################################

	public function call() {
		/*
		OKPAY Data sample
			ok_charset=utf-8
			ok_receiver=OKxxxxxxxxx
			ok_receiver_id=808707774
			ok_receiver_wallet=OKxxxxxxxxx
			ok_receiver_email=itxxxxxxxxxxxxxxor@gmail.com
			ok_txn_id=1681645
			ok_txn_kind=payment_link
			ok_txn_payment_type=instant
			ok_txn_gross=20.00
			ok_txn_amount=0.00
			ok_txn_net=0.00
			ok_txn_fee=0.01
			ok_txn_currency=USD
			ok_txn_datetime=2012-10-23 13:49:44
			ok_txn_status=completed
			ok_invoice=1864
			ok_payer_status=unverified
			ok_payer_id=455172809
			ok_payer_reputation=0
			ok_payer_first_name=Mxxxxxx
			ok_payer_last_name=Yxxxxxxxx
			ok_payer_email=xxxxxx@mail.com
			ok_items_count=1
			ok_item_1_name=Order #281 - VIP Status (3 months)
			ok_item_1_type=digital
			ok_item_1_quantity=1
			ok_item_1_gross=20.00
			ok_item_1_price=20.00
		*/
		$params['ok_verify']	= 'true';
		foreach ($_POST as $key => $value) {
			$params[$key]	= stripslashes($value);
		}
		## Start a request object
		$request = new Request('www.okpay.com', '/ipn-verify.html');
		## Use SSL in IPN?
		if($this->_module['use_ssl']=="Y") {
			$request->setSSL();
		}
		$request->setData($params);
		## Send the request
		$data = $request->send();
		
		## Get the Order ID
		$cart_order_id	= $_POST['ok_invoice'];
		if (!empty($cart_order_id) && !empty($data)) {
			$order				= Order::getInstance();
			$order_summary		= $order->getSummary($cart_order_id);
			$transData['notes']	= array();
			switch ($data) {
				case 'INVALID':
					## If this is the response, then something is wrong with the callback
					$transData['notes'][]	= "Unspecified Error.";
					break;
				case 'VERIFIED':
					switch ($_POST['ok_txn_status']) {
						case 'completed':
							$transData['notes'][]	= "Payment successful. <br />Address: ".$_POST['address_status']."<br />Payer Status: ".$_POST['payer_status'];
							$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
							$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
							break;
						case 'failed':
							$transData['notes'][]	= "The payment has failed. This will only happen if the payment was attempted from your customer's bank account.";
							$order->paymentStatus(Order::PAYMENT_DECLINE, $cart_order_id);
							$order->orderStatus(Order::ORDER_DECLINED, $cart_order_id);
							break;
						case 'pending':
							$transData['notes'][]	= "The payment is pending; see the pending_reason variable for more information. Please note, you will receive another Instant Payment Notification when the status of the payment changes to 'Completed', 'Failed', or 'Denied'.";
							$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
							$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
							break;
						case 'reversed':
							$transData['notes'][]	= "This means that a payment was reversed due to a chargeback or other type of reversal. The funds have been debited from your account balance and returned to the customer. The reason for the reversal is given by the reason_code variable.";
							$order->paymentStatus(Order::PAYMENT_CANCEL, $cart_order_id);
							$order->orderStatus(Order::ORDER_CANCELLED, $cart_order_id);
							break;
						default:
							$transData['notes'][]	= "Unspecified Error.";
							$order->paymentStatus(Order::PAYMENT_DECLINE, $cart_order_id);
							$order->orderStatus(Order::ORDER_DECLINED, $cart_order_id);
							break;
					}
					break;
			}

			## Has the OKPay transaction id already been processed?
			if (isset($_POST['ok_txn_id']) && !empty($_POST['ok_txn_id'])) {
				$trans_id	= $GLOBALS['db']->select('CubeCart_transactions', array('id'), array('trans_id' => $_POST['ok_txn_id']));
				if ($trans_id) {
					$transData['notes'][]	= 'This Transaction ID has been processed before.';
				}
			}
			## Does the reciever email match the email in the module configuration?
			if (isset($_POST['ok_receiver']) && trim($_POST['ok_receiver']) !== trim($this->_module['email'])) {
				$transData['notes'][]	= "Recipient account didn't match specified OKPay account.";
			}
			## Build the transaction log data
			$extraField = array();
			if($_POST['ok_txn_pending_reason'])  $extraField[] = $_POST['ok_txn_pending_reason'];
			if($_POST['ok_txn_reversal_reason']) $extraField[] = $_POST['ok_txn_reversal_reason'];
			##
			$transData['gateway']		= $_GET['module'];
			$transData['order_id']		= $cart_order_id;
			$transData['trans_id']		= $_POST['ok_txn_id'];
			$transData['amount']		= $_POST['ok_txn_gross'];
			$transData['status']		= $_POST['ok_txn_status'];
			$transData['customer_id']	= $order_summary['customer_id'];
			$transData['extra']			= implode("; ", $extraField);
			$order->logTransaction($transData);
		}
		return false;
	}

	public function process() {
		## We're being returned from OKPay
		## This function can do some pre-processing, but must assume NO variables are being passed around
		## The basket will be emptied when we get to _a=complete, and the status isn't Failed/Declined

		## Redirect to _a=complete, and drop out unneeded variables
		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
		
		## Well done.
	}

	public function form() {
		return false;
	}
}