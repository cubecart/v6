<?php
/**
 * CubeCart v5
 * ========================================
 * CubeCart is a registered trade mark of Devellion Limited
 * Copyright Devellion Limited 2010. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  http://www.cubecart.com/v5-software-license
 * ========================================
 * CubeCart is NOT Open Source.
 * Unauthorized reproduction is not allowed.
 */
/**
 * Configuration controller
 *
 * @version 1.0.0
 * @since 5.0.0
 */
class SSL {

	/**
	 * SSL enabled pages
	 * @var array
	 */
	private $_ssl_pages  = array();
	/**
	 * Class instance
	 *
	 * @var instance
	 */
	protected static $_instance;

	public function __construct() {

		$ssl_url = $GLOBALS['config']->get('config', 'ssl_url');
		if (empty($ssl_url)) {
			//trigger_error('SSL URL has not been defined. Cannot enable SSL mode.', E_USER_NOTICE);
			$GLOBALS['config']->merge('config', 'ssl', false);
		}

		$ssl_path = $GLOBALS['config']->get('config', 'ssl_path');
		if (empty($ssl_path)) {
			//trigger_error('SSL Root Relative Path has not been defined. Cannot enable SSL mode.', E_USER_NOTICE);
			$GLOBALS['config']->merge('config', 'ssl', false);
		}
		if (ADMIN_CP) {
			if (isset($_GET['ssl_switch']) && $_GET['ssl_switch']) {
				$this->_sslSwitch('on');
			} elseif (isset($_GET['ssl_switch']) && !$_GET['ssl_switch']) {
				$this->_sslSwitch('off');
			}
			$GLOBALS['storeURL'] = CC_STORE_URL;
			$GLOBALS['rootRel']  = CC_ROOT_REL;

		} elseif ($GLOBALS['config']->get('config', 'ssl')) {
			// Define a list of pages that should always be in SSL mode
			$this->_ssl_pages = array(
				// Cart
				'basket'  => true,
				'cart'   => true,
				'checkout'  => true,
				'complete'  => true,
				'confirm'  => true,
				'gateway'  => true,
				'remote'  => true,
				'template'  => true,

				// User Related
				'login'   => true,
				'logout'  => true,
				'recover'  => true,
				'recovery'  => true,
				'register'  => true,
				'contact'  => true,

				'account'  => true,
				'addressbook' => true,
				'downloads'  => true,
				'download'  => true,
				'profile'  => true,
				'vieworder'  => true,
				'receipt'  => true,
			);

			foreach ($GLOBALS['hooks']->load('class.ssl.pages') as $hook) include $hook;

			// Switch to SSL, if necessary
			$this->_sslSwitch();
		} else {

			// Defaulted
			$GLOBALS['storeURL'] = CC_STORE_URL;
			$GLOBALS['rootRel']  = CC_ROOT_REL;
		}
	}

	/**
	 * Setup the instance (singleton)
	 *
	 * @return SSL
	 */
	public static function getInstance() {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Define a custom page to use SSL
	 *
	 * @param array/string $input
	 * @param bool $secure
	 */
	public function defineSecurePage($input = null, $secure = true) {
		if (!is_null($input)) {
			if (is_array($input)) {
				foreach ($input as $section) {
					$this->_ssl_pages[$section] = (!isset($this->_ssl_pages[$section])) ? $secure : true;
				}
			} else {
				$this->_ssl_pages[$input] = (!isset($this->_ssl_pages[$input])) ? $secure : true;
			}
			$this->_sslSwitch();
		}
		return false;
	}

	/**
	 * Force SSL
	 */
	public function sslForce($default = true) {
		// Force the current page into SSL mode
		$this->_sslSwitch($default);
	}

	/**
	 * Switch to SSL
	 *
	 * @param bool $force
	 */
	private function _sslSwitch($force = false) {
		if ($GLOBALS['config']->get('config', 'ssl') && $_GET['_g']!=='remote' &&  $_GET['_g']!=='rm') { // NEVER switch if a remote call is made
			if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
				parse_str($_SERVER['QUERY_STRING'], $params);
			}
			if (isset($params['SSL'])) {
				$hash  = $params['SSL'];
				$compare = $params;
				unset($compare['SSL'], $compare[session_name()]);
				$validate = md5(serialize($compare));
				$force_val = is_string($force) ? $force : true;
				$force  = ($hash === $validate) ? $force_val : false;
			}

			if (isset($GLOBALS['seo']->_a) && !empty($GLOBALS['seo']->_a)) {
				$current_mode = $GLOBALS['seo']->_a;
			} else {
				$current_mode = (isset($_GET['_a'])) ? $_GET['_a'] : '';
			}

			if (is_string($force)) {
				$enable_ssl = ($force=='off') ? false : true;
			} else {
				$enable_ssl  = ($force || $GLOBALS['config']->get('config', 'ssl_force') || (isset($this->_ssl_pages[$current_mode]) && $this->_ssl_pages[$current_mode] == true)) ? true : false;
			}

			// Fix for remote calls! This STOPS redirect from SSL to standard protocol if a call is to SSL.
			if ($_GET['_g']=='rm' && CC_SSL) {
				$enable_ssl = true;
			}

			if ($enable_ssl && !CC_SSL) {
				// Switch into SSL mode
				$page = $GLOBALS['config']->get('config', 'ssl_url').str_replace(CC_ROOT_REL, '/', $_SERVER['PHP_SELF']);
			} else if (!$enable_ssl && CC_SSL) {
				// Switch out of SSL mode
				$page = $GLOBALS['config']->get('config', 'standard_url').str_replace($GLOBALS['config']->get('config', 'ssl_path'), '/', $_SERVER['PHP_SELF']);
			}

			if (isset($page)) {
				if ($force) {
					$params['SSL'] = md5(serialize($params));
				}
				/* Depreciated for security reasons
				if($GLOBALS['config']->get('config', 'ssl')==1) {
					$ssl_url 		= str_replace('https','',$GLOBALS['config']->get('config', 'ssl_url'));
					$standard_url 	= str_replace('http','',$GLOBALS['config']->get('config', 'standard_url'));
					if ($ssl_url!==$standard_url) {
						$params[session_name()] = session_id();
					}
				}
				*/
				unset($params['ssl_switch']);
				if (!empty($params)) {
					$page .= '?'.http_build_query($params, false, '&');
				}

				if (preg_match('/seo_path/', $page)) {
					$URL = SEO::getInstance()->getItem($params['seo_path'], true);
					$page = str_ireplace($GLOBALS['config']->get('config', 'ssl_url'), $GLOBALS['config']->get('config', 'standard_url'), SEO::getInstance()->SEOable($URL));
				}

				httpredir($page);
			} else {
				return false;
			}
		}

		// Get/Set paths and directories

		if ($GLOBALS['config']->get('config', 'ssl') && CC_SSL) {
			$GLOBALS['storeURL'] = $GLOBALS['config']->get('config', 'ssl_url');
			$GLOBALS['rootRel']  = $GLOBALS['config']->get('config', 'ssl_path');
		} else {
			$GLOBALS['storeURL'] = CC_STORE_URL;
			$GLOBALS['rootRel']  = CC_ROOT_REL;
		}
		// Make $GLOBALS paths fool-proof... until someone invents a better fool...
		if (substr($GLOBALS['storeURL'], -1, 1) == '/') $GLOBALS['storeURL'] = substr($GLOBALS['storeURL'], 0, strlen($GLOBALS['storeURL'])-1);
		if (substr($GLOBALS['rootRel'], -1, 1) !== '/') $GLOBALS['rootRel'] = $GLOBALS['rootRel'].'/';
	}
}