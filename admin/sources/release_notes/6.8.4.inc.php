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
    <p>CubeCart 6.8.4 is a maintenance release following <a href="?_g=release_notes&amp;node=6.8.3">6.8.3</a>. There is one database change, which adds a <em>List until</em> date to products, and no security fixes in this release.</p>

    <p><strong>Stores that take guest orders with a visible captcha should upgrade.</strong> That covers the reCAPTCHA v2 checkbox, hCaptcha and Cloudflare Turnstile. After a guest entered their details, the checkout asked for the captcha again but no longer showed it, so the order stopped with "The verification code was incorrect". Logged-in customers and the invisible reCAPTCHA were not affected.</p>

    <p><strong>Stores whose shipping prices include tax should also upgrade.</strong> Where a tax rate applied to goods but not to shipping, or to shipping but not to goods, shipping was charged too little or too much by that rate. Orders already placed keep the amount they were charged.</p>

    <p>Products gain a <em>List until</em> date, the counterpart to <em>List from</em>: once it passes, the product leaves category listings and search. Thanks to Simon-3ft, who contributed the original change.</p>

    <p>The remaining fixes cover import tariffs on tax-inclusive orders, product specifications, the product statistics page, PHP 8.5 and the Atrium mobile menu. If you have copied Atrium to make your own skin, the mobile menu fix is in <em>templates/element.navigation_tree.php</em> and the skin's CSS, and needs copying across.</p>
END;

$features = array(
    '4298' => 'Guest customers are no longer asked to solve the captcha a second time when they confirm the order, which with a visible captcha stopped the order with "The verification code was incorrect"',
    '4297' => 'Tax is removed from tax-inclusive shipping prices using the tax rate\'s Shipping setting rather than its Goods setting, so shipping is no longer charged too little or too much when the two differ',
    '4300' => 'Import tariffs are no longer added into the subtotal shown on tax-inclusive order pages, order emails, the admin order view and printed invoices. The amount charged was always correct',
    '4303' => 'Product specifications containing characters such as &times;, &deg; or curly quotes are saved, instead of wiping every specification on the product. Specifications already saved with &times; or &deg; are recovered',
    '4304' => 'The variant table on a product\'s statistics page lists every variant sold instead of only the top five, labels each one by its product code or options, and adds together sales recorded before and after the product\'s options were changed',
    '4305' => 'Products have a new List until date, after which they leave category listings and search',
    '4299' => 'When the admin session expires with Statistics open, the page reloads to the login screen instead of showing the login form inside the Users Online tab',
    '4301' => 'PHP 8.5 no longer reports deprecation notices from the cURL, file information and image functions, and stores upgrading from before 6.4.8 now get the SEO URLs that version\'s upgrade step adds',
    '4302' => 'Atrium: in the mobile menu, tapping the arrow beside a category opens its subcategories instead of loading the category, and the menu no longer opens and closes as a mouse passes over it'
);
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, count($features), $notes);
