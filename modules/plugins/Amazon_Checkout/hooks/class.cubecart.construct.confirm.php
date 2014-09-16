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
include(CC_ROOT_DIR.'/modules/plugins/Amazon_Checkout/hooks/common.inc.php');

if(defined('PURCHASE_CONTRACT_ID') && $module_config = $GLOBALS['config']->get('Amazon_Checkout') && $GLOBALS['session']->get('stage', 'amazon')=='wallet') {
	
	$module_config = $GLOBALS['config']->get('Amazon_Checkout');
	
	$scope = (isset($module_config['scope']) && !empty($module_config['scope']) && ($module_config['scope']=='main' && $GLOBALS['gui']->mobile) || ($module_config['scope']=='mobile' && !$GLOBALS['gui']->mobile)) ? false : true;

	if ($module_config['status'] && $scope) {	

		require_once('modules/plugins/Amazon_Checkout/library/CheckoutByAmazon/config.inc.php');
		
		$lib = new CheckoutByAmazon_Service_CBAPurchaseContract();
		$itemList = new CheckoutByAmazon_Service_Model_ItemList();
		
		$merchantURLs = new CheckoutByAmazon_Service_Model_InstantOrderProcessingNotificationURLs();
		
		$call_path = '/index.php?_g=rm&type=gateway&cmd=call&module=Amazon_Checkout';
		$call_domain = ($module_config['mode']=="sandbox") ? $GLOBALS['storeURL'] : $GLOBALS['config']->get('config', 'ssl_url');
		
		$call_url = $call_domain.$call_path;
		
		$merchantURLs->setMerchantURL($call_url);
		
		$order	= Order::getInstance();
		
		foreach ($this->_basket['contents'] as $product) {
			
			$cubecart_total_price_each = $product['total_price_each'];
			$product['total_price_each'] = ($product['tax_each']['tax_inclusive']) ? $product['total_price_each'] : ($product['total_price_each']+($product['tax_each']['amount'] / $product['quantity']));
			
			$product['total_price_each'] = sprintf('%0.2f',$product['total_price_each']);
			
			$itemObject = new CheckoutByAmazon_Service_Model_PurchaseItem();
			$itemObject->createPhysicalItem($product['product_code'],$product['name'],$product['total_price_each'],'Standard');
			$itemObject->setSKU($product['product_code']);
			$itemObject->setQuantity($product['quantity']);
			$itemObject->setShippingLabel($this->_basket['shipping']['name']);
			$custom_data = array(
				'total_tax' 		=> $this->_basket['total_tax'],
				'order_taxes' 		=> $this->_basket['order_taxes'],
				'total_price_each' 	=> sprintf('%0.2f', $cubecart_total_price_each),
				'customer_id' 		=> $GLOBALS['session']->get('customer_id', 'amazon'),
				'product_options'	=> base64_encode($order->serializeOptions($product['options'],$product['id'])),
			);
			
			$custom_data = base64_encode(json_encode($custom_data));
			$itemObject->setItemCustomData($custom_data);
			$itemList->addItem($itemObject);
			
		}
		
		$charges = new CheckoutByAmazon_Service_Model_ContractCharges();
		
		$charges->setContractShippingCharges($this->_basket['shipping']['value']);
    	if($module_config['country']=='US') {
    		$charges->setContractTax('0.00');
    	}
		
    	if($this->_basket['discount']>0) { 
		    $promotion = new CheckoutByAmazon_Service_Model_Promotion();
		    $promotion->createPromotion('Discount','Discount',$this->_basket['discount']);
		    $promotionListObject = new CheckoutByAmazon_Service_Model_PromotionList();
		    $promotionListObject->addPromotion($promotion);
		    $charges->setContractPromotions($promotionListObject);
		      
		}
  
    	$setContractChargesStatus = $lib->setContractCharges(PURCHASE_CONTRACT_ID,$charges);
		
		try { 
		    $setItemsStatus = $lib->setItems(PURCHASE_CONTRACT_ID,$itemList);

		    if($setItemsStatus == 1) {
		        $GLOBALS['session']->set('stage', 'complete', 'amazon');
		        $orderIdList = $lib->completeOrder(PURCHASE_CONTRACT_ID, $marketplace_id, "CubeCart AmazonPayments 1.0", $merchantURLs);
		        if(!is_null($orderIdList)) {
		            foreach ($orderIdList as $orderId) {
		                $GLOBALS['session']->set('order_id', $orderId, 'amazon');  
		            }
		        }
		        httpredir('index.php?_a=basket');
		    }
		}
		//Error with the request parameters passed by the merchant
		catch (CheckoutByAmazon_Service_RequestException $rex) {
		    /*
		    echo("Caught Request Exception: " . $rex->getMessage().'<br />');
		    echo("Response Status Code: " . $rex->getStatusCode().'<br />');
		    echo("Error Code: " . $rex->getErrorCode().'<br />' );
		    echo("Error Type: " . $rex->getErrorType().'<br />' );
		    echo("Request ID: " . $rex->getRequestId().'<br />' . "\n");
		    echo("XML: " . $rex->getXML().'<br />' . "\n");
		    */
		    
		    $GLOBALS['gui']->setError('Error: '.$rex->getErrorCode().' '.$rex->getMessage());
			$GLOBALS['session']->delete('', 'amazon');
			httpredir('index.php?_a=basket');
		    
		}
		
		
		//Internal error occured
		catch (CheckoutByAmazon_Service_Exception $ex) {
		    /*
		    echo("Caught Service Exception: " . $ex->getMessage().'<br />');
		    echo("Response Status Code: " . $ex->getStatusCode().'<br />');
		    echo("Error Code: " . $ex->getErrorCode().'<br />' );
		    echo("Error Type: " . $ex->getErrorType().'<br />' );
		    echo("Request ID: " . $ex->getRequestId().'<br />' . "\n");
		    echo("XML: " . $ex->getXML().'<br />' . "\n");
		    */
		    
		    $GLOBALS['gui']->setError('Error: '.$ex->getErrorCode().' '.$ex->getMessage());
			$GLOBALS['session']->delete('', 'amazon');
			httpredir('index.php?_a=basket');
		    
		}
		exit;
	}
}
?>