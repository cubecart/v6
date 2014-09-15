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
if ($this->_order_summary['gateway'] == 'Google_Checkout') {
	$this->_email_enabled = false;

	if ($google = $GLOBALS['config']->get('Google_Checkout')) {
		include CC_ROOT_DIR.'/modules/plugins/Google_Checkout/library/googlerequest.php';

		$grequest = new GoogleRequest($google['merchId'], $google['merchKey'], $google['mode'], $this->_config['default_currency']);
		switch ($status_id) {
			case self::ORDER_PENDING:
				break;
			case self::ORDER_PROCESS:
				$grequest->SendProcessOrder($google_order_id);
				break;
			case self::ORDER_COMPLETE:
			#	$grequest->SendDeliverOrder($google_order_id, $courier, $tracking);
				break;
			case self::ORDER_DECLINED:
				break;
			case self::ORDER_FAILED:
				break;
			case self::ORDER_CANCELLED:
			#	$grequest->SendCancelOrder($google_order_id, $reason, $comment);
				break;
		}
	}
}