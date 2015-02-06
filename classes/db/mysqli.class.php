<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2015. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('CC_INI_SET')) die('Access Denied');
require CC_ROOT_DIR.'/classes/db/database.class.php';

/**
 * MySQLi database controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Database extends Database_Contoller {

	##############################################

	final protected function __construct($config) {
		$this->_db_engine = 'MySQLi';

		$this->_db_connect_id = new mysqli($config['dbhost'], $config['dbusername'], $config['dbpassword'], $config['dbdatabase']);
		if ($this->_db_connect_id->connect_error) {
			trigger_error($this->_db_connect_id->connect_error, E_USER_ERROR);
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
	public static function getInstance($config = '') {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($config);
		}

		return self::$_instance;
	}

	/**
	 * Returns the rows affected
	 *
	 * @return array
	 */
	public function affected() {
		return mysqli_affected_rows($this->_db_connect_id);
	}

	/**
	 * Close the DB connection
	 *
	 * @return bool
	 */
	public function close() {
		return $this->_db_connect_id->close();
	}

	/**
	 * Is there an error?
	 *
	 * @return bool
	 */
	public function error() {
		return ($this->_db_connect_id->errno) ? true : false;
	}

	/**
	 * Error info
	 *
	 * @return bool
	 */
	public function errorInfo() {
		return (string)$this->_db_connect_id->error;
	}

	/**
	 * Get fields from a table
	 *
	 * @param string $table
	 * @param bool $all
	 * @return array
	 */
	public function getFields($table, $all = false) {

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
	 * Get the inserted ID
	 *
	 * @return id
	 */
	public function insertid() {
		return (int)$this->_db_connect_id->insert_id;
	}

	/**
	 * Get the server version
	 *
	 * @return string
	 */
	public function serverVersion() {
		return $this->_db_connect_id->server_info;
	}

	/**
	 * Make a string SQL safe
	 *
	 * @param string $value
	 * @param string $quote
	 * @return string
	 */
	public function sqlSafe($value, $quote = false) {

		$value = $this->_db_connect_id->escape_string(stripslashes($value));

		return (!$quote || is_null($value)) ? $value : "'$value'";
	}

	/**
	 * Execute a query
	 *
	 * @param bool $cache
	 * @param string $fetch
	 *
	 * @return bool
	 */
	protected function _execute($cache = true, $fetch = true) {
		$this->_found_rows = null;

		if (!empty($this->_query)) {
			$this->_result = array();
			// Don't read from cache in admin CP but write only for front end
			$cache = (defined('ADMIN_CP') && ADMIN_CP) ? false : $cache;
			if ($cache) {
				//Try getting the SQL cache
				$this->_result = $this->_getCached($this->_query);
				if($this->_result=='empty') {
					return false;
				} elseif($this->_result) {
					$this->_found_rows = sizeof($this->_result);
					return true;
				}
			}

			$this->_startTimer();

			if (($result = $this->_db_connect_id->query($this->_query)) !== false) {
				if (is_bool($result)) {
					$this->_result = $result;
				} else {
					$this->_found_rows = $result->num_rows;
					while ($row = $result->fetch_assoc()) {
						$this->_result[] = array_map(array(&$this, 'strip_slashes'), $row);
					}
					$result->close();
				}
			}
			$this->_stopTimer();

			//If there is an error and its not because of system error
			if ($this->error() && (strpos($this->errorInfo(), 'CubeCart_system_error_log') === false)) {
				$this->_logError();
			}

			//Cache the result if needed
			$this->_writeCache($this->_result, $this->_query);
			 
			return (!$this->_sqlDebug($cache)) ? true : false;
		}

		return false;
	}

	/**
	 * Setup anything DB wise
	 */
	private function _setup() {

		@mysqli_query($this->_db_connect_id, "SET SESSION sql_mode = ''");

		if (defined('SKIP_DB_SETUP') && SKIP_DB_SETUP) {
			// check MySQL Strict mode on upgrade/install
			$mysql_mode = $this->misc('SELECT @@sql_mode;');
			if (stristr($mysql_mode[0]['@@sql_mode'], 'strict')) {
				die($lang['setup']['error_strict_mode']);
			}
			return false;
		}

		//Force UTF-8
		@mysqli_query($this->_db_connect_id, "SET NAMES 'utf8'");
		@mysqli_query($this->_db_connect_id, "SET CHARACTER SET 'utf8'");
	}
}