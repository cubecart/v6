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
Admin::getInstance()->permissions('maintenance', CC_PERM_FULL, true);

global $lang, $glob;

if (isset($_POST['execute'])) {
    $raw_query = isset($GLOBALS['RAW']['POST']['query']) ? $GLOBALS['RAW']['POST']['query'] : '';
    if (!empty($raw_query)) {
        if (strstr($raw_query, '; #EOQ')) {
            $db->parseSchema($raw_query);
        } else {
            $GLOBALS['db']->query($raw_query, false);
        }
        if ($GLOBALS['db']->error()) {
            $GLOBALS['main']->errorMessage($GLOBALS['db']->errorInfo());
        } else {
            $affected = (int)$GLOBALS['db']->affected();
            $GLOBALS['main']->successMessage($affected > 0 ? $lang['maintain']['affected_rows'].': '.$affected : $lang['maintain']['query_executed']);
        }
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['no_query_entered']);
    }
} else {
    $GLOBALS['main']->errorMessage($lang['maintain']['expert_use_only']);
}

$GLOBALS['main']->addTabControl($lang['maintain']['tab_query_sql'], 'general');
$GLOBALS['gui']->addBreadcrumb($lang['maintain']['tab_query_sql']);

$GLOBALS['smarty']->assign('INFO', sprintf($lang['maintain']['title_db_info'], $GLOBALS['db']->serverVersion(), $glob['dbhost'], $glob['dbusername'], $glob['dbhost']));
$prefix = (!$GLOBALS['config']->isEmpty('config', 'dbprefix')) ? $GLOBALS['config']->get('config', 'dbprefix') : false;
$GLOBALS['smarty']->assign('PREFIX', $prefix);
if (!empty($raw_query)) {
    $GLOBALS['smarty']->assign('VAL_QUERY', htmlspecialchars($raw_query, ENT_QUOTES, 'UTF-8'));
}
$page_content = $GLOBALS['smarty']->fetch('templates/maintenance.sql.php');
