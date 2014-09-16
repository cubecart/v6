<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */
if(!defined('CC_INI_SET')) die('Access Denied');

if(in_array($status_id, array(3,4,5,6))) {

	require_once('modules/plugins/Amazon_Checkout/library/MarketplaceWebService/config.inc.php');
	require_once('modules/plugins/Amazon_Checkout/gateway.class.php');
	
	$gateway = new Gateway($GLOBALS['config']->get('Amazon_Checkout'));
	$order_info = $gateway->getOrderTrans($this->_order_summary['cart_order_id'], 'order_id');
	
	$feeds = new MarketplaceWebService_MWSFeedsClient();
	$MWSProperties = new MarketplaceWebService_MWSProperties();

	$envelope = new SimpleXMLElement("<AmazonEnvelope></AmazonEnvelope>");
	$envelope->Header->DocumentVersion = $MWSProperties->getDocumentVersion();
	$envelope->Header->MerchantIdentifier = $MWSProperties->getMerchantToken();

	if($status_id == 3) {
		$envelope->MessageType = "OrderFulfillment";
		$envelope->Message[0]->MessageID = 1;
		$envelope->Message[0]->OrderFulfillment->AmazonOrderID = $order_info['trans_id'];
		$envelope->Message[0]->OrderFulfillment->MerchantFulfillmentID = time().rand(0,99999);
		$envelope->Message[0]->OrderFulfillment->FulfillmentDate = date('c');
		$envelope->Message[0]->OrderFulfillment->FulfillmentData->CarrierName = '';
		$envelope->Message[0]->OrderFulfillment->FulfillmentData->ShippingMethod = $this->_order_summary['ship_method'];
		$envelope->Message[0]->OrderFulfillment->FulfillmentData->ShipperTrackingNumber = $this->_order_summary['ship_tracking'];
		$feedSubmissionId = $feeds->confirmShipment($envelope, 'cache');
	} elseif(in_array($status_id, array(4,5,6))) {
		$envelope->MessageType = "OrderAcknowledgement";
		$envelope->Message[0] ->MessageID = 1;
		$envelope->Message[0] ->OrderAcknowledgement->AmazonOrderID = $order_info['trans_id'];
		$envelope->Message[0] ->OrderAcknowledgement->MerchantOrderID = $this->_order_summary['cart_order_id'];
		$envelope->Message[0] ->OrderAcknowledgement->StatusCode = "Failure";
		$feedSubmissionId = $feeds->cancelOrder($envelope, 'cache');
	}
}