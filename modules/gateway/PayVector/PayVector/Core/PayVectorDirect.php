<?php

class PayVectorDirect extends PayVectorBase {

    private $rgeplRequestGatewayEntryPointList;
    private $szGatewayEntryPointToAttemptFirst;
    #
    private $szAddress1Override;
    private $szAddress2Override;
    private $szAddress3Override;
    private $szAddress4Override;
    private $szCityOverride;
    private $szStateOverride;
    private $szPostCodeOverride;

    public function __construct($szIntegrationSource) {

        parent::__construct($szIntegrationSource);

        $this -> imIntegrationMethod = IntegrationMethod::DirectAPI;

        $this -> boEchoCardType = TRUE;
        $this -> boEchoAmountReceived = TRUE;
        $this -> boEchoAVSCheckResult = TRUE;
        $this -> boEchoCV2CheckResult = TRUE;
        $this -> boEchoThreeDSecureCheckResult = TRUE;
    }

    // public function setGatewayEntryPointToAttemptFirst($szGatewayEntryPointToAttemptFirst) {
        // $this -> szGatewayEntryPointToAttemptFirst = $szGatewayEntryPointToAttemptFirst;
    // }

    public function setAddress1Override($szAddress1) {
        $this -> szAddress1Override = $szAddress1;
    }

    public function setAddress2Override($szAddress2) {
        $this -> szAddress2Override = $szAddress2;
    }

    public function setAddress3Override($szAddress3) {
        $this -> szAddress3Override = $szAddress3;
    }

    public function setAddress4Override($szAddress4) {
        $this -> szAddress4Override = $szAddress4;
    }

    public function setCityOverride($szCity) {
        $this -> szCityOverride = $szCity;
    }

    public function setStateOverride($szState) {
        $this -> szStateOverride = $szState;
    }

    public function setPostCodeOverride($szPostCode) {
        $this -> szPostCodeOverride = $szPostCode;
    }

    public function setCountryOverride($szCountry) {
        $this -> szCountryOverride = $szCountry;
    }

    private function InitialiseGatewayEntryPointList() {
	
		$this -> rgeplRequestGatewayEntryPointList = new RequestGatewayEntryPointList();
		
		$result = $GLOBALS['db'] -> misc(PayVectorSQL::selectGEP_EntryPoint());
		
		if(isset($result[0][0]['GatewayEntryPointObject']))
		{
			$geplGatewayEntryPointListXML = $result[0][0]['GatewayEntryPointObject'];
		}
		else
		{
			$geplGatewayEntryPointListXML = null;
		}
		
		if ($geplGatewayEntryPointListXML != null)
		{
			$geplGatewayEntryPointList = GatewayEntryPointList::fromXmlString($geplGatewayEntryPointListXML);

			for ($nCount = 0; $nCount < $geplGatewayEntryPointList->getCount(); $nCount++)
			{
				$geplGatewayEntryPoint = $geplGatewayEntryPointList->getAt($nCount);
				$this -> rgeplRequestGatewayEntryPointList->add($geplGatewayEntryPoint->getEntryPointURL(), $geplGatewayEntryPoint->getMetric(), 1);
			}
		}
		else
		{
			// if we don't have a recent list in the database then just use blind processing
			$this -> rgeplRequestGatewayEntryPointList->add("https://gw1." . $this -> szPaymentProcessorFullDomain, 100, 2);
			$this -> rgeplRequestGatewayEntryPointList->add("https://gw2." . $this -> szPaymentProcessorFullDomain, 200, 2);
			$this -> rgeplRequestGatewayEntryPointList->add("https://gw3." . $this -> szPaymentProcessorFullDomain, 300, 2);
		}
    }

    private function ProcessCardDetailsTransaction() {

        $this -> InitialiseGatewayEntryPointList();

        $this -> toTransactionObject = new CardDetailsTransaction($this -> rgeplRequestGatewayEntryPointList);

        $this -> toTransactionObject -> getMerchantAuthentication() -> setMerchantID($this -> szMerchantID);
        $this -> toTransactionObject -> getMerchantAuthentication() -> setPassword($this -> szPassword);

        $this -> toTransactionObject -> getTransactionDetails() -> getMessageDetails() -> setTransactionType($this -> ttTransactionType);

        $this -> toTransactionObject -> getTransactionDetails() -> getAmount() -> setValue($this -> nAmountUndecimalised);

        $this -> toTransactionObject -> getTransactionDetails() -> getCurrencyCode() -> setValue($this -> iccISOCurrencyCode);

        $this -> toTransactionObject -> getTransactionDetails() -> setOrderID($this -> szOrderID);
        $this -> toTransactionObject -> getTransactionDetails() -> setOrderDescription($this -> szOrderDescription);

        $this -> toTransactionObject -> getTransactionDetails() -> getTransactionControl() -> getEchoCardType() -> setValue($this -> boEchoCardType);
        $this -> toTransactionObject -> getTransactionDetails() -> getTransactionControl() -> getEchoAmountReceived() -> setValue($this -> boEchoAmountReceived);
        $this -> toTransactionObject -> getTransactionDetails() -> getTransactionControl() -> getEchoAVSCheckResult() -> setValue($this -> boEchoAVSCheckResult);
        $this -> toTransactionObject -> getTransactionDetails() -> getTransactionControl() -> getEchoCV2CheckResult() -> setValue($this -> boEchoCV2CheckResult);
        //$this->toTransactionObject->getTransactionDetails()->getTransactionControl()->getEchoThreeDSecureCheckResult()->setValue($this->boEchoThreeDSecureCheckResult);
        //if (!SharedFunctions::isStringNullOrEmpty($this->szAVSOverridePolicy)) {
        $this -> toTransactionObject -> getTransactionDetails() -> getTransactionControl() -> setAVSOverridePolicy($this -> szAVSOverridePolicy);
        //}
        //if (!SharedFunctions::isStringNullOrEmpty($this->szCV2OverridePolicy)) {
        $this -> toTransactionObject -> getTransactionDetails() -> getTransactionControl() -> setCV2OverridePolicy($this -> szCV2OverridePolicy);
        //}
        if (!is_null($this -> boThreeDSecureOverridePolicy)) {
            $this -> toTransactionObject -> getTransactionDetails() -> getTransactionControl() -> getThreeDSecureOverridePolicy() -> setValue($this -> boThreeDSecureOverridePolicy);
        }

        $this -> toTransactionObject -> getTransactionDetails() -> getTransactionControl() -> getDuplicateDelay() -> setValue(60);

        $this -> toTransactionObject -> getTransactionDetails() -> getThreeDSecureBrowserDetails() -> getDeviceCategory() -> setValue($this -> nDeviceCategory);
        $this -> toTransactionObject -> getTransactionDetails() -> getThreeDSecureBrowserDetails() -> setAcceptHeaders($this -> szAcceptHeaders);
        $this -> toTransactionObject -> getTransactionDetails() -> getThreeDSecureBrowserDetails() -> setUserAgent($this -> szUserAgent);

        $this -> toTransactionObject -> getCardDetails() -> setCardName($this -> szCardName);
        $this -> toTransactionObject -> getCardDetails() -> setCardNumber($this -> szCardNumber);

        if ($this -> szCardExpiryDateMonth != "") {
            $this -> toTransactionObject -> getCardDetails() -> getExpiryDate() -> getMonth() -> setValue($this -> szCardExpiryDateMonth);
        }
        if ($this -> szCardExpiryDateYear != "") {
            $this -> toTransactionObject -> getCardDetails() -> getExpiryDate() -> getYear() -> setValue($this -> szCardExpiryDateYear);
        }
        if ($this -> szCardStartDateMonth != "") {
            $this -> toTransactionObject -> getCardDetails() -> getStartDate() -> getMonth() -> setValue($this -> szCardStartDateMonth);
        }
        if ($this -> szCardStartDateYear != "") {
            $this -> toTransactionObject -> getCardDetails() -> getStartDate() -> getYear() -> setValue($this -> szCardStartDateYear);
        }

        $this -> toTransactionObject -> getCardDetails() -> setIssueNumber($this -> szCardIssueNumber);
        $this -> toTransactionObject -> getCardDetails() -> setCV2($this -> szCardCV2);

        $this -> toTransactionObject -> getCustomerDetails() -> getBillingAddress() -> setAddress1($this -> szAddress1);
        $this -> toTransactionObject -> getCustomerDetails() -> getBillingAddress() -> setAddress2($this -> szAddress2);
        $this -> toTransactionObject -> getCustomerDetails() -> getBillingAddress() -> setAddress3($this -> szAddress3);
        $this -> toTransactionObject -> getCustomerDetails() -> getBillingAddress() -> setAddress4($this -> szAddress4);
        $this -> toTransactionObject -> getCustomerDetails() -> getBillingAddress() -> setCity($this -> szCity);
        $this -> toTransactionObject -> getCustomerDetails() -> getBillingAddress() -> setState($this -> szState);
        $this -> toTransactionObject -> getCustomerDetails() -> getBillingAddress() -> setPostCode($this -> szPostCode);

        $this -> toTransactionObject -> getCustomerDetails() -> getBillingAddress() -> getCountryCode() -> setValue($this -> iccISOCountryCode);

        $this -> toTransactionObject -> getCustomerDetails() -> setEmailAddress($this -> szEmailAddress);
        $this -> toTransactionObject -> getCustomerDetails() -> setPhoneNumber($this -> szPhoneNumber);
        $this -> toTransactionObject -> getCustomerDetails() -> setCustomerIPAddress($this -> szCustomerIPAddress);

        $this -> boTransactionProcessed = $this -> toTransactionObject -> processTransaction($this -> trTransactionResult, $this -> todTransactionOutputData);
    }

    private function ProcessCrossReferenceTransaction() {

        $this -> InitialiseGatewayEntryPointList();

        $this -> toTransactionObject = new CrossReferenceTransaction($this -> rgeplRequestGatewayEntryPointList);

        $this -> toTransactionObject -> getMerchantAuthentication() -> setMerchantID($this -> szMerchantID);
        $this -> toTransactionObject -> getMerchantAuthentication() -> setPassword($this -> szPassword);

        $this -> toTransactionObject -> getTransactionDetails() -> getMessageDetails() -> setTransactionType($this -> ttTransactionType);
        $this -> toTransactionObject -> getTransactionDetails() -> getMessageDetails() -> setCrossReference($this -> szOriginCrossReference);

        $this -> toTransactionObject -> getTransactionDetails() -> getAmount() -> setValue($this -> nAmountUndecimalised);

        $this -> toTransactionObject -> getTransactionDetails() -> getCurrencyCode() -> setValue($this -> iccISOCurrencyCode);

        $this -> toTransactionObject -> getTransactionDetails() -> setOrderID($this -> szOrderID);
        $this -> toTransactionObject -> getTransactionDetails() -> setOrderDescription($this -> szOrderDescription);

        $this -> toTransactionObject -> getTransactionDetails() -> getTransactionControl() -> getEchoAmountReceived() -> setValue($this -> boEchoAmountReceived);
        $this -> toTransactionObject -> getTransactionDetails() -> getTransactionControl() -> getEchoAVSCheckResult() -> setValue($this -> boEchoAVSCheckResult);
        $this -> toTransactionObject -> getTransactionDetails() -> getTransactionControl() -> getEchoCV2CheckResult() -> setValue($this -> boEchoCV2CheckResult);

        //if (!SharedFunctions::isStringNullOrEmpty($this->szAVSOverridePolicy)) {
        $this -> toTransactionObject -> getTransactionDetails() -> getTransactionControl() -> setAVSOverridePolicy($this -> szAVSOverridePolicy);
        //}
        //if (!SharedFunctions::isStringNullOrEmpty($this->szCV2OverridePolicy)) {
        $this -> toTransactionObject -> getTransactionDetails() -> getTransactionControl() -> setCV2OverridePolicy($this -> szCV2OverridePolicy);
        //}
        if (!SharedFunctions::isStringNullOrEmpty($this -> boThreeDSecureOverridePolicy)) {
            $this -> toTransactionObject -> getTransactionDetails() -> getTransactionControl() -> getThreeDSecureOverridePolicy() -> setValue($this -> boThreeDSecureOverridePolicy);
        }

        if (!SharedFunctions::isStringNullOrEmpty($this -> szAddress1)) {
            $this -> toTransactionObject -> getCustomerDetails() -> getBillingAddress() -> setAddress1($this -> szAddress1);
        }
        if (!SharedFunctions::isStringNullOrEmpty($this -> szAddress2)) {
            $this -> toTransactionObject -> getCustomerDetails() -> getBillingAddress() -> setAddress2($this -> szAddress2);
        }
        if (!SharedFunctions::isStringNullOrEmpty($this -> szAddress3)) {
            $this -> toTransactionObject -> getCustomerDetails() -> getBillingAddress() -> setAddress3($this -> szAddress3);
        }
        if (!SharedFunctions::isStringNullOrEmpty($this -> szAddress4)) {
            $this -> toTransactionObject -> getCustomerDetails() -> getBillingAddress() -> setAddress4($this -> szAddress4);
        }
        if (!SharedFunctions::isStringNullOrEmpty($this -> szCity)) {
            $this -> toTransactionObject -> getCustomerDetails() -> getBillingAddress() -> setCity($this -> szCity);
        }
        if (!SharedFunctions::isStringNullOrEmpty($this -> szState)) {
            $this -> toTransactionObject -> getCustomerDetails() -> getBillingAddress() -> setState($this -> szState);
        }
        if (!SharedFunctions::isStringNullOrEmpty($this -> szPostCode)) {
            $this -> toTransactionObject -> getCustomerDetails() -> getBillingAddress() -> setPostCode($this -> szPostCode);
        }
        if (!SharedFunctions::isStringNullOrEmpty($this -> iccISOCountryCode)) {
            $this -> toTransactionObject -> getCustomerDetails() -> getBillingAddress() -> getCountryCode() -> setValue($this -> iccISOCountryCode);
        }

        if (($this -> szCardStartDateMonth != "") && ($this -> szCardStartDateMonth != NULL)) {
            $this -> toTransactionObject -> getOverrideCardDetails() -> getStartDate() -> getMonth() -> setValue($this -> szCardStartDateMonth);
        }
        if (($this -> szCardStartDateYear != "") && ($this -> szCardStartDateYear != NULL)) {
            $this -> toTransactionObject -> getOverrideCardDetails() -> getStartDate() -> getYear() -> setValue($this -> szCardStartDateYear);
        }

        if (($this -> szCardExpiryDateMonth != "") && ($this -> szCardExpiryDateMonth != NULL)) {
            $this -> toTransactionObject -> getOverrideCardDetails() -> getExpiryDate() -> getMonth() -> setValue($this -> szCardExpiryDateMonth);
        }
        if (($this -> szCardExpiryDateYear != "") && ($this -> szCardExpiryDateYear != NULL)) {
            $this -> toTransactionObject -> getOverrideCardDetails() -> getExpiryDate() -> getYear() -> setValue($this -> szCardExpiryDateYear);
        }

        if (($this -> szCardCV2 != "") && ($this -> szCardCV2 != NULL)) {
            $this -> toTransactionObject -> getOverrideCardDetails() -> setCV2($this -> szCardCV2);
        }

        // if (($this->szCardStartDateMonthOverride != "") && ($this->szCardStartDateMonthOverride != NULL)) {
        // $this->toTransactionObject->getOverrideCardDetails()->getStartDate()->getMonth()->setValue($this->szCardStartDateMonthOverride);
        // }
        // if (($this->szCardStartDateYearOverride != "") && ($this->szCardStartDateYearOverride != NULL)) {
        // $this->toTransactionObject->getOverrideCardDetails()->getStartDate()->getYear()->setValue($this->szCardStartDateYearOverride);
        // }
        //
        // if (($this->szCardExpiryDateMonthOverride != "") && ($this->szCardExpiryDateMonthOverride != NULL)) {
        // $this->toTransactionObject->getOverrideCardDetails()->getExpiryDate()->getMonth()->setValue($this->szCardExpiryDateMonthOverride);
        // }
        // if (($this->szCardExpiryDateYearOverride != "") && ($this->szCardExpiryDateYearOverride != NULL)) {
        // $this->toTransactionObject->getOverrideCardDetails()->getExpiryDate()->getYear()->setValue($this->szCardExpiryDateYearOverride);
        // }
        //
        // if (($this->szCardCV2Override != "") && ($this->szCardCV2Override != NULL)) {
        // $this->toTransactionObject->getOverrideCardDetails()->setCV2($this->szCardCV2Override);
        // }

        $this -> boTransactionProcessed = $this -> toTransactionObject -> processTransaction($this -> trTransactionResult, $this -> todTransactionOutputData);
    }

    private function ProcessThreeDSecureTransaction() {

        $this -> InitialiseGatewayEntryPointList();

        $this -> toTransactionObject = new ThreeDSecureAuthentication($this -> rgeplRequestGatewayEntryPointList);

        $this -> toTransactionObject -> getMerchantAuthentication() -> setMerchantID($this -> szMerchantID);
        $this -> toTransactionObject -> getMerchantAuthentication() -> setPassword($this -> szPassword);

        $this -> toTransactionObject -> getThreeDSecureInputData() -> setCrossReference($this -> szMD);
        $this -> toTransactionObject -> getThreeDSecureInputData() -> setPaRES($this -> szPaRES);

        $this -> boTransactionProcessed = $this -> toTransactionObject -> processTransaction($this -> trTransactionResult, $this -> todTransactionOutputData);
    }

    private function IsReady() {

        parent::IsReadyBase();

        if ($this -> tmTransactionMethod == TransactionMethod::NONE) {
            array_push($this -> szErrorMessage, Errors::NoTransactionMethodSelected);
        }
        if ($this -> szPaymentProcessorFullDomain == NULL) {
            array_push($this -> szErrorMessage, Errors::NoDomainSpecified);
        }

        if ($this -> nAmount == NULL && $this -> tmTransactionMethod != TransactionMethod::ThreeDSecureTransaction) {
            array_push($this -> szErrorMessage, Errors::NoAmountSpecified);
        }
        if ($this -> iccISOCurrencyCode == NULL && $this -> tmTransactionMethod != TransactionMethod::ThreeDSecureTransaction) {
            array_push($this -> szErrorMessage, Errors::NoCurrencySpecified);
        }
        if ($this -> ttTransactionType == TransactionType::NONE && $this -> tmTransactionMethod != TransactionMethod::ThreeDSecureTransaction) {
            array_push($this -> szErrorMessage, Errors::NoTransactionTypeSelected);
        }

        switch ($this->tmTransactionMethod) {
            case TransactionMethod::CardDetailsTransaction :
                if ($this -> szCardNumber == NULL) {
                    array_push($this -> szErrorMessage, Errors::NoCardNumberSpecified);
                }
                if ($this -> szCardCV2 == NULL) {
                    array_push($this -> szErrorMessage, Errors::NoCardCV2Specified);
                }
                if ($this -> szCardExpiryDateMonth == NULL && $this -> szCardExpiryDateYear == NULL) {
                    array_push($this -> szErrorMessage, Errors::NoCardExpiryDate);
                } elseif ($this -> szCardExpiryDateMonth == NULL || $this -> szCardExpiryDateYear == NULL || $this -> szCardExpiryDateMonth < 1 || $this -> szCardExpiryDateMonth > 12) {
                    array_push($this -> szErrorMessage, Errors::InvalidCardExpiryDate);
                }
                break;
            case TransactionMethod::CrossReferenceTransaction :
                if ($this -> szOriginCrossReference == NULL) {
                    array_push($this -> szErrorMessage, Errors::NoCrossReferenceSpecified);
                }
                break;
            case TransactionMethod::ThreeDSecureTransaction :
                if ($this -> szMD == NULL) {
                    array_push($this -> szErrorMessage, Errors::NoMDSpecified);
                }
                if ($this -> szPaRES == NULL) {
                    array_push($this -> szErrorMessage, Errors::NoPaRESSpecified);
                }
                break;
        }

        $this -> boErrorActive = !(empty($this -> szErrorMessage));

        if ($this -> boErrorActive && $this -> boDebugMode && $this -> szDebugEmail != NULL) {
            mail($this -> szDebugEmail, "FUNC: PayVectorDirect:IsReady()", "ERROR: " . print_r($this -> szErrorMessage, 1));
        }

        return !$this -> boErrorActive;
    }

    public function Process() {

        $return = NULL;

        if ($this -> IsReady()) {

            parent::Process();

            switch ($this->tmTransactionMethod) {
                case TransactionMethod::NONE :
                    break;
                case TransactionMethod::CardDetailsTransaction :
                    $this -> ProcessCardDetailsTransaction();
                    break;
                case TransactionMethod::CrossReferenceTransaction :
                    $this -> ProcessCrossReferenceTransaction();
                    break;
                case TransactionMethod::ThreeDSecureTransaction :
                    $this -> ProcessThreeDSecureTransaction();
                    break;
            }

            if ($return == NULL && !$this -> boTransactionProcessed) {
                $this -> boErrorActive = TRUE;
                array_push($this -> szErrorMessage, Errors::NoCommunicationWithGateway);
            } else {
            	if( isset($this->todTransactionOutputData) && method_exists($this->todTransactionOutputData, "getGatewayEntryPoints") && method_exists($this->todTransactionOutputData->getGatewayEntryPoints(), "toXMLString") )
				{
					$GLOBALS['db'] -> misc(PayVectorSQL::updateGEP_EntryPoint($this->todTransactionOutputData->getGatewayEntryPoints()->toXMLString()));
				}
            }
        } else {
            $return = FALSE;
        }

        return $return;
    }

}
