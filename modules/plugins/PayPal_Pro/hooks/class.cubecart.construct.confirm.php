<?php
/*
$Date: 2010-06-08 17:11:38 +0100 (Tue, 08 Jun 2010) $
$Rev: 1169 $
*/
if (isset($_GET['PPWPP']) && $_GET['PPWPP'] == 'cancel') {
	$GLOBALS['session']->delete('', 'PayPal_Pro');
	httpredir('index.php?_a=basket');
}


if (isset($_GET['token']) && isset($_GET['PayerID']) && $GLOBALS['session']->get('stage', 'PayPal_Pro')=='GetExpressCheckoutDetails') {
		
	include_once (CC_ROOT_DIR.'/modules/plugins/PayPal_Pro/website_payments_pro.class.php');
	
	$wpp	= new Website_Payments_Pro($GLOBALS['config']->get('PayPal_Pro'));
	
	if ($response = $wpp->GetExpressCheckoutDetails()) {
		
		$GLOBALS['session']->set('PayerID', $response['PAYERID'], 'PayPal_Pro');
		
		$phone_no = $GLOBALS['session']->get('phone', 'PayPal_Pro');
		
		if(isset($response['PAYMENTREQUEST_0_SHIPTOPHONENUM']) && !empty($response['PAYMENTREQUEST_0_SHIPTOPHONENUM'])) {
			$phone_no = $response['PAYMENTREQUEST_0_SHIPTOPHONENUM'];
		} elseif(!empty($phone_no)) {
			// use it :)
		} else {
			$GLOBALS['gui']->setError($lang['account']['error_valid_phone']);
			$phone_no = '';
		}
		
		$customer	= array(
			'title'			=> isset($response['SUFFIX']) ? $response['SUFFIX'] : '',
			'first_name'	=> $response['FIRSTNAME'],
			'last_name'		=> $response['LASTNAME'],
			'email'			=> $response['EMAIL'],
			'phone'			=> $phone_no,
		);

		$address	= array(
			'company_name'	=> '',
			'title'			=> $customer['title'],
			'first_name'	=> $customer['first_name'],
			'last_name'		=> $customer['last_name'],
			'line1'			=> $response['PAYMENTREQUEST_0_SHIPTOSTREET'],
			'line2'			=> $response['PAYMENTREQUEST_0_SHIPTOSTREET2'],
			'postcode'		=> $response['PAYMENTREQUEST_0_SHIPTOZIP'],
			'town'			=> $response['PAYMENTREQUEST_0_SHIPTOCITY'],
			
			'state_id'		=> getStateFormat($response['PAYMENTREQUEST_0_SHIPTOSTATE'], 'abbrev', 'id'),
			'state'			=> getStateFormat($response['PAYMENTREQUEST_0_SHIPTOSTATE'], 'abbrev', 'name'),
			'state_abbrev'	=> $response['PAYMENTREQUEST_0_SHIPTOSTATE'],
			
			'country'		=> getCountryFormat($response['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'], 'iso', 'numcode'),
			'country_id'	=> getCountryFormat($response['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'], 'iso', 'numcode'),
			'country_iso'	=> $response['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'],
			'country_iso3'	=> getCountryFormat($response['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'], 'iso', 'iso3'),
			'user_defined'  => true			
			
		);
		
		$this->_basket['customer']			= $customer;
		$this->_basket['billing_address']	= $address;
		$this->_basket['delivery_address']	= $address;
		$this->_basket['register']			= true;
		$this->_basket['terms_agree'] 		= true;		
		
		$GLOBALS['cart']->save();
		
		$address = array_merge($customer, $address);
			
		$address['customer_id'] = $customer_id;
		$address['billing'] = 1;
		$address['default'] = 1;
		$address['description'] = 'Default billing address';
		
		if(!$GLOBALS['user']->is()) {
			$customer['password'] = substr("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ",mt_rand(0,50),1).substr(md5(time()),1);
			$customer_id = $GLOBALS['user']->createUser($customer, false, 2);
			$GLOBALS['db']->update('CubeCart_sessions', array('customer_id' => $customer_id), array('session_id' => $GLOBALS['session']->getId()));
			if(!$GLOBALS['user']->getAddresses()) {
				$GLOBALS['user']->saveAddress($address,$customer_id);
			}
			
			httpredir('?_a=confirm&token='.$_GET['token'].'&PayerID='.$_GET['PayerID']);
		} else {
			if(empty($customer['phone'])) unset($customer['phone']); 
			$GLOBALS['db']->update('CubeCart_customer', $customer, array('customer_id' => $GLOBALS['user']->getId()));
			$GLOBALS['db']->delete('CubeCart_addressbook',array('customer_id' => $GLOBALS['user']->getId(), 'default' => 1, 'billing' => 1));
			$GLOBALS['user']->saveAddress($address);
			
			$GLOBALS['gui']->setNotify('Please click &quot;Make Payment&quot; to complete your order.');
			
			$GLOBALS['session']->set('stage', 'DoExpressCheckoutPayment', 'PayPal_Pro');
		}

	}
}