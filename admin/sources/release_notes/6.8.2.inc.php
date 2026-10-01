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
    <p>CubeCart 6.8.2 is a maintenance release following <a href="?_g=release_notes&amp;node=6.8.1">6.8.1</a>. There are no database changes and no security fixes in this release.</p>

    <p><strong>Stores running older third-party discount extensions should upgrade.</strong> If an extension saved a coupon record without an <em>include</em> key, the storefront stopped with a fatal error on PHP 8 and returned a blank page. The failure was not limited to the basket: once triggered it affected every page for that visitor, and nothing was written to the error log, so it was easy to mistake for a hosting fault. The store now ignores the missing key and carries on.</p>

    <p>Two checkout and account fixes follow. An abandoned cart reminder could be sent to a customer who had already paid, in gateway flows where the request that takes payment keeps running after the receipt has cleared the basket. Separately, the duplicate-email check made during registration could read a cached query result and miss an account created moments earlier, allowing a second account on the same address.</p>

    <p>The remaining five fixes are all in the Atrium skin, and two of them add new settings. If you have copied Atrium to make your own skin, those settings live in the skin's <em>config.xml</em> and will need adding to your copy before they appear.</p>
END;

$features = array(
    '4286' => 'The storefront no longer returns a blank page across the whole site when a third-party extension writes a coupon record without an "include" key, which was a fatal error on PHP 8',
    '4291' => 'The abandoned cart reminder is no longer sent after a successful payment, in checkout flows where the paying request continues after the receipt has cleared the basket',
    '4292' => 'Registering with an email address already on file no longer risks creating a duplicate customer account, as the duplicate check now bypasses the query cache',
    '4293' => 'Atrium: the Price Each and Price values in the basket are shown the right way round',
    '4287' => 'Atrium: a new skin setting chooses whether catalogue thumbnails are cropped square or shown whole',
    '4290' => 'Atrium: a new skin setting controls the size of the header logo',
    '4289' => 'Atrium: long category flyout menus stay on screen and can be scrolled, instead of running off the bottom of the page',
    '4288' => 'Atrium: a deep link to a product tab, such as the one offered by "Quantity discounts available", now opens that tab'
);
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, count($features), $notes);
