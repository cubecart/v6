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
$features = array( 
	'3690' => 'GPSR (General Product Safety Regulation) tools',
    '3815' => 'US Tariffs Integration',
    '2851' => 'Improve print order form layout',
    '3839' => 'Automate dispatch date if not set',
    '3833' => 'Migrate wording from &quot;digital&quot; to &quot;downloadable products&quot;',
    '3819' => 'Foundation *.js optimisations / refactoring'
);
$security = array();
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, 20, '', $security);