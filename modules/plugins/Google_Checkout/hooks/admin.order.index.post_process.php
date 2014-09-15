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
/* Use the Order Processing API */
if (isset($_POST['google'])) {

	include CC_ROOT_DIR.'/modules/plugins/Google_Checkout/library/googlerequest.php';
	$google		= $GLOBALS['config']->get('Google_Checkout');

	$request	= new GoogleRequest($google['merchId'], $google['merchKey'], 'sandbox', $config['default_currency']);
	$data		= $_POST['google'];

	if (isset($data) && !empty($data)) {
		switch ($data['method']) {
			case 'Message':
				$request->SendBuyerMessage($data['google-order-id'], $data['message']['comment']);
				break;
			case 'Deliver':
				if (!empty($_POST['summary']['ship_method'])) {
					$allowed	= array('DHL', 'FedEx', 'UPS', 'UPS MI', 'USPS');
					foreach ($allowed as $courier) {
						if (strstr($_POST['summary']['ship_method'], $courier)) {
							$carrier = $courier;
							break;
						}
					}
					if (!isset($carrier)) $carrier = 'Other';
					$tracking = (!empty($_POST['summary']['ship_tracking'])) ? $_POST['summary']['ship_tracking'] : '';
				} else {
					$carrier = $tracking = '';
				}
				$request->SendDeliverOrder($data['google-order-id'], $carrier, $tracking);
				break;
			case 'Charge':
				$request->SendChargeOrder($data['google-order-id'], $data['charge']['amount']);
				break;
			case 'Refund':
				$request->SendRefundOrder($data['google-order-id'], $data['refund']['amount'], $data['refund']['reason'], $data['refund']['comment']);
				break;
			case 'Cancel':
				$request->SendCancelOrder($data['google-order-id'], $data['cancel']['reason'], $data['cancel']['comment']);
				break;
			case 'Authorize':
				break;

			case 'ShipItem':
				if (isset($data['ship']['item']) && is_array($data['ship']['item'])) {
					foreach ($data['ship']['item'] as $key => $item) {
						$item	= new GoogleShipItem($item);
						$item->AddTrackingData($courier, $tracking_no);
						$items[]	= $item;
					}
					$request->SendShipItem($items);
				}
				break;
			case 'ReturnItem':
				##
				break;
			case 'CancelItem':
				##
				break;
		}
	}
}