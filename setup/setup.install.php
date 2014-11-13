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
##### INSTALL #####
if (!isset($_SESSION['setup']['permissions'])) {
	$step = 4;
	$_SESSION['setup']['config_update'] = true;
	## Stage 3: Permissions Check
	if (!file_exists($global_file)) touch($global_file);
	$targets = array(
		'backup/',
		'cache/',
		'cache/skin/',
		'files/',
		'images/',
		'images/cache/',
		'images/logos/',
		'images/source/',
		'includes/',
		'includes/extra/',
		'language/',
	);
	if (file_exists(CC_ROOT_DIR.'/includes/globals.inc.php')) {
		$targets[] = 'includes/global.inc.php';
	}
	if (file_exists(CC_ROOT_DIR.'/images/uploads')) $targets[] = 'images/uploads/';
	sort($targets);
	$permissions = true;
	foreach ($targets as $target) {
		$target = str_replace('/', '/', $target);
		$perm_status = true;
		if (!is_writable(CC_ROOT_DIR.'/'.$target)) {
			## Attempt to chmod
			if (!chmod(CC_ROOT_DIR.'/'.$target, chmod_writable())) {
				$perm_status = false;
				$permissions = false;
				$errors[] = sprintf($strings['setup']['error_x_not_writable'], $target);
			}
		}
		$GLOBALS['smarty']->append('PERMISSIONS', array('name' => $target, 'status' => (bool)$perm_status));
	}
	if (!$permissions) {
		$proceed = false;
		$retry  = true;
	} else {
		$GLOBALS['smarty']->assign('PERMS_PASS', true);
	}
	$GLOBALS['smarty']->assign('MODE_PERMS', true);
} else {
	// Stage 4: Server Details input
	$step = 5;
	if (!isset($_SESSION['setup']['global']) || !isset($_SESSION['setup']['progress'])) {
		if (isset($_POST['global']) && isset($_POST['admin'])) {
			// Validation
			$validated = true;
			$required = array('dbhost', 'dbusername', 'dbdatabase');
			foreach ($_POST['global'] as $key => $value) {
				if (in_array($key, $required) && empty($value)) {
					$validated  = false;
					unset($_POST[$key]);
				}
			}

			if ($_POST['global']['dbpassword'] !== $_POST['global']['dbpassconf']) {
				unset($_POST['global']['dbpassword'], $_POST['global']['dbpassconf']);
				$errors['dbpass'] = $strings['setup']['error_db_password_mismatch'];
			}
			// Validate admin array
			$required = array('username', 'email', 'name', 'password');
			if ($_POST['admin']['password'] !== $_POST['admin']['passconf']) {
				$errors['password'] = $strings['setup']['error_admin_password_mismatch'];
				unset($_POST['admin']['password'], $_POST['admin']['passconf']);
			}
			foreach ($_POST['admin'] as $key => $value) {
				if (in_array($key, $required) && empty($value)) {
					$validated = false;
					unset($_POST[$key]);
				}
			}
			// Connection Check - Update for mysqli
			$connect = mysql_connect($_POST['global']['dbhost'], $_POST['global']['dbusername'], $_POST['global']['dbpassword'], false);
			if ($connect) {
				if (mysql_select_db($_POST['global']['dbdatabase'], $connect)) {
					##ÊDatabase is fine, so continue to next step
					mysql_close($connect);
					if ($validated) {
						# Set session variables, then proceed
						unset($_POST['global']['dbpassconf'], $_POST['admin']['passconf']);

						$_SESSION['setup']['progress'] = true;
						$_SESSION['setup']['droptable'] = (isset($_POST['drop'])) ? true : false;

						$global = array(
							'installed'  => true,
							'adminFolder' => 'admin',
							'adminFile'  => 'admin.php',
						);
						$_SESSION['setup']['global'] = array_merge($_POST['global'], $global);
						$_SESSION['setup']['config'] = $_POST['config'];
						$salt = Password::getInstance()->createSalt();
						$_SESSION['setup']['admin']  = array_merge($_POST['admin'], array(
								'order_notify' => 1,
								'super_user' => 1,
								'status'  => 1,
								'salt'   => $salt,
								'language'  => $_POST['config']['default_language'],
								'password'  => Password::getInstance()->getSalted($_POST['admin']['password'], $salt),
							));
						httpredir('index.php');
					}
				} else {
					// No such database
					$errors['dbdatabase'] = $strings['setup']['error_db_doesnt_exist'];
					unset($_POST['global']['dbdatabase']);
				}
			} else {
				// Incorrect host/user/pass
				$errors[] = $strings['setup']['error_db_incorrect_something'];
				unset($_POST['global']['dbhost'], $_POST['global']['dbusername'], $_POST['global']['dbpassword']);
			}
			$GLOBALS['smarty']->assign('FORM', $_POST);
		}

		$currencies = array(
			'USD' => 'US Dollar',
			'GBP' => 'British Pound',
			'EUR' => 'Euro',
			#####
			'AUD' => 'Australian Dollar',
			'BGN' => 'Bulgarian Lev',
			'BRL' => 'Brazilian Real',
			'CAD' => 'Canadian Dollar',
			'CHF' => 'Swiss Franc',
			'CNY' => 'Chinese Yuan',
			'CZK' => 'Czech Koruna',
			'DKK' => 'Danish Krone',
			'EEK' => 'Estonian Kroon',
			'HKD' => 'Hong Kong Dollar',
			'HRK' => 'Croatian Kuna',
			'HUF' => 'Hungarian Forint',
			'IDR' => 'Indonesian Rupiah',
			'INR' => 'Indian Rupee',
			'JPY' => 'Japanese Yen',
			'KRW' => 'South Korean Won',
			'LTL' => 'Lithuanian Litas',
			'LVL' => 'Latvian Lat',
			'MXN' => 'Mexican Peso',
			'MYR' => 'Malaysian Ringgit',
			'NOK' => 'Norwegian Krone',
			'NZD' => 'New Zealand Dollar',
			'PHP' => 'Philippine Peso',
			'PLN' => 'Polish Zloty',
			'RON' => 'Romanian Leu',
			'RUB' => 'Russian Ruble',
			'SEK' => 'Swedish Krona',
			'SGD' => 'Singapore Dollar',
			'THB' => 'Thai Baht',
			'TRY' => 'Turkish Lira',
			'ZAR' => 'South African Rand'
		);
		foreach ($currencies as $code => $name) {
			$selected = (isset($_POST['config']['default_currency']) && $_POST['config']['default_currency'] == $code) ? ' selected="selected"' : '';
			$list_currency[] = array('code' => $code, 'selected' => $selected, 'name' => (!empty($name))?$name:$code);
		}
		$GLOBALS['smarty']->assign('CURRENCIES', $list_currency);


		foreach ($languages as $option) {
			$option['selected'] = ($option['code'] == $_SESSION['language']) ? ' selected="selected"' : '';
			$smarty_data['list_langs'][] = $option;
		}
		$GLOBALS['smarty']->assign('LANGUAGES', $smarty_data['list_langs']);


	} else {
		## Stage 5: Actual installation
		## Write config file
		ksort($_SESSION['setup']['global']);
		foreach ($_SESSION['setup']['global'] as $key => $value) {
			$config[] = sprintf("\$glob['%s'] = '%s';", $key, addslashes($value));
		}
		$config = sprintf("<?php\n%s\n?>", implode("\n", $config));
		## Backup existing config file, if it exists
		if (file_exists($global_file)) rename($global_file, $global_file.'-'.date('Ymdgis').'.php');

		if (file_put_contents($global_file, $config)) {
			unset($config);
			## Install database
			include $global_file;
			$GLOBALS['config'] = $glob;
			$GLOBALS['db'] = Database::getInstance($GLOBALS['config']);

			$GLOBALS['db']->misc('ALTER DATABASE `'.$GLOBALS['config']['dbdatabase'].'` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;');

			if ($_SESSION['setup']['droptable']) {
				$GLOBALS['db']->parseSchema(file_get_contents($setup_path.'db/install/table_drop.sql', false));
				unset($_SESSION['setup']['droptable']);
				# httpredir('index.php');
			}
			## Create tables
			$GLOBALS['db']->parseSchema(file_get_contents($setup_path.'db/install/structure.sql', false));
			## Insert basic data
			$GLOBALS['db']->parseSchema(file_get_contents($setup_path.'db/install/data.sql', false));
			## Insert example product/category
			# if (isset($_SESSION['setup']['examples'])) {
			$GLOBALS['db']->parseSchema(file_get_contents($setup_path.'db/install/examples.sql', false));
			# }
			## Insert Email Contents & Templates
			$GLOBALS['db']->parseSchema(file_get_contents($setup_path.'db/install/email.sql', false));
			
			$config_settings = array_merge($default_config_settings,
				array(
					'default_language'     => $_SESSION['setup']['config']['default_language'],
					'default_currency'     => $_SESSION['setup']['config']['default_currency'],
					'email_address'      => $_SESSION['setup']['admin']['email'],
					'store_title'      => 'CubeCart',
					'store_name'      => 'CubeCart',
					'email_name'      => 'CubeCart',
				)
			);
			Config::getInstance($glob)->set('config', '', $config_settings, true);
			$GLOBALS['config'] = array_merge($GLOBALS['config'], $config_settings);
			// Create admin user
			$GLOBALS['db']->insert('CubeCart_admin_users', $_SESSION['setup']['admin']);
			// Set the current exchange rates
			if (!$request = new Request('www.ecb.europa.eu', '/stats/eurofxref/eurofxref-daily.xml')) {
				// if fail fall back to our outdated copy locally
				$rates_xml = file_get_contents('data/eurofxref-daily.xml');
			} else {
				$request->setData(array('null'=>0)); // setData needs a value to work
				$rates_xml = $request->send();
			}

			// If this fails fall back to original file_get_contents
			if (empty($rates_xml)) {
				$rates_xml = file_get_contents('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
			}

			try {
				$xml = new SimpleXMLElement($rates_xml);
				if ($xml) {
					// Magically update all the exchange rates with the latest ECB data
					foreach ($xml->Cube->Cube->Cube as $currency) {
						$rate = $currency->attributes();
						$fx[(string)$rate['currency']] = (float)$rate['rate'];
					}
					$fx['EUR'] = 1;
					$updated = strtotime((string)$xml->Cube->Cube->attributes()->time);
					// Get the divisor
					$base  = (1/$fx[$config['default_currency']]);
					$active_currencies = array_merge(array('AUD', 'CAD', 'EUR', 'GBP', 'JPY', 'USD'), array($config['default_currency']));
					foreach ($fx as $code => $rate) {
						$value = ($base/(1/$rate));
						$active_currency = in_array($code, $active_currencies) ? true : false;
						$GLOBALS['db']->update('CubeCart_currency', array('value' => $value, 'lastUpdated' => $updated, 'active' => $active_currency), array('code' => $code), true);
					}
				}
			} catch (Exception $e) {trigger_error('Error parsing ECB Exchange Rates.', E_USER_WARNING);}


			$default_docs = array(
				0 => array('doc_name' => $strings['setup']['default_doc_title_welcome'], 'doc_content' => $strings['setup']['default_doc_content_welcome'], 'doc_order' => 1, 'doc_lang' => $config['default_language'], 'doc_home' => 1, 'doc_terms' => 0),
				1 => array('doc_name' => $strings['setup']['default_doc_title_about'], 'doc_content' => $strings['setup']['default_doc_content'], 'doc_order' => 2, 'doc_lang' => $config['default_language'], 'doc_home' => 0, 'doc_terms' => 0),
				2 => array('doc_name' => $strings['setup']['default_doc_title_terms'], 'doc_content' => $strings['setup']['default_doc_content'], 'doc_order' => 3, 'doc_lang' => $config['default_language'], 'doc_home' => 0, 'doc_terms' => 1),
				3 => array('doc_name' => $strings['setup']['default_doc_title_privacy'], 'doc_content' => $strings['setup']['default_doc_content'], 'doc_order' => 4, 'doc_lang' => $config['default_language'], 'doc_home' => 0, 'doc_terms' => 0),
				4 => array('doc_name' => 'Returns Policy', 'doc_content' => $strings['setup']['default_doc_content'], 'doc_order' => 5, 'doc_lang' => $config['default_language'], 'doc_home' => 0, 'doc_terms' => 0)
			);
			foreach ($default_docs as $default_doc) {
				$GLOBALS['db']->insert('CubeCart_documents', $default_doc);
			}
			$contact_form = base64_encode(json_encode(array('status' => 1, 'email' => $_SESSION['setup']['admin']['email'],'description' => '')));
			$GLOBALS['db']->insert('CubeCart_config', array('name' => 'Contact_Form', 'array' => $contact_form));

			// Install email templates based on all languages
			if (is_array($languages)) {
				foreach ($languages as $code => $lang) {
					$language->importEmail('email_'.$code.'.xml');
				}
			}

			// Set version number
			$GLOBALS['db']->insert('CubeCart_history', array('version' => CC_VERSION, 'time' => time()));

			build_logos();

			$_SESSION['setup']['complete'] = true;
			httpredir('index.php');
		}

	}
	$GLOBALS['smarty']->assign('MODE_INSTALL', true);
}