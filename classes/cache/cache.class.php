<?php
/**
 * Header
 */

/**
 * Cache controller
 *
 * @author Technocrat
 * @version 1.1.0
 * @since 5.0.0
 */
class Cache_Controler {
	/**
	 * Make sure the cache doesn't get cleared more than once
	 *
	 * @var bool
	 */
	protected $_cleared = false;
	/**
	 * Cache enabled
	 *
	 * @var bool
	 */
	protected $_enabled  = true;
	/**
	 * Cache expire
	 *
	 * @var int
	 */
	protected $_expire  = 86400;
	/**
	 * Cache IDs
	 *
	 * @var array
	 */
	protected $_ids   = array();
	/**
	 * Cache mode/type
	 *
	 * @var string
	 */
	protected $_mode  = 'None';
	/**
	 * Is the cache system itself online (did it load)
	 *
	 * @var bool
	 */
	protected $_online  = false;
	/**
	 * Cache prefix
	 *
	 * @var string
	 */
	protected $_prefix  = '';
	/**
	 * File name suffix
	 *
	 * @var string
	 */
	protected $_suffix  = '.cache';
	protected $_empties_id = 'empties';
	protected $_empties = array();
	protected $_emptied_added = false;
	/**
	 * Temp variable to hold the cache read for exists function
	 *
	 * @var mixed
	 */
	protected $_temp  = null;

	/**
	 * Class instance
	 *
	 * @var instance
	 */
	protected static $_instance;
	
	protected $_dupes = array();

	protected function __construct() {
		// Using the db name as a fixed identifier np's
		$this->_setPrefix();
	}
	
	protected function _setPrefix() {
		$this->_prefix = substr(md5($GLOBALS['glob']['dbdatabase']), 0, 5).'.';
	}

	/**
	 * Enable/Disable cache
	 *
	 * @param bool $enable
	 */
	public function enable($enable = true) {
		$this->_enabled = (bool)($enable);
	}

	/**
	 * Get the current cache type
	 *
	 * @return string Cache system
	 */
	public final function getCacheSystem() {
		if ($this->status()) {
			return $this->_mode;
		}

		return 'None';
	}
	/**
	 * Setup the cache system after the configs are loaded
	 */
	public function setup() {
		$this->enable(true);
	}
	/**
	 * Get the cache status
	 *
	 * @return bool
	 */
	public function status() {
		return $this->_online && $this->_enabled;
	}

	/**
	 * Set cache expire time
	 *
	 * @param int $expire One day
	 */
	public function setExpire($expire = 86400) {
		if (is_numeric($expire)) {
			$this->_expire = $expire;
		}
	}

	/**
	 * Tidy the cache folder
	 *
	 * @return bool
	 */
	public function tidy() {
		trigger_error('Cleaning cached files...', E_USER_NOTICE);

		//Loop through the cache folder
		if (($files = glob(CC_CACHE_DIR.'*', GLOB_NOSORT)) !== false) {
			foreach ($files as $file) {
				//Delete any file that is not a cache file
				if (substr($file, -6) !== '.cache' && $file !== '.htaccess' && $file !== 'index.php') {
					@unlink($file);
				}
			}
		}

		//Loop through the cache/skin folder
		if (($files = glob(CC_SKIN_CACHE_DIR.'*', GLOB_NOSORT)) !== false) {
			/**
			 * Delete any files
			 *
			 * We are doing it this way because smarty class may not be loaded
			 * so this will be quicker and safer
			 */
			foreach ($files as $file) {
				@unlink($file);
			}
		}
		clearstatcache();
		return true;
	}

	/**
	 * Make the cache name key
	 *
	 * @param string $id
	 * @return string
	 */
	protected function _makeName($id) {
		return $this->_prefix.$id.$this->_suffix;
	}
}