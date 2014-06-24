<?php
class Gateway {
	private $_module;
	private $_basket;
	
	private $_hide_login;
	private $_payment_methods;
	private $_url;
	private $_gateway_name;

	public function __construct($module = false, $basket = false) {
		$this->_session	=& $GLOBALS['user'];

		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
		
		if($this->_module['API_method']=='standard') {
			$this->_url = ($this->_module['test_mode']) ? 'https://checkout.test.optimalpayments.com/securePayment/op/profileCheckoutRequest.htm' : 'https://checkout.optimalpayments.com/securePayment/op/profileCheckoutRequest.htm';
		} else {
			$this->_url = ($this->_module['test_mode']) ? 'https://checkout.test.tradegard.com/securePayment/tradegard/checkoutRequest.htm' : 'https://checkout.tradegard.com/securePayment/tradegard/checkoutRequest.htm';
		}
	}

	##################################################

	public function transfer() {
		$transfer	= array(
			'action'	=> $this->_url,
			'method'	=> 'get',
			'target'	=> '_self',
			'submit'	=> ($this->_module['API_method']=='standard') ? 'iframe' : 'auto',
		);
		return $transfer;
	}

	public function repeatVariables() {
		return false;
	}

	public function fixedVariables() {
		
		if($this->_basket['billing_address']['country_iso'] == "US" || $this->_basket['billing_address']['country_iso'] == "CA") {
			$state_region = "state";
		} else {
			$state_region = "region";
		}
		
		$xml	= new XML(true);
		
		if($this->_module['API_method']=='tradegard') {
				
			$xml->startElement('checkoutRequest',array('xmlns' => 'www.optimalpayments.com/checkout'));
				$xml->writeElement('merchantRefNum', $this->merchantRefNum());
				$xml->startElement('returnUrl',array('page' => $GLOBALS['storeURL'].'/index.php'));
					$xml->startElement('param');
						$xml->writeElement('key','_g');
						$xml->writeElement('value','rm');
					$xml->endElement();
					$xml->startElement('param');
						$xml->writeElement('key','type');
						$xml->writeElement('value','gateway');
					$xml->endElement();
					$xml->startElement('param');
						$xml->writeElement('key','cmd');
						$xml->writeElement('value','process');
					$xml->endElement();
					$xml->startElement('param');
						$xml->writeElement('key','module');
						$xml->writeElement('value','OptimalPayments');
					$xml->endElement();
					$xml->startElement('param');
						$xml->writeElement('key','cart_order_id');
						$xml->writeElement('value',$this->_basket['cart_order_id']);
					$xml->endElement(); 
				$xml->endElement(); 
				$xml->startElement('cancelUrl',array('page',$GLOBALS['storeURL'].'/index.php'));
					$xml->startElement('param');
						$xml->writeElement('key','_a');
						$xml->writeElement('value','gateway');
					$xml->endElement();
				$xml->endElement();
				
				$xml->writeElement('currencyCode',$GLOBALS['config']->get('config', 'default_currency'));
					$xml->startElement('shoppingCart');
						$xml->writeElement('description','Order #'.$this->_basket['cart_order_id']);
						$xml->writeElement('quantity','1');
						$xml->writeElement('amount',$this->_basket['total']);
					$xml->endElement(); 
					$xml->writeElement('totalAmount',$this->_basket['total']); 
					$xml->startElement('locale');
						$xml->writeElement('language','en');
						$xml->writeElement('country',$this->getLanguage($this->_basket['billing_address']['country_iso']));
					$xml->endElement();
					$xml->startElement('billingDetails');
						$xml->writeElement('firstName',$this->_basket['billing_address']['first_name']);
						$xml->writeElement('lastName',$this->_basket['billing_address']['last_name']);
						$xml->writeElement('street',$this->_basket['billing_address']['line1']);
						$xml->writeElement('street2',$this->_basket['billing_address']['line2']);
						$xml->writeElement('city',$this->_basket['billing_address']['town']);
						$xml->writeElement($state_region, $this->_basket['billing_address']['state_abbrev']);
						$xml->writeElement('country',$this->_basket['billing_address']['country_iso']);
						$xml->writeElement('zip',$this->_basket['billing_address']['postcode']);
						$xml->writeElement('phone',$this->_basket['billing_address']['phone']);
						$xml->writeElement('email',$this->_basket['billing_address']['email']);
					$xml->endElement(); 
					$xml->writeElement('previousCustomer',$this->previousCustomer());
					$xml->writeElement('productType',$this->productType());
				$xml->endElement();
			
			} else {
				
				$xml->startElement('profileCheckoutRequest', array('xmlns' => 'www.optimalpayments.com/checkout'));
					$xml->writeElement('merchantRefNum', $this->merchantRefNum());
					$xml->startElement('returnUrl', array('page' => $GLOBALS['storeURL'].'/index.php'));
						$xml->startElement('param');
							$xml->writeElement('key','_g');
							$xml->writeElement('value','rm');
						$xml->endElement();
						$xml->startElement('param');
							$xml->writeElement('key','type');
							$xml->writeElement('value','gateway');
						$xml->endElement();
						$xml->startElement('param');
							$xml->writeElement('key','cmd');
							$xml->writeElement('value','process');
						$xml->endElement();
						$xml->startElement('param');
							$xml->writeElement('key','module');
							$xml->writeElement('value','OptimalPayments');
						$xml->endElement();
						$xml->startElement('param');
							$xml->writeElement('key','cart_order_id');
							$xml->writeElement('value',$this->_basket['cart_order_id']);
						$xml->endElement(); 
					$xml->endElement();
					$xml->startElement('cancelUrl', array('page'=> $GLOBALS['storeURL'].'/index.php'));
						$xml->startElement('param');
						$xml->writeElement('key','_a');
						$xml->writeElement('value','gateway');
					$xml->endElement();
					$xml->endElement();
						$xml->writeElement('paymentMethod', 'CC');
						$xml->writeElement('currencyCode', $GLOBALS['config']->get('config', 'default_currency')); 
						$xml->startElement('shoppingCart');
							$xml->writeElement('description', 'Order #'.$this->_basket['cart_order_id']);
							$xml->writeElement('quantity','1');
							$xml->writeElement('amount',$this->_basket['total']);
						$xml->endElement(); 
					$xml->writeElement('totalAmount', $this->_basket['total']);
					$xml->startElement('customerProfile');
						$xml->writeElement('merchantCustomerId', $this->_basket['billing_address']['email']);
				$xml->writeElement('isNewCustomer','false');
				$xml->endElement(); 
					$xml->startElement('locale');
						$xml->writeElement('language', 'en');
						$xml->writeElement('country',$this->getLanguage($this->_basket['billing_address']['country_iso']));
					$xml->endElement();
				$xml->endElement();
		
			}
			$xml_request 	= $xml->getDocument();

			$hidden = array(
				'shopId' => $this->_module['shopId'],
				'encodedMessage' => base64_encode($xml_request),
				'signature' => base64_encode(hash_hmac("sha1", $xml_request, $this->_module['sharedKey'], true))
			);
			return $hidden;
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

	##################################################

	public function call() {
		// return 204 status so it knows there is no content to return
		header("HTTP/1.0 204 No Content");
		
		$decodedMessage = base64_decode($_POST['encodedMessage']);

		// Decode signature received back and check against shop key to verify the message was not tampered with
		$computedSignature = base64_encode(hash_hmac('SHA1', $decodedMessage, $this->_module['sharedKey'], TRUE));
		
		if($computedSignature == $_POST['signature']) {
			/*
			Example checkout response:
			<?xml version="1.0" encoding="UTF-8"?>
			<checkoutResponse xmlns="www.optimalpayments.com/checkout">
				<confirmationNumber>11115555</confirmationNumber> 
				<merchantRefNum>12312331</merchantRefNum>
				<accountNum>123456789</accountNum> 
				<cardType>VI</cardType> 
				<decision>ACCEPTED</decision> 
				<code>0</code>
				<description>Transaction processed successfully.</description>
				<txnTime>2009-09-17T09:30:47.0Z</txnTime>
			</CheckoutResponse>
			*/
			$xmldata = new SimpleXMLElement($decodedMessage);
			$decoded_merchantRefNum = base64_decode($xmldata->merchantRefNum);
			
			$cart_order_id 		= substr($xmldata->merchantRefNum,0,-15);
			$order				= Order::getInstance();
			$order_summary		= $order->getSummary($cart_order_id);
			
			switch (strtoupper($xmldata->decision)) {
				case "ACCEPTED":
					$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
					$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
				break;
				case "ERROR":
					$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
					$order->paymentStatus(Order::PAYMENT_FAILED, $cart_order_id);
				break;
				case "DECLINED":
					$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
					$order->paymentStatus(Order::PAYMENT_FAILED, $cart_order_id);
				break;
			}
			
			$transData['customer_id'] 	= $order_summary['customer_id'];
			$transData['gateway'] 		= 'Optimal Payments ('.ucfirst($this->_module['API_method']).')';
			$transData['trans_id'] 		= $xmldata->confirmationNumber;
			$transData['amount'] 		= $order_summary['total'];
			$transData['status'] 		= $xmldata->decision;
			$transData['notes'] 		= $xmldata->description;
			$order->logTransaction($transData);
		}
	}

	public function process() {
		## Handle the return process
		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
	}
	
	private function merchantRefNum() {
		return $this->_basket['cart_order_id'].time().rand(10000,99999);
	}
	
	private function getLanguage($country_iso) {
		return ($country_iso=='GB') ? 'GB' : 'US'; 
	}
	
	private function previousCustomer() {
		return $GLOBALS['db']->select('CubeCart_order_summary', false, array('status' => 3, 'customer_id' => $this->_basket['billing_address']['customer_id']), false, 1) ? 'true' : 'false';
	}
	
	private function productType() {
		$digital 	= false;
		$tangible 	= false;
		foreach ($this->_basket['contents'] as $key => $value) {
			if($key == 'digital' && $key[$value]===1) {
				$digital = true;
			} else {
				$tangible = true;
			}
		}
		/*
		´ P = Physical Goods 
		´ D = Digital Goods/Subscription Registra-tion
		´ C = Digital Content 
		´ G = Gift Certificate/Digital Cash 
		´ S = Shareware 
		´ M = Digital & Physical 
		´ R = Subscription Renewal
		*/
		if($digital && $tangible) {
			// Digital & Tangible Mix
			return "M";
		} elseif($digital && !$tangible) {
			// Digital Only
			return "C";
		} else {
			// Tangible
			return "P";
		}
	}
}