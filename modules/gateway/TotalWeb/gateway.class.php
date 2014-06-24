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

		// check for test mode or live mode
		if ($this->_module['testMode'] == "test") {
			$url = "https://testsecure.totalwebsecure.com/paypage/clear.asp";
		} else {
			$url = "https://secure.totalwebsecure.com/paypage/clear.asp" ;
		}

		$transfer	= array(
			'action'	=> $url, 
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

		// total web require currency as iso code
      	
		switch ($GLOBALS['config']->get('config', 'default_currency')) {
			case "GBP":
                		$TransactionCurrency = "826";
                		break;
        		case "USD":
                		$TransactionCurrency = "840";
                		break;
        		case "EUR":
                		$TransactionCurrency = "978";
                		break;
        		case "AUD":
                		$TransactionCurrency = "036";
                		break;
      		}

		$hidden	= array(
				'CustomerID' 	=> $this->_module['acNo'],
				'Notes' 	=> $this->_basket['cart_order_id'],
				'TransactionAmount'	=> $this->_basket['total'],
				'Amount'	=> $this->_basket['total'],
				'TransactionCurrency'	=> $TransactionCurrency,
				'CustomerEmail'		=> $this->_basket['billing_address']['email'],
			 	'PayPageType' => '2',
			 	'redirectorsuccess' =>  $GLOBALS['storeURL'].'/index.php?_a=complete',
			 	'redirectorfailed' =>  $GLOBALS['storeURL'].'/index.php?_a=gateway',
			 	'CallBackURLAuth' => $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=TotalWeb&&cart_order_id='.$this->_basket['cart_order_id']

						);
		return $hidden;
	}

	##################################################

	public function call() {
		return false;
	}

	public function process() {

		$cart_order_id = sanitizeVar($_REQUEST['cart_order_id']); // Used in remote.php $cart_order_id is important for failed orders

		$order		= Order::getInstance();
		$order_summary	= $order->getSummary($cart_order_id);


		$transData['customer_id'] = $order_summary["customer_id"];
        	$transData['gateway']  = "TotalWeb";
        	$transData['trans_id'] = $_REQUEST['TransID'];
        	$transData['order_id'] = $_REQUEST['Note'];
		$transData['amount']   = sprintf("%.2f",$_REQUEST['Amount']);

		if ( $this->_module['secretkey'] <> $_REQUEST['SecretKey'] ) {
        		echo "1";
			$transData['status'] 	= "Fail";
			$transData['notes'] 	= "Secret Keys do not match";
			$order->logTransaction($transData);
			return false;
		}

		if($_REQUEST['Status'] == "Success"){
			$transData['status'] 	= "Success";
			$transData['notes'] 	= "Payment was successful.";
			$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
		} else {
			$transData['status'] 	= "Fail";
			$transData['notes'] 	= "Payment unsuccessful. More information may be available in the Total Web solutions ecom control panel.";
			$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
		}
		$order->logTransaction($transData);
       		echo "1";

//		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));

		return false;
	}

	public function form() {
		return false;
	}
}
