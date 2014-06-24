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
 * CheckoutByAmazon_Service_Model_PromotionList
 * 
 * Properties:
 * <ul>
 * 
 * <li>Promotion: CheckoutByAmazon_Service_Model_Promotion</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_PromotionList extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_PromotionList
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>Promotion: CheckoutByAmazon_Service_Model_Promotion</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'Promotion' => array('FieldValue' => array(), 'FieldType' => array('CheckoutByAmazon_Service_Model_Promotion')),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the Promotion.
     * 
     * @return array of Promotion Promotion
     */
    public function getPromotion() 
    {
        return $this->_fields['Promotion']['FieldValue'];
    }

    /**
     * Sets the value of the Promotion.
     * 
     * @param mixed Promotion or an array of Promotion Promotion
     * @return this instance
     */
    public function setPromotion($promotion) 
    {
        if (!$this->_isNumericArray($promotion)) {
            $promotion =  array ($promotion);    
        }
        $this->_fields['Promotion']['FieldValue'] = $promotion;
        return $this;
    }


    /**
     * Checks if Promotion list is non-empty
     * 
     * @return bool true if Promotion list is non-empty
     */
    public function isSetPromotion()
    {
        return count ($this->_fields['Promotion']['FieldValue']) > 0;
    }


   /**
    * Adds a Promotion to PromotionList
    * 
    * @param : CheckoutByAmazon_Service_Model_Promotion
    */
    public function addPromotion($promotion)
    {
       $this->setPromotion($promotion);
       return $this;
    }

}
?>
