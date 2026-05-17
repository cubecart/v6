<?php
/**
 * CubeCart v6 — shared view-layer bootstrap.
 *
 * Initialises the view singletons every web/CLI entry point needs:
 * Smarty, Language, HookLoader, SSL, SEO, GUI, Tax.
 *
 * Included by:
 *   - controllers/controller.index.inc.php          (front-end)
 *   - controllers/controller.admin.pre_session.inc.php (admin)
 *   - cli/cron.php                                  (CLI cron runner)
 *
 * Requires that bootstrap.data.inc.php has already run (Cache, Database,
 * Config, Session must exist). Entry points handle their own post-init work
 * afterwards — HTMLMinify filter (front-end), seo_path handling (front-end),
 * Language::setTemplate() (admin), etc.
 *
 * GUI's admin flag is driven by CC_IN_ADMIN (set in index.php, admin.php, and
 * cli/cron.php before the bootstrap runs).
 */
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}

// Smarty
$GLOBALS['smarty'] = new Smarty();
$GLOBALS['smarty']->muteUndefinedOrNullWarnings();
$GLOBALS['smarty']->error_reporting = E_ALL & ~E_NOTICE & ~E_WARNING;
$GLOBALS['smarty']->compile_dir = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->config_dir  = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->cache_dir   = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->debugging   = false;
$GLOBALS['smarty']->enableSecurity(new CubeCart_Smarty_Security($GLOBALS['smarty']));

// In production (debug off + cache on), skip the per-render filemtime check.
// Compiled-template cache is invalidated by the "Clear Cache" admin action.
if (!(bool)$GLOBALS['config']->get('config', 'debug') && (bool)$GLOBALS['config']->get('config', 'cache')) {
    $GLOBALS['smarty']->setCompileCheck(false);
}

// View-layer singletons
$GLOBALS['language'] = Language::getInstance();
$GLOBALS['hooks']    = HookLoader::getInstance();
$GLOBALS['ssl']      = SSL::getInstance();
$GLOBALS['seo']      = SEO::getInstance();
$GLOBALS['gui']      = GUI::getInstance(defined('CC_IN_ADMIN') && CC_IN_ADMIN);
$GLOBALS['tax']      = Tax::getInstance();
