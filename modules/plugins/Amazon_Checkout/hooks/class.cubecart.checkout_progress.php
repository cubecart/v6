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
if($GLOBALS['session']->get('stage', 'amazon')=='wallet') {
	$status	= 2;
} elseif($GLOBALS['session']->get('stage', 'amazon')=='complete') {
	$status	= 3;
} elseif($GLOBALS['session']->has('purchaseContractId','amazon') && $GLOBALS['session']->get('purchaseContractId', 'amazon')!=='') {
	$status	= 1;
}