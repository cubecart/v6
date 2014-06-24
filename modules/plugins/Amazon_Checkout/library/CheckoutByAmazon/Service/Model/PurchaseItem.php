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
 * CheckoutByAmazon_Service_Model_PurchaseItem
 * 
 * Properties:
 * <ul>
 * 
 * <li>MerchantItemId: IdType</li>
 * <li>SKU: string</li>
 * <li>MerchantId: IdType</li>
 * <li>Title: string</li>
 * <li>Description: string</li>
 * <li>UnitPrice: CheckoutByAmazon_Service_Model_Price</li>
 * <li>Quantity: PositiveInteger</li>
 * <li>URL: string</li>
 * <li>Category: string</li>
 * <li>FulfillmentNetwork: FulfillmentNetwork</li>
 * <li>ItemCustomData: string</li>
 * <li>ProductType: ProductType</li>
 * <li>PhysicalProductAttributes: CheckoutByAmazon_Service_Model_PhysicalProductAttributes</li>
 * <li>DigitalProductAttributes: CheckoutByAmazon_Service_Model_DigitalProductAttributes</li>
 *
 * </ul>
 */ 
class CheckoutByAmazon_Service_Model_PurchaseItem extends CheckoutByAmazon_Service_Model
{


    /**
     * Construct new CheckoutByAmazon_Service_Model_PurchaseItem
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>MerchantItemId: IdType</li>
     * <li>SKU: string</li>
     * <li>MerchantId: IdType</li>
     * <li>Title: string</li>
     * <li>Description: string</li>
     * <li>UnitPrice: CheckoutByAmazon_Service_Model_Price</li>
     * <li>Quantity: PositiveInteger</li>
     * <li>URL: string</li>
     * <li>Category: string</li>
     * <li>FulfillmentNetwork: FulfillmentNetwork</li>
     * <li>ItemCustomData: string</li>
     * <li>ProductType: ProductType</li>
     * <li>PhysicalProductAttributes: CheckoutByAmazon_Service_Model_PhysicalProductAttributes</li>
     * <li>DigitalProductAttributes: CheckoutByAmazon_Service_Model_DigitalProductAttributes</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'MerchantItemId' => array('FieldValue' => null, 'FieldType' => 'IdType'),
        'SKU' => array('FieldValue' => null, 'FieldType' => 'string'),
        'MerchantId' => array('FieldValue' => null, 'FieldType' => 'IdType'),
        'Title' => array('FieldValue' => null, 'FieldType' => 'string'),
        'Description' => array('FieldValue' => null, 'FieldType' => 'string'),
        'UnitPrice' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_Price'),
        'Quantity' => array('FieldValue' => null, 'FieldType' => 'PositiveInteger'),
        'URL' => array('FieldValue' => null, 'FieldType' => 'string'),
        'Category' => array('FieldValue' => null, 'FieldType' => 'string'),
        'FulfillmentNetwork' => array('FieldValue' => null, 'FieldType' => 'FulfillmentNetwork'),
        'ItemCustomData' => array('FieldValue' => null, 'FieldType' => 'string'),
        'ProductType' => array('FieldValue' => null, 'FieldType' => 'ProductType'),
        'PhysicalProductAttributes' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_PhysicalProductAttributes'),
        'DigitalProductAttributes' => array('FieldValue' => null, 'FieldType' => 'CheckoutByAmazon_Service_Model_DigitalProductAttributes'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the MerchantItemId property.
     * 
     * @return IdType MerchantItemId
     */
    public function getMerchantItemId() 
    {
        return $this->_fields['MerchantItemId']['FieldValue'];
    }

    /**
     * Sets the value of the MerchantItemId property.
     * 
     * @param IdType MerchantItemId
     * @return this instance
     */
    public function setMerchantItemId($value) 
    {
        $this->_fields['MerchantItemId']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if MerchantItemId is set
     * 
     * @return bool true if MerchantItemId  is set
     */
    public function isSetMerchantItemId()
    {
        return !is_null($this->_fields['MerchantItemId']['FieldValue']);
    }

    /**
     * Gets the value of the SKU property.
     * 
     * @return string SKU
     */
    public function getSKU() 
    {
        return $this->_fields['SKU']['FieldValue'];
    }

    /**
     * Sets the value of the SKU property.
     * 
     * @param string SKU
     * @return this instance
     */
    public function setSKU($value) 
    {
        $this->_fields['SKU']['FieldValue'] = $value;
        return $this;
    }



    /**
     * Checks if SKU is set
     * 
     * @return bool true if SKU  is set
     */
    public function isSetSKU()
    {
        return !is_null($this->_fields['SKU']['FieldValue']);
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
     * Gets the value of the Title property.
     * 
     * @return string Title
     */
    public function getTitle() 
    {
        return $this->_fields['Title']['FieldValue'];
    }

    /**
     * Sets the value of the Title property.
     * 
     * @param string Title
     * @return this instance
     */
    public function setTitle($value) 
    {
        $this->_fields['Title']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if Title is set
     * 
     * @return bool true if Title  is set
     */
    public function isSetTitle()
    {
        return !is_null($this->_fields['Title']['FieldValue']);
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
     * Gets the value of the UnitPrice.
     * 
     * @return Price UnitPrice
     */
    public function getUnitPrice() 
    {
        return $this->_fields['UnitPrice']['FieldValue'];
    }

    /**
     * Sets the value of the UnitPrice.
     * 
     * @param Price UnitPrice
     * @return void
     */
    public function setUnitPrice($value) 
    {
        $this->_fields['UnitPrice']['FieldValue'] = $value;
        return;
    }


    /**
     * Checks if UnitPrice  is set
     * 
     * @return bool true if UnitPrice property is set
     */
    public function isSetUnitPrice()
    {
        return !is_null($this->_fields['UnitPrice']['FieldValue']);

    }

    /**
     * Gets the value of the Quantity property.
     * 
     * @return PositiveInteger Quantity
     */
    public function getQuantity() 
    {
        return $this->_fields['Quantity']['FieldValue'];
    }

    /**
     * Sets the value of the Quantity property.
     * 
     * @param PositiveInteger Quantity
     * @return this instance
     */
    public function setQuantity($value) 
    {
        $this->_fields['Quantity']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if Quantity is set
     * 
     * @return bool true if Quantity  is set
     */
    public function isSetQuantity()
    {
        return !is_null($this->_fields['Quantity']['FieldValue']);
    }

    /**
     * Gets the value of the URL property.
     * 
     * @return string URL
     */
    public function getURL() 
    {
        return $this->_fields['URL']['FieldValue'];
    }

    /**
     * Sets the value of the URL property.
     * 
     * @param string URL
     * @return this instance
     */
    public function setURL($value) 
    {
        $this->_fields['URL']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if URL is set
     * 
     * @return bool true if URL  is set
     */
    public function isSetURL()
    {
        return !is_null($this->_fields['URL']['FieldValue']);
    }

    /**
     * Gets the value of the Category property.
     * 
     * @return string Category
     */
    public function getCategory() 
    {
        return $this->_fields['Category']['FieldValue'];
    }

    /**
     * Sets the value of the Category property.
     * 
     * @param string Category
     * @return this instance
     */
    public function setCategory($value) 
    {
        $this->_fields['Category']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if Category is set
     * 
     * @return bool true if Category  is set
     */
    public function isSetCategory()
    {
        return !is_null($this->_fields['Category']['FieldValue']);
    }

    /**
     * Gets the value of the FulfillmentNetwork property.
     * 
     * @return FulfillmentNetwork FulfillmentNetwork
     */
    public function getFulfillmentNetwork() 
    {
        return $this->_fields['FulfillmentNetwork']['FieldValue'];
    }

    /**
     * Sets the value of the FulfillmentNetwork property.
     * 
     * @param FulfillmentNetwork FulfillmentNetwork
     * @return this instance
     */
    public function setFulfillmentNetwork($value) 
    {
        $this->_fields['FulfillmentNetwork']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if FulfillmentNetwork is set
     * 
     * @return bool true if FulfillmentNetwork  is set
     */
    public function isSetFulfillmentNetwork()
    {
        return !is_null($this->_fields['FulfillmentNetwork']['FieldValue']);
    }

    /**
     * Gets the value of the ItemCustomData property.
     * 
     * @return string ItemCustomData
     */
    public function getItemCustomData() 
    {
        return $this->_fields['ItemCustomData']['FieldValue'];
    }

    /**
     * Sets the value of the ItemCustomData property.
     * 
     * @param string ItemCustomData
     * @return this instance
     */
    public function setItemCustomData($value) 
    {
        $this->_fields['ItemCustomData']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if ItemCustomData is set
     * 
     * @return bool true if ItemCustomData  is set
     */
    public function isSetItemCustomData()
    {
        return !is_null($this->_fields['ItemCustomData']['FieldValue']);
    }

    /**
     * Gets the value of the ProductType property.
     * 
     * @return ProductType ProductType
     */
    public function getProductType() 
    {
        return $this->_fields['ProductType']['FieldValue'];
    }

    /**
     * Sets the value of the ProductType property.
     * 
     * @param ProductType ProductType
     * @return this instance
     */
    public function setProductType($value) 
    {
        $this->_fields['ProductType']['FieldValue'] = $value;
        return $this;
    }


    /**
     * Checks if ProductType is set
     * 
     * @return bool true if ProductType  is set
     */
    public function isSetProductType()
    {
        return !is_null($this->_fields['ProductType']['FieldValue']);
    }

    /**
     * Gets the value of the PhysicalProductAttributes.
     * 
     * @return PhysicalProductAttributes PhysicalProductAttributes
     */
    public function getPhysicalProductAttributes() 
    {
        return $this->_fields['PhysicalProductAttributes']['FieldValue'];
    }

    /**
     * Sets the value of the PhysicalProductAttributes.
     * 
     * @param PhysicalProductAttributes PhysicalProductAttributes
     * @return void
     */
    public function setPhysicalProductAttributes($value) 
    {
        $this->_fields['PhysicalProductAttributes']['FieldValue'] = $value;
        return;
    }


    /**
     * Checks if PhysicalProductAttributes  is set
     * 
     * @return bool true if PhysicalProductAttributes property is set
     */
    public function isSetPhysicalProductAttributes()
    {
        return !is_null($this->_fields['PhysicalProductAttributes']['FieldValue']);

    }

    /**
     * Gets the value of the DigitalProductAttributes.
     * 
     * @return DigitalProductAttributes DigitalProductAttributes
     */
    public function getDigitalProductAttributes() 
    {
        return $this->_fields['DigitalProductAttributes']['FieldValue'];
    }

    /**
     * Sets the value of the DigitalProductAttributes.
     * 
     * @param DigitalProductAttributes DigitalProductAttributes
     * @return void
     */
    public function setDigitalProductAttributes($value) 
    {
        $this->_fields['DigitalProductAttributes']['FieldValue'] = $value;
        return;
    }


    /**
     * Checks if DigitalProductAttributes  is set
     * 
     * @return bool true if DigitalProductAttributes property is set
     */
    public function isSetDigitalProductAttributes()
    {
        return !is_null($this->_fields['DigitalProductAttributes']['FieldValue']);

    }
    /**
     * Creates an Item
     * 
     * @param MerchantItemId,Title,UnitPriceAmount
     * @return instance
     */
    public function createItem($merchantItemId,$title,$unitPriceAmount)
    {
      $this->setMerchantId( CheckoutByAmazon_Service_MerchantValues::getInstance()->getMerchantId());
      $this->setTitle($title);
      $this->setMerchantItemId($merchantItemId);
      $this->setUnitPrice(new CheckoutByAmazon_Service_Model_Price(array('CurrencyCode' => CheckoutByAmazon_Service_MerchantValues::getInstance()->getCurrencyCode(), 'Amount' => $unitPriceAmount)));
        return $this;
    }
    /**
     * Creates an Physical Item
     * 
     * @param Item item,Delivery Method Service Level
     * @return instance
     */  
    public function createPhysicalItem($merchantItemId,$title,$unitPriceAmount,$deliveryMethod)
    {
      $Item = $this->createItem($merchantItemId,$title,$unitPriceAmount);  
      $deliveryObject = new CheckoutByAmazon_Service_Model_DeliveryMethod();
      $deliveryObject->setServiceLevel($deliveryMethod);
      $physicalAttrObject = new CheckoutByAmazon_Service_Model_PhysicalProductAttributes();
      $physicalAttrObject->setDeliveryMethod($deliveryObject);
      $Item->setPhysicalProductAttributes($physicalAttrObject);
      return $Item;
    }

    /**
     * Sets a custom shipping Label
     * 
     * @param Label
     * @return instance
     */
    public function setShippingLabel($label)
    {
        $this->getPhysicalProductAttributes()->getDeliveryMethod()->setDisplayableShippingLabel($label);        
        return $this;
    }
    
    /**
     * Sets a Destination Name
     * 
     * @param DestinationName
     * @return instance
     */
    public function setDestinationName($destinationName)
    {
        $this->getPhysicalProductAttributes()->getDeliveryMethod()->setDestinationName($destinationName);
        return $this;
    }
    
     /**
     * Sets Shipping Custom Data
     * 
     * @param ShippingCustomData
     * @return instance
     */
    public function setShippingCustomData($shippingCustomData)
    {
        $this->getPhysicalProductAttributes()->getDeliveryMethod()->setShippingCustomData($shippingCustomData);
        return $this;
    }
  
     /**
     * Sets Item tax
     * 
     * @param TaxAmount
     * @return instance
     */    
    public function setItemTax($taxAmount)
    {
        if($this->isSetPhysicalProductAttributes())
        {
            if($this->getPhysicalProductAttributes()->isSetItemCharges())
            {
                $this->getPhysicalProductAttributes()->getItemCharges()->setTax(new                                                   
                                   CheckoutByAmazon_Service_Model_Price(array
                    ('CurrencyCode' => CheckoutByAmazon_Service_MerchantValues::getInstance()->getCurrencyCode(), 'Amount' => $taxAmount)));
            }
            else
            {
                $chargesObject = new CheckoutByAmazon_Service_Model_Charges();
                $chargesObject->setTax(new CheckoutByAmazon_Service_Model_Price(
                                                   array('CurrencyCode' => CheckoutByAmazon_Service_MerchantValues::getInstance()->getCurrencyCode(), 
                                                   'Amount' => $taxAmount)));
                $this->getPhysicalProductAttributes()->setItemCharges($chargesObject); 
            }
        }
        else
        {
            $physicalAttribsObj = new CheckoutByAmazon_Service_Model_PhysicalProductAttributes();
            $this->setPhysicalProductAttributes($physicalAttribsObj);
            $chargesObject = new CheckoutByAmazon_Service_Model_Charges();
            $chargesObject->setTax(new CheckoutByAmazon_Service_Model_Price(
                                                     array('CurrencyCode' => CheckoutByAmazon_Service_MerchantValues::getInstance()->getCurrencyCode(),
                                                     'Amount' => $taxAmount)));
            $this->getPhysicalProductAttributes()->setItemCharges($chargesObject);
 
        }
         return $this;
    }
    
     /**
     * Sets Shipping Charges
     * 
     * @param ShippingAmount
     * @return instance
     */    
    public function setItemShippingCharges($shippingAmount)
    {
        if($this->isSetPhysicalProductAttributes())
        {

            if($this->getPhysicalProductAttributes()->isSetItemCharges())
            {
                $this->getPhysicalProductAttributes()->getItemCharges()->setShipping(new
                        CheckoutByAmazon_Service_Model_Price(array('CurrencyCode' => CheckoutByAmazon_Service_MerchantValues::getInstance()->getCurrencyCode(),
                        'Amount' => $shippingAmount)));
            }                          
            else                       
            {
                $chargesObject = new CheckoutByAmazon_Service_Model_Charges();
                $chargesObject->setShipping(new CheckoutByAmazon_Service_Model_Price(
                    array('CurrencyCode' => CheckoutByAmazon_Service_MerchantValues::getInstance()->getCurrencyCode(), 
                    'Amount' => $shippingAmount)));
                $this->getPhysicalProductAttributes()->setItemCharges($chargesObject); 
            }
        }
        else
        {
            $physicalAttribsObj = new CheckoutByAmazon_Service_Model_PhysicalProductAttributes();
            $this->setPhysicalProductAttributes($physicalAttribsObj);
            $chargesObject = new CheckoutByAmazon_Service_Model_Charges();
            $chargesObject->setShipping(new CheckoutByAmazon_Service_Model_Price(
                                                        array('CurrencyCode' => CheckoutByAmazon_Service_MerchantValues::getInstance()->getCurrencyCode(),
                                                        'Amount' => $shippingAmount)));
            $this->getPhysicalProductAttributes()->setItemCharges($chargesObject);

        }   
              
        return $this;
    }

     /**
     * Sets Item Promotions
     * 
     * @param Promotion Promotion
     * @return instance
     */ 
    public function setItemPromotions($promotion)
    {
        if($this->isSetPhysicalProductAttributes())
        {

            if($this->getPhysicalProductAttributes()->isSetItemCharges())
            {                    
                $this->getPhysicalProductAttributes()->getItemCharges()->setPromotions($promotion);
            }                          
             else 
            {
                $chargesObject = new CheckoutByAmazon_Service_Model_Charges();
                $chargesObject->setPromotions($promotion);
                $this->getPhysicalProductAttributes()->setItemCharges($chargesObject);
            }
        } 
        else
        {
             $physicalAttribsObj = new CheckoutByAmazon_Service_Model_PhysicalProductAttributes();
             $this->setPhysicalProductAttributes($physicalAttribsObj);
             $chargesObject = new CheckoutByAmazon_Service_Model_Charges();
             $chargesObject->setPromotions($promotion);
             $this->getPhysicalProductAttributes()->setItemCharges($chargesObject);

        } 
        return $this;
    }
     /**
     * Sets Item Weight
     * 
     * @param WeightValue
     * @return instance
     */ 
    public function setWeight($weightValue)
    {
        $weightObject = new CheckoutByAmazon_Service_Model_Weight();
        $weightObject->setValue($weightValue);
        $weightObject->setUnit(CheckoutByAmazon_Service_MerchantValues::getInstance()->getWeightUnit());
        $this->getPhysicalProductAttributes()->setWeight($weightObject);
        return $this;
    }
   
     /**
     * Sets Condition
     * 
     * @param Condition
     * @return instance
     */     
    public function setCondition($condition)
    {
        $this->getPhysicalProductAttributes()->setCondition($condition);
        return $this;
    }

}
?>
