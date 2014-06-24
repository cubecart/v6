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
			'action'	=> 'https://www.myvirtualmerchant.com/VirtualMerchant/process.do',
			//'action'	=> 'https://demo.myvirtualmerchant.com/VirtualMerchantDemo/process.do',
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
						
		$hidden		= 	array(				
			'ssl_transaction_type' 		=> empty($this->_module['payment_type']) ? 'ccsale' : $this->_module['payment_type'],
			'ssl_merchant_id' 			=> $this->_module['acNo'],
			'ssl_invoice_number' 		=> $this->_basket['cart_order_id'],
			'ssl_amount' 				=> $this->_basket['total'],
			'ssl_user_id' 				=> $this->_module['usrId'],
			'ssl_pin' 					=> $this->_module['pin'],
			'ssl_show_form' 			=> 'true',
			'ssl_result_format' 		=> 'HTML', 
			'ssl_receipt_link_method'	=> 'POST',
			'ssl_receipt_link_url'		=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=VirtualMerchant',
			'ssl_receipt_link_text'		=> 'Return To Store',
			'ssl_receipt_decl_method'	=> 'POST', 
			'ssl_receipt_decl_post_url' => $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=VirtualMerchant', 
			'ssl_receipt_apprvl_method'	=> 'POST', 
			'ssl_receipt_apprvl_post_url' => $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=VirtualMerchant', 
			'ssl_company'				=> $orderSum['companyName'],
			'ssl_first_name'			=> $this->_basket['billing_address']['first_name'],
			'ssl_last_name'				=> $this->_basket['billing_address']['last_name'],
			'ssl_avs_address'			=> $this->_basket['billing_address']['line1'],
			'ssl_address2'				=> $this->_basket['billing_address']['line2'],
			'ssl_city'					=> $this->_basket['billing_address']['town'],
			'ssl_state'					=> $this->_basket['billing_address']['state'],
			'ssl_avs_zip'				=> substr($this->_basket['billing_address']['postcode'], 0, 5),
			'ssl_country'				=> $this->_basket['billing_address']['country_iso'], 
			'ssl_phone'					=> $this->_basket['billing_address']['phone'],
			'ssl_email'					=> $this->_basket['billing_address']['email']
		);
			
		if($this->_module['testMode']==1) {
			$hidden['ssl_test_mode'] = 'true';
		} else {
			$hidden['ssl_test_mode'] = 'false';
		}
						
		return $hidden;
	}

	##################################################

	public function call() {
		return false;
	}

	public function process() {

		$cart_order_id = sanitizeVar($_REQUEST['ssl_invoice_number']); // Used in remote.php $cart_order_id is important for failed orders

		$order				= Order::getInstance();
		$order_summary		= $order->getSummary($cart_order_id);

		$transData['customer_id'] 	= $order_summary["customer_id"];
		$transData['gateway'] 		= "VirtualMerchant";
		$transData['trans_id'] 		= $_REQUEST['ssl_txn_id'];
		$transData['amount'] 		= $_REQUEST['ssl_amount'];
		$transData['status'] 	= $_REQUEST['ssl_result_message'];
		
		if($_REQUEST['ssl_result_message']=="APPROVED" || $_REQUEST['ssl_result_message']=="APPROVAL"){
			$transData['notes'] 	= "Payment was successful.";
			$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
		} else {
			$transData['notes'] 	= "Payment unsuccessful or pending.";
			$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
		}
		$order->logTransaction($transData);
		httpredir($GLOBALS['storeURL'].'/index.php?_a=complete');
		return false;
	}

	public function form() {
		return false;
	}
}