<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */
if(!defined('CC_INI_SET')) die('Access Denied');
if ($GLOBALS['config']->get('AddShoppers', 'status') && !$GLOBALS['config']->isEmpty('AddShoppers', 'addshoppers_shop_id') && !$GLOBALS['config']->isEmpty('AddShoppers', 'addshoppers_api_secret')) {
	
	$currentPageArray = explode('&as_signature',currentPage());
	$currentPage = $currentPageArray[0];
		
	if (!empty($_GET['as_signature']) && $GLOBALS['user']->getId() == 0) {
			
		require CC_ROOT_DIR.'/modules/plugins/AddShoppers/library/addshoppers.lib.php';

		$response = addshoppers_verify_data($_GET['as_signature'],$GLOBALS['config']->get('AddShoppers', 'addshoppers_api_secret'));

		if (!$response['error']) {
			// find and authenticate
			if (!$GLOBALS['db']->select('CubeCart_customer', array('customer_id'), array('email' => $response['email']))) {
				// Create a new account
				$data	= array(
					'first_name'	=> $response['firstname'],
					'last_name'		=> $response['lastname'],
					'email'			=> $response['email'],
					'new_password'  => '0',
					'registered'	=> time(),
					'ip_address'	=> get_ip_address()
				);
				
				// Remove empty data
				foreach ($data as $key => $value) {
					if (empty($value)) {
						unset($data[$key]);
					}
				}
				if (!isset($data['email'])) {
					$data['email'] = ' ';
				}
				
				// insert new customer
				$GLOBALS['db']->insert('CubeCart_customer', $data);
				$customer_id = $GLOBALS['db']->insertid();
								
			} 
			
			// Log them in
			if (!$customer_id) {
				$customer = $GLOBALS['db']->select('CubeCart_customer', array('customer_id'), array('email' => $response['email']));
				$customer_id = $customer[0]['customer_id'];
			}						
			$GLOBALS['db']->update('CubeCart_sessions', array('customer_id' => $customer_id), array('session_id' => $GLOBALS['session']->getId()), false);
			
			// Load user data
			$GLOBALS['user']->load();
			$append	= ($GLOBALS['session']->isEmpty('contents','basket')) ? array('_a' => 'account') : array('_a' => 'basket');
			if (strpos($currentPage,'?') !== false) $a = '&';
			else $a = '?';
			if ($append['_a'] != '') $currentPage .= $a . '_a' . $append['a'];
			httpredir($currentPage);

		} else {
			$GLOBALS['gui']->setError('Error while logging in. Please try again or contact us.');
			httpredir($currentPage);
		}
	}
	elseif ($GLOBALS['user']->getId() == 0 && $_GET['_a'] != 'logout') {
		$GLOBALS['gui']->changeTemplateDir(str_replace('hooks','skin',dirname(__FILE__)));
		$file_name = 'login.tpl';
		$form_file = str_replace('hooks/','',$GLOBALS['gui']->getCustomModuleSkin('plugins', dirname(__FILE__), $file_name));
		$GLOBALS['gui']->changeTemplateDir($form_file);
		$login_html[] = $GLOBALS['smarty']->fetch($file_name);
		$GLOBALS['gui']->changeTemplateDir();
	}
	
}