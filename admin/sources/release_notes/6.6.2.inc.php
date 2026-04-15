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
    <p>CubeCart 6.6.2 is a maintenance release with improvements to config reliability, bulk pricing, and several UI fixes.</p>
END;

$features = array(
    // Critical
    '4004' => 'Fixed config values silently lost due to non-atomic DELETE + INSERT in NVP config writer',
    '4003' => 'Skin setting no longer permanently overwritten by fallback logic',
    // Improvements
    '4000' => 'Error reporting restricted to fatal errors outside debug mode',
    '3998' => 'Bulk price update can now update customer group pricing',
    // Fixes
    '3996' => 'Verify column extended to VARCHAR(64) for customers and admin users',
    '3995' => 'CSS formatting fixes',
    '3993' => 'Extension install redirect no longer forces page change',
);
$security = array();
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, 7, $notes, $security);
