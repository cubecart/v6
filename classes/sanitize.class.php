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
        $strip_with_content = array('script','title','textarea','noscript','iframe',
            'object','embed','applet','template','head','link','meta','base','form','svg','math','xml');
        // CSS constructs that can execute script or fetch a remote resource. Same list
        // the inline style attribute is tested against, plus -moz-binding.
        $css_unsafe = '#(expression|javascript|vbscript|behaviou?r|-moz-binding|@import|url\s*\()#i';

        // Video embeds: iframes are permitted only from these hosts. Base list plus
        // any hosts in $glob['iframe_whitelist'] and the store's own standard_url domain.
        $allowed_iframe_hosts = array(
            'www.youtube.com', 'youtube.com',
            'www.youtube-nocookie.com', 'youtube-nocookie.com',
            'player.vimeo.com'
        );
        if (!empty($GLOBALS['glob']['iframe_whitelist']) && is_array($GLOBALS['glob']['iframe_whitelist'])) {
            $allowed_iframe_hosts = array_merge($allowed_iframe_hosts, $GLOBALS['glob']['iframe_whitelist']);
        }
        $own_host = '';
        if (!empty($GLOBALS['glob']['standard_url'])) {
            $own_host = strtolower((string)parse_url($GLOBALS['glob']['standard_url'], PHP_URL_HOST));
            if ($own_host !== '') {
                $allowed_iframe_hosts[] = $own_host;
            }
        }
        $allowed_iframe_hosts = array_map('strtolower', $allowed_iframe_hosts);

        // Self-hosted media: <video>/<audio> and their <source>/<track> children are
        // permitted when every URL they reference is same-origin. A relative path is
        // always same-origin; an absolute one must match the store's own host, or a
        // host listed in $glob['media_whitelist'] (e.g. an image/media CDN).
        //
        // These elements cannot execute script on their own, and the generic attribute
        // pass below still strips on* handlers and unsafe URI schemes. The host check is
        // here so a description cannot be used to embed or hotlink arbitrary remote media.
        $allowed_media_hosts = array();
        if (!empty($GLOBALS['glob']['media_whitelist']) && is_array($GLOBALS['glob']['media_whitelist'])) {
            $allowed_media_hosts = array_map('strtolower', $GLOBALS['glob']['media_whitelist']);
        }
        if (!empty($own_host)) {
            $allowed_media_hosts[] = $own_host;
        }
        $media_tags = array('video', 'audio', 'source', 'track');
        $media_attr_keep = array(
            'video'  => array('src','poster','width','height','controls','preload','loop','muted','autoplay','playsinline','title','class','id','style'),
            'audio'  => array('src','controls','preload','loop','muted','autoplay','title','class','id','style'),
            'source' => array('src','type','media','sizes'),
            'track'  => array('src','kind','srclang','label','default'),
        );
        // Same-origin test shared by every media URL (src and poster).
        $media_src_ok = function ($url) use ($allowed_media_hosts) {
            $url = trim(preg_replace('/[\x00-\x20]+/', '', html_entity_decode((string)$url, ENT_QUOTES)));
            if ($url === '') {
                return false;
            }
            if (strpos($url, '//') === 0) { // protocol-relative
                $host = strtolower((string)parse_url('https:'.$url, PHP_URL_HOST));
                return $host !== '' && in_array($host, $allowed_media_hosts, true);
            }
            if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $url, $sm)) { // absolute
                if (!in_array(strtolower(rtrim($sm[0], ':')), array('http', 'https'), true)) {
                    return false; // javascript:, data:, etc
                }
                $host = strtolower((string)parse_url($url, PHP_URL_HOST));
                return $host !== '' && in_array($host, $allowed_media_hosts, true);
            }
            return true; // relative path, same-origin by definition
        };


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
            if ($tag === 'iframe') {
                // Normalise the src before testing it. Requiring a literal 'https://'
                // prefix rejected embeds that work perfectly well in a browser and wiped
                // the whole description with no warning (#4199): YouTube's own share code
                // was protocol-relative (//www.youtube.com/embed/x) for years, older
                // stored embeds are http://, and a copy-paste can carry leading spaces.
                // The trusted-host list is what provides the security here, not the
                // spelling of the scheme.
                $src = trim(html_entity_decode((string)$node->getAttribute('src'), ENT_QUOTES));
                $src = preg_replace('/\A[\x00-\x20]+|[\x00-\x20]+\z/', '', $src);
                if (strpos($src, '//') === 0) {
                    $src = 'https:'.$src;
                }
                $scheme = strtolower((string)parse_url($src, PHP_URL_SCHEME));
                $host   = strtolower((string)parse_url($src, PHP_URL_HOST));
                if (!in_array($scheme, array('http', 'https'), true) || !in_array($host, $allowed_iframe_hosts, true)) {
                    $node->parentNode->removeChild($node);
                    continue;
                }
                // Write it back as https, so a protocol-relative or http embed does not
                // become blocked mixed content on an https storefront.
                $port  = parse_url($src, PHP_URL_PORT);
                $path  = (string)parse_url($src, PHP_URL_PATH);
                $query = (string)parse_url($src, PHP_URL_QUERY);
                $frag  = (string)parse_url($src, PHP_URL_FRAGMENT);
                $node->setAttribute('src', 'https://'.$host.(!empty($port) ? ':'.(int)$port : '').$path
                    .($query !== '' ? '?'.$query : '').($frag !== '' ? '#'.$frag : ''));
                $iframe_keep = array('src','width','height','allow','allowfullscreen','frameborder','title','loading','referrerpolicy');
                foreach (iterator_to_array($node->attributes) as $attr) {
                    if (!in_array(strtolower($attr->nodeName), $iframe_keep, true)) {
                        $node->removeAttribute($attr->nodeName);
                    }
                }
                continue;
            }

            if (in_array($tag, $media_tags, true)) {
                // <source>/<track> only mean anything inside <video>/<audio>; loose ones
                // are unwrapped by the generic path below, same as any other stray tag.
                $parent = strtolower($node->parentNode->nodeName);
                if (($tag === 'source' || $tag === 'track') && !in_array($parent, array('video', 'audio'), true)) {
                    while ($node->firstChild) {
                        $node->parentNode->insertBefore($node->firstChild, $node);
                    }
                    $node->parentNode->removeChild($node);
                    continue;
                }
                // A <video>/<audio> with no src of its own is fine: it plays its <source>
                // children, and each of those is checked in its own turn.
                $bad = false;
                foreach (array('src', 'poster') as $url_attr) {
                    if ($node->hasAttribute($url_attr) && !$media_src_ok($node->getAttribute($url_attr))) {
                        $bad = true;
                        break;
                    }
                }
                if ($bad) {
                    $node->parentNode->removeChild($node);
                    continue;
                }
                foreach (iterator_to_array($node->attributes) as $attr) {
                    $name = strtolower($attr->nodeName);
                    if (!in_array($name, $media_attr_keep[$tag], true)) {
                        $node->removeAttribute($attr->nodeName);
                        continue;
                    }
                    // Same style check the generic attribute pass below applies; media
                    // elements skip that pass, so it has to be repeated here.
                    if ($name === 'style'
                        && preg_match('#(expression|javascript|vbscript|behaviou?r|@import|url\s*\()#i', (string)$attr->nodeValue)) {
                        $node->removeAttribute($attr->nodeName);
                    }
                }
                continue;
            }

            if ($tag === 'style') {
                // Descriptions built in the editor often carry a <style> block for table
                // formatting. Keep it, but only when the CSS is inert.
                if (preg_match($css_unsafe, (string)$node->textContent)) {
                    $node->parentNode->removeChild($node);
                    continue;
                }
                foreach (iterator_to_array($node->attributes) as $attr) {
                    if (strtolower($attr->nodeName) !== 'type') {
                        $node->removeAttribute($attr->nodeName);
                    }
                }
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
