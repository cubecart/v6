<?php

// require_once (__DIR__."/../config.php");
// require_once (__DIR__."/../iridium.php");
// require_once (__DIR__."/PaymentFormHelper.php");
// DO NOT EDIT BETWEEN THIS AND PREVIOUS COMMENT

require_once 'modules/gateway/IridiumCorp/Iridium/Config.php';
require_once dirname(dirname(__FILE__)) . '/Core/IridiumTransparentRedirect.php';

$Iridium = new IridiumTransparentRedirect("CubeCart");
$Iridium -> setDebugMode($this -> module['testMode']);
$Iridium -> setDebugEmailAddress($DeveloperEmailAddress);

$Iridium -> setTransactionType(TransactionType::Sale);
$Iridium -> setPaymentProcessorFullDomain($PaymentProcessorFullDomain);

$Iridium -> setHashMethod($this -> module['hpfHashMethod']);
$Iridium -> setPreSharedKey($this -> module['hpfPreSharedKey']);

if (!$this -> module['testMode']) {
    $Iridium -> setMerchantID($this -> module['mid_test']);
    $Iridium -> setPassword($this -> module['pass_test']);
} else {
    $Iridium -> setMerchantID($this -> module['mid_prod']);
    $Iridium -> setPassword($this -> module['pass_prod']);
}

switch ($_REQUEST['mode']) {
    case 'trInitialCallback' :
        $Iridium -> setHostedTransactionResponse($_POST);
        $Iridium -> setTransparentRedirectMethod(TransparentRedirectMethod::ReceiveInitialRequestResults);
        $Iridium -> Process();
        break;
    case 'tr3DSCallback' :
        $GLOBALS['smarty'] -> assign('DISPLAY_3DS', true);

        if (isset($_POST['PaRes']) && isset($_POST['MD'])) {

            $GLOBALS['session'] -> set('MD', $_POST['MD'], 'iridiumcorp');
            $GLOBALS['session'] -> set('PaRes', $_POST['PaRes'], 'iridiumcorp');

            $Iridium -> setHostedTransactionResponse($_POST);

        } else {

            $_POST['MD'] = $GLOBALS['session'] -> get('MD', 'iridiumcorp');
            $_POST['PaRes'] = $GLOBALS['session'] -> get('PaRes', 'iridiumcorp');

            $Iridium -> setHostedTransactionResponse($_POST);
        }

        $Iridium -> setCallbackURL('index.php?_a=gateway&mode=tr3DSCallbackComplete&gateway=IridiumCorp');
        $Iridium -> setTransactionDateTime($GLOBALS['session'] -> get('TransactionDateTime', 'iridiumcorp'));
        $Iridium -> setTransparentRedirectMethod(TransparentRedirectMethod::Receive3DSPostAuthenticationResults);
        
        $Iridium->setHTML_IncludeFormTags(false);
        $Iridium->setHTML_IncludeSubmitButton(true);
        $Iridium->setHTMLForm_ButtonCaption("Proceed");
        
        $return = $Iridium -> Process();

        if (isset($_POST['PaRes']) && isset($_POST['MD'])) {
            $GLOBALS['session'] -> set('CrossReference', $_POST['MD'], 'iridiumcorp');
            $GLOBALS['session'] -> set('PaRes', $_POST['PaRes'], 'iridiumcorp');
            $GLOBALS['session'] -> set('MerchantID', $Iridium -> getMerchantID(), 'iridiumcorp');
            $GLOBALS['session'] -> set('HashDigest', $Iridium -> getHashDigest(), 'iridiumcorp');
            $GLOBALS['session'] -> set('CallbackURL', $Iridium -> getCallbackURL(), 'iridiumcorp');
        }
        break;
    case 'tr3DSCallbackComplete' :
        $Iridium -> setHostedTransactionResponse($_POST);
        $Iridium -> setTransparentRedirectMethod(TransparentRedirectMethod::ReceivePaymentComplete);
        $Iridium -> Process();
        break;
}
