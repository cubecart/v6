<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

/**
 * Password functions
 */
class Password
{

    /**
     * Class instance
     *
     * @var instance
     */
    private static $_instance;

    ##############################################

    final private function __construct()
    {
    }

    /**
     * Setup the instance (singleton)
     *
     * @return Password
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
     * Create salt for passwords (legacy - kept for backward compatibility)
     * @return string
     */
    public function createSalt()
    {
        return bin2hex(random_bytes(4));
    }

    /**
     * Create a salted password
     *
     * @param string $value
     * @param string $salt
     * @return string
     */
    public function getSalted($value, $salt = '')
    {
        //If there is no salt get some
        if (empty($salt)) {
            $salt = $this->createSalt();
        }
        //Make it a hash and extra salty
        return hash('whirlpool', $salt.$value.$salt);
    }

    /**
     * Hash a password using Argon2id (memory-hard, preferred) where available,
     * otherwise fall back to bcrypt cost 12. Algorithm choice is encoded in the
     * returned hash, so verifyPassword() handles either transparently.
     *
     * @param string $password
     * @return string
     */
    public function hashPassword($password)
    {
        list($algo, $opts) = $this->_currentAlgo();
        return password_hash($password, $algo, $opts);
    }

    /**
     * Verify a password against a modern hash. password_verify() autodetects
     * the algorithm from the hash format ($2y$, $argon2id$, $argon2i$, …).
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Returns true for any modern PHP password_hash() output (bcrypt $2y$,
     * argon2i $argon2i$, argon2id $argon2id$). Method name kept for backward
     * compatibility with hooks/plugins; semantic is "is a modern hash, not a
     * legacy salted md5/whirlpool".
     *
     * @param string $hash
     * @return bool
     */
    public function isBcrypt($hash)
    {
        if (!is_string($hash) || $hash === '') {
            return false;
        }
        $info = password_get_info($hash);
        return !empty($info['algo']);
    }

    /**
     * True if the hash should be re-hashed because the current preferred
     * algorithm or cost differs (e.g. bcrypt cost 12 → Argon2id, or future
     * cost bumps). Call this after a successful verifyPassword() to migrate
     * users to stronger hashes on their next login.
     *
     * @param string $hash
     * @return bool
     */
    public function needsRehash($hash)
    {
        list($algo, $opts) = $this->_currentAlgo();
        return password_needs_rehash($hash, $algo, $opts);
    }

    /**
     * Internal: the currently-preferred algorithm + options tuple.
     * Argon2id when the PHP build supports it, otherwise bcrypt.
     *
     * @return array [algo_const, options_array]
     */
    private function _currentAlgo()
    {
        if (defined('PASSWORD_ARGON2ID')) {
            return array(PASSWORD_ARGON2ID, array(
                'memory_cost' => 65536, // 64 MiB — PHP default
                'time_cost'   => 4,      // PHP default
                'threads'     => 1,      // PHP default
            ));
        }
        return array(PASSWORD_BCRYPT, array('cost' => 12));
    }

    /**
     * Attempts to create a password hash using the older type
     *
     * @param string $value
     * @param string $salt
     * @return string
     */
    public function getSaltedOld($value, $salt)
    {
        return md5(md5($salt).md5($value));
    }

    /**
     * Update to old password hash
     *
     * @param md5 string $md5
     * @param string $salt
     * @return string
     */
    public function updateOld($md5, $salt)
    {
        return md5(md5($salt).$md5);
    }
}
