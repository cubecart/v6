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
 * Zendesk support widget for the admin. Whether the admin pays is no longer
 * asked of cubecart.com from here: the support team looks it up from Stripe
 * when their ticket arrives.
 */
class SupportWidget
{
    private static $_instance;

    /** Zendesk Web Widget key. */
    const WIDGET_KEY = 'a8467fc7-018a-4ce5-9fb4-873f6c733196';

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}
