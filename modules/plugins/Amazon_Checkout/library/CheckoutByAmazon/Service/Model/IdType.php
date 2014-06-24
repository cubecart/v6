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

    
        private   valueField;

/**
 * CheckoutByAmazon_Service_Model_IdType
 * 
 * Properties:
 * <ul>
 * 
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_IdType extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_IdType
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        );
        parent::__construct($data);
    }

    
/**
 * Gets the value of the Value property.
 * 
 * @return  Value
 */
public function getValue() 
{
    return $this->_value;
}

/**
 * Sets the value of the Value property.
 * 
 * @param  Value
 * @return void
 */
public function setValue($value) 
{
    $this->_Value = $value;
    return;
}


/**
 * Checks if Value property is set
 * 
 * @return bool true if Value property is set
 */
public function isSetValue()
{
    return !is_null($this->_value);

}


}
?>
