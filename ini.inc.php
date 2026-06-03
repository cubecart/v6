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
if (version_compare(PHP_VERSION, '7.4') == -1) {
    die("PHP ".PHP_VERSION." detected. CubeCart requires PHP 7.4 or higher.");
}

// Display important errors before debug class is initialised
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE);
ini_set('display_errors', false);

/************* CUSTOMISED PHP.INI SETTINGS *************/

ini_set('arg_separator.output', '&'); // Set argument separator to & HTML validity
ini_set('default_charset', 'UTF-8');  // Set default charset as 'UTF-8'
ini_set('default_mimetype', 'text/html'); // Set default mimetype as 'text/html'

if (!ini_get('output_buffering') && !defined('CC_CLI') && !defined('CC_API_REQUEST')) {   // Enable Zlib Compression, but only if output buffering is disabled (web only — breaks CLI/API output)
    ini_set('zlib.output_compression', true);
    ini_set('zlib.output_compression_level', 7);
}
ini_set('session.name', 'PHPSESSID');  // Customise the session name here, if you feel PHPSESSID is too insecure
ini_set('session.auto_start', false);  // We don't want to auto start session on every request - the session class will handle it all

// Windows/IIS can be a pain in CGI mode - these settings try to alleviate our suffering
if (stristr(PHP_OS, 'WIN') && stristr($_SERVER['SERVER_SOFTWARE'], 'IIS')) {
    switch (strtolower(PHP_SAPI)) {
    case 'cgi-fcgi':
        ini_set('fastcgi.impersonate', true);
        // no break
    case 'cgi':
        ini_set('cgi.rfc2616_headers', true); // Set RFC2616 compliant headers for Windows servers running in CGI mode
        ini_set('cgi.force_redirect', false); // Disable force redirect
        break;
    }
}

/************* CUBECART SPECIFIC SETTINGS *************/
define('CC_VERSION', '6.7.4');     // Version Number
define('CC_INI_SET', true);      // Stop includes and the like from being executed on their own
define('CC_DS', DIRECTORY_SEPARATOR);   // Deprecated but kept for backward compatibility
define('CC_PS', PATH_SEPARATOR);

## Define Permission Constants
define('CC_PERM_READ', 1);
define('CC_PERM_EDIT', 2);
define('CC_PERM_DELETE', 4);
define('CC_PERM_FULL', 7);

define('CC_ROOT_DIR', realpath(dirname(__FILE__))); // Set Root Directory

define('CC_CACHE_DIR', CC_ROOT_DIR.'/cache/');
define('CC_FILES_DIR', CC_ROOT_DIR.'/files/');
define('CC_BACKUP_DIR', CC_ROOT_DIR.'/backup/');
define('CC_SKIN_CACHE_DIR', CC_CACHE_DIR.'skin/');

define('CC_CLASSES_DIR', CC_ROOT_DIR.'/classes/');
define('CC_INCLUDES_DIR', CC_ROOT_DIR.'/includes/');

define('CC_LANGUAGE_DIR', CC_ROOT_DIR.'/language/');

date_default_timezone_set('UTC');  // Set the default timezone for the scripts until the config gets loaded and overrides it

// Automatically detect and assign the store url, and root relative path
$server_name = (!empty($_SERVER['HTTP_HOST'])) ? strtolower($_SERVER['HTTP_HOST']) :  strtolower($_SERVER['SERVER_NAME']);
$script_name = (isset($_SERVER['PHP_SELF']) && !empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : $_SERVER['REQUEST_URI'];
$script_name = preg_replace('/[^a-z0-9-_.~\/]/i', '', $script_name);
$script_path = trim(dirname($script_name));
$script_path = str_replace('\\', '/', $script_path);
$script_path = preg_replace('#[\\\\/]{2,}#', '/', $script_path);
$url = 'https://'.$server_name.$script_path;
// Remove index.php/anything
if (strstr($url, '/index.php')) {
    $url = substr($url, 0, strpos($url, '/index.php'));
}

// Set min value for script path as /
if (substr($script_path, -1) != '/') {
    $script_path .= '/';
}

if (substr($url, -1) == '/') {
    $url = substr($url, 0, -1);
}

## Force SSL Always
if (file_exists(CC_ROOT_DIR.'/ssl-custom.inc.php')) {
    include CC_ROOT_DIR.'/ssl-custom.inc.php';
} else {
    ## Detect if SSL is enabled
    if (
        (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (isset($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false)
    ) {
        define('CC_SSL', true);
    } else {
        define('CC_SSL', false);
    }
}

if(!CC_SSL) {
    header('Location: '.$url, true, 301); // $url will always have https protocol
    exit;
}

## Check for the global file if not in setup mode
if (!strstr($_SERVER['SCRIPT_NAME'], '/setup/')) {
    // If we are in setup we don't need the url to have /setup
    if (substr($url, -6) == '/setup') {
        $url = substr($url, 0, -5);
    }
    if (file_exists(CC_INCLUDES_DIR.'global.inc.php')) {
        require CC_INCLUDES_DIR.'global.inc.php';
        ## Lets check that the installed flag has been set
        if (!$glob['installed'] || !isset($glob['dbdatabase'])) {
            header('Location: setup/index.php');
            exit;
        }
        // Pin store URL to file config so a forged Host header cannot poison
        // transactional email links (GHSA-7pvc-gxc4-chmc).
        if (!empty($glob['standard_url'])) {
            $url = rtrim($glob['standard_url'], '/');
        }
    } else {
        ## If global.inc.php doesn't exists, then we should probably run the installer
        header('Location: setup/index.php');
        exit;
    }
}

define('CC_STORE_URL', $url);
define('CC_ROOT_REL', $script_path);

$GLOBALS['rootRel'] = CC_ROOT_REL;
$GLOBALS['storeURL'] = CC_STORE_URL;

/************* DEFAULT CUBECART CONFIG *************/

$config_default = array(
    'rootRel'  => CC_ROOT_REL,
    'storeURL'  => CC_STORE_URL
);

// Include a custom ini file, if it exists
if (file_exists(CC_ROOT_DIR.'/ini-custom.inc.php')) {
    include CC_ROOT_DIR.'/ini-custom.inc.php';
}
