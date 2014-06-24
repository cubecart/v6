<?php
class Gateway {
	private $_config;
	private $_module;
	private $_basket;
	private $_url;

	public function __construct($module = false, $basket = false) {
		$this->_session	=& $GLOBALS['user'];

		$this->_module			= $module;
		$this->_basket			= $basket;
		$this->_url = "https://mms.cardsaveonlinepayments.com/Pages/PublicPages/PaymentForm.aspx";
	}

	private function simpleXor($InString, $Key) {
		$KeyList = array();
		$output = "";
		for($i = 0; $i < strlen($Key); $i++) {
			$KeyList[$i] = ord(substr($Key, $i, 1));
		}
		for($i = 0; $i < strlen($InString); $i++) {
	    	$output.= chr(ord(substr($InString, $i, 1)) ^ ($KeyList[$i % strlen($Key)]));
	  	}
	  	return $output;
	}

	private function getToken($thisString) {

		$Tokens = array(
		    "Status",
		    "StatusDetail",
		    "VendorTxCode",
		    "VPSTxId",
		    "TxAuthNo",
		    "Amount",
		    "AVSCV2",
		    "AddressResult",
		    "PostCodeResult",
		    "CV2Result",
		    "GiftAid",
		    "3DSecureStatus",
		    "CAVV" );

		$output = array();
		$resultArray = array();
		for ($i = count($Tokens)-1; $i >= 0 ; $i--) {
			$start = strpos($thisString, $Tokens[$i]);
	    	if ($start !== false) {
	      		$resultArray[$i]->start = $start;
	      		$resultArray[$i]->token = $Tokens[$i];
	    	}
	    }

		sort($resultArray);
	  	for ($i = 0; $i<count($resultArray); $i++) {
	  		$valueStart = $resultArray[$i]->start + strlen($resultArray[$i]->token) + 1;
			if ($i==(count($resultArray)-1)) {
	    		$output[$resultArray[$i]->token] = substr($thisString, $valueStart);
	    	} else {
	    		$valueLength = $resultArray[$i+1]->start - $resultArray[$i]->start - strlen($resultArray[$i]->token) - 2;
				$output[$resultArray[$i]->token] = substr($thisString, $valueStart, $valueLength);
	    	}
	 	}
	  return $output;
	}

	public function transfer() {

		$transfer	= array(
			'action'	=> $this->_url,
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
		
		$suppcurr = array(
			'USD' => '840',
			'EUR' => '978',
			'CAD' => '124',
			'JPY' => '392',
			'GBP' => '826',
			'AUD' => '036',
		);
		
		$countriesArray = array(
			'AL' => '8',
			'DZ' => '12',
			'AS' => '16',
			'AD' => '20',
			'AO' => '24',
			'AI' => '660',
			'AG' => '28',
			'AR' => '32',
			'AM' => '51',
			'AW' => '533',
			'AU' => '36',
			'AT' => '40',
			'AZ' => '31',
			'BS' => '44',
			'BH' => '48',
			'BD' => '50',
			'BB' => '52',
			'BY' => '112',
			'BE' => '56',
			'BZ' => '84',
			'BJ' => '204',
			'BM' => '60',
			'BT' => '64',
			'BO' => '68',
			'BA' => '70',
			'BW' => '72',
			'BR' => '76',
			'BN' => '96',
			'BG' => '100',
			'BF' => '854',
			'BI' => '108',
			'KH' => '116',
			'CM' => '120',
			'CA' => '124',
			'CV' => '132',
			'KY' => '136',
			'CF' => '140',
			'TD' => '148',
			'CL' => '152',
			'CN' => '156',
			'CO' => '170',
			'KM' => '174',
			'CG' => '178',
			'CD' => '180',
			'CK' => '184',
			'CR' => '188',
			'CI' => '384',
			'HR' => '191',
			'CU' => '192',
			'CY' => '196',
			'CZ' => '203',
			'DK' => '208',
			'DJ' => '262',
			'DM' => '212',
			'DO' => '214',
			'EC' => '218',
			'EG' => '818',
			'SV' => '222',
			'GQ' => '226',
			'ER' => '232',
			'EE' => '233',
			'ET' => '231',
			'FK' => '238',
			'FO' => '234',
			'FJ' => '242',
			'FI' => '246',
			'FR' => '250',
			'GF' => '254',
			'PF' => '258',
			'GA' => '266',
			'GM' => '270',
			'GE' => '268',
			'DE' => '276',
			'GH' => '288',
			'GI' => '292',
			'GR' => '300',
			'GL' => '304',
			'GD' => '308',
			'GP' => '312',
			'GU' => '316',
			'GT' => '320',
			'GN' => '324',
			'GW' => '624',
			'GY' => '328',
			'HT' => '332',
			'VA' => '336',
			'HN' => '340',
			'HK' => '344',
			'HU' => '348',
			'IS' => '352',
			'IN' => '356',
			'ID' => '360',
			'IR' => '364',
			'IQ' => '368',
			'IE' => '372',
			'IL' => '376',
			'IT' => '380',
			'JM' => '388',
			'JP' => '392',
			'JO' => '400',
			'KZ' => '398',
			'KE' => '404',
			'KI' => '296',
			'KP' => '408',
			'KR' => '410',
			'KW' => '414',
			'KG' => '417',
			'LA' => '418',
			'LV' => '428',
			'LB' => '422',
			'LS' => '426',
			'LR' => '430',
			'LY' => '434',
			'LI' => '438',
			'LT' => '440',
			'LU' => '442',
			'MO' => '446',
			'MK' => '807',
			'MG' => '450',
			'MW' => '454',
			'MY' => '458',
			'MV' => '462',
			'ML' => '466',
			'MT' => '470',
			'MH' => '584',
			'MQ' => '474',
			'MR' => '478',
			'MU' => '480',
			'MX' => '484',
			'FM' => '583',
			'MD' => '498',
			'MC' => '492',
			'MN' => '496',
			'MS' => '500',
			'MA' => '504',
			'MZ' => '508',
			'MM' => '104',
			'NA' => '516',
			'NR' => '520',
			'NP' => '524',
			'NL' => '528',
			'AN' => '530',
			'NC' => '540',
			'NZ' => '554',
			'NI' => '558',
			'NE' => '562',
			'NG' => '566',
			'NU' => '570',
			'NF' => '574',
			'MP' => '580',
			'NO' => '578',
			'OM' => '512',
			'PK' => '586',
			'PW' => '585',
			'PA' => '591',
			'PG' => '598',
			'PY' => '600',
			'PE' => '604',
			'PH' => '608',
			'PN' => '612',
			'PL' => '616',
			'PT' => '620',
			'PR' => '630',
			'QA' => '634',
			'RE' => '638',
			'RO' => '642',
			'RU' => '643',
			'RW' => '646',
			'SH' => '654',
			'KN' => '659',
			'LC' => '662',
			'PM' => '666',
			'VC' => '670',
			'WS' => '882',
			'SM' => '674',
			'ST' => '678',
			'SA' => '682',
			'SN' => '686',
			'SC' => '690',
			'SL' => '694',
			'SG' => '702',
			'SK' => '703',
			'SI' => '705',
			'SB' => '90',
			'SO' => '706',
			'ZA' => '710',
			'ES' => '724',
			'LK' => '144',
			'SD' => '736',
			'SR' => '740',
			'SJ' => '744',
			'SZ' => '748',
			'SE' => '752',
			'CH' => '756',
			'SY' => '760',
			'TW' => '158',
			'TJ' => '762',
			'TZ' => '834',
			'TH' => '764',
			'TG' => '768',
			'TK' => '772',
			'TO' => '776',
			'TT' => '780',
			'TN' => '788',
			'TR' => '792',
			'TM' => '795',
			'TC' => '796',
			'TV' => '798',
			'UG' => '800',
			'UA' => '804',
			'AE' => '784',
			'GB' => '826',
			'US' => '840',
			'UY' => '858',
			'UZ' => '860',
			'VU' => '548',
			'VE' => '862',
			'VN' => '704',
			'VG' => '92',
			'VI' => '850',
			'WF' => '876',
			'EH' => '732',
			'YE' => '887',
			'ZM' => '894',
			'ZW' => '716'
		);
		
		if (in_array($GLOBALS['config']->get('config', 'default_currency'),array_keys($suppcurr))) {
			$currency = $suppcurr[$GLOBALS['config']->get('config', 'default_currency')];
		} else {
			$currency = 'GBP';
		}
		
		if (in_array($this->_basket['billing_address']['country_iso'],array_keys($countriesArray))) {
			$countryISO = $countriesArray[$this->_basket['billing_address']['country_iso']];
		} else {
			$countryISO = "";
		}

		if ($this->_module['CV2Mandatory'] == 1) { $CV2Mandatory .= "true"; } else { $CV2Mandatory .= "false"; }
		if ($this->_module['Address1Mandatory'] == 1) { $Address1Mandatory .= "true"; } else { $Address1Mandatory .= "false"; }
		if ($this->_module['CityMandatory'] == 1) { $CityMandatory .= "true"; } else { $CityMandatory .= "false"; }
		if ($this->_module['PostCodeMandatory'] == 1) { $PostCodeMandatory .= "true"; } else { $PostCodeMandatory .= "false"; }
		if ($this->_module['StateMandatory'] == 1) { $StateMandatory .= "true"; } else { $StateMandatory .= "false"; }
		if ($this->_module['CountryMandatory'] == 1) { $CountryMandatory .= "true"; } else { $CountryMandatory .= "false"; }

		$cryptVars = "PreSharedKey=".$this->_module['merchantPSK'];
		$cryptVars .= "&MerchantID=".$this->_module['merchantID'];
		$cryptVars .= "&Password=".$this->_module['merchantPass'];
		$cryptVars .= "&Amount=".$this->_basket['total']*100;
		$cryptVars .= "&CurrencyCode=".$currency;
		$cryptVars .= "&OrderID=".$this->_basket['cart_order_id'];
		$cryptVars .= "&TransactionType=".$this->_module['charge_type'];
		$cryptVars .= "&TransactionDateTime=".date('Y-m-d H:i:s P');
		$cryptVars .= "&CallbackURL=".$GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=CardSave_Redirect&cart_order_id='.$this->_basket['cart_order_id'];
		$cryptVars .= "&OrderDescription=";
		$cryptVars .= "&CustomerName=".$this->_basket['billing_address']['first_name']." ".$this->_basket['billing_address']['last_name'];
		$cryptVars .= "&Address1=".$this->_basket['billing_address']['line1'];
		$cryptVars .= "&Address2=".$this->_basket['billing_address']['line2'];
		$cryptVars .= "&Address3=";
		$cryptVars .= "&Address4=";
		$cryptVars .= "&City=".$this->_basket['billing_address']['town'];
		$cryptVars .= "&State=".$this->_basket['billing_address']['state'];
		$cryptVars .= "&PostCode=".$this->_basket['billing_address']['postcode'];
		$cryptVars .= "&CountryCode=".$countryISO;
		$cryptVars .= "&CV2Mandatory=".$CV2Mandatory;		
		$cryptVars .= "&Address1Mandatory=".$Address1Mandatory;		
		$cryptVars .= "&CityMandatory=".$CityMandatory;
		$cryptVars .= "&PostCodeMandatory=".$PostCodeMandatory;		
		$cryptVars .= "&StateMandatory=".$StateMandatory;		
		$cryptVars .= "&CountryMandatory=".$CountryMandatory;		
		$cryptVars .= "&ResultDeliveryMethod=SERVER";
		$cryptVars .= "&ServerResultURL=".$GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=call&module=CardSave_Redirect&cart_order_id='.$this->_basket['cart_order_id'];
		$cryptVars .= "&PaymentFormDisplaysResult=FALSE";
		$cryptVars .= "&ServerResultURLCookieVariables=";
		$cryptVars .= "&ServerResultURLFormVariables=";
		$cryptVars .= "&ServerResultURLQueryStringVariables=";
		
		//echo $cryptVars . "<br><br>";

		$hidden	= 	array(
						'HashDigest' => sha1($cryptVars),
						'MerchantID' => $this->_module['merchantID'],	
						'Amount' => $this->_basket['total']*100,
						'CurrencyCode' => $currency,
						'OrderID' => $this->_basket['cart_order_id'],
						'TransactionType' => $this->_module['charge_type'],
						'TransactionDateTime' => date('Y-m-d H:i:s P'),	
						'CallbackURL' => $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=CardSave_Redirect&cart_order_id='.$this->_basket['cart_order_id'],
						'OrderDescription' => "",
						'CustomerName' => $this->_basket['billing_address']['first_name']." ".$this->_basket['billing_address']['last_name'],
						'Address1' => $this->_basket['billing_address']['line1'],
						'Address2' => $this->_basket['billing_address']['line2'],	
						'Address3' => "",
						'Address4' => "",
						'City' => $this->_basket['billing_address']['town'],
						'State' => $this->_basket['billing_address']['state'],
						'PostCode' => $this->_basket['billing_address']['postcode'],	
						'CountryCode' => $countryISO,
						'CV2Mandatory' => $CV2Mandatory,
						'Address1Mandatory' => $Address1Mandatory,
						'CityMandatory' => $CityMandatory,
						'PostCodeMandatory' => $PostCodeMandatory,	
						'StateMandatory' => $StateMandatory,
						'CountryMandatory' => $CountryMandatory,
						'ResultDeliveryMethod' => "SERVER",
						'ServerResultURL' => $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=call&module=CardSave_Redirect&cart_order_id='.$this->_basket['cart_order_id'],
						'PaymentFormDisplaysResult' => "FALSE",
						'ServerResultURLCookieVariables' => "",
						'ServerResultURLFormVariables' => "",
						'ServerResultURLQueryStringVariables' => ""									
					);
		return $hidden;
	}

	##################################################

	public function call() {
		function createhash($PreSharedKey,$Password) { 
			$str="PreSharedKey=" . $PreSharedKey;
			$str=$str . '&MerchantID=' . $_POST["MerchantID"];
			$str=$str . '&Password=' . $Password;
			$str=$str . '&StatusCode=' . $_POST["StatusCode"];
			$str=$str . '&Message=' . $_POST["Message"];
			$str=$str . '&PreviousStatusCode=' . $_POST["PreviousStatusCode"];
			$str=$str . '&PreviousMessage=' . $_POST["PreviousMessage"];
			$str=$str . '&CrossReference=' . $_POST["CrossReference"];
			$str=$str . '&Amount=' . $_POST["Amount"];
			$str=$str . '&CurrencyCode=' . $_POST["CurrencyCode"];
			$str=$str . '&OrderID=' . $_POST["OrderID"];
			$str=$str . '&TransactionType=' . $_POST["TransactionType"];
			$str=$str . '&TransactionDateTime=' . $_POST["TransactionDateTime"];
			$str=$str . '&OrderDescription=' . $_POST["OrderDescription"];
			$str=$str . '&CustomerName=' . $_POST["CustomerName"];
			$str=$str . '&Address1=' . $_POST["Address1"];
			$str=$str . '&Address2=' . $_POST["Address2"];
			$str=$str . '&Address3=' . $_POST["Address3"];
			$str=$str . '&Address4=' . $_POST["Address4"];
			$str=$str . '&City=' . $_POST["City"];
			$str=$str . '&State=' . $_POST["State"];
			$str=$str . '&PostCode=' . $_POST["PostCode"];
			$str=$str . '&CountryCode=' . $_POST["CountryCode"];
			return sha1($str);
		}
		
		// String together other strings using a "," as a seperator.
		function addStringToStringList($szExistingStringList, $szStringToAdd)
		{
			$szReturnString = "";
			$szCommaString = "";
		
			if (strlen($szStringToAdd) == 0)
			{
				$szReturnString = $szExistingStringList;
			}
			else
			{
				if (strlen($szExistingStringList) != 0)
				{
					$szCommaString = ", ";
				}
				$szReturnString = $szExistingStringList.$szCommaString.$szStringToAdd;
			}
		
			return ($szReturnString);
		}
		
		$szHashDigest = "";
		$szOutputMessage = "";
		$boErrorOccurred = false;
		$nStatusCode = 30;
		$szMessage = "";
		$nPreviousStatusCode = 0;
		$szPreviousMessage = "";
		$szCrossReference = "";
		$nAmount = 0;
		$nCurrencyCode = 0;
		$szOrderID = "";
		$szTransactionType= "";
		$szTransactionDateTime = "";
		$szOrderDescription = "";
		$szCustomerName = "";
		$szAddress1 = "";
		$szAddress2 = "";
		$szAddress3 = "";
		$szAddress4 = "";
		$szCity = "";
		$szState = "";
		$szPostCode = "";
		$nCountryCode = "";
		
		try
			{
				// hash digest
				if (isset($_POST["HashDigest"]))
				{
					$szHashDigest = $_POST["HashDigest"];
				}
		
				// transaction status code
				if (!isset($_POST["StatusCode"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [StatusCode] not received");
					$boErrorOccurred = true;
				}
				else
				{
					if ($_POST["StatusCode"] == "")
					{
						$nStatusCode = null;
					}
					else
					{
						$nStatusCode = intval($_POST["StatusCode"]);
					}
				}
				// transaction message
				if (!isset($_POST["Message"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Message] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szMessage = $_POST["Message"];
				}
				// status code of original transaction if this transaction was deemed a duplicate
				if (!isset($_POST["PreviousStatusCode"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [PreviousStatusCode] not received");
					$boErrorOccurred = true;
				}
				else
				{
					if ($_POST["PreviousStatusCode"] == "")
					{
						$nPreviousStatusCode = null;
					}
					else
					{
						$nPreviousStatusCode = intval($_POST["PreviousStatusCode"]);
					}
				}
				// status code of original transaction if this transaction was deemed a duplicate
				if (!isset($_POST["PreviousMessage"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [PreviousMessage] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szPreviousMessage = $_POST["PreviousMessage"];
				}
				// cross reference of transaction
				if (!isset($_POST["CrossReference"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [CrossReference] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szCrossReference = $_POST["CrossReference"];
				}
				// amount (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["Amount"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Amount] not received");
					$boErrorOccurred = true;
				}
				else
				{
					if ($_POST["Amount"] == null)
					{
						$nAmount = null;
					}
					else
					{
						$nAmount = intval($_POST["Amount"]);
					}
				}
				// currency code (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["CurrencyCode"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [CurrencyCode] not received");
					$boErrorOccurred = true;
				}
				else
				{
					if ($_POST["CurrencyCode"] == null)
					{
						$nCurrencyCode = null;
					}
					else
					{
						$nCurrencyCode = intval($_POST["CurrencyCode"]);
					}
				}
				// order ID (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["OrderID"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [OrderID] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szOrderID = $_POST["OrderID"];
				}
				// transaction type (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["TransactionType"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [TransactionType] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szTransactionType = $_POST["TransactionType"];
				}
				// transaction date/time (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["TransactionDateTime"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [TransactionDateTime] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szTransactionDateTime = $_POST["TransactionDateTime"];
				}
				// order description (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["OrderDescription"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [OrderDescription] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szOrderDescription = $_POST["OrderDescription"];
				}
				// customer name (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["CustomerName"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [CustomerName] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szCustomerName = $_POST["CustomerName"];
				}
				// address1 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["Address1"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Address1] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szAddress1 = $_POST["Address1"];
				}
				// address2 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["Address2"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Address2] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szAddress2 = $_POST["Address2"];
				}
				// address3 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["Address3"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Address3] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szAddress3 = $_POST["Address3"];
				}
				// address4 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["Address4"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Address4] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szAddress4 = $_POST["Address4"];
				}
				// city (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["City"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [City] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szCity = $_POST["City"];
				}
				// state (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["State"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [State] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szState = $_POST["State"];
				}
				// post code (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["PostCode"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [PostCode] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szPostCode = $_POST["PostCode"];
				}
				// country code (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["CountryCode"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [CountryCode] not received");
					$boErrorOccurred = true;
				}
				else
				{
					if ($_POST["CountryCode"] == "")
					{
						$nCountryCode = null;
					}
					else
					{
						$nCountryCode = intval($_POST["CountryCode"]);
					}
				}
			}
		catch (Exception $e)
		{
			$boErrorOccurred = true;
			$szOutputMessage = "Error";
			if (isset($_POST["Message"]))
			{
				$szOutputMessage = $_POST["Message"];
			}
		}
		
		// The nOutputProcessedOK should return 0 except if there has been an error talking to the gateway or updating the website order system.
		// Any other process status shown to the gateway will prompt the gateway to send an email to the merchant stating the error.
		// The customer will also be shown a message on the hosted payment form detailing the error and will not return to the merchants website.
		$nOutputProcessedOK = 0;
		
		if (is_null($nStatusCode))
		{
		$nOutputProcessedOK = 30;		
		}
		
		if ($boErrorOccurred == true)
		{
		$nOutputProcessedOK = 30;
		}
		
		// Check the passed HashDigest against our own to check the values passed are legitimate.
		$str1 = $_POST["HashDigest"];
		$hashcode = createhash($this->_module['merchantPSK'],$this->_module['merchantPass']);
		if ($hashcode != $str1) {
		$nOutputProcessedOK = 30; 
		$szOutputMessage = "Hashes did not match";
		} 
		
		// *********************************************************************************************************
		// You should put your code that does any post transaction tasks
		// (e.g. updates the order object, sends the customer an email etc) in this section
		// *********************************************************************************************************
		if ($nOutputProcessedOK != 30)
		{	
			$nOutputProcessedOK = 0;
			$szOutputMessage = $szMessage;
			try
			{
				switch ($nStatusCode)
				{
					// transaction authorised
					case 0:
						$transauthorised = true;
						break;
					// card referred (treat as decline)
					case 4:
						$transauthorised = false;
						break;
					// transaction declined
					case 5:
						$transauthorised = false;
						break;
					// duplicate transaction
					case 20:
						// need to look at the previous status code to see if the
						// transaction was successful
						if ($nPreviousStatusCode == 0)
						{
							// transaction authorised
							$transauthorised = true;
						}
						else
						{
							// transaction not authorised
							$transauthorised = false;
						}
						break;
					// error occurred
					case 30:
						$transauthorised = false;
						break;
					default:
						$transauthorised = false;
						break;
				}
				
				$cart_order_id = sanitizeVar($_GET['cart_order_id']); // Used in remote.php $cart_order_id is important for failed orders
				$order				= Order::getInstance();
				$order_summary		= $order->getSummary($cart_order_id);
		
				$transData['customer_id'] 	= $order_summary["customer_id"];
				$transData['gateway'] 		= "CardSave_Redirect";
				$transData['trans_id'] 		= $szCrossReference;
				$transData['amount'] 		= round($nAmount/100,2);
				$transData['order_id']		= $cart_order_id;
			
				if ($transauthorised == true) {
					// put code here to update/store the order with the a successful transaction result
					$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
					$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
					$transData['notes'] = $szMessage;
					$transData['status'] = "Successful";
					$order->logTransaction($transData);					
				} else {
					// put code here to update/store the order with the a failed transaction result
					$transData['notes'] = $szMessage;
					$transData['status'] = "Failed";
					$order->logTransaction($transData);
				}
			}
			catch (Exception $e)
			{
				$nOutputProcessedOK = 30;
				$szOutputMessage = "Error updating website system, please ask the developer to check code";
			}
		}
		
		if ($nOutputProcessedOK != 0 && $szOutputMessage == "")
		{
		$szOutputMessage = "Unknown error";
		}	
			
		// output the status code and message letting the payment form
		// know whether the transaction result was processed successfully
		echo("StatusCode=".$nOutputProcessedOK."&Message=".$szOutputMessage);
		
		return false;
	}

	public function process() {
		$cart_order_id = sanitizeVar($_GET['cart_order_id']);
		$order			= Order::getInstance();
		$order_summary	= $order->getSummary($cart_order_id);
		
		if (($transactions = $GLOBALS['db']->select('CubeCart_transactions', false, array('order_id' => $cart_order_id), array('time' => 'DESC'))) !== false) {
			$crossref = sanitizeVar($_GET['CrossReference']);
			$transfound = false;
			$i = 0;
			
			while ($i < count($transactions) && !$transfound) {				
				if ($transactions[$i]['trans_id'] == $crossref) {
					$transfound = true;
					$result_message = $transactions[$i]['notes'];
				} else {
					$i++;	
				}
			}			
		}
		
		if ($order_summary['status'] == Order::ORDER_PROCESS) {
			httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
		} else {
			$GLOBALS['gui']->setError($result_message);
			httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'confirm')));
		}
	}

	public function form() {
		return false;
	}
}