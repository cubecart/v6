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

/**
 * Map a PHP-style error message to a severity slug used for badges + filtering.
 * Recognises the common prefixes that PHP and CubeCart's error logger emit.
 */
if (!function_exists('_errorLogSeverity')) {
    function _errorLogSeverity($message) {
        // CubeCart's logger writes `[Severity] ...`; PHP's native handler uses
        // `Severity: ...`. Pull out whichever prefix is present.
        $m = strtolower(ltrim((string)$message));
        if (preg_match('/^\[([a-z ]+)\]/', $m, $brk)) {
            $token = trim($brk[1]);
        } elseif (preg_match('/^([a-z ]+?)(?:\s*error)?\s*:/', $m, $col)) {
            $token = trim($col[1]);
        } else {
            // No structured prefix; scan the whole leading word.
            $token = (string)strtok($m, " :\t");
        }
        if (strpos($token, 'fatal') !== false) return 'fatal';
        if (strpos($token, 'parse') !== false) return 'parse';
        if (strpos($token, 'uncaught') !== false || strpos($token, 'exception') !== false) return 'exception';
        if ($token === 'error' || strpos($token, 'recoverable') !== false) return 'error';
        if (strpos($token, 'warning') !== false) return 'warning';
        if (strpos($token, 'deprecated') !== false) return 'deprecated';
        if (strpos($token, 'notice') !== false || strpos($token, 'strict') !== false) return 'notice';
        return 'other';
    }
}
$severity_class = array(
    'fatal'      => 'red',
    'parse'      => 'red',
    'exception'  => 'red',
    'error'      => 'red',
    'warning'    => 'orange',
    'deprecated' => 'yellow',
    'notice'     => 'yellow',
    'other'      => 'gray',
);

// Filter inputs (search + severity), persisted in session per scope.
foreach (array('admin', 'system') as $scope) {
    $key_q = 'errorlog_'.$scope.'_q';
    $key_s = 'errorlog_'.$scope.'_severity';
    if (isset($_POST['filter_scope']) && $_POST['filter_scope'] === $scope) {
        $q = isset($_POST['q']) ? trim((string)$_POST['q']) : '';
        $sev = isset($_POST['severity']) ? trim((string)$_POST['severity']) : '';
        if ($q === '') {
            $GLOBALS['session']->delete($key_q);
        } else {
            $GLOBALS['session']->set($key_q, $q);
        }
        if ($sev === '' || !isset($severity_class[$sev])) {
            $GLOBALS['session']->delete($key_s);
        } else {
            $GLOBALS['session']->set($key_s, $sev);
        }
    }
}
if (isset($_GET['reset_filter']) && in_array($_GET['reset_filter'], array('admin', 'system'), true)) {
    $GLOBALS['session']->delete('errorlog_'.$_GET['reset_filter'].'_q');
    $GLOBALS['session']->delete('errorlog_'.$_GET['reset_filter'].'_severity');
    httpredir('?_g=settings&node=errorlog');
}

// Bulk actions: mark read/unread/delete. Single SQL per action thanks to the
// hash-deduped schema — one row per unique error.
$pfx = $GLOBALS['config']->get('config', 'dbprefix');
foreach (array(
    'adminread'  => array('table' => 'CubeCart_admin_error_log',  'status_field' => 'admin_error_status'),
    'systemread' => array('table' => 'CubeCart_system_error_log', 'status_field' => 'system_error_status'),
) as $post_key => $info) {
    if (!isset($_POST[$post_key]) || !is_array($_POST[$post_key])) continue;
    $ids = array();
    foreach ($_POST[$post_key] as $id) {
        if (is_numeric($id)) $ids[] = (int)$id;
    }
    if (empty($ids)) continue;
    $id_list = implode(',', $ids);

    $action = isset($_POST['bulk_action']) ? $_POST['bulk_action'] : (isset($_POST[$info['status_field']]) ? 'status' : '');
    $changed = false;
    if ($action === 'delete') {
        $GLOBALS['db']->delete($info['table'], '`log_id` IN ('.$id_list.')');
        $changed = $GLOBALS['db']->affected() > 0;
        if ($changed) $GLOBALS['main']->successMessage($lang['settings']['message_deleted']);
    } else {
        $status = isset($_POST[$info['status_field']]) ? ((int)$_POST[$info['status_field']] === 1 ? 1 : 0) : 1;
        $GLOBALS['db']->update($info['table'], array('read' => $status), '`log_id` IN ('.$id_list.')');
        $changed = $GLOBALS['db']->affected() > 0;
        if ($changed) {
            $GLOBALS['main']->successMessage($status ? $lang['settings']['message_marked_read'] : $lang['settings']['message_marked_unread']);
        }
    }
    if (!$changed) {
        $GLOBALS['main']->errorMessage($lang['settings']['changes_not_made']);
    }
}

/**
 * Build a filtered listing for one of the error-log tables. Rows are already
 * deduped at write time via a unique index on `message_hash` — `occurrences`
 * and `time` (last-seen) / `first_time` are maintained by the writer's
 * INSERT … ON DUPLICATE KEY UPDATE path.
 */
function _errorLogFetch($table, $base_where, $q, $severity_filter, $per_page, $page, $with_extras) {
    global $pfx, $severity_class;
    $page = max(1, (int)$page);
    $per_page = (int)$per_page;
    $where = $base_where ? array($base_where) : array();
    if ($q !== '') {
        $where[] = "`message` LIKE '%".$GLOBALS['db']->sqlSafe($q)."%'";
    }
    $where_sql = !empty($where) ? 'WHERE '.implode(' AND ', $where) : '';

    $cols = '`log_id`, `message`, `time`, `first_time`, `occurrences`, `read`';
    if ($with_extras) {
        $cols .= ', `url`, `backtrace`';
    }
    $sql = sprintf(
        'SELECT %s FROM `%s%s` %s ORDER BY `time` DESC LIMIT %d OFFSET %d',
        $cols, $pfx, $table, $where_sql, $per_page, ($page - 1) * $per_page
    );
    $rows = $GLOBALS['db']->misc($sql, false);

    $count_sql = sprintf('SELECT COUNT(*) AS `c` FROM `%s%s` %s', $pfx, $table, $where_sql);
    $count_row = $GLOBALS['db']->misc($count_sql, false);
    $total = (is_array($count_row) && isset($count_row[0]['c'])) ? (int)$count_row[0]['c'] : 0;

    if (!is_array($rows)) {
        return array(array(), $total);
    }

    $out = array();
    foreach ($rows as $row) {
        $sev = _errorLogSeverity($row['message']);
        if ($severity_filter !== '' && $sev !== $severity_filter) {
            continue;
        }
        $entry = array(
            'log_id'         => (int)$row['log_id'],
            'time'           => formatTime($row['time']),
            'first_time'     => formatTime($row['first_time']),
            'message'        => $row['message'],
            'occurrences'    => (int)$row['occurrences'],
            'read'           => (int)$row['read'],
            'severity'       => $sev,
            'severity_class' => isset($severity_class[$sev]) ? $severity_class[$sev] : 'gray',
            'style'          => $row['read'] ? '' : 'style="font-weight: bold"',
        );
        if ($with_extras) {
            $entry['url']       = $row['url'];
            $entry['backtrace'] = $row['backtrace'];
        }
        $out[] = $entry;
    }
    return array($out, $total);
}

$per_page = 25;
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;

// Severity dropdown options exposed to templates.
$severity_opts = array();
foreach (array('', 'fatal', 'parse', 'exception', 'error', 'warning', 'deprecated', 'notice', 'other') as $s) {
    $severity_opts[] = array('value' => $s, 'label' => ($s === '' ? '—' : ucfirst($s)));
}
$GLOBALS['smarty']->assign('SEVERITY_OPTIONS', $severity_opts);

// Admin error log
$admin_q   = (string)$GLOBALS['session']->get('errorlog_admin_q');
$admin_sev = (string)$GLOBALS['session']->get('errorlog_admin_severity');
$count_unread = $GLOBALS['db']->count('CubeCart_admin_error_log', 'log_id', array('admin_id' => Admin::getInstance()->get('admin_id'), 'read' => '0'));
$GLOBALS['main']->addTabControl($lang['settings']['title_admin_error_log'], 'admin_error_log', null, null, $count_unread);
$GLOBALS['gui']->addBreadcrumb($lang['settings']['title_admin_error_log'], currentPage());

list($admin_log, $admin_total) = _errorLogFetch(
    'CubeCart_admin_error_log',
    '`admin_id` = '.(int)Admin::getInstance()->get('admin_id'),
    $admin_q, $admin_sev, $per_page, $page, false
);
if (!empty($admin_log)) {
    $GLOBALS['smarty']->assign('ADMIN_ERROR_LOG', $admin_log);
}
$GLOBALS['smarty']->assign('ADMIN_FILTER', array('q' => $admin_q, 'severity' => $admin_sev));
$GLOBALS['smarty']->assign('PAGINATION_ADMIN_ERROR_LOG', $GLOBALS['db']->pagination($admin_total, $per_page, $page, 5, 'page', 'admin_error_log'));

// System error log (super-user only)
if (Admin::getInstance()->superUser()) {
    $sys_q   = (string)$GLOBALS['session']->get('errorlog_system_q');
    $sys_sev = (string)$GLOBALS['session']->get('errorlog_system_severity');
    $count_unread = $GLOBALS['db']->count('CubeCart_system_error_log', 'log_id', array('read' => '0'));
    $GLOBALS['main']->addTabControl($lang['settings']['title_system_error_log'], 'system_error_log', null, null, $count_unread);

    list($sys_log, $sys_total) = _errorLogFetch(
        'CubeCart_system_error_log', '', $sys_q, $sys_sev, $per_page, $page, true
    );
    if (!empty($sys_log)) {
        $GLOBALS['smarty']->assign('SYSTEM_ERROR_LOG', $sys_log);
    }
    $GLOBALS['smarty']->assign('SYSTEM_FILTER', array('q' => $sys_q, 'severity' => $sys_sev));
    $GLOBALS['smarty']->assign('PAGINATION_SYSTEM_ERROR_LOG', $GLOBALS['db']->pagination($sys_total, $per_page, $page, 5, 'page', 'system_error_log'));
}
$page_content = $GLOBALS['smarty']->fetch('templates/settings.errorlog.php');
