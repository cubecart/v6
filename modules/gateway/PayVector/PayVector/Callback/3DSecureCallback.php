<?php

// DO NOT EDIT BETWEEN THIS AND NEXT COMMENT
require_once 'modules/gateway/PayVector/PayVector/Config.php';
// DO NOT EDIT BETWEEN THIS AND PREVIOUS COMMENT

$GLOBALS['config']->get('_module');

require_once dirname(dirname(__FILE__)) . '/Core/PayVectorDirect.php';
$PayVector = new PayVectorDirect("CubeCart");
$PayVector->setTransactionMethod(TransactionMethod::ThreeDSecureTransaction);

$PayVector->setDebugMode($this->module['testMode']);
$PayVector->setDebugEmailAddress($DeveloperEmailAddress);
//$PayVector->setDatabaseSupport(TRUE);

$PayVector->setPaymentProcessorFullDomain($PaymentProcessorFullDomain);
$PayVector->setTransactionType(TransactionType::Sale);

//$PayVector->setOriginCrossReference($params['gatewayid']);

if (!$this->module['testMode']) {
	$PayVector->setMerchantID($this->module['mid_prod']);
    $PayVector->setPassword($this->module['pass_prod']);
} else {
    $PayVector->setMerchantID($this->module['mid_test']);
    $PayVector->setPassword($this->module['pass_test']);
}

$PayVector->setOrderID($order_summary['cart_order_id']);
$PayVector->setOrderDescription("Order ID: " . $order_summary['cart_order_id']);

$PayVector->setMD($_REQUEST['MD']);
$PayVector->setPaRES($_REQUEST['PaRes']);

$PayVector->Process();

?>