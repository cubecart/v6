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

if ($share = $GLOBALS['config']->get('ShareYourCart')) {
	if ($share['status']) {	
		$cubecart = new ShareYourCartCubeCartPlugin();
		## Update later
		$product = $GLOBALS['smarty']->getTemplateVars('PRODUCT');

		$product['description'] .=  $cubecart->getPageHeader();
		$product['description'] .=  $cubecart->getProductButton();
		
		$GLOBALS['smarty']->assign('PRODUCT', $product);
		
	}
}