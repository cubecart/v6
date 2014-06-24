<?php
class Gateway {
	private $_config;
	private $_module;
	private $_basket;

	public function __construct($module = false, $basket = false) {
		$this->_session	=& $GLOBALS['user'];

		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;

		if ($this->_module['test']) {
			$this->_module['customerid']	= '87654321';
			$this->_module['customername']	= 'TestAccount';
		}
		switch ($this->_module['mode']) {
			case 'AU':
				$this->_url		= 'www.eway.com.au';
				$this->_path 	= '/gateway/payment.asp';
				break;
			case 'NZ':
				$this->_url		= 'nz.ewaygateway.com';
				$this->_path_request 	= '/Request/';
				$this->_path_result 	= '/Result/';
				break;
			case 'UK':
			default:
				$this->_url		= 'payment.ewaygateway.com';
				$this->_path_request 	= '/Request/';
				$this->_path_result 	= '/Result/';
				break;
		}
	}

	##################################################

	public function transfer() {
		if ($this->_module['mode'] == 'AU') {

			$transfer	= array(
				'action'	=> 'https://'.$this->_url.$this->_path,
				'method'	=> 'post',
				'target'	=> '_self',
				'submit'	=> 'auto',
			);
			return $transfer;

		} else {

			$request_data	= array(
				## Required
				'CustomerID'		=> $this->_module['customerid'],
				'UserName'			=> $this->_module['customername'],
				'Amount'			=> $this->_basket['total'],
				'Currency'			=> $GLOBALS['config']->get('config', 'default_currency'),
				'ReturnURL'			=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=eway&cart_order_id='.$this->_basket['cart_order_id'],
				'CancelURL'			=> $GLOBALS['storeURL'].'/index.php?_g=gateway',
				'CompanyLogo'		=> '', // Enter https URL here to logo if wanted
				
				'MerchantReference'	=> $this->_basket['cart_order_id'],
				'MerchantInvoice'	=> $this->_basket['cart_order_id'],
				
				'PageTitle'			=> $GLOBALS['config']->get('config', 'store_name'),
				'CompanyName'		=> $GLOBALS['config']->get('config', 'store_name'),
				
				'CustomerFirstName'	=> $this->_basket['billing_address']['first_name'],
				'CustomerLastName'	=> $this->_basket['billing_address']['last_name'],
				'CustomerAddress'	=> $this->_basket['billing_address']['line1'].' '.$this->_basket['billing_address']['line2'],
				'CustomerCity'		=> $this->_basket['billing_address']['town'],
				'CustomerState'		=> $this->_basket['billing_address']['state'],
				'CustomerPostCode'	=> $this->_basket['billing_address']['postcode'],
				'CustomerCountry'	=> getCountryFormat($this->_basket['billing_address']['country_id'], 'numcode', 'name'),
				'CustomerPhone'		=> $this->_basket['billing_address']['phone'],
				'CustomerEmail'		=> $this->_basket['billing_address']['email'],
				'Language'			=> 'EN',
			);
			
			$request = new Request($this->_url, $this->_path_request);
			$request->setMethod('get');
			$request->setData($request_data);
			$request->setSSL();
			$response = $request->send();
	
			try {
				$xml = new SimpleXMLElement($response);
				if ((string)$xml->Result == 'True') {
					$target	= (string)$xml->URI;
					httpredir($target);
				}
			} catch (Exception $e) {}
		}
		return false;
	}

	public function repeatVariables() {
		return false;
	}
	
	public function fixedVariables() {
		if ($this->_module['mode'] == 'AU') {
		
			$address = array(
				$this->_basket['billing_address']['line1'],
				$this->_basket['billing_address']['line2'],
				$this->_basket['billing_address']['town'],
				$this->_basket['billing_address']['state'],
				$this->_basket['billing_address']['country']
			);
			
			$hidden	= array(
				'ewayCustomerID'			=> $this->_module['customerid'],
				'ewayTotalAmount'			=> ($this->_basket['total']*100),
				'ewayURL'					=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=eway&cart_order_id='.$this->_basket['cart_order_id'],
				'ewayAutoRedirect'			=> 1,
				'ewaySiteTitle'				=> $GLOBALS['config']->get('config', 'store_name'),
				'ewayCustomerFirstName'		=> $this->_basket['billing_address']['first_name'],
				'ewayCustomerLastName'		=> $this->_basket['billing_address']['last_name'],
				'ewayCustomerEmail'			=> $this->_basket['billing_address']['email'],
				'ewayCustomerAddress'		=> implode(" ",$address),
				'ewayCustomerPostcode'		=> $this->_basket['billing_address']['postcode'],
				'ewayCustomerInvoiceRef'	=> $this->_basket['cart_order_id'],
				'eWAYOption1'				=> $this->_basket['cart_order_id'],
			);
			return $hidden;
		}
		return false;
	}

	##################################################

	public function call() {
		return false;
	}

	public function process() {
		## Handle response payload on return from eway
		$order	= Order::getInstance();
		
		$response_codes = array(
			'00', # Transaction Approved
			'08', # Honour With Identification
			'10', # Approved For Partial Amount
			'11', # Approved, VIP
			'16'  # Approved, Update Track 3
		);

		if ($this->_module['mode'] == 'AU') {
			$eway	= array_change_key_case($_POST, CASE_LOWER);
			
			$cart_order_id		= $eway['ewayoption1'];
			$order_summary		= $order->getSummary($cart_order_id);

			$log['amount']		= $eway['ewayreturnamount'];
			$log['customer_id']	= $order_summary['customer_id'];
			$log['gateway']		= 'eWay ('.$this->_module['mode'].')';
			$log['notes']		= $eway['ewayresponsetext'];
			$log['order_id']	= $cart_order_id;
			$log['trans_id']	= $eway['ewaytrxnnumber'];

			$response_codes = array(
				'00', # Transaction Approved
				'08', # Honour With Identification
				'10', # Approved For Partial Amount
				'11', # Approved, VIP
				'16'  # Approved, Update Track 3
			);

			if (in_array($eway['ewayresponsecode'], $response_codes)) {
				$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
				$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
				$log['status'] = 'Success';
			} else {
				$log['status'] = 'Failed';
			}
			$order->logTransaction($log);
		} else {
			## NZ & UK
			if (isset($_POST['AccessPaymentCode'])) {
				$request_data	= array(
					'CustomerID'		=> $this->_module['customerid'],
					'UserName'			=> $this->_module['customername'],
					'AccessPaymentCode'	=> $_POST['AccessPaymentCode'],
				);
				
				$request = new Request($this->_url, $this->_path_result);
				$request->setMethod('get');
				$request->setData($request_data);
				$request->setSSL();
				$response = $request->send();
				
				try {
					$xml = new SimpleXMLElement($response);

					$cart_order_id		= (string)$xml->MerchantInvoice;
					$order_summary		= $order->getSummary($cart_order_id);

					$log['amount']		= (string)$xml->ReturnAmount;
					$log['customer_id']	= $order_summary['customer_id'];
					$log['gateway']		= 'eWay ('.$this->_module['mode'].')';
					$log['notes']		= (string)$xml->TrxnResponseMessage;
					$log['order_id']	= $cart_order_id;
					$log['trans_id']	= (string)$xml->TrxnNumber;

					if (in_array((string)$xml->ResponseCode, $response_codes)) {
						$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
						$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
						$log['status'] = 'Success';
					} else {
						$log['status'] = 'Failed';
					}

					$order->logTransaction($log);
				} catch (Exception $e) {}
			}
		}
		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
	}

	public function form() {
		return false;
	}
}