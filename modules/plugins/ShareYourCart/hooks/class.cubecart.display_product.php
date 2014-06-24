<?php
/* Generate the alternate checkout button */
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