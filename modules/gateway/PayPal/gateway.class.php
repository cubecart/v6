<?php
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
			'action'	=> ($this->_module['testMode']) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr',
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> 'auto',
		);
		return $transfer;
	}

	public function repeatVariables() {
		/*
		$i = 1;
		foreach ($this->_basket['contents'] as $key => $product) {
			$hidden['quantity_'.$i]		= $product['quantity'];
			$hidden['item_name_'.$i]	= substr($product['name'], 0, 127);
			$hidden['amount_'.$i]		= $product['price'];
		#	$hidden['tax_'.$i]			= '';
			$i++;
		}
		if ($this->_basket['discount'] > 0) {
			$hidden['quantity_'.$i]		= 1;
			$hidden['item_name_'.$i]	= 'Discount';
			$hidden['amount_'.$i]		= '-'.$this->_basket['discount'];
		}
		return $hidden;
		*/
		return false;
	}

	public function fixedVariables() {
		
		if($GLOBALS['db']->count('CubeCart_modules', 'module_id', array('module' => 'gateway', 'status' => '1')) == 1) {
			$cancel_return = 'confirm';
		} else {
			$cancel_return = 'gateway';
		}
		
		$hidden	= array(
			'cmd'			=> '_xclick',
			'charset'		=> 'utf-8',
			'amount'		=> $this->_basket['total'],
			'upload'		=> true,
			'no_note'		=> true,
			'bn'			=> $GLOBALS['config']->get('config', 'default_currency') ? 'CubeCart_Cart_ST_CA' : 'CubeCart_Cart_ST',	## PayPal's integration usage statistics. We get commission from this, so please be kind and leave it in.

			'business'		=> $this->_module['email'],
			'invoice'		=> $this->_basket['cart_order_id'],
			'currency_code'	=> $GLOBALS['config']->get('config', 'default_currency'),
			'item_name'		=> 'Order '.$this->_basket['cart_order_id'],
			'item_number' 	=> $this->_basket['cart_order_id'],

			'address_override'	=> ($GLOBALS['config']->get('PayPal', 'address_override'))  ? '1' : '0',
			'first_name'	=> $this->_basket['billing_address']['first_name'],
			'last_name'		=> $this->_basket['billing_address']['last_name'],
			'address1'		=> $this->_basket['billing_address']['line1'],
			'address2'		=> $this->_basket['billing_address']['line2'],
			'city'			=> $this->_basket['billing_address']['town'],
			'state'			=> ($this->_basket['billing_address']['country_iso']=='US' || $this->_basket['billing_address']['country_iso']=='CA') ? $this->_basket['billing_address']['state_abbrev'] : $this->_basket['billing_address']['state'],
			'zip'			=> $this->_basket['billing_address']['postcode'],
			'country'		=> $this->_basket['billing_address']['country_iso'],

			## IPN and Return URLs
			'notify_url'	=> $GLOBALS['storeURL'].'/index.php?_g=rm&amp;type=gateway&amp;cmd=call&amp;module=PayPal',
			'return'		=> $GLOBALS['storeURL'].'/index.php?_a=complete',
			'cancel_return'	=> $GLOBALS['storeURL'].'/index.php?_a='.$cancel_return,
			'rm'			=> 2 // 2 = return POST vars
		);
		return $hidden;
	}

	##################################################

	public function call() {
		 /* Sample return Data
		   [mc_gross] => 3.00
		   [invoice] => 110204-140118-8837
		   [protection_eligibility] => Eligible
		   [address_status] => confirmed
		   [item_number1] => 
		   [payer_id] => LAJ4A4YCNQCEL
		   [tax] => 0.00
		   [address_street] => 1 Main St
		   [payment_date] => 06:01:59 Feb 04, 2011 PST
		   [payment_status] => Completed
		   [charset] => windows-1252
		   [address_zip] => 95131
		   [mc_shipping] => 0.00
		   [mc_handling] => 0.00
		   [first_name] => Test
		   [mc_fee] => 0.39
		   [address_country_code] => US
		   [address_name] => Test User
		   [notify_version] => 3.0
		   [custom] => 
		   [payer_status] => verified
		   [business] => abrook_1296826860_biz@gmail.com
		   [address_country] => United States
		   [num_cart_items] => 1
		   [mc_handling1] => 0.00
		   [address_city] => San Jose
		   [verify_sign] => An5ns1Kso7MWUdW4ErQKJJJ4qi4-AXkkzDu540jNUQYPfPnj9xlCfWdp
		   [payer_email] => abrook_1219149812_per@gmail.com
		   [mc_shipping1] => 0.00
		   [txn_id] => 1FC10329AU620380S
		   [payment_type] => instant
		   [last_name] => User
		   [address_state] => CA
		   [item_name1] => A Product
		   [receiver_email] => abrook_1296826860_biz@gmail.com
		   [payment_fee] => 0.39
		   [quantity1] => 1
		   [receiver_id] => WT2L5CAWC6JKA
		   [txn_type] => cart
		   [mc_gross_1] => 3.00
		   [mc_currency] => USD
		   [residence_country] => US
		   [test_ipn] => 1
		   [transaction_subject] => Shopping Cart
		   [payment_gross] => 3.00
		*/
	
		$params['cmd']	= '_notify-validate';
		foreach ($_POST as $key => $value) {
			$params[$key]	= stripslashes($value);
		}
		## Set the request URL
		$url			= ($this->_module['testMode']) ? 'www.sandbox.paypal.com' : 'www.paypal.com';
		## Start a request object
		$request		= new Request($url, '/cgi-bin/webscr');
		$request->setSSL();
		$request->setHTTPVersion('1.1');
		$request->setData($params);
		## Send the request
		$data			= $request->send();
		
		## Get the Order ID
		$cart_order_id	= $_POST['invoice'];
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
					switch ($_POST['payment_status']) {
						case 'Completed':
							$transData['notes'][]	= "Payment successful. <br />Address: ".$_POST['address_status']."<br />Payer Status: ".$_POST['payer_status'];
							$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
							$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
							break;
						case 'Canceled_Reversal':
							$transData['notes'][]	= "This means a reversal has been canceled; for example, you, the merchant, won a dispute with the customer and the funds for the transaction that was reversed have been returned to you.";
							$order->paymentStatus(Order::PAYMENT_CANCEL, $cart_order_id);
							$order->orderStatus(Order::ORDER_CANCELLED, $cart_order_id);
							break;
						case 'Denied':
							$transData['notes'][]	= "You, the merchant, denied the payment. This will only happen if the payment was previously pending due to one of the following pending reasons.";
							$order->paymentStatus(Order::PAYMENT_DECLINE, $cart_order_id);
							$order->orderStatus(Order::ORDER_CANCELLED, $cart_order_id);
							break;
						case 'Failed':
							$transData['notes'][]	= "The payment has failed. This will only happen if the payment was attempted from your customer's bank account.";
							$order->paymentStatus(Order::PAYMENT_DECLINE, $cart_order_id);
							$order->orderStatus(Order::ORDER_DECLINED, $cart_order_id);
							break;
						case 'Pending':
							$transData['notes'][]	= "The payment is pending; see the pending_reason variable for more information. Please note, you will receive another Instant Payment Notification when the status of the payment changes to 'Completed', 'Failed', or 'Denied'.";
							$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
							$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
							break;
						case 'Refunded':
							$transData['notes'][]	= "You, the merchant, refunded the payment.";
							
							if(isset($_POST['payment_gross']) && !empty($_POST['payment_gross'])) { // Support for legacy PayPal code for USD
								$_POST['mc_gross'] = $_POST['payment_gross'];
							}
							
							if((string)$_POST['mc_gross']==(string)'-'.$order_summary['total']) { // Change status to refunded if it is a full refund
								$order->paymentStatus(Order::PAYMENT_CANCEL, $cart_order_id);
								$order->orderStatus(Order::ORDER_CANCELLED, $cart_order_id);
							}
							break;
						case 'Reversed':
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

			## Has the PayPal transaction id already been processed?
			if (isset($_POST['txn_id']) && !empty($_POST['txn_id'])) {
				$trans_id	= $GLOBALS['db']->select('CubeCart_transactions', array('id'), array('trans_id' => $_POST['txn_id']));
				if ($trans_id) {
					$transData['notes'][]	= 'This Transaction ID has been processed before.';
				}
			}
			## Does the reciever email match the email in the module configuration?
			if (isset($_POST['receiver_email']) && trim($_POST['receiver_email']) !== trim($this->_module['email'])) {
				$transData['notes'][]	= "Recipient account didn't match specified PayPal account.";
			}
			## Build the transaction log data
			$transData['gateway']		= $_GET['module'];
			$transData['order_id']		= $cart_order_id;
			$transData['trans_id']		= $_POST['txn_id'];
			$transData['amount']		= $_POST['mc_gross'];
			$transData['status']		= $_POST['payment_status'];
			$transData['customer_id']	= $order_summary['customer_id'];
			$transData['extra']			= $_POST['pending_reason'];
			$order->logTransaction($transData);
		}
		return false;
	}

	public function process() {
		## We're being returned from PayPal - This function can do some pre-processing, but must assume NO variables are being passed around
		## The basket will be emptied when we get to _a=complete, and the status isn't Failed/Declined

		## Redirect to _a=complete, and drop out unneeded variables
		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
	}

	public function form() {
		return false;
	}
}