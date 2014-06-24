<?php
/** 
 *  PHP Version 5
 *
 *  @category    Amazon
 *  @package     MarketplaceWebService
 *  @copyright   Copyright 2011 Amazon Technologies, Inc.
 *  @license     http://aws.amazon.com/apache2.0  Apache License, Version 2.0
 *  @version     2011-10-31
 */
/******************************************************************************* 
 * 
 */



class MarketplaceWebService_MWSProperties {

	private $accessKeyId = "";
	private $secretAccessKey = "";

	private $appName = "";
	private $appVersion = "";
	private $documentVersion = "";

	private $marketplaceId = "";
	private $merchantId = "";
	private $merchantToken = "";

	private $serviceUrl = "";

	/**
	 * Construct new MWSProperties and load the values from the MWSConfig.ini
	 */
	public function __construct() {
	
		$config_array = $GLOBALS['config']->get('Amazon_Checkout');

		$this -> merchantId = $config_array['merchId'];
		$this -> accessKeyId = $config_array['access_key'];
		$this -> secretAccessKey = $config_array['secret_key'];
		$this -> merchantToken = $config_array['merchant_token'];
		
		include(CC_ROOT_DIR.'/modules/plugins/Amazon_Checkout/hooks/common.inc.php');
		
		$this -> serviceUrl = $mws_endpoint;
		$this-> documentVersion = '1.01';
		$this -> appVersion = '1.1.0';
		$this -> appName = 'Amazon Marketplace Web Service PHP Sample Code';
		
		
		
		$this -> marketplaceId = $config_array['marketplaceId'];
		
	}

	/**
	 * @return the accessKeyId
	 */
	public function getAccessKeyId() {
		return $this->accessKeyId;
	}

	/**
	 * @param accessKeyId the accessKeyId to set
	 */
	public function setAccessKeyId($accessKeyId) {
		$this->accessKeyId = $accessKeyId;
	}

	/**
	 * @return the secretAccessKey
	 */
	public function getSecretAccessKey() {
		return $this->secretAccessKey;
	}

	/**
	 * @param secretAccessKey the secretAccessKey to set
	 */
	public function setSecretAccessKey($secretAccessKey) {
		$this->secretAccessKey = $secretAccessKey;
	}

	/**
	 * @return the appName
	 */
	public function getAppName() {
		return $this->appName;
	}

	/**
	 * @param appName the appName to set
	 */
	public function setAppName($appName) {
		$this->appName = $appName;
	}

	/**
	 * @return the appVersion
	 */
	public function getAppVersion() {
		return $this->appVersion;
	}

	/**
	 * @param appVersion the appVersion to set
	 */
	public function setAppVersion($appVersion) {
		$this->appVersion = $appVersion;
	}

	/**
	 * @return the marketplaceId
	 */
	public function getMarketplaceId() {
		return $this->marketplaceId;
	}

	/**
	 * @param marketplaceId the marketplaceId to set
	 */
	public function setMarketplaceId($marketplaceId) {
		$this->marketplaceId = $marketplaceId;
	}

	/**
	 * @return the merchantId
	 */
	public function getMerchantId() {
		return $this->merchantId;
	}

	/**
	 * @param merchantId the merchantId to set
	 */
	public function setMerchantId($merchantId) {
		$this->merchantId = $merchantId;
	}

	/**
	 * @return the merchantToken
	 */
	public function getMerchantToken() {
		return $this->merchantToken;
	}

	/**
	 * @param merchantToken the merchantToken to set
	 */
	public function setMerchantToken($merchantToken) {
		$this->merchantToken = $merchantToken;
	}

	/**
	 * @param serviceUrl the serviceUrl to set
	 */
	public function setServiceUrl($serviceUrl) {
		$this->serviceUrl = $serviceUrl;
	}

	/**
	 * @return the serviceUrl
	 */
	public function getServiceUrl() {
		return $this->serviceUrl;
	}
	
	/**
	 * @param documentVersion the documentVersion to set
	 */
	public function setDocumentVersion($documentVersion) {
		$this->documentVersion = $documentVersion;
	}

	/**
	 * @return the documentVersion
	 */
	public function getDocumentVersion() {
		return $this->documentVersion;
	}

}

?>