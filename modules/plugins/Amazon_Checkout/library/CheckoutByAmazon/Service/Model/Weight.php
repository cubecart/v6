<?php
/*******************************************************************************
 *  Copyright 2010 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
 * CheckoutByAmazon_Service_Model_Weight
 * 
 * Properties:
 * <ul>
 * 
 * <li>Value: NonNegativeDouble</li>
 * <li>Unit: WeightUnit</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_Weight extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_Weight
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>Value: NonNegativeDouble</li>
     * <li>Unit: WeightUnit</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'Value' => array('FieldValue' => null, 'FieldType' => 'NonNegativeDouble'),
        'Unit' => array('FieldValue' => null, 'FieldType' => 'WeightUnit'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the Value property.
     * 
     * @return NonNegativeDouble Value
     */
    public function getValue() 
    {
        return $this->_fields['Value']['FieldValue'];
    }

    /**
     * Sets the value of the Value property.
     * 
     * @param NonNegativeDouble Value
     * @return this instance
     */
    public function setValue($value) 
    {
        $this->_fields['Value']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if Value is set
     * 
     * @return bool true if Value  is set
     */
    public function isSetValue()
    {
        return !is_null($this->_fields['Value']['FieldValue']);
    }

    /**
     * Gets the value of the Unit property.
     * 
     * @return WeightUnit Unit
     */
    public function getUnit() 
    {
        return $this->_fields['Unit']['FieldValue'];
    }

    /**
     * Sets the value of the Unit property.
     * 
     * @param WeightUnit Unit
     * @return this instance
     */
    public function setUnit($value) 
    {
        $this->_fields['Unit']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if Unit is set
     * 
     * @return bool true if Unit  is set
     */
    public function isSetUnit()
    {
        return !is_null($this->_fields['Unit']['FieldValue']);
    }

}
?>
