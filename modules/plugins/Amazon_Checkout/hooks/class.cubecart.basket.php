<?php
include(CC_ROOT_DIR.'/modules/plugins/Amazon_Checkout/hooks/common.inc.php');

if(defined('PURCHASE_CONTRACT_ID') && $module_config = $GLOBALS['config']->get('Amazon_Checkout')) {
	$scope = (isset($module_config['scope']) && !empty($module_config['scope']) && ($module_config['scope']=='main' && $GLOBALS['gui']->mobile) || ($module_config['scope']=='mobile' && !$GLOBALS['gui']->mobile)) ? false : true;

	if ($module_config['status'] && $scope) {
		
		if($GLOBALS['session']->get('stage', 'amazon')=='complete') {
			
			$customer_id = $GLOBALS['session']->get('customer_id', 'amazon');
			
			if($customer_id > 0) {
				$GLOBALS['db']->update('CubeCart_sessions', array('customer_id' => $customer_id), array('session_id' => $GLOBALS['session']->getId()));
			}
			
			$amazon_vars = array(
				'widgetURL' => $js_url,
				'merchId' 	=> $module_config['merchId'],
				'order_id'	=> $GLOBALS['session']->get('order_id', 'amazon'),
				'order_url' => 'https://'.$main_url
			);
			
			$GLOBALS['smarty']->assign('AMAZON',$amazon_vars);
			
			$path = 'modules/plugins/Amazon_Checkout';
		
			$file_name = 'complete.php';
			
			$form_file = $GLOBALS['gui']->getCustomModuleSkin('plugins', $path, $file_name);
			$GLOBALS['gui']->changeTemplateDir($form_file);
			$pre_content = $GLOBALS['smarty']->fetch($file_name);
			$GLOBALS['gui']->changeTemplateDir();	
			
			$content = $pre_content;
			
			$GLOBALS['session']->delete('', 'amazon');
			$GLOBALS['cart']->clear();
			
		} else {
		
			require_once('modules/plugins/Amazon_Checkout/library/CheckoutByAmazon/config.inc.php');
		
			unset($GLOBALS['cart']->basket['billing_address']);
			unset($GLOBALS['cart']->basket['delivery_address']);
			unset($GLOBALS['cart']->basket['register']);
			$GLOBALS['cart']->save();
	
			if($_GET['amazon_action']=='cancel') {
				$GLOBALS['session']->delete('', 'amazon');
				httpredir('index.php?_a=basket');
			} elseif($_GET['amazon_action']=='address') {
				$GLOBALS['session']->set('stage', 'address', 'amazon');
				httpredir('index.php?_a=basket');
			} elseif($_GET['_a']=='confirm') {
				$GLOBALS['session']->set('stage', 'wallet', 'amazon');
				httpredir('index.php?_a=basket');
			}
			
			$lib = new CheckoutByAmazon_Service_CBAPurchaseContract();
			
			try {
			    $addressList = $lib->getAddress(PURCHASE_CONTRACT_ID);
			    //Display the Address List
			    foreach($addressList as $address)
			    {
			        
			        $customer_address	= array(
						'postcode'		=> $address->getPostalCode(),
						'town'			=> $address->getCity(),
						'state_id'		=> getStateFormat($address->getStateOrProvinceCode(), 'name', 'id'),
						'state'			=> $address->getStateOrProvinceCode(),
						'state_abbrev'	=> getStateFormat($address->getStateOrProvinceCode(), 'name', 'abbrev'),
						'country'		=> getCountryFormat($address->getCountryCode(), 'iso', 'numcode'),
						'country_iso'	=> $address->getCountryCode(),
						'country_iso3'	=> getCountryFormat($address->getCountryCode(), 'iso', 'iso3'),
						'user_defined'  => false			
						
					);
					$GLOBALS['cart']->basket['billing_address']		= $customer_address;
					$GLOBALS['cart']->basket['delivery_address']	= $customer_address;
					$GLOBALS['cart']->basket['register']			= false;
					$GLOBALS['cart']->save();
			    }
			    
			    if($GLOBALS['session']->get('stage', 'amazon')=='wallet' && $GLOBALS['session']->get('postcode', 'amazon')!==$customer_address['postcode']) {
			    	$GLOBALS['session']->set('postcode', $customer_address['postcode'], 'amazon');
			    	httpredir('index.php?_a=basket');
			    	exit;
			    }
			}
			//Error with the request parameters passed by the merchant
			
			catch (CheckoutByAmazon_Service_RequestException $rex)
			{
			        /*
			        $error = "Caught Request Exception: ".$rex->getMessage();
			        $error .= "Response Status Code: ".$rex->getStatusCode();
			        $error .= "Error Code: ".$rex->getErrorCode();
			        $error .= "Error Type: ".$rex->getErrorType();
			        $error .= "Request ID: ".$rex->getRequestId()."\n";
			        $error .= "XML: ".$rex->getXML()."\n";
			        */
			        
			        $GLOBALS['gui']->setError('Error: '.$rex->getErrorCode().' '.$rex->getMessage());
			        $GLOBALS['session']->delete('', 'amazon');
			        httpredir('index.php?_a=basket');
			}
			
			//Internal system error occured
			catch (CheckoutByAmazon_Service_Exception $ex)
			{
			        /*
			        $error = "Caught Service Exception : ".$ex->getMessage();
			        $error .= "Response Status Code: ".$ex->getStatusCode();
			        $error .= "Error Code: ".$ex->getErrorCode();
			        $error .= "Error Type: ".$ex->getErrorType();
			        $error .= "Request ID: ".$ex->getRequestId() . "\n";
			        $error .= "XML: ".$ex->getXML() . "\n";
			        */
			        
			        $GLOBALS['gui']->setError('Error: '.$ex->getErrorCode().' '.$ex->getMessage());
			        $GLOBALS['session']->delete('', 'amazon');
			        httpredir('index.php?_a=basket');
			        
			}  
	
			$amazon_vars = array(
				'widgetURL' => $js_url,
				'width' 	=> ($GLOBALS['gui']->mobile) ? 280 : 400,
				'height' 	=> 228,
				'merchId' 	=> $module_config['merchId'],
				'delivery_address' => ucwords(strtolower($customer_address['town'])).', '.$customer_address['postcode']
			);
			
			$GLOBALS['smarty']->assign('AMAZON', $amazon_vars);
			
			
			
			$path = 'modules/plugins/Amazon_Checkout';
			
			$file_name = ($GLOBALS['session']->get('stage', 'amazon')=='wallet') ? 'wallet.php' : 'addressbook.php';
			
			$form_file = $GLOBALS['gui']->getCustomModuleSkin('plugins', $path, $file_name);
			$GLOBALS['gui']->changeTemplateDir($form_file);
			$pre_content = $GLOBALS['smarty']->fetch($file_name);
			$GLOBALS['gui']->changeTemplateDir();	
		
			$content = ($GLOBALS['session']->get('stage', 'amazon')=='wallet') ? $pre_content.$content : $pre_content;
		
		}
 	}
}
?>