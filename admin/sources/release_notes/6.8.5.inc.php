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
    <p>CubeCart 6.8.5 follows <a href="?_g=release_notes&amp;node=6.8.4">6.8.4</a> and fixes 21 security vulnerabilities, three of them critical. <strong>Every store should upgrade.</strong></p>

    <p><strong>Before upgrading:</strong> CubeCart now requires PHP 8.0 or higher (8.5 recommended).</p>

    <p><strong>After upgrading:</strong></p>
    <ul>
        <li>All admins are logged out, and each admin account can now be signed in in only one place at a time.</li>
        <li>Cloudflare is detected automatically. Behind Sucuri or any other proxy, a setting in <em>includes/global.inc.php</em> is needed, or every visitor is seen as the proxy's IP address and failed logins from one visitor can lock everyone out. The dashboard tells you which setting to add.</li>
        <li>The server needs an up-to-date CA certificate bundle, or captcha checks and extension installs will fail.</li>
        <li>Skins copied from Atrium or Foundation need this release's template and JavaScript changes.</li>
    </ul>
END;

$features = array(
    // Security fixes: critical
    'GHSA-h67q-8xhx-ghfr' => 'Lists of values passed to database queries are escaped, closing an SQL injection that needed no login, through the newsletter sign-up, and two further routes through the REST API and tax settings',
    'GHSA-rwx3-6h7x-j625' => 'Contact Us attachments are saved under a random name chosen by the store instead of the name sent by the browser, closing a file write that needed no login and could lead to code execution',
    'GHSA-7jf6-336f-wx58' => 'Profile updates, registration, guest checkout and account activation save only the fields a customer may change, so a customer can no longer give themselves store credit or change their account status',

    // Security fixes: high
    'GHSA-47xw-v6gg-m8x7' => 'Admin AJAX requests check the section permission they need, so staff limited to one section can no longer read the email log, which holds password reset links, or search customers outside their permissions',
    'GHSA-35c3-48g3-gpv9' => 'Disabling an admin account, or removing super user rights, takes effect on that admin\'s open session at once, and an admin session ends when the same account signs in elsewhere',
    'GHSA-jwqj-2h64-24pq' => 'The visitor IP address is taken from forwarding headers only when the request comes from Cloudflare or a configured trusted proxy, so it can no longer be forged to get round login throttling, API key IP limits or the debug mode IP restriction',
    'GHSA-3qhx-63gx-3p7x' => 'Product options on orders can no longer carry script into the admin order view, where a guest order could run code in a staff member\'s admin session',
    'GHSA-jx2m-ww56-f8m9' => 'An admin account locked by repeated failed logins refuses the correct password too until the lock expires, and the login blocker no longer keys on the browser\'s User-Agent, so changing it or the IP address no longer allows unlimited password guesses',
    'GHSA-fv5q-rvcx-rv3h' => 'Failed 2FA codes are counted in the database rather than the session, codes for an admin are checked one at a time, and the login count, device record and new-device email wait until the code is accepted, so an attacker who has the password can no longer keep guessing codes',
    'GHSA-hpx7-78ch-9jx4' => 'Deleting product reviews in bulk, by email address or by IP address needs delete permission, and the file manager checks delete permission before deleting a file and edit permission before creating folders or renaming, moving or cropping files. Previously staff with read-only access could do all of these',
    'GHSA-c3g5-r8rf-2m6v' => 'Deleting orders in bulk, including the purge of old orders, needs delete permission for orders, and changing order status in bulk needs edit permission, the same as for a single order. Previously staff with read-only access to orders could delete them',

    // Security fixes: medium
    'GHSA-wmwq-93f9-q8cv' => 'A request with no security token is refused instead of allowed, the token is compared in constant time and replaced at login, the session cookie is SameSite=Lax, Sign in as customer needs the token, and the storefront logout and empty basket links are ignored when followed from another site',
    'GHSA-qc8h-q96p-xjx2' => 'The admin account form saves only its own fields, only a super user can grant super user rights, and admins who are not super users can edit only their own account',
    'GHSA-8xgw-h2r6-f36v' => 'The limit on GDPR data withdrawal requests is counted per IP address in the database, so it can no longer be bypassed to send the store\'s emails to any address without limit',
    'GHSA-hhjh-cp7f-hfm3' => 'The public cron URL runs only the scheduler, which honours each task\'s enabled setting, schedule and lock, instead of any task named in the URL',
    'GHSA-3f4g-3h73-3xpq' => 'Outbound HTTPS requests verify the remote certificate, so an attacker on the network path can no longer substitute an extension download or fake a captcha result',
    'GHSA-m8ch-wcmp-2584' => 'Values imported from a product CSV are cleaned the same way as form input, and the column-mapping preview is escaped, so a supplier feed can no longer store script on the storefront or in the admin product list',
    'GHSA-qgfr-c729-rpw6' => 'Page links on paginated listings no longer reflect script from a crafted URL',
    'GHSA-h8fw-vh4c-3894' => 'A signed-in customer can cancel only their own pending, unpaid orders. Previously any customer could cancel any order by changing the order number in the cancel link',
    'GHSA-r4fv-g337-85x8' => 'The shipping option submitted at checkout is checked against the methods the store offers, including its price, tax and name, so a customer can no longer edit the submitted choice to set their own shipping charge',
    'GHSA-734m-452m-gmh7' => 'Product and category descriptions saved through the REST API pass through the same HTML cleaning as the admin forms, so an API key allowed to edit products can no longer store script that runs on the storefront, and other API request fields are cleaned like form input. Completes the fix for GHSA-43f6-gfcf-wj9c',

    // Fixes and improvements
    '4309' => 'CubeCart requires PHP 8.0 or higher, up from 7.4, and PHP 8.5 is recommended',
    '4323' => 'Customers can reset a forgotten password again. The emailed reset link includes the account email, and Atrium\'s recovery form sends the email and validation key it was leaving blank',
    '4317' => 'Guest checkout with a reCAPTCHA v2 checkbox, hCaptcha or Turnstile: the captcha is shown on the confirmation step whenever a new solve is needed, instead of being hidden with the address form, which left the guest unable to place the order',
    '4319' => 'Guest checkout: changing a detail on the confirmation step, or taking longer than 30 minutes, asks the customer to confirm they are human again instead of reporting "The verification code was incorrect"',
    '4318' => 'hCaptcha is verified using its own h-captcha-response field rather than the reCAPTCHA compatibility copy, so verification no longer depends on hCaptcha\'s compatibility mode',
    '4320' => 'Guest checkout creates the customer record, or the account when "create account" is ticked, only when Checkout is pressed, which is the post the captcha checks, rather than on a coupon, shipping or Update basket post from the same page',
    '4316' => 'Atrium and Foundation: a second press of the Checkout button no longer submits the order again, which created duplicate orders when a shopper thought the first press had done nothing',
    '4325' => 'Module settings no longer write stored secrets, such as API keys, back into the page. A module lists its secret fields under &lt;secretvars&gt; in its config.xml, and an empty field keeps the saved value. Payment extensions will adopt this in their next releases',
    '4328' => 'The reCAPTCHA secret key and SMTP password are no longer written back into the store settings page, and an empty field keeps the saved value',
    '4315' => 'SEO URLs are matched exactly, so a product whose path starts with a dash, such as -p3041.html, opens its own page instead of another product\'s, and a path starting with ~ returns a 404 instead of a loosely matched page. The Redirects page and 404 log use the same exact match',
    '4307' => 'Saving settings writes only the configuration values that changed, instead of rewriting every setting loaded during the request, which on busy stores amounted to millions of identical database writes a day',
    '4310' => 'Where the desktop and mobile skins differ in their CSRF setting, the storefront token check follows the skin serving the visitor, instead of the skin that served the previous visitor, which could reject one visitor\'s forms with a security alert',
    '4321' => 'The bundled Elasticsearch library folder is no longer served over the web. Stores on nginx, which ignores .htaccess files, need an equivalent rule denying access to classes/elasticsearch/',
    '4314' => 'Stored data is unserialized without creating objects, and the Add coupon page no longer raises warnings. Thanks to Simon-3ft, who contributed the change',
    '4322' => 'The file cache also unserializes without creating objects. Thanks to Simon-3ft',
    '4312' => 'Atrium: new skin settings for category image width, colour scheme (light, dark or follow browser), products per row, category description position and subcategory images, and saving skin settings takes effect without clearing the cache',
    '4327' => 'Atrium: the price shown updates when a product option that changes the price is chosen',
    '4326' => 'Atrium: the button that reveals a password stays in place inside the field when a validation message is shown',
    '4313' => 'Admin dark mode: drop-down lists, checkbox toggles and placeholder text follow the dark theme'
);
$security = array_values(array_filter(array_keys($features), function ($id) { return strpos($id, 'GHSA-') === 0; }));
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, count($features) - count($security), $notes, $security);
