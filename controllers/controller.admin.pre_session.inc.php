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
global $glob;
define('ADMIN_CP', true);
// Initialize Cache
$GLOBALS['cache'] = Cache::getInstance();
// Initialize Database class, and fetch default configuration
$GLOBALS['db'] = Database::getInstance($glob);
// Initialize Config class
$GLOBALS['config'] = Config::getInstance($glob);
$time_zone = $GLOBALS['config']->get('config', 'time_zone');
if(!empty($time_zone)) {
    $GLOBALS['db']->misc("SET @@time_zone = '".$time_zone."'");
    date_default_timezone_set($time_zone);
}
// Initialize debug
$GLOBALS['debug'] = Debug::getInstance();
// Initialize sessions
$GLOBALS['session'] = Session::getInstance();
//Check security token
Sanitize::checkToken();
// Initialize Smarty
$GLOBALS['smarty'] = new Smarty();
$GLOBALS['smarty']->muteExpectedErrors();
$GLOBALS['smarty']->error_reporting = E_ALL & ~E_NOTICE & ~E_WARNING;
$GLOBALS['smarty']->compile_dir  = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->config_dir  = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->cache_dir  = CC_SKIN_CACHE_DIR;
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

$GLOBALS['main'] = ACP::getInstance();
$lang = $GLOBALS['language']->getLanguageStrings();

$global_template_file['session_true']  = 'main.php';
$global_template_file['session_false']  = 'login.php';

// hook_tab_content is a place where hooks can specify template includes that
// define their admin tab content.
$GLOBALS['hook_tab_content'] = array();

foreach ($GLOBALS['hooks']->load('controller.admin') as $hook) {
    include $hook;
}
