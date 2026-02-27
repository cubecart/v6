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
    '3774' => 'Apply 50,000 URL limit to sitemap(s), introduce sitemapindex and robots.txt',
    '3745' => 'Show WHERE SEO path is in use',
    '3772' => 'Attachments for contact form (may require skin update)',
    '3804' => 'Minify temporary HTML in print files e.g. order',
	'3803' => 'Print orders to last longer than being deleted immediately',
    '3791' => 'Mailing List Emails to Indicate Status',
    '3800' => 'Print Invoice needs access to display column of CubeCart_tax_details ',
    '3790' => 'Sent Cookies in Debug Output',
    '3747' => 'Function set_cookie() to Handle Session Cookies'
);
$security = array();
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, 49, '', $security);