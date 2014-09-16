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
$get_merge = array(
	'_g' => 'rm',
	'type' => 'gateway',
	'cmd' => 'call',
	'module' => 'UPG'
);
$_GET = array_merge($_GET,$get_merge);
require('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'index.php');