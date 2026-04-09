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
    <p>CubeCart 6.6.1 is a maintenance release focused on Smarty template security and post-upgrade stability fixes.</p>
END;

$features = array(
    // Security / Smarty
    '3987' => 'Smarty security flipped from allow-list to block-list for dangerous functions',
    '3982' => 'Trusted file path for local JavaScript includes (./js/*)',
    '3983' => 'Extensions can now register their own trusted Smarty include paths',
    '3985' => 'Restored the ucfirst Smarty modifier blocked by security settings',
    '3988' => 'Module template paths whitelisted in Smarty security policy',
    // Fixes
    '3989' => 'Abandoned-cart emails &mdash; max age cutoff and one email per cart limit added by default',
    '3984' => 'Version Checker &ldquo;Upgrade Now&rdquo; link corrected',
    '3990' => 'Extension upgrade button only active when the version has actually changed',
);
$security = array('3987', '3982', '3983', '3985', '3988');
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, 8, $notes, $security);
