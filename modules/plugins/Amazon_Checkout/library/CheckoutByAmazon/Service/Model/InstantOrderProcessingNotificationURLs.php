<?php
/*******************************************************************************
 *  Copyright 2011 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *
 *  You may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at: http://aws.amazon.com/apache2.0
 *  This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 *  CONDITIONS OF ANY KIND, either express or implied. See the License for the
 *  specific language governing permissions and limitations under the License.
 * *****************************************************************************
 */


/**
 *  @see CheckoutByAmazon_Service_Model
 */
require_once ('CheckoutByAmazon/Service/Model.php');  

    

/**
 * CheckoutByAmazon_Service_Model_InstantOrderProcessingNotificationURLs
 * 
 * Properties:
 * <ul>
 * 
 * <li>IntegratorURL: string</li>
 * <li>MerchantURL: string</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_InstantOrderProcessingNotificationURLs extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_InstantOrderProcessingNotificationURLs
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>IntegratorURL: string</li>
     * <li>MerchantURL: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'IntegratorURL' => array('FieldValue' => null, 'FieldType' => 'string'),
        'MerchantURL' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the IntegratorURL property.
     * 
     * @return string IntegratorURL
     */
    public function getIntegratorURL() 
    {
        return $this->_fields['IntegratorURL']['FieldValue'];
    }

    /**
     * Sets the value of the IntegratorURL property.
     * 
     * @param string IntegratorURL
     * @return this instance
     */
    public function setIntegratorURL($value) 
    {
        $this->_fields['IntegratorURL']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if IntegratorURL is set
     * 
     * @return bool true if IntegratorURL  is set
     */
    public function isSetIntegratorURL()
    {
        return !is_null($this->_fields['IntegratorURL']['FieldValue']);
    }

    /**
     * Gets the value of the MerchantURL property.
     * 
     * @return string MerchantURL
     */
    public function getMerchantURL() 
    {
        return $this->_fields['MerchantURL']['FieldValue'];
    }

    /**
     * Sets the value of the MerchantURL property.
     * 
     * @param string MerchantURL
     * @return this instance
     */
    public function setMerchantURL($value) 
    {
        $this->_fields['MerchantURL']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if MerchantURL is set
     * 
     * @return bool true if MerchantURL  is set
     */
    public function isSetMerchantURL()
    {
        return !is_null($this->_fields['MerchantURL']['FieldValue']);
    }
}
?>
