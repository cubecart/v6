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
			'action'	=> ($this->_module['checkoutMode']=='single') ? 'https://www.2checkout.com/checkout/spurchase' : 'https://www2.2checkout.com/2co/buyer/purchase',
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> 'auto',
		);
		return $transfer;
	}

	public function repeatVariables() {
		$i = 1;
		/* I don't think this is required as it doesn't have to add up if I am wrong blame
		$discount	= 0;
		if ($this->_basket['discount'] > 0) {
			$count		= count($this->_basket['contents']);
			$discount	= ($this->_basket['discount']/$count);
		}
		*/
		foreach ($this->_basket['contents'] as $product) {
			$hidden['c_prod_'.$i] 			= $product['id'].','.(int)$product['quantity'];
			$hidden['c_name_'.$i] 			= substr($product['name'], 0, 128);
			$hidden['c_description_'.$i] 	= $product['description'];
			$hidden['c_price_'.$i] 			= $product['total_price_each'];
			$hidden['c_tangible_'.$i] 	= ($product['digital']) ? 'N' : 'Y';
			++$i;
		}
		return (isset($hidden)) ? $hidden : false;
	}

	public function fixedVariables() {
		$hidden	= array(
			'id_type'				=> 1,
			'fixed'					=> 'Y',
			'sid' 					=> $this->_module['acNo'],
			'total' 				=> $this->_basket['total'],
			'cart_order_id' 		=> $this->_basket['cart_order_id'],
			'lang' 					=> (strtoupper(substr($this->_config['default_language'],0,2))=='ES') ? 'sp' : 'en',
			'merchant_order_id' 	=> $this->_basket['cart_order_id'],
			'pay_method' 			=> '',
			'skip_landing' 			=> 1,
			
			'x_receipt_link_url'	=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=2Checkout',
			
			'card_holder_name' 		=> $this->_basket['billing_address']['first_name'].' '.$this->_basket['billing_address']['last_name'],
			
			'phone' 				=> $this->_basket['billing_address']['phone'],
			'email' 				=> $this->_basket['billing_address']['email'],
			
			'street_address' 		=> $this->_basket['billing_address']['line1'],
			'street_address2' 		=> $this->_basket['billing_address']['line2'],
			'city' 					=> $this->_basket['billing_address']['town'],
			'state' 				=> ($this->_basket['billing_address']['country_iso']=="US" || $this->_basket['billing_address']['country_iso']=="CA") ? $this->_basket['billing_address']['state'] : "XX",
			'zip' 					=> $this->_basket['billing_address']['postcode'],
			'country' 				=> $this->_basket['billing_address']['country'],
			
			'ship_name' 			=> $this->_basket['delivery_address']['first_name'].' '.$this->_basket['delivery_address']['last_name'],
			'ship_steet_address' 	=> $this->_basket['delivery_address']['line1'],
			'ship_steet_address2'	=> $this->_basket['billing_address']['line2'],
			'ship_city' 			=> $this->_basket['delivery_address']['town'],
			'ship_country' 			=> $this->_basket['delivery_address']['country'],
			'ship_state' 			=> $this->_basket['delivery_address']['state'],
			'ship_zip' 				=> $this->_basket['delivery_address']['postcode'],
		);
		
		if($this->_module['testMode']=="Y") {
			$hidden['demo'] = 'Y';	
		}

		return (isset($hidden)) ? $hidden : false;
	}

	##################################################

	public function call() {
		return false;
	}

	public function process() {
		$order				= Order::getInstance();
		$cart_order_id		= $_REQUEST['cart_id'];
		$order_summary		= $order->getSummary($cart_order_id);
		
		if($_REQUEST['credit_card_processed'] == 'Y'){
			$notes 	= 'Card was successfully processed.';
			$status = 'Processed';
			$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
		} else {
			$notes = 'Card has not yet been processed and is currently pending.';
			$status = 'Pending';
			$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
		}

		$transData['notes']			= $notes;
		$transData['gateway']		= $_REQUEST['module'];
		$transData['order_id']		= $cart_order_id;
		$transData['trans_id']		= $_REQUEST['order_number'];
		$transData['amount']		= isset($_REQUEST['total']) ? $_REQUEST['total'] : '';
		$transData['status']		= $status;
		$transData['customer_id']	= $order_summary['customer_id'];
		$transData['extra']			= '';
		$order->logTransaction($transData);


		// The basket will be emptied when we get to _a=complete, and the status isn't Failed/Declined

		// Redirect to _a=complete, and drop out unneeded variables
		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
	}

	public function form() {
		return false;
	}
}