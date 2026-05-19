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
Admin::getInstance()->permissions('maintenance', CC_PERM_READ, true);

global $lang, $glob;

// Already on CubeCart's own hosting — this page doesn't apply
if (isCubeCartHosting()) {
    httpredir('?');
}

// Bust the session-cached result if the admin asked for a fresh check
if (isset($_GET['refresh'])) {
    $GLOBALS['session']->delete('status', 'performance');
    httpredir('?_g=performance');
}

// Force the Performance status to be (re)computed and assigned to Smarty
$GLOBALS['session']->delete('status', 'performance');
$GLOBALS['main']->showPerformance();

$GLOBALS['gui']->addBreadcrumb('Performance', '?_g=performance', true);
$GLOBALS['main']->addTabControl('Performance', 'performance');

$page_content = $GLOBALS['smarty']->fetch('templates/performance.index.php');
