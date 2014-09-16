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
if($GLOBALS['session']->get('customer_id', 'amazon')>0) {
	$GLOBALS['smarty']->assign('IS_USER',true);
	
	$customer_id = $GLOBALS['session']->get('customer_id', 'amazon');

	$customer = $GLOBALS['db']->select('CubeCart_customer', array('first_name'), array('customer_id' => $customer_id));
	$GLOBALS['smarty']->assign('LANG_WELCOME_BACK', sprintf($GLOBALS['language']->account['welcome_back'], $customer[0]['first_name'], ''));
}