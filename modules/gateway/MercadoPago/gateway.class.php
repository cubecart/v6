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
			'action'	=> 'https://www.mercadopago.com/mlv/buybutton',
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
		$hidden	= array(
			'acc_id' 				=> $this->_module['acc_id'],
			'enc'	 				=> $this->_module['codigo'],
			'price' 				=> $this->_basket['total'],
			'seller_op_id'			=> $this->_basket['cart_order_id'],
			'name'					=> 'Repuestos varios',
			
			'currency'				=> $this->_module['country'],
			
			'url_process'			=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=MercadoPago&cart_id='.$this->_basket['cart_order_id'],
			'url_succesfull'		=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=MercadoPago&cart_id='.$this->_basket['cart_order_id'],
			'url_cancel'			=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=MercadoPago&cart_id='.$this->_basket['cart_order_id'],
			
			'cart_email' 			=> $this->_basket['billing_address']['email'],
			'cart_name' 			=> $this->_basket['billing_address']['first_name'], 
			'cart_surname' 			=> $this->_basket['billing_address']['last_name'],
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
		$notes 				= 'Pago Procesado!';
		$status 			= 'Processed';
		$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
		$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);

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