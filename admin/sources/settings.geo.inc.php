<?php
if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('settings', CC_PERM_READ, true);

global $lang;

$GLOBALS['gui']->addBreadcrumb($lang['country']['bread_geo']);

if (isset($_POST['multi_country_action']) && !empty($_POST['multi_country_action']) && strtolower($_POST['multi_country_action']) == 'delete'  && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
	if (is_array($_POST['multi_country'])) {
		foreach ($_POST['multi_country'] as $country => $value) {
			$GLOBALS['db']->delete('CubeCart_geo_country', array('id' => $country));
			$GLOBALS['db']->delete('CubeCart_geo_zone', array('country_id' => $country));
		}
		$GLOBALS['main']->setACPNotify($lang['country']['notify_country_delete']);
	} else {
		$GLOBALS['main']->setACPWarning($lang['country']['error_country_delete_multi']);
	}
	httpredir(currentPage());
}

if (isset($_POST['multi_zone_action']) && !empty($_POST['multi_zone_action']) && strtolower($_POST['multi_zone_action']) == 'delete' && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
	if (is_array($_POST['multi_zone'])) {
		foreach ($_POST['multi_zone'] as $zone => $value) {
			$GLOBALS['db']->delete('CubeCart_geo_zone', array('id' => $zone));
		}
		$GLOBALS['main']->setACPNotify($lang['country']['notify_zone_delete']);
	} else {
		$GLOBALS['main']->setACPWarning($lang['country']['error_zone_delete_multi']);
	}
	httpredir(currentPage());
}

if (isset($_POST['save']) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
	## Update existing Country
	if (isset($_POST['country']) && is_array($_POST['country'])) {
		$updated = false;
		foreach ($_POST['country'] as $id => $data) {
			if ($GLOBALS['db']->update('CubeCart_geo_country', $data, array('id' => $id), true)) {
				$updated = true;
			}
		}
		if ($updated) {
			$GLOBALS['main']->setACPNotify($lang['country']['notify_country_update']);
		} else {
			$GLOBALS['main']->setACPWarning($lang['country']['error_country_update']);
		}
	}
	## Update existing Zone
	if (isset($_POST['zone']) && is_array($_POST['zone'])) {
		$updated = false;
		foreach ($_POST['zone'] as $id => $data) {
			if ($GLOBALS['db']->update('CubeCart_geo_zone', $data, array('id' => $id), true)) {
				$updated = true;
			}
		}
		if ($updated) {
			$GLOBALS['main']->setACPNotify($lang['country']['notify_zone_update']);
		} else {
			$GLOBALS['main']->setACPWarning($lang['country']['error_zone_update']);
		}
	}
	## Add new Country
	if (isset($_POST['new_country']) && !empty($_POST['new_country']['name'])) {
		if ($GLOBALS['db']->insert('CubeCart_geo_country', $_POST['new_country'])) {
			$GLOBALS['main']->setACPNotify($lang['country']['notify_country_create']);
		} else {
			$GLOBALS['main']->setACPWarning($lang['country']['error_country_create']);
		}
	}
	## Add new Zone
	if (isset($_POST['new_zone']) && !empty($_POST['new_zone']['name']) && !empty($_POST['new_zone']['country_id'])) {
		if ($GLOBALS['db']->insert('CubeCart_geo_zone', $_POST['new_zone'])) {
			$GLOBALS['main']->setACPNotify($lang['country']['notify_zone_create']);
		} else {
			$GLOBALS['main']->setACPWarning($lang['country']['error_zone_create']);
		}
	}
	httpredir(currentPage());
}

if (isset($_GET['delete']) && isset($_GET['id']) && is_numeric($_GET['id']) && Admin::getInstance()->permissions('settings', CC_PERM_DELETE)) {
	switch (strtolower($_GET['delete'])) {
	case 'country':
		$GLOBALS['db']->delete('CubeCart_geo_country', array('id' => $_GET['id']));
		$GLOBALS['main']->setACPNotify($lang['country']['notify_country_delete']);
		break;
	case 'zone':
		$GLOBALS['db']->delete('CubeCart_geo_zone', array('id' => $_GET['id']));
		$GLOBALS['main']->setACPNotify($lang['country']['notify_zone_delete']);
		break;
	}
	httpredir(currentPage(array('delete', 'id')));
}

$GLOBALS['main']->addTabControl($lang['country']['tab_country'], 'countries');
$GLOBALS['main']->addTabControl($lang['country']['tab_country_add'], 'add_country');
$GLOBALS['main']->addTabControl($lang['country']['tab_zone'], 'zones');
$GLOBALS['main']->addTabControl($lang['country']['tab_zone_add'], 'add_zone');

$per_page  = 20;

if (($country_count = $GLOBALS['db']->select('CubeCart_geo_country', array('name', 'id'), '', array('name'=>'ASC'))) !== false) {
	$country_page = (isset($_GET['page-country'])) ? $_GET['page-country'] : 1;
	$count   = count($country_count);
	if ($count > $per_page) {
		$GLOBALS['smarty']->assign('PAGINATION_COUNTRY', $GLOBALS['db']->pagination($count, $per_page, $country_page, 9, 'page-country', 'countries'));
	}
	foreach ($country_count as $country) {
		$country_list[$country['id']] = $country['name'];
		$select_country[] = $country;
	}
	$GLOBALS['smarty']->assign('SELECT_COUNTRY', $select_country);

	if (($countries = $GLOBALS['db']->select('CubeCart_geo_country', false, false, array('name' => 'ASC'), $per_page, $country_page)) !== false) {
		foreach ($countries as $country) {
			$country['delete']   = currentPage(null, array('delete' => 'country', 'id' => $country['id']));
			$smarty_data['countries'][] = $country;
		}
		$GLOBALS['smarty']->assign('COUNTRIES', $smarty_data['countries']);
	}
}

if (($zone_count = $GLOBALS['db']->select('CubeCart_geo_zone', array('id'))) !== false) {
	$zone_page = (isset($_GET['page-zone'])) ? $_GET['page-zone'] : 1;
	$count  = count($zone_count);
	if ($count > $per_page) {
		$GLOBALS['smarty']->assign('PAGINATION_ZONE', $GLOBALS['db']->pagination($count, $per_page, $zone_page, 9, 'page-zone', 'zones'));
	}
	if (($zones = $GLOBALS['db']->select('CubeCart_geo_zone', false, false, array('country_id' => 'ASC', 'name' => 'ASC'), $per_page, $zone_page)) !== false) {
		foreach ($zones as $zone) {
			$zone['country'] = $country_list[$zone['country_id']];
			$zone['delete']  = currentPage(null, array('delete' => 'zone', 'id' => $zone['id']));
			$smarty_data['zones'][] = $zone;
		}
		$GLOBALS['smarty']->assign('ZONES', $smarty_data['zones']);
	}
}
$page_content = $GLOBALS['smarty']->fetch('templates/settings.geo.php');