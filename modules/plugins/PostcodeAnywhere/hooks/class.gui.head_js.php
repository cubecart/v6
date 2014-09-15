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
$capture_key =  $GLOBALS['config']->get('PostcodeAnywhere','capture_key');
$protocol = (CC_SSL) ? 's': '';
$head_js[] = '<link rel="stylesheet" type="text/css" href="http://services.postcodeanywhere.co.uk/css/address-3.20.css"><script type="text/javascript" src="http'.$protocol.'://services.postcodeanywhere.co.uk/js/address-3.20.js"></script>';
$GLOBALS['smarty']->assign('ADDRESS_LOOKUP', true);