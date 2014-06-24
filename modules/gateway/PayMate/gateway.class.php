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
			'action'	=> 'https://www.paymate.com/PayMate/ExpressPayment',
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
			'mid' 					=> $this->_module['acNo'],
			'amt'					=> $this->_basket['total'],
			'currency'				=> $GLOBALS['config']->get('config', 'default_currency'),
			'ref'					=> $this->_basket['cart_order_id'],
			'pmt_sender_email'		=> $this->_basket['billing_address']['email'],
			'pmt_contact_firstname'	=> $this->_basket['billing_address']['first_name'],
			'pmt_contact_surname'	=> $this->_basket['billing_address']['last_name'],
			'pmt_contact_phone'		=> $this->_basket['billing_address']['phone'],
			'pmt_country'			=> $this->_basket['billing_address']['country_iso'],
			'regindi_state'			=> $this->_basket['billing_address']['state'],
			'regindi_address1'		=> $this->_basket['billing_address']['line1'],
			'regindi_address2'		=> $this->_basket['billing_address']['line2'],
			'regindi_sub'			=> $this->_basket['billing_address']['town'],
			'regindi_pcode'			=> $this->_basket['billing_address']['postcode'],
			'return'				=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=PayMate'
		);

		return $hidden;
	}

	##################################################

	public function call() {
		return false;
	}

	public function process() {
		$order				= Order::getInstance();
		$cart_order_id		= $_REQUEST['ref'];
		$order_summary		= $order->getSummary($cart_order_id);
		
		if($_REQUEST['responseCode'] == 'PA'){
			$notes 	= 'Proceed with organising delivery of items or provision of service immediately.';
			$status = 'Approved';
			$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
		} elseif ($_REQUEST['responseCode'] == 'PP') {
			$notes 	= 'Await email notification from Paymate prior to organising delivery of purchased items or service';
			$status = 'Processing';
			$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
		} else {
			$notes = 'Contact buyer to organise another means of payment or discontinue order.';
			$status = 'Declined';
			$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
		}

		$transData['notes']			= $notes;
		$transData['gateway']		= $_REQUEST['module'];
		$transData['order_id']		= $cart_order_id;
		$transData['trans_id']		= $_REQUEST['transactionID'];
		$transData['amount']		= isset($_REQUEST['paymentAmount']) ? $_REQUEST['paymentAmount'] : '';
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