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
 */

/**
 * Configuration controller
 *
 * @author Technocrat
 * @version 1.0.0
 * @since 5.0.0
 */
class Config {

	/**
	 * Current config
	 *
	 * @var array
	 */
	private $_config = array();
	/**
	 * Temp configs that should not be written to the db
	 *
	 * @var array
	 */
	private $_temp  = array();
	/**
	 * Write the config to the DB
	 *
	 * @var bool
	 */
	private $_write_db = false;
	/**
	 * Array of variables before config is written used for validation
	 *
	 * @var array
	 */
	private $_pre_enc_config = array();

	/**
	 * Class instance
	 *
	 * @var instance
	 */
	protected static $_instance;

	final protected function __construct($glob) {
		//Get the main config because it will be used
		if (isset($GLOBALS['db']) && ($result = $GLOBALS['db']->select('CubeCart_config', array('array'), array('name' => 'config'), false, 1, false, false)) !== false) {
			$array_out = $this->_json_decode($result[0]['array']);
		}

		//Remove the db password for safety
		unset($glob['dbpassword']);

		if (!empty($array_out)) {
			$this->_config['config'] = $this->_clean($array_out);
			//Merge the main global with the config
			if (is_array($this->_config['config'])) {
				$this->_config['config'] = array_merge($this->_config['config'], $glob);
			}
		} else {
			$this->_config['config'] = $glob;
		}
	}

	public function __destruct() {
		//Do we need to write to the db
		if ($this->_write_db) {
			$this->_writeDB();
		}
	}

	/**
	 * Setup the instance (singleton)
	 *
	 * @param $glob array Current globals
	 *
	 * @return Config
	 */
	public static function getInstance($glob = array()) {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($glob);
		}

		return self::$_instance;
	}

	//=====[ Public ]====================================================================================================

	/**
	 * Get a value from the config
	 *
	 * Not all config types are loaded from the start this
	 * is done to save cycles and memory
	 *
	 * If element is empty the entire array of the config
	 * is returned
	 *
	 * @param string $config_name
	 * @param string $element
	 *
	 * @return mixed / false
	 */
	public function get($config_name, $element = '') {
		//If there is an config
		if (isset($this->_config[$config_name])) {
			//If there is not an element the entire array
			if (empty($element)) {
				return $this->_config[$config_name];
			} else if (isset($this->_config[$config_name][$element])) {
					return $this->_config[$config_name][$element];
				}

			return false;
		}

		//If we reached this part try to fetch it
		$this->_fetchConfig($config_name);

		//Return it if found
		return $this->get($config_name, $element);
	}

	/**
	 * Is there a config element
	 *
	 * @param string $config_name
	 * @param string $element
	 *
	 * @return bool
	 */
	public function has($config_name, $element) {
		return ($this->get($config_name, $element)) !== false;
	}

	/**
	 * Merge an emlemet to the config
	 *
	 * This is done for items that do not need to be recorded to the db
	 * or are single use config items.  For example ssl enable/disable.
	 *
	 * @param string $config_name
	 * @param string $element
	 * @param string $data
	 */
	public function merge($config_name, $element, $data) {
		if (!empty($element)) {
			$this->_temp[$config_name][$element] = $data;
			$this->_config[$config_name][$element] = $data;
		} else {
			if (is_array($data)) {
				if (isset($this->_temp[$config_name])) {
					$this->_temp[$config_name] = merge_array($this->_temp[$config_name], $data);
				} else {
					$this->_temp[$config_name] = $data;
				}
				$this->_config[$config_name] = merge_array($this->_config[$config_name], $data);
			}
		}
	}

	/**
	 * Is an element empty
	 *
	 * @param string $config_name
	 * @param string $element
	 *
	 * @return bool
	 */
	public function isEmpty($config_name, $element) {
		//If the element isn't there then it is empty
		if (!$this->has($config_name, $element)) {
			return true;
		}

		return empty($this->_config[$config_name][$element]);
	}

	/**
	 * Set a config value
	 *
	 * If no element is set then the entire config is
	 * set to the data
	 *
	 * @param string $config_name
	 * @param string $element
	 * @param string $data
	 * @param bool $force_write
	 *
	 * @return bool
	 */
	public function set($config_name, $element, $data, $force_write = false) {
		//Clean up the config array
		if (is_array($data) && !empty($element)) {
			array_walk_recursive($data, create_function('&$s, $k', '$s=stripslashes($s);'));
			$data = $this->_json_encode($data);
		} else if (is_array($data)) {
				array_walk_recursive($data, create_function('&$s, $k', '$s=stripslashes($s);'));
			} else {
			$data = stripslashes($data);
		}

		/**
		 * Check to see if the data is the same as it was.
		 * If it is we dont need to do anything
		 */
		if ($this->get($config_name, $element) == $data) {
			return true;
		}

		//If there isn't an element assign the entire thing
		if (empty($element)) {
			$this->_config[$config_name] = $data;
		} else {
			$this->_config[$config_name][$element] = $data;
		}

		//Write the to the db
		if (!$force_write) {
			$this->_write_db = true;
		} else {
			$this->_writeDB();
			$this->_write_db = false;
		}

		return true;
	}

	//=====[ Private ]===================================================================================================

	/**
	 * Strip slashes
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	private function _clean($array) {
		array_walk_recursive($array, create_function('&$s,$k', '$s=stripslashes($s);'));
		return $array;
	}

	/**
	 * Fetch config data
	 *
	 * @param string $name
	 */
	private function _fetchConfig($name) {
		//Clean up the entire config array
		$this->_config[$name] = array();

		//If the DB class exists and the config row exists
		if (isset($GLOBALS['db']) && ($result = $GLOBALS['db']->select('CubeCart_config', array('array'), array('name' => $name), false, 1, false)) !== false) {
			$array_out = $this->_json_decode($result[0]['array']);

			if (($module = $GLOBALS['db']->select('CubeCart_modules', array('status', 'countries'), array('folder' => $name), false, 1, false)) !== false) {
				$array_out = array_merge($module[0], $array_out);
			}

			if (!empty($array_out)) {
				$this->_config[$name] = $this->_clean($array_out);
			}
		}
	}

	/**
	 * Json decode but convert if serialized
	 */
	private function _json_decode($string) {
		if (preg_match('/^a:[0-9]/', $string)) { // convert from serialized and next save will convert
			$array = unserialize($string);
			if (isset($array['offline_content']) && !empty($array['offline_content'])) {
				$array['offline_content'] = base64_decode($array['offline_content']);
			}
			if (isset($array['store_copyright']) && !empty($array['store_copyright'])) {
				$array['store_copyright'] = base64_decode($array['store_copyright']);
			}
			return $array;
		} else {
			return json_decode(base64_decode($string), true);
		}
	}

	/**
	 * Json encode
	 */
	private function _json_encode($array) {
		$this->_pre_enc_config = $array;
		return base64_encode(json_encode($array));
	}

	/**
	 * Write config to db
	 */
	private function _writeDB() {
		if (!empty($this->_config) && is_array($this->_config)) {
			foreach ($this->_config as $config => $data) {
				//Remove data that was merged in
				if (!empty($this->_temp) && isset($this->_temp[$config])) {
					$match = array_intersect_key($this->_temp[$config], $this->_config[$config]);
					if (!empty($match)) {
						foreach ($match as $k => $v) {
							unset($data[$k]);
						}
					}
				}
				//If there is a problem abort
				if (empty($data)) {
					continue;
				}
				$record = array('array' => $this->_json_encode($data));
				if (strlen($record['array']) > 65535 || strlen($config) > 100) {
					trigger_error('Config write size error: '.$config, E_USER_ERROR);
				}
				if (Database::getInstance()->count('CubeCart_config', 'name', array('name' => $config))) {
					//Safeguard to prevent config loss
					if ($config=='config' && !isset($this->_pre_enc_config['store_name'])) {
						return false;
					} else {
						Database::getInstance()->update('CubeCart_config', $record, array('name' => $config));
					}
				} else {
					$record['name'] = $config;
					Database::getInstance()->insert('CubeCart_config', $record);
				}
			}

			Cache::getInstance()->clear('sql');
		}
	}
}