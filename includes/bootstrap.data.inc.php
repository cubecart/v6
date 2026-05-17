<?php
/**
 * CubeCart v6 — shared data-layer bootstrap.
 *
 * Initialises the foundational singletons every entry point needs:
 * Cache, Database, Config, timezone, Debug, Session.
 *
 * Included by:
 *   - controllers/controller.index.inc.php          (front-end)
 *   - controllers/controller.admin.pre_session.inc.php (admin)
 *   - cli/cron.php                                  (CLI cron runner)
 *
 * Requires that ini.inc.php has already run (CC_INI_SET defined, $glob loaded).
 * Entry points handle their own divergent setup afterwards — CSRF tokens,
 * Smarty filters, $glob unset, config_default merge, etc.
 */
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}

$GLOBALS['cache']  = Cache::getInstance();
$GLOBALS['db']     = Database::getInstance($glob);
$GLOBALS['config'] = Config::getInstance($glob);

// Apply config-driven timezone to both PHP and the MySQL session.
$time_zone = $GLOBALS['config']->get('config', 'time_zone');
if (!empty($time_zone)) {
    $debug_query = (bool)$GLOBALS['config']->get('config', 'debug');
    $GLOBALS['db']->misc("SET @@time_zone = '".$time_zone."'", false, $debug_query);
    date_default_timezone_set($time_zone);
}

$GLOBALS['debug'] = Debug::getInstance();
// Headless entry points (CLI, API) have no web debug overlay to display.
if ((defined('CC_CLI') && CC_CLI) || (defined('CC_API_REQUEST') && CC_API_REQUEST)) {
    $GLOBALS['debug']->supress();
}

$GLOBALS['session'] = Session::getInstance();
