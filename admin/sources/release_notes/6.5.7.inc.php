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
	'3702' => 'Cloudflare Turnstile - A new captcha alternative.',
    '3699' => 'Bluesky social link support.',
    '3685' => 'Optimisation of cookie consent logging. Saves up to 56% disk space.',
    '3681 ' => 'Back office copy to clipboard functionality to quickly copy email address etc...',
    '3673' => 'Option to filter fields queried by search. Product name, product code &amp; description.',
    '3661' => 'Show remaining character limit for SEO title and description.',
    '3657' => 'Limit discount code to type of shipping method. e.g. Not for free shipping',
    '3652' => 'Mobile column added to sales reporting.',
    '3643' => 'Sort added to Elasticsearch results.',
    '3632' => 'Enable/Disable newsletter signup.',
    '3629' => 'muteUndefinedOrNullWarnings alias added for backward compatibility of older 3rd party extensions.'
);
$security = array();
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, 60, '', $security);
?>