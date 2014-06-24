<?php

/**
 *  ICEPAY PHP API
 *
 *  @version 2.1.3
 *  @author Olaf Abbenhuis
 *  @author Wouter van Tilburg
 *  @copyright Copyright (c) 2011-2012, ICEPAY
 *
 */

/**
 *  Interfaces
 * 
 *  @author Olaf Abbenhuis
 *  @since 2.1.0
 */
interface Icepay_PaymentObject_Interface_Abstract {

    public function setData($data);

    public function getData();

    public function setIssuer($issuer);

    public function getIssuer();

    public function setPaymentMethod($paymentmethod);

    public function getPaymentMethod();

    public function setCountry($country);

    public function getCountry();

    public function setCurrency($currency);

    public function getCurrency();

    public function setLanguage($lang);

    public function getLanguage();

    public function setAmount($amount);

    public function getAmount();

    public function setOrderID($id = "");

    public function getOrderID();

    public function setReference($reference = "");

    public function getReference();

    public function setDescription($info = "");

    public function getDescription();
}

interface Icepay_Basic_Paymentmethod_Interface_Abstract {

    public function getCode();

    public function getReadableName();

    public function getSupportedIssuers();

    public function getSupportedCountries();

    public function getSupportedCurrency();

    public function getSupportedLanguages();

    public function getSupportedAmountRange();
}

interface Icepay_WebserviceTransaction_Interface_Abstract {

    public function setData($data);

    public function getPaymentScreenURL();

    public function getPaymentID();

    public function getProviderTransactionID();

    public function getTestMode();

    public function getTimestamp();

    public function getEndUserIP();
}

/**
 *  The Transaction Object is returned when making a payment using the webservices
 * 
 *  @author Olaf Abbenhuis
 *  @since 2.1.0
 */
class Icepay_TransactionObject implements Icepay_WebserviceTransaction_Interface_Abstract {

    protected $data;

    public function setData($data) {
        $this->data = $data;
    }

    public function getPaymentScreenURL() {
        return $this->data->PaymentScreenURL;
    }

    public function getPaymentID() {
        return $this->data->PaymentID;
    }

    public function getProviderTransactionID() {
        return $this->data->ProviderTransactionID;
    }

    public function getTestMode() {
        return $this->data->TestMode;
    }

    public function getTimestamp() {
        return $this->data->Timestamp;
    }

    public function getEndUserIP() {
        return $this->data->EndUserIP;
    }

}

/**
 *  The Payment Object is the class for a payment. Can be instanced if desired, although the instance isnt used within the API.
 * 
 *  @author Olaf Abbenhuis
 *  @since 2.1.1
 */
class Icepay_PaymentObject implements Icepay_PaymentObject_Interface_Abstract {

    protected $data;
    protected $api_type = "webservice";
    protected $pm_class;
    private static $instance;

    /**
     * Construct of Icepay_PaymentObject
     * @since version 2.1.1
     * @access public 
     */
    public function __construct() {
        // Instantiate $this->data explicitely for PHP Strict error reporting
        $this->data = new stdClass();
    }

    public static function getInstance() {
        if (!self::$instance)
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Set all fields in one method
     * @since version 2.1.0
     * @access public
     * @param object $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * Get all data as an object
     * @since version 2.1.0
     * @access public
     * @return object 
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Load a paymentmethod class for Basic
     * @since version 2.1.0
     * @access protected
     */
    protected function loadBasicPaymentMethodClass() {

        if (!class_exists("Icepay_Api_Basic"))
            return $this;

        $this->pm_class = Icepay_Api_Basic::getInstance()
                ->readFolder()
                ->getClassByPaymentMethodCode($this->data->ic_paymentmethod);

        if (count($this->pm_class->getSupportedIssuers()) == 1) {
            $this->setIssuer(current($this->pm_class->getSupportedIssuers()));
        }
        return $this;
    }

    /**
     * Get all data as an object
     * @since version 2.1.0
     * @access public
     * @return Icepay_Basic_Paymentmethod_Interface_Abstract 
     */
    public function getBasicPaymentmethodClass() {
        return $this->pm_class;
    }

    /**
     * Set the country field
     * @since version 1.0.0
     * @access public
     * @param string $currency Country ISO 3166-1-alpha-2 code !Required
     * @example setCountry("NL") // Netherlands
     */
    public function setCountry($country) {
        $country = strtoupper($country);
        if (!Icepay_Parameter_Validation::country($country))
            throw new Exception('Country not valid');
        $this->data->ic_country = $country;
        return $this;
    }

    /**
     * Set the currency field
     * @since version 1.0.0
     * @access public
     * @param string $currency Language ISO 4217 code !Required
     * @example setCurrency("EUR") // Euro
     */
    public function setCurrency($currency) {
        $this->data->ic_currency = $currency;
        return $this;
    }

    /**
     * Set the language field
     * @since version 1.0.0
     * @access public
     * @param string $lang Language ISO 639-1 code !Required
     * @example setLanguage("EN") // English
     */
    public function setLanguage($lang) {
        if (!Icepay_Parameter_Validation::language($lang))
            throw new Exception('Language not valid');
        $this->data->ic_language = $lang;
        return $this;
    }

    /**
     * Set the amount field
     * @since version 1.0.0
     * @access public
     * @param int $amount !Required
     */
    public function setAmount($amount) {
        intval($amount);
        if (!Icepay_Parameter_Validation::amount($amount))
            throw new Exception('Amount not valid');
        $this->data->ic_amount = $amount;
        return $this;
    }

    /**
     * Set the order ID field (optional)
     * @since version 1.0.0
     * @access public
     * @param string $id
     */
    public function setOrderID($id = "") {
        $this->data->ic_orderid = $id;
        return $this;
    }

    /**
     * Set the reference field (optional)
     * @since version 1.0.0
     * @access public
     * @param string $reference
     */
    public function setReference($reference = "") {
        $this->data->ic_reference = $reference;
        return $this;
    }

    /**
     * Set the description field (optional)
     * @since version 1.0.0
     * @access public
     * @param string $info
     */
    public function setDescription($info = "") {
        $this->data->ic_description = $info;
        return $this;
    }

    /**
     * Sets the issuer and checks if the issuer exists within the paymentmethod
     * @since version 1.0.0
     * @access public
     * @param string $issuer ICEPAY Issuer code
     */
    public function setIssuer($issuer) {
        $this->data->ic_issuer = $issuer;
        return $this;
    }

    /**
     * Sets the payment method and checks if the method exists within the class
     * @since version 1.0.0
     * @access public
     * @param string $paymentMethod ICEPAY Payment method code
     */
    public function setPaymentMethod($paymentMethod) {
        $this->data->ic_paymentmethod = $paymentMethod;
        $this->loadBasicPaymentMethodClass();
        return $this;
    }

    public function getCountry() {
        return $this->data->ic_country;
    }

    public function getCurrency() {
        return $this->data->ic_currency;
    }

    public function getLanguage() {
        return $this->data->ic_language;
    }

    public function getAmount() {
        return $this->data->ic_amount;
    }

    public function getOrderID() {
        return $this->data->ic_orderid;
    }

    public function getReference() {
        return (isset($this->data->ic_reference) ? $this->data->ic_reference : null);
    }

    public function getDescription() {
        return (isset($this->data->ic_description) ? $this->data->ic_description : null);
    }

    public function getIssuer() {
        return (isset($this->data->ic_issuer) ? $this->data->ic_issuer : null);
    }

    public function getPaymentMethod() {
        return (isset($this->data->ic_paymentmethod) ? $this->data->ic_paymentmethod : null);
    }

}

/**
 *  The Icepay_Paymentmethod is the base class for all payment method subclasses
 * 
 *  @author Olaf Abbenhuis
 *  @since 2.1.0
 */
class Icepay_Paymentmethod implements Icepay_Basic_Paymentmethod_Interface_Abstract {

    public $_version = null;
    public $_method = null;
    public $_readable_name = null;
    public $_issuer = null;
    public $_country = null;
    public $_language = null;
    public $_currency = null;
    public $_amount = null;

    /**
     * Get the version of the API or the loaded payment method class
     * @since version 1.0.1
     * @access public
     * @return string
     */
    public function getCode() {
        return $this->_method;
    }

    /**
     * Get the version of the API or the loaded payment method class
     * @since version 1.0.1
     * @access public
     * @return string
     */
    public function getReadableName() {
        return $this->_readable_name;
    }

    /**
     * Get the supported issuers of the loaded paymentmethod
     * @since version 1.0.0
     * @access public
     * @return array The issuer codes of the paymentmethod
     */
    public function getSupportedIssuers() {
        return $this->_issuer;
    }

    /**
     * Get the supported countries of the loaded paymentmethod
     * @since version 1.0.0
     * @access public
     * @return array The country codes of the paymentmethod
     */
    public function getSupportedCountries() {
        return $this->_country;
    }

    /**
     * Get the supported currencies of the loaded paymentmethod
     * @since version 1.0.0
     * @access public
     * @return array The currency codes of the paymentmethod
     */
    public function getSupportedCurrency() {
        return $this->_currency;
    }

    /**
     * Get the supported languages of the loaded paymentmethod
     * @since version 1.0.0
     * @access public
     * @return array The Language codes of the paymentmethod
     */
    public function getSupportedLanguages() {
        return $this->_language;
    }

    /**
     * Get the general amount range of the loaded paymentmethod
     * @since version 1.0.0
     * @access public
     * @return array [minimum(uint), maximum(uint)]
     */
    public function getSupportedAmountRange() {
        return $this->_amount;
    }

}

/**
 *  Icepay_StatusCode static class
 *  Contains the payment statuscode constants
 * 
 *  @author Olaf Abbenhuis
 *  @since 1.0.0
 */
class Icepay_StatusCode {

    const OPEN = "OPEN";
    const ERROR = "ERR";
    const SUCCESS = "OK";
    const REFUND = "REFUND";
    const CHARGEBACK = "CBACK";

}

/**
 *  Icepay_Project_Helper class
 *  A helper for all-in-one solutions
 * 
 *  @author Olaf Abbenhuis
 *  @since 1.0.0
 *
 */
class Icepay_Project_Helper {

    private static $instance;
    private $_release = "2.1.3";
    private $_basic;
    private $_result;
    private $_postback;
    private $_validate;

    /**
     * Returns the Icepay_Basicmode class or creates it
     * 
     * @since 1.0.0
     * @access public
     * @return \Icepay_Basicmode
     */
    public function basic() {
        if (!isset($this->_basic))
            $this->_basic = new Icepay_Basicmode();
        return $this->_basic;
    }

    /**
     * Returns the Icepay_Result class or creates it
     * 
     * @since 1.0.0
     * @access public
     * @return \Icepay_Result
     */
    public function result() {
        if (!isset($this->_result))
            $this->_result = new Icepay_Result();
        return $this->_result;
    }

    /**
     * Returns the Icepay_Postback class or creates it
     * 
     * @since 1.0.0
     * @access public
     * @return \Icepay_Postback
     */
    public function postback() {
        if (!isset($this->_postback))
            $this->_postback = new Icepay_Postback();
        return $this->_postback;
    }

    /**
     * Returns the Icepay_Paramater_Validation class or creates it
     * 
     * @since 1.1.0
     * @access public
     * @return \Icepay_Parameter_Validation
     */
    public function validate() {
        if (!isset($this->_validate))
            $this->_postback = new Icepay_Parameter_Validation();
        return $this->_validate;
    }

    /**
     * Returns the current release version
     * 
     * @since 1.1.0
     * @access public
     * @return string 
     */
    public function getReleaseVersion() {
        return $this->_release;
    }

    /**
     * Create an instance
     * @since version 1.0.2
     * @access public
     * @return instance of self
     */
    public static function getInstance() {
        if (!self::$instance)
            self::$instance = new self();
        return self::$instance;
    }

}

/**
 *  Icepay_Api_Base class
 *  Basic Setters and Getters required in most API
 * 
 *  @author Olaf Abbenhuis
 *  @author Wouter van Tilburg
 *  @since 1.0.0
 *  @version 1.0.2
 *
 */
class Icepay_Api_Base {

    private $_pinCode;
    protected $_merchantID;
    protected $_secretCode;
    protected $_method = null;
    protected $_issuer = null;
    protected $_country = null;
    protected $_language = null;
    protected $_currency = null;
    protected $_version = "1.0.2";
    protected $_doIPCheck = array();
    protected $_whiteList = array();
    protected $data;
    protected $_logger;

    public function __construct() {
        $this->_logger = Icepay_Api_Logger::getInstance();
        $this->data = new stdClass();
    }

    /**
     * Validate data
     * @since version 1.0.0
     * @access public
     * @param string $needle
     * @param array $haystack
     * @return boolean
     */
    public function exists($needle, $haystack = null) {
        $result = true;
        if ($haystack && $result && $haystack[0] != "00")
            $result = in_array($needle, $haystack);
        return $result;
    }

    /**
     * Use IP Check
     * @since 1.0.1
     * @access public
     * @param boolean $bool
     */
    public function doIPCheck($bool = true) {
        // IP Range of ICEPAY servers - Do not change unless asked to.
        $this->setIPRange('194.30.175.0', '194.30.175.255');
        $this->setIPRange('194.126.241.128', '194.126.241.191');

        $this->_doIPCheck = $bool;

        return $this;
    }

    /**
     * Add ip(s) to whitelist
     * 
     * @example '1.1.1.1', '1.1.1.1-1.1.1.2'
     * @since 2.1.2
     * @access public
     * @param type $string 
     */
    public function addToWhitelist($string) {
        // Remove whitespaces
        $string = str_replace(' ', '', $string);

        // Seperate ip's
        $ipRanges = explode(",", $string);

        foreach ($ipRanges as $ip) {
            // Explode for range
            $ip = explode("-", $ip);
            
            if (count($ip) > 1) {
                $this->setIPRange($ip[0], $ip[1]);
            } else {
                $this->setIPRange($ip[0], $ip[0]);
            }
        }
    }

    /**
     * Set the IP range
     * @since 1.0.1
     * @access public
     * @param string $start
     * @param string $end
     */
    public function setIPRange($start, $end) {
        $start = str_replace(' ', '', $start);
        $end = str_replace(' ', '', $end);
        $this->_whiteList[] = array('start' => $start, 'end' => $end);

        return $this;
    }

    /**
     * Get the version of the API or the loaded payment method class
     * @since 1.0.0
     * @access public
     * @return string Version
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * Set the Merchant ID field
     * @since 1.0.0
     * @access public
     * @param (int) $merchantID
     */
    public function setMerchantID($merchantID) {
        if (!Icepay_Parameter_Validation::merchantID($merchantID))
            throw new Exception('MerchantID not valid');

        $this->_merchantID = ( int ) $merchantID;

        return $this;
    }

    /**
     * Get the Merchant ID field
     * @since 1.0.0
     * @access public
     * @return (int) MerchantID
     */
    public function getMerchantID() {
        return $this->_merchantID;
    }

    /**
     * Set the Secret Code field
     * @since 1.0.0
     * @access public
     * @param (string) $secretCode
     */
    public function setSecretCode($secretCode) {
        if (!Icepay_Parameter_Validation::secretCode($secretCode))
            throw new Exception('Secretcode not valid');

        $this->_secretCode = ( string ) $secretCode;
        return $this;
    }

    /**
     * Get the Secret Code field
     * @since 1.0.0
     * @access protected
     * @return (string) Secret Code
     */
    protected function getSecretCode() {
        return $this->_secretCode;
    }

    /**
     * Set the Pin Code field
     * @since 1.0.1
     * @access public
     * @param (int) $pinCode 
     */
    public function setPinCode($pinCode) {
        if (!Icepay_Parameter_Validation::pinCode($pinCode))
            throw new Exception('Pincode not valid');

        $this->_pinCode = ( string ) $pinCode;

        return $this;
    }

    /**
     * Get the Pin Code field
     * @since 1.0.0
     * @access protected
     * @return (int) PinCode
     */
    protected function getPinCode() {
        return $this->_pinCode;
    }

    /**
     * Set the success url field (optional)
     * @since version 1.0.1
     * @access public
     * @param string $url
     */
    public function setSuccessURL($url = "") {
        $this->data->ic_urlcompleted = $url;
        return $this;
    }

    /**
     * Set the error url field (optional)
     * @since version 1.0.1
     * @access public
     * @param string $url
     */
    public function setErrorURL($url = "") {
        $this->data->ic_urlerror = $url;
        return $this;
    }

    /**
     * Get the success URL
     * @since version 2.1.0
     * @access public
     * @return string $url
     */
    public function getSuccessURL() {
        return (isset($this->data->ic_urlcompleted)) ? $this->data->ic_urlcompleted : "";
    }

    /**
     * Get the error URL
     * @since version 2.1.0
     * @access public
     * @return string $url
     */
    public function getErrorURL() {
        return (isset($this->data->ic_urlerror)) ? $this->data->ic_urlerror : "";
    }

    /**
     * Check if the ip is in range
     * @since version 2.1.0
     * @access protected
     * @param string $ip IP used within the request
     * @param string $range Allowed range
     * @return boolean
     */
    protected function ip_in_range($ip, $range) {
        if (strpos($range, '/') !== false) {
            // $range is in IP/NETMASK format
            list($range, $netmask) = explode('/', $range, 2);
            if (strpos($netmask, '.') !== false) {
                // $netmask is a 255.255.0.0 format
                $netmask = str_replace('*', '0', $netmask);
                $netmask_dec = ip2long($netmask);
                return ( (ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec) );
            } else {
                // $netmask is a CIDR size block
                // fix the range argument
                $x = explode('.', $range);
                while (count($x) < 4)
                    $x[] = '0';
                list($a, $b, $c, $d) = $x;
                $range = sprintf("%u.%u.%u.%u", empty($a) ? '0' : $a, empty($b) ? '0' : $b, empty($c) ? '0' : $c, empty($d) ? '0' : $d);
                $range_dec = ip2long($range);
                $ip_dec = ip2long($ip);

                # Strategy 1 - Using substr to chop up the range and pad it with 1s to the right
                $broadcast_dec = bindec(substr($this->decbin32($range_dec), 0, $netmask)
                        . str_pad('', 32 - $netmask, '1'));

                # Strategy 2 - Use math to OR the range with the wildcard to create the Broadcast address
                $wildcard_dec = pow(2, (32 - $netmask)) - 1;
                $broadcast_dec = $range_dec | $wildcard_dec;

                return (($ip_dec & $broadcast_dec) == $ip_dec);
            }
        } else {
            // range might be 255.255.*.* or 1.2.3.0-1.2.3.255
            if (strpos($range, '*') !== false) { // a.b.*.* format
                // Just convert to A-B format by setting * to 0 for A and 255 for B
                $lower = str_replace('*', '0', $range);
                $upper = str_replace('*', '255', $range);
                $range = "$lower-$upper";
            }

            if (strpos($range, '-') !== false) { // A-B format
                list($lower, $upper) = explode('-', $range, 2);
                $lower_dec = ip2long($lower);
                $upper_dec = ip2long($upper);
                $ip_dec = ip2long($ip);
                return ( ($ip_dec >= $lower_dec) && ($ip_dec <= $upper_dec) );
            }

            return false;
        }

        $ip_dec = ip2long($ip);
        return (($ip_dec & $netmask_dec) == $ip_dec);
    }

}

/**
 * Icepay_Parameter_Validation class
 * Validates parameters
 * 
 * @author Olaf Abbenhuis
 * @since 2.1.0
 */
class Icepay_Parameter_Validation {

    protected $_version = "1.0.0";

    /**
     * Check if Merchant ID is valid
     * 
     * @since 2.1.0
     * @access public
     * @param string $string
     * @return bool
     */
    public static function merchantID($string) {
        return (strlen($string) == 5 && is_numeric($string));
    }

    /**
     * Check if Secret Code is valid
     * 
     * @since 2.1.0
     * @access public
     * @param sring $string
     * @return bool 
     */
    public static function secretCode($string) {
        return (strlen($string) == 40 && !is_numeric($string));
    }

    /**
     * Check if Pin Code is valid
     * 
     * @since 2.1.0
     * @access public
     * @param string $string
     * @return bool 
     */
    public static function pinCode($string) {
        return (strlen($string) == 8 && is_numeric($string));
    }

    /**
     * Check if Country is valid
     * 
     * @since 2.1.0
     * @access public
     * @param string $string
     * @return bool 
     */
    public static function country($string) {
        return (strlen($string) == 2);
    }

    /**
     * Check if Language is valid
     * 
     * @since 2.1.0
     * @access public
     * @param string $string
     * @return bool 
     */
    public static function language($string) {
        return (strlen($string) == 2 && !is_numeric($string));
    }

    /**
     * Check if Currency is valid
     * 
     * @since 2.1.0
     * @access public
     * @param string $string
     * @return bool
     */
    public static function currency($string) {
        return (strlen($string) == 3 && !is_numeric($string));
    }

    /**
     * Check if Amount is valid
     * 
     * @since 2.1.0
     * @access public
     * @param int $number
     * @return bool
     */
    public static function amount($number) {
        return (is_numeric($number));
    }

}

/**
 * Icepay_Api_Logger
 * Handles all the logging
 * 
 * @author Olaf Abbenhuis
 * @author Wouter van Tilburg
 * @since 2.1.0
 */
class Icepay_Api_Logger {

    private static $instance;

    const NOTICE = 1;
    const TRANSACTION = 2;
    const ERROR = 4;
    const LEVEL_ALL = 1;
    const LEVEL_TRANSACTION = 2;
    const LEVEL_ERRORS = 4;
    const LEVEL_ERRORS_AND_TRANSACTION = 8;

    private $version = '1.0.0';
    protected $_loggingDirectory = 'logs';
    protected $_loggingFile = 'log.txt';
    protected $_loggingEnabled = false;
    protected $_logToFile = false;
    protected $_logToScreen = false;
    protected $_logToHook = false;
    protected $_logHookClass = null;
    protected $_logHookFunc = null;
    protected $_logLevel = 14; // Log errors and transactions

    /**
     * Enables logging 
     *  
     * @since 2.1.0
     * @access public  
     * @return \Icepay_Basicmode
     */

    public function enableLogging($bool = true) {
        $this->_loggingEnabled = $bool;

        return $this;
    }

    /**
     * Enables logging to file
     * 
     * @since 2.1.0
     * @access public 
     * @param bool $bool
     * @return \Icepay_Basicmode
     */
    public function logToFile($bool = true) {
        $this->_logToFile = $bool;

        return $this;
    }

    /**
     * Enables logging to screen
     * 
     * @since 2.1.0
     * @access public
     * @param bool $bool
     * @return \Icepay_Basicmode
     */
    public function logToScreen($bool = true) {
        $this->_logToScreen = $bool;

        return $this;
    }

    /**
     * Enable or disable logging to a hooked class
     * 
     * @since 2.1.0
     * @access public
     * @param string $className
     * @param string $logFunction 
     * @param bool $bool
     * @return \Icepay_Basicmode
     */
    public function logToFunction($className = null, $logFunction = null, $bool = true) {
        $this->_logToHook = $bool;

        if (class_exists($className))
            $this->_logHookClass = new $className;

        if (is_callable($logFunction))
            $this->_logHookFunc = $logFunction;

        return $this;
    }

    /**
     * Set the directory of the logging file
     * 
     * @since 2.1.0
     * @access public 
     * @param type $dirName 
     * @return \Icepay_Basicmode
     */
    public function setLoggingDirectory($dirName = null) {
        if ($dirName)
            $this->_loggingDirectory = $dirName;

        return $this;
    }

    /**
     * Set the logging file
     * 
     * @since 2.1.0
     * @access public
     * @param string $fileName 
     * @return \Icepay_Basicmode
     */
    public function setLoggingFile($fileName = null) {
        if ($fileName)
            $this->_loggingFile = $fileName;

        return $this;
    }

    /**
     * Set the logging level
     * 
     * @since 2.1.0
     * @access public
     * @param int $level 
     */
    public function setLoggingLevel($level) {
        switch ($level) {
            case Icepay_Api_Logger::LEVEL_ALL:
                $this->_setLoggingFlag(Icepay_Api_Logger::NOTICE);
                $this->_setLoggingFlag(Icepay_Api_Logger::TRANSACTION);
                $this->_setLoggingFlag(Icepay_Api_Logger::ERROR);
                break;
            case Icepay_Api_Logger::LEVEL_ERRORS:
                $this->_setLoggingFlag(Icepay_Api_Logger::NOTICE, false);
                $this->_setLoggingFlag(Icepay_Api_Logger::TRANSACTION, false);
                $this->_setLoggingFlag(Icepay_Api_Logger::ERROR);
                break;
            case Icepay_Api_Logger::LEVEL_TRANSACTION:
                $this->_setLoggingFlag(Icepay_Api_Logger::NOTICE, false);
                $this->_setLoggingFlag(Icepay_Api_Logger::TRANSACTION);
                $this->_setLoggingFlag(Icepay_Api_Logger::ERROR, false);
                break;
            case Icepay_Api_Logger::LEVEL_ERRORS_AND_TRANSACTION:
                $this->_setLoggingFlag(Icepay_Api_Logger::NOTICE, false);
                $this->_setLoggingFlag(Icepay_Api_Logger::TRANSACTION);
                $this->_setLoggingFlag(Icepay_Api_Logger::ERROR);
                break;
        }

        return $this;
    }

    /*
     * Set the logging flag
     * 
     * @since 2.1.0
     * @access private
     * @param int $flag
     * @param bool $boolean
     */

    private function _setLoggingFlag($flag, $boolean = true) {
        if ($boolean) {
            $this->_logLevel |= $flag;
        } else {
            $this->_logLevel &= ~$flag;
        }
    }

    /*
     * Check if type is exists 
     * 
     * @since 2.1.0
     * @access private
     * @param int $type
     * @return bool
     */

    private function _isLoggingSet($type) {
        return (($this->_logLevel & $type) == $type);
    }

    /**
     * Log given line
     * 
     * @since 2.1.0
     * @access public
     * @param string $line
     * @param int $level
     * @return boolean
     * @throws Exception 
     */
    public function log($line, $level = 1) {
        // Check if logging is enabled
        if (!$this->_loggingEnabled)
            return false;
        
        // Check if the level is within the required level
        if (!$this->_isLoggingSet($level))
            return false;

        $dateTime = date("H:i:s", time());
        $line = "{$dateTime} [ICEPAY]: {$line}" . PHP_EOL;

        // Log to Screen
        if ($this->_logToScreen)
            echo "{$line} <br />";

        // Log to Hooked Class
        if ($this->_logToHook && $this->_logHookClass && $this->_logHookFunc) {
            $function = $this->_logHookFunc;
            $this->_logHookClass->$function($line);
        }

        
        // Log to Default File
        if ($this->_logToFile) {
            $file = $this->_loggingDirectory . DS . $this->_loggingFile;
            
            echo $file;
            try {
                $fp = fopen($file, "a");
                fwrite($fp, $line);
                fclose($fp);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            };
        }
    }

    /**
     * Get version of API Logger
     * 
     * @since 2.1.0
     * @access public
     * @return version 
     */
    public function getVersion() {
        return $this->version;
    }

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

}

/**
 *  Icepay_Postback class
 *  To handle the postback
 * 
 *  @author Olaf Abbenhuis
 *  @author Wouter van Tilburg
 * 
 *  @since 1.0.0
 */
class Icepay_Postback extends Icepay_Api_Base {

    public function __construct() {
        parent::__construct();
        $this->data = new stdClass();
    }

    /**
     * Return minimized transactional data
     * @since version 1.0.0
     * @access public
     * @return string
     */
    public function getTransactionString() {
        return sprintf(
                        "Paymentmethod: %s \n| OrderID: %s \n| Status: %s \n| StatusCode: %s \n| PaymentID: %s \n| TransactionID: %s \n| Amount: %s", isset($this->data->paymentMethod) ? $this->data->paymentMethod : "", isset($this->data->orderID) ? $this->data->orderID : "", isset($this->data->status) ? $this->data->status : "", isset($this->data->statusCode) ? $this->data->statusCode : "", isset($this->data->paymentID) ? $this->data->paymentID : "", isset($this->data->transactionID) ? $this->data->transactionID : "", isset($this->data->amount) ? $this->data->amount : ""
        );
    }

    /**
     * Return the statuscode field
     * @since version 1.0.0
     * @access public
     * @return string
     */
    public function getStatus() {
        return (isset($this->data->status)) ? $this->data->status : null;
    }

    /**
     * Return the orderID field
     * @since version 1.0.0
     * @access public
     * @return string
     */
    public function getOrderID() {
        return (isset($this->data->orderID)) ? $this->data->orderID : null;
    }

    /**
     * Return the postback checksum
     * @since version 1.0.0
     * @access protected
     * @return string SHA1 encoded
     */
    protected function generateChecksumForPostback() {
        return sha1(
                        sprintf("%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s", $this->_secretCode, $this->_merchantID, $this->data->status, $this->data->statusCode, $this->data->orderID, $this->data->paymentID, $this->data->reference, $this->data->transactionID, $this->data->amount, $this->data->currency, $this->data->duration, $this->data->consumerIPAddress
                        )
        );
    }

    /**
     * Return the version checksum
     * @since version 1.0.2
     * @access protected
     * @return string SHA1 encoded
     */
    protected function generateChecksumForVersion() {
        return sha1(
                        sprintf("%s|%s|%s|%s", $this->_secretCode, $this->_merchantID, $this->data->status, substr(strval(time()), 0, 8)
                        )
        );
    }

    /**
     * Returns the postback response parameter names, useful for a database install script
     * @since version 1.0.1
     * @access public
     * @return array
     */
    public function getPostbackResponseFields() {
        return array(
            //object reference name => post param name
            "status" => "Status",
            "statusCode" => "StatusCode",
            "merchant" => "Merchant",
            "orderID" => "OrderID",
            "paymentID" => "PaymentID",
            "reference" => "Reference",
            "transactionID" => "TransactionID",
            "consumerName" => "ConsumerName",
            "consumerAccountNumber" => "ConsumerAccountNumber",
            "consumerAddress" => "ConsumerAddress",
            "consumerHouseNumber" => "ConsumerHouseNumber",
            "consumerCity" => "ConsumerCity",
            "consumerCountry" => "ConsumerCountry",
            "consumerEmail" => "ConsumerEmail",
            "consumerPhoneNumber" => "ConsumerPhoneNumber",
            "consumerIPAddress" => "ConsumerIPAddress",
            "amount" => "Amount",
            "currency" => "Currency",
            "duration" => "Duration",
            "paymentMethod" => "PaymentMethod",
            "checksum" => "Checksum");
    }

    /**
     * Validate for version check
     * @since version 1.0.2
     * @access public
     * @return boolean
     */
    public function validateVersion() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->_logger->log('Invalid request method', Icepay_Api_Logger::ERROR);
            return false;
        }

        if ($this->generateChecksumForVersion() != $this->data->checksum) {
            $this->_logger->log('Checksum does not match', Icepay_Api_Logger::ERROR);
            return false;
        }

        return true;
    }

    /**
     * Has Version Check status
     * @since version 1.0.2
     * @access public
     * @return boolean
     */
    public function isVersionCheck() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->_logger->log('Invalid request method', Icepay_Api_Logger::ERROR);
            return false;
        }

        if ($this->data->status != "VCHECK")
            return false;

        return true;
    }

    /**
     * Validate the postback data
     * @since version 1.0.0
     * @access public
     * @return boolean
     */
    public function validate() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->_logger->log("Invalid request method", Icepay_Api_Logger::ERROR);
            return false;
        };

        $this->_logger->log(sprintf("Postback: %s", serialize($_POST)), Icepay_Api_Logger::TRANSACTION);

        /* @since version 1.0.2 */
        foreach ($this->getPostbackResponseFields() as $obj => $param) {
            $this->data->$obj = (isset($_POST[$param])) ? $_POST[$param] : "";
        }

        if ($this->isVersionCheck())
            return false;

        if ($this->_doIPCheck) {
            $check = false;

            foreach ($this->_whiteList as $ip) {
                $start = $ip['start'];
                $end = $ip['end'];
                if ($this->ip_in_range($_SERVER['REMOTE_ADDR'], "{$start}-{$end}")) {
                    $check = true;
                    break;
                }
            }

            if (!$check) {
                $this->_logger->log("IP Address not in range: {$_SERVER['REMOTE_ADDR']}", Icepay_Api_Logger::ERROR);
                return false;
            }
        }

        if (!Icepay_Parameter_Validation::merchantID($this->data->merchant)) {
            $this->_logger->log("Merchant ID is not numeric: {$this->data->merchant}", Icepay_Api_Logger::ERROR);
            return false;
        }

        if (!Icepay_Parameter_Validation::amount($this->data->amount)) {
            $this->_logger->log("Amount is not numeric: {$this->data->amount}", Icepay_Api_Logger::ERROR);
            return false;
        }

        if ($this->_merchantID != $this->data->merchant) {
            $this->_logger->log("Invalid Merchant ID: {$this->data->merchant}", Icepay_Api_Logger::ERROR);
            return false;
        }

        if (!in_array(strtoupper($this->data->status), array(
                    Icepay_StatusCode::OPEN,
                    Icepay_StatusCode::SUCCESS,
                    Icepay_StatusCode::ERROR,
                    Icepay_StatusCode::REFUND,
                    Icepay_StatusCode::CHARGEBACK
                ))) {
            $this->_logger->log("Unknown status: {$this->data->status}", Icepay_Api_Logger::ERROR);
            return false;
        }

        if ($this->generateChecksumForPostback() != $this->data->checksum) {
            $this->_logger->log("Checksum does not match", Icepay_Api_Logger::ERROR);
            return false;
        }
        return true;
    }

    /**
     * Return the postback data
     * @since version 1.0.0
     * @access public
     * @return object
     */
    public function getPostback() {
        return $this->data;
    }

    /**
     * Check between ICEPAY statuscodes whether the status can be updated.
     * @since version 1.0.0
     * @access public
     * @param string $currentStatus The ICEPAY statuscode of the order before a statuschange
     * @return boolean
     */
    public function canUpdateStatus($currentStatus) {
        if (!isset($this->data->status)) {
            $this->_logger->log("Status not set", Icepay_Api_Logger::ERROR);
            return false;
        }
        switch ($this->data->status) {
            case Icepay_StatusCode::SUCCESS: return ($currentStatus == Icepay_StatusCode::OPEN);
            case Icepay_StatusCode::OPEN: return ($currentStatus == Icepay_StatusCode::OPEN);
            case Icepay_StatusCode::ERROR: return ($currentStatus == Icepay_StatusCode::OPEN);
            case Icepay_StatusCode::CHARGEBACK: return ($currentStatus == Icepay_StatusCode::SUCCESS);
            case Icepay_StatusCode::REFUND: return ($currentStatus == Icepay_StatusCode::SUCCESS);
            default:
                return false;
        };
    }

}

/**
 *  Icepay_Result class
 *  To handle the success and error page
 * 
 *  @author Olaf Abbenhuis
 *  @since 1.0.0
 */
class Icepay_Result extends Icepay_Api_Base {

    public function __construct() {
        parent::__construct();
        $this->data = new stdClass();
    }

    /**
     * Validate the ICEPAY GET data
     * @since version 1.0.0
     * @access public
     * @return boolean
     */
    public function validate() {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            $this->_logger->log("Invalid request method", Icepay_Api_Logger::ERROR);
            return false;
        }

        $this->_logger->log(sprintf("Page data: %s", serialize($_GET)), Icepay_Api_Logger::NOTICE);

        $this->data->status = (isset($_GET['Status'])) ? $_GET['Status'] : "";
        $this->data->statusCode = (isset($_GET['StatusCode'])) ? $_GET['StatusCode'] : "";
        $this->data->merchant = (isset($_GET['Merchant'])) ? $_GET['Merchant'] : "";
        $this->data->orderID = (isset($_GET['OrderID'])) ? $_GET['OrderID'] : "";
        $this->data->paymentID = (isset($_GET['PaymentID'])) ? $_GET['PaymentID'] : "";
        $this->data->reference = (isset($_GET['Reference'])) ? $_GET['Reference'] : "";
        $this->data->transactionID = (isset($_GET['TransactionID'])) ? $_GET['TransactionID'] : "";
        $this->data->checksum = (isset($_GET['Checksum'])) ? $_GET['Checksum'] : "";

        if ($this->generateChecksumForPage() != $this->data->checksum) {
            $this->_logger->log("Checksum does not match", Icepay_Api_Logger::ERROR);
            return false;
        }

        return true;
    }

    /**
     * Get the ICEPAY status
     * @since version 1.0.0
     * @access public
     * @param boolean $includeStatusCode Add the statuscode message to the returned string for display purposes
     * @return string ICEPAY statuscode (and statuscode message)
     */
    public function getStatus($includeStatusCode = false) {
        if (!isset($this->data->status))
            return null;
        return ($includeStatusCode) ? sprintf("%s: %s", $this->data->status, $this->data->statusCode) : $this->data->status;
    }

    /**
     * Return the orderID field
     * @since version 1.0.2
     * @access public
     * @return string
     */
    public function getOrderID() {
        return (isset($this->data->orderID)) ? $this->data->orderID : null;
    }

    /**
     * Return the result page checksum
     * @since version 1.0.0
     * @access protected
     * @return string SHA1 hash
     */
    protected function generateChecksumForPage() {
        return sha1(
                        sprintf("%s|%s|%s|%s|%s|%s|%s|%s", $this->_secretCode, $this->data->merchant, $this->data->status, $this->data->statusCode, $this->data->orderID, $this->data->paymentID, $this->data->reference, $this->data->transactionID
                        )
        );
    }

    /**
     * Return the get data
     * @since version 1.0.1
     * @access public
     * @return object
     */
    public function getResultData() {
        return $this->data;
    }

}

?>