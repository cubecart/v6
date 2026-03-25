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
 * API Rate Limiter - sliding window counter via Cache
 */
class ApiRateLimiter
{
    private $_cache;
    private $_limit;
    private $_remaining;
    private $_resetTime;

    public function __construct($limit = 60)
    {
        $this->_cache = Cache::getInstance();
        $this->_limit = $limit;
    }

    /**
     * Check rate limit for a given key ID.
     * Sends 429 response if exceeded, otherwise sets response headers.
     */
    public function check($keyId)
    {
        $minute = (int)floor(time() / 60);
        $this->_resetTime = ($minute + 1) * 60;
        $cacheKey = 'api_rate:' . $keyId . ':' . $minute;

        $count = $this->_cache->read($cacheKey);
        if ($count === false) {
            $count = 0;
        }
        $count++;

        // Write with 120 second TTL (covers current + next minute window)
        $this->_cache->write($count, $cacheKey, 120);
        $this->_remaining = max(0, $this->_limit - $count);

        // Set rate limit headers
        header('X-RateLimit-Limit: ' . $this->_limit);
        header('X-RateLimit-Remaining: ' . $this->_remaining);
        header('X-RateLimit-Reset: ' . $this->_resetTime);

        if ($count > $this->_limit) {
            header('Retry-After: ' . ($this->_resetTime - time()));
            ApiResponse::error('Rate limit exceeded. Try again in ' . ($this->_resetTime - time()) . ' seconds.', 'RATE_LIMITED', 429);
        }
    }
}
