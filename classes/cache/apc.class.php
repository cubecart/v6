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

if (!defined('CC_INI_SET')) die('Access Denied');
require CC_ROOT_DIR.'/classes/cache/cache.class.php';

/**
 * Cache specific class
 *
 * @author Technocrat
 * @version 1.1.0
 * @since 5.0.0
 */
class Cache extends Cache_Controler {
	final protected function __construct() {
		//Make sure we can use APC
		if (!extension_loaded('APC') || !ini_get('apc.enabled')) {
			$this->_online = false;
			return ;
		}
		$this->_mode = 'APC';
		$this->_online = true;
		
		$this->_getEmpties();

		//Run the parent constructor
		parent::__construct();
	}
	
	public function __destruct() {
		if($this->_emptied_added) $this->write($this->_empties, $this->_empties_id);
	}

	/**
	 * Setup the instance (singleton)
	 *
	 * @return instance
	 */
	public static function getInstance() {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Clear all the cache or particular cache types
	 *
	 * @param string $type Cache type prefix
	 * @return bool
	 */
	public function clear($type = '') {
		//Get the current cache IDs
		$this->getIDs();

		if (!empty($type)) {
			$type = strtolower($type);
			$len = strlen($type);
		}

		$return = true;
		if (!empty($this->_ids)) {
			//Loop through each id to delete it
			foreach ($this->_ids as $id) {
				//If there is a type we need to only delete that
				if (!empty($type)) {
					if (substr($id, 0, $len) == $type) {
						if (!$this->delete($id)) {
							$return = false;
						}
					}
				} else {
					//If no type delete every id
					if (!$this->delete($id)) {
						$return = false;
					}
				}
			}
		} else {
			@apc_clear_cache('user');
		}

		return $return;
	}

	/**
	 * Remove a single item of cache
	 *
	 * @param string $id Cache identifier
	 * @return bool
	 */
	public function delete($id) {
		if (!$this->status()) {
			return true;
		}

		return apc_delete($this->_makeName($id));
	}

	/**
	 * Check to see if the cache file exists
	 *
	 * @param string $id Cache identifier
	 * @return bool
	 */
	public function exists($id) {
		if (!$this->status()) {
			return false;
		}

		$name = $this->_makeName($id);

		//Try to set the temp variable to the item
		if (($this->_temp[$name] = apc_fetch($name)) !== false) {
			return true;
		}

		return false;
	}

	/**
	 * Get all the cache ids
	 *
	 * @return array
	 */
	public function getIDs() {
		if (empty($this->_ids)) {
			$info = @apc_cache_info('user');
			if (!empty($info) && is_array($info)) {
				foreach ($info['cache_list'] as $item) {
					if (isset($item['info']) && strpos($item['info'], $this->_prefix)) {
						$this->_ids[] = str_replace(array($this->_prefix, $this->_suffix), '', $item['info']);
					}
				}
			}
		}

		return $this->_ids;
	}
	
	private function _getEmpties() {
		$this->_setPrefix();
		$this->_empties = $this->read($this->_empties_id);
	}
	
	/**
	 * Get the cached data
	 *
	 * @param string $id Cache identifier
	 * @return data/false
	 */
	public function read($id) {
		if (!$this->status()) {
			return false;
		}
		
		if(isset($this->_empties[$id])) {
			return 'empty';
		}

		//Setup the name of the cache
		$name = $this->_makeName($id);

		//Try the temp value that is set if exists was called
		if (isset($this->_temp[$name])) {
			$contents = $this->_temp[$name];
			//Unset temp value
			unset($this->_temp[$name]);
		} else {
			//Try to fetch the data from cache
			$contents = apc_fetch($name);
		}

		//Make sure the cache file exists
		if ($contents !== false && !empty($contents)) {
			return $contents;
		}

		return false;
	}

	/**
	 * Calculates the cache usage
	 *
	 * @return string
	 */
	public function usage() {
		if ($this->status()) {
			$mem = @apc_sma_info();
			if ($mem) {
				$mem_size = $mem['num_seg'] * $mem['seg_size'];
				$mem_avail = $mem['avail_mem'];
				$mem_used = $mem_size - $mem_avail;
				return sprintf('Max: %s - Used: %s (%.2f%%) - Available: %s (%.2f%%)', formatBytes($mem_size, true), formatBytes($mem_used, true), ($mem_used * (100 / $mem_size)), formatBytes($mem_avail, true), ($mem_avail * (100 / $mem_size)));
			} else {
				return 'APC Statistics are unavailable.';
			}
		} else {
			return 'Cache is disabled';
		}
	}

	/**
	 * Write cache data
	 *
	 * @param mixed $data Data to write to the file
	 * @param string $id Cache identifier
	 * @param int $expire Force a time to live
	 * @return bool
	 */
	public function write($data, $id, $expire = '') {
		if (!$this->status()) {
			return true;
		}

		if($this->_empties_id!==$id && empty($data)) {
			if(!isset($this->_empties[$id])) {
				$this->_empties[$id] = true;
				$this->_emptied_added = true;
			}
			return false;
		}

		$name = $this->_makeName($id);

		//Write to file
		apc_store($name, $data, (!empty($expire) && is_numeric($expire)) ? $expire : $this->_expire);

		return true;
	}
}