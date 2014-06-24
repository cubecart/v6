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
 * CheckoutByAmazon_Service_Model_CompletePurchaseContractRequest
 * 
 * Properties:
 * <ul>
 * 
 * <li>PurchaseContractId: string</li>
 * <li>IntegratorId: string</li>
 * <li>IntegratorName: string</li>
 * <li>InstantOrderProcessingNotificationURLs: CheckoutByAmazon_Service_Model_InstantOrderProcessingNotificationURLs</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_CompletePurchaseContractRequest extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_CompletePurchaseContractRequest
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>PurchaseContractId: string</li>
     * <li>IntegratorId: string</li>
     * <li>IntegratorName: string</li>
     * <li>InstantOrderProcessingNotificationURLs: CheckoutByAmazon_Service_Model_InstantOrderProcessingNotificationURLs</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'PurchaseContractId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'IntegratorId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'IntegratorName' => array('FieldValue' => null, 'FieldType' => 'string'),
        'InstantOrderProcessingNotificationURLs' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_InstantOrderProcessingNotificationURLs'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the PurchaseContractId property.
     * 
     * @return string PurchaseContractId
     */
    public function getPurchaseContractId() 
    {
        return $this->_fields['PurchaseContractId']['FieldValue'];
    }

    /**
     * Sets the value of the PurchaseContractId property.
     * 
     * @param string PurchaseContractId
     * @return this instance
     */
    public function setPurchaseContractId($value) 
    {
        $this->_fields['PurchaseContractId']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if PurchaseContractId is set
     * 
     * @return bool true if PurchaseContractId  is set
     */
    public function isSetPurchaseContractId()
    {
        return !is_null($this->_fields['PurchaseContractId']['FieldValue']);
    }

    /**
     * Gets the value of the IntegratorId property.
     * 
     * @return string IntegratorId
     */
    public function getIntegratorId() 
    {
        return $this->_fields['IntegratorId']['FieldValue'];
    }

    /**
     * Sets the value of the IntegratorId property.
     * 
     * @param string IntegratorId
     * @return this instance
     */
    public function setIntegratorId($value) 
    {
        $this->_fields['IntegratorId']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if IntegratorId is set
     * 
     * @return bool true if IntegratorId  is set
     */
    public function isSetIntegratorId()
    {
        return !is_null($this->_fields['IntegratorId']['FieldValue']);
    }

    /**
     * Gets the value of the IntegratorName property.
     * 
     * @return string IntegratorName
     */
    public function getIntegratorName() 
    {
        return $this->_fields['IntegratorName']['FieldValue'];
    }

    /**
     * Sets the value of the IntegratorName property.
     * 
     * @param string IntegratorName
     * @return this instance
     */
    public function setIntegratorName($value) 
    {
        $this->_fields['IntegratorName']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if IntegratorName is set
     * 
     * @return bool true if IntegratorName  is set
     */
    public function isSetIntegratorName()
    {
        return !is_null($this->_fields['IntegratorName']['FieldValue']);
    }

    /**
     * Gets the value of the InstantOrderProcessingNotificationURLs.
     * 
     * @return InstantOrderProcessingNotificationURLs InstantOrderProcessingNotificationURLs
     */
    public function getInstantOrderProcessingNotificationURLs() 
    {
        return $this->_fields['InstantOrderProcessingNotificationURLs']['FieldValue'];
    }

    /**
     * Sets the value of the InstantOrderProcessingNotificationURLs.
     * 
     * @param InstantOrderProcessingNotificationURLs InstantOrderProcessingNotificationURLs
     * @return void
     */
    public function setInstantOrderProcessingNotificationURLs($value) 
    {
        $this->_fields['InstantOrderProcessingNotificationURLs']['FieldValue'] = $value;
        return;
    }

    /**
     * Checks if InstantOrderProcessingNotificationURLs  is set
     * 
     * @return bool true if InstantOrderProcessingNotificationURLs property is set
     */
    public function isSetInstantOrderProcessingNotificationURLs()
    {
        return !is_null($this->_fields['InstantOrderProcessingNotificationURLs']['FieldValue']);

    }

}
?>
