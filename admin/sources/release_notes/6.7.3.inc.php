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
    <p>CubeCart 6.7.3 is a security release addressing <a href="https://github.com/cubecart/v6/security/advisories/GHSA-747j-4mmc-cj63" target="_blank" rel="noopener">GHSA-747j-4mmc-cj63</a> (CVSS 9.0), an authenticated stored-RCE in the Invoice Editor &rarr; order-print pipeline. Upgrading is strongly recommended for any store with more than one admin account or with a sectioned <code>documents</code>-edit role.</p>
    <p>The fix is applied in three layers: print artefacts are now written as <code>print.&lt;hash&gt;.html</code> instead of <code>.php</code> (so Apache no longer executes them), the <code>files/.htaccess</code> carve-out is updated to match, and raw <code>&lt;?php&nbsp;...&nbsp;?&gt;</code> open tags are stripped both at template save and at render time as defence in depth.</p>
END;

$features = array(
    'GHSA-747j-4mmc-cj63' => 'Stored RCE via Invoice Editor &rarr; order print &mdash; print artefacts now ship as <code>.html</code>, the embedded self-prune PHP block is replaced with inline cleanup before write, raw PHP open tags are stripped at editor save and at render time, and <code>files/.htaccess</code> gains a belt-and-braces <code>FilesMatch</code> that disables PHP handlers for any <code>.php</code>-family extension landing in the directory',
);
$security = array('GHSA-747j-4mmc-cj63');
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, count($features), $notes, $security);
