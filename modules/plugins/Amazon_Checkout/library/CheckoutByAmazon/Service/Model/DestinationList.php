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
 * CheckoutByAmazon_Service_Model_DestinationList
 * 
 * Properties:
 * <ul>
 * 
 * <li>Destination: CheckoutByAmazon_Service_Model_Destination</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_DestinationList extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_DestinationList
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>Destination: CheckoutByAmazon_Service_Model_Destination</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'Destination' => array('FieldValue' => array(), 'FieldType' => array('CheckoutByAmazon_Service_Model_Destination')),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the Destination.
     * 
     * @return array of Destination Destination
     */
    public function getDestination() 
    {
        return $this->_fields['Destination']['FieldValue'];
    }

    /**
     * Sets the value of the Destination.
     * 
     * @param mixed Destination or an array of Destination Destination
     * @return this instance
     */
    public function setDestination($destination) 
    {
        if (!$this->_isNumericArray($destination)) {
            $destination =  array ($destination);    
        }
        $this->_fields['Destination']['FieldValue'] = $destination;
        return $this;
    }


    /**
     * Checks if Destination list is non-empty
     * 
     * @return bool true if Destination list is non-empty
     */
    public function isSetDestination()
    {
        return count ($this->_fields['Destination']['FieldValue']) > 0;
    }

}
?>
