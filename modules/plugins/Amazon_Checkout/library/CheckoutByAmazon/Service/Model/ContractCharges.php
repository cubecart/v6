<?php

 class CheckoutByAmazon_Service_Model_ContractCharges extends CheckoutByAmazon_Service_Model_Charges
{

      public function __construct($data = null)
      {
           parent::__construct($data);
      }

      /**
       * Sets the value of the ContractTax.
       * 
       * @param PromotionList ContractTax
       * @return void
       */

      public function setContractTax($amount)
      {
            //$this->Charges->setContractTax($amount);
          parent::setTax( new CheckoutByAmazon_Service_Model_Price(array('CurrencyCode' => 
                                CheckoutByAmazon_Service_MerchantValues::getInstance()->getCurrencyCode(),  'Amount' => $amount)));
      }

      /**
       * Sets the value of the ContractShippingCharges.
       * 
       * @param PromotionList ContractShippingCharges
       * @return void
       */
      public function setContractShippingCharges($amount)
      {
         // $this->Charges->setContractShippingCharges($amount);
        parent::setShipping( new CheckoutByAmazon_Service_Model_Price(array('CurrencyCode' =>  
                                   CheckoutByAmazon_Service_MerchantValues::getInstance()->getCurrencyCode(),'Amount' => $amount)));
      }
     
      /**
       * Sets the value of the Promotions.
       * 
       * @param PromotionList Promotions
       * @return void
       */
      public function setContractPromotions($value)
      {
           //$this->Charges->setContractPromotions($PromotionListObject);
                parent::setPromotions($value);

      }


 
}

?>

