<?php
class Gateway {
	private $_config;
	private $_module;
	private $_basket;
	private $_result_message;
	private $_url;
	private $_path;
	
	private $_target_app;
	private $_response_mode;
	private $_response_fmt;
	private $_upg_auth;
	private $_delimited_fmt_field_delimiter;
	private $_delimited_fmt_include_fields;
	private $_delimited_fmt_value_delimiter;
	
	private $_cards;
	private $_auth;

	public function __construct($module = false, $basket = false) {
		$this->_db		=& $GLOBALS['db'];

		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
		$this->_url			= 'transactions.innovativegateway.com';
		$this->_path		= '/servlet/com.gateway.aai.Aai';
		
		$this->_target_app		= 'WebCharge_v5.06';
		$this->_response_mode 	= 'simple';
		$this->_response_fmt	= 'delimited';
		$this->_upg_auth		= 'zxcvlkjh';
		$this->_delimited_fmt_field_delimiter	= '=';
		$this->_delimited_fmt_include_fields	= 'true';
		$this->_delimited_fmt_value_delimiter	= '|';
		
		$this->_cards = array(
			'visa' 		=> 'Visa',
			'mc' 		=> 'MasterCard',
			'amex' 		=> 'American Express',
			'diners' 	=> 'Diners',
			'discover' 	=> 'Discover',
			'jcb' 		=> 'JCB'
		);
		
		$this->_auth = ($module['testMode']) ? array('username' => 'gatewaytest', 'pw' => 'GateTest2002') : array('username' => $module['username'], 'pw' => $module['pw']);  
		
	}

	##################################################

	public function transfer() {
		$transfer	= array(
			'action'	=> currentPage(),
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> 'manual',
		);
		return $transfer;
	}

	##################################################

	public function repeatVariables() {
		return false;
	}

	public function fixedVariables() {
		return array('gateway' => 'Intuit');
	}

	public function call() {
		return false;
	}

	public function process() {
	
		$order				= Order::getInstance();
		$cart_order_id		= $this->_basket['cart_order_id'];
		$order_summary		= $order->getSummary($cart_order_id);
		
		$transaction = array(
			'target_app' 					=> $this->_target_app,
			'response_mode' 				=> $this->_response_mode,
			'response_fmt' 					=> $this->_response_fmt,
			'upg_auth' 						=> $this->_upg_auth,
			'delimited_fmt_field_delimiter' => $this->_delimited_fmt_field_delimiter,
			'delimited_fmt_include_fields' 	=> $this->_delimited_fmt_include_fields,
			'delimited_fmt_value_delimiter' => $this->_delimited_fmt_value_delimiter,
			
			'username'	=> $this->_auth['username'],
			'pw'		=> $this->_auth['pw'],
			
			'trantype' 		=> 'sale',
			'reference' 	=> '',
			'trans_id'  	=> '',
			'authamount'	=> '',
			'ordernumber'	=> $cart_order_id,
			'cardtype'		=> $_POST['cardType'],
			'ccnumber'		=> trim($_POST['cardNumber']),
			'month'			=> str_pad($_POST['expirationMonth'], 2, '0', STR_PAD_LEFT),
			'year'			=> $_POST["expirationYear"],
			'fulltotal'		=> $this->_basket['total'],
			
			'ccname' 	=> $_POST['firstName'].' '.$_POST['lastName'],
			'baddress'	=> $_POST['addr1'],
			'baddress1'	=> $_POST['addr2'],
			'bcity'		=> $_POST['city'],
			'bstate'	=> $_POST['state'],
			'bzip'		=> $_POST['postcode'],
			'bcountry'	=> $_POST['country'],
			'bphone'	=> '',
			'email'		=> $_POST['emailAddress']
		);
		
				
		//$response = PostTransaction($transaction);
		$request	= new Request($this->_url, $this->_path);
		$request->setSSL();
		$request->setData($transaction);
		$response	= $request->send();
		$response_array = explode($this->_delimited_fmt_value_delimiter, $response);

		$result = array();
		foreach($response_array as $key) {
			$bits = explode("=", $key);
			$result[strtolower($bits[0])] = strip_tags($bits[1]);
		}
				
		if ($result['approval'] != '') {
			$status = $result["approval"];
			$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
			$transData['notes']	= '';
		} else {
			$status = 'Failure';
			$transData['notes']	= $result["error"];
			$GLOBALS['gui']->setError($result["error"]);
		}
		
		$transData['status']		= $status;
		$transData['order_id']		= $cart_order_id;
		$transData['trans_id']		= $result['anatransid'];
		$transData['amount']		= $result['fulltotal'];
		$transData['customer_id']	= $order_summary['customer_id'];
		$transData['gateway']		= 'Intuit';
		$order->logTransaction($transData);
		
		if($result['approval'] != '') {
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
		
		if(is_array($this->_cards)) {
			foreach($this->_cards as $key => $value) {
				if($value) {
					$smarty_data['cards'][] = array (
						'selected' 	=> ($_POST['cardType']==$key) ? 'selected="selected"' : '',
						'value'		=> $key,
						'display'	=> $value
					);
					$GLOBALS['smarty']->assign('CARDS', $smarty_data['cards']);
				}
			}
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
}