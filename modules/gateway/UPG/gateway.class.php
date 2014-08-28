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
			'action'	=> 'https://www.secure-server-hosting.com/secutran/secuitems.php',
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> 'auto',
		);
		return $transfer;
	}

	public function repeatVariables() {
		$string = '';
		foreach ($this->_basket['contents'] as $key => $product) {
			$string .= '['.$product['product_code'].'||'.$product['name'].'|'.$product['price'].'|'.$product['quantity'].'|'.$product['price']*$product['quantity'].']';
		}
		
		return array('secuitems' => $string);
	}

	public function fixedVariables() {
		$hidden	= array(
			'filename' => $this->_module['shreference'].'/'.$this->_module['filename'],
			'shreference'	=> $this->_module['shreference'],
			'checkcode'	=> $this->_module['checkcode'],
			'transactionamount' => $this->_basket['total'],
			'transactioncurrency'	=> $GLOBALS['config']->get('config', 'default_currency'),
			'shippingcharge' => $this->_basket['shipping']['value'],
			'transactiontax' => $this->_basket['total_tax'],
			'cardholdersname' => $this->_basket['billing_address']['first_name'].' '.$this->_basket['billing_address']['last_name'],
			'cardholdersemail' => $this->_basket['billing_address']['email'],
			'cardholderaddr1' => $this->_basket['billing_address']['line1'],
			'cardholderaddr2' => $this->_basket['billing_address']['line2'],
			'cardholdercity' => $this->_basket['billing_address']['town'],
			'cardholderstate' => $this->_basket['billing_address']['state'],
			'cardholderpostcode' => $this->_basket['billing_address']['postcode'],
			'cardholdercountry' => $this->_basket['billing_address']['postcode'],
			'cardholdertelephonenumber' => $this->_basket['billing_address']['phone'],
			'callbackurl'	=> $GLOBALS['storeURL'].'/modules/gateway/UPG/call.php',
			'callbackdata' => 'oid|'.$this->_basket['cart_order_id']
		);

		return $hidden;
	}

	##################################################

	public function call() {

		$order	= Order::getInstance();

		$cart_order_id		= $_GET['oid'];
		$order_summary		= $order->getSummary($cart_order_id);
		
		if($_GET['transactionnumber'] == '-1') {
			$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
			$result = 'Fail';
		} else {
			$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
			$result = 'Success';
		}

		$transData['notes']			= "AVS Result: ".$_GET['cv2avsresult']."<br>Auth Code: ".$_GET['upgauthcode'];
		if(isset($_GET['failurereason']) && !empty($_GET['failurereason'])) {
			$transData['notes']	.= "<br>Fail Reason: ".$_GET['failurereason'];
		}
		$transData['gateway']		= $_GET['module'];
		$transData['order_id']		= $cart_order_id;
		$transData['trans_id']		= $_GET['transactionnumber'];
		$transData['amount']		= $order_summary['total'];
		$transData['status']		= $result.' ('.$_GET['upgcardtype'].')';
		$transData['customer_id']	= $order_summary['customer_id'];
		$transData['extra']			= '';
		$order->logTransaction($transData);
		header("HTTP/1.1 200 OK");
		return true;
	}

	public function process() {
		// Redirect to _a=complete, and drop out unneeded variables
		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
	}

	public function form() {
		return false;
	}
}