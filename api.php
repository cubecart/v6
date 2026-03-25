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

/**
 * REST API entry point
 *
 * Lean bootstrap: only initializes the subsystems needed for API operations.
 * Skips GUI, SEO, CSRF, and frontend rendering.
 */

// Bootstrap core
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ini.inc.php';
define('CC_IN_ADMIN', true);
define('ADMIN_CP', true);
define('CC_API_REQUEST', true);

// Disable zlib compression that ini.inc.php may have enabled - we handle our own output
ini_set('zlib.output_compression', false);

// Clean any output buffers left by ini.inc.php
while (ob_get_level()) {
    ob_end_clean();
}

// JSON output
header('Content-Type: application/json; charset=utf-8');

// Suppress HTML error output
ini_set('display_errors', false);
ini_set('html_errors', false);

global $config_default;

// Include core functions
require CC_INCLUDES_DIR . 'functions.inc.php';

// Load API framework classes
require CC_CLASSES_DIR . 'api/response.class.php';
require CC_CLASSES_DIR . 'api/auth.class.php';
require CC_CLASSES_DIR . 'api/ratelimiter.class.php';
require CC_CLASSES_DIR . 'api/resource.class.php';
require CC_CLASSES_DIR . 'api/router.class.php';

// Enforce HTTPS
if (!CC_SSL) {
    ApiResponse::error('HTTPS required', 'SSL_REQUIRED', 403);
}

// Initialize essential subsystems (lean bootstrap)
try {
    $GLOBALS['cache']    = Cache::getInstance();
    $GLOBALS['db']       = Database::getInstance($glob);
    $GLOBALS['config']   = Config::getInstance($glob);

    $time_zone = $GLOBALS['config']->get('config', 'time_zone');
    if (!empty($time_zone)) {
        $GLOBALS['db']->misc("SET @@time_zone = '" . $time_zone . "'", false, false);
        date_default_timezone_set($time_zone);
    }

    unset($glob);
    $GLOBALS['config']->merge('config', '', $config_default);

    $GLOBALS['debug']     = Debug::getInstance();
    $GLOBALS['debug']->supress();
    $GLOBALS['session']   = Session::getInstance();

    // Smarty stub - Language and other classes call $GLOBALS['smarty']->assign()
    // We don't render templates but need the object to exist
    $GLOBALS['smarty'] = new Smarty();
    $GLOBALS['smarty']->muteUndefinedOrNullWarnings();
    $GLOBALS['smarty']->compile_dir = CC_SKIN_CACHE_DIR;
    $GLOBALS['smarty']->config_dir  = CC_SKIN_CACHE_DIR;
    $GLOBALS['smarty']->cache_dir   = CC_SKIN_CACHE_DIR;

    $GLOBALS['language']  = Language::getInstance();
    $GLOBALS['hooks']     = HookLoader::getInstance();
    $GLOBALS['tax']       = Tax::getInstance();
    $GLOBALS['catalogue'] = Catalogue::getInstance();

    // Set store timezone
    date_default_timezone_set($GLOBALS['config']->get('config', 'time_zone') ?: 'UTC');

} catch (Exception $e) {
    ApiResponse::error('Internal server error during bootstrap', 'INTERNAL_ERROR', 500);
}

// Global error handler for uncaught errors
set_error_handler(function ($severity, $message, $file, $line) {
    if ($severity & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR)) {
        ApiResponse::error('Internal server error', 'INTERNAL_ERROR', 500);
    }
    return false;
});

set_exception_handler(function ($e) {
    ApiResponse::error('Internal server error', 'INTERNAL_ERROR', 500);
});

// Allow hooks to intercept API requests
foreach ($GLOBALS['hooks']->load('api.bootstrap') as $hook) {
    include $hook;
}

// Purge old API log entries (~5% of requests, older than 30 days)
if (executionChance(5)) {
    $cutoff = time() - (30 * 86400);
    $GLOBALS['db']->delete('CubeCart_api_log', array('<' => array('request_time' => $cutoff)));
}

// Route and dispatch
try {
    $router = new ApiRouter();
    $router->dispatch();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'error' => array(
            'code' => 'INTERNAL_ERROR',
            'message' => $e->getMessage(),
        ),
    ));
}
