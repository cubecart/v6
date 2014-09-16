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
$customer_id = $GLOBALS['session']->get('customer_id', 'amazon');
if($customer_id > 0) {
	$GLOBALS['db']->update('CubeCart_sessions', array('customer_id' => $customer_id), array('session_id' => $GLOBALS['session']->getId()));
}
$GLOBALS['session']->delete('', 'amazon');