<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2025. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
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
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;

    ##############################################

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
     * Validate redirect
     *
     * @param string $redir
     * @return bool
     */
    public function validRedirect($redir)
    {
        if (preg_match('#^http#iU', $redir)) {
            $standard_domain = preg_replace("#^https?://|^www.#", "", $GLOBALS['config']->get('config', 'standard_url'));
            return stristr($redir, $standard_domain);
        }
        return true;
    }
}
