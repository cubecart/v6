<?php
/**
 * Smarty shared plugin
 *
 * @package    Smarty
 * @subpackage PluginsShared
 */

if (!function_exists('smarty_strftime_format_to_date')) {
    /**
     * Convert a strftime() format string to a date() format string.
     *
     * strftime() is deprecated in PHP 8.1 and removed in PHP 9.0, so the
     * date_format modifier and html_select_date function use this to keep
     * supporting strftime-style ("%d %b %Y") formats via date().
     *
     * @param string $format strftime-style format
     * @return string date()-compatible format
     */
    function smarty_strftime_format_to_date($format)
    {
        $map = array(
            'a' => 'D', 'A' => 'l', 'd' => 'd', 'e' => 'j', 'j' => 'z',
            'u' => 'N', 'w' => 'w', 'm' => 'm', 'b' => 'M', 'h' => 'M',
            'B' => 'F', 'y' => 'y', 'Y' => 'Y', 'H' => 'H', 'k' => 'G',
            'I' => 'h', 'l' => 'g', 'M' => 'i', 'p' => 'A', 'P' => 'a',
            'S' => 's', 'r' => 'h:i:s A', 'R' => 'H:i', 'T' => 'H:i:s',
            'D' => 'm/d/y', 'F' => 'Y-m-d', 's' => 'U', 'G' => 'o',
            'V' => 'W', 'z' => 'O', 'Z' => 'T', 'n' => "\n", 't' => "\t",
            'c' => 'D M j H:i:s Y', 'x' => 'm/d/y', 'X' => 'H:i:s', '%' => '%',
        );
        $out = '';
        $len = strlen($format);
        for ($i = 0; $i < $len; $i++) {
            $ch = $format[$i];
            if ($ch === '%' && $i + 1 < $len) {
                $code = $format[++$i];
                $out .= isset($map[$code]) ? $map[$code] : '%' . $code;
            } elseif (ctype_alpha($ch)) {
                // escape literal letters so date() treats them as text
                $out .= '\\' . $ch;
            } else {
                $out .= $ch;
            }
        }
        return $out;
    }
}
