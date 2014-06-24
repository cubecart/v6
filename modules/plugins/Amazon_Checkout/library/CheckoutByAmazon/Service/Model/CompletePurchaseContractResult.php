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
 * CheckoutByAmazon_Service_Model_CompletePurchaseContractResult
 * 
 * Properties:
 * <ul>
 * 
 * <li>OrderIds: CheckoutByAmazon_Service_Model_OrderIdList</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_CompletePurchaseContractResult extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_CompletePurchaseContractResult
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>OrderIds: CheckoutByAmazon_Service_Model_OrderIdList</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'OrderIds' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_OrderIdList'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the OrderIds.
     * 
     * @return OrderIdList OrderIds
     */
    public function getOrderIds() 
    {
        return $this->_fields['OrderIds']['FieldValue'];
    }

    /**
     * Sets the value of the OrderIds.
     * 
     * @param OrderIdList OrderIds
     * @return void
     */
    public function setOrderIds($value) 
    {
        $this->_fields['OrderIds']['FieldValue'] = $value;
        return;
    }


    /**
     * Checks if OrderIds  is set
     * 
     * @return bool true if OrderIds property is set
     */
    public function isSetOrderIds()
    {
        return !is_null($this->_fields['OrderIds']['FieldValue']);

    }

}
?>
