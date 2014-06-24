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
 * CheckoutByAmazon_Service_Model_DeliveryMethod
 * 
 * Properties:
 * <ul>
 * 
 * <li>ServiceLevel: ShippingServiceLevel</li>
 * <li>DisplayableShippingLabel: string</li>
 * <li>DestinationName: string</li>
 * <li>ShippingCustomData: string</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_DeliveryMethod extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_DeliveryMethod
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>ServiceLevel: ShippingServiceLevel</li>
     * <li>DisplayableShippingLabel: string</li>
     * <li>DestinationName: string</li>
     * <li>ShippingCustomData: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'ServiceLevel' => array('FieldValue' => null, 'FieldType' => 'ShippingServiceLevel'),
        'DisplayableShippingLabel' => array('FieldValue' => null, 'FieldType' => 'string'),
        'DestinationName' => array('FieldValue' => null, 'FieldType' => 'string'),
        'ShippingCustomData' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the ServiceLevel property.
     * 
     * @return ShippingServiceLevel ServiceLevel
     */
    public function getServiceLevel() 
    {
        return $this->_fields['ServiceLevel']['FieldValue'];
    }

    /**
     * Sets the value of the ServiceLevel property.
     * 
     * @param ShippingServiceLevel ServiceLevel
     * @return this instance
     */
    public function setServiceLevel($value) 
    {
        $this->_fields['ServiceLevel']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if ServiceLevel is set
     * 
     * @return bool true if ServiceLevel  is set
     */
    public function isSetServiceLevel()
    {
        return !is_null($this->_fields['ServiceLevel']['FieldValue']);
    }

    /**
     * Gets the value of the DisplayableShippingLabel property.
     * 
     * @return string DisplayableShippingLabel
     */
    public function getDisplayableShippingLabel() 
    {
        return $this->_fields['DisplayableShippingLabel']['FieldValue'];
    }

    /**
     * Sets the value of the DisplayableShippingLabel property.
     * 
     * @param string DisplayableShippingLabel
     * @return this instance
     */
    public function setDisplayableShippingLabel($value) 
    {
        $this->_fields['DisplayableShippingLabel']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if DisplayableShippingLabel is set
     * 
     * @return bool true if DisplayableShippingLabel  is set
     */
    public function isSetDisplayableShippingLabel()
    {
        return !is_null($this->_fields['DisplayableShippingLabel']['FieldValue']);
    }

    /**
     * Gets the value of the DestinationName property.
     * 
     * @return string DestinationName
     */
    public function getDestinationName() 
    {
        return $this->_fields['DestinationName']['FieldValue'];
    }

    /**
     * Sets the value of the DestinationName property.
     * 
     * @param string DestinationName
     * @return this instance
     */
    public function setDestinationName($value) 
    {
        $this->_fields['DestinationName']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if DestinationName is set
     * 
     * @return bool true if DestinationName  is set
     */
    public function isSetDestinationName()
    {
        return !is_null($this->_fields['DestinationName']['FieldValue']);
    }

    /**
     * Gets the value of the ShippingCustomData property.
     * 
     * @return string ShippingCustomData
     */
    public function getShippingCustomData() 
    {
        return $this->_fields['ShippingCustomData']['FieldValue'];
    }

    /**
     * Sets the value of the ShippingCustomData property.
     * 
     * @param string ShippingCustomData
     * @return this instance
     */
    public function setShippingCustomData($value) 
    {
        $this->_fields['ShippingCustomData']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if ShippingCustomData is set
     * 
     * @return bool true if ShippingCustomData  is set
     */
    public function isSetShippingCustomData()
    {
        return !is_null($this->_fields['ShippingCustomData']['FieldValue']);
    }

}
?>
