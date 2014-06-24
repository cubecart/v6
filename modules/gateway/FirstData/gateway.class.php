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
		if ($this->_module['gateway_solution'] == 'connect') {
			$action = ($this->_module['gateway_mode'] == 'live') ? 'https://www.linkpointcentral.com/lpc/servlet/lppay' : 'https://www.staging.linkpointcentral.com/lpc/servlet/lppay';
			$transfer	= array(
				'action'	=> $action,
				'method'	=> 'post',
				'target'	=> '_self',
				'submit'	=> 'auto',
			);
			return $transfer;
		}
		return false;
	}

	public function repeatVariables() {
		return false;
	}

	public function fixedVariables() {

		$hidden	= array(
				'mode' 					=> 'payonly',
				'chargetotal' 			=> $this->_basket['total'],
				'storename' 			=> $this->_module['storename'],
				'baddr1' 				=> $this->_basket['billing_address']['line1']." ".$this->_basket['billing_address']['line2'],
				'bzip' 					=> $this->_basket['billing_address']['postcode'],
				'txnorg' 				=> 'eci',
				'txntype' 				=> $this->_module['txntype'],
				'authenticateTransaction' => 'false',
				'bname' 				=> $this->_basket['billing_address']['first_name']." ".$this->_basket['billing_address']['last_name'],
				'bcity' 				=> $this->_basket['billing_address']['town'],
				'bcountry' 				=> $this->_basket['billing_address']['country_iso'],
				'bstate' 				=> $this->_basket['billing_address']['state'],
				'email' 				=> $this->_basket['billing_address']['email'],
				'responseURL' 		=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=FirstData',
				'oid' 					=> $this->_basket['cart_order_id'],
				'comments' 				=> $this->_basket['customer_comments'],
				'phone' 				=> $this->_basket['billing_address']['phone'],
		);
		return $hidden;
	}

	##################################################

	public function call() {
		return false;
	}

	public function process() {

		$order				= Order::getInstance();
		$cart_order_id		= $_POST['OID'];
		$order_summary		= $order->getSummary($cart_order_id);

		if($_POST['status']=='APPROVED'){
			if ($this->_module['txntype']=='sale') {
				$notes 	= 'Card was charged successfully.';
				$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
				$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
			} else {
				$notes 	= 'Card was authorized successfully.';
				$status = 'Approved';
				$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
				$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
			}
		} else {
			$notes 	=  $_POST['failReason'];
			$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
		}
		$transData['notes']			= $notes;
		$transData['gateway']		= $_GET['module'];
		$transData['order_id']		= $cart_order_id;
		$transData['trans_id']		= '';
		$transData['amount']		= $_POST['chargetotal'];
		$transData['status']		= $_POST['status'];
		$transData['customer_id']	= $order_summary['customer_id'];
		$transData['extra']			= '';
		$order->logTransaction($transData);

		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
		return false;
	}

	public function form() {
		return false;
	}
}