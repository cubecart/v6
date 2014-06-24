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
 * CheckoutByAmazon_Service_Model_Destination
 * 
 * Properties:
 * <ul>
 * 
 * <li>DestinationName: string</li>
 * <li>DestinationType: DestinationType</li>
 * <li>PhysicalDestinationAttributes: CheckoutByAmazon_Service_Model_PhysicalDestinationAttributes</li>
 * <li>DigitalDestinationAttributes: CheckoutByAmazon_Service_Model_DigitalDestinationAttributes</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_Destination extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_Destination
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>DestinationName: string</li>
     * <li>DestinationType: DestinationType</li>
     * <li>PhysicalDestinationAttributes: CheckoutByAmazon_Service_Model_PhysicalDestinationAttributes</li>
     * <li>DigitalDestinationAttributes: CheckoutByAmazon_Service_Model_DigitalDestinationAttributes</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'DestinationName' => array('FieldValue' => null, 'FieldType' => 'string'),
        'DestinationType' => array('FieldValue' => null, 'FieldType' => 'DestinationType'),
        'PhysicalDestinationAttributes' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_PhysicalDestinationAttributes'),
        'DigitalDestinationAttributes' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_DigitalDestinationAttributes'),
        );
        parent::__construct($data);
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
     * Gets the value of the DestinationType property.
     * 
     * @return DestinationType DestinationType
     */
    public function getDestinationType() 
    {
        return $this->_fields['DestinationType']['FieldValue'];
    }

    /**
     * Sets the value of the DestinationType property.
     * 
     * @param DestinationType DestinationType
     * @return this instance
     */
    public function setDestinationType($value) 
    {
        $this->_fields['DestinationType']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the DestinationType and returns this instance
     * 
     * @param DestinationType $value DestinationType
     * @return CheckoutByAmazon_Service_Model_Destination instance
     */
    public function withDestinationType($value)
    {
        $this->setDestinationType($value);
        return $this;
    }


    /**
     * Checks if DestinationType is set
     * 
     * @return bool true if DestinationType  is set
     */
    public function isSetDestinationType()
    {
        return !is_null($this->_fields['DestinationType']['FieldValue']);
    }

    /**
     * Gets the value of the PhysicalDestinationAttributes.
     * 
     * @return PhysicalDestinationAttributes PhysicalDestinationAttributes
     */
    public function getPhysicalDestinationAttributes() 
    {
        return $this->_fields['PhysicalDestinationAttributes']['FieldValue'];
    }

    /**
     * Sets the value of the PhysicalDestinationAttributes.
     * 
     * @param PhysicalDestinationAttributes PhysicalDestinationAttributes
     * @return void
     */
    public function setPhysicalDestinationAttributes($value) 
    {
        $this->_fields['PhysicalDestinationAttributes']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the PhysicalDestinationAttributes  and returns this instance
     * 
     * @param PhysicalDestinationAttributes $value PhysicalDestinationAttributes
     * @return CheckoutByAmazon_Service_Model_Destination instance
     */
    public function withPhysicalDestinationAttributes($value)
    {
        $this->setPhysicalDestinationAttributes($value);
        return $this;
    }


    /**
     * Checks if PhysicalDestinationAttributes  is set
     * 
     * @return bool true if PhysicalDestinationAttributes property is set
     */
    public function isSetPhysicalDestinationAttributes()
    {
        return !is_null($this->_fields['PhysicalDestinationAttributes']['FieldValue']);

    }

    /**
     * Gets the value of the DigitalDestinationAttributes.
     * 
     * @return DigitalDestinationAttributes DigitalDestinationAttributes
     */
    public function getDigitalDestinationAttributes() 
    {
        return $this->_fields['DigitalDestinationAttributes']['FieldValue'];
    }

    /**
     * Sets the value of the DigitalDestinationAttributes.
     * 
     * @param DigitalDestinationAttributes DigitalDestinationAttributes
     * @return void
     */
    public function setDigitalDestinationAttributes($value) 
    {
        $this->_fields['DigitalDestinationAttributes']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the DigitalDestinationAttributes  and returns this instance
     * 
     * @param DigitalDestinationAttributes $value DigitalDestinationAttributes
     * @return CheckoutByAmazon_Service_Model_Destination instance
     */
    public function withDigitalDestinationAttributes($value)
    {
        $this->setDigitalDestinationAttributes($value);
        return $this;
    }


    /**
     * Checks if DigitalDestinationAttributes  is set
     * 
     * @return bool true if DigitalDestinationAttributes property is set
     */
    public function isSetDigitalDestinationAttributes()
    {
        return !is_null($this->_fields['DigitalDestinationAttributes']['FieldValue']);

    }




}
?>
