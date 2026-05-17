<?php
/**
 * CubeCart v6 — CLI cron runner
 *
 * Runs the same cron dispatcher (or a specific cron method) as the web entry
 * at ?_g=cron&_m=..., but without going through Apache/nginx, the CDN, or
 * PHP-FPM. Intended for multi-shop hosts where the per-tick web-stack overhead
 * matters at scale.
 *
 * Usage:
 *   php cli/cron.php             # runs the dispatcher (Cron::run)
 *   php cli/cron.php run         # same as above (explicit)
 *   php cli/cron.php <method>    # runs a specific public Cron method
 *
 * Example crontab:
 *   *\/5 * * * * /usr/bin/php /path/to/store/cli/cron.php >/dev/null 2>&1
 */

if (php_sapi_name() !== 'cli') {
    if (!headers_sent()) {
        header('HTTP/1.1 403 Forbidden');
    }
    exit("This script must be run from the command line.\n");
}

define('CC_CLI', true);
define('CC_IN_ADMIN', false);
define('ADMIN_CP', false);

// cli/cron.php → store root is the parent directory
$store_root = dirname(__DIR__);

// Web-context shims so ini.inc.php's URL/SSL detection doesn't redirect
// or fall back to nonsense values. Values are placeholders only — store URL
// is overridden from $glob['standard_url'] inside ini.inc.php anyway.
$_SERVER['HTTP_HOST']      = $_SERVER['HTTP_HOST']      ?? 'cli.local';
$_SERVER['SERVER_NAME']    = $_SERVER['SERVER_NAME']    ?? 'cli.local';
$_SERVER['REQUEST_URI']    = '/cli-cron';
$_SERVER['PHP_SELF']       = '/cli-cron';
$_SERVER['SCRIPT_NAME']    = '/cli-cron';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR']    = '127.0.0.1';
$_SERVER['HTTPS']          = 'on';
$_SERVER['SERVER_PORT']    = 443;
$_SERVER['HTTP_USER_AGENT'] = 'CubeCart-CLI/1.0';

require $store_root . '/ini.inc.php';
require CC_INCLUDES_DIR . 'functions.inc.php';
// Shared data-layer bootstrap (Cache, Database, Config, timezone, Debug, Session).
// Debug is auto-suppressed when CC_CLI is defined.
require CC_INCLUDES_DIR . 'bootstrap.data.inc.php';
unset($glob);
$GLOBALS['config']->merge('config', '', $config_default);

// View-layer singletons. Smarty must exist before Language (Language::__construct
// assigns to it). Mailer parses email templates via Smarty. SEO is needed by
// Cron::rebuildSitemap. GUI is touched by Mailer and SEO. Skipped vs the web
// bootstrap: User, Cart, Catalogue, Cubecart — not used by any cron task.
$GLOBALS['smarty'] = new Smarty();
$GLOBALS['smarty']->muteUndefinedOrNullWarnings();
$GLOBALS['smarty']->error_reporting = E_ALL & ~E_NOTICE & ~E_WARNING;
$GLOBALS['smarty']->compile_dir = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->config_dir  = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->cache_dir   = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->debugging   = false;
$GLOBALS['smarty']->enableSecurity(new CubeCart_Smarty_Security($GLOBALS['smarty']));

$GLOBALS['language'] = Language::getInstance();
$GLOBALS['hooks']    = HookLoader::getInstance();
$GLOBALS['ssl']      = SSL::getInstance();
$GLOBALS['seo']      = SEO::getInstance();
$GLOBALS['gui']      = GUI::getInstance();
$GLOBALS['tax']      = Tax::getInstance();

// Dispatch
$method = $argv[1] ?? 'run';

$cron = new Cron();
if (!method_exists($cron, $method)) {
    fwrite(STDERR, "Unknown cron method: $method\n");
    exit(2);
}

$start  = microtime(true);
$result = $cron->$method();
$ms     = (int)round((microtime(true) - $start) * 1000);

// Match the web entry's behaviour: when an individual method is invoked
// directly, stamp last_run/last_result. Cron::run() handles its own bookkeeping.
if ($method !== 'run') {
    $GLOBALS['db']->update(
        'CubeCart_cron_tasks',
        array('last_run' => date('Y-m-d H:i:s'), 'last_result' => 'OK'),
        array('method' => $method)
    );
}

echo (is_string($result) ? $result : 'done') . " ({$ms}ms)\n";
