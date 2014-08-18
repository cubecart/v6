<?php
class Gateway {
	private $_config;
	private $_module;
	private $_basket;
	private $_vendorTxCode;
	private $_url;
	private $_encryption = 'AES'; // can be XOR

	public function __construct($module = false, $basket = false) {
		$this->_session	=& $GLOBALS['user'];

		$this->_module			= $module;
		$this->_basket			= $basket;
		$this->_vendorTxCode 	= md5($this->_randomPass(3).time().rand(0,32000)).$this->_randomPass(8);
		$this->_encryption		= ($this->_module['encryption'] == 'XOR') ? 'XOR' : 'AES';
		
		if($this->_module['gate'] == "sim") {
			$this->_url = "https://test.sagepay.com/Simulator/VSPFormGateway.asp";
		} elseif($this->_module['gate'] == "test") {
			$this->_url = "https://test.sagepay.com/gateway/service/vspform-register.vsp";
		} elseif($this->_module['gate'] == "live"){
			$this->_url = "https://live.sagepay.com/gateway/service/vspform-register.vsp";
		} 
	}
	
	private function _randomPass($max = 8) {
		$chars = array("a","A","b","B","c","C","d","D","e","E","f","F","g","G","h","H","i","I","j","J", "k","K","l","L","m","M","n","N","o","O","p","P","q","Q","r","R","s","S","t","T", "u","U","v","V","w","W","x","X","y","Y","z","Z","1","2","3","4","5","6","7","8","9","0");
		
		$max_chars = count($chars) - 1;
		srand((double)microtime()*1000000);
		for ($i = 0; $i < $max; $i++) {
			$newPass = ($i == 0) ? $chars[rand(0, $max_chars)] : $newPass . $chars[rand(0, $max_chars)];
		}
		return $newPass;
	}
	
	private function _encryptAndEncode($strIn) {
		//** AES encryption, CBC blocking with PKCS5 padding then HEX encoding - DEFAULT **
	    	
    	//** add PKCS5 padding to the text to be encypted
    	$strIn = $this->_addPKCS5Padding($strIn);

    	//** perform encryption with PHP's MCRYPT module
		$strCrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->_module['passphrase'], $strIn, MCRYPT_MODE_CBC, $this->_module['passphrase']);
		
		//** perform hex encoding and return
		return "@" . bin2hex($strCrypt);
	}
	
	
	//** Wrapper function do decode then decrypt based on header of the encrypted field **
	private function _decodeAndDecrypt($strIn) {
		//** HEX decoding then AES decryption, CBC blocking with PKCS5 padding - DEFAULT **
		
    	//** remove the first char which is @ to flag this is AES encrypted
    	$strIn = substr($strIn,1); 
    	
    	//** HEX decoding
    	$strIn = pack('H*', $strIn);
    	
    	//** perform decryption with PHP's MCRYPT module
		return $this->_removePKCS5Padding(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->_module['passphrase'], $strIn, MCRYPT_MODE_CBC, $this->_module['passphrase'])); 
	}
	
	private function _removePKCS5Padding($decrypted) {
		$padChar = ord($decrypted[strlen($decrypted) - 1]);
	    return substr($decrypted, 0, -$padChar); 
	}
	
	//** PHP's mcrypt does not have built in PKCS5 Padding, so we use this
	private function _addPKCS5Padding($input) {
	   $blocksize = 16;
	   $padding = "";
	
	   // Pad input to an even block size boundary
	   $padlength = $blocksize - (strlen($input) % $blocksize);
	   for($i = 1; $i <= $padlength; $i++) {
	      $padding .= chr($padlength);
	   }
	   
	   return $input . $padding;
	}
	

	private function getToken($thisString) {

		// List the possible tokens
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
		    "CAVV",
			"AddressStatus",
			"CardType",
			"Last4Digits",
			"PayerStatus");
		
		  // Initialise arrays
		  $output = array();
		  $resultArray = array();
		  
		  // Get the next token in the sequence
		  for ($i = count($Tokens)-1; $i >= 0 ; $i--){
		    // Find the position in the string
		    $start = strpos($thisString, $Tokens[$i]);
			// If it's present
		    if ($start !== false){
		      // Record position and token name
		      $resultArray[$i]->start = $start;
		      $resultArray[$i]->token = $Tokens[$i];
		    }
		  }
		  
		  // Sort in order of position
		  sort($resultArray);
			// Go through the result array, getting the token values
		  for ($i = 0; $i<count($resultArray); $i++){
		    // Get the start point of the value
		    $valueStart = $resultArray[$i]->start + strlen($resultArray[$i]->token) + 1;
			// Get the length of the value
		    if ($i==(count($resultArray)-1)) {
		      $output[$resultArray[$i]->token] = substr($thisString, $valueStart);
		    } else {
		      $valueLength = $resultArray[$i+1]->start - $resultArray[$i]->start - strlen($resultArray[$i]->token) - 2;
			  $output[$resultArray[$i]->token] = substr($thisString, $valueStart, $valueLength);
		    }      
		
		  }
		
		  // Return the ouput array
		  return $output;
	}

	private function _ci($strRawText) {
	 	$strAllowableChars = "";
	    $blnAllowAccentedChars = FALSE;
	    $strCleaned = "";
	    
	    $strAllowableChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789 .,'/\\{}@():?-_&£$=%~*+\"\n\r";
	    $strCleaned = $this->_ci2($strRawText, $strAllowableChars, TRUE);
	    
	    return $strCleaned;
	}
	
	private function _ci2($strRawText, $strAllowableChars, $blnAllowAccentedChars) {
	    $iCharPos = 0;
	    $chrThisChar = "";
	    $strCleanedText = "";
	    
	    
	    //Compare each character based on list of acceptable characters
	    while ($iCharPos < strlen($strRawText))
	    {
	        // Only include valid characters **
	        $chrThisChar = substr($strRawText, $iCharPos, 1);
	        if (strpos($strAllowableChars, $chrThisChar) !== FALSE)
	        {
	            $strCleanedText = $strCleanedText . $chrThisChar;
	        }
	        elseIf ($blnAllowAccentedChars == TRUE)
	        {
	            // Allow accented characters and most high order bit chars which are harmless **
	            if (ord($chrThisChar) >= 191)
	            {
	            	$strCleanedText = $strCleanedText . $chrThisChar;
	            }
	        }
	        
	        $iCharPos = $iCharPos + 1;
	    }
	    
	    return $strCleanedText;
	}
	
	public function transfer() {

		$transfer	= array(
			'action'	=> $this->_url,
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> ($this->_module['iframe']) ? 'iframe' : 'auto',
		);
		return $transfer;
	}

	public function repeatVariables() {
		return false;
	}
	
	public function iframeURL() {
		$repeat_vars 	= $this->repeatVariables();
		if(is_array($repeat_vars)) {
			$request_vars = array_merge($this->fixedVariables(),$this->repeatVariables());
		} else {
			$request_vars = $this->fixedVariables();
		}
		return ($request_vars) ? $this->_url.'?'.http_build_query($request_vars, '', '&') : false;	
	}

	public function fixedVariables() {

		
		$cryptVars = "VendorTxCode=".$this->_vendorTxCode
			."&Amount="				.$this->_basket['total']
			."&Currency="			.$GLOBALS['config']->get('config', 'default_currency')
			."&Description="		."Cart - ".$this->_basket['cart_order_id']
			."&ApplyAVSCV2="		."0"
			."&Apply3DSecure="		."0"
			."&SuccessURL="			.$GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=SagePay&cart_order_id='.$this->_basket['cart_order_id']
			."&FailureURL="			.$GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=SagePay&cart_order_id='.$this->_basket['cart_order_id']
			."&CustomerEmail="		.$this->_ci($this->_basket['billing_address']['email'], CLEAN_INPUT_FILTER_TEXT)
			."&CustomerName="		.$this->_ci($this->_basket['billing_address']['first_name']." ".$this->_basket['billing_address']['last_name'], CLEAN_INPUT_FILTER_TEXT)
			."&BillingSurname="		.$this->_ci($this->_basket['billing_address']['last_name'], CLEAN_INPUT_FILTER_TEXT)
			."&BillingFirstnames="	.$this->_ci($this->_basket['billing_address']['first_name'], CLEAN_INPUT_FILTER_TEXT)
			."&BillingAddress1="	.$this->_ci($this->_basket['billing_address']['line1'], CLEAN_INPUT_FILTER_TEXT)
			."&BillingAddress2="	.$this->_ci($this->_basket['billing_address']['line2'], CLEAN_INPUT_FILTER_TEXT)
			."&BillingCity="		.$this->_ci($this->_basket['billing_address']['town'], CLEAN_INPUT_FILTER_TEXT)
			."&BillingCountry="		.$this->_ci($this->_basket['billing_address']['country_iso'], CLEAN_INPUT_FILTER_TEXT)
			."&BillingPostCode="	.$this->_ci($this->_basket['delivery_address']['postcode'], CLEAN_INPUT_FILTER_TEXT)
			."&BillingPhone="		.$this->_ci($this->_basket['billing_address']['phone'], CLEAN_INPUT_FILTER_TEXT)
			."&DeliverySurname="	.$this->_ci($this->_basket['delivery_address']['last_name'], CLEAN_INPUT_FILTER_TEXT)
			."&DeliveryFirstnames="	.$this->_ci($this->_basket['delivery_address']['first_name'], CLEAN_INPUT_FILTER_TEXT)
			."&DeliveryAddress1="	.$this->_ci($this->_basket['delivery_address']['line1'], CLEAN_INPUT_FILTER_TEXT)
			."&DeliveryAddress2="	.$this->_ci($this->_basket['delivery_address']['line2'], CLEAN_INPUT_FILTER_TEXT)
			."&DeliveryCity="		.$this->_ci($this->_basket['delivery_address']['town'], CLEAN_INPUT_FILTER_TEXT)
			."&DeliveryPostCode="	.$this->_ci($this->_basket['delivery_address']['postcode'], CLEAN_INPUT_FILTER_TEXT)
			."&DeliveryCountry="	.$this->_ci($this->_basket['delivery_address']['country_iso'], CLEAN_INPUT_FILTER_TEXT)
			."&DeliveryPhone="		.$this->_ci($this->_basket['delivery_address']['phone'], CLEAN_INPUT_FILTER_TEXT)
			."&Basket="				.''
			."&AllowGiftAid="		.'0'
			."&SendEMail="			.'1'
			."&VendorEMail="		.$this->_module['VendorEMail']
			."&ReferrerID="			."32839EA8-8935-49A4-95FB-369E755B632C";


		if($this->_basket['delivery_address']['country_iso']=="US") {
			if(strlen($this->_basket['billing_address']['state_abbrev']) > 2) {
				$this->_basket['billing_address']['state_abbrev'] = getStateFormat($this->_basket['billing_address']['state_abbrev'], 'name', 'abbrev');
			}
			$cryptVars .= "&BillingState=" .$this->_ci($this->_basket['billing_address']['state_abbrev'], CLEAN_INPUT_FILTER_TEXT);
		}
		if($this->_basket['delivery_address']['country_iso']=="US") {
			if(strlen($this->_basket['delivery_address']['state_abbrev']) > 2) {
				$this->_basket['delivery_address']['state_abbrev'] = getStateFormat($this->_basket['delivery_address']['state_abbrev'], 'name', 'abbrev');
			}
			$cryptVars .= "&DeliveryState" .$this->_ci($this->_basket['delivery_address']['state_abbrev'],CLEAN_INPUT_FILTER_TEXT);
		}

		$TxType	= empty($this->_module['TxType']) ? 'PAYMENT' : $this->_module['TxType'];

		$hidden	= 	array(
			'VPSProtocol' 	=> '3.00',
			'TxType' 		=> $TxType,
			'Vendor'		=> $this->_module['acNo'],
			'Crypt'			=> $this->_encryptAndEncode($cryptVars),
			'BillingAgreement' => 0
		);
		return $hidden;
	}

	##################################################

	public function call() {
		return false;
	}

	public function process() {

		$Decoded 			= $this->_decodeAndDecrypt($_REQUEST['crypt']);
		$values 			= $this->getToken($Decoded);
		$cart_order_id 		= sanitizeVar($_GET['cart_order_id']); // Used in remote.php $cart_order_id is important for failed orders

		$order				= Order::getInstance();
		$order_summary		= $order->getSummary($cart_order_id);

		$transData['customer_id'] 	= $order_summary["customer_id"];
		$transData['gateway'] 		= "SagePay";
		$transData['trans_id'] 		= $values["VendorTxCode"];
		$transData['amount'] 		= $order_summary['total'];
		$transData['status'] 		= $values['Status'];
		$transData['order_id']		= $cart_order_id;

		$transData['notes'] = '';
		
		if(!empty($values['StatusDetail']))		$transData['notes'] .= "StatusDetail: ".$values['StatusDetail'].'<br>';
		if(!empty($values['ExpiryDate'])) 		$transData['notes'] .= "ExpiryDate: ".$values['ExpiryDate'].'<br>';
		if(!empty($values['BankAuthCode'])) 	$transData['notes'] .= "BankAuthCode: ".$values['BankAuthCode'].'<br>';
		if(!empty($values['DeclineCode'])) 		$transData['notes'] .= "DeclineCode: ".$values['DeclineCode'].'<br>';
		if(!empty($values['FraudResponse'])) 	$transData['notes'] .= "FraudResponse: ".$values['FraudResponse'].'<br>';
		if(!empty($values['Surcharge'])) 		$transData['notes'] .= "Surcharge: ".$values['Surcharge'].'<br>';
		if(!empty($values['AddressStatus'])) 	$transData['notes'] .= "AddressStatus: ".$values['AddressStatus'].'<br>';
		if(!empty($values['PayerStatus'])) 		$transData['notes'] .= "PayerStatus: ".$values['PayerStatus'];

		if($values['Status']=="OK"){
			$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
		} else {
			$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
		}
		$order->logTransaction($transData);
		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
		return false;
	}

	public function form() {
		return false;
	}
}