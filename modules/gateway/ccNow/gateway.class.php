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
			'action'	=> 'https://www.ccnow.com/cgi-local/transact.cgi',
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> 'auto',
		);
		return $transfer;
	}

	public function repeatVariables() {
		return (isset($hidden)) ? $hidden : false;
	}

	public function fixedVariables() {
		
		$hidden	= array(
			'x_product_sku_0' 		=> $this->_basket['cart_order_id'],
			'x_product_title_0' 	=> "CubeCart Order #".$this->_basket['cart_order_id'],
			'x_product_quantity_0' 	=> 1,
			'x_product_unitprice_0' => $this->_basket['total'],
			'x_product_url_0' 		=> $GLOBALS['storeURL'],

			'x_login'				=> $this->_module['acName'],
			'x_version'				=> '1.0',
			'x_fp_arg_list'			=> 'x_login^x_fp_arg_list^x_fp_sequence^x_amount^x_currency_code',
			'x_fp_hash'				=> md5($this->_module['acName']."^x_login^x_fp_arg_list^x_fp_sequence^x_amount^x_currency_code^".$this->_basket['cart_order_id']."^".$this->_basket['total']."^".$GLOBALS['config']->get('config', 'default_currency')."^".$this->_module['actKey']),
			'x_fp_sequence'			=> $this->_basket['cart_order_id'],
			'x_currency_code'		=> $GLOBALS['config']->get('config', 'default_currency'),
			'x_method'				=> ($this->_module['testMode']) ? 'TEST' : 'NONE',

			'x_name'				=> $this->_basket['billing_address']['first_name']." ".$this->_basket['billing_address']['last_name'],
			'x_address'				=> $this->_basket['billing_address']['line1'],
			'x_address2'			=> $this->_basket['billing_address']['line2'],
			'x_city'				=> $this->_basket['billing_address']['town'],
			'x_state'				=> $this->_basket['billing_address']['state'],
			'x_zip'					=> $this->_basket['billing_address']['postcode'],
			'x_country'				=> $this->_basket['billing_address']['country_iso'],
			'x_phone'				=> $this->_basket['billing_address']['phone'],
			'x_email'				=> $this->_basket['billing_address']['email'],

			'x_ship_to_name'		=> $this->_basket['delivery_address']['first_name']." ".$this->_basket['billing_address']['last_name'],
			'x_ship_to_address'		=> $this->_basket['delivery_address']['line1'],
			'x_ship_to_address2'	=> $this->_basket['delivery_address']['line2'],
			'x_ship_to_city'		=> $this->_basket['delivery_address']['town'],
			'x_ship_to_state'		=> $this->_basket['delivery_address']['state'],
			'x_ship_to_zip'			=> $this->_basket['delivery_address']['postcode'],
			'x_ship_to_country'		=> $this->_basket['delivery_address']['country_iso'],

			'x_invoice_num'			=> $this->_basket['cart_order_id'],
			'x_instructions'		=> $this->_basket['comments'],
			'x_amount'				=> $this->_basket['total'],
			'x_shipping_amount'		=> '0.00',
			'x_discount_amount' 	=> '',
			'x_discount_label' 		=> '',
		);
		return (isset($hidden)) ? $hidden : false;
	}

	##################################################

	public function call() {
		echo "OK"; // ccNow requires confirmation with OK string
		/* Example return data
		Array
		(
		   [x_storeid] => cubecarttest
		   [x_method] => TEST
		   [x_status] => test
		   [x_orderid] => 199-40-6587
		   [x_orderdate] => 01/28/2011 12:53
		   [x_invoice_num] => 110128-185313-8872
		   [x_currency_code] => GBP
		   [x_fp_hash] => ca7e0a7c2ba6a1ec42ac61deffbc2ecf
		   [x_amount] => 8.39
		   [x_timestamp] => 01/28/2011 13:01
		   [x_clientid] => cubecarttest
		)
		Status List
		received:	New order just placed by buyer. Note that the order has not yet been processed by CCNow payment or fraud screening system. That is you should not process or ship this order until you receive the "pending" order alert.
		
		pending:	Order has been approved and available for order processing. These are the orders that show up in the Sales / Pending Orders area in your CCNow backroom, and in which you ordinarily first receive an email notice.
		
		test:	Test order has been placed.
		
		canceled:	Order has been canceled. Note if you previously received a "pending" alert for this order you should not ship the order since it has been marked canceled in CCNow system. If you are configured for "pending" alerts but not "received" alerts, then you will only receive the "canceled" alert if order was canceled after CCNow approval, that is after you received the "pending" alert. If you are also configured for "received" alerts, then you will receive a "canceled" alert if order canceled from any batch processing state prior to approval ("pending" alert).
		
		declined:	Payment was declined for this order.
		
		rejected:	Order has been rejected due to not meeting CCNow guidelines, consumer fraud, or missing information in order. Note you also receive an email notice for this event.
		
		partial_refund:	A partial refund has been issued for this order.
		
		refunded:	The order has been fully refunded to consumer.
		
		chargeback:	A chargeback has been issued for this order. Note you also receive an email notice for this event.
		
		chargeback_reversal:	The chargeback on an order has been reversed and funds put back into your account log.
		
		open_inquiry:	An inquiry has been opened on order. Note you also receive an email notice for this event.
		
		reject_inquiry:	An inquiry resolution you had entered in your backroom has been rejected.
		
		close_inquiry:	An inquiry has been closed on order.
		*/
		$order				= Order::getInstance();
		$cart_order_id		= $_POST['x_invoice_num'];
		$order_summary		= $order->getSummary($cart_order_id);
		
		if($_POST['x_status'] == 'Pending'){
			$notes 	= 'Card was successfully processed.';
			$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
		}
		

		$transData['notes']			= $notes;
		$transData['gateway']		= $_REQUEST['module'];
		$transData['order_id']		= $cart_order_id;
		$transData['trans_id']		= $_POST['x_orderid'];
		$transData['amount']		= $_POST['x_amount'];
		$transData['status']		= $_POST['x_status'];
		$transData['customer_id']	= $order_summary['customer_id'];
		$transData['extra']			= '';
		$order->logTransaction($transData);
		return true;
	}

	public function process() {
		// ccNow doesn't send back any data at all right now so we have to leave it pending
		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
	}

	public function form() {
		return false;
	}
}