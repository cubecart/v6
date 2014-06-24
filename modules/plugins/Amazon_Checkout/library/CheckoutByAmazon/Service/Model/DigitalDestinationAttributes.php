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
 * CheckoutByAmazon_Service_Model_DigitalDestinationAttributes
 * 
 * Properties:
 * <ul>
 * 
 * <li>DummyDigitalDestinationAttribute: string</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_DigitalDestinationAttributes extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_DigitalDestinationAttributes
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>DummyDigitalDestinationAttribute: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'DummyDigitalDestinationAttribute' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the DummyDigitalDestinationAttribute property.
     * 
     * @return string DummyDigitalDestinationAttribute
     */
    public function getDummyDigitalDestinationAttribute() 
    {
        return $this->_fields['DummyDigitalDestinationAttribute']['FieldValue'];
    }

    /**
     * Sets the value of the DummyDigitalDestinationAttribute property.
     * 
     * @param string DummyDigitalDestinationAttribute
     * @return this instance
     */
    public function setDummyDigitalDestinationAttribute($value) 
    {
        $this->_fields['DummyDigitalDestinationAttribute']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if DummyDigitalDestinationAttribute is set
     * 
     * @return bool true if DummyDigitalDestinationAttribute  is set
     */
    public function isSetDummyDigitalDestinationAttribute()
    {
        return !is_null($this->_fields['DummyDigitalDestinationAttribute']['FieldValue']);
    }




}
?>
