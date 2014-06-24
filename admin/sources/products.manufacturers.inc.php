<?php
/*
+--------------------------------------------------------------------------
|   CubeCart
|   ========================================
|	CubeCart is a registered trade mark of Devellion Limited
|   (c) Devellion Limited 2008. All rights reserved.
|   ========================================
|   Web: http://www.cubecart.com
|   Email: info (at) cubecart (dot) com
|	License Type: CubeCart is NOT Open Source Software and Limitations Apply
|   Licence Info: http://www.cubecart.com/site/faq/license.php
+--------------------------------------------------------------------------
|	Manage Manufacturer
+--------------------------------------------------------------------------
*/

if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('reviews', CC_PERM_READ, true);

global $lang;

## Delete Manufacturer
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && Admin::getInstance()->permissions('products', CC_PERM_DELETE)) {
	if ($GLOBALS['db']->delete('CubeCart_manufacturers', array('id' => (int)$_GET['delete']))) {
		$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_manufacturer_delete']);
	} else {
		$GLOBALS['main']->setACPWarning($lang['catalogue']['error_manufacturer_delete']);
	}
	foreach ($GLOBALS['hooks']->load('admin.product.manufacturers.delete') as $hook) include $hook;
	httpredir(currentPage(array('delete')));
}

## Update Manufacturer
if (isset($_POST['manufacturer']) && is_array($_POST['manufacturer'])) {
	foreach ($GLOBALS['hooks']->load('admin.product.manufacturers.save.pre_process') as $hook) include $hook;
	if (!empty($_POST['manufacturer']['URL'])) {
		$url_parts = parse_url($_POST['manufacturer']['URL']);
		if (!isset($url_parts['scheme']) || empty($url_parts['scheme'])) {
			$_POST['manufacturer']['URL'] = "http://".$_POST['manufacturer']['URL'];
		}
	}
	if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
		if ($GLOBALS['db']->update('CubeCart_manufacturers', $_POST['manufacturer'], array('id' => (int)$_GET['edit']))) {
			$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_manufacturer_update']);
		} else {
			$GLOBALS['main']->setACPWarning($lang['catalogue']['error_manufacturer_update']);
		}
	} else {
		if ($GLOBALS['db']->insert('CubeCart_manufacturers', $_POST['manufacturer'])) {
			$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_manufacturer_create']);
		} else {
			$GLOBALS['main']->setACPWarning($lang['catalogue']['error_manufacturer_create']);
		}
	}
	foreach ($GLOBALS['hooks']->load('admin.product.manufacturers.save.post_process') as $hook) include $hook;
	httpredir('?_g=products&node=manufacturers#tab_manufacturers');
}
$GLOBALS['gui']->addBreadcrumb($lang['catalogue']['title_manufacturer'], currentPage(array('edit')));

foreach ($GLOBALS['hooks']->load('admin.product.manufacturer.pre_display') as $hook) include $hook;

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
	$GLOBALS['main']->addTabControl($lang['catalogue']['title_manufacturer'], false, currentPage(array('edit')));
	$GLOBALS['main']->addTabControl($lang['catalogue']['title_manufacturer_edit'], 'manu_edit');
	if (($manufacturers = $GLOBALS['db']->select('CubeCart_manufacturers', array('name', 'id', 'URL'), array('id' => (int)$_GET['edit']))) !== false) {
		$GLOBALS['smarty']->assign('EDIT', $manufacturers[0]);
	} else {
		$GLOBALS['main']->setACPWarning($lang['catalogue']['error_manufacturer_found']);
		httpredir(currentPage(array('edit')));
	}
	$GLOBALS['smarty']->assign('DISPLAY_FORM', true);

} else {
	$GLOBALS['main']->addTabControl($lang['catalogue']['title_manufacturer'], 'manufacturers');
	$GLOBALS['main']->addTabControl($lang['catalogue']['title_manufacturer_add'], 'manu_add');
	$catalogue = Catalogue::getInstance();
	$page  = (isset($_GET['page'])) ? $_GET['page'] : 1;
	$per_page = 10;
	if (($manufacturers = $GLOBALS['db']->select('CubeCart_manufacturers', array('id', 'image'), false, 'name', $per_page, $page)) !== false) {
		$GLOBALS['smarty']->assign('PAGINATION', $GLOBALS['db']->pagination(false, $per_page, $page));
		foreach ($manufacturers as $manufacturer) {
			$manufacturer['name'] = $catalogue->getManufacturer($manufacturer['id']);
			$smarty_data['manufacturers'][] = $manufacturer;
		}
		$GLOBALS['smarty']->assign('MANUFACTURERS', $smarty_data['manufacturers']);

	}
	$GLOBALS['smarty']->assign('DISPLAY_LIST', true);
}
$page_content = $GLOBALS['smarty']->fetch('templates/products.manufacturers.php');