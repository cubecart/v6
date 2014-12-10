<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */


/**
 * Debug controller
 *
 * @author Technocrat
 * @version 1.1.0
 * @since 5.0.0
 */
class Debug {
	/**
	 * FirePHP controller
	 *
	 * @var FirePHP object
	 */
	public $firephp  = null;

	/**
	 * Custom debug messages
	 *
	 * @var array of strings
	 */
	private $_custom  = array();
	/**
	 * Debug timer used to calc page load time
	 *
	 * @var float
	 */
	private $_debug_timer = 0;
	/**
	 * Display debug message
	 *
	 * @var bool
	 */
	private $_display  = true;
	/**
	 * Enabled/disabled
	 *
	 * @var bool
	 */
	private $_enabled  = false;
	/**
	 * Error messages
	 *
	 * @var array of strings
	 */
	private $_errors  = array();
	/**
	 * FirePHP log group
	 *
	 * @var array
	 */
	private $_messages  = array();
	/**
	 * SQL messages
	 *
	 * @var array of strings
	 */
	private $_sql   = array();
	/**
	 * Custom timers
	 *
	 * @var array of floats
	 */
	private $_timers  = array();
	/**
	 * XDebug enabled
	 *
	 * @var bool
	 */
	private $_xdebug  = false;

	/**
	 * Class instance
	 *
	 * @var instance
	 */
	protected static $_instance;

	final protected function __construct() {
		// Turn error reporting off as it is displayed in debugger mode only!
		ini_set('display_errors', false);

		// Show ALL errors & notices
		error_reporting(E_ALL ^ E_NOTICE);
		ini_set('ignore_repeated_errors', true);
		ini_set('ignore_repeated_source', true);

		// Enable HTML Error messages
		ini_set('html_errors', true);
		ini_set('docref_root', 'http://docs.php.net/manual/en/');
		ini_set('docref_ext', '.php');

		// Define the Error & Exception handlers
		set_error_handler(array(&$this, 'errorLogger'), ini_get('error_reporting'));
		set_exception_handler(array(&$this, 'exceptionHandler'));

		// Enable debugger
		if (isset($GLOBALS['config']) && is_object($GLOBALS['config'])) {
			$this->_enabled = $GLOBALS['config']->get('config', 'debug');
			$ip_string = $GLOBALS['config']->get('config', 'debug_ip_addresses');
			if (!empty($ip_string)) {
				if (strstr($ip_string, ',')) {
					$ip_addresses = explode(',', $ip_string);
					if (!in_array(get_ip_address(), $ip_addresses)) {
						$this->_enabled = false;
					}
				} else {
					if ($ip_string!==get_ip_address()) {
						$this->_enabled = false;
					}
				}
			}
		}

		//If its time to clear the cache
		if (isset($_GET['debug-cache-clear'])) {
			$GLOBALS['cache']->clear();
			$GLOBALS['cache']->tidy();
			httpredir(currentPage(array('debug-cache-clear')));
		}

		//Check for xdebug
		if (extension_loaded('xdebug') && function_exists('xdebug_is_enabled')) {
			$this->_xdebug = xdebug_is_enabled();
		}

		$this->_debug_timer = $this->_getTime();

		if ($this->_enabled && file_exists(CC_INCLUDES_DIR.'FirePHPCore/')) {
			require_once CC_INCLUDES_DIR.'FirePHPCore/fb.php';    // (procedural API) or
			require_once CC_INCLUDES_DIR.'FirePHPCore/FirePHP.class.php'; // (object oriented API)
			$this->firephp = FirePHP::getInstance(true);
		}

		// Check register_globals
		if (ini_get('register_globals')) {
			trigger_error('register_globals are enabled. It is highly recommended that you disable this in your PHP configuration, as it is a large security hole, and may wreak havoc.', E_USER_WARNING);
		}
		Sanitize::cleanGlobals();
	}

	public function __destruct() {
		$this->display();
		restore_error_handler();
		restore_exception_handler();
	}

	/**
	 * Setup the instance (singleton)
	 *
	 * @return Debug
	 */
	public static function getInstance() {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Set an SQL message
	 *
	 * @param string $type
	 * @param string $message
	 * @return bool
	 */
	public function debugSQL($type, $message) {
		if (!is_null($type) && !is_null($message)) {
			$this->_sql[$type][] = htmlentities($message, ENT_COMPAT, 'UTF-8');
			return true;
		}

		return false;
	}

	/**
	 * Set a debug message
	 *
	 * @param string $message
	 */
	public function debugMessage($message) {
		$this->_messages[] = $message;
	}

	/**
	 * Add a message to the debug tail
	 *
	 * @param mixed $data
	 * @param string $name
	 * @return bool
	 */
	public function debugTail(&$data, $name = '') {
		$name = (empty($name)) ? count($this->_custom) : $name;
		if (!isset($this->_custom[$name])) {
			$this->_custom[$name] =& $data;
			return true;
		} else {
			return $this->debugTail($data);
		}

		return false;
	}

	/**
	 * Display debug
	 */
	public function display($return = false, $glue = "\n") {
		
		// Cheeky hack for the w3c validator - we don't want it seeing the debug output
		if (strstr($_SERVER['HTTP_USER_AGENT'], 'W3C_Validator')) {
			$this->_enabled = false;
		}

		if ($this->_display && $this->_enabled) {
			
			$output[] = "<div style='font-family: \"Courier New\",Courier,monospace;font-size: 10px;border-top: 5px dashed silver;color: #000;background-color: #E7E7E7; clear: both'>";

			// Display the PHP errors
			$output[] = '<strong>PHP</strong>:<br />'.$this->_errorDisplay().'<hr size="1" />';

			//Get the super globals
			if (($ret = $this->_makeExportString('GET', $_GET)) !== false) {
				$output[] = $ret;
			}
			if (($ret = $this->_makeExportString('POST', $_POST)) !== false) {
				$output[] = $ret;
			}
			if (isset($_SESSION) && !empty($_SESSION) && ($ret = $this->_makeExportString('SESSION', $_SESSION)) !== false) {
				$output[] = $ret;
			}
			if (($ret = $this->_makeExportString('COOKIE', $_COOKIE)) !== false) {
				$output[] = $ret;
			}
			if (($ret = $this->_makeExportString('FILES', $_FILES)) !== false) {
				$output[] = $ret;
			}

			//Custom timers
			if (!empty($this->_timers)) {
				$output[] = '<strong>Timers</strong><br />';
				foreach ($this->_timers as $name => $timer) {
					$output[] = '<strong>'.$name.'</strong>: '.$timer['diff'].'<br />';
				}
				$output[] = '<hr size="1" />';
			}

			// Display SQL Queries and Errors
			if (!empty($this->_sql)) {
				$output[] = '<strong>'.Database::getInstance()->getDbEngine().'</strong><br />';

				if(defined('ADMIN_CP') && ADMIN_CP) {
					$output[] = '### In the admin control panel SQL cache is written but not read! ###<br />';
				} else {
					$output[] = '<br />';
				}

				if (!empty($this->_sql['query'])) {
					$output[] = '<strong>Queries ('.count($this->_sql['query']).')</strong>:';

					foreach ($this->_sql['query'] as $index => $query) {
						if (!empty($query)) {
							$output[] = '[<strong>'.($index + 1).'</strong>] '.strip_tags($query).'<br />';
						}
					}
				}
				if (!empty($this->_sql['error'])) {
					$output[] = '<strong>Errors</strong>:<br />';
					foreach ($this->_sql['error'] as $index => $error) {
						if (!empty($error)) {
							$sql_error = true;
							$output[] = '[<strong>'.($index + 1).'</strong>] '.strip_tags($error).'<br />';
						}
					}
					if (!isset($sql_error)) {
						$output[] = 'No Errors';
					}
				}
				$output[] = '<hr size="1" />';
			}

			if (!empty($this->_messages)) {
				$output[] = '<strong>Debug Messages</strong>:<br />';
				foreach ($this->_messages as $key => $message) {
					$output[] = '['.$key.'] '.$message.'<br />';
				}
				$output[] = '<hr size="1" />';
			}

			// Display logged variables
			if (!empty($this->_custom)) {
				foreach ($this->_custom as $name => $data) {
					if (empty($data)) {
						$data = 'No data';
					}
					if (is_numeric($name)) {
						$name = "customLog[$name]";
					}
					if (is_array($data)) {
						ksort($data);
						$data = '<pre>'.print_r($data, true).'</pre>';
					}
					$output[] = '<strong>'.htmlentities($name, ENT_QUOTES, 'UTF-8').'</strong>:<br />'.$data.'<hr size="1" />';
				}
			}

			// Show some performance data
			$output[] = '<strong>Memory: Peak Usage / Max (%)</strong>:<br />'.$this->_debugMemoryUsage(true).'<hr size="1" />';
			// Show cache stats
			//We need another cache instance because of the destruct
			
			$cache = Cache::getInstance();
			$output[] = '<strong>Cache ('.$cache->getCacheSystem().')</strong>:<br />'.$cache->usage().' [<a href="'.currentPage(null, array('debug-cache-clear' => 'true')).'">Clear Cache</a>]<hr size="1" />';

			// Page render timer
			$output[] = '<strong>Page Load Time</strong>:<br />'.($this->_getTime() - $this->_debug_timer).' seconds';
			if ($this->_xdebug && ini_get('xdebug.profiler_enable_trigger') == 1) {
				$output[] = ' [<a href="'.currentPage(null, array('XDEBUG_PROFILE' => 'true')).'">CacheGrind</a>]';
			}

			$output[] = '</div>';
			$content = implode($glue, $output);
			$this->_display = false;
			
			if ($return) {
				return $content;
			} else {
				echo $content;
			}
		}
	}

	/**
	 * End a custom timer
	 *
	 * The timer will be displayed on the debug display as well
	 *
	 * @param string $name
	 * @return float
	 */
	public function endTimer($name) {
		if (isset($this->_timers[$name])) {
			$this->_timers[$name]['end'] = $this->_getTime();
			$this->_timers[$name]['diff'] = $this->_timers[$name]['end'] - $this->_timers[$name]['start'];

			return $this->_timers[$name]['diff'];
		}

		trigger_error('Timer not started ('.$name.')', E_USER_WARNING);

		return 0;
	}

	/**
	 * Error handler
	 *
	 * @param object $e
	 */
	public function exceptionHandler($e) {
		$message = "[<strong>Exception</strong>] \t".$e->getFile().":".$e->getLine()." - ".$e->getMessage();
		$this->_errors[] = $message;
		$this->_writeErrorLog($message);
	}

	/**
	 * Error logger
	 *
	 * @param int $error_no
	 * @param string $error_string
	 * @param string $error_file
	 * @param string $error_line
	 * @param string $error_context
	 * @return bool
	 */
	public function errorLogger($error_no, $error_string, $error_file, $error_line, $error_context = null) {
		$log = true;
		switch ($error_no) {
		case E_CORE_ERROR:
			$type = 'Core Error';
			break;
		case E_CORE_WARNING:
			$type = 'Core Warning';
			$log = false;
			break;
		case E_COMPILE_ERROR:
			$type = 'Compile Error';
			break;
		case E_COMPILE_WARNING:
			$type = 'Compile Warning';
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$type = 'Error';
			break;
		case E_NOTICE:
		case E_USER_NOTICE:
			$type = 'Notice';
			$log = false;
			break;
		case E_PARSE:
			$type = 'Parse Error';
			break;
		case E_RECOVERABLE_ERROR:
			$type = 'Recoverable';
			break;
		case E_STRICT:
			$type = 'Strict';
			$group = 'warn';
			$log = false;
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$type = 'Warning';
			$log = false;
			break;
		case 'EXCEPTION':
			$type = 'Exception';
			break;
		default:
			$type = 'Unknown ('.$error_no.')';
			if (CC_PHP_ID > 52) {
				if ($error_no == E_DEPRECATED || $error_no == E_USER_DEPRECATED) {
					$type = 'Deprecated';
				}
			}
			break;
		}
		$error = "[<strong>".$type."</strong>] \t".$error_file.":".$error_line." - ".$error_string;
		$this->_errors[] = $error;

		if ($log) {
			if (is_object($this->firephp)) {
				$this->firephp->error($error_file.":".$error_line." - ".$error_string, 'ERROR');
			}
			$this->_writeErrorLog($error);
		}

		return false;
	}

	/**
	 * Bulk firePHP something
	 *
	 */
	public function firePHP() {
		if (!$this->_enabled || !is_object($this->firephp)) {
			return;
		}

		$arg_list = func_get_args();
		$numargs = func_num_args();
		$type = 'log';
		for ($i = 0; $i < $numargs; ++$i) {
			if ($i == 0) {
				switch ($arg_list[0]) {
				case 'error':
					$type = 'error';
					break;
				case 'info':
					$type = 'info';
					break;
				case 'log':
					$type = 'log';
					break;
				case 'warn':
					$type = 'warn';
					break;
				default:
					$this->firephp->{$type}($arg_list[0]);
					break;
				}
			} else {
				$this->firephp->{$type}($arg_list[$i]);
			}
		}
	}

	/**
	 * Start a custom timer
	 *
	 * @param string $name
	 */
	public function startTimer($name) {
		$this->_timers[$name]['start'] = $this->_getTime();
	}

	/**
	 * Get/set the debug status
	 *
	 * @param bool $status
	 * @return bool
	 */
	public function status($status = null) {
		if (!is_null($status) && is_bool($status)) {
			$this->_enabled = $status;
		}

		return $this->_enabled;
	}

	/**
	 * Supress display
	 */
	public function supress() {
		$this->_display = false;
	}

	/**
	 * Get the byte size
	 *
	 * @param float $input
	 * @return string
	 */
	private static function _debugGetBytes($input) {
		switch (substr($input, -1, 1)) {
		case 'G':
			$bytes = ((substr($input, 0, strlen($input)-1) * 1024) * 1024) * 1024;
			break;
		case 'M':
			$bytes = (substr($input, 0, strlen($input)-1) * 1024) * 1024;
			break;
		case 'K':
			$bytes = substr($input, 0, strlen($input)-1) * 1024;
			break;
		default:
			$bytes = $input;
		}
		return $bytes;
	}

	/**
	 * Get memory usage
	 *
	 * @param bool $peak
	 * @return string
	 */
	private function _debugMemoryUsage($peak = false) {
		$memAvail = ini_get('memory_limit');
		if ($this->_xdebug) {
			$memUsed = ($peak) ? xdebug_peak_memory_usage() : xdebug_memory_usage();
		} else {
			$memUsed = ($peak) ? memory_get_peak_usage() : memory_get_usage();
		}
		$memPercent = round(($memUsed/$this->_debugGetBytes($memAvail))*100, 2);
		$memUsedHR = implode('', formatBytes($memUsed));

		return $memUsedHR.' / '.$memAvail.' ('.$memPercent.'%)';
	}

	/**
	 * Make error message
	 *
	 * @param string $glue
	 * @return string
	 */
	private function _errorDisplay($glue = '<br />') {
		if (!empty($this->_errors) && is_array($this->_errors)) {
			return implode($glue, $this->_errors);
		} else {
			return 'No Errors or Warnings';
		}
	}

	/**
	 * Get time using microtime or xdebug
	 */
	private function _getTime() {
		if ($this->_xdebug) {
			return xdebug_time_index();
		}

		return microtime(false);
	}

	/**
	 * Make a variable export string
	 *
	 * @param string $variable
	 * @param int $left
	 * @return string
	 */
	private function _makeExport($variable, $left = 8) {
		$output = '';
		foreach ($variable as $key => $value) {
			if (is_array($value)) {
				$output .= '<div style="margin-left: '.$left.'px;">\''.$key.'\' => '.$this->_makeExport($value, ($left + 8)).'</div>';
			} else {
				$output .= '<div style="margin-left: '.$left.'px;">\''.$key.'\' => '.var_export($value, true).'</div>';
			}
		}

		return $output;
	}

	/**
	 * Makes an export string for debug
	 *
	 * @param string $name
	 * @param array $variable
	 * @return string/false
	 */
	private function _makeExportString($name, $variable) {
		$output = '';

		if (!empty($variable)) {
			$output = '<strong>'.$name.'</strong>:<br />';
			$output .= $this->_makeExport($variable);
			$output .= '<hr size="1" />';
		}

		if (!empty($output)) {
			return $output;
		} else {
			return false;
		}
	}

	/**
	 * Write message to the error log in the DB
	 *
	 * @param string $message
	 */
	private function _writeErrorLog($message) {
		Database::getInstance()->insert('CubeCart_system_error_log', array('message' => $message, 'time' => time()));
	}
}