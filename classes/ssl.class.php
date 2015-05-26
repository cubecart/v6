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

/**
 * Configuration controller
 *
 * @since 5.0.0
 */
class SSL {

	/**
	 * Class instance
	 *
	 * @var instance
	 */
	protected static $_instance;

	##############################################

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
				$this->_sslSwitch();
			} elseif (isset($_GET['ssl_switch']) && !$_GET['ssl_switch']) {
				$this->_sslSwitch('off');
			}
			$GLOBALS['storeURL'] = CC_STORE_URL;
			$GLOBALS['rootRel']  = CC_ROOT_REL;

		} elseif ($GLOBALS['config']->get('config', 'ssl')) {
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

	//=====[ Public ]=======================================

	/**
	 * Define a custom page to use SSL
	 *
	 * @param array/string $input
	 * @param bool $secure
	 * @return bool
	 */
	public function defineSecurePage($input = null, $secure = true) {
		return false;
	}

	/**
	 * Force SSL
	 *
	 * @param bool $default
	 */
	public function sslForce($default = 'on') {
		// Force the current page into SSL mode
		$this->_sslSwitch($default);
	}

	//=====[ Private ]=======================================

	/**
	 * Switch to SSL
	 *
	 * @param bool $force
	 */
	private function _sslSwitch($force = 'on') {
		if ($GLOBALS['config']->get('config', 'ssl') && $_GET['_g']!=='remote' &&  $_GET['_g']!=='rm') { // NEVER switch if a remote call is made
			if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
				parse_str($_SERVER['QUERY_STRING'], $params);
			}

			if (isset($GLOBALS['seo']->_a) && !empty($GLOBALS['seo']->_a)) {
				$current_mode = $GLOBALS['seo']->_a;
			} else {
				$current_mode = (isset($_GET['_a'])) ? $_GET['_a'] : '';
			}

			$enable_ssl = ($force=='off') ? false : true;

			if ($enable_ssl && !CC_SSL) {
				// Switch into SSL mode
				$page = $GLOBALS['config']->get('config', 'ssl_url').str_replace(CC_ROOT_REL, '/', $_SERVER['PHP_SELF']);
			} else if (!$enable_ssl && CC_SSL) {
				// Switch out of SSL mode
				$page = $GLOBALS['config']->get('config', 'standard_url').str_replace($GLOBALS['config']->get('config', 'ssl_path'), '/', $_SERVER['PHP_SELF']);
			}

			if (isset($page)) {
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