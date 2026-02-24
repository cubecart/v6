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
 * Smarty CacheResource implementation for Redis (Predis)
 *
 * @author Al Brookbanks
 * @since 6.5.0
 */
class Smarty_CacheResource_Redis extends Smarty_CacheResource_KeyValueStore
{
    protected $redis;
    protected $prefix = 'smarty_cc_';

    public function __construct($redis)
    {
        $this->redis = $redis;
    }

    private function _key($key)
    {
        if (strlen($key) > 200) {
            return $this->prefix . md5($key);
        }
        return $this->prefix . str_replace(' ', '_', $key);
    }

    protected function read(array $keys)
    {
        $mcKeys = array();
        foreach ($keys as $key) {
            $mcKeys[] = $this->_key($key);
        }
        $lookup = $this->redis->mget($mcKeys);
        $result = array();
        foreach ($keys as $i => $key) {
            $result[$key] = (isset($lookup[$i]) && $lookup[$i] !== null) ? $lookup[$i] : null;
        }
        return $result;
    }

    protected function write(array $keys, $expire = null)
    {
        foreach ($keys as $key => $value) {
            $k = $this->_key($key);
            if ($expire) {
                $this->redis->setex($k, $expire, $value);
            } else {
                $this->redis->set($k, $value);
            }
        }
        return true;
    }

    protected function delete(array $keys)
    {
        $mcKeys = array();
        foreach ($keys as $key) {
            $mcKeys[] = $this->_key($key);
        }
        $this->redis->del($mcKeys);
        return true;
    }

    protected function purge()
    {
        return false;
    }
}
