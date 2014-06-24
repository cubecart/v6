<?php
class Gateway {
	private $_config;
	private $_module;
	private $_basket;
	
	private $_hide_login;
	private $_payment_methods;
	private $_url;
	private $_gateway_name;

	public function __construct($module = false, $basket = false) {
		$this->_session	=& $GLOBALS['user'];

		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
		
		switch($this->_module['variant']) {
			case 'OBT':
				$this->_hide_login 			= 1;
				$this->_payment_methods		= 'OBT';
				$this->_gateway_name		= 'Moneybookers (OBT)';
			break;
			case 'moneybookers_ewallet':
				$this->_hide_login 			= 0;
				$this->_payment_methods		= 'WLT';
				$this->_gateway_name		= 'Moneybookers (eWallet)';
			break;
			default:
				$this->_hide_login 			= 1;
				$this->_payment_methods		= 'ACC';
				$this->_gateway_name		= 'Moneybookers (Credit/Debit Card)';
			break;
		}
		$this->_url = 'https://www.moneybookers.com/app/payment.pl';
	}

	##################################################

	public function transfer() {
		$transfer	= array(
			'action'	=> $this->_url,
			'method'	=> 'get',
			'target'	=> '_self',
			'submit'	=> ($this->_module['iframe']) ? 'iframe' : 'auto',
		);
		return $transfer;
	}

	public function repeatVariables() {
		## Can money bookers support a basket system?
		## I don't know can they?
		## Maybe there is a user guide?!
		## Wot like this 8 million page document infront of me?
		## Why am I talking to myself?!
		## Call a doctor... or maybe just have a break from coding :S
	}

	public function fixedVariables() {
		## Send required variables
		$hidden = array(
			'pay_to_email' 			=> $this->_module['email'],
			'transaction_id' 		=> $this->_basket['cart_order_id'],
			'return_url' 			=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=moneybookers&cart_order_id='.$this->_basket['cart_order_id'],
			'cancel_url' 			=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway',
			'status_url' 			=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=call&module=moneybookers&cart_order_id='.$this->_basket['cart_order_id'],
			'language' 				=> 'EN',
			'pay_from_email' 		=> $this->_basket['billing_address']['email'],
			'amount' 				=> $this->_basket['total'],
			'currency' 				=> $GLOBALS['config']->get('config', 'default_currency'),
			'firstname' 			=> $this->_basket['billing_address']['first_name'],
			'lastname' 				=> $this->_basket['billing_address']['last_name'],
			'address' 				=> $this->_basket['billing_address']['line1']." ".$this->_basket['billing_address']['line2'],
			'postal_code' 			=> $this->_basket['billing_address']['postcode'],
			'city' 					=> $this->_basket['billing_address']['town'],
			'country' 				=> $this->_basket['billing_address']['country_iso'],
			
			'return_url_target'		=> 3, ## 3 = _self
			'cancel_url_target'		=> 3,
			
			'hide_login'			=> $this->_hide_login,
			'payment_methods'		=> $this->_payment_methods,
			
			'recipient_description' => $GLOBALS['config']->get('config', 'store_title'),
			'merchant_fields' 		=> 'referring_platform',
			'referring_platform' 	=> 'cubecart',
			'status_url2' 			=> $GLOBALS['config']->get('config', 'email_address'),
			'logo_url' 				=> $this->_module['logoURL']
		);
		
		return $hidden;
	}
	
	public function iframeURL() {
		$repeat_vars 	= $this->repeatVariables();
		if(is_array($repeat_vars)) {
			$request_vars = array_merge($this->fixedVariables(),$repeat_vars);
		} else {
			$request_vars = $this->fixedVariables();
		}
		return ($request_vars) ? $this->_url.'?'.http_build_query($request_vars, '', '&') : false;	
	}

	##################################################

	public function call() {
		## Respond to the status_url request
		if (isset($_POST['transaction_id']) && !empty($_POST['transaction_id']) && isset($_POST['status'])) {
			/*
			if (isset($this->_module['secret'])) {
				$hash	= md5($_POST['merchant_id'].$_POST['transaction_id'].strtoupper($this->_module['secret']).$_POST['mb_amount'].$_POST['mb_currency'].$_POST['status']);
				$proceed	= ($hash === $_POST['md5sig']) ? true : false;
			} else {
				$proceed	= true;
			}
			*/

			//if ($proceed) {
			
				$cart_order_id	= $_POST['transaction_id'];
				$order				= Order::getInstance();
				$order_summary		= $order->getSummary($cart_order_id);
				
				$transData['customer_id'] 	= $order_summary["customer_id"];
				$transData['gateway'] 		= $this->_gateway_name;
				$transData['trans_id'] 		= $_POST['transaction_id'];
				$transData['amount'] 		= $_POST['mb_amount'];

				switch ((int)$_POST['status']) {
					case '0':	## Pending
						$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
						$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
						$transData['status'] 		= "Pending";
						break;
					case '2':	## Processed
						$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
						$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
						$transData['status'] 		= "Success";
						break;
					case '-2':	## Failed
						$order->orderStatus(Order::ORDER_FAILED, $cart_order_id);
						$order->paymentStatus(Order::PAYMENT_FAILED, $cart_order_id);
						$transData['status'] 		= "Failed";
						break;
					case '-1':	## Cancelled
						$order->orderStatus(Order::ORDER_CANCELLED, $cart_order_id);
						$order->paymentStatus(Order::PAYMENT_CANCEL, $cart_order_id);
						$transData['status'] 		= "Cancelled";
						break;
				}
				$order->logTransaction($transData);
			//}
		}
	}

	public function process() {
		## Handle the return process
		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
	}

}