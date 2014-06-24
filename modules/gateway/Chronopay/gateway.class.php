<?php
class Gateway {
	private $_config;
	private $_module;
	private $_basket;

	public function __construct($module = false, $basket = false) {
		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
	}

	##################################################

	public function transfer() {
		$transfer	= array(
			'action'	=> 'https://payments.chronopay.com',
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> 'auto',
		);
		return $transfer;
	}

	public function repeatVariables() {
		return false;
	}

	public function fixedVariables() {
		// Get language for payment page
		$sessionLang = substr($this->_basket['language'],0,2);
		switch($sessionLang) {
			case 'nl':
				$chronoLang = 'NL';
			break;
			case 'ru':
				$chronoLang = 'RU';
			break;
			case 'es':
				$chronoLang = 'ES';
			break;
			default:
				$chronoLang = 'EN';
		}

		$sign	= $this->generateSign($this->_basket['total']);
		
		$hidden	= array(
			/* Required Fields */
			'product_id' 				=> $this->_module['product_id'],
			'product_price' 			=> $this->_basket['total'],
			'sign' 						=> $sign,
			
			'product_price_currency'	=> $GLOBALS['config']->get('config', 'default_currency'),
			'language' 					=> $chronoLang,
			
			/* Custom fields */
			'cs1' 						=> $this->_basket['cart_order_id'],
			'cs2' 						=> '',
			'cs3' 						=> '',
			
			'cb_url' 					=> $GLOBALS['storeURL'].'/modules/gateway/Chronopay/call.php',
			'cb_type' 					=> 'P',
			'decline_url' 				=> $GLOBALS['storeURL'].'/index.php?_a=gateway',
			'success_url'				=> $GLOBALS['storeURL'].'/index.php?_a=vieworder&cart_order_id='.$this->_basket['cart_order_id'],
			
			'f_name' 					=> $this->_basket['billing_address']['first_name'],
			's_name' 					=> $this->_basket['billing_address']['last_name'],
			'street' 					=> $this->_basket['billing_address']['line1']." ".$this->_basket['billing_address']['line2'],
			'city' 						=> $this->_basket['billing_address']['town'],
			'state' 					=> $this->_basket['billing_address']['state'],
			'zip' 						=> $this->_basket['billing_address']['postcode'],
			'country' 					=> $this->_basket['billing_address']['country_iso3'],
			'email' 					=> $this->_basket['billing_address']['email'],
			'phone' 					=> $this->_basket['billing_address']['phone'],
			
		);
		return $hidden;
	}
	
	private function generateSign($total) {
		return md5($this->_module['product_id'].'-'.$total.'-'.$this->_module['shared_sec']);
	}

	##################################################

	public function call() {
		/* Example return data
		$_POST = array(
			'transaction_type' => 'onetime',
			'customer_id' => '004943-000000008',
			'site_id' => '004943-0001',
			'product_id' => '004943-0001-0001',
			'date' => '12/18/2008 06:15:09',
			'time' => '06:15:09',
			'transaction_id' => '14585314',
			'email' => 'user@chronopay.com',
			'country' => 'RUS',
			'name' => 'Joe Blow',
			'city' => 'Moscow',
			'street' => 'Some Street',
			'phone' => '00666888666444',
			'state' => 'XX',
			'zip' => '000000',
			'language' => 'EN',
			'username' => '',
			'password' => '',
			'total' => '1.00',
			'total' => '1.00',
			'currency' => 'USD',
			'payment_type' => 'VISA / Visa Electron',
			'sign' => '0e199aeb075bb0bcd9309d36d482b6d8',
			'cs1' => '090710-134521-5661',
			'cs2' => '',
			'cs3' => '',
		);
		*/

		$cart_order_id	= $_POST['cs1'];

		$transData['customer_id'] 	= $_POST['customer_id'];
		$transData['gateway'] 		= "Chronopay";
		$transData['trans_id'] 		= $_POST['transaction_id'];
		$transData['order_id'] 		= $cart_order_id;
		$transData['amount'] 		= $_POST['total'];

		$order				= Order::getInstance();
		$order_summary		= $order->getSummary($cart_order_id);

		if ($sign == $this->generateSign($_POST['total']) && !empty($_POST['transaction_type']) && !empty($cart_order_id)) {
			// check the status of the payment
			if ($_POST['transaction_type'] == 'onetime' || $_POST['transaction_type'] == 'initial' || $_POST['transaction_type'] == 'rebill') {
				// successful payment
				$transData['status'] = 'Successful';
				$transData['notes'] = 'Payment has been taken successfully.';
				$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
				$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
			} else {
				// failed payment
				$transData['status'] = "Failed";
				$transData['notes'] = "Payment failed.";
				$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
				$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
			}
		} else {
			// invalid request from Chronopay server.
			// if the transaction is fraudolent or not correct do something to notify yourself of the problem
			// failed payment
			$transData['status'] = 'Error';
			$transData['notes'] = 'Invalid request from Chronopay server.';
		}

		$order->logTransaction($transData);

	}

	public function process() {
		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
		return false;
	}

	public function form() {
		return false;
	}
}