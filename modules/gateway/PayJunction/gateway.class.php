<?php
class Gateway {
	private $_module;
	private $_basket;
	private $_result_message;

	public function __construct($module = false, $basket = false) {
		$this->_session	=& $GLOBALS['user'];

		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
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
		return (isset($hidden)) ? $hidden : false;
	}

	public function fixedVariables() {
		$hidden['gateway']	= basename(dirname(__FILE__));
		return (isset($hidden)) ? $hidden : false;
	}

	public function call() {
		return false;
	}

	public function process() {

		$order				= Order::getInstance();
		$order_summary		= $order->getSummary($this->_basket['cart_order_id']);

		## Process the payment
		$url	= ($this->_module['testMode']) ? 'demo.payjunction.com' : 'payjunction.com';
		$path	= '/quick_link';

		$post_array 	= array (
			"dc_test"				=> $this->_module['testMode'],
			"dc_logon"				=> $this->_module['user'],
			"dc_password"			=> $this->_module['pass'],
			"dc_transaction_type"	=> "AUTHORIZATION_CAPTURE",
			"dc_transaction_amount"	=> $order_summary['total'],
			"dc_first_name"			=> trim($_POST['firstName']),
			"dc_last_name"			=> trim($_POST['lastName']),
			"dc_number"				=> trim($_POST["cardNumber"]),
			"dc_expiration_month"	=> str_pad($_POST["expirationMonth"], 2, '0', STR_PAD_LEFT),
			"dc_expiration_year"	=> substr($_POST["expirationYear"],2,2),
			"dc_verification_number"=> trim($_POST['cvc2']),
			"dc_address"			=> trim($_POST['addr1']).' '.trim($_POST['addr2']),
			"dc_city"				=> trim($_POST['city']),
			"dc_state"				=> trim($_POST['state']),
			"dc_zipcode"			=> trim($_POST['postcode']),
			"dc_country"			=> trim($_POST['country'])
		);
		
		## Setup the POST string to send to the PayJunction Server
		$data = "";
		foreach($post_array as $key => $value) {
			$data .= $key."=".urlencode($value)."&";
		}

		$request	= new Request($url, $path);
		$request->setSSL();
		$request->setData(rtrim($data,"& "));
		$response	= $request->send();

		## Parse the response from PayJunction
		$content 	= array_values(explode(chr(28), $response));
		$response 	= array();
		while ($key_value = next ($content)) {
			list ($key, $value) = explode('=', $key_value);
			$response[$key] = $value;
		}

		if ($response['response_code'] == "00" || $response['response_code'] == "85") {
			$order->orderStatus(Order::ORDER_PROCESS, $order_summary['cart_order_id']);
			$order->paymentStatus(Order::PAYMENT_SUCCESS, $order_summary['cart_order_id']);
			$transData['trans_id'] = $response['transaction_id'];
			$status = 'Success';
			$this->_result_message 	= $response['response_message'];
		} else {
			$order->orderStatus(Order::ORDER_PENDING, $order_summary['cart_order_id']);
			$order->paymentStatus(Order::PAYMENT_PENDING, $order_summary['cart_order_id']);
			$status = 'Fail';
			switch($response['response_code']) {
				case "FE":
					$this->_result_message = "There was a format error with your Trinity Gateway Service (API) request.";
				break;
				case "LE":
					$this->_result_message =  "Could not log you in (problem with dc_logon and/or dc_password).";
				break;
				case "AE":
					$this->_result_message =  "Address verification failed because address did not match.";
				break;
				case "ZE":
					$this->_result_message =  "Address verification failed because zip did not match.";
				break;
				case "XE":
					$this->_result_message =  "Address verification failed because zip and address did not match.";
				break;
				case "YE":
					$this->_result_message =  "Address verification failed because zip and address did not match.";
				break;
				case "OE":
					$this->_result_message =  "Address verification failed because address or zip did not match.";
				break;
				case "UE":
					$this->_result_message =  "Address verification failed because cardholder address unavailable.";
				break;
				case "RE":
					$this->_result_message =  "Address verification failed because address verification system is not working.";
				break;
				case "SE":
					$this->_result_message =  "Address verification failed because address verification system is unavailable.";
				break;
				case "EE":
					$this->_result_message =  "Address verification failed because transaction is not a mail or phone order.";
				break;
				case "GE":
					$this->_result_message =  "Address verification failed because international support is unavailable.";
				break;
				case "CE":
					$this->_result_message =  "Declined because CVV2/CVC2 code did not match.";
				break;
				case "NL":
					$this->_result_message =  "Aborted because of a system error, please try again later.";
				break;
				case "AB":
					$this->_result_message =  "Aborted because of an upstream system error, please try again later.";
				break;
				case "04":
					$this->_result_message =  "Declined. Pick up card.";
				break;
				case "07":
					$this->_result_message =  "Declined. Pick up card (Special Condition).";
				break;
				case "41":
					$this->_result_message =  "Declined. Pick up card (Lost).";
				break;
				case "43":
					$this->_result_message =  "Declined. Pick up card (Stolen).";
				break;
				case "13":
					$this->_result_message =  "Declined because of the amount is invalid.";
				break;
				case "14":
					$this->_result_message =  "Declined because the card number is invalid.";
				break;
				case "80":
					$this->_result_message =  "Declined because of an invalid date.";
				break;
				case "05":
					$this->_result_message =  "Declined. Do not honor.";
				break;
				case "51":
					$this->_result_message =  "Declined because of insufficient funds.";
				break;
				case "N4":
					$this->_result_message =  "Declined because the amount exceeds issuer withdrawal limit.";
				break;
				case "61":
					$this->_result_message =  "Declined because the amount exceeds withdrawal limit.";
				break;
				case "62":
					$this->_result_message =  "Declined because of an invalid service code (restricted).";
				break;
				case "65":
					$this->_result_message =  "Declined because the card activity limit exceeded.";
				break;
				case "93":
					$this->_result_message =  "Declined because there a violation (the transaction could not be completed).";
				break;
				case "06":
					$this->_result_message =  "Declined because address verification failed.";
				break;
				case "54":
					$this->_result_message =  "Declined because the card has expired.";
				break;
				case "15":
					$this->_result_message =  "Declined because there is no such issuer.";
				break;
				case "96":
					$this->_result_message =  "Declined because of a system error.";
				break;
				case "N7":
					$this->_result_message =  "Declined because of a CVV2/CVC2 mismatch.";
				break;
				case "M4":
					$this->_result_message =  "Declined.";
				break;
			}
		}

		$transData['notes']			= $this->_result_message;
		$transData['gateway']		= 'PayJunction';
		$transData['order_id']		= $order_summary['cart_order_id'];
		$transData['amount']		= $order_summary['total'];
		$transData['status']		= $status;
		$transData['customer_id']	= $order_summary['customer_id'];
		$order->logTransaction($transData);

		if($response['response_code'] == "00" || $response['response_code'] == "85") {
			httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
		}
	}

	##################################################

	private function formatMonth($val) {
		return $val." - ".strftime("%b", mktime(0,0,0,$val,1 ,2009));
	}

	public function form() {
		## Process transaction
		if (isset($_POST['cardNumber']) && !empty($_POST['cardNumber'])) {
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
			'line1'		 => isset($_POST['addr1']) ? $_POST['addr1'] : $this->_basket['billing_address']['line1'],
			'line2'		 => isset($_POST['addr2']) ? $_POST['addr2'] : $this->_basket['billing_address']['line2'],
			'town'		 => isset($_POST['city']) ? $_POST['city'] : $this->_basket['billing_address']['town'],
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