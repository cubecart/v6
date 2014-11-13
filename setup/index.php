<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */
require_once preg_replace('/setup/', '', realpath(dirname(__FILE__))).'ini.inc.php';
require_once CC_INCLUDES_DIR.'functions.inc.php';

@ini_set('memory_limit', '512M');
@set_time_limit('600');
define('SKIP_DB_SETUP', true);

/*! Check cache folder is writable! */
@chmod(CC_CACHE_DIR, 0777);
if (!is_writable(CC_CACHE_DIR)) {
	$cache_dir = str_replace(CC_ROOT_DIR, '', CC_CACHE_DIR);
	die('<p>Please make sure the following folders are writable in order to continue.</p><pre>'.$cache_dir.'</pre>');
}

$global_file = CC_INCLUDES_DIR.'global.inc.php';
$setup_path = CC_ROOT_DIR.'/setup'.'/';

session_start();

if (isset($_GET['autoupdate']) && $_GET['autoupdate']) {
	$_SESSION['setup'] = ''; // remove any past upgrade/install data
	$_SESSION['setup']['method'] = 'upgrade';
	$_SESSION['setup']['autoupgrade'] = true;
	httpredir('index.php');
}

// Empty the cache before we start
$GLOBALS['cache'] = Cache::getInstance();
if (!isset($_SESSION['setup']) || is_null($_SESSION['setup'])) {
	$GLOBALS['cache']->clear();

	// Remove cached skins
	$skin_cached = glob(CC_CACHE_DIR.'skin/*.*');
	if ($skin_cached) {
		foreach ($skin_cached as $cache_file) {
			unlink($cache_file);
		}
		unset($skin_cached);
	}

	// Remove all other cache
	$cached = glob(CC_CACHE_DIR.'*.*');
	if ($cached) {
		foreach ($cached as $cache_file) {
			unlink($cache_file);
		}
		unset($cached);
	}
}

$GLOBALS['debug'] = Debug::getInstance();

$proceed  = true;
$retry  = false;
$installed = false;
$restart = true;

$domain = parse_url(CC_STORE_URL);
$cookie_domain = '.'.str_replace('www.','',$domain['host']);

$default_config_settings = array (
	'default_language'     => '',
	'default_currency'     => '',
	'email_address'      => '',
	'store_title'      => '',
	'store_name'      => '',
	'email_name'      => '',
	'admin_notify_status'    => 2,
	'catalogue_mode'     => false,
	'debug'        => false,
	'admin_skin'      => 'default',
	'skin_folder'      => 'kurouto',
	'skin_style'      => 'blue',
	'skin_change'      => false,
	'default_rss_feed'     => 'http://forums.cubecart.com/rss/forums/1-cubecart-news-announcements/',
	'email_method'      => 'mail',
	'seo_metadata'      => '',
	'store_meta_keywords'    => '',
	'store_meta_description'   => '',
	'verify_settings'     => true,
	'recaptcha'       => true,
	'time_format'      => '%d %b %Y, %H:%M',
	'time_offset'      => 0,
	'time_zone'       => 'UTC',
	'download_expire'     => 36000,
	'download_count'     => 10,
	'email_smtp'       => false,
	'email_smtp_host'      => '',
	'email_smtp_password'     => '',
	'email_smtp_port'      => '',
	'email_smtp_user'      => '',
	'enable_ssl'      => false,
	'google_analytics'     => '',
	'cache'        => true,
	'basket_allow_non_invoice_address' => true,
	'basket_jump_to'     => false,
	'basket_order_expire'    => '',
	'basket_out_of_stock_purchase'  => false,
	'basket_tax_by_delivery'   => false,
	'store_country'      => '',
	'catalogue_expand_tree'    => true,
	'catalogue_hide_prices'    => false,
	'catalogue_latest_products_count' => 8,
	'catalogue_latest_products'   => true,
	'catalogue_popular_products_count' => 10,
	'catalogue_popular_products_source' => 1,
	'catalogue_products_per_page'  => 10,
	'catalogue_sale_items'    => 10,
	'catalogue_sale_mode'    => false,
	'catalogue_sale_percentage'   => '',
	'catalogue_show_empty'    => true,
	'product_weight_unit'     => 'Lb',
	'proxy'        => false,
	'proxy_host'       => '',
	'proxy_port'      => '',
	'default_directory_symbol'   => '/',
	'product_precis'     => 120,
	'stock_warn_type'     => 0,
	'stock_warn_level'     => 5,
	'enable_reviews'     => true,
	'store_address'      => '',
	'store_copyright'     => '',
	'store_postcode'     => '',
	'store_zone'      => '',
	'ssl_force'       => false,
	'ssl_path'       => '',
	'standard_url'      => '',
	'cookie_domain'		=> $cookie_domain,
	'show_basket_weight'    => true,
	'stock_change_time'     => 2,
	'stock_level'      => 0,
	'offline'       => false,
	'offline_content'     => '<div style="font-family: georgia,serif; text-align: center;"><p style="font-size: 18px;">Store is currently offline.</p><p style="font-size: 14px;">Please visit again soon.</p></div>',
	'product_sort_column'    => 'name',
	'product_sort_direction'   => 'ASC',
	'bftime'       => '600',
	'bfattempts'      => 5
);

ksort($default_config_settings);

$GLOBALS['debug']->debugTail($_SESSION, '$_SESSION');

$GLOBALS['smarty'] = new Smarty();
$GLOBALS['smarty']->compile_dir  = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->config_dir   = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->cache_dir    = CC_SKIN_CACHE_DIR;
$GLOBALS['smarty']->template_dir = dirname(__FILE__).'/';


$language = Language::getInstance();
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
			$redir = false;
		}
		if (isset($_SESSION['setup']['method']) && !isset($_POST['licence']) && !isset($_SESSION['setup']['licence'])) {
			$errors[] = $strings['setup']['error_accept_licence'];
			$redir = false;
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


// We're geeks, and we're proud of it!
// But clearly so are you, for poking around in here :p
$store_names = array(
	'Abstergo Industries',     # Assassin's Creed
	'The Androids Dungeon',     # The Simpsons
	'Applied Cryogenics',     # Futurama
	'Aperture Science',      # Portal
	'Bad Wolf Corporation',     # Doctor Who - Series 1 "Bad Wolf"/"The Parting of the Ways"
	"Benjamin Barker's Shaving Supplies", # Sweeney Todd
	'Black Mesa Research',     # Half-Life
	'Blue Sun Corporation',     # Firefly 'verse
	'Buy n Large Corporation',    # WALL-E
	'Buy More',        # Chuck
	'Colby Enterprises',     # Dynasty
	'CompuGlobalHyperMegaNet',    # Simpsons
	'Cyberdyne Systems',     # The Terminator Series
	'Cybus Industries',      # Doctor Who - Series 2 "Rise of the Cybermen"/"The Age of Steel"
	'Daystrom Data Concepts',    # Star Trek
	'Dervish and Banges',     # Harry Potter
	'Doublemeat Palace',     # Buffy
	'ENCOM',        # TRON
	'FrobozzCo International',    # Zork
	'Global Dynamics',      # (A Town Called) Eureka
	'Globotech Industries',     # Small Soldiers
	'Grace Brothers',      # Are You Being Served?
	'Hanso Foundation',      # Lost
	'Initech',         # Office Space
	'Initrode',        # Office Space
	'Input, Inc.',       # Short Circuit (Thanks to Brivtech)
	'Jupiter Mining Corporation',   # Red Dwarf
	'Kaiba Corporation',     # Yu-Gi-Oh!
	'Large Mart',       # Chuck
	'LexCorp',        # 'Superman' series
	'LuthorCorp',       # 'Superman' series
	'The Magic Box',      # Buffy the Vampire Slayer
	'Magpie Electricals',     # Doctor Who - Series 2 "The Idiot's Lantern"
	'Massive Dynamic',      # Fringe
	'Megadodo Publications',    # The Hitchiker's Guide to the Galaxy
	'Mishima Zaibatsu',      # Tekken
	'Moms Friendly Robot Company',   # Futurama
	"Mrs Lovett's Pies",     # Sweeney Todd
	'Nakatomi Trading',      # Die Hard & Die Hard 2
	'Omni Consumer Products',    # Robocop
	'Planet Express',      # Futurama
	'Powell Motors',      # The Simpsons
	'Prescott Pharmaceuticals',    # The Colbert Report
	'Primatech Paper Company',    # Heroes
	'Quest Aerospace',      # Spiderman (Rival of Oscorp)
	'Ravenwood',       # Jericho
	'Rekall, Inc',       # Total Recall
	'Rentaghost',       # Rentaghost, funnily enough...
	'Sparrow and Nightingale',    # Doctor Who - Series 3 "Blink"
	'Stark Industries',      # Iron Man
	'Torchwood Institute',     # Torchwood (obviously enough...)
	'Tyrell Corporation',     # Blade Runner
	'Universal Export',      # James Bond series (Front for MI6)
	'VersaLife Corporation',    # Max Payne
	'Virtucon',        # Austin Powers: International Man of Mystery (Thanks to Brivtech)
	'Wayne Enterprises',     # Batman
	'Weyland-Yutani',      # Alien
	'Wolfram and Hart',      # Angel
	'Xanatos Enterprises',     # Gargoyles (Thanks to Brivtech)
	'Yoyodyne Propulsion Systems',   # The Adventures of Buckaroo Banzai Across the 8th Dimension (Many thanks to Kristen from padlockoutlet.com)
	'Zorg Corporation',      # The Fifth Element
);

if (!isset($_SESSION['setup']) || is_null($_SESSION['setup'])) {
	$restart = false;
	$step  = 1;
	// Compatibility Test
	$checks  = array(
		'PHP' => array(
			'title'  => 'PHP 5.2.3+',
			'status' => version_compare(PHP_VERSION, '5.2.3', '>='),
			'pass'  => PHP_VERSION,
			'fail'  => PHP_VERSION,
		),
		'MySQL' => array(
			'title'  => 'MySQL 4.1+',
			'status' => (extension_loaded('mysqli') || extension_loaded('mysql')),
			'pass'  => (function_exists('mysqli_get_client_info')) ? mysqli_get_client_info() : mysql_get_client_info(),
			'fail'  => (function_exists('mysqli_get_client_info')) ? mysqli_get_client_info() : mysql_get_client_info(),
		),
		'GD' => array(
			'title'  => 'GD Image Library',
			'status' => detectGD(),
			'pass'  => $strings['common']['installed'],
			'fail'  => $strings['common']['not_installed'],
		),
		'cURL' => array(
			'title'  => 'cURL',
			'status' => extension_loaded('curl'),
			'pass'  => $strings['common']['installed'],
			'fail'  => $strings['common']['not_installed'],
		),
		'Loader' => array(
			'title'  => 'IonCube PHP Loader',
			'status' => (has_ioncube_loader()),
			'pass'  => $strings['common']['installed'],
			'fail'  => $strings['common']['not_installed'],
		),
	);

	if (!has_ioncube_loader()) {
		$errors[] = $strings['setup']['error_ion_zend_required'];
	}
	$GLOBALS['smarty']->assign('CHECKS', $checks);
	// Optional extensions
	$extensions = array('APC', 'bz2', 'EXIF', 'FileInfo', 'Hash', 'mCrypt', 'mysqli', 'XCache', 'XDebug', 'ZIP');
	natcasesort($extensions);
	foreach ($extensions as $extension) {
		$GLOBALS['smarty']->append('EXTENSIONS', array('name' => $extension, 'status' => (bool)extension_loaded($extension)));
	}
	$GLOBALS['smarty']->assign('MODE_COMPAT', true);
} else {

	if (!isset($_SESSION['setup']['method'])) {
		$step = 2;
		// Select Install/Upgrade
		$GLOBALS['smarty']->assign('LANG_INSTALL_CUBECART_TITLE', sprintf($strings['setup']['install_cubecart_title'], CC_VERSION));
		// Check if upgrading is possible
		if (file_exists($global_file)) {
			include $global_file;
			$installed = (isset($glob['installed'])) ? (bool)$glob['installed'] : false;
			unset($glob);
		}
		if ($installed) {
			$GLOBALS['smarty']->assign('LANG_UPGRADE_CUBECART_TITLE', sprintf($strings['setup']['upgrade_cubecart_title'], CC_VERSION));
			$GLOBALS['smarty']->assign('SHOW_UPGRADE', true);
		}
		$GLOBALS['smarty']->assign('MODE_METHOD', true);
	} else if (!isset($_SESSION['setup']['licence'])) {
			$step = 3;
			if (file_exists(CC_ROOT_DIR.'/docs/licence.txt')) {
				$GLOBALS['smarty']->assign('SOFTWARE_LICENCE', file_get_contents(CC_ROOT_DIR.'/docs/licence.txt'));
			}
			$GLOBALS['smarty']->assign('MODE_LICENCE', true);
		} else if (!isset($_SESSION['setup']['complete'])) {
			if (in_array($_SESSION['setup']['method'], array('install' => 'upgrade'))) {
				require_once 'setup.'.$_SESSION['setup']['method'].'.php';
			} else {
				require_once 'setup.install.php';
			}
		} else {
		// Install/Upgrade Complete
		// Upgrade Main Configuration
		include $global_file;
		$GLOBALS['db'] = Database::getInstance($glob);

		// Move to scripts folder?
		$config_string = $db->select('CubeCart_config', array('array'), array('name' => 'config'));
		$main_config = json_decode(base64_decode($config_string[0]['array']), true);

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
				'stock_change_time',
				'stock_warn_type',
				'store_copyright',
				'sqlSessionExpiry',
				'taxCountry',
				'taxCounty',
				'uploadSize',
				'noRelatedProds'
			);
			// Rename existing keys
			$remapped = array(
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
				'force_ssl' => 'ssl_force',
				'storeURL_SSL' => 'ssl_url',
				'rootRel_SSL' => 'ssl_path',
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
			if (isset($main_config['siteCountry']) && $main_config['siteCountry']>0) {
				$country = $db->select('CubeCart_geo_country', array('numcode'), array('id' => $main_config['siteCountry']));
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

			if (file_exists('language/'.$main_config['default_language'].'.xml')) {
				$default_language = $main_config['default_language'];
			} elseif (isset($_SESSION['setup']['long_lang_identifier']) && file_exists('language/'.$_SESSION['setup']['long_lang_identifier'].'.xml')) {
				$default_language = $_SESSION['setup']['long_lang_identifier'];
			} else {
				$default_language = 'en-US';
			}

			## Redefine the default skin
			$reset  = array(
				'skin_folder'  => 'kurouto',
				'skin_style'  => 'blue',
				'seo'    => '0',
				'default_language'  =>  $default_language
			);
			$new_config = array_merge($main_config, $new_config, $reset);
			## Set some defaults
			$defaults = array(
				'admin_skin'   => 'default',
				'verify_settings'  => true,
				'enable_reviews'  => true,
				'show_basket_weight' => true,
			);
			$new_config = array_merge($defaults, $new_config);
			ksort($new_config);

			// Write new config to database
			$db->update('CubeCart_config', array('array' => base64_encode(json_encode($new_config))), array('name' => 'config'));
			$_SESSION['setup']['config_update'] = true;
		}
		## Delete the key file, if it exists
		$key_file = CC_ROOT_DIR.'/includes/extra/key.php';
		if (file_exists($key_file)) unlink($key_file);

		$proceed = false;
		$restart = true;
		$step   = 6;
		switch ($_SESSION['setup']['method']) {
		case 'install':
			$GLOBALS['smarty']->assign('MODE_COMPLETE_INSTALL', true);
			break;
		case 'upgrade':
			$GLOBALS['smarty']->assign('MODE_COMPLETE_UPGRADE', true);
			break;
		}
		$GLOBALS['smarty']->assign('MODE_COMPLETE', true);
		$GLOBALS['smarty']->assign('SHOW_LINKS', true);

		/* Truncate CubeCart_system_error_log table. There are a number of failed SQL queries on upgrade depending
		 * on to/from version. We need a clean slate to detect operational errors.
		 */
		$db->misc('TRUNCATE TABLE `'.$glob['dbprefix'].'CubeCart_system_error_log`');
	}
}

## Display error messages
if (isset($errors) && is_array($errors)) {
	$vars['errors'] = $errors;
	$GLOBALS['smarty']->assign('GUI_MESSAGE', $vars);
}

if (isset($step)) {
	$progress = (100/5)*((int)$step-1);
	$progress = ($progress >= 100) ? 100 : $progress;
	$GLOBALS['smarty']->assign('PROGRESS', array(
			'percent' => (int)$progress,
			'message' => sprintf($strings['setup']['percent_complete'], (int)$progress),
		));
}

## Build Logos
function build_logos($image_name = '') {

	global $db;

	$skins = glob('../skins/*/config.xml');
	if ($skins) {
		foreach ($skins as $skin) {
			$xml = new SimpleXMLElement(file_get_contents($skin));
			if (isset($xml->styles)) {
				## List substyles
				foreach ($xml->styles->style as $style) {
					$skins[(string)$xml->info->name][(string)$style->directory] = ((string)$style->attributes()->images == 'true') ? true : false;
				}
			} else {
				## Only one style here
				$skins[(string)$xml->info->name] = true;
			}
		}
		foreach ($skins as $skinname => $value) {
			if (!is_numeric($skinname)) {
				if (is_array($value)) {
					foreach ($value as $subskin => $name) {
						$logo_config[$skinname.$subskin] = (!empty($image_name)) ? 'images/logos/'.$image_name : 'skins/'.$skinname.'/images/'.$subskin.'/logo/default.png';
					}
				} else {
					$logo_config[$skinname] = (!empty($image_name)) ? 'images/logos/'.$image_name : 'skins/'.$skinname.'/images/logo/default.png';
				}
			}
		}
	}
	/* Add default skin image to invoices and emails */
	$logo_config['emails']   = (!empty($image_name)) ? $image_name : 'skins/kurouto/images/blue/logo/default.png';
	$logo_config['invoices']  = (!empty($image_name)) ? $image_name : 'skins/kurouto/images/blue/logo/default.png';

	$db->insert('CubeCart_config', array('name' => 'logos' , 'array' => base64_encode(json_encode($logo_config))));
}

## Controller elements
if ($proceed) $vars['controller']['continue'] = true;
if ($retry)  $vars['controller']['retry'] = true;
if ($restart) $vars['controller']['restart'] = true;
if (isset($vars['controller'])) $GLOBALS['smarty']->assign('CONTROLLER', $vars['controller']);

$GLOBALS['smarty']->assign('COPYRIGHT_YEAR', date('Y'));
$GLOBALS['smarty']->display('skin.install.php');