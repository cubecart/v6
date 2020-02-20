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
 * @author Al Brookbanks
 * @since 6.0.0
 */
class Cache extends Cache_Controler
{
    private $_memcache_host = '127.0.0.1';
    private $_memcache_port = '11211';
    
    ##############################################

    final protected function __construct()
    {
        global $glob;

        $this->_mode = 'Memcache';
        $this->_memcache = new Memcache;
    
        $this->_memcache_host = isset($glob['memcache_host']) ? $glob['memcache_host'] : $this->_memcache_host;
        $this->_memcache_port = isset($glob['memcache_post']) ? $glob['memcache_post'] : $this->_memcache_port;
        if (!$this->_memcache->connect($this->_memcache_host, $this->_memcache_port)) {
            trigger_error("Couldn't initiate Memcache. Please set 'memcache_host' and 'memcache_port' in the includes/global.inc.php file.", E_USER_WARNING);
        }

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
        return $this->_memcache->delete($this->_makeName($id));
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
        
        if (!$this->_memcache->get($this->_makeName($id))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get all the cache ids
     *
     * @return array
     */
    public function getIDs()
    {
        if (empty($this->_ids)) {
            $allSlabs = $this->_memcache->getExtendedStats('slabs');
            foreach ($allSlabs as $server => $slabs) {
                foreach ($slabs as $slabId => $slabMeta) {
                    $cdump = $this->_memcache->getExtendedStats('cachedump', (int)$slabId);
                    if (is_array($cdump)) {
                        foreach ($cdump as $keys => $arrVal) {
                            if (is_array($arrVal)) {
                                foreach ($arrVal as $k => $v) {
                                    $this->_ids[] = str_replace(array($this->_prefix, $this->_suffix), '', $k);
                                }
                            }
                        }
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
        if ($contents = $this->_memcache->get($name)) {
            if (!empty($contents)) {
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
        return 'memcache';
    }
    /**
     * Get session save path
     *
     * @return string
     */
    public function session_save_path() {
        return 'tcp://'.$this->_memcache_host.':'.$this->_memcache_port;
    }

    /**
     * Calculates the cache usage
     *
     * @return string
     */
    public function usage()
    {
        $stats = $this->_memcache->getStats();
        if (is_array($stats)) {
            $output = $this->_printStats($stats);
            return $output;
        } else {
            return "No stats available for memcache.";
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
        if ($this->_memcache->set($name, $data, (!empty($expire) && is_numeric($expire)) ? $expire : $this->_expire)) {
            return true;
        }
        trigger_error('Cache data not written (Memcache).', E_USER_WARNING);

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
    
    /**
     * Return string of stats for output
     */
    private function _printStats($data)
    {
        $output = "";
        $output .= "<table border='1'><tbody>";
        $output .= "<tr><td>Memcache Server version:</td><td> ".$data["version"]."</td></tr>";
        $output .= "<tr><td>Process id of this server process </td><td>".$data["pid"]."</td></tr>";
        $output .= "<tr><td>Number of seconds this server has been running </td><td>".$data["uptime"]."</td></tr>";
        $output .= "<tr><td>Accumulated user time for this process </td><td>".$data["rusage_user"]." seconds</td></tr>";
        $output .= "<tr><td>Accumulated system time for this process </td><td>".$data["rusage_system"]." seconds</td></tr>";
        $output .= "<tr><td>Total number of items stored by this server ever since it started </td><td>".$data["total_items"]."</td></tr>";
        $output .= "<tr><td>Number of open connections </td><td>".$data["curr_connections"]."</td></tr>";
        $output .= "<tr><td>Total number of connections opened since the server started running </td><td>".$data["total_connections"]."</td></tr>";
        $output .= "<tr><td>Number of connection structures allocated by the server </td><td>".$data["connection_structures"]."</td></tr>";
        $output .= "<tr><td>Cumulative number of retrieval requests </td><td>".$data["cmd_get"]."</td></tr>";
        $output .= "<tr><td> Cumulative number of storage requests </td><td>".$data["cmd_set"]."</td></tr>";

        $percCacheHit=((real)$data["get_hits"]/ (real)$data["cmd_get"] *100);
        $percCacheHit=round($percCacheHit, 3);
        $percCacheMiss=100-$percCacheHit;

        $output .= "<tr><td>Number of keys that have been requested and found present </td><td>".$data["get_hits"]." ($percCacheHit%)</td></tr>";
        $output .= "<tr><td>Number of items that have been requested and not found </td><td>".$data["get_misses"]." ($percCacheMiss%)</td></tr>";
        $MBRead= (real)$data["bytes_read"]/(1024*1024);
        $output .= "<tr><td>Total number of bytes read by this server from network </td><td>".$MBRead." Mega Bytes</td></tr>";
        $MBWrite=(real) $data["bytes_written"]/(1024*1024) ;
        $output .= "<tr><td>Total number of bytes sent by this server to network </td><td>".$MBWrite." Mega Bytes</td></tr>";
        $MBSize=(real) $data["limit_maxbytes"]/(1024*1024) ;
        $output .= "<tr><td>Number of bytes this server is allowed to use for storage.</td><td>".$MBSize." Mega Bytes</td></tr>";
        $output .= "<tr><td>Number of valid items removed from cache to free memory for new items.</td><td>".$data["evictions"]."</td></tr>";
        $output .= "</tbody></table>";
        return $output;
    }
}
