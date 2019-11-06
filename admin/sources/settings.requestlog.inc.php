<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}
Admin::getInstance()->permissions('settings', CC_PERM_READ, true);

global $lang;

$GLOBALS['main']->addTabControl($lang['navigation']['nav_request_log'], 'request_log');
$GLOBALS['gui']->addBreadcrumb($lang['navigation']['nav_request_log'], currentPage());

if (Admin::getInstance()->superUser()) {
    //System errors
    $per_page = 25;
    $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
    $request_log = $GLOBALS['db']->select('CubeCart_request_log', '*', false, array('time' => 'DESC'), $per_page, $page, false);
    $count = $GLOBALS['db']->getFoundRows();
    if (is_array($request_log)) {
        foreach ($request_log as $log) {
            $error_code_fd = substr($log['response_code'],0,1);
            if(!empty($log['error'])) {
                $error = htmlspecialchars($log['error']);
            } elseif($log['response_code']>0 && in_array($error_code_fd, array('4', '5'))) {
                $error = true;
            } else {
                $error = false;
            }

            $smarty_data['request_log'][] = array(
                'time'    => formatTime(strtotime($log['time'])),
                'request'   => htmlspecialchars($log['request']),
                'result'   => htmlspecialchars($log['result']),
                'response_code'   => $log['response_code'],
                'response_code_description'   => Request::getResponseCodeDescription($log['response_code']),
                'is_curl'   => $log['is_curl'],
                'request_url' => $log['request_url'],
                'error' => $error
            );
        }
    }

    $GLOBALS['smarty']->assign('REQUEST_LOG', $smarty_data['request_log']);
    $GLOBALS['smarty']->assign('PAGINATION_REQUEST_LOG', $GLOBALS['db']->pagination($count, $per_page, $page, 5, 'page'));
}
$page_content = $GLOBALS['smarty']->fetch('templates/settings.requestlog.php');
