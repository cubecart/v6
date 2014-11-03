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
 * @author Sir William
 * @version 1.1.0
 * @since 5.0.0
 */
class Cache extends Cache_Controler {
	/**
	 * Path to cache files
	 *
	 * @var string
	 */
	protected $_cache_path = '';
	protected $_page_cache_usage = 0;
	protected $_file_data_split = "\n##### DATA #####\n";

	final protected function __construct() {
		if (!$this->setPath()) {
			$this->_online = false;
			return ;
		}

		$this->_mode = 'File';
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
	 * Clear all the cache
	 *
	 * @param string $type Cache type prefix
	 *
	 * @return bool
	 */
	public function clear($type = '') {
		if (!empty($type)) {
			$prefix = '*'.strtolower($type).'*';
		} else {
			$prefix = '*';
		}

		//Loop through each cache file
		$files = glob($this->_cache_path.$this->_prefix.$prefix.$this->_suffix, GLOB_NOSORT);
		if (is_array($files)) {
			foreach ($files as $file) {
				unlink($file);
			}
		}
		clearstatcache();
		return true;
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
		clearstatcache(); // Clear cached results

		if (file_exists($this->_cache_path.$this->_makeName($id))) {
			return unlink($this->_cache_path.$this->_makeName($id));
		}

		return true;
	}

	/**
	 * Check to see if the cache file exists
	 *
	 * @param string $id Cache identifier
	 *
	 * @return bool
	 */
	public function exists($id) {
		if (!$this->status()) {
			return false;
		}

		clearstatcache(); // Clear cached results

		return file_exists($this->_cache_path.$this->_makeName($id));
	}

	/**
	 * Get all the cache ids
	 *
	 * @return array
	 */
	public function getIDs() {
		if (empty($this->_ids)) {
			foreach (glob($this->_cache_path.'*'.$this->_suffix, GLOB_NOSORT) as $file) {
				if (strpos($file, $this->_prefix) !== false) {
					$this->_ids[] = str_replace(array($this->_prefix, $this->_suffix, CC_CACHE_DIR), '', $file);
				}
			}
		}

		return $this->_ids;
	}

	private function _getEmpties() {
		$this->_setPrefix();
		$this->_empties = $this->read($this->_empties_id);
	}

	public function deepslashes(&$array) {  
	  if(is_array($array)) {  
	    foreach ($array as &$val)  
	      is_array($val) ? self::deepslashes($val):$val=addslashes($val);  
	      unset($val);  
	  } else { 
	    $array=addslashes($array); 
	  } 
	} 

	/**
	 * Get the cache data
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
		
		if(isset($this->_dupes[$id])) {
			return $this->_dupes[$id];
		} else {
			$name = $this->_makeName($id);
			$file = $this->_cache_path.$name;
	
			clearstatcache(); // Clear cached results
	
			//Make sure the cache file exists
			if (file_exists($file)) {
				
				$contents = @file_get_contents($file, false);
				$this->_page_cache_usage += filesize($file);
				//If there is no newline then the file isn't valid
				if (strpos($contents, $this->_file_data_split) === false) {
					@unlink($file);
					return false;
				}
	
				//Split meta and data
				list($meta, $data) = explode($this->_file_data_split, $contents);
				$meta = unserialize($meta);

				//Check to see if the cache is past the experation date
				if (($meta['time'] + $meta['expire']) <= time()) {
					
					unlink($file);
					return false;
				}
				$this->_dupes[$id] = unserialize($data);
				return $this->_dupes[$id];
			}
		}
		
		return false;
	}


	/**
	 * Write cache data
	 *
	 * @param mixed $data Data to write to the file
	 * @param string $id Cache identifier
	 * @param int $expire Force a time to live
	 * return bool
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
		
		try {
			$data = serialize($this->deepslashes($data));
		} catch (Exception $e) {
		    trigger_error($e->getMessage());
		    return false;
		}

		$name = $this->_makeName($id);
		
		//Create the meta data for the file
		$meta = array(
			'time'  => time(),
			'expire' => (!empty($expire) && is_numeric($expire)) ? $expire : $this->_expire,
		);
		//Combine the meta and the data
		$data  = serialize($this->deepslashes($meta)).$this->_file_data_split.$data;

		//Write to file
		if (file_put_contents($this->_cache_path.$name, $data)) {
			return true;
		}
		trigger_error('Cache data not written.', E_USER_WARNING);
		return false;
	}
	
	
	/**
	 * Set cache path to some where else
	 *
	 * @param string $path
	 */
	public function setPath($path = '') {
		if (empty($path)) {
			$path = CC_ROOT_DIR.'/cache'.'/';
		} else {
			$ds = substr($path, -1);
			if ($ds != '/' && $ds != '\\') {
				$path .= '/';
			}
		}

		clearstatcache(); // Clear cached results

		if (is_dir($path) && file_exists($path) && is_writable($path)) {
			$this->_cache_path = $path;
		} else {
			trigger_error('Could not change cache path ('.$path.')', E_USER_WARNING);
			return false;
		}

		return true;
	}

	/**
	 * Calculates the cache usage
	 *
	 * @return string
	 */
	public function usage() {
		if ($this->status()) {
			$cache_size = 0;
			foreach (glob($this->_cache_path.'*', GLOB_NOSORT) as $file) {
				$cache_size += filesize($file);
			}
			
			return 'Cache Used: '.formatBytes($this->_page_cache_usage, true).' of '.formatBytes($cache_size, true).' ('.number_format((($this->_page_cache_usage/$cache_size) * 100),2).'%)';
		} else {
			return 'Cache is disabled';
		}
	}
}