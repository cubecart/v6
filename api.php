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

// Constants must be defined BEFORE ini.inc.php so the bootstrap can detect API context
// (skips zlib output compression; bootstrap.data auto-suppresses Debug).
define('CC_IN_ADMIN', true);
define('ADMIN_CP', true);
define('CC_API_REQUEST', true);

require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ini.inc.php';

// Clean any output buffers PHP's default output_buffering may have started
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

// Initialize subsystems
try {
    // Shared data-layer bootstrap (Cache, Database, Config, timezone, Debug, Session).
    // Debug is auto-suppressed because CC_API_REQUEST is set.
    require CC_INCLUDES_DIR . 'bootstrap.data.inc.php';
    unset($glob);
    $GLOBALS['config']->merge('config', '', $config_default);

    // API-specific view layer: Smarty stub (no security, no full setup — we don't render
    // templates, but Language and other classes call $GLOBALS['smarty']->assign()).
    $GLOBALS['smarty'] = new Smarty();
    $GLOBALS['smarty']->muteUndefinedOrNullWarnings();
    $GLOBALS['smarty']->compile_dir = CC_SKIN_CACHE_DIR;
    $GLOBALS['smarty']->config_dir  = CC_SKIN_CACHE_DIR;
    $GLOBALS['smarty']->cache_dir   = CC_SKIN_CACHE_DIR;

    $GLOBALS['language']  = Language::getInstance();
    $GLOBALS['hooks']     = HookLoader::getInstance();
    $GLOBALS['tax']       = Tax::getInstance();
    $GLOBALS['catalogue'] = Catalogue::getInstance();
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

// Purge old API log entries (~5% of requests, older than 30 days)
if (executionChance(5)) {
    $cutoff = time() - (30 * 86400);
    $GLOBALS['db']->delete('CubeCart_api_log', array('<' => array('request_time' => $cutoff)));
}

// Allow hooks to intercept API requests
foreach ($GLOBALS['hooks']->load('api.bootstrap') as $hook) {
    include $hook;
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
