<?php
if(isset($_GET['purchaseContractId']) && !empty($_GET['purchaseContractId'])) {
	if($_GET['purchaseContractId']=='null') {
		$GLOBALS['session']->delete('purchaseContractId', 'amazon');
	} else {
		$GLOBALS['session']->set('purchaseContractId', $_GET['purchaseContractId'], 'amazon');
	}
	httpredir('index.php?_a=basket');
}

$purchaseContractId = $GLOBALS['session']->get('purchaseContractId', 'amazon');
if(!empty($purchaseContractId)) {
	define(PURCHASE_CONTRACT_ID,$purchaseContractId);
}

// Set locals
$module_config = $GLOBALS['config']->get('Amazon_Checkout');

switch($module_config['country']) {

	case "US":
		$main_url 			= ($module_config['mode']=='sandbox') ? 'payments-sandbox.amazon.com' : 'payments.amazon.com';
		$js_url 			= ($module_config['mode']=='sandbox') ? 'https://static-na.payments-amazon.com/cba/js/us/sandbox/PaymentWidgets.js' : 'https://static-na.payments-amazon.com/cba/js/us/PaymentWidgets.js';
		$cba_endpoint 		= ($module_config['mode']=='sandbox') ? 'https://payments-sandbox.amazon.com/cba/api/purchasecontract/' : 'https://payments.amazon.com/cba/api/purchasecontract/';
		$mws_endpoint 		= 'https://mws.amazonservices.com';
		$marketplace_id 	= 'AZ4B0ZS3LGLX';
		$currency_code 		= 'USD';
	break;
	
	case "DE":
		$main_url 			= ($module_config['mode']=='sandbox') ? 'payments-sandbox.amazon.de' : 'payments.amazon.de';
		$js_url 			= ($module_config['mode']=='sandbox') ? 'https://static-eu.payments-amazon.com/cba/js/de/sandbox/PaymentWidgets.js' : 'https://static-eu.payments-amazon.com/cba/js/de/PaymentWidgets.js';
		$cba_endpoint 		= ($module_config['mode']=='sandbox') ? 'https://payments-sandbox.amazon.de/cba/api/purchasecontract/' : 'https://payments.amazon.de/cba/api/purchasecontract/';
		$mws_endpoint 		= 'https://mws-eu.amazonservices.com';
		$marketplace_id 	= 'A1OCY9REWJOCW5';
		$currency_code 		= 'EUR';
	break;
	
	default: // UK
		$main_url 			= ($module_config['mode']=='sandbox') ? 'payments-sandbox.amazon.co.uk' : 'payments.amazon.co.uk';
		$js_url 			= ($module_config['mode']=='sandbox') ? 'https://static-eu.payments-amazon.com/cba/js/gb/sandbox/PaymentWidgets.js' : 'https://static-eu.payments-amazon.com/cba/js/gb/PaymentWidgets.js';
		$cba_endpoint 		= ($module_config['mode']=='sandbox') ? 'https://payments-sandbox.amazon.co.uk/cba/api/purchasecontract/' : 'https://payments.amazon.co.uk/cba/api/purchasecontract/';
		$mws_endpoint 		= 'https://mws.amazonservices.co.uk';
		$marketplace_id 	= 'A1XL5LAOXFJ3SB';
		$currency_code 		= 'GBP';
	break;

}

?>