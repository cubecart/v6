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
    <p>CubeCart 6.6.3 brings stronger password security with Argon2id, a substantially improved dashboard, smarter Elasticsearch search and indexing, and a wide range of bug fixes and refinements.</p>
END;

$features = array(
    // Critical / Security
    '4040' => 'Password hashing upgraded to Argon2id with transparent on-login migration',
    '4010' => 'Gift certificate not clearing the balance due to an inclusive-tax rounding error',
    '4042' => 'Image cache no longer writes malformed ".500" files when the source filename lacks an extension',
    '4007' => 'HTML tags stripped from error messages before writing to the system error log',
    // Improvements
    '4032' => 'Dashboard overhaul: extended date range, abandoned cart stats, news & announcements, version notice and progress widgets',
    '4022' => 'Elasticsearch: improved indexing strategy and search relevance',
    '4023' => 'Elasticsearch: reindex now uses the bulk API',
    '4021' => 'Elasticsearch: search considers a product\'s primary category and boosts product name',
    '4024' => 'Elasticsearch: name and product_name consolidated into a single multi-field mapping',
    '4025' => 'Elasticsearch: NaN count on the indexer page fixed',
    '4026' => 'Elasticsearch: category included in search-as-you-type results',
    '4017' => 'New Extensions dashboard tab with per-admin seen state',
    '4019' => 'Installed extensions now show their enabled / disabled state',
    '4020' => 'Extensions can be toggled on or off from the listing',
    '4018' => 'Order status change emails now include full order data (billing, shipping, products and taxes)',
    '4039' => 'SEO::rewriteUrls refactored away from fragile regex-on-HTML for single-URL processing',
    '4011' => 'Sale price column added to the product list',
    '4008' => 'Sales report can now be filtered by product',
    '4013' => 'Add Customer flow improvements',
    '4035' => 'Check for language pack version updates',
    '4036' => 'Stay on the same tab when installing a language',
    '4029' => 'Tabs layout improved on smaller viewports',
    '4030' => 'Clear Cache button repositioned for better use of space',
    '4031' => 'Documentation link consistently shown when applicable',
    '4033' => 'Date range picker on product stats fixed',
    '4034' => 'Joyride cache positioning fixed',
    '4006' => 'Bestseller stats query optimised and composite indexes added',
    '3998' => 'Further improvements to bulk price processing, including multi-select on options',
    // Fixes
    '4038' => 'Erroneous seo_path on URLs such as /shop/product-name.html?seo_path=product-name.html',
    '4028' => 'Added UNIQUE KEY to CubeCart_order_summary.custom_oid (empty values converted to NULL first)',
    '4027' => 'Duplicate index warnings no longer raise false positives',
    '4009' => 'CubeCart_email_log table is now created on fresh install',
    '4005' => 'PHP deprecation warnings cleared',
);
$security = array();
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, 33, $notes, $security);
