<?php
if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('products', CC_PERM_READ, true);

## Option Sets - Assign
if (isset($_POST['set'])) {
	$updated = false;
	foreach ($_POST['set'] as $set_id) {
		foreach ($_POST['product'] as $product_id) {
			$set_search = array('product_id' => (int)$product_id, 'set_id' => (int)$set_id);
			if (!$GLOBALS['db']->select('CubeCart_options_set_product', array('set_product_id'), $set_search)) {
				if ($GLOBALS['db']->insert('CubeCart_options_set_product', $set_search)) {
					$updated = true;
				}
			}
		}
	}
	if ($updated) {
		$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_option_sets_updated']);
	} else {
		$GLOBALS['main']->setACPWarning($lang['catalogue']['notify_option_sets_already_assigned']);
	}
	httpredir(currentPage());
}

#############################################
$GLOBALS['main']->addTabControl($lang['catalogue']['title_product_list'], null, currentPage(array('node')));
$GLOBALS['main']->addTabControl($lang['catalogue']['product_add'], null, currentPage(array('node'), array('action' => 'add')));
$GLOBALS['main']->addTabControl($lang['catalogue']['title_category_assign_to'], null, currentPage(null, array('node' => 'assign')));
$GLOBALS['main']->addTabControl($lang['catalogue']['title_option_set_assign'], 'assign');
$GLOBALS['gui']->addBreadcrumb($lang['catalogue']['title_option_set_assign'], currentPage());

## List option sets
if (($option_sets = $GLOBALS['db']->select('CubeCart_options_set')) !== false) {
	$GLOBALS['smarty']->assign('OPTION_SETS', $option_sets);
}

## List products
if (($products = $GLOBALS['db']->select('CubeCart_inventory', false, false, array('name' => 'ASC'))) !== false) {
	$GLOBALS['smarty']->assign('PRODUCTS', $products);
}

$page_content = $GLOBALS['smarty']->fetch('templates/products.optionsets.php');