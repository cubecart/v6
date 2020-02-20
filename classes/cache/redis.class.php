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
 * @since 6.1.0
 */
class Cache extends Cache_Controler
{

    ##############################################

    final protected function __construct()
    {
        global $glob;
        require CC_INCLUDES_DIR."lib/predis/autoload.php";
        Predis\Autoloader::register();
        try {
            if (isset($glob['redis_parameters']) && isset($glob['redis_options'])) {
                $this->redis_client = new Predis\Client($glob['redis_parameters'], $glob['redis_options']);
            } elseif (isset($glob['redis_parameters'])) {
                $this->redis_client = new Predis\Client($glob['redis_parameters']);
            } else {
                $this->redis_client = new Predis\Client();
            }
        } catch (Predis\Connection\ConnectionException $e) {
            trigger_error($e->getMessage());
            return ;
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
        return $this->redis_client->del($this->_makeName($id));
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

        $name = $this->_makeName($id);

        //Try to set the temp variable to the item
        if (($this->_temp[$name] = (bool)$this->redis_client->exists($name)) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Get all the cache ids
     *
     * @return array
     */
    public function getIDs()
    {
        if (empty($this->_ids)) {
            $info = $this->redis_client->keys('*');

            if (!empty($info) && is_array($info)) {
                foreach ($info as $item) {
                    $this->_ids[] = str_replace(array($this->_prefix, $this->_suffix), '', $item);
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
        if ($contents = $this->redis_client->get($name)) {
            if (!empty($contents)) {
                return json_decode($contents, true);
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
        return 'files'; // for now
    }
    /**
     * Get session save path
     *
     * @return string
     */
    public function session_save_path() {
        return '';
    }

    /**
     * Calculates the cache usage
     *
     * @return string
     */
    public function usage()
    {
        $groups = $this->redis_client->info();
        $output = '<table>';
        foreach ($groups as $group_name => $group_data) {
            $output .= '<tr><th colspan="2">'.$group_name.'</th></tr>';
            foreach ($group_data as $key => $value) {
                if (is_array($value)) {
                    $array_value = '';
                    foreach ($value as $key => $key_value) {
                        $array_value .= $key.': '.$key_value.'<br>';
                    }
                    $value = $array_value;
                }
                $output .= '<tr><td>'.$key.'</td><td>'.$value.'</td></tr>';
            }
        }
        $output .= '</table>';
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

        if (is_array($data) || is_string($data)) {
            $data = json_encode($data);
        } else {
            return false;
        }

        $this->redis_client->set($name, $data);
        return true;
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
}
