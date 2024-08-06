<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
$GLOBALS['main']->addTabControl($lang['settings']['release_notes'], 'general');
$GLOBALS['gui']->addBreadcrumb($lang['settings']['release_notes'], currentPage(array('node')), true);
$features = array( 
	'3622' => 'Security: Password reset SQL injection vulnerability & misc. improvements.',
    '3591' => 'Store credit: Allows customers to used a stored amount of credit. Requires skin changes if Foundation skin isn\'t used.',
    '3620' => 'Elasticsearch: Feature to only index items that are in stock.',
    '3605' => 'Default Gravatar changed to person silhouette.',
    '3603' => 'Customer group discount by category.',
    '3599' => 'Foundation skin to have styles related products on checkout.',
    '3595' => 'Promotional codes to be restricted by category.',
    '3595' => 'Anonymous reviews for unauthenticated customers.'
);
$security = array(3622);
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, 45, '', $security);
?>