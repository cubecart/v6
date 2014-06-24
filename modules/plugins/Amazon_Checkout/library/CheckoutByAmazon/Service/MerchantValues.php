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
 
include(CC_ROOT_DIR.'/modules/plugins/Amazon_Checkout/hooks/common.inc.php'); 

$amazon_config = $GLOBALS['config']->get('Amazon_Checkout');

define(MERCHANT_ID,$amazon_config['merchId']); // Non configurable CubeCart identifier
define(ACCESS_KEY_ID,$amazon_config['access_key']);
define(SECRET_ACCESS_KEY,$amazon_config['secret_key']);
define(WEIGHT_UNIT,strtoupper($GLOBALS['config']->get('config','product_weight_unit')));
define(CURRENCY_CODE,$currency_code);
define(VERSION,'2010-08-31');
define(CBA_SERVICE_URL,$cba_endpoint);

class  CheckoutByAmazon_Service_MerchantValues
{
          private $merchantId;
          private static  $instance ;
          private $accessKey;
          private $secretKey;
          private $weightUnit;
          private $currencyCode;
          private $version;
          private $cbaServiceUrl;
          
          private function __construct()
          {
                 $this->merchantId = MERCHANT_ID;
                 $this->accessKey  = ACCESS_KEY_ID;
                 $this->secretKey  = SECRET_ACCESS_KEY;
                 $this->weightUnit = WEIGHT_UNIT;
                 $this->currencyCode = CURRENCY_CODE;
                 $this->version = VERSION;
                 $this->cbaServiceUrl = CBA_SERVICE_URL;
                 
                 if($this->merchantId  == "")
                 {
                     trigger_error("Merchant Id not set in the properties file ",E_USER_ERROR);
                     exit(0);
                 }
                 if($this->accessKey == "")
                 {
                     trigger_error("AccessKey not set in the properties file ",E_USER_ERROR);
                     exit(0);
                 }
                 if($this->secretKey == "")
                 {
                     trigger_error("Secret Key not set in the properties file ",E_USER_ERROR);
                     exit(0);
                 }
                 if($this->weightUnit == "")
                 {
                     trigger_error("Weight Unit not set in the properties file ",E_USER_ERROR);
                     exit(0);
                 }
                 if($this->currencyCode == "")
                 {
                     trigger_error("Currency Code not set in the properties file ",E_USER_ERROR);
                     exit(0);
                 }
                 if($this->version == "")
                 {
                     trigger_error("Version Id not set in the properties file ",E_USER_ERROR);
                     exit(0);
                 }
                 if($this->cbaServiceUrl == "")
                 {
                     trigger_error("CbaServiceUrl not set in the properties file ",E_USER_ERROR);
                     exit(0);
                 }


             }
          

         public static function getInstance() 
         {
              if(self::$instance == null)
              {
                  self::$instance   = new CheckoutByAmazon_Service_MerchantValues();
                  
              }
              return self::$instance;
          }
          public  function getMerchantId()
          {
                return $this->merchantId;
          }


          public function getAccessKey()
          {
                return $this->accessKey;
          }
         
          public function getSecretKey()
          {
                return $this->secretKey;
          }
          public function getWeightUnit()
          {
                return $this->weightUnit;

          }
          public function getCurrencyCode()
          {
                return $this->currencyCode;
          }
          public function getVersion()
          {
                return $this->version;
          }
          public function getCbaServiceUrl()
          {
                return $this->cbaServiceUrl;
          }

}

?>


