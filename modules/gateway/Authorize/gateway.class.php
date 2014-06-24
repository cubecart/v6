<?php
class Gateway {
	private $_config;
	private $_module;
	private $_basket;
	private $_result_message;
	private $_url;
	private $_path;

	public function __construct($module = false, $basket = false) {
		$this->_db		=& $GLOBALS['db'];

		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
		$this->_url			= $this->_module['testMode'] ? 'test.authorize.net' : 'secure.authorize.net';
		$this->_path		= '/gateway/transact.dll';
		$this->_aim_delimiter = '|';
	}

	##################################################

	public function transfer() {
		$transfer	= array(
			'action'	=> ($this->_module['mode']=='sim') ? 'https://'.$this->_url.$this->_path : currentPage(),
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> ($this->_module['mode']=='sim') ? 'auto'  : 'manual',
		);
		return $transfer;
	}

	##################################################

	public function repeatVariables() {
		return (isset($hidden)) ? $hidden : false;
	}

	public function fixedVariables() {
		if($this->_module['mode']=='sim') {
			
			$fp_sequence 	= $this->_basket['cart_order_id'].time(); // Enter an invoice or other unique number.
			$fp_timestamp 	= time();
			$fingerprint 	= $this->_getFingerprint($this->_module['acNo'],$this->_module['txnkey'], $this->_basket['total'], $fp_sequence, $fp_timestamp);
			
			$hidden = array(
				'x_type'				=> $this->_module['payment_type'], //AUTH_CAPTURE or AUTH_ONLY
				'x_login' 				=> $this->_module['acNo'],
				'x_fp_hash'				=> $fingerprint,
				'x_amount'				=> $this->_basket['total'],
				'x_fp_timestamp'		=> $fp_timestamp,
				'x_fp_sequence'			=> $fp_sequence,
				'x_version'				=> '3.1',
				'x_show_form'			=> 'payment_form',
				'x_test_request'		=> 'false',
				'x_method'				=> 'cc',
				'x_invoice_num'			=> $this->_basket['cart_order_id'],
				'x_description'			=> "Payment for order #".$this->_basket['cart_order_id'],
				
				'x_first_name'			=> $this->_basket['billing_address']['first_name'],
				'x_last_name'			=> $this->_basket['billing_address']['last_name'],
				'x_address'				=> $this->_basket['billing_address']['line1'].' '.$this->_basket['billing_address']['line2'],
				'x_city'				=> $this->_basket['billing_address']['town'],
				'x_state'				=> $this->_basket['billing_address']['state'],
				'x_zip'					=> $this->_basket['billing_address']['postcode'],
				'x_country'				=> $this->_basket['billing_address']['country_iso'],
				
				'x_ship_to_first_name'	=> $this->_basket['delivery_address']['first_name'],
				'x_ship_to_last_name'	=> $this->_basket['delivery_address']['last_name'],
				'x_ship_to_address'		=> $this->_basket['delivery_address']['line1'].' '.$this->_basket['delivery_address']['line2'],
				'x_ship_to_city'		=> $this->_basket['delivery_address']['town'],
				'x_ship_to_state'		=> $this->_basket['delivery_address']['state'],
				'x_ship_to_zip'			=> $this->_basket['delivery_address']['postcode'],
				'x_ship_to_country'		=> $this->_basket['delivery_address']['country_iso'],
				
				'x_email'				=> $this->_basket['billing_address']['email'],
				'x_phone'				=> $this->_basket['billing_address']['phone'],
				
				'x_customer_ip' 		=> get_ip_address(),
				'x_receipt_link_method' => 'POST',
				'x_receipt_link_text'	=> 'Return to Store &amp; Confirm Order',
				'x_receipt_link_url'	=> $GLOBALS['storeURL'].'/index.php?_g=remote&type=gateway&cmd=process&module=Authorize'
				
				/* Ideal setup doesn't work :(
				'x_receipt_link_url'	=> $GLOBALS['storeURL'].'?_a=vieworder&cart_order_id='.$this->_basket['cart_order_id'],
				'x_relay_response'		=> 'TRUE',
				'x_relay_url'			=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=call&module=Authorize'
				*/
			);
		} else {
			$hidden['gateway']	= basename(dirname(__FILE__));
		}
		return (isset($hidden)) ? $hidden : false;
	}

	public function call() {
		return false;
	}

	public function process() {
	
		$order				= Order::getInstance();
		$cart_order_id 		= $this->_basket['cart_order_id'];
		$order_summary		= $order->getSummary($cart_order_id);
		
		if($this->_module['mode']=='sim') {
			
			if($_POST['x_response_code']){
				$status = 'Approved';
				$order->orderStatus(Order::ORDER_PROCESS, $_POST['x_invoice_num']);
				$order->paymentStatus(Order::PAYMENT_SUCCESS, $_POST['x_invoice_num']);
			} else {
				$status = 'Declined';
				$order->orderStatus(Order::ORDER_PENDING, $_POST['x_invoice_num']);
				$order->paymentStatus(Order::PAYMENT_PENDING, $_POST['x_invoice_num']);
			}
	
			$transData['notes']			= $_POST['x_response_reason_text'];
			$transData['order_id']		= $_POST['x_invoice_num'];
			$transData['trans_id']		= $_POST['x_trans_id'];
			$transData['amount']		= $_POST['x_amount'];
			$transData['extra']			= '';
		
		} else {
			## Process the payment for AIM	
			$authnet_array	= array(
				'x_test_request'		=> $x_test_request,
				'x_login'				=> $this->_module['acNo'],
				'x_tran_key'			=> $this->_module['txnkey'],
				"x_password"			=> $this->_module['password'],
				'x_version'				=> '3.1',
				'x_delim_data'			=> 'TRUE',
				'x_delim_char'			=> $this->_aim_delimiter,
				'x_type'				=> $this->_module['payment_type'], //AUTH_CAPTURE or AUTH_ONLY
				'x_method'				=> 'CC',
				'x_amount'				=> $this->_basket['total'],
				'x_card_num'			=> trim($_POST['cardNumber']),
				'x_exp_date'			=> str_pad($_POST['expirationMonth'], 2, '0', STR_PAD_LEFT).substr($_POST["expirationYear"],2,2),
				'x_card_code'			=> trim($_POST['cvc2']),
				'x_invoice_num'			=> $this->_basket['cart_order_id'],
				'x_description'			=> "Payment for order #".$this->_basket['cart_order_id'],
				'x_first_name'			=> trim($_POST['firstName']),
				'x_last_name'			=> trim($_POST['lastName']),
				'x_address'				=> trim($_POST['addr1']).' '.trim($_POST['addr2']),
				'x_city'				=> trim($_POST['city']),
				'x_state'				=> trim($_POST['state']),
				'x_zip'					=> trim($_POST['postcode']),
				'x_country'				=> trim($_POST['country']),
				'x_email'				=> trim($_POST['emailAddress']),
				'x_customer_ip' 		=> get_ip_address(),
				'x_test_request'		=> ($this->_module['testMode']) ? 'TRUE' : 'FALSE'
			);
			$request	= new Request($this->_url, $this->_path);
			$request->setSSL();
			$request->setData($authnet_array);
			$resp		= $request->send();
			$results 	= explode($this->_aim_delimiter,$resp);
	
			if($results[0] == 1) {
				$status	= 'Approved';
				$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
				$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
			} elseif($results[0] == 2) {
				$status	= 'Declined';
				$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
				$order->paymentStatus(Order::PAYMENT_DECLINE, $cart_order_id);
			} elseif($results[0] == 3) {
				$status	= 'Error';
				$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
			}
	
			$this->_result_message		= $results[3];
	
			$transData['notes']			= $this->_result_message;
			$transData['order_id']		= $results[7];
			$transData['trans_id']		= $results[37];
			$transData['amount']		= $results[9];
			$transData['status']		= $status;
			$transData['customer_id']	= $order_summary['customer_id'];
			$transData['extra']			= '';
		}
		
		$transData['status']		= $status;
		$transData['customer_id']	= $order_summary['customer_id'];
		$transData['gateway']		= 'Authorize.net ('.strtoupper($this->_module['mode']).')';
		$order->logTransaction($transData);
		
		if($status=='Approved') {
			httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
		}
	}

	##################################################

	private function formatMonth($val) {
		return $val." - ".strftime("%b", mktime(0,0,0,$val,1 ,2009));
	}

	public function form() {
		
		## Process transaction
		if (isset($_POST['cardNumber'])) {
			$return	= $this->process();
		}

		// Display payment result message
		if (!empty($this->_result_message))	{
			$GLOBALS['gui']->setError($this->_result_message);
		}

		//Show Expire Months
		$selectedMonth	= (isset($_POST['expirationMonth'])) ? $_POST['expirationMonth'] : date('m');
		for($i = 1; $i <= 12; ++$i) {
			$val = sprintf('%02d',$i);
			$smarty_data['card']['months'][]	= array(
				'selected'	=> ($val == $selectedMonth) ? 'selected="selected"' : '',
				'value'		=> $val,
				'display'	=> $this->formatMonth($val),
			);
		}

		## Show Expire Years
		$thisYear = date("Y");
		$maxYear = $thisYear + 10;
		$selectedYear = isset($_POST['expirationYear']) ? $_POST['expirationYear'] : ($thisYear+2);
		for($i = $thisYear; $i <= $maxYear; ++$i) {
			$smarty_data['card']['years'][]	= array(
				'selected'	=> ($i == $selectedYear) ? 'selected="selected"' : '',
				'value'		=> $i,
			);
		}
		$GLOBALS['smarty']->assign('CARD', $smarty_data['card']);
		
		$smarty_data['customer'] = array(
			'first_name' => isset($_POST['firstName']) ? $_POST['firstName'] : $this->_basket['billing_address']['first_name'],
			'last_name'	 => isset($_POST['lastName']) ? $_POST['lastName'] : $this->_basket['billing_address']['last_name'],
			'email'      => isset($_POST['emailAddress']) ? $_POST['emailAddress'] : $this->_basket['billing_address']['email'],
			'add1'		 => isset($_POST['addr1']) ? $_POST['addr1'] : $this->_basket['billing_address']['line1'],
			'add2'		 => isset($_POST['addr2']) ? $_POST['addr2'] : $this->_basket['billing_address']['line2'],
			'city'		 => isset($_POST['city']) ? $_POST['city'] : $this->_basket['billing_address']['town'],
			'state'		 => isset($_POST['state']) ? $_POST['state'] : $this->_basket['billing_address']['state'],
			'postcode'		 => isset($_POST['postcode']) ? $_POST['postcode'] : $this->_basket['billing_address']['postcode']
		);
		
		$GLOBALS['smarty']->assign('CUSTOMER', $smarty_data['customer']);
		
		## Country list
		$countries = $GLOBALS['db']->select('CubeCart_geo_country', false, false, array('name' => 'ASC'));
		if ($countries) {
			$currentIso = isset($_POST['country']) ? $_POST['country'] : $this->_basket['billing_address']['country_iso'];
			foreach ($countries as $country) {
				$country['selected']	= ($country['iso'] == $currentIso) ? 'selected="selected"' : '';
				$smarty_data['countries'][]	= $country;
			}
			$GLOBALS['smarty']->assign('COUNTRIES', $smarty_data['countries']);
		}
		
		## Check for custom template for module in skin folder
		$file_name = 'form.tpl';
		$form_file = $GLOBALS['gui']->getCustomModuleSkin('gateway', dirname(__FILE__), $file_name);
		$GLOBALS['gui']->changeTemplateDir($form_file);
		$ret = $GLOBALS['smarty']->fetch($file_name);
		$GLOBALS['gui']->changeTemplateDir();
		return $ret;
	}
	
	private static function _getFingerprint($api_login_id, $transaction_key, $amount, $fp_sequence, $fp_timestamp) {
        if (function_exists('hash_hmac')) {
            return hash_hmac("md5", $api_login_id . "^" . $fp_sequence . "^" . $fp_timestamp . "^" . $amount . "^", $transaction_key); 
        }
        return bin2hex(mhash(MHASH_MD5, $api_login_id . "^" . $fp_sequence . "^" . $fp_timestamp . "^" . $amount . "^", $transaction_key));
    }
}