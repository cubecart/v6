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
    <p>CubeCart 6.8.5 is a security and maintenance release following <a href="?_g=release_notes&amp;node=6.8.4">6.8.4</a>. It resolves five responsibly disclosed vulnerabilities and raises the minimum PHP version to 8.0. There are no database changes.</p>

    <p><strong>Check your PHP version before upgrading.</strong> CubeCart now requires PHP 8.0 or higher, and PHP 8.5 is recommended. A store still running PHP 7.4 will show only an error message once the new files are in place, so move it to PHP 8 first.</p>

    <p><strong>All stores should upgrade.</strong> Two of the vulnerabilities could be used from the storefront: any signed-in customer could cancel another customer's order, and a customer could set their own shipping charge at checkout, down to nothing. The other three needed a back-office login or an API key: staff given read-only access to orders, reviews or the file manager could delete orders, reviews and files, and an API key allowed to edit products could save script into a product description that then ran for anyone viewing the product. All five were fixed before public disclosure. By running this version your store is protected against them; if you operate any additional CubeCart stores, make sure they are updated too. See the linked advisories for full details.</p>

    <p><strong>Stores that take guest orders with a captcha should also upgrade.</strong> This completes the checkout captcha work in 6.8.4. A guest who returns to the confirmation step can see and solve the captcha again, hCaptcha is verified with its own response field, and a guest who changes a detail is asked to verify again instead of being told the verification failed. The guest's customer record is now created only when they press Checkout, the step the captcha protects, rather than when a coupon, shipping choice or basket update is posted from the same page. Both shipped skins also stop a second press of the Checkout button from creating a duplicate order.</p>

    <p>If you have copied Atrium or Foundation to make your own skin, the captcha fix is in <em>templates/content.checkout.confirm.php</em> and the duplicate order guard is in the skin's JavaScript (<em>js/2.cubecart.js</em> in Foundation; <em>js/vendor/atrium.app.js</em> and <em>templates/content.recaptcha.head.php</em> in Atrium), and both need copying across. Atrium also gains five new skin settings, which live in its <em>config.xml</em>.</p>
END;

$features = array(
    // Security fixes (responsibly disclosed)
    'GHSA-h8fw-vh4c-3894' => 'A signed-in customer can cancel only their own pending, unpaid orders. Previously any customer could cancel any order by changing the order number in the cancel link',
    'GHSA-r4fv-g337-85x8' => 'The shipping option submitted at checkout is checked against the methods the store offers, including its price, tax and name, so a customer can no longer edit the submitted choice to set their own shipping charge',
    'GHSA-c3g5-r8rf-2m6v' => 'Deleting orders in bulk, including the purge of old orders, needs delete permission for orders, and changing order status in bulk needs edit permission, the same as for a single order. Previously staff with read-only access to orders could delete them',
    'GHSA-hpx7-78ch-9jx4' => 'Deleting product reviews in bulk, by email address or by IP address needs delete permission, and the file manager checks delete permission before deleting a file and edit permission before creating folders or renaming, moving or cropping files. Previously staff with read-only access could do all of these',
    'GHSA-734m-452m-gmh7' => 'Product and category descriptions saved through the REST API pass through the same HTML cleaning as the admin forms, so an API key allowed to edit products can no longer store script that runs on the storefront, and other API request fields are cleaned like form input. Completes the fix for GHSA-43f6-gfcf-wj9c',

    // Fixes and improvements
    '4309' => 'CubeCart requires PHP 8.0 or higher, up from 7.4, and PHP 8.5 is recommended',
    '4317' => 'Guest checkout with a reCAPTCHA v2 checkbox, hCaptcha or Turnstile: the captcha is shown on the confirmation step whenever a new solve is needed, instead of being hidden with the address form, which left the guest unable to place the order',
    '4319' => 'Guest checkout: changing a detail on the confirmation step, or taking longer than 30 minutes, asks the customer to confirm they are human again instead of reporting "The verification code was incorrect"',
    '4318' => 'hCaptcha is verified using its own h-captcha-response field rather than the reCAPTCHA compatibility copy, so verification no longer depends on hCaptcha\'s compatibility mode',
    '4320' => 'Guest checkout creates the customer record, or the account when "create account" is ticked, only when Checkout is pressed, which is the post the captcha checks, rather than on a coupon, shipping or Update basket post from the same page',
    '4316' => 'Atrium and Foundation: a second press of the Checkout button no longer submits the order again, which created duplicate orders when a shopper thought the first press had done nothing',
    '4315' => 'SEO URLs are matched exactly, so a product whose path starts with a dash, such as -p3041.html, opens its own page instead of another product\'s, and a path starting with ~ returns a 404 instead of a loosely matched page. The Redirects page and 404 log use the same exact match',
    '4307' => 'Saving settings writes only the configuration values that changed, instead of rewriting every setting loaded during the request, which on busy stores amounted to millions of identical database writes a day',
    '4310' => 'Where the desktop and mobile skins differ in their CSRF setting, the storefront token check follows the skin serving the visitor, instead of the skin that served the previous visitor, which could reject one visitor\'s forms with a security alert',
    '4321' => 'The bundled Elasticsearch library folder is no longer served over the web. Stores on nginx, which ignores .htaccess files, need an equivalent rule denying access to classes/elasticsearch/',
    '4314' => 'Stored data is unserialized without creating objects, and the Add coupon page no longer raises warnings. Thanks to Simon-3ft, who contributed the change',
    '4312' => 'Atrium: new skin settings for category image width, colour scheme (light, dark or follow browser), products per row, category description position and subcategory images, and saving skin settings takes effect without clearing the cache',
    '4313' => 'Admin dark mode: drop-down lists, checkbox toggles and placeholder text follow the dark theme'
);
$security = array('GHSA-h8fw-vh4c-3894', 'GHSA-r4fv-g337-85x8', 'GHSA-c3g5-r8rf-2m6v', 'GHSA-hpx7-78ch-9jx4', 'GHSA-734m-452m-gmh7');
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, count($features) - count($security), $notes, $security);
