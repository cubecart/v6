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

/**
 * API Authentication - Bearer token verification and permission checking
 */
class ApiAuth
{
    private $_db;
    private $_keyData   = null;
    private $_adminData = null;
    private $_permissions = array();
    private $_ownerPermissions = null;

    /**
     * Admin section behind each API resource, matching the checks on the admin screens for
     * the same data. Keys are created and checked against these, so a key never reaches
     * further than its owner could in the admin.
     */
    public static $resourceSections = array(
        'products'   => 'products',
        'categories' => 'categories',
        'orders'     => 'orders',
        'customers'  => 'customers',
        'coupons'    => 'settings',
        'tax'        => 'settings',
        'settings'   => 'settings',
        'reviews'    => 'reviews',
        'files'      => 'filemanager',
    );

    public function __construct()
    {
        $this->_db = Database::getInstance();
    }

    /**
     * Authenticate the request. Returns true or sends error response.
     */
    public function authenticate()
    {
        $header = $this->_getAuthHeader();
        if (empty($header)) {
            ApiResponse::error('Missing Authorization header. Use: Authorization: Bearer {api_key}:{api_secret}', 'AUTH_REQUIRED', 401);
        }

        // Parse Bearer token
        if (stripos($header, 'Bearer ') !== 0) {
            ApiResponse::error('Invalid Authorization format. Use: Bearer {api_key}:{api_secret}', 'AUTH_INVALID', 401);
        }

        $token = substr($header, 7);
        $parts = explode(':', $token, 2);
        if (count($parts) !== 2 || empty($parts[0]) || empty($parts[1])) {
            ApiResponse::error('Invalid token format. Use: {api_key}:{api_secret}', 'AUTH_INVALID', 401);
        }

        $apiKey    = $parts[0];
        $apiSecret = $parts[1];

        // Look up key
        $keyData = $this->_db->select('CubeCart_api_keys', false, array('api_key' => $apiKey, 'status' => 1));
        if (!$keyData || empty($keyData[0])) {
            ApiResponse::error('Invalid API key', 'AUTH_INVALID', 401);
        }
        $this->_keyData = $keyData[0];

        // Check expiry
        if ($this->_keyData['expires'] > 0 && $this->_keyData['expires'] < time()) {
            ApiResponse::error('API key has expired', 'AUTH_EXPIRED', 401);
        }

        // Check IP whitelist
        if (!empty($this->_keyData['ip_whitelist'])) {
            $allowed = json_decode($this->_keyData['ip_whitelist'], true);
            if (is_array($allowed) && !empty($allowed)) {
                $clientIp = $this->getClientIp();
                if (!in_array($clientIp, $allowed)) {
                    ApiResponse::error('IP address not whitelisted', 'AUTH_IP_DENIED', 403);
                }
            }
        }

        // Verify secret
        if (!password_verify($apiSecret, $this->_keyData['api_secret_hash'])) {
            ApiResponse::error('Invalid API secret', 'AUTH_INVALID', 401);
        }

        // Load permissions
        $this->_permissions = json_decode($this->_keyData['permissions'], true);
        if (!is_array($this->_permissions)) {
            $this->_permissions = array();
        }

        // Load admin user data to use as permission ceiling
        $adminData = $this->_db->select('CubeCart_admin_users', false, array('admin_id' => $this->_keyData['admin_id'], 'status' => 1), false, false, false, false);
        if (!$adminData || empty($adminData[0])) {
            ApiResponse::error('API key owner account is disabled', 'AUTH_ADMIN_DISABLED', 403);
        }
        $this->_adminData = $adminData[0];

        // Update last_used timestamp
        $this->_db->update('CubeCart_api_keys', array('last_used' => time()), array('key_id' => $this->_keyData['key_id']));

        return true;
    }

    /**
     * Check if the current API key has permission for a resource at the given level
     * @param string $resource Resource name (e.g. 'products', 'orders')
     * @param string $level Required level: 'r', 'w', or 'd'
     */
    public function checkPermission($resource, $level = 'r')
    {
        // Check API key scope
        $perm = isset($this->_permissions[$resource]) ? $this->_permissions[$resource] : '';
        if (empty($perm) || $perm === 'none') {
            ApiResponse::error('API key does not have access to this resource', 'FORBIDDEN', 403);
        }

        // Map level to what's required
        if ($level === 'w' && strpos($perm, 'w') === false) {
            ApiResponse::error('API key does not have write access to this resource', 'FORBIDDEN', 403);
        }
        if ($level === 'd' && strpos($perm, 'd') === false) {
            ApiResponse::error('API key does not have delete access to this resource', 'FORBIDDEN', 403);
        }

        // The key is also capped at what its owner holds now, which covers keys created with
        // more than their creator had and owners who have since lost a section
        if (!$this->_ownerAllows($resource, $level)) {
            ApiResponse::error('API key owner does not have access to this resource', 'FORBIDDEN', 403);
        }

        return true;
    }

    /**
     * Whether the key's owner holds the admin permission behind a resource at this level
     * @param string $resource
     * @param string $level 'r', 'w' or 'd'
     * @return bool
     */
    private function _ownerAllows($resource, $level)
    {
        if (!empty($this->_adminData['super_user'])) {
            return true;
        }
        if (!isset(self::$resourceSections[$resource])) {
            return false;
        }
        $bits = array('r' => CC_PERM_READ, 'w' => CC_PERM_EDIT, 'd' => CC_PERM_DELETE);
        $bit  = isset($bits[$level]) ? $bits[$level] : CC_PERM_FULL;

        if ($this->_ownerPermissions === null) {
            $this->_ownerPermissions = array();
            $rows = $this->_db->select('CubeCart_permissions', array('section_id', 'level'), array('admin_id' => (int)$this->_adminData['admin_id']), false, false, false, false);
            if ($rows) {
                foreach ($rows as $row) {
                    $this->_ownerPermissions[(int)$row['section_id']] = (int)$row['level'];
                }
            }
        }
        // Same lookup as Admin::permissions(): the section's own row, otherwise the global row (0)
        $sectionId = Admin::SECTION_IDS[self::$resourceSections[$resource]];
        if (isset($this->_ownerPermissions[$sectionId])) {
            $held = $this->_ownerPermissions[$sectionId];
        } else {
            $held = isset($this->_ownerPermissions[0]) ? $this->_ownerPermissions[0] : 0;
        }
        return ($held & $bit) === $bit;
    }

    /**
     * Get the API key ID
     */
    public function getKeyId()
    {
        return $this->_keyData ? (int)$this->_keyData['key_id'] : 0;
    }

    /**
     * Get the rate limit for this key
     */
    public function getRateLimit()
    {
        return $this->_keyData ? (int)$this->_keyData['rate_limit'] : 60;
    }

    /**
     * Get the admin ID associated with this key
     */
    public function getAdminId()
    {
        return $this->_keyData ? (int)$this->_keyData['admin_id'] : 0;
    }

    /**
     * Get client IP address
     */
    public function getClientIp()
    {
        // The store's own rules: forwarding headers only count when they come from a trusted proxy
        return get_ip_address();
    }

    /**
     * Extract the Authorization header
     */
    private function _getAuthHeader()
    {
        // Apache
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }
        // Nginx or CGI
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        // Apache mod_rewrite
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            foreach ($headers as $key => $value) {
                if (strtolower($key) === 'authorization') {
                    return $value;
                }
            }
        }
        return null;
    }

    /**
     * Generate a new API key pair
     * @return array ['api_key' => '...', 'api_secret' => '...', 'api_secret_hash' => '...']
     */
    public static function generateKeyPair()
    {
        $apiKey    = 'ccapi_' . bin2hex(random_bytes(24));
        $apiSecret = bin2hex(random_bytes(32));
        $hash      = password_hash($apiSecret, PASSWORD_BCRYPT);
        return array(
            'api_key'         => $apiKey,
            'api_secret'      => $apiSecret,
            'api_secret_hash' => $hash,
        );
    }
}
