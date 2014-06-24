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
 * CheckoutByAmazon_Service_Model_SetContractChargesRequest
 * 
 * Properties:
 * <ul>
 * 
 * <li>PurchaseContractId: string</li>
 * <li>Charges: CheckoutByAmazon_Service_Model_Charges</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_SetContractChargesRequest extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_SetContractChargesRequest
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>PurchaseContractId: string</li>
     * <li>Charges: CheckoutByAmazon_Service_Model_Charges</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'PurchaseContractId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'Charges' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_Charges'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the PurchaseContractId property.
     * 
     * @return string PurchaseContractId
     */
    public function getPurchaseContractId() 
    {
        return $this->_fields['PurchaseContractId']['FieldValue'];
    }

    /**
     * Sets the value of the PurchaseContractId property.
     * 
     * @param string PurchaseContractId
     * @return this instance
     */
    public function setPurchaseContractId($value) 
    {
        $this->_fields['PurchaseContractId']['FieldValue'] = $value;
        return $this;
    }



    /**
     * Checks if PurchaseContractId is set
     * 
     * @return bool true if PurchaseContractId  is set
     */
    public function isSetPurchaseContractId()
    {
        return !is_null($this->_fields['PurchaseContractId']['FieldValue']);
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
