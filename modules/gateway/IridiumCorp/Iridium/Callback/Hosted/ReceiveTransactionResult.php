<?php

// DO NOT EDIT BETWEEN THIS AND NEXT COMMENT
$nOutputStatusCode    = 0;
$szOutputMessage      = "RECEIVED";
$szUpdateOrderMessage = "";
$boErrorOccurred      = false;

require (dirname(dirname(dirname(__FILE__))) . "/Config.php");
require_once (dirname(dirname(dirname(__FILE__))) ."/Core/Iridium.php");
require_once (dirname(dirname(dirname(__FILE__))) ."/Core/IridiumHosted.php");
// DO NOT EDIT BETWEEN THIS AND PREVIOUS COMMENT

$Iridium = new IridiumHosted("CubeCart");
$Iridium->setDebugMode($this->_module['testMode']);
$Iridium->setDebugEmailAddress($DeveloperEmailAddress);

$Iridium->setTransactionType(TransactionType::Sale);
$Iridium->setHostedFormMethod(HostedFormMethod::ReceiveResults);
$Iridium->setPaymentProcessorFullDomain($PaymentProcessorFullDomain);

$Iridium->setHashMethod($this->module['hpfHashMethod']);
$Iridium->setPreSharedKey($this->module['hpfPreSharedKey']);
$Iridium->setResultDeliveryMethod($this->module['hpfResultDeliveryMethod']);

if (!$this->module['testMode']) {
    $Iridium->setMerchantID($this->module['mid_test']);
    $Iridium->setPassword($this->module['pass_test']);
} else {
    $Iridium->setMerchantID($this->module['mid_prod']);
    $Iridium->setPassword($this->module['pass_prod']);
}
$Iridium->setHostedTransactionResponse($_POST);

if (!$Iridium->Process()) {
    $nOutputStatusCode = 30;
    $szOutputMessage   = $Iridium->getErrorMessage();
    
    if ($Iridium -> HasActiveError() && $Iridium -> getDebugMode() && $Iridium -> getDebugEmailAddress() != null) {
        mail($DeveloperEmailAddress, "DisplayTransactionResult", print_r($szOutputMessage, 1));
    }
}
echo("StatusCode=" . $nOutputStatusCode . "&Message=" . print_r($szOutputMessage, 1));