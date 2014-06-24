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
			'action'	=> ($this->_module['mode']) ? 'https://www.payson.se/testagent/default.aspx' : 'https://www.payson.se/merchant/default.aspx',
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
		
		$cost = sprintf("%.2f",(float)$this->_basket['total']);
  		$cost = str_replace(".", ",", $cost);
  		$shipcost = '0,00';

  		$handler_url = $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=Payson';

  		$cHash = md5($this->_module['email'].":".$cost.":".$shipcost.":".str_replace('&','&',$handler_url).":1".$this->_module['key']);

		$hidden	= array(
			'BuyerEmail' 		=> 	$this->_basket['billing_address']['email'],
			'BuyerFirstName'	=> 	$this->_basket['billing_address']['first_name'],
			'BuyerLastName'		=>	$this->_basket['billing_address']['last_name'],
			'SellerEmail'		=> 	$this->_module['email'],
			'Description'		=> 	'Order #'.$this->_basket['cart_order_id'],
      		'Cost'				=>	$cost,
			'RefNr' 			=>	$this->_basket['cart_order_id'],
			'OkUrl' 			=>	$handler_url,
			'CancelUrl'			=>	$handler_url,
			'GuaranteeOffered'	=> 	'1',
			'PaymentMethod'		=>	$this->_module['paymethod'],
			'AgentId'			=>	$this->_module['agentid'],
			'MD5'				=>	$cHash,
			'ExtraCost'			=>	$shipcost
		);
		return $hidden;

	}

	##################################################

	public function call() {
		return false;
	}

	public function process() {
		
		$retHashString	= str_replace('&','&',$_GET['OkURL']).$_GET['Paysonref'].$this->_module['key'];
		$retHash		= md5($retHashString);
		
		$order			= Order::getInstance();
		$cart_order_id	= $_GET['RefNr'];
		$order_summary	= $order->getSummary($cart_order_id);


		if ($retHash==$_GET['MD5'] && $order_summary["cart_order_id"]) {
			
			if ($order->orderSum["status"]==2) {
				return false;
			} else {
				$notes 	= 'Payment was successful.';
				$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
				$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
				$status = 'Success';
			}
			
		} else {
			$notes 	= 'Payment failed.';
			$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
			$status = 'Fail';
		}

		$transData['notes']			= $notes;
		$transData['gateway']		= $_GET['module'];
		$transData['order_id']		= $cart_order_id;
		$transData['trans_id']		= $_GET['Paysonref'];
		$transData['amount']		= $order_summary['total'];
		$transData['status']		= $status;
		$transData['customer_id']	= $order_summary['customer_id'];
		$transData['extra']			= '';
		$order->logTransaction($transData);

		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
		return true;
	}

	public function form() {
		return false;
	}
}