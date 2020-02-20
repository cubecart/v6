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
 * @author Sir William
 * @since 5.0.0
 */
class Cache extends Cache_Controler
{
    /**
     * Path to cache files
     *
     * @var string
     */
    protected $_cache_path = '';
    protected $_page_cache_usage = 0;
    protected $_file_data_split = "\n-- CubeCart Cache Split --\n";

    ##############################################

    final protected function __construct()
    {
        if (!$this->setPath()) {
            return;
        }

        $this->_mode = 'File';
        
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
     * Clear all the cache
     *
     * @param string $type Cache type prefix
     *
     * @return bool
     */
    public function clear($type = '')
    {
        if (!empty($type)) {
            $prefix = '*'.strtolower($type).'*';
        } else {
            $prefix = '*';
        }
        //Loop through each cache file to delete
        $files = array();
        $cache_files = glob($this->_cache_path.$this->_prefix.$prefix.$this->_suffix, GLOB_NOSORT);
        if (is_array($cache_files)) {
            $files = array_merge($files, $cache_files);
        }
        $this->_clearFileCache($prefix, $files);
        clearstatcache();
        return true;
    }

    /**
     * Remove a single item of cache
     *
     * @param string $id Cache identifier
     * @return bool
     */
    public function delete($id)
    {
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
    public function exists($id)
    {
        if (!$this->status) {
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
    public function getIDs()
    {
        if (empty($this->_ids)) {
            foreach (glob($this->_cache_path.'*'.$this->_suffix, GLOB_NOSORT) as $file) {
                if (strpos($file, $this->_prefix) !== false) {
                    $this->_ids[] = str_replace(array($this->_prefix, $this->_suffix, CC_CACHE_DIR), '', $file);
                }
            }
        }

        return $this->_ids;
    }

    /**
     * Get the cache data
     *
     * @param string $id Cache identifier
     * @return data/false
     */
    public function read($id, $serialized = true)
    {
        if (!$this->status) {
            return false;
        }
        
        if (preg_match('/^sql\./', $id) && $this->_empties_id!==$id && isset($this->_empties[$id])) {
            return array('empty' => true, 'data' => $this->_empties[$id]);
        }
        
        if ($this->_empties_id!==$id && isset($this->_dupes[$id])) {
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
                $this->_dupes[$id] = ($serialized) ? unserialize($data) : $data;
                return $this->_dupes[$id];
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
        return 'files';
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
     * Write cache data
     *
     * @param mixed $data Data to write to the file
     * @param string $id Cache identifier
     * @param int $expire Force a time to live
     * return bool
     */
    public function write($data, $id, $expire = '', $serialize = true)
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
        
        try {
            $data = ($serialize) ? serialize($data) : $data;
        } catch (Exception $e) {
            trigger_error($e->getMessage());
            return false;
        }

        $name = $this->_makeName($id);
        
        //Create the metadata for the file
        $meta = array(
            'time'  => time(),
            'expire' => (!empty($expire) && is_numeric($expire)) ? $expire : $this->_expire,
        );
        //Combine the meta and the data
        $data  = serialize($meta).$this->_file_data_split.$data;

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
    public function setPath($path = '')
    {
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
    public function usage()
    {
        $cache_size = 0;
        foreach (glob($this->_cache_path.'*', GLOB_NOSORT) as $file) {
            $cache_size += filesize($file);
        }
        return 'Cache Used: '.(
          ($cache_size > 0)
          ? formatBytes($this->_page_cache_usage, true).' of ' .
             formatBytes($cache_size, true) .
             ' ('.number_format((($this->_page_cache_usage/$cache_size) * 100), 2).'%)'
          : '0%'
        );
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
