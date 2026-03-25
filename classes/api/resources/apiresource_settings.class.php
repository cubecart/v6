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
 * Settings API resource controller
 */
class ApiResource_Settings extends ApiResource
{
    protected $_resourceName = 'settings';

    /**
     * Sensitive config keys that must not be exposed
     */
    private $_sensitiveKeys = array(
        'dbhost', 'dbusername', 'dbpassword', 'dbdatabase', 'dbprefix',
        'enc_key', 'admin_file', 'smtp_password',
    );

    /**
     * GET /settings
     */
    public function listing()
    {
        $this->_requireRead();

        $config = $GLOBALS['config']->get('config');
        if (is_array($config)) {
            foreach ($this->_sensitiveKeys as $key) {
                unset($config[$key]);
            }
        }

        ApiResponse::success($config ?: array());
    }

    /**
     * GET /settings/{key}
     * Special routes: currencies, countries, zones/{country_id}
     */
    public function get($id)
    {
        $this->_requireRead();

        switch ($id) {
            case 'currencies':
                $currencies = $this->_db->select('CubeCart_currency', false, false, array('name' => 'ASC'));
                ApiResponse::success($currencies ?: array());
                return;

            case 'countries':
                $countries = $this->_db->select('CubeCart_geo_country', false, false, array('name' => 'ASC'));
                ApiResponse::success($countries ?: array());
                return;

            case 'languages':
                $data = array(
                    'available' => $GLOBALS['language']->listLanguages(),
                    'current'   => $GLOBALS['language']->current(),
                );
                ApiResponse::success($data);
                return;

            case 'store':
                // Store info subset
                $config = $GLOBALS['config']->get('config');
                $info = array(
                    'store_name'   => isset($config['store_name']) ? $config['store_name'] : '',
                    'store_url'    => CC_STORE_URL,
                    'version'      => CC_VERSION,
                    'currency'     => isset($config['default_currency']) ? $config['default_currency'] : '',
                    'time_zone'    => isset($config['time_zone']) ? $config['time_zone'] : 'UTC',
                );
                ApiResponse::success($info);
                return;
        }

        // Single config key lookup
        if (in_array($id, $this->_sensitiveKeys)) {
            ApiResponse::error('Access denied to this setting', 'FORBIDDEN', 403);
        }

        $value = $GLOBALS['config']->get('config', $id);
        if ($value === false || $value === null) {
            ApiResponse::error("Setting '$id' not found", 'NOT_FOUND', 404);
        }

        ApiResponse::success(array('key' => $id, 'value' => $value));
    }

    /**
     * PUT /settings (batch update)
     */
    public function updateBatch()
    {
        $this->_requireWrite();
        $data = $this->_getRequestBody();

        if (empty($data)) {
            ApiResponse::error('No settings to update', 'BAD_REQUEST', 400);
        }

        $updated = array();
        foreach ($data as $key => $value) {
            if (in_array($key, $this->_sensitiveKeys)) {
                continue; // silently skip sensitive keys
            }
            $GLOBALS['config']->set('config', $key, $value);
            $updated[$key] = $value;
        }

        ApiResponse::success(array('updated' => $updated));
    }

    /**
     * Sub-resource: GET /settings/zones/{country_id}
     */
    public function subList($id, $subResource)
    {
        $this->_requireRead();

        if ($id === 'zones' && is_numeric($subResource)) {
            $zones = $this->_db->select('CubeCart_geo_zone', false, array('country_id' => (int)$subResource), array('name' => 'ASC'));
            ApiResponse::success($zones ?: array());
            return;
        }

        parent::subList($id, $subResource);
    }
}
