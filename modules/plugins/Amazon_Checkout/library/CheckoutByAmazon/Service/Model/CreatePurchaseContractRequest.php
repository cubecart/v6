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
 * CheckoutByAmazon_Service_Model_CreatePurchaseContractRequest
 * 
 * Properties:
 * <ul>
 * 
 * <li>PurchaseContractMetadata: byte[]</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_CreatePurchaseContractRequest extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_CreatePurchaseContractRequest
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>PurchaseContractMetadata: byte[]</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'PurchaseContractMetadata' => array('FieldValue' => null, 'FieldType' => 'byte[]'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the PurchaseContractMetadata property.
     * 
     * @return byte[] PurchaseContractMetadata
     */
    public function getPurchaseContractMetadata() 
    {
        return $this->_fields['PurchaseContractMetadata']['FieldValue'];
    }

    /**
     * Sets the value of the PurchaseContractMetadata property.
     * 
     * @param byte[] PurchaseContractMetadata
     * @return this instance
     */
    public function setPurchaseContractMetadata($value) 
    {
        $this->_fields['PurchaseContractMetadata']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if PurchaseContractMetadata is set
     * 
     * @return bool true if PurchaseContractMetadata  is set
     */
    public function isSetPurchaseContractMetadata()
    {
        return !is_null($this->_fields['PurchaseContractMetadata']['FieldValue']);
    }

}
?>
