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
 * Encryption controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Encryption {

	/**
	 * Encryption cipher
	 *
	 * @var string
	 */
	private $_cipher = null;
	/**
	 * Initialisation for encryption
	 *
	 * @var string
	 */
	private $_iv  = null;
	/**
	 * Encryption key
	 *
	 * @var string
	 */
	private $_key  = null;
	/**
	 * Encryption mode
	 *
	 * @var string
	 */
	private $_mode  = null;
	/**
	 * Encryption handler
	 *
	 * @var resource
	 */
	private $_td  = null;

	/**
	 * Class instance
	 *
	 * @var instance
	 */
	protected static $_instance;

	##############################################

	final protected function __construct() { }

	public function __destruct() {
		//If there is a mcrypt module close it
		if (isset($this->_td)) {
			mcrypt_module_close($this->_td);
		}
	}

	//=====[ Public ]=======================================

	/**
	 * Decrypt data
	 *
	 * @param string $data
	 * @return string/false
	 */
	public function decrypt($data) {
		if (!empty($data)) {
			return mcrypt_decrypt($this->_cipher, $this->_key, base64_decode($data), $this->_mode, $this->_iv);
		}
		return false;
	}

	/**
	 * Encrypt data
	 *
	 * @param string $data
	 * @return bool
	 */
	public function encrypt($data) {
		if (!empty($data)) {
			return base64_encode(mcrypt_encrypt($this->_cipher, $this->_key, $data, $this->_mode, $this->_iv));
		}
		return false;
	}

	/**
	 * Setup the instance (singleton)
	 *
	 * @return Encryption
	 */
	public static function getInstance() {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}
		self::$_instance->setup();

		return self::$_instance;
	}

	/**
	 * Setup encryption
	 *
	 * @param string $key
	 * @param string $iv
	 * @param string $cipher
	 * @param string $mode
	 */
	public function setup($key = '', $iv = '', $cipher = MCRYPT_RIJNDAEL_256, $mode = MCRYPT_MODE_CBC) {
		$key   = (!empty($key)) ? $key : $GLOBALS['config']->get('config', 'license_key');
		$iv    = (!empty($iv)) ? $iv : $GLOBALS['config']->get('config', 'license_key');

		$this->_cipher = $cipher;
		$this->_mode = $mode;

		$this->_td  = mcrypt_module_open($this->_cipher, '', $this->_mode, '');
		$this->_iv  = substr(md5($iv), 0, mcrypt_enc_get_iv_size($this->_td));
		$this->_key  = substr(md5($key), 0, mcrypt_enc_get_key_size($this->_td));
	}
}