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
 * CheckoutByAmazon_Service_Model_PhysicalDestinationAttributes
 * 
 * Properties:
 * <ul>
 * 
 * <li>ShippingAddress: CheckoutByAmazon_Service_Model_ShippingAddress</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_PhysicalDestinationAttributes extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_PhysicalDestinationAttributes
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>ShippingAddress: CheckoutByAmazon_Service_Model_ShippingAddress</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'ShippingAddress' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_ShippingAddress'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the ShippingAddress.
     * 
     * @return ShippingAddress ShippingAddress
     */
    public function getShippingAddress() 
    {
        return $this->_fields['ShippingAddress']['FieldValue'];
    }

    /**
     * Sets the value of the ShippingAddress.
     * 
     * @param ShippingAddress ShippingAddress
     * @return void
     */
    public function setShippingAddress($value) 
    {
        $this->_fields['ShippingAddress']['FieldValue'] = $value;
        return;
    }

    /**
     * Checks if ShippingAddress  is set
     * 
     * @return bool true if ShippingAddress property is set
     */
    public function isSetShippingAddress()
    {
        return !is_null($this->_fields['ShippingAddress']['FieldValue']);

    }

}
?>
