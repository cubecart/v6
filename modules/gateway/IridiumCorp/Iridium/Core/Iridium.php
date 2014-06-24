<?php

class Errors {

    const NoIntegrationMethodSelected = "No integration method selected";
    const NoTransactionMethodSelected = "No transaction method selected";
    const NoTransactionTypeSelected = "No transaction type selected";
    const NoIntegrationSourceSpecified = "No transaction source specified";

    #
    const NoDomainSpecified = "No gateway domain specified";

    # Merchat Details
    const NoMerchantIDSpecified = "No merchant ID specified";
    const NoPasswordSpecified = "No password specified";

    # Multiple Transaction Types
    const NoAmountSpecified = "No amount specified";
    const NoCurrencySpecified = "No transaction currency specified";
    Const InvalidCurrencySpecified = "Invalid currency specified";

    # Order Details Errors
    const NoOrderIDSpecified = "No OrderID specified";
    const NoOrderDescriptionSpecified = "No OrderDescription Specified";

    # Card Details Transaction
    const NoCardNumberSpecified = "No card number specified";
    const NoCardCV2Specified = "No CV2 specified";
    const NoCardExpiryDate = "No card expiry date specified";
    const InvalidCardExpiryDate = "Invalid card expiry date specified";

    # Cross Reference Transaction
    const NoCrossReferenceSpecified = "No cross reference specified for a cross reference transaction";

    # ThreeDSecure Transaction
    const NoMDSpecified = "No MD specified for a ThreeDSecure transaction";
    const NoPaRESSpecified = "No PaRES specified for a ThreeDSecure transaction";

    # Hosted Payment Form
    const NoHostedFormMethodSelected = "No HostedFormMethod type selected";
    const NoReturnTypeSelected = "No return type select for a Hosted Payment Form integration";
    const NoHashMethodSelected = "No hash method select for a Hosted Payment Form integration";
    const NoPreSharedKeySpecified = "No PreSharedKey specified";
    const NoResultReturnTypeSelected = "No server result return type selected";
    const NoResultDeliveryMethodSelected = "No Result Delivery Method selected";
    const NoCallbackURLSpecified = "No CallbackURL specified";
    const NoHostedTransactionResponseSpecified = "No hosted transaction response specified";

    # Transparent Redirect Errors
    const NoTransparentRedirectMethodSelected = "No TransparentRedirectMethod type selected";

    # No Communication
    const NoCommunicationWithGateway = "Couldn't communicate with payment gateway";

}

class IntegrationMethod {

    const NONE = NULL;
    const DirectAPI = "Direct API";
    const HostedPaymentForm = "Hosted Payment Form";
    const TransparentRedirect = "Transparent Redirect";

}

class TransactionMethod {

    const NONE = NULL;
    const CardDetailsTransaction = "CardDetailsTransaction";
    const CrossReferenceTransaction = "CrossReferenceTransaction";
    const ThreeDSecureTransaction = "ThreeDSecureTransaction";

}

class TransactionType {

    const NONE = NULL;
    const Sale = "SALE";
    const PreAuth = "PREAUTH";
    const Refund = "REFUND";
    const Collection = "COLLECTION";

}

class AVSPolicy {

    const NONE = NULL;
    const FailIfEitherFail = "E";
    const FailOnlyIfBothFail = "B";
    const FailOnlyIfAddressFails = "A";
    const FailOnlyIfPostCodeFails = "P";
    const DoNotFail = "N";

}

class AVSPartialAddress {

    const NONE = NULL;
    const PASS = "P";
    const FAIL = "F";

}

class AVSPartialPostCode {

    const NONE = NULL;
    const PASS = "P";
    const FAIL = "F";

}

class AVSResultsUnknown {

    const NONE = NULL;
    const PASS = "P";
    const FAIL = "F";

}

class CV2OverridePolicy {

    const NONE = NULL;
    const PASS = "P";
    const FAIL = "F";

}

class CV2ResultsUnknown {

    const NONE = NULL;
    const PASS = "P";
    const FAIL = "F";

}

class HashMethod {

    const NONE = NULL;
    const SHA1 = "SHA1";
    const MD5 = "MD5";
    const HMACSHA1 = "HMACSHA1";
    const HMACMD5 = "HMACMD5";

}

abstract class IridiumBase {

    protected $boDebugMode;
    protected $szDebugEmail;
    #
    protected $boDatabaseSupport;
    #
    protected $szPaymentProcessorFullDomain;
    protected $nEntryPointsValidilityTime;
    #
    protected $szTransactionDateTime;
    #
    protected $imIntegrationMethod;
    protected $tmTransactionMethod;
    protected $ttTransactionType;
    protected $szIntegrationSource;
    #
    protected $boThreeDSecureCompatMode;
    #
    protected $szAVSOverridePolicy;
    protected $szCV2OverridePolicy;
    protected $boThreeDSecureOverridePolicy;
    #
    protected $boEchoCardType;
    protected $boEchoAmountReceived;
    protected $boEchoAVSCheckResult;
    protected $boEchoCV2CheckResult;
    protected $boEchoThreeDSecureAuthenticationCheckResult;
    #
    protected $nDeviceCategory;
    protected $szAcceptHeaders;
    protected $szUserAgent;
    protected $szCustomerIPAddress;
    #
    protected $szMerchantID;
    protected $szPassword;
    #
    protected $nAmount;
    protected $nAmountUndecimalised;
    #
    protected $icISOCurrency;
    protected $szCurrencyShort3;
    protected $iccISOCurrencyCode;
    protected $nExponent;
    #
    protected $szOrderID;
    protected $szOrderDescription;
    #
    protected $szAddress1;
    protected $szAddress2;
    protected $szAddress3;
    protected $szAddress4;
    protected $szCity;
    protected $szState;
    protected $szPostCode;
    #
    protected $icISOCountry;
    protected $szCountryShort2;
    protected $szCountryShort3;
    protected $iccISOCountryCode;
    #
    protected $szEmailAddress;
    protected $szPhoneNumber;
    #
    protected $szCardName;
    protected $szCardNumber;
    protected $szCardLastFour;
    protected $szCardExpiryDateMonth;
    protected $szCardExpiryDateYear;
    protected $szCardStartDateMonth;
    protected $szCardStartDateYear;
    protected $szCardIssueNumber;
    protected $szCardCV2;
    #
    protected $szOverrideCardName;
    protected $szOverrideCardNumber;
    protected $szOverrideCardLastFour;
    protected $szOverrideCardExpiryDateMonth;
    protected $szOverrideCardExpiryDateYear;
    protected $szOverrideCardStartDateMonth;
    protected $szOverrideCardStartDateYear;
    protected $szOverrideCardIssueNumber;
    protected $szOverrideCardCV2;
    #
    protected $szMD;
    protected $szPaRES;
    #
    protected $szOriginCrossReference;
    protected $boTransactionProcessed;
    protected $boTransactionResultsValidated;
    #
    protected $toTransactionObject;
    protected $trTransactionResult;
    protected $todTransactionOutputData;
    #
    protected $boErrorActive;
    protected $szErrorMessage;

    public function __construct($szIntegrationSource) {

        require_once (dirname(dirname(__FILE__)) . "/Config.php");
        require_once ("ThePaymentGateway/PaymentSystem.php");

        $this -> szErrorMessage = array();

        if (!$szIntegrationSource == NULL) {
            $this -> szIntegrationSource = "[" . $szIntegrationSource . "] ";
        }

        $this -> boDebugMode = FALSE;
        $this -> szDebugEmail = NULL;
        $this -> boDatabaseSupport = FALSE;
        $this -> nEntryPointsValidilityTime = 10;

        $this -> imIntegrationMethod = IntegrationMethod::NONE;
        $this -> tmTransactionMethod = TransactionMethod::NONE;
        $this -> ttTransactionType = TransactionType::NONE;

        $this -> boThreeDSecureCompatMode = FALSE;

        $this -> boEchoAVSCheckResult = true;
        $this -> boEchoCV2CheckResult = true;
        $this -> boEchoThreeDSecureAuthenticationCheckResult = true;
        $this -> boEchoCardType = true;
        $this -> boEchoAmountReceived = true;

        #
        $this -> nDeviceCategory = 0;
        $this -> szAcceptHeaders = "*/*";
        $this -> szUserAgent = $_SERVER["HTTP_USER_AGENT"];

        $this -> szCustomerIPAddress = $_SERVER["REMOTE_ADDR"];

        $this -> setAVSOverridePolicy(AVSPolicy::NONE, AVSPartialAddress::NONE, AVSPartialPostCode::NONE, AVSResultsUnknown::NONE);
        $this -> setCV2OverridePolicy(CV2OverridePolicy::NONE, CV2ResultsUnknown::NONE);
    }

    public static function addStringToStringList($szExistingStringList, $szStringToAdd) {
        $szReturnString = "";
        $szCommaString = "";

        if (strlen($szStringToAdd) == 0) {
            $szReturnString = $szExistingStringList;
        } else {
            if (strlen($szExistingStringList) != 0) {
                $szCommaString = ", ";
            }
            $szReturnString = $szExistingStringList . $szCommaString . $szStringToAdd;
        }

        return ($szReturnString);
    }

    public static function parseNameValueStringIntoArray($szNameValueString, $boURLDecodeValues) {
        // break the reponse into an array
        // first break the variables up using the "&" delimter
        $aPostVariables = explode("&", $szNameValueString);

        $aParsedVariables = array();

        foreach ($aPostVariables as $szVariable) {
            // for each variable, split is again on the "=" delimiter
            // to give name/value pairs
            $aSingleVariable = explode("=", $szVariable);
            $szName = $aSingleVariable[0];
            if (!$boURLDecodeValues) {
                $szValue = $aSingleVariable[1];
            } else {
                $szValue = urldecode($aSingleVariable[1]);
            }

            $aParsedVariables[$szName] = $szValue;
        }

        return ($aParsedVariables);
    }

    public static function boolToString($boBool) {
        $szReturnValue = "false";

        if ($boBool) {
            $szReturnValue = "true";
        }

        return ($szReturnValue);
    }

    public static function stringToBool($szString) {
        $boReturnValue = false;

        if (strToUpper($szString) == "TRUE") {
            $boReturnValue = true;
        }

        return ($boReturnValue);
    }

    /**
     * Clean/Sanitise user input
     *
     * @param string $data - the string to clean
     * @return string
     *
     */
    public static function CleanInput($data) {

        $data = trim($data);
        //$data = htmlentities($data);

        //$data = htmlspecialchars($data);
        //$data = $this -> db -> real_escape_string($data);

        return $data;
    }

    public function setThreeDSecureCompatMode($boThreeDSecureCompatMode) {
        $this -> boThreeDSecureCompatMode = $boThreeDSecureCompatMode;
    }

    public function getThreeDSecureCompatMode() {
        return $this -> boThreeDSecureCompatMode;
    }

    public function setDebugMode($boDebugMode) {
        $this -> boDebugMode = $boDebugMode;
    }

    public function getDebugMode() {
        return $this -> boDebugMode;
    }

    public function setDebugEmailAddress($szDebugEmailAddress) {
        $this -> szDebugEmail = $szDebugEmailAddress;
    }

    public function getDebugEmailAddress() {
        return $this -> szDebugEmail;
    }

    public function setDatabaseSupport($boDatabaseSupport) {
        $this -> boDatabaseSupport = $boDatabaseSupport;
    }

    public function setPaymentProcessorFullDomain($szPaymentProcessorFullDomain) {
        $this -> szPaymentProcessorFullDomain = self::CleanInput($szPaymentProcessorFullDomain);
    }

    public function setEntryPointsValidilityTimeInMinutes($nTimeInMinutes) {
        $this -> nEntryPointsValidilityTime = $nTimeInMinutes;
    }

    public function getEntryPointsValidilityTimeInMinutes() {
        return $this -> nEntryPointsValidilityTime;
    }

    public function setTransactionDateTime($szDateTime) {
        $this -> szTransactionDateTime = self::CleanInput($szDateTime);
    }

    public function getTransactionDateTime() {
        return $this -> szTransactionDateTime;
    }

    public function getIntegrationMethod() {
        return $this -> imIntegrationMethod;
    }

    public function setTransactionMethod($tmTransactionMethod) {
        $this -> tmTransactionMethod = self::CleanInput($tmTransactionMethod);
    }

    public function getTransactionMethod() {
        return $this -> tmTransactionMethod;
    }

    public function setTransactionType($ttTransactionType) {
        $this -> ttTransactionType = self::CleanInput($ttTransactionType);
    }

    public function getTransactionType() {
        return $this -> ttTransactionType;
    }

    public function setIntegrationSource($szIntegrationSource) {
        $this -> szIntegrationSource = self::CleanInput($szIntegrationSource);
    }

    public function getIntegrationSource() {
        return $this -> szIntegrationSource;
    }

    public function setHandleTransactionResultsFunctionName($HandleTransactionResultsFunctionName) {
        $this -> szHandleTransactionResultsFunctionName = $HandleTransactionResultsFunctionName;
    }

    public function setHandleTransactionResultsIncludePath($HandleTransactionResultsIncludePath) {
        $this -> szHandleTransactionResultsIncludePath = $HandleTransactionResultsIncludePath;
    }

    public function setEchoCardType($boEchoCardType) {
        $this -> boEchoCardType = (bool)self::CleanInput($boEchoCardType);
    }

    public function setEchoAmountReceived($boEchoAmountReceived) {
        $this -> boEchoAmountReceived = (bool)self::CleanInput($boEchoAmountReceived);
    }

    public function setEchoAVSCheckResult($boEchoAVSCheckResult) {
        $this -> boEchoAVSCheckResult = (bool)self::CleanInput($boEchoAVSCheckResult);
    }

    public function setEchoCV2CheckResult($boEchoCV2CheckResult) {
        $this -> boEchoCV2CheckResult = (bool)self::CleanInput($boEchoCV2CheckResult);
    }

    public function setAVSOverridePolicy($AVSPolicyOverride, $AVSPartialAddress, $AVSPartialPostCode, $AVSResultsUnknown) {
        $this -> szAVSOverridePolicy = $AVSPolicyOverride . $AVSPartialAddress . $AVSPartialPostCode . $AVSResultsUnknown;
    }

    public function setCV2OverridePolicy($CV2OverridePolicy, $CV2ResultsUnknown) {
        $this -> szCV2OverridePolicy = $CV2OverridePolicy . $CV2ResultsUnknown;
    }

    public function setThreeDSecureOverridePolicy($boThreeDSecureOverridePolicy) {
        $this -> boThreeDSecureOverridePolicy = (boolean)self::CleanInput($boThreeDSecureOverridePolicy);
    }

    public function setMerchantID($szMerchantID) {
        $this -> szMerchantID = self::CleanInput($szMerchantID);
    }

    public function getMerchantID() {
        return $this -> szMerchantID;
    }

    public function setPassword($szPassword) {
        $this -> szPassword = self::CleanInput($szPassword);
    }

    public function getPassword() {
        return $this -> szPassword;
    }

    public function setTransactionAmountAndCurrency($nAmountWithDecimals, $vCurrency) {

        require ("ISOCurrencies.php");

        $vCurrency = self::CleanInput($vCurrency);

        $iclISOCurrencyList -> getISOCurrency($vCurrency, $this -> icISOCurrency);

        if (!$this -> icISOCurrency == NULL) {

            $this -> iccISOCurrencyCode = $this -> icISOCurrency -> getISOCode();
            $this -> szCurrencyShort3 = $this -> icISOCurrency -> getCurrencyShort();
            $this -> nExponent = $this -> icISOCurrency -> getExponent();

            $nAmountWithDecimals = self::CleanInput($nAmountWithDecimals);

            if (is_numeric($nAmountWithDecimals)) {

                $this -> nAmount = $nAmountWithDecimals;

                $this -> nAmountUndecimalised = $this -> getAmount($this -> nAmount, $this -> nExponent, FALSE);
            }
        } else {
            $this -> boErrorActive = true;
            $this -> szErrorMessage = "Invalid currency supplied [" . $vCurrency . "]";
            if ($this -> boDebugMode && $this -> szDebugEmail != NULL) {
                mail($this -> szDebugEmail, "FUNC: IridiumBase:setResultsAmountAndCurrency()", "ERROR: " . print_r($this -> szErrorMessage, 1));
            }
        }
    }

    public function setResultsAmountAndCurrency($nAmountWithoutDecimals, $vCurrency) {

        require ("ISOCurrencies.php");

        $vCurrency = self::CleanInput($vCurrency);

        $iclISOCurrencyList -> getISOCurrency($vCurrency, $this -> icISOCurrency);

        if (!$this -> icISOCurrency == NULL) {
            $this -> iccISOCurrencyCode = $this -> icISOCurrency -> getISOCode();
            $this -> szCurrencyShort3 = $this -> icISOCurrency -> getCurrencyShort();
            $this -> nExponent = $this -> icISOCurrency -> getExponent();

            $nAmountWithoutDecimals = self::CleanInput($nAmountWithoutDecimals);

            if (is_numeric($nAmountWithoutDecimals)) {

                $this -> nAmountUndecimalised = $nAmountWithoutDecimals;

                $this -> nAmount = number_format($this -> getAmount($this -> nAmountUndecimalised, $this -> nExponent, TRUE), $this -> nExponent);
            }
        } else {
            $this -> boErrorActive = true;
            $this -> szErrorMessage = "Invalid currency supplied [" . $vCurrency . "]";
            if ($this -> boDebugMode && $this -> szDebugEmail != NULL) {
                mail($this -> szDebugEmail, "FUNC: IridiumBase:setResultsAmountAndCurrency()", "ERROR: " . print_r($this -> szErrorMessage, 1));
            }
        }
    }

    public function getTransactionCurrencyISOCode() {
        return $this -> iccISOCurrencyCode;
    }

    public function setOrderID($szOrderID) {
        $this -> szOrderID = self::CleanInput($szOrderID);
    }

    public function getOrderID() {
        return $this -> szOrderID;
    }

    public function setOrderDescription($szOrderDescription) {
        $this -> szOrderDescription = self::CleanInput($szOrderDescription);
    }

    public function getOrderDescription() {
        return $this -> szOrderDescription;
    }

    public function setAddress1($szAddress1) {
        $this -> szAddress1 = self::CleanInput($szAddress1);
    }

    public function setAddress2($szAddress2) {
        $this -> szAddress2 = self::CleanInput($szAddress2);
    }

    public function setAddress3($szAddress3) {
        $this -> szAddress3 = self::CleanInput($szAddress3);
    }

    public function setAddress4($szAddress4) {
        $this -> szAddress4 = self::CleanInput($szAddress4);
    }

    public function setCity($szCity) {
        $this -> szCity = self::CleanInput($szCity);
    }

    public function setState($szState) {
        $this -> szState = self::CleanInput($szState);
    }

    public function setPostCode($szPostCode) {
        $this -> szPostCode = self::CleanInput($szPostCode);
    }

    public function setCountry($vCountry) {

        $vCountry = self::CleanInput($vCountry);

        require ("ISOCountries.php");

        $iclISOCountryList -> getISOCountry($vCountry, $this -> icISOCountry);

        if (!is_null($this -> icISOCountry)) {
            $this -> iccISOCountryCode = $this -> icISOCountry -> getISOCode();
            $this -> szCountryShort2 = $this -> icISOCountry -> getCountryShort2();
            $this -> szCountryShort3 = $this -> icISOCountry -> getCountryShort3();
        }
    }

    public function setEmailAddress($szEmailAddress) {
        $this -> szEmailAddress = self::CleanInput($szEmailAddress);
    }

    public function setPhoneNumber($szPhoneNumber) {
        $this -> szPhoneNumber = self::CleanInput($szPhoneNumber);
    }

    public function setCardName($szCardName) {
        $this -> szCardName = self::CleanInput($szCardName);
    }

    public function setCardNumber($szCardNumber) {
        $szCardNumber = self::CleanInput($szCardNumber);
        $this -> szCardNumber = $szCardNumber;
        $this -> szCardLastFour = substr($szCardNumber, -4, 4);
    }

    public function getCardNumber() {
        return $this -> szCardNumber;
    }

    public function setCardLastFour($szCardLastFour) {
        $this -> szCardLastFour = self::CleanInput($szCardLastFour);
    }

    public function getCardLastFour() {
        return $this -> szCardLastFour;
    }

    public function setCardExpiryDateMonth($szCardExpiryDateMonth) {
        $this -> szCardExpiryDateMonth = self::CleanInput($szCardExpiryDateMonth);
    }

    public function getCardExpiryDateMonth() {
        return $this -> szCardExpiryDateMonth;
    }

    public function setCardExpiryDateYear($szCardExpiryDateYear) {
        $this -> szCardExpiryDateYear = self::CleanInput($szCardExpiryDateYear);
    }

    public function getCardExpiryDateYear() {
        return $this -> szCardExpiryDateYear;
    }

    public function setCardStartDateMonth($szCardStartDateMonth) {
        $this -> szCardStartDateMonth = self::CleanInput($szCardStartDateMonth);
    }

    public function getCardStartDateMonth() {
        return $this -> szCardStartDateMonth;
    }

    public function setCardStartDateYear($szCardStartDateYear) {
        $this -> szCardStartDateYear = self::CleanInput($szCardStartDateYear);
    }

    public function getCardStartDateYear() {
        return $this -> szCardStartDateYear;
    }

    public function setCardIssueNumber($szCardIssueNumber) {
        $this -> szCardIssueNumber = self::CleanInput($szCardIssueNumber);
    }

    public function setCardCV2($szCardCV2) {
        $this -> szCardCV2 = self::CleanInput($szCardCV2);
    }

    public function setCardNameOverride($szCardNameOverride) {
        $this -> szCardNameOverride = self::CleanInput($szCardNameOverride);
    }

    public function setCardNumberOverride($szCardNumberOverride) {
        $szCardNumberOverride = self::CleanInput($szCardNumberOverride);
        $this -> szCardNumberOverride = $szCardNumberOverride;
        $this -> szCardLastFourOverride = substr($szCardNumberOverride, -4, 4);
    }

    public function getCardNumberOverride() {
        return $this -> szCardNumberOverride;
    }

    public function setCardLastFourOverride($szCardLastFourOverride) {
        $this -> szCardLastFourOverride = self::CleanInput($szCardLastFourOverride);
    }

    public function getCardLastFourOverride() {
        return $this -> szCardLastFourOverride;
    }

    public function setCardExpiryDateMonthOverride($szCardExpiryDateMonthOverride) {
        $this -> szCardExpiryDateMonthOverride = self::CleanInput($szCardExpiryDateMonthOverride);
    }

    public function getCardExpiryDateMonthOverride() {
        return $this -> szCardExpiryDateMonthOverride;
    }

    public function setCardExpiryDateYearOverride($szCardExpiryDateYearOverride) {
        $this -> szCardExpiryDateYearOverride = self::CleanInput($szCardExpiryDateYearOverride);
    }

    public function getCardExpiryDateYearOverride() {
        return $this -> szCardExpiryDateYearOverride;
    }

    public function setCardStartDateMonthOverride($szCardStartDateMonthOverride) {
        $this -> szCardStartDateMonthOverride = self::CleanInput($szCardStartDateMonthOverride);
    }

    public function getCardStartDateMonthOverride() {
        return $this -> szCardStartDateMonthOverride;
    }

    public function setCardStartDateYearOverride($szCardStartDateYearOverride) {
        $this -> szCardStartDateYearOverride = self::CleanInput($szCardStartDateYearOverride);
    }

    public function getCardStartDateYearOverride() {
        return $this -> szCardStartDateYearOverride;
    }

    public function setCardIssueNumberOverride($szCardIssueNumberOverride) {
        $this -> szCardIssueNumberOverride = self::CleanInput($szCardIssueNumberOverride);
    }

    public function setCardCV2Override($szCardCV2Override) {
        $this -> szCardCV2Override = self::CleanInput($szCardCV2Override);
    }

    public function setOriginCrossReference($szCrossReference) {
        $this -> szOriginCrossReference = self::CleanInput($szCrossReference);
    }

    public function getOriginCrossReference() {
        return $this -> szOriginCrossReference;
    }

    public function setMD($szMD) {
        $this -> szMD = self::CleanInput($szMD);
    }

    public function getMD() {
        return $this -> szMD;
    }

    public function setPaRES($szPaRES) {
        $this -> szPaRES = self::CleanInput($szPaRES);
    }

    public function getPaRES() {
        return $this -> szPaRES;
    }

    public function getTransactionObject() {
        return $this -> toTransactionObject;
    }

    public function getTransactionOutputData() {
        return $this -> todTransactionOutputData;
    }

    public function getTransactionResult() {
        return $this -> trTransactionResult;
    }

    public function TransactionProcessed() {
        return $this -> boTransactionProcessed;
    }

    public function getAmountDecimalised() {
        return $this -> nAmount;
    }

    public function getAmountUndecimalised() {
        return $this -> nAmountUndecimalised;
    }

    public function HasActiveError() {
        return $this -> boErrorActive;
    }

    public function getErrorMessage($index = null) {
        if ($index != null) {
            return $this -> szErrorMessage[$index];
        } else {
            return $this -> szErrorMessage;
        }
    }

    private function getAmount($Amount, $nExponent, $GetDecimalisedAmount) {

        $nAmount = 0;

        $Amount = round($Amount, $nExponent);
        $Power = pow(10, $nExponent);
        if ($GetDecimalisedAmount) {
            $nAmount = $Amount / $Power;
        } else {
            $nAmount = $Amount * $Power;
        }

        return $nAmount;
    }

    function IsReadyBase() {

        $this -> szErrorMessage = array();

        if ($this -> imIntegrationMethod == IntegrationMethod::NONE) {
            array_push($this -> szErrorMessage, Errors::NoIntegrationMethodSelected);
        }
        if ($this -> szIntegrationSource == NULL) {
            array_push($this -> szErrorMessage, Errors::NoIntegrationSourceSpecified);
        }
        if ($this -> szMerchantID == NULL) {
            array_push($this -> szErrorMessage, Errors::NoMerchantIDSpecified);
        }
        if ($this -> szPassword == NULL) {
            array_push($this -> szErrorMessage, Errors::NoPasswordSpecified);
        }
    }

    public function Process() {
        if ($this -> szTransactionDateTime == NULL) {
            $this -> szTransactionDateTime = date('Y-m-d H:i:s P');
        }
        if ($this -> szOrderDescription == NULL) {
            $this -> szOrderDescription = $this -> szIntegrationSource;
        } else {
            $this -> szOrderDescription = $this -> szIntegrationSource . " " . $this -> szOrderDescription;
        }
    }

    public function GetExpiryDate($dtExpiryDateMonth, $dtExpiryDateYear) {

        $dtReturn = NULL;

        $dtExpiryDateMonth = (int)$dtExpiryDateMonth;
        $dtExpiryDateYear = (int)$dtExpiryDateYear;

        $dtNow = mktime(0, 0, 0, date("m"), 1, date("y"));
        $dtExpiry = mktime(0, 0, 0, $dtExpiryDateMonth, 1, $dtExpiryDateYear);

        $dtDifference = $dtExpiry - dtNow;

        if ($dtDifference < 0) {
            $dtReturn['m'] = date("m", $dtNow);
            $dtReturn['y'] = date("y", $dtNow);
            //$dtReturn = date("my", $dtNow);
        } else {
            $dtReturn['m'] = date("m", $dtExpiry);
            $dtReturn['y'] = date("y", $dtExpiry);
            //$dtReturn = $dtExpiryDate;
        }

        return $dtReturn;
    }

}

abstract class IridiumSQL {

    const tblGEP_EntryPoints = "tblIridium_GEP_EntryPoints";
    const tblHPF_Transactions = "tblIridium_HPF_Transactions";
    const tbl3DS_Transactions = "tblIridium_3DS_Transactions";
    const tblHPF_SERVER_Results = "tblIridium_HPF_SERVER_Results";
    const tblCRT_CrossReference = "tblIridium_CRT_CrossReference";

    public static $g_szQueryString;

    public function __constructor() {

    }

    public static function TableExists($szTableName) {
        self::$g_szQueryString = "SHOW TABLES LIKE '$szTableName'";
        return self::$g_szQueryString;
    }

    public static function createCRT_CrossReference() {

        self::$g_szQueryString = "
            CREATE TABLE `" . self::tblCRT_CrossReference . "`
            (
                `UserID`                varchar(255)    NOT NULL,
                `CrossReference`        varchar(24)     NOT NULL,
                `CardLastFour`          text(4)         NOT NULL,
                `ExpiryDateMonth`       text(2)         NOT NULL,
                `ExpiryDateYear`        text(4)         NOT NULL,
                `TransactionDateTime`   DateTime        NOT NULL,
                `ThreeDSecureRequired`  boolean         NOT NULL,
                
                PRIMARY KEY (`CrossReference`)
            );";

        return self::$g_szQueryString;
    }

    public static function insertCRT_NewCardDetailsTransaction($szUserID, $szCrossReference, $szCardLastFour, $szCardExpiryDateMonth, $szCardExpiryDateYear, $szTransactionDateTime, $boThreeDSecureRequired) {

        self::$g_szQueryString = "
            INSERT INTO " . self::tblCRT_CrossReference . "
            (
                `UserID`,
                `CrossReference`,
                `CardLastFour`,
                `ExpiryDateMonth`,
                `ExpiryDateYear`,
                `TransactionDateTime`,
                `ThreeDSecureRequired`
            )
            VALUES
            (
                '$szUserID',
                '$szCrossReference',
                '$szCardLastFour',
                '$szCardExpiryDateMonth',
                '$szCardExpiryDateYear',
                '$szTransactionDateTime',
                '$boThreeDSecureRequired'
            );";

        return self::$g_szQueryString;
    }

    public static function updateCRT_CardDetails($szUserID, $szCrossReference, $szCardLastFour, $szCardExpiryDateMonth, $szCardExpiryDateYear, $szTransactionDateTime, $boThreeDSecureRequired) {

        self::$g_szQueryString = "
            UPDATE " . self::tblCRT_CrossReference . "
            SET `CrossReference`        = '$szCrossReference',
                `CardLastFour`          = '$szCardLastFour',
                `ExpiryDateMonth`       = '$szCardExpiryDateMonth',
                `ExpiryDateYear`        = '$szCardExpiryDateYear',
                `TransactionDateTime`   = '$szTransactionDateTime',
                `ThreeDSecureRequired`  = '$boThreeDSecureRequired'
            WHERE 
                `UserID` = '$szUserID';";

        return self::$g_szQueryString;
    }

    public static function updateCRT_TransactionDetails($szUserID, $szOriginCrossReference, $szNewCrossReference, $szTransactionDateTime, $boThreeDSecureRequired) {

        self::$g_szQueryString = "
            UPDATE " . self::tblCRT_CrossReference . "
            SET `CrossReference`        = '$szNewCrossReference',
                `TransactionDateTime`   = '$szTransactionDateTime',
                `ThreeDSecureRequired`  = '$boThreeDSecureRequired'
            WHERE `UserID`              = '$szUserID'
                AND `CrossReference`    = '$szOriginCrossReference'
                ;";

        return self::$g_szQueryString;
    }

    public static function deleteCRT_CardDetailsSpecific($szUserID, $szCardLastFour) {

        self::$g_szQueryString = "
            DELETE FROM " . self::tblCRT_CrossReference . "
            WHERE `UserID` = '$szUserID'
                AND `CardLastFour` = '$szCardLastFour'
            ;";

        return self::$g_szQueryString;

    }

    public static function deleteCRT_CardDetailsAllExceptSpecificCrossReference($szUserID, $szCrossReference) {

        self::$g_szQueryString = "
            DELETE FROM " . self::tblCRT_CrossReference . "
            WHERE `UserID` = '$szUserID'
                AND `CrossReference` != '$szCrossReference'
            ;";

        return self::$g_szQueryString;

    }

    public static function deleteCRT_CardDetailsAll($szUserID) {

        self::$g_szQueryString = "
            DELETE FROM " . self::tblCRT_CrossReference . "
            WHERE `UserID` = '$szUserID'
            ;";

        return self::$g_szQueryString;

    }

    public static function selectCRT_CrossReference($szUserID) {

        self::$g_szQueryString = "
            SELECT `CrossReference`
            FROM " . self::tblCRT_CrossReference . "
            WHERE `UserID` = '$szUserID';";

        return self::$g_szQueryString;
    }

    public static function selectCRT_TransactionDate($szUserID) {

        self::$g_szQueryString = "
            SELECT `TransactionDateTime`
            FROM " . self::tblCRT_CrossReference . "
            WHERE `UserID` = '$szUserID';";

        return self::$g_szQueryString;
    }

    public static function selectCRT_CrossReferenceDetails($szUserID) {

        self::$g_szQueryString = "
            SELECT `CrossReference`,`CardLastFour`,`ExpiryDateMonth`,`ExpiryDateYear`,`TransactionDateTime`
            FROM " . self::tblCRT_CrossReference . "
            WHERE `UserID` = '$szUserID'
                AND `ThreeDSecureRequired` = false
            ORDER BY `TransactionDateTime` DESC
            ;";

        return self::$g_szQueryString;
    }

    public static function createGEP_EntryPoints() {

        self::$g_szQueryString = "
            CREATE TABLE `" . self::tblGEP_EntryPoints . "`
                (
                    `ID`                    text(11)    NOT NULL AUTO_INCREMENT,
                    `GatewayEntryPoint`     text(10)    NOT NULL,
                    `TransactionDateTime`   DateTime    NOT NULL,
                    PRIMARY KEY (`ID`)
                );";

        return self::$g_szQueryString;
    }

    public static function insertGEP_EntryPoint($GatewayEntryPointURL, $szTransactionDateTime) {

        self::$g_szQueryString = "
            INSERT INTO " . self::tblGEP_EntryPoints . "
            (
                `GatewayEntryPoint`,
                `TransactionDateTime`
            )
            VALUES
            (
                '$GatewayEntryPointURL',
                '$szTransactionDateTime'
            )";
        return self::$g_szQueryString;
    }

    public static function selectGEP_EntryPoint($RemoveDataThreshold_AmountInMinutes) {

        self::$g_szQueryString = "
            SELECT `GatewayEntryPoint`, MAX(`TransactionDateTime`)
            FROM " . self::tblGEP_EntryPoints . "
            WHERE `TransactionDateTime` >= SUBDATE(NOW(), INTERVAL $RemoveDataThreshold_AmountInMinutes MINUTE)";

        return self::$g_szQueryString;
    }

    public static function deleteGEP_EntryPoint($RemoveDataThreshold_AmountInMinutes) {

        self::$g_szQueryString = "  
            DELETE FROM " . self::tblGEP_EntryPoints . "
            WHERE `TransactionDateTime` < SUBDATE(NOW(), INTERVAL $RemoveDataThreshold_AmountInMinutes MINUTE)";

        return self::$g_szQueryString;
    }

    public static function create3DS_Transactions() {

        self::$g_szQueryString = "   
            CREATE TABLE `" . self::tbl3DS_Transactions . "`
            (
                `CrossReference`        varchar(24) NOT NULL,
                `UserID`                text(10)     NOT NULL,
                `ISOCurrencyCode`       text(3)     NOT NULL,
                `Amount`                varchar(12) NOT NULL,
                `TransactionDateTime`   DateTime    NOT NULL,
                PRIMARY KEY (`CrossReference`)
            );";

        return self::$g_szQueryString;
    }

    public static function insert3DS_Transaction($szCrossReference, $szUserID, $iccISOCurrencyCode, $nAmount, $szTransactionDateTime) {

        self::$g_szQueryString = "
            INSERT INTO " . self::tbl3DS_Transactions . "
            (
                `CrossReference`,
                `UserID`,
                `ISOCurrencyCode`,
                `Amount`,
                `TransactionDateTime`
            )
            VALUES
            (
                '$szCrossReference',
                '$szUserID',
                '$iccISOCurrencyCode',
                '$nAmount',
                '$szTransactionDateTime'
            )";

        return self::$g_szQueryString;
    }

    public static function select3DS_UserID($CrossReference) {

        self::$g_szQueryString = "
            SELECT `UserID`
            FROM " . self::tbl3DS_Transactions . "
            WHERE `CrossReference` = '$CrossReference'";

        return self::$g_szQueryString;
    }

    public static function select3DS_ISOCurrencyCode($CrossReference) {

        self::$g_szQueryString = "
            SELECT `ISOCurrencyCode`
            FROM " . self::tbl3DS_Transactions . "
            WHERE `CrossReference` = '$CrossReference'";

        return self::$g_szQueryString;
    }

    public static function select3DS_Amount($CrossReference) {

        self::$g_szQueryString = "
            SELECT `Amount`
            FROM " . self::tbl3DS_Transactions . "
            WHERE `CrossReference` = '$CrossReference'";

        return self::$g_szQueryString;
    }

    public static function delete3DS_Transaction($CrossReference, $UserID) {

        self::$g_szQueryString = "
            DELETE FROM " . self::tbl3DS_Transactions . "
            WHERE `CrossReference` = '$CrossReference'
            AND `UserID` = '$UserID'";

        return self::$g_szQueryString;
    }

    public static function delete3DS_HistoricTransactions() {

        self::$g_szQueryString = "
            DELETE FROM " . self::tbl3DS_Transactions . "
            WHERE `TransactionDateTime` < SUBDATE(NOW(), INTERVAL 2 DAY)";

        return self::$g_szQueryString;
    }

    public static function createHPF_RESULTS() {

        self::$g_szQueryString = "
            CREATE TABLE `" . self::tblHPF_SERVER_Results . "`
            (
                `HashDigest`                            text(64)    NOT NULL,
                `MerchantID`                            text(15)    NOT NULL,
                `StatusCode`                            text(3)     NOT NULL,
                `Message`                               text(512)   ,
                `PreviousStatusCode`                    text(3)     ,
                `PreviousMessage`                       text(512)   ,
                `CrossReference`                        varchar(24) NOT NULL,
                `Amount`                                text(13)    NOT NULL,
                `CurrencyCode`                          text(3)     NOT NULL,
                `OrderID`                               text(50)    NOT NULL,
                `TransactionType`                       text(50)    NOT NULL,
                `TransactionDateTime`                   DateTime    NOT NULL,
                `OrderDescription`                      text(256)   ,
                `CustomerName`                          text(100)   ,
                `Address1`                              text(100)   ,
                `Address2`                              text(100)   ,
                `Address3`                              text(100)   ,
                `Address4`                              text(100)   ,
                `City`                                  text(100)   ,
                `State`                                 text(100)   ,
                `PostCode`                              text(100)   ,
                `CountryCode`                           text(3)     ,
                `EmailAddress`                          text(256)   ,
                `PhoneNumber`                           text(50)    ,
                `CardType`                              text(100)   ,
                `CardClass`                             text(100)   ,
                `CardIssuer`                            text(256)   ,
                `CardIssuerCountryCode`                 text(3)     ,
                `AddressNumericCheckResult`             text(50)    ,
                `PostCodeCheckResult`                   text(50)    ,
                `CV2CheckResult`                        text(50)    ,
                `ThreeDSecureAuthenticationCheckResult` text(50)    ,

                PRIMARY KEY (`CrossReference`)
            );";

        return self::$g_szQueryString;
    }

    public static function insertHPF_SERVER_Results($aResponseVariables) {

        self::$g_szQueryString = "
            INSERT INTO " . self::tblHPF_SERVER_Results . "
            (";

        foreach ($aResponseVariables as $key => $value) {
            self::$g_szQueryString .= "
                        `" . $key . "`,";
        }

        self::$g_szQueryString = substr(self::$g_szQueryString, 0, strlen(self::$g_szQueryString) - 1);

        self::$g_szQueryString .= "
            )
            VALUES
            (
            ";

        foreach ($aResponseVariables as $key => $value) {
            self::$g_szQueryString .= "
                        '" . $value . "',";
        }
        self::$g_szQueryString = substr(self::$g_szQueryString, 0, strlen(self::$g_szQueryString) - 1);

        self::$g_szQueryString .= "   );";

        return self::$g_szQueryString;
    }

    public static function selectHPF_SERVER_Results($CrossReference) {

        $results = array();

        self::$g_szQueryString = "
            SELECT *
            FROM " . self::tblHPF_SERVER_Results . "
            WHERE `CrossReference` = '$CrossReference';";

        return self::$g_szQueryString;
    }

    public static function deleteHPF_HistoricResults() {

        self::$g_szQueryString = "
            DELETE FROM " . self::tblHPF_SERVER_Results . "
            WHERE `TransactionDateTime` < SUBDATE(NOW(), INTERVAL 2 DAY);";

        return self::$g_szQueryString;
    }

}
