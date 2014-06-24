<?php

/**
 *  ICEPAY Basicmode API library
 *
 *  @version 2.1.0
 *  @author Olaf Abbenhuis
 *  @author Wouter van Tilburg
 *  @copyright Copyright (c) 2012, ICEPAY
 *
 */

// Define constants
if(!defined('DIR')) {
    define("DIR", realpath(dirname(__FILE__)));
}

if(!defined('DS')) {
    define("DS", DIRECTORY_SEPARATOR);
}

// Include API base 
require_once(DIR . DS .  "icepay_api_base.php");




/**
 *  Icepay_Api_Basic class
 *  Loads and filters the paymentmethod classes
 *  @author Olaf Abbenhuis
 *
 * @var $instance Instance Class object
 * @var string $_content The contents of the files for the fingerprint
 * @var string $_folderPaymentMethods Folder of paymentmethod classes
 * @var array $paymentMethods List of all classes
 * @var array $_paymentMethods Filtered list
 *
 */
class Icepay_Api_Basic extends Icepay_Api_Base {

    private static $instance;
    private $version = "1.0.2";
    private $_folderPaymentMethods;
    private $paymentMethods = null; // Classes
    private $_paymentMethodsObject = null; // Loaded classes
    private $_paymentMethod = null; // Filtered list

    /**
     * Create an instance
     * @since version 1.0.0
     * @access public
     * @return instance of self
     */
    public static function getInstance() {
        if (!self::$instance)
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Set the folder where the paymentmethod classes reside
     * @since version 1.0.0
     * @access public
     * @param string $dir Folder of the paymentmethod classes
     */
    public function setPaymentMethodsFolder($dir) {
        $this->_folderPaymentMethods = $dir;
        return $this;
    }

    /**
     * Store the paymentmethod class names in the paymentmethods array.
     * @since version 1.0.0
     * @access public
     * @param string $dir Folder of the paymentmethod classes
     */
    public function readFolder($dir = null) {
        $this->setPaymentMethodsFolder(DIR . DS . 'paymentmethods');
        
        if ($dir) $this->setPaymentMethodsFolder($dir);

        $this->paymentMethods = array();
        try {
            $folder = $this->_folderPaymentMethods;
            $handle = is_dir($folder) ? opendir($folder) : false;

            if ($handle) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != ".svn") {
                        require_once (sprintf("%s/%s", $this->_folderPaymentMethods, $file));
                        $name = strtolower(substr($file, 0, strlen($file) - 4));
                        $className = "Icepay_Paymentmethod_" . ucfirst($name);
                        $this->paymentMethods[$name] = $className;
                    }
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $this;
    }
    
    /**
     * Returns a single class based on payment method code
     * @since version 2.1.0
     * @access public
     * @param string pmcode
     */
    public function getClassByPaymentMethodCode($pmcode){
        return new $this->paymentMethods[strtolower($pmcode)]();
    }

    /**
     * Load all the paymentmethod classes and store these in the filterable paymentmethods array.
     * @since version 1.0.0
     * @access public
     */
    public function prepareFiltering() {
        foreach ($this->paymentMethods as $name => $class) {
            $this->_paymentMethod[$name] = new $class();
        }
        return $this;
    }

    /**
     * Filter the paymentmethods array by currency
     * @since version 1.0.0
     * @access public
     * @param string $currency Language ISO 4217 code
     */
    public function filterByCurrency($currency) {
        foreach ($this->_paymentMethod as $name => $class) {
            if (!in_array($currency, $class->getSupportedCurrency()) && !in_array('00', $class->getSupportedCurrency()))
                unset($this->paymentMethods[$name]);
        }
        return $this;
    }

    /**
     * Filter the paymentmethods array by country
     * @since version 1.0.0
     * @access public
     * @param string $country Country ISO 3166-1-alpha-2 code
     */
    public function filterByCountry($country) {
        foreach ($this->_paymentMethod as $name => $class) {
            if (!in_array(strtoupper($country), $class->getSupportedCountries()) && !in_array('00', $class->getSupportedCountries()))
                unset($this->paymentMethods[$name]);
        }
        return $this;
    }

    /**
     * Filter the paymentmethods array by language
     * @since version 1.0.0
     * @access public
     * @param string $language Language ISO 639-1 code
     */
    public function filterByLanguage($language) {
        foreach ($this->_paymentMethod as $name => $class) {
            if (!in_array(strtoupper($language), $class->getSupportedLanguages()) && !in_array('00', $class->getSupportedLanguages()))
                unset($this->paymentMethods[$name]);
        }
        return $this;
    }

    /**
     * Filter the paymentmethods array by amount
     * @since version 1.0.0
     * @access public
     * @param int $amount Amount in cents
     */
    public function filterByAmount($amount) {
        foreach ($this->_paymentMethod as $name => $class) {
            $amountRange = $class->getSupportedAmountRange();
            if (intval($amount) >= $amountRange["minimum"] &&
                    intval($amount) <= $amountRange["maximum"]) {
                
            } else
                unset($this->paymentMethods[$name]);
        }
        return $this;
    }

    /**
     * Return the filtered paymentmethods array
     * @since version 1.0.0
     * @access public
     * @return array Paymentmethods
     */
    public function getArray() {
        return $this->paymentMethods;
    }

    public function loadArray() {
        if ($this->_paymentMethodsObject != null)
            return $this->_paymentMethodsObject;

        $this->_paymentMethodsObject = new stdClass();

        foreach ($this->getArray() as $key => $value) {
            $this->_paymentMethodsObject->$key = new $value();
        }

        return $this->_paymentMethodsObject;
    }

    public function getObject() {
        return $this->loadArray();
    }

}

/**
 *  Icepay_Basicmode class
 *  To start a basicmode payment
 *  @author Olaf Abbenhuis
 */
class Icepay_Basicmode extends Icepay_Api_Base {

    private static $instance;
    protected $_basicmodeURL = "pay.icepay.eu/basic/";
    protected $_postProtocol = "https";
    protected $_basicMode = false;
    protected $_fingerPrint = null;
    private $_checkout_version = 2;
    protected $_webservice = false;
    protected $data = null;
    protected $version = "1.0.1";
    protected $_readable_name = "Basicmode";
    protected $_api_type = "basicmode";
    private $_defaultCountryCode = "00";
    
    private $_generatedURL = "";
    
    protected $paymentObj;

    /**
     * Create an instance
     * @since version 1.0.0
     * @access public
     * @return instance of self
     */
    public static function getInstance() {
        if (!self::$instance)
            self::$instance = new self();
        return self::$instance;
    }
    
    /**
     * Ensure the class data is set
     * @since version 1.0.0
     * @access public
     */
    public function __construct() {
        $this->data = new stdClass();
        //$this->setPaymentMethodsFolder(DIR . '/paymentmethods/');
    }
    
    
    /**
     * Required for using the basicmode
     * @since version 2.1.0
     * @access public
     * @param Icepay_PaymentObject_Interface_Abstract $payment
     */
    public function validatePayment(Icepay_PaymentObject_Interface_Abstract $payment){
 
        $this->data = $payment->getData();
        
        if (!$payment->getPaymentMethod()) return $this;
        
        $paymentmethod = $payment->getBasicPaymentmethodClass();

        if (!$this->exists($payment->getCountry(), $paymentmethod->getSupportedCountries()))
            throw new Exception('Country not supported');
        
        if (!$this->exists($payment->getCurrency(), $paymentmethod->getSupportedCurrency()))
            throw new Exception('Currency not supported');
        
        if (!$this->exists($payment->getLanguage(), $paymentmethod->getSupportedLanguages()))
            throw new Exception('Language not supported');
        
        if (!$this->exists($payment->getIssuer(), $paymentmethod->getSupportedIssuers()) && $payment->getPaymentMethod() != null)
            throw new Exception('Issuer not supported');
        
        /* used for webservice call */
        $this->paymentObj = $payment;
        
        /* Clear the generated URL */
        $this->_generatedURL = "";
        
        return $this;
    }

    /**
     * Post the fields and return the URL generated by ICEPAY
     * @since version 1.0.0
     * @access public
     * @return string URL or Error message
     */
    public function getURL() {

        if ($this->_generatedURL != "") return $this->_generatedURL;
        
        if (!isset($this->_merchantID))
            throw new Exception('Merchant ID not set, use the setMerchantID() method');
        if (!isset($this->_secretCode))
            throw new Exception('Merchant ID not set, use the setSecretCode() method');

        if (!isset($this->data->ic_country)) {
            if (count($this->_country) == 1) {
                $this->data->ic_country = current($this->_country);
            } else
                $this->data->ic_country = $this->_defaultCountryCode;
        }

        if (!isset($this->data->ic_issuer) && isset($this->data->ic_paymentmethod)) {
            if (count($this->_issuer) == 1) {
                $this->data->ic_issuer = current($this->_issuer);
            } else
                throw new Exception('Issuer not set, use the setIssuer() method');
        }

        if (!isset($this->data->ic_language)) {
            if (count($this->_language) == 1) {
                $this->data->ic_language = current($this->_language);
            } else
                throw new Exception('Language not set, use the setLanguage() method');
        }

        if (!isset($this->data->ic_currency)) {
            if (count($this->_currency) == 1) {
                $this->data->ic_currency = current($this->_currency);
            } else
                throw new Exception('Currency not set, use the setCurrency() method');
        }

        if (!isset($this->data->ic_amount))
            throw new Exception('Amount not set, use the setAmount() method');
        
        if (!isset($this->data->ic_orderid))
            throw new Exception('OrderID not set, use the setOrderID() method');

        if (!isset($this->data->ic_reference))
            $this->data->ic_reference = "";
        if (!isset($this->data->ic_description))
            $this->data->ic_description = "";

        /*
         * Dynamic URLs
         * @since 1.0.1
         */
        if (!isset($this->data->ic_urlcompleted))
            $this->data->ic_urlcompleted = "";
        if (!isset($this->data->ic_urlerror))
            $this->data->ic_urlerror = "";
        
        $this->data->ic_version = $this->_checkout_version;
        $this->data->ic_merchantid = $this->_merchantID;
        $this->data->chk = $this->generateCheckSumDynamic();

        /* @since version 1.0.2 */
        if ($this->_webservice) {
            require_once(DIR . DS . "icepay_api_webservice.php");

            if (!isset($this->data->ic_issuer) || $this->data->ic_issuer == "")
                throw new Exception("Issuer not set");

            //$ws = Icepay_Api_Webservice::getInstance()->paymentService();
            $ws = Icepay_Api_Webservice::getInstance()->paymentService();
            $ws->setMerchantID($this->_merchantID)
               ->setSecretCode($this->_secretCode)
               ->setSuccessURL($this->data->ic_urlcompleted)
               ->setErrorURL($this->data->ic_urlerror);
            try {
                $this->_generatedURL = $ws->checkOut($this->paymentObj, true);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
            return $this->_generatedURL;
        }
        
        if (isset($this->data->ic_paymentmethod)){
            $this->_generatedURL = $this->postRequest($this->basicMode(), $this->prepareParameters());
        } else {
            $this->_generatedURL = sprintf("%s&%s", $this->basicMode(), $this->prepareParameters());
        }
        
        return $this->_generatedURL;
    }

    /**
     * Calls the API to generate a Fingerprint
     * @since version 1.0.0
     * @access protected
     * @return string SHA1 hash
     */
    public function generateFingerPrint() {
        if ($this->_fingerPrint != null)
            return $this->fingerPrint;
        $this->fingerPrint = sha1($this->getVersion());
        return $this->fingerPrint;
    }

    /**
     * Generates a URL to the ICEPAY basic API service
     * @since version 1.0.0
     * @access protected
     * @return string URL
     */
    protected function basicMode() {
        if (isset($this->data->ic_paymentmethod)) {
            $querystring = http_build_query(array(
                'type' => $this->data->ic_paymentmethod,
                'checkout' => 'yes',
                'ic_redirect' => 'no',
                'ic_country' => $this->data->ic_country,
                'ic_language' => $this->data->ic_language,
                'ic_fp' => $this->generateFingerPrint()
                    ), '', '&');
        } else {
            $querystring = http_build_query(array(
                'ic_country' => $this->data->ic_country,
                'ic_language' => $this->data->ic_language,
                'ic_fp' => $this->generateFingerPrint()
                    ), '', '&');
        }

        return sprintf("%s://%s?%s", $this->_postProtocol, $this->_basicmodeURL, $querystring);
    }

    /**
     * Used to connect to the ICEPAY servers
     * @since version 1.0.0
     * @access protected
     * @param string $url
     * @param array $data
     * @return string Returns a response from the specified URL
     */
    protected function postRequest($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response)
            throw new Exception("Error reading $url");

        if (( substr(strtolower($response), 0, 7) == "http://" ) || ( substr(strtolower($response), 0, 8) == "https://" )) {
            return $response;
        }
        else
            throw new Exception("Server response: " . strip_tags($response));
    }

    /**
     * Generate checksum for basicmode checkout
     * @since version 1.0.0
     * @access protected
     * @return string SHA1 encoded
     */
    protected function generateCheckSum() {
        return sha1(
                        sprintf("%s|%s|%s|%s|%s|%s|%s", $this->_merchantID, $this->_secretCode, $this->data->ic_amount, $this->data->ic_orderid, $this->data->ic_reference, $this->data->ic_currency, $this->data->ic_country
                        )
        );
    }

    /**
     * Generate checksum for basicmode checkout using dynamic urls
     * @since version 1.0.1
     * @access protected
     * @return string SHA1 encoded
     */
    protected function generateCheckSumDynamic() {
        return sha1(
                        sprintf("%s|%s|%s|%s|%s|%s|%s|%s|%s", $this->_merchantID, $this->_secretCode, $this->data->ic_amount, $this->data->ic_orderid, $this->data->ic_reference, $this->data->ic_currency, $this->data->ic_country, $this->data->ic_urlcompleted, $this->data->ic_urlerror
                        )
        );
    }

    /**
     * Create the query string
     * @since version 1.0.0
     * @access protected
     * @return string
     */
    protected function prepareParameters() {
        return http_build_query($this->data, '', '&');
    }

    /**
     * Set the protocol for local testing
     * @since version 1.0.0
     * @access public
     * @param string $protocol [http|https]
     */
    public function setProtocol($protocol = "https") {
        $this->_postProtocol = $protocol;
        return $this;
    }

    /**
     * Use the webservice for the Call
     * @since version 1.0.2
     * @access public
     */
    public function useWebservice() {
        $this->_webservice = true;
        return $this;
    }

}


?>
