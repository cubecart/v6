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

/* Chronopay malforms the return URL so we have to bring it to a *static* URL. */
$_GET = array(
	'_g' => 'rm',
	'type' => 'gateway',
	'cmd' => 'process',
	'module' => 'WorldPay'
);

require('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'index.php');