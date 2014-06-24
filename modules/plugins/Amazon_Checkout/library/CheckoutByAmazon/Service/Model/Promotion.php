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
 * CheckoutByAmazon_Service_Model_Promotion
 * 
 * Properties:
 * <ul>
 * 
 * <li>PromotionId: IdType</li>
 * <li>Description: string</li>
 * <li>Discount: CheckoutByAmazon_Service_Model_Price</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_Promotion extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_Promotion
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>PromotionId: IdType</li>
     * <li>Description: string</li>
     * <li>Discount: CheckoutByAmazon_Service_Model_Price</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'PromotionId' => array('FieldValue' => null, 'FieldType' => 'IdType'),
        'Description' => array('FieldValue' => null, 'FieldType' => 'string'),
        'Discount' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_Price'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the PromotionId property.
     * 
     * @return IdType PromotionId
     */
    public function getPromotionId() 
    {
        return $this->_fields['PromotionId']['FieldValue'];
    }

    /**
     * Sets the value of the PromotionId property.
     * 
     * @param IdType PromotionId
     * @return this instance
     */
    public function setPromotionId($value) 
    {
        $this->_fields['PromotionId']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if PromotionId is set
     * 
     * @return bool true if PromotionId  is set
     */
    public function isSetPromotionId()
    {
        return !is_null($this->_fields['PromotionId']['FieldValue']);
    }

    /**
     * Gets the value of the Description property.
     * 
     * @return string Description
     */
    public function getDescription() 
    {
        return $this->_fields['Description']['FieldValue'];
    }

    /**
     * Sets the value of the Description property.
     * 
     * @param string Description
     * @return this instance
     */
    public function setDescription($value) 
    {
        $this->_fields['Description']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if Description is set
     * 
     * @return bool true if Description  is set
     */
    public function isSetDescription()
    {
        return !is_null($this->_fields['Description']['FieldValue']);
    }

    /**
     * Gets the value of the Discount.
     * 
     * @return Price Discount
     */
    public function getDiscount() 
    {
        return $this->_fields['Discount']['FieldValue'];
    }

    /**
     * Sets the value of the Discount.
     * 
     * @param Price Discount
     * @return void
     */
    public function setDiscount($value) 
    {
        $this->_fields['Discount']['FieldValue'] = $value;
        return;
    }


    /**
     * Checks if Discount  is set
     * 
     * @return bool true if Discount property is set
     */
    public function isSetDiscount()
    {
        return !is_null($this->_fields['Discount']['FieldValue']);

    }

       /*
       * Creates a Promotion  and returns this instance
       * 
       * @param PromotionId,Description,Amount
       * @return CheckoutByAmazon_Service_Model_Promotion instance
       */

    public function createPromotion($promotionId,$description,$amount)
    {
        $discountObject = new  CheckoutByAmazon_Service_Model_Price(
                                                        array('CurrencyCode' => CURRENCY_CODE, 'Amount' => $amount));
        $this->setPromotionId($promotionId);
        $this->setDescription($description);
        $this->setDiscount($discountObject);
        return $this;
    }

}
?>
