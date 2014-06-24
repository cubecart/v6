<?php

class HostedFormReturnType {

    const NONE = NULL;
    const HTMLFormCode = "HTMLFormCode";
    const InstantRedirect = "InstantRedirect";
    const RedirectURL = "RedirectURL";

}

class HostedFormMethod {

    const NONE = NULL;
    const Process = "Process";
    const ReceiveResults = "Receive Results";
    const DisplayResults = "Display Results";
}

class ResultDeliveryMethod {

    const NONE = "NONE";
    const POST = "POST";
    const SERVER = "SERVER";
    const SERVER_PULL = "SERVER_PULL";

}

class PayVectorHosted extends PayVectorBase {

    #
    private $boServerResultCompatMode;
    private $boServerResultResponseCompatMode;
    #
    private $szHostedCrossReference;
    #
    private $hfrtHostedFormReturnType;
    private $hfmHostedFormMethod;
    #
    private $boEmailAddressEditable;
    private $boPhoneNumberEditable;
    #
    private $boCV2Mandatory;
    private $boAddress1Mandatory;
    private $boCityMandatory;
    private $boPostCodeMandatory;
    private $boStateMandatory;
    private $boCountryMandatory;
    #
    private $boIncludeFormTags;
    private $boIncludeFormSubmitButton;
    #
    private $szPreSharedKey;
    private $boPaymentFormDisplaysResult;
    private $rdmResultDeliveryMethod;
    private $szServerResultURL;
    private $szCallbackURL;
    #
    private $szServerResultURLCookieVariables;
    private $szServerResultURLFormVariables;
    private $szServerResultURLQueryStringVariables;
    #
    private $hmHashMethod;
    private $szStringToHash;
    private $szHashDigest;
    # results
    private $aTransactionResponse;
    private $aHostedResponsePaymentHandler;

    //private $this->trTransactionResult;

    public function __construct($szIntegrationSource) {

        parent::__construct($szIntegrationSource);

        $this -> imIntegrationMethod = IntegrationMethod::HostedPaymentForm;

        $this -> boServerResultCompatMode = FALSE;
        $this -> boServerResultResponseCompatMode = FALSE;

        $this -> rdmResultDeliveryMethod = ResultDeliveryMethod::NONE;

        $this -> boThreeDSecureOverridePolicy = TRUE;

        $this -> boEmailAddressEditable = TRUE;
        $this -> boPhoneNumberEditable = TRUE;

        $this -> boIncludeFormTags = TRUE;
        $this -> boIncludeFormSubmitButton = TRUE;
    }

    public function setServerResultCompatMode($boServerResultCompatMode) {
        $this -> boServerResultCompatMode = $boServerResultCompatMode;
    }

    public function getServerResultCompatMode() {
        return $this -> boServerResultCompatMode;
    }

    public function setServerResultResponseCompatMode($boServerResultResponseCompatMode) {
        $this -> boServerResultResponseCompatMode = $boServerResultResponseCompatMode;
    }

    public function getServerResultResponseCompatMode() {
        return $this -> boServerResultResponseCompatMode;
    }

    public function setEmailAddressEditable($boEmailAddressEditable) {
        $this -> boEmailAddressEditable = (bool)self::CleanInput($boEmailAddressEditable);
    }

    public function setPhoneNumberEditable($boPhoneNumberEditable) {
        $this -> boPhoneNumberEditable = (bool)self::CleanInput($boPhoneNumberEditable);
    }

    public function setHostedFormReturnType($hfrtHostedFormReturnType) {
        $this -> hfrtHostedFormReturnType = self::CleanInput($hfrtHostedFormReturnType);
    }

    public function setHostedFormMethod($hfmHostedFormMethod) {
        $this -> hfmHostedFormMethod = $hfmHostedFormMethod;
    }

    public function getHostedFormMethod() {
        return $this -> hfmHostedFormMethod;
    }

    public function setHTML_IncludeFormTags($boIncludeFormTags) {
        $this -> boIncludeFormTags = $boIncludeFormTags;
    }

    public function setHTML_IncludeSubmitButton($boIncludeFormSubmitButton) {
        $this -> boIncludeFormSubmitButton = $boIncludeFormSubmitButton;
    }

    public function setHTMLForm_ButtonCaption($szCaption) {
        $this -> szHTML_ButtonCaption = self::CleanInput($szCaption);
    }

    public function setCV2Mandatory($boCV2Mandatory) {
        $this -> boCV2Mandatory = (bool)self::CleanInput($boCV2Mandatory);
    }

    public function setAddress1Mandatory($boAddress1Mandatory) {
        $this -> boAddress1Mandatory = (bool)self::CleanInput($boAddress1Mandatory);
    }

    public function setCityMandatory($boCityMandatory) {
        $this -> boCityMandatory = (bool)self::CleanInput($boCityMandatory);
    }

    public function setPostCodeMandatory($boPostCodeMandatory) {
        $this -> boPostCodeMandatory = (bool)self::CleanInput($boPostCodeMandatory);
    }

    public function setStateMandatory($boStateMandatory) {
        $this -> boStateMandatory = (bool)self::CleanInput($boStateMandatory);
    }

    public function setCountryMandatory($boCountryMandatory) {
        $this -> boCountryMandatory = (bool)self::CleanInput($boCountryMandatory);
    }

    public function setPaymentFormDisplayResult($boPaymentFormDisplayResult) {
        $this -> boPaymentFormDisplaysResult = (bool)self::CleanInput($boPaymentFormDisplayResult);
    }

    public function getPaymentFormDisplayResult() {
        return $this -> boPaymentFormDisplaysResult;
    }

    public function setResultDeliveryMethod($rdmResultDeliveryMethod) {
        $this -> rdmResultDeliveryMethod = self::CleanInput($rdmResultDeliveryMethod);
    }

    public function setServerResultURL($szServerResultURL) {
        $szServerResultURL = self::CleanInput($szServerResultURL);
        if ($szServerResultURL != NULL) {
            $this->szServerResultURL = $this->getSiteSecureBaseURL() . $szServerResultURL;
        } else {
            $this -> szServerResultURL = $szServerResultURL;
        }
    }

    public function setCallbackURL($szCallbackURL) {
        $szCallbackURL = self::CleanInput($szCallbackURL);
        if ($szCallbackURL != NULL) {
            $this -> szCallbackURL = $this -> getSiteSecureBaseURL() . $szCallbackURL;
        } else {
            $this -> szCallbackURL = $szCallbackURL;
        }
    }

    public function getCallbackURL() {
        return $this -> szCallbackURL;
    }

    public function setHashMethod($hmHashMethod) {
        $this -> hmHashMethod = self::CleanInput($hmHashMethod);
    }

    public function getHashMethod() {
        return $this -> hmHashMethod;
    }

    public function setPreSharedKey($szPreSharedKey) {
        $this -> szPreSharedKey = self::CleanInput($szPreSharedKey);
    }

    public function getPreSharedKey() {
        return $this -> szPreSharedKey;
    }

    public function setHostedTransactionResponse($aTransactionResponse) {
        $this -> aTransactionResponse = $aTransactionResponse;
    }

    public function getHostedTransactionResponse() {
        return $this -> aTransactionResponse;
    }

    public function getHostedPaymentHandler() {
        return "https://mms." . $this -> szPaymentProcessorFullDomain . "Pages/PublicPages/PaymentFormResultHandler.ashx";
    }

    private function getTransactionReferenceFromQueryString() {

        $this -> szHashDigest = "";
        $this -> szErrorMessage = "";
        $this -> boErrorActive = false;

        try {
            // hash digest
            if (isset($this -> aTransactionResponse["HashDigest"])) {
                $this -> szHashDigest = $this -> aTransactionResponse["HashDigest"];
            }

            // cross reference of transaction
            if (!isset($this -> aTransactionResponse["CrossReference"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [CrossReference] not received");
                $this -> boErrorActive = true;
            } else {
                $this -> szHostedCrossReference = $this -> aTransactionResponse["CrossReference"];
            }
            // order ID (same as value passed into payment form - echoed back out by payment form)
            if (!isset($this -> aTransactionResponse["OrderID"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [OrderID] not received");
                $this -> boErrorActive = true;
            } else {
                $this -> szOrderID = $this -> aTransactionResponse["OrderID"];
            }
        } catch (Exception $e) {
            $this -> boErrorActive = true;
            $this -> szErrorMessage = $e -> getMessage();

            if ($this -> boDebugMode && $this -> szDebugEmail) {
                mail($this -> szDebugEmail, "FUNC: getTransactionReferenceFromQueryString", "SERVER RESPONSE: \n" . print_r($this -> aTransactionResponse, 1));
            }
        }
        return (!$this -> boErrorActive);
    }

    private function getTransactionResultFromPostVariables() {

        //$this->trTransactionResult = null;
        $this -> szHashDigest = "";
        $this -> szErrorMessage = "";
        $this -> boErrorActive = false;

        try {
            $this -> trTransactionResult = new TransactionResult();

            // hash digest
            if (isset($this -> aTransactionResponse["HashDigest"])) {
                $this -> szHashDigest = $this -> aTransactionResponse["HashDigest"];
            }

            // transaction status code
            if (!isset($this -> aTransactionResponse["StatusCode"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [StatusCode] not received");
                $this -> boErrorActive = true;
            } else {
                if ($this -> aTransactionResponse["StatusCode"] == "") {
                    $this -> trTransactionResult -> setStatusCode(null);
                } else {
                    $this -> trTransactionResult -> setStatusCode(intval($this -> aTransactionResponse["StatusCode"]));
                }
            }
            // transaction message
            if (!isset($this -> aTransactionResponse["Message"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [Message] not received");
                $this -> boErrorActive = true;
            } else {
                $this -> trTransactionResult -> setMessage($this -> aTransactionResponse["Message"]);
            }
            // status code of original transaction if this transaction was deemed a duplicate
            if (!isset($this -> aTransactionResponse["PreviousStatusCode"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [PreviousStatusCode] not received");
                $this -> boErrorActive = true;
            } else {
                if ($this -> aTransactionResponse["PreviousStatusCode"] == "") {
                    $this -> trTransactionResult -> setPreviousStatusCode(null);
                } else {
                    $this -> trTransactionResult -> setPreviousStatusCode(intval($this -> aTransactionResponse["PreviousStatusCode"]));
                }
            }
            // status code of original transaction if this transaction was deemed a duplicate
            if (!isset($this -> aTransactionResponse["PreviousMessage"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [PreviousMessage] not received");
                $this -> boErrorActive = true;
            } else {
                $this -> trTransactionResult -> setPreviousMessage($this -> aTransactionResponse["PreviousMessage"]);
            }
            // cross reference of transaction
            if (!isset($this -> aTransactionResponse["CrossReference"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [CrossReference] not received");
                $this -> boErrorActive = true;
            } else {
                $this -> trTransactionResult -> setCrossReference($this -> aTransactionResponse["CrossReference"]);
            }
            // amount (same as value passed into payment form - echoed back out by payment form)
            if (!isset($this -> aTransactionResponse["Amount"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [Amount] not received");
                $this -> boErrorActive = true;
            } else {
                if ($this -> aTransactionResponse["Amount"] == "") {
                    $this -> trTransactionResult -> setAmount(NULL);
                } else {
                    $this -> trTransactionResult -> setAmount($this -> aTransactionResponse["Amount"]);
                }
            }

            if (!isset($this -> aTransactionResponse["CurrencyCode"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [CurrencyCode] not received");
                $this -> boErrorActive = true;
            } else {
                if ($this -> aTransactionResponse["CurrencyCode"] == "") {
                    $this -> trTransactionResult -> setCurrencyCode(NULL);
                } else {
                    $this -> trTransactionResult -> setCurrencyCode($this -> aTransactionResponse["CurrencyCode"]);
                }
            }
            // currency code (same as value passed into payment form - echoed back out by payment form)
            // order ID (same as value passed into payment form - echoed back out by payment form)
            if (!isset($this -> aTransactionResponse["OrderID"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [OrderID] not received");
                $this -> boErrorActive = true;
            } else {
                $this -> trTransactionResult -> setOrderID($this -> aTransactionResponse["OrderID"]);
            }
            // transaction type (same as value passed into payment form - echoed back out by payment form)
            if (!isset($this -> aTransactionResponse["TransactionType"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [TransactionType] not received");
                $this -> boErrorActive = true;
            } else {
                $this -> trTransactionResult -> setTransactionType($this -> aTransactionResponse["TransactionType"]);
            }
            // transaction date/time (same as value passed into payment form - echoed back out by payment form)
            if (!isset($this -> aTransactionResponse["TransactionDateTime"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [TransactionDateTime] not received");
                $this -> boErrorActive = true;
            } else {
                $this -> trTransactionResult -> setTransactionDateTime($this -> aTransactionResponse["TransactionDateTime"]);
            }
            // order description (same as value passed into payment form - echoed back out by payment form)
            if (!isset($this -> aTransactionResponse["OrderDescription"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [OrderDescription] not received");
                $this -> boErrorActive = true;
            } else {
                $this -> trTransactionResult -> setOrderDescription($this -> aTransactionResponse["OrderDescription"]);
            }
            // customer name (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($this -> aTransactionResponse["CustomerName"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [CustomerName] not received");
                $this -> boErrorActive = true;
            } else {
                $this -> trTransactionResult -> setCustomerName($this -> aTransactionResponse["CustomerName"]);
            }
            // address1 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($this -> aTransactionResponse["Address1"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [Address1] not received");
                $this -> boErrorActive = true;
            } else {
                $this -> trTransactionResult -> setAddress1($this -> aTransactionResponse["Address1"]);
            }
            // address2 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($this -> aTransactionResponse["Address2"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [Address2] not received");
                $this -> boErrorActive = true;
            } else {
                $this -> trTransactionResult -> setAddress2($this -> aTransactionResponse["Address2"]);
            }
            // address3 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($this -> aTransactionResponse["Address3"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [Address3] not received");
                $this -> boErrorActive = true;
            } else {
                $this -> trTransactionResult -> setAddress3($this -> aTransactionResponse["Address3"]);
            }
            // address4 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($this -> aTransactionResponse["Address4"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [Address4] not received");
                $this -> boErrorActive = true;
            } else {
                $this -> trTransactionResult -> setAddress4($this -> aTransactionResponse["Address4"]);
            }
            // city (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($this -> aTransactionResponse["City"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [City] not received");
                $this -> boErrorActive = true;
            } else {
                $this -> trTransactionResult -> setCity($this -> aTransactionResponse["City"]);
            }
            // state (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($this -> aTransactionResponse["State"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [State] not received");
                $this -> boErrorActive = true;
            } else {
                $this -> trTransactionResult -> setState($this -> aTransactionResponse["State"]);
            }
            // post code (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($this -> aTransactionResponse["PostCode"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [PostCode] not received");
                $this -> boErrorActive = true;
            } else {
                $this -> trTransactionResult -> setPostCode($this -> aTransactionResponse["PostCode"]);
            }
            // country code (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($this -> aTransactionResponse["CountryCode"])) {
                $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [CountryCode]mail not received");
                $this -> boErrorActive = true;
            } else {
                if ($this -> aTransactionResponse["CountryCode"] == "") {
                    $this -> trTransactionResult -> setCountryCode(NULL);
                } else {
                    $this -> trTransactionResult -> setCountryCode($this -> aTransactionResponse["CountryCode"]);
                }
            }

            if ($this -> rdmResultDeliveryMethod != ResultDeliveryMethod::SERVER_PULL) {
                if (!isset($this -> aTransactionResponse["AddressNumericCheckResult"])) {
                    $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [AddressNumericCheckResult] not received");
                    $this -> boErrorActive = true;
                } else {
                    if ($this -> aTransactionResponse["AddressNumericCheckResult"] == "") {
                        $this -> trTransactionResult -> setAddressNumericCheckResult(null);
                    } else {
                        $this -> trTransactionResult -> setAddressNumericCheckResult($this -> aTransactionResponse["AddressNumericCheckResult"]);
                    }
                }
                if (!isset($this -> aTransactionResponse["PostCodeCheckResult"])) {
                    $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [PostCodeCheckResult] not received");
                    $this -> boErrorActive = true;
                } else {
                    if ($this -> aTransactionResponse["PostCodeCheckResult"] == "") {
                        $this -> trTransactionResult -> setPostCodeCheckResult(null);
                    } else {
                        $this -> trTransactionResult -> setPostCodeCheckResult($this -> aTransactionResponse["PostCodeCheckResult"]);
                    }
                }
                if (!isset($this -> aTransactionResponse["CV2CheckResult"])) {
                    $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [CV2CheckResult] not received");
                    $this -> boErrorActive = true;
                } else {
                    if ($this -> aTransactionResponse["CV2CheckResult"] == "") {
                        $this -> trTransactionResult -> setCV2CheckResult(null);
                    } else {
                        $this -> trTransactionResult -> setCV2CheckResult($this -> aTransactionResponse["CV2CheckResult"]);
                    }
                }
                if (!isset($this -> aTransactionResponse["ThreeDSecureAuthenticationCheckResult"])) {
                    $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [ThreeDSecureAuthenticationCheckResult] not received");
                    $this -> boErrorActive = true;
                } else {
                    if ($this -> aTransactionResponse["ThreeDSecureAuthenticationCheckResult"] == "") {
                        $this -> trTransactionResult -> setThreeDSecureAuthenticationCheckResult(null);
                    } else {
                        $this -> trTransactionResult -> setThreeDSecureAuthenticationCheckResult($this -> aTransactionResponse["ThreeDSecureAuthenticationCheckResult"]);
                    }
                }
                if (!isset($this -> aTransactionResponse["CardType"])) {
                    $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [CardType] not received");
                    $this -> boErrorActive = true;
                } else {
                    if ($this -> aTransactionResponse["CardType"] == "") {
                        $this -> trTransactionResult -> setCardType(null);
                    } else {
                        $this -> trTransactionResult -> setCardType($this -> aTransactionResponse["CardType"]);
                    }
                }
                if (!isset($this -> aTransactionResponse["CardClass"])) {
                    $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [CardClass] not received");
                    $this -> boErrorActive = true;
                } else {
                    if ($this -> aTransactionResponse["CardClass"] == "") {
                        $this -> trTransactionResult -> setCardClass(null);
                    } else {
                        $this -> trTransactionResult -> setCardClass($this -> aTransactionResponse["CardClass"]);
                    }
                }
                if (!isset($this -> aTransactionResponse["CardIssuer"])) {
                    $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [CardIssuer] not received");
                    $this -> boErrorActive = true;
                } else {
                    if ($this -> aTransactionResponse["CardIssuer"] == "") {
                        $this -> trTransactionResult -> setCardIssuer(null);
                    } else {
                        $this -> trTransactionResult -> setCardIssuer($this -> aTransactionResponse["CardIssuer"]);
                    }
                }
                if (!isset($this -> aTransactionResponse["CardIssuerCountryCode"])) {
                    $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [CardIssuerCountryCode] not received");
                    $this -> boErrorActive = true;
                } else {
                    if ($this -> aTransactionResponse["CardIssuerCountryCode"] == "") {
                        $this -> trTransactionResult -> setCardIssuerCountryCode(null);
                    } else {
                        $this -> trTransactionResult -> setCardIssuerCountryCode(intval($this -> aTransactionResponse["CardIssuerCountryCode"]));
                    }
                }
                if (!isset($this -> aTransactionResponse["EmailAddress"])) {
                    $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [EmailAddress] not received");
                    $this -> boErrorActive = true;
                } else {
                    if ($this -> aTransactionResponse["EmailAddress"] == "") {
                        $this -> trTransactionResult -> setEmailAddress(null);
                    } else {
                        $this -> trTransactionResult -> setEmailAddress($this -> aTransactionResponse["EmailAddress"]);
                    }
                }
                if (!isset($this -> aTransactionResponse["PhoneNumber"])) {
                    $this -> szErrorMessage = PaymentFormHelper::addStringToStringList($this -> szErrorMessage, "Expected variable [PhoneNumber] not received");
                    $this -> boErrorActive = true;
                } else {
                    if ($this -> aTransactionResponse["PhoneNumber"] == "") {
                        $this -> trTransactionResult -> setPhoneNumber(null);
                    } else {
                        $this -> trTransactionResult -> setPhoneNumber($this -> aTransactionResponse["PhoneNumber"]);
                    }
                }
            }

            if ($this -> boErrorActive && $this -> boDebugMode && $this -> szDebugEmail != NULL) {
                mail($this -> szDebugEmail, "FUNC: PayVectorHosted:getTransactionResultFromPostVariables()", "ERROR: \n" . print_r($this -> szErrorMessage, 1));
            }
        } catch (Exception $e) {
            $this -> boErrorActive = true;
            $this -> szErrorMessage = $e -> getMessage();

            if ($this -> boDebugMode && $this -> szDebugEmail != NULL) {
                mail($this -> szDebugEmail, "FUNC: PayVectorHosted:getTransactionResultFromPostVariables()", "ERROR: \n" . print_r($this -> szErrorMessage, 1));
            }
        }

        return (!$this -> boErrorActive);
    }

    public static function getSiteSecureBaseURL() {

        $szReturnString = "";
        $szPortString = "";
        $szProtocolString = "";

        if ($_SERVER["HTTPS"] == "on") {
            $szProtocolString = "https://";
            if ($_SERVER["SERVER_PORT"] != 443) {
                $szPortString = ":" . $_SERVER["SERVER_PORT"];
            }
        } else {
            $szProtocolString = "http://";
            if ($_SERVER["SERVER_PORT"] != 80) {
                $szPortString = ":" . $_SERVER["SERVER_PORT"];
            }
        }

        $szReturnString = $szProtocolString . $_SERVER["SERVER_NAME"] . $szPortString . $_SERVER["SCRIPT_NAME"];

        $boFinished = false;
        $LoopIndex = strlen($szReturnString) - 1;

        while ($boFinished == false && $LoopIndex >= 0) {
            if ($szReturnString[$LoopIndex] == "/") {
                $boFinished = true;
                $szReturnString = substr($szReturnString, 0, $LoopIndex + 1);
            }
            $LoopIndex--;
        }

        return ($szReturnString);
    }

    private function generateStringToHash() {

        $szReturnString = "";

        if ($this -> IncludePreSharedKeyInString()) {
            $szReturnString = "PreSharedKey=" . $this -> szPreSharedKey . "&";
        }

        $szReturnString .= "MerchantID=" . $this -> szMerchantID . "&Password=" . $this -> szPassword . "&Amount=" . $this -> nAmountUndecimalised . "&CurrencyCode=" . $this -> iccISOCurrencyCode . "&EchoAVSCheckResult=" . self::boolToString($this -> boEchoAVSCheckResult) . "&EchoCV2CheckResult=" . self::boolToString($this -> boEchoCV2CheckResult) . "&EchoThreeDSecureAuthenticationCheckResult=" . self::boolToString($this -> boEchoThreeDSecureAuthenticationCheckResult) . "&EchoCardType=" . self::boolToString($this -> boEchoCardType);

        if (!$szAVSOverridePolicy == NULL) {
            $szReturnString .= "&AVSOverridePolicy=" . $this -> szAVSOverridePolicy;
        }
        if (!$szCV2OverridePolicy == NULL) {
            $szReturnString .= "&CV2OverridePolicy=" . $this -> szCV2OverridePolicy;
        }

        $szReturnString .= "&ThreeDSecureOverridePolicy=" . self::boolToString($this -> boThreeDSecureOverridePolicy) . "&OrderID=" . $this -> szOrderID . "&TransactionType=" . $this -> ttTransactionType . "&TransactionDateTime=" . $this -> szTransactionDateTime . "&CallbackURL=" . $this -> szCallbackURL . "&OrderDescription=" . $this -> szOrderDescription . "&CustomerName=" . $this -> szCardName . "&Address1=" . $this -> szAddress1 . "&Address2=" . $this -> szAddress2 . "&Address3=" . $this -> szAddress3 . "&Address4=" . $this -> szAddress4 . "&City=" . $this -> szCity . "&State=" . $this -> szState . "&PostCode=" . $this -> szPostCode . "&CountryCode=" . $this -> iccISOCountryCode . "&EmailAddress=" . $this -> szEmailAddress . "&PhoneNumber=" . $this -> szPhoneNumber . "&EmailAddressEditable=" . self::boolToString($this -> boEmailAddressEditable) . "&PhoneNumberEditable=" . self::boolToString($this -> boPhoneNumberEditable) . "&CV2Mandatory=" . self::boolToString($this -> boCV2Mandatory) . "&Address1Mandatory=" . self::boolToString($this -> boAddress1Mandatory) . "&CityMandatory=" . self::boolToString($this -> boCityMandatory) . "&PostCodeMandatory=" . self::boolToString($this -> boPostCodeMandatory) . "&StateMandatory=" . self::boolToString($this -> boStateMandatory) . "&CountryMandatory=" . self::boolToString($this -> boCountryMandatory) . "&ResultDeliveryMethod=" . $this -> rdmResultDeliveryMethod . "&ServerResultURL=" . $this -> szServerResultURL . "&PaymentFormDisplaysResult=" . self::boolToString($this -> boPaymentFormDisplaysResult) . '&ServerResultURLCookieVariables=' . '&ServerResultURLFormVariables=' . '&ServerResultURLQueryStringVariables=';

        return $szReturnString;
    }

    private function IncludePreSharedKeyInString() {

        switch ($this->hmHashMethod) {
            case HashMethod::MD5 :
                $boIncludePreSharedKeyInString = true;
                break;
            case HashMethod::SHA1 :
                $boIncludePreSharedKeyInString = true;
                break;
            case HashMethod::HMACMD5 :
                $boIncludePreSharedKeyInString = false;
                break;
            case HashMethod::HMACSHA1 :
                $boIncludePreSharedKeyInString = false;
                break;
        }

        return $boIncludePreSharedKeyInString;
    }

    private function generateStringToHash2() {

        $szReturnString = "";

        if ($this -> IncludePreSharedKeyInString()) {
            $szReturnString = "PreSharedKey=" . $this -> szPreSharedKey . "&";
        }

        $szReturnString .= "MerchantID=" . $this -> szMerchantID . "&Password=" . $this -> szPassword . "&StatusCode=" . $this -> trTransactionResult -> getStatusCode() . "&Message=" . $this -> trTransactionResult -> getMessage() . "&PreviousStatusCode=" . $this -> trTransactionResult -> getPreviousStatusCode() . "&PreviousMessage=" . $this -> trTransactionResult -> getPreviousMessage() . "&CrossReference=" . $this -> trTransactionResult -> getCrossReference();

        if (!$this -> boServerResultCompatMode) {
            $szReturnString .= "&AddressNumericCheckResult=" . $this -> trTransactionResult -> getAddressNumericCheckResult() . "&PostCodeCheckResult=" . $this -> trTransactionResult -> getPostCodeCheckResult() . "&CV2CheckResult=" . $this -> trTransactionResult -> getCV2CheckResult();
            if ($this -> boThreeDSecureCompatMode) {
                $szReturnString .= "&ThreeDSecureCheckResult=" . $this -> trTransactionResult -> getThreeDSecureAuthenticationCheckResult();
            } else {
                $szReturnString .= "&ThreeDSecureAuthenticationCheckResult=" . $this -> trTransactionResult -> getThreeDSecureAuthenticationCheckResult();
            }
            $szReturnString .= "&CardType=" . $this -> trTransactionResult -> getCardType() . "&CardClass=" . $this -> trTransactionResult -> getCardClass() . "&CardIssuer=" . $this -> trTransactionResult -> getCardIssuer() . "&CardIssuerCountryCode=" . $this -> trTransactionResult -> getCardIssuerCountryCode();
        }

        $szReturnString .= "&Amount=" . $this -> trTransactionResult -> getAmount() . "&CurrencyCode=" . $this -> trTransactionResult -> getCurrencyCode() . "&OrderID=" . $this -> trTransactionResult -> getOrderID() . "&TransactionType=" . $this -> trTransactionResult -> getTransactionType() . "&TransactionDateTime=" . $this -> trTransactionResult -> getTransactionDateTime() . "&OrderDescription=" . $this -> trTransactionResult -> getOrderDescription() . "&CustomerName=" . $this -> trTransactionResult -> getCustomerName() . "&Address1=" . $this -> trTransactionResult -> getAddress1() . "&Address2=" . $this -> trTransactionResult -> getAddress2() . "&Address3=" . $this -> trTransactionResult -> getAddress3() . "&Address4=" . $this -> trTransactionResult -> getAddress4() . "&City=" . $this -> trTransactionResult -> getCity() . "&State=" . $this -> trTransactionResult -> getState() . "&PostCode=" . $this -> trTransactionResult -> getPostCode() . "&CountryCode=" . $this -> trTransactionResult -> getCountryCode() . "&EmailAddress=" . $this -> trTransactionResult -> getEmailAddress() . "&PhoneNumber=" . $this -> trTransactionResult -> getPhoneNumber();

        return ($szReturnString);
    }

    private function generateStringToHash3() {

        $szReturnString = "";

        if ($this -> IncludePreSharedKeyInString()) {
            $szReturnString = "PreSharedKey=" . $this -> szPreSharedKey . "&";
        }

        $szReturnString .= "MerchantID=" . $this -> szMerchantID . "&Password=" . $this -> szPassword . "&CrossReference=" . $this -> szHostedCrossReference . "&OrderID=" . $this -> szOrderID;

        return ($szReturnString);
    }

    private function calculateHashDigest() {

        $szHashDigest = null;
        switch ($this->hmHashMethod) {
            case HashMethod::MD5 :
                $szHashDigest = md5($this -> szStringToHash);
                break;
            case HashMethod::SHA1 :
                $szHashDigest = sha1($this -> szStringToHash);
                break;
            case HashMethod::HMACMD5 :
                $szHashDigest = hash_hmac("md5", $this -> szStringToHash, $this -> szPreSharedKey);
                break;
            case HashMethod::HMACSHA1 :
                $szHashDigest = hash_hmac("sha1", $this -> szStringToHash, $this -> szPreSharedKey);
                break;
        }

        return $szHashDigest;
    }

    private function validateTransactionResult_POST() {

        $this -> boErrorActive = false;

        // read the transaction result variables from the post variable list
        if (!$this -> getTransactionResultFromPostVariables()) {
            $this -> boErrorActive = true;
            $this -> szErrorMessage = $this -> szErrorMessage;
        } else {
            // now need to validate the hash digest
            $this -> szStringToHash = $this -> generateStringToHash2();
            $this -> szHashDigest = $this -> calculateHashDigest();

            // does the calculated hash match the one that was passed?
            if (strToUpper($this -> aTransactionResponse['HashDigest']) != strToUpper($this -> szHashDigest)) {
                $this -> boErrorActive = true;
                $this -> szErrorMessage = "Hash digests don't match - possible variable tampering";
            }
        }

        return (!$this -> boErrorActive);
    }

    private function validateTransactionResult_SERVER() {

        $this -> boErrorActive = false;

        // read the transaction reference variables from the query string variable list
        if ($this -> hfmHostedFormMethod == HostedFormMethod::ReceiveResults) {
            if ($this -> getTransactionResultFromPostVariables()) {
                $this -> szStringToHash = $this -> generateStringToHash2();
            }
        } else {
            if ($this -> getTransactionReferenceFromQueryString()) {
                $this -> szStringToHash = $this -> generateStringToHash3();
            }
        }

        if (!$this -> boErrorActive) {

            // now need to validate the hash digest
            $szCalculatedHashDigest = $this -> calculateHashDigest();

            // does the calculated hash match the one that was passed?
            if (strToUpper($szCalculatedHashDigest) != strToUpper($this -> szHashDigest)) {
                $this -> boErrorActive = true;
                $this -> szErrorMessage = "Hash digests don't match - possible variable tampering";
                $this -> szErrorMessage .= "\n\n" . $this -> szStringToHash . "\n" . $this -> szHashDigest . "\n\n" . $this -> aTransactionResponse['HashDigest'] . "\n\n" . print_r($this -> aTransactionResponse, 1);
            } else {

                require (dirname(dirname(__FILE__)) . '/Callback/Hosted/ServerMethodResponse.php');

                if ($this -> hfmHostedFormMethod == HostedFormMethod::ReceiveResults) {//&& $this->boDatabaseSupport ) {
                    StoreServerMethodResponse($this -> aTransactionResponse);
                } else {

                    $this -> aTransactionResponse = RetrieveServerMethodResponse($this -> szHostedCrossReference);

                    $this -> trTransactionResult = new TransactionResult();
                    $this -> trTransactionResult -> setStatusCode($this -> aTransactionResponse['StatusCode']);
                    // transaction status code
                    $this -> trTransactionResult -> setMessage($this -> aTransactionResponse['Message']);
                    // transaction message
                    $this -> trTransactionResult -> setPreviousStatusCode($this -> aTransactionResponse['PreviousStatusCode']);
                    // status code of original transaction if duplicate transaction
                    $this -> trTransactionResult -> setPreviousMessage($this -> aTransactionResponse['PreviousMessage']);
                    // status code of original transaction if duplicate transaction
                    $this -> trTransactionResult -> setCrossReference($this -> aTransactionResponse['CrossReference']);
                    // cross reference of transaction
                    $this -> trTransactionResult -> setAddressNumericCheckResult($this -> aTransactionResponse['AddressNumericCheckResult']);
                    $this -> trTransactionResult -> setPostCodeCheckResult($this -> aTransactionResponse['PostCodeCheckResult']);
                    $this -> trTransactionResult -> setCV2CheckResult($this -> aTransactionResponse['CV2CheckResult']);
                    $this -> trTransactionResult -> setThreeDSecureAuthenticationCheckResult($this -> aTransactionResponse['ThreeDSecureAuthenticationCheckResult']);
                    $this -> trTransactionResult -> setCardType($this -> aTransactionResponse['CardType']);
                    $this -> trTransactionResult -> setCardClass($this -> aTransactionResponse['CardClass']);
                    $this -> trTransactionResult -> setCardIssuer($this -> aTransactionResponse['CardIssuer']);
                    $this -> trTransactionResult -> setCardIssuerCountryCode($this -> aTransactionResponse['CardIssuerCountryCode']);
                    $this -> trTransactionResult -> setAmount($this -> aTransactionResponse['Amount']);
                    // amount echoed back
                    $this -> trTransactionResult -> setCurrencyCode($this -> aTransactionResponse['CurrencyCode']);
                    // currency code echoed back
                    $this -> trTransactionResult -> setOrderID($this -> aTransactionResponse['OrderID']);
                    // order ID echoed back
                    $this -> trTransactionResult -> setTransactionType($this -> aTransactionResponse['TransactionType']);
                    // transaction type echoed back
                    $this -> trTransactionResult -> setTransactionDateTime($this -> aTransactionResponse['TransactionDateTime']);
                    // transaction date/time echoed back
                    $this -> trTransactionResult -> setOrderDescription($this -> aTransactionResponse['OrderDescription']);
                    // order description echoed back
                    // the customer details that were actually
                    // processed (might be different
                    // from those passed to the payment form)
                    $this -> trTransactionResult -> setCustomerName($this -> aTransactionResponse['CustomerName']);
                    $this -> trTransactionResult -> setAddress1($this -> aTransactionResponse['Address1']);
                    $this -> trTransactionResult -> setAddress2($this -> aTransactionResponse['Address2']);
                    $this -> trTransactionResult -> setAddress3($this -> aTransactionResponse['Address3']);
                    $this -> trTransactionResult -> setAddress4($this -> aTransactionResponse['Address3']);
                    $this -> trTransactionResult -> setCity($this -> aTransactionResponse['City']);
                    $this -> trTransactionResult -> setState($this -> aTransactionResponse['State']);
                    $this -> trTransactionResult -> setPostCode($this -> aTransactionResponse['PostCode']);
                    $this -> trTransactionResult -> setCountryCode($this -> aTransactionResponse['CountryCode']);
                    $this -> trTransactionResult -> setEmailAddress($this -> aTransactionResponse['EmailAddress']);
                    $this -> trTransactionResult -> setPhoneNumber($this -> aTransactionResponse['PhoneNumber']);

                    $this -> setResultsAmountAndCurrency($this -> trTransactionResult -> getAmount(), $this -> trTransactionResult -> getCurrencyCode());
                    $this -> ttTransactionType = $this -> trTransactionResult -> getTransactionType();
                    $this -> szOrderID = $this -> trTransactionResult -> getOrderID();
                    $this -> szOrderDescription = $this -> trTransactionResult -> getOrderDescription();
                }
            }
        }

        return (!$this -> boErrorActive);
    }

    private function validateTransactionResult_SERVER_PULL() {

        $this -> boErrorActive = false;
        $this -> trTransactionResult = null;

        // read the transaction reference variables from the query string variable list
        if (!$this -> getTransactionReferenceFromQueryString()) {
            $this -> boErrorActive = true;
        } else {
            // now need to validate the hash digest
            $this -> szStringToHash = $this -> generateStringToHash3();
            $szCalculatedHashDigest = $this -> calculateHashDigest();

            // does the calculated hash match the one that was passed?
            if (strToUpper($this -> szHashDigest) != strToUpper($szCalculatedHashDigest)) {
                $this -> boErrorActive = true;
                $this -> szErrorMessage = "Hash digests don't match - possible variable tampering";
            } else {
                // use the cross reference and/or the order ID to pull the
                // transaction results out of storage
                if (!$this -> getTransactionResultFromPaymentFormHandler()) {
                    $this -> szErrorMessage = "Error querying transaction result [" . $this -> szCrossReference . "] from [" . $szPaymentFormResultHandlerURL . "]: " . $this -> szErrorMessage;
                    $this -> boErrorActive = true;
                } else {
                    $this -> boErrorActive = false;
                }
            }
        }

        return (!$this -> boErrorActive);
    }

    private function getTransactionResultFromPaymentFormHandler() {

        $this -> boErrorActive = false;
        $this -> szErrorMessage = "";
        $this -> trTransactionResult = null;

        try {
            // use curl to post the cross reference to the
            // payment form to query its status
            $cCURL = curl_init();

            // build up the post string
            $szPostString = "MerchantID=" . urlencode($this -> szMerchantID) . "&Password=" . urlencode($this -> szPassword) . "&CrossReference=" . urlencode($this -> szHostedCrossReference);

            curl_setopt($cCURL, CURLOPT_URL, $this -> getHostedPaymentHandler());
            curl_setopt($cCURL, CURLOPT_POST, true);
            curl_setopt($cCURL, CURLOPT_POSTFIELDS, $szPostString);
            curl_setopt($cCURL, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($cCURL, CURLOPT_RETURNTRANSFER, true);

            // read the response
            $szResponse = curl_exec($cCURL);
            $szErrorNo = curl_errno($cCURL);
            $ezErrorMsg = curl_error($cCURL);
            $szHeaderInfo = curl_getinfo($cCURL);

            curl_close($cCURL);

            if ($szResponse == "") {
                $this -> boErrorActive = true;
                $this -> szErrorMessage = "Received empty response from payment form hander";
            } else {
                try {
                    // parse the response into an array
                    $this -> aTransactionResponse = PaymentFormHelper::parseNameValueStringIntoArray($szResponse, true);

                    if (!isset($this -> aTransactionResponse["StatusCode"]) OR intval($this -> aTransactionResponse["StatusCode"]) != 0) {
                        $this -> boErrorActive = true;

                        // the message field is expected if the status code is non-zero
                        if (!isset($this -> aTransactionResponse["Message"])) {
                            $this -> szErrorMessage = "Received invalid response from payment form hander [" . $szResponse . "]";
                        } else {
                            $this -> szErrorMessage = $this -> aTransactionResponse["Message"];
                        }
                    } else {
                        // status code is 0, so get the transaction result
                        if (!isset($this -> aTransactionResponse["TransactionResult"])) {
                            $this -> boErrorActive = true;
                            $this -> szErrorMessage = "No transaction result in response from payment form hander [" . $szResponse . "]";
                        } else {
                            // parse the URL decoded transaction result field into a name value array
                            $aTransactionResultArray = PaymentFormHelper::parseNameValueStringIntoArray(urldecode($this -> aTransactionResponse["TransactionResult"]), false);
                            $this -> aTransactionResponse = $aTransactionResultArray;

                            // parse this array into a transaction result object
                            if (!$this -> getTransactionResultFromPostVariables()) {
                                $this -> boErrorActive = true;
                                $this -> szErrorMessage = "Error [" . $this -> szErrorMessage . "] parsing transaction result [" . urldecode($this -> aTransactionResponse["TransactionResult"]) . "] in response from payment form hander [" . $szResponse . "]";

                                if ($this -> boErrorActive && $this -> boDebugMode && $this -> szDebugEmail != NULL) {
                                    mail($this -> szDebugEmail, "FUNC: PayVectorHosted:getTransactionResultFromPostVariables()", "ERROR: \n" . print_r($this -> szErrorMessage, 1));
                                }
                            } else {
                                $this -> boErrorActive = false;
                            }
                        }
                    }
                } catch (Exception $e) {
                    $this -> boErrorActive = true;
                    $this -> szErrorMessage = "Exception [" . $e -> getMessage() . "] when processing response from payment form handler [" . $szResponse . "]";
                }
            }
        } catch (Exception $e) {
            $this -> boErrorActive = true;
            $this -> szErrorMessage = $e -> getMessage();
        }

        return (!$this -> boErrorActive);
    }

    private function IsReady() {

        parent::IsReadyBase();

        if ($this -> hmHashMethod == HashMethod::NONE) {
            array_push($this -> szErrorMessage, Errors::NoHashMethodSelected);
        }
        if ($this -> szPreSharedKey == NULL) {
            array_push($this -> szErrorMessage, Errors::NoPreSharedKeySpecified);
        }

        if ($this -> hfmHostedFormMethod == HostedFormMethod::NONE) {
            array_push($this -> szErrorMessage, Errors::NoHostedFormMethodSelected);
        } else {
            switch ($this->hfmHostedFormMethod) {
                case HostedFormMethod::Process :
                    if ($this -> ttTransactionType == TransactionType::NONE) {
                        array_push($this -> szErrorMessage, Errors::NoTransactionTypeSelected);
                    }
                    if ($this -> nAmount == NULL) {
                        array_push($this -> szErrorMessage, Errors::NoAmountSpecified);
                    }
                    if ($this -> iccISOCurrencyCode == NULL) {
                        array_push($this -> szErrorMessage, Errors::NoCurrencySpecified);
                    }
                    if ($this -> rdmResultDeliveryMethod == NULL) {
                        array_push($this -> szErrorMessage, Errors::NoResultDeliveryMethodSelected);
                    }
                    if ($this -> szCallbackURL == NULL) {
                        array_push($this -> szErrorMessage, Errors::NoCallbackURLSpecified);
                    }
                    break;
                case HostedFormMethod::ReceiveResults :
                    if ($this -> aTransactionResponse == null) {
                        array_push($this -> szErrorMessage, Errors::NoHostedTransactionResponseSpecified);
                    }
                    break;
                case HostedFormMethod::DisplayResults :
                    break;
            }
        }

        $this -> boErrorActive = !(empty($this -> szErrorMessage));

        if (!$this -> boErrorActive) {
            if ($this -> szHTML_ButtonCaption == NULL) {
                $this -> szHTML_ButtonCaption = "Pay Now";
            }
        }

        if ($this -> boErrorActive && $this -> boDebugMode && $this -> szDebugEmail != NULL) {
            mail($this -> szDebugEmail, "FUNC: PayVectorHosted:IsReady()", "ERROR: \n" . print_r($this -> szErrorMessage, 1));
        }
        return !$this -> boErrorActive;
    }

    public function Process() {

        $return = NULL;

        if ($this -> IsReady()) {
            parent::Process();

            switch ($this->hfmHostedFormMethod) {
                case HostedFormMethod::Process :
                    switch ($this->hfrtHostedFormReturnType) {
                        case HostedFormReturnType::HTMLFormCode :
                            $return = $this -> GenerateHostedPaymentForm();
                            break;
                    }
                    break;
                default :
                    $return = $this -> HostedTransactionResult();
                    break;
            }

            //            if (!$this->boTransactionProcessed) {
            //                $this->boErrorActive = TRUE;
            //                //array_push($this->szErrorMessage, Errors::NoCommunicationWithGateway);
            //            }
        } else {
            $return = FALSE;
        }

        return $return;
    }

    private function GenerateHostedPaymentForm() {

        $this -> szStringToHash = $this -> generateStringToHash();
        $this -> szHashDigest = $this -> calculateHashDigest();

        if ($this -> boIncludeFormTags) {
            $return = '<form name="contactForm" id="contactForm" method="POST" action="https://mms.payvector.net/Pages/PublicPages/PaymentForm.aspx" >';
        }

        if ($this -> szAVSOverridePolicy != NULL) {
            $return .= '    
            <input type="hidden" name="AVSOverridePolicy" value="' . $this -> szAVSOverridePolicy . '" />
            ';
        }
        if ($this -> szCV2OverridePolicy != NULL) {
            $return .= '    
            <input type="hidden" name="CV2OverridePolicy" value="' . $this -> szCV2OverridePolicy . '" />
            ';
        }

        $return .= '
            <input type="hidden" name="ThreeDSecureCompatMode" value="' . PaymentFormHelper::boolToString($this -> boThreeDSecureCompatMode) . '" />
            <input type="hidden" name="ServerResultCompatMode" value="' . PaymentFormHelper::boolToString($this -> boServerResultCompatMode) . '" />
            <input type="hidden" name="ServerResultResponseCompatMode" value="' . PaymentFormHelper::boolToString($this -> boServerResultResponseCompatMode) . '" />
                            ' . '
            <input type="hidden" name="HashDigest" value="' . $this -> szHashDigest . '" />
            <input type="hidden" name="MerchantID" value="' . $this -> szMerchantID . '" />
            <input type="hidden" name="Amount" value="' . $this -> nAmountUndecimalised . '" />
            <input type="hidden" name="CurrencyCode" value="' . $this -> iccISOCurrencyCode . '" />
            <input type="hidden" name="EchoAVSCheckResult" value="' . PaymentFormHelper::boolToString($this -> boEchoAVSCheckResult) . '" />
            <input type="hidden" name="EchoCV2CheckResult" value="' . PaymentFormHelper::boolToString($this -> boEchoCV2CheckResult) . '" />
            <input type="hidden" name="EchoThreeDSecureAuthenticationCheckResult" value="' . PaymentFormHelper::boolToString($this -> boEchoThreeDSecureAuthenticationCheckResult) . '" />
            <input type="hidden" name="EchoCardType" value="' . PaymentFormHelper::boolToString($this -> boEchoCardType) . '" />
            <input type="hidden" name="ThreeDSecureOverridePolicy" value="' . PaymentFormHelper::boolToString($this -> boThreeDSecureOverridePolicy) . '" />
                            ' . '
            <input type="hidden" name="OrderID" value="' . $this -> szOrderID . '" />
            <input type="hidden" name="TransactionType" value="' . $this -> ttTransactionType . '" />
            <input type="hidden" name="TransactionDateTime" value="' . $this -> szTransactionDateTime . '" />
            <input type="hidden" name="CallbackURL" value="' . $this -> szCallbackURL . '" />
            <input type="hidden" name="OrderDescription" value="' . $this -> szOrderDescription . '" />
            <input type="hidden" name="CustomerName" value="' . $this -> szCardName . '" />
                            ' . '
            <input type="hidden" name="Address1" value="' . $this -> szAddress1 . '" />
            <input type="hidden" name="Address2" value="' . $this -> szAddress2 . '" />
            <input type="hidden" name="Address3" value="' . $this -> szAddress3 . '" />
            <input type="hidden" name="Address4" value="' . $this -> szAddress4 . '" />
            <input type="hidden" name="City" value="' . $this -> szCity . '" />
            <input type="hidden" name="State" value="' . $this -> szState . '" />
            <input type="hidden" name="PostCode" value="' . $this -> szPostCode . '" />
            <input type="hidden" name="CountryCode" value="' . $this -> iccISOCountryCode . '" />
                            ' . '
            <input type="hidden" name="EmailAddress" value="' . $this -> szEmailAddress . '" />
            <input type="hidden" name="PhoneNumber" value="' . $this -> szPhoneNumber . '" />
            <input type="hidden" name="EmailAddressEditable" value="' . PaymentFormHelper::boolToString($this -> boEmailAddressEditable) . '" />
            <input type="hidden" name="PhoneNumberEditable" value="' . PaymentFormHelper::boolToString($this -> boPhoneNumberEditable) . '" />
                            ' . '
            <input type="hidden" name="CV2Mandatory" value="' . PaymentFormHelper::boolToString($this -> boCV2Mandatory) . '" />
            <input type="hidden" name="Address1Mandatory" value="' . PaymentFormHelper::boolToString($this -> boAddress1Mandatory) . '" />
            <input type="hidden" name="CityMandatory" value="' . PaymentFormHelper::boolToString($this -> boCityMandatory) . '" />
            <input type="hidden" name="PostCodeMandatory" value="' . PaymentFormHelper::boolToString($this -> boPostCodeMandatory) . '" />
            <input type="hidden" name="StateMandatory" value="' . PaymentFormHelper::boolToString($this -> boStateMandatory) . '" />
            <input type="hidden" name="CountryMandatory" value="' . PaymentFormHelper::boolToString($this -> boCountryMandatory) . '" />
            <input type="hidden" name="ResultDeliveryMethod" value="' . $this -> rdmResultDeliveryMethod . '" />
            <input type="hidden" name="ServerResultURL" value="' . $this -> szServerResultURL . '" />
            <input type="hidden" name="PaymentFormDisplaysResult" value="' . PaymentFormHelper::boolToString($this -> boPaymentFormDisplaysResult) . '" />
                            ' . '
            <input type="hidden" name="ServerResultURLCookieVariables" value="" />
            <input type="hidden" name="ServerResultURLFormVariables" value="" />
            <input type="hidden" name="ServerResultURLQueryStringVariables" value="" />
            ';

        if ($this -> boIncludeFormSubmitButton) {
            $return .= '<input type="submit" value="' . $this -> szHTML_ButtonCaption . '" />';
        }

        if ($this -> boIncludeFormTags) {
            $return .= '</form>';
        }
        return $return;
    }

    private function HostedTransactionResult() {

        switch ($this->rdmResultDeliveryMethod) {
            case "POST" :
                $this -> boTransactionProcessed = $this -> validateTransactionResult_POST();

                if ($this -> boTransactionProcessed) {
                    $this -> setResultsAmountAndCurrency($this -> trTransactionResult -> getAmount(), $this -> trTransactionResult -> getCurrencyCode());
                    $this -> ttTransactionType = $this -> trTransactionResult -> getTransactionType();
                    $this -> szOrderID = $this -> trTransactionResult -> getOrderID();
                    $this -> szOrderDescription = $this -> trTransactionResult -> getOrderDescription();
                }

                break;
            case "SERVER" :

                // the results have already been delivered via a server-to-server
                // call from the payment form to the ServerResultURL
                // need to query these transaction results to display
                $this -> boTransactionProcessed = $this -> validateTransactionResult_SERVER();

                break;
            case "SERVER_PULL" :

                // need to query the results from the payment form using the passed
                // cross reference

                $this -> boTransactionProcessed = $this -> validateTransactionResult_SERVER_PULL();
                // the results need to be stored here as this is the first time
                // they will have touched this system
                if ($this -> boTransactionProcessed) {

                    $this -> setResultsAmountAndCurrency($this -> trTransactionResult -> getAmount(), $this -> trTransactionResult -> getCurrencyCode());
                    $this -> ttTransactionType = $this -> trTransactionResult -> getTransactionType();
                    $this -> szOrderID = $this -> trTransactionResult -> getOrderID();
                    $this -> szOrderDescription = $this -> trTransactionResult -> getOrderDescription();

                    if (!$this -> boTransactionProcessed) {
                        // handle the case where the results aren't stored correctly
                    }
                }
                break;
        }

        return $this -> boTransactionProcessed;
    }

    // These functions that are run to deal with storing and retrieving the
    // transaction results. They will be specific to the merchant environment, so cannot
    // be generalised. The developer MUST implement these functions
    // This function needs to be able to retrieve the saved transaction resultt
    // so that the result can be displayed to the customer
    public static function getTransactionResultFromStorage($szCrossReference, $szOrderID, &$trTransactionResult, &$szOutputMessage) {
        $boErrorOccurred = true;
        $szOutputMessage = "Environment specific function getTransactionResultFromStorage() needs to be implemented by merchant developer";
        $trTransactionResult = null;

        $boErrorOccurred != ($trTransactionResult = $_SESSION['tr']);

        return (!$boErrorOccurred);
    }

}

class TransactionResult {

    private $m_nStatusCode;
    private $m_szMessage;
    private $m_nPreviousStatusCode;
    private $m_szPreviousMessage;
    private $m_szCrossReference;
    private $m_nAmount;
    private $m_nCurrencyCode;
    private $m_szOrderID;
    private $m_szTransactionType;
    private $m_szTransactionDateTime;
    private $m_szOrderDescription;
    private $m_szCustomerName;
    private $m_szAddress1;
    private $m_szAddress2;
    private $m_szAddress3;
    private $m_szAddress4;
    private $m_szCity;
    private $m_szState;
    private $m_szPostCode;
    private $m_nCountryCode;
    private $m_bAddressNumericCheckResult;
    private $m_bPostCodeCheckResult;
    private $m_bCV2CheckResult;
    private $m_bThreeDSecureAuthenticationCheckResult;
    private $m_szCardType;
    private $m_szCardClass;
    private $m_szCardIssuer;
    private $m_nCardIssuerCountryCode;
    private $m_szEmailAddress;
    private $m_szPhoneNumber;

    public function getStatusCode() {
        return $this -> m_nStatusCode;
    }

    public function setStatusCode($nStatusCode) {
        $this -> m_nStatusCode = $nStatusCode;
    }

    public function getMessage() {
        return $this -> m_szMessage;
    }

    public function setMessage($szMessage) {
        $this -> m_szMessage = $szMessage;
    }

    public function getPreviousStatusCode() {
        return $this -> m_nPreviousStatusCode;
    }

    public function setPreviousStatusCode($nPreviousStatusCode) {
        $this -> m_nPreviousStatusCode = $nPreviousStatusCode;
    }

    public function getPreviousMessage() {
        return $this -> m_szPreviousMessage;
    }

    public function setPreviousMessage($szPreviousMessage) {
        $this -> m_szPreviousMessage = $szPreviousMessage;
    }

    public function getCrossReference() {
        return $this -> m_szCrossReference;
    }

    public function setCrossReference($szCrossReference) {
        $this -> m_szCrossReference = $szCrossReference;
    }

    public function getAmount() {
        return $this -> m_nAmount;
    }

    public function setAmount($nAmount) {
        $this -> m_nAmount = $nAmount;
    }

    public function getCurrencyCode() {
        return $this -> m_nCurrencyCode;
    }

    public function setCurrencyCode($nCurrencyCode) {
        $this -> m_nCurrencyCode = $nCurrencyCode;
    }

    public function getOrderID() {
        return $this -> m_szOrderID;
    }

    public function setOrderID($szOrderID) {
        $this -> m_szOrderID = $szOrderID;
    }

    public function getTransactionType() {
        return $this -> m_szTransactionType;
    }

    public function setTransactionType($szTransactionType) {
        $this -> m_szTransactionType = $szTransactionType;
    }

    public function getTransactionDateTime() {
        return $this -> m_szTransactionDateTime;
    }

    public function setTransactionDateTime($szTransactionDateTime) {
        $this -> m_szTransactionDateTime = $szTransactionDateTime;
    }

    public function getOrderDescription() {
        return $this -> m_szOrderDescription;
    }

    public function setOrderDescription($szOrderDescription) {
        $this -> m_szOrderDescription = $szOrderDescription;
    }

    public function getCustomerName() {
        return $this -> m_szCustomerName;
    }

    public function setCustomerName($szCustomerName) {
        $this -> m_szCustomerName = $szCustomerName;
    }

    public function getAddress1() {
        return $this -> m_szAddress1;
    }

    public function setAddress1($szAddress1) {
        $this -> m_szAddress1 = $szAddress1;
    }

    public function getAddress2() {
        return $this -> m_szAddress2;
    }

    public function setAddress2($szAddress2) {
        $this -> m_szAddress2 = $szAddress2;
    }

    public function getAddress3() {
        return $this -> m_szAddress3;
    }

    public function setAddress3($szAddress3) {
        $this -> m_szAddress3 = $szAddress3;
    }

    public function getAddress4() {
        return $this -> m_szAddress4;
    }

    public function setAddress4($szAddress4) {
        $this -> m_szAddress4 = $szAddress4;
    }

    public function getCity() {
        return $this -> m_szCity;
    }

    public function setCity($szCity) {
        $this -> m_szCity = $szCity;
    }

    public function getState() {
        return $this -> m_szState;
    }

    public function setState($szState) {
        $this -> m_szState = $szState;
    }

    public function getPostCode() {
        return $this -> m_szPostCode;
    }

    public function setPostCode($szPostCode) {
        $this -> m_szPostCode = $szPostCode;
    }

    public function getCountryCode() {
        return $this -> m_nCountryCode;
    }

    public function setCountryCode($nCountryCode) {
        $this -> m_nCountryCode = $nCountryCode;
    }

    public function setAddressNumericCheckResult($bAddressNumericCheckResult) {
        $this -> m_bAddressNumericCheckResult = $bAddressNumericCheckResult;
    }

    public function getAddressNumericCheckResult() {
        return $this -> m_bAddressNumericCheckResult;
    }

    public function setPostCodeCheckResult($bPostCodeCheckResult) {
        $this -> m_bPostCodeCheckResult = $bPostCodeCheckResult;
    }

    public function getPostCodeCheckResult() {
        return $this -> m_bPostCodeCheckResult;
    }

    public function setCV2CheckResult($bCV2CheckResult) {
        $this -> m_bCV2CheckResult = $bCV2CheckResult;
    }

    public function getCV2CheckResult() {
        return $this -> m_bCV2CheckResult;
    }

    public function setThreeDSecureAuthenticationCheckResult($bThreeDSecureAuthenticationCheckResult) {
        $this -> m_bThreeDSecureAuthenticationCheckResult = $bThreeDSecureAuthenticationCheckResult;
    }

    public function getThreeDSecureAuthenticationCheckResult() {
        return $this -> m_bThreeDSecureAuthenticationCheckResult;
    }

    public function setCardType($szCardType) {
        $this -> m_szCardType = $szCardType;
    }

    public function getCardType() {
        return $this -> m_szCardType;
    }

    public function setCardClass($szCardClass) {
        $this -> m_szCardClass = $szCardClass;
    }

    public function getCardClass() {
        return $this -> m_szCardClass;
    }

    public function setCardIssuer($szCardIssuer) {
        $this -> m_szCardIssuer = $szCardIssuer;
    }

    public function getCardIssuer() {
        return $this -> m_szCardIssuer;
    }

    public function setCardIssuerCountryCode($nCardIssuerCountryCode) {
        $this -> m_nCardIssuerCountryCode = $nCardIssuerCountryCode;
    }

    public function getCardIssuerCountryCode() {
        return $this -> m_nCardIssuerCountryCode;
    }

    public function setEmailAddress($szEmailAddress) {
        $this -> m_szEmailAddress = $szEmailAddress;
    }

    public function getEmailAddress() {
        return $this -> m_szEmailAddress;
    }

    public function setPhoneNumber($szPhoneNumber) {
        $this -> m_szPhoneNumber = $szPhoneNumber;
    }

    public function getPhoneNumber() {
        return $this -> m_szPhoneNumber;
    }

}

class ListItemList {
    private $m_lilListItemList;

    public function getCount() {
        return count($this -> m_lilListItemList);
    }

    public function getAt($nIndex) {
        if ($nIndex < 0 || $nIndex >= count($this -> m_lilListItemList)) {
            throw new Exception('Array index out of bounds');
        }

        return $this -> m_lilListItemList[$nIndex];
    }

    public function add($szName, $szValue, $boIsSelected) {
        $liListItem = new ListItem($szName, $szValue, $boIsSelected);

        $this -> m_lilListItemList[] = $liListItem;
    }

    public function toString() {
        $szReturnString = "";

        for ($nCount = 0; $nCount < count($this -> m_lilListItemList); $nCount++) {
            $liListItem = $this -> m_lilListItemList[$nCount];

            $szReturnString = $szReturnString . "<option";

            if ($liListItem -> getValue() != null && $liListItem -> getValue() != "") {
                $szReturnString = $szReturnString . " value=\"" . $liListItem -> getValue() . "\"";
            }

            if ($liListItem -> getIsSelected() == true) {
                $szReturnString = $szReturnString . " selected=\"selected\"";
            }

            $szReturnString = $szReturnString . ">" . $liListItem -> getName() . "</option>\n";
        }

        return ($szReturnString);
    }

    //constructor
    public function __construct() {
        $this -> m_lilListItemList = array();
    }

}

class ListItem {
    private $m_szName;
    private $m_szValue;
    private $m_boIsSelected;

    //public properties
    public function getName() {
        return $this -> m_szName;
    }

    public function getValue() {
        return $this -> m_szValue;
    }

    public function getIsSelected() {
        return $this -> m_boIsSelected;
    }

    //constructor
    public function __construct($szName, $szValue, $boIsSelected) {
        $this -> m_szName = $szName;
        $this -> m_szValue = $szValue;
        $this -> m_boIsSelected = $boIsSelected;
    }

}

class PaymentFormHelper {
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

    public static function getSiteSecureBaseURL() {
        $szReturnString = "";
        $szPortString = "";
        $szProtocolString = "";

        if ($_SERVER["HTTPS"] == "on") {
            $szProtocolString = "https://";
            if ($_SERVER["SERVER_PORT"] != 443) {
                $szPortString = ":" . $_SERVER["SERVER_PORT"];
            }
        } else {
            $szProtocolString = "http://";
            if ($_SERVER["SERVER_PORT"] != 80) {
                $szPortString = ":" . $_SERVER["SERVER_PORT"];
            }
        }

        $szReturnString = $szProtocolString . $_SERVER["SERVER_NAME"] . $szPortString . $_SERVER["SCRIPT_NAME"];

        $boFinished = false;
        $LoopIndex = strlen($szReturnString) - 1;

        while ($boFinished == false && $LoopIndex >= 0) {
            if ($szReturnString[$LoopIndex] == "/") {
                $boFinished = true;
                $szReturnString = substr($szReturnString, 0, $LoopIndex + 1);
            }
            $LoopIndex--;
        }

        return ($szReturnString);
    }

    public static function getTransactionReferenceFromQueryString($aQueryStringVariables, &$szCrossReference, &$szOrderID, &$szHashDigest, &$szOutputMessage) {
        $szHashDigest = "";
        $szOutputMessage = "";
        $boErrorOccurred = false;

        try {
            // hash digest
            if (isset($aQueryStringVariables["HashDigest"])) {
                $szHashDigest = $aQueryStringVariables["HashDigest"];
            }

            // cross reference of transaction
            if (!isset($aQueryStringVariables["CrossReference"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [CrossReference] not received");
                $boErrorOccurred = true;
            } else {
                $szCrossReference = $aQueryStringVariables["CrossReference"];
            }
            // order ID (same as value passed into payment form - echoed back out by payment form)
            if (!isset($aQueryStringVariables["OrderID"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [OrderID] not received");
                $boErrorOccurred = true;
            } else {
                $szOrderID = $aQueryStringVariables["OrderID"];
            }
        } catch (Exception $e) {
            $boErrorOccurred = true;
            $szOutputMessage = $e -> getMessage();
        }

        return (!$boErrorOccurred);
    }

    public static function getTransactionResultFromPostVariables($aFormVariables, &$trTransactionResult, &$szHashDigest, &$szOutputMessage) {
        $trTransactionResult = null;
        $szHashDigest = "";
        $szOutputMessage = "";
        $boErrorOccurred = false;

        try {
            // hash digest
            if (isset($aFormVariables["HashDigest"])) {
                $szHashDigest = $aFormVariables["HashDigest"];
            }

            // transaction status code
            if (!isset($aFormVariables["StatusCode"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [StatusCode] not received");
                $boErrorOccurred = true;
            } else {
                if ($aFormVariables["StatusCode"] == "") {
                    $nStatusCode = null;
                } else {
                    $nStatusCode = intval($aFormVariables["StatusCode"]);
                }
            }
            // transaction message
            if (!isset($aFormVariables["Message"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [Message] not received");
                $boErrorOccurred = true;
            } else {
                $szMessage = $aFormVariables["Message"];
            }
            // status code of original transaction if this transaction was deemed a duplicate
            if (!isset($aFormVariables["PreviousStatusCode"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [PreviousStatusCode] not received");
                $boErrorOccurred = true;
            } else {
                if ($aFormVariables["PreviousStatusCode"] == "") {
                    $nPreviousStatusCode = null;
                } else {
                    $nPreviousStatusCode = intval($aFormVariables["PreviousStatusCode"]);
                }
            }
            // status code of original transaction if this transaction was deemed a duplicate
            if (!isset($aFormVariables["PreviousMessage"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [PreviousMessage] not received");
                $boErrorOccurred = true;
            } else {
                $szPreviousMessage = $aFormVariables["PreviousMessage"];
            }
            // cross reference of transaction
            if (!isset($aFormVariables["CrossReference"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [CrossReference] not received");
                $boErrorOccurred = true;
            } else {
                $szCrossReference = $aFormVariables["CrossReference"];
            }
            // amount (same as value passed into payment form - echoed back out by payment form)
            if (!isset($aFormVariables["Amount"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [Amount] not received");
                $boErrorOccurred = true;
            } else {
                if ($aFormVariables["Amount"] == null) {
                    $nAmount = null;
                } else {
                    $nAmount = intval($aFormVariables["Amount"]);
                }
            }
            // currency code (same as value passed into payment form - echoed back out by payment form)
            if (!isset($aFormVariables["CurrencyCode"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [CurrencyCode] not received");
                $boErrorOccurred = true;
            } else {
                if ($aFormVariables["CurrencyCode"] == null) {
                    $nCurrencyCode = null;
                } else {
                    $nCurrencyCode = intval($aFormVariables["CurrencyCode"]);
                }
            }
            // order ID (same as value passed into payment form - echoed back out by payment form)
            if (!isset($aFormVariables["OrderID"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [OrderID] not received");
                $boErrorOccurred = true;
            } else {
                $szOrderID = $aFormVariables["OrderID"];
            }
            // transaction type (same as value passed into payment form - echoed back out by payment form)
            if (!isset($aFormVariables["TransactionType"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [TransactionType] not received");
                $boErrorOccurred = true;
            } else {
                $szTransactionType = $aFormVariables["TransactionType"];
            }
            // transaction date/time (same as value passed into payment form - echoed back out by payment form)
            if (!isset($aFormVariables["TransactionDateTime"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [TransactionDateTime] not received");
                $boErrorOccurred = true;
            } else {
                $szTransactionDateTime = $aFormVariables["TransactionDateTime"];
            }
            // order description (same as value passed into payment form - echoed back out by payment form)
            if (!isset($aFormVariables["OrderDescription"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [OrderDescription] not received");
                $boErrorOccurred = true;
            } else {
                $szOrderDescription = $aFormVariables["OrderDescription"];
            }
            // customer name (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($aFormVariables["CustomerName"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [CustomerName] not received");
                $boErrorOccurred = true;
            } else {
                $szCustomerName = $aFormVariables["CustomerName"];
            }
            // address1 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($aFormVariables["Address1"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [Address1] not received");
                $boErrorOccurred = true;
            } else {
                $szAddress1 = $aFormVariables["Address1"];
            }
            // address2 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($aFormVariables["Address2"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [Address2] not received");
                $boErrorOccurred = true;
            } else {
                $szAddress2 = $aFormVariables["Address2"];
            }
            // address3 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($aFormVariables["Address3"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [Address3] not received");
                $boErrorOccurred = true;
            } else {
                $szAddress3 = $aFormVariables["Address3"];
            }
            // address4 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($aFormVariables["Address4"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [Address4] not received");
                $boErrorOccurred = true;
            } else {
                $szAddress4 = $aFormVariables["Address4"];
            }
            // city (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($aFormVariables["City"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [City] not received");
                $boErrorOccurred = true;
            } else {
                $szCity = $aFormVariables["City"];
            }
            // state (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($aFormVariables["State"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [State] not received");
                $boErrorOccurred = true;
            } else {
                $szState = $aFormVariables["State"];
            }
            // post code (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($aFormVariables["PostCode"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [PostCode] not received");
                $boErrorOccurred = true;
            } else {
                $szPostCode = $aFormVariables["PostCode"];
            }
            // country code (not necessarily the same as value passed into payment form - as the customer can change it on the form)
            if (!isset($aFormVariables["CountryCode"])) {
                $szOutputMessage = PaymentFormHelper::addStringToStringList($szOutputMessage, "Expected variable [CountryCode] not received");
                $boErrorOccurred = true;
            } else {
                if ($aFormVariables["CountryCode"] == "") {
                    $nCountryCode = null;
                } else {
                    $nCountryCode = intval($aFormVariables["CountryCode"]);
                }
            }

            if (!$boErrorOccurred) {
                $trTransactionResult = new TransactionResult();
                $trTransactionResult -> setStatusCode($nStatusCode);
                // transaction status code
                $trTransactionResult -> setMessage($szMessage);
                // transaction message
                $trTransactionResult -> setPreviousStatusCode($nPreviousStatusCode);
                // status code of original transaction if duplicate transaction
                $trTransactionResult -> setPreviousMessage($szPreviousMessage);
                // status code of original transaction if duplicate transaction
                $trTransactionResult -> setCrossReference($szCrossReference);
                // cross reference of transaction
                $trTransactionResult -> setAmount($nAmount);
                // amount echoed back
                $trTransactionResult -> setCurrencyCode($nCurrencyCode);
                // currency code echoed back
                $trTransactionResult -> setOrderID($szOrderID);
                // order ID echoed back
                $trTransactionResult -> setTransactionType($szTransactionType);
                // transaction type echoed back
                $trTransactionResult -> setTransactionDateTime($szTransactionDateTime);
                // transaction date/time echoed back
                $trTransactionResult -> setOrderDescription($szOrderDescription);
                // order description echoed back
                // the customer details that were actually
                // processed (might be different
                // from those passed to the payment form)
                $trTransactionResult -> setCustomerName($szCustomerName);
                $trTransactionResult -> setAddress1($szAddress1);
                $trTransactionResult -> setAddress2($szAddress2);
                $trTransactionResult -> setAddress3($szAddress3);
                $trTransactionResult -> setAddress4($szAddress4);
                $trTransactionResult -> setCity($szCity);
                $trTransactionResult -> setState($szState);
                $trTransactionResult -> setPostCode($szPostCode);
                $trTransactionResult -> setCountryCode($nCountryCode);
            }
        } catch (Exception $e) {
            $boErrorOccurred = true;
            $szOutputMessage = $e -> getMessage();
        }

        return (!$boErrorOccurred);
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

    public static function calculateHashDigest($szInputString, $szPreSharedKey, $szHashMethod) {
        switch ($szHashMethod) {
            case "MD5" :
                $hashDigest = md5($szInputString);
                break;
            case "SHA1" :
                $hashDigest = sha1($szInputString);
                break;
            case "HMACMD5" :
                $hashDigest = hash_hmac("md5", $szInputString, $szPreSharedKey);
                break;
            case "HMACSHA1" :
                $hashDigest = hash_hmac("sha1", $szInputString, $szPreSharedKey);
                break;
        }

        return ($hashDigest);
    }

    public static function generateStringToHash($szMerchantID, $szPassword, $szAmount, $szCurrencyCode, $szOrderID, $szTransactionType, $szTransactionDateTime, $szCallbackURL, $szOrderDescription, $szCustomerName, $szAddress1, $szAddress2, $szAddress3, $szAddress4, $szCity, $szState, $szPostCode, $szCountryCode, $szCV2Mandatory, $szAddress1Mandatory, $szCityMandatory, $szPostCodeMandatory, $szStateMandatory, $szCountryMandatory, $szResultDeliveryMethod, $szServerResultURL, $szPaymentFormDisplaysResult, $szPreSharedKey, $szHashMethod) {
        $szReturnString = "";

        switch ($szHashMethod) {
            case "MD5" :
                $boIncludePreSharedKeyInString = true;
                break;
            case "SHA1" :
                $boIncludePreSharedKeyInString = true;
                break;
            case "HMACMD5" :
                $boIncludePreSharedKeyInString = false;
                break;
            case "HMACSHA1" :
                $boIncludePreSharedKeyInString = false;
                break;
        }

        if ($boIncludePreSharedKeyInString) {
            $szReturnString = "PreSharedKey=" . $szPreSharedKey . "&";
        }

        $szReturnString = $szReturnString . "MerchantID=" . $szMerchantID . "&Password=" . $szPassword . "&Amount=" . $szAmount . "&CurrencyCode=" . $szCurrencyCode . "&OrderID=" . $szOrderID . "&TransactionType=" . $szTransactionType . "&TransactionDateTime=" . $szTransactionDateTime . "&CallbackURL=" . $szCallbackURL . "&OrderDescription=" . $szOrderDescription . "&CustomerName=" . $szCustomerName . "&Address1=" . $szAddress1 . "&Address2=" . $szAddress2 . "&Address3=" . $szAddress3 . "&Address4=" . $szAddress4 . "&City=" . $szCity . "&State=" . $szState . "&PostCode=" . $szPostCode . "&CountryCode=" . $szCountryCode . "&CV2Mandatory=" . $szCV2Mandatory . "&Address1Mandatory=" . $szAddress1Mandatory . "&CityMandatory=" . $szCityMandatory . "&PostCodeMandatory=" . $szPostCodeMandatory . "&StateMandatory=" . $szStateMandatory . "&CountryMandatory=" . $szCountryMandatory . "&ResultDeliveryMethod=" . $szResultDeliveryMethod . "&ServerResultURL=" . $szServerResultURL . "&PaymentFormDisplaysResult=" . $szPaymentFormDisplaysResult . "&ServerResultURLCookieVariables=" . "&ServerResultURLFormVariables=" . "&ServerResultURLQueryStringVariables=";

        return ($szReturnString);
    }

    public static function generateStringToHash2($szMerchantID, $szPassword, $trTransactionResult, $szPreSharedKey, $szHashMethod) {
        $szReturnString = "";

        switch ($szHashMethod) {
            case "MD5" :
                $boIncludePreSharedKeyInString = true;
                break;
            case "SHA1" :
                $boIncludePreSharedKeyInString = true;
                break;
            case "HMACMD5" :
                $boIncludePreSharedKeyInString = false;
                break;
            case "HMACSHA1" :
                $boIncludePreSharedKeyInString = false;
                break;
        }

        if ($boIncludePreSharedKeyInString) {
            $szReturnString = "PreSharedKey=" . $szPreSharedKey . "&";
        }

        $szReturnString = $szReturnString . "MerchantID=" . $szMerchantID . "&Password=" . $szPassword . "&StatusCode=" . $trTransactionResult -> getStatusCode() . "&Message=" . $trTransactionResult -> getMessage() . "&PreviousStatusCode=" . $trTransactionResult -> getPreviousStatusCode() . "&PreviousMessage=" . $trTransactionResult -> getPreviousMessage() . "&CrossReference=" . $trTransactionResult -> getCrossReference() . "&Amount=" . $trTransactionResult -> getAmount() . "&CurrencyCode=" . $trTransactionResult -> getCurrencyCode() . "&OrderID=" . $trTransactionResult -> getOrderID() . "&TransactionType=" . $trTransactionResult -> getTransactionType() . "&TransactionDateTime=" . $trTransactionResult -> getTransactionDateTime() . "&OrderDescription=" . $trTransactionResult -> getOrderDescription() . "&CustomerName=" . $trTransactionResult -> getCustomerName() . "&Address1=" . $trTransactionResult -> getAddress1() . "&Address2=" . $trTransactionResult -> getAddress2() . "&Address3=" . $trTransactionResult -> getAddress3() . "&Address4=" . $trTransactionResult -> getAddress4() . "&City=" . $trTransactionResult -> getCity() . "&State=" . $trTransactionResult -> getState() . "&PostCode=" . $trTransactionResult -> getPostCode() . "&CountryCode=" . $trTransactionResult -> getCountryCode();

        return ($szReturnString);
    }

    public static function generateStringToHash3($szMerchantID, $szPassword, $szCrossReference, $szOrderID, $szPreSharedKey, $szHashMethod) {
        $szReturnString = "";

        switch ($szHashMethod) {
            case "MD5" :
                $boIncludePreSharedKeyInString = true;
                break;
            case "SHA1" :
                $boIncludePreSharedKeyInString = true;
                break;
            case "HMACMD5" :
                $boIncludePreSharedKeyInString = false;
                break;
            case "HMACSHA1" :
                $boIncludePreSharedKeyInString = false;
                break;
        }

        if ($boIncludePreSharedKeyInString) {
            $szReturnString = "PreSharedKey=" . $szPreSharedKey . "&";
        }

        $szReturnString = $szReturnString . "MerchantID=" . $szMerchantID . "&Password=" . $szPassword . "&CrossReference=" . $szCrossReference . "&OrderID=" . $szOrderID;

        return ($szReturnString);
    }

    public static function validateTransactionResult_POST($szMerchantID, $szPassword, $szPreSharedKey, $szHashMethod, $aPostVariables, &$trTransactionResult, &$szValidateErrorMessage) {
        $boErrorOccurred = false;

        $szValidateErrorMessage = "";
        $trTransactionResult = null;

        // read the transaction result variables from the post variable list
        if (!PaymentFormHelper::getTransactionResultFromPostVariables($aPostVariables, $trTransactionResult, $szHashDigest, $szOutputMessage)) {
            $boErrorOccurred = true;
            $szValidateErrorMessage = $szOutputMessage;
        } else {
            // now need to validate the hash digest
            $szStringToHash = PaymentFormHelper::generateStringToHash2($szMerchantID, $szPassword, $trTransactionResult, $szPreSharedKey, $szHashMethod);
            $szCalculatedHashDigest = PaymentFormHelper::calculateHashDigest($szStringToHash, $szPreSharedKey, $szHashMethod);

            // does the calculated hash match the one that was passed?
            if (strToUpper($szHashDigest) != strToUpper($szCalculatedHashDigest)) {
                $boErrorOccurred = true;
                $szValidateErrorMessage = "Hash digests don't match - possible variable tampering";
            } else {
                $boErrorOccurred = false;
            }
        }

        return (!$boErrorOccurred);
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

    // These functions that are run to deal with storing and retrieving the
    // transaction results. They will be specific to the merchant environment, so cannot
    // be generalised. The developer MUST implement these functions

    // This function needs to be able to retrieve the saved transaction resultt
    // so that the result can be displayed to the customer
    public static function getTransactionResultFromStorage($szCrossReference, $szOrderID, &$trTransactionResult, &$szOutputMessage) {
        $boErrorOccurred = true;
        $szOutputMessage = "Environment specific function getTransactionResultFromStorage() needs to be implemented by merchant developer";
        $trTransactionResult = null;

        return (!$boErrorOccurred);
    }

    // You should put your code that does any post transaction tasks
    // (e.g. updates the order object, sends the customer an email etc) in this function
    public static function reportTransactionResults($trTransactionResult, &$szOutputMessage) {
        $boErrorOccurred = true;
        $szOutputMessage = "Environment specific function reportTransactionResults() needs to be implemented by merchant developer";

        try {
            switch ($trTransactionResult->getStatusCode()) {
                // transaction authorised
                case 0 :
                    break;
                // card referred (treat as decline)
                case 4 :
                    break;
                // transaction declined
                case 5 :
                    break;
                // duplicate transaction
                case 20 :
                    // need to look at the previous status code to see if the
                    // transaction was successful
                    if ($trTransactionResult -> getPreviousStatusCode() == 0) {
                        // transaction authorised
                    } else {
                        // transaction not authorised
                    }
                    break;
                // error occurred
                case 30 :
                    break;
                default :
                    break;
            }

            // put code to update/store the order with the transaction result
        } catch (Exception $e) {
            $boErrorOccurred = true;
            $szOutputMessage = $e -> getMessage();
        }
        return (!$boErrorOccurred);
    }

}
