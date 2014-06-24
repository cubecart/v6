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
 * CheckoutByAmazon_Service_Model_GetPurchaseContractResult
 * 
 * Properties:
 * <ul>
 * 
 * <li>PurchaseContract: CheckoutByAmazon_Service_Model_PurchaseContract</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_GetPurchaseContractResult extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_GetPurchaseContractResult
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>PurchaseContract: CheckoutByAmazon_Service_Model_PurchaseContract</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'PurchaseContract' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_PurchaseContract'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the PurchaseContract.
     * 
     * @return PurchaseContract PurchaseContract
     */
    public function getPurchaseContract() 
    {
        return $this->_fields['PurchaseContract']['FieldValue'];
    }

    /**
     * Sets the value of the PurchaseContract.
     * 
     * @param PurchaseContract PurchaseContract
     * @return void
     */
    public function setPurchaseContract($value) 
    {
        $this->_fields['PurchaseContract']['FieldValue'] = $value;
        return;
    }


    /**
     * Checks if PurchaseContract  is set
     * 
     * @return bool true if PurchaseContract property is set
     */
    public function isSetPurchaseContract()
    {
        return !is_null($this->_fields['PurchaseContract']['FieldValue']);

    }

}
?>
