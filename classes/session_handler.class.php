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

/**
 * Database-backed session handler
 *
 * Stores PHP session data in the CubeCart_sessions table so that
 * sessions are not lost when an in-memory cache (memcached/redis)
 * evicts entries under memory pressure.
 */
class Session_Handler implements SessionHandlerInterface
{
    private $_db;
    private $_ttl;

    public function __construct($ttl = 86400)
    {
        $this->_db = Database::getInstance();
        $this->_ttl = $ttl;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $session_id): string|false
    {
        $result = $this->_db->select('CubeCart_sessions', array('session_data'), array('session_id' => $session_id), false, 1, false, false);
        if ($result && isset($result[0]['session_data'])) {
            return $result[0]['session_data'];
        }
        return '';
    }

    public function write(string $session_id, string $data): bool
    {
        $this->_db->update('CubeCart_sessions', array('session_data' => $data, 'session_last' => time()), array('session_id' => $session_id), false);
        return true;
    }

    public function destroy(string $session_id): bool
    {
        $this->_db->delete('CubeCart_sessions', array('session_id' => $session_id));
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        $this->_db->delete('CubeCart_sessions', array('session_last' => '<=' . (time() - $max_lifetime)), 500);
        return 0;
    }

    /**
     * Register this handler with PHP
     */
    public function register()
    {
        session_set_save_handler($this, true);
    }
}
