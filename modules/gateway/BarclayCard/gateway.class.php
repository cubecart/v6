<?php
class Gateway {
	private $_module;
	private $_basket;

	public function __construct($module = false, $basket = false) {
		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
		if($this->_module['mode']=='new') {
			$this->_server	= ($this->_module['test_mode']) ? 'mdepayments.epdq.co.uk' : 'payments.epdq.co.uk';
			$this->_path	= ($this->_module['test_mode']) ? '/ncol/test/orderstandard.asp' : '/ncol/prod/orderstandard.asp';
		} else {
			$this->_server	= ($this->_module['test_mode']) ? 'secure2.mde.epdq.co.uk' : 'secure2.epdq.co.uk';
			$this->_path	= '/cgi-bin/CcxBarclaysEpdq.e';
		}
	}

	##################################################

	public function transfer() {
		$transfer	= array(
			'action'	=> 'https://'.$this->_server.$this->_path,
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
		
		if($this->_module['mode']=='new') {
			
			// The array keys below MUST be ascending alphabetically!! At the time of development ksort just would NOT work?!?	
			$hidden = array(	
				'ACCEPTURL' 	=> (string)$GLOBALS['storeURL'].'/index.php?_a=complete',
				'CANCELURL' 	=> (string)$GLOBALS['storeURL'].'/index.php?_a=gateway',
				'EXCEPTIONURL' 	=> (string)$GLOBALS['storeURL'].'/index.php?_a=complete',
				'DECLINEURL' 	=> (string)$GLOBALS['storeURL'].'/index.php?_a=complete',
				'AMOUNT' 		=> (string)($this->_basket['total']*100),
				'CN' 			=> (string)$this->_basket['billing_address']['first_name'].' '.$this->_basket['billing_address']['last_name'],
				'CURRENCY' 		=> (string)'GBP',
				'DECLINEURL' 	=> (string)urlencode($GLOBALS['storeURL'].'/index.php?_a=complete'),
				'EMAIL' 		=> (string)$this->_basket['billing_address']['email'],
				'LANGUAGE' 		=> (string)'en_US',
				'ORDERID' 		=> (string)$this->_basket['cart_order_id'],
				'OWNERADDRESS' 	=> (string)$this->_basket['delivery_address']['line1'].' '.(string)$this->_basket['delivery_address']['line2'],
				'OWNERCTY'	 	=> (string)$this->_basket['billing_address']['country_iso'],
				'OWNERTELNO' 	=> (string)$this->_basket['billing_address']['phone'],
				'OWNERTOWN' 	=> (string)$this->_basket['billing_address']['town'],
				'OWNERZIP' 		=> (string)$this->_basket['billing_address']['postcode'],
				'PSPID' 		=> (string)$this->_module['clientid'],	
			);
			
			if(!empty($this->_module['logo_url']) && preg_match('/^https:\/\//i',$this->_module['logo_url'])) {
				$hidden['LOGO'] = (string)urlencode($this->_module['logo_url']);
			}

			$sha_string = '';
			ksort($hidden);
			foreach($hidden as $key => $value) {
				$sha_string .= $key.'='.$value.$this->_module['passphrase'];
			}
		
			$hidden['SHASIGN'] = strtoupper(sha1($sha_string));
			
		} else {
		
			$request_data	= array(
				'clientid'		=> $this->_module['clientid'],
				'oid'			=> $this->_basket['cart_order_id'],
				'password'		=> $this->_module['passphrase'],
				'total'			=> $this->_basket['total'],
				'currencycode'	=> '826',
				'chargetype'	=> $this->_module['charge_type'],
			);
	
			//@todo What if there is no CURL!?
			$ch	= curl_init();
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_data, '', '&'));
			curl_setopt($ch, CURLOPT_URL, 'https://'.$this->_server.'/cgi-bin/CcxBarclaysEpdqEncTool.e');
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$result	= curl_exec($ch);
			
			/* This should work and I've just not been able to see why it doesn!!
			$request = new Request($this->_server, $this->_path);
			$request->setData($request_data);
			$request->setSSL();
			$response = $request->send();
			*/
			
			if ((bool)curl_errno($ch) == false) {
				curl_close($ch);
				//Parse their html response
				if (preg_match('#value="([A-Z0-9]+)"#iu', $result, $match)) {
					
					$hidden	= array(
						'epdqdata'				=> $match[1],
						'returnurl'				=> $GLOBALS['storeURL'].'/modules/gateway/BarclayCard/return.php',
						'merchantdisplayname'	=> $GLOBALS['config']->get('config', 'store_name'),
	
						'email'				=> $this->_basket['billing_address']['email'],
						## Billing data
						'bfullname'			=> $this->_basket['billing_address']['first_name'].' '.$this->_basket['billing_address']['last_name'],
						'baddr1'			=> $this->_basket['billing_address']['line1'],
						'baddr2'			=> $this->_basket['billing_address']['line2'],
						'bcity'				=> $this->_basket['billing_address']['town'],
						'bpostalcode'		=> $this->_basket['billing_address']['postcode'],
						'bcountry'			=> $this->_basket['billing_address']['country_iso'],
						'btelephonenumber'	=> $this->_basket['billing_address']['phone'],
						## Shipping Data
						'sfullname'		=> $this->_basket['delivery_address']['first_name'].' '.$this->_basket['delivery_address']['last_name'],
						'saddr1'		=> $this->_basket['delivery_address']['line1'],
						'saddr2'		=> $this->_basket['delivery_address']['line2'],
						'scity'			=> $this->_basket['delivery_address']['town'],
						'spostalcode'	=> $this->_basket['delivery_address']['postcode'],
						'scountry'		=> $this->_basket['delivery_address']['country_iso'],
					);
	
					if ($hidden['bcountry'] == 'US') {
						$hidden['bstate']	= $this->_basket['billing_address']['state_abbrev'];
					} else {
						$hidden['bcountyprovince'] = $this->_basket['billing_address']['state'];
					}
	
					if ($hidden['scountry'] == 'US') {
						$hidden['sstate']	= $this->_basket['delivery_address']['state_abbrev'];
					} else {
						$hidden['scountyprovince'] = $this->_basket['delivery_address']['state'];
					}
				}
			}
		}
		return $hidden;
	}

	##################################################

	public function call() {
		
		$order	= Order::getInstance();

		if($this->_module['mode']=='new') {
			
			if(!empty($_POST['SHASIGN'])) {
				
				$order_summary	= $order->getSummary($_POST['orderID']);
				
				if(!empty($this->_module['passphrase_out'])) {
					
					$sha_string = 	'ACCEPTANCE='.$_POST['ACCEPTANCE'].$this->_module['passphrase_out'].
									'AMOUNT='.$_POST['amount'].$this->_module['passphrase_out'].
									'BRAND='.$_POST['BRAND'].$this->_module['passphrase_out'].
									'CARDNO='.$_POST['CARDNO'].$this->_module['passphrase_out'].
									'CN='.$_POST['CN'].$this->_module['passphrase_out'].
									'CURRENCY='.$_POST['currency'].$this->_module['passphrase_out'].
									'ED='.$_POST['ED'].$this->_module['passphrase_out'].
									'IP='.$_POST['IP'].$this->_module['passphrase_out'].
									'NCERROR='.$_POST['NCERROR'].$this->_module['passphrase_out'].
									'ORDERID='.$_POST['orderID'].$this->_module['passphrase_out'].
									'PAYID='.$_POST['PAYID'].$this->_module['passphrase_out'].
									'PM='.$_POST['PM'].$this->_module['passphrase_out'].
									'STATUS='.$_POST['STATUS'].$this->_module['passphrase_out'].
									'TRXDATE='.$_POST['TRXDATE'].$this->_module['passphrase_out'];
									
					$sha_check = strtoupper(sha1($sha_string));
					
					if($sha_check!==$_POST['SHASIGN']) {
						$transData['status']		= 'Security Fail';
						$transData['notes']			= 'Unable to verify SHA1 siganture. Please check SHA-OUT Pass phrase in module settings. Returned value was '.$_POST['SHASIGN'].' and calculated value was '.$sha_check.'.';
						$transData['gateway']		= $_GET['module'];
						$transData['order_id']		= $_POST['orderID'];
						$transData['trans_id']		= $_POST['ACCEPTANCE'];
						$transData['amount']		= $_POST['amount'];
						$transData['customer_id']	= $order_summary['customer_id'];
						$order->logTransaction($transData);
						return false;
					}
				}
				
				
				
				/* STATUS
				0 - Incomplete or invalid 
				1 - Cancelled by client
				2 - Authorization refused 
				5 - Authorized
				9 - Payment requested
				*/
				
				if($_POST['STATUS']==9 && $_POST['amount']==$order_summary['total']) {
					$transData['status']	= 'Success';
					$transData['notes']		= '';
					$order->orderStatus(Order::ORDER_PROCESS, $_POST['orderID']);
				} elseif($_POST['STATUS']==5) {
					$transData['status']	= 'Pending (Authorized)';
					$transData['notes']		= '';
				} elseif($_POST['STATUS']==0) {
					$transData['status']	= 'Fail';
					$transData['notes']		= 'Incomplete or invalid';
				} elseif($_POST['STATUS']==1) {
					$transData['status']	= 'Fail';
					$transData['notes']		= 'Cancelled by customer';
				} elseif($_POST['STATUS']==2) {
					$transData['status']	= 'Fail';
					$transData['notes']		= 'Authorization refused';
				}
				
				$transData['gateway']		= $_GET['module'];
				$transData['order_id']		= $_POST['orderID'];
				$transData['trans_id']		= $_POST['ACCEPTANCE'];
				$transData['amount']		= $_POST['amount'];
				$transData['customer_id']	= $order_summary['customer_id'];
				$order->logTransaction($transData);
			}
		
		} else {		
			/* Available Parameters
				¥ transactionstatus Ð The outcome of the authorisation request. 
				¥ oid Ð The Order ID value. 
				¥ total Ð The transaction amount. 
				¥ clientid Ð Your unique ePDQ identifier. 
				¥ chargetype Ð The type of transaction you requested. 
				¥ datetime Ð Date and time of order. 
				¥ ecistatus Ð The outcome of the Internet Authentication process. 
				¥ cardprefix Ð First digit of the cardholderÕs card number. 
			*/
			//if ($_SERVER['PHP_AUTH_USER'] == $this->_module['post_user'] && $_SERVER['PHP_AUTH_PW'] == $this->_module['post_pass']) {
			
			$proceed 	= true;
			if ($_POST['clientid'] != $this->_module['clientid'] || !isset($_POST['transactionstatus'])) {
				$proceed = false;
			}
			if ((bool)$proceed) {
				$order_summary	= $order->getSummary($_POST['oid']);
				switch ($_POST['transactionstatus']) {
					case 'Success':
						$transData['status']	= 'Success';
						$order->orderStatus(Order::ORDER_PROCESS, $_POST['oid']);
						break;
					default:
						$transData['status']	= 'Declined';
						$order->orderStatus(Order::ORDER_CANCELLED, $_POST['oid']);
				}
				## Build the transaction log data
				$transData['gateway']		= $_GET['module'];
				$transData['order_id']		= $_POST['oid'];
				$transData['trans_id']		= '';
				$transData['amount']		= $_POST['total'];
				$transData['customer_id']	= $order_summary['customer_id'];
				$transData['notes']			= $_POST['transactionstatus'];
				$order->logTransaction($transData);
			}
			/*
			} else {
				header('WWW-Authenticate: Basic realm="Payment"');
				header('HTTP/1.0 401 Unauthorized');
			}
			*/
		}
	}

	public function process() {
		if (($status = $GLOBALS['db']->select('CubeCart_order_summary', 'status', array('cart_order_id' => $_GET['cart_order_id']))) !== false) {
			$cart_order_id = $_GET['cart_order_id'];
			switch($status[0]['status']) {
				case 2:
				case 3:
					$paymentResult = 2;
					break;
				default:
					$paymentResult = 1;
			}
			httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
		}
	}

	public function form() {
		return false;
	}
}