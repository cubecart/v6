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
 * GUI controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @author Sir William
 * @since 5.0.0
 */
class GUI
{

    /**
     * Bread crumbs
     *
     * @var array of strings
     */
    private $_breadcrumb = array();

    /**
     * Is useragent mobile or not
     *
     * @var bool
     */
    public $mobile   = false;

    /**
     * Product Images
     *
     * @var array
     */
    public $_product_images = array();
    
    /**
     * Do we have any sale items?
     *
     * @var bool
     */
    private $_sale_items = false;

    /**
     * Current skin
     *
     * @var string
     */
    private $_skin   = '';
    /**
     * Current skin data
     *
     * @var array
     */
    private $_skin_data  = array();
    /**
     * Current available skins
     *
     * @var array
     */
    private $_skins   = array();
    /**
     * Current style
     *
     * @var string
     */
    private $_style   = '';
    /**
     * Template directory for smarty
     *
     * @var string
     */
    private $_logo   = '';
    /**
     * Document root relative path to the store logo
     *
     * @var string
     */
    private $_template_dir = '';
    /**
     * Postfix string for mobile config variables
     *
     * @var string
     */
    private $_skin_config_postfix = '';

    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;

    ##############################################

    final protected function __construct($admin = false)
    {
        $this->recaptchaValidate();

        //Get current skins
        $this->_skins = $this->listSkins();

        if (!$admin) {
            $this->_detectMobile();

            if ($GLOBALS['config']->get('config', 'skin_change')) {
                // Switch Skins
                if (isset($_POST['select_skin']) && !empty($_POST['select_skin']) && ($switch = $_POST['select_skin']) || isset($_GET['select_skin']) && !empty($_GET['select_skin']) && ($switch = $_GET['select_skin'])) {
                    list($skin, $style) = explode('|', $switch);
                    if (isset($this->_skins[$skin])) {
                        $GLOBALS['session']->set('skin', $skin, 'client');
                        $GLOBALS['session']->set('style', $style, 'client');
                    }
                    httpredir(currentPage(array('select_skin')));
                }
            }

            //Setup skin and style
            $this->_setSkin();
            $this->_setStyle();

            //Setup current skin data
            $this->_skin_data = $this->_skins[$this->_skin];

            // Make sure CSRF is enabled if skin has it enabled
            if ($this->_skin_data['info']['csrf'] && $GLOBALS['config']->get('config', 'csrf')!=='1') {
                $GLOBALS['config']->set('config', 'csrf', '1');
            } elseif (!$this->_skin_data['info']['csrf'] && $GLOBALS['config']->get('config', 'csrf')=='1') {
                $GLOBALS['config']->set('config', 'csrf', '0');
            }

            //Set smarty to the skin
            $GLOBALS['smarty']->template_dir = CC_ROOT_DIR.'/skins/'.$this->_skin.'/';
            $this->_template_dir = CC_ROOT_DIR.'/skins/'.$this->_skin.'/';

            //Give smarty the logo
            $this->_setLogo();
            if (empty($this->_logo)) {
                $this->rebuildLogos();
                $this->_setLogo();
            }
            $GLOBALS['smarty']->assign('STORE_LOGO', CC_STORE_URL.'/'.$this->_logo);

            //CSS hooks
            /**
             * Append the $css array with paths to the CSS.
             * The store URL gets appended to the beginning
             * ie $css[] = 'skins/test/syles/style.css';
             */
            $css = array();
            foreach ($GLOBALS['hooks']->load('class.gui.css') as $hook) {
                include $hook;
            }
            $GLOBALS['smarty']->assign('CSS', $css);

            // <head> JS
            $head_js = array();
            foreach ($GLOBALS['hooks']->load('class.gui.head_js') as $hook) {
                include $hook;
            }
            $GLOBALS['smarty']->assign('HEAD_JS', $head_js);

            // <body> JS (Top)
            $body_js_top = array();
            foreach ($GLOBALS['hooks']->load('class.gui.body_js_top') as $hook) {
                include $hook;
            }
            $GLOBALS['smarty']->assign('BODY_JS_TOP', $body_js_top);

            // <body> JS
            $body_js = array();
            foreach ($GLOBALS['hooks']->load('class.gui.body_js') as $hook) {
                include $hook;
            }
            $GLOBALS['smarty']->assign('BODY_JS', $body_js);

            //Put in the javascripts
            $js = glob('skins/'.$this->_skin.'/'.'{js,scripts}/*.js', GLOB_BRACE);
            foreach ($GLOBALS['hooks']->load('class.gui.javascripts') as $hook) {
                include $hook;
            }

            $GLOBALS['smarty']->assign('JS_SCRIPTS', $js);

            //Put in the live help
            if (($livehelp_plugins = $GLOBALS['db']->select('CubeCart_modules', array('folder'), array('module' => 'livehelp', 'status' => '1'))) !== false) {
                $livehelp_html = array();
                foreach ($livehelp_plugins as $plugin) {
                    $file_path = CC_ROOT_DIR.'/modules/livehelp/'.$plugin['folder'].'/livehelp.class.php';
                    if (file_exists($file_path)) {
                        if (!class_exists($plugin['folder'])) {
                            include $file_path;
                        }
                        $livehelp_plugin = new $plugin['folder']($GLOBALS['config']->get($plugin['folder']));
                        $livehelp_html[] = $livehelp_plugin->invocationHTML();
                    }
                }
                $GLOBALS['smarty']->assign('LIVE_HELP', implode("\r\n", $livehelp_html));
            }


            //Setup copyright
            $copyright = $GLOBALS['smarty']->fetch('string:'.stripslashes($GLOBALS['config']->get('config', 'store_copyright')));
            $GLOBALS['smarty']->assign('COPYRIGHT', $copyright);

            //Setup Google Analytics
            $google_analytics = $GLOBALS['config']->get('config', 'google_analytics');
            if (!empty($google_analytics)) {
                // Third party cookies are needed for this define to we can warn
                $GLOBALS['smarty']->assign('ANALYTICS', $google_analytics);
            }
        } else {
            $skin_folder = (!$GLOBALS['config']->isEmpty('config', 'admin_skin')) ? $GLOBALS['config']->get('config', 'admin_skin') : 'default';
            $admin_folder  = (!$GLOBALS['config']->isEmpty('config', 'adminFolder')) ? $GLOBALS['config']->get('config', 'adminFolder') : 'admin';
            $admin_file  = (!$GLOBALS['config']->isEmpty('config', 'adminFile')) ? $GLOBALS['config']->get('config', 'adminFile') : 'admin.php';
            $skin_root  = $admin_folder.'/'.'skins';
            if (strstr($skin_root, CC_ROOT_DIR)) {
                $skindir = $skin_root;
            } else {
                if (!$GLOBALS['config']->isEmpty('config', 'admin_skin') && file_exists($skin_root.'/'.$skin_folder) && is_dir($skin_root.'/'.$skin_folder)) {
                    $skindir = CC_ROOT_DIR.'/'.$skin_root.'/'.$skin_folder;
                } else {
                    $skindir = CC_ROOT_DIR.'/'.$skin_root.'/'.'default'.'/';
                }
            }

            if (substr($skindir, -1) != '/' && substr($skindir, -1) != '\\') {
                $skindir .= '/';
            }

            // Assign global admin skin variables
            $skin_data['admin_folder']   = $admin_folder;
            $skin_data['admin_file']   = $admin_file;
            $skin_data['skin_folder']   = $skin_folder;
            $skin_data['clear_cache_link']   = currentPage(null, array('clear_cache' => 'true'));
            $GLOBALS['smarty']->assign('SKIN_VARS', $skin_data);
            $GLOBALS['smarty']->template_dir = $this->_template_dir = $skindir;
        }

        //Assign common GUI parts
        $GLOBALS['smarty']->assign('VAL_SELF', currentPage());
        $GLOBALS['smarty']->assign('STORE_URL', $GLOBALS['storeURL']);
        $GLOBALS['smarty']->assign('ROOT_PATH', $GLOBALS['rootRel']);
        $GLOBALS['smarty']->assign('CURRENT_PAGE', currentPage());
        define('SESSION_TOKEN', $GLOBALS['session']->getToken());
        $GLOBALS['smarty']->assign('SESSION_TOKEN', SESSION_TOKEN);
        $GLOBALS['smarty']->assign('CATALOGUE_MODE', $GLOBALS['config']->get('config', 'catalogue_mode'));
        $config = $GLOBALS['config']->get('config');
        $config['store_country_name'] = getCountryFormat($config['store_country']);
        $GLOBALS['smarty']->assign('CONFIG', $config);
        ## Version hash to refresh CSS on each version load (storeURL added to prevent possible version disclosure)
        $GLOBALS['smarty']->assign('VERSION_HASH', md5($GLOBALS['storeURL'].CC_VERSION));
    }

    /**
     * Setup the instance (singleton)
     *
     * @param bool
     * @return GUI
     */
    public static function getInstance($admin = false)
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($admin);
        }

        return self::$_instance;
    }

    //=====[ Public ]=======================================

    /**
     * Add a breadcrumb
     *
     * @param string $name
     * @param array $url
     */
    public function addBreadcrumb($name, $url = array(), $replace = false)
    {
        if ($replace) {
            $this->_breadcrumb = array();
        }

        if (is_array($url) && !empty($url)) {
            $href = '?'.http_build_query($url);
        } else {
            if (empty($url)) {
                $url = array(
                    '_g' => $_GET['_g'],
                    'node' => (!empty($this->_breadcrumb) && isset($_GET['node'])) ? $_GET['node'] : 'index',
                );
                if (isset($_GET['mode'])) {
                    $url = array_merge($url, array('mode' => $_GET['mode']));
                }
                $href = '?'.http_build_query($url);
            } else {
                $href = $url;
            }
        }

        if (!empty($name)) {
            $this->_breadcrumb[] = array(
                'url' => $GLOBALS['seo']->SEOable($href),
                'title' => ucfirst(strip_tags(str_replace('_', ' ', $name)))
            );
        }
    }

    /**
     * Change template location
     *
     * @param string $directory
     */
    public function changeTemplateDir($directory = '')
    {
        if (empty($directory)) {
            $GLOBALS['smarty']->template_dir = $this->_template_dir;
        } else {
            $GLOBALS['smarty']->template_dir = $directory;
        }
    }

    /**
     * Display output
     *
     * @param string $file
     */
    public function display($file)
    {
        $this->_setCanonical();
        $GLOBALS['debug']->stream_into_session = false;
        if ($GLOBALS['config']->get('config', 'csrf')=='1' || CC_IN_ADMIN) {
            die(preg_replace('#</form>#i', '<input type="hidden" name="token" value="'.SESSION_TOKEN.'"></form>', $GLOBALS['smarty']->fetch($file)));
        } else {
            $GLOBALS['smarty']->display($file);
        }
    }

    /**
     * Display common GUI parts like the boxes
     *
     * @param bool $admin
     * @param array $url
     */
    public function displayCommon()
    {
        if (!CC_IN_ADMIN) {
            if (!isset($_GET['_a']) || $_GET['_a'] != 'template') {
                $this->_displayLanguageSwitch();
                $this->_displayCurrencySwitch();
                $this->_displaySessionBox();
                if (!in_array($_GET['_a'], array('basket', 'cart', 'complete', 'checkout', 'confirm', 'gateway')) && !$GLOBALS['config']->get('config', 'catalogue_mode')) {
                    $this->displaySideBasket();
                }
            }
            $this->_displaySearchBox();
            $this->_displaySaleItems();
            $this->_displayMailingList();
            $this->_displayDocuments();
            $this->_displayRandomProduct();
            $this->_displayPopularProducts();
            $this->_displaySkinSelect();
            $this->_displaySocial();
            $this->_displayNavigation();
            /*! display common hooks */
            foreach ($GLOBALS['hooks']->load('class.gui.display') as $hook) {
                include $hook;
            }
            if (isset($display_html) && is_array($display_html)) {
                foreach ($display_html as $html) {
                    $GLOBALS['smarty']->assign($html['macro_name'], $html['html']);
                }
            }
            $GLOBALS['seo']->displayMetaData();
        }

        //Setup HTML lang
        $GLOBALS['smarty']->assign('HTML_LANG', $GLOBALS['language']->getLanguage());

        //Setup bread crumbs
        $GLOBALS['smarty']->assign('CRUMBS', $this->_breadcrumb);
        //Show any errors or warnings
        $this->_displayErrors();
        // Display cookie warning is needed
        $this->_displayCookieDialogue();
    }

    /**
     * Display / Generate Side Basket
     *
     * @return string/bool
     */
    public function displaySideBasket()
    {

        // Display the basket sidebar
        $basket_total = 0;
        $basket_items = 0;

        if (($contents = $GLOBALS['cart']->get()) !== false) {
            $gc = $GLOBALS['config']->get('gift_certs');
            $vars = array();
            foreach ($contents as $hash => $product) {
                $product['name_abbrev'] = (strlen($product['name']) >= 15) ? substr($product['name'], 0, 15).'&hellip;' : $product['name'];
                $product['total']  = $GLOBALS['tax']->priceFormat($product['price_display']);
                if (isset($gc['product_code']) && $product['product_code'] == $gc['product_code']) {
                    $product['link'] = $GLOBALS['seo']->buildURL('certificates');
                } else {
                    $product['link'] = $GLOBALS['seo']->buildURL('prod', $product['product_id']);
                }
                $product['image'] = $this->getProductImage($product['product_id']);
                $vars['contents'][$hash] = $product;
                $basket_total += $product['price_display'];
                $basket_items += $product['quantity'];
            }
            $GLOBALS['smarty']->assign('CONTENTS', array_reverse($vars['contents'], true));
            $GLOBALS['smarty']->assign('CART_ITEMS', $basket_items);
        }
        $GLOBALS['smarty']->assign('CART_TOTAL', isset($this->_total) ? Tax::getInstance()->priceFormat($this->_total) : $GLOBALS['tax']->priceFormat($basket_total));
        $button = array(
            'link'  => $GLOBALS['storeURL'].'/index.php?_a=basket',
            'text'  => $GLOBALS['language']->basket['view_basket']
        );
        $GLOBALS['smarty']->assign('BUTTON', $button);
        foreach ($GLOBALS['hooks']->load('class.gui.display_side_basket') as $hook) {
            include $hook;
        }
        $content = $GLOBALS['smarty']->fetch('templates/box.basket.php');
        $GLOBALS['smarty']->assign('SHOPPING_CART', $content);

        if (isset($_GET['_g']) && $_GET['_g'] == 'ajaxadd') {
            return $content;
        } else {
            return true;
        }
    }

    /**
     * Get current bread crumbs
     *
     * @return string/false
     */
    public function getBreadcrumbs()
    {
        return (!empty($this->_breadcrumb)) ? $this->_breadcrumb : false;
    }

    /**
     * Get custom template for module from default skin
     *
     * @param string $type
     * @param string $dirname
     * @param string $file_name
     * @return string
     */
    public function getCustomModuleSkin($type = 'gateway', $dirname, $file_name)
    {
        $root_path  = CC_ROOT_DIR.'/skins/'.$GLOBALS['config']->get('config', 'skin_folder').'/'.'templates/modules/'.$type.'/'.basename($dirname);
        return file_exists($root_path.'/'.$file_name) ? $root_path : $dirname.'/'.'skin';
    }

    /**
     * Get current logo
     *
     * @param bool $absolute
     * @param string $type
     * @return string
     */
    public function getLogo($absolute = false, $type = '')
    {
        if (!empty($type)) {
            $this->_setLogo($type);
        } elseif (empty($this->_logo)) {
            $this->_setLogo();
        }
        return ($absolute) ? $GLOBALS['storeURL'].'/'.$this->_logo : $this->_logo;
    }

    /**
     * Get a product image
     *
     * @param int $product_id
     * @param string $mode
     *
     * @return image path/false
     */
    public function getProductImage($product_id = false, $mode = 'small')
    {
        
        // If array take first key that exists (This is for backward compatibility to ensure old skins still work).
        if (is_array($mode)) {
            foreach ($mode as $mode_name) {
                if (isset($this->_skin_data['images'][$mode_name])) {
                    $mode = $mode_name;
                    break;
                }
            }
        }

        if (is_numeric($product_id)) {
            $this->_product_images[$product_id] = isset($this->_product_images[$product_id]) ? $this->_product_images[$product_id] : $GLOBALS['db']->select('CubeCart_image_index', false, array('product_id' => $product_id), array('main_img' => 'DESC'), 1);

            if ($this->_product_images[$product_id]) {
                return $GLOBALS['catalogue']->imagePath($this->_product_images[$product_id][0]['file_id'], $mode, 'url');
            }
        }

        if (isset($this->_skin_data['images'][$mode])) {
            $default = $this->_skin_data['images'][$mode]['default'];
            
            if ($this->_style !== '' && $this->_skin_data['styles'][$this->_style]['images']) { // do we use a separate style folder for images?
                $files = glob('skins/'.$this->_skin.'/'.'images/{common,'.$this->_style.'}/'.$default, GLOB_BRACE);
            } else {
                $files = glob('skins/'.$this->_skin.'/'.'images/'.$default, GLOB_BRACE);
            }
            if ($files) {
                //echo str_replace(array('//', '/'), '/', $files[0]);
                return $GLOBALS['catalogue']->imagePath(str_replace(array('//', '/'), '/', $files[0]), $mode);
            }
        }
        return false;
    }

    /**
     * Get current skin
     *
     * @return string
     */
    public function getSkin()
    {
        if (empty($this->_skin)) {
            $this->_setSkin();
        }
        return $this->_skin;
    }

    /**
     * Get current skin data
     *
     * @return array
     */
    public function getSkinData()
    {
        if ($this->_skin_data) {
            return $this->_skin_data;
        } else {
            //Setup skin and style
            $this->_setSkin();
            $this->_setStyle();

            //Setup current skin data
            return $this->_skin_data = $this->_skins[$this->_skin];
        }
    }

    /**
     * Get current style
     *
     * @return string
     */
    public function getStyle()
    {
        if (empty($this->_style)) {
            $this->_setStyle();
        }
        return $this->_style;
    }
    
    /**
     * Get number items per page
     *
     * @param string $list_id
     * @param string $page_key
     * @return int
     */
    public function itemsPerPage($list_id = 'products', $page_key = 'perpage')
    {
        if (isset($_GET[$page_key]) && (int)$_GET[$page_key]>0) {
            return (int)$_GET[$page_key];
        }

        if (isset($this->_skin_data['layout'][$list_id]['perpage'])) {
            foreach ($this->_skin_data['layout'][$list_id]['perpage'] as $amount => $default) {
                if ($default) {
                    return (int)$amount;
                }
            }
        }

        if (is_numeric($GLOBALS['config']->get('config', 'catalogue_products_per_page'))) {
            return $GLOBALS['config']->get('config', 'catalogue_products_per_page');
        } else { // Last ditch ..
            return 10;
        }
    }

    public function getSkinConfig($path = '', $foldername = '')
    {
        if (!empty($foldername)) {
            $path = CC_ROOT_DIR.'/skins/'.$foldername.'/config.xml';
        }
        if (!empty($path) && file_exists($path)) {
            $xml = file_get_contents($path, true);
            return new SimpleXMLElement($xml);
        }
        return false;
    }

    /**
     * Get the currently available skins
     *
     * @return array of skin data
     */
    public function listSkins()
    {
        if (($skins = $GLOBALS['cache']->read('info.skins.list')) !== false) {
            return $skins;
        } else {
            foreach (glob(CC_ROOT_DIR.'/skins/*/config.xml') as $data_file) {
                $data = $this->getSkinConfig($data_file);
                                
                if ($data) {
                    $skins[(string)$data->info->{'name'}]['info'] = array(
                        'name'   	=> (string)$data->info->{'name'},
                        'display'  	=> (string)$data->info->{'display'},
                        'version'  	=> (string)$data->info->{'version'},
                        'compatible'=> array(
                            'min' => (string)$data->info->{'minVersion'},
                            'max' => (string)$data->info->{'maxVersion'},
                        ),
                        'creator'  	=> (string)$data->info->{'creator'},
                        'homepage'  => (string)$data->info->{'homepage'},
                        'mobile'  	=> ((string)$data->info->{'mobile'}=='true') ? true : false,
                        'responsive'=> ((string)$data->info->{'responsive'}=='true') ? true : false,
                        'csrf'=> ((string)$data->info->{'csrf'}=='true') ? true : false,
                        'newsletter_recaptcha'=> ((string)$data->info->{'newsletter_recaptcha'}=='true') ? true : false
                    );
            
                    if (is_object($data->layout)) {
                        foreach ($data->layout as $layout) {
                            foreach ($layout as $section => $v) {
                                if (is_object($v)) {
                                    foreach ($v->perpage as $attribs) {
                                        $skins[(string)$data->info->{'name'}]['layout'][(string)$section]['perpage'][(string)$attribs['amount']] = (bool)$attribs->attributes()->{'default'};
                                    }
                                }
                            }
                        }
                    }
                    // Substyles
                    if ($data->styles) {
                        $i = 0;
                        foreach ($data->styles->style as $style) {
                            $name = (string)$style->{'directory'};
                            $record = array(
                                'name'   => (string)$style->{'name'},
                                'description' => (string)$style->{'description'},
                                'directory'  => $name,
                                'images'  => (bool)($style->attributes()->{'images'} == 'true'),
                                'default'  => (bool)($style->attributes()->{'default'} == 'true'),
                            );
                            $skins[(string)$data->info->{'name'}]['styles'][$name] = $record;
                            $i++;
                        }
                    }
                    // Image sizes
                    if ($data->images) {
                        foreach ($data->images->image as $key => $image) {
                            $attrib = $image->attributes();
                            $skins[(string)$data->info->{'name'}]['images'][(string)$attrib->{'reference'}] = array(
                                'maximum' => (int)$attrib->{'maximum'},
                                'quality' => (int)$attrib->{'quality'},
                                'default' => (string)$attrib->default,
                            );
                        }
                    }
                }
                foreach ($GLOBALS['hooks']->load('class.gui.skin_data') as $hook) {
                    include $hook;
                }
            }
            
            if (isset($skins)) {
                $GLOBALS['cache']->write($skins, 'info.skins.list');
            }

            return $skins;
        }

        return false;
    }
    

    /**
     * Get page splits
     *
     * @param string $list_id
     * @param string $page_key
     * @return int
     */
    public function perPageSplits($list_id = 'products', $page_key = 'perpage')
    {
        if (isset($this->_skin_data['layout'][$list_id]['perpage'])) {
            foreach ($this->_skin_data['layout'][$list_id]['perpage'] as $amount => $default) {
                if (!isset($_GET[$page_key]) && $default) {
                    $selected = true;
                } elseif (isset($_GET[$page_key]) && $_GET[$page_key]==$amount) {
                    $selected = true;
                } else {
                    $selected = false;
                }
                    
                $page_splits[] = array('selected' => $selected ,'url' => currentPage(null, array($page_key => $amount)), 'amount' => $amount);
            }
            return $page_splits;
        }
        return false;
    }

    /**
     * Do we require Recaptcha check?
     */
    public function recaptchaRequired()
    {
        if ($GLOBALS['config']->get('config', 'recaptcha') && !$GLOBALS['session']->get('confirmed', 'recaptcha')) {
            $GLOBALS['smarty']->assign('RECAPTCHA', $GLOBALS['config']->get('config', 'recaptcha'));
            return true;
        }
        return false;
    }

    /**
     * Validate recaptcha response
     */
    public function recaptchaValidate()
    {
        if ($this->recaptchaRequired()) {
            $recaptcha['error'] = null;
            $recaptcha['confirmed'] = false;

            if (empty($_POST['g-recaptcha-response'])) {
                $recaptcha['error'] = $GLOBALS['language']->form['verify_human_fail'];
            } else {
                $g_data = array(
                    'secret' => $GLOBALS['config']->get('config', 'recaptcha_secret_key'),
                    'response' => $_POST['g-recaptcha-response'],
                    'remoteip' => get_ip_address()
                );
                $request = new Request('www.google.com', '/recaptcha/api/siteverify');
                $request->setMethod('get');
                $request->cache(false);
                $request->setSSL();
                $request->setData($g_data);

                $response = $request->send();
                $g_result = json_decode($response);
                
                if ($g_result->success) {
                    $recaptcha['confirmed'] = true;
                } else {
                    $recaptcha['error'] = $GLOBALS['language']->form['verify_human_fail'];
                }
            }
            $GLOBALS['session']->set('', $recaptcha, 'recaptcha');
        } elseif (!$GLOBALS['session']->get('confirmed', 'recaptcha')) {
            $GLOBALS['session']->delete('', 'recaptcha');
        }
    }

    /**
     * Rebuild logo
     */
    public function rebuildLogos()
    {
        if ($logos = $GLOBALS['db']->select('CubeCart_logo', false, array('status' => 1))) {
            foreach ($logos as $logo) {
                $skin = (!empty($logo['skin'])) ? $logo['skin'] : 'all';
                $style = (!empty($logo['style'])) ? $logo['style'] : 'all';
                $custom[$skin][$style] = $logo['filename'];
            }
        } else {
            $custom = null;
        }

        $skins = glob(CC_ROOT_DIR.'/skins/*/config.xml');

        /* Add logos for extra templates */
        $extra_templates = array('emails', 'invoices');

        if ($skins) {
            foreach ($skins as $skin) {
                $xml = $this->getSkinConfig($skin);
                if (isset($xml->styles)) {
                    ## List substyles
                    foreach ($xml->styles->style as $style) {
                        $default[(string)$xml->info->name][(string)$style->directory] = ((string)$style->attributes()->images == 'true') ? true : false;
                    }
                } else {
                    ## Only one style here
                    $default[(string)$xml->info->name] = true;
                }
            }
        }

        foreach ($default as $skin => $data) {
            if (is_array($data)) {
                ## Has substyles
                foreach ($data as $style => $value) {
                    if (isset($custom['all'])) {
                        $target = 'images/logos/'.$custom['all']['all'];
                    } elseif (isset($custom[$skin]['all'])) {
                        $target = 'images/logos/'.$custom[$skin]['all'];
                    } elseif (isset($custom[$skin][$style])) {
                        ## Use custom logo
                        $target = 'images/logos/'.$custom[$skin][$style];
                    } else {
                        ## Look for default logo
                        $target = $this->_getLogoDefault($skin, $style);
                    }
                    $logo_config[$skin.$style] = $target;
                }
            } else {
                ## Basic skin with no substyles
                if (isset($custom['all'])) {
                    $target = 'images/logos/'.$custom['all']['all'];
                } elseif (isset($custom[$skin])) {
                    ## Use custom logo
                    $target = 'images/logos/'.$custom[$skin]['all'];
                } else {
                    ## Look for default logo
                    $target = $this->_getLogoDefault($skin);
                }
                $logo_config[$skin] = $target;
            }
        }

        foreach ($extra_templates as $type) {
            if (isset($custom[$type])) {
                ## Use custom logo
                $target = 'images/logos/'.$custom[$type]['all'];
            } else {
                ## Look for default logo
                $target = $this->getLogo();
            }
            $logo_config[$type] = $target;
        }
        $GLOBALS['config']->set('logos', false, str_replace('/', '/', $logo_config)); // Save skin and extra templates
    }

    /**
     * Set an error message
     *
     * @param string $message
     * @param bool admin_only
     */
    public function setError($message = null, $admin_only = false)
    {
        if ($admin_only && !ADMIN_CP) {
            return false;
        }
        $this->_errorMessage('error', $message);
    }

    /**
     * Set a notification message
     *
     * @param string $message
     * @param bool admin_only
     */
    public function setNotify($message = null, $admin_only = false)
    {
        if ($admin_only && !ADMIN_CP) {
            return false;
        }
        $this->_errorMessage('notice', $message);
    }

    /**
     * Set a notification message
     *
     * @param string $message
     * @param bool admin_only
     */
    public function setInfo($message = null, $admin_only = false)
    {
        if ($admin_only && !ADMIN_CP) {
            return false;
        }
        $this->_errorMessage('info', $message);
    }

    //=====[ Private ]=======================================

    /**
     * Detect mobile phone browser. Open Source code thanks to http://detectmobilebrowsers.com
     *
     * @param string $useragent
     * @return bool
     */
    private function _detectMobile($useragent = '')
    {
        if (isset($_GET['display_mobile'])) {
            $GLOBALS['session']->set('display_mobile', (bool)$_GET['display_mobile']);
            httpredir('index.php');
        }

        if ($GLOBALS['config']->get('config', 'disable_mobile_skin')) {
            $mobile = false;
        } elseif ($GLOBALS['session']->has('display_mobile')) {
            $mobile = $GLOBALS['session']->get('display_mobile');
        } else {
            $useragent = empty($useragent) ? $_SERVER['HTTP_USER_AGENT'] : $useragent;
            if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) {
                $mobile = true;
            } else {
                $mobile = false;
            }
        }

        if ($mobile) {
            $this->mobile = $mobile;
            $this->_skin_config_postfix = '_mobile';
        } else {
            $this->mobile = $mobile;
            $this->_skin_config_postfix = '';
        }

        return $this->mobile;
    }

    /**
     * Display cookie acceptance dialogue
     */
    private function _displayCookieDialogue()
    {
        if (CC_IN_ADMIN) {
            return false;
        }

        //If there is a session id already unset and destory it
        if (isset($_POST['accept_cookies_submit'])) {
            $accept = (isset($_POST['accept_cookies'])) ? true : false;
            $GLOBALS['session']->set_cookie('accept_cookies', true, time()+31536000);
            httpredir();
        }
        if (Config::getInstance()->get('config', 'cookie_dialogue') && !isset($_COOKIE['accept_cookies'])) {
            if ($privacy = $GLOBALS['db']->select('CubeCart_documents', 'doc_id', array('doc_privacy' => '1'))) {
                $dialogue = str_replace(array('%s','%PRIVACY_URL%'), array($GLOBALS['config']->get('config', 'store_name'), $GLOBALS['seo']->buildURL('doc', $privacy[0]['doc_id'])), $GLOBALS['language']->notification['cookie_dialogue']);
            } else {
                $dialogue = str_replace('%s', $GLOBALS['config']->get('config', 'store_name'), $GLOBALS['language']->notification['cookie_dialogue']);
                $dialogue = preg_replace('/<\/?a[^>]*>/', '', $dialogue);
            }
            $GLOBALS['smarty']->assign('COOKIE_DIALOGUE_TEXT', $dialogue);
            $log_hash = md5($dialogue);
            if(!$GLOBALS['db']->select('CubeCart_cookie_consent', false, array('log_hash' => $log_hash, 'session_id' => $GLOBALS['session']->getId()), false, 1, false, false)) {
                $consent_log = array(
                    'ip_address' => get_ip_address(),
                    'session_id' => $GLOBALS['session']->getId(),
                    'customer_id' => $GLOBALS['user']->getId(),
                    'log' => $dialogue,
                    'log_hash' => $log_hash,
                    'url_shown' => currentPage(),
                    'time' => time()
                );
                $GLOBALS['db']->insert('CubeCart_cookie_consent', $consent_log);
            }
            $GLOBALS['smarty']->assign('COOKIE_DIALOGUE', true);
        } else {
            $GLOBALS['smarty']->assign('COOKIE_DIALOGUE', false);
        }
    }


    /**
     * Display currency switch box
     */
    private function _displayCurrencySwitch()
    {
        if (!$GLOBALS['smarty']->templateExists('templates/box.currency.php')) {
            return false;
        }

        if (($currencies = $GLOBALS['db']->select('CubeCart_currency', false, array('active' => '1'))) !== false) {
            if (count($currencies) > 0) {
                $vars = array();
                foreach ($currencies as $offset => $currency) {
                    $currency['selected']  = ($GLOBALS['session']->has('currency', 'client') && $GLOBALS['session']->get('currency', 'client') == $currency['code'] || !$GLOBALS['session']->has('currency', 'client') && $GLOBALS['config']->get('config', 'default_currency') == $currency['code']) ? 'selected="selected"' : '';
                    $currency['url']   = currentPage(null, array('set_currency' => $currency['code']));
                    $currency['css']   = ($currency['selected']) ? 'current' : '';
                    $vars[]      = $currency;
                    if ($currency['selected']) {
                        $GLOBALS['smarty']->assign('CURRENT_CURRENCY', $currency);
                    }
                }
                foreach ($GLOBALS['hooks']->load('class.gui.display_currency_switch') as $hook) {
                    include $hook;
                }
                $GLOBALS['smarty']->assign('CURRENCIES', $vars);
                $content = $GLOBALS['smarty']->fetch('templates/box.currency.php');
                $GLOBALS['smarty']->assign('CURRENCY', $content);
            }
        }
    }

    /**
     * Display document content
     */
    private function _displayDocuments()
    {
        if (!$GLOBALS['smarty']->templateExists('templates/box.documents.php')) {
            return false;
        }

        $vars = array();
        if (($docs = $GLOBALS['db']->select('CubeCart_documents', false, array('doc_parent_id' => '0', 'doc_status' => '1', 'navigation_link' => 1), '`doc_order` ASC')) !== false) {
            foreach ($docs as $doc) {
                if ($doc['doc_home']) {
                    continue;
                }

                if ($GLOBALS['language']->translateDocument($doc)) {
                    if (empty($doc['doc_url'])) {
                        $doc['doc_url'] = $GLOBALS['seo']->buildURL('doc', $doc['doc_id']);
                    }

                    $vars[] = $doc;
                }
            }
        }

        // Include contact form, if enabled
        if (($contact = $GLOBALS['config']->get('Contact_Form')) !== false) {
            if (isset($contact['status']) && $contact['status']) {
                $contact_url = $GLOBALS['seo']->buildURL('contact');
                $GLOBALS['smarty']->assign('CONTACT_URL', $contact_url);
            }
        }
        foreach ($GLOBALS['hooks']->load('class.gui.display_documents') as $hook) {
            include $hook;
        }
        $GLOBALS['smarty']->assign('DOCUMENTS', $vars);
        $documents_list_hooks = array();
        foreach ($GLOBALS['hooks']->load('class.gui.documents.list') as $hook) {
            include $hook;
        }
        $GLOBALS['smarty']->assign('DOCUMENTS_LIST_HOOKS', $documents_list_hooks);
        $content = $GLOBALS['smarty']->fetch('templates/box.documents.php');
        $GLOBALS['smarty']->assign('SITE_DOCS', $content);
    }

    /**
     * Display errors
     */
    private function _displayErrors()
    {
        if ($GLOBALS['session']->has('GUI_MESSAGE')) {
            $GLOBALS['smarty']->assign('GUI_MESSAGE', $GLOBALS['session']->get('GUI_MESSAGE'));
            $GLOBALS['session']->delete('GUI_MESSAGE');
        }
    }

    /**
     * Display language switch box
     */
    private function _displayLanguageSwitch()
    {
        if (!$GLOBALS['smarty']->templateExists('templates/box.language.php')) {
            return false;
        }

        $lang_list = $GLOBALS['language']->listLanguages();
        $enabled = $GLOBALS['config']->get('languages');
        if (is_array($lang_list)) {
            foreach ($lang_list as $language) {
                if (preg_match(Language::LANG_REGEX, $language['code'], $match) && isset($match[2])) {
                    if (isset($enabled[$language['code']]) && !$enabled[$language['code']]) {
                        unset($lang_list[$language['code']]);
                    } elseif (isset($language['override']) && isset($lang_list[$match[1]]) && $match[1] != $GLOBALS['config']->get('config', 'default_language')) {
                        unset($lang_list[$match[1]]);
                    }
                }
            }
            if (count($lang_list) > 1) {
                foreach ($lang_list as $language) {
                    if ($GLOBALS['language']->current() == $language['code']) {
                        $language['selected'] = 'selected="selected"';
                        $current_language = $language;
                    } else {
                        $language['selected'] = '';
                    }
                    $language['url'] = currentPage(null, array('set_language' => $language['code']));
                    $language['css'] = ($language['selected']) ? 'current' : '';
                    $languages[] = $language;
                }
                foreach ($GLOBALS['hooks']->load('class.gui.display_language_switch') as $hook) {
                    include $hook;
                }
                $GLOBALS['smarty']->assign('current_language', $current_language);
                $GLOBALS['smarty']->assign('LANGUAGES', $languages);
                $content = $GLOBALS['smarty']->fetch('templates/box.language.php');
                $GLOBALS['smarty']->assign('LANGUAGE', $content);
            }
        }
    }

    /**
     * Display mailing list box
     */
    private function _displayMailingList()
    {
        if (!$GLOBALS['smarty']->templateExists('templates/box.newsletter.php')) {
            return false;
        }

        if ($GLOBALS['user']->is()) {
            $where = array('email' => $GLOBALS['user']->get('email'));
            if ((bool)$GLOBALS['config']->get('config', 'dbl_opt')) {
                $where['dbl_opt'] = '1';
            }
            $GLOBALS['smarty']->assign('CTRL_SUBSCRIBED', (bool)$GLOBALS['db']->select('CubeCart_newsletter_subscriber', false, $where, false, 1, false, true));
        }

        if (isset($_POST['subscribe'])) {
            $newsletter = Newsletter::getInstance();
            if (isset($_POST['force_unsubscribe']) && $_POST['force_unsubscribe']=='1') {
                $newsletter->unsubscribe($_POST['subscribe'], $GLOBALS['user']->getId());
            } elseif ($newsletter->subscribe($_POST['subscribe'], $GLOBALS['user']->getId())) {
                httpredir(currentPage());
            } else {
                httpredir(currentPage(null, array('_a' => 'newsletter')));
            }
        }

        foreach ($GLOBALS['hooks']->load('class.gui.display_mailing_list') as $hook) {
            include $hook;
        }
        $content = $GLOBALS['smarty']->fetch('templates/box.newsletter.php');
        $GLOBALS['smarty']->assign('MAIL_LIST', $content);
    }

    /**
     * Display navigation box
     */
    private function _displayNavigation()
    {
        if (!$GLOBALS['smarty']->templateExists('templates/box.navigation.php')) {
            return false;
        }

        $cache_id = 'html.'.$this->_skin.'.menu.'.$GLOBALS['language']->current();
        $serialize = false;
        if (($content = $GLOBALS['cache']->read($cache_id, $serialize)) == false) {
            
            //Get the navigation tree data
            $tree_data = $GLOBALS['catalogue']->getCategoryTree();
            //Make the navigation tree
            $navigation_tree = $this->_makeTree($tree_data);
            $GLOBALS['smarty']->assign('NAVIGATION_TREE', $navigation_tree);

            //Check for sales
            $GLOBALS['smarty']->assign('CTRL_SALE', $this->_sale_items);

            //Check for gift certs
            if (in_array($GLOBALS['config']->get('gift_certs', 'status'), array('1', '2'))) {
                $GLOBALS['smarty']->assign('CTRL_CERTIFICATES', true);
            } else {
                $GLOBALS['smarty']->assign('CTRL_CERTIFICATES', false);
            }

            $url = array(
                'saleitems' => $GLOBALS['seo']->buildURL('saleitems', false, '&', false),
                'certificates' => $GLOBALS['seo']->buildURL('certificates', false, '&', false)
            );
            $GLOBALS['smarty']->assign('URL', $url);
            //Fetch the navigation so we can cache it
            foreach ($GLOBALS['hooks']->load('class.gui.display_navigation.pre_cache') as $hook) {
                include $hook;
            }
            $content = $GLOBALS['smarty']->fetch('templates/box.navigation.php');
            $content = str_replace('/index.php/', '/', $content);
            
            $GLOBALS['cache']->write($content, $cache_id, '', $serialize);
        }

        foreach ($GLOBALS['hooks']->load('class.gui.display_navigation') as $hook) {
            include $hook;
        }

        // Rel path must be absolute to meet W3C requirememts (URL must not be cached)
        $content = str_replace('rel="'.CC_ROOT_REL, 'rel="'.CC_STORE_URL.'/', $content);
        //Send it to the main template
        $GLOBALS['smarty']->assign('CATEGORIES', $content);
    }

    /**
     * Display popular products box
     */
    private function _displayPopularProducts()
    {
        if (!$GLOBALS['smarty']->templateExists('templates/box.popular.php')) {
            return false;
        }

        if ((int)$GLOBALS['config']->get('config', 'catalogue_popular_products_count') < 1) {
            return false;
        }
        $limit = (is_numeric($GLOBALS['config']->get('config', 'catalogue_popular_products_count'))) ? $GLOBALS['config']->get('config', 'catalogue_popular_products_count') : 10;
        switch ((int)$GLOBALS['config']->get('config', 'catalogue_popular_products_source')) {
            case 1:  // sale-based
                $whereStr   = $GLOBALS['catalogue']->outOfStockWhere(false, 'i', true);
                $query  = "SELECT `oi`.`product_id`, `i`.`name`, `i`.`price`, `i`.`sale_price`, `i`.`tax_type`, `i`.`tax_inclusive`, SUM(`oi`.`quantity`) as `quantity` FROM `".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_order_inventory` as `oi` JOIN `".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_inventory` as `i` WHERE `oi`.`product_id` = `i`.`product_id` AND `i`.`status` = 1 $whereStr GROUP BY `oi`.`product_id` ORDER BY `quantity` DESC LIMIT ".$limit.";";
                $products = $GLOBALS['db']->query($query);
            break;
            default: // view-based
                $where      = $GLOBALS['catalogue']->outOfStockWhere(array('status' => '1'));
                $products = $GLOBALS['db']->select('CubeCart_inventory', array('name', 'product_id', 'quantity', 'price', 'sale_price', 'tax_type', 'tax_inclusive'), $where, 'popularity DESC', $limit);
        }
        if ($products) {
            foreach ($products as $product) {
                $category_data = $GLOBALS['catalogue']->getCategoryStatusByProductID($product['product_id']);
                $category_status = false;
                if (is_array($category_data)) {
                    foreach ($category_data as $trash => $data) {
                        if ($data['status'] == 1) {
                            $category_status = true;
                        }
                    }
                }
                if (!$category_status) {
                    continue;
                }

                $GLOBALS['language']->translateProduct($product);
                $product['url'] = $GLOBALS['seo']->buildURL('prod', $product['product_id']);

                $product['ctrl_sale'] = (!$GLOBALS['tax']->salePrice($product['price'], $product['sale_price']) || !$GLOBALS['config']->get('config', 'catalogue_sale_mode')) ? false : true;

                $GLOBALS['catalogue']->getProductPrice($product);
                $sale = $GLOBALS['tax']->salePrice($product['price'], $product['sale_price']);
                $product['price_unformatted'] = $product['price'];
                $product['sale_price_unformatted'] = ($sale) ? $product['sale_price'] : null;
                $product['price'] = $GLOBALS['tax']->priceFormat($product['price']);
                $product['sale_price'] = ($sale) ? $GLOBALS['tax']->priceFormat($product['sale_price']) : null;
                $product['image'] = $this->getProductImage($product['product_id']);
                $vars[] = $product;
            }
            foreach ($GLOBALS['hooks']->load('class.gui.display_popular_products') as $hook) {
                include $hook;
            }
            $GLOBALS['smarty']->assign('POPULAR', $vars);
            $content = $GLOBALS['smarty']->fetch('templates/box.popular.php');
            $GLOBALS['smarty']->assign('POPULAR_PRODUCTS', $content);
        }
    }

    /**
     * Display featured products box
     */
    private function _displayRandomProduct()
    {
        if (!$GLOBALS['smarty']->templateExists('templates/box.featured.php')) {
            return false;
        }

        foreach ($GLOBALS['hooks']->load('class.gui.display_random_product_pre') as $hook) {
            include $hook;
        }
        $featured_products  = $GLOBALS['db']->select('CubeCart_inventory', 'product_id', $GLOBALS['catalogue']->outOfStockWhere(array('status' => '1', 'featured' => '1')));
        $n = ($featured_products ? count($featured_products) : 0);
        if ($n > 0) {
            $random_product = false;
            $tries = 0;
            while (!$random_product && $tries < 15) {
                $random_id = $featured_products[mt_rand(0, $n - 1)]['product_id'];
                $category_data = $GLOBALS['catalogue']->getCategoryStatusByProductID($random_id);
                $category_status = false;
                if (is_array($category_data)) {
                    foreach ($category_data as $trash => $data) {
                        if ($data['status'] == 1 && $data['primary'] == 1) {
                            $category_status = true;
                            break;
                        }
                    }
                }
                if ($category_status) {
                    $random_product = $GLOBALS['db']->select('CubeCart_inventory', false, array('product_id' => $random_id), false, 1);
                }
                ++$tries;
            }
        }

        if ($random_product) {
            $image = $this->getProductImage($random_product[0]['product_id']);
            $product = $random_product[0];

            $GLOBALS['language']->translateProduct($product);

            $product['image'] = $image;

            $product['ctrl_sale'] = (!$GLOBALS['tax']->salePrice($product['price'], $product['sale_price']) || !$GLOBALS['config']->get('config', 'catalogue_sale_mode')) ? false : true;

            $GLOBALS['catalogue']->getProductPrice($product);
            $sale = $GLOBALS['tax']->salePrice($product['price'], $product['sale_price']);
            $product['price_unformatted']  = $product['price'];
            $product['sale_price_unformatted'] = ($sale) ? $product['sale_price'] : null;
            $product['price']  = $GLOBALS['tax']->priceFormat($product['price']);
            $product['sale_price'] = ($sale) ? $GLOBALS['tax']->priceFormat($product['sale_price']) : null;

            $product['ctrl_purchase'] = true;
            if ($product['use_stock_level']) {
                // Get Stock Level
                $stock_level = $GLOBALS['catalogue']->getProductStock($product['product_id']);
                if ((int)$stock_level <= 0) {
                    // Out of Stock
                    if (!$GLOBALS['config']->get('config', 'basket_out_of_stock_purchase')) {
                        // Not Allowed
                        $product['ctrl_purchase'] = false;
                    }
                }
            }
            $product['url'] = $GLOBALS['seo']->buildURL('prod', $product['product_id']);
            foreach ($GLOBALS['hooks']->load('class.gui.display_random_product') as $hook) {
                include $hook;
            }
            $GLOBALS['smarty']->assign('featured', $product);
            $content = $GLOBALS['smarty']->fetch('templates/box.featured.php');
            $GLOBALS['smarty']->assign('RANDOM_PROD', $content);
        }
    }

    /**
     * Display sale items box
     */
    private function _displaySaleItems()
    {
        if (!$GLOBALS['smarty']->templateExists('templates/box.sale_items.php')) {
            return false;
        }

        if ($GLOBALS['config']->get('config', 'catalogue_sale_mode')=='1') {
            $sale_sql_group_select = '`G`.`price`-`G`.`sale_price`';
            $sale_sql_standard_select = '`price`-`sale_price`';
            $sale_sql_group_where = '`G`.`price` > `G`.`sale_price` AND `G`.`sale_price` > 0';
            $sale_sql_standard_where = '`price` > `sale_price` AND `sale_price` > 0';
        } elseif ($GLOBALS['config']->get('config', 'catalogue_sale_mode')=='2' && $GLOBALS['config']->get('config', 'catalogue_sale_percentage')>0) {
            $decimal_percent = ($GLOBALS['config']->get('config', 'catalogue_sale_percentage')/100);
            $sale_sql_group_select = '`G`.`price` * '.$decimal_percent;
            $sale_sql_standard_select = '`price` * '.$decimal_percent;
            $sale_sql_group_where = '`G`.`price` > 0';
            $sale_sql_standard_where = '`price` > 0';
        } else {
            return false;
        }
        // Hide out-of-stock
        $sale_sql_standard_where = $GLOBALS['catalogue']->outOfStockWhere($sale_sql_standard_where);
        $sale_sql_group_where    = $GLOBALS['catalogue']->outOfStockWhere($sale_sql_group_where, 'G');

        // Check for group pricing
        if (($memberships = $GLOBALS['user']->getMemberships()) !== false) {
            foreach ($memberships as $membership) {
                $group_id[] = $membership['group_id'];
            }
        }

        if (!$GLOBALS['config']->get('config', 'catalogue_sale_items')) {
            $catalogue_sale_items = 10;
        } else {
            $catalogue_sale_items = $GLOBALS['config']->get('config', 'catalogue_sale_items');
        }
        if (isset($group_id) && is_array($group_id)) {
            $query = 'SELECT `I`.`product_id`,`I`.`description`,`I`.`name`, '.$sale_sql_group_select.' AS `saving` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_pricing_group` AS `G` INNER JOIN `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_inventory` AS `I` ON `G`.`product_id` = `I`.`product_id` WHERE  '.$sale_sql_group_where.' AND `G`.`group_id` IN('.implode(',', $group_id).') AND `I`.`status` = \'1\'';
            $group_pricing = $GLOBALS['db']->query($query, $catalogue_sale_items);
        }

        if (isset($group_pricing) && is_array($group_pricing)) {
            foreach ($group_pricing as $product) {
                $group_products[$product['product_id']] = $product;
            }
        }

        if (isset($group_id) && is_array($group_id)) {
            //Get list of group priced products that are NOT on sale so we can exclude them from standard prices query result
            $nosale_sql_group_where = ' G.sale_price = 0 ';
            $query_group_not_on_sale = 'SELECT `I`.`product_id`	FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_pricing_group` AS `G` INNER JOIN `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_inventory` AS `I` ON `G`.`product_id` = `I`.`product_id` WHERE  '.$nosale_sql_group_where.' AND `group_id` IN('.implode(',', $group_id).') AND `I`.`status` = \'1\' ';
            $group_pricing_no_sale = $GLOBALS['db']->query($query_group_not_on_sale);
            if ($group_pricing_no_sale) {
                foreach ($group_pricing_no_sale as $product_no_sale) {
                    $not_on_sale[] = $product_no_sale['product_id'];
                }
            }
        }
        $not_on_sale = isset($not_on_sale) ? 'AND `product_id` NOT IN ('.implode(',', $not_on_sale).') ' : '';


        // Get Retail Prices second
        $no_sale_items = (is_numeric($GLOBALS['config']->get('config', 'catalogue_sale_items'))) ? (int)$GLOBALS['config']->get('config', 'catalogue_sale_items') : 10;

        if ($no_sale_items && isset($sale_sql_standard_select) && ($standard_pricing = $GLOBALS['db']->query('SELECT `price`, `sale_price`, `product_id`,`description`,`name`, '.$sale_sql_standard_select.' AS `saving` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_inventory` WHERE '.$sale_sql_standard_where.' AND `status` = \'1\' '.$not_on_sale.' LIMIT '.$no_sale_items)) !== false && is_array($standard_pricing)) {
            foreach ($standard_pricing as $product) {
                if (isset($group_products[$product['product_id']])) {
                    $unsorted_products[$product['product_id']] = $group_products[$product['product_id']];
                } else {
                    $unsorted_products[$product['product_id']] = $product;
                }
            }
        }

        // Loop and merge group price into Retail Prices
        if (!empty($unsorted_products) && is_array($unsorted_products)) {
            foreach ($unsorted_products as $product) {
                $sorted_products[$product['saving'].$product['product_id']] = $product;
            }
            krsort($sorted_products);
        } else {
            $sorted_products = false;
        }
        unset($group_pricing, $standard_pricing, $group_products, $unsorted_products, $product, $not_on_sale);

        $vars = array();
        if ($sorted_products) {
            foreach ($sorted_products as $product) {
                $GLOBALS['language']->translateProduct($product);
                $product['name'] = validHTML($product['name']);
                $product['url']  = $GLOBALS['seo']->buildURL('prod', $product['product_id']);
                $product['saving_unformatted']  = $product['saving'];
                $product['saving']  = $GLOBALS['tax']->priceFormat($product['saving']);

                $GLOBALS['catalogue']->getProductPrice($product);
                $sale = $GLOBALS['tax']->salePrice($product['price'], $product['sale_price']);
                $product['price_unformatted'] = $product['price'];
                $product['sale_price_unformatted'] = ($sale) ? $product['sale_price'] : null;
                $product['price'] = $GLOBALS['tax']->priceFormat($product['price']);
                $product['sale_price'] = ($sale) ? $GLOBALS['tax']->priceFormat($product['sale_price']) : null;

                $vars[] = $product;
            }
        }

        foreach ($GLOBALS['hooks']->load('class.gui.display_sale_items') as $hook) {
            include $hook;
        }
        if(count($vars)>0) {
            $GLOBALS['smarty']->assign('PRODUCTS', $vars);
            $GLOBALS['smarty']->assign('SALE_ITEMS_URL', $GLOBALS['seo']->buildURL('saleitems'));
            $content = $GLOBALS['smarty']->fetch('templates/box.sale_items.php');
            $GLOBALS['smarty']->assign('SALE_ITEMS', $content);
            $this->_sale_items = true;
            return true;
        }
        return false;
        
    }

    /**
     * Display search box
     */
    private function _displaySearchBox()
    {
        if (!$GLOBALS['smarty']->templateExists('templates/box.search.php')) {
            return false;
        }

        foreach ($GLOBALS['hooks']->load('class.gui.display_search_box') as $hook) {
            include $hook;
        }
        $GLOBALS['smarty']->assign('SEARCH_URL', $GLOBALS['seo']->buildURL('search'));
        $GLOBALS['smarty']->assign('SEARCH_FORM', $GLOBALS['smarty']->fetch('templates/box.search.php'));
    }

    /**
     * Display session box
     */
    private function _displaySessionBox()
    {
        if (!$GLOBALS['smarty']->templateExists('templates/box.session.php')) {
            return false;
        }

        if ($GLOBALS['user']->is()) {
            $customer = $GLOBALS['user']->get();
            
            $GLOBALS['smarty']->assign('CUSTOMER', $customer);
            
            // Account may be made but name may not be known yet e.g. Yahoo login
            if (empty($customer['first_name'])) {
                $GLOBALS['smarty']->assign('LANG_WELCOME_BACK', $GLOBALS['language']->account['welcome_back_guest']);
            } else {
                $GLOBALS['smarty']->assign('LANG_WELCOME_BACK', sprintf($GLOBALS['language']->account['welcome_back'], $customer['first_name'], ''));
            }
        }
        $session_list_hooks = array();
        foreach ($GLOBALS['hooks']->load('class.gui.session.list') as $hook) {
            include $hook;
        }
        $GLOBALS['smarty']->assign('SESSION_LIST_HOOKS', $session_list_hooks);
        foreach ($GLOBALS['hooks']->load('class.gui.display_session_box') as $hook) {
            include $hook;
        }
        $GLOBALS['smarty']->assign('URL', array('login' => $GLOBALS['seo']->buildURL('login'), 'register' => $GLOBALS['seo']->buildURL('register')));
        $content = $GLOBALS['smarty']->fetch('templates/box.session.php');
        $GLOBALS['smarty']->assign('SESSION', $content);
    }

    /**
     * Display select skin box
     */
    private function _displaySkinSelect()
    {
        if (!$GLOBALS['smarty']->templateExists('templates/box.skins.php')) {
            return false;
        }

        $skin_setting = $GLOBALS['config']->get('config', 'skin_change');
        if ($skin_setting == '1' || ($skin_setting == '2' && Admin::getInstance()->is())) {
            foreach ($this->_skins as $skin => $data) {
                ## Do not show mobile skins
                if (!$data['info']['mobile']) {
                    $data['info']['selected'] = ($this->_skin == $data['info']['name']) ? 'selected="selected"' : '';
                    $vars[$skin] = $data['info'];
                    if (isset($data['styles']) && is_array($data['styles'])) {
                        foreach ($data['styles'] as $style) {
                            $style['selected'] = ($this->_skin == $data['info']['name'] && $this->_style == $style['directory']) ? 'selected="selected"' : '';
                            $vars[$skin]['styles'][] = $style;
                        }
                    }
                }
            }
            foreach ($GLOBALS['hooks']->load('class.gui.display_skin_select') as $hook) {
                include $hook;
            }
            $GLOBALS['smarty']->assign('SKINS', $vars);
            $content = $GLOBALS['smarty']->fetch('templates/box.skins.php');
            $GLOBALS['smarty']->assign('SKIN_SELECT', $content);
        }
    }

    /**
     * Display social links
     */
    private function _displaySocial()
    {
        if (!$GLOBALS['smarty']->templateExists('templates/element.social.php')) {
            return false;
        }

        $vars = array();
        
        $config = $GLOBALS['config']->get('config');
        
        if (!empty($config['facebook'])) {
            $vars[] = array(
                'url' => (filter_var($config['facebook'], FILTER_VALIDATE_URL)) ? $config['facebook'] : 'https://www.facebook.com/'.$config['facebook'],
                'name' => 'Facebook',
                'icon'	=> 'facebook-square'
            );
        }
        if (!empty($config['flickr'])) {
            $vars[] = array(
                'url' => (filter_var($config['flickr'], FILTER_VALIDATE_URL)) ? $config['flickr'] : 'http://www.flickr.com/photos/'.$config['flickr'],
                'name' => 'Flickr',
                'icon'	=> 'flickr'
            );
        }
        if (!empty($config['google_plus'])) {
            $vars[] = array(
                'url' => (filter_var($config['google_plus'], FILTER_VALIDATE_URL)) ? $config['google_plus'] : 'https://plus.google.com/'.$config['google_plus'],
                'name' => 'Google+',
                'icon'	=> 'google-plus-square'
            );
        }
        if (!empty($config['instagram'])) {
            $vars[] = array(
                'url' => (filter_var($config['instagram'], FILTER_VALIDATE_URL)) ? $config['instagram'] : 'http://www.instagram.com/'.$config['instagram'],
                'name' => 'Instagram',
                'icon'	=> 'instagram'
            );
        }
        if (!empty($config['linkedin'])) {
            $vars[] = array(
                'url' => (filter_var($config['linkedin'], FILTER_VALIDATE_URL)) ? $config['linkedin'] : 'http://www.linkedin.com/company/'.$config['linkedin'],
                'name' => 'Linkedin',
                'icon'	=> 'linkedin-square'
            );
        }
        if (!empty($config['pinterest'])) {
            $vars[] = array(
                'url' => (filter_var($config['pinterest'], FILTER_VALIDATE_URL)) ? $config['pinterest'] : 'http://www.pinterest.com/'.$config['pinterest'],
                'name' => 'Pinterest',
                'icon'	=> 'pinterest-square'
            );
        }
        if (!empty($config['twitter'])) {
            $vars[] = array(
                'url' => (filter_var($config['twitter'], FILTER_VALIDATE_URL)) ? $config['twitter'] : 'https://twitter.com/'.$config['twitter'],
                'name' => 'Twitter',
                'icon'	=> 'twitter-square'
            );
        }
        if (!empty($config['vimeo'])) {
            $vars[] = array(
                'url' => (filter_var($config['vimeo'], FILTER_VALIDATE_URL)) ? $config['vimeo'] : 'https://vimeo.com/'.$config['vimeo'],
                'name' => 'Vimeo',
                'icon'	=> 'vimeo-square'
            );
        }
        if (!empty($config['wordpress'])) {
            $vars[] = array(
                'url' => (filter_var($config['wordpress'], FILTER_VALIDATE_URL)) ? $config['wordpress'] : 'http://'.$config['wordpress'].'.wordpress.com',
                'name' => 'WordPress',
                'icon'	=> 'wordpress'
            );
        }
        if (!empty($config['youtube'])) {
            $vars[] = array(
                'url' => (filter_var($config['youtube'], FILTER_VALIDATE_URL)) ? $config['youtube'] : 'http://www.youtube.com/user/'.$config['youtube'],
                'name' => 'YouTube',
                'icon'	=> 'youtube-square'
            );
        }

        foreach ($GLOBALS['hooks']->load('class.gui.display_social') as $hook) {
            include $hook;
        }

        $GLOBALS['smarty']->assign('SOCIAL_LINKS', $vars);
        $content = $GLOBALS['smarty']->fetch('templates/element.social.php');
        $GLOBALS['smarty']->assign('SOCIAL_LIST', $content);
    }

    /**
     * Setup error messages
     * @param string $type
     * @param string $message
     */
    private function _errorMessage($type, $message)
    {
        if (!empty($message)) {
            if (is_array($message)) {
                foreach ($message as $text) {
                    $gui_message[$type][md5($text)] = strip_tags($text, '<a>');
                }
            } else {
                $gui_message[$type][md5($message)] = strip_tags($message, '<a>');
            }
            $GLOBALS['session']->set('GUI_MESSAGE', $gui_message);
        }
    }

    /**
     * Get default skin logo
     * @param string $skin
     * @param string $style
     */
    private function _getLogoDefault($skin = '', $style = '')
    {
        if (empty($skin)) {
            $skin = $GLOBALS['config']->get('config', 'skin_folder');
            $style = $GLOBALS['config']->get('config', 'skin_style');
        }

        $path = (!empty($style)) ? 'images/'.$style : 'images';
        $source = glob('skins/'.$skin.'/'.$path.'/'.'logo/default.{gif,jpg,png,svg}', GLOB_BRACE);
        return (!empty($source)) ? $source[0] : null;
    }

    /**
     * Make navigation tree
     *
     * @param array $tree_data
     * @return string
     */
    private function _makeTree($tree_data)
    {
        if (!$GLOBALS['smarty']->templateExists('templates/element.navigation_tree.php')) {
            return false;
        }
        
        $out = '';
        if (is_array($tree_data)) {
            foreach ($tree_data as $branch) {
                if (isset($branch['children'])) {
                    $branch['children'] = $this->_makeTree($branch['children']);
                }
                $branch['url'] = $GLOBALS['seo']->buildURL('cat', $branch['cat_id'], '&', false);
                $GLOBALS['smarty']->assign('BRANCH', $branch);
                foreach ($GLOBALS['hooks']->load('class.gui.display_navigation.make_tree') as $hook) {
                    include $hook;
                }
                $out .= $GLOBALS['smarty']->fetch('templates/element.navigation_tree.php');
            }
        }
        return $out;
    }

    /**
     * Set canonical
     */
    private function _setCanonical()
    {
        $canonical = $GLOBALS['smarty']->getTemplateVars('CANONICAL');
        if (empty($canonical) && !is_numeric($canonical)) {
            $excluded = array('review','sort','perpage');
            if (is_array($GLOBALS['db']->page_one)) {
                $excluded = array_merge($excluded, $GLOBALS['db']->page_one);
            }
            $GLOBALS['smarty']->assign('CANONICAL', currentPage($excluded, null, false));
            return true;
        }
        return false;
    }

    /**
     * Set logo
     *
     * @param string $type
     */
    private function _setLogo($type = '')
    {
        if (!empty($type)) {
            $this->_logo = $GLOBALS['config']->get('logos', $type);
        } else {
            if (empty($this->_skin)) {
                $this->_setSkin();
            }
            if (empty($this->_style)) {
                $this->_setStyle();
            }
            $this->_logo = $GLOBALS['config']->get('logos', $this->_skin.$this->_style);
        }
    }


    /**
     * Set the correct skin
     */
    private function _setSkin()
    {
        //Try the session
        if ($GLOBALS['session']->has('skin', 'client') && isset($this->_skins[$GLOBALS['session']->get('skin', 'client')])) {
            $this->_skin = $GLOBALS['session']->get('skin', 'client');
        //Try the config
        } elseif ($GLOBALS['config']->has('config', 'skin_folder'.$this->_skin_config_postfix) && isset($this->_skins[$GLOBALS['config']->get('config', 'skin_folder'.$this->_skin_config_postfix)])) {
            $this->_skin = $GLOBALS['config']->get('config', 'skin_folder'.$this->_skin_config_postfix);
        } else {
            //This is a last ditch effort to get a skin loaded
            if (is_array($this->_skins) && !empty($this->_skins)) {
                foreach ($this->_skins as $name => $skin) {
                    if ($name != 'vanilla') {
                        $this->_skin = $name;
                        break;
                    }
                }
                if (!empty($this->_skin)) {
                    $GLOBALS['config']->set('config', 'skin_folder'.$this->_skin_config_postfix, $this->_skin);
                }
            }
        }

        if (empty($this->_skin)) {
            $this->_skin = 'foundation';
            $GLOBALS['config']->set('config', 'skin_folder'.$this->_skin_config_postfix, $this->_skin);
        }

        if (($custom = $GLOBALS['cache']->read('skin.'.$this->_skin.'.custom')) === false && file_exists(CC_ROOT_DIR.'/skins/'.$this->_skin.'/'.'config.xml')) {
            $xml = $this->getSkinConfig('', $this->_skin);
            $custom = array();
            if (isset($xml->custom)) {
                foreach ($xml->custom->children() as $element) {
                    $custom[$element->getName()] = (string)$element;
                }
            }
            $GLOBALS['cache']->write($custom, 'skin.'.$this->_skin.'.custom');
        }

        $GLOBALS['smarty']->assign('SKIN_CUSTOM', $custom);

        $GLOBALS['smarty']->assign('SKIN_FOLDER', $this->_skin);
        $common = file_exists(CC_ROOT_DIR.CC_DS.'skins'.CC_DS.$this->_skin.CC_DS.'images'.CC_DS.'common') ? 'common' : '';
        $GLOBALS['smarty']->assign('SKIN_COMMON', $common);
    }

    /**
     * Set the correct style
     */
    private function _setStyle()
    {
        if ($GLOBALS['session']->has('style', 'client') && isset($this->_skins[$this->_skin]['styles'][$GLOBALS['session']->get('style', 'client')])) {
            $this->_style = $GLOBALS['session']->get('style', 'client');
        } elseif ($GLOBALS['config']->has('config', 'skin_style'.$this->_skin_config_postfix) && isset($this->_skins[$this->_skin]['styles'][$GLOBALS['config']->get('config', 'skin_style'.$this->_skin_config_postfix)])) {
            $this->_style = $GLOBALS['config']->get('config', 'skin_style'.$this->_skin_config_postfix);
        } else {
            //Check for styles at all
            $directories = glob('skins/'.$this->_skin.'/'.'styles/*.*', GLOB_ONLYDIR | GLOB_NOSORT);
            //If there are styles
            if (!empty($directories)) {
                //This is a last ditch effort to get a skin style loaded
                if (isset($this->_skins[$this->_skin]['styles']) && is_array($this->_skins[$this->_skin]['styles'])) {
                    $styles = array_keys($this->_skins[$this->_skin]['styles']);
                    $this->_style = $styles[0];
                    if (!empty($this->_style) && is_string($this->_style)) {
                        $GLOBALS['config']->set('config', 'skin_style'.$this->_skin_config_postfix, $this->_style);
                    }
                } else {
                    $this->_style = '';
                }
            } else {
                //There are no styles
                $this->_style = '';
            }
        }
        $GLOBALS['smarty']->assign('SKIN_SUBSET', $this->_style);
    }
}
