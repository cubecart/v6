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
require_once preg_replace('/setup/', '', realpath(dirname(__FILE__))) . 'ini.inc.php';
require_once CC_INCLUDES_DIR . 'functions.inc.php';

$error_log_path = ini_get('error_log');
if(!strstr($error_log_path, '/')) {
    ini_set('error_log', '../'.$error_log_path);
}

@set_time_limit('900');
define('CC_IN_SETUP', true);
define('CC_IN_ADMIN', false);

/*! Check cache folder is writable! */
@chmod(CC_CACHE_DIR, chmod_writable());
if (!is_writable(CC_CACHE_DIR)) {
    $cache_dir = str_replace(CC_ROOT_DIR, '', CC_CACHE_DIR);
    die('<p>Please make sure the following folders are writable in order to continue.</p><pre>' . $cache_dir . '</pre>');
}

$gitignore = CC_ROOT_DIR.'/images/source/.gitignore';
if (file_exists($gitignore)) {
    @unlink($gitignore);
    if (file_exists($gitignore)) {
        die('Please delete the images/source/.gitignore file before proceeding.');
    }
}

$global_file = CC_INCLUDES_DIR . 'global.inc.php';
$setup_path  = CC_ROOT_DIR . '/setup' . '/';

// Load preset database config if available
$cc_setup_file = $setup_path . 'config/cc_setup.php';
$existing_db = null;
$extra_config = null;
if (file_exists($cc_setup_file)) {
    include $cc_setup_file;
}

/**
 * Write the global config array to global.inc.php
 *
 * @param array $config_data Associative array of glob key/value pairs
 * @param string $file_path Path to global.inc.php
 * @return int|false Bytes written or false on failure
 */
function writeGlobalConfig($config_data, $file_path)
{
    $lines = array();
    foreach ($config_data as $key => $value) {
        $value = is_array($value) ? var_export($value, true) : "'".addslashes($value)."'";
        $lines[] = sprintf("\$glob['%s'] = %s;", $key, $value);
    }
    $config = sprintf("<?php\n// See global.inc.php-dist for full list of configuration options\n%s\n", implode("\n", $lines));

    if (file_exists($file_path) && file_get_contents($file_path) !== $config) {
        rename($file_path, $file_path.'-'.date('Ymdgis').'.php');
    }

    return file_put_contents($file_path, $config);
}

session_start();

if (isset($_GET['autoupdate']) && $_GET['autoupdate']) {
    $_SESSION['setup'] = array(); // remove any past upgrade/install data
    $_SESSION['setup']['method'] = 'upgrade';
    $_SESSION['setup']['autoupgrade'] = true;
    httpredir('index.php');
}
// Empty the cache before we start
$GLOBALS['cache'] = Cache::getInstance();
if (!isset($_SESSION['setup']) || (isset($_SESSION['setup']) && empty($_SESSION['setup']))) {
    $GLOBALS['cache']->clear();

    // Remove cached skins
    $skin_cached = glob(CC_CACHE_DIR . 'skin/*.*');
    if ($skin_cached) {
        foreach ($skin_cached as $cache_file) {
            unlink($cache_file);
        }
        unset($skin_cached);
    }

    // Remove all other cache
    $cached = glob(CC_CACHE_DIR . '*.*');
    if ($cached) {
        foreach ($cached as $cache_file) {
            unlink($cache_file);
        }
        unset($cached);
    }
}

$GLOBALS['debug'] = Debug::getInstance();

$proceed   = true;
$retry     = false;
$installed = false;
$restart   = true;

$domain        = parse_url(CC_STORE_URL);
$cookie_domain = strpos($domain['host'], '.') ? '.'.str_replace('www.', '', $domain['host']) : '';

$default_config_settings = array(
  'csrf' => '1',
  'update_main_stock' => '',
  'tax_number' => '',
  'recaptcha_public_key' => '',
  'recaptcha_secret_key' => '',
  'no_skip_processing_check' => '',
  'hide_out_of_stock' => '',
  'force_completed' => '',
  'dispatch_date_format' => 'M d Y',
  'disable_shipping_groups' => '',
  'disable_mobile_skin' => '1',
  'disable_checkout_terms' => '',
  'allow_no_shipping' => '',
  'debug_ip_addresses' => '',
  'twitter' => 'https://x.com',
  'facebook' => 'https://facebook.com',
  'linkedin' => 'https://linkedin.com',
  'vimeo' => 'https://vimeo.com',
  'default_language' => '',
  'default_currency' => '',
  'email_address' => '',
  'store_title' => '',
  'store_name' => '',
  'email_name' => '',
  'admin_notify_status' => '2',
  'catalogue_mode' => '0',
  'debug' => '0',
  'admin_skin' => 'default',
  'skin_folder' => 'foundation',
  'skin_style' => 'default',
  'skin_change' => '0',
  'email_method' => 'mail',
  'seo_metadata' => '2',
  'store_meta_description' => '',
  'recaptcha' => '0',
  'time_format' => 'j M Y, H:i',
  'time_offset' => '0',
  'time_zone' => '',
  'download_expire' => '36000',
  'download_count' => '10',
  'email_smtp' => '0',
  'email_smtp_host' => '',
  'email_smtp_password' => '',
  'email_smtp_port' => '',
  'email_smtp_user' => '',
  'enable_ssl' => '0',
  'cache' => '1',
  'basket_allow_non_invoice_address' => '1',
  'basket_jump_to' => '0',
  'basket_order_expire' => '',
  'basket_out_of_stock_purchase' => '0',
  'basket_tax_by_delivery' => '0',
  'store_country' => '826',
  'store_zone' => '12',
  'catalogue_expand_tree' => '1',
  'catalogue_hide_prices' => '0',
  'catalogue_latest_products_count' => '9',
  'catalogue_latest_products' => '1',
  'catalogue_popular_products_count' => '10',
  'catalogue_popular_products_source' => '0',
  'catalogue_related_products' => '1',
  'catalogue_related_products_count' => '5',
  'catalogue_products_per_page' => '10',
  'catalogue_sale_items' => '10',
  'catalogue_sale_mode' => '1',
  'catalogue_sale_percentage' => '',
  'catalogue_show_empty' => '1',
  'product_weight_unit' => 'Kg',
  'product_size_unit' => 'cm',
  'proxy' => '0',
  'proxy_host' => '',
  'proxy_port' => '',
  'product_precis' => '120',
  'stock_warn_type' => '0',
  'stock_warn_level' => '5',
  'enable_reviews' => '1',
  'store_address' => '',
  'store_copyright' => '<p>&copy;'.date('Y').' '.$domain['host'].' -  All rights reserved.</p>',
  'store_postcode' => '',
  'standard_url' => preg_replace(array('#^https#i','#/setup$#'), array('http',''), CC_STORE_URL),
  'cookie_domain' => $cookie_domain,
  'show_basket_weight' => '1',
  'stock_change_time' => '1',
  'stock_level' => '0',
  'offline' => '0',
  'offline_content' => '<html><head><title>Store Offline</title></head><body><p>We are offline right now. Please visit again soon.</p></body></html>',
  'product_sort_column' => 'name',
  'product_sort_direction' => 'ASC',
  'bftime' => '600',
  'bfattempts' => '5',
  'fuzzy_time_format' => 'H:i',
  'feed_access_key' => randomString(12),
  'seo_add_cats'  => '2',
  'seo_cat_add_cats' => '1',
  'r_admin_activity' => '30',
  'r_admin_error' => '30',
  'r_email' => '30',
  'r_request' => '14',
  'r_staff' => '30',
  'r_system_error' => '7',
  'seo_ext' => '',
  'admin_login_notify' => '1',
  'abandoned_cart_enabled' => '0',
  'abandoned_cart_delay' => '86400',
  'abandoned_cart_notify_cooldown' => '604800',
  'abandoned_cart_order_window' => '259200',
  'abandoned_cart_coupon' => '0',
  'allow_telemetry' => '1'
);

ksort($default_config_settings);

$GLOBALS['debug']->debugTail($_SESSION, '$_SESSION');

$GLOBALS['smarty']               = new Smarty();
$GLOBALS['smarty']->compile_dir  = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->config_dir   = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->cache_dir    = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->template_dir = dirname(__FILE__) . '/';


$language  = Language::getInstance();
$languages = $language->listLanguages();

if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'en-GB';
}

$language->change($_SESSION['language']);

$strings = $language->getStrings();

// Fallback strings when no language files exist on disk (fresh install)
if (empty($strings['common']['continue'])) {
    $strings['common']['continue'] = 'Continue';
}
if (empty($strings['setup']['button_restart'])) {
    $strings['setup']['button_restart'] = 'Restart';
}
if (empty($strings['setup']['button_retry'])) {
    $strings['setup']['button_retry'] = 'Retry';
}
if (empty($strings['gui_message']['errors_detected'])) {
    $strings['gui_message']['errors_detected'] = 'Errors';
}
if (empty($strings['setup']['error_lang_download'])) {
    $strings['setup']['error_lang_download'] = 'Failed to download language pack. Please try again.';
}
if (empty($strings['setup']['error_lang_fetch'])) {
    $strings['setup']['error_lang_fetch'] = 'Unable to fetch available languages. Please check your internet connection and try again.';
}
if (empty($strings['setup']['title_select_language'])) {
    $strings['setup']['title_select_language'] = 'Select Language';
}
if (empty($strings['setup']['select_language_note'])) {
    $strings['setup']['select_language_note'] = 'Please select your language. It will be downloaded and used for the setup process and your store.';
}
if (empty($strings['common']['language'])) {
    $strings['common']['language'] = 'Language';
}

$GLOBALS['smarty']->assign('LANG', $strings);
$GLOBALS['smarty']->assign('VERSION', CC_VERSION);
$GLOBALS['smarty']->assign('ROOT', CC_ROOT_DIR);
$GLOBALS['smarty']->assign('SESSION_LANGUAGE', $_SESSION['language']);
$GLOBALS['smarty']->assign('SHOW_LANG_BACK', !empty($_SESSION['language_selected']));

// Header shown above every step once the method is locked in (e.g. "Install CubeCart 6.6.4"
// or "Upgrade CubeCart 6.6.4"). install_cubecart_title is parameterised; for upgrade we
// compose from the plain label to avoid the marketing-flavoured upgrade_cubecart_title.
if (isset($_SESSION['setup']['method'])) {
    if ($_SESSION['setup']['method'] === 'install') {
        $GLOBALS['smarty']->assign('STEP_HEADING', sprintf($strings['setup']['install_cubecart_title'], CC_VERSION));
    } elseif ($_SESSION['setup']['method'] === 'upgrade') {
        $GLOBALS['smarty']->assign('STEP_HEADING', $strings['setup']['upgrade_cubecart'].' '.CC_VERSION);
    }
}

// AJAX endpoint: pre-submit DB connection test from the install form. Returns JSON
// and exits, so it has to short-circuit before the regular HTML render path below.
if (isset($_POST['test_connection']) && $_POST['test_connection'] === '1') {
    header('Content-Type: application/json');
    $host   = isset($_POST['global']['dbhost'])     ? trim($_POST['global']['dbhost'])     : '';
    $name   = isset($_POST['global']['dbdatabase']) ? trim($_POST['global']['dbdatabase']) : '';
    $user   = isset($_POST['global']['dbusername']) ? trim($_POST['global']['dbusername']) : '';
    $pass   = isset($_POST['global']['dbpassword']) ? $_POST['global']['dbpassword']       : '';
    $port   = !empty($_POST['global']['dbport'])    ? (int)$_POST['global']['dbport']      : (int)ini_get('mysqli.default_port');
    $socket = !empty($_POST['global']['dbsocket'])  ? $_POST['global']['dbsocket']         : ini_get('mysqli.default_socket');

    if ($host === '' || $user === '' || $name === '') {
        echo json_encode(array('ok' => false, 'message' => $strings['setup']['test_connection_missing']));
        exit;
    }
    if (!function_exists('mysqli_connect')) {
        echo json_encode(array('ok' => false, 'message' => $strings['setup']['error_mysqli_missing']));
        exit;
    }
    try {
        mysqli_report(MYSQLI_REPORT_OFF);
        $mysqli = @new mysqli($host, $user, $pass, $name, $port, $socket);
        if ($mysqli->connect_errno) {
            echo json_encode(array('ok' => false, 'message' => $strings['setup']['error_db_incorrect_something'].' '.$mysqli->connect_error));
        } else {
            echo json_encode(array('ok' => true, 'message' => $strings['setup']['test_connection_ok']));
            $mysqli->close();
        }
    } catch (Exception $e) {
        echo json_encode(array('ok' => false, 'message' => $strings['setup']['error_db_incorrect_something'].' '.$e->getMessage()));
    }
    exit;
}

// Detect upgrade (skip language selection stage)
$is_upgrade = false;
if (file_exists($global_file) && filesize($global_file) > 0) {
    include $global_file;
    if (isset($glob['installed']) && $glob['installed']) {
        $is_upgrade = true;
    }
    unset($glob);
}

// Reset selected language: delete the downloaded pack files and clear the session
// flag so the user lands back on the language-select screen with a clean slate.
if (isset($_GET['reset_language']) || isset($_POST['reset_language'])) {
    $code = isset($_SESSION['language']) ? $_SESSION['language'] : '';
    if ($code !== '' && preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $code)) {
        $files_to_remove = array(
            CC_LANGUAGE_DIR . $code . '.xml',
            CC_LANGUAGE_DIR . 'email_' . $code . '.xml',
        );
        foreach ($files_to_remove as $f) {
            if (file_exists($f) && is_writable($f)) {
                @unlink($f);
            }
        }
    }
    unset($_SESSION['language'], $_SESSION['language_selected']);
    httpredir('index.php');
}

// Handle language selection POST (fresh install only)
if (isset($_POST['select_language'])) {
    $selected_code = $_POST['select_language'];
    $download_ok = false;

    // Fetch API to get download URL
    $api_url_path = '/api/languages';
    $request = new Request('extensions.cubecart.com', $api_url_path, 443, false, true, 10);
    $request->setMethod('get');
    $request->setSSL();
    $request->skiplog(true);
    $json = $request->send();
    if (!$json) {
        $json = file_get_contents('https://extensions.cubecart.com'.$api_url_path);
    }
    if ($json) {
        $api_data = json_decode($json, true);
        if ($api_data && !empty($api_data['languages'])) {
            foreach ($api_data['languages'] as $api_lang) {
                if ($api_lang['code'] === $selected_code) {
                    // Download ZIP
                    $url_parts = parse_url($api_lang['download']);
                    $dl_request = new Request($url_parts['host'], $url_parts['path'], 443, false, true, 30);
                    $dl_request->setMethod('get');
                    $dl_request->setSSL();
                    $dl_request->skiplog(true);
                    $zip_data = $dl_request->send();
                    if (!$zip_data) {
                        $zip_data = file_get_contents($api_lang['download']);
                    }
                    if ($zip_data) {
                        $tmp_path = CC_CACHE_DIR . $selected_code . '.zip';
                        file_put_contents($tmp_path, $zip_data);
                        $zip = new ZipArchive();
                        if ($zip->open($tmp_path) === true) {
                            for ($i = 0; $i < $zip->numFiles; $i++) {
                                $entry = $zip->getNameIndex($i);
                                $file_data = $zip->getFromIndex($i);
                                if ($file_data === false) continue;
                                if (preg_match('/\.png$/i', $entry)) {
                                    file_put_contents(CC_LANGUAGE_DIR . 'flags/' . basename($entry), $file_data);
                                } else {
                                    file_put_contents(CC_LANGUAGE_DIR . basename($entry), $file_data);
                                }
                            }
                            $zip->close();
                            $download_ok = true;
                        }
                        if (file_exists($tmp_path)) {
                            unlink($tmp_path);
                        }
                    }
                    break;
                }
            }
        }
    }

    if ($download_ok) {
        $_SESSION['language'] = $selected_code;
        $_SESSION['language_selected'] = true;
        httpredir('index.php');
    } else {
        $errors[] = $strings['setup']['error_lang_download'];
    }
} elseif (isset($_POST['proceed'])) {
    $redir = true;
    if (!isset($_SESSION['setup'])) {
        $_SESSION['setup'] = array();
    } else {
        if (!isset($_POST['method']) && !isset($_SESSION['setup']['method'])) {
            $errors[] = $strings['setup']['error_action_required'];
            $redir    = false;
        }
        if (isset($_POST['method'])) {
            $_SESSION['setup']['method'] = $_POST['method'];
        } elseif (isset($_POST['permissions'])) {
            $_SESSION['setup']['permissions'] = true;
        } elseif (isset($_POST['progress'])) {
            $redir = false;
        }
    }
    if (!isset($errors) && $redir) {
        httpredir('index.php');
    }
} elseif (isset($_POST['cancel']) || isset($_GET['cancel'])) {
    $_SESSION['setup'] = array();
    httpredir('index.php', 'cancelled');
}

if (!$is_upgrade && !isset($_SESSION['language_selected'])) {
    // Language selection stage (fresh install only, no language files on disk yet)
    $restart = false;
    $api_url_path = '/api/languages';
    $request = new Request('extensions.cubecart.com', $api_url_path, 443, false, true, 10);
    $request->setMethod('get');
    $request->setSSL();
    $request->skiplog(true);
    $json = $request->send();
    if (!$json) {
        $json = file_get_contents('https://extensions.cubecart.com'.$api_url_path);
    }
    if ($json) {
        $api_data = json_decode($json, true);
        if ($api_data && !empty($api_data['languages'])) {
            // Native names for the more widely-shipped CubeCart packs. Falls back to the
            // API-provided English name when a code isn't listed here.
            $native_names = array(
                'en-GB' => 'English (UK)',     'en-US' => 'English (US)',
                'fr-FR' => 'Français',         'de-DE' => 'Deutsch',
                'es-ES' => 'Español',          'it-IT' => 'Italiano',
                'pt-PT' => 'Português',        'pt-BR' => 'Português (Brasil)',
                'nl-NL' => 'Nederlands',       'pl-PL' => 'Polski',
                'ru-RU' => 'Русский',          'zh-CN' => '中文 (简体)',
                'ja-JP' => '日本語',            'ko-KR' => '한국어',
                'ar-SA' => 'العربية',          'tr-TR' => 'Türkçe',
                'sv-SE' => 'Svenska',          'da-DK' => 'Dansk',
                'no-NO' => 'Norsk',            'fi-FI' => 'Suomi',
                'cs-CZ' => 'Čeština',          'el-GR' => 'Ελληνικά',
                'bg-BG' => 'Български',        'ro-RO' => 'Română',
                'hu-HU' => 'Magyar',           'vi-VN' => 'Tiếng Việt',
                'th-TH' => 'ไทย',              'id-ID' => 'Bahasa Indonesia',
                'ms-MY' => 'Bahasa Melayu',    'he-IL' => 'עברית',
                'uk-UA' => 'Українська',       'hi-IN' => 'हिन्दी',
                'sk-SK' => 'Slovenčina',       'hr-HR' => 'Hrvatski',
                'sl-SI' => 'Slovenščina',      'lt-LT' => 'Lietuvių',
                'lv-LV' => 'Latviešu',         'et-EE' => 'Eesti',
            );

            // Pick a default pre-selection from the browser's Accept-Language header.
            // Sort entries by quality factor desc, then try exact match, then primary-language fallback.
            $available_codes = array();
            foreach ($api_data['languages'] as $api_lang) {
                $available_codes[] = $api_lang['code'];
            }
            $best_match = 'en-GB';
            $accept_raw = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
            if ($accept_raw !== '') {
                $prefs = array();
                foreach (explode(',', $accept_raw) as $entry) {
                    $entry = trim($entry);
                    if ($entry === '') continue;
                    $q = 1.0;
                    $tag = $entry;
                    if (strpos($entry, ';') !== false) {
                        list($tag, $rest) = explode(';', $entry, 2);
                        if (preg_match('/q\s*=\s*([0-9.]+)/i', $rest, $m)) {
                            $q = (float)$m[1];
                        }
                    }
                    $prefs[] = array('tag' => trim($tag), 'q' => $q);
                }
                usort($prefs, function ($a, $b) {
                    if ($a['q'] === $b['q']) return 0;
                    return ($a['q'] < $b['q']) ? 1 : -1;
                });
                $available_lc = array_map('strtolower', $available_codes);
                foreach ($prefs as $p) {
                    $tag_lc = strtolower($p['tag']);
                    $i = array_search($tag_lc, $available_lc, true);
                    if ($i !== false) {
                        $best_match = $available_codes[$i];
                        break;
                    }
                    $primary = strtolower(strtok($p['tag'], '-'));
                    $hit = false;
                    foreach ($available_lc as $i => $code_lc) {
                        if (strtok($code_lc, '-') === $primary) {
                            $best_match = $available_codes[$i];
                            $hit = true;
                            break;
                        }
                    }
                    if ($hit) break;
                }
            }

            $api_lang_list = array();
            foreach ($api_data['languages'] as $api_lang) {
                $code = $api_lang['code'];
                $api_lang_list[] = array(
                    'code'        => $code,
                    'name'        => $api_lang['name'],
                    'name_native' => isset($native_names[$code]) ? $native_names[$code] : $api_lang['name'],
                    'selected'    => ($code === $best_match) ? ' selected="selected"' : '',
                );
            }
            $GLOBALS['smarty']->assign('API_LANGUAGES', $api_lang_list);
        }
    }
    if (!isset($api_lang_list) || empty($api_lang_list)) {
        $errors[] = $strings['setup']['error_lang_fetch'];
        $retry = true;
        $proceed = false;
    }
    $GLOBALS['smarty']->assign('MODE_LANGUAGE', true);
} elseif (!isset($_SESSION['setup'])) {
    $restart = false;
    $step    = 1;
    // Compatibility Test
    $checks  = array(
    'PHP' => array(
      'title' => 'PHP 7.4+ (8.4 Recommended)',
      'status' => version_compare(PHP_VERSION, '7.4', '>='),
      'pass' => PHP_VERSION,
      'fail' => PHP_VERSION
    ),
    'MySQL' => array(
      'title' => 'MySQL 5.7+ / MariaDB 10.3+',
      'status' => extension_loaded('mysqli'),
      'pass' => (function_exists('mysqli_get_client_info')) ? mysqli_get_client_info() : $strings['setup']['error_bad_db_ext'],
      'fail' => $strings['setup']['error_mysqli_missing']
    ),
    'GD' => array(
      'title' => 'GD Image Library',
      'status' => detectGD(),
      'pass' => $strings['common']['installed'],
      'fail' => $strings['common']['not_installed']
    ),
    'XML' => array(
      'title' => 'Simple XML Parser',
      'status' => extension_loaded('simplexml'),
      'pass' => $strings['common']['installed'],
      'fail' => $strings['common']['not_installed']
    ),
    'cURL' => array(
      'title' => 'cURL',
      'status' => extension_loaded('curl'),
      'pass' => $strings['common']['installed'],
      'fail' => $strings['common']['not_installed']
    ),
    'Zip' => array(
      'title' => 'Zip (ZipArchive)',
      'status' => class_exists('ZipArchive'),
      'pass' => $strings['common']['installed'],
      'fail' => $strings['common']['not_installed']
    ),
    'mbstring' => array(
      'title' => 'mbstring (Multibyte String)',
      'status' => extension_loaded('mbstring'),
      'pass' => $strings['common']['installed'],
      'fail' => $strings['common']['not_installed']
    )
  );
  $status = true;
  foreach($checks as $check_type => $data) {
    foreach($data as $key => $value) {
        if($key=='status') {
            if(!$value) {
                $status = false;
                break;
            }
        }
    }
  }
  if(!$status) {
    $errors[] = $strings['setup']['error_hosting_compat'];
    $retry = true;
    $proceed = false;
  } else {
    // All compatibility checks pass — skip the screen and advance straight to method
    // selection. The user will only ever see this step if there's something to fix.
    $_SESSION['setup'] = array();
    httpredir('index.php');
  }

  $GLOBALS['smarty']->assign('CHECKS', $checks);
  $GLOBALS['smarty']->assign('MODE_COMPAT', true);
} else {
    if (!isset($_SESSION['setup']['method'])) {
        $step = 2;
        // Select Install/Upgrade — first interactive page, nothing to restart from.
        $restart = false;
        $GLOBALS['smarty']->assign('LANG_INSTALL_CUBECART_TITLE', sprintf($strings['setup']['install_cubecart_title'], CC_VERSION));
        // Check if upgrading is possible
        if (file_exists($global_file) && filesize($global_file) > 0) {
            include $global_file;
            $installed = (isset($glob['installed'])) ? (bool) $glob['installed'] : false;
            unset($glob);
        }
        if ($installed) {
            $GLOBALS['smarty']->assign('LANG_UPGRADE_CUBECART_TITLE', sprintf($strings['setup']['upgrade_cubecart_title'], CC_VERSION));
            $GLOBALS['smarty']->assign('SHOW_UPGRADE', true);
            $GLOBALS['smarty']->assign('MODE_METHOD', true);
        } else {
            // No existing install — install is the only option, skip the method-choice screen.
            $_SESSION['setup']['method'] = 'install';
            httpredir('index.php');
        }
    } elseif (!isset($_SESSION['setup']['complete'])) {
        if (in_array($_SESSION['setup']['method'], array(
            'install', 'upgrade'))) {
            require_once 'setup.' . $_SESSION['setup']['method'] . '.php';
        } else {
            require_once 'setup.install.php';
        }
    } else {
        // Install/Upgrade Complete
        // Upgrade Main Configuration
        clearstatcache(true, $global_file);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($global_file, true);
        }
        include $global_file;
        $GLOBALS['db'] = Database::getInstance($glob);

        // Move to scripts folder?
        $config_rows = $db->select('CubeCart_config', array('config_key', 'config_value'), array('name' => 'config'));
        $main_config = array();
        if ($config_rows) {
            foreach ($config_rows as $row) {
                $main_config[$row['config_key']] = $row['config_value'];
            }
        }

        if ($_SESSION['setup']['config_update'] && is_array($main_config)) {
            // Remove unused keys
            $dead_keys = array(
        'cat_newest_first',
        'captcha_private',
        'captcha_public',
        'copyright',
        'currecyAuto',
        'currencyAuto',
        'dateFormat',
        'ftp_server',
        'ftp_username',
        'ftp_password',
        'ftp_root_dir',
        'gdGifSupport',
        'gdmaxImgSize',
        'gdquality',
        'gdthumbSize',
        'gdversion',
        'maxImageUploadSize',
        'imgGalleryType',
        'richTextEditor',
        'rteHeight',
        'rteHeightUnit',
        'sefprodnamefirst',
        'show_category_count',
        'sqlSessionExpiry',
        'taxCountry',
        'taxCounty',
        'uploadSize',
        'noRelatedProds'
      );
            // Rename existing keys
            $remapped  = array(
        'dirSymbol' => 'defualt_directory_symbol',
        'installTime' => 'install_time',
        'defaultCurrency' => 'default_currency',
        'defaultLang' => 'default_language',
        'dirSymbol' => 'default_directory_symbol',
        'dnLoadExpire' => 'download_expire',
        'dnLoadTimes' => 'download_count',
        'mailMethod' => 'email_method',
        'masterName' => 'email_name',
        'masterEmail' => 'email_address',
        'disable_alert_email' => 'email_disable_alert',
        'smtpAuth' => 'email_smtp',
        'smtpHost' => 'email_smtp_host',
        'smtpPassword' => 'email_smtp_password',
        'smtpPort' => 'email_smtp_port',
        'smtpUsername' => 'email_smtp_user',
        'hide_prices' => 'catalogue_hide_prices',
        'cat_tree' => 'catalogue_expand_tree',
        'productPages' => 'catalogue_products_per_page',
        'showLatestProds' => 'catalogue_latest_products',
        'noLatestProds' => 'catalogue_latest_products_count',
        'show_empty_cat' => 'catalogue_show_empty',
        'noPopularBoxItems' => 'catalogue_popular_products_count',
        'pop_products_source' => 'catalogue_popular_products_source',
        'saleMode' => 'catalogue_sale_mode',
        'noSaleBoxItems' => 'catalogue_sale_items',
        'salePercentOff' => 'catalogue_sale_percentage',
        'productPrecis' => 'product_precis',
        'weightUnit' => 'product_weight_unit',
        'stockLevel' => 'stock_level',
        'add_to_basket_act' => 'basket_jump_to',
        'shipAddressLock' => 'basket_allow_non_invoice_address',
        'outofstockPurchase' => 'basket_out_of_stock_purchase',
        'priceTaxDelInv' => 'basket_tax_by_delivery',
        'orderExpire' => 'basket_order_expire',
        'offLine' => 'offline',
        'offLineContent' => 'offline_content',
        'proxyHost' => 'proxy_host',
        'proxyPort' => 'proxy_port',
        'sef' => 'seo',
        'sefserverconfig' => 'seo_method',
        'seftags' => 'seo_metadata',
        'storeAddress' => 'store_address',
        'storeName' => 'store_name',
        'postcode' => 'store_postcode',
        'siteCountry' => 'store_country',
        'siteCounty' => 'store_zone',
        'siteTitle' => 'store_title',
        'metaDescription' => 'store_meta_description',
        'metaKeyWords' => 'store_meta_keywords',
        'skinDir' => 'skin_folder',
        'skinStyle' => 'skin_style',
        'changeskin' => 'skin_change',
        'timeFormat' => 'time_format',
        'timeOffset' => 'time_offset',
        'timezone' => 'time_zone',
        'floodControl' => 'recaptcha'
      );

            ## Remap store country from id to numcode
            if (isset($main_config['siteCountry']) && $main_config['siteCountry'] > 0) {
                $country                    = $db->select('CubeCart_geo_country', array(
          'numcode'
        ), array(
          'id' => $main_config['siteCountry']
        ));
                $main_config['siteCountry'] = $country[0]['numcode'];
            }

            ## Parse
            $new_config = array();
            foreach ($main_config as $key => $value) {
                if (in_array($key, $dead_keys)) {
                    unset($main_config[$key]);
                    continue;
                } else {
                    if (isset($remapped[$key])) {
                        $new_config[$remapped[$key]] = $value;
                        unset($main_config[$key]);
                    }
                }
            }

            if ($new_config['recaptcha'] == 'recaptcha') {
                $new_config['recaptcha'] = true;
            }
            if (file_exists(CC_LANGUAGE_DIR . $main_config['default_language'] . '.xml')) {
                $default_language = $main_config['default_language'];
            } elseif (isset($_SESSION['setup']['long_lang_identifier']) && file_exists(CC_LANGUAGE_DIR . $_SESSION['setup']['long_lang_identifier'] . '.xml')) {
                $default_language = $_SESSION['setup']['long_lang_identifier'];
            } else {
                $default_language = isset($_SESSION['setup']['config']['default_language']) ? $_SESSION['setup']['config']['default_language'] : 'en-GB';
            }

            if (!file_exists(CC_LANGUAGE_DIR . $default_language . '.xml')) {
                $default_language = 'en-GB';
            }

            ## Redefine the default skin
            $reset      = array(
        'skin_folder' => 'foundation',
        'skin_style' => 'default',
        'default_language' => $default_language
      );
            $new_config = array_merge($main_config, $new_config, $reset);
            ## Set some defaults
            $defaults   = array(
        'admin_skin' => 'default',
        'cache' => '1',
        'enable_reviews' => true,
        'show_basket_weight' => true
      );
            $new_config = array_merge($defaults, $new_config);
            ksort($new_config);

            // Write new config to database
            $db->delete('CubeCart_config', array('name' => 'config'));
            foreach ($new_config as $cfg_key => $cfg_value) {
                $db->insert('CubeCart_config', array(
                    'name'         => 'config',
                    'config_key'   => $cfg_key,
                    'config_value' => is_array($cfg_value) ? json_encode($cfg_value) : (string)$cfg_value
                ));
            }
            $_SESSION['setup']['config_update'] = true;
        }

        $proceed = false;
        $restart = true;
        $step    = 6;
        switch ($_SESSION['setup']['method']) {
      case 'install':
        $GLOBALS['smarty']->assign('MODE_COMPLETE_INSTALL', true);
        break;
      case 'upgrade':
        $GLOBALS['smarty']->assign('MODE_COMPLETE_UPGRADE', true);
        break;
    }
        $GLOBALS['smarty']->assign('MODE_COMPLETE', true);
        // delete setup folder on admin login
        $date = new Datetime(date('r',time()+7200));
        $attributes = '';
        $attributes .= ';Path=/';
        $attributes .= ';Expires='.$date->format(DateTime::COOKIE);
        $attributes .= ';SameSite=None';
        $attributes .= ';Secure';
        $attributes .= ';HttpOnly';
        header('Set-Cookie: cc_delete_setup=1'.$attributes);

        //Attempt admin file and folder rename
        if (!isset($_SESSION['setup']['admin_rename']) && (file_exists('../admin') || file_exists('../admin.php'))) {
            $adminFolder = 'admin_'.randomString(6);
            $adminFile   = 'admin_'.randomString(6).'.php';
            $update_config = false;

            rename('../'.$glob['adminFolder'], '../'.$adminFolder);
            rename('../'.$glob['adminFile'], '../'.$adminFile);

            if (file_exists('../'.$adminFolder)) {
                $update_config = true;
            } else {
                $adminFolder = $glob['adminFolder'];
            }

            if (file_exists('../'.$adminFile)) {
                $update_config = true;
            } else {
                $adminFile   = $glob['adminFile'];
            }

            if ($update_config) {
                $_SESSION['setup']['admin_rename'] = true;
                if(is_array($glob) && !empty($glob)) {
                    $glob['adminFile'] = $adminFile;
                    $glob['adminFolder'] = $adminFolder;
                    writeGlobalConfig($glob, $global_file);
                }
            }
            $adminURL = str_replace('/setup', '', CC_STORE_URL).'/'.$adminFile;
            if ($admins = $db->select('CubeCart_admin_users', false, array('status'=> 1))) {
                $headers = 'From: nobody@'.parse_url(CC_STORE_URL, PHP_URL_HOST);
                foreach ($admins as $admin) {
                    mail($admin['email'], $strings['setup']['email_admin_url_subject'], sprintf($strings['setup']['email_admin_url_body'], html_entity_decode($admin['name'], ENT_QUOTES), CC_VERSION, $adminURL), $headers);
                }
            }
            $GLOBALS['smarty']->assign('ADMIN_URL', $adminURL);
            $GLOBALS['smarty']->assign('STORE_URL', str_replace('/setup', '', CC_STORE_URL).'/');
            $GLOBALS['smarty']->assign('SHOW_LINKS', true);
        }

        // secure global files
        $gfs = glob(CC_INCLUDES_DIR.'global.*.php');
        if (is_array($gfs)) {
            foreach ($gfs as $gf) {
                chmod($gf, 0444);
            }
        }

        /* Truncate CubeCart_system_error_log table. There are a number of failed SQL queries on upgrade depending
         * on to/from version. We need a clean slate to detect operational errors.
         */
        $db->truncate('CubeCart_system_error_log');
        include $global_file;
        if ($_SESSION['setup']['autoupgrade'] && !$update_config) {
            httpredir('../'.$glob['adminFile'].'?_g=maintenance&node=index', 'upgrade');
        }
    }
}

## Display error messages
if (isset($errors) && is_array($errors)) {
    $vars['errors'] = $errors;
    $GLOBALS['smarty']->assign('GUI_MESSAGE', $vars);
}

## Build Logos
function build_logos($image_name = '')
{
    global $db;

    $logo_path = empty($image_name) ? 'skins/foundation/images/default/logo/default.png' : 'images/logos/'.$image_name;

    $logo_config = array(
    'foundationdefault' => $logo_path,
    'emails' => $logo_path,
    'invoices' => $logo_path
  );

    foreach ($logo_config as $key => $value) {
        $db->insert('CubeCart_config', array(
            'name'         => 'logos',
            'config_key'   => $key,
            'config_value' => $value
        ));
    }
}

## Controller elements
if ($proceed) {
    $vars['controller']['continue'] = true;
}
if ($retry) {
    $vars['controller']['retry'] = true;
}
if ($restart) {
    $vars['controller']['restart'] = true;
}
if (isset($vars['controller'])) {
    $GLOBALS['smarty']->assign('CONTROLLER', $vars['controller']);
}

$GLOBALS['smarty']->assign('COPYRIGHT_YEAR', date('Y'));
$GLOBALS['smarty']->display('skin.install.php');