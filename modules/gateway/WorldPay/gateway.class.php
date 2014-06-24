<?php
class Gateway {
	private $_config;
	private $_module;
	private $_basket;

	public function __construct($module = false, $basket = false) {
		$this->_session	=& $GLOBALS['user'];

		$this->_module			= $module;
		$this->_basket			= $basket;
	}

	public function transfer() {
		$transfer	= array(
			'action'	=> ($tis->_module['testMode']) ? 'https://secure-test.worldpay.com/wcc/purchase' : 'https://secure.worldpay.com/wcc/purchase',
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


		$invAddL2 	=	!empty($this->_basket['billing_address']['line2']) ? $this->_basket['billing_address']['line2'].",&#10;" : "";
		$invAdd 	= 	$this->_basket['billing_address']['line1'].",&#10;".
						$invAddL2.
						$this->_basket['billing_address']['town'].",&#10;".
						$this->_basket['billing_address']['state'];

		$hidden		= 	array(
							'authMode' 	=> 'E',
							'instId' 	=> $this->_module['acNo'],
							'cartId' 	=> $this->_basket['cart_order_id'],
							'MC_OID' 	=> $this->_basket['cart_order_id'],
							'amount'	=> $this->_basket['total'],
							'currency'	=> $GLOBALS['config']->get('config', 'default_currency'),
							'desc'		=> "Payment for Order ".$this->_basket['cart_order_id'],
							'name'		=> $this->_basket['billing_address']['first_name'].' '.$this->_basket['billing_address']['last_name'],
							'address'	=> $invAdd,
							'postcode'	=> $this->_basket['billing_address']['postcode'],
							'country'	=> $this->_basket['billing_address']['country'],
							'tel'		=> $this->_basket['billing_address']['phone'],
							'email'		=> $this->_basket['billing_address']['email'],
							'testMode'	=> $this->_module['testMode']
						);
		return $hidden;
	}

	##################################################

	public function call() {
		return false;
	}

	public function process() {

		$cart_order_id = sanitizeVar($_REQUEST['cartId']); // Used in remote.php $cart_order_id is important for failed orders

		$order			= Order::getInstance();
		$order_summary		= $order->getSummary($cart_order_id);

		$transData['customer_id'] 	= $order_summary["customer_id"];
		$transData['gateway'] 		= "WorldPay";
		$transData['trans_id'] 		= $_REQUEST['transId'];
		$transData['amount'] 		= sprintf("%.2f",$_REQUEST['amount']);

		$GLOBALS['storeURL'] = str_replace('/modules/gateway/WorldPay','',$GLOBALS['storeURL']);

		if($_REQUEST['transStatus'] == "Y" && $order_summary["cart_order_id"]){
			$transData['status'] 	= "Success";
			$transData['notes'] 	= "Payment was successful.";
			$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
		} else if ($order_summary["cart_order_id"]) {
			$transData['status'] 	= "Failed";
			$transData['notes'] 	= "Payment unsuccessful. More information may be available in the WorldPay control panel.";
			$order$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
		} else {
			$transData['status'] 	= "None";
			$transData['notes'] 	= "Payment unsuccessful if any. Order not found.";
		}
		$order->logTransaction($transData);
		unset($GLOBALS['seo']);
		httpredir($GLOBALS['storeURL'].'/index.php?_a=complete', '', true, 200, true);
		return false;
	}

	public function form() {
		return false;
	}
}