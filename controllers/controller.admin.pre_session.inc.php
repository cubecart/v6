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
define('ADMIN_CP', true);
// Shared data-layer bootstrap (Cache, Database, Config, timezone, Debug, Session)
require CC_INCLUDES_DIR.'bootstrap.data.inc.php';
//Check security token
Sanitize::checkToken();
// Initialize Smarty
$GLOBALS['smarty'] = new Smarty();
$GLOBALS['smarty']->muteUndefinedOrNullWarnings();
$GLOBALS['smarty']->error_reporting = E_ALL & ~E_NOTICE & ~E_WARNING;
$GLOBALS['smarty']->compile_dir  = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->config_dir  = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->cache_dir  = CC_SKIN_CACHE_DIR;
// In production (debug off + cache on), skip the per-render filemtime check.
// Compiled-template cache is invalidated by the "Clear Cache" admin action.
if (!(bool)$GLOBALS['config']->get('config', 'debug') && (bool)$GLOBALS['config']->get('config', 'cache')) {
    $GLOBALS['smarty']->setCompileCheck(false);
}
$GLOBALS['smarty']->enableSecurity(new CubeCart_Smarty_Security($GLOBALS['smarty']));
//Initialize language
$GLOBALS['language'] = Language::getInstance();
//Initialize hooks
$GLOBALS['hooks'] = HookLoader::getInstance();
//Initialize GUI
$GLOBALS['gui'] = GUI::getInstance(true);
//Initialize SSL
$GLOBALS['ssl'] = SSL::getInstance();
//Initialize SEO
$GLOBALS['seo'] = SEO::getInstance();
//Setup language template
$GLOBALS['language']->setTemplate();
//Initialize Catalogue
$GLOBALS['catalogue'] = Catalogue::getInstance();
//Initialize ACP
$GLOBALS['main'] = ACP::getInstance();
$lang = $GLOBALS['language']->getLanguageStrings();
//Initialize Cart
$GLOBALS['cart'] = Cart::getInstance();


$global_template_file['session_true']  = 'main.php';
$global_template_file['session_false']  = 'login.php';

// hook_tab_content is a place where hooks can specify template includes that
// define their admin tab content.
$GLOBALS['hook_tab_content'] = array();

foreach ($GLOBALS['hooks']->load('controller.admin') as $hook) {
    include $hook;
}
