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
 * CheckoutByAmazon_Service_Model_PurchaseContract
 * 
 * Properties:
 * <ul>
 * 
 * <li>Id: IdType</li>
 * <li>ExpirationTimeStamp: string</li>
 * <li>MerchantId: IdType</li>
 * <li>MarketplaceId: IdType</li>
 * <li>State: PurchaseContractState</li>
 * <li>Metadata: byte[]</li>
 * <li>Destinations: CheckoutByAmazon_Service_Model_DestinationList</li>
 * <li>PurchaseItems: CheckoutByAmazon_Service_Model_ItemList</li>
 * <li>Charges: CheckoutByAmazon_Service_Model_Charges</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_PurchaseContract extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_PurchaseContract
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>Id: IdType</li>
     * <li>ExpirationTimeStamp: string</li>
     * <li>MerchantId: IdType</li>
     * <li>MarketplaceId: IdType</li>
     * <li>State: PurchaseContractState</li>
     * <li>Metadata: byte[]</li>
     * <li>Destinations: CheckoutByAmazon_Service_Model_DestinationList</li>
     * <li>PurchaseItems: CheckoutByAmazon_Service_Model_ItemList</li>
     * <li>Charges: CheckoutByAmazon_Service_Model_Charges</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'Id' => array('FieldValue' => null, 'FieldType' => 'IdType'),
        'ExpirationTimeStamp' => array('FieldValue' => null, 'FieldType' => 'string'),
        'MerchantId' => array('FieldValue' => null, 'FieldType' => 'IdType'),
        'MarketplaceId' => array('FieldValue' => null, 'FieldType' => 'IdType'),
        'State' => array('FieldValue' => null, 'FieldType' => 'PurchaseContractState'),
        'Metadata' => array('FieldValue' => null, 'FieldType' => 'byte[]'),
        'Destinations' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_DestinationList'),
        'PurchaseItems' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_ItemList'),
        'Charges' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_Charges'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the Id property.
     * 
     * @return IdType Id
     */
    public function getId() 
    {
        return $this->_fields['Id']['FieldValue'];
    }

    /**
     * Sets the value of the Id property.
     * 
     * @param IdType Id
     * @return this instance
     */
    public function setId($value) 
    {
        $this->_fields['Id']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if Id is set
     * 
     * @return bool true if Id  is set
     */
    public function isSetId()
    {
        return !is_null($this->_fields['Id']['FieldValue']);
    }

    /**
     * Gets the value of the ExpirationTimeStamp property.
     * 
     * @return string ExpirationTimeStamp
     */
    public function getExpirationTimeStamp() 
    {
        return $this->_fields['ExpirationTimeStamp']['FieldValue'];
    }

    /**
     * Sets the value of the ExpirationTimeStamp property.
     * 
     * @param string ExpirationTimeStamp
     * @return this instance
     */
    public function setExpirationTimeStamp($value) 
    {
        $this->_fields['ExpirationTimeStamp']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if ExpirationTimeStamp is set
     * 
     * @return bool true if ExpirationTimeStamp  is set
     */
    public function isSetExpirationTimeStamp()
    {
        return !is_null($this->_fields['ExpirationTimeStamp']['FieldValue']);
    }

    /**
     * Gets the value of the MerchantId property.
     * 
     * @return IdType MerchantId
     */
    public function getMerchantId() 
    {
        return $this->_fields['MerchantId']['FieldValue'];
    }

    /**
     * Sets the value of the MerchantId property.
     * 
     * @param IdType MerchantId
     * @return this instance
     */
    public function setMerchantId($value) 
    {
        $this->_fields['MerchantId']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if MerchantId is set
     * 
     * @return bool true if MerchantId  is set
     */
    public function isSetMerchantId()
    {
        return !is_null($this->_fields['MerchantId']['FieldValue']);
    }

    /**
     * Gets the value of the MarketplaceId property.
     * 
     * @return IdType MarketplaceId
     */
    public function getMarketplaceId() 
    {
        return $this->_fields['MarketplaceId']['FieldValue'];
    }

    /**
     * Sets the value of the MarketplaceId property.
     * 
     * @param IdType MarketplaceId
     * @return this instance
     */
    public function setMarketplaceId($value) 
    {
        $this->_fields['MarketplaceId']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if MarketplaceId is set
     * 
     * @return bool true if MarketplaceId  is set
     */
    public function isSetMarketplaceId()
    {
        return !is_null($this->_fields['MarketplaceId']['FieldValue']);
    }

    /**
     * Gets the value of the State property.
     * 
     * @return PurchaseContractState State
     */
    public function getState() 
    {
        return $this->_fields['State']['FieldValue'];
    }

    /**
     * Sets the value of the State property.
     * 
     * @param PurchaseContractState State
     * @return this instance
     */
    public function setState($value) 
    {
        $this->_fields['State']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if State is set
     * 
     * @return bool true if State  is set
     */
    public function isSetState()
    {
        return !is_null($this->_fields['State']['FieldValue']);
    }

    /**
     * Gets the value of the Metadata property.
     * 
     * @return byte[] Metadata
     */
    public function getMetadata() 
    {
        return $this->_fields['Metadata']['FieldValue'];
    }

    /**
     * Sets the value of the Metadata property.
     * 
     * @param byte[] Metadata
     * @return this instance
     */
    public function setMetadata($value) 
    {
        $this->_fields['Metadata']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if Metadata is set
     * 
     * @return bool true if Metadata  is set
     */
    public function isSetMetadata()
    {
        return !is_null($this->_fields['Metadata']['FieldValue']);
    }

    /**
     * Gets the value of the Destinations.
     * 
     * @return DestinationList Destinations
     */
    public function getDestinations() 
    {
        return $this->_fields['Destinations']['FieldValue'];
    }

    /**
     * Sets the value of the Destinations.
     * 
     * @param DestinationList Destinations
     * @return void
     */
    public function setDestinations($value) 
    {
        $this->_fields['Destinations']['FieldValue'] = $value;
        return;
    }

    /**
     * Checks if Destinations  is set
     * 
     * @return bool true if Destinations property is set
     */
    public function isSetDestinations()
    {
        return !is_null($this->_fields['Destinations']['FieldValue']);

    }

    /**
     * Gets the value of the PurchaseItems.
     * 
     * @return ItemList PurchaseItems
     */
    public function getPurchaseItems() 
    {
        return $this->_fields['PurchaseItems']['FieldValue'];
    }

    /**
     * Sets the value of the PurchaseItems.
     * 
     * @param ItemList PurchaseItems
     * @return void
     */
    public function setPurchaseItems($value) 
    {
        $this->_fields['PurchaseItems']['FieldValue'] = $value;
        return;
    }


    /**
     * Checks if PurchaseItems  is set
     * 
     * @return bool true if PurchaseItems property is set
     */
    public function isSetPurchaseItems()
    {
        return !is_null($this->_fields['PurchaseItems']['FieldValue']);

    }

    /**
     * Gets the value of the Charges.
     * 
     * @return Charges Charges
     */
    public function getCharges() 
    {
        return $this->_fields['Charges']['FieldValue'];
    }

    /**
     * Sets the value of the Charges.
     * 
     * @param Charges Charges
     * @return void
     */
    public function setCharges($value) 
    {
        $this->_fields['Charges']['FieldValue'] = $value;
        return;
    }


    /**
     * Checks if Charges  is set
     * 
     * @return bool true if Charges property is set
     */
    public function isSetCharges()
    {
        return !is_null($this->_fields['Charges']['FieldValue']);

    }

}
?>
