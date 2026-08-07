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
// Shared view-layer bootstrap (Smarty, Language, HookLoader, SSL, SEO, GUI, Tax).
// Deliberately NOT set here, unlike the web bootstrap: $GLOBALS['user'],
// ['cart'], ['catalogue'] and ['cubecart'] - initialising them on every tick
// costs more than cron needs, and User/Cart are session-bound anyway.
//
// So anything reachable from a cron task must not assume those globals exist.
// Use the singleton accessor instead - Catalogue::getInstance() and friends
// self-instantiate and work in both contexts. Assuming $GLOBALS['catalogue']
// was set is what caused #4228 ("Call to a member function getProductData() on
// null") during an Elasticsearch rebuild.
require CC_INCLUDES_DIR . 'bootstrap.view.inc.php';

// Dispatch
$method = $argv[1] ?? 'run';

$cron = new Cron();
if (method_exists($cron, $method)) {
    $callable = array($cron, $method);
} else {
    // Plugin-contributed tasks are callable by name here too, so they can be
    // tested and re-run individually just like core ones.
    $registered = $cron->registeredTasks();
    if (isset($registered[$method]) && is_callable($registered[$method])) {
        $callable = $registered[$method];
    } else {
        fwrite(STDERR, "Unknown cron method: $method\n");
        exit(2);
    }
}

$start  = microtime(true);
$result = call_user_func($callable);
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
