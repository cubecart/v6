<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2025. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

if (!defined('CC_INI_SET')) {
    die('Access Denied');
}

/**
 * Smarty CacheResource implementation for Memcached
 *
 * Extends Smarty's KeyValueStore base which handles cache group
 * invalidation via timestamps. Only read/write/delete need implementing.
 *
 * @author Al Brookbanks
 * @since 6.5.0
 */
class Smarty_CacheResource_Memcached extends Smarty_CacheResource_KeyValueStore
{
    protected $memcached;
    protected $prefix = 'smarty_cc_';

    public function __construct(Memcached $memcached)
    {
        $this->memcached = $memcached;
    }

    /**
     * Sanitise key for memcached (250 byte limit)
     */
    private function _key($key)
    {
        if (strlen($key) > 200) {
            return $this->prefix . md5($key);
        }
        return $this->prefix . str_replace(' ', '_', $key);
    }

    /**
     * Read values for a set of keys from cache
     *
     * @param array $keys list of keys to fetch
     * @return array list of values with the given keys used as indexes
     */
    protected function read(array $keys)
    {
        $mapped = array();
        foreach ($keys as $key) {
            $mapped[$this->_key($key)] = $key;
        }
        $lookup = $this->memcached->getMulti(array_keys($mapped)) ?: array();
        $result = array();
        foreach ($mapped as $mcKey => $origKey) {
            $result[$origKey] = isset($lookup[$mcKey]) ? $lookup[$mcKey] : null;
        }
        return $result;
    }

    /**
     * Save values for a set of keys to cache
     *
     * @param array $keys   list of values to save
     * @param int   $expire expiration time
     * @return boolean true on success, false on failure
     */
    protected function write(array $keys, $expire = null)
    {
        foreach ($keys as $key => $value) {
            $this->memcached->set($this->_key($key), $value, $expire ?: 0);
        }
        return true;
    }

    /**
     * Remove values from cache
     *
     * @param array $keys list of keys to delete
     * @return boolean true on success, false on failure
     */
    protected function delete(array $keys)
    {
        $mcKeys = array();
        foreach ($keys as $key) {
            $mcKeys[] = $this->_key($key);
        }
        $this->memcached->deleteMulti($mcKeys);
        return true;
    }

    /**
     * Do not purge - memcached is shared with CubeCart's own cache.
     * Returning false tells the KeyValueStore base to use timestamp
     * invalidation instead, which is safe.
     *
     * @return boolean false
     */
    protected function purge()
    {
        return false;
    }
}
