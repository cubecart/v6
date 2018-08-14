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
 * Password functions
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
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
     * Create salt for passwords
     * @author http://www.richardlord.net/blog/php-password-security
     * @return string
     */
    public function createSalt()
    {
        return substr(str_pad(dechex(mt_rand()), 8, '0', STR_PAD_LEFT), -8);
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
