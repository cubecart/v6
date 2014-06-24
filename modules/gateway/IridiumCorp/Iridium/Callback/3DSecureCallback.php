<?php

// DO NOT EDIT BETWEEN THIS AND NEXT COMMENT
require_once 'modules/gateway/IridiumCorp/Iridium/Config.php';
// DO NOT EDIT BETWEEN THIS AND PREVIOUS COMMENT

$GLOBALS['config']->get('_module');

require_once dirname(dirname(__FILE__)) . '/Core/IridiumDirect.php';
$Iridium = new IridiumDirect("CubeCart");
$Iridium->setTransactionMethod(TransactionMethod::ThreeDSecureTransaction);

$Iridium->setDebugMode($this->module['testMode']);
$Iridium->setDebugEmailAddress($DeveloperEmailAddress);
//$Iridium->setDatabaseSupport(TRUE);

$Iridium->setPaymentProcessorFullDomain($PaymentProcessorFullDomain);
$Iridium->setTransactionType(TransactionType::Sale);

//$Iridium->setOriginCrossReference($params['gatewayid']);

if (!$this->module['testMode']) {
    $Iridium->setMerchantID($this->module['mid_test']);
    $Iridium->setPassword($this->module['pass_test']);
} else {
    $Iridium->setMerchantID($this->module['mid_prod']);
    $Iridium->setPassword($this->module['pass_prod']);
}

$Iridium->setOrderID($order_summary['cart_order_id']);
$Iridium->setOrderDescription("Order ID: " . $order_summary['cart_order_id']);

$Iridium->setMD($_REQUEST['MD']);
$Iridium->setPaRES($_REQUEST['PaRes']);

$Iridium->Process();

?>