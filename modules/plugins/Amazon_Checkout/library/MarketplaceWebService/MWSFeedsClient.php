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

/**
 * 
 */
class MarketplaceWebService_MWSFeedsClient {

	private $accessKeyId = "";
	private $secretAccessKey = "";

	private $appName = "";
	private $appVersion = "";
	private $documentVersion = "";

	private $marketplaceId = "";
	private $merchantId = "";
	private $merchantToken = "";

	private $service = null;
	private $config = null;

	public function __construct() {

		// loading the values from the properties file MWSConfig.ini
		// in the classpath
		$MWSProperties = new MarketplaceWebService_MWSProperties();
		$this -> merchantId = $MWSProperties -> getMerchantId();
		$this -> marketplaceId = $MWSProperties -> getMarketplaceId();
		$this -> accessKeyId = $MWSProperties -> getAccessKeyId();
		$this -> secretAccessKey = $MWSProperties -> getSecretAccessKey();
		$this -> appName = $MWSProperties -> getAppName();
		$this -> appVersion = $MWSProperties -> getAppVersion();
		$this -> merchantToken = $MWSProperties -> getMerchantToken();
		$this -> documentVersion = $MWSProperties->getDocumentVersion();

		//Setting the MWs endpoint URL
		$this -> config = array('ServiceURL' => $MWSProperties -> getServiceUrl(), 'ProxyHost' => null, 'ProxyPort' => -1, 'MaxErrorRetry' => 3, );

		/************************************************************************
		 * Instantiate Http Client Implementation of Marketplace Web Service
		 ***********************************************************************/

		$this -> service = new MarketplaceWebService_Client($this -> accessKeyId, $this -> secretAccessKey, $this -> config, $this -> appName, $this -> appVersion);

	}
   
    /**
	 * Uploads a feed to an Amazon MWS endpoint
	 * 
	 * @param feedType A FeedType enumeration
	 * @param feedContent
	 * @return String The FeedSubmissionId for the submitted feed.
	 * @throws IOException 
	 */
	private function submitFeed($feedType, $feedContent, $contentMD5Header)
	{
		$request = new MarketplaceWebService_Model_SubmitFeedRequest();
        $request->setMarketplace($this->marketplaceId);
        $request->setMerchant($this->merchantId);

        $request->setFeedType($feedType);
        
        $request->setFeedContent($feedContent);
        
        $request->setContentMD5($contentMD5Header);
        
        return $this->invokeSubmitFeed($this->service, $request);
	}
 	
	/**
	 * Submits a feed to the MWS endpoint for associating the AmazonOrderId's with the given MerchantOrderId's. Also can be used for associating AmazonOrderItemCode's with their respective MerchantOrderItemID's.
	 * 
	 * @param $envelope A SimpleXML representation of the <AmazonEnvelope> for OrderAcknowledgement Feed 
	 * @return tempFeedDirPath the path of the dir into which the feeds have to be stored
	 */
	public function acknowledgeOrder($envelope,$tempFeedDirPath)
	{
		//Storing the passed feed into a file at the given location with FeedType and timestamp appended to the filename
		$feedFilePath = $tempFeedDirPath . "/" . $this->getFileNameWithTimeStamp("Feed_OrderAcknowledgement");
		
		$feedStoreHandle = fopen($feedFilePath, 'a');
		fwrite($feedStoreHandle, $envelope->asXML());
		
		$feedHandle = @fopen($feedFilePath,"r");
				
		//Computing the MD5 hash 
		$contentMD5Header = base64_encode(md5(stream_get_contents($feedHandle), true));
		rewind($feedHandle);
		
		//Submit the OrderAcknowledgment feed
		return $this->submitFeed(MarketplaceWebService_Model_FeedType::POST_ORDER_ACKNOWLEDGEMENT_DATA, $feedHandle, $contentMD5Header);
		
	}

	/**
	 * Submits a feed to MWS endpoint for cancelling the Order Id's passed
	 * This is achieved by submitting an OrderAcknowledegment feed consisting of the order ID's with StatusCode set as "Failure".
	 * @param $envelope A SimpleXML representation of the <AmazonEnvelope> for OrderAcknowledgement Feed 
	 * @return tempFeedDirPath the path of the dir into which the feeds have to be stored
	 */
	public function cancelOrder($envelope,$tempFeedDirPath)
	{
		//Storing the passed feed into a file at the given location with FeedType and timestamp appended to the filename
		$feedFilePath = $tempFeedDirPath . "/" . $this->getFileNameWithTimeStamp("Feed_OrderAcknowledgement");
		
		$feedStoreHandle = fopen($feedFilePath, 'a');
		fwrite($feedStoreHandle, $envelope->asXML());
		
		$feedHandle = @fopen($feedFilePath,"r");
				
		//Computing the MD5 hash 
		$contentMD5Header = base64_encode(md5(stream_get_contents($feedHandle), true));
		rewind($feedHandle);
		
		//Submit the Order Cancelation feed
		return $this->submitFeed(MarketplaceWebService_Model_FeedType::POST_ORDER_ACKNOWLEDGEMENT_DATA, $feedHandle, $contentMD5Header);
		
	}
	
	/**
	 * The Order Fulfillment feed allows a seller to update Amazon with information about an order’s fulfillment status. Data provided by the seller is used to update the Amazon Payments order display to the buyer from their account.
	 * 
	 * @param $envelope A SimpleXML representation of the <AmazonEnvelope> for OrderAcknowledgement Feed 
	 * @return tempFeedDirPath the path of the dir into which the feeds have to be stored
	 */
	public function confirmShipment($envelope,$tempFeedDirPath)
	{
		//Storing the passed feed into a file at the given location with FeedType and timestamp appended to the filename
		$feedFilePath = $tempFeedDirPath . "/" . $this->getFileNameWithTimeStamp("Feed_OrderFulfillment");
		
		$feedStoreHandle = fopen($feedFilePath, 'a');
		fwrite($feedStoreHandle, $envelope->asXML());
		
		$feedHandle = @fopen($feedFilePath,"r");
				
		//Computing the MD5 hash 
		$contentMD5Header = base64_encode(md5(stream_get_contents($feedHandle), true));
		rewind($feedHandle);
		
		//Submit the Order Fulfillment feed
		return $this->submitFeed(MarketplaceWebService_Model_FeedType::POST_ORDER_FULFILLMENT_DATA, $feedHandle, $contentMD5Header);
		
	}
	
	/**
	 * The Order Adjustment feed accepts data from a seller about a refund/adjustment to existing orders. Orders can be identified either by the Amazon Payments order ID or by the seller’s order ID, if it was previously provided in the 
	 * Order Acknowledgement feed. Similarly, items within the order can be identified either by the Amazon Payments order item code or by the seller’s order item ID, if it was previously provided. Sellers must provide a reason for the 
	 * adjustment, and the amounts to be adjusted, broken out by price component (principle, shipping, tax, and so on). All adjustments for an order specified within the same adjustment message constitute one “unit of work”; the buyer’s 
	 * credit card will only be credited one time for the aggregate amount. Although the adjustment feed allows the seller to charge the buyer additional money, the net amount of the adjustment must be a credit to the buyer.
	 * @param $envelope A SimpleXML representation of the <AmazonEnvelope> for OrderAcknowledgement Feed 
	 * @return tempFeedDirPath the path of the dir into which the feeds have to be stored
	 */
	public function refundOrder($envelope,$tempFeedDirPath)
	{
		//Storing the passed feed into a file at the given location with FeedType and timestamp appended to the filename
		$feedFilePath = $tempFeedDirPath . "/" . $this->getFileNameWithTimeStamp("Feed_OrderAdjustment");
		
		$feedStoreHandle = fopen($feedFilePath, 'a');
		fwrite($feedStoreHandle, $envelope->asXML());
		
		$feedHandle = @fopen($feedFilePath,"r");
				
		//Computing the MD5 hash 
		$contentMD5Header = base64_encode(md5(stream_get_contents($feedHandle), true));
		rewind($feedHandle);
		
		//Submit the Order Fulfillment feed
		return $this->submitFeed(MarketplaceWebService_Model_FeedType::POST_PAYMENT_ADJUSTMENT_DATA, $feedHandle, $contentMD5Header);
		
	}
	
	/**
	 * Function to append the timestamp to the given File prefix and return
	 */
	private function getFileNameWithTimeStamp($filePrefix)
	{
		$timeStamp = date("Y-m-d_H-i-s");
		return $filePrefix . "_" . $timeStamp;
	}
	
	/**
	 * Returns a list of all feed submissions submitted and their processing status
	 * 
	 * Use this operation to determine the status of a feed 
	 * submission by passing in the FeedProcessingId that was returned by the SubmitFeed operation.
	 * 
	 * Send null or 0 for ignoring any of the arguments while submitting the
	 * request.
	 * @param feedSubmissionIdList
	 * @param maxCount
	 * @param feedTypeList
	 * @param feedProcessingStatusList
	 * @param submittedFromDate
	 * @param submittedToDate
	 * @return
	 */
	public function getFeedSubmissionList($feedSubmissionIdList, $maxCount, 
									$feedTypeList, $feedProcessingStatusList, 
									$submittedFromDate, $submittedToDate)
	{
		$request = new MarketplaceWebService_Model_GetFeedSubmissionListRequest();
        $request->setMarketplace( $this->marketplaceId );
        $request->setMerchant( $this->merchantId );
        
     // Checking if any of the parameters are to be ignored while preparing
		// the request.
		if ($feedSubmissionIdList != null && !empty($feedSubmissionIdList))
			$request->setFeedSubmissionIdList(new MarketplaceWebService_Model_IdList($feedSubmissionIdList));

		if ($feedTypeList != null && !empty($feedTypeList))
			$request->setFeedTypeList(new MarketplaceWebService_Model_TypeList(feedTypeList));

		if ($feedProcessingStatusList != null
				&& !empty($feedProcessingStatusList))
			$request->setFeedProcessingStatusList(new MarketplaceWebService_Model_StatusList(
					$feedProcessingStatusList));

		if ($maxCount != 0)
			$request->setMaxCount(maxCount);

		if ($submittedFromDate != null)
			$request->setSubmittedFromDate($submittedFromDate);

		if ($submittedToDate != null)
			$request->setSubmittedToDate($submittedToDate);
       
		
		return $this->invokeGetFeedSubmissionList($this->service, $request);
	}
	
	/**
	 * Returns a list of feed submissions using the NextToken parameter.
	 * 
	 * The GetFeedSubmissionListByNextToken operation returns a list of feed submissions that match
	 * the query parameters. It uses the NextToken, which was supplied in a previous request to either the
	 * GetFeedSubmissionListByNextToken operation or the GetFeedSubmissionList operation where 
	 * the value of HasNext was true.
	 * 
	 * @param nextToken
	 * @return
	 */
	public function getFeedSubmissionListByNextToken($nextToken)
	{	
		$request = new MarketplaceWebService_Model_GetFeedSubmissionListByNextTokenRequest();
		$request->setMarketplace( $this->marketplaceId );
        $request->setMerchant( $this->merchantId );
        
        $request->setNextToken($nextToken);
		
		return $this->invokeGetFeedSubmissionListByNextToken($this->service, $request);
	}
	
	/**
	 * Returns the feed processing report and the Content-MD5 header of the given FeedSubmissionId
	 * 
	 * @param feedSubmissionId
	 * @param out
	 */
	public function getFeedSubmissionResult($feedSubmissionId, $feedHandle)
	{
		$request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest();
		$request->setMarketplace( $this->marketplaceId );
        $request->setMerchant( $this->merchantId );
        
        $request->setFeedSubmissionId($feedSubmissionId);
        $request->setFeedSubmissionResult($feedHandle);
		
        return $this->invokeGetFeedSubmissionResult($this->service, $request);
	}
	
  /**
  * Submit Feed Action Sample
  * Uploads a file for processing together with the necessary
  * metadata to process the file, such as which type of feed it is.
  * PurgeAndReplace if true means that your existing e.g. inventory is
  * wiped out and replace with the contents of this feed - use with
  * caution (the default is false).
  *   
  * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
  * @param mixed $request MarketplaceWebService_Model_SubmitFeed or array of parameters
  */
  private function invokeSubmitFeed(MarketplaceWebService_Interface $service, $request) 
  {
  	$feedSubmissionId = "";
      try {
              $response = $service->submitFeed($request);
                if ($response->isSetSubmitFeedResult()) { 
                    $submitFeedResult = $response->getSubmitFeedResult();
                    if ($submitFeedResult->isSetFeedSubmissionInfo()) { 
                        $feedSubmissionInfo = $submitFeedResult->getFeedSubmissionInfo();
                        if ($feedSubmissionInfo->isSetFeedSubmissionId()) 
                        {
                            $feedSubmissionId =  $feedSubmissionInfo->getFeedSubmissionId() ;
                        }
                    } 
                } 
     } catch (MarketplaceWebService_Exception $ex) {
         echo("Caught Exception: " . $ex->getMessage() . "\n");
         echo("Response Status Code: " . $ex->getStatusCode() . "\n");
         echo("Error Code: " . $ex->getErrorCode() . "\n");
         echo("Error Type: " . $ex->getErrorType() . "\n");
         echo("Request ID: " . $ex->getRequestId() . "\n");
         echo("XML: " . $ex->getXML() . "\n");
     }
	 
	 return $feedSubmissionId;
 }

/**
  * Get Feed Submission List Action Sample
  * returns a list of feed submission identifiers and their associated metadata
  *   
  * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
  * @param mixed $request MarketplaceWebService_Model_GetFeedSubmissionList or array of parameters
  */
  private function invokeGetFeedSubmissionList(MarketplaceWebService_Interface $service, $request) 
  {
  	$getFeedSubmissionListResult = null;
      try {
              $response = $service->getFeedSubmissionList($request);
                if ($response->isSetGetFeedSubmissionListResult()) { 
                    $getFeedSubmissionListResult = $response->getGetFeedSubmissionListResult();
				}
     } catch (MarketplaceWebService_Exception $ex) {
         echo("Caught Exception: " . $ex->getMessage() . "\n");
         echo("Response Status Code: " . $ex->getStatusCode() . "\n");
         echo("Error Code: " . $ex->getErrorCode() . "\n");
         echo("Error Type: " . $ex->getErrorType() . "\n");
         echo("Request ID: " . $ex->getRequestId() . "\n");
         echo("XML: " . $ex->getXML() . "\n");
     }
	 
	 return $getFeedSubmissionListResult;
 }

/**
  * Get Feed Submission List By Next Token Action Sample
  * retrieve the next batch of list items and if there are more items to retrieve
  *   
  * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
  * @param mixed $request MarketplaceWebService_Model_GetFeedSubmissionListByNextToken or array of parameters
  */
  private function invokeGetFeedSubmissionListByNextToken(MarketplaceWebService_Interface $service, $request) 
  {
  	$getFeedSubmissionListByNextTokenResult = null;
      try {
              $response = $service->getFeedSubmissionListByNextToken($request);
                if ($response->isSetGetFeedSubmissionListByNextTokenResult()) { 
                    $getFeedSubmissionListByNextTokenResult = $response->getGetFeedSubmissionListByNextTokenResult();
                } 
     } catch (MarketplaceWebService_Exception $ex) {
         echo("Caught Exception: " . $ex->getMessage() . "\n");
         echo("Response Status Code: " . $ex->getStatusCode() . "\n");
         echo("Error Code: " . $ex->getErrorCode() . "\n");
         echo("Error Type: " . $ex->getErrorType() . "\n");
         echo("Request ID: " . $ex->getRequestId() . "\n");
         echo("XML: " . $ex->getXML() . "\n");
     }
	 
	 return $getFeedSubmissionListByNextTokenResult;
 }
	
/**
  * Get Feed Submission Result Action Sample
  * retrieves the feed processing report
  *   
  * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
  * @param mixed $request MarketplaceWebService_Model_GetFeedSubmissionResult or array of parameters
  */
  private function invokeGetFeedSubmissionResult(MarketplaceWebService_Interface $service, $request) 
  {
      try {
              $response = $service->getFeedSubmissionResult($request);
     } catch (MarketplaceWebService_Exception $ex) {
         echo("Caught Exception: " . $ex->getMessage() . "\n");
         echo("Response Status Code: " . $ex->getStatusCode() . "\n");
         echo("Error Code: " . $ex->getErrorCode() . "\n");
         echo("Error Type: " . $ex->getErrorType() . "\n");
         echo("Request ID: " . $ex->getRequestId() . "\n");
         echo("XML: " . $ex->getXML() . "\n");
     }
 }
	
 } 
?>

