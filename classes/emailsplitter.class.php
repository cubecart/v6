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
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}

/**
 * Splits a stored email body into the parts a merchant can safely edit and the parts
 * that hold logic
 *
 * An email body is HTML with Smarty in it. Most of it is wording with a few values
 * dropped in ({$DATA.first_name}); some of it is logic (the loop that prints one table
 * row per product). The email editor lets the wording be edited as text and keeps the
 * logic out of reach, so it has to know which is which.
 *
 * split() cuts the body into units at the points where no HTML element and no Smarty
 * block is open. Every unit is an exact slice of the body: joining the units' sources
 * gives the body back, byte for byte, which is what lets the editor write back only
 * the units that were changed. Each unit is one of:
 *
 *   gap       white space between two units
 *   editable  wording, with values only
 *   inline    wording whose only logic is {if} blocks that open and close inside a line
 *   cond      an {if}...{/if} around whole blocks of wording
 *   locked    anything else that holds logic
 *
 * It fails closed. A body it cannot follow (tags that do not balance, markup it does
 * not recognise) comes back as a single locked unit, which the editor shows as code.
 */
class EmailSplitter
{
    const GAP      = 'gap';
    const EDITABLE = 'editable';
    const INLINE   = 'inline';
    const COND     = 'cond';
    const LOCKED   = 'locked';

    /**
     * Smarty comment, Smarty tag, HTML comment, HTML tag, then text. A '{' followed by
     * white space is text, as it is to Smarty, which is what keeps CSS rules out of it.
     */
    const TOKEN = '~(?<scomment>\{\*.*?\*\})|(?<smarty>\{(?=[^\s{}])[^{}]*\})|(?<hcomment><!--.*?-->)|(?<tag></?[a-zA-Z][a-zA-Z0-9:-]*(?:\{[^{}]*\}|"[^"]*"|\'[^\']*\'|[^<>"\'{}])*>)|(?<text>[^<{]+|[<{])~s';

    /** A value and nothing else: {$DATA.first_name}, {$product.name|truncate:20} */
    const VALUE = '/^\{\$[A-Za-z_][\w.\[\]]*(?:\|[^{}]*)?\}$/';

    private static $_void     = array('br', 'img', 'hr', 'meta', 'input', 'link', 'base', 'col', 'area', 'wbr', 'source', 'embed', 'param', 'track');
    private static $_block    = array('p', 'div', 'table', 'ul', 'ol', 'dl', 'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'pre', 'center', 'address', 'section', 'article', 'header', 'footer', 'form', 'fieldset');
    private static $_optional = array('p', 'li', 'td', 'th', 'tr', 'tbody', 'thead', 'tfoot', 'dt', 'dd', 'option', 'colgroup');
    private static $_table    = array('table', 'tr', 'td', 'th', 'tbody', 'thead', 'tfoot', 'caption', 'colgroup', 'col');
    private static $_opens    = array('if', 'foreach', 'section', 'for', 'while', 'capture', 'literal', 'strip', 'nocache', 'function', 'block', 'setfilter');
    private static $_middles  = array('else', 'elseif', 'foreachelse', 'sectionelse', 'forelse');
    /** Never shown in the visual editor, whatever is in them */
    private static $_opaque   = array('script', 'style', 'iframe', 'object', 'embed', 'form', 'svg', 'math', 'video', 'audio');

    //=====[ Public ]=======================================

    /**
     * Split a body into units
     *
     * @param string $source
     * @return array Units, each with 'kind' and 'source'. Editable and inline units also
     *   carry 'parts' (see _parts()). A cond unit carries 'open', 'close' and 'inner'.
     */
    public static function split($source)
    {
        $source = (string)$source;
        if ($source === '') {
            return array();
        }
        try {
            $units = self::_units($source, true);
        } catch (Exception $e) {
            $units = false;
        }
        // The promise everything else rests on: the units are the body, exactly
        if (!is_array($units) || self::join($units) !== $source) {
            return array(array('kind' => self::LOCKED, 'source' => $source));
        }
        return $units;
    }

    /**
     * Put units back together
     *
     * @param array $units
     * @return string
     */
    public static function join($units)
    {
        $source = '';
        foreach ($units as $unit) {
            $source .= $unit['source'];
        }
        return $source;
    }

    /**
     * Find a unit by the id the editor knows it by: its position, and for a unit inside
     * a cond its position in there too ("4" or "4.1")
     *
     * @param array $units
     * @param string $id
     * @return array|false
     */
    public static function unit($units, $id)
    {
        $path = explode('.', (string)$id);
        $top  = array_shift($path);
        if (!ctype_digit((string)$top) || !isset($units[(int)$top])) {
            return false;
        }
        $unit = $units[(int)$top];
        if (empty($path)) {
            return $unit;
        }
        if ($unit['kind'] !== self::COND || count($path) !== 1 || !ctype_digit($path[0]) || !isset($unit['inner'][(int)$path[0]])) {
            return false;
        }
        return $unit['inner'][(int)$path[0]];
    }

    //=====[ Private ]=======================================

    /**
     * @param string $source
     * @return array Tokens as array(type, text, start, end)
     * @throws Exception
     */
    private static function _tokens($source)
    {
        if (preg_match_all(self::TOKEN, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) === false) {
            throw new Exception('The body could not be read');
        }
        $tokens = array();
        $at = 0;
        foreach ($matches as $match) {
            if ($match[0][1] !== $at) {
                throw new Exception('Unreadable at '.$at);
            }
            $type = 'text';
            foreach (array('scomment', 'smarty', 'hcomment', 'tag') as $name) {
                if (isset($match[$name]) && $match[$name][1] !== -1 && $match[$name][0] !== '') {
                    $type = $name;
                    break;
                }
            }
            $at += strlen($match[0][0]);
            $tokens[] = array($type, $match[0][0], $match[0][1], $at);
        }
        if ($at !== strlen($source)) {
            throw new Exception('Unreadable at '.$at);
        }
        return $tokens;
    }

    /**
     * What a Smarty tag is to the splitter
     *
     * @param string $tag
     * @return array(kind, name): value, open, close, middle, char or logic
     */
    private static function _smarty($tag)
    {
        if (preg_match(self::VALUE, $tag)) {
            return array('value', null);
        }
        if (!preg_match('/^\{(\/?)([A-Za-z_]\w*)/', $tag, $match)) {
            return array('logic', null); // {$a = 1}, {"text"|upper}, anything unusual
        }
        $name = strtolower($match[2]);
        if ($name === 'ldelim' || $name === 'rdelim') {
            return array('char', $name);
        }
        if ($match[1] === '/') {
            return array('close', $name);
        }
        if (in_array($name, self::$_opens, true)) {
            return array('open', $name);
        }
        if (in_array($name, self::$_middles, true)) {
            return array('middle', $name);
        }
        return array('logic', $name); // {assign}, {include}, {math}, plugin functions
    }

    /**
     * @param string $tag An HTML tag token
     * @return string Its element name, lower case
     */
    private static function _name($tag)
    {
        return preg_match('~^</?([a-zA-Z][a-zA-Z0-9:-]*)~', $tag, $match) ? strtolower($match[1]) : '';
    }

    /**
     * Cut a body into units
     *
     * @param string $source
     * @param bool $conds Whether an {if} around whole blocks may become a cond unit
     * @return array
     * @throws Exception when the body's tags do not balance
     */
    private static function _units($source, $conds)
    {
        $tokens = self::_tokens($source);
        $units  = array();
        $html   = array(); // open HTML elements
        $smarty = 0;       // open Smarty blocks
        $unit   = null;    // the unit being gathered: first token, last token, and whether it is a bare line

        $finish = function ($unit) use (&$units, $tokens) {
            $last = $unit['last'];
            if ($unit['bare']) {
                // White space after a bare line is a gap, not part of the line
                while ($last > $unit['first'] && $tokens[$last][0] === 'text' && trim($tokens[$last][1]) === '') {
                    $last--;
                }
            }
            $units[] = array('first' => $unit['first'], 'last' => $last);
            if ($last < $unit['last']) {
                $units[] = array('gap' => true, 'first' => $last + 1, 'last' => $unit['last']);
            }
        };

        foreach ($tokens as $i => $token) {
            list($type, $text) = $token;
            $top  = (empty($html) && $smarty === 0);
            $name = ($type === 'tag') ? self::_name($text) : '';
            $closing = ($type === 'tag' && $text[1] === '/');
            $blocky  = ($type === 'tag' && !$closing && in_array($name, self::$_block, true));

            if ($top && $unit === null) {
                if ($type === 'text' && trim($text) === '') {
                    $units[] = array('gap' => true, 'first' => $i, 'last' => $i);
                    continue;
                }
                $opens = ($type === 'smarty') ? self::_smarty($text) : array('', '');
                // A block element or a Smarty block is a unit of its own; anything else starts a bare line
                $unit = array('first' => $i, 'last' => $i, 'bare' => !($blocky || $opens[0] === 'open'));
            } elseif ($top && $unit !== null && $unit['bare'] && $blocky) {
                // A bare line ends where the next block element starts
                $finish($unit);
                $unit = array('first' => $i, 'last' => $i, 'bare' => false);
            } elseif ($unit === null) {
                throw new Exception('Content outside a unit');
            }
            $unit['last'] = $i;

            if ($type === 'tag') {
                if ($closing) {
                    while (!empty($html) && end($html) !== $name && in_array(end($html), self::$_optional, true)) {
                        array_pop($html);
                    }
                    if (empty($html) || end($html) !== $name) {
                        throw new Exception('Unbalanced </'.$name.'>');
                    }
                    array_pop($html);
                } elseif (!in_array($name, self::$_void, true) && substr($text, -2) !== '/>') {
                    if ($name === 'p' && end($html) === 'p') {
                        array_pop($html);
                    }
                    $html[] = $name;
                }
            } elseif ($type === 'smarty') {
                $kind = self::_smarty($text);
                if ($kind[0] === 'open') {
                    $smarty++;
                } elseif ($kind[0] === 'close') {
                    $smarty--;
                    if ($smarty < 0) {
                        throw new Exception('Unbalanced '.$text);
                    }
                }
            }
            // A block unit is complete when everything it opened has closed
            if (!$unit['bare'] && empty($html) && $smarty === 0) {
                $finish($unit);
                $unit = null;
            }
        }
        if ($unit !== null) {
            $left = array_diff($html, self::$_optional);
            if (!empty($left) || $smarty !== 0) {
                throw new Exception('Unclosed at the end');
            }
            $finish($unit);
        }

        $result = array();
        foreach ($units as $unit) {
            $slice  = array_slice($tokens, $unit['first'], $unit['last'] - $unit['first'] + 1);
            $text   = substr($source, $slice[0][2], $slice[count($slice) - 1][3] - $slice[0][2]);
            if (!empty($unit['gap'])) {
                $result[] = array('kind' => self::GAP, 'source' => $text);
                continue;
            }
            $result[] = self::_classify($slice, $text, $conds);
        }
        return $result;
    }

    /**
     * Decide what kind of unit a run of tokens is
     *
     * @param array $tokens
     * @param string $source
     * @param bool $conds
     * @return array The unit
     */
    private static function _classify($tokens, $source, $conds)
    {
        $unit = array('kind' => self::LOCKED, 'source' => $source);
        $has_logic = false;
        $has_table = false;
        foreach ($tokens as $token) {
            list($type, $text) = $token;
            if ($type === 'scomment' || $type === 'hcomment') {
                return $unit;
            }
            if ($type === 'smarty') {
                $kind = self::_smarty($text);
                if ($kind[0] === 'logic') {
                    return $unit;
                }
                if ($kind[0] !== 'value' && $kind[0] !== 'char') {
                    $has_logic = true;
                }
            } elseif ($type === 'tag') {
                $name = self::_name($text);
                if (in_array($name, self::$_opaque, true)) {
                    return $unit;
                }
                if (in_array($name, self::$_table, true)) {
                    $has_table = true;
                }
                if (!self::_plainTag($text, $name)) {
                    return $unit;
                }
            }
        }
        if (!$has_logic) {
            $unit['kind']  = self::EDITABLE;
            $unit['parts'] = self::_parts($tokens);
            return $unit;
        }
        // An {if} with no {else} around whole blocks of plain wording
        if ($conds && ($cond = self::_cond($tokens, $source)) !== false) {
            return $cond;
        }
        // Logic that never leaves the line it starts on. A table with logic in it stays locked.
        if (!$has_table && ($parts = self::_parts($tokens)) !== false) {
            $unit['kind']  = self::INLINE;
            $unit['parts'] = $parts;
        }
        return $unit;
    }

    /**
     * Whether the Smarty inside an HTML tag is something the editor can carry: whole
     * attribute values or parts of them that are plain values, with nothing in them
     * that would end the attribute early. An image whose address is a value cannot be
     * shown, so that is refused too.
     *
     * @param string $tag
     * @param string $name
     * @return bool
     */
    private static function _plainTag($tag, $name)
    {
        if (strpos($tag, '{') === false) {
            return true;
        }
        if (!preg_match_all('/\{[^{}]*\}/', $tag, $found)) {
            return false; // a brace that is not a tag
        }
        foreach ($found[0] as $smarty) {
            if (!preg_match(self::VALUE, $smarty) || strpbrk($smarty, '"\'<>') !== false) {
                return false;
            }
        }
        // Every tag has to sit inside a quoted attribute value
        $outside = preg_replace('/"[^"]*"|\'[^\']*\'/', '', $tag);
        if (strpos($outside, '{') !== false) {
            return false;
        }
        if ($name === 'img' && preg_match('/\ssrc\s*=\s*("[^"]*\{|\'[^\']*\{)/i', $tag)) {
            return false;
        }
        return true;
    }

    /**
     * Break a unit of wording into what the editor shows: stretches of HTML, values, and
     * islands of logic. An island is an {if}...{/if} that starts and ends inside one line:
     * no block element or table part in it, and any element it opens it also closes.
     *
     * @param array $tokens
     * @return array|false Parts as array('html'|'value'|'island', text); false when the
     *   logic in the unit is more than islands
     */
    private static function _parts($tokens)
    {
        $parts  = array();
        $buffer = '';
        $depth  = 0;       // open {if} blocks
        $island = '';
        $inside = array(); // elements opened inside the island
        foreach ($tokens as $token) {
            list($type, $text) = $token;
            if ($depth === 0) {
                if ($type === 'smarty') {
                    $kind = self::_smarty($text);
                    if ($kind[0] === 'value') {
                        if ($buffer !== '') {
                            $parts[] = array('html', $buffer);
                            $buffer = '';
                        }
                        $parts[] = array('value', $text);
                        continue;
                    }
                    if ($kind[0] === 'open' && $kind[1] === 'if') {
                        if ($buffer !== '') {
                            $parts[] = array('html', $buffer);
                            $buffer = '';
                        }
                        $depth  = 1;
                        $island = $text;
                        $inside = array();
                        continue;
                    }
                    if ($kind[0] !== 'char') {
                        return false;
                    }
                }
                $buffer .= $text;
                continue;
            }
            // Inside an island
            $island .= $text;
            if ($type === 'tag') {
                $name = self::_name($text);
                if (in_array($name, self::$_block, true) || in_array($name, self::$_table, true)) {
                    return false;
                }
                if ($text[1] === '/') {
                    if (empty($inside) || array_pop($inside) !== $name) {
                        return false;
                    }
                } elseif (!in_array($name, self::$_void, true) && substr($text, -2) !== '/>') {
                    $inside[] = $name;
                }
            } elseif ($type === 'smarty') {
                $kind = self::_smarty($text);
                if ($kind[0] === 'open') {
                    if ($kind[1] !== 'if') {
                        return false;
                    }
                    $depth++;
                } elseif ($kind[0] === 'close') {
                    $depth--;
                    if ($depth === 0) {
                        if (!empty($inside)) {
                            return false;
                        }
                        $parts[] = array('island', $island);
                        $island = '';
                    }
                } elseif ($kind[0] === 'middle') {
                    // Each branch has to close what it opened
                    if ($depth === 1 && !empty($inside)) {
                        return false;
                    }
                }
            }
        }
        if ($depth !== 0) {
            return false;
        }
        if ($buffer !== '') {
            $parts[] = array('html', $buffer);
        }
        return $parts;
    }

    /**
     * Whether a unit is {if X} + whole blocks of wording + {/if}
     *
     * @param array $tokens
     * @param string $source
     * @return array|false The cond unit
     */
    private static function _cond($tokens, $source)
    {
        $count = count($tokens);
        if ($count < 3 || $tokens[0][0] !== 'smarty' || $tokens[$count - 1][0] !== 'smarty') {
            return false;
        }
        $open  = self::_smarty($tokens[0][1]);
        $close = self::_smarty($tokens[$count - 1][1]);
        if ($open !== array('open', 'if') || $close !== array('close', 'if')) {
            return false;
        }
        // The first tag has to be closed by the last one, with no {else} of its own
        $depth = 0;
        for ($i = 1; $i < $count - 1; $i++) {
            if ($tokens[$i][0] !== 'smarty') {
                continue;
            }
            $kind = self::_smarty($tokens[$i][1]);
            if ($kind[0] === 'open') {
                $depth++;
            } elseif ($kind[0] === 'close') {
                $depth--;
                if ($depth < 0) {
                    return false;
                }
            } elseif ($kind[0] === 'middle' && $depth === 0) {
                return false;
            }
        }
        $open_tag  = $tokens[0][1];
        $close_tag = $tokens[$count - 1][1];
        $inside    = substr($source, strlen($open_tag), strlen($source) - strlen($open_tag) - strlen($close_tag));
        try {
            $inner = self::_units($inside, false);
        } catch (Exception $e) {
            return false;
        }
        $wording = 0;
        foreach ($inner as $unit) {
            if ($unit['kind'] === self::GAP) {
                continue;
            }
            if ($unit['kind'] !== self::EDITABLE && $unit['kind'] !== self::INLINE) {
                return false;
            }
            $wording++;
        }
        if ($wording === 0) {
            return false;
        }
        return array('kind' => self::COND, 'source' => $source, 'open' => $open_tag, 'close' => $close_tag, 'inner' => $inner);
    }
}
