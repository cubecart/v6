<?php
class Gateway {
	private $_config;
	private $_module;
	private $_basket;
	private $_result_message;

	public function __construct($module = false, $basket = false) {
		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
	}

	##################################################

	public function transfer() {
		$transfer	= array(
			'action'	=> ($this->_module['sandbox']) ? 'https://safe.sandbox-cresecure.net/securepayments/a1/cc_collection.php' : 'https://safe.cresecure.net/securepayments/a1/cc_collection.php',
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> 'auto',
		);
		return $transfer;
	}

	##################################################

	public function repeatVariables() {
		return false;
	}

	public function fixedVariables() {
		$vars	= array(
			// Required
			'CRESecureID'			=> $this->_module['CRESecureID'],
			'CRESecureAPIToken'		=> $this->_module['CRESecureAPIToken'],
			'total_amt'				=> $this->_basket['total'],
			'return_url'			=> $GLOBALS['config']->get('config', 'ssl_url').'/index.php?_g=rm&mod_type=gateway&cmd=process&module=CRESecure',
			'content_template_url'	=> $GLOBALS['config']->get('config', 'ssl_url').'/index.php?_a=template&type=gateway&module=CRESecure',
			// Optional (but useful)
			'order_id'				=> $this->_basket['cart_order_id'],
		#	'customer_id'			=> '',
			'currency_code'			=> $GLOBALS['config']->get('config', 'default_currency'),
			'ip_address'			=> get_ip_address(),
			'sess_name'				=> session_id(),
			// Address (Billing)
			'customer_firstname'	=> $this->_basket['billing_address']['first_name'],
			'customer_lastname'		=> $this->_basket['billing_address']['last_name'],
			'customer_company'		=> $this->_basket['billing_address']['company_name'],
			'customer_address'		=> $this->_basket['billing_address']['line1'],
			'customer_city'			=> $this->_basket['billing_address']['town'],
			'customer_state'		=> $this->_basket['billing_address']['state'],
			'customer_postal_code'	=> $this->_basket['billing_address']['postcode'],
			'customer_country'		=> getCountryFormat($this->_basket['billing_address']['country_id'], 'numcode', 'iso3'),
			'customer_email'		=> $this->_basket['billing_address']['email'],
			// Address (Delivery)
			'delivery_firstname'	=> $this->_basket['delivery_address']['first_name'],
			'delivery_lastname'		=> $this->_basket['delivery_address']['last_name'],
			'delivery_company'		=> $this->_basket['delivery_address']['company_name'],
			'delivery_address'		=> $this->_basket['delivery_address']['line1'],
			'delivery_city'			=> $this->_basket['delivery_address']['town'],
			'delivery_state'		=> $this->_basket['delivery_address']['state'],
			'delivery_postal_code'	=> $this->_basket['delivery_address']['postcode'],
			'delivery_country'		=> getCountryFormat($this->_basket['delivery_address']['country_id'], 'numcode', 'iso3'),
			'delivery_email'		=> $this->_basket['billing_address']['email'],
		);
		return (isset($vars)) ? $vars : false;
	}

	public function call() {
		## IPN callback
		return false;
	}

	public function process() {
		## Return from host
		if (isset($_GET['sess_name']) && $_GET['sess_name'] == session_id()) {
			$order	= Order::getInstance();

			$cart_order_id	= ($GLOBALS['session']->has('cart_order_id', 'basket')) ? $GLOBALS['session']->get('cart_order_id', 'basket') : $_GET['order_id'];

			if (($order_summary = $order->getSummary($cart_order_id)) !== false) {
				if ($_GET['code'] == '000' && $_GET['msg'] == 'Success') {
					$notes 	= 'Card was successfully processed.';
					$status = 'Processed';
					$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
					$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
					$redirect	= array('_a' => 'complete');
				} else {
					$notes	= sprintf('%s (Code: %03d)', $_GET['msg'], $_GET['code']);
					$status	= 'Pending';
					$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
					$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
					## GUI_MESSAGE?
					$redirect = array('_a' => 'gateway');
				}
				$transData['notes']			= $notes;
				$transData['gateway']		= $_GET['module'];
				$transData['order_id']		= $cart_order_id;
				$transData['trans_id']		= isset($_POST['TxnGUID']) ? $_POST['TxnGUID'] : '';
				$transData['amount']		= $order_summary['total'];
				$transData['status']		= $status;
				$transData['customer_id']	= $order_summary['customer_id'];
				$transData['extra']			= '';
				$order->logTransaction($transData);
				httpredir(currentPage(true, $redirect));
			}
		} else {
			httpredir(currentPage(true, array('_a' => 'gateway')));
		}
	}

	##################################################

	public function form() {
		return false;
	}
}