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
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}
define('ADMIN_CP', false);
// Include core functions
require CC_INCLUDES_DIR.'functions.inc.php';
// Shared data-layer bootstrap (Cache, Database, Config, timezone, Debug, Session)
require CC_INCLUDES_DIR.'bootstrap.data.inc.php';
// We will not need this anymore
unset($glob);
$GLOBALS['config']->merge('config', '', $config_default);
//Check security token
if ($GLOBALS['config']->get('config', 'csrf')=='1') {
    Sanitize::checkToken();
}
// Shared view-layer bootstrap (Smarty, Language, HookLoader, SSL, SEO, GUI, Tax)
require CC_INCLUDES_DIR.'bootstrap.view.inc.php';
// Front-end-only: HTML minification filter
if (!(bool)$GLOBALS['config']->get('config', 'debug')) {
    define('HTML_MINIFY_URL_ENABLED', false);
    include(CC_INCLUDES_DIR.'smarty/filters/HTMLMinify.smarty.php');
    $GLOBALS['smarty']->registerFilter("output", "minify_html");
}
// Front-end-only: resolve seo_path immediately so the dispatcher can route to the right item
if (isset($_GET['seo_path']) && !empty($_GET['seo_path'])) {
    $_GET['seo_path'] = preg_replace('/(\/\~[a-z0-9]{1,}\/)/', '', $_GET['seo_path']); // Remove /~username/ from seo_path
    $GLOBALS['seo']->getItem($_GET['seo_path']);
}
//Initialize catalogue
$GLOBALS['catalogue'] = Catalogue::getInstance();
//Initialize cubecart
$GLOBALS['cubecart'] = Cubecart::getInstance();
//Initialize user
$GLOBALS['user'] = User::getInstance();
//Initialize cart
$GLOBALS['cart'] = Cart::getInstance();
$GLOBALS['cart']->init();

$_REQUEST['_a'] = $_REQUEST['_a'] ?? null;

foreach ($GLOBALS['hooks']->load('controller.index') as $hook) {
    include $hook;
}

$GLOBALS['language']->setTemplate();
$GLOBALS['cubecart']->loadPage();
$GLOBALS['gui']->displayCommon();

$checkout_pages = array('confirm', 'basket', 'gateway', 'cart','checkout');


$global_template_file = (isset($_GET['_a']) && in_array($_GET['_a'], $checkout_pages) && file_exists(CC_ROOT_DIR.'/skins/'.$GLOBALS['gui']->getSkin().'/templates/main.checkout.php')) ? 'main.checkout.php' : 'main.php';

offline();
