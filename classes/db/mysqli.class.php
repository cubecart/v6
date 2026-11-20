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
require CC_ROOT_DIR.'/classes/db/database.class.php';

/**
 * MySQLi database controller
 */
class Database extends Database_Contoller
{

    ##############################################

    final protected function __construct($config)
    {
        $this->_db_engine = 'MySQLi';

        $dbport = (isset($config['dbport']) && !empty($config['dbport'])) ? $config['dbport'] : ini_get('mysqli.default_port');
        $dbsocket = (isset($config['dbsocket']) && !empty($config['dbsocket'])) ? $config['dbsocket'] : ini_get('mysqli.default_socket');

        $this->_db_connect_id = new mysqli($config['dbhost'], $config['dbusername'], $config['dbpassword'], $config['dbdatabase'], $dbport, $dbsocket);

        mysqli_options($this->_db_connect_id, MYSQLI_OPT_LOCAL_INFILE, true);

        if ($this->_db_connect_id->connect_error) {
            trigger_error($this->_db_connect_id->connect_error, E_USER_ERROR);
        } else {
            $this->connected = true;
        }

        $this->_prefix = $config['dbprefix'];

        $this->_setup();

        //Run the parent constructor
        parent::__construct();
    }

    /**
     * Setup the instance (singleton)
     *
     * @param $config array
     * @return Database
     */
    public static function getInstance($config = '')
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($config);
        }

        return self::$_instance;
    }

    /**
     * Rows affected by the last statement
     *
     * Noted as the statement ran, not read from the connection now. The connection
     * only remembers its latest query, and after a failure that is the one that
     * wrote the failure to the error log.
     *
     * @return int
     */
    public function affected()
    {
        return (int)$this->_statement_affected;
    }

    /**
     * Close the DB connection
     *
     * @return bool
     */
    public function close()
    {
        return $this->_db_connect_id->close();
    }

    /**
     * Did the last statement fail?
     *
     * @return bool
     */
    public function error()
    {
        $this->_errorno = (int)$this->_statement_errno;
        return ((bool)$this->_errorno) ? true : false;
    }

    /**
     * The last statement's error message, empty if it worked
     *
     * @return string
     */
    public function errorInfo()
    {
        return (string)$this->_statement_error;
    }

    /**
     * Get fields from a table
     *
     * @param string $table
     * @param bool $all
     * @return array
     */
    public function getFields($table, $all = false)
    {
        if (isset($this->_allowedColumns[$table]) && is_array($this->_allowedColumns[$table])) {
            return $this->_allowedColumns[$table];
        }

        $query = "SHOW COLUMNS FROM {$this->_prefix}$table;";

        //Try cache first
        if (($return = $this->_getCached($query)) !== false) {
            $this->_allowedColumns[$table] = $return;
            return $return;
        }

        $return = array();
        if (($result = $this->_db_connect_id->query($query)) !== false) {
            while (($row = $result->fetch_assoc()) !== null) {
                $return[$row['Field']] = $row['Field'];
            }
        }

        //Write the cache
        $this->_writeCache($return, $query);
        $this->_allowedColumns[$table] = $return;

        return $return;
    }

    /**
     * The AUTO_INCREMENT id made by the last statement, 0 if it made none or failed
     *
     * @return int
     */
    public function insertid()
    {
        return (int)$this->_statement_insert_id;
    }

    /**
     * Get the server version
     *
     * @return string
     */
    public function serverVersion()
    {
        return $this->_db_connect_id->server_info;
    }

    /**
     * Make a string SQL safe
     *
     * @param string $value
     * @param string $quote
     * @return string
     */
    public function sqlSafe($value, $quote = false)
    {
        $value = $this->_db_connect_id->escape_string(stripslashes((string)$value));

        return (!$quote || is_null($value)) ? $value : "'$value'";
    }

    //=====[ Private ]=======================================

    /**
     * Execute a query
     *
     * @param bool $cache
     * @param string $fetch
     *
     * @return bool
     */
    protected function _execute($cache = true, $fetch = true, $log = true)
    {
        $cache = $cache && !preg_match('#\b('.$this->_cache_block_functions.')\b#', $this->_query) ?: false;

        $this->_found_rows = null;
        $this->_statement_affected  = 0;
        $this->_statement_errno     = 0;
        $this->_statement_error     = '';
        $this->_statement_insert_id = 0;

        if (!empty($this->_query)) {
            $this->_result = array();
            // Don't read from cache in admin CP but write only for front end
            $cache = (defined('ADMIN_CP') && ADMIN_CP) ? false : $cache;
            if ($cache) {
                //Try getting the SQL cache
                $cache_check = $this->_getCached($this->_query);
                if (is_array($cache_check) && (isset($cache_check['empty']) && $cache_check['empty']) && isset($cache_check['data'])) {
                    $this->_result = $cache_check['data'];
                    $this->_found_rows = sizeof($this->_result);
                    $this->_sqlDebug($cache, true);
                    return true;
                } elseif (is_array($cache_check)) {
                    $this->_result = $cache_check;
                    $this->_found_rows = sizeof($this->_result);
                    $this->_sqlDebug($cache, true);
                    return true;
                }
            }

            $this->_startTimer();

            $result = $this->_db_connect_id->query($this->_query);

            // Note this statement's own outcome before anything else uses the
            // connection. All four are live reads there, and _logError() below runs a
            // statement of its own, so a caller asking afterwards would get the log
            // write's answers. affected(), insertid(), error() and errorInfo() all
            // answer from these notes.
            $this->_statement_affected  = mysqli_affected_rows($this->_db_connect_id);
            $this->_statement_errno     = (int)$this->_db_connect_id->errno;
            $this->_statement_error     = (string)$this->_db_connect_id->error;
            // A statement that fails leaves the connection holding the id from the
            // last INSERT that worked, which is not this statement's to claim.
            $this->_statement_insert_id = $this->_statement_errno ? 0 : (int)$this->_db_connect_id->insert_id;

            if ($result) {
                if (is_bool($result)) {
                    $this->_result = $result;
                } else {
                    $this->_found_rows = $result->num_rows;
                    $this->_result = array();
                    while ($row = $result->fetch_assoc()) {
                        $this->_result[] = $row;
                    }
                    $result->close();
                }
            }
            $this->_stopTimer();

            //If there is an error and its not because of system error
            if ($log && $this->error() && (strpos($this->errorInfo(), 'CubeCart_system_error_log') === false)) {
                $this->_logError();
            }

            //Cache the result if needed
            if ($this->_statement_errno) {
                // A statement that failed has no result to remember. Cached, its empty
                // answer would be served as "no rows" until the cache was next cleared.
                $cache = false;
            } elseif ($cache && ($this->_writeCache($this->_result, $this->_query)) === false) {
                $cache = false; // Query not cached for some reason. Check error log and cache status.
            }
             
            return (!$this->_sqlDebug($cache, false)) ? true : false;
        }

        return false;
    }

    /**
     * Setup anything DB wise
     */
    private function _setup()
    {
        @mysqli_query($this->_db_connect_id, "SET SESSION sql_mode = ''");

        if (defined('CC_IN_SETUP') && CC_IN_SETUP) {
            // check MySQL Strict mode on upgrade/install
            $mysql_mode = $this->misc('SELECT @@sql_mode;');
            if (stristr($mysql_mode[0]['@@sql_mode'], 'strict')) {
                die($GLOBALS['language']->setup['error_strict_mode']);
            }
            return false;
        }

        //Force UTF-8
        $this->_db_connect_id->set_charset("utf8mb4");
    }
}
