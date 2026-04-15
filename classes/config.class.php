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
 * Configuration controller
 */
class Config
{

    /**
     * Current config
     *
     * @var array
     */
    private $_config = array();
    /**
     * Session config
     *
     * @var array
     */
    private $_session_config = array();
    /**
     * Temp configs that should not be written to the db
     *
     * @var array
     */
    private $_temp  = array();
    /**
     * Write the config to the DB
     *
     * @var bool
     */
    private $_write_db = false;
    /**
     * Array of variables before config is written used for validation
     *
     * @var array
     */
    private $_pre_enc_config = array();

    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;

    ##############################################

    final protected function __construct($glob)
    {
        //Get the main config because it will be used
        if (isset($GLOBALS['db'])) {
            $array_out = $this->_fetchRows('config');
        }

        //Remove the db password for safety
        unset($glob['dbpassword']);
        //Remove cache setting due to variable clash
        if (isset($glob['cache'])) {
            unset($glob['cache']);
        }

        if (!empty($array_out)) {
            $this->_config['config'] = $this->_clean($array_out);
            //Merge the main global with the config
            if (is_array($this->_config['config'])) {
                $this->_config['config'] = array_merge($this->_config['config'], $glob);
            }
        } else {
            $this->_config['config'] = $glob;
        }
        // Mark $glob keys as temp so they override DB values
        // in memory but are never written back to the database
        foreach ($glob as $key => $value) {
            $this->_temp['config'][$key] = $value;
        }
        if(isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) {
            $GLOBALS['cache']->enable(isset($this->_config['config']['cache']) ? (bool)$this->_config['config']['cache'] : false);
        }
    }

    public function __destruct()
    {
        //Do we need to write to the db
        if ($this->_write_db) {
            $this->_writeDB();
        }
    }

    /**
     * Setup the instance (singleton)
     *
     * @param $glob array Current globals
     *
     * @return Config
     */
    public static function getInstance($glob = array())
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($glob);
        }

        return self::$_instance;
    }

    //=====[ Public ]=======================================

    /**
     * Is there a config element
     *
     * @param string $config_name
     * @param string $element
     *
     * @return bool
     */
    public function has($config_name, $element)
    {
        return ($this->get($config_name, $element, true)) !== false;
    }

    /**
     * Get a value from the config
     *
     * Not all config types are loaded from the start this
     * is done to save cycles and memory
     *
     * If element is empty the entire array of the config
     * is returned
     *
     * @param string $config_name
     * @param string $element
     *
     * @return mixed / false
     */
    public function get($config_name, $element = '', $isset = false)
    {
        if(!empty($element) && isset($this->_session_config[$config_name][$element])) {
            return $this->_session_config[$config_name][$element];
        }

        //If there is an config
        if (isset($this->_config[$config_name])) {
            //If there is not an element the entire array
            if (empty($element)) {
                return ($isset) ? true : $this->_config[$config_name];
            } elseif (isset($this->_config[$config_name][$element])) {
                return ($isset) ? true : $this->_config[$config_name][$element];
            }

            return false;
        }

        //If we reached this part try to fetch it
        $this->_fetchConfig($config_name);

        //Return it if found
        return $this->get($config_name, $element, $isset);
    }

    /**
     * Is an element empty
     *
     * @param string $config_name
     * @param string $element
     *
     * @return bool
     */
    public function isEmpty($config_name, $element)
    {
        //If the element isn't there then it is empty
        if (!$this->has($config_name, $element)) {
            return true;
        }

        return empty($this->_config[$config_name][$element]);
    }

    /**
     * Merge an emlemet to the config
     *
     * This is done for items that do not need to be recorded to the db
     * or are single use config items.  For example ssl enable/disable.
     *
     * @param string $config_name
     * @param string $element
     * @param string $data
     */
    public function merge($config_name, $element, $data)
    {
        if (!empty($element)) {
            $this->_temp[$config_name][$element] = $data;
            $this->_config[$config_name][$element] = $data;
        } else {
            if (is_array($data)) {
                if (isset($this->_temp[$config_name])) {
                    $this->_temp[$config_name] = merge_array($this->_temp[$config_name], $data);
                } else {
                    $this->_temp[$config_name] = $data;
                }
                $this->_config[$config_name] = merge_array($this->_config[$config_name], $data);
            }
        }
    }

    public function setSessionConfig($config_name, $data) {
        if (isset($this->_session_config[$config_name])) {
            $this->_session_config[$config_name] = merge_array($this->_session_config[$config_name], $data);
        } else {
            $this->_session_config[$config_name] = $data;
        }
    }

    /**
     * Set a config value
     *
     * If no element is set then the entire config is
     * set to the data
     *
     * @param string $config_name
     * @param string $element
     * @param string $data
     * @param bool $force_write
     *
     * @return bool
     */
    public function set($config_name, $element, $data, $force_write = false)
    {
        //Clean up the config array
        if (is_array($data)) {
            array_walk_recursive($data, function (&$s, $k) {
                $s = $this->_stripslashes($s);
            });
        } else {
            $data = $this->_stripslashes($data);
        }

        /**
         * Check to see if the data is the same as it was.
         * If it is we dont need to do anything
         */
        if ($this->has($config_name, $element) && $this->get($config_name, $element) === $data) {
            return true;
        }

        //If there isn't an element assign the entire thing
        if (empty($element)) {
            $this->_config[$config_name] = $data;
        } else {
            $this->_config[$config_name][$element] = $data;
        }

        //Write the to the db
        if (!$force_write) {
            $this->_write_db = true;
        } else {
            $this->_writeDB();
            $this->_write_db = false;
        }
        return true;
    }

    //=====[ Private ]=======================================

    /**
     * Strip slashes
     *
     * @param array $array
     *
     * @return array
     */
    private function _clean($array)
    {
        array_walk_recursive($array, function (&$s, $k) {
            return $this->_stripslashes($s);
        });
        return $array;
    }

    /**
     * Decode a stored config value back to its PHP type
     *
     * Scalars are stored as plain text, arrays as JSON strings
     *
     * @param string $raw
     * @return mixed
     */
    private function _decodeValue($raw)
    {
        if ($raw === null || $raw === '') {
            return $raw;
        }
        // Only attempt JSON decode for values that look like JSON arrays/objects
        if (isset($raw[0]) && ($raw[0] === '{' || $raw[0] === '[')) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }
        return $raw;
    }

    /**
     * Encode a PHP value for NVP storage
     *
     * Arrays are stored as JSON, scalars as plain text
     *
     * @param mixed $value
     * @return string
     */
    private function _encodeValue($value)
    {
        if (is_array($value)) {
            return json_encode($value);
        }
        return (string)$value;
    }

    /**
     * Fetch NVP rows for a config section and rebuild the array
     *
     * @param string $name
     * @return array|false
     */
    private function _fetchRows($name)
    {
        if (!isset($GLOBALS['db'])) {
            return false;
        }
        $result = $GLOBALS['db']->select('CubeCart_config', array('config_key', 'config_value'), array('name' => $name));
        if ($result === false) {
            return false;
        }
        $array_out = array();
        foreach ($result as $row) {
            $array_out[$row['config_key']] = $this->_decodeValue($row['config_value']);
        }
        return $array_out;
    }

    /**
     * Fetch config data
     *
     * @param string $name
     */
    private function _fetchConfig($name)
    {
        //Clean up the entire config array
        $this->_config[$name] = array();

        $array_out = $this->_fetchRows($name);

        if (isset($GLOBALS['db']) && ($module = $GLOBALS['db']->select('CubeCart_modules', array('status', 'countries'), array('folder' => $name), false, 1, false)) !== false) {
            $array_out = is_array($array_out) ? array_merge($module[0], $array_out) : $module[0];
        }

        if (!empty($array_out)) {
            $this->_config[$name] = $this->_clean($array_out);
        }
    }

    /**
     * Strip slashes for strings only
     */
    private function _stripslashes($value)
    {
        return is_string($value) ? stripslashes($value) : $value;
    }

    /**
     * Write config to db
     */
    private function _writeDB()
    {
        if (!empty($this->_config) && is_array($this->_config)) {
            $db = Database::getInstance();
            foreach ($this->_config as $config => $data) {
                //Remove data that was merged in
                if (!empty($this->_temp) && isset($this->_temp[$config])) {
                    $match = array_intersect_key($this->_temp[$config], $this->_config[$config]);
                    if (!empty($match)) {
                        foreach ($match as $k => $v) {
                            unset($data[$k]);
                        }
                    }
                }
                //If there is a problem abort
                if (empty($data)) {
                    continue;
                }
                //Safeguard to prevent config loss
                if ($config == 'config' && !isset($data['store_name'])) {
                    return false;
                }
                if (strlen($config) > 100) {
                    trigger_error('Config write size error: '.$config, E_USER_ERROR);
                    continue;
                }
                $this->_pre_enc_config = $data;
                // Upsert each key to avoid race conditions
                // The old DELETE + INSERT pattern was not atomic — concurrent
                // requests could read an empty table mid-write and lose config
                foreach ($data as $key => $value) {
                    $db->upsert('CubeCart_config', array(
                        'name'         => $config,
                        'config_key'   => $key,
                        'config_value' => $this->_encodeValue($value)
                    ), array('config_value'));
                }
            }
        }
    }
}
