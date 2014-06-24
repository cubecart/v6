<?php

// DO NOT EDIT BETWEEN THIS AND NEXT COMMENT
require (dirname(dirname(dirname(__FILE__))) . "/Config.php");
require_once (dirname(dirname(dirname(__FILE__))) . "/Core/Iridium.php");
require_once (dirname(dirname(dirname(__FILE__))) . "/Core/IridiumHosted.php");
// DO NOT EDIT BETWEEN THIS AND PREVIOUS COMMENT

$Iridium = new IridiumHosted("CubeCart");
$Iridium -> setDebugMode($this -> module['testMode']);
$Iridium -> setDebugEmailAddress($DeveloperEmailAddress);

$Iridium -> setTransactionType(TransactionType::Sale);
$Iridium -> setHostedFormMethod(HostedFormMethod::DisplayResults);
$Iridium -> setPaymentProcessorFullDomain($PaymentProcessorFullDomain);

$Iridium -> setHashMethod($this -> module['hpfHashMethod']);
$Iridium -> setPreSharedKey($this -> module['hpfPreSharedKey']);
$Iridium -> setResultDeliveryMethod($this -> module['hpfResultDeliveryMethod']);

if (!$this -> module['testMode']) {
    $Iridium -> setMerchantID($this -> module['mid_test']);
    $Iridium -> setPassword($this -> module['pass_test']);
} else {
    $Iridium -> setMerchantID($this -> module['mid_prod']);
    $Iridium -> setPassword($this -> module['pass_prod']);
}

if ($this -> module['hpfResultDeliveryMethod'] == ResultDeliveryMethod::POST) {
    $Iridium -> setHostedTransactionResponse($_POST);
} else {
    $Iridium -> setHostedTransactionResponse($_GET);
}
$results = $Iridium -> Process();

//$results = HandleTransactionResults($Iridium, 'DisplayTransactionResult');

if ($Iridium -> HasActiveError() && $Iridium -> getDebugMode() && $Iridium -> getDebugEmailAddress() != null) {
    mail($DeveloperEmailAddress, "DisplayTransactionResult", print_r($Iridium -> getErrorMessage(), 1));
}

return $results;
?>