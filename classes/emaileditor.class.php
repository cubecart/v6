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
 * The server's half of the visual email editor
 *
 * describe() turns a stored email body into what the editor shows: the wording as
 * HTML with each value drawn as a named placeholder, and the parts that hold logic
 * rendered with sample data. rebuild() turns what the editor sends back into a body
 * again, taking every unit the merchant did not touch from the stored bytes.
 *
 * EmailSplitter decides what is wording and what is logic; this class only dresses
 * its units up and puts them back.
 */
class EmailEditor
{
    /** Where the email's wording goes inside the template when the editor shows it */
    const SLOT = '<div id="cc-email-slot"></div>';

    /**
     * Describe a body for the editor
     *
     * @param string $source The body as stored
     * @param string $content_type e.g. cart.order_confirmation
     * @param array $macros The email's macro list
     * @param string $sample EmailSample::SOURCE_LATEST or SOURCE_EXAMPLE
     * @return array 'units', 'placeholders', and 'sample' (what the sample data came from)
     */
    public static function describe($source, $content_type, $macros, $sample = EmailSample::SOURCE_LATEST)
    {
        $used    = EmailSample::assign($content_type, $sample, $macros);
        $units   = EmailSplitter::split($source);
        $renders = self::_renderUnits($units);
        $described = array();
        foreach ($units as $i => $unit) {
            $described[] = self::_unit((string)$i, $unit, $macros, isset($renders[$i]) ? $renders[$i] : '');
        }
        return array(
            'units'        => $described,
            'placeholders' => self::placeholders($macros),
            'sample'       => $used,
        );
    }

    /**
     * Describe a subject or preview text: one line of plain text that may hold values.
     * A line with logic in it is edited as code.
     *
     * @param string $line
     * @param array $macros
     * @return array 'text' as stored, 'code' (bool) and, unless it is code, 'parts'
     */
    public static function describeLine($line, $macros)
    {
        $line  = (string)$line;
        $parts = array();
        $code  = false;
        $at    = 0;
        if (preg_match_all('/\{(?=[^\s{}])[^{}]*\}/', $line, $found, PREG_OFFSET_CAPTURE)) {
            foreach ($found[0] as $tag) {
                if (!preg_match(EmailSplitter::VALUE, $tag[0])) {
                    $code = true;
                    break;
                }
                if ($tag[1] > $at) {
                    $parts[] = array('kind' => 'text', 'text' => substr($line, $at, $tag[1] - $at));
                }
                $parts[] = array_merge(array('kind' => 'value'), self::_placeholder($tag[0], $macros));
                $at = $tag[1] + strlen($tag[0]);
            }
        }
        if ($code) {
            return array('text' => $line, 'code' => true);
        }
        if ($at < strlen($line)) {
            $parts[] = array('kind' => 'text', 'text' => substr($line, $at));
        }
        return array('text' => $line, 'code' => false, 'parts' => $parts);
    }

    /**
     * The values a merchant can drop into this email, for the editor's list. Values
     * that only exist inside a loop (one product, one tax line) are left out: outside
     * the loop they are empty.
     *
     * @param array $macros
     * @return array
     */
    public static function placeholders($macros)
    {
        $list = array();
        if (!is_array($macros)) {
            return $list;
        }
        foreach ($macros as $tag => $entry) {
            if (preg_match('/^\{\$[A-Z][\w.]*\}$/', (string)$tag)) {
                $list[] = self::_placeholder($tag, $macros);
            }
        }
        return $list;
    }

    /**
     * Turn what the editor sent back into a body
     *
     * Each item is one of: array('k' => id) to keep a stored unit exactly as it is,
     * array('s' => source) for new or changed text, or array('c' => id, 'inner' => items)
     * for a conditional section whose wording changed but whose {if} did not.
     *
     * @param string $stored The body the editor was opened on
     * @param array $items
     * @return string|false False when an item refers to a unit the stored body does not have
     */
    public static function rebuild($stored, $items)
    {
        if (!is_array($items)) {
            return false;
        }
        return self::_rebuild(EmailSplitter::split((string)$stored), $items, 0);
    }

    /**
     * The template an email is wrapped in, rendered around an empty slot for the editor
     * to put the wording into
     *
     * @param string $content_type
     * @param int|false $template_id
     * @return array 'html' (what goes in <body>), 'css', 'body' (its attributes), or an empty array
     */
    public static function frame($content_type, $template_id = false)
    {
        try {
            $mailer   = new Mailer();
            $composed = $mailer->previewEmail($content_type, array('subject' => '', 'content_html' => self::SLOT), $template_id);
        } catch (Throwable $e) {
            return array();
        }
        $document = (string)$composed['html'];
        if (strpos($document, 'cc-email-slot') === false) {
            return array();
        }
        $css = '';
        if (preg_match_all('#<style\b[^>]*>(.*?)</style>#is', $document, $styles)) {
            $css = implode("\n", $styles[1]);
        }
        $attributes = array();
        $html = $document;
        if (preg_match('#<body\b([^>]*)>(.*)</body>#is', $document, $body)) {
            $html = $body[2];
            if (preg_match_all('/([a-zA-Z-]+)\s*=\s*("[^"]*"|\'[^\']*\')/', $body[1], $pairs, PREG_SET_ORDER)) {
                foreach ($pairs as $pair) {
                    $attributes[strtolower($pair[1])] = html_entity_decode(substr($pair[2], 1, -1), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
            }
        }
        return array('html' => $html, 'css' => $css, 'body' => $attributes);
    }

    //=====[ Private ]=======================================

    /**
     * @param array $units
     * @param array $items
     * @param int $depth
     * @return string|false
     */
    private static function _rebuild($units, $items, $depth)
    {
        $body = '';
        foreach ($items as $item) {
            if (!is_array($item)) {
                return false;
            }
            // Each item is exactly one of the three shapes, with values of the right kind
            if (isset($item['s']) && is_string($item['s'])) {
                $body .= $item['s'];
            } elseif (isset($item['k']) && is_scalar($item['k'])) {
                $unit = EmailSplitter::unit($units, $item['k']);
                if ($unit === false) {
                    return false;
                }
                $body .= $unit['source'];
            } elseif (isset($item['c'], $item['inner']) && is_scalar($item['c']) && is_array($item['inner']) && $depth === 0) {
                $unit = EmailSplitter::unit($units, $item['c']);
                if ($unit === false || $unit['kind'] !== EmailSplitter::COND) {
                    return false;
                }
                $inner = self::_rebuild($units, $item['inner'], 1);
                if ($inner === false) {
                    return false;
                }
                $body .= $unit['open'].$inner.$unit['close'];
            } else {
                return false;
            }
        }
        return $body;
    }

    /**
     * Render something with the sample data that is assigned. A fragment that cannot be
     * rendered shows as nothing; the whole body is compiled before it is saved, so a
     * real error is reported there.
     *
     * @param string $template
     * @return string
     */
    private static function _render($template)
    {
        try {
            return (string)$GLOBALS['smarty']->fetch('eval:'.$template);
        } catch (Throwable $e) {
            return '';
        }
    }

    /**
     * Render every top level unit in one pass, so a value one unit sets is there for the
     * next, as it is when the email is sent. Falls back to one unit at a time.
     *
     * @param array $units
     * @return array Rendered HTML by unit position, for locked and cond units
     */
    private static function _renderUnits($units)
    {
        $wanted = array();
        foreach ($units as $i => $unit) {
            if ($unit['kind'] === EmailSplitter::LOCKED || $unit['kind'] === EmailSplitter::COND) {
                $wanted[] = $i;
            }
        }
        if (empty($wanted)) {
            return array();
        }
        $nonce    = bin2hex(random_bytes(6));
        $template = '';
        foreach ($units as $i => $unit) {
            $template .= '<!--ccu:'.$nonce.':'.$i.'-->'.$unit['source'];
        }
        $renders = array();
        try {
            $output = (string)$GLOBALS['smarty']->fetch('eval:'.$template);
            $pieces = preg_split('/<!--ccu:'.$nonce.':(\d+)-->/', $output, -1, PREG_SPLIT_DELIM_CAPTURE);
            for ($n = 1; $n + 1 < count($pieces); $n += 2) {
                $renders[(int)$pieces[$n]] = $pieces[$n + 1];
            }
        } catch (Throwable $e) {
            $renders = array();
        }
        foreach ($wanted as $i) {
            if (!isset($renders[$i])) {
                $renders[$i] = self::_render($units[$i]['source']);
            }
        }
        return $renders;
    }

    /**
     * @param string $id
     * @param array $unit
     * @param array $macros
     * @param string $render
     * @return array
     */
    private static function _unit($id, $unit, $macros, $render)
    {
        $described = array('id' => $id, 'kind' => $unit['kind'], 'source' => $unit['source']);
        switch ($unit['kind']) {
            case EmailSplitter::EDITABLE:
            case EmailSplitter::INLINE:
                $described['html'] = self::_html($unit['parts'], $macros);
                break;
            case EmailSplitter::COND:
                $described['open']  = $unit['open'];
                $described['close'] = $unit['close'];
                $described['label'] = self::_condition($unit['open'], $macros);
                $described['shown'] = (trim($render) !== '');
                $described['inner'] = array();
                foreach ($unit['inner'] as $n => $inner) {
                    $described['inner'][] = self::_unit($id.'.'.$n, $inner, $macros, '');
                }
                break;
            case EmailSplitter::LOCKED:
                $described['title']  = self::_title($unit['source']);
                $described['render'] = $render;
                break;
        }
        return $described;
    }

    /**
     * The HTML the editor shows for a unit of wording
     *
     * @param array $parts From EmailSplitter
     * @param array $macros
     * @return string
     */
    private static function _html($parts, $macros)
    {
        $lang = $GLOBALS['language']->email;
        $html = '';
        foreach ($parts as $part) {
            list($kind, $text) = $part;
            if ($kind === 'value') {
                $placeholder = self::_placeholder($text, $macros);
                $html .= '<span class="cc-ph mceNonEditable" data-cc-s="'.self::_attribute($text).'" data-cc-l="'.self::_attribute($placeholder['label']).'" data-cc-v="'.self::_attribute($placeholder['sample']).'" title="'.self::_attribute($placeholder['description']).'">'.htmlspecialchars($placeholder['label'], ENT_QUOTES, 'UTF-8').'</span>';
            } elseif ($kind === 'island') {
                // Logic inside a sentence: shown as what it comes to for the sample data
                $shown = trim(self::_render($text));
                $empty = (trim(strip_tags($shown)) === '');
                $html .= '<span class="cc-ph cc-ph--logic mceNonEditable'.($empty ? ' cc-ph--empty' : '').'" data-cc-s="'.self::_attribute($text).'" title="'.self::_attribute($lang['editor_island_title']).'">'.($empty ? htmlspecialchars($lang['editor_island_empty'], ENT_QUOTES, 'UTF-8') : $shown).'</span>';
            } else {
                $html .= $text;
            }
        }
        return $html;
    }

    /**
     * How a macro is shown: its short name as one word in capitals, in brackets, so
     * that nobody takes it for wording. "Order Number" is shown as [ORDER_NUMBER].
     *
     * @param string $label
     * @return string
     */
    public static function macroName($label)
    {
        $name = trim((string)preg_replace('/[^\p{L}\p{M}\p{N}]+/u', '_', (string)$label), '_');
        if ($name === '') {
            return (string)$label;
        }
        return '['.(function_exists('mb_strtoupper') ? mb_strtoupper($name, 'UTF-8') : strtoupper($name)).']';
    }

    /**
     * What a value is called, what it is, and what it comes to for the sample data
     *
     * @param string $tag e.g. {$DATA.first_name|capitalize}
     * @param array $macros
     * @return array tag, label, description, sample
     */
    private static function _placeholder($tag, $macros)
    {
        $plain = preg_replace('/\|[^{}]*\}$/', '}', $tag); // without its modifiers
        $entry = (is_array($macros) && isset($macros[$plain])) ? $macros[$plain] : null;
        $label = '';
        $description = '';
        if (is_array($entry)) {
            $description = isset($entry['description']) ? (string)$entry['description'] : '';
            $label = isset($entry['label']) ? (string)$entry['label'] : '';
        } elseif (is_string($entry)) {
            $description = $entry;
        }
        if ($label === '' && !is_null($entry) && preg_match('/^\{\$([A-Za-z_]\w*)((?:\.\w+)*)\}$/', $plain, $name)) {
            // An extension that gives only a description, which may be a sentence: the
            // name is made from the tag, so {$REWARDS.points} is [REWARDS_POINTS]
            $label = trim((($name[1] === 'DATA') ? '' : $name[1]).str_replace('.', ' ', $name[2]));
        }
        // Nothing known about it: it shows as itself
        $label = ($label === '') ? $tag : self::macroName($label);
        $sample = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(self::_render($tag)), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        if (function_exists('mb_strlen') && mb_strlen($sample, 'UTF-8') > 120) {
            $sample = mb_substr($sample, 0, 117, 'UTF-8').'...';
        }
        return array('tag' => $tag, 'label' => $label, 'description' => ($description !== '') ? $description : $tag, 'sample' => $sample);
    }

    /**
     * Say when a conditional section is shown
     *
     * @param string $open e.g. {if !empty($DATA.ship_tracking)}
     * @param array $macros
     * @return string
     */
    private static function _condition($open, $macros)
    {
        $lang = $GLOBALS['language']->email;
        if (preg_match('/^\{if\s+!\s*empty\(\s*\$([A-Za-z_][\w.]*)\s*\)\s*\}$/', $open, $match)) {
            $placeholder = self::_placeholder('{$'.$match[1].'}', $macros);
            return sprintf($lang['editor_cond_value'], $placeholder['label']);
        }
        return sprintf($lang['editor_cond_code'], trim(preg_replace('/^\{if\s+|\}$/', '', $open)));
    }

    /**
     * Name a locked section from what it works with
     *
     * @param string $source
     * @return string
     */
    private static function _title($source)
    {
        $lang = $GLOBALS['language']->email;
        if (strpos($source, '$PRODUCTS') !== false) {
            return $lang['editor_locked_products'];
        }
        if (strpos($source, '$DOWNLOADS') !== false) {
            return $lang['editor_locked_downloads'];
        }
        if (strpos($source, '$BILLING.') !== false || strpos($source, '$SHIPPING.') !== false) {
            return $lang['editor_locked_addresses'];
        }
        return $lang['editor_locked'];
    }

    /**
     * @param string $text
     * @return string Safe inside a double quoted attribute
     */
    private static function _attribute($text)
    {
        return htmlspecialchars((string)$text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
