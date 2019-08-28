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
 * Configuration controller
 *
 * @since 5.0.0
 */
class SSL
{

    /**
     * SSL enabled pages
     * @var array
     */
    private $_ignored_pages  = array('remote' => true, 'rm' => true);

    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;

    ##############################################

    public function __construct()
    {
        if ($GLOBALS['config']->get('config', 'ssl') && !ADMIN_CP && !CC_SSL && !in_array($_GET['_g'], $this->_ignored_pages)) {
            $ssl_url = currentPage();
            $ssl_url = preg_replace('#^http://#', 'https://', $ssl_url);
            httpredir($ssl_url, '', false, 301);
        }
    }

    /**
     * Setup the instance (singleton)
     *
     * @return SSL
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
     * Define a custom page to ignore SSL
     *
     * @param array/string $input
     * @return bool
     */
    public function defineIgnorePage($input = null)
    {
        foreach ($GLOBALS['hooks']->load('class.ssl.ignored') as $hook) {
            include $hook;
        }

        if (!is_null($input)) {
            if (is_array($input)) {
                foreach ($input as $section) {
                    $this->_ignored_pages[$section] = true;
                }
            } else {
                $this->_ignored_pages[$input] = true;
            }
        }
        return false;
    }

    //=====[ Public Defunct Functions ]=======================================

    /**
     * Define a custom page to use SSL
     *
     * @param array/string $input
     * @param bool $secure
     * @return false
     */
    public function defineSecurePage($input = null, $secure = true)
    {
        return false;
    }

    /**
     * Force SSL
     *
     * @param bool $default
     * @return false
     */
    public function sslForce($default = true)
    {
        return false;
    }

    /**
     * Validate redirect
     *
     * @param string $redir
     * @return bool
     */
    public function validRedirect($redir)
    {
        if (preg_match('#^http#iU', $redir)) {
            $standard_domain = preg_replace("(^https?://)", "", $GLOBALS['config']->get('config', 'standard_url'));
            return stristr($redir, $standard_domain);
        }
        return true;
    }

    //=====[ Private ]=======================================

    /**
     * Switch to SSL
     *
     * @param bool $force
     * @return false
     */
    private function _sslSwitch($force = false)
    {
        return false;
    }
}
