<?php

// You should put your code that does any post transaction tasks
// (e.g. updates the order object, sends the customer an email etc) in this function
function HandleTransactionResults($PayVectorClassObject, $CallingFunction, Order $order, $order_summary, Gateway $gateway) {

    $boNotify = true;
    $szNotify = null;
    $redirectto = null;
    $return = false;

    $szIntegrationMethod = $PayVectorClassObject -> getIntegrationMethod();

    $transData['gateway'] = 'PayVector (' . strtoupper($szIntegrationMethod) . ')';

    if ($PayVectorClassObject instanceof PayVectorDirect || ($PayVectorClassObject instanceof PayVectorHosted && $PayVectorClassObject -> getHostedFormMethod() != HostedFormMethod::Process) || $PayVectorClassObject instanceof PayVectorTransparentRedirect) {

        $TransactionResult = $PayVectorClassObject -> getTransactionResult();

        if (!$PayVectorClassObject -> TransactionProcessed()) {

            $status = "Error";
            $szNotify = "Payment Processing Failed  =>  ";

            $errors = $PayVectorClassObject -> getErrorMessage();
            
            for ($LoopIndex = 0; $LoopIndex < count($errors); $LoopIndex++) {
                $szNotify .= "  - " . $errors[$LoopIndex];
            }

        } else {

            switch ($gateway->module['mode']) {
                case 'api' :
                    $CrossReference = $PayVectorClassObject -> getTransactionOutputData() -> getCrossReference();
                    break;
                case 'hpf' :
                    break;
                case 'tr' :
                    $CrossReference = $TransactionResult -> getCrossReference();
                    break;
            }

            if ($_REQUEST['mode'] == "tr3DSCallback") {

                $status = "Pending";
                $szNotify = "3D Secure Authentication Required";

                $GLOBALS['smarty'] -> assign('DISPLAY_3DS', true);

                $return = '
                            <script type="text/javascript">
                            //<![CDATA[
                                onload = function()
                                {
                                    $(\'#gateway-transfer\').submit();
                                }
                            //]]>
                            </script>
                            <h2>3D Secure Authentication Required</h2>
                            <h3>Please click Submit to continue the processing of your transaction.</h3>
                            ' . $PayVectorClassObject -> get3DSPostAuthenticationFields();

            } else {

                switch ($TransactionResult->getStatusCode()) {
                    case 0 :
                        $return = true;

                        $status = 'Approved';
                        $szNotify = "Payment Processed Successfully  =>  {$TransactionResult->getMessage()}";

                        switch ($gateway->module['mode']) {
                            case 'api' :
                                if ($gateway -> module['crt']) {

                                    $OriginCrossReference = $GLOBALS['db'] -> misc(PayVectorSQL::selectCRT_CrossReference($order_summary['customer_id']));

                                    if (!$OriginCrossReference) {
                                        $results = $GLOBALS['db'] -> misc(PayVectorSQL::insertCRT_NewCardDetailsTransaction($order_summary['customer_id'], $CrossReference, $PayVectorClassObject -> getCardLastFour(), $PayVectorClassObject -> getCardExpiryDateMonth(), $PayVectorClassObject -> getCardExpiryDateYear(), $PayVectorClassObject -> getTransactionDateTime(), FALSE));
                                    } else {
                                        switch($PayVectorClassObject->getTransactionMethod()) {
                                            case TransactionMethod::CardDetailsTransaction :
                                                $results = $GLOBALS['db'] -> misc(PayVectorSQL::updateCRT_CardDetails($order_summary['customer_id'], $CrossReference, $PayVectorClassObject -> getCardLastFour(), $PayVectorClassObject -> getCardExpiryDateMonth(), $PayVectorClassObject -> getCardExpiryDateYear(), $PayVectorClassObject -> getTransactionDateTime(), FALSE));
                                                break;
                                            case TransactionMethod::CrossReferenceTransaction :
                                                $results = $GLOBALS['db'] -> misc(PayVectorSQL::updateCRT_TransactionDetails($order_summary['customer_id'], $PayVectorClassObject -> getOriginCrossReference(), $CrossReference, $PayVectorClassObject -> getTransactionDateTime(), FALSE));
                                                break;
                                            case TransactionMethod::ThreeDSecureTransaction :
                                                $results = $GLOBALS['db'] -> misc(PayVectorSQL::updateCRT_TransactionDetails($order_summary['customer_id'], $PayVectorClassObject -> getMD(), $CrossReference, $PayVectorClassObject -> getTransactionDateTime(), FALSE));
                                                $results = $GLOBALS['db'] -> misc(PayVectorSQL::deleteCRT_CardDetailsAllExceptSpecificCrossReference($order_summary['customer_id'], $CrossReference));
                                                break;
                                        }
                                    }
                                }
                                break;
                        }

                        break;
                    case 3 :
                        $status = "Pending";
                        $szNotify = "3D Secure Authentication Required";

                        $Is3DSecureEnrolled = TRUE;

                        ## Display 3D-Secure screen
                        if ($PayVectorClassObject -> getIntegrationMethod() == IntegrationMethod::DirectAPI) {

                            if ($gateway -> module['crt']) {
                                $results = $GLOBALS['db'] -> misc(PayVectorSQL::deleteCRT_CardDetailsSpecific($order_summary['customer_id'], $PayVectorClassObject -> getCardLastFour()));
                                $results = $GLOBALS['db'] -> misc(PayVectorSQL::insertCRT_NewCardDetailsTransaction($order_summary['customer_id'], $CrossReference, $PayVectorClassObject -> getCardLastFour(), $PayVectorClassObject -> getCardExpiryDateMonth(), $PayVectorClassObject -> getCardExpiryDateYear(), $PayVectorClassObject -> getTransactionDateTime(), TRUE));
                            }

                            $GLOBALS['session'] -> set('ACSUrl', $PayVectorClassObject -> getTransactionOutputData() -> getThreeDSecureOutputData() -> getACSURL(), 'payvector');
                            $GLOBALS['session'] -> set('PaREQ', $PayVectorClassObject -> getTransactionOutputData() -> getThreeDSecureOutputData() -> getPaREQ(), 'payvector');
                            $GLOBALS['session'] -> set('TermUrl', $GLOBALS['storeURL'] . '/index.php?_g=rm&type=gateway&cmd=call&module=PayVector&mode=3ds', 'payvector');
                        } else {
                            $GLOBALS['session'] -> set('ACSUrl', $TransactionResult -> getACSUrl(), 'payvector');
                            $GLOBALS['session'] -> set('PaREQ', $TransactionResult -> getPaREQ(), 'payvector');
                            $GLOBALS['session'] -> set('TermUrl', $GLOBALS['storeURL'] . '/index.php?_a=gateway&gateway=PayVector&mode=tr3DSCallback', 'payvector');
                        }

                        $GLOBALS['session'] -> set('CrossReference', $CrossReference, 'payvector');
                        $GLOBALS['session'] -> set('TransactionDateTime', $_REQUEST['TransactionDateTime'], 'payvector');

                        $GLOBALS['smarty'] -> assign('DISPLAY_3DS', true);
                        $GLOBALS['session'] -> set('DISPLAY_3DS', true, 'payvector');

                        ## Check for custom template for module in skin folder
                        $file_name = 'form.tpl';
                        $form_file = $GLOBALS['gui'] -> getCustomModuleSkin('gateway', dirname(dirname(__FILE__)), $file_name);
                        $GLOBALS['gui'] -> changeTemplateDir($form_file);
                        $return = $GLOBALS['smarty'] -> fetch($file_name);
                        $GLOBALS['gui'] -> changeTemplateDir();

                        break;
                    case 5 :
                        $status = 'Declined';
                        $szNotify = "Payment Processing Failed  =>  {$TransactionResult->getMessage()}";

                        break;
                    case 20 :
                        if ($TransactionResult -> getPreviousTransactionResult() -> getStatusCode() -> getValue() == 0) {

                            $return = TRUE;

                            $status = "Approved";
                            $szNotify = "Payment Processed Successfully  =>  {$TransactionResult->getPreviousTransactionResult() -> getMessage()}";

                            switch ($gateway->module['mode']) {
                                case 'api' :
                                    if ($gateway -> module['crt']) {

                                        $OriginCrossReference = $GLOBALS['db'] -> misc(PayVectorSQL::selectCRT_CrossReference($order_summary['customer_id']));

                                        if (!$OriginCrossReference) {
                                            $results = $GLOBALS['db'] -> misc(PayVectorSQL::insertCRT_NewCardDetailsTransaction($order_summary['customer_id'], $CrossReference, $PayVectorClassObject -> getCardLastFour(), $PayVectorClassObject -> getCardExpiryDateMonth(), $PayVectorClassObject -> getCardExpiryDateYear(), $PayVectorClassObject -> getTransactionDateTime()));
                                        } else {
                                            switch($_POST['TransactionType']) {
                                                case 'cdt' :
                                                    $results = $GLOBALS['db'] -> misc(PayVectorSQL::updateCRT_CardDetails($order_summary['customer_id'], $CrossReference, $PayVectorClassObject -> getCardLastFour(), $PayVectorClassObject -> getCardExpiryDateMonth(), $PayVectorClassObject -> getCardExpiryDateYear(), $PayVectorClassObject -> getTransactionDateTime()));
                                                    break;
                                                case 'crt' :
                                                    $results = $GLOBALS['db'] -> misc(PayVectorSQL::updateCRT_TransactionDetails($order_summary['customer_id'], $PayVectorClassObject -> getOriginCrossReference(), $CrossReference, $PayVectorClassObject -> getTransactionDateTime()));
                                                    break;
                                            }
                                        }
                                    }
                                    break;
                            }
                        } else {

                            $status = 'Error';
                            $szNotify = "Payment Processing Failed  =>  {$TransactionResult->getMessage()}";
                        }
                        break;
                    case 30 :
                        $status = 'Error';
                        $szNotify = "Payment Processing Failed  => ";

                        if ($TransactionResult -> getErrorMessages() -> getCount() > 0) {

                            for ($LoopIndex = 0; $LoopIndex < $TransactionResult -> getErrorMessages() -> getCount(); $LoopIndex++) {
                                $szNotify .= "  - " . $TransactionResult -> getErrorMessages() -> getAt($LoopIndex);
                            }
                        }

                        break;
                    default :
                        $status = 'Error';

                        $szNotify = "Payment Processing Failed  => ";
                        if ($TransactionResult -> getErrorMessages() -> getCount() > 0) {

                            for ($LoopIndex = 0; $LoopIndex < $TransactionResult -> getErrorMessages() -> getCount(); $LoopIndex++) {
                                $szNotify .= "  - " . $TransactionResult -> getErrorMessages() -> getAt($LoopIndex);
                            }
                        }
                        break;
                }
            }

            $transData['status'] = $status;
            $transData['notes'] = $szNotify;
            $transData['trans_id'] = $CrossReference;
            $transData['order_id'] = $order_summary['cart_order_id'];
            $transData['amount'] = $PayVectorClassObject -> getAmountDecimalised();
            $transData['customer_id'] = $order_summary['customer_id'];
            $transData['extra'] = '';
            $order -> logTransaction($transData);

        }

        $redirect_to = null;

        switch ($status) {
            case "Approved" :
                $order -> orderStatus(Order::ORDER_PROCESS, $transData['order_id']);
                $order -> paymentStatus(Order::PAYMENT_SUCCESS, $transData['order_id']);

                $redirect_to = 'complete';

                $GLOBALS['gui'] -> setNotify($szNotify);

                break;

            case "Declined" :
            case "Error" :
                $order -> orderStatus(Order::ORDER_PROCESS, $transData['order_id']);
                $order -> paymentStatus(Order::PAYMENT_DECLINE, $transData['order_id']);

                $redirect_to = 'gateway';
                $GLOBALS['gui'] -> setError($szNotify);

                break;

            case "Pending" :
                $order -> orderStatus(Order::ORDER_PENDING, $transData['order_id']);
                $order -> paymentStatus(Order::PAYMENT_PENDING, $transData['order_id']);

                break;
        }

    } else {

    }

    if ($redirect_to == null) {
        return $return;
    } else {

        $redirect_to = currentPage(array(
            '_g',
            'type',
            'cmd',
            'module',
            'mode',
            'gateway'
        ), array('_a' => $redirect_to));
        $GLOBALS['session'] -> delete('', 'payvector');

        if ($_REQUEST['mode'] == "3ds") {
            echo sprintf('<script type="text/javascript">self.parent.location=\'%s\'</script>', $redirect_to);
            echo sprintf('<noscript><a href="%s" target="_parent">Please click to continue</a></noscript>', $redirect_to);
        } else {
            httpredir($redirect_to);
        }
    }

    return $return;
}
?>
