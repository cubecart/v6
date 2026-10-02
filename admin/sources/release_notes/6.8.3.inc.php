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
    <p>CubeCart 6.8.3 is a maintenance release following <a href="?_g=release_notes&amp;node=6.8.2">6.8.2</a>. There are no database changes and no security fixes in this release.</p>

    <p><strong>Stores using quantity or group pricing should upgrade.</strong> If a customer changed the quantity mid-checkout and crossed a price break, the order line kept the old unit price, so invoices and order emails showed a line total that disagreed with the subtotal beneath it. The amount charged was always correct. Orders already placed need correcting by hand.</p>
END;

$features = array(
    '4294' => 'Changing the quantity before payment now rewrites the unit price, so a quantity that crosses a quantity or group pricing break is priced at the correct tier'
);
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, count($features), $notes);
