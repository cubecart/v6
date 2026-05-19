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
 * Resolves whether the current admin's email(s) belong to an active Stripe
 * subscriber on cubecart.com. Calls a proxy endpoint on cubecart.com so the
 * Stripe secret key never ships with installs. Result is cached locally so
 * each admin only triggers one outbound call per day.
 */
class SupportWidget
{
    private static $_instance;

    /** Zendesk Web Widget key. */
    const WIDGET_KEY = 'a8467fc7-018a-4ce5-9fb4-873f6c733196';

    /** cubecart.com proxy that checks Stripe subscriptions by email. */
    const CHECK_URL = 'https://www.cubecart.com/api/subscription-status';

    /** TTL for a successful network response (seconds). */
    const CACHE_TTL_OK = 86400;

    /** TTL when the proxy was unreachable; retry sooner. */
    const CACHE_TTL_ERR = 3600;

    /** Hard upper bound on the outbound call so it never blocks page render. */
    const REQUEST_TIMEOUT = 4;

    private $_status = null;

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Look up subscriber status for one or more emails.
     *
     * @param array $emails
     * @return array ['subscriber' => bool, 'matched_email' => string|null]
     */
    public function getStatus(array $emails)
    {
        $emails = array_values(array_unique(array_filter(array_map(function ($e) {
            $e = strtolower(trim((string)$e));
            return filter_var($e, FILTER_VALIDATE_EMAIL) ? $e : '';
        }, $emails))));

        if (empty($emails)) {
            return array('subscriber' => false, 'matched_email' => null);
        }

        if ($this->_status !== null) {
            return $this->_status;
        }

        $cache_id = 'support_widget.'.md5(implode('|', $emails));
        if (isset($GLOBALS['cache']) && ($cached = $GLOBALS['cache']->read($cache_id)) !== false) {
            $this->_status = $cached;
            return $cached;
        }

        $result = $this->_lookup($emails);
        $ttl    = $result['_ok'] ? self::CACHE_TTL_OK : self::CACHE_TTL_ERR;
        unset($result['_ok']);

        if (isset($GLOBALS['cache'])) {
            $GLOBALS['cache']->write($result, $cache_id, $ttl);
        }
        $this->_status = $result;
        return $result;
    }

    private function _lookup(array $emails)
    {
        $url = parse_url(self::CHECK_URL);
        if (empty($url['host'])) {
            return array('subscriber' => false, 'matched_email' => null, '_ok' => false);
        }
        $scheme = isset($url['scheme']) ? $url['scheme'] : 'https';
        $port   = isset($url['port']) ? (int)$url['port'] : ($scheme === 'https' ? 443 : 80);
        $path   = isset($url['path']) ? $url['path'] : '/';

        try {
            $request = new Request($url['host'], $path, $port, false, true, self::REQUEST_TIMEOUT);
            if ($scheme === 'https') {
                $request->setSSL();
            }
            $request->setMethod('post');
            $request->setData(array(
                'emails'    => $emails,
                'version'   => CC_VERSION,
                'store_url' => CC_STORE_URL,
            ));
            $body = $request->send();
        } catch (Exception $e) {
            return array('subscriber' => false, 'matched_email' => null, '_ok' => false);
        }

        if (empty($body)) {
            return array('subscriber' => false, 'matched_email' => null, '_ok' => false);
        }

        $parsed = json_decode($body, true);
        if (!is_array($parsed)) {
            return array('subscriber' => false, 'matched_email' => null, '_ok' => false);
        }

        return array(
            'subscriber'    => !empty($parsed['subscriber']),
            'matched_email' => isset($parsed['matched_email']) && filter_var($parsed['matched_email'], FILTER_VALIDATE_EMAIL)
                ? strtolower($parsed['matched_email'])
                : null,
            '_ok' => true,
        );
    }
}
