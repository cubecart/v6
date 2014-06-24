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
 * Checkout By Service  Exception provides details of errors 
 * returned by Checkout By Service  service
 *
 */
class CheckoutByAmazon_Service_RequestException extends CheckoutByAmazon_Service_Exception

{

    /**
     * Constructs CheckoutByAmazon_Service_Exception
     * @param array $ErrorInfo details of exception.
     * Keys are:
     * <ul>
     * <li>Message - (string) text message for an exception</li>
     * <li>StatusCode - (int) HTTP status code at the time of exception</li>
     * <li>ErrorCode - (string) specific error code returned by the service</li>
     * <li>ErrorType - (string) Possible types:  Sender, Receiver or Unknown</li>
     * <li>RequestId - (string) request id returned by the service</li>
     * <li>XML - (string) compete xml response at the time of exception</li>
     * <li>Exception - (Exception) inner exception if any</li>
     * </ul>
     *         
     */
    public function __construct($errorCode, $message, $statusCode, $errorType,$requestId, $xml )
    {
        $this->_message = $message;
       // parent::__construct($message);
        $ErrorInfo["StatusCode"] = $statusCode;
        $ErrorInfo["ErrorCode"] = $errorCode;
        $ErrorInfo["ErrorType"] = $errorType;
        $ErrorInfo["RequestId"] = $requestId;
        $ErrorInfo["XML"]= $xml;
        parent::__construct($ErrorInfo);
    }

}

?>
