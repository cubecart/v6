<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

if (!defined('CC_INI_SET')) {
    die('Access Denied');
}

require CC_ROOT_DIR.'/classes/cache/cache.class.php';

/**
 * Cache specific class
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Cache extends Cache_Controler
{

    ##############################################

    final protected function __construct()
    {
        $this->_mode = 'XCache';
        
        //Run the parent constructor
        parent::__construct();
    }
    
    public function __destruct()
    {
        if ($this->_empties_added) {
            $this->write($this->_empties, $this->_empties_id);
        }
    }

    /**
     * Setup the instance (singleton)
     *
     * @return instance
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    //=====[ Public ]=======================================

    /**
     * Clear the cache
     *
     * @param string $type Cache type prefix
     * @return bool
     */
    public function clear($type = '')
    {
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
        }
        $this->_clearFileCache($prefix);
        return $return;
    }

    /**
     * Remove a single item of cache
     *
     * @param string $id Cache identifier
     * @return bool
     */
    public function delete($id)
    {
        return xcache_unset($this->_makeName($id));
    }

    /**
     * Check to see if the cache file exists
     *
     * @param string $id Cache identifier
     * @return bool
     */
    public function exists($id)
    {
        if (!$this->status) {
            return false;
        }
        return xcache_isset($this->_makeName($id));
    }

    /**
     * Get all the cache ids
     *
     * @return array
     */
    public function getIDs()
    {
        if (empty($this->_ids)) {
            for ($i = 0, $count = xcache_count(XC_TYPE_VAR); $i < $count; ++$i) {
                $entries = xcache_list(XC_TYPE_VAR, $i);

                if (is_array($entries['cache_list'])) {
                    foreach ($entries['cache_list'] as $entry) {
                        $this->_ids[] = str_replace(array($this->_prefix, $this->_suffix), '', $entry['name']);
                    }
                }
            }
        }

        return $this->_ids;
    }
    
    /**
     * Get the cached data
     *
     * @param string $id Cache identifier
     * @return data/false
     */
    public function read($id)
    {
        if (!$this->status) {
            return false;
        }
        
        if (preg_match('/^sql\./', $id) && $this->_empties_id!==$id && isset($this->_empties[$id])) {
            return array('empty' => true, 'data' => $this->_empties[$id]);
        }

        //Setup the name of the cache
        $name = $this->_makeName($id);

        //Make sure the cache file exists
        if (xcache_isset($name)) {
            $contents = xcache_get($name);

            if (!empty($contents)) {
                //Remove base64 & serialization
                return $contents;
            }
        }

        return false;
    }

    /**
     * Get session save handler
     *
     * @return string
     */
    public function session_save_handler() {
        return 'files'; // for now
    }
    /**
     * Get session save path
     *
     * @return string
     */
    public function session_save_path() {
        return '';
    }

    /**
     * Calculates the cache usage
     *
     * @return string
     */
    public function usage()
    {
        return 'XCache Statistics are unavailable.';
    }

    /**
     * Write cache data
     *
     * @param mixed $data Data to write to the file
     * @param string $id Cache identifier
     * @param int $expire Force a time to live
     * @return bool
     */
    public function write($data, $id, $expire = '')
    {
        if (!$this->status) {
            return false;
        }
        
        if (preg_match('/^sql\./', $id) && $this->_empties_id!==$id && empty($data)) {
            if (!isset($this->_empties[$id])) {
                $this->_empties[$id] = $data;
                $this->_empties_added = true;
            }
            return false;
        }

        $name = $this->_makeName($id);

        //Write to file
        if (xcache_set($name, $data, (!empty($expire) && is_numeric($expire)) ? $expire : $this->_expire)) {
            return true;
        }
        trigger_error('Cache data not written (XCache).', E_USER_WARNING);

        return false;
    }

    //=====[ Private ]=======================================

    /**
     * Get empty cache queries
     */
    protected function _getEmpties()
    {
        $this->_setPrefix();
        $this->_empties = $this->read($this->_empties_id);
    }
}
