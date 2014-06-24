<?php
class Gateway {
	private $_config;
	private $_module;
	private $_basket;

	private $defaultLocale;

	public function __construct($module = false, $basket = false) {
		$this->_config	=& $GLOBALS['config'];
		$this->_session	=& $GLOBALS['user'];
		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
	}

	##################################################

	public function transfer() {
		$transfer	= array(
			'action'	=> ($this->_module['testMode']) ? 'https://test-payment.hipay.com/order/' : 'https://payment.hipay.com/order/',
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

		$this->defaultLocale = $this->_module['locale'];
		$this->defaultCurrency = $this->_module['currency'];

		$hidden	= array(
			'testMode'			=> $this->_module['testMode'],
			'debugMode'			=> $this->_module['debug_mode'],
			'action'			=> ($this->_module['testMode']) ? 'https://test-payment.hipay.com/order/' : 'https://payment.hipay.com/order/',
			'account_id'		=> ($this->_module['testMode']) ? $this->_module['sandbox_account_id'] : $this->_module['live_account_id'],
			'website_id'		=> ($this->_module['testMode']) ? $this->_module['sandbox_website_id'] : $this->_module['live_website_id'],
			'website_category'	=> ($this->_module['testMode']) ? $this->_module['sandbox_website_category'] : $this->_module['live_website_category'],
			'website_password'	=> ($this->_module['testMode']) ? $this->_module['sandbox_website_password'] : $this->_module['live_website_password'],
			'shop_id'			=> ($this->_module['testMode']) ? $this->_module['sandbox_shop_id'] : $this->_module['live_shop_id'],
			'email_ack'			=> $this->_module['email_ack'],
			'currency_code'		=> $this->_getCurrency($GLOBALS['config']->get('config', 'default_currency')),
			'client_email'		=> $this->_basket['billing_address']['email'],
			'country'			=> $this->_getLocale($this->_basket['billing_address']['country_iso']),
			'age_group' 		=> $this->_getAgeGroup($this->_module['age_group']),
			'cancel_return'		=> $GLOBALS['storeURL'].'/index.php?_a=gateway',
			'code_to_encrypt'	=> sha1($this->_module['code_to_encrypt'] . $this->_basket['cart_order_id'])
		);


        $cleanXML='';
        $xml = trim($xml);
        $md5 = hash('md5',$xml);
        $cleanXML="<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
        $cleanXML.="<mapi><mapiversion>1.0</mapiversion>\n";
        $cleanXML.='<md5content>'.$md5."</md5content>\n";
        $cleanXML.='<HIPAY_MAPI_SimplePayment>
		        	<HIPAY_MAPI_PaymentParams>
				        <login>'.$hidden['account_id'].'</login>
				        <password>'.$hidden['website_password'].'</password>
				        <itemAccount>'.$hidden['account_id'].'</itemAccount>
				        <taxAccount>'.$hidden['account_id'].'</taxAccount>
				        <insuranceAccount>'.$hidden['account_id'].'</insuranceAccount>
				        <fixedCostAccount>'.$hidden['account_id'].'</fixedCostAccount>
				        <shippingCostAccount>'.$hidden['account_id'].'</shippingCostAccount>
				        <defaultLang>'.$hidden['country'].'</defaultLang>
				        <media>WEB</media>
				        <rating>'.$hidden['age_group'].'</rating>
				        <paymentMethod>0</paymentMethod>
				        <captureDay>0</captureDay>
				        <currency>'.$hidden['currency_code'].'</currency>
				        <idForMerchant>'.$this->_basket['cart_order_id'].'</idForMerchant>
				        <merchantSiteId>'.$hidden['website_id'].'</merchantSiteId>
				        <statsGroupId>0</statsGroupId>
				        <merchantDatas><_aKey_id>'.$this->_basket['cart_order_id'].'</_aKey_id><_aKey_key>'.$hidden['code_to_encrypt'].'</_aKey_key></merchantDatas>
				        <url_ok>'.$GLOBALS['storeURL'].'/index.php?_g=remote&type=gateway&cmd=process&status=Approved&module=Hipay</url_ok>
				        <url_nok>'.$GLOBALS['storeURL'].'/index.php?_g=remote&type=gateway&cmd=process&status=Declined&module=Hipay</url_nok>
				        <url_cancel>'.$GLOBALS['storeURL'].'/index.php?_g=remote&type=gateway&cmd=process&status=Canceled&module=Hipay</url_cancel>
				        <url_ack>'.$GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=call&module=Hipay</url_ack>
				        <ack_wd></ack_wd>
				        <email_ack>'.$hidden['email_ack'].'</email_ack>
				        <bg_color></bg_color>
				        <logo_url></logo_url>
				        <locale>'.$hidden['country'].'</locale>
				        <issuerAccountLogin>'.$hidden['client_email'].'</issuerAccountLogin>
				        <shopId>'.$hidden['shop_id'].'</shopId>
				        <merchantDescription></merchantDescription>
				        <informations></informations>
				        <cguChecked>0</cguChecked>
			        </HIPAY_MAPI_PaymentParams>
			        <order>
			        	<HIPAY_MAPI_Order>
				            <shippingAmount>0</shippingAmount>
				            <shippingTax></shippingTax>
				            <insuranceAmount>0</insuranceAmount>                       
				            <insuranceTax></insuranceTax>
				            <fixedCostAmount>0</fixedCostAmount>
				            <fixedCostTax></fixedCostTax>
				            <affiliate></affiliate>
				            <orderTitle>Ord: '.$this->_basket['cart_order_id'].'</orderTitle>
				            <orderInfo></orderInfo>
				            <orderCategory>273</orderCategory>
		            	</HIPAY_MAPI_Order>
		            </order>
		            <items>
		            	<HIPAY_MAPI_Product>
		            		<name>Ord: '.$this->_basket['cart_order_id'].'</name>
		            		<info></info>
		            		<quantity>1</quantity>
		            		<ref>'.$this->_basket['cart_order_id'].'</ref>
		            		<category>1</category>
		            		<price>'.$this->_basket['total'].'</price>
		            		<tax></tax>
		            	</HIPAY_MAPI_Product>
		            </items>
	            </HIPAY_MAPI_SimplePayment>
        	</mapi>';
        $xml = $cleanXML;

        if ($hidden["debugMode"] && $hidden["testMode"]) error_log("#################################\nORDER: " . $this->_basket['cart_order_id'] . "\nDATE: " . date("Y-m-d H:i:s") . "\n" . $xml."\n\n",3,"hipay.log");

        try {
            $url = $hidden["action"];
            $curl = curl_init();
            curl_setopt($curl,CURLOPT_TIMEOUT, 9);
            curl_setopt($curl,CURLOPT_POST,true);
            curl_setopt($curl,CURLOPT_USERAGENT,"HI-MEDIA");
        	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); //Do not check SSL certificate (but use SSL of course), live dangerously!
        	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); //Return the result as string
        	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 33); //Return the result as string
            curl_setopt($curl,CURLOPT_URL, $url);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$url_params.'xml='.urlencode($xml));
            curl_setopt($curl, CURLOPT_HEADER, 0);
        	$result = curl_exec($curl);
			$data = simplexml_load_string($result);	

	

			if ($data->result->status == "accepted") {
		       if ($hidden["debugMode"] && $hidden["testMode"]) error_log("################################# GOOD CALL! - ". $data->result->url . "\n\n",3,"hipay.log");
				header("location:".$data->result->url);
			}
			$cancel_return = 'gateway';
            curl_close($curl);
        } catch (Exception $e) {
	        if ($hidden["debugMode"] && $hidden["testMode"]) error_log("################################# OOPS BAD CALL!\n\n",3,"hipay.log");
			$cancel_return = 'gateway';           
        }

		return $hidden;
	}



	##################################################

	public function process() {
		$order				= Order::getInstance();
		$cart_order_id 		= $this->_basket['cart_order_id'];
		$order_summary		= $order->getSummary($cart_order_id);
		$status 			= $_GET["status"];

		if($status == "Approved") {
			//$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
			$transData['notes']			= "HIPAY: Payment Success - Waiting Capture.";
		} elseif($status == 'Declined') {
			$order->paymentStatus(Order::PAYMENT_DECLINE, $cart_order_id);
			$transData['notes']			= "HIPAY: Payment Decline.";
		} elseif($status == "Canceled") {
			$transData['notes']			= "HIPAY: Payment Canceled.";
		}
		
		$transData['order_id']		= $cart_order_id;
		$transData['amount']		= $this->_basket['total'];
		$transData['extra']			= '';
		$transData['status']		= $status;
		$transData['customer_id']	= $order_summary['customer_id'];
		$transData['gateway']		= 'Hipay ('.strtoupper($this->_module['mode']).')';
		$order->logTransaction($transData);

		if ($this->_module['debug_mode'] && $this->_module['testMode']) error_log("################################## END PROCESS - STATUS: ". $status . "\n\n",3,"hipay.log");

		if($status == "Approved") {
			httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
		}
        else {
			httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'confirm')));
		}

	}


	public function call() {

		if ($this->_module['debug_mode'] && $this->_module['testMode']) error_log("##################################  CALLBACK RECEIVED " . date('Y.m-d H:i:s') .  "\n",3,"hipay.log");
		//if (!extension_loaded('simplexml')) error_log("ATENTION NEED TO INSTALL SIMPLEXML - libapache2-mod-php5\n\n",3,"hipay.log");
		
		$xml = $_POST["xml"];
		if ($this->_module['debug_mode'] && $this->_module['testMode']) error_log($xml . $d . "\n",3,"hipay.log");
		$data = simplexml_load_string($xml);		
		$operation = strtoupper($data->result->operation);
		$status = strtoupper($data->result->status);
		$operation_date = $data->result->date . " " . $data->result->time;
		$transid = $data->result->transid;
		$cart_order_id = $data->result->idForMerchant;
		$buyer_email = $data->result->emailClient;
		$origAmount = $data->result->origAmount;

		$merchantDatas = $data->result->merchantDatas;
		$cart_order_id_2 = $merchantDatas->_aKey_id;
		$cart_key = $merchantDatas->_aKey_key;

		if ($cart_order_id != "") {

			if ($this->_module['debug_mode'] && $this->_module['testMode']) error_log("CART ID: " .  $cart_order_id . "\n\n",3,"hipay.log");

			$order				= Order::getInstance();
			$order_summary		= $order->getSummary($cart_order_id);
			$transData['notes']	= array();

			$code_to_check = sha1($this->_module['code_to_encrypt'] . $cart_order_id);

			if ($operation == "CAPTURE" && $status == "OK" && (string)$code_to_check == (string)$cart_key && (string)$cart_order_id == (string)$cart_order_id_2) {
				$transData['notes'][]	= "Payment successful. <br />Email: ".$buyer_email;
				$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
				$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
			}	
			elseif ($operation == "AUTHORIZATION" && $status == "OK" && $code_to_check == $cart_key && $cart_order_id == $cart_order_id_2) {
				$transData['notes'][]	= "Payment authorization - Waiting Capture. <br />Email: ".$buyer_email;
			}
			elseif ($operation == "CANCELLATION" && $code_to_check == $cart_key && $cart_order_id == $cart_order_id_2) {
				$transData['notes'][]	= "Request for total or partial cancellation. <br />Email: ".$buyer_email;
				$order->paymentStatus(Order::PAYMENT_CANCEL, $cart_order_id);
				$order->orderStatus(Order::ORDER_CANCELLED, $cart_order_id);
			}
			elseif ($operation == "REFUND" && $code_to_check == $cart_key && $cart_order_id == $cart_order_id_2) {
				$transData['notes'][]	= "Request for total or partial refund. <br />Email: ".$buyer_email;

				if((string)$origAmount==(string)'-'.$order_summary['total']) { // Change status to refunded if it is a full refund
					$order->paymentStatus(Order::PAYMENT_CANCEL, $cart_order_id);
					$order->orderStatus(Order::ORDER_CANCELLED, $cart_order_id);
				}
			}
			elseif ($operation == "REJECT" && $code_to_check == $cart_key && $cart_order_id == $cart_order_id_2) {
				$transData['notes'][]	= "rejected transaction after capture. <br />Email: ".$buyer_email;
				$order->paymentStatus(Order::PAYMENT_DECLINE, $cart_order_id);
				$order->orderStatus(Order::ORDER_DECLINED, $cart_order_id);				
			}
			else {
				$transData['notes'][]	= "Unspecified Error or wrong check.";
			}

			//if (isset($transid) && !empty($transid)) {
			//	$trans_id	= $GLOBALS['db']->select('CubeCart_transactions', array('id'), array('trans_id' => $transid));
			//	if ($trans_id && $operation != "AUTHORIZATION") {
			//		$transData['notes'][]	= 'This Transaction ID has been processed before.';
			//	}
			//}

			## Build the transaction log data
			$transData['gateway']		= $_GET['module'];
			$transData['order_id']		= $cart_order_id;
			$transData['trans_id']		= $transid;
			$transData['amount']		= $origAmount;
			$transData['status']		= $operation . " " . $status;
			$transData['customer_id']	= $order_summary['customer_id'];
			$transData['extra']			= "";
			$order->logTransaction($transData);
		}
		return false;
	}


	public function form() {
		return false;
	}

	private function  _getLocale($locale){
		$locale = strtoupper($locale);
		switch ($locale) {
			case 'PT':
				return "pt_PT";
				break;

			case 'US':
				return "en_US";
				break;

			case 'GB':
				return "en_GB";
				break;

			case 'FR':
				return "fr_FR";
				break;

			case 'NL':
				return "nl_NL";
				break;

			case 'ES':
				return "es_ES";
				break;

			case 'DE':
				return "de_DE";
				break;
		
			default:
				return $this->defaultLocale;
				break;
		}
	}


	private function  _getCurrency($currency){
		$currency = strtoupper($currency);
		$currencies = array("EUR", "USD", "CAD", "AUD","CHF", "SEK", "GBP"); 
		if (in_array($currency, $currencies)) return $currency;
		return $this->defaultCurrency;
	}

	private function  _getAgeGroup($age_group){
		if ($age_group != "ALL") $age_group = "+" . $age_group;
		return $age_group;
	}


}



