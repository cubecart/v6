<?php
class Gateway {
	private $_config;
	private $_module;

	public function __construct($module = false, $basket = false) {
		$this->_config	=& $GLOBALS['config'];
		$this->_session	=& $GLOBALS['user'];

		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
	}

	##################################################

	public function transfer() {
		$transfer	= array(
			'action'	=> 'https://secure.nochex.com',
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
			
		$billing_address = array();
		if (!empty($this->_basket['billing_address']['line1']))	$billing_address[] = $this->_basket['billing_address']['line1'];
		if (!empty($this->_basket['billing_address']['line2']))	$billing_address[] = $this->_basket['billing_address']['line2'];
		if (!empty($this->_basket['billing_address']['town']))	$billing_address[] = $this->_basket['billing_address']['town'];
		if (!empty($this->_basket['billing_address']['state']))	$billing_address[] = $this->_basket['billing_address']['state'];

		$delivery_address = array();
		if (!empty($this->_basket['delivery_address']['line1']))	$delivery_address[] = $this->_basket['delivery_address']['line1'];
		if (!empty($this->_basket['delivery_address']['line2']))	$delivery_address[] = $this->_basket['delivery_address']['line2'];
		if (!empty($this->_basket['delivery_address']['town']))		$delivery_address[] = $this->_basket['delivery_address']['town'];
		if (!empty($this->_basket['delivery_address']['state']))	$delivery_address[] = $this->_basket['delivery_address']['state'];
		
		$hidden = array(
			'merchant_id' 			=> $this->_module['email'],
			'amount' 				=> $this->_basket['total'],
			'description' 			=> 'Payment for order '.$this->_basket['cart_order_id'],
			'order_id' 				=> $this->_basket['cart_order_id'],
			'customer_phone_number' => $this->_basket['billing_address']['phone'],
			'billing_fullname' 		=> $this->_basket['billing_address']['first_name'].' '.$this->_basket['billing_address']['last_name'],
			'billing_address' 		=> implode("\r\n", $billing_address),
			'billing_postcode' 		=> $this->_basket['billing_address']['postcode'],
			'delivery_fullname' 	=> $this->_basket['delivery_address']['first_name'].' '.$this->_basket['delivery_address']['last_name'],
			'delivery_address' 		=> implode("\r\n", $delivery_address),
			'delivery_postcode' 	=> $this->_basket['delivery_address']['postcode'],
			'email_address' 		=> $this->_basket['billing_address']['email'],

			'success_url' 			=> $GLOBALS['storeURL'].'/index.php?_a=vieworder&cart_order_id='.$this->_basket['cart_order_id'].'&_a=complete',	
			'cancel_url' 			=> $GLOBALS['storeURL'].'/index.php?_a=gateway',
			'callback_url' 			=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=call&module=Nochex_APC&cart_order_id='.$this->_basket['cart_order_id'],
			
			
			'test_transaction' 		=> ($this->_module['testMode']) ? 100 : '',
			'test_success_url' 		=> $GLOBALS['storeURL'].'/index.php?_a=vieworder&cart_order_id='.$this->_basket['cart_order_id'].'&_a=complete'
			
		);
		return $hidden;
	}

	##################################################

	public function call() {
	
	
		/*
		[transaction_id] => 1259615
		[transaction_date] => 27/04/2009 10:58:01
		[order_id] => 090427-100756-1552
		[amount] => 19.95
		[from_email] => customer@example.com
		[to_email] => merchant@example.com
		[security_key] => abcdefghijklmnopqrst1234567 (32 char string)
		[status] => test
		[custom] =>
		*/

		// Make sure this payment hasn't been processed already!
		if($GLOBALS['db']->select('CubeCart_transactions','id',array('trans_id' => $_POST["transaction_id"]))) return false;

		$cart_order_id = $_POST['order_id']; // Used in remote.php $cart_order_id is important for failed orders

		$order				= Order::getInstance();
		$order_summary		= $order->getSummary($cart_order_id);

		$transData['customer_id'] 	= $order_summary["customer_id"];
		$transData['gateway'] 		= "Nochex";
		$transData['trans_id'] 		= $_POST["transaction_id"];
		$transData['amount'] 		= sprintf("%.2f",$_POST['amount']);
		$transData['status'] 		= $_POST['status'];
		
		$extraNotes = '';

		foreach ($_POST as $key => $value) {
			$params[$key]	= stripslashes($value);
		}
		## Set the request URL
		$url = "www.nochex.com/nochex.dll/apc/apc";
		## Start a request object
		$request = new Request($url);
		$request->setSSL();
		$request->setData($params);
		## Send the request
		$data = $request->send();

		$cart_order_id	= $_POST['order_id'];
		if (!empty($cart_order_id) && !empty($data)) {
			$order				= Order::getInstance();
			$order_summary		= $order->getSummary($cart_order_id);
			$transData['notes']	= array();
			switch ($data) {
				case 'DECLINED':
					## If this is the response, then something is wrong with the callback
					$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
					$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
					$transData['notes'] .= 'Payment declined.';
					$transData['notes'] = $extraNotes;
					break;
				case 'AUTHORISED':  ## All good, update order status
					$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
					$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
					$extraNotes			.= 'Payment authorised.';
					$transData['notes'] = $extraNotes;
					break;
					}
		}
		$order->logTransaction($transData);
	}
	public function process() {
		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
		return false;
	}

}