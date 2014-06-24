<?php
if (!defined('CC_INI_SET')) die('Access Denied');

global $lang;

$GLOBALS['main']->addTabControl($lang['navigation']['nav_request_log'], 'request_log');
$GLOBALS['gui']->addBreadcrumb($lang['navigation']['nav_request_log'], currentPage());

if (Admin::getInstance()->superUser()) {
	//System errors
	$per_page = 25;
	$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
	$request_log = $GLOBALS['db']->select('CubeCart_request_log', array('time', 'request', 'result', 'request_url'), false, array('time' => 'DESC'), $per_page, $page, false);
	if (is_array($request_log)) {
		foreach ($request_log as $log) {
			$smarty_data['request_log'][] = array(
				'time'    => formatTime(strtotime($log['time'])),
				'request'   => htmlspecialchars($log['request']),
				'result'   => htmlspecialchars($log['result']),
				'request_url' => $log['request_url']
			);
		}
	}

	$GLOBALS['smarty']->assign('REQUEST_LOG', $smarty_data['request_log']);
	$count = $GLOBALS['db']->count('CubeCart_request_log', 'request_id');
	$GLOBALS['smarty']->assign('PAGINATION_REQUEST_LOG', $GLOBALS['db']->pagination($count, $per_page, $page, 5, 'page'));
}
$page_content = $GLOBALS['smarty']->fetch('templates/settings.requestlog.php');