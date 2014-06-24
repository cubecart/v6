<?php

// DO NOT EDIT BETWEEN THIS AND NEXT COMMENT
$nOutputStatusCode    = 0;
$szOutputMessage      = "RECEIVED";
$szUpdateOrderMessage = "";
$boErrorOccurred      = false;

require (dirname(dirname(dirname(__FILE__))) . "/Config.php");
require_once (dirname(dirname(dirname(__FILE__))) ."/Core/PayVector.php");
require_once (dirname(dirname(dirname(__FILE__))) ."/Core/PayVectorHosted.php");
// DO NOT EDIT BETWEEN THIS AND PREVIOUS COMMENT

$PayVector = new PayVectorHosted("CubeCart");
$PayVector->setDebugMode($this->_module['testMode']);
$PayVector->setDebugEmailAddress($DeveloperEmailAddress);

$PayVector->setTransactionType(TransactionType::Sale);
$PayVector->setHostedFormMethod(HostedFormMethod::ReceiveResults);
$PayVector->setPaymentProcessorFullDomain($PaymentProcessorFullDomain);

$PayVector->setHashMethod($this->module['hpfHashMethod']);
$PayVector->setPreSharedKey($this->module['hpfPreSharedKey']);
$PayVector->setResultDeliveryMethod($this->module['hpfResultDeliveryMethod']);

if (!$this->module['testMode']) {
    $PayVector->setMerchantID($this->module['mid_test']);
    $PayVector->setPassword($this->module['pass_test']);
} else {
    $PayVector->setMerchantID($this->module['mid_prod']);
    $PayVector->setPassword($this->module['pass_prod']);
}
$PayVector->setHostedTransactionResponse($_POST);

if (!$PayVector->Process()) {
    $nOutputStatusCode = 30;
    $szOutputMessage   = $PayVector->getErrorMessage();
    
    if ($PayVector -> HasActiveError() && $PayVector -> getDebugMode() && $PayVector -> getDebugEmailAddress() != null) {
        mail($DeveloperEmailAddress, "DisplayTransactionResult", print_r($szOutputMessage, 1));
    }
}
echo("StatusCode=" . $nOutputStatusCode . "&Message=" . print_r($szOutputMessage, 1));