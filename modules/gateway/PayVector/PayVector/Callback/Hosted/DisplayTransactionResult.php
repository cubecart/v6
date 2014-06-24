<?php

// DO NOT EDIT BETWEEN THIS AND NEXT COMMENT
require (dirname(dirname(dirname(__FILE__))) . "/Config.php");
require_once (dirname(dirname(dirname(__FILE__))) . "/Core/PayVector.php");
require_once (dirname(dirname(dirname(__FILE__))) . "/Core/PayVectorHosted.php");
// DO NOT EDIT BETWEEN THIS AND PREVIOUS COMMENT

$PayVector = new PayVectorHosted("CubeCart");
$PayVector -> setDebugMode($this -> module['testMode']);
$PayVector -> setDebugEmailAddress($DeveloperEmailAddress);

$PayVector -> setTransactionType(TransactionType::Sale);
$PayVector -> setHostedFormMethod(HostedFormMethod::DisplayResults);
$PayVector -> setPaymentProcessorFullDomain($PaymentProcessorFullDomain);

$PayVector -> setHashMethod($this -> module['hpfHashMethod']);
$PayVector -> setPreSharedKey($this -> module['hpfPreSharedKey']);
$PayVector -> setResultDeliveryMethod($this -> module['hpfResultDeliveryMethod']);

if (!$this -> module['testMode']) {
    $PayVector -> setMerchantID($this -> module['mid_test']);
    $PayVector -> setPassword($this -> module['pass_test']);
} else {
    $PayVector -> setMerchantID($this -> module['mid_prod']);
    $PayVector -> setPassword($this -> module['pass_prod']);
}

if ($this -> module['hpfResultDeliveryMethod'] == ResultDeliveryMethod::POST) {
    $PayVector -> setHostedTransactionResponse($_POST);
} else {
    $PayVector -> setHostedTransactionResponse($_GET);
}
$results = $PayVector -> Process();

//$results = HandleTransactionResults($PayVector, 'DisplayTransactionResult');

if ($PayVector -> HasActiveError() && $PayVector -> getDebugMode() && $PayVector -> getDebugEmailAddress() != null) {
    mail($DeveloperEmailAddress, "DisplayTransactionResult", print_r($PayVector -> getErrorMessage(), 1));
}

return $results;
?>