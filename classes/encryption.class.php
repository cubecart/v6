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
    private $_method  = 'mcrypt';
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

    final protected function __construct()
    {
        // Default to mcrypt for existing data from older versions
        if(function_exists('mcrypt_encrypt')) {
            $this->_method = 'mcrypt';
        } elseif(function_exists('openssl_encrypt')) {
            $this->_method = 'openssl';
        } else {
            $this->_method = false; 
        }

    }

    public function __destruct()
    {
        //If there is a mcrypt module close it
        if ($this->_method=='mcrypt' && isset($this->_td)) {
            mcrypt_module_close($this->_td);
        }
    }

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
            if ($this->_method=='mcrypt') {
                return mcrypt_decrypt($this->_cipher, $this->_key, base64_decode($data), $this->_mode, $this->_iv);
            } else {
                $data_parts = explode(':iv:', $data);
				return openssl_decrypt($data_parts[1], $this->_cipher, $this->_key, 0, $data_parts[0]);
            }
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
        if ($this->_method=='mcrypt') {
            $keyArray = array($cart_order_id);
            $this->_td_old  = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', 'ecb', '');
            $this->_iv_old  = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->_td_old), MCRYPT_RAND);
            $this->_ks_old  = mcrypt_enc_get_key_size($this->_td_old);
            $this->_key_old = substr(md5(implode('@', $keyArray)), 0, $this->_ks_old);
            if (!empty($data)) {
                mcrypt_generic_init($this->_td_old, $this->_key_old, $this->_iv_old);
                $stringDecrypted = mdecrypt_generic($this->_td_old, $data);
                mcrypt_generic_deinit($this->_td_old);
                return trim($stringDecrypted);
            }
        }
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
            if ($this->_method=='mcrypt') {
                return base64_encode(mcrypt_encrypt($this->_cipher, $this->_key, $data, $this->_mode, $this->_iv));
            } else {
                return $this->_iv.':iv:'.openssl_encrypt($data, $this->_cipher, $this->_key, 0, $this->_iv);
            }
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
     * @param string $mode
     */
    public function setup($key = '', $iv = '', $cipher = '', $mode = '', $method = '')
    {
        $key = (!empty($key)) ? $key : $this->getEncryptKey();
        if (in_array($method, array('openssl', 'mcrypt'))) {
            $this->_method = $method;
        }

        if ($this->_method=='mcrypt') {
            $iv = (!empty($iv)) ? $iv : $this->getEncryptKey();
            $this->_cipher = empty($cipher) ? MCRYPT_RIJNDAEL_256 : $cipher;
            $this->_mode = empty($mode) ? MCRYPT_MODE_CBC : $mode;
            $this->_td  = mcrypt_module_open($this->_cipher, '', $this->_mode, '');
            $this->_iv  = substr(md5($iv), 0, mcrypt_enc_get_iv_size($this->_td));
            $this->_key  = substr(md5($key), 0, mcrypt_enc_get_key_size($this->_td));
        } else {
            $this->_key = $key;
            $this->_cipher = empty($cipher) ? 'AES-128-CBC' : $cipher;
            ;
            $this->_mode = $this->_td  = ''; // Not used with openssl
            $ivlen = openssl_cipher_iv_length($this->_cipher);
            $this->_iv = openssl_random_pseudo_bytes($ivlen);
        }
    }
}
