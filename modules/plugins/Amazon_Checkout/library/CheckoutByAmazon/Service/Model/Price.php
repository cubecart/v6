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
 * CheckoutByAmazon_Service_Model_Price
 * 
 * Properties:
 * <ul>
 * 
 * <li>Amount: NonNegativeDouble</li>
 * <li>CurrencyCode: string</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_Price extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_Price
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>Amount: NonNegativeDouble</li>
     * <li>CurrencyCode: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'Amount' => array('FieldValue' => null, 'FieldType' => 'NonNegativeDouble'),
        'CurrencyCode' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the Amount property.
     * 
     * @return NonNegativeDouble Amount
     */
    public function getAmount() 
    {
        return $this->_fields['Amount']['FieldValue'];
    }

    /**
     * Sets the value of the Amount property.
     * 
     * @param NonNegativeDouble Amount
     * @return this instance
     */
    public function setAmount($value) 
    {
        $this->_fields['Amount']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if Amount is set
     * 
     * @return bool true if Amount  is set
     */
    public function isSetAmount()
    {
        return !is_null($this->_fields['Amount']['FieldValue']);
    }

    /**
     * Gets the value of the CurrencyCode property.
     * 
     * @return string CurrencyCode
     */
    public function getCurrencyCode() 
    {
        return $this->_fields['CurrencyCode']['FieldValue'];
    }

    /**
     * Sets the value of the CurrencyCode property.
     * 
     * @param string CurrencyCode
     * @return this instance
     */
    public function setCurrencyCode($value) 
    {
        $this->_fields['CurrencyCode']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if CurrencyCode is set
     * 
     * @return bool true if CurrencyCode  is set
     */
    public function isSetCurrencyCode()
    {
        return !is_null($this->_fields['CurrencyCode']['FieldValue']);
    }


}
?>
