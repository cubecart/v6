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
if(isset($_GET['purchaseContractId']) && !empty($_GET['purchaseContractId']) && $GLOBALS['user']->getId()>0) {
	$GLOBALS['session']->set('customer_id', $GLOBALS['user']->getId(), 'amazon');
	$GLOBALS['db']->update('CubeCart_sessions', array('customer_id' => 0), array('session_id' => $GLOBALS['session']->getId()));
	httpredir('index.php?_a=basket&purchaseContractId='.$_GET['purchaseContractId']);
}