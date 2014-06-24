<?php

// require_once (__DIR__."/../config.php");
// require_once (__DIR__."/../iridium.php");
// require_once (__DIR__."/PaymentFormHelper.php");
// DO NOT EDIT BETWEEN THIS AND PREVIOUS COMMENT

require_once 'modules/gateway/PayVector/PayVector/Config.php';
require_once dirname(dirname(__FILE__)) . '/Core/PayVectorTransparentRedirect.php';

$PayVector = new PayVectorTransparentRedirect("CubeCart");
$PayVector -> setDebugMode($this -> module['testMode']);
$PayVector -> setDebugEmailAddress($DeveloperEmailAddress);

$PayVector -> setTransactionType(TransactionType::Sale);
$PayVector -> setPaymentProcessorFullDomain($PaymentProcessorFullDomain);

$PayVector -> setHashMethod($this -> module['hpfHashMethod']);
$PayVector -> setPreSharedKey($this -> module['hpfPreSharedKey']);

if (!$this -> module['testMode']) {
    $PayVector -> setMerchantID($this -> module['mid_test']);
    $PayVector -> setPassword($this -> module['pass_test']);
} else {
    $PayVector -> setMerchantID($this -> module['mid_prod']);
    $PayVector -> setPassword($this -> module['pass_prod']);
}

switch ($_REQUEST['mode']) {
    case 'trInitialCallback' :
        $PayVector -> setHostedTransactionResponse($_POST);
        $PayVector -> setTransparentRedirectMethod(TransparentRedirectMethod::ReceiveInitialRequestResults);
        $PayVector -> Process();
        break;
    case 'tr3DSCallback' :
        $GLOBALS['smarty'] -> assign('DISPLAY_3DS', true);

        if (isset($_POST['PaRes']) && isset($_POST['MD'])) {

            $GLOBALS['session'] -> set('MD', $_POST['MD'], 'payvector');
            $GLOBALS['session'] -> set('PaRes', $_POST['PaRes'], 'payvector');

            $PayVector -> setHostedTransactionResponse($_POST);

        } else {

            $_POST['MD'] = $GLOBALS['session'] -> get('MD', 'payvector');
            $_POST['PaRes'] = $GLOBALS['session'] -> get('PaRes', 'payvector');

            $PayVector -> setHostedTransactionResponse($_POST);
        }

        $PayVector -> setCallbackURL('index.php?_a=gateway&mode=tr3DSCallbackComplete&gateway=PayVector');
        $PayVector -> setTransactionDateTime($GLOBALS['session'] -> get('TransactionDateTime', 'payvector'));
        $PayVector -> setTransparentRedirectMethod(TransparentRedirectMethod::Receive3DSPostAuthenticationResults);
        
        $PayVector->setHTML_IncludeFormTags(false);
        $PayVector->setHTML_IncludeSubmitButton(true);
        $PayVector->setHTMLForm_ButtonCaption("Proceed");
        
        $return = $PayVector -> Process();

        if (isset($_POST['PaRes']) && isset($_POST['MD'])) {
            $GLOBALS['session'] -> set('CrossReference', $_POST['MD'], 'payvector');
            $GLOBALS['session'] -> set('PaRes', $_POST['PaRes'], 'payvector');
            $GLOBALS['session'] -> set('MerchantID', $PayVector -> getMerchantID(), 'payvector');
            $GLOBALS['session'] -> set('HashDigest', $PayVector -> getHashDigest(), 'payvector');
            $GLOBALS['session'] -> set('CallbackURL', $PayVector -> getCallbackURL(), 'payvector');
        }
        break;
    case 'tr3DSCallbackComplete' :
        $PayVector -> setHostedTransactionResponse($_POST);
        $PayVector -> setTransparentRedirectMethod(TransparentRedirectMethod::ReceivePaymentComplete);
        $PayVector -> Process();
        break;
}
