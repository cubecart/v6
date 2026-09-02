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

    private $_redis;

    ##############################################

    /**
     * Get the Redis connection instance
     *
     * @return Redis
     */
    public function getConnection()
    {
        return $this->_redis;
    }

    final protected function __construct()
    {
        global $glob;

        $host = isset($glob['redis_host']) ? $glob['redis_host'] : '127.0.0.1';
        $port = isset($glob['redis_port']) ? (int)$glob['redis_port'] : 6379;
        $timeout = isset($glob['redis_timeout']) ? (float)$glob['redis_timeout'] : 2.0;

        try {
            $this->_redis = new Redis();
            $this->_redis->connect($host, $port, $timeout);

            if (isset($glob['redis_password']) && !empty($glob['redis_password'])) {
                $this->_redis->auth($glob['redis_password']);
            }

            if (isset($glob['redis_db']) && is_numeric($glob['redis_db'])) {
                $this->_redis->select((int)$glob['redis_db']);
            }
        } catch (RedisException $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return;
        }

        $this->_mode = 'Redis';

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
     * Clear all the cache or particular cache types
     *
     * @param string $type Cache type prefix
     * @return bool
     */
    public function clear($type = '')
    {
        //Get the current cache IDs
        $this->getIDs();

        $return = true;
        if (!empty($this->_ids)) {
            //Loop through each id to delete it directly (bypass delete() which would double-hash)
            foreach ($this->_ids as $id) {
                if (!(bool)$this->_redis->del($this->_makeName($id))) {
                    $return = false;
                }
            }
            //Reset so subsequent getIDs() fetches fresh from Redis
            $this->_ids = array();
            $this->_dupes = array();
        }
        $this->_clearFileCache();
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
        $id = shortHash($id, 8, array($this->_empties_id));
        return (bool)$this->_redis->del($this->_makeName($id));
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
        return (bool)$this->_redis->exists($this->_makeName($id));
    }

    /**
     * Get all the cache ids
     *
     * @return array
     */
    public function getIDs()
    {
        if (empty($this->_ids)) {
            $info = $this->_redis->keys('*');
            $len = strlen($this->_prefix);
            if (!empty($info) && is_array($info)) {
                foreach ($info as $item) {
                    if(substr($item, 0, $len) === $this->_prefix) {
                        $this->_ids[] = str_replace(array($this->_prefix, $this->_suffix), '', $item);
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

        //Make sure the cache file exists
        if ($contents = $this->_redis->get($name)) {
            if (!empty($contents)) {
                if ($this->_oversized($contents)) {
                    return false;
                }
                $this->_dupes[$id] = json_decode($contents, true);
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
        $info = $this->_redis->info();

        $uptime = (int)$info['uptime_in_seconds'];
        if ($uptime >= 86400) {
            $uptime_str = floor($uptime / 86400).'d '.gmdate('H:i:s', $uptime % 86400);
        } else {
            $uptime_str = gmdate('H:i:s', $uptime);
        }

        $hits = (int)$info['keyspace_hits'];
        $misses = (int)$info['keyspace_misses'];
        $total_requests = $hits + $misses;
        $hit_rate = $total_requests > 0 ? round($hits / $total_requests * 100, 1) : 0;

        $output = "<table border='1' style='border-collapse: collapse;'>";
        $output .= "<thead><tr><th colspan='2'>Redis Server: ".$info['redis_version']." (".$info['redis_mode'].")</th></tr></thead>";
        $output .= "<tbody>";
        $output .= "<tr><td>Uptime</td><td>".$uptime_str."</td></tr>";
        $output .= "<tr><td>Connected clients</td><td>".$info['connected_clients']."</td></tr>";
        $output .= "<tr><td>Memory used</td><td>".$info['used_memory_human']."</td></tr>";
        $output .= "<tr><td>Memory peak</td><td>".$info['used_memory_peak_human']."</td></tr>";
        $output .= "<tr><td>Total keys</td><td>".($this->_redis->dbSize())."</td></tr>";
        $output .= "<tr><td>Cache hits</td><td>".$hits." (".$hit_rate."%)</td></tr>";
        $output .= "<tr><td>Cache misses</td><td>".$misses." (".(100 - $hit_rate)."%)</td></tr>";
        $output .= "<tr><td>Total commands processed</td><td>".number_format((float)$info['total_commands_processed'])."</td></tr>";
        $output .= "<tr><td>Evicted keys</td><td>".$info['evicted_keys']."</td></tr>";
        $output .= "<tr><td>Expired keys</td><td>".$info['expired_keys']."</td></tr>";
        $output .= "</tbody></table>";

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

        if (is_array($data) || is_string($data)) {
            $data = json_encode($data);
        } else {
            return false;
        }

        if ($this->_oversized($data)) {
            return false;
        }

        $ttl = (!empty($expire) && is_numeric($expire)) ? $expire : $this->_expire;
        if ($this->_redis->setex($name, $ttl, $data)) {
            return true;
        }
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
