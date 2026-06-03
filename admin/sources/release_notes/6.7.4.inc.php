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
    <p>CubeCart 6.7.4 is a feature and maintenance release following <a href="?_g=release_notes&amp;node=6.7.3">6.7.3</a>. Headline additions are a customer <strong>Right of Withdrawal</strong> workflow, an in-admin support / live-chat widget for active subscribers, a refactored REST API, a dedicated CLI cron runner, and order-basket storage compression. It also rolls up a broad set of statistics, abandoned-cart and admin UX refinements.</p>
    <p>The upgrade adds the <code>CubeCart_withdrawal_requests</code> table, compresses historic non-Pending order baskets in the background (<code>setup/scripts/upgrade/6.7.4.php</code>), and imports the three new withdrawal email templates across every shipped language.</p>
END;

$features = array(
    // Major features
    '4152' => 'EU Right of Withdrawal &mdash; customers can submit a statutory cancellation/return request against an order from the order list and receipt. New admin management screen under <em>Customers &rarr; Withdrawal Requests</em> with new/accepted/rejected/refunded states, acknowledgement and decision emails, and a new <code>CubeCart_withdrawal_requests</code> table',
    '4143' => 'Support &amp; live-chat widget in admin',
    '4134' => 'REST API refactor &mdash; phased rework of <code>api.php</code> for cleaner routing and request handling',
    '4135' => 'REST API refactor phase 2',
    '4136' => 'REST API refactor phase 3',
    '4137' => 'REST API refactor &mdash; final tidy and consolidation',
    '4133' => 'Dedicated CLI cron runner &mdash; new <code>cli/cron.php</code> entry point with a <code>cli/.htaccess</code> that denies direct web access; cron dispatcher logic hardened',
    '4130' => 'Order basket compression &mdash; <code>CubeCart_order_summary.basket</code> is now stored gzip-compressed (column widened to <code>MEDIUMBLOB</code>). The upgrade backfills historic non-Pending orders in batches; Pending orders stay uncompressed and compress on their next status change',

    // Statistics
    '4125' => 'Statistics can now be reported by accounting reference date as well as order date',
    '4138' => 'Statistics: best-selling products period selectable by month',
    '4139' => 'Statistics: chart sizing fix',
    '4129' => 'Statistics: bot/crawler visits separated from genuine traffic in visitor stats',

    // Admin UX
    '4154' => 'Plugin/extension auto-updates surfaced on the dashboard and extensions page',
    '4147' => 'Default admin skin &mdash; the redundant <code>admin_skin</code> config setting is removed and the bundled default skin is always used',
    '4150' => 'Sticky save bar on long admin forms',
    '4151' => 'Contact form handling tidied',
    '4155' => 'Page-break fixes on admin index listings (customers, orders, products, dashboard)',
    '4156' => 'Product export: digital download parts handling',
    '4144' => 'CKEditor &ldquo;show protected&rdquo; plugin gains inline SVG icons for Smarty <code>{if}</code>/<code>{else}</code>, loop, variable and unknown tags',

    // Storefront
    '4157' => 'Checkout: hidden-field handling corrected on the Foundation checkout template',
    '4145' => 'Abandoned cart: stability improvements to the cron-driven recovery flow',
    '4146' => 'Abandoned cart: additional test coverage',

    // Reliability / PHP 8 hardening
    '4123' => '<code>array_merge(): Argument #2</code> warning fixed',
    '4126' => '<code>@ob_end_flush() === false</code> guard to avoid output-buffer warnings',
    '4141' => 'Smarty <code>MuteExpectedErrors</code> handler added to suppress expected template filesystem notices',
    '4142' => 'Graceful handling when the visitor has cookies blocked',
    '4127' => 'Preserve the configured <code>adminFolder</code> / <code>adminFile</code> through the request lifecycle',
    '4128' => 'Elasticsearch / Searchly handler robustness',
    '4131' => 'Admin listing pagination fix',
    '4148' => 'Session/cron timing tightened to a one-hour window',
    '4149' => 'Removed deprecated API usage',
    'misc'  => 'Misc error fixes across admin and storefront',
);
$security = array();
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, 33, $notes, $security);
