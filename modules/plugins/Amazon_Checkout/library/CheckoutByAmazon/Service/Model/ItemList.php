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
 * CheckoutByAmazon_Service_Model_ItemList
 * 
 * Properties:
 * <ul>
 * 
 * <li>PurchaseItem: CheckoutByAmazon_Service_Model_PurchaseItem</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_ItemList extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_ItemList
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>PurchaseItem: CheckoutByAmazon_Service_Model_PurchaseItem</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'PurchaseItem' => array('FieldValue' => array(), 'FieldType' => array('CheckoutByAmazon_Service_Model_PurchaseItem')),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the PurchaseItem.
     * 
     * @return array of PurchaseItem PurchaseItem
     */
    public function getPurchaseItem() 
    {
        return $this->_fields['PurchaseItem']['FieldValue'];
    }

    /**
     * Sets the value of the PurchaseItem.
     * 
     * @param mixed PurchaseItem or an array of PurchaseItem PurchaseItem
     * @return this instance
     */
    public function setPurchaseItem($purchaseItem) 
    {
        if (!$this->_isNumericArray($purchaseItem)) {
            $purchaseItem =  array ($purchaseItem);    
        }
        $this->_fields['PurchaseItem']['FieldValue'] = $purchaseItem;
        return $this;
    }
    
    /**
     * Checks if PurchaseItem list is non-empty
     * 
     * @return bool true if PurchaseItem list is non-empty
     */
    public function isSetPurchaseItem()
    {
        return count ($this->_fields['PurchaseItem']['FieldValue']) > 0;
    }

    //Akhil
    /**
     * Sets the value of the PurchaseItem with merchantItemId.
     * 
     * @param mixed PurchaseItem or an array of PurchaseItem PurchaseItem
     * @return this instance
     */
    public function setPurchaseItemWithMerchantItemId($purchaseItem,$merchantItemID)
    {
         $this->_fields['PurchaseItem']['FieldValue'][$merchantItemID] = $purchaseItem;
    }

     /**
     * Gets the value of the PurchaseItem when merchantItemId is given.
     * 
     * @return array of PurchaseItem PurchaseItem
     */
    public function getpurchaseItemWithMerchantItemId($merchantItemID)
    {
         return $this->_fields['PurchaseItem']['FieldValue'][$merchantItemID];
    }
    //Akhil
     /**
     * Checks if PurchaseItem list is non-empty for merchantItemId
     * 
     * @return bool true if PurchaseItem list is non-empty
     */
    public function isSetPurchaseItemWithMerchantItemId($merchantItemID)
    {
        return count ($this->_fields['PurchaseItem']['FieldValue'][$merchantItemID]) > 0;
    }

    /**
     * Add the item with merchantItemId as index
     * 
     * @param mixed PurchaseItem or an array of PurchaseItem PurchaseItem
     * @return this instance
     */
      public function addItem($purchaseItem)
      {
               $merchantItemID = $purchaseItem->_fields['MerchantItemId']['FieldValue'];       
              $this->_fields['PurchaseItem']['FieldValue'][$merchantItemID] = $purchaseItem;
      }

}
?>
