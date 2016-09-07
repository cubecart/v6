<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2015. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('CC_INI_SET')) die('Access Denied');
define('ADMIN_CP', false);
// Include core functions
require CC_INCLUDES_DIR . 'functions.inc.php';
// Initialize Cache
$GLOBALS['cache'] = Cache::getInstance();
// Initialise Database class, and fetch default configuration
$GLOBALS['db'] = Database::getInstance($glob);
// Initialise Config class
$GLOBALS['config'] = Config::getInstance($glob);
//We will not need this anymore
unset($glob);
$GLOBALS['config']->merge('config', '', $config_default);
// Initialize debug
$GLOBALS['debug'] = Debug::getInstance();
//Initialize sessions
$GLOBALS['session'] = Session::getInstance();
//Initialize Smarty
$GLOBALS['smarty'] = new Smarty();
$GLOBALS['smarty']->muteExpectedErrors();
$GLOBALS['smarty']->error_reporting = E_ALL & ~E_NOTICE & ~E_WARNING;
$GLOBALS['smarty']->compile_dir = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->config_dir = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->cache_dir = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->debugging = false;
//Initialize language
$GLOBALS['language'] = Language::getInstance();
//Initialize hooks
$GLOBALS['hooks'] = HookLoader::getInstance();
//Initialize SEO
$GLOBALS['seo'] = SEO::getInstance();
if (isset($_GET['seo_path']) && !empty($_GET['seo_path'])) {
    $_GET['seo_path'] = preg_replace('/(\/\~[a-z0-9]{1,}\/)/', '', $_GET['seo_path']); // Remove /~username/ from seo_path
    $GLOBALS['seo']->getItem($_GET['seo_path']);
}
//Initialize SSL
$GLOBALS['ssl'] = SSL::getInstance();
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
if ($GLOBALS['config']->get('config', 'recaptcha') && !$GLOBALS['session']->get('confirmed', 'recaptcha')) {

    $recaptcha['error'] = null;
    $recaptcha['confirmed'] = false;

    if ($GLOBALS['config']->get('config', 'recaptcha') == 2 && isset($_POST['g-recaptcha-response'])) {

        if (empty($_POST['g-recaptcha-response'])) {
            $recaptcha['error'] = $GLOBALS['language']->form['verify_human_fail'];
        } else {
            $g_data = array(
                'secret' => $GLOBALS['config']->get('config', 'recaptcha_secret_key'),
                'response' => $_POST['g-recaptcha-response'],
                'remoteip' => get_ip_address()
            );
            $json = file_get_contents('https://www.google.com/recaptcha/api/siteverify?' . http_build_query($g_data));
            $g_result = json_decode($json);
            if ($g_result->success) {
                $recaptcha['confirmed'] = true;
            } else {
                $recaptcha['error'] = $GLOBALS['language']->form['verify_human_fail'];
            }
        }
    } else {
        require CC_INCLUDES_DIR . 'lib/recaptcha/recaptchalib.php';
        $GLOBALS['recaptcha_keys'] = array('captcha_private' => '6LfT4sASAAAAAKQMCK9w6xmRkkn6sl6ORdnOf83H', 'captcha_public' => '6LfT4sASAAAAAOl71cRz11Fm0erGiqNG8VAfKTHn');

        if (isset($_POST['recaptcha_response_field'])) {
            $resp = recaptcha_check_answer($GLOBALS['recaptcha_keys']['captcha_private'], $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
            if ($resp->is_valid) {
                // All good!
                $recaptcha['confirmed'] = true;
            } else {
                // Set the error code so that we can display it
                $recaptcha['error'] = $GLOBALS['language']->form['verify_human_fail'];
            }
        }
    }
    $GLOBALS['session']->set('', $recaptcha, 'recaptcha');
} elseif (!$GLOBALS['session']->get('confirmed', 'recaptcha')) {
    $GLOBALS['session']->delete('', 'recaptcha');
}
$_GET['_a'] = (isset($_GET['_a'])) ? $_GET['_a'] : null;
$_REQUEST['_a'] = (isset($_REQUEST['_a'])) ? $_REQUEST['_a'] : null;

foreach ($GLOBALS['hooks']->load('controller.index') as $hook) include $hook;

$GLOBALS['language']->setTemplate();
$GLOBALS['cubecart']->loadPage();
$GLOBALS['gui']->displayCommon();

$checkout_pages = array('confirm', 'basket', 'gateway', 'cart', 'checkout');


$global_template_file = (in_array($_GET['_a'], $checkout_pages) && file_exists(CC_ROOT_DIR . '/skins/' . $GLOBALS['gui']->getSkin() . '/templates/main.checkout.php')) ? 'main.checkout.php' : 'main.php';

offline();