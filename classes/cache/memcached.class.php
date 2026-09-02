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

if (!defined('CC_INI_SET')) {
    die('Access Denied');
}

require CC_ROOT_DIR.'/classes/cache/cache.class.php';

/**
 * Cache specific class
 */
class Cache extends Cache_Controler
{

    private $_memcache_servers = array('127.0.0.1',11211);
    private $_memcached;

    ##############################################

    /**
     * Get the Memcached connection instance
     *
     * @return Memcached
     */
    public function getConnection()
    {
        return $this->_memcached;
    }

    final protected function __construct()
    {
        global $glob;

        $this->_mode = 'Memcached';
        $this->_memcached = new memcached;
    
        $this->_memcache_servers = isset($glob['memcached_servers']) ? array($glob['memcached_servers']) : array($this->_memcache_servers);

        $this->_memcached->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
        if (!count($this->_memcached->getServerList())) {
            $this->_memcached->addServers($this->_memcache_servers);
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
        $this->_memcached->flush();
        $this->_clearFileCache();
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
        $id = shortHash($id, 8, array($this->_empties_id));
        return $this->_memcached->delete($this->_makeName($id));
    }

    /**
     * Check to see if the cache file exists
     *
     * @param string $id Cache identifier
     * @return bool
     */
    public function exists($id)
    {
        if (!$this->status && !$this->statusException($id)) {
            return false;
        }
        $id = shortHash($id, 8, array($this->_empties_id));
        
        return (bool)$this->_memcached->get($this->_makeName($id));
    }
    
    /**
     * Get the cached data
     *
     * @param string $id Cache identifier
     * @return data/false
     */
    public function read($id)
    {
        if (!$this->status && !$this->statusException($id)) {
            return false;
        }

        $id = shortHash($id, 8, array($this->_empties_id));
        
        if ($this->_empties_id!==$id && isset($this->_empties[$id])) {
            return array('empty' => true, 'data' => $this->_empties[$id]);
        }

        if ($this->_empties_id!==$id && isset($this->_dupes[$id])) {
            return $this->_dupes[$id];
        }

        //Setup the name of the cache
        $name = $this->_makeName($id);

        // No check here: memcached enforces its own item limit server-side (-I).
        //Make sure the cache file exists
        if ($contents = $this->_memcached->get($name)) {
            if (!empty($contents)) {
                $this->_dupes[$id] = $contents;
                return $this->_dupes[$id];
            }
        }

        return false;
    }

    /**
     * Calculates the cache usage
     *
     * @return string
     */
    public function usage()
    {
        $stats = $this->_memcached->getStats();
        if (!is_array($stats)) {
            return "No stats available for memcached.";
        }

        $output = '';
        foreach ($stats as $server => $data) {
            $uptime = (int)$data['uptime'];
            if ($uptime >= 86400) {
                $uptime_str = floor($uptime / 86400).'d '.gmdate('H:i:s', $uptime % 86400);
            } else {
                $uptime_str = gmdate('H:i:s', $uptime);
            }

            $hits = (int)$data['get_hits'];
            $misses = (int)$data['get_misses'];
            $total_requests = $hits + $misses;
            $hit_rate = $total_requests > 0 ? round($hits / $total_requests * 100, 1) : 0;

            $used = (float)$data['bytes'] / (1024 * 1024);
            $limit = (float)$data['limit_maxbytes'] / (1024 * 1024);

            $output .= "<table border='1' style='border-collapse: collapse;'>";
            $output .= "<thead><tr><th colspan='2'>Memcached Server: ".$server." (v".$data['version'].")</th></tr></thead>";
            $output .= "<tbody>";
            $output .= "<tr><td>Uptime</td><td>".$uptime_str."</td></tr>";
            $output .= "<tr><td>Connected clients</td><td>".$data['curr_connections']."</td></tr>";
            $output .= "<tr><td>Memory used</td><td>".round($used, 2)." MiB</td></tr>";
            $output .= "<tr><td>Memory limit</td><td>".round($limit, 0)." MiB</td></tr>";
            $output .= "<tr><td>Total items stored</td><td>".number_format((float)$data['total_items'])."</td></tr>";
            $output .= "<tr><td>Current items</td><td>".number_format((float)$data['curr_items'])."</td></tr>";
            $output .= "<tr><td>Cache hits</td><td>".$hits." (".$hit_rate."%)</td></tr>";
            $output .= "<tr><td>Cache misses</td><td>".$misses." (".(100 - $hit_rate)."%)</td></tr>";
            $output .= "<tr><td>Evicted keys</td><td>".$data['evictions']."</td></tr>";
            $output .= "</tbody></table>";
        }

        return $output;
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
        if (!$this->status && !$this->statusException($id)) {
            return false;
        }

        $id = shortHash($id, 8, array($this->_empties_id));
        
        if ($this->_empties_id!==$id && empty($data)) {
            if (!isset($this->_empties[$id])) {
                $this->_empties[$id] = $data;
                $this->_empties_added = true;
            }
            return false;
        }

        $name = $this->_makeName($id);

        //Write to file
        if ($this->_memcached->set($name, $data, (!empty($expire) && is_numeric($expire)) ? $expire : $this->_expire)) {
            return true;
        }
        trigger_error('Cache data not written (Memcached).', E_USER_WARNING);

        return false;
    }

    //=====[ Private ]=======================================

    /**
     * Get empty cache queries
     */
    protected function _getEmpties()
    {
        $this->_setPrefix();
        $this->_empties = ($this->read($this->_empties_id))?:array();
    }
}
