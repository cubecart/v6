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
 * SSL controller
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
        if (preg_match('#^https?://#i', $redir)) {
            // Extract just the host from standard_url — it may include a subpath
            // (e.g. https://example.com/shop) which must not leak into the host match.
            $standard_domain = parse_url($GLOBALS['config']->get('config', 'standard_url'), PHP_URL_HOST);
            if (empty($standard_domain)) {
                return false;
            }
            $standard_domain = strtolower(preg_replace("#^www\.#i", "", $standard_domain));
            $redir_host = parse_url($redir, PHP_URL_HOST);
            if ($redir_host === false || $redir_host === null) {
                return false;
            }
            $redir_host = strtolower(preg_replace("#^www\.#i", "", $redir_host));
            return ($redir_host === $standard_domain || substr($redir_host, -strlen('.'.$standard_domain)) === '.'.$standard_domain);
        }
        return true;
    }
}
