<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}
define('ADMIN_CP', false);
// Include core functions
require CC_INCLUDES_DIR.'functions.inc.php';
// Initialize Cache
$GLOBALS['cache'] = Cache::getInstance();
// Initialise Database class, and fetch default configuration
$GLOBALS['db'] = Database::getInstance($glob);
// Initialise Config class
$GLOBALS['config'] = Config::getInstance($glob);
$time_zone = $GLOBALS['config']->get('config', 'time_zone');
if(!empty($time_zone)) {
    $GLOBALS['db']->misc("SET @@time_zone = '".$time_zone."'");
    date_default_timezone_set($time_zone);
}
//We will not need this anymore
unset($glob);
$GLOBALS['config']->merge('config', '', $config_default);
// Initialize debug
$GLOBALS['debug'] = Debug::getInstance();
//Initialize sessions
$GLOBALS['session'] = Session::getInstance();
//Check security token
if ($GLOBALS['config']->get('config', 'csrf')=='1') {
    Sanitize::checkToken();
}
//Initialize Smarty
$GLOBALS['smarty'] = new Smarty();
$GLOBALS['smarty']->muteExpectedErrors();
$GLOBALS['smarty']->error_reporting = E_ALL & ~E_NOTICE & ~E_WARNING;
$GLOBALS['smarty']->compile_dir  = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->config_dir   = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->cache_dir    = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->debugging = false;
if (!(bool)$GLOBALS['config']->get('config', 'debug')) {
    define('HTML_MINIFY_URL_ENABLED', false);
    include(CC_INCLUDES_DIR.'lib/smarty/filters/HTMLMinify.smarty.php');
    $GLOBALS['smarty']->registerFilter("output", "minify_html");
}
//Initialize language
$GLOBALS['language'] = Language::getInstance();
//Initialize hooks
$GLOBALS['hooks'] = HookLoader::getInstance();
//Initialize SSL
$GLOBALS['ssl'] = SSL::getInstance();
//Initialize SEO
$GLOBALS['seo'] = SEO::getInstance();
if (isset($_GET['seo_path']) && !empty($_GET['seo_path'])) {
    $_GET['seo_path'] = preg_replace('/(\/\~[a-z0-9]{1,}\/)/', '', $_GET['seo_path']); // Remove /~username/ from seo_path
    $GLOBALS['seo']->getItem($_GET['seo_path']);
}
//Initialize GUI
$GLOBALS['gui'] = GUI::getInstance();
//Initialize Taxes
$GLOBALS['tax'] = Tax::getInstance();
//Initialize catalogue
$GLOBALS['catalogue'] = Catalogue::getInstance();
//Initialize cubecart
$GLOBALS['cubecart'] = Cubecart::getInstance();
//Initialize user
$GLOBALS['user'] = User::getInstance();
//Initialize cart
$GLOBALS['cart'] = Cart::getInstance();

// Set store timezone - default to UTC
date_default_timezone_set(($GLOBALS['config']->get('config', 'time_zone')) ? $GLOBALS['config']->get('config', 'time_zone') : 'UTC');
$_GET['_a']  = (isset($_GET['_a'])) ? $_GET['_a'] : null;
$_REQUEST['_a'] = (isset($_REQUEST['_a'])) ? $_REQUEST['_a'] : null;

foreach ($GLOBALS['hooks']->load('controller.index') as $hook) {
    include $hook;
}

$GLOBALS['language']->setTemplate();
$GLOBALS['cubecart']->loadPage();
$GLOBALS['gui']->displayCommon();

$checkout_pages = array('confirm', 'basket', 'gateway', 'cart','checkout');


$global_template_file = (in_array($_GET['_a'], $checkout_pages) && file_exists(CC_ROOT_DIR.'/skins/'.$GLOBALS['gui']->getSkin().'/templates/main.checkout.php')) ? 'main.checkout.php' : 'main.php';

offline();
