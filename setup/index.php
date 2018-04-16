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
require_once preg_replace('/setup/', '', realpath(dirname(__FILE__))) . 'ini.inc.php';
require_once CC_INCLUDES_DIR . 'functions.inc.php';

@ini_set('memory_limit', '512M');
@set_time_limit('600');
define('SKIP_DB_SETUP', true);

/*! Check cache folder is writable! */
@chmod(CC_CACHE_DIR, chmod_writable());
if (!is_writable(CC_CACHE_DIR)) {
  $cache_dir = str_replace(CC_ROOT_DIR, '', CC_CACHE_DIR);
  die('<p>Please make sure the following folders are writable in order to continue.</p><pre>' . $cache_dir . '</pre>');
}

$gitignore = CC_ROOT_DIR.'/images/source/.gitignore';
if(file_exists($gitignore)) {
  @unlink($gitignore);
  if(file_exists($gitignore)) {
    die('Please delete the images/source/.gitignore file before proceeding.');
  }
}

$global_file = CC_INCLUDES_DIR . 'global.inc.php';
$setup_path  = CC_ROOT_DIR . '/setup' . '/';

session_start();

if (isset($_GET['autoupdate']) && $_GET['autoupdate']) {
  $_SESSION['setup']                = ''; // remove any past upgrade/install data
  $_SESSION['setup']['method']      = 'upgrade';
  $_SESSION['setup']['autoupgrade'] = true;
  httpredir('index.php');
}
// Empty the cache before we start
$GLOBALS['cache'] = Cache::getInstance();
if (!isset($_SESSION['setup']) || is_null($_SESSION['setup'])) {
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
$cookie_domain = strpos($domain['host'],'.') ? '.'.str_replace('www.', '', $domain['host']) : '';

$default_config_settings = array(
  'csrf' => '1',
  'update_main_stock' => '',
  'tax_number' => '',
  'recaptcha_public_key' => '',
  'recaptcha_secret_key' => '',
  'no_skip_processing_check' => '',
  'hide_out_of_stock' => '',
  'force_completed' => '',
  'dispatch_date_format' => '%b %d %Y',
  'disable_shipping_groups' => '',
  'disable_mobile_skin' => '1',
  'disable_checkout_terms' => '',
  'allow_no_shipping' => '',
  'cookie_dialogue' => '',
  'debug_ip_addresses' => '',
  'twitter' => 'cubecart',
  'facebook' => 'cubecart',
  'linkedin' => 'cubecart',
  'vimeo' => 'cubecart',
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
  'default_rss_feed' => 'https://forums.cubecart.com/forum/1-news-announcements.xml',
  'email_method' => 'mail',
  'seo_metadata' => '1',
  'store_meta_keywords' => '',
  'store_meta_description' => '',
  'recaptcha' => '0',
  'time_format' => '%d %b %Y, %H:%M',
  'time_offset' => '0',
  'time_zone' => 'Europe/London',
  'download_expire' => '36000',
  'download_count' => '10',
  'email_smtp' => '0',
  'email_smtp_host' => '',
  'email_smtp_password' => '',
  'email_smtp_port' => '',
  'email_smtp_user' => '',
  'enable_ssl' => '0',
  'google_analytics' => '',
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
  'catalogue_products_per_page' => '10',
  'catalogue_sale_items' => '10',
  'catalogue_sale_mode' => '1',
  'catalogue_sale_percentage' => '',
  'catalogue_show_empty' => '1',
  'product_weight_unit' => 'Kg',
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
  'standard_url' => preg_replace(array('#^https#i','#/setup$#'),array('http',''), CC_STORE_URL),
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
  'fuzzy_time_format' => '%H:%M',
  'feed_access_key' => randomString(12),
  'seo_add_cats'  => '2',
  'seo_cat_add_cats' => '1',
  'r_admin_activity' => '30',
  'r_admin_error' => '30',
  'r_email' => '30',
  'r_request' => '30',
  'r_staff' => '30',
  'r_system_error' => '30'
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

if (isset($_POST['language'])) {
  $_SESSION['language'] = $_POST['language'];
  httpredir('index.php', 'language');
} else {
  if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'en-GB';
  }
}

$language->change($_SESSION['language']);

if (is_array($languages)) {
  foreach ($languages as $code => $lang) {
    $lang['selected'] = ($code == $_SESSION['language']) ? ' selected="selected"' : '';
    $GLOBALS['smarty']->append('LANG_LIST', $lang);
  }
}
$strings = $language->getStrings();
$GLOBALS['smarty']->assign('LANG', $strings);
$GLOBALS['smarty']->assign('VERSION', CC_VERSION);
$GLOBALS['smarty']->assign('ROOT', CC_ROOT_DIR);

if (isset($_POST['proceed'])) {
  $redir = true;
  if (!isset($_SESSION['setup']) || is_null($_SESSION['setup'])) {
    $_SESSION['setup'] = array();
  } else {
    if (!isset($_POST['method']) && !isset($_SESSION['setup']['method'])) {
      $errors[] = $strings['setup']['error_action_required'];
      $redir    = false;
    }
    if (isset($_SESSION['setup']['method']) && !isset($_POST['licence']) && !isset($_SESSION['setup']['licence'])) {
      $errors[] = $strings['setup']['error_accept_licence'];
      $redir    = false;
    }
    if (isset($_POST['method'])) {
      $_SESSION['setup']['method'] = $_POST['method'];
    } else if (isset($_POST['licence'])) {
      $_SESSION['setup']['licence'] = true;
    } else if (isset($_POST['permissions'])) {
      $_SESSION['setup']['permissions'] = true;
    } else if (isset($_POST['progress'])) {
      $redir = false;
    }
  }
  if (!isset($errors) && $redir) {
    httpredir('index.php');
  }
} else if (isset($_POST['cancel']) || isset($_GET['cancel'])) {
  $_SESSION['setup'] = null;
  httpredir('index.php', 'cancelled');
}

if (!isset($_SESSION['setup']) || is_null($_SESSION['setup'])) {
  $restart = false;
  $step    = 1;
  // Compatibility Test
  $checks  = array(
    'PHP' => array(
      'title' => 'PHP 5.4+ (5.6 Recommended)',
      'status' => version_compare(PHP_VERSION, '5.4', '>='),
      'pass' => PHP_VERSION,
      'fail' => PHP_VERSION
    ),
    'MySQL' => array(
      'title' => 'MySQL 5.5+',
      'status' => (extension_loaded('mysqli') || extension_loaded('mysql')),
      'pass' => (function_exists('mysqli_get_client_info')) ? mysqli_get_client_info() : mysql_get_client_info(),
      'fail' => (function_exists('mysqli_get_client_info')) ? mysqli_get_client_info() : mysql_get_client_info()
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
    )
  );
  
  $GLOBALS['smarty']->assign('CHECKS', $checks);
  $GLOBALS['smarty']->assign('MODE_COMPAT', true);
} else {
  if (!isset($_SESSION['setup']['method'])) {
    $step = 2;
    // Select Install/Upgrade
    $GLOBALS['smarty']->assign('LANG_INSTALL_CUBECART_TITLE', sprintf($strings['setup']['install_cubecart_title'], CC_VERSION));
    // Check if upgrading is possible
    if (file_exists($global_file)) {
      include $global_file;
      $installed = (isset($glob['installed'])) ? (bool) $glob['installed'] : false;
      unset($glob);
    }
    if ($installed) {
      $GLOBALS['smarty']->assign('LANG_UPGRADE_CUBECART_TITLE', sprintf($strings['setup']['upgrade_cubecart_title'], CC_VERSION));
      $GLOBALS['smarty']->assign('SHOW_UPGRADE', true);
    }
    $GLOBALS['smarty']->assign('MODE_METHOD', true);
  } else if (!isset($_SESSION['setup']['licence'])) {
    if (file_exists(CC_ROOT_DIR . '/docs/license.txt')) {
      $GLOBALS['smarty']->assign('SOFTWARE_LICENCE', file_get_contents(CC_ROOT_DIR . '/docs/license.txt'));
    }
    $GLOBALS['smarty']->assign('MODE_LICENCE', true);
  } else if (!isset($_SESSION['setup']['complete'])) {
    if (in_array($_SESSION['setup']['method'], array(
      'install' => 'upgrade'
    ))) {
      require_once 'setup.' . $_SESSION['setup']['method'] . '.php';
    } else {
      require_once 'setup.install.php';
    }
  } else {
    // Install/Upgrade Complete
    // Upgrade Main Configuration
    include $global_file;
    $GLOBALS['db'] = Database::getInstance($glob);
    
    // Move to scripts folder?
    $config_string = $db->select('CubeCart_config', array(
      'array'
    ), array(
      'name' => 'config'
    ));
    $main_config   = json_decode(base64_decode($config_string[0]['array']), true);
    
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
        'latestNewsRRS' => 'default_rss_feed',
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
      ## Set default RSS feed to correct value if not set, empty or our of date
      if(empty($new_config['default_rss_feed']) || !isset($new_config['default_rss_feed']) || $new_config['default_rss_feed'] == 'http://forums.cubecart.com/index.php?act=rssout&id=1') {
        $new_config['default_rss_feed'] = 'http://forums.cubecart.com/rss/forums/1-cubecart-news-announcements/';
      }
      if (file_exists('language/' . $main_config['default_language'] . '.xml')) {
        $default_language = $main_config['default_language'];
      } elseif (isset($_SESSION['setup']['long_lang_identifier']) && file_exists('language/' . $_SESSION['setup']['long_lang_identifier'] . '.xml')) {
        $default_language = $_SESSION['setup']['long_lang_identifier'];
      } else {
        $default_language = isset($_SESSION['setup']['config']['default_language']) ? $_SESSION['setup']['config']['default_language'] : 'en-GB';
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
        'enable_reviews' => true,
        'show_basket_weight' => true
      );
      $new_config = array_merge($defaults, $new_config);
      ksort($new_config);
      
      // Write new config to database
      $db->update('CubeCart_config', array(
        'array' => base64_encode(json_encode($new_config))
      ), array(
        'name' => 'config'
      ));
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
    setcookie('delete_setup', true, time()+7200, '/');
    
    //Attempt admin file and folder rename
    if(!isset($_SESSION['setup']['admin_rename']) && (file_exists('../admin') || file_exists('../admin.php'))) {
      $adminFolder = 'admin_'.randomString(6);
      $adminFile   = 'admin_'.randomString(6).'.php';
      $update_config = false;

      rename('../'.$glob['adminFolder'], '../'.$adminFolder);
      rename('../'.$glob['adminFile'], '../'.$adminFile);
      
      if(file_exists('../'.$adminFolder)) {
        $update_config = true;
      } else {
        $adminFolder = $glob['adminFolder'];
      }

      if(file_exists('../'.$adminFile)) {
        $update_config = true;
      } else {
        $adminFile   = $glob['adminFile'];
      }

      if($update_config) {
        $_SESSION['setup']['admin_rename'] = true;
        foreach ($glob as $key => $value) {
          if($key=='adminFile') {
            $value = $adminFile;
          } elseif($key=='adminFolder') {
            $value = $adminFolder;
          }
          $value = is_array($value) ? var_export($value, true) : "'".addslashes($value)."'";
          $config[] = sprintf("\$glob['%s'] = %s;", $key, $value);
        }
        $config = sprintf("<?php\n%s\n?>", implode("\n", $config));
        ## Backup existing config file, if it exists
        if (file_exists($global_file)) rename($global_file, $global_file.'-'.date('Ymdgis').'.php');
        if (file_put_contents($global_file, $config)); 
      }
      $adminURL = str_replace('/setup','',CC_STORE_URL).'/'.$adminFile;
      if($admins = $db->select('CubeCart_admin_users', false, array('status'=> 1))) {
        $headers = 'From: nobody@'.parse_url(CC_STORE_URL, PHP_URL_HOST);
        foreach($admins as $admin) {
          mail($admin['email'],"Store Admin URL", "Hi ".html_entity_decode($admin['name'], ENT_QUOTES).",\r\n\r\nYour store has been setup to CubeCart version ".CC_VERSION.".\r\n\r\nFor security reasons the administrator URL has been obscured to divert any possible unwanted attention. Please set your bookmark to ".$adminURL."\r\n\r\n\r\nThis email was sent automatically by the CubeCart setup tool.", $headers);
        }
      }
      $GLOBALS['smarty']->assign('ADMIN_URL', $adminURL);
      $GLOBALS['smarty']->assign('STORE_URL', str_replace('/setup','',CC_STORE_URL).'/');
      $GLOBALS['smarty']->assign('SHOW_LINKS', true);
    }

    // secure global files
    $global_files = glob(CC_INCLUDES_DIR.'global.*.php');
    if(is_array($global_files)) {
      foreach($global_files as $global_file) {
        chmod($global_file, 0444);
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

if (isset($step)) {
  $progress = (100 / 5) * ((int) $step - 1);
  $progress = ($progress >= 100) ? 100 : $progress;
  $GLOBALS['smarty']->assign('PROGRESS', array(
    'percent' => (int) $progress,
    'message' => sprintf($strings['setup']['percent_complete'], (int) $progress)
  ));
}

## Build Logos
function build_logos($image_name = '')
{
  global $db;

  $logo_path = empty($image_name) ? 'skins/foundation/images/default/logo/default.png' : 'images/logos/'.$image_name;

  $logo_config = array (
    'foundationdefault' => $logo_path,
    'emails' => $logo_path,
    'invoices' => $logo_path
  );
  
  $db->insert('CubeCart_config', array(
    'name' => 'logos',
    'array' => base64_encode(json_encode($logo_config))
  ));
}

## Controller elements
if ($proceed)
  $vars['controller']['continue'] = true;
if ($retry)
  $vars['controller']['retry'] = true;
if ($restart)
  $vars['controller']['restart'] = true;
if (isset($vars['controller']))
  $GLOBALS['smarty']->assign('CONTROLLER', $vars['controller']);

$GLOBALS['smarty']->assign('COPYRIGHT_YEAR', date('Y'));
$GLOBALS['smarty']->display('skin.install.php');