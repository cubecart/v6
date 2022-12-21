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
 * Encryption controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Encryption
{

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
     * Encryption method
     *
     * @var string
     */
    private $_method  = 'openssl';

    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;

    ##############################################

    final protected function __construct()
    {
        if(function_exists('openssl_encrypt')) {
            $this->_method = 'openssl';
        } else {
            $this->_method = false; 
        }
    }

    public function __destruct() {}

    /**
     * Setup the instance (singleton)
     *
     * @return Encryption
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        self::$_instance->setup();

        return self::$_instance;
    }

    //=====[ Public ]=======================================

    /**
     * Decrypt data
     *
     * @param string $data
     * @return string/false
     */
    public function decrypt($data)
    {
        if (!empty($data)) {
            $data_parts = explode(':iv:', $data);
			return openssl_decrypt($data_parts[1], $this->_cipher, $this->_key, 0, $data_parts[0]);
        }
        return false;
    }

    /**
     * Decrypt CC3/CC4 data
     *
     * @param string $data
     * @param string $cart_order_id
     * @return string/false
     */
    public function decryptDepreciated($data, $cart_order_id)
    {
        return false;
    }

    /**
     * Encrypt data
     *
     * @param string $data
     * @return bool
     */
    public function encrypt($data)
    {
        if (!empty($data)) {
            return $this->_iv.':iv:'.openssl_encrypt($data, $this->_cipher, $this->_key, 0, $this->_iv);
        }
        return false;
    }

    /**
     * Get encryption key
     *
     * @return string
     */
    public function getEncryptKey()
    {
        if ($GLOBALS['config']->has('config', 'enc_key')) {
            $enc_key = $GLOBALS['config']->get('config', 'enc_key');
            if (empty($enc_key)) {
                return $this->setEncryptKey();
            }
            return $enc_key;
        } else {
            return $this->setEncryptKey();
        }
    }
    
    /**
     * Get encryption method
     *
     * @return string/false
     */
    public function getEncryptionMethod()
    {
        return $this->_method;
    }

    /**
     * Set encryption key
     *
     * @return string
     */
    public function setEncryptKey()
    {

        // Older stores used the software license key so lets keep using that if it exists
        $key = $GLOBALS['config']->get('config', 'license_key');

        // If license_key isn't set and we don't have an "enc_key".. make one
        if ((!$key || empty($key)) && !$GLOBALS['config']->has('config', 'enc_key')) {
            $key = randomString();
            $GLOBALS['config']->set('config', 'enc_key', $key);
        } else {
            // Get enc_key
            $key = $GLOBALS['config']->get('config', 'enc_key');
            if (!$key || empty($key)) {
                $key = randomString();
                $GLOBALS['config']->set('config', 'enc_key', $key);
            }
        }
        return $key;
    }

    /**
     * Setup encryption
     *
     * @param string $key
     * @param string $iv
     * @param string $cipher
     * @param string $mode (unused hangover from mcrypt days)
     * @param string $method (unused hangover from mcrypt/openssl switch days)
     */
    public function setup($key = '', $iv = '', $cipher = '', $mode = '', $method = '')
    {
        $key = (!empty($key)) ? $key : $this->getEncryptKey();
        $this->_method = 'openssl';
        $this->_key = $key;
        $this->_cipher = empty($cipher) ? 'AES-128-CBC' : $cipher;
        $ivlen = openssl_cipher_iv_length($this->_cipher);
        $this->_iv = openssl_random_pseudo_bytes($ivlen);
    }
}
