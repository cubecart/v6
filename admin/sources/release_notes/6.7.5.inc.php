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
    <p>CubeCart 6.7.5 is a security and maintenance release following <a href="?_g=release_notes&amp;node=6.7.4">6.7.4</a>. It resolves a batch of responsibly-disclosed vulnerabilities &mdash; two admin SQL injections, three broken-access-control / CSRF issues in the order and customer tools, and two cross-site scripting flaws &mdash; alongside a set of database and PHP 8 maintenance fixes.</p>
    <p>The reported vulnerabilities are all authenticated (they require an existing admin session) and were fixed before public disclosure. By running this version your store is now protected against them; if you operate any additional CubeCart stores, make sure they are updated too. See the linked advisories for full details.</p>
END;

$features = array(
    // Security fixes (responsibly disclosed)
    'GHSA-qcx6-cg43-ffmx' => 'Fixed SQL identifier injection in the database maintenance tools &mdash; submitted table names are now validated against the live database and safely quoted before use in <code>CHECK</code>/<code>ANALYZE</code>/<code>ALTER TABLE</code> statements',
    'GHSA-hvmw-v8gc-4c29' => 'Fixed SQL injection via the <code>download_expire</code> value when updating existing downloads from store settings',
    'GHSA-43f6-gfcf-wj9c' => 'Fixed stored XSS in product description fields &mdash; rich-text content is now passed through an allowlist HTML sanitiser that strips scripts, event handlers and unsafe URI schemes while preserving formatting',
    'GHSA-v55x-fh73-29vq' => 'Fixed XSS via anchor attributes in admin system messages &mdash; links are rebuilt with only a scheme-validated <code>href</code>',
    'GHSA-r376-2wr5-g9qx' => 'Fixed a missing delete-permission check on the GDPR customer purge tools that allowed a read-only admin to trigger bulk customer deletions',
    'GHSA-8mmq-8hpq-2h23' => 'Fixed a missing delete-permission check on order note deletion',
    'GHSA-cq6g-rf5m-42xg' => 'Added CSRF protection to the order download-counter reset and stored-card deletion actions',

    // Maintenance / fixes
    '4159' => 'Coupons can now be limited to specific delivery countries (new <code>country_id</code> column on <code>CubeCart_coupons</code>)',
    '4160' => '<code>CubeCart_cron_tasks</code> converted to the <code>utf8mb4_unicode_ci</code> collation',
    '4161' => 'Replaced deprecated <code>strftime()</code> usage (removed in PHP 8.1)',
    '4162' => 'Filemanager <code>md5hash</code> index rebuilt',
    '4163' => 'Database schema-diff tool in maintenance recoded for more accurate comparisons',
    '4164' => 'Core fixes',
    '4165' => 'Image upload handling improved; the redundant <code>image_upload_format</code> setting is removed',
    '4166' => 'Free / paid handling',
    '4152' => 'Withdrawal-request email macros and related tweaks'
);
$security = array('GHSA-qcx6-cg43-ffmx', 'GHSA-hvmw-v8gc-4c29', 'GHSA-43f6-gfcf-wj9c', 'GHSA-v55x-fh73-29vq', 'GHSA-r376-2wr5-g9qx', 'GHSA-8mmq-8hpq-2h23', 'GHSA-cq6g-rf5m-42xg');
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, 8, $notes, $security);
