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
class MarketplaceWebService_MWSReportsClient {

	private $accessKeyId = "";
	private $secretAccessKey = "";

	private $appName = "";
	private $appVersion = "";

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

		//Setting the MWs endpoint URL
		$this -> config = array('ServiceURL' => $MWSProperties -> getServiceUrl(), 'ProxyHost' => null, 'ProxyPort' => -1, 'MaxErrorRetry' => 3, );

		/************************************************************************
		 * Instantiate Http Client Implementation of Marketplace Web Service
		 ***********************************************************************/

		$this -> service = new MarketplaceWebService_Client($this -> accessKeyId, $this -> secretAccessKey, $this -> config, $this -> appName, $this -> appVersion);

	}

	/**
	 * Creates a report request and submits the request to Amazon MWS.
	 *
	 * @param reportType
	 *            A value of the ReportType enumeration that indicates the type
	 *            of report to request.
	 * @param startDate
	 *            The start of a date range used for selecting the data to
	 *            report.
	 * @param endDate
	 *            The end of a date range used for selecting the data to report.
	 *
	 * @return String Value of the ReportRequestId returned in the response if
	 *         submitted, else returns an empty string.
	 */
	public function requestReport($reportType, $startDate, $endDate) {
		$request = new MarketplaceWebService_Model_RequestReportRequest();
		$request -> setMarketplace($this -> marketplaceId);
		$request -> setMerchant($this -> merchantId);
		$request -> setReportType($reportType);
		$request -> setStartDate($startDate);
		$request -> setEndDate($endDate);

		return $this -> invokeRequestReport($this -> service, $request);
	}

	/**
	 * Get Report List Action Sample
	 * returns a list of reports; by default the most recent ten reports,
	 * regardless of their acknowledgement status
	 *
	 * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
	 * @param mixed $request MarketplaceWebService_Model_GetReportList or array of parameters
	 * @return String value of the ReportRequestId returned in the response if submitted, else
	 *         returns an empty String.
	 */
	private function invokeRequestReport(MarketplaceWebService_Interface $service, $request) {
		$reportRequestId = "";
		try {
			$response = $service -> requestReport($request);

			if($response -> isSetRequestReportResult()) {
				$requestReportResult = $response -> getRequestReportResult();

				if($requestReportResult -> isSetReportRequestInfo()) {

					$reportRequestInfo = $requestReportResult -> getReportRequestInfo();
					if($reportRequestInfo -> isSetReportRequestId()) {
						$reportRequestId = $reportRequestInfo -> getReportRequestId();
					}
				}
			}

		} catch (MarketplaceWebService_Exception $ex) {
			echo("Caught Exception: " . $ex -> getMessage() . "\n");
			echo("Response Status Code: " . $ex -> getStatusCode() . "\n");
			echo("Error Code: " . $ex -> getErrorCode() . "\n");
			echo("Error Type: " . $ex -> getErrorType() . "\n");
			echo("Request ID: " . $ex -> getRequestId() . "\n");
			echo("XML: " . $ex -> getXML() . "\n");
		}

		return $reportRequestId;
	}

	/**
	 * Creates, updates, or deletes a report request schedule for a specified
	 * report type.
	 *
	 * @param reportType
	 *            A value of the ReportType enumeration that indicates the type
	 *            of report to request.
	 * @param schedule
	 *            A value of the Schedule enumeration that indicates how often a
	 *            report request should be created.
	 * @param scheduledDate
	 *            The date when the next report request is scheduled to be
	 *            submitted. Send null for default value of "Now".
	 */
	public function manageReportSchedule($reportType, $schedule, $scheduledDate) {
		$request = new MarketplaceWebService_Model_ManageReportScheduleRequest();
		$request -> setMarketplace($this -> marketplaceId);
		$request -> setMerchant($this -> merchantId);
		$request -> setReportType($reportType);
		$request -> setSchedule($schedule);

		// Checking if ScheduledDate is to be ignored
		if($scheduledDate != null) {
			$request -> setScheduledDate($scheduledDate);
		}

		return $this -> invokeManageReportSchedule($this -> service, $request);
	}

	/**
	 * Manage Report Schedule Action Sample
	 * Creates, updates, or deletes a report schedule
	 * for a given report type, such as order reports in particular.
	 *
	 * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
	 * @param mixed $request MarketplaceWebService_Model_ManageReportSchedule or array of parameters
	 */
	private function invokeManageReportSchedule(MarketplaceWebService_Interface $service, $request) {
		try {
			$response = $service -> manageReportSchedule($request);

		} catch (MarketplaceWebService_Exception $ex) {
			echo("Caught Exception: " . $ex -> getMessage() . "\n");
			echo("Response Status Code: " . $ex -> getStatusCode() . "\n");
			echo("Error Code: " . $ex -> getErrorCode() . "\n");
			echo("Error Type: " . $ex -> getErrorType() . "\n");
			echo("Request ID: " . $ex -> getRequestId() . "\n");
			echo("XML: " . $ex -> getXML() . "\n");
		}
	}

	/**
	 * Returns a list of report requests that you can use to get the
	 * ReportRequestId for a report.
	 *
	 * Send null or 0 for ignoring any of the arguments while submitting the
	 * request.
	 *
	 * @param reportRequestIdList
	 * @param reportTypeList
	 * @param reportProcessingStatusList
	 * @param maxCount
	 * @param reqeustedFromDate
	 * @param requestedToDate
	 * @return GetReportRequestListResult
	 */
	public function getReportRequestList($reportRequestIdList, $reportTypeList, $reportProcessingStatusList, $maxCount, $requestedFromDate, $requestedToDate) {

		$request = new MarketplaceWebService_Model_GetReportRequestListRequest();
		$request -> setMarketplace($this -> marketplaceId);
		$request -> setMerchant($this -> merchantId);

		// Checking if any of the parameters are to be ignored while preparing
		// the $request->
		if(($reportRequestIdList != null) && !empty($reportRequestIdList)) {
			$request -> setReportRequestIdList(new MarketplaceWebService_Model_IdList($reportRequestIdList));
		}

		if(($reportTypeList != null) && !empty($reportTypeList)) {
			$request -> setReportTypeList(new MarketplaceWebService_Model_TypeList($reportTypeList));
		}

		if(($reportProcessingStatusList != null) && !empty($reportProcessingStatusList)) {
			$request -> setReportProcessingStatusList(new MarketplaceWebService_Model_StatusList($reportProcessingStatusList));
		}

		if($maxCount != 0) {
			$request -> setMaxCount($maxCount);
		}

		if($requestedFromDate != null) {
			$request -> setRequestedFromDate($requestedFromDate);
		}

		if($requestedToDate != null) {
			$request -> setRequestedToDate($requestedToDate);
		}

		return $this -> invokeGetReportRequestList($this -> service, $request);
	}

	/**
	 * Get Report List Action Sample
	 * returns a list of reports; by default the most recent ten reports,
	 * regardless of their acknowledgement status
	 *
	 * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
	 * @param mixed $request MarketplaceWebService_Model_GetReportList or array of parameters
	 * @return GetReportRequestListResult
	 */
	private function invokeGetReportRequestList(MarketplaceWebService_Interface $service, $request) {
		$getReportRequestListResult = null;
		try {
			$response = $service -> getReportRequestList($request);
			if($response -> isSetGetReportRequestListResult()) {
				$getReportRequestListResult = $response -> getGetReportRequestListResult();
			}
		} catch (MarketplaceWebService_Exception $ex) {
			echo("Caught Exception: " . $ex -> getMessage() . "\n");
			echo("Response Status Code: " . $ex -> getStatusCode() . "\n");
			echo("Error Code: " . $ex -> getErrorCode() . "\n");
			echo("Error Type: " . $ex -> getErrorType() . "\n");
			echo("Request ID: " . $ex -> getRequestId() . "\n");
			echo("XML: " . $ex -> getXML() . "\n");
		}

		return $getReportRequestListResult;
	}

	/**
	 * Returns a list of report requests using the NextToken, which was supplied
	 * by a previous request to either GetReportRequestListByNextToken or
	 * GetReportRequestList, where the value of HasNext was true in that
	 * previous request.
	 *
	 * @param nextToken
	 * @return GetReportRequestListResult
	 */
	public function getReportRequestListByNextToken($nextToken) {
		$request = new MarketplaceWebService_Model_GetReportRequestListByNextTokenRequest();
		$request -> setMarketplace($this -> marketplaceId);
		$request -> setMerchant($this -> merchantId);

		$request -> setNextToken($nextToken);

		return $this -> invokeGetReportRequestListByNextToken($this -> service, $request);
	}

	/**
	 * Get Report Request List By Next Token Action Sample
	 * returns a list of reports; by default the most recent ten reports,
	 * regardless of their acknowledgement status
	 *
	 * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
	 * @param mixed $request MarketplaceWebService_Model_GetReportRequestListByNextTokenRequest or array of parameters
	 */
	private function invokeGetReportRequestListByNextToken(MarketplaceWebService_Interface $service, $request) {
		$getReportRequestListByNextTokenResult = null;
		try {
			$response = $service -> getReportRequestListByNextToken($request);
			if($response -> isSetGetReportRequestListByNextTokenResult()) {
				$getReportRequestListByNextTokenResult = $response -> getGetReportRequestListByNextTokenResult();
			}
		} catch (MarketplaceWebService_Exception $ex) {
			echo("Caught Exception: " . $ex -> getMessage() . "\n");
			echo("Response Status Code: " . $ex -> getStatusCode() . "\n");
			echo("Error Code: " . $ex -> getErrorCode() . "\n");
			echo("Error Type: " . $ex -> getErrorType() . "\n");
			echo("Request ID: " . $ex -> getRequestId() . "\n");
			echo("XML: " . $ex -> getXML() . "\n");
		}

		return $getReportRequestListByNextTokenResult;
	}

	/**
	 * Returns a list of reports that were created in between the given dates.
	 * Giving the acknowledged as true returns only the reports that were
	 * acknowledged by a prior call to updateReportAcknowledgements, and false
	 * returns only the reports that were not acknowledged.
	 *
	 * @param reportRequestIdList
	 * @param reportTypeList
	 * @param maxCount
	 * @param acknowledged
	 * @param availableFromDate
	 * @param availableToDate
	 * @return GetReportListResult
	 */
	public function getReportList($reportRequestIdList, $reportTypeList, $acknowledged, $maxCount, $availableFromDate, $availableToDate) {

		$request = new MarketplaceWebService_Model_GetReportListRequest();
		$request -> setMarketplace($this -> marketplaceId);
		$request -> setMerchant($this -> merchantId);

		// Checking if any of the parameters are to be ignored while preparing
		// the request.
		if(($reportRequestIdList != null) && !empty($reportRequestIdList)) {
			$request -> setReportRequestIdList(new MarketplaceWebService_Model_IdList($reportRequestIdList));
		}

		if(($reportTypeList != null) && !empty($reportTypeList)) {
			$request -> setReportTypeList(new MarketplaceWebService_Model_TypeList($reportTypeList));
		}

		if($maxCount != 0) {
			$request -> setMaxCount($maxCount);
		}

		if($availableFromDate != null) {
			$request -> setAvailableFromDate($availableFromDate);
		}

		if($availableToDate != null) {
			$request -> setAvailableToDate($availableToDate);
		}

		$request -> setAcknowledged($acknowledged);

		return $this -> invokeGetReportList($this -> service, $request);
	}

	/**
	 * Returns a list of reports that were created in in between the given
	 * dates.
	 *
	 * @param reportRequestIdList
	 * @param reportTypeList
	 * @param maxCount
	 * @param availableFromDate
	 * @param availableToDate
	 * @return GetReportListResult
	 */
	public function getAllReportsList($reportRequestIdList, $reportTypeList, $maxCount, $availableFromDate, $availableToDate) {
		$request = new MarketplaceWebService_Model_GetReportListRequest();
		$request -> setMarketplace($this -> marketplaceId);
		$request -> setMerchant($this -> merchantId);

		// Checking if any of the parameters are to be ignored while preparing
		// the request.
		if(($reportRequestIdList != null) && !empty($reportRequestIdList)) {
			$request -> setReportRequestIdList(new MarketplaceWebService_Model_IdList($reportRequestIdList));
		}

		if(($reportTypeList != null) && !empty($reportTypeList)) {
			$request -> setReportTypeList(new MarketplaceWebService_Model_TypeList($reportTypeList));
		}

		if($maxCount != 0) {
			$request -> setMaxCount($maxCount);
		}

		if($availableFromDate != null) {
			$request -> setAvailableFromDate($availableFromDate);
		}

		if($availableToDate != null) {
			$request -> setAvailableToDate($availableToDate);
		}

		return $this -> invokeGetReportList($this -> service, $request);
	}

	/**
	 * Get Report List Action Sample
	 * returns a list of reports; by default the most recent ten reports,
	 * regardless of their acknowledgement status
	 *
	 * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
	 * @param mixed $request MarketplaceWebService_Model_GetReportList or array of parameters
	 * @return GetReportListResult
	 */
	private function invokeGetReportList(MarketplaceWebService_Interface $service, $request) {
		$getReportListResult = null;
		try {
			$response = $service -> getReportList($request);
			if($response -> isSetGetReportListResult()) {
				$getReportListResult = $response -> getGetReportListResult();
			}
		} catch (MarketplaceWebService_Exception $ex) {
			echo("Caught Exception: " . $ex -> getMessage() . "\n");
			echo("Response Status Code: " . $ex -> getStatusCode() . "\n");
			echo("Error Code: " . $ex -> getErrorCode() . "\n");
			echo("Error Type: " . $ex -> getErrorType() . "\n");
			echo("Request ID: " . $ex -> getRequestId() . "\n");
			echo("XML: " . $ex -> getXML() . "\n");
		}

		return $getReportListResult;
	}

	/**
	 * Returns a list of report using the NextToken, which was supplied by a
	 * previous request to either GetReportListByNextToken or GetReportList,
	 * where the value of HasNext was true in that previous request.
	 *
	 * @param nextToken
	 * @return GetReportListByNextTokenResult
	 */
	public function getReportListByNextToken($nextToken) {
		$request = new MarketplaceWebService_Model_GetReportListByNextTokenRequest();
		$request -> setMarketplace($this -> marketplaceId);
		$request -> setMerchant($this -> merchantId);

		$request -> setNextToken($nextToken);

		return $this -> invokeGetReportListByNextToken($this -> service, $request);
	}

	/**
	 * Get Report List By Next Token Action Sample
	 * retrieve the next batch of list items and if there are more items to retrieve
	 *
	 * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
	 * @param mixed $request MarketplaceWebService_Model_GetReportListByNextToken or array of parameters
	 * @return GetReportListByNextTokenResult
	 */
	private function invokeGetReportListByNextToken(MarketplaceWebService_Interface $service, $request) {
		$getReportListByNextTokenResult = null;
		try {
			$response = $service -> getReportListByNextToken($request);
			if($response -> isSetGetReportListByNextTokenResult()) {
				$getReportListByNextTokenResult = $response -> getGetReportListByNextTokenResult();
			}
		} catch (MarketplaceWebService_Exception $ex) {
			echo("Caught Exception: " . $ex -> getMessage() . "\n");
			echo("Response Status Code: " . $ex -> getStatusCode() . "\n");
			echo("Error Code: " . $ex -> getErrorCode() . "\n");
			echo("Error Type: " . $ex -> getErrorType() . "\n");
			echo("Request ID: " . $ex -> getRequestId() . "\n");
			echo("XML: " . $ex -> getXML() . "\n");
		}

		return $getReportListByNextTokenResult;
	}

	/**
	 * Downloads the contents of the report for the given ReportId
	 *
	 * @param reportId
	 *            ReportId of the processed report that needs to be downloaded.
	 * @param $feedHandle
	 *            FeedHandle to which the reports is written to.
	 */
	public function getReport($reportId, $feedHandle) {
		$request = new MarketplaceWebService_Model_GetReportRequest();
		$request -> setMarketplace($this -> marketplaceId);
		$request -> setMerchant($this -> merchantId);

		$request -> setReportId($reportId);
		$request -> setReport($feedHandle);

		$this -> invokeGetReport($this -> service, $request);
	}

	/**
	 * Get Report Action Sample
	 * The GetReport operation returns the contents of a report. Reports can potentially be
	 * very large (>100MB) which is why we only return one report at a time, and in a
	 * streaming fashion.
	 *
	 * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
	 * @param mixed $request MarketplaceWebService_Model_GetReport or array of parameters
	 */
	function invokeGetReport(MarketplaceWebService_Interface $service, $request) {
		try {
			$response = $service -> getReport($request);

		} catch (MarketplaceWebService_Exception $ex) {
			echo("Caught Exception: " . $ex -> getMessage() . "\n");
			echo("Response Status Code: " . $ex -> getStatusCode() . "\n");
			echo("Error Code: " . $ex -> getErrorCode() . "\n");
			echo("Error Type: " . $ex -> getErrorType() . "\n");
			echo("Request ID: " . $ex -> getRequestId() . "\n");
			echo("XML: " . $ex -> getXML() . "\n");
		}
	}

}
?>