<?php

define('ICEPAY_VERSION', '1.0.1');

class Gateway {

    private $_module;
    private $_basket;

    public function __construct($module = false) {
        require(realpath(dirname(__FILE__)) . '/api/icepay_api_basic.php');

        $this->_module = $module;
        $this->_basket = & $GLOBALS['cart']->basket;

        $dbPrefix = $GLOBALS['config']->get('config', 'dbprefix');

        $this->_icepayTable = "icepay_transactions";
        $this->_icepayTablePrefixed = "{$dbPrefix}icepay_transactions";

        if (!$GLOBALS['db']->misc("show tables like '$this->_icepayTablePrefixed';")) {
            $install_table = "CREATE TABLE IF NOT EXISTS `$this->_icepayTablePrefixed` (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `cc_order_id` varchar(255) NOT NULL,
                                `status` varchar(255) NOT NULL,
                                `transaction_id` int(11) DEFAULT NULL,
                                `paymentmethod` varchar(255) NOT NULL,
                                PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";
            $GLOBALS['db']->misc($install_table);
        }
    }

    public function transfer() {
        try {
            $data = array(
                'cc_order_id' => $this->_basket['cart_order_id'],
                'status' => 'OPEN',
                'transaction_id' => null,
            );

            // Get cart for currency
            $cart = Tax::getInstance();

            // Get currency the user selected
            $currency = $cart->_currency_vars['code'];

            // Convert total amount to selected currency (Values are from Cubecart's db)
            $amount = round(($this->_basket['total'] * $cart->_currency_vars['value']), 2) * 100;

            $paymentObj = new Icepay_PaymentObject();
            $paymentObj->setAmount($amount)
                    ->setCountry($this->_basket['billing_address']['country_iso'])
                    ->setReference($this->_basket['cart_order_id'])
                    ->setDescription($this->_module['customdescription'])
                    ->setCurrency($currency);

            $language = strtoupper(substr($_SESSION['__client']['language'], 0, 2));
            $paymentObj->setLanguage($language);
 
            if (isset($_POST['icepay_paymentmethod']) && $_POST['icepay_paymentmethod'] != 'icepay_basic') {

                $pmCode = $_POST['icepay_paymentmethod'];

                $data['paymentmethod'] = $pmCode;

                $paymentMethodClass = Icepay_Api_Basic::getInstance()
                        ->readFolder()
                        ->getClassByPaymentmethodCode($pmCode);

                $supportedIssuers = $paymentMethodClass->getSupportedIssuers();

                $paymentObj->setPaymentMethod($paymentMethodClass->_method);

                if (isset($_POST['icepay_issuer'])) {
                    $issuer = $_POST['icepay_issuer'];
                } elseif (count($supportedIssuers > 0)) {
                    $issuer = $supportedIssuers[0];
                } else {
                    $issuer = 'DEFAULT';
                }

                $paymentObj->setIssuer($issuer);

                $supportedLanguages = $paymentMethodClass->getSupportedLanguages();

                if (count($supportedLanguages) == 1 && ($supportedLanguages[0] != '00') || !in_array($language, $supportedLanguages)) {
                    $paymentObj->setLanguage($supportedLanguages[0]);
                }

                if (!Icepay_Api_Basic::getInstance()->exists($this->_basket['billing_address']['country_iso'], $paymentMethodClass->getSupportedCountries())) {
                    $GLOBALS['gui']->setError('Your billing country is not supported by this paymentmethod.');
                    httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'gateway')));
                    return false;
                }

                if (!Icepay_Api_Basic::getInstance()->exists($GLOBALS['config']->get('config', 'default_currency'), $paymentMethodClass->getSupportedCurrency())) {
                    $GLOBALS['gui']->setError('The currency is not supported by this paymentmethod.');
                    httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'gateway')));
                    return false;
                }
            }

            $order_id = $GLOBALS['db']->insert('icepay_transactions', $data);

            $paymentObj->setOrderID($order_id);

            $basicmode = Icepay_Basicmode::getInstance();
            $basicmode->setMerchantID($this->_module['merchantid'])
                    ->setSecretCode($this->_module['secretcode'])
                    ->setSuccessURL("{$GLOBALS['storeURL']}/index.php?_g=rm&type=gateway&cmd=process&module=ICEPAY")
                    ->setErrorURL("{$GLOBALS['storeURL']}/index.php?_g=rm&type=gateway&cmd=process&module=ICEPAY")
                    ->validatePayment($paymentObj);

            $basicmode->setProtocol('https');

            $transfer = array(
                'action' => $basicmode->getURL(),
                'method' => 'post',
                'target' => '_self',
                'submit' => 'auto',
            );
        } catch (Exception $e) {
            $GLOBALS['gui']->setError($e->getMessage());
            httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'gateway')));
            return false;
        }

        return $transfer;
    }

    public function process() {
        // Check if ICEPAY sends a postback, or user is returning
        $icepay = ($_SERVER['REQUEST_METHOD'] == 'POST') ? Icepay_Project_Helper::getInstance()->postback() : Icepay_Project_Helper::getInstance()->result();

        try {
            $icepay->setMerchantID($this->_module['merchantid'])
                    ->setSecretCode($this->_module['secretcode']);
        } catch (Exception $e) {
            // If no merchant and secretcode are set -- and ICEPAY does an URL check to create a merchant.
            echo "Postback URL installed correctly.";
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Set IP check to true and add custom ip's to the whitelist
            $icepay->doIPCheck(true)
                    ->addToWhitelist($this->_module['customiprange']);

            if ($this->_module['logging']) {
                $logger = Icepay_Api_Logger::getInstance();
                $logger->enableLogging(true)
                        ->setLoggingLevel(Icepay_Api_Logger::LEVEL_ERRORS_AND_TRANSACTION)
                        ->logToFile(true)
                        ->setLoggingDirectory(realpath(dirname(__FILE__)) . '/logs');
            }

            // Validate the POST variables and IP
            if ($icepay->validate()) {
                // Get post variables
                $data = $icepay->GetPostback();

                // Get the cubecart order id
                $cc_order = $GLOBALS['db']->select($this->_icepayTable, array('cc_order_id', 'status', 'paymentmethod'), array("id" => $data->orderID));
                $cc_order_id = $cc_order[0]['cc_order_id'];
                $cc_order_paymentmethod = $cc_order[0]['paymentmethod'];

                // Get the icepay order status
                $icepay_order_status = $cc_order[0]['status'];

                $cc_order = Order::getInstance();
                $order_summary = $cc_order->getSummary($cc_order_id);

                if ($icepay->canUpdateStatus($icepay_order_status)) {
                    switch ($data->status) {
                        case Icepay_StatusCode::ERROR:
                            $cc_order->orderStatus(Order::ORDER_CANCELLED, $cc_order_id);
                            $cc_order->paymentStatus(Order::PAYMENT_CANCEL, $cc_order_id);
                            break;
                        case Icepay_StatusCode::OPEN:
                            $cc_order->orderStatus(Order::ORDER_PENDING, $cc_order_id);
                            $cc_order->paymentStatus(Order::PAYMENT_PENDING, $cc_order_id);
                            break;
                        case Icepay_StatusCode::SUCCESS:
                            $cc_order->orderStatus(Order::ORDER_PROCESS, $cc_order_id);
                            $cc_order->paymentStatus(Order::PAYMENT_SUCCESS, $cc_order_id);
                            break;
                        case Icepay_StatusCode::REFUND:

                            break;
                        case Icepay_StatusCode::CHARGEBACK:

                            break;
                    }

                    // Update transaction in icepay table
                    $GLOBALS['db']->update($this->_icepayTable, array("status" => $data->status, 'transaction_id' => $data->transactionID), array("id" => $data->orderID));

                    // Insert into Cubecart's transaction log
                    $transData = array(
                        'notes' => $data->statusCode,
                        'gateway' => "Icepay {$cc_order_paymentmethod}",
                        'order_id' => $cc_order_id,
                        'trans_id' => $data->transactionID,
                        'amount' => $data->amount / 100,
                        'status' => $data->status,
                        'customer_id' => $order_summary['customer_id']
                    );

                    $cc_order->logTransaction($transData);
                }
            } else {
                if ($icepay->isVersionCheck()) {
                    $dump = array(
                        "module" => sprintf("ICEPAY Cubecart payment module version %s using PHP API version %s", ICEPAY_VERSION, Icepay_Project_Helper::getInstance()->getReleaseVersion()), //<--- Module version and PHP API version
                        "notice" => "Checksum validation passed!"
                    );

                    if ($icepay->validateVersion()) {
                        $dump["additional"] = array(
                            "Cubecart" => '5' // CMS name & version                            
                        );
                    } else {
                        $dump["notice"] = "Checksum failed! Merchant ID and Secret code probably incorrect.";
                    }
                    var_dump($dump);
                    exit();
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // Validate the GET variables
            if ($icepay->validate()) {
                if ($icepay->getStatus(true) == 'ERR: Cancelled') {
                    $cc_order = $GLOBALS['db']->select($this->_icepayTable, array('cc_order_id', 'status', 'paymentmethod'), array("id" => $icepay->getOrderID()));
                    $cc_order_id = $cc_order[0]['cc_order_id'];

                    $cc_order = Order::getInstance();

                    $cc_order->orderStatus(Order::ORDER_CANCELLED, $cc_order_id);
                    $cc_order->paymentStatus(Order::PAYMENT_CANCEL, $cc_order_id);
                }

                httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
            }
        }

        exit();
    }

}