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
 * RFC 6238 Time-Based One-Time Password (TOTP)
 * Compatible with Google Authenticator, Authy, etc.
 */
class TOTP
{
    const DIGITS  = 6;
    const PERIOD  = 30;
    const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a random base32-encoded secret (20 bytes = 160-bit)
     *
     * @return string
     */
    public static function generateSecret()
    {
        return self::base32Encode(random_bytes(20));
    }

    /**
     * Get the TOTP code for a given time offset (in 30-second steps)
     *
     * @param string $secret  base32-encoded secret
     * @param int    $offset  time step offset (0 = now, -1 = previous step, 1 = next step)
     * @return string  6-digit code, zero-padded
     */
    public static function getCode($secret, $offset = 0)
    {
        $counter = floor(time() / self::PERIOD) + (int)$offset;
        $key     = self::base32Decode($secret);
        $msg     = pack('N*', 0) . pack('N*', $counter);
        $hash    = hash_hmac('sha1', $msg, $key, true);
        $ofs     = ord($hash[19]) & 0x0f;
        $code    = (
            (ord($hash[$ofs])   & 0x7f) << 24 |
            (ord($hash[$ofs+1]) & 0xff) << 16 |
            (ord($hash[$ofs+2]) & 0xff) <<  8 |
            (ord($hash[$ofs+3]) & 0xff)
        ) % 1000000;
        return str_pad((string)$code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a TOTP code, allowing ±1 time step for clock skew
     *
     * @param string $secret  base32-encoded secret
     * @param string $code    code submitted by the user
     * @return bool
     */
    public static function verifyCode($secret, $code)
    {
        $code = preg_replace('/\s+/', '', (string)$code);
        if (strlen($code) !== self::DIGITS || !ctype_digit($code)) {
            return false;
        }
        foreach ([-1, 0, 1] as $offset) {
            if (hash_equals(self::getCode($secret, $offset), $code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Build an otpauth URI for QR code generation
     *
     * @param string $secret  base32-encoded secret
     * @param string $label   account label (e.g. admin email)
     * @param string $issuer  issuer name (e.g. store name)
     * @return string
     */
    public static function otpauthURI($secret, $label, $issuer)
    {
        return 'otpauth://totp/' . rawurlencode($label)
            . '?secret='  . rawurlencode($secret)
            . '&issuer='  . rawurlencode($issuer)
            . '&algorithm=SHA1&digits=6&period=30';
    }

    /**
     * Encode raw bytes to base32 string
     *
     * @param string $bytes  raw binary string
     * @return string
     */
    public static function base32Encode($bytes)
    {
        $alphabet = self::ALPHABET;
        $output   = '';
        $v        = 0;
        $vbits    = 0;
        $len      = strlen($bytes);
        for ($i = 0; $i < $len; $i++) {
            $v      = ($v << 8) | ord($bytes[$i]);
            $vbits += 8;
            while ($vbits >= 5) {
                $vbits  -= 5;
                $output .= $alphabet[($v >> $vbits) & 31];
            }
        }
        if ($vbits > 0) {
            $output .= $alphabet[($v << (5 - $vbits)) & 31];
        }
        return $output;
    }

    /**
     * Decode a base32 string to raw bytes
     *
     * @param string $str  base32-encoded string
     * @return string  raw binary string
     */
    public static function base32Decode($str)
    {
        $alphabet = self::ALPHABET;
        $str      = strtoupper(preg_replace('/[^A-Z2-7]/i', '', $str));
        $output   = '';
        $v        = 0;
        $vbits    = 0;
        $len      = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $pos = strpos($alphabet, $str[$i]);
            if ($pos === false) {
                continue;
            }
            $v      = ($v << 5) | $pos;
            $vbits += 5;
            if ($vbits >= 8) {
                $vbits  -= 8;
                $output .= chr(($v >> $vbits) & 0xff);
            }
        }
        return $output;
    }
}
