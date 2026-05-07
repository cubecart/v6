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
    <p>CubeCart 6.7.0 is a major release: a brand-new sales reporting suite, a redesigned email subsystem with async sending and open tracking, audit and request logs, a rewritten image picker, and dozens of admin UX refinements. Several legacy components have been retired in favour of leaner, modern replacements.</p>
    <ul>
        <li><strong>Sales Reports Suite</strong> &mdash; Sales Stats with drill-down, Best Selling Products, Search Terms, Users Online, Conversion, Abandoned Carts and Sales by Country, with .xlsx export</li>
        <li><strong>Email Overhaul</strong> &mdash; asynchronous sending, open tracking, full template preview and a redesigned email log</li>
        <li><strong>Audit Log</strong> &mdash; every admin action is recorded with actor, IP, target and diff</li>
        <li><strong>Request Log</strong> &mdash; outbound HTTP calls hash-deduped to a single row with an occurrence counter</li>
        <li><strong>Error Log Revamp</strong> &mdash; same hash-dedup model with first/last-seen timestamps</li>
        <li><strong>Image Picker Rewrite</strong> &mdash; shared modular component across products, categories, gift certificates, CKEditor and digital files</li>
        <li><strong>Hook System Overhaul</strong> &mdash; extensions are first-class citizens of the hook registry</li>
        <li><strong>Async Newsletters</strong> &mdash; batched, scheduled sending via cron with throttling</li>
        <li><strong>Setup Overhaul</strong> &mdash; new install/upgrade flow with vanilla JS and tighter gating</li>
        <li><strong>Smarty 4.5.6 &amp; CKEditor 4.22.1</strong> &mdash; core dependencies upgraded</li>
    </ul>
    <p>Security findings in this release responsibly disclosed by <a href="https://github.com/Th3-SAx11" target="_blank" rel="noopener">Th3-SAx11</a> and <a href="https://github.com/tuannm-1876" target="_blank" rel="noopener">Nguy Minh Tuan</a> (Sun* Cyber Security Research Team) &mdash; thank you.</p>
END;

$features = array(
    // Security
    'GHSA-wpjx-g695-qc5j' => 'Blocked SSTI/RCE via dangerous PHP functions in Smarty templates',
    'GHSA-gvcp-wpvp-c6f7' => 'Reflected XSS in the storefront search bar',
    'GHSA-652f-8c88-25cx' => 'Authenticated arbitrary-file-upload to RCE via REST <code>POST /api/v1/files</code> &mdash; filename validation, filepath traversal containment and a defence-in-depth <code>.htaccess</code> in <code>images/source/</code>',
    // Major features
    '4065' => 'Sales reports suite &mdash; Sales Stats with drill-down, Best Selling Products, Search Terms, Users Online, Conversion, Abandoned Carts and Sales by Country',
    '4066' => 'New Excel class powering .xlsx exports of sales reports, with show/hide column controls',
    '4062' => 'Email content and template overhaul',
    '4071' => 'Email template preview from the back office',
    '4075' => 'Asynchronous email sending via cron',
    '4076' => 'Email open tracking with per-message counters',
    '4087' => 'Audit log &mdash; every admin action recorded with actor, IP, target and diff',
    '4086' => 'Request log &mdash; outbound HTTP calls captured with hash-deduplication',
    '4084' => 'System and admin error logs revamped with hash-deduped rows and occurrence counts',
    '4085' => 'Hook system overhaul &mdash; extensions are first-class hook owners; manage-hooks UI shows ownership',
    '4051' => 'Asynchronous newsletter sending &mdash; batched and scheduled via a new processNewsletters cron task with throttling',
    '4102' => 'Image picker rewrite &mdash; modular component shared across products, categories, gift certificates, CKEditor and a digital-files mode',
    '4067' => 'Bulk price changes by percent or fixed amount',
    '4101' => 'New bulk-tools admin patterns for products, categories and orders',
    '4099' => 'Tab row relocation &mdash; tabs reflow to the page when wrapped',
    '4100' => 'Action tabs &mdash; destructive utilities like &ldquo;Clear log&rdquo; sit on the tab row with a danger treatment',
    '4090' => 'What3Words integration for address enrichment',
    '4072' => 'Latest Products is now a paged index, backed by a new composite index',
    '4073' => 'Product stats page overhauled',
    '4069' => 'SEO redirects overhaul, with hit-count and last-hit tracking on URLs',
    '4057' => 'Smarty upgraded to 4.5.6',
    '4058' => 'CKEditor upgraded to 4.22.1',
    '4047' => 'Smarty class moved into the canonical includes/lib/ location',
    // Setup &amp; install
    '4052' => 'Setup overhaul &mdash; new install/upgrade icons, vanilla-JS rewrite, tighter database-required gating',
    // Admin UX
    '4105' => 'Order builder refactored to a shared row-list component, fixing penny-rounding, stale-total and currency-precision bugs',
    '4104' => 'Per-option product image assignment from the new options grid',
    '4103' => 'Responsive product images with srcset/sizes',
    '4096' => 'Image search across the filemanager',
    '4094' => 'Bulk image pre-fetch on product listings',
    '4093' => 'Hook lookup optimisation',
    '4095' => 'Misc admin performance optimisations',
    '4092' => 'Filemanager edit screen layout: preview tile + metadata side-by-side',
    '4091' => 'Filemanager digital-files tab integrated with the new image picker',
    '4089' => 'Settings UI consolidation &amp; tweaks',
    '4077' => 'Category UX tweaks',
    '4074' => 'Multi-language fixes for documentation and miscellaneous strings',
    '4073' => 'Documentation panel updates',
    '3471' => 'Contact-form department rows redesigned',
    '4037' => 'Kosovo added as a country',
    // Bug fixes
    '4098' => 'Customer group delete fixed',
    '4083' => 'Email content preserved across save',
    '4081' => 'Documentation page bug fixes',
    '4080' => 'Email log bug fixes',
    '4078' => 'Promo code fixes',
    '4068' => 'Cookie-consent stub fixes prior to removal',
    '4063' => 'Removed XHTML residue from older templates',
    '4061' => 'b64DecodeUnicode helper hardened against malformed input',
    '4044' => 'number_format defensive cast for null/non-numeric inputs',
    '3980' => 'Related-products fixes',
    // Performance &amp; database
    '4050' => 'O(1) duplicate detection and sitemap batch processing',
    '4049' => 'Search-column performance improvements',
    '4048' => 'sizeof() &rarr; count() and other micro-fixes',
    '4046' => 'recaptchalib.php cleanup',
    '4045' => 'Default user-agent and fallback handling for outbound requests',
    '4043' => 'Third-party CDN handling for select assets with local fallback',
    '4053' => 'Exception class allowlist (_allowed_exceptions) honoured in catch blocks',
    '4055' => 'Mail-method dispatch consolidation',
    '4064' => 'Replaced glob() with safer scandir-based traversal in hot paths',
    // Removed / breaking
    '4079' => 'On/offline toggle removed &mdash; use maintenance mode instead',
    '4088' => 'In-app database backup tool removed; Colorbox dependency removed in favour of native admin modals',
    '4070' => 'Cookie-consent banner replaced with silent server-side logging',
    // Translations
    '4082' => 'French and Italian translations completed; categories module fully translated',
);
$security = array('GHSA-wpjx-g695-qc5j', 'GHSA-gvcp-wpvp-c6f7', 'GHSA-652f-8c88-25cx');
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, 61, $notes, $security);
