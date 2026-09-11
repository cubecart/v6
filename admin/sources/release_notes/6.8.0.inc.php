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
    <p>CubeCart 6.8.0 is a feature release following <a href="?_g=release_notes&amp;node=6.7.6">6.7.6</a>. The headline is <strong>Atrium</strong>, the first new core storefront skin since Foundation, and it becomes the default on fresh installs.</p>

    <p><strong>Atrium</strong> is built on Tailwind CSS v4 and Alpine.js 3, and is responsive, accessible and dark-mode aware. Merchants build nothing: the compiled CSS and JavaScript ship with it. Every colour, radius and font resolves through a custom property, so the skin can be repainted from the admin panel without touching a file. It arrives with seven colour sub-themes, separate Header and Footer colour pickers, an exact-hex Brand Colour override, a choice of horizontal menu or vertical rail, a bundled self-hosted Figtree webfont (with 17 Google families as an opt-in alternative), quick view, sticky add-to-basket, image swipe and lightbox, search-as-you-type, recently viewed, low-stock disclosure, password strength metering and a vector "no image" placeholder that is 1KB instead of eight cached PNGs.</p>

    <p><strong>Upgrading stores are not switched.</strong> Your current skin stays selected and Foundation is untouched. Atrium appears in Manage Extensions to be chosen when you are ready, and is what a new installation starts with.</p>

    <p>Beyond the skin: every stock movement is now recorded in an audit log, stock is deducted atomically so concurrent checkouts cannot oversell the last item, product reviews can be restricted to verified purchasers, new accounts can be held until the customer confirms their email address, and the Contact Us form is finally multi-lingual.</p>

    <p>Also included, without individual issue numbers: the minimum password length is raised from 6 to 8 characters in line with NIST SP 800-63B (existing shorter passwords still sign in); a fix for replacement characters appearing in minified pages on servers whose locale made the minifier treat a UTF-8 non-breaking space as whitespace; core now serves SVG images from a skin directly rather than through the GD resize pipeline; SVG country flags; a cache item size ceiling; the image file manager no longer lists files that are not images; and reduced per-request work in the file manager rebuild, the orphan sweep and error backtraces.</p>

    <p>The upgrade adds the <code>CubeCart_stock_log</code> table, an index on <code>CubeCart_search</code>, <code>verified</code> on <code>CubeCart_reviews</code>, <code>activate</code> and <code>activate_expires</code> on <code>CubeCart_customer</code>, <code>doc_contact</code> and <code>doc_departments</code> on <code>CubeCart_documents</code>, and <code>extensions_dismissed</code> on <code>CubeCart_admin_users</code>. It drops the unused <code>new_password</code> columns and the <code>doc_privacy</code> column.</p>
END;

$features = array(
    // The headline
    '4232' => 'New default storefront skin, <strong>Atrium</strong>, built on Tailwind CSS v4 and Alpine.js 3. Dark mode, seven colour sub-themes, admin colour pickers for the brand, header and footer, horizontal or vertical main menu, a bundled Figtree webfont or a choice of 17 Google families, quick view, sticky add-to-basket, swipe galleries, recently viewed, low-stock disclosure and a vector image placeholder. Existing stores keep the skin they are on',
    '4268' => 'Skin settings are now declared in the skin\'s own <code>config.xml</code>, so a skin can offer its own options without a plugin',

    // Features
    '4224' => 'Every stock movement is recorded in a new audit log, and stock is now deducted atomically so two concurrent checkouts can no longer oversell the last item',
    '4179' => 'Product reviews can be restricted to verified purchasers, with guest purchasers given a route to reviewing without an account',
    '4263' => 'New accounts created at checkout are held until the customer confirms their email address, and a legacy unsalted password is only migrated once the password itself has been verified',
    '4243' => 'The Contact Us form is multi-lingual, moving its content into <code>CubeCart_documents</code>',
    '4059' => 'The homepage can show more than the latest items, with the options expanded',
    '4214' => 'Added a list view to the admin product image picker',
    '4242' => 'Reworked how new extensions are presented to developers, with the panel expandable and dismissible per admin',
    '4269' => 'The storefront says an option combination is out of stock before Add to Basket, rather than bouncing the customer back from the basket',
    '4245' => 'Thumbnails are served on demand instead of being written to <code>images/cache</code>, which suits read-only and CDN-fronted deployments',
    '4274' => 'Promotional codes and gift cards can be searched, and a gift card in that list can now be opened for editing',

    // Fixes and improvements
    '4275' => 'Language packs built for a newer CubeCart release are no longer offered for install or upgrade',
    '4272' => 'Restored the missing pagination styling on the gift card and discount code lists',
    '4271' => 'Fixed the order confirmation page rendering only once, with a refresh redirecting to the basket',
    '4270' => 'The payment gate now keys off explicit state rather than the message queue',
    '4267' => 'The admin login no longer reports "Invalid username or password" after a successful sign in, and no longer runs a customer login alongside it',
    '4266' => 'Font Awesome is loaded on the admin login page, so message icons are no longer missing-glyph boxes',
    '4265' => 'Disabling an extension now disables its hooks',
    '4262' => 'Disabling a product removes it from the Elasticsearch index, and search-as-you-type no longer returns it',
    '4257' => 'SQL error logging can no longer recurse and exhaust memory when the log write itself fails',
    '4251' => 'The captcha is required on every submission rather than once per session, closing off unlimited spam after a single solve',
    '4249' => '<code>&lt;style&gt;</code> blocks are no longer stripped from product descriptions on save',
    '4248' => 'Fixed removing all images from a product not saving',
    '4246' => 'Restocked products return to the Elasticsearch index; the <code>document_missing</code> response was being swallowed',
    '4244' => 'A coupon that discounts shipping only no longer reduces the tax on the whole order',
    '4241' => 'The featured product box no longer logs a SQL error on every page load when the database uses a table prefix',
    '4240' => 'CKEditor now stamps its own resources with the version hash, so <code>config.js</code> and plugin updates reach admin browsers after an update',
    '4239' => 'Smarty tags in image attributes no longer render as <code>{cke_protected_N}</code> in the WYSIWYG editor',
    '4238' => 'The email content editor saves a fragment rather than a full HTML document, and keeps image dimensions in the email and invoice editors',
    '4236' => 'Added the missing index on <code>CubeCart_search</code>.<code>hits</code>, which two admin pages sort by',
    '4235' => 'Sale-based popular products are bounded to the last year instead of aggregating the entire order history on every cache miss',
    '4234' => 'The skin selector is no longer hidden on portrait mobile by a hard-coded <code>max-width: 40em</code> media query',
    '4229' => 'Fixed the blank admin page when a module template is missing, which left Smarty pointed at the module skin',
    '4228' => 'Elasticsearch fixes for code that assumed web-only globals, which cron does not provide',
    '4226' => 'The installer and upgrader no longer allow PHP 7.4, which CubeCart has not supported for some time',
    '4225' => 'Admin search-as-you-type suggestions no longer show HTML entities in product names',
    '4199' => 'The document editor allows external videos again',
    '4198' => 'The receipt page matches checkout for tax-inclusive prices, and inclusive figures are shown on the admin order view and printed invoice',
    '4197' => 'Fixed remaining tax-inclusive rounding issues',
    '4195' => 'The "Products per page" setting on the Layout tab overrides the skin again'
);
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, 49, $notes);
