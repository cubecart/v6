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
    <p>CubeCart 6.8.1 is a maintenance release following <a href="?_g=release_notes&amp;node=6.8.0">6.8.0</a>. There are no database changes and no security fixes in this release.</p>

    <p><strong>Tax-inclusive stores should upgrade.</strong> A rounding correction in 6.8.0 meant a discounted line could be shown at its list price on the receipt, the admin order view and the printed invoice. Worse, saving an order in the admin panel wrote that displayed tax-inclusive figure back into the price column, which stores prices excluding tax, so simply opening and saving an affected order inflated it. Orders already saved that way are not repaired automatically and need correcting by hand.</p>

    <p>The remaining fixes cover the basket quantity limit, search-as-you-type matching, and two Atrium presentation issues.</p>

    <p>If you use Elasticsearch, the search improvement changes how product names are indexed and <strong>only takes effect once the store is reindexed</strong>. Existing indexes keep working unchanged until then.</p>
END;

$features = array(
    '4282' => 'Sale prices are shown as sale prices again on tax-inclusive receipts, admin order views and printed invoices, and the admin order screen no longer writes the tax-inclusive display value back into the price column',
    '4277' => 'The basket quantity limit is raised from 999 to 9999, and a larger quantity is now capped with a message instead of silently resetting to 1',
    '4280' => 'Search-as-you-type matches run-together terms such as "topsoil" against a product named "TOP-SOIL", or "rocksalt" against "Rock Salt" (requires a reindex)',
    '4281' => 'Atrium: modal overlays no longer appear behind the PayPal Commerce Platform buttons, while still sitting below a captcha challenge',
    '4284' => 'Atrium: the product image lightbox no longer opens on small screens, where it added nothing and interfered with swiping between images'
);
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, count($features), $notes);
