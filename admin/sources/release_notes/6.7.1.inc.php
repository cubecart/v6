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
$GLOBALS['main']->addTabControl($lang['settings']['release_notes'], 'general');
$GLOBALS['gui']->addBreadcrumb($lang['settings']['release_notes'], currentPage(array('node')), true);

$notes = <<<END
    <p>CubeCart 6.7.1 is a maintenance release with bug fixes and minor refinements following the <a href="?_g=release_notes&amp;node=6.7.0">6.7.0</a> launch.</p>
END;

$features = array(
    '4107' => 'Fatal in Debug::writeSystemErrorLog during setup when the Config singleton was not yet wired',
);
$security = array();
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, count($features), $notes, $security);
