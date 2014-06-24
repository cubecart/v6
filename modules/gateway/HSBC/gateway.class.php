<?php
## Thanks to Adam from Xomy.com for originally making this module for CubeCart v3!
## Much of the original code remains.
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
			'action'	=> 'index.php?_g=rm&type=gateway&cmd=process&module=HSBC&auth=1&cart_order_id='.$this->_basket['cart_order_id'],
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> 'manual',
		);
		return $transfer;
	}

	public function repeatVariables() {
		return false;
	}

	public function fixedVariables() {
		return false;
	}

	##################################################

	public function call() {
		return false;
	}

	public function process() {

		$order				= Order::getInstance();

		if(isset($_GET['auth'])) {

		$order_summary		= $order->getSummary($_GET['cart_order_id']);

			## Set pass through variables
			$ccPassthru[] = trim($_POST['emailAddress']);
			$ccPassthru[] = $order_summary['phone'];
			$ccPassthru[] = trim($_POST['firstName']);
			$ccPassthru[] = trim($_POST['lastName']);
			$ccPassthru[] = trim($_POST['city']);
			$ccPassthru[] = trim($_POST['addr1']);
			$ccPassthru[] = trim($_POST['addr2']);
			$ccPassthru[] = trim($_POST['state']);
			$ccPassthru[] = $order_summary['postcode'];
			$ccPassthru[] = trim($_POST['cvc2']);
			$ccPassthru[] = str_pad($_POST['expirationMonth'], 2, '0', STR_PAD_LEFT).'/'.$_POST['expirationYear'];
			$ccPassthru[] = trim($_POST['issue']);
			$ccPassthru[] = trim($_POST['cardNumber']);
			$ccPassthru[] = str_pad($_POST['startMonth'], 2, '0', STR_PAD_LEFT).'/'.str_pad($_POST['startYear'], 2, '0', STR_PAD_LEFT);
			$ccPassthru[] = $_POST['cardType'];
			$ccPassthru[] = $order_summary['total'];
			$ccPassthru[] = $order_summary['customer_id'];
			$ccPassthru[] = $order_summary['cart_order_id'];

			$authdata = array(
				'logo' 				=> $GLOBALS['config']->get('config', 'ssl_url').'/modules/gateway/HSBC/admin/logo.gif',
				'ajax' 				=> $GLOBALS['config']->get('config', 'ssl_url').'/modules/gateway/HSBC/skin/images/ajax.gif',
				'vbv' 				=> $GLOBALS['config']->get('config', 'ssl_url').'/modules/gateway/HSBC/skin/images/vbv.png',
				'mcs' 				=> $GLOBALS['config']->get('config', 'ssl_url').'/modules/gateway/HSBC/skin/images/mcs.png',
				'pas' 				=> 'www.ccpa.hsbc.com/ccpa',
				'CardExpiration' 	=> $_POST['expirationYear'].str_pad(trim($_POST['expirationMonth']), 2, '0', STR_PAD_LEFT),
				'CardholderPan' 	=> $_POST['cardNumber'],
				'CcpaClientId' 		=> $this->_module['alias'],
				'PurchaseAmount' 	=> $order_summary['total'],
				'PurchaseAmountRaw' => preg_replace('#[^0-9]#', '', $order_summary['total']),
				'MD'				=> base64_encode(implode('|', $ccPassthru)),
				'ResultUrl' 		=> $GLOBALS['config']->get('config', 'ssl_url').'/index.php?_a=gateway&gateway=HSBC'

			);

			$GLOBALS['smarty']->assign("DATA", $authdata);
						
			## Check for custom template for module in skin folder
			$file_name = 'auth.tpl';
			$form_file = $GLOBALS['gui']->getCustomModuleSkin('gateway', dirname(__FILE__), $file_name);
			$GLOBALS['gui']->changeTemplateDir($form_file);
			$ret = $GLOBALS['smarty']->fetch($file_name);
			$GLOBALS['gui']->changeTemplateDir();
			die($ret);	
			
		} else {

			$order_summary		= $order->getSummary($this->_basket['cart_order_id']);

			$pasData = explode('|', base64_decode($_POST['MD']));
			switch ($_POST['CcpaResultsCode']) {
				case "0":
					## Payer authentication was successful.
					$pasConfig['PayerSecurityLevel'] 		= "2";
					$pasConfig['PayerAuthenticationCode'] 	= $_POST['CAVV'];
					$pasConfig['PayerTxnId'] 				= $_POST['XID'];
					$pasConfig['CardholderPresentCode'] 	= "13";
				break;
				case "1":
					## The cardholder's card was not within a participating BIN range.
					$pasConfig['PayerSecurityLevel'] 		= "5";
					$pasConfig['PayerAuthenticationCode'] 	= false;
					$pasConfig['PayerTxnId'] 				= false;
					$pasConfig['CardholderPresentCode'] 	= "13";
				break;
				case "2":
					## The cardholder was in a participating BIN range, but was not enrolled in 3-D Secure.
					$pasConfig['PayerSecurityLevel'] 		= "1";
					$pasConfig['PayerAuthenticationCode'] 	= false;
					$pasConfig['PayerTxnId'] 				= false;
					$pasConfig['CardholderPresentCode'] 	= "13";
				break;
				case "3":
					## The cardholder was not enrolled in 3-D Secure. However, the cardholder was authenticated using the 3-D Secure attempt server.
					$pasConfig['PayerSecurityLevel'] 		= "6";
					$pasConfig['PayerAuthenticationCode'] 	= $_POST['CAVV'];
					$pasConfig['PayerTxnId'] 				= $_POST['XID'];
					$pasConfig['CardholderPresentCode'] 	= "13";
				break;
				case "4":
					## The cardholder was enrolled in 3-D Secure. A PARes has not yet been received for this transaction.
					$pasConfig['PayerSecurityLevel'] 		= "4";
					$pasConfig['PayerAuthenticationCode'] 	= false;
					$pasConfig['PayerTxnId'] 				= false;
					$pasConfig['CardholderPresentCode'] 	= false;
				break;
				case "5":
					## The cardholder has failed payer authentication.
				break;
				case "6":
					## Signature validation of the results from the ACS failed.
				break;
				case "7":
					## The ACS was unable to provide authentication results.
					$pasConfig['PayerSecurityLevel'] 		= "4";
					$pasConfig['PayerAuthenticationCode'] 	= false;
					$pasConfig['PayerTxnId'] 				= false;
					$pasConfig['CardholderPresentCode'] 	= false;
				break;
				case "8":
					## The CCPA failed to communicate with the Directory Server.
					$pasConfig['PayerSecurityLevel'] 		= "4";
					$pasConfig['PayerAuthenticationCode'] 	= false;
					$pasConfig['PayerTxnId'] 				= false;
					$pasConfig['CardholderPresentCode'] 	= false;
				break;
				case "9":
					## The CCPA was unable to interpret the results from payer authentication or enrolment verification.
					$pasConfig['PayerSecurityLevel'] 		= "4";
					$pasConfig['PayerAuthenticationCode'] 	= false;
					$pasConfig['PayerTxnId'] 				= false;
					$pasConfig['CardholderPresentCode'] 	= false;
				break;
				case "10":
					## The CCPA failed to locate or access configuration information for this merchant.
					$pasConfig['PayerSecurityLevel'] 		= "4";
					$pasConfig['PayerAuthenticationCode'] 	= false;
					$pasConfig['PayerTxnId'] 				= false;
					$pasConfig['CardholderPresentCode'] 	= false;
				break;
				case "11":
					## Data submitted or configured in the CCPA has failed validation checks.
					$pasConfig['PayerSecurityLevel'] 		= "4";
					$pasConfig['PayerAuthenticationCode'] 	= false;
					$pasConfig['PayerTxnId'] 				= false;
					$pasConfig['CardholderPresentCode'] 	= false;
				break;
				case "12":
					## Unexpected system error from CCPA.
					$pasConfig['PayerSecurityLevel'] 		= "4";
					$pasConfig['PayerAuthenticationCode'] 	= false;
					$pasConfig['PayerTxnId'] 				= false;
					$pasConfig['CardholderPresentCode'] 	= false;
				break;
				case "14":
					## Indicates that card submitted is not recognised, or the PAS does not support the card type.
					$pasConfig['PayerSecurityLevel'] 		= "7";
					$pasConfig['PayerAuthenticationCode'] 	= false;
					$pasConfig['PayerTxnId'] 				= false;
					$pasConfig['CardholderPresentCode'] 	= false;
				break;
			}

			switch ($this->_module['test']) {
				case 2:
					## Testmode - Always Declined
					$pp_mode = 'N';
					break;
				case "1":
					## Testmode - Always Approved
					$pp_mode = 'Y';
					break;
				case 0:
				default:
					## Live Mode
					$pp_mode = 'P';
			}

			switch ($this->_module['authmode']) {
				case '1':
					$authMode = 'PreAuth';
					break;
				case '0':
				default:
					$authMode = 'Auth';
			}

			## Rewrite me for SimpleXML/XMLWriter...
			$xml	= new XML(true);
			$xml->startElement('EngineDocList');
			$xml->writeElement('DocVersion','1.0');
			$xml->startElement('EngineDoc');
			$xml->writeElement('ContentType','OrderFormDoc');
			$xml->startElement('User');
			$xml->writeElement('Name',$this->_module['userID']);
			$xml->writeElement('Password',$this->_module['passPhrase']);

			$ClientIdAttributes['DataType'] = 'S32';
			$xml->setElement('ClientId', $this->_module['acNo'], $ClientIdAttributes, false);

			$xml->endElement();
			$xml->startElement('Instructions');
			$xml->writeElement('Pipeline','Payment');
			$xml->endElement();
			$xml->startElement('OrderFormDoc');
			$xml->writeElement('Mode',$pp_mode);
			$xml->startElement('Consumer');
			$xml->writeElement('Email',$pasData[0]);
			$xml->startElement('BillTo');
			$xml->startElement('Location');
			$xml->writeElement('TelVoice',$pasData[1]);
			$xml->startElement('Address');
			$xml->writeElement('Name',$pasData[2].' '.$pasData[3]);
			$xml->writeElement('City',$pasData[4]);
			$xml->writeElement('Street1',$pasData[5]);
			$xml->writeElement('Street2',$pasData[6]);
			$xml->writeElement('StateProv',$pasData[7]);
			$xml->writeElement('PostalCode',$pasData[8]);
			$xml->endElement();
			$xml->endElement();
			$xml->endElement();
			$xml->startElement('PaymentMech');
			$xml->startElement('CreditCard');
			$xml->writeElement('Cvv2Indicator',(!empty($pasData[9])?1:2));
			$xml->writeElement('Cvv2Val',$pasData[9]);

			$Expires = array(
				'DataType' 	=> 'ExpirationDate',
				'Locale'	=> '840'
			);
			$xml->setElement('Expires', $pasData[10], $Expires, false);

			if ((($pasData[14] == 9)||($pasData[14] == 10))&&($pasData[11])){
				$xml->writeElement('IssueNum',$pasData[11]);
			}

			$xml->writeElement('Number',$pasData[12]);

			if ((($pasData[14] == 9)||($pasData[14] == 10))&&($pasData[13] !== "00/00")){

				$StartDate	= array(
					'DataType'	=> 'StartDate',
					'Locale'	=> '840'
				);
				$xml->setElement('StartDate', $pasData[13], $StartDate, false);
			}

			$xml->writeElement('Type',$pasData[14]);
			$xml->endElement();
			$xml->endElement();
			$xml->endElement();
			$xml->startElement('Transaction');
			$xml->writeElement('Type',$authMode);
			$xml->writeElement('ChargeDesc1',false);
			$xml->startElement('CurrentTotals');
			$xml->startElement('Totals');

			$Total = array (
				'DataType' => 'Money',
				'Currency' => '826'
			);
			$xml->setElement('Total', preg_replace('#[^0-9]#i', '', $pasData[15]), $Total, false);

			$xml->endElement();
			$xml->endElement();
			$xml->endElement();
			## PAS
			$xml->writeElement('PayerSecurityLevel',$pasConfig['PayerSecurityLevel']);
			$xml->writeElement('PayerAuthenticationCode',$pasConfig['PayerAuthenticationCode']);
			$xml->writeElement('PayerTxnId',$pasConfig['PayerTxnId']);
			$xml->writeElement('CardholderPresentCode',$pasConfig['CardholderPresentCode']);
			$xml->endElement();
			$xml->endElement();
			$xml->endElement();
			$xml->endElement();

			## Send request
			$data 		= $xml->getDocument();
			$request	= new Request('www.secure-epayments.apixml.hsbc.com');
			$request->setSSL();
			$request->customOption(CURLOPT_SSLVERSION, 3);
			$request->customOption(CURLOPT_SSL_CIPHER_LIST, 'RC4-MD5');
			$request->setData('CLRCMRC_XML='.$data);
			$request->cache(false);
			$response	= $request->send();
			
			$return		= str_replace("\n", '', $response);

			## Transaction Data
			$transData['gateway']		= 'HSBC API';
			$transData['customer_id']	= $order_summary['customer_id'];
			$transData['order_id']		= $order_summary['cart_order_id'];
			$transData['amount']		= $order_summary['total'];

			$xmldata = new SimpleXMLElement($return);
			$transData['trans_id']	= (string)$xmldata->EngineDoc->Overview->TransactionId;

			if (in_array((string)$xmldata->EngineDoc->Overview->TransactionStatus, array('A', 'C'))) {
				$order->orderStatus(Order::ORDER_PROCESS, $order_summary['cart_order_id']);
				$order->paymentStatus(Order::PAYMENT_SUCCESS, $order_summary['cart_order_id']);
				$transData['status']	= 'Success';
			} else {
				$transData['notes']		= (string)$xmldata->EngineDoc->Overview->Notice;
				$order->orderStatus(Order::ORDER_PENDING, $order_summary['cart_order_id']);
				if (stristr((string)$xmldata->EngineDoc->OrderFormDoc->FraudInfo->Alerts->Action, 'Reject')) {
					$this->_result_message = (string)$xmldata->EngineDoc->Overview->CcReturnMsg;
					$transData['notes']	.= (string)$xmldata->EngineDoc->Overview->CcReturnMsg."<br />".(string)$xmldata->EngineDoc->OrderFormDoc->FraudInfo->Alerts->Message;
					$order->paymentStatus(Order::PAYMENT_DECLINE, $order_summary['cart_order_id']);
				} else {
					$order->paymentStatus(Order::PAYMENT_FAILED, $order_summary['cart_order_id']);
					$this->_result_message = (string)$xmldata->EngineDoc->Overview->Notice;
				}

			}
			$order->logTransaction($transData);
			if($transData['status']=='Success') {
				httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
			}

		}

	}

	private function formatMonth($val) {
		return $val." - ".strftime("%b", mktime(0,0,0,$val,1 ,2009));
	}

	public function form() {
		if(!$GLOBALS['config']->get('config', 'ssl_url')) {
			die('The HSBC payment module will only work under SSL. Please login to the admin side of your store and specify as secure SSL URL.');
		}
		## Process transaction
		if (isset($_POST['CcpaResultsCode'])) {
			$this->process();
		}

		## Display payment result message
		if (!empty($this->_result_message))	$GLOBALS['gui']->setError($this->_result_message);

		## Card Types
		if ($this->_module['amex']) {
			$GLOBALS['smarty']->assign("AMEX", true);
			$GLOBALS['smarty']->assign("MAX_CVV2", '4');
		} else {
			$GLOBALS['smarty']->assign("MAX_CVV2", '3');
		}

		## Show Start Months
		$selectedMonth	= (isset($_POST['startMonth'])) ? $_POST['startMonth'] : '';
		$smarty_data['card']['start']['months'][]	= array(
			'selected'	=> '',
			'value'		=> '',
			'display'	=> '',
		);
		for($i=1;$i<=12;$i++) {
			$val = sprintf('%02d',$i);
			$smarty_data['card']['start']['months'][]	= array(
				'selected'	=> ($val == $selectedMonth) ? 'selected="selected"' : '',
				'value'		=> $val,
				'display'	=> $this->formatMonth($val),
			);
		}

		## Show Start Years
		$thisYear 	= date("Y");
		$maxYear 	= $thisYear-5;
		$selectedYear = isset($_POST['startYear']) ? $_POST['startYear'] : ($thisYear+2);
		$smarty_data['card']['start']['years'][]	= array(
			'selected'	=> '',
			'value'		=> '',
		);
		for($i=$thisYear;$i>=$maxYear;$i--) {
			$smarty_data['card']['start']['years'][]	= array(
				'selected'	=> ($i == $selecetdYear) ? 'selected="selected"' : '',
				'value'		=> str_pad(substr($i,-2), 2, '0', STR_PAD_LEFT),
				'display'	=> $i,
			);
		}

		## Show Expire Months
		$selectedMonth	= (isset($_POST['expirationMonth'])) ? $_POST['expirationMonth'] : date('m');
		for($i=1;$i<=12;$i++) {
			$val = sprintf('%02d',$i);
			$smarty_data['card']['expire']['months'][]	= array(
				'selected'	=> ($val == $selectedMonth) ? 'selected="selected"' : '',
				'value'		=> $val,
				'display'	=> $this->formatMonth($val),
			);
		}

		## Show Expire Years
		$thisYear = date("Y");
		$maxYear = $thisYear + 10;
		$selectedYear = isset($_POST['expirationYear']) ? $_POST['expirationYear'] : ($thisYear+2);
		for($i=$thisYear;$i<=$maxYear;$i++) {
			$smarty_data['card']['expire']['years'][]	= array(
				'selected'	=> ($i == $selecetdYear) ? 'selected="selected"' : '',
				'value'		=> str_pad(substr($i,-2), 2, '0', STR_PAD_LEFT),
				'display'	=> $i,
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
			'postcode'	 => isset($_POST['postcode']) ? $_POST['postcode'] : $this->_basket['billing_address']['postcode']
		);

		$GLOBALS['smarty']->assign('CUSTOMER', $smarty_data['customer']);
		
		## Country list
		$countries	= $GLOBALS['db']->select('CubeCart_geo_country', false, false, array('name' => 'ASC'));
		if ($countries) {
			$currentIso = isset($_POST['country']) ? $_POST['country'] : $this->_basket['billing_address']['country_iso'];
			foreach ($countries as $country) {
				$country['selected']	= ($country['iso'] == $currentIso) ? 'selected="selected"' : '';
				$smarty_data['countries'][]	= $country;
			}
		}
		$GLOBALS['smarty']->assign('COUNTRIES', $smarty_data['countries']);
		
		## Check for custom template for module in skin folder
		$file_name = 'form.tpl';
		$form_file = $GLOBALS['gui']->getCustomModuleSkin('gateway', dirname(__FILE__), $file_name);
		$GLOBALS['gui']->changeTemplateDir($form_file);
		$ret = $GLOBALS['smarty']->fetch($file_name);
		$GLOBALS['gui']->changeTemplateDir();
		return $ret;
	}
}