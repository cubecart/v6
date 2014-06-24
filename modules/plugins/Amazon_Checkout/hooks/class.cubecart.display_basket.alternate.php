<?php
include(CC_ROOT_DIR.'/modules/plugins/Amazon_Checkout/hooks/common.inc.php');

if ($GLOBALS['cart']->basket_digital==false && !defined('PURCHASE_CONTRACT_ID') && $module_config = $GLOBALS['config']->get('Amazon_Checkout')) {
	
	$scope = (isset($module_config['scope']) && !empty($module_config['scope']) && ($module_config['scope']=='main' && $GLOBALS['gui']->mobile) || ($module_config['scope']=='mobile' && !$GLOBALS['gui']->mobile)) ? false : true;

	if ($module_config['status'] && $scope) {
		
		$buttonSize = $module_config['buttonSize']; 
		$buttonColor = $module_config['buttonColor'];
		$buttonBg = $module_config['buttonBg'];
		
		$returnURL = $GLOBALS['storeURL'].'/index.php?_a=basket';
		
		$amazon_vars = array(
			'widgetURL' 		=> $js_url,
			'redirectDomain'	=> $main_url,
			'merchId' 			=> $module_config['merchId'],
			'buttonSize' 		=> $buttonSize,
			'buttonColor'		=> $buttonColor,
			'buttonBg' 			=> $buttonBg,
			'returnURL' 		=> $returnURL
			
		);
		
		$GLOBALS['smarty']->assign('AMAZON', $amazon_vars);
		
		$path = 'modules/plugins/Amazon_Checkout';
		
		$file_name = 'button.php';
		
		$form_file = $GLOBALS['gui']->getCustomModuleSkin('plugins', $path, $file_name);
		$GLOBALS['gui']->changeTemplateDir($form_file);
		$button = $GLOBALS['smarty']->fetch($file_name);
		$GLOBALS['gui']->changeTemplateDir();	
	

		if(is_numeric($module_config['position']) && !isset($list_checkouts[$module_config['position']])) {
			$position = $module_config['position'];
		} else {
			$position = '';
		}
		$list_checkouts[$position]	= $button;
 	}
} elseif(defined('PURCHASE_CONTRACT_ID')) {
	$load_checkouts = false;
}
if($GLOBALS['session']->get('stage', 'amazon')=='wallet') {
	$GLOBALS['smarty']->assign('DISABLE_GATEWAYS', true);
	$GLOBALS['smarty']->assign('CHECKOUT_BUTTON', $GLOBALS['language']->gateway['make_payment']);
}