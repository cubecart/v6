<?php
/*******************************************************************************
 *  Copyright 2012 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *
 *  You may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at: http://aws.amazon.com/apache2.0
 *  This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 *  CONDITIONS OF ANY KIND, either express or implied. See the License for the
 *  specific language governing permissions and limitations under the License.
 * *****************************************************************************
 */


class CheckoutByAmazon_Service_Client 
{

    /** @var string */
    private  $_awsAccessKeyId = null;

    /** @var string */
    private  $_awsSecretAccessKey = null;

    /** @var array */
    private  $_config = array ('ServiceURL' => null,
                               'UserAgent' => 'CBA_CheckoutAPI_Inline/1.1 (Language=PHP; ReleaseDate=10_2012)',
                               'SignatureVersion' => 2,
                               'SignatureMethod' => 'HmacSHA256',
                               'ProxyHost' => null,
                               'ProxyPort' => -1,
                               'MaxErrorRetry' => 3
                               );

    /**
     * Construct new Client
     *
     * @param string $awsAccessKeyId AWS Access Key ID
     * @param string $awsSecretAccessKey AWS Secret Access Key
     * @param array $config configuration options.
     * Valid configuration options are:
     * <ul>
     * <li>ServiceURL</li>
     * <li>UserAgent</li>
     * <li>SignatureVersion</li>
     * <li>TimesRetryOnError</li>
     * <li>ProxyHost</li>
     * <li>ProxyPort</li>
     * <li>MaxErrorRetry</li>
     * </ul>
     */
    public function __construct($awsAccessKeyId, $awsSecretAccessKey)
    {
        iconv_set_encoding('output_encoding', 'UTF-8');
        iconv_set_encoding('input_encoding', 'UTF-8');
        iconv_set_encoding('internal_encoding', 'UTF-8');

        $this->_awsAccessKeyId = $awsAccessKeyId;
        $this->_awsSecretAccessKey = $awsSecretAccessKey;
        $config = array ('ServiceURL' =>CheckoutByAmazon_Service_MerchantValues::getInstance()->getCbaServiceURL());
        $this->_config = array_merge($this->_config, $config);
        define( SERVICE_VERSION , CheckoutByAmazon_Service_MerchantValues::getInstance()->getVersion());
    }

    // Public API ------------------------------------------------------------//


        
    /**
     * Create Purchase Contract 
     * @param mixed $request array of parameters for CheckoutByAmazon_Service_Model_CreatePurchaseContractRequest request
     * or CheckoutByAmazon_Service_Model_CreatePurchaseContractRequest object itself
     * @see CheckoutByAmazon_Service_Model_CreatePurchaseContract
     * @return CheckoutByAmazon_Service_Model_CreatePurchaseContractResponse CheckoutByAmazon_Service_Model_CreatePurchaseContractResponse
     *
     * @throws CheckoutByAmazon_Service_Exception
     */
    public function createPurchaseContract($request)
    {
        if (!$request instanceof CheckoutByAmazon_Service_Model_CreatePurchaseContractRequest) {
            require_once ('CheckoutByAmazon/Service/Model/CreatePurchaseContractRequest.php');
            $request = new CheckoutByAmazon_Service_Model_CreatePurchaseContractRequest($request);
        }
        require_once ('CheckoutByAmazon/Service/Model/CreatePurchaseContractResponse.php');
        return CheckoutByAmazon_Service_Model_CreatePurchaseContractResponse::fromXML($this->invoke($this->convertCreatePurchaseContract($request)));
    }


        
    /**
     * Get Purchase Contract 
     * @param mixed $request array of parameters for CheckoutByAmazon_Service_Model_GetPurchaseContractRequest request
     * or CheckoutByAmazon_Service_Model_GetPurchaseContractRequest object itself
     * @see CheckoutByAmazon_Service_Model_GetPurchaseContract
     * @return CheckoutByAmazon_Service_Model_GetPurchaseContractResponse CheckoutByAmazon_Service_Model_GetPurchaseContractResponse
     *
     * @throws CheckoutByAmazon_Service_Exception
     */
    public function getPurchaseContract($request)
    {
        if (!$request instanceof CheckoutByAmazon_Service_Model_GetPurchaseContractRequest) {
            require_once ('CheckoutByAmazon/Service/Model/GetPurchaseContractRequest.php');
            $request = new CheckoutByAmazon_Service_Model_GetPurchaseContractRequest($request);
        }
        require_once ('CheckoutByAmazon/Service/Model/GetPurchaseContractResponse.php');
        return CheckoutByAmazon_Service_Model_GetPurchaseContractResponse::fromXML($this->invoke($this->convertGetPurchaseContract($request)));
    }


        
    /**
     * Set Purchase Items 
     * @param mixed $request array of parameters for CheckoutByAmazon_Service_Model_SetPurchaseItemsRequest request
     * or CheckoutByAmazon_Service_Model_SetPurchaseItemsRequest object itself
     * @see CheckoutByAmazon_Service_Model_SetPurchaseItems
     * @return CheckoutByAmazon_Service_Model_SetPurchaseItemsResponse CheckoutByAmazon_Service_Model_SetPurchaseItemsResponse
     *
     * @throws CheckoutByAmazon_Service_Exception
     */
    public function setPurchaseItems($request)
    {
        if (!$request instanceof CheckoutByAmazon_Service_Model_SetPurchaseItemsRequest) {
            require_once ('CheckoutByAmazon/Service/Model/SetPurchaseItemsRequest.php');
            $request = new CheckoutByAmazon_Service_Model_SetPurchaseItemsRequest($request);
        }
        require_once ('CheckoutByAmazon/Service/Model/SetPurchaseItemsResponse.php');
        return CheckoutByAmazon_Service_Model_SetPurchaseItemsResponse::fromXML($this->invoke($this->convertSetPurchaseItems($request)));
    }


        
    /**
     * Complete Purchase Contract 
     * @param mixed $request array of parameters for CheckoutByAmazon_Service_Model_CompletePurchaseContractRequest request
     * or CheckoutByAmazon_Service_Model_CompletePurchaseContractRequest object itself
     * @see CheckoutByAmazon_Service_Model_CompletePurchaseContract
     * @return CheckoutByAmazon_Service_Model_CompletePurchaseContractResponse CheckoutByAmazon_Service_Model_CompletePurchaseContractResponse
     *
     * @throws CheckoutByAmazon_Service_Exception
     */
    public function completePurchaseContract($request)
    {
        if (!$request instanceof CheckoutByAmazon_Service_Model_CompletePurchaseContractRequest) {
            require_once ('CheckoutByAmazon/Service/Model/CompletePurchaseContractRequest.php');
            $request = new CheckoutByAmazon_Service_Model_CompletePurchaseContractRequest($request);
        }
        require_once ('CheckoutByAmazon/Service/Model/CompletePurchaseContractResponse.php');
        return CheckoutByAmazon_Service_Model_CompletePurchaseContractResponse::fromXML($this->invoke($this->convertCompletePurchaseContract($request)));
    }


        
    /**
     * Set Contract Charges 
     * @param mixed $request array of parameters for CheckoutByAmazon_Service_Model_SetContractChargesRequest request
     * or CheckoutByAmazon_Service_Model_SetContractChargesRequest object itself
     * @see CheckoutByAmazon_Service_Model_SetContractCharges
     * @return CheckoutByAmazon_Service_Model_SetContractChargesResponse CheckoutByAmazon_Service_Model_SetContractChargesResponse
     *
     * @throws CheckoutByAmazon_Service_Exception
     */
    public function setContractCharges($request)
    {
        if (!$request instanceof CheckoutByAmazon_Service_Model_SetContractChargesRequest) {
            require_once ('CheckoutByAmazon/Service/Model/SetContractChargesRequest.php');
            $request = new CheckoutByAmazon_Service_Model_SetContractChargesRequest($request);
        }
        require_once ('CheckoutByAmazon/Service/Model/SetContractChargesResponse.php');
        return CheckoutByAmazon_Service_Model_SetContractChargesResponse::fromXML($this->invoke($this->convertSetContractCharges($request)));
    }

        // Private API ------------------------------------------------------------//

    /**
     * Invoke request and return response
     */
    protected function invoke(array $parameters)
    {
        $actionName = $parameters["Action"];
        $response = array();
        $responseBody = null;
        $statusCode = 200;

        /* Submit the request and read response body */
        try {

            /* Add required request parameters */
            $parameters = $this->addRequiredParameters($parameters);

            $shouldRetry = true;
            $retries = 0;
            do {
                try {
                    $response = $this->httpPost($parameters);
                        if ($response['Status'] === 200) {
                            $shouldRetry = false;
                        } else {
                            if ($response['Status'] === 500 || $response['Status'] === 503) {
                                $shouldRetry = true;
                                $this->pauseOnRetry(++$retries, $response['Status']);
                            } else {
                                throw $this->reportAnyErrors($response['ResponseBody'], $response['Status']);
                            }
                        }
                /* Rethrow on deserializer error */
                } catch (Exception $e) {
                    require_once ('CheckoutByAmazon/Service/Exception.php');
                    if (($e instanceof CheckoutByAmazon_Service_Exception)) {
                        throw $e;
                    } else {
                        require_once ('CheckoutByAmazon/Service/Exception.php');
                        throw new CheckoutByAmazon_Service_Exception(array('Exception' => $e, 'Message' => $e->getMessage()));
                    }
                }

            } while ($shouldRetry);

        } catch (CheckoutByAmazon_Service_Exception $se) {
            throw $se;
        } catch (Exception $t) {
            throw new CheckoutByAmazon_Service_Exception(array('Exception' => $t, 'Message' => $t->getMessage()));
        }

        return $response['ResponseBody'];
    }

    /**
     * Look for additional error strings in the response and return formatted exception
     */
    protected function reportAnyErrors($responseBody, $status, Exception $e =  null)
    {
        $ex = null;
        $xml = new SimpleXMLElement($responseBody);
        $doc = simplexml_load_string($responseBody);
        $message = $doc->Error->Message;
        $requestId = $doc->RequestId;
        $code = $doc->Error->Code;
        $type = $doc->Error->Type;
        if(is_null($type))
        {
            $type = 'Unknown';
        }
          
        if(!is_null($message))
        {
            require_once ('CheckoutByAmazon/Service/Exception.php');
            $ex = new CheckoutByAmazon_Service_Exception(array ('Message' => $message, 'StatusCode' => $status, 'ErrorCode' => $code,
                                                                             'ErrorType' => $type, 'RequestId' => $requestId, 'XML' => $responseBody));
        }
        else
        {
            require_once ('CheckoutByAmazon/Service/Exception.php');
            $ex = new CheckoutByAmazon_Service_Exception(array('Message' => 'Internal Error', 'StatusCode' => $status));

        }   


        return $ex;
    }



    /**
     * Perform HTTP post with exponential retries on error 500 and 503
     *
     */
    protected function httpPost(array $parameters)
    {

	$CBAServiceEndpoint = $this->_config['ServiceURL'];
	$query = http_build_query($parameters, '', '&');
	//initialize CURL
        $curlHandle = curl_init();
		$ca_bundle = 'modules/plugins/Amazon_Checkout/library/CheckoutByAmazon/ca-bundle.crt';
	//compose CURL request
        curl_setopt($curlHandle, CURLOPT_URL, $CBAServiceEndpoint);
	curl_setopt($curlHandle, CURLOPT_USERAGENT, $this->_config['UserAgent']);
        curl_setopt($curlHandle, CURLOPT_POST, true);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $query);
        curl_setopt($curlHandle, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlHandle, CURLOPT_CAINFO, $ca_bundle);
        curl_setopt($curlHandle, CURLOPT_CAPATH, $ca_bundle);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curlHandle, CURLOPT_MAXREDIRS, 0);
        curl_setopt($curlHandle, CURLOPT_HEADER, true);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_NOSIGNAL, true);
	curl_setopt($curlHandle, CURLOPT_HEADER, false);
         // Execute the request
        $responseBody = curl_exec($curlHandle);
        $info = curl_getinfo($curlHandle);
        // to grab the response code only from the Header
        $code = $info["http_code"];

        //close the CURL conection
        curl_close($curlHandle);



        return array ('Status' => (int)$code, 'ResponseBody' => $responseBody);
    }

    /**
     * Exponential sleep on failed request
     * @param retries current retry
     * @throws CheckoutByAmazon_Service_Exception if maximum number of retries has been reached
     */
    protected function pauseOnRetry($retries, $status)
    {
        if ($retries <= $this->_config['MaxErrorRetry']) {
            $delay = (int) (pow(4, $retries) * 100000) ;
            usleep($delay);
        } else {
            require_once ('CheckoutByAmazon/Service/Exception.php');
            throw new CheckoutByAmazon_Service_Exception (array ('Message' => "Maximum number of retry attempts reached :  $retries", 'StatusCode' => $status));
        }
    }

    /**
     * Add authentication related and version parameters
     */
    protected function addRequiredParameters(array $parameters)
    {
        $parameters['AWSAccessKeyId'] = $this->_awsAccessKeyId;
        $parameters['Timestamp'] = $this->getFormattedTimestamp();
        $parameters['Version'] = SERVICE_VERSION;
        $parameters['SignatureVersion'] = $this->_config['SignatureVersion'];
        if ($parameters['SignatureVersion'] > 1) {
            $parameters['SignatureMethod'] = $this->_config['SignatureMethod'];
        }
        $parameters['Signature'] = $this->signParameters($parameters, $this->_awsSecretAccessKey);

        return $parameters;
    }

    /**
     * Convert paremeters to Url encoded query string
     */
    protected function getParametersAsString(array $parameters)
    {
        $queryParameters = array();
        foreach ($parameters as $key => $value) {
            $queryParameters[] = $key . '=' . $this->_urlencode($value);
        }
        return implode('&', $queryParameters);
    }


    /**
     * Computes RFC 2104-compliant HMAC signature for request parameters
     * Implements AWS Signature, as per following spec:
     *
     * If Signature Version is 0, it signs concatenated Action and Timestamp
     *
     * If Signature Version is 1, it performs the following:
     *
     * Sorts all  parameters (including SignatureVersion and excluding Signature,
     * the value of which is being created), ignoring case.
     *
     * Iterate over the sorted list and append the parameter name (in original case)
     * and then its value. It will not URL-encode the parameter values before
     * constructing this string. There are no separators.
     *
     * If Signature Version is 2, string to sign is based on following:
     *
     *    1. The HTTP Request Method followed by an ASCII newline (%0A)
     *    2. The HTTP Host header in the form of lowercase host, followed by an ASCII newline.
     *    3. The URL encoded HTTP absolute path component of the URI
     *       (up to but not including the query string parameters);
     *       if this is empty use a forward '/'. This parameter is followed by an ASCII newline.
     *    4. The concatenation of all query string components (names and values)
     *       as UTF-8 characters which are URL encoded as per RFC 3986
     *       (hex characters MUST be uppercase), sorted using lexicographic byte ordering.
     *       Parameter names are separated from their values by the '=' character
     *       (ASCII character 61), even if the value is empty.
     *       Pairs of parameter and values are separated by the '&' character (ASCII code 38).
     *
     */
    protected function signParameters(array $parameters, $key) {
        $signatureVersion = $parameters['SignatureVersion'];
        $algorithm = "HmacSHA1";
        $stringToSign = null;
        $algorithm = $this->_config['SignatureMethod'];
        $parameters['SignatureMethod'] = $algorithm;
        $stringToSign = $this->calculateStringToSignV2($parameters);

        return $this->sign($stringToSign, $key, $algorithm);
    }


    /**
     * Calculate String to Sign for SignatureVersion 2
     * @param array $parameters request parameters
     * @return String to Sign
     */
    protected function calculateStringToSignV2(array $parameters) {
        $data = 'POST';
        $data .= "\n";
        $endpoint = parse_url ($this->_config['ServiceURL']);
        $data .= $endpoint['host'];
        $data .= "\n";
        $uri = array_key_exists('path', $endpoint) ? $endpoint['path'] : null;
        if (!isset ($uri)) {
        	$uri = "/";
        }
		$uriencoded = implode("/", array_map(array($this, "_urlencode"), explode("/", $uri)));
        $data .= $uriencoded;
        $data .= "\n";
        uksort($parameters, 'strcmp');
        $data .= $this->getParametersAsString($parameters);
        return $data;
    }

    protected function _urlencode($value) {
        return str_replace('%7E', '~', rawurlencode($value));
    }


    /**
     * Computes RFC 2104-compliant HMAC signature.
     */
    protected function sign($data, $key, $algorithm)
    {
        switch($algorithm)
        {
            case 'HmacSHA1' :
                $hash = 'sha1';
                break;
            case 'HmacSHA256' :
                 $hash = 'sha256';
                 break;
            default :
                throw new Exception ("Non-supported signing method specified");
        }
        
        return base64_encode(
            hash_hmac($hash, $data, $key, true));
    }


    /**
     * Formats date as ISO 8601 timestamp
     */
    protected function getFormattedTimestamp()
    {
        return gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
    }


                                                
    /**
     * Convert CreatePurchaseContractRequest to name value pairs
     */
    protected function convertCreatePurchaseContract($request) {
        
        $parameters = array();
        $parameters['Action'] = 'CreatePurchaseContract';
        if ($request->isSetPurchaseContractMetadata()) {
            $parameters['PurchaseContractMetadata'] =  $request->getPurchaseContractMetadata();
        }
        return $parameters;
    }
        
                                                
    /**
     * Convert GetPurchaseContractRequest to name value pairs
     */
    protected function convertGetPurchaseContract($request) {
        
        $parameters = array();
        $parameters['Action'] = 'GetPurchaseContract';
        if ($request->isSetPurchaseContractId()) {
            $parameters['PurchaseContractId'] =  $request->getPurchaseContractId();
        }

        return $parameters;
    }
        
                                                
    /**
     * Convert SetPurchaseItemsRequest to name value pairs
     */
    protected function convertSetPurchaseItems($request) {
        
        $parameters = array();
        $parameters['Action'] = 'SetPurchaseItems';
        if ($request->isSetPurchaseContractId()) {
            $parameters['PurchaseContractId'] =  $request->getPurchaseContractId();
        }
        if ($request->isSetPurchaseItems()) {
            $purchaseItemssetPurchaseItemsRequest = $request->getPurchaseItems();
             $purchaseItempurchaseItemsIndex = 1;
            foreach ($purchaseItemssetPurchaseItemsRequest->getPurchaseItem() as $purchaseItempurchaseItemsIndex1 => $purchaseItempurchaseItems) {
                if ($purchaseItempurchaseItems->isSetMerchantItemId()) {
                    $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.MerchantItemId'] =  $purchaseItempurchaseItems->getMerchantItemId();
                }
                if ($purchaseItempurchaseItems->isSetSKU()) {
                    $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.SKU'] =  $purchaseItempurchaseItems->getSKU();
                }
                if ($purchaseItempurchaseItems->isSetMerchantId()) {
                    $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.MerchantId'] =  $purchaseItempurchaseItems->getMerchantId();
                }
                if ($purchaseItempurchaseItems->isSetTitle()) {
                    $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.Title'] =  $purchaseItempurchaseItems->getTitle();
                }
                if ($purchaseItempurchaseItems->isSetDescription()) {
                    $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.Description'] =  $purchaseItempurchaseItems->getDescription();
                }
                if ($purchaseItempurchaseItems->isSetUnitPrice()) {
                    $UnitPricepurchaseItem = $purchaseItempurchaseItems->getUnitPrice();
                    if ($UnitPricepurchaseItem->isSetAmount()) {
                        $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.UnitPrice.Amount'] =  $UnitPricepurchaseItem->getAmount();
                    }
                    if ($UnitPricepurchaseItem->isSetCurrencyCode()) {
                        $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.UnitPrice.CurrencyCode'] =  $UnitPricepurchaseItem->getCurrencyCode();
                    }
                }
                if ($purchaseItempurchaseItems->isSetQuantity()) {
                    $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.Quantity'] =  $purchaseItempurchaseItems->getQuantity();
                }
                if ($purchaseItempurchaseItems->isSetURL()) {
                    $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.URL'] =  $purchaseItempurchaseItems->getURL();
                }
                if ($purchaseItempurchaseItems->isSetCategory()) {
                    $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.Category'] =  $purchaseItempurchaseItems->getCategory();
                }
                if ($purchaseItempurchaseItems->isSetFulfillmentNetwork()) {
                    $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.FulfillmentNetwork'] =  $purchaseItempurchaseItems->getFulfillmentNetwork();
                }
                if ($purchaseItempurchaseItems->isSetItemCustomData()) {
                    $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.ItemCustomData'] =  $purchaseItempurchaseItems->getItemCustomData();
                }
                if ($purchaseItempurchaseItems->isSetProductType()) {
                    $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.ProductType'] =  $purchaseItempurchaseItems->getProductType();
                }
                if ($purchaseItempurchaseItems->isSetPhysicalProductAttributes()) {
                    $physicalProductAttributespurchaseItem = $purchaseItempurchaseItems->getPhysicalProductAttributes();
                    if ($physicalProductAttributespurchaseItem->isSetWeight()) {
                        $weightphysicalProductAttributes = $physicalProductAttributespurchaseItem->getWeight();
                        if ($weightphysicalProductAttributes->isSetValue()) {
                            $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.PhysicalProductAttributes.Weight.Value'] =  $weightphysicalProductAttributes->getValue();
                        }
                        if ($weightphysicalProductAttributes->isSetUnit()) {
                            $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.PhysicalProductAttributes.Weight.Unit'] =  $weightphysicalProductAttributes->getUnit();
                        }
                    }
                    if ($physicalProductAttributespurchaseItem->isSetCondition()) {
                        $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.PhysicalProductAttributes.Condition'] =  $physicalProductAttributespurchaseItem->getCondition();
                    }
                    if ($physicalProductAttributespurchaseItem->isSetDeliveryMethod()) {
                        $deliveryMethodphysicalProductAttributes = $physicalProductAttributespurchaseItem->getDeliveryMethod();
                        if ($deliveryMethodphysicalProductAttributes->isSetServiceLevel()) {
                            $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.PhysicalProductAttributes.DeliveryMethod.ServiceLevel'] =  $deliveryMethodphysicalProductAttributes->getServiceLevel();
                        }
                        if ($deliveryMethodphysicalProductAttributes->isSetDisplayableShippingLabel()) {
                            $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.PhysicalProductAttributes.DeliveryMethod.DisplayableShippingLabel'] =  $deliveryMethodphysicalProductAttributes->getDisplayableShippingLabel();
                        }
                        if ($deliveryMethodphysicalProductAttributes->isSetDestinationName()) {
                            $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.PhysicalProductAttributes.DeliveryMethod.DestinationName'] =  $deliveryMethodphysicalProductAttributes->getDestinationName();
                        }
                        if ($deliveryMethodphysicalProductAttributes->isSetShippingCustomData()) {
                            $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.PhysicalProductAttributes.DeliveryMethod.ShippingCustomData'] =  $deliveryMethodphysicalProductAttributes->getShippingCustomData();
                        }
                    }
                    if ($physicalProductAttributespurchaseItem->isSetItemCharges()) {
                        $itemChargesPhysicalProductAttributes = $physicalProductAttributespurchaseItem->getItemCharges();
                        if ($itemChargesPhysicalProductAttributes->isSetTax()) {
                            $taxItemCharges = $itemChargesPhysicalProductAttributes->getTax();
                            if ($taxItemCharges->isSetAmount()) {
                                $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.PhysicalProductAttributes.ItemCharges.Tax.Amount'] =  $taxItemCharges->getAmount();
                            }
                            if ($taxItemCharges->isSetCurrencyCode()) {
                                $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.PhysicalProductAttributes.ItemCharges.Tax.CurrencyCode'] =  $taxItemCharges->getCurrencyCode();
                            }
                        }
                        if ($itemChargesPhysicalProductAttributes->isSetShipping()) {
                            $shippingItemCharges = $itemChargesPhysicalProductAttributes->getShipping();
                            if ($shippingItemCharges->isSetAmount()) {
                                $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.PhysicalProductAttributes.ItemCharges.Shipping.Amount'] =  $shippingItemCharges->getAmount();
                            }
                            if ($shippingItemCharges->isSetCurrencyCode()) {
                                $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.PhysicalProductAttributes.ItemCharges.Shipping.CurrencyCode'] =  $shippingItemCharges->getCurrencyCode();
                            }
                        }
                        if ($itemChargesPhysicalProductAttributes->isSetPromotions()) {
                            $promotionsItemCharges = $itemChargesPhysicalProductAttributes->getPromotions();
                            foreach ($promotionsItemCharges->getPromotion() as $promotionPromotionsIndex1 => $promotionPromotions ) {
                                $promotionPromotionsIndex = $promotionPromotionsIndex1 + 1;
                                if ($promotionPromotions->isSetPromotionId()) {
                                    $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.PhysicalProductAttributes.ItemCharges.Promotions.Promotion.'  . ($promotionPromotionsIndex) . '.PromotionId'] =  $promotionPromotions->getPromotionId();
                                }
                                if ($promotionPromotions->isSetDescription()) {
                                    $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.PhysicalProductAttributes.ItemCharges.Promotions.Promotion.'  . ($promotionPromotionsIndex) . '.Description'] =  $promotionPromotions->getDescription();
                                }
                                if ($promotionPromotions->isSetDiscount()) {
                                    $discountPromotion = $promotionPromotions->getDiscount();
                                    if ($discountPromotion->isSetAmount()) {
                                        $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.PhysicalProductAttributes.ItemCharges.Promotions.Promotion.'. ($promotionPromotionsIndex) . '.Discount.Amount'] =  $discountPromotion->getAmount();
                                    }
                                    if ($discountPromotion->isSetCurrencyCode()) {
                                        $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.PhysicalProductAttributes.ItemCharges.Promotions.Promotion.'  . ($promotionPromotionsIndex) . '.Discount.CurrencyCode'] =  $discountPromotion->getCurrencyCode();
                                    }
                                }

                            }
                        }
                    }
                }
                if ($purchaseItempurchaseItems->isSetDigitalProductAttributes()) {
                    $digitalProductAttributespurchaseItem = $purchaseItempurchaseItems->getDigitalProductAttributes();
                    if ($digitalProductAttributespurchaseItem->isSetdummyDigitalProperty()) {
                        $parameters['PurchaseItems.PurchaseItem.'  . ($purchaseItempurchaseItemsIndex) . '.DigitalProductAttributes.dummyDigitalProperty'] =  $digitalProductAttributespurchaseItem->getdummyDigitalProperty();
                    }
                }
                $purchaseItempurchaseItemsIndex++;
            }
        }

        return $parameters;
    }
        
                                        
    /**
     * Convert CompletePurchaseContractRequest to name value pairs
     */
    protected function convertCompletePurchaseContract($request) {
        
        $parameters = array();
        $parameters['Action'] = 'CompletePurchaseContract';
        if ($request->isSetPurchaseContractId()) {
            $parameters['PurchaseContractId'] =  $request->getPurchaseContractId();
        }
        if ($request->isSetIntegratorId()) {
            $parameters['IntegratorId'] =  $request->getIntegratorId();
        }
        if ($request->isSetIntegratorName()) {
            $parameters['IntegratorName'] =  $request->getIntegratorName();
        }
        if ($request->isSetInstantOrderProcessingNotificationURLs()) {
            $instantOrderProcessingNotificationURLscompletePurchaseContractRequest = $request->getInstantOrderProcessingNotificationURLs();
            if ($instantOrderProcessingNotificationURLscompletePurchaseContractRequest->isSetIntegratorURL()) {
                $parameters['InstantOrderProcessingNotificationURLs.IntegratorURL'] =  $instantOrderProcessingNotificationURLscompletePurchaseContractRequest->getIntegratorURL();
            }
            if ($instantOrderProcessingNotificationURLscompletePurchaseContractRequest->isSetMerchantURL()) {
                $parameters['InstantOrderProcessingNotificationURLs.MerchantURL'] =  $instantOrderProcessingNotificationURLscompletePurchaseContractRequest->getMerchantURL();
            }
        }

        return $parameters;
    }
        
                                                
    /**
     * Convert SetContractChargesRequest to name value pairs
     */
    protected function convertSetContractCharges($request) {
        
        $parameters = array();
        $parameters['Action'] = 'SetContractCharges';
        if ($request->isSetPurchaseContractId()) {
            $parameters['PurchaseContractId'] =  $request->getPurchaseContractId();
        }
        if ($request->isSetCharges()) {
            $chargesSetContractChargesRequest = $request->getCharges();
            if ($chargesSetContractChargesRequest->isSetTax()) {
                $taxCharges = $chargesSetContractChargesRequest->getTax();
                if ($taxCharges->isSetAmount()) {
                    $parameters['Charges.Tax.Amount'] =  $taxCharges->getAmount();
                }
                if ($taxCharges->isSetCurrencyCode()) {
                    $parameters['Charges.Tax.CurrencyCode'] =  $taxCharges->getCurrencyCode();
                }
            }
            if ($chargesSetContractChargesRequest->isSetShipping()) {
                $shippingCharges = $chargesSetContractChargesRequest->getShipping();
                if ($shippingCharges->isSetAmount()) {
                    $parameters['Charges.Shipping.Amount'] =  $shippingCharges->getAmount();
                }
                if ($shippingCharges->isSetCurrencyCode()) {
                    $parameters['Charges.Shipping.CurrencyCode'] =  $shippingCharges->getCurrencyCode();
                }
            }
            if ($chargesSetContractChargesRequest->isSetPromotions()) {
                $promotionsCharges = $chargesSetContractChargesRequest->getPromotions();
                $promotionPromotionsIndex = 1;
                foreach ($promotionsCharges->getPromotion() as $promotionPromotionsIndex1 => $promotionPromotions) {
                    if ($promotionPromotions->isSetPromotionId()) {
                        
                        $parameters['Charges.Promotions.Promotion.'  . ($promotionPromotionsIndex) . '.PromotionId'] =  $promotionPromotions->getPromotionId();
                    }
                    if ($promotionPromotions->isSetDescription()) {
                        $parameters['Charges.Promotions.Promotion.' . ($promotionPromotionsIndex) . '.Description'] =  $promotionPromotions->getDescription();
                    }
                    if ($promotionPromotions->isSetDiscount()) {
                        $discountPromotion = $promotionPromotions->getDiscount();
                        if ($discountPromotion->isSetAmount()) {
                            $parameters['Charges.Promotions.Promotion.'  . ($promotionPromotionsIndex) . '.Discount.Amount'] =  $discountPromotion->getAmount();
                        }
                        if ($discountPromotion->isSetCurrencyCode()) {
                            $parameters['Charges.Promotions.Promotion.'  . ($promotionPromotionsIndex) . '.Discount.CurrencyCode'] =  $discountPromotion->getCurrencyCode();
                        }
                    }
                    $promotionPromotionsIndex++;
                }
            }
        }

        return $parameters;
    }
        
                                                                                                                                                                                                                                        
}

?>
