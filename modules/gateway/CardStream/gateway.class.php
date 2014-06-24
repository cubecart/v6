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
			'action'	=> (filter_var($this->_module['payment_page_url'], FILTER_VALIDATE_URL)) ? $this->_module['payment_page_url'] : 'https://gateway.cardstream.com/secure.asp',
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
			'VPMerchantID' => $this->_module['merchant_id'],
			'VPMerchantPassword' => $this->_module['merchant_password'],
			'VPAmount' => ($this->_basket['total']*100),
			'VPCountryCode' => 826, 
			'VPCurrencyCode' => 826,
			'VPTransactionUnique' => md5($this->_basket['cart_order_id'].time()), 
			'VPOrderDesc' => $this->_basket['cart_order_id'], 
			'VPCallBack' => $GLOBALS['storeURL'].'/modules/gateway/CardStream/response_process.php',
			'VPVerifyURL' => $GLOBALS['storeURL'].'/modules/gateway/CardStream/response_verify.php',
			'VPBillingHouseNumber' => $this->_basket['billing_address']['line1'],
			'VPBillingStreet' => $this->_basket['billing_address']['line2'],
			'VPBillingCity' => $this->_basket['billing_address']['town'],
			'VPBillingPostCode' => $this->_basket['billing_address']['postcode'],
			'VPBillingEmail' => $this->_basket['billing_address']['email'], 
			'VPBillingPhoneNumber' => $this->_basket['billing_address']['phone']
		);
		return (isset($hidden)) ? $hidden : false;
	}

	##################################################

	public function call() {
		return false;
	}

	public function process() {
		
		$order				= Order::getInstance();
		$cart_order_id		= $_GET['VPOrderDesc'];
		$order_summary		= $order->getSummary($cart_order_id);
		
		if($_GET['VPResponseCode'] == '00'){
			$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
		}
		
		$transData['notes']			= '';
		$transData['gateway']		= 'CardStream';
		$transData['order_id']		= $_GET['VPOrderDesc'];
		$transData['trans_id']		= $_GET['VPCrossReference'];
		$transData['amount']		= ($_GET['VPAmountReceived']>0) ? ($_GET['VPAmountReceived']/10) : '';
		$transData['status']		= $_GET['VPMessag'];
		$transData['customer_id']	= $order_summary['customer_id'];
		$transData['extra']			= '';
		$order->logTransaction($transData);
		
		// ccNow doesn't send back any data at all right now so we have to leave it pending
		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
	}

	public function form() {
		return false;
	}
}