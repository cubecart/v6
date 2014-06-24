<?php

/**
 *  ICEPAY Webservice API library
 *
 *  @version 2.1.1
 *  @author Olaf Abbenhuis
 *  @author Wouter van Tilburg
 *  @copyright Copyright (c) 2012, ICEPAY
 *
 */
// Define constants
if(!defined('DIR')) {
    define("DIR", dirname(__FILE__));
}

if(!defined('DS')) {
    define("DS", DIRECTORY_SEPARATOR);
}

// Include API base 
require_once(DIR . DS . "icepay_api_base.php");


class Icepay_Api_Webservice extends Icepay_Api_Base  {

    private static $instance;
    private $_service_reporting;
    private $_service_pay;
    private $_service_paymentMethods;
    private $_service_refunds;
    private $_filtering;
    private $_single;
    protected $version = "1.0.0";
    

    /**
     * Create an instance
     * 
     * @since 2.1.0
     * @access public
     * @return instance of self
     */
    public static function getInstance() {
        if (!self::$instance)
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Returns class or creates the Payment Methods class
     * 
     * @since 2.1.0
     * @access public
     * @return object
     */
    public function paymentMethodService() {
        if (!$this->_service_paymentMethods)
            $this->_service_paymentMethods = new Icepay_Webservice_Paymentmethods();
        return $this->_service_paymentMethods;
    }

    /**
     * Returns class or creates the Filtering class
     * 
     * @since 2.1.0
     * @access public
     * @return object
     */
    public function filtering() {
        if (!$this->_filtering)
            $this->_filtering = new Icepay_Webservice_Filtering();
        return $this->_filtering;
    }

    /**
     * Returns class or creates the Paymentmethod class
     * 
     * @since 2.1.0
     * @access public
     * @return object
     */
    public function singleMethod() {
        if (!$this->_single)
            $this->_single = new Icepay_Webservice_Paymentmethod();
        return $this->_single;
    }

    /**
     * Returns class or creates the Pay class
     * 
     * @since 2.1.0
     * @access public
     * @return object
     */
    public function paymentService() {
        if (!$this->_service_pay)
            $this->_service_pay = new Icepay_Webservice_Pay();
        return $this->_service_pay;
    }

    /**
     * Returns class or creates the Reporting class
     * 
     * @since 2.1.0
     * @access public
     * @return object
     */
    public function reportingService() {
        if (!$this->_service_reporting)
            $this->_service_reporting = new Icepay_Webservice_Reporting();
        return $this->_service_reporting;
    }

    /**
     * Returns class or creates the Refund class
     * 
     * @since 2.1.0
     * @access public
     * @return object
     */
    public function refundService() {
        if (!$this->_service_refunds)
            $this->_service_refunds = new Icepay_Webservice_Refunds();
        return $this->_service_refunds;
    }

}

class Icepay_Webservice_Base extends Icepay_Api_Base {

    protected $service = 'https://connect.icepay.com/webservice/icepay.svc?wsdl';
    protected $client;

    /**
     * Make connection with the soap client
     * 
     * @since 2.1.0
     * @access public
     * @return \Icepay_Webservice_Base 
     */
    public function setupClient() {
        /* Return if already set */
        if ($this->client)
            return $this;

        /* Start a new client */
        $this->client = new SoapClient(
                        $this->service,
                        array(
                            "location" => $this->service,
                            'cache_wsdl' => 'WSDL_CACHE_NONE'
                        )
        );

        /* Client configuration */
        $this->client->soap_defencoding = "utf-8";

        return $this;
    }

    /**
     * Return current timestamp in gmdate format
     * 
     * @since 2.1.0
     * @access protected
     * @return string
     */
    protected function getTimeStamp() {
        return gmdate("Y-m-d\TH:i:s\Z");
    }

    /**
     * Return IP Address
     * 
     * @since 2.1.0
     * @access protected
     * @return string
     */
    protected function getIP() {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Arrange the object in given order
     * 
     * @since 1.0.2
     * @access public
     * @param object $object !required
     * @param array $order !required
     * @return object $obj     
     */
    public function arrangeObject($object, $order = array()) {

        if (!is_object($object))
            throw new Exception("Please provide a valid Object for the arrangeObject method");
        if (!is_array($order) || empty($order))
            throw new Exception("Please provide a valid orderArray for the arrangeObject method");

        $obj = new stdClass();

        foreach ($order as $key) {
            $obj->$key = $object->$key;
        }
        return $obj;
    }

    /**
     * Inserts properties of sub object into mainobject as property
     * 
     * @since version 1.0.2
     * @access public
     * @param object $mainObject !required
     * @param object $subObject !required
     * @param bool $arrange
     * @param array $order !required if $arrange == true
     * @return object $obj  
     */
    public function parseForChecksum($mainObject, $subObject, $arrange = false, $order = array()) {

        if (!is_object($mainObject))
            throw new Exception("Please provide a valid Object");

        $mainObject = $mainObject;

        $i = 1;

        $subObject = $this->forceArray($subObject);

        foreach ($subObject as $sub) {
            // $sub is always an object, just a double-check
            if (is_object($sub)) {
                if ($arrange) {
                    // Arrange object in right order
                    $sub = $this->arrangeObject($sub, $order);
                }

                // Inject each value of subObject into $obj as property for checksum
                foreach ($sub as $value) {
                    $mainObject->$i = $value;
                    $i++;
                }
            }
        }

        return $mainObject;
    }

    /**
     * Generates the checksum
     * 
     * @since 2.1.0
     * @access public
     * @param object $obj
     * @param string $secretCode  
     * @return string
     */
    public function generateChecksum($obj = null, $secretCode = null) {
        $arr = array();
        if ($secretCode)
            array_push($arr, $secretCode);

        $i = 0;
        foreach ($obj as $val) {

            $insert = $val;

            if (is_bool($val)) {
                $insert = ($val) ? 'true' : 'false';
            }

            array_push($arr, $insert);
            $i++;
        }

        return sha1(implode("|", $arr));
    }

    /**
     * Force object into array
     * 
     * @since 2.1.0
     * @access protected
     * @param object $obj
     * @return array 
     */
    protected function forceArray($obj) {
        if (is_array($obj))
            return $obj;

        $arr = array();
        array_push($arr, $obj);
        return $arr;
    }

}

class Icepay_Webservice_Paymentmethods extends Icepay_Webservice_Base {

    protected $_paymentMethod = null;
    protected $_paymentMethods = null;
    protected $_paymentMethodsArray;
    protected $_savedData = array();

    public function __construct() {
        $this->setupClient();
    }

    /**
     * Retrieve all payment methods
     * 
     * @since 2.1.0
     * @access public
     * 
     * @return \Icepay_Webservice_Paymentmethods 
     */
    public function retrieveAllPaymentmethods() {
        if (isset($this->_paymentMethodsArray))
            return $this;

        $obj = new stdClass();
        // Must be in specific order for checksum ---------
        $obj->MerchantID = $this->_merchantID;
        $obj->SecretCode = $this->_secretCode;
        $obj->Timestamp = $this->getTimeStamp();
        // ------------------------------------------------
        $obj->Checksum = $this->generateChecksum($obj);
        $obj->SecretCode = null;

        $this->_paymentMethods = $this->client->GetMyPaymentMethods(array('request' => $obj));

        $this->_paymentMethodsArray = $this->clean($this->_paymentMethods);

        return $this;
    }

    /**
     * Return clean array
     * 
     * @since 2.1.0
     * @access protected
     * @param object $obj
     * @return array 
     */
    protected function clean($obj) {
        $methods = array();
        foreach ($this->forceArray($obj->GetMyPaymentMethodsResult->PaymentMethods->PaymentMethod) as $value) {
            array_push($methods, array(
                'PaymentMethodCode' => $value->PaymentMethodCode,
                'Description' => $value->Description,
                'Issuers' => $this->convertIssuers($this->forceArray($value->Issuers->Issuer))
                    )
            );
        };
        return $methods;
    }

    /**
     * Convert Issuers
     * 
     * @since 2.1.0
     * @access private
     * @param array $array
     * @return array 
     */
    private function convertIssuers($array) {
        $return = array();
        foreach ($array as $value) {
            array_push($return, array(
                'IssuerKeyword' => $value->IssuerKeyword,
                'Description' => $value->Description,
                'Countries' => $this->convertCountries($this->forceArray($value->Countries->Country))
                    )
            );
        }
        return $return;
    }

    /**
     * Convert Countries
     * 
     * @since 2.1.0
     * @access private
     * @param array $array
     * @return array 
     */
    private function convertCountries($array) {
        $return = array();
        foreach ($array as $value) {
            array_push($return, array(
                'CountryCode' => $value->CountryCode,
                'MinimumAmount' => $value->MinimumAmount,
                'MaximumAmount' => $value->MaximumAmount,
                'Currencies' => $this->convertCurrencies($value->Currency)
            ));
        }
        return $return;
    }

    /**
     * Convert Currencies
     * 
     * @since 2.1.0
     * @access private     * 
     * @param string $string
     * @return string 
     */
    private function convertCurrencies($string) {
        $return = explode(", ", $string);
        return $return;
    }

    /**
     * Returns paymentmethods as array
     * 
     * @since 2.1.0
     * @access public
     * @return array 
     */
    public function asArray() {
        return $this->_paymentMethodsArray;
    }

    /**
     * Returns paymentmethods as object
     * 
     * @since 2.1.0
     * @access public
     * @return object 
     */
    public function asObject() {
        return $this->_paymentMethods;
    }

    /**
     * get Webservice Data
     * 
     * @since 2.1.0
     * @access public
     * @return string 
     */
    public function exportAsString() {
        return urlencode(serialize($this->_paymentMethodsArray));
    }

    /**
     * Save ws to File
     * 
     * @since 2.1.0
     * @access public
     * @param string $fileName
     * @param directory $directory
     * @return boolean
     * @throws Exception 
     */
    public function saveToFile($fileName = "wsdata", $directory = "") {
        if ($directory == "")
            $directory = dirname(__FILE__);

        date_default_timezone_set("Europe/Paris");
        $line = sprintf("Paymentmethods %s,%s\r\n", date("H:i:s", time()), $this->exportAsString());

        $filename = sprintf("%s/%s.csv", $directory, $fileName);
        try {
            $fp = @fopen($filename, "w");
            @fwrite($fp, $line);
            @fclose($fp);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        };

        return true;
    }

}

class Icepay_Webservice_Filtering {

    protected $_paymentMethodsArray;
    protected $_paymentMethodsArrayFiltered;

    /**
     * From String
     * 
     * @since 2.1.0
     * @access public
     * @param string $string
     * @return \Icepay_Webservice_Filtering 
     */
    public function importFromString($string) {
        $this->_paymentMethodsArray = unserialize(urldecode($string));
        $this->_paymentMethodsArrayFiltered = $this->_paymentMethodsArray;
        return $this;
    }

    /**
     * Export String
     * 
     * @since 2.1.0
     * @access public
     * @return string
     */
    public function exportAsString() {
        return urlencode(serialize($this->_paymentMethodsArrayFiltered));
    }

    /**
     * From Array
     * 
     * @since 2.1.0
     * @access public
     * @param array $array
     * @return \Icepay_Webservice_Filtering 
     */
    public function loadFromArray($array) {
        $this->_paymentMethodsArray = $array;
        $this->_paymentMethodsArrayFiltered = $this->_paymentMethodsArray;
        return $this;
    }

    /**
     * Read data from stored file
     * 
     * @since 2.1.0
     * @access public
     * @param string $fileName
     * @param string $directory
     * @return \Icepay_Webservice_Filtering
     * @throws Exception 
     */
    public function loadFromFile($fileName = "wsdata", $directory = "") {
        if ($directory == "")
            $directory = dirname(__FILE__);

        $filename = sprintf("%s/%s.csv", $directory, $fileName);
        try {
            $fp = @fopen($filename, "r");
            $line = @fgets($fp);
            @fclose($fp);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        };

        if (!$line) {
            throw new Exception("No data stored");
        }

        $arr = explode(",", $line);
        $this->importFromString($arr[1]);

        return $this;
    }

    /**
     * Get payment methods
     * 
     * @since 2.1.0
     * @access public 
     * @return array 
     */
    public function getPaymentmethods() {
        return $this->_paymentMethodsArray;
    }

    /**
     * Get filtered payment methods
     * 
     * @since 2.1.0
     * @access public
     * @return array 
     */
    public function getFilteredPaymentmethods() {
        return $this->_paymentMethodsArrayFiltered;
    }

    /**
     * Filter the paymentmethods array by currency
     * @since 2.1.0
     * @access public
     * @param string $currency Language ISO 4217 code
     */
    public function filterByCurrency($currency) {
        $filteredArr = array();
        foreach ($this->_paymentMethodsArrayFiltered as $paymentMethod) {
            $continue = true;
            foreach ($paymentMethod["Issuers"] as $issuer) {
                foreach ($issuer["Countries"] as $country) {
                    if (in_array(strtoupper($currency), $country["Currencies"])) {
                        array_push($filteredArr, $paymentMethod); //return//return
                        $continue = false;
                    }
                    if (!$continue)
                        break;
                }
                if (!$continue)
                    break;
            }
        }
        $this->_paymentMethodsArrayFiltered = $filteredArr;
        return $this;
    }

    /**
     * Filter the paymentmethods array by country
     * @since 2.1.0
     * @access public
     * @param string $country Country ISO 3166-1-alpha-2 code
     */
    public function filterByCountry($countryCode) {
        $filteredArr = array();
        foreach ($this->_paymentMethodsArrayFiltered as $paymentMethod) {
            $continue = true;
            foreach ($paymentMethod["Issuers"] as $issuer) {
                foreach ($issuer["Countries"] as $country) {
                    if (strtoupper($country["CountryCode"]) == strtoupper($countryCode) || $country["CountryCode"] == "00") {
                        array_push($filteredArr, $paymentMethod);
                        $continue = false;
                    }
                    if (!$continue)
                        break;
                }
                if (!$continue)
                    break;
            }
        }
        $this->_paymentMethodsArrayFiltered = $filteredArr;
        return $this;
    }

    /**
     * Filter the paymentmethods array by amount
     * @since 2.1.0
     * @access public
     * @param int $amount Amount in cents
     */
    public function filterByAmount($amount) {
        $amount = intval($amount);
        $filteredArr = array();
        foreach ($this->_paymentMethodsArrayFiltered as $paymentMethod) {
            $continue = true;
            foreach ($paymentMethod["Issuers"] as $issuer) {
                foreach ($issuer["Countries"] as $country) {
                    if ($amount >= intval($country["MinimumAmount"]) &&
                            $amount <= intval($country["MaximumAmount"])) {
                        array_push($filteredArr, $paymentMethod);
                        $continue = false;
                    }
                    if (!$continue)
                        break;
                }
                if (!$continue)
                    break;
            }
        }
        $this->_paymentMethodsArrayFiltered = $filteredArr;
        return $this;
    }

}

class Icepay_Webservice_Paymentmethod extends Icepay_Webservice_Filtering {

    protected $_methodData;
    protected $_issuerData;
    protected $_country;

    /**
     * Select the payment method by code
     * 
     * @since 2.1.0
     * @access public
     * @param string $name
     * @return \Icepay_Webservice_Paymentmethod
     * @throws Exception 
     */
    public function selectPaymentMethodByCode($name) {
        if (!isset($this->_paymentMethodsArray))
            throw new Exception("No data loaded");
        foreach ($this->_paymentMethodsArray as $paymentMethod) {
            if ($paymentMethod["PaymentMethodCode"] == strtoupper($name)) {
                $this->_methodData = $paymentMethod;
                break;
            }
        }
        return $this;
    }

    /**
     * Select an issuer by keyword
     * 
     * @since 2.1.0
     * @access public
     * @param string $name
     * @return \Icepay_Webservice_Paymentmethod
     * @throws Exception 
     */
    public function selectIssuerByKeyword($name) {
        if (!isset($this->_paymentMethodsArray))
            throw new Exception("No data loaded");
        foreach ($this->_paymentMethodsArray as $paymentMethod) {
            foreach ($paymentMethod["Issuers"] as $issuer) {
                if ($issuer["IssuerKeyword"] == strtoupper($name)) {
                    $this->_methodData = $paymentMethod;
                    $this->_issuerData = $issuer;
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * Selects the country out of the issuer data
     * 
     * @since 2.1.0
     * @access Public
     * @param string $country
     * @return \Icepay_Webservice_Paymentmethod 
     */
    public function selectCountry($country) {
        if (!isset($this->_issuerData)) {
            $this->_country = $this->validateCountry($country);
            return $this;
        }

        if (in_array($country, $this->getCountries())) {
            $this->_country = $this->validateCountry($country);
            return $this;
        };

        if (in_array("00", $this->getCountries())) {
            $this->_country = "00";
        };

        return $this;
    }

    /**
     * Get payment method data
     * 
     * @since 2.1.0
     * @access public
     * @return array
     */
    public function getPaymentmethodData() {
        return $this->_methodData;
    }

    /**
     * Get issuer data
     * 
     * @since 2.1.0
     * @access public
     * @return array 
     */
    public function getIssuerData() {
        return $this->_issuerData;
    }

    /**
     * Get issuer list
     * 
     * @since 2.1.0
     * @access public
     * @return array
     * @throws Exception 
     */
    public function getIssuers() {
        if (!isset($this->_methodData))
            throw new Exception("Paymentmethod must be selected first");
        return $this->_methodData["Issuers"];
    }

    /**
     * Get currencies
     * 
     * @since 2.1.0
     * @access public
     * @return array
     * @throws Exception 
     */
    public function getCurrencies() {
        if (!isset($this->_issuerData))
            throw new Exception("Issuer must be selected first");
        if (!isset($this->_country))
            throw new Exception("Country must be selected first");
        foreach ($this->_issuerData["Countries"] as $country) {
            if ($this->_country == $country["CountryCode"]) {
                return $country["Currencies"];
            }
        }
        return array();
    }

    /**
     * Get countries
     * 
     * @since 2.1.0
     * @access public
     * @return array
     * @throws Exception 
     */
    public function getCountries() {
        if (!isset($this->_issuerData))
            throw new Exception("Issuer must be selected first");
        $countries = array();
        foreach ($this->_issuerData["Countries"] as $country) {
            array_push($countries, $country["CountryCode"]);
        }
        return $countries;
    }

    /**
     * Get minimum amount
     * 
     * @since 2.1.0
     * @access public
     * @return int
     * @throws Exception 
     */
    public function getMinimumAmount() {
        if (!isset($this->_issuerData))
            throw new Exception("Issuer must be selected first");
        if (!isset($this->_country))
            throw new Exception("Country must be selected first");
        foreach ($this->_issuerData["Countries"] as $country) {
            if ($this->_country == $country["CountryCode"]) {
                return intval($country["MinimumAmount"]);
            }
        }
    }

    /**
     * Get maximum amount
     * 
     * @since 2.1.0
     * @access public
     * @return int
     * @throws Exception 
     */
    public function getMaximumAmount() {
        if (!isset($this->_issuerData))
            throw new Exception("Issuer must be selected first");
        if (!isset($this->_country))
            throw new Exception("Country must be selected first");
        foreach ($this->_issuerData["Countries"] as $country) {
            if ($this->_country == $country["CountryCode"]) {
                return intval($country["MaximumAmount"]);
            }
        }
    }

    /**
     * Validate given country
     * 
     * @since 2.1.0
     * @access protected
     * @param string $country
     * @return string
     * @throws Exception 
     */
    protected function validateCountry($country) {
        if (strlen($country) != 2)
            throw new Exception("Country must be ISO 3166-1 alpha-2");
        return strtoupper($country);
    }

}

class Icepay_Webservice_Pay extends Icepay_Webservice_Base {

    protected $service = 'https://connect.icepay.com/webservice/icepay.svc?wsdl';

    public function __construct() {
        $this->setupClient();
    }

    /**
     * The Checkout web method allows you to  initialize a new payment in the ICEPAY system for  ALL the 
     * payment methods that you have access to
     * 
     * @since version 2.1.0
     * @access public
     * @param Icepay_PaymentObject_Interface_Abstract $paymentObj
     * @param bool $geturlOnly
     * @return array result
     */
    public function checkOut(Icepay_PaymentObject_Interface_Abstract $paymentObj, $getUrlOnly = false) {
        $obj = new stdClass();

        // Must be in specific order for checksum ---------
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->Amount = $paymentObj->getAmount();
        $obj->Country = $paymentObj->getCountry();
        $obj->Currency = $paymentObj->getCurrency();
        $obj->Description = $paymentObj->getDescription();
        $obj->EndUserIP = $this->getIP();
        $obj->Issuer = $paymentObj->getIssuer();
        $obj->Language = $paymentObj->getLanguage();
        $obj->OrderID = $paymentObj->getOrderID();
        $obj->PaymentMethod = $paymentObj->getPaymentMethod();
        $obj->Reference = $paymentObj->getReference();
        $obj->URLCompleted = $this->getSuccessURL();
        $obj->URLError = $this->getErrorURL();

        // ------------------------------------------------
        $obj->Checksum = $this->generateChecksum($obj, $this->getSecretCode());

        $result = $this->client->Checkout(array('request' => $obj));

        /* store the checksum momentarily */
        $checksum = $result->CheckoutResult->Checksum;

        /* Replace the checksum in the data with secretCode to generate a new checksum */
        $result->CheckoutResult->Checksum = $this->getSecretCode();

        /* Verify response data */
        if ($checksum != $this->generateChecksum($result->CheckoutResult))
            throw new Exception("Data could not be verified");

        /* Return mister checksum */
        $result->CheckoutResult->Checksum = $checksum;

        /* Return just the payment URL if required */
        if ($getUrlOnly)
            return $result->CheckoutResult->PaymentScreenURL;

        $transactionObj = new Icepay_TransactionObject();
        $transactionObj->setData($result->CheckoutResult);
        
        
        /* Default return all data */
        return $transactionObj;
    }

    /**
     * The PhoneCheckout web method  allows you to create a phone payment in the ICEPAY system. The 
     * main difference with the  Checkout web method is the response. The response  is  a 
     * PhoneCheckoutResponse object, which contains extra members such as the phone number etc., making 
     * seamless integration possible.
     * 
     * @since 2.1.0
     * @access public
     * @param array $data
     * @param bool $geturlOnly
     * @return array result
     */
    public function phoneCheckout(Icepay_PaymentObject_Interface_Abstract $paymentObj, $getUrlOnly = false) {
        $obj = new StdClass();

        // Must be in specific order for checksum ---------
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->Amount = $paymentObj->getAmount();
        $obj->Country = $paymentObj->getCountry();
        $obj->Currency = $paymentObj->getCurrency();
        $obj->Description = $paymentObj->getDescription();
        $obj->EndUserIP = $this->getIP();
        $obj->Issuer = $paymentObj->getIssuer();
        $obj->Language = $paymentObj->getLanguage();
        $obj->OrderID = $paymentObj->getOrderID();
        $obj->PaymentMethod = $paymentObj->getPaymentMethod();
        $obj->Reference = $paymentObj->getReference();
        $obj->URLCompleted = $this->getSuccessURL();
        $obj->URLError = $this->getErrorURL();

        $obj->Checksum = $this->generateChecksum($obj, $this->getSecretCode());

        $result = $this->client->PhoneCheckout(array('request' => $obj));

        /* store the checksum momentarily */
        $checksum = $result->PhoneCheckoutResult->Checksum;

        /* Replace the checksum in the data with secretCode to generate a new checksum */
        $result->PhoneCheckoutResult->Checksum = $this->getSecretCode();

        /* Verify response data */
        if ($checksum != $this->generateChecksum($result->PhoneCheckoutResult))
            throw new Exception("Data could not be verified");

        /* Return mister checksum */
        $result->PhoneCheckoutResult->Checksum = $checksum;

        /* Return only the payment URL if required */
        if ($getUrlOnly)
            return $result->PhoneCheckoutResult->PaymentScreenURL;

        /* Default return all data */
        return (array) $result->PhoneCheckoutResult;
    }

    /**
     * The SmsCheckout web method allows you to create an SMS payment in the ICEPAY system. The main 
     * difference with the Checkout web method is the response. The response will contain extra members such 
     * as the premium-rate number, making seamless integration possible.
     * 
     * @since 2.1.0
     * @access public
     * @param array $data
     * @param bool $geturlOnly
     * @return array
     */
    public function smsCheckout(Icepay_PaymentObject_Interface_Abstract $paymentObj, $getUrlOnly = false) {
        $obj = new StdClass();

        // Must be in specific order for checksum ---------
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->Amount = $paymentObj->getAmount();
        $obj->Country = $paymentObj->getCountry();
        $obj->Currency = $paymentObj->getCurrency();
        $obj->Description = $paymentObj->getDescription();
        $obj->EndUserIP = $this->getIP();
        $obj->Issuer = $paymentObj->getIssuer();
        $obj->Language = $paymentObj->getLanguage();
        $obj->OrderID = $paymentObj->getOrderID();
        $obj->PaymentMethod = $paymentObj->getPaymentMethod();
        $obj->Reference = $paymentObj->getReference();
        $obj->URLCompleted = $this->getSuccessURL();
        $obj->URLError = $this->getErrorURL();

        $obj->Checksum = $this->generateChecksum($obj, $this->getSecretCode());

        $result = $this->client->SmsCheckout(array('request' => $obj));
        $result = $result->SMSCheckoutResult;

        /* store the checksum momentarily */
        $checksum = $result->Checksum;

        /* Replace the checksum in the data with secretCode to generate a new checksum */
        $result->Checksum = $this->getSecretCode();

        // Order object in correct order for Checksum
        $checksumObject = $this->arrangeObject($result, array(
            'Checksum', 'MerchantID', 'Timestamp', 'Amount', 'Country',
            'Currency', 'Description', 'EndUserIP', 'Issuer', 'Language',
            'OrderID', 'PaymentID', 'PaymentMethod', 'PaymentScreenURL',
            'ProviderTransactionID', 'Reference', 'TestMode', 'URLCompleted',
            'URLError', 'ActivationCode', 'Keyword', 'PremiumNumber', 'Disclaimer'
                ));

        /* Verify response data */
        if ($checksum != $this->generateChecksum($checksumObject))
            throw new Exception("Data could not be verified");

        /* Return mister checksum */
        $result->Checksum = $checksum;

        /* Return only the payment URL if required */
        if ($getUrlOnly)
            return $result->PaymentScreenURL;

        /* Default return all data */
        return (array) $result;
    }

    /**
     * The ValidatePhoneCode web method verifies the code that the end-user must provide in 
     * order to start a phone payment.
     * 
     * @since 2.1.0
     * @access public
     * @param int $paymentID
     * @param int $phoneCode
     * @return bool success
     */
    public function validatePhoneCode($paymentID, $phoneCode) {
        $obj = new StdClass();

        // Must be in specific order for checksum ---------        
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->PaymentID = $paymentID;
        $obj->PhoneCode = $phoneCode;

        $obj->Checksum = $this->generateChecksum($obj, $this->getSecretCode());

        $result = $this->client->ValidatePhoneCode(array('request' => $obj));
        $result = $result->ValidatePhoneCodeResult;

        /* store the checksum momentarily */
        $checksum = $result->Checksum;

        /* Replace the checksum in the data with secretCode to generate a new checksum */
        $result->Checksum = $this->getSecretCode();

        /* Verify response data */
        if ($checksum != $this->generateChecksum($result))
            throw new Exception("Data could not be verified");

        return $result->Success;
    }

    /**
     * The ValidateSmsCode web method validates the code that the end-user must provide.
     * 
     * @since 2.1.0
     * @access public
     * @param int $paymentID
     * @param int $smsCode
     * @return bool success
     */
    public function validateSmsCode($paymentID, $smsCode) {
        $obj = new StdClass();

        // Must be in specific order for checksum ---------        
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->PaymentID = $paymentID;
        $obj->SmsCode = $smsCode;

        $obj->Checksum = $this->generateChecksum($obj, $this->getSecretCode());

        $result = $this->client->ValidateSmsCode(array('request' => $obj));
        $result = $result->ValidateSmsCodeResult;

        /* store the checksum momentarily */
        $checksum = $result->Checksum;

        /* Replace the checksum in the data with secretCode to generate a new checksum */
        $result->Checksum = $this->getSecretCode();

        /* Verify response data */
        if ($checksum != $this->generateChecksum($result))
            throw new Exception("Data could not be verified");

        return $result->Success;
    }

    /**
     * The phoneDirectCheckout web method allows you to initialize a new payment in the ICEPAY system 
     * with paymentmethod Phone with Pincode
     * 
     * @since version 2.1.0
     * @access public
     * @param object $data
     * @return array result
     */
    public function phoneDirectCheckout(Icepay_PaymentObject_Interface_Abstract $paymentObj) {
        $obj = new StdClass();
        
        // Must be in specific order for checksum ---------
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->Amount = $paymentObj->getAmount();
        $obj->Country = $paymentObj->getCountry();
        $obj->Currency = $paymentObj->getCurrency();
        $obj->Description = $paymentObj->getDescription();
        $obj->EndUserIP = $this->getIP();
        $obj->Issuer = $paymentObj->getIssuer();
        $obj->Language = $paymentObj->getLanguage();
        $obj->OrderID = $paymentObj->getOrderID();
        $obj->PaymentMethod = $paymentObj->getPaymentMethod();
        $obj->Reference = $paymentObj->getReference();
        $obj->URLCompleted = $this->getSuccessURL();
        $obj->URLError = $this->getErrorURL();
        $obj->PINCode = $this->getPinCode();

        $obj->Checksum = $this->generateChecksum($obj, $this->getSecretCode());

        $result = $this->client->phoneDirectCheckout(array('request' => $obj));
        $result = $result->PhoneDirectCheckoutResult;

        /* store the checksum momentarily */
        $checksum = $result->Checksum;

        /* Replace the checksum in the data with secretCode to generate a new checksum */
        $result->Checksum = $this->getSecretCode();

        // Reverse Success and Error Description, since order must be specific for Checksum
        $success = $result->Success;
        $errorDescription = $result->ErrorDescription;

        unset($result->Success, $result->ErrorDescription);

        $result->Success = $success;
        $result->ErrorDescription = $errorDescription;

        /* Verify response data */
        if ($checksum != $this->generateChecksum($result))
            throw new Exception("Data could not be verified");

        /* Return mister checksum */
        $result->Checksum = $checksum;

        /* Default return all data */
        return (array) $result;
    }

    /**
     * The GetPremiumRateNumbers web method is supplementary to the PhoneDirectCheckout web method. 
     * The idea is that you query the latest premium-rate number information (such as rate per minute, etc.) 
     * and cache it on your own system so that you can display the 
     * premium-rate number information to the enduser without having to start a new transaction.
     * 
     * @since version 2.1.0
     * @access public
     * @return array result
     */
    public function getPremiumRateNumbers() {
        $obj = new StdClass();

        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();

        $obj->Checksum = $this->generateChecksum($obj);

        $result = $this->client->GetPremiumRateNumbers(array('request' => $obj));
        $result = $result->GetPremiumRateNumbersResult;
        $premiumRateNumbers = isset($result->PremiumRateNumbers->PremiumRateNumberInformation) ? $result->PremiumRateNumbers->PremiumRateNumberInformation : null;

        // Checksum
        $obj = new StdClass();

        $obj->SecretCode = $this->getSecretCode();
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $result->Timestamp;

        if (!is_null($premiumRateNumbers)) {
            $obj = $this->parseForChecksum($obj, $premiumRateNumbers, true, array("PhoneNumber", "RatePerCall", "RatePerMinute"));
        }

        $Checksum = $this->generateChecksum($obj);

        // Compare Checksums
        if ($result->Checksum != $Checksum)
            throw new Exception('Data could not be verified');

        return (array) $result;
    }

    /**
     * The GetPremiumRateNumbers web method is supplementary to the PhoneDirectCheckout web method. 
     * The idea is that you query the latest premium-rate number information (such as rate per minute, etc.) 
     * and cache it on your own system so that you can display the 
     * premium-rate number information to the enduser without having to start a new transaction.
     * 
     * @since version 2.1.0
     * @access public
     * @param int $paymentID
     * @return array result
     */
    public function getPayment($paymentID) {
        $obj = new StdClass();

        $obj->SecretCode = $this->getSecretCode();
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimestamp();
        $obj->PaymentID = $paymentID;

        $obj->Checksum = $this->generateChecksum($obj);

        $result = $this->client->GetPayment(array('request' => $obj));
        $result = $result->GetPaymentResult;

        $checksum = $result->Checksum;

        $result->Checksum = $this->getSecretCode();

        // Order object in correct order for Checksum
        $result = $this->arrangeObject($result, array(
            "Checksum", "MerchantID", "Timestamp", "PaymentID",
            "Amount", "ConsumerAccountNumber", "ConsumerAddress",
            "ConsumerCity", "ConsumerCountry", "ConsumerEmail",
            "ConsumerHouseNumber", "ConsumerIPAddress", "ConsumerName",
            "ConsumerPhoneNumber", "Currency", "Description", "Duration",
            "Issuer", "OrderID", "OrderTime", "PaymentMethod", "PaymentTime",
            "Reference", "Status", "StatusCode", "TestMode"
                ));

        /* Verify response data */
        if ($checksum != $this->generateChecksum($result))
            throw new Exception("Data could not be verified");

        /* Return mister checksum */
        $result->Checksum = $checksum;

        /* Default return all data */
        return (array) $result;
    }

}

class Icepay_Webservice_Refunds extends Icepay_Webservice_Base {

    protected $service = 'https://connect.icepay.com/webservice/refund.svc?wsdl';

    public function __construct() {
        $this->setupClient();
    }

    /**
     * The RequestRefund web method allows you to initiate a refund request for a payment. You can request 
     * the entire amount to be refunded or just a part of it. If you request only a partial amount to be refunded 
     * then you  are allowed to perform refund requests for the same payment until you have reached its full 
     * amount. After that you cannot request refunds anymore for that payment.
     * 
     * @since version 2.1.0
     * @access public
     * @param int $paymentID
     * @param int $refundAmount Amount in cents
     * @param string $refundCurrency
     */
    public function requestRefund($paymentID, $refundAmount, $refundCurrency) {
        $obj = new stdClass();

        // Must be in specific order for checksum --
        $obj->Secret = $this->getSecretCode();
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->PaymentID = $paymentID;
        $obj->RefundAmount = $refundAmount;
        $obj->RefundCurrency = $refundCurrency;
        // -----------------------------------------        
        // Generate Checksum
        $obj->Checksum = $this->generateChecksum($obj);

        // Ask for getPaymentRefunds and get response
        $result = $this->client->requestRefund($obj);
        $result = $result->RequestRefundResult;

        $obj = new StdClass();

        // Must be in specific order for checksum -------------------
        $obj->Secret = $this->getSecretCode();
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $result->Timestamp;
        $obj->RefundID = $result->RefundID;
        $obj->PaymentID = $paymentID;
        $obj->RefundAmount = $refundAmount;
        $obj->RemainingRefundAmount = $result->RemainingRefundAmount;
        $obj->RefundCurrency = $refundCurrency;
        // ----------------------------------------------------------          
        // Verify response data by making a new Checksum
        $Checksum = $this->generateChecksum($obj);

        // Compare Checksums
        if ($result->Checksum != $Checksum)
            throw new Exception('Data could not be verified');

        return (array) $result;
    }

    /**
     * The CancelRefund web method allows you to cancel a refund request if it has not already been processed.
     * 
     * @since version 2.1.0
     * @access public
     * @param int $refundID
     * @param int $paymentID
     */
    public function cancelRefund($refundID, $paymentID) {
        $obj = new stdClass();

        // Must be in specific order for checksum --
        $obj->Secret = $this->getSecretCode();
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->RefundID = $refundID;
        $obj->PaymentID = $paymentID;
        // -----------------------------------------  
        // Generate Checksum
        $obj->Checksum = $this->generateChecksum($obj);

        // Ask for cancelRefunt and get response
        $result = $this->client->CancelRefund($obj);
        $result = $result->CancelRefundResult;

        $obj->Timestamp = $result->Timestamp;
        $obj->Success = $result->Success;

        // Unset properties for new Checksum
        unset($obj->RefundID, $obj->PaymentID, $obj->Checksum);

        // Verify response data by making a new Checksum
        $Checksum = $this->generateChecksum($obj);

        // Compare Checksums
        if ($result->Checksum != $Checksum)
            throw new Exception('Data could not be verified');

        return (array) $result;
    }

    /**
     * The GetPaymentRefunds web method allows you to query refund request information that belongs to the payment.
     * 
     * @since version 2.1.0
     * @access public
     * @param int $paymentID
     */
    public function getPaymentRefunds($paymentID) {
        $obj = new stdClass();

        // Must be in specific order for checksum --
        $obj->Secret = $this->getSecretCode();
        $obj->MerchantID = $this->getMerchantID();
        $obj->Timestamp = $this->getTimeStamp();
        $obj->PaymentID = $paymentID;
        // -----------------------------------------  
        // Generate Checksum
        $obj->Checksum = $this->generateChecksum($obj);

        // Ask for getPaymentRefunds and get response
        $result = $this->client->getPaymentRefunds($obj);

        $result = $result->GetPaymentRefundsResult;
        $refunds = isset($result->Refunds->Refund) ? $result->Refunds->Refund : null;

        $obj->Timestamp = $result->Timestamp;

        if (!is_null($refunds)) {
            // Assign all properties of the DayStatistics object as property of mainObject
            $obj = $this->parseForChecksum($obj, $refunds, true, array("RefundID", "DateCreated", "RefundAmount", "RefundCurrency", "Status"));
        }

        // Unset properties for new Checksum
        unset($obj->Checksum);

        // Verify response data by making a new Checksum
        $Checksum = $this->generateChecksum($obj);

        // Compare Checksums
        if ($result->Checksum != $Checksum)
            throw new Exception('Data could not be verified');

        return (!is_null($refunds)) ? $this->forceArray($refunds) : array();
    }

}

class Icepay_Webservice_Reporting extends Icepay_Webservice_Base {

    protected $service = 'https://connect.icepay.com/webservice/report.svc?wsdl';
    protected $_autokill = false;
    protected $_sessionName = "icepay_api_webservice_reportingsession";
    protected $_session;
    protected $_username;
    protected $_useragent;
    protected $_cookie;
    protected $_phpsession;

    public function __construct() {
        $this->setupClient();
    }

    public function __destruct() {
        if ($this->_autokill)
            $this->killSession();
    }

    /**
     * Set the Session ID field
     * @since version 2.1.0
     * @access public
     * @param string $val 
     */
    public function setSessionID($val) {
        $this->_session = $val;
        return $this;
    }

    /**
     * Get the SessionID field
     * @since version 2.1.0
     * @access public
     * @return (string)session
     */
    public function getSessionID() {
        return $this->_session;
    }

    /**
     * Set the Username field
     * @since version 2.1.0
     * @access public
     * @param string $val 
     */
    public function setUsername($val) {
        $this->_username = $val;
        return $this;
    }

    /**
     * Get the Username field
     * @since version 2.1.0
     * @access private
     * @return (string)username
     */
    private function getUsername() {
        return $this->_username;
    }

    /**
     * Set autokill to true or false
     * @since version 2.1.0
     * @access public
     * @param bool $bool
     */
    public function autoKill($bool) {
        $this->_autokill = $bool;
        return $this;
    }

    /**
     * Set the User Agent field
     * @since version 2.1.0
     * @access public
     * @param string $val 
     */
    public function setUserAgent($val) {
        $this->_useragent = $val;
        return $this;
    }

    /**
     * Get the User Agent field
     * @since version 2.1.0
     * @access public
     * @return (string)useragent
     */
    private function getUserAgent() {
        return $this->_useragent;
    }
    
    /*
     * Make use of Cookies
     * 
     * @since 2.1.0
     * @access public
     * @param bool $bool
     */
    public function useCookie($bool = true) {
        $this->_cookie = $bool;
        return $this;
    }

    /*
     * Make use of PHP Sessions
     * 
     * @since 2.1.0
     * @access public
     * @param bool $bool
     */
    public function usePHPSession($bool = true) {
        if (!isset($_SESSION)) {
            session_start();
        }
        $this->_phpsession = $bool;
        return $this;
    }

    /*
     * Creates the PHP Session
     * 
     * @since 2.1.0
     * @access public
     * @param bool $sessionID
     */
    public function createPHPSession($sessionID = true) {
        if ($sessionID) {
            $_SESSION[$this->_sessionName] = $this->_session;
        }
        return $this;
    }
    
    /*
     * Read the PHP Session
     * 
     * @since 2.1.0
     * @access private
     * @param bool $sessionID
     * @return bool
     */
    private function readFromPHPSession($sessionID = true) {
        if ($sessionID) {
            if (isset($_SESSION[$this->_sessionName]) && $_SESSION[$this->_sessionName] != "") {
                $this->_session = $_SESSION[$this->_sessionName]->SessionID;
                return true;
            }
        }
        return false;
    }
    
    /*
     * Unsets the php Session
     * 
     * @since 2.1.0
     * @access private
     */
    private function unsetPHPSession() {
        unset($_SESSION[$this->_sessionName]);
    }

    /**
     * Create Cookie
     * 
     * @since 2.1.0
     * @access public
     * @return obj this
     */
    public function createCookie($cookie = true) {
        if ($cookie) {
            $cookietime = time()+(60*60*24*365);  
            setcookie($this->_sessionName . "_SessionID", $this->_session->SessionID, $cookietime);
            setcookie($this->_sessionName . "_Timestamp", $this->_session->Timestamp, $cookietime);
        }

        return $this;
    }

    /**
     * Read Cookie
     * 
     * @since 2.1.0
     * @access public
     * @return bool
     */
    private function readFromCookie($cookie = true) {
        if ($cookie) {  
            if (isset($_COOKIE[$this->_sessionName . "_SessionID"])) {
                $this->_session = $_COOKIE[$this->_sessionName . "_SessionID"];
                return true;
            }
        }

        return false;
    }
    
    /*
     * Unset cookie
     * 
     * @since 2.1.0
     * @access public     * 
     */
    public function unsetCookie() {
        setcookie("icepay_api_webservice_reportingsession_SessionID", '', time()-1000);
        setcookie("icepay_api_webservice_reportingsession_Timestamp", '', time()-1000);
    }

    public function initSession() { 
        if ($this->_cookie && $this->readFromCookie())
            return true;
        if ($this->_phpsession && $this->readFromPHPSession())
            return true;
        return $this->createSession();
    }

    /**
     * Create Session
     * 
     * @since 2.1.0
     * @access public
     * @return (array)session
     */
    public function createSession() {
        $obj = new stdClass();

        $obj->Timestamp = $this->getTimeStamp();
        $obj->Username = $this->getUsername();
        $obj->PinCode = $this->getPinCode();
        $obj->UserAgent = $this->getUserAgent();

        // Generate Checksum
        $obj->Checksum = $this->generateChecksum($obj);

        // PinCode only used for the Checksum
        unset($obj->PinCode);
        
        // Create the session and get the response
        $response = $this->client->CreateSession($obj);
        $result = $response->CreateSessionResult;

        // Verify response data by making a new Checksum
        $res = new stdClass();
        // Must be in specific order for checksum -----
        $res->Timestamp = $result->Timestamp;
        $res->SessionID = $result->SessionID;
        $res->PinCode = $this->getPinCode();
        $res->Success = $result->Success;
        $res->Description = $result->Description;
        // --------------------------------------------
        $checkSum = $this->generateChecksum($res);

        // Compare Checksums
        if ($result->Checksum != $checkSum)
            throw new Exception("Data could not be verified");

        // Assign Session
        $this->_session = $result;
        
        $this->createCookie(true);
        $this->createPHPSession(true);
        
        // Return Respsonse
        return (array) $response;
    }
    
    /* 
     * Set the session name
     * 
     * @since 2.1.0
     * @access public
     */
    public function setSessionName($name = "icepay_api_webservice_reportingsession") {
        $this->_sessionName = $name;
    }
    
    /*
     * Get the Session Timestamp
     * 
     * @since 2.1.0
     * @access private
     * @return string $timestamp
     */
    private function getSessionTimestamp() {        
        if ($this->_phpsession && isset($_SESSION[$this->_sessionName]->SessionID)) 
            $timestamp = $_SESSION[$this->_sessionName]->SessionID;
        
        
        if ($this->_cookie && isset($_COOKIE[$this->_sessionName . "_Timestamp"])) 
            $timestamp = $_COOKIE[$this->_sessionName . "_Timestamp"]; 
        
        return $timestamp;
    }

    /*
     * Kill the current session
     * 
     * @since 2.1.0
     * @access public
     * @return array $session
     */    
    public function killSession() { 
        $obj = new stdClass();      
        
        $obj->Timestamp = $this->getSessionTimestamp();
        $obj->SessionID = $this->getSessionID();        
        $obj->PinCode = $this->getPinCode();
        $obj->UserAgent = $this->getUserAgent();

        // Generate Checksum
        $obj->Checksum = $this->generateChecksum($obj);

        // PinCode only used for the Checksum
        unset($obj->Pincode);

        $session = $this->client->KillSession($obj);

        $this->unsetCookie();
        $this->unsetPHPSession();

        $this->_session = null;
        return (array) $session;
    }

    /**
     * The MonthlyTurnoverTotals web method returns the sum of the turnover of all the transactions according
     * to the provided criteria: month, year and currency.
     * 
     * @since version 2.1.0
     * @access public
     * @param int $month !required
     * @param int $year !required
     * @param string $currency
     */
    public function monthlyTurnoverTotals($month, $year, $currency = "EUR") {
        if ($month == "" || !is_numeric($month))
            throw new Exception('Please enter a valid month');
        if ($year == "" || !is_numeric($year))
            throw new Exception('Please enter a valid year');

        $obj = new stdClass();

        // Must be in specific order for checksum ------
        $obj->Timestamp = $this->getTimeStamp();
        $obj->SessionID = $this->getSessionID();
        $obj->ReportingPinCode = $this->getPinCode();
        $obj->UserAgent = $this->getUserAgent();
        $obj->MerchantID = $this->getMerchantID();
        $obj->CurrencyCode = $currency;
        $obj->Year = $year;
        $obj->Month = $month;
        // ---------------------------------------------        
        // Generate Checksum
        $obj->Checksum = $this->generateChecksum($obj);

        // PinCode only used for the Checksum
        unset($obj->PinCode);

        // Ask for MonthlyTurnoverTotals and get response
        $result = $this->client->MonthlyTurnoverTotals($obj);
        $result = $result->MonthlyTurnoverTotalsResult;
        $dayStats = $result->Days->DayStatistics;

        $obj->Timestamp = $result->Timestamp;

        // Assign all properties of the DayStatistics object as property of mainObject
        $obj = $this->parseForChecksum($obj, $dayStats, true, array("Year", "Month", "Day", "Duration", "TransactionsCount", "Turnover"));

        // Unset properties for new Checksum
        unset($obj->MerchantID, $obj->Checksum);

        // Verify response data by making a new Checksum
        $Checksum = $this->generateChecksum($obj);

        // Compare Checksums
        if ($result->Checksum != $Checksum)
            throw new Exception('Data could not be verified');

        return (array) $dayStats;
    }

    /**
     * The getMerchant web method returns a list of merchants that belong to your ICEPAY account.
     * 
     * @since version 2.1.0
     * @access public
     * @return array
     */
    public function getMerchants() {
        $obj = new stdClass();

        // Must be in specific order for checksum --
        $obj->Timestamp = $this->getTimeStamp();
        $obj->SessionID = $this->getSessionID();
        $obj->PinCode = $this->getPinCode();
        $obj->UserAgent = $this->getUserAgent();
        // -----------------------------------------
        // Generate Checksum
        $obj->Checksum = $this->generateChecksum($obj);

        // Ask for getMerchants and get response
        $result = $this->client->getMerChants($obj);
        $result = $result->GetMerchantsResult;
        $merchants = isset($result->Merchants->Merchant) ? $result->Merchants->Merchant : null;

        $obj->Timestamp = $result->Timestamp;

        if (!is_null($merchants)) {
            // Assign all properties of the Merchants object as property of mainObject
            $obj = $this->parseForChecksum($obj, $merchants, true, array("MerchantID", "Description", "TestMode"));
        }

        // Unset properties for new Checksum
        unset($obj->Checksum);

        // Verify response data by making a new Checksum
        $Checksum = $this->generateChecksum($obj);

        // Compare Checksums
        if ($result->Checksum != $Checksum)
            throw new Exception('Data could not be verified');

        return (array) $merchants;
    }

    /**
     * The getPaymentMethods web method returns a list of  all  supported payment methods by ICEPAY.
     * 
     * @since version 2.1.0
     * @access public
     */
    public function getPaymentMethods() {
        $obj = new stdClass();

        // Must be in specific order for checksum --
        $obj->Timestamp = $this->getTimeStamp();
        $obj->SessionID = $this->getSessionID();
        $obj->PinCode = $this->getPinCode();
        $obj->UserAgent = $this->getUserAgent();
        // -----------------------------------------
        // Generate Checksum
        $obj->Checksum = $this->generateChecksum($obj);

        // Ask for GetPaymentMethods and get response
        $result = $this->client->GetPaymentMethods($obj);
        $result = $result->GetPaymentMethodsResult;
        $methods = isset($result->PaymentMethods->PaymentMethod) ? $result->PaymentMethods->PaymentMethod : null;

        $obj->Timestamp = $result->Timestamp;

        if (!is_null($methods)) {
            // Assign all properties of the PaymentMethods object as property of mainObject
            $obj = $this->parseForChecksum($obj, $methods);
        }

        // Unset properties for new Checksum
        unset($obj->Checksum);

        // Verify response data by making a new Checksum
        $CheckSum = $this->generateChecksum($obj);

        // Compare Checksums
        if ($result->Checksum != $CheckSum)
            throw new Exception('Data could not be verified');

        return (array) $methods;
    }

    /**
     * The searchPayments web method allows you to search for payments linked to your ICEPAY account. There are 
     * several filters which you can employ for a more detailed search.
     * 
     * @since version 2.1.0
     * @access public
     * @param array searchOptions
     * @return array
     */
    public function searchPayments($searchOptions = array()) {

        $obj = new stdClass();
        // Must be in specific order for checksum ----------
        $obj->Timestamp = $this->getTimeStamp();
        $obj->SessionID = $this->getSessionID();
        $obj->PinCode = $this->getPinCode();
        $obj->UserAgent = $this->getUserAgent();
        $obj->MerchantID = null;
        $obj->PaymentID = null;
        $obj->OrderID = null;
        $obj->Reference = null;
        $obj->Description = null;
        $obj->Status = null;
        $obj->OrderTime1 = null;
        $obj->OrderTime2 = null;
        $obj->PaymentTime1 = null;
        $obj->PaymentTime2 = null;
        $obj->CountryCode = null;
        $obj->CurrencyCode = null;
        $obj->Amount = null;
        $obj->PaymentMethod = null;
        $obj->ConsumerAccountNumber = null;
        $obj->ConsumerName = null;
        $obj->ConsumerAddress = null;
        $obj->ConsumerHouseNumber = null;
        $obj->ConsumerPostCode = null;
        $obj->ConsumerCity = null;
        $obj->ConsumerCountry = null;
        $obj->ConsumerEmail = null;
        $obj->ConsumerPhoneNumber = null;
        $obj->ConsumerIPAddress = null;
        $obj->Page = (int) 1;
        // ------------------------------------------------

        if (!empty($searchOptions)) {
            foreach ($searchOptions as $key => $filter) {
                $obj->$key = $filter;
            }
        }
        
        // Generate Checksum
        $obj->Checksum = $this->generateChecksum($obj);

        // Properties only used for the Checksum
        unset($obj->PinCode);

        // Ask for SearchPayments and get response
        $result = $this->client->SearchPayments($obj);
        $result = $result->SearchPaymentsResult;

        $searchResults = isset($result->Payments->Payment) ? $result->Payments->Payment : null;

        $obj = new stdClass();
        $obj->Timestamp = $result->Timestamp;
        $obj->SessionID = $this->getSessionID();
        $obj->ReportingPinCode = $this->getPinCode();

        if (!is_null($searchResults)) {
            // Assign all properties of the sub object(s) as property of mainObject
            $obj = $this->parseForChecksum($obj, $searchResults, true, array(
                "Amount", "ConsumerAccountNumber", "ConsumerAddress", "ConsumerHouseNumber", "ConsumerName",
                "ConsumerPostCode", "CountryCode", "CurrencyCode", "Duration", "MerchantID", "OrderTime",
                "PaymentID", "PaymentMethod", "PaymentTime", "Status", "StatusCode", "TestMode"
                    ));
        }

        // Verify response data by making a new Checksum
        $CheckSum = $this->generateChecksum($obj);

        // Compare Checksums
        if ($result->Checksum != $CheckSum)
            throw new Exception('Data could not be verified');

        return (array) $searchResults;
    }

}

?>