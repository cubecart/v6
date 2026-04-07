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
    <p><strong>CubeCart 6.6.0 &mdash; 98 Reasons to Upgrade!</strong></p>
    <p>This is the biggest CubeCart release in years &mdash; packed with new features, security hardening, performance improvements and quality-of-life upgrades across the board.</p>
END;

$features = array(
    '3912' => 'Two-factor authentication for the back office (email OTP or authenticator app)',
    '3930' => 'Admin login alerts for unrecognised browsers or IP addresses',
    '3928' => 'Full penetration testing audit and security hardening',
    '3881' => 'Tax rounding bugs eliminated &mdash; penny-perfect calculations',
    '3859' => 'Coupon + tax calculation completely rewritten for mixed-rate scenarios',
    '3490' => 'Gift certificates now deduct from order total, not subtotal',
    '3966' => 'Universal skin selector &mdash; one place to switch styles across all skins',
    '3965' => 'Hook editor with version control for custom code',
    '3917' => 'Abandoned cart notification system',
    '3916' => 'Global cron system with configurable tasks and scheduling',
    '3933' => 'One-click language installation &mdash; rewritten language management',
    '3913' => 'Automated box packing for smarter shipping rate calculation',
    '3879' => 'Shipping priority &mdash; control the order options appear',
    '3939' => 'Minimum and maximum subtotal checkout gating',
    '3946' => 'One-click upgrades direct from GitHub',
    '3945' => 'Latest version check from GitHub',
    '3888' => 'Backups run in the background with email notification',
    '3900' => 'Schema.org markup in order emails for rich inbox cards',
    '3910' => 'Plain text email dropped &mdash; auto-generated from HTML',
    '3954' => 'Sendgrid and Mailgun moved to plugins for easier updates',
    '3891' => 'Lazy sessions and bot filtering for better performance',
    '3893' => 'Improved page indexing with proper nofollow/noindex tags',
    '3947' => 'Request class supports DELETE/PUT natively',
    '3929' => 'ElasticSearch works without authentication + API key auth',
    '3902' => 'PECL Redis favoured over Predis; updated Predis libraries',
    '3901' => 'Dropped legacy XCache and memcache support',
    '3883' => 'Missing database indexes added',
    '3938' => 'Expensive live_from query optimised',
    '3889' => 'Config storage migrated from base64/JSON to name-value pairs',
    '3896' => 'Created and updated timestamps on documents and categories',
    '3959' => 'Module class rewritten',
    '3926' => 'Improved review management layout',
    '3921' => 'Improved admin address book management',
    '3970' => 'Administrator list converted to table layout',
    '3951' => 'Back office toggle to enable/disable email content',
    '3957' => 'Email send method shown in email log',
    '3864' => 'Shipping fallback to cheapest when no qualifying options exist',
    '3796' => 'Gift card enhancements',
    '3855' => 'PHP 8.5 deprecations resolved',
    '3886' => 'Zero-rated tax lines hidden on checkout',
    '3955' => 'Redeveloped Extension Marketplace',
    '3979' => 'REST API for CubeCart &mdash; 9 resources, Bearer token auth, admin UI for key management',
    'GHSA-8gj6-9fwc-h4gh' => 'Fix blind SQL injection in admin sort parameters',
    'GHSA-gvxc-5v7r-272m' => 'Stored Cross-Site Scripting (XSS) in CubeCart v6.x.x',
);
$security = array();
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, 125, $notes, $security);
