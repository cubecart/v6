<?php 
// Set include path for library
$amazon_lib = 'modules/plugins/Amazon_Checkout/library';
set_include_path(get_include_path().PATH_SEPARATOR.$amazon_lib);

$amazon_classes = array(
	'CheckoutByAmazon_Service_CBAPurchaseContract',
	'CheckoutByAmazon_Service_Client',
	'CheckoutByAmazon_Service_MerchantValues',
	'CheckoutByAmazon_Service_Exception',
	'CheckoutByAmazon_Service_RequestException',
	'CheckoutByAmazon_Service_Model_GetPurchaseContractRequest',
	'CheckoutByAmazon_Service_Model_ItemList',
	'CheckoutByAmazon_Service_Model_PurchaseItem',
	'CheckoutByAmazon_Service_Model_Price',
	'CheckoutByAmazon_Service_Model_DeliveryMethod',
	'CheckoutByAmazon_Service_Model_PhysicalProductAttributes',
	'CheckoutByAmazon_Service_Model_Charges',
	'CheckoutByAmazon_Service_Model_SetPurchaseItemsRequest',
	'CheckoutByAmazon_Service_Model_CompletePurchaseContractRequest',
	'CheckoutByAmazon_Service_Model_Promotion',
	'CheckoutByAmazon_Service_Model_PromotionList',
	'CheckoutByAmazon_Service_Model_ContractCharges',
	'CheckoutByAmazon_Service_Model_SetContractChargesRequest',
	'CheckoutByAmazon_Service_Model_InstantOrderProcessingNotificationURLs'
);

foreach($amazon_classes as $class) {
	$file_name = str_replace('_','/',$class);
	$file_path = CC_ROOT_DIR.'/modules/plugins/Amazon_Checkout/library/'.$file_name.'.php';
	if(file_exists($file_path)) {
		require_once $file_path;
	} else {
		continue;
	}	
}