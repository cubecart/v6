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
    <p>This is the biggest CubeCart release in years &mdash; packed with new features, security hardening, performance improvements and quality-of-life upgrades across the board.</p>
    <ul>
        <li><strong>REST API (Beta)</strong> &mdash; 9 resources with Bearer-token authentication and an admin UI for key management</li>
        <li><strong>Two-Factor Authentication</strong> &mdash; optional per-admin 2FA via email OTP or authenticator app</li>
        <li><strong>Abandoned-Cart Notifications</strong> &mdash; configurable follow-up emails to recover lost sales</li>
        <li><strong>Extension Marketplace</strong> &mdash; completely redeveloped for browsing and installing extensions</li>
        <li><strong>Hook Editor</strong> &mdash; edit custom hooks with built-in version control</li>
        <li><strong>Global Cron System</strong> &mdash; configurable scheduled tasks from the back office</li>
        <li><strong>Box-Packing Algorithm</strong> &mdash; automated packing for smarter shipping-rate calculation</li>
        <li><strong>Tax &amp; Rounding Fixes</strong> &mdash; penny-perfect calculations and a full coupon-tax rewrite</li>
        <li><strong>Language Manager</strong> &mdash; one-click language installation, completely rewritten</li>
        <li><strong>Performance</strong> &mdash; bot filtering, cache optimisations and new database indexes</li>
    </ul>
END;

$features = array(
    // Security
    '3912' => 'Two-factor authentication for the back office (email OTP or authenticator app)',
    '3928' => 'Full penetration-testing audit with security hardening across the platform',
    '3978' => 'Additional security patches from responsible-disclosure reports',
    '3948' => 'Card numbers automatically obfuscated in the request log',
    'GHSA-8gj6-9fwc-h4gh' => 'Closed a blind SQL-injection vector in admin sort parameters',
    'GHSA-gvxc-5v7r-272m' => 'Closed a stored cross-site scripting vector in product fields',
    // Major features
    '3979' => 'REST API &mdash; 9 resources, Bearer-token auth and an admin UI for key management',
    '3966' => 'Universal skin selector &mdash; one place to switch styles across all skins',
    '3965' => 'Hook editor with built-in version control for custom code',
    '3917' => 'Abandoned-cart notification system with configurable follow-ups',
    '3916' => 'Global cron system with configurable tasks and scheduling',
    '3894' => 'Restrict categories and products to specific customer groups',
    '3963' => 'Modernised help system replacing the legacy wiki',
    // Tax &amp; pricing
    '3881' => 'Tax rounding bugs eliminated &mdash; penny-perfect calculations',
    '3859' => 'Coupon-plus-tax calculation completely rewritten for mixed-rate baskets',
    '3490' => 'Gift certificates now deduct from the order total, not the subtotal',
    '3882' => 'Removed the redundant tax setting from gift cards',
    '3886' => 'Zero-rated tax lines hidden on the checkout page',
    '3885' => 'Total-discount line only shown when more than one discount applies',
    // Shipping
    '3913' => 'Automated box-packing algorithm for smarter shipping-rate calculation',
    '3879' => 'Shipping priority &mdash; control the order in which options appear',
    '3864' => 'Shipping falls back to the cheapest method when no qualifying options exist',
    '3838' => 'Basket preserved when no shipping methods are available',
    // Checkout &amp; orders
    '3939' => 'Gate the checkout with a minimum and/or maximum subtotal',
    '3941' => 'Order-total formatting corrected in notification emails',
    '3949' => 'Currency code now included in payment-notification emails',
    // Catalogue
    '3962' => 'Toggle between WebP, PNG and JPEG image output',
    '3880' => 'Fixed undefined option list in product-option display',
    '3884' => 'Fixed regex parentheses error in catalogue search',
    '3817' => 'Image attributes now update correctly on change',
    // Structured data
    '3877' => 'JSON-LD product description trimmed to the 5,000-character limit',
    '3876' => 'Added the optional priceValidUntil field to JSON-LD markup',
    '3875' => 'JSON-LD description markup validated and corrected',
    '3900' => 'Schema.org markup in order emails for rich inbox cards',
    // Email
    '3910' => 'Plain-text email auto-generated from HTML &mdash; no more manual duplicates',
    '3954' => 'SendGrid and Mailgun moved to plugins for independent updates',
    '3953' => 'Mailgun available as a first-class email-sending option',
    '3951' => 'Per-template toggle to enable or disable email content in the back office',
    '3957' => 'Send method (SMTP, SendGrid, etc.) recorded in the email log',
    '3934' => 'Removed the unused Import/Export tab from Email Contents',
    '3931' => 'Missing email content auto-recovers from the language XML',
    // Languages &amp; translations
    '3933' => 'One-click language installation &mdash; completely rewritten language manager',
    '3932' => 'Fresh installs automatically fetch and select the latest language packs',
    '3898' => 'New and improved foreign-language translations',
    // Upgrades &amp; system
    '3888' => 'Backups run in the background with an email notification on completion',
    '3969' => 'Delete-all-backups button for quick clean-up',
    '3890' => 'Setup only backs up global.inc.php when the file has actually changed',
    '3904' => 'global.inc.php now correctly references global.inc.php-dist',
    '3955' => 'Extension Marketplace completely redeveloped',
    // Performance
    '3891' => 'Bot filtering to reduce unnecessary overhead',
    '3971' => 'Redundant cache lookups eliminated (exists + read collapsed to one call)',
    '3850' => 'Housekeeping tidy governed by probability and configurable limits',
    '3938' => 'Expensive live_from inventory query optimised',
    '3883' => 'Missing database indexes added for faster queries',
    '3902' => 'PECL Redis preferred over Predis; Predis libraries updated',
    '3901' => 'Dropped legacy XCache and memcache (non-d) support',
    '3905' => 'Richer debug output for Redis and Memcached connections',
    // Admin UI
    '3964' => 'Additional tools added to the ACP flyout widget',
    '3970' => 'Administrator list converted to a sortable table layout',
    '3926' => 'Improved review-management layout',
    '3921' => 'Improved admin address-book management',
    '3956' => 'Quick-clear shortcuts on log pages',
    '3927' => 'Visual &ldquo;Copied!&rdquo; feedback when using the back-office copy tool',
    '3940' => 'Sortable table rows no longer lose column widths when dragged',
    '3937' => 'Single quotes in the SQL Query tool no longer HTML-encoded',
    '3936' => 'Database table optimise feature restored',
    '3919' => 'No more false &ldquo;Product successfully updated&rdquo; when nothing changed',
    '3918' => 'Cache-clear button visual state now updates correctly',
    '3887' => 'Debug window no longer appears on top of other content',
    '3906' => 'ACP widget collapses properly with longer text strings',
    '3960' => 'Removed the misleading &ldquo;changes after returning to basket&rdquo; notice',
    '3967' => 'Store logo handled gracefully when not set',
    '3968' => 'Fixed undefined GLOB_BRACE constant on non-GNU systems',
    // SEO &amp; indexing
    '3893' => 'Improved page indexing with proper nofollow/noindex directives',
    '3944' => 'Elasticsearch indexes rebuilt automatically when needed',
    '3943' => 'Sitemap regeneration runs automatically via cron',
    '3929' => 'ElasticSearch works without authentication, plus new API-key auth option',
    // Data &amp; config
    '3889' => 'Config storage migrated from base64/JSON blobs to clean name-value pairs',
    '3896' => 'Created and updated timestamps added to documents and categories',
    '3952' => 'Default-currency setting moved to the Currencies section where it belongs',
    '3923' => 'Currency data validated on save',
    '3922' => 'Country and state/region data validated on save',
    '3899' => 'Cookies follow a uniform naming convention',
    '3914' => 'Removed the &ldquo;Title&rdquo; honorific field from name forms',
    '3798' => 'Customer access-log fields aligned with the database table',
    // Code quality
    '3959' => 'Module class completely rewritten for clarity and reliability',
    '3947' => 'Request class natively supports DELETE and PUT methods',
    '3892' => 'Back-office schema map reads the live database instead of a static index',
    '3915' => 'Removed obsolete ini_set calls for magic_quotes',
    '3897' => 'PHP deprecation notices resolved across the codebase',
    '3855' => 'PHP 8.5 deprecations resolved',
    '3878' => 'Image uploads with Firefox no longer fail silently',
    '3796' => 'Gift-card enhancements and edge-case fixes',
);
$security = array('3928', '3912', '3978', '3948', 'GHSA-8gj6-9fwc-h4gh', 'GHSA-gvxc-5v7r-272m');
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, 125, $notes, $security);
