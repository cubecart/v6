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
 * CheckoutByAmazon_Service_Model_ShippingAddress
 * 
 * Properties:
 * <ul>
 * 
 * <li>Name: string</li>
 * <li>AddressLineOne: string</li>
 * <li>AddressLineTwo: string</li>
 * <li>AddressLineThree: string</li>
 * <li>City: string</li>
 * <li>StateOrProvinceCode: string</li>
 * <li>PostalCode: string</li>
 * <li>CountryCode: string</li>
 * <li>PhoneNumber: string</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_ShippingAddress extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_ShippingAddress
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>Name: string</li>
     * <li>AddressLineOne: string</li>
     * <li>AddressLineTwo: string</li>
     * <li>AddressLineThree: string</li>
     * <li>City: string</li>
     * <li>StateOrProvinceCode: string</li>
     * <li>PostalCode: string</li>
     * <li>CountryCode: string</li>
     * <li>PhoneNumber: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'Name' => array('FieldValue' => null, 'FieldType' => 'string'),
        'AddressLineOne' => array('FieldValue' => null, 'FieldType' => 'string'),
        'AddressLineTwo' => array('FieldValue' => null, 'FieldType' => 'string'),
        'AddressLineThree' => array('FieldValue' => null, 'FieldType' => 'string'),
        'City' => array('FieldValue' => null, 'FieldType' => 'string'),
        'StateOrProvinceCode' => array('FieldValue' => null, 'FieldType' => 'string'),
        'PostalCode' => array('FieldValue' => null, 'FieldType' => 'string'),
        'CountryCode' => array('FieldValue' => null, 'FieldType' => 'string'),
        'PhoneNumber' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the Name property.
     * 
     * @return string Name
     */
    public function getName() 
    {
        return $this->_fields['Name']['FieldValue'];
    }

    /**
     * Sets the value of the Name property.
     * 
     * @param string Name
     * @return this instance
     */
    public function setName($value) 
    {
        $this->_fields['Name']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if Name is set
     * 
     * @return bool true if Name  is set
     */
    public function isSetName()
    {
        return !is_null($this->_fields['Name']['FieldValue']);
    }

    /**
     * Gets the value of the AddressLineOne property.
     * 
     * @return string AddressLineOne
     */
    public function getAddressLineOne() 
    {
        return $this->_fields['AddressLineOne']['FieldValue'];
    }

    /**
     * Sets the value of the AddressLineOne property.
     * 
     * @param string AddressLineOne
     * @return this instance
     */
    public function setAddressLineOne($value) 
    {
        $this->_fields['AddressLineOne']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if AddressLineOne is set
     * 
     * @return bool true if AddressLineOne  is set
     */
    public function isSetAddressLineOne()
    {
        return !is_null($this->_fields['AddressLineOne']['FieldValue']);
    }

    /**
     * Gets the value of the AddressLineTwo property.
     * 
     * @return string AddressLineTwo
     */
    public function getAddressLineTwo() 
    {
        return $this->_fields['AddressLineTwo']['FieldValue'];
    }

    /**
     * Sets the value of the AddressLineTwo property.
     * 
     * @param string AddressLineTwo
     * @return this instance
     */
    public function setAddressLineTwo($value) 
    {
        $this->_fields['AddressLineTwo']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if AddressLineTwo is set
     * 
     * @return bool true if AddressLineTwo  is set
     */
    public function isSetAddressLineTwo()
    {
        return !is_null($this->_fields['AddressLineTwo']['FieldValue']);
    }

    /**
     * Gets the value of the AddressLineThree property.
     * 
     * @return string AddressLineThree
     */
    public function getAddressLineThree() 
    {
        return $this->_fields['AddressLineThree']['FieldValue'];
    }

    /**
     * Sets the value of the AddressLineThree property.
     * 
     * @param string AddressLineThree
     * @return this instance
     */
    public function setAddressLineThree($value) 
    {
        $this->_fields['AddressLineThree']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if AddressLineThree is set
     * 
     * @return bool true if AddressLineThree  is set
     */
    public function isSetAddressLineThree()
    {
        return !is_null($this->_fields['AddressLineThree']['FieldValue']);
    }

    /**
     * Gets the value of the City property.
     * 
     * @return string City
     */
    public function getCity() 
    {
        return $this->_fields['City']['FieldValue'];
    }

    /**
     * Sets the value of the City property.
     * 
     * @param string City
     * @return this instance
     */
    public function setCity($value) 
    {
        $this->_fields['City']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if City is set
     * 
     * @return bool true if City  is set
     */
    public function isSetCity()
    {
        return !is_null($this->_fields['City']['FieldValue']);
    }

    /**
     * Gets the value of the StateOrProvinceCode property.
     * 
     * @return string StateOrProvinceCode
     */
    public function getStateOrProvinceCode() 
    {
        return $this->_fields['StateOrProvinceCode']['FieldValue'];
    }

    /**
     * Sets the value of the StateOrProvinceCode property.
     * 
     * @param string StateOrProvinceCode
     * @return this instance
     */
    public function setStateOrProvinceCode($value) 
    {
        $this->_fields['StateOrProvinceCode']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if StateOrProvinceCode is set
     * 
     * @return bool true if StateOrProvinceCode  is set
     */
    public function isSetStateOrProvinceCode()
    {
        return !is_null($this->_fields['StateOrProvinceCode']['FieldValue']);
    }

    /**
     * Gets the value of the PostalCode property.
     * 
     * @return string PostalCode
     */
    public function getPostalCode() 
    {
        return $this->_fields['PostalCode']['FieldValue'];
    }

    /**
     * Sets the value of the PostalCode property.
     * 
     * @param string PostalCode
     * @return this instance
     */
    public function setPostalCode($value) 
    {
        $this->_fields['PostalCode']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if PostalCode is set
     * 
     * @return bool true if PostalCode  is set
     */
    public function isSetPostalCode()
    {
        return !is_null($this->_fields['PostalCode']['FieldValue']);
    }

    /**
     * Gets the value of the CountryCode property.
     * 
     * @return string CountryCode
     */
    public function getCountryCode() 
    {
        return $this->_fields['CountryCode']['FieldValue'];
    }

    /**
     * Sets the value of the CountryCode property.
     * 
     * @param string CountryCode
     * @return this instance
     */
    public function setCountryCode($value) 
    {
        $this->_fields['CountryCode']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if CountryCode is set
     * 
     * @return bool true if CountryCode  is set
     */
    public function isSetCountryCode()
    {
        return !is_null($this->_fields['CountryCode']['FieldValue']);
    }

    /**
     * Gets the value of the PhoneNumber property.
     * 
     * @return string PhoneNumber
     */
    public function getPhoneNumber() 
    {
        return $this->_fields['PhoneNumber']['FieldValue'];
    }

    /**
     * Sets the value of the PhoneNumber property.
     * 
     * @param string PhoneNumber
     * @return this instance
     */
    public function setPhoneNumber($value) 
    {
        $this->_fields['PhoneNumber']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if PhoneNumber is set
     * 
     * @return bool true if PhoneNumber  is set
     */
    public function isSetPhoneNumber()
    {
        return !is_null($this->_fields['PhoneNumber']['FieldValue']);
    }


}
?>
