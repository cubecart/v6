<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2015. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:			http://www.cubecart.com
 * Email:		sales@cubecart.com
 * License:		GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

/**
 * Session controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @version 1.0.0
 * @since 5.0.0
 */
class Session {
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
	private $_session_timeout = 3600;
	/**
	 * Is user blocked
	 *
	 * @var bool
	 */
	private $_user_blocked	= false;

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

	final private function __construct() {

		if (session_id()) {
			session_unset();
			session_destroy();
			$_SESSION = array();
		}
		
		//Get all the ini settings to save time later
		$ini = (CC_PHP_ID > 52) ? ini_get_all(null, false) : ini_get_all();

		if ($ini['session.save_handler'] != 'files') {
			//set default sessions save handler
			ini_set('session.save_handler', 'files');
		}

		if ($ini['session.use_trans_sid'] != '0') {
			//disable transparent sid support
			ini_set('session.use_trans_sid', '0');
		}

		if ($ini['session.gc_probability'] != 15) {
			//Clean up 15% of the time
			ini_set('session.gc_probability', 15);
		}
		if ($ini['session.gc_divisor'] != 100) {
			ini_set('session.gc_divisor', 100);
		}
		$cookie_domain = $GLOBALS['config']->get('config', 'cookie_domain');
		if(!empty($cookie_domain) && strstr($GLOBALS['storeURL'],ltrim($cookie_domain,'.'))) {
			ini_set('session.cookie_domain',$cookie_domain);
		}
		if (!$ini['session.cookie_path']) {
			ini_set('session.cookie_path', $GLOBALS['rootRel']);
		}

		ini_set('session.cookie_httponly',true);

		//If the current session time is longer we will not change anything
		if ($ini['session.gc_maxlifetime'] < $this->_session_timeout) {
			ini_set('session.gc_maxlifetime', $this->_session_timeout);
			session_set_cookie_params($this->_session_timeout);
		}
		if (!$ini['session.use_cookies']) {
			//Enforce cookies only
			ini_set('session.use_cookies', true);
		}
		if (!$ini['session.use_only_cookies']) {
			// make sure session is cookie based only
			ini_set('session.use_only_cookies', true);
		}
		if (!$ini['session.cookie_httponly']) {
			// make sure sesison cookies are http ONLY!
			ini_set('session.cookie_httponly',true);
		}
		
		$this->_start();
		$this->_validate();
		$this->_setTimers();
	}

	public function __destruct() {
		//Close this session
		$this->_close();
	}

	/**
	 * Setup the instance (singleton)
	 *
	 * @return Session
	 */
	public static function getInstance() {
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
	public function blocked() {
		return $this->_user_blocked;
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
	public function blocker($user, $user_id, $login = false, $location = false, $attempts = 5, $time = 600) {
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
		$GLOBALS['db']->insert('CubeCart_access_log', $record);
		// Remove expired blocks
		$GLOBALS['db']->delete('CubeCart_blocker', array('last_attempt' => '<='.($now - $time)));

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
			} else if (!$login) {
				// Attempts remaining
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
		} else if (!$login) {
			// Login failed - Create blacklist entry
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
	 *
	 * @return bool
	 */
	public function checkToken($token) {
		return ($this->get('token') == $token);
	}
	
	// Depreciated but left for backward compatibility
	public function cookiesBlocked() {
	
		// Check cookies exists for verified and if so return value
		if(isset($_COOKIE['accept_cookies'])) {
			return false;
		} elseif(!$GLOBALS['config']->get('config', 'cookie_dialogue')) {
			return false;
		}

		$eu_countries = array('AT','BE','BG','CY','CZ','DE','DK','EE','ES','FI','FR','GB','GR','HU','IE','IT','LT','LU','LV','MT','NL','PL','PT','RO','SE','SI','SK');
		$store_country_iso =  getCountryFormat($GLOBALS['config']->get('config', 'store_country'),'numcode', 'iso');
		if(in_array($store_country_iso, $eu_countries)) {
			return true;
		}
		return false;
	}

	/**
	 * Delete something from the session
	 *
	 * @param string $name
	 * @param string $namespace
	 *
	 * @return bool
	 */
	public function delete($name, $namespace = 'system') {
		$namespace = $this->_namespace($namespace);

		//If the session isn't active we don't need to continue
		if( $this->_state != 'active' ) {
			return true;
		}

		if (!isset($_SESSION[$namespace])) {
			return false;
		}

		//If there is not a name
		if (empty($name)) {
			//Remove the entire namespace
			unset($_SESSION[$namespace]);
			return true;
		} else if (isset($_SESSION[$namespace][$name])) {
			//Remove just the element
			unset($_SESSION[$namespace][$name]);
			return true;
		}

		return false;
	}

	/**
	 * Destroy session
	 *
	 * @return bool
	 */
	public function destroy() {
		if ($this->_state == 'destroyed') {
			return true;
		}

		//Delete the session from the DB
		$GLOBALS['db']->delete('CubeCart_sessions', array('session_id' => $this->getId()), false);
		//Completely unset everything
		$_SESSION = array();

		//Kill the cookies
		if (isset($_COOKIE[session_name()])) {
		    $this->set_cookie(session_name(), '', time() - 42000);
			unset($_COOKIE[session_name()]);
		}

		//Destory it
		session_unset();
		session_destroy();

		$this->_state = 'destroyed';

		return true;
	}

	/**
	 * Get data from the session
	 *
	 * If name is empty the entire name space will be returned
	 *
	 * @param unknown_type $name
	 * @param unknown_type $namespace
	 * @param unknown_type $default
	 */
	public function get($name, $namespace = 'system', $default = false) {
		$namespace = $this->_namespace($namespace);

		if($this->_state != 'active' && $this->_state != 'expired') {
			return $default;
		}

		if (isset($_SESSION[$namespace])) {
			if (!empty($name) && isset($_SESSION[$namespace][$name])) {
				return $_SESSION[$namespace][$name];
			} else if (empty($name) && !empty($_SESSION[$namespace])) {
				return $_SESSION[$namespace];
			}
		}

		return $default;
	}

	public function getBack() {
		return $this->get('back');
	}

	/**
	 * Get session id
	 *
	 * @return string The session id
	 */
	public function getId() {
		if ($this->_state == 'destroyed') {
			return null;
		}

		return session_id();
	}

	/**
	 * Get session name
	 *
	 * @return string The session name
	 */
	public function getName() {
		if ($this->_state == 'destroyed') {
			return null;
		}

		return session_name();
	}

	/**
	 * Get the session state
	 *
	 * @return string The session state
	 */
    public function getState() {
		return $this->_state;
	}

	/**
	 * Create a session token to help top spam
	 *
	 * @param bool $new If true, force a new token to be created
	 * @return string The session token
	 */
	public function getToken($new = false) {
		if ((($token = $this->get('token')) === false) || $new) {
			$token = $this->_createToken();
			$this->set('token', $token);
		}

		return $token;
	}

	/**
	 * Does the session have something
	 *
	 * @param string $name
	 * @param string $namespace
	 *
	 * @return bool
	 */
	public function has($name, $namespace = 'system') {
		$namespace = $this->_namespace($namespace);

		if ($this->_state != 'active') {
			return false;
		}

		if (!isset($_SESSION[$namespace])) {
			return false;
		}

		if (empty($name)) {
			return true;
		} else {
			return isset($_SESSION[$namespace][$name]);
		}
	}

	/**
	 * Is an element empty
	 *
	 * @param string $config_name
	 * @param string $element
	 *
	 * @return bool
	 */
	public function isEmpty($name, $namespace) {
		//If the element isn't there then it is empty
		if (!$this->has($name, $namespace)) {
			return true;
		}

		$namespace = $this->_namespace($namespace);

		return empty($_SESSION[$namespace][$name]);
	}

	/**
	 * Set a session value to something
	 *
	 * @param string $name
	 * @param string $value
	 * @param string $namespace
	 *
	 * @return bool
	 */
	public function set($name, $value, $namespace = 'system', $overwrite = false) {
		$namespace = $this->_namespace($namespace);
		if($this->_state != 'active') {
			return true;
		}

		if (is_null($value)) {
			unset($_SESSION[$namespace][$name]);
		} else {
			if (empty($name)) {
				if (!is_array($value)) {
					$_SESSION[$namespace] = $value;
				} else {
					if (isset($_SESSION[$namespace]) && !$overwrite) {
						$_SESSION[$namespace] = merge_array($_SESSION[$namespace], $value);
					} else {
						$_SESSION[$namespace] = $value;
					}
				}
			} else {
				if (!is_array($value)) {
					$_SESSION[$namespace][$name] = $value;
				} else {
					if (isset($_SESSION[$namespace][$name]) && !$overwrite) {
						$_SESSION[$namespace][$name] = merge_array($_SESSION[$namespace][$name], $value);
					} else {
						$_SESSION[$namespace][$name] = $value;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Set a page back to the session
	 */
	public function setBack() {
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
	 *
	 * @return bool
	 */
	public function set_cookie($name, $value, $expire) {
		$params = session_get_cookie_params();
		return setcookie($name, $value, $expire, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
	}

	//=====[ Private ]=======================================
	
	/**
	 * User agent
	 */
	private function _http_user_agent() {
		return strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') ? 'IEX' : $_SERVER['HTTP_USER_AGENT'];
	}
	
	/**
	 * Close a session
	 */
	private function _close() {
		if ($this->_state == 'closed') {
			return true;
		}

		$record = array(
			'location'		=> currentPage(),
			'session_last'	=> $this->get('session_last', 'client', '')
		);
		
		//Use the instance because the global might be gone already
		Database::getInstance()->update('CubeCart_sessions', $record, array('session_id' => $this->getId()), false);
		// Tidy Access Logs keep months worth
		Database::getInstance()->delete('CubeCart_access_log', array('time' => '<'.(time()-(3600*24*7*4))));
		// Purge sessions older than the session time out
		Database::getInstance()->delete('CubeCart_sessions', array('session_last' => '<='.(time() - $this->_session_timeout)), false);

		$this->_state = 'closed';

		session_write_close();

		return true;
	}

	/**
	 * Create a form token
	 *
	 * @return string
	 */
	private function _createToken() {
		return md5(session_name().time().mt_rand(0, mt_getrandmax()));
	}

	/**
	 * Check & build the namespace
	 *
	 * @param string $namespace
	 *
	 * @return string
	 */
	private function _namespace($namespace) {
		if ($namespace[0] == '_') {
			trigger_error('Session namespace cannot start with _', E_USER_ERROR);
		}

		return '__'.$namespace;
	}

	/**
	 * Setup session timers
	 */
	private function _setTimers() {
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
	 * Start session
	 */
	private function _start() {

		$save_path = session_save_path();
		if(!file_exists($save_path) || !is_writeable($save_path)) {	
			if(is_writeable(CC_INCLUDES_DIR.'/extra')) {
				session_save_path(CC_INCLUDES_DIR.'/extra');
			} else {
				die("Error: Failed to create PHP session. It may be possible to fix this by following these steps:
			<ol>
				<li>Create a folder in the root directory of your store called &quot;sessions&quot;</li>
				<li>Create a file in the root folder of your store called &quot;ini-custom.inc.php&quot;</li>
				<li>Add the following code to this file and save the changes.
				<pre>".htmlspecialchars("<?php
session_save_path(CC_ROOT_DIR.'/sessions');")."</pre>
				</li>
			</ol>Once that has been done refresh this page.");
			}
			
		}
		session_cache_limiter('none');
		session_start();
		
		// Increase session length on each page load. NOT IE however as we all know it is a wingy PITA
		if($this->_http_user_agent()!=='IEX') {
			$this->set_cookie(session_name(),session_id(),time()+$this->_session_timeout);
		}
		
	}

	/**
	 * Validate session
	 *
	 * @param bool $restart
	 */
	private function _validate() {
		$ip = get_ip_address();

		if (($current = $GLOBALS['db']->select('CubeCart_sessions', false, array('session_id' => $this->getId()), false, 1, false, false)) === false) {
			$record = array(
				'admin_id'		=> 0,
				'customer_id'	=> 0,
				'ip_address'	=> $ip,
				'location'		=> '',
				'session_id'	=> $this->getId(),
				'session_last'	=> time(),
				'session_start'	=> time(),
				'useragent'		=> $this->_http_user_agent()
			);
			$GLOBALS['db']->insert('CubeCart_sessions', $record, false);
			$this->set('ip_address', $ip, 'client');
			$this->set('useragent', $this->_http_user_agent(), 'client');
		} else {
			$this->session_data = $current[0];
			if ($current[0]['useragent'] !== $this->_http_user_agent()) {
				$this->destroy();
				trigger_error('Stored session data did not match DB record. Session aborted as possible session hijack. Old IP Address: \''.$current[0]['ip_address'].'\' New IP Address: \''.$ip.'\' Old User Agent: \''.$current[0]['useragent'].'\' New User Agent: \''.$this->_http_user_agent().'\'', E_USER_WARNING);
			}
			$this->set('ip_address', $current[0]['ip_address'], 'client');
			$this->set('useragent', $current[0]['useragent'], 'client');
		}
	}
}
