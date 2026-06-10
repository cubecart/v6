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
 * Module controller
 */
class Module
{
	public $_settings;

	private $_content;
	private $_folder_name;
	private $_info = array();
	private $_path;
	private $_template;
	private $_template_data = array();

	##############################################

	public function __construct($path = false, $folder_name = false, $template = 'index.php', $zones = false, $fetch = true)
	{
		$this->_template = $template;

		if (!$path) {
			return;
		}

		$this->_loadModuleData($path, $folder_name);
		$this->_loadModuleClasses();

		if (isset($_POST['module']['status']) && is_array($_POST['module'])) {
			$this->_handleSave();
			$this->_loadModuleData($path, $folder_name);
		}

		$GLOBALS['main']->addTabControl($GLOBALS['language']->common['general'], $_GET['module']);
		$GLOBALS['smarty']->assign('GENERAL_TAB_ID', $_GET['module']);

		$GLOBALS['language']->loadDefinitions($this->_folder_name, $this->_path.'/language', 'module.definitions.xml');
		$GLOBALS['language']->loadLanguageXML($this->_folder_name, '', $this->_path.'/language');

		if ($template) {
			$this->_setupTemplate($zones);
			$GLOBALS['language']->setTemplate();
			if ($fetch) {
				$this->fetch();
			}
		}
	}

	//=====[ Public ]=======================================

	public function __get($key)
	{
		return (isset($this->_settings[$key])) ? $this->_settings[$key] : false;
	}

	public function assign_to_template($name, $value = null)
	{
		if (is_array($name) && !empty($name)) {
			foreach ($name as $key => $val) {
				$this->_template_data[$key] = $val;
			}
			return true;
		}
		if (!empty($name) && !is_null($value)) {
			$this->_template_data[$name] = $value;
			return true;
		}
		return false;
	}

	public function communicateURL($method = 'process')
	{
		if ($method === 'from') {
			return $GLOBALS['storeURL'].'/index.php?_a=gateway';
		}
		return $GLOBALS['storeURL'].'/index.php?_g=rm&type='.$this->_info['type'].'&cmd='.$method.'&module='.$this->_folder_name;
	}

	public function display($return = true)
	{
		if ($return) {
			return $this->_content;
		}
		echo $this->_content;
	}

	public function fetch()
	{
		if (!$GLOBALS['smarty']->templateExists($this->_template)) {
			return false;
		}
		foreach ($this->_template_data as $key => $value) {
			$GLOBALS['smarty']->assign($key, $value);
		}
		$this->_content = $GLOBALS['smarty']->fetch($this->_template);
		$GLOBALS['gui']->changeTemplateDir();
	}

	public function get_logo($type, $name, $module_title = '')
	{
		$title = (!empty($module_title)) ? $module_title : str_replace('_', ' ', $name);
		// Primary: modules/{type}/{name}/images/logo.*
		$images = cc_glob(CC_ROOT_DIR.'/modules/'.$type.'/'.$name.'/images/logo.{png,jpg,jpeg,gif,svg,webp}');
        $style = 'style="max-width:200px; max-height:50px;"';
		if (is_array($images) && isset($images[0])) {
			return '<img src="modules/'.$type.'/'.$name.'/images/'.basename($images[0]).'" alt="'.$title.'" title="'.$title.'" '.$style.' />';
		}
		// Legacy: modules/{type}/{name}/admin/logo.*
		$images = cc_glob(CC_ROOT_DIR.'/modules/'.$type.'/'.$name.'/admin/logo.{png,jpg,jpeg,gif,svg,webp}');
		if (is_array($images) && isset($images[0])) {
			return '<img src="modules/'.$type.'/'.$name.'/admin/'.basename($images[0]).'" alt="'.$title.'" title="'.$title.'" '.$style.' />';
		}
		return $title;
	}

	public function get_zones($label)
	{
		if (!isset($_POST[$label]) || !is_array($_POST[$label])) {
			return '';
		}
		$zones = array();
		foreach ($_POST[$label] as $zone) {
			if (!empty($zone)) {
				$zones[$zone] = $zone;
			}
		}
		return !empty($zones) ? serialize($zones) : '';
	}

	public function module_settings_save($settings)
	{
		if (empty($settings) || !is_array($settings)) {
			return false;
		}

		$updated = false;

		$settings['countries'] = $this->get_zones('zones');
		$settings['disabled_countries'] = $this->get_zones('disabled_zones');

		// Save packaging boxes (global, shared across all shipping modules)
		if (isset($_POST['packaging_boxes'])) {
			$boxes = array();
			foreach ((array)$_POST['packaging_boxes'] as $box) {
				if (!empty($box['name'])) {
					$boxes[] = array(
						'name' => trim($box['name']),
						'l'    => round((float)$box['l'], 4),
						'w'    => round((float)$box['w'], 4),
						'h'    => round((float)$box['h'], 4),
					);
				}
			}
			$GLOBALS['config']->set('config', 'packaging_boxes', $boxes);
		}

		// Save module config
		if ($GLOBALS['config']->set($this->_folder_name, '', $settings)) {
			$updated = true;
		}

		// Handle default flag: unset all others first
		if (!empty($settings['default'])) {
			$GLOBALS['db']->update('CubeCart_modules', array('default' => 0), array('module' => $this->_info['type']));
		}

		// Upsert module record
		$data = array(
			'folder'   => $this->_folder_name,
			'module'   => $this->_info['type'],
			'status'   => $settings['status'],
			'position' => (isset($settings['position']) && $settings['position'] > 0) ? $settings['position'] : 0,
		);
		if (isset($settings['default'])) {
			$data['default'] = $settings['default'];
		}
		$GLOBALS['db']->delete('CubeCart_modules', array('module' => $this->_info['type'], 'folder' => $this->_folder_name));
		if ($GLOBALS['db']->insert('CubeCart_modules', $data)) {
			$updated = true;
		}

		return $updated;
	}

	//=====[ Private ]=======================================

	private function _getRawVars()
	{
		$rawvars = array();
		$config_file = $this->_path.'/config.xml';
		if (file_exists($config_file)) {
			try {
				$xml = new SimpleXMLElement(file_get_contents($config_file, true));
				foreach ((array)$xml->rawvars->var as $value) {
					$rawvars[] = (string)$value;
				}
			} catch (Exception $e) {
				trigger_error($e->getMessage(), E_USER_WARNING);
			}
		}
		return $rawvars;
	}

	private function _handleSave()
	{
		$display_name = str_replace('_', ' ', $this->_folder_name);

		// Substitute raw POST values for rawvar fields
		foreach ($this->_getRawVars() as $key_name) {
			$_POST['module'][$key_name] = $GLOBALS['RAW']['POST']['module'][$key_name];
		}

		if ($this->module_settings_save($_POST['module'])) {
			$GLOBALS['main']->successMessage(sprintf($GLOBALS['language']->notification['notify_module_settings'], $display_name));
		} else {
			$GLOBALS['main']->errorMessage(sprintf($GLOBALS['language']->notification['error_module_settings'], $display_name));
		}

		// Install/uninstall hooks based on status
		if ($_POST['module']['status']) {
			$GLOBALS['hooks']->install($this->_folder_name);
		} else {
			$GLOBALS['hooks']->uninstall($this->_folder_name);
		}
	}

	private function _loadModuleClasses()
	{
		$classes_dir = $this->_path.'/classes';
		if (is_dir($classes_dir)) {
			foreach (glob($classes_dir.DIRECTORY_SEPARATOR.'*.inc.php', GLOB_NOSORT) as $include) {
				require $include;
			}
		}
	}

	private function _loadModuleData($path, $folder_name)
	{
		if ($path) {
			$drop = array(CC_DS.'admin', CC_DS.'classes', CC_DS.'skin', CC_DS.'language');
			$this->_path = CC_ROOT_DIR.str_replace($drop, '', dirname(str_replace(CC_ROOT_DIR, '', $path)));
			$this->_path = rtrim($this->_path, '/');
		}

		$this->_folder_name = $folder_name;

		$config_file = $this->_path.'/config.xml';
		if (!file_exists($config_file)) {
			return;
		}

		try {
			$xml = new SimpleXMLElement($config_file, LIBXML_NOCDATA, true);
			if (isset($xml->info)) {
				$config_array = json_decode(json_encode($xml->info), true);
				if (is_array($config_array)) {
					foreach ($config_array as $key => $value) {
						$this->_info[$key] = (string)$value;
					}
				}
			}
		} catch (Exception $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
			return;
		}

		$config = $GLOBALS['config']->get($folder_name);
		$module = $GLOBALS['db']->select('CubeCart_modules', false, array('folder' => $folder_name));
		$this->_settings = ($module) ? array_merge($module[0], $config) : $config;
	}

	private function _setupTemplate($zones)
	{
		$GLOBALS['gui']->changeTemplateDir($this->_path.'/skin');

		// Fall back to .tpl if .php template doesn't exist (third-party modules)
		if (substr($this->_template, -4) === '.php' && !$GLOBALS['smarty']->templateExists($this->_template)) {
			$this->_template = substr($this->_template, 0, -4) . '.tpl';
		}

		$lang = $GLOBALS['language']->getStrings(strtolower($this->_folder_name));
		$xml_name = !empty($this->_info['name']) ? $this->_info['name'] : str_replace('_', ' ', $this->_folder_name);
		$display_name = isset($lang['module_title']) ? $lang['module_title'] : $xml_name;
		$GLOBALS['smarty']->assign('TITLE', $this->get_logo($this->_info['type'], $this->_folder_name, $display_name));

		// Tax types dropdown
		if (($taxes = $GLOBALS['db']->select('CubeCart_tax_class', array('id', 'tax_name'), false, array('tax_name' => 'ASC'))) !== false) {
			$taxes[] = array('id' => 999999, 'tax_name' => $GLOBALS['language']->common['inherit']);
			$tax_list = array();
			foreach ($taxes as $tax) {
				$tax['selected'] = (isset($this->_settings['tax']) && $this->_settings['tax'] == $tax['id']) ? "selected='selected'" : '';
				$tax_list[] = $tax;
			}
			$GLOBALS['smarty']->assign('TAXES', $tax_list);
		}

		// Assign module settings to template
		if (!empty($this->_settings)) {
			$GLOBALS['debug']->debugTail($this->_settings, $this->_folder_name.': settings');

			if ($this->_info['type'] === 'gateway') {
				$this->_settings['processURL'] = $this->communicateURL('process');
				$this->_settings['callURL'] = $this->communicateURL('call');
				$this->_settings['fromURL'] = $this->communicateURL('from');
			}

			$basesettings = array();
			foreach ($this->_settings as $key => $value) {
				if (is_array($value)) {
					$GLOBALS['smarty']->assign('MODULE_'.strtoupper($key), $value);
				} else {
					$basesettings[$key] = $value;
				}
			}
			$GLOBALS['smarty']->assign('MODULE', $basesettings);

			// Assign SELECT_ and CHECKED_ helpers for form elements
			foreach (array_filter($this->_settings, 'is_scalar') as $setting => $value) {
				$safe_value = str_replace(array('.', '-'), '_', $value);
				$GLOBALS['smarty']->assign('SELECT_'.$setting.'_'.$safe_value, 'selected="selected"');
				$GLOBALS['smarty']->assign('CHECKED_'.$setting.'_'.$safe_value, 'checked="checked"');
			}
		}

		$GLOBALS['smarty']->assign('CONFIG', $GLOBALS['config']->get('config'));

		// Zone selector + packaging tabs. Country zones apply to gateway and
		// shipping modules, plus plugin-based gateways (a plugin that ships a
		// gateway.class.php) — those are the only modules whose countries /
		// disabled_countries settings are actually enforced at checkout
		// (see Cubecart::_gateways() and Cart::loadShippingModules()). Packaging
		// remains shipping-only.
		$type = $_GET['type'] ?? '';
		$is_gateway_plugin = ($type === 'plugins' && file_exists($this->_path.'/gateway.class.php'));
		if ($zones && (in_array($type, array('gateway', 'shipping')) || $is_gateway_plugin)) {
			$this->_loadZones();
			if ($type === 'shipping') {
				$this->_loadPackaging();
			}
			$GLOBALS['gui']->changeTemplateDir($this->_path.'/skin');
		} else {
			$GLOBALS['smarty']->assign('MODULE_ZONES', '');
		}
	}

	private function _loadPackaging()
	{
		$boxes = $GLOBALS['config']->get('config', 'packaging_boxes');
		$boxes = is_array($boxes) ? $boxes : array();
		$wunit = $GLOBALS['config']->get('config', 'product_weight_unit');
		$dim_unit = ($wunit === 'Lb') ? 'in' : 'cm';

		$GLOBALS['smarty']->assign('PACKAGING_BOXES', $boxes);
		$GLOBALS['smarty']->assign('PACKAGING_DIM_UNIT', $dim_unit);

		$GLOBALS['main']->addTabControl(
			$GLOBALS['language']->settings['packaging_tab'],
			'packaging-boxes', null, null, count($boxes), '', 999999
		);
		$GLOBALS['gui']->changeTemplateDir();
		$GLOBALS['smarty']->assign('LANG', $GLOBALS['lang']);
		$packaging_html = $GLOBALS['smarty']->fetch('templates/modules.packaging.php');

		$existing = $GLOBALS['smarty']->getTemplateVars('MODULE_ZONES');
		$GLOBALS['smarty']->assign('MODULE_ZONES', $existing.$packaging_html);
	}

	private function _loadZones()
	{
		$countries = $GLOBALS['db']->select('CubeCart_geo_country', array('numcode', 'name', 'status'), 'status > 0', array('name' => 'ASC'));
		if ($countries === false) {
			return;
		}

		$options = array();
		$all_countries = array();
		foreach ($countries as $country) {
			$options[$country['numcode']] = $country;
			$all_countries[] = $country;
		}
		$GLOBALS['smarty']->assign('ALL_COUNTRIES', $all_countries);

		// Enabled zones
		$enabled = (!empty($this->_settings['countries'])) ? unserialize($this->_settings['countries']) : array();
		$enabled_countries = array();
		if (is_array($enabled)) {
			sort($enabled);
			foreach ($enabled as $code) {
				if (isset($options[$code])) {
					$enabled_countries[] = $options[$code];
				}
			}
		}
		$GLOBALS['smarty']->assign('ENABLED_COUNTRIES', $enabled_countries);
		$GLOBALS['main']->addTabControl($GLOBALS['language']->settings['allowed_zones'], 'zone-list', null, null, count($enabled_countries), '', 999999);

		$GLOBALS['gui']->changeTemplateDir();
		$GLOBALS['smarty']->assign('LANG', $GLOBALS['lang']);
		$zone_tabs = $GLOBALS['smarty']->fetch('templates/modules.zones.php');

		// Disabled zones
		$disabled = (!empty($this->_settings['disabled_countries'])) ? unserialize($this->_settings['disabled_countries']) : array();
		$disabled_countries = array();
		if (is_array($disabled)) {
			sort($disabled);
			foreach ($disabled as $code) {
				if (isset($options[$code])) {
					$disabled_countries[] = $options[$code];
				}
			}
		}
		$GLOBALS['smarty']->assign('DISABLED_COUNTRIES', $disabled_countries);
		$GLOBALS['main']->addTabControl($GLOBALS['language']->settings['disabled_zones'], 'disabled-zone-list', null, null, count($disabled_countries), '', 999999);

		$zone_tabs .= $GLOBALS['smarty']->fetch('templates/modules.zones-disabled.php');
		$GLOBALS['smarty']->assign('MODULE_ZONES', $zone_tabs);
	}
}
