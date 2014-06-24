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
 * CheckoutByAmazon_Service_Model_OrderIdList
 * 
 * Properties:
 * <ul>
 * 
 * <li>OrderId: string</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_OrderIdList extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_OrderIdList
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>OrderId: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'OrderId' => array('FieldValue' => array(), 'FieldType' => array('string')),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the OrderId .
     * 
     * @return array of string OrderId
     */
    public function getOrderId() 
    {
        return $this->_fields['OrderId']['FieldValue'];
    }

    /**
     * Sets the value of the OrderId.
     * 
     * @param string or an array of string OrderId
     * @return this instance
     */
    public function setOrderId($orderId) 
    {
        if (!$this->_isNumericArray($orderId)) {
            $orderId =  array ($orderId);    
        }
        $this->_fields['OrderId']['FieldValue'] = $orderId;
        return $this;
    }
  

    /**
     * Checks if OrderId list is non-empty
     * 
     * @return bool true if OrderId list is non-empty
     */
    public function isSetOrderId()
    {
        return count ($this->_fields['OrderId']['FieldValue']) > 0;
    }

}
?>
