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
 * Sanitize class
 */
class Sanitize
{

    /**
     * Checks GET & POSTs for valid security token
     */
    public static function checkToken()
    {
        // Check defined CSRF on speficfied GET
        if (ADMIN_CP) {
            global $glob;
            $csrf_path = CC_ROOT_DIR.'/'.$glob['adminFolder'].'/skins/default/csrf.inc.php';
            if (file_exists($csrf_path)) {
                require_once($csrf_path);
                if (isset($csrf_maps) && is_array($csrf_maps)) {
                    // All CRSF mappings are lowercase
                    $g = array();
                    if(is_array($_GET)) {
                        foreach($_GET as $k => $v) {
                            $g[strtolower($k)] = is_string($v) ? strtolower($v) : $v;
                        }
                    }
                    foreach ($csrf_maps as $csrf_map) {
                        if (is_array($csrf_map)) {
                            $csrf_check = false;
                            foreach ($csrf_map as $key => $value) {
                                if ((!$value && isset($g[$key])) || (isset($g[$key]) && $g[$key]==$value)) {
                                    $csrf_check = true;
                                } else {
                                    $csrf_check = false;
                                    break;
                                }
                            }

                            if ($csrf_check) {
                                if (!isset($_GET['token']) || !$GLOBALS['session']->checkToken($_GET['token'])) {
                                    //Make a new token just to insure that it doesn't get used again
                                    $GLOBALS['session']->getToken(true);
                                    self::_stopToken();
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }

        if (!empty($_POST)) {
            $csrf_exception = false;
            // Exception for payment gateways
            if (!isset($_GET['_a']) && isset($_GET['_g'], $_GET['type'], $_GET['cmd'], $_GET['module']) && in_array($_GET['_g'], array('remote','rm')) && $_GET['type']=='gateway' && in_array($_GET['cmd'], array('call', 'process')) && !empty($_GET['module'])) {
                $csrf_exception = true;
            } elseif (isset($_GET['_a']) && $_GET['_a']=='complete' && !isset($_GET['_g'])) {
                $csrf_exception = true;
            }

            //Validate the POST token
            if (!$csrf_exception && (!isset($_POST['token']) || !$GLOBALS['session']->checkToken($_POST['token']))) {
                //Make a new token just to insure that it doesn't get used again
                $GLOBALS['session']->getToken(true);
                self::_stopToken();
            }
        }
    }

    /**
     * Sanitise rich (CKEditor) HTML against an allowlist of tags/attributes,
     * removing script-bearing tags, event handlers and unsafe URI schemes while
     * keeping safe formatting markup (GHSA-43f6-gfcf-wj9c).
     * @param string $html
     * @return string
     */
    public static function htmlClean($html)
    {
        if ($html === null || $html === '' || strpos($html, '<') === false) {
            return (string)$html;
        }
        $allowed_tags = array(
            'a','abbr','address','b','blockquote','br','caption','cite','code','col','colgroup',
            'dd','del','div','dl','dt','em','figcaption','figure','h1','h2','h3','h4','h5','h6',
            'hr','i','img','ins','li','mark','ol','p','pre','q','s','small','span','strike',
            'strong','sub','sup','table','tbody','td','tfoot','th','thead','tr','u','ul'
        );
        $uri_attrs    = array('href','src','longdesc','cite','background','action','formaction','xlink:href','data','srcset',
            'dynsrc','lowsrc','ping','poster','usemap','classid','codebase','profile');
        $safe_schemes = array('http','https','mailto','tel');
        // Disallowed tags whose contents are code/markup, not display text: drop the
        // whole subtree. Any other disallowed tag is unwrapped (text kept).
        $strip_with_content = array('script','style','title','textarea','noscript','iframe',
            'object','embed','applet','template','head','link','meta','base','form','svg','math','xml');

        $dom  = new DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="cc-sanitize-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $xpath = new DOMXPath($dom);
        foreach (iterator_to_array($xpath->query('//*')) as $node) {
            // Node may already have been detached as part of a removed ancestor subtree.
            if ($node->parentNode === null) {
                continue;
            }
            $tag = strtolower($node->nodeName);
            if ($tag === 'div' && $node->getAttribute('id') === 'cc-sanitize-root') {
                continue;
            }
            if (!in_array($tag, $allowed_tags, true)) {
                if (in_array($tag, $strip_with_content, true)) {
                    $node->parentNode->removeChild($node);
                } else {
                    while ($node->firstChild) {
                        $node->parentNode->insertBefore($node->firstChild, $node);
                    }
                    $node->parentNode->removeChild($node);
                }
                continue;
            }
            if ($node->hasAttributes()) {
                foreach (iterator_to_array($node->attributes) as $attr) {
                    $name  = strtolower($attr->nodeName);
                    $value = $attr->nodeValue;
                    if (strpos($name, 'on') === 0 || $name === 'srcdoc') {
                        $node->removeAttribute($attr->nodeName);
                        continue;
                    }
                    if ($name === 'style') {
                        if (preg_match('#(expression|javascript|vbscript|behaviou?r|@import|url\s*\()#i', (string)$value)) {
                            $node->removeAttribute($attr->nodeName);
                        }
                        continue;
                    }
                    if (in_array($name, $uri_attrs, true)) {
                        $test = preg_replace('/[\x00-\x20]+/', '', html_entity_decode((string)$value, ENT_QUOTES));
                        if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $test, $sm)
                            && !in_array(strtolower(rtrim($sm[0], ':')), $safe_schemes, true)) {
                            $node->removeAttribute($attr->nodeName);
                        }
                    }
                }
            }
        }

        $root = $xpath->query('//*[@id="cc-sanitize-root"]')->item(0);
        $out  = '';
        if ($root) {
            foreach ($root->childNodes as $child) {
                $out .= $dom->saveHTML($child);
            }
        }
        return $out;
    }

    /**
     * Clean all the global varaibles
     */
    public static function cleanGlobals()
    {
        $GLOBALS['RAW'] = array(
            'GET' 		=> $_GET,
            'POST' 		=> $_POST,
            'COOKIE' 	=> $_COOKIE,
            'REQUEST' 	=> $_REQUEST
        );

        self::_clean($_GET);
        self::_clean($_POST);
        self::_clean($_COOKIE);
        self::_clean($_REQUEST);
    }

    //=====[ Private ]=======================================

    /**
     * Clean a variable
     *
     * @param array $data
     */
    private static function _clean(&$data)
    {
        if (empty($data)) {
            return;
        }
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                //Make sure the variable's key name is a valid one
                if (preg_match('#([^a-z0-9\-\_\:\@\|])#i', urldecode($key))) {
                    trigger_error('Security Warning: Illegal array key "'.htmlentities($key).'" was detected and was removed.', E_USER_WARNING);
                    unset($data[$key]);
                    continue;
                } else {
                    if (is_array($value)) {
                        self::_clean($data[$key]);
                    } else {
                        if (!empty($value)) {
                            $data[$key] = self::_safety($value);
                        }
                    }
                }
            }
        } else {
            $data = self::_safety($data);
        }
    }

    /**
     * Sanitize a string for HTML
     *
     * @param string $value
     * @return string
     */
    private static function _safety($value)
    {
        return htmlspecialchars(html_entity_decode($value));
    }

    /**
     * Clears POST and triggers error
     * Used when the POST token is not valid
     */
    private static function _stopToken()
    {
        $_POST = $_GET = $_REQUEST = array();
        $message = 'Security Alert: Possible Cross-Site Request Forgery (CSRF). <a href="https://support.cubecart.com/hc/en-gb/articles/360003831797">Learn more</a>.';
        $gui_message['error'][md5($message)] = $message;
        $GLOBALS['session']->set('GUI_MESSAGE', $gui_message);
        trigger_error('Invalid Security Token', E_USER_WARNING);
    }
}
