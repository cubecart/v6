<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

/**
 * Language controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class SEO
{
    /**
     * Category directories
     *
     * @var array of strings
     */
    private $_cat_dirs   = null;
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
    private $_extension   = '.html';
    /**
     * Ignored URL sections
     *
     * @var array of strings
     */
    private $_ignored   = array(
        'account', 'addressbook', 'basket', 'checkout', 'complete', 'confirm', 'downloads', 'gateway', 'logout', 'profile', 'receipt', 'recover', 'recovery', 'remote', 'vieworder', 'plugin', 'unsubscribe'
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

        $this->_sitemap_base_url = $GLOBALS['config']->get('config', 'ssl')=='1' ? preg_replace('#^http://#', 'https://', $GLOBALS['config']->get('config', 'standard_url')) : $GLOBALS['config']->get('config', 'standard_url');

        self::_checkModRewrite();

        // Build an array of ALL categories
        $this->_getCategoryList();
        //If URL is an SEO
        if (preg_match('#^'.self::PCRE_REQUEST_URI.'$#Sui', $_SERVER['REQUEST_URI'], $match)) {
            if (!in_array($match[2], $this->_ignored)) {
                //Generate SEO URL
                $seo_url = html_entity_decode($this->generatePath($match[5], $match[2], $match[4], true, true));
                if(isset($match[6]) && $match[6][0]=='&'){  
                    $match[6][0]='?';
                    $seo_url.=$match[6];
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
            if (($item = $GLOBALS['db']->select('CubeCart_seo_urls', array('path'), array('type' => $type))) !== false) {
                return $url.$item[0]['path'].$this->_extension;
            } else {
                return  $url.$this->setdbPath($type, '', '', false).$this->_extension;
            }
        } elseif (($item = $GLOBALS['db']->select('CubeCart_seo_urls', array('path'), array('type' => $type, 'item_id' => $item_id))) !== false) {
            return $url.$item[0]['path'].$this->_extension;
        } else {
            return  $url.$this->setdbPath($type, $item_id, '', false).$this->_extension;
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
     * Generate SEO path
     *
     * @param string $id
     * @param string $type
     * @param string $key
     * @param string $absolute
     * @param string $extension
     * @return string
     */
    public function generatePath($id = null, $type = null, $key = null, $absolute = false, $extension = false)
    {
        $prefix  = '';
        $type   = strtolower($type);
        if (!isset($GLOBALS['db']) || !is_object($GLOBALS['db'])) {
            $GLOBALS['db'] = Database::getInstance();
        }

        if (in_array($type, $this->_static_sections)) { /*! Static */
            if (($existing = $GLOBALS['db']->select('CubeCart_seo_urls', 'path', array('type' => $type))) !== false) {
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
                    if (($existing = $GLOBALS['db']->select('CubeCart_seo_urls', array('path', 'custom'), array('type' => 'cat', 'item_id' => $id))) !== false) {
                        $path = $existing[0]['path'];
                        $custom = (bool)$existing[0]['custom'];
                    } elseif (is_numeric($id) && isset($this->_cat_dirs[$id])) {
                        $path = $this->getDirectory($id);
                    } elseif (!isset($this->_cat_dirs[$id])) {
                        // new category won't be in cache so it needs rebuilding
                        $GLOBALS['cache']->delete('seo.category.list');
                        $this->_getCategoryList();
                        $path = $this->getDirectory($id);
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
                    if (($existing = $GLOBALS['db']->select('CubeCart_seo_urls', 'path', array('type' => 'doc', 'item_id' => $id))) !== false) {
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
                    if (($existing = $GLOBALS['db']->select('CubeCart_seo_urls', 'path', array('type' => 'prod', 'item_id' => $id))) !== false) {
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
        $safe_path = SEO::_safeUrl($path);

        return $this->_getBaseUrl($absolute).$safe_path.(($extension) ? $this->_extension : '');
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
        if (($item = $GLOBALS['db']->select('CubeCart_seo_urls', array('path'), array('type' => $type, 'item_id' => $item_id))) !== false) {
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
            if (($item = $GLOBALS['db']->select('CubeCart_seo_urls', false, array('path' => $path))) !== false) {
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
                    $this->_cat_path[] = (empty($category['path']) || empty($custom) ? $category['cat_name'] : $category['path']);
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
        if (is_array($title)) {
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
        $path = preg_replace('@index.php$@', '', $path); // remove index.php if last chars in URL

        if (strpos($path, 'index.php?_a=category&search') !== false) {
            $path = str_replace('index.php?', 'search.html?', $path);
            return $path;
        } elseif (($pos = strpos($path, 'index.php?_a=search')) !== false) {
            if (strlen($path) == $pos + 19) {
                $path = str_replace('index.php?_a=search', 'search.html', $path);
            } else {
                $path = str_replace('index.php?_a=search&', 'search.html?', $path);
            }
            return $path;
        }
        if (preg_match('#^(.*/)?[\w]+.[a-z]+\?_a\=([\w]+)(?:\&(amp;)?([\w\[\]]+)\=([\w\-\_]+)([^"\']*))$#iS', $path, $match)) {
            if (in_array($match[2], $this->_static_sections)) {
                if (!empty($match[4]) && !empty($match[5])) {
                    $match[6] = $match[6].'&'.$match[4].'='.$match[5];
                }
            }
            return $this->generatePath($match[5], $match[2], $match[4], true, true).$this->queryString($match[6]);
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
    public function setdbPath($type, $item_id, $path, $bool = true, $show_error = true)
    {
        if (!empty($path)) {
            $path = SEO::_safeUrl($path);
        }
        if (in_array($type, array_merge($this->_dynamic_sections, $this->_static_sections))) {
            $custom = 1;

            // if path is empty or already taken generate one
            if (empty($path) || $GLOBALS['db']->count('CubeCart_seo_urls', 'id', "`path` = '$path' AND `type` = '$type' AND `item_id` <> $item_id") > 0) {
                // send warning if in use
                if (!empty($path)) {
                    if ($show_error) {
                        $GLOBALS['gui']->setError($GLOBALS['language']->settings['seo_path_taken'], true);
                    }
                }
                // try to generate
                $path = $this->generatePath($item_id, $type);

                $custom = 0;
            }

            if (empty($path)) {
                return ($bool) ? false : '';
            }
            if (($existing = $GLOBALS['db']->select('CubeCart_seo_urls', 'id', array('type' => $type, 'item_id' => $item_id), false, 1, false, false)) !== false) {
                $GLOBALS['db']->update('CubeCart_seo_urls', array('type' => $type, 'item_id' => $item_id, 'path' => $path, 'custom' => $custom), array('id' => $existing[0]['id']));
            } else {
                // Check for duplicate path
                if (!$GLOBALS['db']->select('CubeCart_seo_urls', false, array('path' => $path), false, 1, false, false)) {
                    $GLOBALS['db']->insert('CubeCart_seo_urls', array('type' => $type, 'item_id' => $item_id, 'path' => $path, 'custom' => $custom));
                } else {
                    // Force unique path is it's already taken
                    $unique_id = substr($type, 0, 1).$item_id;
                    $GLOBALS['db']->insert('CubeCart_seo_urls', array('type' => $type, 'item_id' => $item_id, 'path' => $path.'-'.$unique_id, 'custom' => $custom));
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
        // Generate a Sitemap Protocol v0.9 compliant sitemap (http://sitemaps.org)
        $this->_sitemap_xml = new XML();
        $this->_sitemap_xml->startElement('urlset', array('xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9'));

        $this->_sitemap_link(array('url' => $this->_sitemap_base_url.'/index.php'));
        # Sale Items
        if ($GLOBALS['config']->get('config', 'catalogue_sale_mode')!=='0' && $GLOBALS['config']->get('config', 'catalogue_sale_mode')!=='2') {
            $this->_sitemap_link(array('url' => $this->_sitemap_base_url.'/index.php?_a=saleitems'));
        }
        # Gift Certificates
        if ($GLOBALS['config']->get('gift_certs', 'status')=='1') {
            $this->_sitemap_link(array('url' => $this->_sitemap_base_url.'/index.php?_a=certificates'));
        }

        $queryArray = array(
            'category' => $GLOBALS['db']->select('CubeCart_category', array('cat_id'), array('status' => '1')),
            'product' => $GLOBALS['db']->select('CubeCart_inventory', array('product_id', 'updated'), array('status' => '1')),
            'document' => $GLOBALS['db']->select('CubeCart_documents', array('doc_id'), array('doc_parent_id' => '0', 'doc_status' => 1)),
        );

        foreach ($queryArray as $type => $results) {
            if ($results) {
                foreach ($results as $record) {
                    switch ($type) {
                    case 'category':
                        $id  = $record['cat_id'];
                        $key = 'cat_id';
                        break;
                    case 'product':
                        $id  = $record['product_id'];
                        $key = 'product_id';
                        break;
                    case 'document':
                        $id  = $record['doc_id'];
                        $key = 'doc_id';
                        break;
                    }
                    $this->_sitemap_link(array('key' => $key, 'id' => $id), $record['updated'], $type);
                }
            }
        }

        foreach ($GLOBALS['hooks']->load('class.seo.sitemap') as $hook) {
            include $hook;
        }

        $sitemap = $this->_sitemap_xml->getDocument(true);

        if (function_exists('gzencode')) {
            // Compress the file if GZip is enabled
            $filename = CC_ROOT_DIR.'/sitemap.xml.gz';
            $mapdata = gzencode($sitemap, 9, FORCE_GZIP);
            if (file_exists(CC_ROOT_DIR.'/sitemap.xml')) {
                unlink(CC_ROOT_DIR.'/sitemap.xml');
            }
        } else {
            $filename = CC_ROOT_DIR.'/sitemap.xml';
            $mapdata = $sitemap;
            if (file_exists(CC_ROOT_DIR.'/sitemap.xml.gz')) {
                unlink(CC_ROOT_DIR.'/sitemap.xml.gz');
            }
        }
        if (file_put_contents($filename, $mapdata)) {
            if ($GLOBALS['config']->get('config', 'offline')=='0') {
                // Ping Google
                $request = new Request('www.google.com', '/webmasters/sitemaps/ping');
                $request->setMethod('get');
                $request->setData(array('sitemap' => $this->_sitemap_base_url.'/'.basename($filename)));
                $request->send();
            }
            return true;
        }
        return false;
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
</ifModule>

### Rewrite rules for SEO functionality ###
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase '.CC_ROOT_REL.'

  ##### START v4 SEO URL BACKWARD COMPATIBILITY #####
  RewriteCond %{QUERY_STRING} (.*)$
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule cat_([0-9]+)(\.[a-z]{3,4})?(.*)$ index.php?_a=category&cat_id=$1&%1 [NC]

  RewriteCond %{QUERY_STRING} (.*)$
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule prod_([0-9]+)(\.[a-z]{3,4})?$ index.php?_a=product&product_id=$1&%1 [NC]

  RewriteCond %{QUERY_STRING} (.*)$
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule info_([0-9]+)(\.[a-z]{3,4})?$ index.php?_a=document&doc_id=$1&%1 [NC]

  RewriteCond %{QUERY_STRING} (.*)$
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule tell_([0-9]+)(\.[a-z]{3,4})?$ index.php?_a=product&product_id=$1&%1 [NC]

  RewriteCond %{QUERY_STRING} (.*)$
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule _saleItems(\.[a-z]+)?(\?.*)?$ index.php?_a=saleitems&%1 [NC,L]
  ##### END v4 SEO URL BACKWARD COMPATIBILITY #####

  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_URI} !=/favicon.ico
  RewriteRule ^(.*)\.html?$ index.php?seo_path=$1 [L,QSA]
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
    private function _getCategoryList($rebuild = false)
    {
        $language = Session::getInstance()->has('language', 'client') ? Session::getInstance()->get('language', 'client') : Language::getInstance()->current();
        if ($rebuild || ($this->_cat_dirs = Cache::getInstance()->read('seo.category.list.'.$language)) === false) {
            //   if (($results = $GLOBALS['db']->select('CubeCart_category', array('cat_id', 'cat_name', 'cat_parent_id'), array('hide' => '0'), array('cat_id' => 'DESC'))) !== false) {
            $query = sprintf("SELECT C.cat_id, C.cat_name, C.cat_parent_id, S.path FROM `%1\$sCubeCart_category` as C LEFT JOIN `%1\$sCubeCart_seo_urls` as S ON S.item_id=C.cat_id AND S.type='cat' AND S.custom='1' ORDER BY C.cat_id DESC", $GLOBALS['config']->get('config', 'dbprefix'));
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

                if (!empty($this->_cat_dirs)) {
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
     * @param string $url
     * @return bool
     */
    private static function _safeUrl($url)
    {
        $url = trim($url);
        $url = function_exists('mb_strtolower') ? mb_strtolower($url) : strtolower($url);
        $url = preg_replace("/\.\w{2,4}$/", '', $url);
        $url = str_replace(' ', '-', html_entity_decode($url, ENT_QUOTES));
        $url = preg_replace('#[^\w\-_/]#iuU', '-', str_replace('/', '/', $url));
        $url = preg_replace(array('#/{2,}#iu', '#-{2,}#'), array('/', '-'), $url);
        return trim($url, '-');
    }

    /**
     * Create sitemap link
     *
     * @param string $input
     * @param string $updated
     * @param string $type
     */
    private function _sitemap_link($input, $updated = false, $type = false)
    {
        $updated =  (!$updated || "0000-00-00" == substr($updated, 0, 10)) ? 'NOW' : $updated;

        $dateTime = new DateTime($updated);
        $updated = $dateTime->format(DateTime::W3C);

        if (!isset($input['url']) && !empty($type)) {
            $input['url'] = $this->_sitemap_base_url.'/'.$this->generatePath($input['id'], $type, '', false, true);
        }

        $this->_sitemap_xml->startElement('url');
        $this->_sitemap_xml->setElement('loc', htmlspecialchars($input['url']), false, false);
        $this->_sitemap_xml->setElement('lastmod', $updated, false, false);
        $this->_sitemap_xml->endElement();
    }
}
