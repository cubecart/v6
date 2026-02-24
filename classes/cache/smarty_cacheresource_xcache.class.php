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
 * Smarty CacheResource implementation for XCache
 *
 * @author Al Brookbanks
 * @since 6.5.0
 */
class Smarty_CacheResource_XCache extends Smarty_CacheResource_KeyValueStore
{
    protected $prefix = 'smarty_cc_';

    private function _key($key)
    {
        if (strlen($key) > 200) {
            return $this->prefix . md5($key);
        }
        return $this->prefix . str_replace(' ', '_', $key);
    }

    protected function read(array $keys)
    {
        $result = array();
        foreach ($keys as $key) {
            $k = $this->_key($key);
            $result[$key] = xcache_isset($k) ? xcache_get($k) : null;
        }
        return $result;
    }

    protected function write(array $keys, $expire = null)
    {
        foreach ($keys as $key => $value) {
            xcache_set($this->_key($key), $value, $expire ?: 0);
        }
        return true;
    }

    protected function delete(array $keys)
    {
        foreach ($keys as $key) {
            xcache_unset($this->_key($key));
        }
        return true;
    }

    protected function purge()
    {
        return false;
    }
}
