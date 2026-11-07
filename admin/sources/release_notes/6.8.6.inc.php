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
    <p>CubeCart 6.8.6 follows <a href="?_g=release_notes&amp;node=6.8.5">6.8.5</a>. It fixes two security vulnerabilities in the REST API and the problems found since 6.8.5, including a visitor's first Add to Basket potentially failing with a security alert on older skins. <strong>Every store should upgrade.</strong></p>
END;

$features = array(
    // Security fixes: high
    'GHSA-9r66-r93r-w356' => 'An API key can no longer do more than the admin who owns it. Its permissions are capped at its creator\'s own, every API request is checked against what the owner holds now, and only a super user can create, edit, regenerate or delete another admin\'s keys',

    // Security fixes: medium
    'GHSA-fc6p-gcvx-cqfc' => 'The REST API\'s Settings endpoint no longer returns the SMTP password, the reCAPTCHA secret key, the feed access key or the admin folder name, and a key\'s IP allow-list can no longer be passed with a forged X-Forwarded-For header',

    // Fixes and improvements
    '4333' => 'A visitor\'s first Add to Basket, or any other form on their first page, no longer fails with a CSRF security alert',
    '4332' => 'Signing in to the admin on a second device no longer signs the first one out. Changing the password still signs out every other session',
    '4329' => 'Quantity discounts: guests no longer see customer group prices in the discount table, and customer group members get the retail tiers again',
    '4331' => 'A product\'s minimum and maximum quantity are enforced in the basket as well as on the product page, counting what is already in the basket',
    '4718' => 'Marketplace updates match extensions by id, folder and type rather than by name, so renaming an extension no longer strands older installs, and extensions from other developers update automatically only when that is switched on',
    '4719' => 'Extensions are marked 3rd party from the marketplace\'s official flag rather than the creator\'s name',
    '4720' => 'The support chat no longer sends the admin\'s email address to cubecart.com to check for a paid support plan',
    '4335' => 'Saving a product keeps &lt;center&gt; in its description, so centred text and video trailers stay centred',
    '4338' => 'Reviews added in the admin are dated, so they show a date and sort with the rest',
    '4339' => 'Upgrading adds the Japan, India, Brazil and Argentina zones missing from stores first installed before 6.2.1',
    '4337' => 'Atrium and Foundation: the product page\'s structured data for search engines carries each review\'s text instead of a stray quote mark',
    '4590' => 'Atrium: Complete Payment on My Orders resumes the unpaid order, and Re-order refills the basket',
    '4264' => 'Atrium and Foundation: description images saved before 6.7.0 keep their proportions on phones',
    '4613' => 'Switching skin with ?select_skin= on a search-friendly URL returns to that page instead of redirecting to itself',
    '4340' => 'Back office messages close only from their X, so their text can be selected and copied',
    '4336' => 'Dashboard news matches the other back office messages, with a bullhorn icon and a readable link in dark mode',
    '4330' => 'Admin dark mode: description editors keep readable text on their white background',
    '4716' => 'includes/global.inc.php-dist documents block_thumbs and describes es_is correctly, and the hosting backup note says backups are daily',
    '4721' => 'Minifying JavaScript no longer raises a PHP deprecation when a script ends inside a comment or string'
);
$security = array_values(array_filter(array_keys($features), function ($id) { return strpos($id, 'GHSA-') === 0; }));
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, count($features) - count($security), $notes, $security);
