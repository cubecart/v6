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
 * CheckoutByAmazon_Service_Model_Charges
 * 
 * Properties:
 * <ul>
 * 
 * <li>Tax: CheckoutByAmazon_Service_Model_Price</li>
 * <li>Shipping: CheckoutByAmazon_Service_Model_Price</li>
 * <li>GiftWrap: CheckoutByAmazon_Service_Model_Price</li>
 * <li>Promotions: CheckoutByAmazon_Service_Model_PromotionList</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_Charges extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_Charges
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>Tax: CheckoutByAmazon_Service_Model_Price</li>
     * <li>Shipping: CheckoutByAmazon_Service_Model_Price</li>
     * <li>GiftWrap: CheckoutByAmazon_Service_Model_Price</li>
     * <li>Promotions: CheckoutByAmazon_Service_Model_PromotionList</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'Tax' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_Price'),
        'Shipping' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_Price'),
        'GiftWrap' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_Price'),
        'Promotions' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_PromotionList'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the Tax.
     * 
     * @return Price Tax
     */
    public function getTax() 
    {
        return $this->_fields['Tax']['FieldValue'];
    }

    /**
     * Sets the value of the Tax.
     * 
     * @param Price Tax
     * @return void
     */
    public function setTax($value) 
    {
        $this->_fields['Tax']['FieldValue'] = $value;
        return;
    }


    /**
     * Checks if Tax  is set
     * 
     * @return bool true if Tax property is set
     */
    public function isSetTax()
    {
        return !is_null($this->_fields['Tax']['FieldValue']);

    }

    /**
     * Gets the value of the Shipping.
     * 
     * @return Price Shipping
     */
    public function getShipping() 
    {
        return $this->_fields['Shipping']['FieldValue'];
    }

    /**
     * Sets the value of the Shipping.
     * 
     * @param Price Shipping
     * @return void
     */
    public function setShipping($value) 
    {
        $this->_fields['Shipping']['FieldValue'] = $value;
        return;
    }


    /**
     * Checks if Shipping  is set
     * 
     * @return bool true if Shipping property is set
     */
    public function isSetShipping()
    {
        return !is_null($this->_fields['Shipping']['FieldValue']);

    }

    /**
     * Gets the value of the GiftWrap.
     * 
     * @return Price GiftWrap
     */
    public function getGiftWrap() 
    {
        return $this->_fields['GiftWrap']['FieldValue'];
    }

    /**
     * Sets the value of the GiftWrap.
     * 
     * @param Price GiftWrap
     * @return void
     */
    public function setGiftWrap($value) 
    {
        $this->_fields['GiftWrap']['FieldValue'] = $value;
        return;
    }


    /**
     * Checks if GiftWrap  is set
     * 
     * @return bool true if GiftWrap property is set
     */
    public function isSetGiftWrap()
    {
        return !is_null($this->_fields['GiftWrap']['FieldValue']);

    }

    /**
     * Gets the value of the Promotions.
     * 
     * @return PromotionList Promotions
     */
    public function getPromotions() 
    {
        return $this->_fields['Promotions']['FieldValue'];
    }

    /**
     * Sets the value of the Promotions.
     * 
     * @param PromotionList Promotions
     * @return void
     */
    public function setPromotions($value) 
    {
        $this->_fields['Promotions']['FieldValue'] = $value;
        return;
    }


    /**
     * Checks if Promotions  is set
     * 
     * @return bool true if Promotions property is set
     */
    public function isSetPromotions()
    {
        return !is_null($this->_fields['Promotions']['FieldValue']);

    }

}
?>
