<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */

require_once 'modules/gateway/IridiumCorp/Iridium/Core/Iridium.php';

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

        if ($this -> module['mode'] == 'api') {

            if (!$GLOBALS['db'] -> misc(IridiumSQL::TableExists(IridiumSQL::tblGEP_EntryPoints))) {
                $GLOBALS['db'] -> misc(IridiumSQL::createGEP_EntryPoints());
            }
        }
        if (!$GLOBALS['db'] -> misc(IridiumSQL::TableExists(IridiumSQL::tblCRT_CrossReference))) {
            $GLOBALS['db'] -> misc(IridiumSQL::createCRT_CrossReference());
        }
    }

    ##################################################

    public function transfer() {

        require_once 'modules/gateway/IridiumCorp/Iridium/Config.php';

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
                        require_once (__DIR__ . "/Iridium/Callback/Hosted/ReceiveTransactionResult.php");
                        break;
                    case "display" :
                        require_once (__DIR__ . "/Iridium/Callback/Hosted/DisplayTransactionResult.php");
                        break;
                    default :
                        break;
                }
                break;

            case 'tr' :
                require_once (__DIR__ . "/Iridium/Callback/TransparentRedirectCallback.php");
                break;

            case 'api' :
                switch ($_REQUEST['mode']) {
                    case "3ds" :
                        require_once (__DIR__ . "/Iridium/Callback/3DSecureCallback.php");
                        break;
                    default :
                        break;
                }
                break;
            default :
                break;
        }

        if ($_REQUEST['mode'] != "receive" && !$Iridium -> HasActiveError()) {
            require __DIR__ . '/Iridium/HandleTransactionResults.php';
            $return = HandleTransactionResults($Iridium, __FUNCTION__, $order, $order_summary, $this);
        }

        return $return;
    }

    public function process() {

        require dirname(__FILE__) . '/Iridium/Config.php';

        $order = Order::getInstance();
        $cart_order_id = $this -> _basket['cart_order_id'];
        $order_summary = $order -> getSummary($cart_order_id);

        switch ($this -> module['mode']) {
            case 'hpf' :
                require_once dirname(__FILE__) . '/Iridium/Core/IridiumHosted.php';
                $Iridium = new IridiumHosted("CubeCart");
                $Iridium -> setHostedFormMethod(HostedFormMethod::Process);
                break;
            case 'tr' :
                require_once dirname(__FILE__) . '/Iridium/Core/IridiumTransparentRedirect.php';
                $Iridium = new IridiumTransparentRedirect("CubeCart");
                $Iridium -> setTransparentRedirectMethod(TransparentRedirectMethod::GetHiddenHashFields);
                $Iridium -> setHashMethod($this -> module['hpfHashMethod']);
                $Iridium -> setPreSharedKey($this -> module['hpfPreSharedKey']);
                $Iridium -> setCallbackURL('index.php?_a=gateway&mode=trInitialCallback&gateway=IridiumCorp');
                break;
            case 'api' :
                require_once dirname(__FILE__) . '/Iridium/Core/IridiumDirect.php';
                $Iridium = new IridiumDirect("CubeCart");
                break;
            default :
                break;
        }

        $Iridium -> setDebugMode($this -> module['testMode']);
        $Iridium -> setDebugEmailAddress($DeveloperEmailAddress);
        //$Iridium->setDatabaseSupport(TRUE);

        $Iridium -> setPaymentProcessorFullDomain($PaymentProcessorFullDomain);
        $Iridium -> setTransactionType(TransactionType::Sale);

        if ($this -> module['testMode']) {
            $Iridium -> setMerchantID($this -> module['mid_test']);
            $Iridium -> setPassword($this -> module['pass_test']);
        } else {
            if (isset($this -> module['mid_ca']) && isset($this -> module['pass_ca'])) {
                $Iridium -> setMerchantID($this -> module['mid_ca']);
                $Iridium -> setPassword($this -> module['pass_ca']);
            } else {
                $Iridium -> setMerchantID($this -> module['mid_prod']);
                $Iridium -> setPassword($this -> module['pass_prod']);
            }
        }

        $Iridium -> setOrderID($this -> _basket['cart_order_id']);
        $Iridium -> setOrderDescription("Order ID: " . $Iridium -> getOrderID());

        $nAmountWithDecimals = $this -> _basket['total'];
        if ($nAmountWithDecimals == "0.00" || $nAmountWithDecimals == NULL || $CallingFunction == "iridium_storeremote") {
            $nAmountWithDecimals = 0.01;
        }
        $Iridium -> setTransactionAmountAndCurrency($nAmountWithDecimals, $GLOBALS['config'] -> get('config', 'default_currency'));

        if ($this -> module['mode'] != 'tr') {

            if ($this -> module['mode'] == 'api') {

                $SuccessfulGatewayEntryPoint = null;

                $SuccessfulGatewayEntryPoint = $GLOBALS['db'] -> misc(IridiumSQL::selectGEP_EntryPoint(10));
                $SuccessfulGatewayEntryPoint = $SuccessfulGatewayEntryPoint[0]['GatewayEntryPoint'];

                $Iridium -> setGatewayEntryPointToAttemptFirst($SuccessfulGatewayEntryPoint);

                switch ($_POST['CurrentTransactionType']) {
                    case 'cdt' :
                        $Iridium -> setTransactionMethod(TransactionMethod::CardDetailsTransaction);

                        $Iridium -> setAddress1($_POST['Address1']);
                        $Iridium -> setAddress2($_POST['Address2']);
                        $Iridium -> setCity($_POST['City']);
                        $Iridium -> setState($_POST['State']);
                        $Iridium -> setPostCode($_POST['Postcode']);
                        $Iridium -> setCountry($_POST['CountryCode']);
                        $Iridium -> setEmailAddress($_POST['EmailAddress']);
                        $Iridium -> setPhoneNumber($_POST['PhoneNumber']);

                        $Iridium -> setCardName($_POST['CardName']);
                        $Iridium -> setCardNumber($_POST['CardNumber']);

                        $Iridium -> setCardExpiryDateMonth(substr($_POST['ExpiryDateMonth'], 0, 2));
                        $Iridium -> setCardExpiryDateYear(substr($_POST['ExpiryDateYear'], -2));

                        $Iridium -> setCardStartDateMonth(substr($_POST['StartDateMonth'], 0, 2));
                        $Iridium -> setCardStartDateYear(substr($_POST['StartDateYear'], -2));

                        $Iridium -> setCardIssueNumber($_POST['CardIssueNumber']);

                        break;
                    case 'crt' :
                        $Iridium -> setTransactionMethod(TransactionMethod::CrossReferenceTransaction);
                        $Iridium -> setOriginCrossReference($_POST['CrossReference']);

                        $crtDetails = $GLOBALS['db'] -> misc(IridiumSQL::selectCRT_CrossReferenceDetails($order_summary['customer_id']));

                        if (is_array($crtDetails)) {

                            $crtDetails = $crtDetails[0];

                            if (isset($this -> module['mid_ca']) && isset($this -> module['pass_ca'])) {

                                $dtExpiryDate = $Iridium -> GetExpiryDate($crtDetails['ExpiryDateMonth'], $crtDetails['ExpiryDateYear']);

                                if ($dtExpiryDate['m'] != $crtDetails['ExpiryDateMonth'] && $dtExpiryDate['y'] != $crtDetails['ExpiryDateYear']) {

                                    $Iridium -> setCardExpiryDateMonth($dtExpiryDate['m'], 0, 2);
                                    $Iridium -> setCardExpiryDateYear($dtExpiryDate['y'], -2);

                                    // $Iridium -> setCardExpiryDateMonthOverride($dtExpiryDate['m']);
                                    // $Iridium -> setCardExpiryDateYearOverride($dtExpiryDate['y']);
                                }

                            }
                        }
                        break;
                }

                $Iridium -> setCardCV2($_POST['CV2']);
            } else {

                $Iridium -> setAddress1($this -> _basket['billing_address']['line1']);
                $Iridium -> setAddress2($this -> _basket['billing_address']['line2']);
                $Iridium -> setCity($this -> _basket['billing_address']['town']);
                $Iridium -> setState($this -> _basket['billing_address']['state']);
                $Iridium -> setPostCode($this -> _basket['billing_address']['postcode']);
                $Iridium -> setCountry($this -> _basket['billing_address']['country_id']);
                $Iridium -> setEmailAddress($this -> _basket['billing_address']['email']);
                $Iridium -> setPhoneNumber($this -> _basket['billing_address']['phone']);

                $Iridium -> setCardName("{$this->_basket['billing_address']['first_name']} {$this->_basket['billing_address']['last_name']}");

                $Iridium -> setHostedFormMethod(HostedFormMethod::Process);
                $Iridium -> setTransactionType(TransactionType::Sale);
                $Iridium -> setThreeDSecureOverridePolicy(TRUE);

                $Iridium -> setHostedFormReturnType(HostedFormReturnType::HTMLFormCode);

                $Iridium -> setHTML_IncludeFormTags(FALSE);
                $Iridium -> setHTML_IncludeSubmitButton(FALSE);

                $Iridium -> setHashMethod($this -> module['hpfHashMethod']);
                $Iridium -> setPreSharedKey($this -> module['hpfPreSharedKey']);
                $Iridium -> setResultDeliveryMethod($this -> module['hpfResultDeliveryMethod']);

                $Iridium -> setTransactionDateTime(date('Y-m-d H:i:s P'));

                $Iridium -> setEmailAddressEditable(true);
                $Iridium -> setPhoneNumberEditable(true);

                $Iridium -> setCV2Mandatory($this -> module['hpfCV2Mandatory']);
                $Iridium -> setAddress1Mandatory($this -> module['hpfAddress1Mandatory']);
                $Iridium -> setCityMandatory($this -> module['hpfCityMandatory']);
                $Iridium -> setPostCodeMandatory($this -> module['hpfPostCodeMandatory']);
                $Iridium -> setStateMandatory($this -> module['hpfStateMandatory']);
                $Iridium -> setCountryMandatory($this -> module['hpfCountryMandatory']);

                if ($this -> module['hpfResultDeliveryMethod'] != ResultDeliveryMethod::SERVER) {
                    $Iridium -> setServerResultURL(NULL);
                } else {
                    $Iridium -> setServerResultURL('index.php?_g=rm&type=gateway&cmd=call&module=IridiumCorp&mode=receive');
                    //$Iridium->setServerResultURL('modules/gateway/IridiumCorp/iridium/Hosted/ReceiveTransactionResult.php');
                }

                if ($this -> module['hpfResultDeliveryMethod'] != ResultDeliveryMethod::SERVER) {
                    $Iridium -> setPaymentFormDisplayResult(NULL);
                } else {
                    $Iridium -> setPaymentFormDisplayResult(false);
                }

                // the callback URL on this site that will display the transaction
                // result to the customer
                // (always required unless ResultDeliveryMethod = "SERVER" and
                // PaymentFormDisplaysResult = "true")
                if ($this -> module['hpfResultDeliveryMethod'] == ResultDeliveryMethod::SERVER && $Iridium -> getPaymentFormDisplayResult() == true) {
                    $Iridium -> setCallbackURL(NULL);
                } else {
                    $Iridium -> setCallbackURL('index.php?_g=rm&type=gateway&cmd=call&module=IridiumCorp&mode=display');
                }
            }
        }
        $return = $Iridium -> Process();

        switch ($this -> module['mode']) {
            case 'api' :
                require __DIR__ . '/Iridium/HandleTransactionResults.php';
                $return = HandleTransactionResults($Iridium, __FUNCTION__, $order, $order_summary, $this);
                break;
            case 'tr' :
                // require __DIR__ . '/iridium/HandleTransactionResults.php';
                // $return = HandleTransactionResults($Iridium, __FUNCTION__, $order, $order_summary);
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

                    if ($GLOBALS['session'] -> get('DISPLAY_3DS', 'iridiumcorp')) {
                        return $return;
                    }

                } else {
                    if ($this -> module['crt']) {

                        $crtDetails = $GLOBALS['db'] -> misc(IridiumSQL::selectCRT_CrossReferenceDetails($order_summary['customer_id']));

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
                                    $GLOBALS['db'] -> misc(IridiumSQL::deleteCRT_CardDetails($order_summary['customer_id'], $crtDetails[$i]['CardLastFour']));
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
            $GLOBALS['language'] -> loadDefinitions("iridiumcorp", dirname(__FILE__) .'/'. 'language', 'module.definitions.xml');
            // Load other lang either customized ones
            $GLOBALS['language'] -> loadLanguageXML("iridiumcorp", '', dirname(__FILE__) .'/'. 'language');

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
