<?php
/**
 * CubeCart v5
 * ========================================
 * CubeCart is a registered trade mark of Devellion Limited
 * Copyright Devellion Limited 2010. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  http://www.cubecart.com/v5-software-license
 * ========================================
 * CubeCart is NOT Open Source.
 * Unauthorized reproduction is not allowed.
 *
 * @tutorial
 * To call hooks, use the following:
 *   foreach ($hooks->load('HOOK_TRIGGER') as $hook) include $hook;
 * Note that all hooks will be included into the current scope
 */

/**
 * Hook controller
 *
 * @author Technocrat
 * @version 1.1.0
 * @since 5.0.0
 */
class HookLoader {

	/**
	 * Enable/disable
	 *
	 * @var bool
	 */
	private $_enabled  = true;
	/**
	 * Hook path
	 *
	 * @var path
	 */
	private $_hook_dir  = false;
	/**
	 * Code snippet path
	 *
	 * @var path
	 */
	private $_snippet_dir  = false;
	/**
	 * Code snippet file prefix
	 *
	 * @var path
	 */
	private $_snippet_prefix  = 'snippet_';
	/**
	 * Hook list
	 *
	 * @var array
	 */
	private $_hook_list  = array();
	/**
	 * Code snippet list
	 *
	 * @var array
	 */
	private $_snippet_list  = array();
	/**
	 * Array of the currently loaded language files for hooks
	 * to help stop repeated lang loads
	 *
	 * @var array
	 */
	private $_loaded_lang = array();
	/**
	 * Plugin list
	 *
	 * @var array
	 */
	private $_plugin_list = array();

	/**
	 * Class instance
	 *
	 * @var instance
	 */
	protected static $_instance;

	final protected function __construct() {
		// Define the plugins directory
		$this->_hook_dir = CC_ROOT_DIR.'/modules/plugins';
		// Define the code snippets directory
		$this->_snippet_dir = CC_ROOT_DIR.'/includes/extra';
		// Generate a list of all hooks
		$this->_build_hooks_list(null, true);
		$this->_build_code_snippet_list(null, true);
	}

	/**
	 * Setup the instance (singleton)
	 *
	 * @return HookLoader
	 */
	public static function getInstance() {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	//=====[ Public ]====================================================================================================

	/**
	 * Delete code snippet include file
	 *
	 * @param bool $enable
	 */
	public function delete_snippet_file($unique_id = '') {

		if (is_numeric($unique_id)) {
			$unique_id = $this->_get_unique_id($unique_id);
		}

		if (!empty($unique_id)) {
			$full_path = $this->_snippet_dir.'/'.$this->_snippet_prefix.md5($unique_id).'.php';
			if (file_exists($full_path)) {
				return unlink($full_path);
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Enable/disable hooks
	 *
	 * @param bool $enable
	 */
	public function enable($enable = true) {
		$this->_enabled = (bool)$enable;
	}

	/**
	 * Load hook
	 *
	 * @param string $trigger
	 * @param unknown_type $gui
	 */
	public function load($trigger) {
		if ($this->_enabled && !empty($trigger) && !empty($this->_hook_list)) {
			// Find all registered hooks
			if (is_array($this->_hook_list) && isset($this->_hook_list[$trigger]) && !empty($this->_hook_list[$trigger])) {
				// Load hooks for plugins
				foreach ($this->_hook_list[$trigger] as $hook) {
					$this->_plugin_name($hook['plugin']);
					$this->_plugin_language($hook['plugin']);
					$hook['filepath'] = (!empty($hook['filepath'])) ? str_replace('/', '/', $hook['filepath']) : 'hooks/'.$trigger.'.php';
					if (file_exists($this->_hook_dir.'/'.$hook['plugin'].'/'.$hook['filepath']) && $this->_security_check($hook['filepath'])) {
						$include[] = $this->_hook_dir.'/'.$hook['plugin'].'/'.$hook['filepath'];
					} else {
						trigger_error("Error: Hook '".$hook['plugin'].'/'.$hook['filepath']."' was not found", E_USER_NOTICE);
					}
				}

			}
		}

		// Load hook for code snippets
		if ($this->_snippet_list) {
			foreach ($this->_snippet_list as $snippet) {
				if ($snippet['hook_trigger'] == $trigger) {
					$file_name = $this->_snippet_dir.'/'.$this->_snippet_prefix.md5($snippet['unique_id']).'.php';
					if (file_exists($file_name)) {
						$include[] = $file_name;
					} else {
						if (file_put_contents($file_name, $snippet['php_code'])) {
							$include[] = $file_name;
						} else {
							trigger_error("Error: Failed to write code snippet for '".$snippet['description']."'", E_USER_NOTICE);
						}
					}
				}
			}
		}
		return (isset($include) && is_array($include)) ? $include : array();
	}

	public function is_enabled($trigger, $plugin) {

		if (!empty($trigger) && !empty($plugin)) {
			$result = $GLOBALS['db']->select('CubeCart_hooks', array('enabled'), array('trigger' => $trigger, 'plugin' => $plugin));
			if ($result[0]['enabled']==1) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Scan for new plugs
	 *
	 * @return bool
	 */
	public function scan_plugins() {
		// Scan the plugins directory for config files, and adds the hooks to the database if they don't already exist
		if (($files = glob($this->_hook_dir.'/'.'*', GLOB_ONLYDIR | GLOB_NOSORT)) !== false) {
			foreach ($files as $file) {
				$this->install(basename($file));
			}
			return true;
		}
		return false;
	}

	/**
	 * Scan for all plugs
	 *
	 * @return bool
	 */
	public function scan_all_plugins($dir = 'plugins', $enabled = false) {

		$dir = ($dir=='plugins') ? $this->_hook_dir : $dir;
		if (($folders = glob($dir.'/'.'*', GLOB_ONLYDIR)) !== false) {
			foreach ($folders as $folder) {
				$basename = basename($folder);

				if ($enabled) {
					$plugin = $GLOBALS['config']->get($basename);
					if (!$plugin['status']) continue;
				}

				$plugins[$basename] = array(
					'plugin' => $basename,
					'name'  => str_replace('_', ' ', $basename),
				);
			}
			return $plugins;
		}
		return array();
	}


	/**
	 * Import code snippets
	 *
	 * @param $_FILES array
	 *
	 * @return bool
	 */

	public function import_code_snippets($file) {
		if (file_exists($file['tmp_name'])) {
			if ($file['size']>0) {
				if (in_array($file['type'], array('application/x-zip', 'application/zip'))) {

					require_once CC_INCLUDES_DIR.'lib/pclzip/pclzip.lib.php';

					$archive = new PclZip($file['tmp_name']);
					$extract  = $archive->extract(PCLZIP_OPT_PATH, CC_ROOT_DIR.'/cache');
					if ($extract == 0) {
						trigger_error("Error: ".$archive->errorInfo(true), E_USER_NOTICE);
					} else {
						$contents = file_get_contents($extract[0]['filename']);
						if (is_array($extract)) {
							foreach ($extract as $extracted) {
								if (file_exists($extracted['filename'])) {
									unlink($extracted['filename']);
								}
							}
						}
					}

				} else {
					$contents = file_get_contents($file['tmp_name']);
				}
				try {
					$xml   = new simpleXMLElement($contents);
					foreach ($xml->snippets->snippet as $snippet) {
						$record = array(
							'author'  => $xml->info->author,
							'enabled'  => $snippet->enabled,
							'description' => $snippet->description,
							'hook_trigger' => $snippet->hook_trigger,
							'php_code'  => $snippet->php_code,
							'version'  => $snippet->version,
							'priority'  => $snippet->priority,
						);

						if ($GLOBALS['db']->select('CubeCart_code_snippet', array('snippet_id'), array('unique_id' => $snippet->unique_id))) {
							$GLOBALS['db']->update('CubeCart_code_snippet', $record, array('unique_id' => $snippet->unique_id));
						} else {
							$record['unique_id'] = $snippet->unique_id;
							$GLOBALS['db']->insert('CubeCart_code_snippet', $record);
						}
						$this->delete_snippet_file($snippet->unique_id);
					}
					return true;
				} catch (Exception $e) {
					trigger_error("Error: Code snippet file does not contains valid XML.", E_USER_NOTICE);
				}
			} else {
				trigger_error("Error: Code snippet import failed as the file is ".$file['size']." bytes in size.", E_USER_NOTICE);
			}
		} else {
			trigger_error("Error: Code snippet import for '".$file['tmp_name']."' doesn't exist.", E_USER_NOTICE);
		}
		return false;
	}

	/**
	 * Install new plugin
	 *
	 * @param string $plugin
	 *
	 * @return bool
	 */
	public function install($plugin) {
		if (!empty($plugin)) {
			$this->_plugin_name($plugin);
			$file = $this->_hook_dir.'/'.$plugin.'/'.'config.xml';
			if (file_exists($file)) {
				// Read each XML file, check contents, and update/add to database
				try {
					$xml   = new simpleXMLElement(file_get_contents($file));
					$plugin_name = (string)$xml->info->name;
					foreach ($xml->hooks->hook as $hook) {
						// Check if the hook already exists
						$allowed_hooks[] = (string)$hook->attributes()->trigger;
						$check = $GLOBALS['db']->select('CubeCart_hooks', array('hook_id'), array('plugin' => $plugin, 'trigger' => (string)$hook->attributes()->trigger));

						$record = array(
							'plugin' => $plugin,
							'hook_name' => (string)$hook,
							'trigger' => (string)$hook->attributes()->trigger,
							'priority' => (int)$hook->attributes()->priority,
							'filepath' => (string)$hook->file,
						);

						if ($check) {
							$GLOBALS['db']->update('CubeCart_hooks', $record, array('plugin' => $plugin, 'trigger' => (string)$hook->attributes()->trigger));
						} else {
							$record['enabled'] = (int)$hook->attributes()->enabled;
							$GLOBALS['db']->insert('CubeCart_hooks', $record);
						}
					}
					// remove hooks not allowed
					$GLOBALS['db']->misc("DELETE FROM `".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_hooks` WHERE `plugin` = '".$plugin."' AND `trigger` NOT IN ('".implode("','", $allowed_hooks)."')");
					return true;
				} catch (Exception $e) {}
			}
		}
		return false;
	}

	/**
	 * Uninstall plugin
	 *
	 * @param string $plugin
	 *
	 * @return bool
	 */
	public function uninstall($plugin) {
		if (!empty($plugin)) {
			return $GLOBALS['db']->delete('CubeCart_hooks',  array('plugin' => $plugin));
		}
	}


	//=====[ Private ]===================================================================================================

	/**
	 * Build hook list
	 *
	 * @param string $trigger
	 * @param bool $enabled_only
	 *
	 * @return bool
	 */
	private function _build_hooks_list($trigger = null, $enabled_only = true) {
		$where = array();
		if (!is_null($trigger) && !empty($trigger)) {
			$where['trigger'] = $trigger;
		}
		if ($enabled_only) {
			$where['enabled'] = '1';
		}

		if (($hooks = $GLOBALS['db']->select('CubeCart_hooks', false, $where, 'priority ASC')) !== false) {
			foreach ($hooks as $hook) {
				$this->_security_check($hook['filepath']);
				$this->_plugin_name($hook['plugin']);
				$this->_hook_list[$hook['trigger']][$hook['plugin']] = $hook;
			}
			return true;
		}
		return false;
	}

	/**
	 * Build hook list
	 *
	 * @param string $trigger
	 * @param bool $enabled_only
	 *
	 * @return bool
	 */
	private function _build_code_snippet_list($trigger = null, $enabled_only = true) {
		$where = array();
		if (!is_null($trigger) && !empty($trigger)) {
			$where['hook_trigger'] = $trigger;
		}
		if ($enabled_only) {
			$where['enabled'] = '1';
		}

		if ($snippets = $GLOBALS['db']->select('CubeCart_code_snippet', array('php_code', 'unique_id', 'description', 'hook_trigger'), $where, array('priority' => 'ASC'))) {
			$this->_snippet_list = $snippets;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get hook unique ID
	 *
	 * @param int code snippet id
	 *
	 * @return bool
	 */
	private function _get_unique_id($snippet_id) {
		if ($id = $GLOBALS['db']->select('CubeCart_code_snippet', array('unique_id'), array('snippet_id' => $snippet_id))) {
			return $id[0]['unique_id'];
		} else {
			return false;
		}
	}

	/**
	 * Setup plugin language
	 *
	 * @param string $plugin
	 */
	private function _plugin_language($plugin) {
		$lang_dir = $this->_hook_dir.'/'.$plugin.'/'.'language';
		if (!isset($this->_loaded_lang[$plugin])) {
			if (file_exists($lang_dir)) {
				$GLOBALS['language']->loadDefinitions($plugin, $lang_dir, 'module.definitions.xml');
				$strings = $GLOBALS['language']->loadLanguageXML($plugin, '', $lang_dir);
				/* already done above
				if (!empty($strings)) {
					$GLOBALS['language']->addStrings($strings);
				}
*/
				unset($strings);
				$this->_loaded_lang[$plugin] = true;
				$GLOBALS['language']->assignLang(); // Make the language strings fresh
			}
		}
	}

	/**
	 * Setup plugin name
	 *
	 * @param string $plugin_name
	 *
	 * @return bool
	 */
	private function _plugin_name(&$plugin_name) {
		$plugin_name = preg_replace('#[^a-z0-9]#iU', '_', $plugin_name);
		if (!in_array($plugin_name, $this->_plugin_list)) {
			$this->_plugin_list[] = $plugin_name;
			ini_set('include_path', ini_get('include_path').CC_PS.$this->_hook_dir.'/'.$plugin_name);
		}
	}

	/**
	 * Check plugin
	 *
	 * @param string $filename
	 */
	private static function _security_check(&$filename) {
		$find  = array('#^[^a-z0-9.\\\\/_]$#iU', '#(/+)|(\\\+)#', '#\.{1,2}/#');
		$replace = array('', '/', '');
		$filename = preg_replace($find, $replace, $filename);
		return true;
	}

}