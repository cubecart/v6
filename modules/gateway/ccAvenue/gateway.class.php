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
			'action'	=> ($this->_module['mode']=='live') ? 'https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction' : 'https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction',
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> 'auto',
		);
		return $transfer;
	}

	public function repeatVariables() {
		return false;
	}

	private function _encRequest() {
		
		if($GLOBALS['db']->count('CubeCart_modules', 'module_id', array('module' => 'gateway', 'status' => '1')) == 1) {
			$cancel_return = 'confirm';
		} else {
			$cancel_return = 'gateway';
		}

		$billing_address = (empty($this->_basket['billing_address']['line2'])) ? $this->_basket['billing_address']['line1'] : $this->_basket['billing_address']['line1'].', '.$this->_basket['billing_address']['line2'];

		$delivery_address = (empty($this->_basket['delivery_address']['line2'])) ? $this->_basket['delivery_address']['line1'] : $this->_basket['delivery_address']['line1'].', '.$this->_basket['delivery_address']['line2'];

		$params = array(
			'merchant_id' 		=> $this->_module['merchant_id'],
			'order_id' 			=> $this->_basket['cart_order_id'],
			'currency' 			=> $GLOBALS['config']->get('config', 'default_currency'),
			'amount' 			=> $this->_basket['total'],
			'redirect_url' 		=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=ccAvenue',
			'cancel_url' 		=> $GLOBALS['storeURL'].'/index.php?_a='.$cancel_return,
			'billing_name' 		=> $this->_basket['billing_address']['first_name'].' '.$this->_basket['billing_address']['last_name'],
			'billing_address' 	=> $billing_address,
			'billing_city' 		=> $this->_basket['billing_address']['town'],
			'billing_state' 	=> $this->_basket['billing_address']['state'],
			'billing_zip' 		=> $this->_basket['billing_address']['postcode'],
			'billing_country' 	=> $this->_basket['billing_address']['country'],
			'billing_tel' 		=> $this->_basket['billing_address']['phone'],
			'billing_email' 	=> $this->_basket['billing_address']['email'],
			'delivery_name'		=> $this->_basket['delivery_address']['first_name'].' '.$this->_basket['delivery_address']['last_name'],
			'delivery_address'	=> $delivery_address,
			'delivery_city'		=> $this->_basket['delivery_address']['town'],
			'delivery_state'	=> $this->_basket['delivery_address']['state'],
			'delivery_zip'		=> $this->_basket['delivery_address']['postcode'],
			'delivery_country'	=> $this->_basket['delivery_address']['country'],
			'delivery_tel'		=> ''
		);

		$string = '';
		foreach($params as $key => $value) {
			$string .= $key."=".$value."&";
		}

		return $this->_encrypt($string, $this->_module['encryption_key']);
	}

	private function _encrypt($plainText,$key)
	{
		$secretKey = $this->_hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
	  	$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');
	  	$blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');
		$plainPad = $this->_pkcs5_pad($plainText, $blockSize);
	  	if (mcrypt_generic_init($openMode, $secretKey, $initVector) != -1) 
		{
		      $encryptedText = mcrypt_generic($openMode, $plainPad);
	      	      mcrypt_generic_deinit($openMode);
		      			
		} 
		return bin2hex($encryptedText);
	}

	private function _decrypt($encryptedText,$key)
	{
		$secretKey = $this->_hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		$encryptedText=$this->_hextobin($encryptedText);
	  	$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');
		mcrypt_generic_init($openMode, $secretKey, $initVector);
		$decryptedText = mdecrypt_generic($openMode, $encryptedText);
		$decryptedText = rtrim($decryptedText, "\0");
	 	mcrypt_generic_deinit($openMode);
		return $decryptedText;
		
	}
	//*********** Padding Function *********************

	private function _pkcs5_pad ($plainText, $blockSize)
	{
	    $pad = $blockSize - (strlen($plainText) % $blockSize);
	    return $plainText . str_repeat(chr($pad), $pad);
	}

	//********** Hexadecimal to Binary function for php 4.0 version ********

	private function _hextobin($hexString) 
   	 { 
        	$length = strlen($hexString); 
    	$binString="";   
    	$count=0; 
    	while($count<$length) 
    	{       
    	    $subString =substr($hexString,$count,2);           
    	    $packedString = pack("H*",$subString); 
    	    if ($count==0)
	    {
			$binString=$packedString;
	    } 
    	    
	    else 
	    {
			$binString.=$packedString;
	    } 
    	    
	    $count+=2; 
    	} 
	        return $binString; 
	  } 

	public function fixedVariables() {
		$hidden	= array(
			'encRequest' => $this->_encRequest(),
			'access_code'	=> $this->_module['access_code'],
			'command'	=> 'initiateTransaction'
		);

		return $hidden;
	}

	##################################################

	public function call() {
		return false;
	}

	public function process() {
		$order	= Order::getInstance();
		
		$string = $this->_decrypt($_POST['encResp'],$this->_module['encryption_key']);
		parse_str($string, $output);

		$cart_order_id		= $output['order_id'];
		$order_summary		= $order->getSummary($cart_order_id);
		
		if($output['order_status'] == 'Success') {
			$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
		} else {
			$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
		}

		$transData['notes']			= $order['status_message'];
		$transData['gateway']		= $_REQUEST['module'];
		$transData['order_id']		= $cart_order_id;
		$transData['trans_id']		= $order['tracking_id'];
		$transData['amount']		= $order['Amount'];
		$transData['status']		= $output['order_status'];
		$transData['customer_id']	= $order_summary['customer_id'];
		$transData['extra']			= '';
		$order->logTransaction($transData);

		// Redirect to _a=complete, and drop out unneeded variables
		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
	}

	public function form() {
		return false;
	}
}