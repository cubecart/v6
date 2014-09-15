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
/* Generate the alternate checkout button */
require_once(dirname(__FILE__).'/../class.shareyourcart-cubecart.php');

if ($share = $GLOBALS['config']->get('ShareYourCart')) {
	$scope = (isset($share['scope']) && !empty($share['scope']) && ($share['scope']=='main' && $GLOBALS['gui']->mobile) || ($share['scope']=='mobile' && !$GLOBALS['gui']->mobile)) ? false : true;

	if ($share['status'] && $scope) {		
		$cubecart = new ShareYourCartCubeCartPlugin();
		## Update later
		$list_checkouts[] = $cubecart->getCartButton();
		$list_checkouts[] = $cubecart->getPageHeader(); 		
	}
}