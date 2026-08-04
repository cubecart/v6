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
    <p>CubeCart 6.7.6 is a security and maintenance release following <a href="?_g=release_notes&amp;node=6.7.5">6.7.5</a>. It resolves two responsibly-disclosed vulnerabilities &mdash; both residuals of earlier fixes that turned out to be incomplete &mdash; and rolls up a broad set of tax, pricing, multi-domain and admin fixes.</p>
    <p>Both reported vulnerabilities are authenticated (they require an existing back-office session) and were fixed before public disclosure. By running this version your store is now protected against them; if you operate any additional CubeCart stores, make sure they are updated too. See the linked advisories for full details.</p>
    <p>Also included, without individual issue numbers: Elasticsearch fixes for a <code>page=all</code> fatal, stock exclusion and empty documents; a tax fix for inclusive-price subtotals where the customer's VAT rate differs from the store's; retail quantity tiers now kept for customers in a customer group; stable ordering for paginated statistics reports; an SEO fix for double-slash URLs; and dependency updates for the Elasticsearch client.</p>
    <p>The upgrade adds an <code>updated</code> column to <code>CubeCart_saved_cart</code> and an index on <code>CubeCart_search</code>.</p>
END;

$features = array(
    // Security fixes (responsibly disclosed)
    'GHSA-fcv7-88v5-vv5f' => 'Fixed <code>ORDER BY</code> SQL injection in the admin Customers and Orders listings &mdash; string-typed sort expressions are now structurally validated in <code>Database::select()</code> rather than passed through quote-escaping, which does nothing in an <code>ORDER BY</code> context. Completes the fix for GHSA-rm2f-rpcq-6w9f, which hardened only the array form and the single page reported at the time',
    'GHSA-5mr8-hgcv-3pcj' => 'Closed callback-based bypasses of the Smarty security policy for editable email, document and invoice templates. <code>array_walk_recursive</code> and a number of sibling callback sinks were unlisted, and both policy checks compared function names case-sensitively &mdash; so any change of capitalisation bypassed the list entirely. A new <code>$glob[\'smarty_allowed_php\']</code> setting can un-ban a specific function per install where an extension needs one; shell and eval functions are exempt from that override',

    // Fixes and improvements
    '4217' => 'Fixed the HTML minifier un-escaping entities in text nodes, which silently defeated <code>|escape</code> in every skin and let broken markup in a short description leak out of its container',
    '4145' => 'Fixed abandoned-cart reminders being sent to customers who had already completed and paid for their order. Saved carts are now cleared when payment lands rather than only when the shopper returns to the receipt page, and the suppression check is anchored to the cart instead of to session activity',
    '4209' => 'Admin product Categories tab: added a search filter and a &quot;selected only&quot; toggle, and selected categories are now highlighted rather than relying on a small checkbox &mdash; particularly hard to spot in dark mode',
    '4177' => 'Plugins can register their own scheduled tasks via a new <code>class.cron.tasks</code> hook, each appearing in Scheduled Tasks with its own enable toggle, frequency, last run and last result instead of having to share the single code-snippets task',
    '4210' => 'Removed the last remnants of the retired social links so Vimeo and others can be cleared properly',
    '4208' => 'Admin dashboard: added &quot;vs same period last month&quot; comparisons',
    '4207' => 'Fixed the white screen and error-log entry when filtering Users Online by &quot;Display Customers Only&quot;',
    '4206' => 'Fixed the &quot;recent extensions&quot; notification not clearing for stores west of UTC',
    '4205' => 'Fixed &quot;Lock wait timeout&quot; errors on the <code>CubeCart_search</code> hit counter',
    '4204' => 'Fixed 404 log &quot;Duplicate entry&quot; errors under concurrent requests',
    '4203' => 'Featured Product box no longer uses <code>ORDER BY RAND()</code>, using a random primary-key pivot instead &mdash; a significant improvement on large catalogues',
    '4202' => 'Orphaned <code>product_id = 0</code> images no longer appear on the Add Product page',
    '4201' => 'Fixed CSS minification failures on PHP 8 with modern CSS features, and preserved whitespace in non-base64 data URLs',
    '4200' => 'Product review JSON-LD no longer hardcodes <code>ratingValue</code> to 5',
    '4199' => 'Document editor now allows external videos via the iframe allowlist',
    '4197' => 'Fixed remaining tax-inclusive rounding issues and inclusive totals are now shown on order receipts',
    '4193' => 'Fixed being unable to remove a digital download file from a product',
    '4190' => 'File manager no longer reports &quot;Not enough memory to create thumbnail&quot; for non-image files in <code>images/source</code>',
    '4187' => 'Fixed tariff code charging both physical and digital products',
    '4186' => 'Fixed tariff display issues',
    '4185' => 'Fixed being logged out of the admin after a short time',
    '4182' => 'Fixed PHP 8 fatal errors in the product CSV import',
    '4181' => 'Fixed <code>getProductPrice()</code> applying percentage group/category discounts twice on category listings',
    '4180' => 'Customer group pricing at product level now accepts 0.00 and empty values',
    '4173' => 'Restored multi-domain / per-language subdomain stores broken by store URL pinning',
    '4172' => 'Fixed domain-based language selection broken by store URL pinning',
    '4171' => 'Fixed the tax/tariff line missing from totals when a zero-value inherited tax is present',
    '4170' => 'Plugins providing a <code>gateway.class.php</code> now show allowed/disabled zones',
    '4168' => 'Foundation: improved SEO and social tags in <code>element.meta.php</code>'
);
$security = array('GHSA-fcv7-88v5-vv5f', 'GHSA-5mr8-hgcv-3pcj');
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, 8, $notes, $security);
