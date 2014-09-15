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
require_once(dirname(__FILE__).'/../class.shareyourcart-cubecart.php');
$cubecart = new ShareYourCartCubeCartPlugin();

switch ($_GET['action'])
{
	case 'buttonCallback':
		$cubecart->buttonCallBack();
		break;
	case 'couponCallback':
		$cubecart->couponCallback();
		break;
}
