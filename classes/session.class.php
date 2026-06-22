<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:			https://www.cubecart.com
 * Email:		hello@cubecart.com
 * License:		GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

/**
 * Session controller
 *
 * Direct database-backed sessions — no PHP session_start().
 * Session ID stored in cc_session cookie, data in CubeCart_sessions table.
 */
class Session
{
    /**
     * Session ID
     *
     * @var string|null
     */
    private $_session_id = null;
    /**
     * Session cookie/name
     *
     * @var string
     */
    private $_session_name = 'cc_session';
    /**
     * Session data (replaces $_SESSION)
     *
     * @var array
     */
    private $_data = array();
    /**
     * Hash of data at load time for dirty detection
     *
     * @var string|null
     */
    private $_data_hash = null;
    /**
     * Current session status
     *
     * @var string
     */
    private $_state	= 'active';
    /**
     * Session timeout
     *
     * @var int
     */
    private $_session_timeout = 172800; // 2 days (guest default)
    /**
     * Session path
     *
     * @var string
     */
    private $_session_path = '';
    /**
     * Session domain
     *
     * @var string
     */
    private $_session_domain = '';
    /**
     * Session token name
     *
     * @var string
     */
    private $_token_name = 'token';
    /**
     * Is user blocked
     *
     * @var bool
     */
    private $_user_blocked	= false;
    /**
     * Cookie-backed preference keys: namespace => array(name => cookie_name)
     *
     * @var array
     */
    private static $_cookie_prefs = array(
        'client' => array(
            'currency' => 'cc_currency',
            'language' => 'cc_language',
            'skin'     => 'cc_skin',
            'style'    => 'cc_style',
        ),
    );
    /**
     * Default bot UA substrings used by Layer 2 of _isBot().
     * Override at runtime via $glob['bot_sigs'] in includes/global.inc.php.
     *
     * @var string[]
     */
    private static $_bot_sigs_default = array(
        'bot', 'crawl', 'spider', 'slurp',           // generic patterns
        'headless', 'phantom', 'puppeteer',          // headless browsers
        'lighthouse', 'pagespeed', 'gtmetrix',       // performance testing
        'pingdom', 'uptimerobot', 'statuscake',      // uptime monitors
        'semrush', 'ahrefs', 'majestic', 'dotbot',   // SEO tools
        'facebookexternal',                          // social media
        'perplexity-user', 'claude-web', 'cohere-ai',// AI on-demand fetchers without bot/crawl tokens
    );

    const BLOCKER_FRONTEND	= 'F';
    const BLOCKER_BACKEND	= 'B';

    /**
     * Class instance
     *
     * @var instance
     */
    private static $_instance;

    /**
     * Current session data
     *
     * @var array
     */
    public $session_data = array();

    ##############################################

    final private function __construct()
    {
        if (CC_IN_ADMIN) {
            $this->_token_name = 'token_acp';
        }

        $cookie_domain = ltrim($GLOBALS['config']->get('config', 'cookie_domain'), '.');
        if (!empty($cookie_domain) && strstr($GLOBALS['storeURL'], $cookie_domain) && strpos($cookie_domain, '.')) {
            $this->_session_domain = '.'.$cookie_domain;
        }
        $this->_session_path = $GLOBALS['rootRel'] == '/' ? $GLOBALS['rootRel'] : substr($GLOBALS['rootRel'],0,-1);

        // Three-layer bot protection: skip session entirely for detected bots
        if (!CC_IN_ADMIN && ($this->_isBot() || !isset($_COOKIE['cc_browser']))) {
            $this->_state = 'destroyed';
            return;
        }

        $this->_start();

        // Extend session to 7 days for logged-in customers/admins
        if (!empty($this->session_data['customer_id']) || !empty($this->session_data['admin_id'])) {
            $this->_session_timeout = 604800; // 7 days
            $this->set_cookie($this->_session_name, $this->_session_id, time() + $this->_session_timeout);
        }

        $this->_setTimers();

        // Write session data during shutdown, BEFORE object destruction.
        // This ensures Cart::__destruct()->save() etc. can still write to the
        // session because _close() runs after all user code but before PHP
        // tears down objects. Mirrors what session_set_save_handler(,true) did.
        register_shutdown_function(array($this, 'closeOnShutdown'));
    }

    /**
     * Shutdown callback — write session to DB before object destruction
     */
    public function closeOnShutdown()
    {
        $this->_close();
    }

    public function __destruct()
    {
        //Close this session (no-op if already closed by shutdown function)
        $this->_close();
    }

    /**
     * Setup the instance (singleton)
     *
     * @return Session
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
     * Is a user blocked
     *
     * @return bool
     */
    public function blocked()
    {
        return $this->_user_blocked;
    }

    /**
     * Backward-compatibility stub. Cookie-blocked detection was removed; always returns false.
     *
     * @return bool
     */
    public function cookiesBlocked()
    {
        return false;
    }

    /**
     * Block a user
     *
     * @param string $user
     * @param bool $login
     * @param string $location
     * @param int $attempts
     * @param int $time
     */
    public function blocker($user, $user_id, $login = false, $location = false, $attempts = 5, $time = 600)
    {
        $now = time();
        // Access Log
        $record	= array(
            'type'		=> $location,
            'time'		=> $now,
            'username'	=> (!empty($user)) ? $user : '--',
            'user_id'   => $user_id,
            'ip_address'=> get_ip_address(),
            'useragent' => $this->_http_user_agent(),
            'success'	=> ($login) ? 'Y' : 'N',
        );
        $log_days = $GLOBALS['config']->get('config', 'r_staff');
        if (ctype_digit((string)$log_days) &&  $log_days > 0) {
            $GLOBALS['db']->insert('CubeCart_access_log', $record);
            $GLOBALS['db']->delete('CubeCart_access_log', 'time < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL '.$log_days.' DAY))');
        } elseif (empty($log_days) || !$log_days) {
            $GLOBALS['db']->insert('CubeCart_access_log', $record);
        }
        // Remove expired blocks
        $GLOBALS['db']->delete('CubeCart_blocker', array('last_attempt' => '<='.($now - $time)), 500);

        // Search for active blocks
        $where = array(
            'user_agent'	=> $this->_http_user_agent(),
            'ip_address'	=> get_ip_address(),
            'location'		=> $location,
        );
        $blacklist = $GLOBALS['db']->select('CubeCart_blocker', array('block_id', 'ban_expires', 'last_attempt', 'level'), $where);
        if ($blacklist) {
            $blocked = $blacklist[0];
            if ((int)$blocked['level'] == (int)$attempts) {
                // Ban level reached
                if ((int)$blocked['ban_expires'] <= $now) {
                    // Ban expired - Allowed
                    $GLOBALS['db']->delete('CubeCart_blocker', array('block_id' => $blocked['block_id']));
                } else {
                    // Still banned - Denied
                    $this->_user_blocked = true;
                }
            } elseif (!$login) {
                // Attempts remaining
                $record	= array(
                    'last_attempt'	=> $now,
                    'level'			=> ($blocked['last_attempt'] <= ($now - $time)) ? 1 : $blocked['level'] + 1,
                );
                if ($record['level'] == $attempts) {
                    // Blocked
                    $record['ban_expires'] = ($now+$time);
                    $this->_user_blocked = true;
                }
                $GLOBALS['db']->update('CubeCart_blocker', $record, array('block_id' => $blocked['block_id']));
            }
        } elseif (!$login) {
            // Login failed - Create blacklist entry
            $record	= array(
                'level'			=> 1,
                'last_attempt'	=> $now,
                'ban_expires'	=> 0,
                'username'		=> strip_tags($user),
                'location'		=> $location,
                'user_agent'	=> $this->_http_user_agent(),
                'ip_address'	=> get_ip_address(),
            );
            $GLOBALS['db']->insert('CubeCart_blocker', $record);
        }
        return (bool)$this->_user_blocked;
    }

    /**
     * Check a form token
     *
     * @param string $token
     * @return bool
     */
    public function checkToken($token)
    {
        // Continue without error if no security token is set
        if (!$this->get($this->_token_name)) {
            return true;
        }
        return ($this->get($this->_token_name) == $token);
    }

    /**
     * Delete something from the session
     *
     * @param string $name
     * @param string $namespace
     * @return bool
     */
    public function delete($name, $namespace = 'system')
    {
        if ($this->_isCookiePref($name, $namespace)) {
            $cookie = self::$_cookie_prefs[$namespace][$name];
            $this->set_cookie($cookie, '', time() - 42000, array('httponly' => false));
            unset($_COOKIE[$cookie]);
            return true;
        }

        $namespace = $this->_namespace($namespace);

        //If the session isn't active we don't need to continue
        if ($this->_state != 'active') {
            return true;
        }

        if (!isset($this->_data[$namespace])) {
            return false;
        }

        //If there is not a name
        if (empty($name)) {
            //Remove the entire namespace
            unset($this->_data[$namespace]);
            return true;
        } elseif (isset($this->_data[$namespace][$name])) {
            //Remove just the element
            unset($this->_data[$namespace][$name]);
            return true;
        }

        return false;
    }

    /**
     * Destroy session
     *
     * @return bool
     */
    public function destroy()
    {
        if ($this->_state == 'destroyed') {
            return true;
        }

        //Delete the session from the DB
        $GLOBALS['db']->delete('CubeCart_sessions', array('session_id' => $this->_session_id), false);
        //Completely unset everything
        $this->_data = array();

        //Kill the cookies
        if (isset($_COOKIE[$this->_session_name])) {
            $this->set_cookie($this->_session_name, '', time() - 42000);
            unset($_COOKIE[$this->_session_name]);
        }

        $this->_state = 'destroyed';

        return true;
    }

    /**
     * Get data from the session
     *
     * If name is empty the entire name space will be returned
     *
     * @param string $name
     * @param string $namespace
     * @param string $default
     */
    public function get($name, $namespace = 'system', $default = false)
    {
        if ($this->_isCookiePref($name, $namespace)) {
            $cookie = self::$_cookie_prefs[$namespace][$name];
            return isset($_COOKIE[$cookie]) && $_COOKIE[$cookie] !== '' ? $_COOKIE[$cookie] : $default;
        }

        $namespace = $this->_namespace($namespace);

        if ($this->_state != 'active' && $this->_state != 'expired') {
            return $default;
        }

        if (isset($this->_data[$namespace])) {
            if (!empty($name) && isset($this->_data[$namespace][$name])) {
                return $this->_data[$namespace][$name];
            } elseif (empty($name) && !empty($this->_data[$namespace])) {
                return $this->_data[$namespace];
            }
        }

        return $default;
    }

    /**
     * Get all session data (for debug output)
     *
     * @return array
     */
    public function getAllData()
    {
        return $this->_data;
    }

    /**
     * Get session id
     *
     * @return string
     */
    public function getId()
    {
        if ($this->_state == 'destroyed') {
            return null;
        }

        return $this->_session_id;
    }

    /**
     * Get session name
     *
     * @return string The session name
     */
    public function getName()
    {
        if ($this->_state == 'destroyed') {
            return null;
        }

        return $this->_session_name;
    }

    /**
     * Get the session state
     *
     * @return string
     */
    public function getState()
    {
        return $this->_state;
    }

    /**
     * Get session data from database
     *
     * @return false/array/string
     */
    public function getSessionTableData($column = false)
    {
        $data = $GLOBALS['db']->select('CubeCart_sessions', $column, array('session_id' => $this->_session_id), false, 1, false, false);
        if (is_array($data)) {
            if (count($data[0])==1 && is_string($column)) {
                return $data[0][$column];
            } else {
                return $data[0];
            }
        }
        return false;
    }

    /**
     * Create a session token to help prevent CSRF
     *
     * @param bool $new If true, force a new token to be created
     * @return string The session token
     */
    public function getToken($new = false)
    {
        if ((($token = $this->get($this->_token_name)) === false) || $new) {
            $token = $this->_createToken();
            $this->set($this->_token_name, $token);
        }

        return $token;
    }

    /**
     * Does the session have something
     *
     * @param string $name
     * @param string $namespace
     * @return bool
     */
    public function has($name, $namespace = 'system')
    {
        if ($this->_isCookiePref($name, $namespace)) {
            $cookie = self::$_cookie_prefs[$namespace][$name];
            return isset($_COOKIE[$cookie]) && $_COOKIE[$cookie] !== '';
        }

        $namespace = $this->_namespace($namespace);

        if ($this->_state != 'active') {
            return false;
        }

        if (!isset($this->_data[$namespace])) {
            return false;
        }

        if (empty($name)) {
            return true;
        } else {
            return isset($this->_data[$namespace][$name]);
        }
    }

    /**
     * Is an element empty
     *
     * @param string $config_name
     * @param string $element
     * @return bool
     */
    public function isEmpty($name, $namespace)
    {
        if ($this->_isCookiePref($name, $namespace)) {
            $cookie = self::$_cookie_prefs[$namespace][$name];
            return !isset($_COOKIE[$cookie]) || empty($_COOKIE[$cookie]);
        }

        //If the element isn't there then it is empty
        if (!$this->has($name, $namespace)) {
            return true;
        }

        $namespace = $this->_namespace($namespace);

        return empty($this->_data[$namespace][$name]);
    }

    /**
     * Get the session cookie path
     *
     * @return string
     */
    public function getCookiePath()
    {
        return $this->_session_path;
    }

    /**
     * Get the session cookie domain
     *
     * @return string
     */
    public function getCookieDomain()
    {
        return $this->_session_domain;
    }

    public function regenerateSessionId() {
        $old_id = $this->_session_id;
        $this->_session_id = $this->_generateSessionId();
        Database::getInstance()->update('CubeCart_sessions', array('session_id' => $this->_session_id), array('session_id' => $old_id), false);
        $this->set_cookie($this->_session_name, $this->_session_id, time()+$this->_session_timeout);
    }

    /**
     * Set a session value to something
     *
     * @param string $name
     * @param string $value
     * @param string $namespace
     * @param bool $overwrite
     * @return bool
     */
    public function set($name, $value, $namespace = 'system', $overwrite = false)
    {
        if ($this->_isCookiePref($name, $namespace)) {
            $cookie = self::$_cookie_prefs[$namespace][$name];
            if (is_null($value)) {
                $this->set_cookie($cookie, '', time() - 42000, array('httponly' => false));
                unset($_COOKIE[$cookie]);
            } else {
                $_COOKIE[$cookie] = $value;
                $this->set_cookie($cookie, $value, time() + 31536000, array('httponly' => false)); // 1 year
            }
            return true;
        }

        $namespace = $this->_namespace($namespace);
        if ($this->_state != 'active') {
            return true;
        }

        if (is_null($value)) {
            unset($this->_data[$namespace][$name]);
        } else {
            if (empty($name)) {
                if (!is_array($value)) {
                    $this->_data[$namespace] = $value;
                } else {
                    if (isset($this->_data[$namespace]) && !$overwrite) {
                        $this->_data[$namespace] = merge_array($this->_data[$namespace], $value);
                    } else {
                        $this->_data[$namespace] = $value;
                    }
                }
            } else {
                if (!is_array($value)) {
                    $this->_data[$namespace][$name] = $value;
                } else {
                    if (isset($this->_data[$namespace][$name]) && !$overwrite) {
                        $this->_data[$namespace][$name] = merge_array($this->_data[$namespace][$name], $value);
                    } else {
                        $this->_data[$namespace][$name] = $value;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Set a page back to the session
     */
    public function setBack()
    {
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            //Make sure the referer is local and not the login
            if (substr($_SERVER['HTTP_REFERER'], 0, strlen(CC_STORE_URL)) == CC_STORE_URL && $_SERVER['HTTP_REFERER'] != CC_STORE_URL.'index.php?_a=login') {
                $this->set('back', $_SERVER['HTTP_REFERER']);
            }
        }
    }

    /**
     * Set cookie
     *
     * @param string $name
     * @param string $value
     * @param integer $expire
     * @return bool
     */
    public function set_cookie($name, $value, $expires = false, $options = array())
    {
        $params = array(
            'samesite' => 'None',
            'httponly'  => true,
            'secure'   => true,
            'path'     => $this->_session_path,
            'domain'   => $this->_session_domain,
        );
        $params = array_merge($params, $options); // Allow overwrite for specific cookies

        $date = new Datetime();
        $date->setTimestamp($expires);
        $attributes = '';
        $attributes .= ($expires !== false) ? ';Expires='.$date->format(DateTime::COOKIE) : '';
        if (!empty($this->_session_domain)) {
            $attributes .= ';Domain='.$this->_session_domain;
        }
        $attributes .= ';Path='.$this->_session_path;
        $attributes .= ';SameSite='.$params['samesite'];
        $attributes .= ';Secure';

        if($params['httponly']) {
            $attributes .= ';HttpOnly';
        }
        // Ref: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie
        header('Set-Cookie: '.$name.'='.$value.$attributes, false);
        $GLOBALS['SENT_COOKIES'][] = "header('Set-Cookie: '".$name.'='.$value.$attributes; // phrased to show how sent
    }

    //=====[ Private ]=======================================

    /**
     * Close a session — serialize data and write to DB
     *
     * @return true
     */
    private function _close()
    {
        if ($this->_state == 'closed' || $this->_state == 'destroyed') {
            return true;
        }

        $cp = str_replace($GLOBALS['storeURL'].'/','',currentPage());

        $record = array(
            'location' => $cp . (strpos($cp,"_a=404")!==false ? "<br /><strike>".$_SERVER['REQUEST_URI']."</strike>" : ""),
            'session_last'	=> $this->get('session_last', 'client', ''),
            'acp'		=> ADMIN_CP
        );

        // Only write session_data if it changed
        $current_hash = md5(serialize($this->_data));
        if ($current_hash !== $this->_data_hash) {
            $record['session_data'] = serialize($this->_data);
        }

        //Use the instance because the global might be gone already
        Database::getInstance()->update('CubeCart_sessions', $record, array('session_id' => $this->_session_id), false);
        if (executionChance(2)) {  // 2% probability
            // Tidy Access Logs keep months worth
            Database::getInstance()->delete('CubeCart_access_log', array('time' => '<'.(time()-(3600*24*7*4))), 500);
            // Purge sessions older than 7 days (the longest possible session lifetime)
            Database::getInstance()->delete('CubeCart_sessions', array('session_last' => '<='.(time() - 604800)), 500);
        }

        $this->_state = 'closed';

        return true;
    }

    /**
     * Create a form token (CSPRNG)
     *
     * @return string
     */
    private function _createToken()
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Generate a cryptographically secure session ID
     *
     * @return string 64 hex characters (256 bits)
     */
    private function _generateSessionId()
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * User agent
     *
     * @return string
     */
    private function _http_user_agent()
    {
        return strpos(($_SERVER['HTTP_USER_AGENT'] ?? "Not Available"), 'Trident') ? 'IEX' : htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? "Not Available");
    }

    /**
     * Check if the user agent matches a known bot signature
     *
     * Three-layer protection:
     * Layer 1 — Whitelist: Empty UA or no mozilla/ = bot (kills curl, wget, python, etc.)
     * Layer 2 — Blacklist: Known bots that spoof browser UAs
     * Layer 3 — CCB cookie: checked in constructor (cc_browser cookie set by JS)
     *
     * @return bool
     */
    private function _isBot()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

        // Layer 1: Must look like a real browser (all include 'mozilla/')
        if (empty($agent) || strpos($agent, 'mozilla/') === false) {
            return true;
        }

        // Layer 2: Block known bots that spoof full browser UAs
        foreach (self::botSignatures() as $sig) {
            if (strpos($agent, $sig) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Bot UA substrings used by Layer 2 detection.
     * Returns the $glob['bot_sigs'] override when set, otherwise the
     * built-in default in self::$_bot_sigs_default. Shared with the admin
     * Users Online report so per-row labels and SQL-side bucket counts
     * stay in sync.
     *
     * @return string[]
     */
    public static function botSignatures()
    {
        if (!empty($GLOBALS['glob']['bot_sigs']) && is_array($GLOBALS['glob']['bot_sigs'])) {
            return array_values(array_filter(array_map(function ($s) {
                return strtolower(trim((string)$s));
            }, $GLOBALS['glob']['bot_sigs']), 'strlen'));
        }
        return self::$_bot_sigs_default;
    }

    /**
     * Check if a key is a cookie-backed preference
     *
     * @param string $name
     * @param string $namespace
     * @return bool
     */
    private function _isCookiePref($name, $namespace)
    {
        return !empty($name) && isset(self::$_cookie_prefs[$namespace][$name]);
    }

    /**
     * Check & build the namespace
     *
     * @param string $namespace
     * @return string
     */
    private function _namespace($namespace)
    {
        if ($namespace[0] == '_') {
            trigger_error('Session namespace cannot start with _', E_USER_ERROR);
        }

        return '__'.$namespace;
    }

    /**
     * Setup session timers
     */
    private function _setTimers()
    {
        if (!$this->has('session_start', 'client')) {
            $start = time();
            $this->set('session_start', $start, 'client');
            $this->set('session_last', $start, 'client');
        } else {
            $this->set('session_start', $this->get('session_last', 'client'), 'client');
            $this->set('session_last', time(), 'client');
        }
    }

    /**
     * Start session — read cookie, validate, load from DB
     */
    private function _start()
    {
        // Cache control headers (replaces session_cache_limiter('nocache'))
        header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        // Read and validate session ID from cookie
        $cookie_id = $_COOKIE[$this->_session_name] ?? null;
        if ($cookie_id !== null && !preg_match('/^[a-f0-9]{26,64}$/', $cookie_id)) {
            $cookie_id = null; // invalid format, treat as new session
        }

        $ip = get_ip_address();

        if ($cookie_id !== null) {
            $current = $GLOBALS['db']->select('CubeCart_sessions', false, array('session_id' => $cookie_id), false, 1, false, false);
        } else {
            $current = false;
        }

        // Back office idle timeout. Defaults to 15 minutes; override with
        // $glob['admin_idle_timeout'] (seconds) in includes/global.inc.php.
        $admin_idle_timeout = (isset($GLOBALS['glob']['admin_idle_timeout']) && (int)$GLOBALS['glob']['admin_idle_timeout'] > 0)
            ? (int)$GLOBALS['glob']['admin_idle_timeout']
            : 900;
        if ($current !== false && CC_IN_ADMIN && !empty($current[0]['admin_id']) && (time() - (int)$current[0]['session_last']) > $admin_idle_timeout) {
            $GLOBALS['db']->update('CubeCart_admin_users', array('session_id' => ''), array('admin_id' => (int)$current[0]['admin_id']));
            $GLOBALS['db']->delete('CubeCart_sessions', array('session_id' => $cookie_id), false);
            $current = false;
        }

        if ($current !== false) {
            // Existing session found
            $this->_session_id = $cookie_id;
            $this->session_data = $current[0];

            // Load session data
            if (!empty($current[0]['session_data'])) {
                $decoded = @unserialize($current[0]['session_data'], ['allowed_classes' => false]);
                if (is_array($decoded)) {
                    $this->_data = $decoded;
                } else {
                    // Migration: try PHP session format for old sessions
                    $this->_data = $this->_decodePhpSession($current[0]['session_data']);
                }
            }

            $this->set('ip_address', $current[0]['ip_address'], 'client');
            $this->set('useragent', $current[0]['useragent'], 'client');
        } else {
            // New session
            $this->_session_id = $this->_generateSessionId();
            $this->_data = array();

            $record = array(
                'admin_id'		=> 0,
                'customer_id'	=> 0,
                'ip_address'	=> $ip,
                'location'		=> '',
                'session_id'	=> $this->_session_id,
                'session_last'	=> time(),
                'session_start'	=> time(),
                'useragent'		=> $this->_http_user_agent(),
                'acp'		=> ADMIN_CP
            );
            $GLOBALS['db']->insert('CubeCart_sessions', $record, false);
            $this->set('ip_address', $ip, 'client');
            $this->set('useragent', $this->_http_user_agent(), 'client');
        }

        // Snapshot for dirty detection
        $this->_data_hash = md5(serialize($this->_data));

        // Send/refresh session cookie
        $this->set_cookie($this->_session_name, $this->_session_id, time()+$this->_session_timeout);
    }

    /**
     * Decode old PHP session serialization format for migration
     *
     * @param string $data PHP session serialized string
     * @return array
     */
    private function _decodePhpSession($data)
    {
        $result = array();
        $offset = 0;
        $len = strlen($data);
        while ($offset < $len) {
            $pipe = strpos($data, '|', $offset);
            if ($pipe === false) break;
            $key = substr($data, $offset, $pipe - $offset);
            $offset = $pipe + 1;
            $value = @unserialize(substr($data, $offset), ['allowed_classes' => true]);
            if ($value === false && substr($data, $offset, 2) !== 'b:0;') {
                break;
            }
            $result[$key] = $value;
            // Advance past the serialized value
            $serialized = serialize($value);
            $offset += strlen($serialized);
        }
        return $result;
    }
}
