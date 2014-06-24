<?php

require_once 'modules/gateway/PayVector/PayVector/Core/PayVector.php';

class Gateway {

    private $_config;
    private $_basket;
    private $_result_message;
    private $_url;
    private $_path;

    public $module;

    public function __construct($module = false, $basket = false) {

        $this -> _db = &$GLOBALS['db'];

        $this -> module = $module;
        $this -> _basket = &$GLOBALS['cart'] -> basket;
    }

    ##################################################

    public function transfer() {

        require_once 'modules/gateway/PayVector/PayVector/Config.php';

        switch($this -> module['mode']) {
            case 'hpf' :
                $transfer = array(
                    'action' => "https://mms." . $PaymentProcessorFullDomain . "Pages/PublicPages/PaymentForm.aspx",
                    'method' => 'post',
                    'target' => '_self',
                    'submit' => ''
                );
                break;
            case 'tr' :
                $transfer = array(
                    'action' => "https://mms." . $PaymentProcessorFullDomain . "Pages/PublicPages/TransparentRedirect.aspx",
                    'method' => 'post',
                    'target' => '_self',
                    'submit' => ''
                );
                break;
            default :
                $transfer = array(
                    'action' => "",
                    'method' => 'post',
                    'target' => '_self',
                    'submit' => ''
                );
                break;
        }

        return $transfer;
    }

    ##################################################

    public function repeatVariables() {
        return (isset($hidden)) ? $hidden : false;
    }

    public function fixedVariables() {

        $hidden['gateway'] = basename(dirname(__FILE__));

        switch($this -> module['mode']) {
            case "tr" :
                break;

            default :
                break;
        }

        return (isset($hidden)) ? $hidden : false;
    }

    public function call() {

        $return = null;

        $order = Order::getInstance();
        $cart_order_id = $this -> _basket['cart_order_id'];
        $order_summary = $order -> getSummary($cart_order_id);

        switch ($this -> module['mode']) {
            case 'hpf' :
                switch ($_REQUEST['mode']) {
                    case "receive" :
                        require_once (__DIR__ . "/PayVector/Callback/Hosted/ReceiveTransactionResult.php");
                        break;
                    case "display" :
                        require_once (__DIR__ . "/PayVector/Callback/Hosted/DisplayTransactionResult.php");
                        break;
                    default :
                        break;
                }
                break;

            case 'tr' :
                require_once (__DIR__ . "/PayVector/Callback/TransparentRedirectCallback.php");
                break;

            case 'api' :
                switch ($_REQUEST['mode']) {
                    case "3ds" :
                        require_once (__DIR__ . "/PayVector/Callback/3DSecureCallback.php");
                        break;
                    default :
                        break;
                }
                break;
            default :
                break;
        }

        if ($_REQUEST['mode'] != "receive" && !$PayVector -> HasActiveError()) {
            require __DIR__ . '/PayVector/HandleTransactionResults.php';
            $return = HandleTransactionResults($PayVector, __FUNCTION__, $order, $order_summary, $this);
        }

        return $return;
    }

    public function process() {

        require dirname(__FILE__) . '/PayVector/Config.php';

        $order = Order::getInstance();
        $cart_order_id = $this -> _basket['cart_order_id'];
        $order_summary = $order -> getSummary($cart_order_id);

        switch ($this -> module['mode']) {
            case 'hpf' :
                require_once dirname(__FILE__) . '/PayVector/Core/PayVectorHosted.php';
                $PayVector = new PayVectorHosted("CubeCart");
                $PayVector -> setHostedFormMethod(HostedFormMethod::Process);
                break;
            case 'tr' :
                require_once dirname(__FILE__) . '/PayVector/Core/PayVectorTransparentRedirect.php';
                $PayVector = new PayVectorTransparentRedirect("CubeCart");
                $PayVector -> setTransparentRedirectMethod(TransparentRedirectMethod::GetHiddenHashFields);
                $PayVector -> setHashMethod($this -> module['hpfHashMethod']);
                $PayVector -> setPreSharedKey($this -> module['hpfPreSharedKey']);
                $PayVector -> setCallbackURL('index.php?_a=gateway&mode=trInitialCallback&gateway=payvector');
                break;
            case 'api' :
                require_once dirname(__FILE__) . '/PayVector/Core/PayVectorDirect.php';
                $PayVector = new PayVectorDirect("CubeCart");
                break;
            default :
                break;
        }

        $PayVector -> setDebugMode($this -> module['testMode']);
        $PayVector -> setDebugEmailAddress($DeveloperEmailAddress);
        //$PayVector->setDatabaseSupport(TRUE);

        $PayVector -> setPaymentProcessorFullDomain($PaymentProcessorFullDomain);
        $PayVector -> setTransactionType(TransactionType::Sale);

        if ($this -> module['testMode']) {
            $PayVector -> setMerchantID($this -> module['mid_test']);
            $PayVector -> setPassword($this -> module['pass_test']);
        } else {
            $PayVector -> setMerchantID($this -> module['mid_prod']);
            $PayVector -> setPassword($this -> module['pass_prod']);
        }

        $PayVector -> setOrderID($this -> _basket['cart_order_id']);
        $PayVector -> setOrderDescription("Order ID: " . $PayVector -> getOrderID());

        $nAmountWithDecimals = $this -> _basket['total'];
        if ($nAmountWithDecimals == "0.00" || $nAmountWithDecimals == NULL || $CallingFunction == "payvector_storeremote") {
            $nAmountWithDecimals = 0.01;
        }
        $PayVector -> setTransactionAmountAndCurrency($nAmountWithDecimals, $GLOBALS['config'] -> get('config', 'default_currency'));

        if ($this -> module['mode'] != 'tr') {

            if ($this -> module['mode'] == 'api') {

                // $SuccessfulGatewayEntryPoint = null;
// 
                // $SuccessfulGatewayEntryPoint = $GLOBALS['db'] -> misc(PayVectorSQL::selectGEP_EntryPoint(10));
                // $SuccessfulGatewayEntryPoint = $SuccessfulGatewayEntryPoint[0]['GatewayEntryPoint'];
// 				
                // $PayVector -> setGatewayEntryPointToAttemptFirst($SuccessfulGatewayEntryPoint);

                switch ($_POST['CurrentTransactionType']) {
                    case 'cdt' :
                        $PayVector -> setTransactionMethod(TransactionMethod::CardDetailsTransaction);

                        $PayVector -> setAddress1($_POST['Address1']);
                        $PayVector -> setAddress2($_POST['Address2']);
                        $PayVector -> setCity($_POST['City']);
                        $PayVector -> setState($_POST['State']);
                        $PayVector -> setPostCode($_POST['Postcode']);
                        $PayVector -> setCountry($_POST['CountryCode']);
                        $PayVector -> setEmailAddress($_POST['EmailAddress']);
                        $PayVector -> setPhoneNumber($_POST['PhoneNumber']);

                        $PayVector -> setCardName($_POST['CardName']);
                        $PayVector -> setCardNumber($_POST['CardNumber']);

                        $PayVector -> setCardExpiryDateMonth(substr($_POST['ExpiryDateMonth'], 0, 2));
                        $PayVector -> setCardExpiryDateYear(substr($_POST['ExpiryDateYear'], -2));

                        $PayVector -> setCardStartDateMonth(substr($_POST['StartDateMonth'], 0, 2));
                        $PayVector -> setCardStartDateYear(substr($_POST['StartDateYear'], -2));

                        $PayVector -> setCardIssueNumber($_POST['CardIssueNumber']);

                        break;
                    case 'crt' :
                        $PayVector -> setTransactionMethod(TransactionMethod::CrossReferenceTransaction);
                        $PayVector -> setOriginCrossReference($_POST['CrossReference']);
						$PayVector -> setAddress1($this -> _basket['billing_address']['line1']);
		                $PayVector -> setAddress2($this -> _basket['billing_address']['line2']);
		                $PayVector -> setCity($this -> _basket['billing_address']['town']);
		                $PayVector -> setState($this -> _basket['billing_address']['state']);
		                $PayVector -> setPostCode($this -> _basket['billing_address']['postcode']);
		                $PayVector -> setCountry($this -> _basket['billing_address']['country_id']);
		                $PayVector -> setEmailAddress($this -> _basket['billing_address']['email']);
		                $PayVector -> setPhoneNumber($this -> _basket['billing_address']['phone']);

                        $crtDetails = $GLOBALS['db'] -> misc(PayVectorSQL::selectCRT_CrossReferenceDetails($order_summary['customer_id']));

                        if (is_array($crtDetails)) {

                            $crtDetails = $crtDetails[0];

                            if (isset($this -> module['mid_ca']) && isset($this -> module['pass_ca'])) {

                                $dtExpiryDate = $PayVector -> GetExpiryDate($crtDetails['ExpiryDateMonth'], $crtDetails['ExpiryDateYear']);

                                if ($dtExpiryDate['m'] != $crtDetails['ExpiryDateMonth'] && $dtExpiryDate['y'] != $crtDetails['ExpiryDateYear']) {

                                    $PayVector -> setCardExpiryDateMonth($dtExpiryDate['m'], 0, 2);
                                    $PayVector -> setCardExpiryDateYear($dtExpiryDate['y'], -2);

                                    // $PayVector -> setCardExpiryDateMonthOverride($dtExpiryDate['m']);
                                    // $PayVector -> setCardExpiryDateYearOverride($dtExpiryDate['y']);
                                }

                            }
                        }
                        break;
                }

                $PayVector -> setCardCV2($_POST['CV2']);
            } else {

                $PayVector -> setAddress1($this -> _basket['billing_address']['line1']);
                $PayVector -> setAddress2($this -> _basket['billing_address']['line2']);
                $PayVector -> setCity($this -> _basket['billing_address']['town']);
                $PayVector -> setState($this -> _basket['billing_address']['state']);
                $PayVector -> setPostCode($this -> _basket['billing_address']['postcode']);
                $PayVector -> setCountry($this -> _basket['billing_address']['country_id']);
                $PayVector -> setEmailAddress($this -> _basket['billing_address']['email']);
                $PayVector -> setPhoneNumber($this -> _basket['billing_address']['phone']);

                $PayVector -> setCardName("{$this->_basket['billing_address']['first_name']} {$this->_basket['billing_address']['last_name']}");

                $PayVector -> setHostedFormMethod(HostedFormMethod::Process);
                $PayVector -> setTransactionType(TransactionType::Sale);
                $PayVector -> setThreeDSecureOverridePolicy(TRUE);

                $PayVector -> setHostedFormReturnType(HostedFormReturnType::HTMLFormCode);

                $PayVector -> setHTML_IncludeFormTags(FALSE);
                $PayVector -> setHTML_IncludeSubmitButton(FALSE);

                $PayVector -> setHashMethod($this -> module['hpfHashMethod']);
                $PayVector -> setPreSharedKey($this -> module['hpfPreSharedKey']);
                $PayVector -> setResultDeliveryMethod($this -> module['hpfResultDeliveryMethod']);

                $PayVector -> setTransactionDateTime(date('Y-m-d H:i:s P'));

                $PayVector -> setEmailAddressEditable(true);
                $PayVector -> setPhoneNumberEditable(true);

                $PayVector -> setCV2Mandatory($this -> module['hpfCV2Mandatory']);
                $PayVector -> setAddress1Mandatory($this -> module['hpfAddress1Mandatory']);
                $PayVector -> setCityMandatory($this -> module['hpfCityMandatory']);
                $PayVector -> setPostCodeMandatory($this -> module['hpfPostCodeMandatory']);
                $PayVector -> setStateMandatory($this -> module['hpfStateMandatory']);
                $PayVector -> setCountryMandatory($this -> module['hpfCountryMandatory']);

                if ($this -> module['hpfResultDeliveryMethod'] != ResultDeliveryMethod::SERVER) {
                    $PayVector -> setServerResultURL(NULL);
                } else {
                    $PayVector -> setServerResultURL('index.php?_g=rm&type=gateway&cmd=call&module=payvector&mode=receive');
                    //$PayVector->setServerResultURL('modules/gateway/payvector/PayVector/Hosted/ReceiveTransactionResult.php');
                }

                if ($this -> module['hpfResultDeliveryMethod'] != ResultDeliveryMethod::SERVER) {
                    $PayVector -> setPaymentFormDisplayResult(NULL);
                } else {
                    $PayVector -> setPaymentFormDisplayResult(false);
                }

                // the callback URL on this site that will display the transaction
                // result to the customer
                // (always required unless ResultDeliveryMethod = "SERVER" and
                // PaymentFormDisplaysResult = "true")
                if ($this -> module['hpfResultDeliveryMethod'] == ResultDeliveryMethod::SERVER && $PayVector -> getPaymentFormDisplayResult() == true) {
                    $PayVector -> setCallbackURL(NULL);
                } else {
                    $PayVector -> setCallbackURL('index.php?_g=rm&type=gateway&cmd=call&module=payvector&mode=display');
                }
            }
        }
        $return = $PayVector -> Process();

        switch ($this -> module['mode']) {
            case 'api' :
                require __DIR__ . '/PayVector/HandleTransactionResults.php';
                $return = HandleTransactionResults($PayVector, __FUNCTION__, $order, $order_summary, $this);
                break;
            case 'tr' :
                // require __DIR__ . '/PayVector/HandleTransactionResults.php';
                // $return = HandleTransactionResults($PayVector, __FUNCTION__, $order, $order_summary);
                break;
            case 'hpf' :
                $return = '
                        <!DOCTYPE html>
                        <html>
                            <head>
                                <title>Launch Payer Authentication Page</title>
                                <script language="javascript">
                                    onload = function()
                                      {
                                          $(\'#gateway-transfer\').submit();
                                      }
                                </script>
                            </head>
                            <body>' . $return . '
                            </body>
                        </html>';
        }

        return $return;
    }

    ##################################################

    private function formatMonth($val) {
        return $val . " - " . strftime("%b", mktime(0, 0, 0, $val, 1, 2009));
    }

    public function form() {

        $order = Order::getInstance();
        $cart_order_id = $this -> _basket['cart_order_id'];
        $order_summary = $order -> getSummary($cart_order_id);

        switch ($this -> module['mode']) {

            case 'api' :
                if (isset($_POST['CardNumber'])) {

                    $return = $this -> process();

                    if ($GLOBALS['session'] -> get('DISPLAY_3DS', 'payvector')) {
                        return $return;
                    }

                } else {
                    if ($this -> module['crt']) {

                        $crtDetails = $GLOBALS['db'] -> misc(PayVectorSQL::selectCRT_CrossReferenceDetails($order_summary['customer_id']));

                        if (is_array($crtDetails)) {

                            for ($i = 0; $i < count($crtDetails); $i++) {

                                $date1 = new DateTime('now');
                                $date2 = new DateTime($crtDetails[$i]['TransactionDateTime']);
                                $date2 = $date2 -> add(new DateInterval("P1Y"));

                                if ($date1 < $date2) {

                                    $GLOBALS['smarty'] -> assign('DISPLAY_CRT', true);

                                    $smarty_data['CRT'][$crtDetails[$i]['CardLastFour']]['LastFour'] = $crtDetails[$i]['CardLastFour'];
                                    $smarty_data['CRT'][$crtDetails[$i]['CardLastFour']]['CrossReference'] = $crtDetails[$i]['CrossReference'];
                                    $smarty_data['CRT'][$crtDetails[$i]['CardLastFour']]['ExpiryDateMonth'] = $crtDetails[$i]['ExpiryDateMonth'];
                                    $smarty_data['CRT'][$crtDetails[$i]['CardLastFour']]['ExpiryDateYear'] = $crtDetails[$i]['ExpiryDateYear'];

                                } else {
                                    $GLOBALS['db'] -> misc(PayVectorSQL::deleteCRT_CardDetails($order_summary['customer_id'], $crtDetails[$i]['CardLastFour']));
                                }
                            }

                            if (is_array($smarty_data['CRT'])) {
                                $GLOBALS['smarty'] -> assign('CRT', $smarty_data['CRT']);
                            }
                        }
                    }
                }

                break;

            case 'hpf' :
                $return = $this -> process();
                break;

            case 'tr' :
                if (!isset($_REQUEST['mode'])) {
                    $return = $this -> process();
                } else {
                    $return = $this -> call();
                }
                break;

            default :
                break;
        }

        if ($this -> module['mode'] == 'api' || ($this -> module['mode'] == 'tr' && !isset($_REQUEST['mode']))) {

            // Display payment result message
            if (!empty($this -> _result_message)) {
                foreach ($this-> _result_message as $error) {
                    $GLOBALS['gui'] -> setError($error);
                }
            }

            //Show Expire Months
            $selectedMonth = (isset($_POST['ExpiryDateMonth'])) ? $_POST['ExpiryDateMonth'] : date('m');
            for ($i = 1; $i <= 12; ++$i) {
                $val = sprintf('%02d', $i);
                $smarty_data['card']['expiry']['months'][] = array(
                    'selected' => ($val == $selectedMonth) ? 'selected="selected"' : '',
                    'value' => $val,
                    'display' => $this -> formatMonth($val),
                );
            }

            ## Show Expire Years
            $thisYear = date("Y");
            $maxYear = $thisYear + 10;
            $selectedYear = isset($_POST['ExpiryDateYear']) ? $_POST['ExpiryDateYear'] : ($thisYear + 2);
            for ($i = $thisYear; $i <= $maxYear; ++$i) {
                $smarty_data['card']['expiry']['years'][] = array(
                    'selected' => ($i == $selectedYear) ? 'selected="selected"' : '',
                    'value' => $i,
                );
            }

            $GLOBALS['smarty'] -> assign('CARD', $smarty_data['card']);

            $smarty_data['customer'] = array(
                'name_on_card' => isset($_POST['cardName']) ? $_POST['cardName'] : $this -> _basket['billing_address']['first_name'] . " " . $this -> _basket['billing_address']['last_name'],
                'first_name' => isset($_POST['firstName']) ? $_POST['firstName'] : $this -> _basket['billing_address']['first_name'],
                'last_name' => isset($_POST['lastName']) ? $_POST['lastName'] : $this -> _basket['billing_address']['last_name'],
                'email' => isset($_POST['emailAddress']) ? $_POST['emailAddress'] : $this -> _basket['billing_address']['email'],
                'add1' => isset($_POST['addr1']) ? $_POST['addr1'] : $this -> _basket['billing_address']['line1'],
                'add2' => isset($_POST['addr2']) ? $_POST['addr2'] : $this -> _basket['billing_address']['line2'],
                'city' => isset($_POST['city']) ? $_POST['city'] : $this -> _basket['billing_address']['town'],
                'state' => isset($_POST['state']) ? $_POST['state'] : $this -> _basket['billing_address']['state'],
                'postcode' => isset($_POST['postcode']) ? $_POST['postcode'] : $this -> _basket['billing_address']['postcode']
            );

            $GLOBALS['smarty'] -> assign('CUSTOMER', $smarty_data['customer']);

            ## Country list
            $countries = $GLOBALS['db'] -> select('CubeCart_geo_country', false, false, array('name' => 'ASC'));
            if ($countries) {
                $currentIso = isset($_POST['country']) ? $_POST['country'] : $this -> _basket['billing_address']['country_iso'];
                foreach ($countries as $country) {
                    $country['selected'] = ($country['iso'] == $currentIso) ? 'selected="selected"' : '';
                    $smarty_data['countries'][] = $country;
                }
                $GLOBALS['smarty'] -> assign('COUNTRIES', $smarty_data['countries']);
            }

            // Include module language strings - use Language class
            $GLOBALS['language'] -> loadDefinitions("payvector", dirname(__FILE__) .'/'. 'language', 'module.definitions.xml');
            // Load other lang either customized ones
            $GLOBALS['language'] -> loadLanguageXML("payvector", '', dirname(__FILE__) .'/'. 'language');

            ## Check for custom template for module in skin folder
            $file_name = 'form.tpl';
            $form_file = $GLOBALS['gui'] -> getCustomModuleSkin('gateway', dirname(__FILE__), $file_name);
            $GLOBALS['gui'] -> changeTemplateDir($form_file);
            $return .= $GLOBALS['smarty'] -> fetch($file_name);

            $GLOBALS['gui'] -> changeTemplateDir();
        }
        return $return;
    }

}
