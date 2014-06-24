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
 * CheckoutByAmazon_Service_Model_PhysicalProductAttributes
 * 
 * Properties:
 * <ul>
 * 
 * <li>Weight: CheckoutByAmazon_Service_Model_Weight</li>
 * <li>Condition: string</li>
 * <li>GiftOption: string</li>
 * <li>GiftMessage: string</li>
 * <li>DeliveryMethod: CheckoutByAmazon_Service_Model_DeliveryMethod</li>
 * <li>ItemCharges: CheckoutByAmazon_Service_Model_Charges</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_PhysicalProductAttributes extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_PhysicalProductAttributes
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>Weight: CheckoutByAmazon_Service_Model_Weight</li>
     * <li>Condition: string</li>
     * <li>GiftOption: string</li>
     * <li>GiftMessage: string</li>
     * <li>DeliveryMethod: CheckoutByAmazon_Service_Model_DeliveryMethod</li>
     * <li>ItemCharges: CheckoutByAmazon_Service_Model_Charges</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'Weight' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_Weight'),
        'Condition' => array('FieldValue' => null, 'FieldType' => 'string'),
        'GiftOption' => array('FieldValue' => null, 'FieldType' => 'string'),
        'GiftMessage' => array('FieldValue' => null, 'FieldType' => 'string'),
        'DeliveryMethod' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_DeliveryMethod'),
        'ItemCharges' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_Charges'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the Weight.
     * 
     * @return Weight Weight
     */
    public function getWeight() 
    {
        return $this->_fields['Weight']['FieldValue'];
    }

    /**
     * Sets the value of the Weight.
     * 
     * @param Weight Weight
     * @return void
     */
    public function setWeight($value) 
    {
        $this->_fields['Weight']['FieldValue'] = $value;
        return;
    }


    /**
     * Checks if Weight  is set
     * 
     * @return bool true if Weight property is set
     */
    public function isSetWeight()
    {
        return !is_null($this->_fields['Weight']['FieldValue']);

    }

    /**
     * Gets the value of the Condition property.
     * 
     * @return string Condition
     */
    public function getCondition() 
    {
        return $this->_fields['Condition']['FieldValue'];
    }

    /**
     * Sets the value of the Condition property.
     * 
     * @param string Condition
     * @return this instance
     */
    public function setCondition($value) 
    {
        $this->_fields['Condition']['FieldValue'] = $value;
        return $this;
    }



    /**
     * Checks if Condition is set
     * 
     * @return bool true if Condition  is set
     */
    public function isSetCondition()
    {
        return !is_null($this->_fields['Condition']['FieldValue']);
    }

    /**
     * Gets the value of the GiftOption property.
     * 
     * @return string GiftOption
     */
    public function getGiftOption() 
    {
        return $this->_fields['GiftOption']['FieldValue'];
    }

    /**
     * Sets the value of the GiftOption property.
     * 
     * @param string GiftOption
     * @return this instance
     */
    public function setGiftOption($value) 
    {
        $this->_fields['GiftOption']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if GiftOption is set
     * 
     * @return bool true if GiftOption  is set
     */
    public function isSetGiftOption()
    {
        return !is_null($this->_fields['GiftOption']['FieldValue']);
    }

    /**
     * Gets the value of the GiftMessage property.
     * 
     * @return string GiftMessage
     */
    public function getGiftMessage() 
    {
        return $this->_fields['GiftMessage']['FieldValue'];
    }

    /**
     * Sets the value of the GiftMessage property.
     * 
     * @param string GiftMessage
     * @return this instance
     */
    public function setGiftMessage($value) 
    {
        $this->_fields['GiftMessage']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if GiftMessage is set
     * 
     * @return bool true if GiftMessage  is set
     */
    public function isSetGiftMessage()
    {
        return !is_null($this->_fields['GiftMessage']['FieldValue']);
    }

    /**
     * Gets the value of the DeliveryMethod.
     * 
     * @return DeliveryMethod DeliveryMethod
     */
    public function getDeliveryMethod() 
    {
        return $this->_fields['DeliveryMethod']['FieldValue'];
    }

    /**
     * Sets the value of the DeliveryMethod.
     * 
     * @param DeliveryMethod DeliveryMethod
     * @return void
     */
    public function setDeliveryMethod($value) 
    {
        $this->_fields['DeliveryMethod']['FieldValue'] = $value;
        return;
    }

    /**
     * Checks if DeliveryMethod  is set
     * 
     * @return bool true if DeliveryMethod property is set
     */
    public function isSetDeliveryMethod()
    {
        return !is_null($this->_fields['DeliveryMethod']['FieldValue']);

    }

    /**
     * Gets the value of the ItemCharges.
     * 
     * @return Charges ItemCharges
     */
    public function getItemCharges() 
    {
        return $this->_fields['ItemCharges']['FieldValue'];
    }

    /**
     * Sets the value of the ItemCharges.
     * 
     * @param Charges ItemCharges
     * @return void
     */
    public function setItemCharges($value) 
    {
        $this->_fields['ItemCharges']['FieldValue'] = $value;
        return;
    }

    /**
     * Checks if ItemCharges  is set
     * 
     * @return bool true if ItemCharges property is set
     */
    public function isSetItemCharges()
    {
        return !is_null($this->_fields['ItemCharges']['FieldValue']);

    }

}
?>
