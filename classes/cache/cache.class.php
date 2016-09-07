<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2015. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

/**
 * Cache controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Cache_Controler {
	/**
	 * Public status
	 *
	 * @var @string
	 */
	public $status_desc = '';
	/**
	 * Public status
	 *
	 * @var @bool
	 */
	public $status = false;
	/**
	 * Make sure the cache doesn't get cleared more than once
	 *
	 * @var bool
	 */
	protected $_cleared = false;
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
	protected $_empties_id = 'sql.empties';
	protected $_empties = array();
	protected $_empties_added = false;
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

	##############################################

	protected function __construct() {
		$this->_setPrefix();
	}

	//=====[ Public ]=======================================
	
	protected function _setPrefix() {
		$this->_prefix = substr(md5($GLOBALS['glob']['dbdatabase']), 0, 5).'.';
	}

	/**
	 * Enable/Disable cache
	 *
	 * @param bool $enable
	 */
	public function enable($enable = true) {
		$this->status = $enable;
		$this->status();
		if($enable) $this->_getEmpties();
	}

	/**
	 * Get the current cache type
	 *
	 * @return string Cache system
	 */
	public final function getCacheSystem() {
		return $this->_mode;
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
	 * Return cache status
	 *
	 * @return bool
	 */
	public function status() {
		if(defined('ADMIN_CP') && ADMIN_CP) {
			$this->status_desc = 'Always Disabled in ACP';
			$this->status = false;
		} else { 
			$this->status_desc = $this->status ? 'Enabled' : 'Disabled';
		}
		return $this->status;
	}

	/**
	 * Tidy the cache folder
	 *
	 * @return bool
	 */
	public function tidy() {
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

	//=====[ Private ]=======================================

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