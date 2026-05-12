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
    <p>CubeCart 6.7.2 is a maintenance release following <a href="?_g=release_notes&amp;node=6.7.1">6.7.1</a>, focused on a Host-header injection fix, async-email correctness and a handful of admin UX refinements.</p>
    <p>The <code>standard_url</code> and <code>cookie_domain</code> values are now pinned in <code>includes/global.inc.php</code> so they cannot be overridden by a forged <code>Host:</code> header at request time. The upgrade migrates the existing database values into <code>global.inc.php</code> automatically.</p>
END;

$features = array(
    // Security
    'GHSA-7pvc-gxc4-chmc' => 'Host-header injection prevention &mdash; <code>standard_url</code> and <code>cookie_domain</code> pinned in <code>includes/global.inc.php</code>; <code>Config::set()</code> refuses to write either key',
    // Admin UX &amp; features
    '4122' => 'Image picker auto-selects newly uploaded files on the product add/edit page &mdash; no more scrolling down to assign them manually',
    '4121' => 'Google Base product feed exports only the main product image, not the comma-joined list',
    '4117' => 'Product export URLs are now absolute',
    '4118' => 'Add Category template fix',
    '4113' => 'Title columns added to relevant tables (<code>setup/db/upgrade/6.7.2.sql</code>)',
    '4109' => 'Statistics flag-by-country: Kosovo flag rendering and an &ldquo;unknown&rdquo; fallback for orphan country codes',
    // Email
    '4119' => 'Async email macros &mdash; store emails (admin order received, customer confirmation etc.) were rendering with empty <code>{$DATA.*}</code> fields because parsing was deferred to shutdown by which time the global Smarty <code>DATA</code> variable had been reassigned by page rendering. Parsing now happens at queue time while the order context is still authoritative.',
    '4116' => '<code>preg_match(): Passing null to parameter #2</code> in <code>Mailer</code>',
    '4115' => 'PHPMailer <code>AltBodyEncoding</code> consistency',
    // Redirects &amp; SEO
    '4120' => 'Over-encoded basket bad-address redirect &mdash; <code>SEO::rewriteSingleUrl()</code> emitted URLs like <code>?_a=addressbook&amp;action=edit%3Faddress_id%3D86075</code> when <code>generatePath()</code> hit its default branch for unrewritable types; the appended remaining params now use the correct separator',
    'redirect'  => '<code>SSL::validRedirect()</code> host comparison fixed for installs under a subpath &mdash; <code>standard_url</code> like <code>https://example.com/shop</code> no longer leaks the path into the domain check',
    // Search &amp; data
    '4114' => 'Fulltext matching hardened in <code>Database::select()</code>',
    '4111' => 'Elasticsearch handler &mdash; static config and a clean fallback when the cluster is unreachable',
    // Setup
    '4112' => 'Setup install: <code>localhost</code> handling and relaxed not-required guards',
    // Bug fixes
    'count' => '<code>count(): Argument must be of type Countable|array, true given</code> in <code>Catalogue</code>',
    'dupe-key' => 'Duplicate-key check on the maintenance page',
    'misc' => 'Misc admin tweaks &mdash; orders index, language settings, Elasticsearch handler edges',
);
$security = array('GHSA-7pvc-gxc4-chmc');
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, count($features), $notes, $security);
