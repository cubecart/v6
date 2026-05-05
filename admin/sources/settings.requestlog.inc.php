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
// Surface "Clear log" as a destructive action tab in the tab bar when there's anything to clear.
if ($GLOBALS['db']->select('CubeCart_request_log', array('request_id'), false, false, 1, false, false)) {
    $GLOBALS['main']->addTabAction($lang['maintain']['clear_log'], '?_g=maintenance&emptyRequestLogs=true&redir=viewlog');
}
$GLOBALS['gui']->addBreadcrumb($lang['navigation']['nav_request_log'], currentPage());

if (Admin::getInstance()->superUser()) {

    // Filter inputs (search + status-code class + errors-only). Persisted in session
    // so paging through results doesn't lose them.
    if (isset($_GET['reset_filter'])) {
        $GLOBALS['session']->delete('requestlog_q');
        $GLOBALS['session']->delete('requestlog_status');
        $GLOBALS['session']->delete('requestlog_errors');
        httpredir('?_g=settings&node=requestlog');
    }
    if (isset($_GET['filter_submit'])) {
        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $sc = isset($_GET['status']) ? trim((string)$_GET['status']) : '';
        $errs = !empty($_GET['errors_only']) ? '1' : '';
        if ($q === '') $GLOBALS['session']->delete('requestlog_q'); else $GLOBALS['session']->set('requestlog_q', $q);
        if (!in_array($sc, array('2', '3', '4', '5'), true)) $GLOBALS['session']->delete('requestlog_status'); else $GLOBALS['session']->set('requestlog_status', $sc);
        if ($errs === '') $GLOBALS['session']->delete('requestlog_errors'); else $GLOBALS['session']->set('requestlog_errors', '1');
    }
    $q   = (string)$GLOBALS['session']->get('requestlog_q');
    $sc  = (string)$GLOBALS['session']->get('requestlog_status');
    $errs_only = (string)$GLOBALS['session']->get('requestlog_errors') === '1';

    $where_parts = array();
    if ($q !== '') {
        $safe_q = $GLOBALS['db']->sqlSafe($q);
        $where_parts[] = "(`request_url` LIKE '%".$safe_q."%' OR `request` LIKE '%".$safe_q."%' OR `result` LIKE '%".$safe_q."%' OR `error` LIKE '%".$safe_q."%' OR `response_code` LIKE '%".$safe_q."%')";
        $GLOBALS['smarty']->assign('SEARCH_QUERY', htmlspecialchars($q));
    }
    if (in_array($sc, array('2', '3', '4', '5'), true)) {
        $where_parts[] = "`response_code` LIKE '".$sc."__'";
    }
    if ($errs_only) {
        $where_parts[] = "(`error` <> '' OR `response_code` LIKE '4__' OR `response_code` LIKE '5__')";
    }
    $where = !empty($where_parts) ? implode(' AND ', $where_parts) : false;

    $per_page = 25;
    $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;

    $request_log = $GLOBALS['db']->select('CubeCart_request_log', '*', $where, array('time' => 'DESC'), $per_page, $page, false);
    $count = $GLOBALS['db']->getFoundRows();
    $smarty_data = array('request_log' => array());
    if (is_array($request_log)) {
        foreach ($request_log as $log) {
            $code     = (string)$log['response_code'];
            $code_int = ctype_digit($code) ? (int)$code : 0;
            $bucket   = ($code_int >= 200 && $code_int <= 599) ? (int)substr($code, 0, 1) : 0;
            $is_error = !empty($log['error']) || in_array($bucket, array(4, 5), true);

            $smarty_data['request_log'][] = array(
                'request_id'                => (int)$log['request_id'],
                'time'                      => formatTime(strtotime($log['time'])),
                'first_time'                => formatTime(strtotime($log['first_time'])),
                'occurrences'               => (int)$log['occurrences'],
                'request'                   => (string)$log['request'],
                'result'                    => (string)$log['result'],
                'response_code'             => $code,
                'response_code_description' => Request::getResponseCodeDescription($code),
                'response_bucket'           => $bucket,
                'is_curl'                   => $log['is_curl'],
                'request_url'               => (string)$log['request_url'],
                'error'                     => (string)$log['error'],
                'is_error'                  => $is_error,
                'response_headers'          => (string)$log['response_headers'],
                'request_headers'           => (string)$log['request_headers'],
            );
        }
    }

    $GLOBALS['smarty']->assign('REQUEST_LOG', !empty($smarty_data['request_log']) ? $smarty_data['request_log'] : false);
    $GLOBALS['smarty']->assign('FILTER_STATUS', $sc);
    $GLOBALS['smarty']->assign('FILTER_ERRORS_ONLY', $errs_only);
    $GLOBALS['smarty']->assign('FILTER_ACTIVE', ($q !== '' || $sc !== '' || $errs_only));
    $GLOBALS['smarty']->assign('TOTAL_RESULTS', $count);
    $GLOBALS['smarty']->assign('PAGINATION_REQUEST_LOG', $GLOBALS['db']->pagination($count, $per_page, $page, 5, 'page'));

    // Retention banner — show what r_request is configured to.
    $log_days = $GLOBALS['config']->get('config', 'r_request');
    $GLOBALS['smarty']->assign('REQUEST_LOG_RETENTION_DAYS', (ctype_digit((string)$log_days) && $log_days > 0) ? (int)$log_days : 0);
}
$page_content = $GLOBALS['smarty']->fetch('templates/settings.requestlog.php');
