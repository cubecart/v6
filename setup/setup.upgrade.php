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
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!isset($_SESSION['setup']['permissions'])) {
	$step = 4;
	// Stage 3: Permissions Check
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
		'includes/global.inc.php',
		'language/',
	);
	if (file_exists(CC_ROOT_DIR.'/images/uploads')) $targets[] = 'images/uploads/';
	sort($targets);
	$permissions = true;
	foreach ($targets as $target) {
		$target = str_replace('/', '/', $target);
		$perm_status = true;
		if (!is_writable(CC_ROOT_DIR.'/'.$target)) {
			// Attempt to chmod
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
	##### UPGRADE #####
	require_once $global_file;
	$config  = $glob;
	$db   = Database::getInstance($config);
	## Admin Session thingy

	## Get version history
	if (($versions = $db->select('CubeCart_history', array('version'), null, array('id' => 'DESC'), false, false, false)) !== false) {
		## Version 4
		$current = $versions[0]['version'];
		foreach ($versions as $version) {
			$previous[] = $version['version'];
		}
		unset($versions, $version);
	} else {
		// Version 3 - Get version from ini
		$v3_ini = CC_ROOT_DIR.'/includes/ini.inc.php';
		if (file_exists($v3_ini)) {
			include $v3_ini;
			$current = $ini['ver'];
		} else {
			## We'll assume that it's coming from the latest version 3
			$current = '3.0.20';
		}
		unset($ini, $v3_ini);
	}

	if (!isset($_SESSION['setup']['start_version']) || empty($_SESSION['setup']['start_version'])) {
		$_SESSION['setup']['start_version'] = $current;
		## We do not want a config update from v5 to v5
		$_SESSION['setup']['config_update'] = version_compare($current, '5.0.0b1', '<') ? true : false;
	}

	if (!isset($_SESSION['setup']['progress']) || is_null($_SESSION['setup']['progress'])) {
		if (isset($_POST['progress']) && version_compare($current, 3, 'ge')) {
			$_SESSION['setup']['progress'] = true;
			httpredir('index.php');
		}
		## Confirmation
		if (version_compare($current, CC_VERSION, '<')) {

			$step = 4;
			$GLOBALS['smarty']->assign('UPGRADE', array('from' => $current, 'to' => CC_VERSION));
			$_SESSION['setup']['start_version'] = $current;
			$GLOBALS['smarty']->assign('LANG_UPGRADE_FROM_TO', sprintf($strings['setup']['upgrade_from_to'], $current, CC_VERSION));
			$GLOBALS['smarty']->assign('MODE_UPGRADE_CONFIRM', true);
		} else {
			$progress_value = 100;
			$vars['notices'][] = $strings['setup']['already_upgraded'];
			$GLOBALS['smarty']->assign('GUI_MESSAGE', $vars);
			$GLOBALS['smarty']->assign('SHOW_LINKS', true);
			$proceed = false;
		}
	} else {
		$step = 5;
		## If version is less then 4 or 5 try to fix database encoding
		if (!$_SESSION['setup']['db_converted'] && version_compare($current, '5.0.0', '<')) {
			/* ########################
			##	Following code based on;
			##	Migrating MySQL Data to Unicode
			##	http://daveyshafik.com/archives/166-migrating-mysql-data-to-unicode.html
			##	Thanks to Davey Shafik
			## ######################## */
			$tables = $db->getRows();
			foreach ($tables as $table) {
				## Get Schema
				if ($schema = $db->misc('SHOW CREATE TABLE '.$table['Name'])) {
					## Fix Schema and Create Temp Table
					$find   = array("latin1", $table['Name']);
					$replace  = array("utf8 COLLATE utf8_unicode_ci", $table['Name'].'_utf8');

					$db->misc(str_replace($find, $replace, $schema[0]['Create Table']));

					if ($GLOBALS['db']->misc("SHOW TABLES LIKE '".$table['Name']."_utf8'", false)) {
						## Copy Data
						$db->misc('INSERT INTO '.$table['Name'].'_utf8 SELECT * FROM '.$table['Name']);
						## Deleting Original Table
						$db->misc('DROP TABLE '.$table['Name']);
						## Renaming Temporary Table
						$db->misc('ALTER TABLE '.$table['Name'].'_utf8 RENAME TO '.$table['Name']);
					}
				}
			}
			$_SESSION['setup']['db_converted'] = true;
		}

		## Updates from version 3
		if (!$_SESSION['setup']['config_converted'] && version_compare($current, '4.0.0', '<')) {

			## Version 3: Upgrade config data
			$config_string = $db->select('CubeCart_config', array('array'), array('name' => 'config'));
			if ($config_string) {
				$old_config = unserialize($config_string[0]['array']);
				foreach ($old_config as $key => $value) {
					$new_config[base64_decode($key)] = stripslashes(base64_decode($value));
				}
				$db->update('CubeCart_config', array('array' => base64_encode(json_encode($new_config))), array('name' => 'config'));

			}
			$_SESSION['setup']['short_lang_identifier'] = $new_config['defaultLang'];
			unset($old_config, $new_config, $config_string);

			## Upgrade v3 global file to v5 spec
			include $global_file;
			$append = array(
				'adminFolder' => 'admin',
				'adminFile'  => 'admin.php',
			);
			$global = array_merge($glob, $append);
			ksort($global);
			## Write new file
			unset($config, $global['rootDir'], $global['rootRel'], $global['storeURL']);
			foreach ($global as $key => $value) {
				$config[] = sprintf("\$glob['%s'] = '%s';", $key, addslashes($value));
			}
			$config = sprintf("<?php\n%s\n?>", implode("\n", $config));
			##ÊBackup existing config file, if it exists
			if (file_exists($global_file)) {
				rename($global_file, $global_file.'-'.date('Ymdgi').'.php');
			}
			file_put_contents($global_file, $config);
			$_SESSION['setup']['config_converted'] = true;

			## Updates from version 4
		} elseif (!$_SESSION['setup']['config_converted'] && version_compare($current, '5.0.0', '<')) {

			## Version 4: Upgrade config data
			$config_string = $db->select('CubeCart_config', array('array'), array('name' => 'config'));
			if ($config_string) {
				$new_config = unserialize($config_string[0]['array']);
				$new_config['offLineContent'] = base64_decode($new_config['offLineContent']);
				$db->update('CubeCart_config', array('array' => base64_encode(json_encode($new_config))), array('name' => 'config'));
			}

			## Upgrade v4 global file to v5 spec
			include $global_file;
			unset($glob['license_key'], $glob['rootDir'], $glob['rootRel'], $glob['storeURL']);
			ksort($glob);
			## Write new file
			unset($config);
			foreach ($glob as $key => $value) {
				$config[] = sprintf("\$glob['%s'] = '%s';", $key, addslashes($value));
			}
			$config = sprintf("<?php\n%s\n?>", implode("\n", $config));
			##ÊBackup existing config file, if it exists
			if (file_exists($global_file)) {
				rename($global_file, $global_file.'-'.date('Ymdgi').'.php');
			}
			file_put_contents($global_file, $config);

			$_SESSION['setup']['short_lang_identifier'] = $new_config['defaultLang'];
			$_SESSION['setup']['config_converted'] = true;
			unset($config_string, $new_config, $country_config);
		}


		## List of versions to upgrade though
		$files_sql = glob($setup_path.'db/upgrade/*.sql');
		if ($files_sql) {
			foreach ($files_sql as $key => $file) {
				$version = str_replace('.sql', '', basename($file));
				if (!isset($previous) || version_compare($version, $current, '>')) {
					if (isset($updates) && in_array($version, $updates) || version_compare($version, CC_VERSION, '>')) continue;
					$updates[] = $version;
				}
			}
			unset($files_sql);
		}

		$files_php = glob($setup_path.'scripts/upgrade/*.php');
		if ($files_php) {
			foreach ($files_php as $key => $file) {
				$version = str_replace('.php', '', basename($file));
				if (!isset($previous) || version_compare($version, $current, '>')) {
					if (isset($updates) && in_array($version, $updates) || version_compare($version, CC_VERSION, '>')) continue;
					$updates[] = $version;
				}
			}
			unset($files_php);
		}
		## Run upgrade scripts (loop)
		if (is_array($updates) && !empty($updates)) {
			## Check for updates to process
			natsort($updates);
			foreach ($updates as $version) {
				$GLOBALS['smarty']->assign('UPGRADE', array('from' => $current, 'to' => $version));
				$file_sql = 'db/upgrade/'.$version.'.sql';
				if (file_exists($file_sql)) {
					## Process file
					$db->parseSchema(file_get_contents($file_sql, false));
				}

				$file_php = 'scripts/upgrade/'.$version.'.php';
				if (file_exists($file_php)) {
					## Include file
					include $file_php;
				}
				## Add version history record if less than current as its added at the end
				if (!$db->select('CubeCart_history', false, array('version' => $version))) {
					$db->insert('CubeCart_history', array('version' => $version, 'time' => time()));
				}
				break;
			}

			## Set auto-refresh
			$GLOBALS['smarty']->append('REFRESH', true);
		} else {

			## Check for new language packs in this version and install email templates if required
			$existing_languages = $db->select('CubeCart_email_content', 'DISTINCT `language`');
			$missing_languages  = $languages;

			## Loop existing languages and remove to leave missing languages array with the ones we need to import
			if ($existing_languages) {
				foreach ($existing_languages as $key => $value) {
					unset($missing_languages[$value['language']]);
				}
			}
			## Import missing language email templates if they exist... pukka
			if (is_array($missing_languages)) {
				foreach ($missing_languages as $code => $lang) {
					$language->importEmail('email_'.$code.'.xml');
				}
			}

			// Set version number
			if (!$GLOBALS['db']->select('CubeCart_history', false, array('version' => CC_VERSION))) {
				$GLOBALS['db']->insert('CubeCart_history', array('version' => CC_VERSION, 'time' => time()));
			}

			## Progressive updates completed
			## Redirect to the 'complete' page
			$_SESSION['setup']['complete'] = true;
			if ($_SESSION['setup']['autoupgrade']) {
				httpredir('../admin.php?_g=maintenance&node=index#upgrade');
			}
			httpredir('index.php', 'upgraded');
		}
		$GLOBALS['smarty']->assign('LANG_UPGRADE_IN_PROGRESS', sprintf($strings['setup']['upgrade_in_progress'], $current, $version));
		$GLOBALS['smarty']->append('MODE_UPGRADE_PROGRESS', true);
	}
	$GLOBALS['smarty']->assign('MODE_UPGRADE', true);
}