<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}
Admin::getInstance()->permissions('settings', CC_PERM_READ, true);


$GLOBALS['main']->addTabControl($lang['navigation']['nav_request_log'], 'request_log');
$GLOBALS['gui']->addBreadcrumb($lang['navigation']['nav_request_log'], currentPage());

if (Admin::getInstance()->superUser()) {

    if (isset($_POST['search']) && !empty($_POST['search'])) {
        httpredir(currentPage(null, array('q' => $_POST['search'])));
    }

    $per_page = 25;
    $page = (isset($_GET['page'])) ? $_GET['page'] : 1;

    if (isset($_GET['q']) && !empty($_GET['q'])) {
        $safe_q = $GLOBALS['db']->sqlSafe(trim($_GET['q']));
        $where = "(`request_url` LIKE '%".$safe_q."%' OR `request` LIKE '%".$safe_q."%' OR `result` LIKE '%".$safe_q."%' OR `error` LIKE '%".$safe_q."%' OR `response_code` LIKE '%".$safe_q."%')";
        $GLOBALS['smarty']->assign('SEARCH_QUERY', htmlspecialchars(trim($_GET['q'])));
    } else {
        $where = false;
    }

    $request_log = $GLOBALS['db']->select('CubeCart_request_log', '*', $where, array('time' => 'DESC'), $per_page, $page, false);
    $count = $GLOBALS['db']->getFoundRows();
    if (is_array($request_log)) {
        foreach ($request_log as $log) {
            $error_code_fd = (isset($log['response_code']) && !empty($log['response_code'])) ? (int)substr($log['response_code'],0,1) : 0;
            if(!empty($log['error'])) {
                $error = htmlspecialchars($log['error']);
            } elseif($error_code_fd > 0 && in_array($error_code_fd, array(4, 5))) {
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
                'error' => $error,
                'response_headers' => $log['response_headers'],
                'request_headers' => $log['request_headers']
            );
        }
    }

    $GLOBALS['smarty']->assign('REQUEST_LOG', $smarty_data['request_log'] ?? false);
    $GLOBALS['smarty']->assign('TOTAL_RESULTS', $count);
    $GLOBALS['smarty']->assign('PAGINATION_REQUEST_LOG', $GLOBALS['db']->pagination($count, $per_page, $page, 5, 'page'));
}
$page_content = $GLOBALS['smarty']->fetch('templates/settings.requestlog.php');
