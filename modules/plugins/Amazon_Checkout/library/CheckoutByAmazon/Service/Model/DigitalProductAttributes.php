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
 * CheckoutByAmazon_Service_Model_DigitalProductAttributes
 * 
 * Properties:
 * <ul>
 * 
 * <li>dummyDigitalProperty: string</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_DigitalProductAttributes extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_DigitalProductAttributes
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>dummyDigitalProperty: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'dummyDigitalProperty' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the dummyDigitalProperty property.
     * 
     * @return string dummyDigitalProperty
     */
    public function getdummyDigitalProperty() 
    {
        return $this->_fields['dummyDigitalProperty']['FieldValue'];
    }

    /**
     * Sets the value of the dummyDigitalProperty property.
     * 
     * @param string dummyDigitalProperty
     * @return this instance
     */
    public function setdummyDigitalProperty($value) 
    {
        $this->_fields['dummyDigitalProperty']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if dummyDigitalProperty is set
     * 
     * @return bool true if dummyDigitalProperty  is set
     */
    public function isSetdummyDigitalProperty()
    {
        return !is_null($this->_fields['dummyDigitalProperty']['FieldValue']);
    }




}
?>
