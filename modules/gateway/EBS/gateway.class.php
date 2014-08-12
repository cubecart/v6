<?php
ini_set('display_errors',1);
error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
class Gateway {
	private $_config;
	private $_module;
	private $_basket;

	public function __construct($module = false, $basket = false) {
		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
	}

	public function transfer() {
		$transfer	= array(
			'action'	=> 'https://secure.ebs.in/pg/ma/sale/pay',
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
		if($this->_module['testMode']=="Y") {
			$mode = 'TEST';	
		}
		else{
			$mode = 'LIVE';
		}
		$account_id = $this->_module['account_id'];
		$secret_key = $this->_module['secret_key'];
		$amount = $this->_basket['total'];
		$reference_no = $this->_basket['cart_order_id'];
		$return_url = $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=EBS&DR={DR}';
		$hash = $secret_key."|".$account_id."|".$amount."|".$reference_no."|".$return_url."|".$mode;
		echo $hash;
		$securehash = md5($hash);
		$hidden	= array(
			'account_id' => $account_id,
			'mode' => $mode,
			'reference_no' => $reference_no,
			'amount' => $amount,
			'description' => 'description',
		
			'name' => $this->_basket['billing_address']['first_name'].' '.$this->_basket['delivery_address']['last_name'],
			'address' => $this->_basket['billing_address']['line1'].' '.$this->_basket['delivery_address']['line2'],
			'city' => $this->_basket['billing_address']['town'],
			'state' => $this->_basket['billing_address']['state'],
			'postal_code' => $this->_basket['billing_address']['postcode'],
			'country' => $this->_basket['billing_address']['country_iso'],
			'phone' => $this->_basket['billing_address']['phone'],
			'email' => $this->_basket['billing_address']['email'],
					
			'ship_name' => $this->_basket['delivery_address']['first_name'].' '.$this->_basket['delivery_address']['last_name'],
			'ship_address' => $this->_basket['delivery_address']['line1'].' '.$this->_basket['delivery_address']['line2'],
			'ship_city' => $this->_basket['delivery_address']['town'],
			'ship_country' => $this->_basket['delivery_address']['country_iso'],
			'ship_state' => $this->_basket['delivery_address']['state'],
			'ship_postal_code' => $this->_basket['delivery_address']['postcode'],
		
			'return_url'=> $return_url,
			'secure_hash' => $securehash,
		);
		
		return (isset($hidden)) ? $hidden : false;
	}

	public function call() {
		return false;
	}

	public function process() {
		$order = Order::getInstance();	
		require (CC_ROOT_DIR.CC_DS.'modules'.CC_DS.'gateway'.CC_DS.'EBS'.CC_DS.'lib'.CC_DS.'Rc43.php');
		$secret_key = $this->_module['secret_key'];
		$DR = $_GET['DR'];
		if(isset($DR)){
			$DR = preg_replace("/\s/","+",$DR);
			$rc4 = new Crypt_RC4($secret_key);
			$QueryString = base64_decode($DR);
			$rc4->decrypt($QueryString);
			$QueryString = explode('&',$QueryString);
			$response = array();
			foreach($QueryString as $param){
				$param = explode('=',$param);
				$response[$param[0]] = urldecode($param[1]);
			}
		}
				
		$order_summary = $order->getSummary($response['MerchantRefNo']);				
		$cart_order_id = $response['MerchantRefNo'];		
		
		$responseMsg = $response['ResponseMessage'];		
		if($response['ResponseCode']==0){
			$action = "Complete";
			if($response['IsFlagged'] == "NO" && $response['Amount'] == $order_summary['total']){
				$notes 	= $responseMsg;
				$status = 'Received';
				$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
				$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);				
			}
			else {
				$notes = $responseMsg.". The payment has been kept on hold until the manual verification is completed and authorized by EBS";
				$status = 'Pending';	
				$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
				$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
			}
		}
		else{
			$action = "Error";
			$notes 	= $response['ResponseMessage'];
			$status = 'Failed';
			$order->paymentStatus(Order::PAYMENT_FAILED, $cart_order_id);
			$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);			
		}		
		
		$transData['notes']			= $notes;
		$transData['gateway']		= $_REQUEST['module'];
		$transData['order_id']		= $cart_order_id;
		$transData['trans_id']		= $response['TransactionID'];
		$transData['amount']		= $response['Amount'];
		$transData['status']		= $status;
		$transData['customer_id']	= $order_summary['customer_id'];
		$transData['extra']			= '';
		$order->logTransaction($transData);
		
		if($action == "Complete"){
			$url = $GLOBALS['storeURL'].'/index.php?_a=complete';
			httpredir($url);
		}
		else{
			$GLOBALS['gui']->setError("Transaction Failed. Please try again!!!");
			$url = $GLOBALS['storeURL'].'/index.php?_a=gateway';
			httpredir($url);			
		}
		
	}

	public function form() {
		return false;
	}
}