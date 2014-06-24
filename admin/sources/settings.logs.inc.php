<?php
if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('settings', CC_PERM_READ, true);

global $lang;

$GLOBALS['gui']->addBreadcrumb($lang['settings']['title_logs'], currentPage('page_admin', 'page_activity', 'page_customer'));

$per_page = 25;

$admins = $GLOBALS['db']->select('CubeCart_admin_users', array('admin_id', 'username', 'name'));
if ($admins) {
	foreach ($admins as $admin) {
		$users[$admin['admin_id']] = $admin;
	}
}
## Admin access logs
$sort_var  = 'sort_admin';
$anchor  = 'logs_admin';
$page_var = 'page_admin';
if (!isset($_GET[$sort_var]) || !is_array($_GET[$sort_var])) {
	$_GET[$sort_var] = array('time' => 'DESC');
}
$current_page = currentPage(array($sort_var));
$thead_sort = array (
	'success'   => $GLOBALS['db']->column_sort('success', 'Success?', $sort_var, $current_page, $_GET[$sort_var], $anchor),
	'ip_address' => $GLOBALS['db']->column_sort('ip_address', $lang['common']['ip_address'], $sort_var, $current_page, $_GET[$sort_var], $anchor),
	'username'   => $GLOBALS['db']->column_sort('username', $lang['account']['username'], $sort_var, $current_page, $_GET[$sort_var], $anchor),
	'date'    => $GLOBALS['db']->column_sort('time', $lang['common']['date'], $sort_var, $current_page, $_GET[$sort_var], $anchor)
);
$GLOBALS['smarty']->assign('THEAD_ADMIN', $thead_sort);
$page_admin  = (isset($_GET[$page_var])) ? $_GET[$page_var] : 1;
$logs_admin = $GLOBALS['db']->select('CubeCart_access_log', false, array('type' => 'B'), $_GET[$sort_var], $per_page, $page_admin);
if ($logs_admin) {
	$GLOBALS['main']->addTabControl($lang['settings']['title_logs_access_admin'], $anchor);
	foreach ($logs_admin as $log) {
		$log['date'] = formatTime($log['time']);
		$log['img']  = ($log['success']=='Y') ? 1 : 0;
		$smarty_data['admin_logs'][] = $log;
	}
	$GLOBALS['smarty']->assign('PAGINATION_ADMIN_ACCESS', $GLOBALS['db']->pagination(false, $per_page, $page_admin, 5, $page_var, $anchor));
	$GLOBALS['smarty']->assign('ADMIN_LOGS', $smarty_data['admin_logs']);
}

## Admin activity logs
$sort_var  = 'sort_activity';
$anchor  = 'logs_activity';
$page_var  = 'page_activity';
if (!isset($_GET[$sort_var]) || !is_array($_GET[$sort_var])) {
	$_GET[$sort_var] = array('time' => 'DESC');
}

$current_page = currentPage(array($sort_var));
$thead_sort = array (
	'username'   => $GLOBALS['db']->column_sort('admin_id', $lang['account']['username'], $sort_var, $current_page, $_GET[$sort_var], $anchor),
	'ip_address' => $GLOBALS['db']->column_sort('ip_address', $lang['common']['ip_address'], $sort_var, $current_page, $_GET[$sort_var], $anchor),
	'description'  => $GLOBALS['db']->column_sort('description', $lang['common']['description'], $sort_var, $current_page, $_GET[$sort_var], $anchor),
	'date'    => $GLOBALS['db']->column_sort('time', $lang['common']['date'], $sort_var, $current_page, $_GET[$sort_var], 'logs_activity')
);
$GLOBALS['smarty']->assign('THEAD_ACTIVITY', $thead_sort);

$page_activity = (isset($_GET[$page_var])) ? $_GET[$page_var] : 1;
$logs_activity = $GLOBALS['db']->select('CubeCart_admin_log', false, false, $_GET[$sort_var], $per_page, $page_activity);
if ($logs_activity) {
	$GLOBALS['main']->addTabControl($lang['settings']['title_logs_activity_admin'], $anchor);
	foreach ($logs_activity as $log) {
		$log['date'] = formatTime($log['time']);
		$log['admin'] = $users[$log['admin_id']];
		$smarty_data['admin_activity'][] = $log;
	}
	$GLOBALS['smarty']->assign('PAGINATION_ADMIN_ACTIVITY', $GLOBALS['db']->pagination(false, $per_page, $page_activity, 5, $page_var, $anchor));
	$GLOBALS['smarty']->assign('ADMIN_ACTIVITY', $smarty_data['admin_activity']);
}

## Customer access logs
$sort_var  = 'sort_customer';
$anchor  = 'logs_customer';
$page_var  = 'page_customer';
if (!isset($_GET[$sort_var]) || !is_array($_GET[$sort_var])) {
	$_GET[$sort_var] = array('time' => 'DESC');
}
$current_page = currentPage(array($sort_var));
$thead_sort = array (
	'username'   => $GLOBALS['db']->column_sort('admin_id', $lang['account']['username'], $sort_var, $current_page, $_GET[$sort_var], $anchor),
	'ip_address' => $GLOBALS['db']->column_sort('ip_address', $lang['common']['ip_address'], $sort_var, $current_page, $_GET[$sort_var], $anchor),
	'description'  => $GLOBALS['db']->column_sort('description', $lang['common']['description'], $sort_var, $current_page, $_GET[$sort_var], $anchor),
	'date'    => $GLOBALS['db']->column_sort('time', $lang['common']['date'], $sort_var, $current_page, $_GET[$sort_var], $anchor)
);
$GLOBALS['smarty']->assign('THEAD_CUSTOMER', $thead_sort);
$page_customer  = (isset($_GET[$page_var])) ? $_GET[$page_var] : 1;
if (($logs_admin = $GLOBALS['db']->select('CubeCart_access_log', false, array('type' => 'F'), $_GET[$sort_var], $per_page, $page_customer)) !== false) {
	$GLOBALS['main']->addTabControl($lang['settings']['title_logs_access_customer'], $anchor);
	foreach ($logs_admin as $log) {
		$log['date'] = formatTime($log['time']);
		$log['img'] = ($log['success']=='Y') ? 1 : 0;
		$smarty_data['customer_activity'][] = $log;
	}
	$GLOBALS['smarty']->assign('PAGINATION_CUSTOMER', $GLOBALS['db']->pagination(false, $per_page, $page_customer, 5, $page_var, $anchor));
	$GLOBALS['smarty']->assign('CUSTOMER_ACTIVITY', $smarty_data['customer_activity']);
}
$page_content = $GLOBALS['smarty']->fetch('templates/settings.logs.php');