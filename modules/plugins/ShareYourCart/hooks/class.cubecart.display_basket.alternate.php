<?php
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