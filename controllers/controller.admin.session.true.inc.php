<?php
if (!defined('CC_INI_SET')) die('Access Denied');
// Load admin user details
if (!isset($_GET['_g']) || !in_array(strtolower($_GET['_g']), array('login', 'logout', 'password', 'recovery'))) {
	$GLOBALS['main']->setTemplate();
}

if ($GLOBALS['config']->has('config', 'verify_settings') && (!isset($_GET['_g']) || $_GET['_g'] != 'settings')) {
	$GLOBALS['gui']->setNotify($lang['settings']['error_settings_verify']);
	$config_new = $GLOBALS['config']->get('config');
	unset($config_new['verify_settings']);
	// Remove global file variables from config data
	foreach ($glob as $key => $value) unset($config_new[$key]);
	// Replace config data
	$GLOBALS['config']->set('config', '', $config_new);
	httpredir('?_g=settings');
}

if (isset($_GET['_g']) && in_array($_GET['_g'], array('login', 'password', 'recovery'))) {
	httpredir('?');
}
if (isset($_GET['_g']) && !empty($_GET['_g']) && $_GET['_g'] != 'modules') {
	$GLOBALS['gui']->addBreadcrumb(ucwords($_GET['_g']));
}

if (!empty($_GET['_g'])) {

	$module_type = preg_match("/[a-z]/i", $_GET['type']) ? $_GET['type'] : '';

	$node = (!empty($_GET['node'])) ? strtolower($_GET['node']) : 'index';
	if (strtolower($_GET['_g']) == 'modules' && !empty($module_type)) {

		$GLOBALS['gui']->addBreadcrumb(ucwords($_GET['_g']), currentPage());
		// Display Modules
		$GLOBALS['main']->wikiNamespace('Modules');
		$modules_list = glob('modules'.'/'.strtolower($module_type).'/'.'*');
		
		if (is_array($modules_list)) {
			foreach ($modules_list as $folder) {
				if (file_exists($folder.'/'.'admin') && preg_match('#([a-z]+)[\\\/]([a-z0-9\_\-]+)$#iU', $folder, $matches)) {
					$module_list[$matches[1]][] = basename($folder);
				}
			}
		}

		// Get order for module based on popularity listing most popular first
		$request = new Request('www.cubecart.com', '/stats/');
		$request->skiplog(true);
		$request->setData('null');
		$response = $request->send();
		$module_order = (!empty($response)) ? json_decode($response, true) : false;
		if (isset($module_list) && is_array($module_list) && array_key_exists(strtolower($module_type), $module_list)) {
			$breadcrumb = (isset($lang['navigation'][$module_type])) ? $lang['navigation'][$module_type] : $module_type;

			$GLOBALS['gui']->addBreadcrumb($breadcrumb, array('_g' => 'modules', 'type' => strtolower($module_type)));

			if (!empty($_GET['module']) || stristr($module_type, 'installer')) {
				// Load Module
				$GLOBALS['main']->wikiPage($_GET['module']);
				// Load additional data from XML
				$config_xml = CC_ROOT_DIR.'/modules/'.$module_type.'/'.$_GET['module'].'/config.xml';
				if (file_exists($config_xml)) {
					$xml   = new SimpleXMLElement(file_get_contents($config_xml));
					$module_info = array(
						'name' => (string)$xml->info->name,
					);
				} else {
					$module_info = array(
						'name' => str_replace('_', ' ', $_GET['module']),
					);
				}
				$module = array(
					'type' => strtolower($module_type),
					'module'=> ($module_type == 'installer') ? '' : $_GET['module'],
				);
				$GLOBALS['gui']->addBreadcrumb((isset($_GET['variant']) ? $_GET['variant'] : $module_info['name']), $_GET);

				$module_admin = CC_ROOT_DIR.'/modules/'.$module['type'].'/'.$module['module'].'/admin/'.$node.'.inc.php';
				if (file_exists($module_admin)) {
					define('MODULE_FORM_ACTION', (defined('VAL_SELF')) ? constant('VAL_SELF') : currentPage());

					$default_priority = $module_order[$module_type][strtolower($_GET['module'])];
					if (is_numeric($default_priority) && !isset($_POST['module']['position'])) {
						$_POST['module']['position'] = $default_priority;
					}
					include $module_admin;
				} else {
					trigger_error(sprintf("File '%s' doesn't exist", $module_admin), E_USER_WARNING);
				}
			} else {
				// List modules
				if (isset($_POST['status'])) {
					foreach ($_POST['status'] as $module_name => $status) {
						if ($GLOBALS['db']->select('CubeCart_modules', false, array('folder' => $module_name))) {
							$GLOBALS['db']->update('CubeCart_modules', array('status' => (int)$status), array('folder' => $module_name), true);
							if ($module_type=='plugins') {
								if ($status) {
									$GLOBALS['hooks']->install($module_name);
								} else {
									$GLOBALS['hooks']->uninstall($module_name);
								}
							}
						} else {
							$GLOBALS['db']->insert('CubeCart_modules', array('status' => (int)$status, 'folder' => $module_name, 'module' => $module_type));
						}
						// Update config
						$GLOBALS['config']->set($module_name, 'status', $status);
						$updated = true;
					}
					if ($updated) {
						$GLOBALS['gui']->setNotify($lang['module']['notify_module_status']);
					}

					httpredir(currentPage(null, array('order' => $_POST['order'])));
				}

				$GLOBALS['smarty']->assign('MODULES_TYPE', $lang['navigation']['nav_'.$module_type]);
				$GLOBALS['main']->addTabControl($lang['navigation']['nav_'.$module_type], 'modules');

				$module_type = strtolower($module_type);

				if (is_array($module_order) && $_GET['order']!=='alpha') {
					$other = 100;
					foreach ($module_list[$module_type] as $name) {
						$order = isset($module_order[$module_type][strtolower($name)]) ? $module_order[$module_type][strtolower($name)] : $other++;
						$ordered_name[$module_type][$order] = $name;
					}
					ksort($ordered_name[$module_type]);
					unset($module_list);
					$module_list = $ordered_name;
					$order_select['pop'] = ' selected="selected"';
				} else {
					natcasesort($module_list[$module_type]);
					$order_select['alpha'] = ' selected="selected"';
				}
				
				foreach ($module_list[strtolower($module_type)] as $module) {
					$module_config = $GLOBALS['config']->get($module);
					if (($module_info = $GLOBALS['db']->select('CubeCart_modules', false, array('module' => $module_type, 'folder' => $module))) !== false) {
						unset($module_config['status'], $module_config['default']);
						$module_config = array_merge($module_info[0], $module_config);
					}
					$module_info = array(
						'name'  => str_replace('_', ' ', $module),
						'type'  => strtolower($module_type),
						'node'  => $module,
						'status' => (isset($module_config['status']) && $module_config['status']) ? 1 : 0,
					);

					// Load additional data from XML
					$config_xml = CC_ROOT_DIR.'/modules/'.$module_type.'/'.$module.'/'.'config.xml';
					if (file_exists($config_xml)) {
						$xml  = new SimpleXMLElement(file_get_contents($config_xml));
						$xml_data = array(
							'name' => $xml->info->name,
						);
						$module_info = array_merge($module_info, $xml_data);
					}
					$module_logo  = new Module(false, $module, false);
					$module_info['title'] = $module_logo->module_fetch_logo($module_type, $module);
					$module_info['mobile_optimized'] = ($xml && strtolower($xml->info->mobile_optimized)=="true") ? true : false;
					if(!isset($xml->info->block) || (isset($xml) && $xml->info->block=='false')) { $modules[] = $module_info; }
				}
				$GLOBALS['smarty']->assign('MODULES', $modules);
				$GLOBALS['smarty']->assign('ORDER_SELECT', $order_select);
				if ($module_type=='gateway') {
					$GLOBALS['smarty']->assign('PLUGINS_LINK', true);
				}
				$page_content = $GLOBALS['smarty']->fetch('templates/modules.index.php');
			}
		} else {
			trigger_error(sprintf("Unknown module type '%s' - loading halted", $module_type), E_USER_WARNING);
		}
	} else if (strtolower($_GET['_g']) == 'plugin' && isset($_GET['name'])) {
			// Include plugins
			$GLOBALS['main']->wikiNamespace('Plugins');
			foreach ($GLOBALS['hooks']->load('admin.'.strtolower($_GET['name'])) as $hook) include $hook;
		} else if ($_GET['_g'] == '401') {
			$GLOBALS['gui']->setError($lang['navigation']['error_401']);
		} else {
		if (strtolower($_GET['_g']) == 'xml') {
			$suppress_output = true;
			// Process an XMLHTTPRequest
			$json = AJAX::load();
			@ob_end_clean();
			die($json);
		} else {
			// Everything else
			$include = $GLOBALS['main']->importNode($_GET['_g'], $node);
			if (file_exists($include)) {
				require $include;
			} else {
				trigger_error(sprintf('Unable to load content for %s:%s', $_GET['_g'], $node), E_USER_WARNING);
			}
		}
	}
} else {
	include CC_ROOT_DIR.'/'.$GLOBALS['config']->get('config', 'adminFolder').'/'.'sources/dashboard.index.inc.php';
}
$GLOBALS['main']->showHelp();

include CC_ROOT_DIR.'/'.$glob['adminFolder'].'/sources/element.navigation.inc.php';
if (is_array($nav_sections)) {
	foreach ($nav_sections as $key => $name) {
		if (isset($nav_items[$key]) && is_array($nav_items[$key])) {
			$GLOBALS['main']->addNavItem($name, $nav_items[$key]);
		}
	}
}
// Create the page tabs
$GLOBALS['main']->showTabs();
// Navigation
$GLOBALS['main']->showNavigation();
// Render main page content
if (!empty($page_content)) {
	$GLOBALS['smarty']->assign('DISPLAY_CONTENT', $page_content);
}

// jQuery UI & Themeroller styles
$styles = glob('js/{styles}/*.css', GLOB_BRACE);
if ($styles && is_array($styles)) {
	foreach ($styles as $style) {
		if (preg_match('#^ui\.#iuU', basename($style))) {
			$vars['jquery_styles'][] = str_replace('/', "/", $style);
		}
	}
	$GLOBALS['smarty']->assign('JQUERY_STYLES', $vars['jquery_styles']);
}