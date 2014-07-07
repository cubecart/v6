<?php
/*
$Date: 2010-05-11 16:50:17 +0100 (Tue, 11 May 2010) $
$Rev: 1087 $
*/
class Website_Payments_Pro  {

	private $_api_username;
	private $_api_password;
	private $_api_signature;
	private $_api_version	= '112';

	private $_api_endpoint;
	private $_api_paypal;
	private $_api_method;

	private $_basket;
	private $_module;
	private $_token;

	################################################

	public function __construct($settings = false) {
		$this->_basket =& $GLOBALS['cart']->basket;

		//parent::__construct();

		if (is_array($settings)) {
			$this->_module			= $settings;
			## Settings
			$this->_api_username	= $settings['username'];
			$this->_api_password	= $settings['password'];
			$this->_api_signature	= $settings['signature'];
			$this->_api_method		= $settings['paymentAction'];
			$mobile = ($GLOBALS['gui']->mobile) ? '-mobile' : '';
			
			if ($settings['gateway']) {
				## Live Mode
				$this->_api_endpoint	= 'api-3t.paypal.com/nvp';
				$this->_api_paypal_url	= 'https://www.paypal.com/webscr&cmd=_express-checkout'.$mobile.'&token=';
				$this->_api_paypal_inline_url	= 'https://www.paypal.com/checkoutnow?useraction=commit&useraction=continue&token=';
				$this->_api_paypal_recover_url	= 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';
				
			} else {
				## Sandbox/Testing Mode
				$this->_api_endpoint	= 'api-3t.sandbox.paypal.com/nvp';
				$this->_api_paypal_url	= 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout'.$mobile.'&token=';
				$this->_api_paypal_inline_url	= 'https://www.sandbox.paypal.com/checkoutnow?useraction=commit&useraction=continue&token=';
				$this->_api_paypal_recover_url	= 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';
			}
		} else {
			## Accelerated Boarding
			$this->_api_method		= 'Sale';
		}

		## Fetch Token, if set
		$this->_token	= $GLOBALS['session']->isEmpty('token','PayPal_Pro') ? false : $GLOBALS['session']->get('token', 'PayPal_Pro');
	}

	public function __destruct() {}

	public function __call($method, $arguments) {
		if (!empty($arguments) && is_array($arguments[0])) {
			if ($response = $this->nvp_request($method, $arguments[0])) {
				return $response;
			}
		}
		return false;
	}

	################################################

	public function GetTargetUrl() {
		return $this->_api_paypal_url.$this->_token;
	}
	
	public function GetHostedUrl($parameters) {
		$static_nvp_data = array(
			'BUTTONCODE' 		=> 'TOKEN',
			'BUTTONTYPE' 		=> 'PAYMENT',
		);	
		
		$k = 0;
		foreach($parameters as $key => $value) {
			$dynamic_nvp_data['L_BUTTONVAR'.$k] = $key.'='.$value;
			$k++;
		}
		if(!empty($this->_module['partner']) && !empty($this->_module['vendor'])) {
			$dynamic_nvp_data['L_BUTTONVAR'.$k] = 'partner='.$this->_module['partner'];
			$k++;
			$dynamic_nvp_data['L_BUTTONVAR'.$k] = 'vendor='.$this->_module['vendor'];
		}
		$nvp_data = array_merge($dynamic_nvp_data, $static_nvp_data);
		
		$response = $this->nvp_request('BMCreateButton', $nvp_data);
		return $response['EMAILLINK'];
	}

	################################################

	public function DoDirectPayment($nvp = array()) {
		## Process a credit card payment
		if (!empty($nvp)) {
			$nvp_data	= array(
				## Payment Data
				'PAYMENTACTION'		=> ($this->_api_method == 'Order') ? 'Authorization' : $this->_api_method,
				'IPADDRESS'			=> get_ip_address(),
				'RETURNFMFDETAILS'	=> '1',
				
				'INVNUM'			=> $this->_basket['cart_order_id'],

				## Values
				'AMT'				=> sprintf('%.2f', $this->_basket['total']),
				'ITEMAMT'			=> sprintf('%.2f', $this->_basket['total']),

				## Card details
				'CREDITCARDTYPE'	=> $nvp['card_type'],
				'ACCT'				=> preg_replace('#[\D]+#', '', $nvp['card_number']),
				'CVV2'				=> preg_replace('#[\D]+#', '', $nvp['cvv2']),
				'EXPDATE'			=> sprintf('%02d%d', (int)$nvp['exp_month'], (int)$nvp['exp_year']),

				## Billing Address
				'EMAIL'				=> $this->_basket['billing_address']['email'],
				'FIRSTNAME'			=> $nvp['first_name'],
				'LASTNAME'			=> $nvp['last_name'],
				'STREET'			=> $nvp['line1'],
				'STREET2'			=> $nvp['line2'],
				'CITY'				=> $nvp['town'],
				'STATE'				=> getStateFormat($nvp['state_id'], 'id', 'abbrev'),
				'ZIP'				=> $nvp['postcode'],
				'COUNTRYCODE'		=> getCountryFormat($nvp['country_id'], 'numcode', 'iso'),
				'CURRENCYCODE'		=> $GLOBALS['config']->get('config','default_currency'),
				'SHIPTOPHONENUM'	=> $nvp['phone'],

				## Shipping Address
				'SHIPTONAME'		=> trim(sprintf('%s %s %s', $this->_basket['delivery_address']['title'], $this->_basket['delivery_address']['first_name'], $this->_basket['delivery_address']['last_name'])),
				'SHIPTOSTREET'		=> $this->_basket['delivery_address']['line1'],
				'SHIPTOSTREET2'		=> $this->_basket['delivery_address']['line2'],
				'SHIPTOCITY'		=> $this->_basket['delivery_address']['town'],
				'SHIPTOZIP'			=> $this->_basket['delivery_address']['postcode'],
				'SHIPTOSTATE'		=> getStateFormat($this->_basket['delivery_address']['state_id'], 'id', 'abbrev'),
				'SHIPTOCOUNTRY'		=> $this->_basket['delivery_address']['country_iso']
				
			);
			if ($this->_module['3ds_status']) {
				$centinel	= array(
					'AUTHSTATUS3DS'		=> $GLOBALS['session']->get('AUTHSTATUS3DS', 'centinel'),
					'MPIVENDOR3DS'		=> $GLOBALS['session']->get('MPIVENDOR3DS', 'centinel'),
					'CAVV'				=> $GLOBALS['session']->get('CAVV', 'centinel'),
					'ECI3DS'			=> $GLOBALS['session']->get('ECI', 'centinel'),
					'XID'				=> $GLOBALS['session']->get('XID', 'centinel'),
				);
				$nvp_data	= array_merge($nvp_data, $centinel);
			}

			## Maestro/Solo required additional details
			if (in_array($nvp['card_type'], array('Maestro', 'Solo'))) {
				if (isset($nvp['issue_number']) && !empty($nvp['issue_number'])) {
					$nvp_data['ISSUENUMBER'] = (int)$nvp['issue_number'];
				}
				if (isset($nvp['issue']) && !empty($nvp['issue_month']) && !empty($nvp['issue_year'])) {
					$nvp_data['STARTDATE'] = sprintf('%02d%d', (int)$nvp['issue_month'], $nvp['issue_year']);
				}
			}
			## Handle Issue Date/Number
			if (isset($nvp['issue_month']) && !empty($nvp['issue_month']) && isset($nvp['issue_year']) && !empty($nvp['issue_year'])) {
				$nvp_data['STARTDATE']		= sprintf('%02d%d', trim($nvp['issue_month']), trim($nvp['issue_year']));
			}
			if (isset($nvp['issue_no']) && !empty($nvp['issue_no'])) {
				$nvp_data['ISSUENUMBER']	= trim($nvp['issue_no']);
			}
			unset($nvp);
			switch (strtoupper($GLOBALS['config']->get('config','default_currency'))) {
				case 'CAD':
					$nvp_data['BUTTONSOURCE']	= 'CubeCart_Cart_DP_CA';
					break;
				case 'GBP':
					$nvp_data['BUTTONSOURCE']	= 'CubeCart_Cart_DP';
					break;
				case 'USD':
					$nvp_data['BUTTONSOURCE']	= 'CubeCart_Cart_DP_US';
					break;
			}
			return $this->nvp_request('DoDirectPayment', $nvp_data);
		}
		return false;
	}

	public function DoExpressCheckoutPayment() {
		## Completes an Express Checkout transaction
		$delivery	= $this->_basket['delivery_address'];
		$nvp_data	= array(
			'PAYMENTREQUEST_0_PAYMENTACTION'		=> $this->_api_method,
			'TOKEN'				=> $this->_token,
			'PAYERID'			=> $GLOBALS['session']->get('PayerID', 'PayPal_Pro'),
			'RETURNFMFDETAILS'	=> '1',
			'PAYMENTREQUEST_0_CURRENCYCODE'		=> $GLOBALS['config']->get('config','default_currency'),
			'PAYMENTREQUEST_0_INVNUM'			=> $this->_basket['cart_order_id'],
			'PAYMENTREQUEST_0_AMT' => sprintf('%.2f', $this->_basket['total']),
			'PAYMENTREQUEST_0_SHIPPINGAMT' => 0,
			'PAYMENTREQUEST_0_NOTIFYURL'     => $GLOBALS['storeURL'].'/index.php?_g=rm&amp;type=gateway&amp;cmd=call&amp;module=PayPal',
			'PAYMENTREQUEST_0_MULTISHIPPING' => 0,	
			## Delivery Address
			'ADDROVERRIDE'	=> 1,
			'PAYMENTREQUEST_0_SHIPTONAME'	=> sprintf('%s %s', $delivery['first_name'], $delivery['last_name']),
			'PAYMENTREQUEST_0_SHIPTOSTREET'	=> $delivery['line1'],
			'PAYMENTREQUEST_0_SHIPTOSTREET2'	=> isset($delivery['line2']) ? $delivery['line2'] : '',
			'PAYMENTREQUEST_0_SHIPTOCITY'	=> $delivery['town'],
			'PAYMENTREQUEST_0_SHIPTOSTATE'	=> getStateFormat($delivery['state_id'], 'id', 'abbrev'),
			'PAYMENTREQUEST_0_SHIPTOZIP'		=> $delivery['postcode'],
			'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'	=> getCountryFormat($delivery['country_id'], 'numcode', 'iso'),
			'PAYMENTREQUEST_0_SHIPTOPHONENUM'=> isset($delivery['phone']) ? $delivery['phone'] : ''
		);

		$i	= 0;
		$tax_total	= 0;
		$prod_total	= 0;
		$itemamt 	= 0;
		$store_country = $GLOBALS['config']->get('config', 'store_country');
		
		if(!$GLOBALS['session']->get('skip_line_items', 'PayPal_Pro')) {
			
			$nvp_data['PAYMENTREQUEST_0_TAXAMT'] = sprintf('%.2f', $this->_basket['total_tax']);
			
			foreach ($this->_basket['contents'] as $hash => $item) {
				$product	= $GLOBALS['catalogue']->getProductData($item['id']);
				$price		= $item['total_price_each'];	## Always tax exclusive
				$GLOBALS['tax']->loadTaxes($this->_basket['delivery_address']['country_id']);
				$taxes = $GLOBALS['tax']->productTax($price, $product['tax_type'], false, $this->_basket['delivery_address']['state_id']);
	
				$tax_total	+= $taxes['amount'];
				$prod_total	+= $price;
				
				$nvp_data	= array_merge(array(
					'L_PAYMENTREQUEST_0_ITEMCATEGORY'.$i => ($item['digital']=='1') ? 'Digital' : 'Physical',
					'L_PAYMENTREQUEST_0_ITEMURL'.$i => $GLOBALS['storeURL'].'/index.php?_a=product&product_id='.$item['id'],
					'L_PAYMENTREQUEST_0_NUMBER'.$i => $item['product_code'],
					'L_PAYMENTREQUEST_0_ITEMWEIGHTVALUE'.$i => $item['product_weight'],
					'L_PAYMENTREQUEST_0_ITEMWEIGHTUNIT'.$i => $GLOBALS['config']->get('config','product_weight_unit'),
					'L_PAYMENTREQUEST_0_NAME'.$i => $item['name'],
					'L_PAYMENTREQUEST_0_AMT'.$i	=> sprintf('%.2f', $price),
					'L_PAYMENTREQUEST_0_QTY'.$i	=> $item['quantity'],
					'L_PAYMENTREQUEST_0_TAXAMT'.$i	=> sprintf('%.2f', $taxes['amount']),
				), $nvp_data);
				
				$itemamt+=sprintf('%.2f', $price);
				$i++;
			}
			
			if($this->_basket['discount']>0) {
				$nvp_data	= array_merge(array(
					'L_PAYMENTREQUEST_0_NAME'.$i	=> 'Discount',
					'L_PAYMENTREQUEST_0_AMT'.$i	=> '-'.sprintf('%.2f', $this->_basket['discount']),
					'L_PAYMENTREQUEST_0_QTY'.$i	=> 1,
					'L_PAYMENTREQUEST_0_TAXAMT'.$i	=> 0,
				), $nvp_data);
				$itemamt-=sprintf('%.2f', $this->_basket['discount']);
			}
			
			if($this->_basket['shipping']['value']>0) {
				$nvp_data	= array_merge(array(
					'L_PAYMENTREQUEST_0_NAME'.$i => 'Postage: '.$this->_basket['shipping']['name'],
					'L_PAYMENTREQUEST_0_AMT'.$i	=> sprintf('%.2f', $this->_basket['shipping']['value']),
					'L_PAYMENTREQUEST_0_QTY'.$i	=> 1,
					'L_PAYMENTREQUEST_0_TAXAMT'.$i => sprintf('%.2f',($this->_basket['total_tax'] - $tax_total)),
				), $nvp_data);
				$itemamt+=sprintf('%.2f', $this->_basket['shipping']['value']);
			}
			
			$nvp_data['PAYMENTREQUEST_0_ITEMAMT'] = $itemamt;
		
		} else {
			
			$nvp_data['PAYMENTREQUEST_0_ITEMAMT'] = sprintf('%.2f', $this->_basket['total']);
		}

		## PayPal's statistic tracking stuff
		if($GLOBALS['session']->has('BML', 'PayPal_Pro')) {
			$nvp_data['BUTTONSOURCE'] = 'CubeCart_Cart_BML';
		} else {		
			switch (strtoupper($GLOBALS['config']->get('config','default_currency'))) {
				case 'CAD':
					$nvp_data['BUTTONSOURCE'] = 'CubeCart_Cart_EC_CA';
					break;
				case 'GBP':
					$nvp_data['BUTTONSOURCE'] = 'CubeCart_Cart_EC';
					break;
				case 'USD':
					$nvp_data['BUTTONSOURCE'] = 'CubeCart_Cart_EC_US';
					break;
			}
		}
		if ($response = $this->nvp_request('DoExpressCheckoutPayment', $nvp_data)) {
			switch ($response['ACK']) {
				case 'SuccessWithWarning':
				case 'Success':
				#	$response	= $this->GetTransactionDetails($response['TRANSACTIONID']);
					break;
				case 'FailureWithWarning':
				case 'Failure':
					$GLOBALS['session']->delete('', 'PayPal_Pro');
					break;
			}
			return $response;
		}
		return false;
	}
	
	public function RecoverExpressCheckout() {
		httpredir($this->_api_paypal_recover_url.$this->_token);
	}

	public function GetExpressCheckoutDetails() {
		## Obtain information about an Express Checkout transaction
		if ($this->_token) {
			$nvp_data['TOKEN']	= $this->_token;
			if ($response = $this->nvp_request('GetExpressCheckoutDetails', $nvp_data)) {
				switch ($response['ACK']) {
					case 'SuccessWithWarning':
					case 'Success':
						// Disable recaptcha as PayPal human identity confirmed
						$recaptcha['confirmed'] = true;
						$GLOBALS['session']->set('', $recaptcha, 'recaptcha');
						return $response;
					case 'FailureWithWarning':
					case 'Failure':
						break;
				}
			}
		}
		return false;
	}
	public function SetExpressCheckout($bml = false, $inline = false, $line_items = true) {
		
		## Initiates an Express Checkout transaction		
		$nvp_data	= array(
			'RETURNURL'		=> $GLOBALS['storeURL'].'/index.php?_a=confirm',
			'CANCELURL'		=> $GLOBALS['storeURL'].'/index.php?_a=confirm&PPWPP=cancel',
			'PAYMENTREQUEST_0_NOTIFYURL' => $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=call&module=PayPal',
			'PAYMENTREQUEST_0_PAYMENTACTION'	=> $this->_api_method,
			'PAYMENTREQUEST_0_AMT' => sprintf('%.2f', $this->_basket['total']),
			'PAYMENTREQUEST_0_SHIPPINGAMT' => 0,
			'PAYMENTREQUEST_0_CURRENCYCODE' => $GLOBALS['config']->get('config','default_currency'),
			'PAYMENTREQUEST_0_INVNUM'	=> $this->_basket['cart_order_id'],
			'PAYMENTREQUEST_0_MULTISHIPPING' => 0,
			'ALLOWNOTE'		=> 0,
			'LOCALECODE'	=> $GLOBALS['language']->getLanguage(),
			'CARTBORDERCOLOR' => $this->_module['cartborder_color'],
			'LOGOIMG'		=> $this->_module['logoimg'],
			'BRANDNAME'		=> $GLOBALS['config']->get('config', 'store_name'),
			'GIFTMESSAGEENABLE' => 0,
			'GIFTRECEIPTENABLE' => 0,
			'GIFTWRAPENABLE'	=> 0,
			'BUYEREMAILOPTINENABLE'	=> 0,
			'SURVEYENABLE'	=> 0,
			'REQCONFIRMSHIPPING' => $this->_module['confAddress']
		);
		
		if($bml) {
			$nvp_data = array_merge($nvp_data, array('USERSELECTEDFUNDINGSOURCE' => 'BML','SOLUTIONTYPE' => 'SOLE', 'LANDINGPAGE' => 'BILLING'));
			$GLOBALS['session']->set('BML', true, 'PayPal_Pro');
		} elseif($GLOBALS['session']->has('BML', 'PayPal_Pro')) {
			$GLOBALS['session']->delete('BML', 'PayPal_Pro');
		}
		
		## Billing information
		$billing = $this->_basket['billing_address'];
		
		## Delivery information
		if (isset($this->_basket['delivery_address']['first_name']) && $delivery = $this->_basket['delivery_address']) {
			$nvp_data	= array_merge(array(
				'EMAIL'				=> $billing['email'],
				'ADDROVERRIDE'	=> 1,
				'PAYMENTREQUEST_0_SHIPTONAME'	=> sprintf('%s %s', $delivery['first_name'], $delivery['last_name']),
				'PAYMENTREQUEST_0_SHIPTOSTREET'	=> $delivery['line1'],
				'PAYMENTREQUEST_0_SHIPTOSTREET2'	=> $delivery['line2'],
				'PAYMENTREQUEST_0_SHIPTOCITY'	=> $delivery['town'],
				'PAYMENTREQUEST_0_SHIPTOSTATE'	=> $delivery['state_abbrev'],
				'PAYMENTREQUEST_0_SHIPTOZIP'		=> $delivery['postcode'],
				'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'	=> $delivery['country_iso'],
				'PAYMENTREQUEST_0_SHIPTOPHONENUM'=> $billing['phone'], /* we don't have a delivery value for this */
				'TOTALTYPE'		=> 'Total',
			), $nvp_data);
		} else {
			$nvp_data['TOTALTYPE'] = 'EstimatedTotal';
		}

		## Add basket contents
		$i 			= 0;
		$tax_total	= 0;
		$prod_total	= 0;
		$itemamt 	= 0;
		
		$store_country = $GLOBALS['config']->get('config', 'store_country');
		
		if($line_items) {
		
			$nvp_data['PAYMENTREQUEST_0_TAXAMT'] = sprintf('%.2f', $this->_basket['total_tax']);
		
			foreach ($this->_basket['contents'] as $hash => $item) {
				$product	= $GLOBALS['catalogue']->getProductData($item['id']);
				$price		= $item['total_price_each'];	## Always tax exclusive
				$GLOBALS['tax']->loadTaxes($this->_basket['delivery_address']['country_id']);
				$taxes = $GLOBALS['tax']->productTax($price, $product['tax_type'], false, $this->_basket['delivery_address']['state_id']);

				$tax_total	+= $taxes['amount'];
				$prod_total	+= $price;
				
				$itemamt+=sprintf('%.2f', $price);

				$nvp_data	= array_merge(array(
					'L_PAYMENTREQUEST_0_ITEMCATEGORY'.$i => ($item['digital']=='1') ? 'Digital' : 'Physical',
					'L_PAYMENTREQUEST_0_ITEMURL'.$i => $GLOBALS['storeURL'].'/index.php?_a=product&product_id='.$item['id'],
					'L_PAYMENTREQUEST_0_NUMBER'.$i => $item['product_code'],
					'L_PAYMENTREQUEST_0_ITEMWEIGHTVALUE'.$i => $item['product_weight'],
					'L_PAYMENTREQUEST_0_ITEMWEIGHTUNIT'.$i => $GLOBALS['config']->get('config','product_weight_unit'),
					'L_PAYMENTREQUEST_0_NAME'.$i => $item['name'],
					'L_PAYMENTREQUEST_0_AMT'.$i	=> sprintf('%.2f', $price),
					'L_PAYMENTREQUEST_0_QTY'.$i	=> $item['quantity'],
					'L_PAYMENTREQUEST_0_TAXAMT'.$i	=> sprintf('%.2f', $taxes['amount']),
				), $nvp_data);
				$i++;
			}
			if($this->_basket['discount']>0) {
				$nvp_data	= array_merge(array(
					'L_PAYMENTREQUEST_0_NAME'.$i	=> 'Discount',
					'L_PAYMENTREQUEST_0_AMT'.$i	=> '-'.sprintf('%.2f', $this->_basket['discount']),
					'L_PAYMENTREQUEST_0_QTY'.$i	=> 1,
					'L_PAYMENTREQUEST_0_TAXAMT'.$i	=> 0,
				), $nvp_data);
				$itemamt-=sprintf('%.2f', $this->_basket['discount']);
				$i++;
			}
			if($this->_basket['shipping']['value']>0) {
				$nvp_data	= array_merge(array(
					'L_PAYMENTREQUEST_0_NAME'.$i => 'Postage: '.$this->_basket['shipping']['name'],
					'L_PAYMENTREQUEST_0_AMT'.$i	=> sprintf('%.2f', $this->_basket['shipping']['value']),
					'L_PAYMENTREQUEST_0_QTY'.$i	=> 1,
					'L_PAYMENTREQUEST_0_TAXAMT'.$i => sprintf('%.2f',($this->_basket['total_tax'] - $tax_total)),
				), $nvp_data);
				$itemamt+=sprintf('%.2f', $this->_basket['shipping']['value']);
			}
		
			$nvp_data['PAYMENTREQUEST_0_ITEMAMT'] = sprintf('%.2f', $itemamt);
		}
	
		if ($response = $this->nvp_request('SetExpressCheckout', $nvp_data)) {
			
			// Line items can screw up transaction rarely due to reounding.. lets skip them if we error on this
			if($line_items && $response['L_ERRORCODE0']==10413) {
				$GLOBALS['session']->set('skip_line_items', true, 'PayPal_Pro');
				unset($nvp_data);
				$this->SetExpressCheckout($bml, $inline, false);
			}

			$GLOBALS['db']->update('CubeCart_order_summary', array('gateway' => 'PayPal_Pro'), array('cart_order_id' => $this->_basket['cart_order_id']));
			switch ($response['ACK']) {
				case 'SuccessWithWarning':
				case 'Success':
					$this->_token	= $response['TOKEN'];
					$GLOBALS['session']->set('token', $this->_token, 'PayPal_Pro');
					
					$GLOBALS['session']->set('stage', 'GetExpressCheckoutDetails', 'PayPal_Pro');
	
					if($inline) {
						httpredir($this->_api_paypal_inline_url.$this->_token);
					} else {
						httpredir($this->_api_paypal_url.$this->_token);
					}
					
					break;
				case 'FailureWithWarning':
				case 'Failure':
					$GLOBALS['gui']->setError($GLOBALS['language']->gui_message['error'].': '.$response['L_LONGMESSAGE0'].' '.$response['L_SHORTMESSAGE0']);
					httpredir(CC_STORE_URL.'/index.php?_a=confirm&PPWPP=cancel');
					return false;
					break;
			}
		}
	}

	################################################
	/* !Protected Methods */

	final protected function nvp_request($method_name = null, $nvp_data = array()) {
		if (!empty($method_name) && is_array($nvp_data)) {
			$nvp_basic	= array(
				'METHOD' 	=> $method_name,
				'VERSION'	=> $this->_api_version,
				'PWD'		=> $this->_api_password,
				'USER'		=> $this->_api_username,
				'SIGNATURE'	=> $this->_api_signature,
			);
			
			$nvp_data		= array_change_key_case($nvp_data, CASE_UPPER);
			$nvp_request	= http_build_query(array_merge($nvp_data, $nvp_basic), '', '&');

			## Send Request
			$request	= new Request($this->_api_endpoint);
			$request->setSSL();
			$request->setData($nvp_request);
			if ($nvp_response = $request->send()) {
				$response_array	= $this->nvp_decode($nvp_response);
				$request_array	= $this->nvp_decode($nvp_request);
				return array_change_key_case($response_array, CASE_UPPER);
			}
		}
		return false;
	}

	final protected function nvp_decode($nvp_string) {
		parse_str($nvp_string, $nvp_array);
		ksort($nvp_array);
		return $nvp_array;
	}
}


class CentinelClient {

	private $_parser	= false;
	private $_request	= false;
	private $_response	= false;
	private $_xml		= false;

	public function __construct($merchant_id = null, $transaction_pwd = null, $processor_id = null, $version = '1.7') {
		$this->_request	= array(
			'Version'			=> $version,
			'ProcessorId'		=> $processor_id,
			'MerchantId'		=> $merchant_id,
			'TransactionPwd'	=> $transaction_pwd,
		);
		$this->_xml = new XML(true);
	}

	public function __destruct() {}

	## Public Methods

	public function add($name, $value = null) {
		if (is_array($name)) {
			foreach ($name as $key => $value) {
				$this->_request[$key] = $value;
			}
		} else {
			$this->_request[$name] = $value;
		}
	}

	public function getValue($name) {
		if (isset($this->_response[$name])) return $this->_response[$name];
		trigger_error(sprintf("Value '%s' does not exist.", $name), E_USER_WARNING);
		return null;
	}

	public function sendHttp($url, $connect_timeout = 5, $request_timeout = 10) {

		$data 		= $this->getRequestXml();
		$request	= new Request($url);
		$request->setSSL();
		$request->setData($data);
		$result		= $request->send();

		if (!$result) {
			$result = $this->setErrorResponse(8030, 'Communication timeout encountered.');
		} else if(!preg_match('/<CardinalMPI>/',$result)) {
			$result = $this->setErrorResponse(8010, 'Unable to communicate with MAPS server.');
		}

		if (!empty($result)) {
			try {
				$parser	= new SimpleXMLElement($result);
				foreach ($parser as $key => $value) {
					$this->_response[(string)$key] = (string)$value;
				}
				return $this->_response;
			} catch (Exception $e) {
				$this->_response['ErrorNo']		= 8020;
				$this->_response['ErrorDesc']	= 'Error parsing XML response.';
			}
		}
		return false;
	}

	## Private Methods

	private function getRequestXml() {
		$this->_xml->startElement('CardinalMPI');
		foreach ($this->_request as $name => $value) {
			if (is_numeric($value)) {
				$this->_xml->writeElement($name, $value);
			} else {
				$this->_xml->startElement($name);
				$this->_xml->writeCData($value);
				$this->_xml->endElement();
			}
		}
		$this->_xml->endElement();
		$data	= $this->_xml->getDocument();
		return 'cmpi_msg='.urlencode($data);
	}

	private function setErrorResponse($error_no, $error_desc) {
		$this->_xml->startElement('CardinalMPI');
		$this->_xml->writeElement('ErrorNo', $error_no);
		$this->_xml->writeElement('ErrorDesc', $error_desc);
		$this->_xml->endElement();
		return $this->_xml->getDocument();
	}

	private function escapeXML($value) {
		trigger_error(__CLASS__.'::'.__METHOD__.' is deprecated.', E_USER_WARNING);
		return $value;
	}
}