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
     * Hash a password using bcrypt
     *
     * @param string $password
     * @return string
     */
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify a password against a bcrypt hash
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
     * Check if a hash is bcrypt (starts with $2y$)
     *
     * @param string $hash
     * @return bool
     */
    public function isBcrypt($hash)
    {
        return (substr($hash, 0, 4) === '$2y$');
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
