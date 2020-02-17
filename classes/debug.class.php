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


/**
 * Debug controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Debug
{
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
     * Debug collect sections flag
     *
     * @var bool
     */
    public $stream_into_session = true;
    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;

    ##############################################

    final protected function __construct()
    {
        // Turn error reporting off as it is displayed in debugger mode only!
        ini_set('display_errors', false);

        // Show ALL errors & notices
        error_reporting(E_ALL);
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
            $this->_enabled = (bool)$GLOBALS['config']->get('config', 'debug');
            if (!$this->_enabled) {
                error_reporting(E_ALL ^ (E_NOTICE | E_DEPRECATED | E_USER_DEPRECATED));
            }
            $ip_string = $GLOBALS['config']->get('config', 'debug_ip_addresses');
            if (!empty($ip_string)) {
                if (strstr($ip_string, ',')) {
                    $ip_string = preg_replace('/\s+/', '', $ip_string);
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
        if (!CC_IN_ADMIN && isset($_GET['debug-cache-clear'])) {
            $GLOBALS['cache']->clear();
            $GLOBALS['cache']->tidy();
            httpredir(currentPage(array('debug-cache-clear')));
        }

        //Check for xdebug
        if (extension_loaded('xdebug') && function_exists('xdebug_is_enabled')) {
            $this->_xdebug = xdebug_is_enabled();
        }

        $this->_debug_timer = $this->_getTime();

        // Check register_globals
        if (ini_get('register_globals')) {
            trigger_error('register_globals are enabled. It is highly recommended that you disable this in your PHP configuration, as it is a large security hole, and may wreak havoc.', E_USER_WARNING);
        }
        Sanitize::cleanGlobals();
    }

    public function __destruct()
    {
        if ($this->stream_into_session) {
            $GLOBALS['session']->set('debug_spool', array($this->display(true)));
        } else {
            $this->display();
        }
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * Setup the instance (singleton)
     *
     * @return This instance
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
     * Set a debug message
     *
     * @param string $message
     */
    public function debugMessage($message)
    {
        $this->_messages[] = $message;
    }

    /**
     * Set an SQL message
     *
     * @param string $type
     * @param string $message
     * @return bool
     */
    public function debugSQL($type, $message, $cache, $source)
    {
        if (!$this->_enabled) {
            return false;
        }

        if (!is_null($type) && !is_null($message) && !empty($message)) {
            $tag = '';

            if ($cache && $source) { // Request from cache and taken from cache
                $tag = 'CACHE READ';
                $colour = '008000';
            } elseif ($cache && !$source) { // Request from cache but taken from SQL!
                $tag = 'CACHE WRITE';
                $colour = 'FF6600';
            } elseif (!$cache && $source) { // NOT requested from cache but taken from cache
                $tag = 'CACHE READ - NOT REQUESTED!';
                $colour = 'FF0000';
            } elseif (!$cache && !$source) {
                $tag = 'NOT CACHED';
                $colour = '000';
            }

            if ($type=='error' || preg_match('/^INSERT .*CubeCart_system_error_log/', $message)) {
                $tag = empty($tag) ? 'ERROR' : 'ERROR - '.$tag;
                $colour = 'FF0000';
            }
            $this->_sql[$type][] = '<span style="color:#'.$colour.'">'.htmlentities($message.' ['.$tag.']', ENT_COMPAT, 'UTF-8').'</span>';
            return true;
        }

        return false;
    }

    /**
     * Add a message to the debug tail
     *
     * @param mixed $data
     * @param string $name
     * @return bool
     */
    public function debugTail(&$data, $name = '')
    {
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
     *
     * @param bool $return
     * @param glue $string
     * @return bool
     */
    public function display($return = false, $glue = "\n")
    {
        
        // Cheeky hack for the w3c validator - we don't want it seeing the debug output
        if (strstr($_SERVER['HTTP_USER_AGENT'], 'W3C_Validator')) {
            $this->_enabled = false;
        }

        if ($this->_display && $this->_enabled) {
            $output[] = "<div style='font-family: \"Courier New\",Courier,monospace;font-size: 10px;border-top: 5px dashed silver;color: #000;background-color: #E7E7E7; clear: both'>";

            // Display the PHP errors
            $output[] = '<strong>PHP</strong>:<br />'.htmlspecialchars(strip_tags($this->_errorDisplay())).'<hr size="1" />';

            //Get the super globals
            if (($ret = $this->_makeExportString('GET', merge_array(array('Before Sanitise:' => $GLOBALS['RAW']['GET']), array('After Sanitise:' => $_GET)))) !== false) {
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

                if (!empty($this->_sql['query'])) {
                    $output[] = '<strong>Queries ('.count($this->_sql['query']).')</strong>:<br />';

                    foreach ($this->_sql['query'] as $index => $query) {
                        if (!empty($query)) {
                            $output[] = '[<strong>'.($index + 1).'</strong>] '.$query.'<br />';
                        }
                    }
                }
                if (!empty($this->_sql['error'])) {
                    $output[] = '<strong>Errors</strong>:<br />';
                    foreach ($this->_sql['error'] as $index => $error) {
                        if (!empty($error)) {
                            $sql_error = true;
                            $output[] = '<span style="color: #ff0000">[<strong>'.($index + 1).'</strong>] '.strip_tags($error).'</span><br />';
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
            $cache->status();
            $cacheState = $cache->status ? '<span style="color: #008000">'.$cache->status_desc.'</span>' : '<span style="color: #ff0000">'.$cache->status_desc.'</span>';

            $clear_cache = CC_IN_ADMIN ? '' : '[<a href="'.currentPage(null, array('debug-cache-clear' => 'true')).'">Clear Cache</a>]';
            $output[] = '<strong>Cache ('.$cache->getCacheSystem().'): '.$cacheState.'</strong><br />'.$cache->usage().' '.$clear_cache.'<hr size="1" />';

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
                $has_debug_spool = $GLOBALS['session']->has('debug_spool');
                echo implode(($has_debug_spool) ? $GLOBALS['session']->get('debug_spool') : array()).$content;
                $GLOBALS['session']->set('debug_spool',null);
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
    public function endTimer($name)
    {
        if (isset($this->_timers[$name])) {
            $this->_timers[$name]['end'] = $this->_getTime();
            $this->_timers[$name]['diff'] = $this->_timers[$name]['end'] - $this->_timers[$name]['start'];

            return $this->_timers[$name]['diff'];
        }

        trigger_error('Timer not started ('.$name.')', E_USER_WARNING);

        return 0;
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
    public function errorLogger($error_no, $error_string, $error_file, $error_line, $error_context = null)
    {
        $log = true;
        $can_log = method_exists($GLOBALS['config'], 'get') ? (bool)$GLOBALS['config']->get('config', 'debug') : false;

        switch ($error_no) {
            case E_CORE_ERROR:
                $type = 'Core Error';
            break;
            case E_CORE_WARNING:
                $type = 'Core Warning';
                $log = $can_log;
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
                $log = $can_log;
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
                $log = $can_log;
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $type = 'Warning';
                $log = $can_log;
            break;
            case 'EXCEPTION':
                $type = 'Exception';
            break;
            default:
                $type = 'Unknown ('.$error_no.')';
                if ($error_no == E_DEPRECATED || $error_no == E_USER_DEPRECATED) {
                    $type = 'Deprecated';
                }
                
            break;
        }
        $error = "[<strong>".$type."</strong>] \t".$error_file.":".$error_line." - ".$error_string;
        $this->_errors[] = $error;

        if ($log) {
            $this->_writeErrorLog($error, $type);
        }

        return false;
    }

    /**
     * Error handler
     *
     * @param object $e
     */
    public function exceptionHandler($e)
    {
        $message = "[<strong>Exception</strong>] \t".$e->getFile().":".$e->getLine()." - ".$e->getMessage();
        $this->_errors[] = $message;
        $this->_writeErrorLog($message, 'Exception');
    }

    /**
     * Start a custom timer
     *
     * @param string $name
     */
    public function startTimer($name)
    {
        $this->_timers[$name]['start'] = $this->_getTime();
    }

    /**
     * Get/set the debug status
     *
     * @param bool $status
     * @return bool
     */
    public function status($status = null)
    {
        if (!is_null($status) && is_bool($status)) {
            $this->_enabled = $status;
        }
        return $this->_enabled;
    }

    /**
     * Supress display
     */
    public function supress()
    {
        $this->_display = false;
    }

    //=====[ Private ]=======================================

    /**
     * Get the byte size
     *
     * @param float $input
     * @return string
     */
    private static function _debugGetBytes($input)
    {
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
    private function _debugMemoryUsage($peak = false)
    {
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
    private function _errorDisplay($glue = '<br />')
    {
        if (!empty($this->_errors) && is_array($this->_errors)) {
            return implode($glue, $this->_errors);
        } else {
            return 'No Errors or Warnings';
        }
    }

    /**
     * Get time using microtime or xdebug
     */
    private function _getTime()
    {
        if ($this->_xdebug) {
            return xdebug_time_index();
        }

        return microtime(true);
    }

    /**
     * Make a variable export string
     *
     * @param string $variable
     * @param int $left
     * @return string
     */
    private function _makeExport($variable, $left = 8)
    {
        $output = '';
        foreach ($variable as $key => $value) {
            if ((string)$key == 'debug_spool') continue;
            if (is_array($value)) {
                $output .= '<div style="margin-left: '.$left.'px;">\''.$key.'\' => '.$this->_makeExport($value, ($left + 8)).'</div>';
            } else {
                $output .= '<div style="margin-left: '.$left.'px;">\''.$key.'\' => '.nl2br(htmlspecialchars($value)).'</div>';
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
    private function _makeExportString($name, $variable)
    {
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
    private function _writeErrorLog($message, $type)
    {
        if (isset($GLOBALS['db']) && $GLOBALS['db']->connected) {
            $log_days = method_exists($GLOBALS['config'], 'get') ? $GLOBALS['config']->get('config', 'r_system_error') : 30;
            if (ctype_digit((string)$log_days) &&  $log_days > 0) {
                $GLOBALS['db']->insert('CubeCart_system_error_log', array('message' => $message, 'time' => time()));
                $GLOBALS['db']->delete('CubeCart_system_error_log', 'time < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL '.$log_days.' DAY))');
            } elseif (empty($log_days) || !$log_days) {
                $GLOBALS['db']->insert('CubeCart_system_error_log', array('message' => $message, 'time' => time()));
            }
        } elseif ($type == 'Exception' || $type == E_PARSE) {
            echo $message;
        }
    }
}
