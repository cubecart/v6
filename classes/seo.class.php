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
 * SEO controller
 */
class SEO
{
    /**
     * Bots to block in robots.txt
     * @var array
     */
    private $_blocked_bots = [
        'AhrefsBot',
        'SemrushBot',
        'MJ12bot',
        'DotBot',
        'BLEXBot',
        'SearchmetricsBot',
        'PetalBot',
        'Bytespider',
        'GPTBot',
        'CCBot',
        'ClaudeBot',
        'Google-Extended',
    ];
    /**
     * In-process cache for CubeCart_seo_urls rows, keyed [type][item_id] => path.
     * Populated lazily by generatePath() on miss, and may be pre-seeded in bulk
     * by callers via primeSeoUrls() to avoid per-row SELECTs in hot loops.
     *
     * @var array
     */
    private $_seo_url_cache = array();
    /**
     * Category directories
     *
     * @var array of strings
     */
    private $_cat_dirs   = array();
    /**
     * Category paths
     *
     * @var array of strings
     */
    private $_cat_path   = null;
    /**
     * Dynamic URL sections
     *
     * @var array of strings
     */
    private $_dynamic_sections = array('prod', 'cat', 'doc');
    /**
     * SEO url extension
     *
     * @var string
     */
    private $_extension   = '';
    /**
     * Ignored URL sections
     *
     * @var array of strings
     */
    private $_ignored   = array(
        'account', 'addressbook', 'basket', 'checkout', 'complete', 'confirm', 'download', 'downloads', 'gateway', 'logout', 'profile', 'receipt', 'recover', 'recovery', 'remote', 'vieworder', 'plugin', 'unsubscribe'
    );
    /**
     * Rewrite URL Absolute?
     *
     * @var bool
     */
    private $_rewrite_url_absolute = false;
    /**
     * Meta data
     *
     * @var array
     */
    private $_meta_data   = array();
    /**
     * Sitemap XML handle
     *
     * @var handle
     */
    private $_sitemap_xml  = false;
    /**
     * Sitemap Count
     *
     * @var int
     */
    private $_sitemap_count  = 0;
    /**
     * Sitemap Limit
     *
     * @var int
     */
    private $_sitemap_limit  = 50000; // 50,000 URLs per sitemap (Limit is generally 50,000 URLs per sitemap, but we are being conservative here)
    /**
     * Sitemap URL Count
     *
     * @var int
     */
    private $_sitemap_url_count  = 0; // Count of URLs in the current sitemap file
    /**
     * Sitemap Duplicates
     *
     * @var array
     */
    private $_sitemap_duplicates  = array(); // Count of URLs in the current sitemap file
    /**
     * Static URL sections
     *
     * @var array of strings
     */
    private $_static_sections = array('recover', 'saleitems', 'certificates', 'trackback', 'contact', 'search', 'login', 'register');
    /**
     * SSL URL
     *
     * @var string
     */
    private $_sitemap_base_url = '';
    /**
     * Standard Dynamic URL
     *
     * @var string
     */
    private $_url = '';

    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;

    const TAGS_DEFAULT = 0;
    const TAGS_MERGE = 1;
    const TAGS_REPLACE = 2;

    const PCRE_REQUEST_URI = '(.*/)?[\w\-\_]+.[a-z]+\?_a\=([\w]+)\&(amp;)?([\w]+)\=([\w\-\_]+)([^"\']*)';

    public $_a = '';

    ##############################################

    public function __construct()
    {

        // Allow hooks to append to private variables via array_merge
        // e.g.
        // Dynamic
        // $new_dynamic_sections = array('dynamic1', 'dynamic2', 'dynamic3');
        // $this->_dynamic_sections = array_merge($this->_dynamic_sections, $new_dynamic_sections);
        // Static
        // $new_static_sections = array('static1', 'static2', 'static3');
        // $this->_static_sections = array_merge($this->_static_sections, $new_static_sections);
        foreach ($GLOBALS['hooks']->load('class.seo.construct') as $hook) {
            include $hook;
        }

        // Block known bad bots early
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            foreach ($this->_blocked_bots as $bot) {
                if (stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false) {
                    header('HTTP/1.1 403 Forbidden');
                    exit;
                }
            }
        }

        $this->_sitemap_base_url = str_replace('http://','https://', $GLOBALS['config']->get('config', 'standard_url'));

        self::_checkModRewrite();

        if($GLOBALS['config']->has('config', 'seo_ext')) {
            $this->_extension = $GLOBALS['config']->get('config', 'seo_ext');
        } else {
            $this->_extension = '.html';
        }

        // Build an array of ALL categories
        $this->_getCategoryList();
        //If URL is an SEO
        if (preg_match('#^'.self::PCRE_REQUEST_URI.'$#Sui', $_SERVER['REQUEST_URI'], $match)) {
            if (!in_array($match[2], $this->_ignored)) {
                //Generate SEO URL
                $seo_url = html_entity_decode($this->generatePath($match[5], $match[2], $match[4], true));
                if(!empty($match[6]) && $match[6][0]=='&'){
                    // Strip internal-only params from the trailing query string
                    // before appending — `seo_path` is set by .htaccess and must
                    // never leak into a public URL (else it can recursively
                    // accumulate via Apache's QSA flag on subsequent visits).
                    parse_str(ltrim($match[6], '&'), $extra_params);
                    unset($extra_params['seo_path']);
                    if (!empty($extra_params)) {
                        $seo_url .= '?'.http_build_query($extra_params);
                    }
                }
                //If the SEO URL != to the current URL
                if (str_replace($GLOBALS['rootRel'], '', $_SERVER['REQUEST_URI']) != $seo_url) {
                    //Push the user to that URL
                    httpredir($seo_url);
                }
            }
        }
    }

    /**
     * Setup the instance (singleton)
     *
     * @return SEO
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
     * Add another element to the ignored url segments
     *
     * @param string $string
     */
    public function addIgnore($string)
    {
        if (!empty($string)) {
            $this->_ignored[] = $string;
        }
    }

    /**
     * Build SEO URL
     *
     * @param string $type
     * @param string $item_id
     * @param string $amp
     * @return string
     */
    public function buildURL($type, $item_id = false, $amp = '&', $absolute = true)
    {
        // Some SEO paths are not stored in the database
        $url = ($absolute) ? $GLOBALS['storeURL'].'/' : $GLOBALS['rootRel'];

        if (!$item_id && in_array($type, $this->_static_sections)) {
            if (($item = $GLOBALS['db']->select('CubeCart_seo_urls', array('path'), array('type' => $type, 'redirect' => 0), false, 1, false, false)) !== false) {
                foreach ($GLOBALS['hooks']->load('class.seo.buildurl.static_sections') as $hook) {
                    include $hook;
                } 
                return $url.$item[0]['path'];
            } else {
                return  $url.$this->setdbPath($type, '', '', false).$this->_extension;
            }
        } elseif (($item = $GLOBALS['db']->select('CubeCart_seo_urls', array('path'), array('type' => $type, 'item_id' => $item_id, 'redirect' => 0), false, 1, false, false)) !== false) {
            foreach ($GLOBALS['hooks']->load('class.seo.buildurl.dynamic_url') as $hook) {
                include $hook;
            }
            return $url.$item[0]['path'];
        } else {
            return  $url.$this->setdbPath($type, $item_id, '', false);
        }
    }

    /**
     * Delete SEO URL
     *
     * @param string $type
     * @param string $item_id
     * @return bool
     */
    public function delete($type, $item_id)
    {
        if (in_array($type, $this->_dynamic_sections) && is_numeric($item_id)) {
            return $GLOBALS['db']->delete('CubeCart_seo_urls', array('type' => $type, 'item_id' => $item_id));
        }
        return false;
    }

    /**
     * Put the META data to the GUI
     */
    public function displayMetaData()
    {
        $GLOBALS['smarty']->assign('META_DESCRIPTION', $this->meta_description());
        $GLOBALS['smarty']->assign('META_TITLE', $this->meta_title());
    }

    /**
     * Create full URL
     *
     * @param string $url
     * @param bool $process
     * @return string
     */
    public function fullURL($url, $process = false)
    {
        if (!empty($url) && !preg_match('#^([a-z]+:|\"|\'|\#|\?)#Si', $url)) {
            if ($process) {
                $url = $GLOBALS['storeURL'] . (($GLOBALS['rootRel'] != '/') ? '/'. str_replace($GLOBALS['rootRel'], '', $url) : '/'.$url);
            } elseif (substr($url, 0, strlen($GLOBALS['rootRel'])) != $GLOBALS['rootRel']) {
                $url = $GLOBALS['rootRel'].$url;
            }
        }
        return $url;
    }

    /**
     * Pre-populate the in-process SEO URL cache. Hot loops (admin product
     * export, sitemap rebuild, etc.) can fetch CubeCart_seo_urls in a single
     * `WHERE item_id IN (...)` query and seed the cache here, so subsequent
     * generatePath() calls skip the per-row SELECT.
     *
     * @param array $rows  Array of rows shaped {type, item_id, path[, custom]}
     */
    public function primeSeoUrls(array $rows)
    {
        foreach ($rows as $row) {
            if (!isset($row['type'], $row['item_id'])) continue;
            $type = strtolower((string)$row['type']);
            $this->_seo_url_cache[$type][(int)$row['item_id']] = array(
                'path'   => isset($row['path']) ? $row['path'] : '',
                'custom' => isset($row['custom']) ? (bool)$row['custom'] : false,
            );
        }
    }

    /**
     * Generate SEO path
     *
     * @param string $id
     * @param string $type
     * @param string $key
     * @param string $absolute
     * @param string $extension
     * @return string
     */
    public function generatePath($id = null, $type = null, $key = null, $absolute = false)
    {
        $prefix  = '';
        $type   = strtolower($type);
        if (!isset($GLOBALS['db']) || !is_object($GLOBALS['db'])) {
            $GLOBALS['db'] = Database::getInstance();
        }

        if (in_array($type, $this->_static_sections)) { /*! Static */
            if (($existing = $GLOBALS['db']->select('CubeCart_seo_urls', 'path', array('type' => $type, 'redirect' => 0), false, 1, false, false)) !== false) {
                $path = $existing[0]['path'];
            } else {
                /* Force static English SEO paths until we have improved SEO for languages */
                $current_language = $GLOBALS['language']->current();
                $reset_language = false;
                if ($current_language!=='en-GB') {
                    $GLOBALS['language']->change('en-GB');
                    $GLOBALS['language']->loadDefinitions('default');
                    $reset_language = true;
                }
                $path = (!empty($GLOBALS['language']->navigation['seo_path_'.$type])) ? $GLOBALS['language']->navigation['seo_path_'.$type] : $type;
                if ($reset_language) {
                    $GLOBALS['language']->change($current_language);
                }
            }
        } else { /*! Dynamic */
            switch ($type) {
                case 'cat':
                case 'category':
                case 'viewcat':
                    // check its not been made already
                    $custom = false;
                    $existing = false;
                    if (isset($this->_seo_url_cache['cat'][(int)$id])) {
                        $cached = $this->_seo_url_cache['cat'][(int)$id];
                        $existing = array(array('path' => $cached['path'], 'custom' => $cached['custom'] ? 1 : 0));
                        $path = $cached['path'];
                        $custom = (bool)$cached['custom'];
                    } elseif (($existing = $GLOBALS['db']->select('CubeCart_seo_urls', array('path', 'custom'), array('type' => 'cat', 'item_id' => $id, 'redirect' => 0), false, 1, false, false)) !== false) {
                        $path = $existing[0]['path'];
                        $custom = (bool)$existing[0]['custom'];
                    } elseif (is_numeric($id) && isset($this->_cat_dirs[$id])) {
                        $path = $this->getDirectory($id);
                    } elseif (!isset($this->_cat_dirs[$id])) {
                        // new category won't be in cache so it needs rebuilding
                        $GLOBALS['cache']->delete('seo.category.list');
                        $this->_getCategoryList();
                        $path = $this->getDirectory($id);
                        // If try from cache fails... 
                        if(empty($path)) {
                            $this->_getCategoryList(true, true);
                            $path = $this->getDirectory($id);
                        }
                    } else {
                        // last panic resort which shouldn't happen
                        $path = 'cat'.$id;
                    }

                    if ($GLOBALS['config']->get('config', 'seo_cat_add_cats') == 0 && !$custom) {
                        // Get last part of path
                        $cat_parts = explode('/', $path);
                        $path = array_pop($cat_parts);
                    }

                    break;
                case 'doc':
                case 'document':
                case 'viewdoc':
                    // check its not been made already
                    if (($existing = $GLOBALS['db']->select('CubeCart_seo_urls', 'path', array('type' => 'doc', 'item_id' => $id, 'redirect' => 0), false, 1, false, false)) !== false) {
                        $path = $existing[0]['path'];
                    } else {
                        $docs = $GLOBALS['db']->select('CubeCart_documents', array('doc_name'), array('doc_id' => $id));
                        $path = $docs[0]['doc_name'];
                    }
                    break;
                case 'prod':
                case 'product':
                case 'viewprod':
                    // check its not been made already
                    $existing = false;
                    if (isset($this->_seo_url_cache['prod'][(int)$id])) {
                        $cached = $this->_seo_url_cache['prod'][(int)$id];
                        $existing = array(array('path' => $cached['path']));
                        $path = $cached['path'];
                    } elseif (($existing = $GLOBALS['db']->select('CubeCart_seo_urls', 'path', array('type' => 'prod', 'item_id' => $id, 'redirect' => 0), false, 1, false, false)) !== false) {
                        $path = $existing[0]['path'];
                    } elseif (($prods = $GLOBALS['db']->select('CubeCart_inventory', array('product_id', 'name', 'cat_id'), array('product_id' => (int)$id), false, 1)) !== false) {
                        if ($GLOBALS['config']->get('config', 'seo_add_cats')==0) {
                            $path = $prods[0]['name'];
                        } else {
                            $cat_directory = '';
                            if (($cats = $GLOBALS['db']->select('CubeCart_category_index', array('cat_id'), array('product_id' => (int)$id), array('primary' => 'DESC'), 1)) !== false) {
                                $prods[0]['cat_id'] = $cats[0]['cat_id'];
                            }
                            $cat_directory = $this->getDirectory($prods[0]['cat_id']);
                            if ($GLOBALS['config']->get('config', 'seo_add_cats')==1) {
                                // Get first part of path
                                $cat_parts = explode('/', $cat_directory);
                                $cat_directory = array_shift($cat_parts);
                            }
                        }
                        $path = empty($cat_directory) ? $prods[0]['name'] : $cat_directory.'/'.$prods[0]['name'];
                    }
                    break;
                default:
                    $this->_url = 'index.php?_a=' . $type . '&' . $key . '=' . $id;
                    return $this->_url;
            }
        }
        $path = SEO::_safeUrl($path);
        // If path exists without .html but setting has .html enabled we mustn't add it!
        return $existing ? $this->_getBaseUrl($absolute).$path : $this->_getBaseUrl($absolute).$this->_handleExtension($path);
    }

    /**
     * Get SEO URLs from the DB
     *
     * @param string $type
     * @param string $item_id
     * @return string
     */
    public function getdbPath($type, $item_id)
    {
        if (($item = $GLOBALS['db']->select('CubeCart_seo_urls', array('path'), array('type' => $type, 'item_id' => $item_id, 'redirect' => 0), false, 1, false, false)) !== false) {
            return $item[0]['path'];
        } else {
            return '';
        }
    }

    /**
     * Create item URL
     *
     * @param string $path
     * @param string $url
     * @param bool $url
     */
    public function getItem($path, $url = false)
    {
        if (isset($_GET['seo_path'])) {
            unset($_GET['seo_path']);
        }

        if (!empty($path)) {
            if (($item = $GLOBALS['db']->select('CubeCart_seo_urls', false, array('path' => $path), false, 1, false, false)) !== false) {
                if(in_array($item[0]['redirect'],array('301','302'))) {
                    $GLOBALS['db']->query(sprintf("UPDATE `%sCubeCart_seo_urls` SET `hit_count` = `hit_count` + 1, `last_hit` = NOW() WHERE `id` = %d", $GLOBALS['config']->get('config', 'dbprefix'), (int)$item[0]['id']));
                    httpredir($GLOBALS['storeURL'].'/'.$this->getdbPath($item[0]['type'], $item[0]['item_id']), '', false, (int)$item[0]['redirect']);
                }
                $item_vars = $this->_getItemVars($item[0]['type'], $item[0]['item_id']);
                foreach ($GLOBALS['hooks']->load('class.seo.getitem.parameters') as $hook) {
                    include $hook;
                }
                $_GET = (is_array($_GET)) ? array_merge($item_vars, $_GET) : $item_vars;
                if ($url) {
                    return $GLOBALS['storeURL'].'/index.php?'.http_build_query($_GET);
                } else {
                    return true;
                }
            } else {
                $_GET['_a'] = '404';
            }
        } else {
            httpredir('index.php');
        }
    }

    /**
     * Get items redirects
     *
     * @param string $type
     * @param string $item_id
     */
    public function getRedirects($type, $item_id)
    {
        if(ctype_digit((string)$item_id) && !empty($type)) {
            return $GLOBALS['db']->select('CubeCart_seo_urls', false, array('type'=> $type, 'item_id' => $item_id, 'redirect' => '>0'), false, false, false, false);
        }
        return false;
    }

    /**
     * Create meta description
     *
     * @param string $cat_id
     * @param string $link
     * @param string $glue
     * @param string $append
     * @param string $custom
     *
     * @return string
     */
    public function getDirectory($cat_id, $link = false, $glue = '/', $append = false, $custom = true, &$noLoops = array())
    {
        if (is_numeric($cat_id)) {
            if(!$this->_cat_dirs) {
                $this->_getCategoryList(true, true);
            }
            $category = (isset($this->_cat_dirs[$cat_id])) ? $this->_cat_dirs[$cat_id] : false;
            if (!empty($category)) {

                // Prevent never-ending loops!
                if (in_array($cat_id, $noLoops)) {
                    trigger_error('Cat Loop Detected! Cat Path: '.implode(' -> ', $noLoops).'.', E_USER_WARNING);
                    return false;
                }
                $noLoops[] = $cat_id;

                if ($link) {
                    $this->_cat_path[] = '<a href="'.$GLOBALS['storeURL'].'/index.php?_a=category&cat_id='.(int)$category['cat_id'].'">'.$category['cat_name'].'</a>';
                } else {
                    // Use only the last segment of the stored path (the slug for this level only).
                    // $category['path'] is the full ancestor path e.g. "a/b/c"; using it whole
                    // and then recursing into the parent would duplicate every ancestor segment.
                    if (!empty($category['path']) && !empty($custom)) {
                        $parts = explode('/', $category['path']);
                        $this->_cat_path[] = end($parts);
                    } else {
                        $this->_cat_path[] = $category['cat_name'];
                    }
                }
                if (is_numeric($category['cat_parent_id']) && $category['cat_parent_id'] != 0) {
                    $this->_cat_path[] = $this->getDirectory($category['cat_parent_id'], $link, $glue, $append, $custom, $noLoops);
                }
                krsort($this->_cat_path);
                if ($append) {
                    $this->_cat_path[] = $append;
                }
                $path = implode($glue, $this->_cat_path);
                $this->_cat_path = null;
                return $path;
            }
        }
        return false;
    }

    /**
     * Get the SEO extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->_extension;
    }

    /**
     * Create meta description
     *
     * @param string $glue
     * @return string
     */
    public function meta_description()
    {
        if ($GLOBALS['config']->has('config', 'seo_metadata') && $GLOBALS['config']->get('config', 'seo_metadata') && !empty($this->_meta_data['description'])) {
            switch ((int)$GLOBALS['config']->get('config', 'seo_metadata')) {
            case self::TAGS_MERGE:
                if ($GLOBALS['config']->get('config', 'store_meta_description') && $this->_meta_data['description']) {
                    $description[] = $this->_meta_data['description'];
                    $description[] = $GLOBALS['config']->get('config', 'store_meta_description');
                } elseif ($this->_meta_data['description']) {
                    $description = $this->_meta_data['description'];
                } else {
                    $description = $GLOBALS['config']->get('config', 'store_meta_description');
                }
                break;
            case self::TAGS_REPLACE:
                $description = $this->_meta_data['description'];
                break;
            }
            return (is_array($description)) ? implode(' ', $description) : $description;
        } else {
            return $GLOBALS['config']->get('config', 'store_meta_description');
        }
    }

    /**
     * Make Meta title
     *
     * @param string $glue
     * @return string
     */
    public function meta_title($glue = ' - ')
    {
        // Return the title
        if ($GLOBALS['config']->has('config', 'seo_metadata')) {
            if (!empty($this->_meta_data['title'])) {
                $title[1] = $this->_meta_data['title'];
            }
        }
        if ((int)$GLOBALS['config']->get('config', 'seo_metadata')!==self::TAGS_DEFAULT && !isset($title[1]) && isset($this->_meta_data['name'])) {
            $title[2] = $this->_meta_data['name'];
        }
        if ((int)$GLOBALS['config']->get('config', 'seo_metadata')!==self::TAGS_REPLACE && $GLOBALS['config']->get('config', 'store_title')!=='') {
            $title[69] = $GLOBALS['config']->get('config', 'store_title');
        }
        if (isset($title) && is_array($title)) {
            ksort($title);
            return implode($glue, $title);
        } else {
            return false;
        }
    }

    /**
     * Parse query string
     *
     * @param string $query
     * @return string
     */
    public function queryString($query)
    {
        $query = trim($query);
        if (!empty($query)) {
            $question = true;
            // Get any exists in variables
            if (isset($this->_url) && !empty($this->_url)) {
                //This is already being done else where
                //parse_str(html_entity_decode($this->_url), $existing_vars);
                if (strpos($this->_url, '?') !== false) {
                    $question = false;
                }
            }
            // Get query string variables
            parse_str(html_entity_decode($query), $vars);
            //$vars = (isset($existing_vars) && is_array($existing_vars)) ? array_merge($existing_vars, $vars) : $vars;
            // Strip seo_path — it's an internal-only param set by the .htaccess
            // rewrite and must never leak into a public URL.
            unset($vars['seo_path']);
            foreach ($vars as $key => $var) {
                if (substr($key, 0, 1) == '#') {
                    unset($vars[$key]);
                }
            }
            // Get URL elements
            if (!empty($vars)) {
                if ($question) {
                    $append[] = '?'.http_build_query($vars);
                } else {
                    $append[] = '&'.http_build_query($vars);
                }
            }
            $fragment = parse_url($query, PHP_URL_FRAGMENT);
            if (!empty($fragment)) {
                $append[] = '#'. $fragment;
            }

            if (is_array($append)) {
                return implode('', $append);
            }
        }

        return $query;
    }

    /**
     * Rebuild category listings
     */
    public function rebuildCategoryList()
    {
        $this->_getCategoryList(true);
    }

    /**
     * Rewrite URL
     *
     * @param string $html
     * @param bool $html
     * @return bool
     */
    public function rewriteUrls($html, $absolute = false)
    {
        $this->_rewrite_url_absolute  = $absolute;

        $search 	= '#(href|action)=["\'](.*/)?[\w]+.[a-z]+\?_a\=([\w]+)\&(amp;)?([\w]+)\=([\w\-\_]+)([^"\']*)["\']#Si';
        $rule1 		= preg_replace_callback($search, array(&$this, '_callbackRule1'), $html);

        $search 	= '#(href|src|background)=([\"\'])([^\"]*)([\"\'])#Sui';
        return preg_replace_callback($search, array(&$this, '_callbackRule2'), $rule1);
    }

    /**
     * Rewrite a single bare URL into its SEO-friendly equivalent.
     *
     * Preferred over rewriteUrls() when callers already have a bare URL —
     * avoids the href="..." wrapping/unwrapping dance and the regex-on-HTML
     * fragility. Preserves the same Rule1 matching constraints (so existing
     * URL shapes transform identically), strips internal-only `seo_path`, and
     * handles URL fragments and encoded values properly.
     *
     * @param string $url       Bare URL (relative or absolute)
     * @param bool   $absolute  Return absolute URL using storeURL as base
     * @return string           SEO-rewritten URL
     */
    public function rewriteSingleUrl($url, $absolute = false)
    {
        // External absolute URLs (different host) — pass through unchanged.
        if (preg_match('#^[a-z][a-z0-9+.\-]*://#i', $url)) {
            $store_host = parse_url($GLOBALS['storeURL'], PHP_URL_HOST);
            $url_host   = parse_url($url, PHP_URL_HOST);
            if ($store_host && $url_host && strcasecmp($url_host, $store_host) !== 0) {
                return $url;
            }
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return $this->fullURL($url, $absolute);
        }

        // Parse + sanitize query
        $query = array();
        if (!empty($parts['query'])) {
            parse_str(html_entity_decode($parts['query']), $query);
            unset($query['seo_path']); // never leak the internal rewrite param
            foreach ($query as $k => $v) {
                if (substr((string)$k, 0, 1) === '#') {
                    unset($query[$k]); // legacy fragment-style keys
                }
            }
        }

        // Match the original Rule1 condition exactly:
        //   path ends in something.ext (e.g. index.php), and the query has
        //   _a=word AND a single key=value pair where value is [\w\-\_]+.
        $script = isset($parts['path']) ? basename($parts['path']) : '';
        if (preg_match('/^[\w\-\_]+\.[a-z]+$/i', $script)
            && !empty($query['_a'])
            && preg_match('/^[\w]+$/', $query['_a'])
        ) {
            $type = $query['_a'];
            $id_key = $id_val = null;
            $remaining = array();
            foreach ($query as $k => $v) {
                if ($k === '_a') continue;
                if ($id_key === null
                    && preg_match('/^[\w]+$/', (string)$k)
                    && is_string($v)
                    && preg_match('/^[\w\-\_]+$/', $v)
                ) {
                    $id_key = $k;
                    $id_val = $v;
                    continue;
                }
                $remaining[$k] = $v;
            }
            if ($id_key !== null) {
                $base_path = $this->_getBaseUrl($absolute);
                $out = $base_path . $this->generatePath($id_val, $type, $id_key);
                if (!empty($remaining)) {
                    // generatePath()'s default branch returns "index.php?_a=…&key=…" for
                    // unrewritable types, so the separator depends on what we got back.
                    $sep = (strpos($out, '?') === false) ? '?' : '&';
                    $out .= $sep . http_build_query($remaining);
                }
                if (!empty($parts['fragment'])) {
                    $out .= '#' . $parts['fragment'];
                }
                return $out;
            }
        }

        // Doesn't fit Rule1 — rebuild the URL with sanitized query and absolutize.
        $rebuilt = '';
        if (!empty($parts['scheme'])) $rebuilt .= $parts['scheme'] . '://';
        if (!empty($parts['host']))   $rebuilt .= $parts['host'];
        if (!empty($parts['port']))   $rebuilt .= ':' . $parts['port'];
        if (!empty($parts['path']))   $rebuilt .= $parts['path'];
        if (!empty($query))           $rebuilt .= '?' . http_build_query($query);
        if (!empty($parts['fragment'])) $rebuilt .= '#' . $parts['fragment'];
        return $this->fullURL($rebuilt, $absolute);
    }

    /**
     * Generate a safe SEO URL
     *
     * @param string $path
     * @return string
     */
    public static function sanitizeSEOPath($path)
    {
        return SEO::_safeUrl($path);
    }

    /**
     * Can we use SEO?
     *
     * @param string $path
     * @return bool/string
     */
    public function SEOable($path)
    {
        // Nothing to rewrite for an empty/null path; preg_replace() warns on null in PHP 8.1.
        if ($path === null || $path === '') {
            return $path;
        }
        $path = preg_replace('@index.php$@', '', $path); // remove index.php if last chars in URL
        $seo_ext = $GLOBALS['config']->get('config', 'seo_ext'); 
        if (strpos($path, 'index.php?_a=category&search') !== false) {
            $path = str_replace('index.php?', 'search'.$seo_ext.'?', $path);
            return $path;
        } elseif (($pos = strpos($path, 'index.php?_a=search')) !== false) {
            if (strlen($path) == $pos + 19) {
                $path = str_replace('index.php?_a=search', 'search'.$seo_ext, $path);
            } else {
                $path = str_replace('index.php?_a=search&', 'search'.$seo_ext.'?', $path);
            }
            return $path;
        }
        if (preg_match('#^(.*/)?[\w]+.[a-z]+\?_a\=([\w]+)(?:\&(amp;)?([\w\[\]]+)\=([\w\-\_]+)([^"\']*))$#iS', $path, $match)) {
            if (in_array($match[2], $this->_static_sections)) {
                if (!empty($match[4]) && !empty($match[5])) {
                    $match[6] = $match[6].'&'.$match[4].'='.$match[5];
                }
            }
            return $this->generatePath($match[5], $match[2], $match[4], true).$this->queryString($match[6]);
        } else {
            return $path;
        }
    }

    /**
     * Set a DB path
     *
     * @param string $type
     * @param int $item_id
     * @param string $path
     * @param bool $bool
     * @param bool $show_error
     * @return bool/string
     */
    public function setdbPath($type, $item_id, $path, $bool = true, $show_error = true, $status_code = 0)
    {
        // Check dynamic $type has an valid $item_id
        if(in_array($type, $this->_dynamic_sections) && (int)$item_id <= 0) {
            return false;
        }

        if (!empty($path)) {
            $path = SEO::_safeUrl($path);
        }
        if($status_code!==0) {
            if($GLOBALS['db']->count('CubeCart_seo_urls', 'id', "`path` = '".$GLOBALS['db']->sqlSafe($path)."'") > 0) {
                return false;
            } else {
                return $GLOBALS['db']->insert('CubeCart_seo_urls', array('type' => $type, 'item_id' => $item_id, 'path' => $this->_handleExtension($path), 'custom' => 1, 'redirect' => $status_code));
            }
        } elseif (in_array($type, array_merge($this->_dynamic_sections, $this->_static_sections))) {
            $custom = 1;

            // if path is empty or already taken generate one
            if (empty($path) || $GLOBALS['db']->count('CubeCart_seo_urls', 'id', "`path` = '".$GLOBALS['db']->sqlSafe($path)."' AND `type` = '$type' AND `item_id` <> $item_id") > 0) {
                // send warning if in use
                if (!empty($path)) {
                    if ($show_error) {
                        $GLOBALS['gui']->setError($GLOBALS['language']->settings['seo_path_taken'], true);
                    }
                }
                // try to generate
                $path = $this->generatePath($item_id, $type, null, false);

                $custom = 0;
            }

            if (empty($path)) {
                return ($bool) ? false : '';
            }
            $existing = $GLOBALS['db']->select('CubeCart_seo_urls', array('id', 'path'), array('type' => $type, 'item_id' => $item_id), false, false, false, false);
            if ($existing) {
                $match = false;
                $path = $this->_handleExtension($path);
                if($path !== $this->_extension) {
                    foreach($existing as $e) {
                        if($e['path']==$path) {
                            $match = true;
                            $GLOBALS['db']->update('CubeCart_seo_urls', array('redirect' => 0), array('id' => $e['id']));
                        } else {
                            $GLOBALS['db']->update('CubeCart_seo_urls', array('redirect' => 301), array('id' => $e['id']));
                        }
                    }
                    $insert_data = array('redirect' => 0, 'type' => $type, 'item_id' => $item_id, 'path' => $path, 'custom' => $custom);
                    if(!$match && !$GLOBALS['db']->select('CubeCart_seo_urls', false, $insert_data, false, false, false, false)) {
                        $GLOBALS['db']->insert('CubeCart_seo_urls', $insert_data);
                    }
                }
            } else {
                // Check for duplicate path
                if (!$GLOBALS['db']->select('CubeCart_seo_urls', false, array('path' => $path), false, 1, false, false)) {
                    $GLOBALS['db']->insert('CubeCart_seo_urls', array('type' => $type, 'item_id' => $item_id, 'path' => $this->_handleExtension($path), 'custom' => $custom));
                    if(empty($this->_extension) && in_array($type, $this->_static_sections)) { // Backward compatibility 
                        $GLOBALS['db']->insert('CubeCart_seo_urls', array('type' => $type, 'item_id' => $item_id, 'path' => $this->_handleExtension($path).'.html', 'redirect' => 301));
                    }
                } else {
                    // Force unique path is it's already taken
                    $unique_id = substr($type, 0, 1).$item_id;
                    $GLOBALS['db']->insert('CubeCart_seo_urls', array('type' => $type, 'item_id' => $item_id, 'path' => $this->_handleExtension($path, '-'.$unique_id), 'custom' => $custom));
                    $GLOBALS['gui']->setError($GLOBALS['language']->settings['seo_path_taken'], true);
                }
            }
            return $bool ? true : $path;
        } else {
            trigger_error('Invalid SEO path type '.$type.'.', E_USER_NOTICE);
            return false;
        }
    }

    /**
     * Set all metadata
     *
     * @param array $meta_data
     * @return bool
     */
    public function set_meta_data($meta_data)
    {
        if (!empty($meta_data) && is_array($meta_data)) {
            $default = array('name' => '', 'path' => '', 'title' => '', 'description' => '');
            $meta_data = array_merge($default, $this->_meta_data, $meta_data);
            foreach ($meta_data as $key => $value) {
                $this->_meta_data[$key] = $value;
            }
            return true;
        }
        return false;
    }

    /**
     * Create sitemap
     *
     * @return bool
     */
    public function sitemap()
    {
        $this->_deleteExitingSitemaps();
        $prefix = $GLOBALS['config']->get('config', 'dbprefix');

        // Generate a Sitemap Protocol v0.9 compliant sitemap (http://sitemaps.org)
        $this->_sitemap_xml = new XML();
        $this->_sitemap_xml->startElement('urlset', array('xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9'));

        $this->_sitemap_link(array('url' => $this->_sitemap_base_url));
        # Sale Items
        if ($GLOBALS['config']->get('config', 'catalogue_sale_mode')!=='0' && $GLOBALS['config']->get('config', 'catalogue_sale_mode')!=='2') {
            $this->_sitemap_link(array('url' => $this->_sitemap_base_url.'/index.php?_a=saleitems'));
        }
        # Gift Certificates
        if ($GLOBALS['config']->get('gift_certs', 'status')=='1') {
            $this->_sitemap_link(array('url' => $this->_sitemap_base_url.'/index.php?_a=certificates'));
        }

        // Build set of restricted category IDs (have group access rows or inherit from parent)
        $restricted_cats = array();
        $all_cats = $GLOBALS['db']->select('CubeCart_category', array('cat_id', 'cat_parent_id', 'guest_access'), array('status' => '1'));
        if ($all_cats) {
            $cat_parents = array();
            $cat_guest = array();
            foreach ($all_cats as $c) {
                $cat_parents[(int)$c['cat_id']] = (int)$c['cat_parent_id'];
                $cat_guest[(int)$c['cat_id']] = (int)$c['guest_access'];
            }
            // Categories with their own group restrictions
            $own_restricted = array();
            $group_rows = $GLOBALS['db']->select('CubeCart_category_group', array('cat_id'), false, false, false, false, false);
            if ($group_rows) {
                foreach ($group_rows as $gr) {
                    $own_restricted[(int)$gr['cat_id']] = true;
                }
            }
            // A category is restricted if it (or an ancestor) has group rows AND guest_access is off
            foreach ($cat_parents as $cid => $pid) {
                $current = $cid;
                $visited = array();
                while ($current > 0 && !isset($visited[$current])) {
                    $visited[$current] = true;
                    if (isset($own_restricted[$current])) {
                        // Found restrictions — check guest_access on the category that owns them
                        if (empty($cat_guest[$current])) {
                            $restricted_cats[$cid] = true;
                        }
                        break;
                    }
                    $current = isset($cat_parents[$current]) ? $cat_parents[$current] : 0;
                }
            }
        }

        // Pre-fetch SEO URLs we actually look up in the loop. Products use the JOIN
        // in the products query for their path, so only cat + doc rows are needed here.
        // On large stores this drops the prefetch from full-table to a few thousand rows.
        $seo_urls = array();
        $seo_rows = $GLOBALS['db']->misc(sprintf(
            "SELECT `type`, `item_id`, `path`, `custom` FROM `%sCubeCart_seo_urls` WHERE `redirect` = 0 AND `type` IN ('cat','doc')", $prefix
        ));
        if ($seo_rows) {
            foreach ($seo_rows as $sr) {
                $seo_urls[$sr['type']][(int)$sr['item_id']] = $sr;
            }
        }

        // Categories: exclude hidden and guest-restricted
        $categories = $GLOBALS['db']->select('CubeCart_category', array('cat_id', 'updated'), array('status' => '1', 'hide' => '0'));
        $sitemap_categories = array();
        if ($categories) {
            foreach ($categories as $cat) {
                if (isset($restricted_cats[(int)$cat['cat_id']])) {
                    continue;
                }
                $sitemap_categories[] = $cat;
            }
        }

        // Categories + documents are bounded sets, fine to load up front.
        // Products are streamed in batches below to keep memory bounded on large catalogues.
        $queryArray = array(
            'category' => $sitemap_categories,
            'document' => $GLOBALS['db']->select('CubeCart_documents', array('doc_id', 'updated'), array('doc_parent_id' => '0', 'doc_status' => 1)),
        );

        // Hook may inject custom types or pre-populate 'product' to override the streamed path.
        foreach ($GLOBALS['hooks']->load('class.seo.sitemap') as $hook) {
            include $hook;
        }

        foreach ($queryArray as $type => $results) {
            if ($results) {
                foreach ($results as $record) {
                    $this->_sitemap_emit($type, $record, $seo_urls);
                }
            }
        }

        // Stream products with cursor pagination so memory stays flat regardless of catalogue size.
        // Skip when a hook has already populated $queryArray['product'] (legacy override path).
        if (empty($queryArray['product'])) {
            $batch_size = 1000;
            $last_id = 0;
            $add_cats = ($GLOBALS['config']->get('config', 'seo_add_cats') != 0);
            do {
                $batch = $GLOBALS['db']->misc(sprintf(
                    "SELECT I.`product_id`, I.`updated`, I.`name`, I.`cat_id`, S.`path` AS `seo_path`
                     FROM `%1\$sCubeCart_inventory` AS I
                     LEFT JOIN `%1\$sCubeCart_seo_urls` AS S ON S.`item_id` = I.`product_id` AND S.`type` = 'prod' AND S.`redirect` = 0
                     WHERE I.`status` = 1 AND I.`product_id` > %2\$d
                     ORDER BY I.`product_id`
                     LIMIT %3\$d", $prefix, (int)$last_id, (int)$batch_size
                ));
                if (!$batch) {
                    break;
                }

                // Per-batch primary-category lookup. Only fetch ids in this chunk, and
                // only when restrictions exist (otherwise the result is unused).
                $cat_index = array();
                if ($add_cats && !empty($restricted_cats)) {
                    $batch_ids = array();
                    foreach ($batch as $r) {
                        $batch_ids[] = (int)$r['product_id'];
                    }
                    $ci_rows = $GLOBALS['db']->misc(sprintf(
                        "SELECT `product_id`, `cat_id` FROM `%sCubeCart_category_index` WHERE `product_id` IN (%s) ORDER BY `primary` DESC",
                        $prefix, implode(',', $batch_ids)
                    ));
                    if ($ci_rows) {
                        foreach ($ci_rows as $ci) {
                            if (!isset($cat_index[(int)$ci['product_id']])) {
                                $cat_index[(int)$ci['product_id']] = (int)$ci['cat_id'];
                            }
                        }
                    }
                }

                foreach ($batch as $prod) {
                    $pid = (int)$prod['product_id'];
                    $prod_cat = isset($cat_index[$pid]) ? $cat_index[$pid] : (int)$prod['cat_id'];
                    if ($prod_cat > 0 && isset($restricted_cats[$prod_cat])) {
                        continue;
                    }
                    $this->_sitemap_emit('product', $prod);
                }

                $last_row = end($batch);
                $last_id = (int)$last_row['product_id'];
                $batch_count = count($batch);
                unset($batch, $cat_index);
            } while ($batch_count === $batch_size);
        }
        if($this->_sitemap_url_count > 0) {
            $this->_writeSiteMap();
        }
        $this->_sitemap_xml = new XML();
        $this->_sitemap_xml->startElement('sitemapindex', array('xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9'));
        foreach (glob(CC_ROOT_DIR."/sitemap_*") as $filename) {
            $this->_sitemap_link(array('url' => $this->_sitemap_base_url.'/'.basename($filename)), false, false, 'sitemap');
        }
        $this->_writeSiteMap(true);
        $this->_writeRobots();
        return true;
    }

    /**
     * Unset a DB path
     *
     * @param string $type
     * @param int $item_id
     * @return bool
     */
    public function unsetdbPath($type, $item_id) 
    {
        return $GLOBALS['db']->update('CubeCart_seo_urls', array('redirect' => 301), array('type' => $type, 'item_id' => $item_id, 'redirect' => 0));
    }

    //=====[ Private ]=======================================

    /**
     * Get SEO path from standard one
     *
     * @return string
     */
    private function _callbackRule1($matches)
    {
        $base_path = $this->_getBaseUrl($this->_rewrite_url_absolute);
        return $matches[1].'="'.$base_path.$this->generatePath($matches[6], $matches[3], $matches[5]).$this->queryString($matches[7]).'"';
    }

    /**
     * Add base path onto SEO path
     *
     * @return string
     */
    private function _callbackRule2($matches)
    {
        return $matches[1].'='.$matches[2].$this->fullURL($matches[3], $this->_rewrite_url_absolute).$matches[4];
    }

    /**
     * Create .htaccess exists and write if not
     *
     * @return bool
     */
    private static function _checkModRewrite()
    {
        $cache_id = 'seo_check';

        if (!isset($GLOBALS['cache']) || !is_object($GLOBALS['cache']) || $GLOBALS['cache']->read($cache_id)) {
            return false;
        } else {
            $htaccess_path = CC_ROOT_DIR.'/.htaccess';
            $htaccess_content = '##### START CubeCart .htaccess #####

### GZIP Compression ###
<ifmodule mod_deflate.c>
	AddOutputFilterByType DEFLATE text/text text/html text/plain text/xml text/css application/x-javascript application/javascript
</ifmodule>

### Files Expiration ###
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/html "access 0 seconds"
    ExpiresDefault "access 7 days"
</IfModule>

### File Security ###
<FilesMatch "\.(htaccess)$">
  Order Allow,Deny
  Deny from all
</FilesMatch>

### Apache directory listing rules ###
DirectoryIndex index.php index.htm index.html
IndexIgnore *

<ifModule mod_headers.c>
  Header always append X-Frame-Options SAMEORIGIN
  Header always set X-Content-Type-Options nosniff
  Header always set Referrer-Policy strict-origin-when-cross-origin
</ifModule>

### Rewrite rules for SEO functionality ###
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase '.CC_ROOT_REL.'
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_URI} !='.CC_ROOT_REL.'favicon.ico
  RewriteRule ^(.*)?$ index.php?seo_path=$1 [L,QSA]
</IfModule>

### Default store 404 page ###
ErrorDocument 404 '.CC_ROOT_REL.'index.php

## Override default 404 error document for missing page resources ##
<FilesMatch "\.(gif|jpe?g|png|ico|css|js|svg|webp)$">
  ErrorDocument 404 "<html></html>
</FilesMatch>
##### END CubeCart .htaccess #####';

            if (!file_exists($htaccess_path)) {
                if (!file_put_contents($htaccess_path, $htaccess_content)) {
                    die('<p>Failed to create .htaccess file for Search Engine Friendly URL\'s. Please create this file in the stores root directory with the following content.</p><textarea style="width: 400px; height: 300px;" readonly>'.$htaccess_content.'</textarea>');
                }
                $GLOBALS['cache']->write('1', $cache_id);
                httpredir();
            } else {
                $current_contents = file_get_contents($htaccess_path);
                if (!strstr($current_contents, 'seo_path')) {
                    $htaccess_content = $current_contents."\r\n\r\n".$htaccess_content;
                    if (!file_put_contents($htaccess_path, $htaccess_content)) {
                        die('<p>Failed to update existing .htaccess file for Search Engine Friendly URL\'s. Please edit this file in the stores root directory with the following content.</p><textarea style="width: 400px; height: 300px;" readonly>'.$htaccess_content.'</textarea>');
                    }
                    $GLOBALS['cache']->write('1', $cache_id);
                    httpredir();
                }
            }
        }
    }

    /**
     * Delete existing sitemaps
     *
     * @param string $type
     * @param string $item_id
     * @return string
     */
    private function _deleteExitingSitemaps() {
        // Delete existing sitemaps
        $sitemap_files = glob(CC_ROOT_DIR.'/sitemap_*.xml*');
        if ($sitemap_files !== false) {
            foreach ($sitemap_files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        if(file_exists(CC_ROOT_DIR.'/sitemap.xml')) {
            unlink(CC_ROOT_DIR.'/sitemap.xml');
        }
        if(file_exists(CC_ROOT_DIR.'/sitemap.xml.gz')) {
            unlink(CC_ROOT_DIR.'/sitemap.xml.gz');
        }
    }

    /**
     * Write the sitemap XML to file
     *
     * @param bool $index
     * @return bool
     */
    private function _writeSiteMap($index = false) {
        $sitedata = $this->_sitemap_xml->getDocument(true);
        $this->_sitemap_xml->endDocument(); 
        if($index) {
            $filepath_uncompressed = CC_ROOT_DIR.'/sitemap_index.xml'; 
        } else {
            $this->_sitemap_count++;
            $filepath_uncompressed = CC_ROOT_DIR.'/sitemap_'.$this->_sitemap_count.'.xml';
            $filepath_compressed = $filepath_uncompressed.'.gz';  
        }
        
        if (!$index && function_exists('gzencode')) {
            // Compress the file if GZip is enabled
            $filepath = $filepath_compressed;
            $sitedata = gzencode($sitedata, 9, FORCE_GZIP);
        } else {
            $filepath = $filepath_uncompressed;
        }
        return file_put_contents($filepath, $sitedata);
    }
    
    /**
     * Write the robots.txt file
     *
     * @return int|false Number of bytes written, or false on failure
     */
    private function _writeRobots() {
        $robots_path = CC_ROOT_DIR.'/robots.txt';
        $sitemap_index = $this->_sitemap_base_url.'/sitemap_index.xml'; 
        if(file_exists($robots_path)) {
            $robots_content = file_get_contents($robots_path);
            if (strpos($robots_content, 'Sitemap:') === false) {
                $robots_content .= "\n\nSitemap: $sitemap_index";
            } else {
                $robots_content = preg_replace('/^Sitemap:.*$/m', "Sitemap: $sitemap_index", $robots_content);
            }
            unlink($robots_path);
        } else {
            $bot_rules = '';
            foreach ($this->_blocked_bots as $bot) {
                $bot_rules .= "\nUser-agent: $bot\nDisallow: /\n";
            }
            $robots_content = "User-agent: *\n";
            $robots_content .= "Crawl-delay: 10\n";
            $robots_content .= "Disallow: /*?set_currency=*\n";
            $robots_content .= "Disallow: /*?set_language=*\n";
            $robots_content .= "Disallow: /*&set_currency=*\n";
            $robots_content .= "Disallow: /*&set_language=*\n";
            $robots_content .= $bot_rules;
            $robots_content .= "\nSitemap: $sitemap_index";
        }
        return file_put_contents($robots_path, $robots_content);
    }

    /**
     * Get the base url
     *
     * @param bool $full_urls
     * @return string
     */
    private function _getBaseUrl($full_urls = false)
    {
        return ($full_urls) ? $GLOBALS['storeURL'].'/' : '';
    }

    /**
     * Get categories
     *
     * @param bool $rebuild
     */
    private function _getCategoryList($rebuild = false, $skip_seo_path = false)
    {
        $language = Session::getInstance()->has('language', 'client') ? Session::getInstance()->get('language', 'client') : Language::getInstance()->current();
        if ($rebuild || !($this->_cat_dirs = (Cache::getInstance()->read('seo.category.list.'.$language))?:array())) {
            //$this->_cat_dirs = array();
            if($skip_seo_path) {
                $query = sprintf("SELECT cat_id, cat_name, cat_parent_id FROM `%1\$sCubeCart_category` ORDER BY cat_id DESC", $GLOBALS['config']->get('config', 'dbprefix'));
            } else {
                $query = sprintf("SELECT C.cat_id, C.cat_name, C.cat_parent_id, S.path FROM `%1\$sCubeCart_category` as C LEFT JOIN `%1\$sCubeCart_seo_urls` as S ON S.item_id=C.cat_id WHERE S.type='cat' AND S.redirect='0' ORDER BY C.cat_id DESC", $GLOBALS['config']->get('config', 'dbprefix'));
            }
            if (($results = $GLOBALS['db']->query($query)) !== false) {
                foreach ($results as $result) {
                    $this->_cat_dirs[$result['cat_id']] = $result;
                }

                // Write over with translations
                if (($translations = $GLOBALS['db']->select('CubeCart_category_language', array('cat_id', 'cat_name'), array('language' => $language))) !== false) {
                    foreach ($translations as $translation) {
                        $this->_cat_dirs[$translation['cat_id']]['cat_name'] = $translation['cat_name'];
                    }
                }
                if($skip_seo_path) {
                    $this->_getCategoryList(true);
                } else if (!empty($this->_cat_dirs)) {
                    $GLOBALS['cache']->write($this->_cat_dirs, 'seo.category.list.'.$language);
                }
            }
        }
    }

    /**
     * Get an SEO item
     *
     * @param string $type
     * @param string $item_id
     * @return array
     */
    private function _getItemVars($type, $item_id)
    {
        
        // Allow hooks to set SEO items
        $array = array();
        foreach ($GLOBALS['hooks']->load('class.seo.get_item_vars') as $hook) {
            include $hook;
        }

        switch ($type) {
            /* Static */
            case 'recover':
                $array = array(
                    '_a' => 'recover'
                );
            break;
            case 'search':
                $array = array(
                    '_a' => 'search'
                );
            break;
            case 'contact':
                $array = array(
                    '_a' => 'contact'
                );
            break;
            case 'saleitems':
                $array = array(
                    '_a' => 'saleitems'
                );
            break;
            case 'certificates':
                $array = array(
                    '_a' => 'certificates'
                );
            break;
            case 'basket':
                $array = array(
                    '_a' => 'basket',
                );
            break;
            case 'login':
                $array = array(
                    '_a' => 'login'
                );
            break;
            case 'register':
                $array = array(
                    '_a' => 'register'
                );
            break;
            /* Dynamic */
            case 'cat':
                $array = array(
                    '_a' => 'category',
                    'cat_id' => $item_id
                );
            break;
            case 'doc':
                $array = array(
                    '_a' => 'document',
                    'doc_id' => $item_id
                );
            break;
            case 'prod':
                $array = array(
                    '_a' => 'product',
                    'product_id' => $item_id
                );
            break;
        }
        if (isset($array['_a'])) {
            $this->_a = $array['_a'];
        }
        return $array;
    }

    /**
     * Is URL safe?
     *
     * @param string $path
     * @param string $uid
     * @return string $path
     */
    private function _handleExtension($path, $uid = '') {
        $extension = preg_match('/\.html$/', $path) ?  '.html' : $this->_extension;
        return $path = str_replace('.html', '', $path).$uid.$extension;
    }

    /**
     * Is URL safe?
     *
     * @param string $url
     * @return bool
     */
    private static function _safeUrl($url)
    {
        $url = trim($url);
        $url = function_exists('mb_strtolower') ? mb_strtolower($url) : strtolower($url);
        $url = str_replace(' ', '-', html_entity_decode($url, ENT_QUOTES));
        $url = preg_replace('#[^\w\-._/]#iuU', '-', str_replace('/', '/', $url));
        $url = preg_replace(array('#/{2,}#iu', '#-{2,}#'), array('/', '-'), $url);
        // Trim leading/trailing slashes as well as dashes. A slug that arrives with a
        // leading slash (e.g. an empty/malformed seo_path) would otherwise survive here
        // and produce a double slash when concatenated as base.'/'.slug in the sitemap,
        // canonical tags and internal links.
        return trim($url, '-/');
    }

    /**
     * Emit a sitemap URL for a single record and rotate to a new file when
     * the per-file URL limit is reached.
     *
     * @param string $type    'category' | 'product' | 'document' (or hook-injected)
     * @param array  $record
     * @param array  $seo_urls Pre-fetched type=>id=>row map (cat + doc only)
     */
    private function _sitemap_emit($type, $record, $seo_urls = array())
    {
        $url = null;
        $id  = null;
        $key = '';
        switch ($type) {
        case 'category':
            $id  = $record['cat_id'];
            $key = 'cat_id';
            if (isset($seo_urls['cat'][(int)$id])) {
                $slug = $seo_urls['cat'][(int)$id]['path'];
                if ($GLOBALS['config']->get('config', 'seo_cat_add_cats') == 0 && !$seo_urls['cat'][(int)$id]['custom']) {
                    $parts = explode('/', $slug);
                    $slug = array_pop($parts);
                }
                $url = $this->_sitemap_base_url.'/'.SEO::_safeUrl($slug).$this->_extension;
            }
            break;
        case 'product':
            $id  = $record['product_id'];
            $key = 'product_id';
            if (!empty($record['seo_path'])) {
                $url = $this->_sitemap_base_url.'/'.SEO::_safeUrl($record['seo_path']).$this->_extension;
            }
            break;
        case 'document':
            $id  = $record['doc_id'];
            $key = 'doc_id';
            if (isset($seo_urls['doc'][(int)$id])) {
                $url = $this->_sitemap_base_url.'/'.SEO::_safeUrl($seo_urls['doc'][(int)$id]['path']).$this->_extension;
            }
            break;
        }
        if ($url) {
            $this->_sitemap_link(array('url' => $url), $record['updated'] ?? false);
        } else {
            $this->_sitemap_link(array('key' => $key, 'id' => $id), $record['updated'] ?? false, $type);
        }
        if ($this->_sitemap_url_count == $this->_sitemap_limit) {
            $this->_writeSiteMap();
            $this->_sitemap_xml = new XML();
            $this->_sitemap_xml->startElement('urlset', array('xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9'));
            $this->_sitemap_url_count = 0;
        }
    }

    /**
     * Create sitemap link
     *
     * @param string $input
     * @param string $updated
     * @param string $type
     */
    private function _sitemap_link($input, $updated = false, $type = false, $masterElement = 'url')
    {
        if (!isset($input['url']) && !empty($type)) {
            $slug = $this->generatePath($input['id'], $type, '', false);
            $input['url'] = $this->_sitemap_base_url.'/'.$this->_encodeSlug($slug);
        }

        $input['url'] = stristr($input['url'],$this->_sitemap_base_url) ? $input['url'] : $this->_sitemap_base_url.$input['url'];

        // Dedupe lookup is hot — N URLs × in_array() over a growing array was O(N^2).
        // Use the hash as the array key for O(1) isset() lookup; same memory footprint.
        $hash = md5($input['url']);
        if (!isset($this->_sitemap_duplicates[$hash]) && substr($input['url'], -6) !== '/.html') {
            $this->_sitemap_xml->startElement($masterElement);
            $this->_sitemap_xml->setElement('loc', $input['url'], false, false);
            if ($updated && "0000-00-00" !== substr($updated, 0, 10)) {
                $dateTime = new DateTime($updated);
                $this->_sitemap_xml->setElement('lastmod', $dateTime->format(DateTime::W3C), false, false);
            }
            $this->_sitemap_xml->endElement();
            $this->_sitemap_url_count++;
            $this->_sitemap_duplicates[$hash] = true;
        }
    }

    /**
     * Percent-encode non-ASCII characters in a URL slug,
     * but preserve safe characters like / - _ . ~
     *
     * @param string $url
     * @return string
     */
    private function _encodeSlug($url) {
        $result = '';
        $len = mb_strlen($url, 'UTF-8');

        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($url, $i, 1, 'UTF-8');
            $ord = $this->_uniord($char);

            // Allowed: A-Z a-z 0-9 / - _ . ~
            if (
                ($ord >= 0x30 && $ord <= 0x39) || // 0-9
                ($ord >= 0x41 && $ord <= 0x5A) || // A-Z
                ($ord >= 0x61 && $ord <= 0x7A) || // a-z
                in_array($char, ['/', '-', '_', '.', '~'])
            ) {
                $result .= $char;
            } else {
                // Convert to UTF-8 bytes and percent-encode
                $bytes = $char;
                foreach (str_split($bytes) as $b) {
                    $result .= sprintf("%%%02X", ord($b));
                }
            }
        }
        return $result;
    }

    /**
     * Get Unicode codepoint of a UTF-8 character
     */
    private function _uniord($c) {
        $h = ord($c[0]);
        if ($h <= 0x7F) return $h;
        if ($h < 0xC2) return null;
        if ($h <= 0xDF) return ($h & 0x1F) << 6 | (ord($c[1]) & 0x3F);
        if ($h <= 0xEF) return ($h & 0x0F) << 12 | (ord($c[1]) & 0x3F) << 6 | (ord($c[2]) & 0x3F);
        if ($h <= 0xF4) return ($h & 0x07) << 18 | (ord($c[1]) & 0x3F) << 12 | (ord($c[2]) & 0x3F) << 6 | (ord($c[3]) & 0x3F);
        return null;
    }
}
