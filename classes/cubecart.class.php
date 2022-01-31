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
 * Core controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Cubecart
{
    /**
     * Holds the current basket
     *
     * @var array
     */
    private $_basket = null;

    const AFFILIATE_GATEWAY  = 1;
    const AFFILIATE_COMPLETE = 2;

    const OPTION_SELECT   = 0;
    const OPTION_TEXTBOX  = 1;
    const OPTION_TEXTAREA  = 2;
    const OPTION_PASSWORD  = 3;
    const OPTION_RADIO   = 4;
    const OPTION_CHECKBOX  = 5;
    const OPTION_DATEPICKER  = 6;
    const OPTION_HIDDEN   = 7;
    const OPTION_FILE   = 8;

    const ORDER_PENDING   = 1;
    const ORDER_PROCESS   = 2;
    const ORDER_COMPLETE  = 3;
    const ORDER_DECLINED  = 4;
    const ORDER_FAILED   = 5;
    const ORDER_CANCELLED  = 6;

    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;

    ##############################################

    final protected function __construct()
    {
        if (isset($_SERVER['REDIRECT_STATUS']) && $_SERVER['REDIRECT_STATUS'] >= 400) {
            $_GET['_a'] = '404';
        }
    }

    /**
     * Setup the instance (singleton)
     *
     * @return Cubecart
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
     * Show the home page
     */
    public function displayHomePage()
    {
        if (($home = $this->getDocument(null, true)) !== false) {
            $GLOBALS['smarty']->assign('DOCUMENT', array(
                    'title'  => $home['doc_name'],
                    'content' => $home['doc_content'],
                    'hide_title' => $home['hide_title']
                ));
        }

        $products = array();

        $where = $GLOBALS['catalogue']->outOfStockWhere(array('I.status' => '1', 'I.latest' => '1'), 'I');

        if ($GLOBALS['config']->get('config', 'catalogue_latest_products')) {
            $query = sprintf("SELECT I.* FROM `%1\$sCubeCart_inventory` AS I JOIN `%1\$sCubeCart_category` AS C ON C.cat_id=I.cat_id AND C.`status`=1 AND $where ORDER BY I.date_added DESC, I.product_id DESC", $GLOBALS['config']->get('config', 'dbprefix'));
            $latestProducts = $GLOBALS['db']->query($query, (int)$GLOBALS['config']->get('config', 'catalogue_latest_products_count'));
            foreach ($GLOBALS['hooks']->load('class.cubecart.latest_products') as $hook) {
                include $hook;
            }
            if ($latestProducts) {
                foreach ($latestProducts as $product) {
                    // Product Translation
                    $GLOBALS['language']->translateProduct($product);
                    $product['image'] = $GLOBALS['gui']->getProductImage($product['product_id'], 'small');
                    $product['ctrl_sale'] = (!$GLOBALS['tax']->salePrice($product['price'], $product['sale_price']) || !$GLOBALS['config']->get('config', 'catalogue_sale_mode')) ? false : true;

                    $GLOBALS['catalogue']->getProductPrice($product);
                    $sale = $GLOBALS['tax']->salePrice($product['price'], $product['sale_price']);
                    $product['price_unformatted'] = $product['price'];
                    $product['sale_price_unformatted'] = ($sale) ? $product['sale_price'] : null;
                    $product['price'] = $GLOBALS['tax']->priceFormat($product['price']);
                    $product['sale_price'] = ($sale) ? $GLOBALS['tax']->priceFormat($product['sale_price']) : null;

                    // ctrl_stock True when a product is considered 'in stock' for purposes of allowing a purchase, either by actually being in stock or via certain settings
                    $product['ctrl_stock'] = (!$product['use_stock_level'] || $GLOBALS['config']->get('config', 'basket_out_of_stock_purchase') || ($product['use_stock_level'] && $GLOBALS['catalogue']->getProductStock($product['product_id'], null, true) > 0)) ? true : false;
                    $product['url'] = $GLOBALS['seo']->buildURL('prod', $product['product_id'], '&');

                    $GLOBALS['smarty']->assign('CTRL_REVIEW', (bool)$GLOBALS['config']->get('config', 'enable_reviews'));
                    if (($product_review = $GLOBALS['db']->select('CubeCart_reviews', 'SUM(`rating`) AS Score, COUNT(`id`) as Count', array('approved' => 1, 'product_id' => $product['product_id']))) !== false) {
                        if (!empty($product_review[0]['Count'])) {
                            $product['review_score'] = round($product_review[0]['Score']/$product_review[0]['Count'], 1);
                        }
                    }
                    $product['description_short'] = $GLOBALS['catalogue']->descriptionShort($product);
                    $products[] = $product;
                }
                $GLOBALS['smarty']->assign('LATEST_PRODUCTS', $products);
            }
        }

        $GLOBALS['smarty']->assign('CTRL_HIDE_PRICES', $GLOBALS['session']->get('hide_prices'));
        foreach ($GLOBALS['hooks']->load('class.cubecart.display_homepage') as $hook) {
            include $hook;
        }
        $content = $GLOBALS['smarty']->fetch('templates/content.homepage.php');
        $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
    }

    /**
     * Load a document
     *
     * @param inst $doc_id
     * @param bool $homepage
     *
     * @return string/false
     */
    public function getDocument($doc_id = null, $homepage = false)
    {
        if (is_numeric($doc_id) || $homepage) {
            $where  = ($homepage) ? array('doc_home' => true, 'doc_status' => '1') : array('doc_id' => $doc_id, 'doc_status' => '1');
            $doc_lang = $GLOBALS['language']->current();
            if ($parent = $GLOBALS['db']->select('CubeCart_documents', false, $where)) {
                $contents = $parent[0];
                if ($parent[0]['doc_lang'] != $doc_lang) {
                    if ($contents['doc_parent_id']>0) {
                        // we have different than store default lang document but language switched just on product page
                        $target_column = ($doc_lang == $GLOBALS['config']->get('config', 'default_language')) ? 'doc_id' : 'doc_parent_id' ;
                        $document = $GLOBALS['db']->select('CubeCart_documents', false, array($target_column => $contents['doc_parent_id'], 'doc_lang' => $doc_lang));

                        // Default Lang, if it exists
                        if (!$document) {
                            $document = $GLOBALS['db']->select('CubeCart_documents', false, array('doc_id' => $contents['doc_parent_id'], 'doc_lang' => $GLOBALS['config']->get('config', 'default_language')));
                        }
                    } elseif (($document = $GLOBALS['db']->select('CubeCart_documents', false, array('doc_parent_id' => $contents['doc_id'], 'doc_lang' => $doc_lang))) !== false) {
                        //      $contents = $document[0];
                    } else {
                        // Default Lang, if it exists
                        $document = $GLOBALS['db']->select('CubeCart_documents', false, array('doc_parent_id' => $contents['doc_id'], 'doc_lang' => $GLOBALS['config']->get('config', 'default_language')));
                    }

                    if ($document) {
                        $contents = $document[0];
                    }
                }
                $meta_data = array(
                    'name'   => $contents['doc_name'],
                    'path'   => null,
                    'description' => $contents['seo_meta_description'],
                    'title'   => $contents['seo_meta_title'],
                );

                $GLOBALS['seo']->set_meta_data($meta_data);
            }
        }
        if ($contents['doc_privacy']==1) {
            $GLOBALS['smarty']->assign('COOKIE_SWITCH', true);
        }
        $contents['doc_content'] = ($contents['doc_parse']==1) ? $GLOBALS['smarty']->fetch('string:'.$contents['doc_content']) : $contents['doc_content'];

        return (isset($contents) && !empty($contents)) ? $contents : false;
    }

    /**
     * Load a page
     */
    public function loadPage()
    {
        if (isset($_GET['_g']) && !empty($_GET['_g'])) {
            switch (strtolower($_GET['_g'])) {
                case 'ajax_cookie_consent':
                    $dialogue = ($_GET['accept']==1) ? 'Accepted chosen.' : 'Blocked chosen.';
                    $consent_log = array(
                        'ip_address' => get_ip_address(),
                        'session_id' => $GLOBALS['session']->getId(),
                        'customer_id' => $GLOBALS['user']->getId(),
                        'log' => $dialogue,
                        'log_hash' => md5($dialogue),
                        'url_shown' => currentPage(),
                        'time' => time()
                    );
                    $GLOBALS['db']->insert('CubeCart_cookie_consent', $consent_log);
                    exit;
                break;
                case 'ajax_price_format':
                    $GLOBALS['debug']->supress();
                    if (is_numeric($_GET['price'])) {
                        echo json_encode($GLOBALS['tax']->priceFormat($_GET['price']));
                    } elseif (is_array($_GET['price'])) {
                        $prices = array();
                        foreach ($_GET['price'] as $key => $price) {
                            if (is_numeric($price)) {
                                $prices[$key] = $GLOBALS['tax']->priceFormat($price);
                            }
                        }
                        die(json_encode($prices));
                    }
                    exit;
                break;
                case 'ajax_email':
                    $GLOBALS['debug']->supress();

                    if ($_GET['source']=='newsletter') {
                        $result = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', 'subscriber_id', array('email' => $_POST['subscribe']), false, 1, false, false);
                    } else {
                        $email = isset($_POST['user']['email']) ? $_POST['user']['email'] : $_POST['email'];
                        $result = $GLOBALS['db']->select('CubeCart_customer', 'customer_id', array('email' => $email, 'type' => 1));
                    }

                    if ($result) {
                        $response = ($GLOBALS['config']->get('config', 'csrf')=='1') ? array('result' => false, 'token' => SESSION_TOKEN): false;
                        die(json_encode($response));
                    } else {
                        $response = ($GLOBALS['config']->get('config', 'csrf')=='1') ? array('result' => true, 'token' => SESSION_TOKEN): true;
                        die(json_encode($response));
                    }
                    break;
                case 'ajaxadd':
                    $GLOBALS['debug']->supress();
                    $sideBasket = $GLOBALS['gui']->displaySideBasket();
                    die($sideBasket);
                    break;
                case 'rm':
                case 'remote':
                    $GLOBALS['debug']->supress();
                    $mod_type = (isset($_GET['mod_type'])) ? $_GET['mod_type'] : $_GET['type'];
                    if (!empty($mod_type)) {
                        switch (strtolower($mod_type)) {
                        case 'plugins':
                        case 'gateway':
                            /* Important Notice!
                            Please be careful with this hook that you don't always force $plugin to 'plugin'.
                            This will stop other normal gateways working. Wrap your code in a condition to
                            make sure that it only ever executed when needed.
                            */
                            foreach ($GLOBALS['hooks']->load('class.cubecart.construct.callback.gateway') as $hook) {
                                include $hook;
                            }
                            $folder = (isset($plugin)) ? 'plugins' : 'gateway';
                            $module = preg_replace('#[^a-z0-9\_\-]#iU', '', $_GET['module']);

                            $class_file = CC_ROOT_DIR.'/modules/'.$folder.'/'.$module.'/'.'gateway.class.php';

                            if (file_exists($class_file)) {
                                include $class_file;
                                $gateway = new Gateway($GLOBALS['config']->get($module));
                                $command = (isset($_GET['cmd'])) ? strtolower($_GET['cmd']) : null;
                                if (!empty($command)) {
                                    # if (method_exists($gateway, $command)) $gateway->{$command}();
                                    switch ($_GET['cmd']) {
                                    case 'call':
                                        if (method_exists($gateway, 'call')) {
                                            $gateway->call();
                                        }
                                        exit;
                                        break;
                                    case 'process':
                                        if (method_exists($gateway, 'process')) {
                                            $gateway->process();
                                        }
                                        break;
                                    }
                                }
                            }
                            break;
                        default:
                            httpredir('index.php');
                        }
                    } else {
                        httpredir('index.php');
                    }
                    break;
                default:
                    foreach ($GLOBALS['hooks']->load('class.cubecart.construct.g_switch') as $hook) {
                        include $hook;
                    }
                    trigger_error('No callback method defined.', E_USER_WARNING);
                    httpredir('index.php');
            }
        } elseif (isset($_GET['_a']) && !empty($_GET['_a'])) {
            //Clear cart
            if (isset($_GET['empty-basket'])) {
                $GLOBALS['cart']->clear();
                httpredir(currentPage(array('empty-basket'), array('_a' => 'basket')));
            }
            switch (strtolower($_GET['_a'])) {
                /**
                 * These are hard coded method calls that require variables or special calls
                 * All others are done by the case default using $this->{$method}() method
                 * See below
                 */
                case '404':
                    $GLOBALS['smarty']->assign('SECTION_NAME', '404');
                    
                    $this->_404();
                break;

                case 'cancel':
                    $GLOBALS['smarty']->assign('SECTION_NAME', 'checkout');
                    // Cancel payment
                    foreach ($GLOBALS['hooks']->load('class.cubecart.construct.cancel') as $hook) {
                        include $hook;
                    }
                break;

                case 'checkout':
                case 'confirm':
                    $GLOBALS['smarty']->assign('SECTION_NAME', 'checkout');
                    if ($GLOBALS['config']->get('config', 'catalogue_mode')) {
                        httpredir('index.php');
                    }
                    $this->_checkout();
                    $editable = false;
                    // no break
                case 'basket':
                case 'cart':
                    $GLOBALS['smarty']->assign('SECTION_NAME', 'checkout');
                    if ($GLOBALS['config']->get('config', 'catalogue_mode')) {
                        httpredir('index.php');
                    }
                    $this->_basket(isset($editable) ? $editable : true);
                break;

                case 'document':
                    $GLOBALS['smarty']->assign('SECTION_NAME', 'document');
                    // Site Documents
                    if (($document = $this->getDocument($_GET['doc_id'])) !== false) {
            
                        // Homepage shouldn't load as a document (duplicate content)
                        if ($document['doc_home']=='1') {
                            httpredir('index.php', '', false, 301);
                        }
                        
                        foreach ($GLOBALS['hooks']->load('class.cubecart.document_widgets') as $hook) {
                            include $hook;
                        }
                        
                        $GLOBALS['gui']->addBreadcrumb($document['doc_name'], currentPage());
                        $GLOBALS['smarty']->assign('DOCUMENT', $document);
                        /* Social Bookmarks */
                        $GLOBALS['smarty']->assign('SHARE', $this->_getSocial('document', 'getButtonHTML'));
                        /* Social Comments */
                        $GLOBALS['smarty']->assign('COMMENTS', $this->_getSocial('document', 'getCommunityHTML'));
                        $content = $GLOBALS['smarty']->fetch('templates/content.document.php');
                        $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
                    }
                break;

                case 'download':
                case 'downloads':
                    $GLOBALS['smarty']->assign('SECTION_NAME', 'download');
                    $this->_download();
                break;

                case 'saleitems':
                    $GLOBALS['smarty']->assign('SECTION_NAME', 'saleitems');
                    $_GET['cat_id'] = 'sale';
                    $this->_category();
                break;

                case 'category':
                    $GLOBALS['smarty']->assign('SECTION_NAME', 'category');
                    $this->_category();
                break;

                case 'login':
                    if ($GLOBALS['user']->is()) {
                        httpredir('index.php');
                    } else {
                        $GLOBALS['smarty']->assign('SECTION_NAME', 'login');
                        $this->_login();
                    }
                break;

                case 'newsletter':
                case 'unsubscribe':
                    $this->_newsletter();
                break;

                case 'plugin':
                    $trigger = 'class.cubecart.display_content';
                    $plugin = preg_replace('#[^a-z0-9\_\-]#iU', '', $_GET['plugin']);
                    $path = 'modules/plugins/'.$plugin.'/'.'hooks/'.$trigger.'.php';
                    if ($GLOBALS['hooks']->is_enabled($trigger, $plugin) && file_exists($path)) {
                        include $path;
                    } else {
                        httpredir('index.php');
                    }
                break;

                case 'template':
                    if (isset($_GET['type']) && isset($_GET['module'])) {
                        $module = preg_replace('#[^a-z0-9\_\-]#iU', '', $_GET['module']);
                        $type = preg_replace('#[^a-z0-9\_\-]#iU', '', $_GET['type']);
                        $template = 'modules/'.$type.'/'.$module.'/skin/inline.php';
                        if (file_exists($template)) {
                            $GLOBALS['smarty']->assign('PAGE_CONTENT', file_get_contents($template, false));
                        }
                    }
                break;

                case 'vieworders':
                case 'vieworder':
                case 'orderhistory':
                    $GLOBALS['smarty']->assign('SECTION_NAME', 'order');
                    $this->_orders();
                break;

                default:
                    $method = '_'.strtolower($_GET['_a']);
                    // CubeCart will auto load any class in the classes folder. Please use the method below to
                    // load a function from the class. e.g. widget.class.php
                    if (method_exists($this, $method)) {
                        $this->{$method}();
                    }
                    // and/or you can use code from a hook in the plugins folder.
                    foreach ($GLOBALS['hooks']->load('class.cubecart.construct') as $hook) {
                        include $hook;
                    }
            }
            if ($GLOBALS['smarty']->getTemplateVars('PAGE_CONTENT') === null) {
                $this->_404();
            }
        } else {
            $GLOBALS['smarty']->assign('SECTION_NAME', 'home');
            $this->displayHomePage();
        }
    }

    //=====[ Private ]=======================================

    /**
     * Display user account
     */
    private function _account()
    {
        // Display profile overview
        $GLOBALS['user']->is(true);

        $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->account['your_account'], 'index.php?_a=account');

        // Custom Account Menu Items
        $account_list_hooks = array();
        foreach ($GLOBALS['hooks']->load('class.cubecart.account.list') as $hook) {
            include $hook;
        }
        $GLOBALS['smarty']->assign('ACCOUNT_LIST_HOOKS', $account_list_hooks);
        $content = $GLOBALS['smarty']->fetch('templates/content.account.php');
        $GLOBALS['smarty']->assign('SECTION_NAME', 'account');
        $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
    }

    /**
     * Display addressbook
     */
    private function _addressbook()
    {
        // Address Book
        $GLOBALS['user']->is(true);

        $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->account['your_account'], 'index.php?_a=account');
        $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->account['your_addressbook'], 'index.php?_a=addressbook');
            
        $_a = isset($_GET['redir']) ? preg_replace('/[^a-z]/i', '', $_GET['redir']) : 'addressbook';

        $GLOBALS['smarty']->assign('REDIR', $_a);

        if (isset($_POST['save'])) {
            if (empty($_POST['description'])) {
                if ($_POST['billing']==1 && $_POST['default']==1) {
                    $_POST['description'] = $GLOBALS['language']->address['billing_delivery_address'];
                } elseif ($_POST['billing']==1) {
                    $_POST['description'] = $GLOBALS['language']->address['billing_address'];
                } elseif ($_POST['default']==1) {
                    $_POST['description'] = $GLOBALS['language']->address['delivery_address'];
                } else {
                    $_POST['description'] = $GLOBALS['language']->address['extra_address'];
                }
            }
            $empties = false;
            $required_fields = $GLOBALS['user']->getRequiredAddressFields($_POST['country']);
            foreach ($_POST as $key => $value) {
                if (in_array($key, $required_fields) && empty($value)) {
                    $empties = true;
                    break;
                }
            }
            // Update address data
            foreach ($GLOBALS['hooks']->load('class.cubecart.addressbook.update') as $hook) {
                include $hook;
            }
            if (!$empties && $GLOBALS['user']->saveAddress($_POST)) {
                $message = ($_POST['address_id']) ? $GLOBALS['language']->account['notify_address_updated'] : $GLOBALS['language']->account['notify_address_added'];
                $GLOBALS['gui']->setNotify($message);
                httpredir('?_a='.$_a);
            } else {
                $message = ($_POST['address_id']) ? $GLOBALS['language']->account['error_address_updated'] : $GLOBALS['language']->account['error_address_added'];
                $GLOBALS['gui']->setError($message);
            }
        }
        if (isset($_POST['delete'])) {
            if ($GLOBALS['user']->deleteAddress($_POST['delete'])) {
                $GLOBALS['gui']->setNotify($GLOBALS['language']->account['notify_address_delete']);
            } else {
                $GLOBALS['gui']->setError($GLOBALS['language']->account['error_address_delete']);
            }
            httpredir('?_a='.$_a);
        }

        // Display addressbook
        foreach ($GLOBALS['hooks']->load('class.cubecart.construct.addressbook') as $hook) {
            include $hook;
        }

        if (isset($_GET['action'])) {
            $address = array();
            switch ($_GET['action']) {
            case 'edit':
                if (isset($_GET['address_id']) && is_numeric($_GET['address_id'])) {
                    if (($address = $GLOBALS['user']->getAddress($_GET['address_id'])) !== false) {
                        $address['default'] = ($address['default']) ? 'checked="checked"' : '';
                        $address['billing'] = ($address['billing']) ? 'checked="checked"' : '';
                        $GLOBALS['smarty']->assign('DATA', $address);
                    } else {
                        $GLOBALS['gui']->setError($GLOBALS['language']->account['error_address_not_found']);
                        httpredir('?_a='.$_a);
                    }
                }
                // no break
            case 'add':
                if (empty($address)) {
                    $addresses = $GLOBALS['user']->getAddresses();
                    $GLOBALS['smarty']->assign('DATA', array(
                            'first_name' => $GLOBALS['user']->get('first_name'),
                            'last_name'  => $GLOBALS['user']->get('last_name'),
                            'description' => '',
                            'title'   => '',
                            'company_name' => '',
                            'line1'   => '',
                            'line2'   => '',
                            'town'   => '',
                            'state'   => '',
                            'postcode'  => '',
                            'billing'  => (!is_array($addresses)) ? 'checked="checked"' : '',
                            'default'  => (!is_array($addresses)) ? 'checked="checked"' : '',
                        ));
                }
                if (($countries = $GLOBALS['db']->select('CubeCart_geo_country', false, 'status > 0', array('name'=>'ASC'))) !== false) {
                    if (!isset($address['country'])) {
                        $address['country'] = $GLOBALS['config']->get('config', 'store_country');
                    }
                    foreach ($countries as $country) {
                        $country['selected'] = ($country['numcode'] == $address['country']) ? 'selected="selected"' : '';
                        $GLOBALS['smarty']->append('COUNTRIES', $country);
                    }
                }

                $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->address['add_edit'], currentPage());
                break;
            }
            $GLOBALS['smarty']->assign('CTRL_FORM', true);
        } else {
            $addresses = $GLOBALS['user']->getAddresses();
            if ($addresses) {
                foreach ($addresses as $address) {
                    $address['billing'] = ($address['billing']) ? '('.$GLOBALS['language']->address['billing_address'].')' : false;
                    $address['default'] = ($address['default']) ? '('.$GLOBALS['language']->address['default_delivery_address'].')' : false;
                    $GLOBALS['smarty']->append('ADDRESSES', $address);
                }
            } else {
                httpredir('?_a=addressbook&action=add');
            }
        }

        $GLOBALS['smarty']->assign('VAL_JSON_STATE', state_json());
        
        foreach ($GLOBALS['hooks']->load('class.cubecart.addressbook') as $hook) {
            include $hook;
        }

        if (isset($GLOBALS['cart']->basket['contents'])) {
            $GLOBALS['smarty']->assign('CHECKOUT_BUTTON', true);
        }

        $content = $GLOBALS['smarty']->fetch('templates/content.addressbook.php');
        $GLOBALS['smarty']->assign('SECTION_NAME', 'account');
        $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
    }

    /**
     * Display basket
     */
    private function _basket($editable = true)
    {
        // Basket
        foreach ($GLOBALS['hooks']->load('class.cubecart.pre_basket') as $hook) {
            include $hook;
        }

        $this->_checkoutProcess();
        $GLOBALS['cart']->verifyBasket();
        $this->_basket =& $GLOBALS['cart']->basket;
        
        if ($_GET['_a'] == 'basket' && $this->_basket['billing_address']['user_defined']) {
            httpredir('index.php?_a=confirm');
        }

        if ($editable) {
            if ($_GET['_a']=='basket') {
                if (isset($_POST['get-estimate'])) {
                    $_POST['estimate']['postcode'] = empty($_POST['estimate']['postcode']) ? $GLOBALS['config']->get('config', 'store_postcode') : $_POST['estimate']['postcode'];
                    $basket_data['delivery_address'] = $GLOBALS['user']->formatAddress($_POST['estimate'], false, true);
                    
                    $before = md5(serialize($this->_basket['delivery_address']));
                    $this->_basket['delivery_address'] = $basket_data['delivery_address'];
                    $this->_basket['billing_address'] = $basket_data['delivery_address'];

                    if ($before !== md5(serialize($this->_basket['delivery_address']))) {
                        $GLOBALS['cart']->save();
                        $GLOBALS['gui']->setNotify($GLOBALS['language']->basket['shipping_address_updated']);
                    }
                }

                // Estimated shipping
                if (($countries = $GLOBALS['db']->select('CubeCart_geo_country', false, 'status > 0', array('name' => 'ASC'))) !== false) {
                    foreach ($countries as $country) {
                        $country['selected'] = '';

                        if (isset($this->_basket['delivery_address'])) {
                            if ($country['numcode'] == $this->_basket['delivery_address']['country_id']) {
                                $country['selected'] = 'selected="selected"';
                            }
                        } else {
                            if ($country['numcode'] == $GLOBALS['config']->get('config', 'store_country')) {
                                $country['selected_d'] = 'selected="selected"';
                            }
                        }
                        $GLOBALS['smarty']->append('COUNTRIES', $country);
                    }
                    $delivery_address = $this->_basket['delivery_address'];
                    if ($this->_basket['delivery_address']['postcode'] == $GLOBALS['config']->get('config', 'store_postcode')) {
                        $delivery_address['postcode'] = '';
                    }
                    $GLOBALS['smarty']->assign('ESTIMATES', $delivery_address);
                    $GLOBALS['smarty']->assign('STATE_JSON', state_json());
                    $GLOBALS['smarty']->assign('ESTIMATE_SHIPPING', true);
                }
            } else {
                $GLOBALS['smarty']->assign('ESTIMATE_SHIPPING', false);
            }

            $GLOBALS['smarty']->assign('INCLUDE_CHECKOUT', false);
        }

        if ($GLOBALS['user']->is() && in_array($_GET['_a'], array('basket', 'cart'))) {
            httpredir(currentPage(null, array('_a' => 'confirm')));
        }
        
        if (isset($_POST['gateway']) && !empty($_POST['gateway'])) {
            $this->_basket['gateway'] = $_POST['gateway'];
            $GLOBALS['cart']->save();
        }
        
        if (isset($_POST['update'])) {
            $GLOBALS['cart']->update();
            if (isset($_POST['coupon']) && !empty($_POST['coupon'])) {
                $GLOBALS['cart']->discountAdd($_POST['coupon']);
            }
            foreach ($GLOBALS['hooks']->load('class.cubecart.post_discount_add') as $hook) include $hook;
            $GLOBALS['cart']->save();
            httpredir(currentPage());
        }
        if (isset($_POST['coupon']) && !empty($_POST['coupon'])) {
            $GLOBALS['cart']->discountAdd($_POST['coupon']);
            $GLOBALS['cart']->save();
            httpredir(currentPage());
        }
        if (isset($_GET['remove_code'])) {
            $GLOBALS['cart']->discountRemove($_GET['remove_code']);
            $GLOBALS['cart']->save();
            httpredir(currentPage(array('remove_code')));
        }
        // Update shipping values
        if (isset($_POST['shipping']) && !empty($_POST['shipping'])) {
            $posted_shipping = json_decode(base64url_decode($_POST['shipping']), true);

            $GLOBALS['cart']->set('shipping', $posted_shipping);
            $GLOBALS['cart']->set('shipping_hash', '');
            if (!isset($_POST['proceed'])) {
                httpredir(currentPage());
            }
        }

        if ((isset($this->_basket['delivery_address']['user_defined']) && !$this->_basket['delivery_address']['user_defined']) || (isset($this->_basket['billing_address']['user_defined']) && !$this->_basket['billing_address']['user_defined'])) {
            $customer_locale = array(
                'mark' => $GLOBALS['language']->basket['unconfirmed_locale_mark'],
                'description' => true
            );
        } else {
            $customer_locale = array(
                'mark' => '',
                'description' => false
            );
        }

        $GLOBALS['smarty']->assign('CUSTOMER_LOCALE', $customer_locale);

        // Can we proceed?
        if (isset($_POST['proceed']) && in_array($_GET['_a'], array('basket', 'cart'))) {
            httpredir(currentPage(null, array('_a' => 'confirm')));
        }

        // Display basket
        $this->_displayBasket($editable);

        if (!empty($this->_basket['contents']) && is_array($this->_basket['contents'])) {
            $gatway_proceed = (($_GET['_a']=='confirm' || $_GET['_a']=='checkout') && isset($_POST['proceed'])) ? true : false;

            // Check shipping has been defined for tangible orders
            if (!isset($this->_basket['digital_only']) && !isset($this->_basket['shipping'])) {
                $de = $GLOBALS['config']->get('config', 'disable_estimates');
                if (($de == '1' && $this->_basket['delivery_address']['user_defined']) || ($de == '0' || !$de)) {
                    $GLOBALS['gui']->setError($GLOBALS['language']->checkout['error_shipping']);
                }
                $gatway_proceed = false;
            }

            // Check billing address is user defined
            if ($this->_basket['billing_address']['user_defined']==false) {
                $gatway_proceed = false;
            }

            // Check new registered cutomers has been to confirmed address page
            if ($GLOBALS['user']->is() && !$this->_basket['confirm_addresses']) {
                $gatway_proceed = false;
            }

            // Check there are no system errors
            if ($GLOBALS['session']->has('GUI_MESSAGE')) {
                $gatway_proceed = false;
            }

            // All good we go to payment
            if ($gatway_proceed) {
                httpredir('index.php?_a=gateway');
            } elseif (isset($_POST['user'])) {
                httpredir('index.php?_a=confirm');
            }
        }
        $content = $GLOBALS['smarty']->fetch('templates/content.checkout.php');
        foreach ($GLOBALS['hooks']->load('class.cubecart.basket') as $hook) {
            include $hook;
        }
        $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
    }

    /**
     * Display product categories
     */
    private function _category()
    {
        // Category
        $query = array();
        $search = false;
        if (isset($_POST['sort'])) {
            list($field, $order) = explode('|', $_POST['sort']);
            $_GET['sort'][$field] = $query['sort'][$field] =  $order;
            if (isset($_GET['search'])) {
                foreach ($_GET['search'] as $key => $value) {
                    $query['search'][$key] = $value;
                }
            }
        }

        if (isset($_REQUEST['search'])) {
            if (isset($_POST['search'])) {
                foreach ($_POST['search'] as $key => $value) {
                    $query['search'][$key] = $value;
                }
            }
            $search = true;
            // Insert into search records
            if (isset($_REQUEST['search']['keywords'])) {
                $keys = explode(' ', $_REQUEST['search']['keywords']);
                $terms = array();
                if (is_array($keys)) {
                    foreach ($keys as $key) {
                        if (empty($key)) {
                            continue;
                        }
                        $terms[] = $key;
                    }
                } else {
                    // a string, with no spaces
                    $terms[] = $keys;
                }
                if (!empty($terms)) {
                    foreach ($terms as $term) {
                        if ($GLOBALS['session']->has($term, 'search')) {
                            continue;
                        }
                        
                        $GLOBALS['session']->set($term, '1', 'search');

                        if (($select = $GLOBALS['db']->select('CubeCart_search', array('id', 'hits'), array('searchstr' => strtoupper($term)), false, 1, false, false)) !== false) {
                            $GLOBALS['db']->update('CubeCart_search', array('hits' => $select[0]['hits'] + 1), array('id' => $select[0]['id']), false);
                        } else {
                            $GLOBALS['db']->insert('CubeCart_search', array('searchstr' => strtoupper($term)));
                        }
                    }
                }
            }
        }
        if (!empty($query)) {
            $query['_a'] = $_GET['_a'];
            if ($search || $_GET['cat_id'] == 'sale') {
                $query['cat_id'] = $_GET['cat_id'];
            }
            ksort($query);
            httpredir('?'.http_build_query($query, null, '&'));
        }
        $GLOBALS['session']->delete('', 'search');

        $page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
        
        $catalogue_products_per_page = $GLOBALS['gui']->itemsPerPage();

        if (isset($_REQUEST['search']) && is_array($_REQUEST['search'])) {
            if (!$GLOBALS['catalogue']->searchCatalogue($_REQUEST['search'], $page, $catalogue_products_per_page)) {
                $GLOBALS['catalogue']->setCategory('cat_name', $GLOBALS['language']->navigation['search_results']);
            } else {
                $GLOBALS['catalogue']->setCategory('cat_name', sprintf($GLOBALS['language']->catalogue['notify_product_search'], $_REQUEST['search']['keywords']));
            }
            $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->common['search'], 'index.php?_a=search');
            $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->navigation['search_results'], currentPage());
        } else {
            $GLOBALS['catalogue']->searchCatalogue($_GET['cat_id'], $page, $catalogue_products_per_page);
            if ($_GET['cat_id'] == 'sale') {
                $GLOBALS['catalogue']->setCategory('cat_name', $GLOBALS['language']->navigation['saleitems']);
                $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->navigation['saleitems'], currentPage());
            }
        }

        $GLOBALS['catalogue']->categoryPagination($page);

        // Display Product listing
        $GLOBALS['catalogue']->displayCategory();
    }

    /**
     * Display certificates
     */
    private function _certificates()
    {
        // Gift Certificates

        foreach ($GLOBALS['hooks']->load('class.cubecart.construct.certificates') as $hook) {
            include $hook;
        }

        $gc = $GLOBALS['config']->get('gift_certs');
        if (!$gc['status']) {
            httpredir('index.php');
        }
        $meta_data = array(
            'description' => $gc['seo_meta_description'],
            'title'   => $gc['seo_meta_title'],
        );
        $GLOBALS['seo']->set_meta_data($meta_data);

        $error = false;
        $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->catalogue['gift_certificates'], currentPage());
        if (isset($_POST['gc'])) {
            // Validate submitted data
            $_POST['gc']['value'] = str_replace(',', '.', $_POST['gc']['value']);
            $_POST['gc']['value'] = preg_replace('/[^0-9.]*/', '', $_POST['gc']['value']); // Strip off currency symbols etc...
            if (!is_numeric($_POST['gc']['value'])) {
                $GLOBALS['gui']->setError($GLOBALS['language']->catalogue['error_gc_value']);
                $error = true;
            // Validate amount
            } elseif ((isset($gc['max']) && !empty($gc['max'])) && $_POST['gc']['value'] > $gc['max']) {
                $GLOBALS['gui']->setError($GLOBALS['language']->catalogue['error_gc_value_high']);
                $error = true;
            } elseif ((isset($gc['min']) && !empty($gc['min'])) && $_POST['gc']['value'] < $gc['min']) {
                $GLOBALS['gui']->setError($GLOBALS['language']->catalogue['error_gc_value_low']);
                $error = true;
            }
            // Validate email if email delivery
            if (strtolower($_POST['gc']['method']) == 'e' && !filter_var($_POST['gc']['email'], FILTER_VALIDATE_EMAIL)) {
                $GLOBALS['gui']->setError($GLOBALS['language']->catalogue['error_gc_email']);
                $error = true;
            }

            if (!$error) {
                $GLOBALS['cart']->add($gc['product_code'], $_POST['gc']);
            } else {
                $GLOBALS['smarty']->assign('POST', $_POST['gc']);
            }
        }

        if ($gc['status']=='2' && !$GLOBALS['user']->is()) {
            $purchase_enabled = false;
            $GLOBALS['gui']->setInfo($GLOBALS['language']->customer['login_register']);
        } else {
            $purchase_enabled = true;
        }
        
        $GLOBALS['smarty']->assign('LANG_CERT_VALUES', sprintf($GLOBALS['language']->catalogue['gift_certificate_value'], $GLOBALS['tax']->priceFormat($gc['min'], true, true, $purchase_enabled), $GLOBALS['tax']->priceFormat($gc['max'], true, true, $purchase_enabled)));
        $GLOBALS['smarty']->assign('ctrl_allow_purchase', $purchase_enabled);
        $GLOBALS['smarty']->assign('GC', $gc);
        $content = $GLOBALS['smarty']->fetch('templates/content.certificates.php');
        $GLOBALS['smarty']->assign('SECTION_NAME', 'giftcertificate');
        $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
    }

    /**
     * Display checkout
     */
    private function _checkout()
    {
        // Update basket if we need to!
        $GLOBALS['cart']->update();

        $GLOBALS['smarty']->assign('URL', array('login' => $GLOBALS['seo']->buildURL('login')));
        $GLOBALS['smarty']->assign('INCLUDE_CHECKOUT', true);

        $this->_basket =& $GLOBALS['cart']->basket;

        if (isset($_POST['comments']) && !empty($_POST['comments'])) {
            $this->_basket['comments'] = strip_tags(urldecode($_POST['comments']));
            $GLOBALS['cart']->save();
        }
        $GLOBALS['smarty']->assign('VAL_CUSTOMER_COMMENTS', isset($this->_basket['comments']) ? $this->_basket['comments'] : '');

        foreach ($GLOBALS['hooks']->load('class.cubecart.construct.confirm') as $hook) {
            include $hook;
        }
        // Display order confirmation page
        if (!$GLOBALS['user']->is()) {
            // Unregistered Users
            if (!isset($this->_basket['register'])) {
                $this->_basket['register'] = true;
            }
            if (!isset($_POST['username']) && isset($_POST['user']) && isset($_POST['billing'])) {
                $proceed = true;
                $optional = array('mobile', 'line2');

                $handle_post = array(
                    'user'  => 'customer',
                    'billing' => 'billing_address',
                    'delivery' => 'delivery_address',
                );
                foreach ($_POST as $index => $data) {
                    if (!in_array($index, $handle_post)) {
                        continue;
                    }
                    $missing_field = false;
                    foreach ($data as $key => $value) {
                        if (!in_array($key, $optional) && empty($value)) {
                            $proceed  = false;
                            $missing_field  = true;
                        }
                    }
                }

                if ($missing_field) {
                    $GLOBALS['gui']->setError($GLOBALS['language']->common['error_fields_required']);
                }

                // Check T&C's have been agreed to
                if (!$GLOBALS['config']->get('config', 'disable_checkout_terms') && ($GLOBALS['db']->select('CubeCart_documents', false, array('doc_terms' => '1')) !== false) && !isset($_POST['terms_agree'])) {
                    $GLOBALS['gui']->setError($GLOBALS['language']->account['error_terms_agree']);
                    $errors['terms_agree'] = true;
                } elseif ($_POST['terms_agree']) {
                    $this->_basket['terms_agree'] = true;
                }

                // Handle user data, and put into the basket array
                $this->_basket['customer'] = $_POST['user'];

				$old_addresses = $GLOBALS['user']->addressCompare($this->_basket['billing_address'], $this->_basket['delivery_address']);

                $this->_basket['billing_address'] = array(
                    'user_defined' => true,
                    'title'   => $_POST['user']['title'],
                    'first_name'  => $_POST['user']['first_name'],
                    'last_name'  => $_POST['user']['last_name'],
                    'company_name'  => $_POST['billing']['company_name'],
                    'line1'   => $_POST['billing']['line1'],
                    'line2'   => $_POST['billing']['line2'],
                    'town'    => $_POST['billing']['town'],
                    'postcode'   => strtoupper($_POST['billing']['postcode']),
                    'state_id'   => $_POST['billing']['state'],
                    'state'   => getStateFormat($_POST['billing']['state'], 'id', 'name'),
                    'state_abbrev'  => getStateFormat($_POST['billing']['state'], 'id', 'abbrev'),
                    'country'   => $_POST['billing']['country'],
                    'country_id'  => $_POST['billing']['country'],
                    'country_iso'  => getCountryFormat($_POST['billing']['country'], 'numcode', 'iso'),
                    'country_name' => getCountryFormat($_POST['billing']['country'], 'numcode', 'name'),
                    'w3w' => $_POST['billing']['w3w']
                );
                $required_billing_fields = $GLOBALS['user']->getRequiredAddressFields($_POST['billing']['country']);
                foreach ($this->_basket['billing_address'] as $key => $value) {
                    if (in_array($key, $required_billing_fields) && empty($value)) {
                        $errors['billing_address'] = true;
                    }
                }
                if (isset($errors['billing_address']) && $errors['billing_address']) {
                    $error_messages[] = $GLOBALS['language']->account['error_billing_fields_missing'];
                }

                if (isset($_POST['delivery']) && !isset($_POST['delivery_is_billing'])) {
                    $required_delivery_fields = $GLOBALS['user']->getRequiredAddressFields($_POST['delivery']['country']);
                    foreach ($_POST['delivery'] as $key => $value) {
                        if (in_array($key, $required_delivery_fields) && empty($value)) {
                            $errors['delivery_address'] = true;
                        }
                    }
                    if (isset($errors['delivery_address']) && $errors['delivery_address']) {
                        $error_messages[] = $GLOBALS['language']->account['error_delivery_fields_missing'];
                    }

                    $this->_basket['delivery_address'] = array(
                        'user_defined' => true,
                        'title'   => $_POST['delivery']['title'],
                        'first_name'  => $_POST['delivery']['first_name'],
                        'last_name'  => $_POST['delivery']['last_name'],
                        'company_name'  => $_POST['delivery']['company_name'],
                        'line1'   => $_POST['delivery']['line1'],
                        'line2'   => $_POST['delivery']['line2'],
                        'town'    => $_POST['delivery']['town'],
                        'postcode'   => strtoupper($_POST['delivery']['postcode']),
                        'state_id'   => $_POST['delivery']['state'],
                        'state'   => getStateFormat($_POST['delivery']['state'], 'id', 'name'),
                        'state_abbrev'  => getStateFormat($_POST['delivery']['state'], 'id', 'abbrev'),
                        'country'   => $_POST['delivery']['country'],
                        'country_id'  => $_POST['delivery']['country'],
                        'country_iso'  => getCountryFormat($_POST['delivery']['country'], 'numcode', 'iso'),
                        'country_name' => getCountryFormat($_POST['delivery']['country'], 'numcode', 'name'),
                        'w3w' => $_POST['delivery']['w3w']
                    );
                } else {
                    $this->_basket['delivery_address'] = $this->_basket['billing_address'];
                }

                $this->_basket['delivery_address']['is_billing'] = (isset($_POST['delivery_is_billing'])) ? true : false;

                $new_addresses = $GLOBALS['user']->addressCompare($this->_basket['billing_address'], $this->_basket['delivery_address']);

                if ($new_addresses!==$old_addresses) {
                    // Set notice to prevent proceed to payment screen
                    $message = $GLOBALS['cart']->basket['digital_only'] ? $GLOBALS['language']->checkout['confirm_billing'] : $GLOBALS['language']->account["notify_address_updated"];
                    $GLOBALS['gui']->setNotify($message);
                }

                if ($GLOBALS['gui']->recaptchaRequired()) {
                    if (($message = $GLOBALS['session']->get('error', 'recaptcha')) === false) {
                        //If the error message from recaptcha fails for some reason:
                        $error_messages[] = $GLOBALS['language']->form['verify_human_fail'];
                    } else {
                        $error_messages[] = $GLOBALS['session']->get('error', 'recaptcha');
                    }
                    $errors['recaptcha'] = true;
                }

                // Check email is valid
                if (!filter_var($_POST['user']['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors['email'] = true;
                    $error_messages[] = $GLOBALS['language']->common['error_email_invalid'];
                }
                // Check email is not in use
                if ($GLOBALS['db']->select('CubeCart_customer', array('email'), array('email' => $_POST['user']['email'], 'type' => 1))) {
                    // Email in use
                    $errors['email'] = true;
                    $error_messages[] = $GLOBALS['language']->account['error_email_in_use'];
                }
                // Check passwords match if not empty
                if (isset($_POST['register']) && $_POST['register']==1 && !empty($_POST['password']) && $_POST['password'] !== $_POST['passconf']) {
                    $errors['password'] = true;
                    $error_messages[] = $GLOBALS['language']->account['error_password_mismatch'];
                }
                if (isset($_POST['register']) && $_POST['register']==1 && strlen($_POST['password']) < 6) {
                    $errors['password'] = true;
                    $error_messages[] = $GLOBALS['language']->account['error_password_length'];
                }
                if (isset($_POST['register']) && $_POST['register']==1 && strlen($_POST['password']) > 64) {
                    $errors['password'] = true;
                    $error_messages[] = $GLOBALS['language']->account['error_password_length_max'];
                }
                if (preg_match("/[a-z]/i", $_POST['user']['phone'])) {
                    $errors['phone'] = true;
                    $error_messages[] = $GLOBALS['language']->account['error_valid_phone'];
                }
                if (!empty($_POST['user']['mobile']) && preg_match("/[a-z]/i", $_POST['user']['mobile'])) {
                    $errors['phone'] = true;
                    $error_messages[] = $GLOBALS['language']->account['error_valid_mobile_phone'];
                }
                if (is_array($error_messages)) {
                    $GLOBALS['gui']->setError($error_messages);
                }

                if (!isset($errors)) {
                    // Create the user account
                    $_POST['user']['password'] = (isset($_POST['register']) && $_POST['register']==1) ? md5($_POST['password']) : md5(time().$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']);
                    $type = (isset($_POST['register']) && $_POST['register']==1) ? 1 : 2;
                    $user_id = $GLOBALS['user']->createUser($_POST['user'], false, $type);
                    $this->_basket['customer']['customer_id'] = $user_id;

                    // Insert a new BILLING address
                    $address = array(
                        'customer_id' => $user_id,
                        'billing'  => true,
                        'default'  => (isset($_POST['delivery'])) ? false : true,
                        'title'   => $this->_basket['customer']['title'],
                        'first_name' => $this->_basket['customer']['first_name'],
                        'last_name'  => $this->_basket['customer']['last_name'],
                        'description' => $GLOBALS['language']->address['default_billing_address'],
                    );

                    $GLOBALS['user']->saveAddress(array_merge($this->_basket['billing_address'], $address), $user_id);

                    // Insert a new DELIVERY address
                    $address = array(
                        'customer_id' => $user_id,
                        'billing'  => false,
                        'default'  => true, // This selects the correct delivery address
                        'description' => $GLOBALS['language']->address['default_delivery_address'],
                    );
                    $GLOBALS['user']->saveAddress(array_merge($this->_basket['delivery_address'], $address), $user_id);

                    foreach ($GLOBALS['hooks']->load('class.cubecart.construct.confirm.create_user.created') as $hook) {
                        include $hook;
                    }

                    // Log in
                    $GLOBALS['session']->set('redir', $GLOBALS['rootRel'].'index.php?_a=confirm');
                    if (isset($_POST['register']) && $_POST['register']==1 && !$GLOBALS['user']->authenticate($_POST['user']['email'], $_POST['password'], false, false, false, false)) {
                        httpredir('index.php?_a=login');
                    }
                }

                if (isset($_POST['register']) && $_POST['register']==1) {
                    $this->_basket['register'] = true;
                } else {
                    $this->_basket['register'] = false;
                }

                if (isset($errors) && is_array($errors)) {
                    foreach ($errors as $parent => $error) {
                        if (is_array($error)) {
                            foreach ($error as $key => $value) {
                                unset($this->_basket[$parent][$key]);
                            }
                        }
                    }
                }
                $GLOBALS['cart']->save();
            }

            $GLOBALS['smarty']->assign('ALLOW_DELIVERY_ADDRESS', ($GLOBALS['config']->get('config', 'basket_allow_non_invoice_address') && !$GLOBALS['cart']->getBasketDigital()));

            if (isset($this->_basket['customer'])) {
                $GLOBALS['smarty']->assign('USER', $this->_basket['customer']);
            }
            if ((isset($this->_basket['billing_address']) && $this->_basket['billing_address']['user_defined']) || $this->_basket['billing_address']['estimate']) {
                $GLOBALS['smarty']->assign('BILLING', $this->_basket['billing_address']);
            }
            if (isset($this->_basket['delivery_address']) && $this->_basket['delivery_address']['user_defined'] || $this->_basket['delivery_address']['estimate']) {
                $GLOBALS['smarty']->assign('DELIVERY', $this->_basket['delivery_address']);
            }

            // @todo fix this - should auto select on first load
            if (!isset($this->_basket['delivery_address']['is_billing']) || $this->_basket['delivery_address']['is_billing']) {
                $GLOBALS['smarty']->assign('DELIVERY_CHECKED', 'checked="checked"');
            }

            // Parse page elements
            if (($countries = $GLOBALS['db']->select('CubeCart_geo_country', false, 'status > 0', array('name' => 'ASC'))) !== false) {
                foreach ($countries as $country) {
                    $country['selected'] = '';
                    if (isset($this->_basket['billing_address']['country_id']) && !empty($this->_basket['billing_address']['country_id'])) {
                        if ($country['numcode'] == $this->_basket['billing_address']['country_id']) {
                            $country['selected'] = 'selected="selected"';
                        }
                    } else {
                        if ($country['numcode'] == $GLOBALS['config']->get('config', 'store_country')) {
                            $country['selected'] = 'selected="selected"';
                        }
                    }
                    $country['selected_d'] = '';
                    if (isset($this->_basket['delivery_address']['country_id']) && !empty($this->_basket['delivery_address']['country_id'])) {
                        if ($country['numcode'] == $this->_basket['delivery_address']['country_id']) {
                            $country['selected_d'] = 'selected="selected"';
                        }
                    } else {
                        if ($country['numcode'] == $GLOBALS['config']->get('config', 'store_country')) {
                            $country['selected_d'] = 'selected="selected"';
                        }
                    }
                    $GLOBALS['smarty']->append('COUNTRIES', $country);
                }
                $GLOBALS['smarty']->assign('STATE_JSON', state_json());
            }

            foreach ($GLOBALS['hooks']->load('class.cubecart.prerecaptcha.confirm') as $hook) {
                include $hook;
            }

            $GLOBALS['smarty']->assign('TERMS_CONDITIONS', (!$GLOBALS['config']->get('config', 'disable_checkout_terms') && $terms = $GLOBALS['db']->select('CubeCart_documents', false, array('doc_terms' => '1'))) ? $GLOBALS['seo']->buildURL('doc', $terms[0]['doc_id'], '&') : false);
            if (isset($_POST['terms_agree']) && $_POST['terms_agree']==1) {
                $this->_basket['terms_agree'] = true;
            }
            if (isset($_POST['mailing_list']) && $_POST['mailing_list']==1) {
                $this->_basket['mailing_list'] = true;
                $newsletter = Newsletter::getInstance();
                $newsletter->subscribe($this->_basket['customer']['email'], (int)$user_id);
            }
            $GLOBALS['smarty']->assign('REGISTER_CHECKED', (isset($this->_basket['register']) && $this->_basket['register']) ? 'checked="checked"' : '');
            $GLOBALS['smarty']->assign('TERMS_CONDITIONS_CHECKED', (isset($this->_basket['terms_agree']) && $this->_basket['terms_agree']) ? 'checked="checked"' : '');
            $GLOBALS['smarty']->assign('MAILING_LIST_SUBSCRIBE', (isset($this->_basket['mailing_list']) && $this->_basket['mailing_list']) ? 'checked="checked"' : '');
        } else {
            // Registered users - Display predefined addresses, if any exist
            $this->_displayAddresses();
        }
    }

    /**
     * Display part of the checkout process
     */
    private function _checkoutProcess($section = null)
    {
        switch ($section) {
            case 'complete':
                $status = 3;
                break;
            case 'gateway':
                $status = 2;
                break;
            default:
                $status = ($GLOBALS['user']->is() || $_GET['_a']=='confirm' || (isset($GLOBALS['cart']->basket['register']) && $GLOBALS['cart']->basket['register']==false)) ? 1 : 0;
                break;
        }

        foreach ($GLOBALS['hooks']->load('class.cubecart.checkout_progress') as $hook) {
            include $hook;
        }

        $blocks = array(
            0 => $GLOBALS['language']->checkout['process_basket'],
            1 => $GLOBALS['language']->checkout['process_checkout'],
            2 => $GLOBALS['language']->checkout['process_payment'],
            3 => $GLOBALS['language']->checkout['process_complete'],
        );
        
        // v6 skins don't have a payment step!
        $skin_data = $GLOBALS['gui']->getSkinData();
        
        if (version_compare($skin_data['info']['compatible']['min'], '6.0.0a', '>=')) {
            unset($blocks[2]);
        }
        
        $step = 1;
        foreach ($blocks as $key => $title) {
            switch (true) {
            case ($key == $status):
                $class[] = 'current';
                break;
            case ($key < $status):
                $class[] = 'previous';
                break;
            default:
                $class[] = 'next';
            }
            if ($key == count($blocks)-1) {
                $class[] = 'last';
            }
            if ($key > $status) {
                $url = '#';
            } else {
                switch ($key) {
                case 0:
                    $url = '?_a=basket';
                    break;
                case 1:
                    $url = '?_a=confirm';
                    break;
                case 2:
                    $url = '?_a=gateway';
                    break;
                default:
                    $url = '#';
                }
            }
            $vars[$key] = array('title' => $title, 'class' => implode(' ', $class), 'id' => '', 'url' => $url, 'step' => $step);
            $step++;
            unset($class);
        }
        $GLOBALS['smarty']->assign('BLOCKS', $vars);
        $GLOBALS['smarty']->assign('CHECKOUT_PROGRESS', $GLOBALS['smarty']->fetch('templates/box.progress.php'));
    }

    /**
     * Display final checkout process
     */
    private function _complete()
    {
        $this->_basket =& $GLOBALS['cart']->basket;

        if (isset($this->_basket['cart_order_id'])) {
            $this->_checkoutProcess('complete');
            foreach ($GLOBALS['hooks']->load('class.cubecart.construct.complete') as $hook) {
                include $hook;
            }
            $formatting  = array('discount', 'price', 'shipping', 'subtotal', 'total', 'total_tax');
            $empty_basket = true;
            # Get the order details, and display a receipt
            if (($orders = $GLOBALS['db']->select('CubeCart_order_summary', false, array('cart_order_id' => $this->_basket['cart_order_id']), false, false, false, false)) !== false) {
                $order = $orders[0];
                $GLOBALS['user']->setGhostId($order['customer_id']);
                if (($items = $GLOBALS['db']->select('CubeCart_order_inventory', false, array('cart_order_id' => $this->_basket['cart_order_id']))) !== false) {
                    $GLOBALS['smarty']->assign('GA_ITEMS', $items);
                    $prod_ids = array();
                    foreach ($items as $item) {
                        array_push($prod_ids, $item['product_id']);
                        foreach ($item as $key => $value) {
                            if (!in_array($key, $formatting)) {
                                continue;
                            } elseif ($key == 'price') {
                                $item['price_total'] = $GLOBALS['tax']->priceFormat(($item['price'] * $item['quantity']), true);
                            }
                            $item[$key] = $GLOBALS['tax']->priceFormat($value);
                        }
                        $item['options'] = Order::getInstance()->unSerializeOptions($item['product_options']);
                        $vars['items'][] = $item;
                    }
                    if ($cats = $GLOBALS['db']->select('`'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_category_index` AS `I` INNER JOIN `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_category` AS `C` ON `I`.`cat_id` = `C`.`cat_id`', '`I`.`product_id`, `C`.`cat_name`', '`I`.`product_id` IN ('.implode(',', $prod_ids).') AND `primary` = 1')) {
                        $cat_names = array();
                        foreach ($cats as $cat) {
                            $cat_names[$cat['product_id']] = $cat['cat_name'];
                        }
                        $GLOBALS['smarty']->assign('ITEM_CATS', $cat_names);
                    }
                    $GLOBALS['smarty']->assign('ITEMS', $vars['items']);
                }
                // Retrieve taxes
                if (($taxes = $GLOBALS['db']->select('CubeCart_order_tax', false, array('cart_order_id' => $order['cart_order_id']))) !== false) {
                    $GLOBALS['tax']->loadTaxes(($GLOBALS['config']->get('config', 'basket_tax_by_delivery')) ? (int)$order['country'] : (int)$order['country_d']);
                    foreach ($taxes as $vat) {
                        $detail = $GLOBALS['tax']->fetchTaxDetails($vat['tax_id']);
                        $vars['taxes'][] = array('name' => $detail['name'], 'value' => $GLOBALS['tax']->priceFormat($vat['amount'], true));
                    }
                } elseif (!empty($order['total_tax']) && $order['total_tax'] > 0) {
                    $vars['taxes'][] = array('name' => $GLOBALS['language']->basket['total_tax'], 'value' => $GLOBALS['tax']->priceFormat($order['total_tax']));
                }

                $GLOBALS['smarty']->assign('TAXES', $vars['taxes']);
                $order['state'] = getStateFormat($order['state']);
                $order['state_d'] = getStateFormat($order['state_d']);

                // Analytics
                if (in_array($order['status'], array(2, 3))) {
                    $vars['ga_sum'] = $order;
                    $vars['ga_sum']['country_iso'] = getCountryFormat($order['country'], 'numcode', 'iso');
                    $vars['ga_sum']['google_id'] = $GLOBALS['config']->get('config', 'google_analytics');
                    $vars['ga_sum']['store_name'] = $GLOBALS['config']->get('config', 'store_name');
                    $GLOBALS['smarty']->assign('GA_SUM', $vars['ga_sum']);
                }

                $order['country'] = getCountryFormat($order['country']);
                $order['country_d'] = getCountryFormat($order['country_d']);

                $order['order_status'] = $GLOBALS['language']->order_state['name_'.$order['status']];

                foreach ($order as $key => $value) {
                    if (!in_array($key, $formatting)) {
                        continue;
                    }
                    $order[$key] = $GLOBALS['tax']->priceFormat($value);
                }
                $order['order_date_formatted'] = formatTime($order['order_date'], false, true);

                foreach ($GLOBALS['hooks']->load('class.cubecart.order_summary') as $hook) {
                    include $hook;
                }
                $order['basket'] = unserialize($order['basket']);

                if ($order['discount']>0) {
                    $GLOBALS['smarty']->assign('DISCOUNT', true);
                }

                $GLOBALS['smarty']->assign('SUM', $order);

                switch ($order['status']) {
                case self::ORDER_PENDING:
                    $GLOBALS['gui']->setNotify($GLOBALS['language']->confirm['order_pending']);
                    break;
                case self::ORDER_PROCESS:
                    $GLOBALS['gui']->setNotify($GLOBALS['language']->confirm['order_processing']);
                    break;
                case self::ORDER_COMPLETE:
                    if((int)preg_replace('/[^0-9]/', '', $order['total'])==0) {
                        $GLOBALS['gui']->setNotify($GLOBALS['language']->confirm['free_order_complete']);
                    } else {
                        $GLOBALS['gui']->setNotify($GLOBALS['language']->confirm['order_complete']);
                    }
                    break;
                case self::ORDER_DECLINED:
                case self::ORDER_FAILED:
                    $empty_basket = false;
                    $GLOBALS['gui']->setError($GLOBALS['language']->confirm['order_failed']);
                    $GLOBALS['smarty']->assign('CTRL_PAYMENT', true);
                    break;
                case self::ORDER_CANCELLED:
                    $GLOBALS['gui']->setError($GLOBALS['language']->confirm['order_cancelled']);
                    break;
                }

                // Display Affilate Tracker code
                $affiliates = $this->_getAffiliates(self::AFFILIATE_COMPLETE);
                if ($affiliates) {
                    $GLOBALS['smarty']->assign('AFFILIATES', $affiliates);
                }
                
                $ga_id = $GLOBALS['config']->get('config', 'google_analytics');
                $ga_id = trim($ga_id);
                $GLOBALS['smarty']->assign('ANALYTICS', !empty($ga_id) ? $ga_id : false);

                $content = $GLOBALS['smarty']->fetch('templates/content.receipt.php');
                $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
            }
            // Empty the basket
            if ($empty_basket) {
                $GLOBALS['cart']->clear();
            }
        } else {
            httpredir($GLOBALS['rootRel'].'index.php?_a=basket');
        }
    }

    /**
     * Display contact page
     */
    private function _contact()
    {
        // Contact Form
        $contact = $GLOBALS['config']->get('Contact_Form');
        if ($contact && $contact['status']) {
            $meta_data = array(
                'description' => $contact['seo_meta_description'],
                'title'   => $contact['seo_meta_title'],
            );
            $GLOBALS['seo']->set_meta_data($meta_data);
            $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->documents['document_contact'], $GLOBALS['seo']->buildURL('contact'));
            if (isset($_POST['contact'])) {
                $error = false;
                $required = array('email', 'name', 'subject', 'enquiry');
                
                foreach ($GLOBALS['hooks']->load('class.cubecart.contact') as $hook) {
                    include $hook;
                }
                
                $GLOBALS['smarty']->assign('MESSAGE', $_POST['contact']);
                // Validation
                foreach ($_POST['contact'] as $key => $value) {
                    if (in_array($key, $required) && empty($value)) {
                        $GLOBALS['gui']->setError($GLOBALS['language']->common['error_fields_required']);
                        $error = true;
                        break;
                    }
                }
                if (!filter_var($_POST['contact']['email'], FILTER_VALIDATE_EMAIL)) {
                    $GLOBALS['gui']->setError($GLOBALS['language']->common['error_email_invalid']);
                    $error = true;
                }

                // reCAPTCHA, if enabled
                if ($GLOBALS['gui']->recaptchaRequired()) {
                    if (($message = $GLOBALS['session']->get('error', 'recaptcha')) === false) {
                        //If the error message from recaptcha fails for some reason:
                        $GLOBALS['gui']->setError($GLOBALS['language']->form['verify_human_fail']);
                    } else {
                        $GLOBALS['gui']->setError($GLOBALS['session']->get('error', 'recaptcha'));
                    }
                    $error = true;
                }
                if (!$error) {
                    $email = (isset($contact['email']) && filter_var($contact['email'], FILTER_VALIDATE_EMAIL)) ? $contact['email'] : $GLOBALS['config']->get('config', 'email_address');
                    // Send email to correct department
                    $mailer = new Mailer();

                    $department = $GLOBALS['config']->get('config', 'store_name');
                    if (isset($_POST['contact']['dept']) && is_array($contact['department'])) {
                        $key  = (int)$_POST['contact']['dept'];
                        $department = $contact['department'][$key]['name'];
                        $email  = (!empty($contact['department'][$key]['email'])) ? $contact['department'][$key]['email'] : $email;
                        $mailer->AddAddress($email, $department);
                    }
                    // Load content, assign variables
                    $mailer->IsHTML(false);
                    $mailer->AddAddress($email, $department);
                    $from_name = strip_tags($_POST['contact']['name']);
                    $from_email = $_POST['contact']['email'];
                    if (isset($_POST['contact']['cc'])) {
                        $mailer->AddAddress($from_email, $from_name);
                    }
                    $mailer->addReplyTo($_POST['contact']['email'], strip_tags($_POST['contact']['name']));
                    $mailer->Subject = html_entity_decode(strip_tags($_POST['contact']['subject']), ENT_QUOTES);
                    $enquiry = html_entity_decode(strip_tags($_POST['contact']['enquiry']), ENT_QUOTES);
                    if(!empty($_POST['contact']['phone'])) {
                        $enquiry .= "\r\n\r\n".$GLOBALS['language']->address['phone'].': '.$_POST['contact']['phone'];
                    }
                    $mailer->Body  = sprintf($GLOBALS['language']->contact['email_content'], $_POST['contact']['name'], $_POST['contact']['email'], $department, $enquiry);
                    foreach ($GLOBALS['hooks']->load('class.cubecart.contact.mailer') as $hook) {
                        include $hook;
                    }
                    // Send
                    $email_sent = $mailer->Send();
                    $email_data = array(
                        'subject' => $mailer->Subject,
                        'content_html' => '',
                        'content_text' => $mailer->Body,
                        'to' => "$department <$email>",
                        'from' => "$from_name <$from_email>",
                        'result' => $email_sent,
                        'email_content_id' => ''
                    );
                    $GLOBALS['db']->insert('CubeCart_email_log', $email_data);
                    if ($email_sent) {
                        $GLOBALS['gui']->setNotify($GLOBALS['language']->documents['notify_document_contact']);
                        httpredir('index.php');
                    } else {
                        $GLOBALS['gui']->setError($GLOBALS['language']->documents['error_document_contact']);
                    }
                }
            }

            // Display form
            $contact['description'] = base64_decode($contact['description']);
            $contact['description'] = ($contact['parse']=='1') ? $GLOBALS['smarty']->fetch('string:'.$contact['description']) : $contact['description'];
            if (!isset($_POST['contact']) && $GLOBALS['user']->is()) {
                $GLOBALS['smarty']->assign('MESSAGE', array('name' => $GLOBALS['user']->get('first_name').' '.$GLOBALS['user']->get('last_name'), 'email' => $GLOBALS['user']->get('email')));
            }
            $GLOBALS['smarty']->assign('CONTACT', $contact);
            if (isset($contact['department']) && is_array($contact['department'])) {
                foreach ($contact['department'] as $key => $dept) {
                    $dept['key']  = $key;
                    $dept['selected'] = (isset($_POST['contact']['dept']) && $_POST['contact']['dept'] == $key) ? ' selected="selected"' : '';
                    $vars['departments'][] = $dept;
                }
                $GLOBALS['smarty']->assign('DEPARTMENTS', $vars['departments']);
            }

            foreach ($GLOBALS['hooks']->load('class.cubecart.contact.display') as $hook) {
                include $hook;
            }
            $content = $GLOBALS['smarty']->fetch('templates/content.contact.php');
            $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
        } else {
            httpredir('index.php');
        }
    }

    /**
     * Display addresses
     */
    private function _displayAddresses()
    {
        $billing_address = false;
        $this->_basket =& $GLOBALS['cart']->basket;

        if ($GLOBALS['config']->get('config', 'basket_allow_non_invoice_address')) {
            $addresses = $GLOBALS['user']->getAddresses();
        } else {
            $addresses = $GLOBALS['user']->getDefaultAddress();
        }

        if ($addresses) {
            $GLOBALS['cart']->set('confirm_addresses', true);

            // Work out which address id to default to
            if (isset($_POST['delivery_address']) && is_numeric($_POST['delivery_address'])) {
                $selected = (int)$_POST['delivery_address'];
            } elseif (isset($this->_basket['delivery_address']) && isset($this->_basket['delivery_address']['address_id'])) {
                $selected = (is_array($this->_basket['delivery_address'])) ? (int)$this->_basket['delivery_address']['address_id'] : (int)$this->_basket['delivery_address'];
            } else {
                $selected = false;
            }
            // Loop addresses
            foreach ($addresses as $address) {
                if ($selected) {
                    $address['selected'] = ($address['address_id'] == $selected) ? 'selected="selected"' : '';
                    $address['checked']  = ($address['address_id'] == $selected) ? 'checked="checked"' : '';
                } else {
                    $address['selected'] = ($address['default']) ? 'selected="selected"' : '';
                    $address['checked']  = ($address['default']) ? 'checked="checked"' : '';
                }
                $address_list[$address['address_id']] = $address;

                unset($address['selected'], $address['checked']);

                if ($address['billing'] || !$GLOBALS['config']->get('config', 'basket_allow_non_invoice_address')) {
                    $billing_address = true;
                    $this->_basket['billing_address'] = $address;
                    $GLOBALS['cart']->save();
                    $GLOBALS['smarty']->assign('DATA', $address);
                }
                if ($selected && (int)$selected === (int)$address['address_id'] || !$selected && $address['default']) {
                    $this->_basket['delivery_address'] = $address;
                    $GLOBALS['cart']->save();
                }
            }
            if (!$billing_address) {
                $GLOBALS['gui']->setInfo($GLOBALS['language']->account['error_address_billing']);
                httpredir('?_a=addressbook&action=add&redir=confirm');
            }
            // If for some reason we have no delivery address defined but we do have billing address.. take that
            if (!$this->_basket['delivery_address']['user_defined'] && $this->_basket['billing_address']['user_defined']) {
                $this->_basket['delivery_address'] = $this->_basket['billing_address'];
                $GLOBALS['cart']->save();
            }

            $GLOBALS['smarty']->assign('ADDRESSES', $address_list);
            // Display selector, if allowed
            $GLOBALS['smarty']->assign('CTRL_DELIVERY', ($GLOBALS['config']->get('config', 'basket_allow_non_invoice_address') && !$GLOBALS['cart']->getBasketDigital()));

            $subscribed = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', 'subscriber_id', array('status' => '1', 'email' => $GLOBALS['user']->get('email')), false, false, false, false);
            $GLOBALS['smarty']->assign('USER_SUBSCRIBED', $subscribed);
        } else {
            // no address found - lets redirect to the 'add address' page
            $GLOBALS['gui']->setInfo($GLOBALS['language']->account['error_address_billing']);
            httpredir('?_a=addressbook&action=add');
        }
    }

    /**
     * Display basket
     */
    private function _displayBasket($editable = true)
    {
        // Display the basket/cart page
        $contents = $GLOBALS['cart']->get();

        if ($contents) {
            $gc = $GLOBALS['config']->get('gift_certs');
            $digital_only = true;
            foreach ($contents as $hash => $product) {
                if ((int)$product['product_id']>0) {
                    $product_list[] = $product['product_id']; // Certificate ID is NULL
                }
                $product['line_price_display'] = $GLOBALS['tax']->priceFormat($product['line_price']);
                $product['price_display'] = $GLOBALS['tax']->priceFormat($product['price']);

                if (!$product['digital']) {
                    $digital_only = false;
                }
                // Thumbnail
                $images = $GLOBALS['db']->select('CubeCart_image_index', false, array('product_id' => $product['product_id']), array('main_img' => 'DESC'), 1);
                $product['image'] = $GLOBALS['gui']->getProductImage($product['product_id'], array('checkout', 'gallery'));
                if (isset($gc['product_code']) && $product['product_code'] == $gc['product_code']) {
                    if ($gc['image']) {
                        $product['image'] = $GLOBALS['catalogue']->imagePath($gc['image'], array('checkout', 'gallery'), 'url');
                    }
                    $product['link'] = $GLOBALS['seo']->buildURL('certificates');
                } else {
                    $product['link'] = $GLOBALS['seo']->buildURL('prod', $product['product_id']);
                }
                $items[$hash] = $product;
            }
            $GLOBALS['smarty']->assign('ITEMS', array_reverse($items, true));

            // Get basket total
            if (isset($this->_basket['coupons']) && is_array($this->_basket['coupons']) || !empty($this->_basket['discount'])) {
                if (!empty($this->_basket['discount']) && $this->_basket['discount']>0) {
                    $GLOBALS['smarty']->assign('DISCOUNT', $GLOBALS['tax']->priceFormat($this->_basket['discount']));
                }

                if (is_array($this->_basket['coupons'])) {
                    foreach ($this->_basket['coupons'] as $coupon) {
                        $coupon['remove_code'] = $coupon['voucher'];
                        if ($coupon['type'] == 'fixed') {
                            $this->_basket['discount_type'] = 'f';
                            $coupon['value'] = $GLOBALS['tax']->priceFormat($coupon['value_display'], true);
                            $coupons[] = $coupon;
                        } elseif ($coupon['type'] == 'percent') {
                            $this->_basket['discount_type'] = $coupon['products'] ? 'pp' : 'p';
                            $coupon['voucher'] .= ' ('.$coupon['value'].'%)';
                            $coupon['value'] = $GLOBALS['tax']->priceFormat($coupon['value_display'], true);
                            $coupons[] = $coupon;
                        }
                    }
                    $GLOBALS['smarty']->assign('COUPONS', $coupons);
                }
            }

            foreach ($GLOBALS['hooks']->load('class.cubecart.post_coupon') as $hook) {
                include $hook;
            }

            // Shipping Calculations
            if (($shipping = $GLOBALS['cart']->loadShippingModules()) !== false) {
                $offset = 1;
                
                if ($this->_basket['free_coupon_shipping']==1) {
                    $GLOBALS['smarty']->assign('free_coupon_shipping', (bool)$this->_basket['free_coupon_shipping']);
                    $shipping['Free_Coupon_Shipping'] = array(
                        0 => array(
                              'name' => $GLOBALS['language']->basket['free_coupon_shipping'],
                              'value' => 0
                            )
                        );
                }
                
                $offset_matched = false;
                foreach ($shipping as $ship_name => $methods) {
                    $label = (!is_numeric($ship_name) && !empty($ship_name)) ? str_replace('_', ' ', $ship_name) : null;
                    foreach ($methods as $data) {
                        if ($data['tax_inclusive']) {
                            $GLOBALS['tax']->inclusiveTaxRemove($data['value'], $data['tax_id']);
                        }
                        
                        $regex = '/[^a-z0-9]/i';
                        if (preg_replace($regex, '', $data['name']) == preg_replace($regex, '', $ship_name)) {
                            $data['name'] = '';
                        }

                        $value = array(
                            'offset' 	=> $offset,
                            'name'		=> $ship_name, // e.g. UPS
                            'product'	=> $data['name'], // e.g. Ground
                            'value'		=> $data['value'],
                            'tax_id' 	=> $data['tax_id'], // Kept for legacy
                            'tax'		=> $data['tax']
                        );
                        $shipping_values[] = $value;
                        $data['name'] = empty($data['name']) ? '' : ' ('.$data['name'].')';
                        $data['desc'] = empty($data['desc']) ? '' : ' ('.$data['desc'].')';
                        $option = array(
                            'value'  => base64url_encode(json_encode($value)),
                            'display' => (isset($data['name'])) ? $GLOBALS['tax']->priceFormat($data['value'], true).$data['name'] : $data['desc']
                        );
                        if (isset($this->_basket['shipping']) && $this->_basket['shipping']['offset'] == $offset) {
                            $offset_matched = true;
                            $option['selected'] = ' selected="selected"';
                            if ((string)$value['value'] !== (string)$this->_basket['shipping']['value']) {
                                $this->_basket['shipping'] = $value;
                                $GLOBALS['cart']->save();
                                httpredir(currentPage());
                            }
                        } else {
                            $option['selected'] = '';
                        }
                        $offset++;
                        $shipping_list[$label][] = $option;
                    }
                }

                // Lets try to choose cheapest shipping option
                // for them if they haven't chosen already
                if (((!isset($this->_basket['shipping']) || $this->_basket['shipping']===0) && !$digital_only) || (!$offset_matched && isset($this->_basket['shipping']['offset']) && !$digital_only)) {
                    $shipping_defaults = $GLOBALS['config']->get('config', 'shipping_defaults');
                    foreach ($shipping_values as $value) {
                        if (!isset($default_shipping)) { $default_shipping = $value; } // Make sure we have a shipping value
                        switch($shipping_defaults) {
                            case '1': // Cheapest > 0
                            if (($value['value'] < $default_shipping['value'] || $default_shipping['value']==0) && $value['value']>0) { $default_shipping = $value; }
                            break;
                            case '2': // Most expensive
                            if ($value['value'] > $default_shipping['value']) { $default_shipping = $value; }
                            break;
                            default: // Cheapest
                            if ($value['value'] < $default_shipping['value']) { $default_shipping = $value; }
                        }
                        
                    }
                    if (!empty($default_shipping)) {
                        $GLOBALS['cart']->set('shipping', $default_shipping);
                        if (!isset($this->_basket['default_shipping_set'])) {
                            $GLOBALS['cart']->set('default_shipping_set', true);
                            if (!isset($this->_basket['free_coupon_shipping'])) {
                                httpredir(currentPage());
                            }
                        }
                    } elseif (!$GLOBALS['config']->get('config', 'allow_no_shipping')) {
                        trigger_error('Shipping not setup or allow no shipping not enabled', E_USER_WARNING);
                    }
                    if (isset($this->_basket['digital_only'])) {
                        unset($this->_basket['digital_only']);
                    } // Digital good removed fix
                } elseif ($digital_only) {
                    $GLOBALS['cart']->set('digital_only', true);
                }
            } else {
                if ($digital_only || $GLOBALS['config']->get('config', 'allow_no_shipping')) {
                    $GLOBALS['cart']->set('shipping', 0);
                    if ($digital_only) {
                        $GLOBALS['cart']->set('digital_only', true);
                    } #gift card purchased only
                } else {
                    trigger_error('Shipping not setup or allow no shipping not enabled', E_USER_WARNING);
                    unset($GLOBALS['cart']->basket['shipping'], $GLOBALS['cart']->basket['default_shipping_set']); // past 5.0.9
                }
                $shipping_list = false;
            }

            foreach ($GLOBALS['hooks']->load('class.cubecart.post_shipping') as $hook) {
                include $hook;
            }

            // Check if new shipping methods are avialble and notify if they are
            $shipping_hash = md5(serialize($shipping_list));
            if (isset($GLOBALS['cart']->basket['shipping_hash']) && !empty($GLOBALS['cart']->basket['shipping_hash']) && $shipping_hash!==$GLOBALS['cart']->basket['shipping_hash']){
                $GLOBALS['gui']->setNotify($GLOBALS['language']->checkout['check_shipping']);
            }
            $GLOBALS['cart']->basket['shipping_hash'] = $shipping_hash;

            if (!$digital_only && isset($this->_basket['digital_only'])) {
                unset($this->_basket['digital_only']); // Digital good removed fix
            }
            $GLOBALS['smarty']->assign('SHIPPING_VALUE', (isset($this->_basket['shipping'])) ? $GLOBALS['tax']->priceFormat($this->_basket['shipping']['value'], true) : '-');
            $GLOBALS['smarty']->assign('HIDE_OPTION_GROUPS', $GLOBALS['config']->get('config', 'disable_shipping_groups'));
            if (!$digital_only && $shipping) {
                $GLOBALS['smarty']->assign('SHIPPING', $shipping_list);
            }

            $GLOBALS['smarty']->assign('SUBTOTAL', $GLOBALS['tax']->priceFormat($GLOBALS['cart']->getSubTotal()));

            $GLOBALS['tax']->displayTaxes();
            $GLOBALS['smarty']->assign('TOTAL', $GLOBALS['tax']->priceFormat($GLOBALS['cart']->getTotal()));
            $checkout_button = (CC_SSL) ? $GLOBALS['language']->checkout['secure_checkout'] : $GLOBALS['language']->checkout['checkout'];
            $GLOBALS['smarty']->assign('CHECKOUT_BUTTON', $checkout_button);
            if ($this->_basket['weight'] > 0) {
                $GLOBALS['smarty']->assign('BASKET_WEIGHT', ($GLOBALS['config']->get('config', 'show_basket_weight')) ? (float)$this->_basket['weight'].strtolower($GLOBALS['config']->get('config', 'product_weight_unit')) : false);
            }
            
            $this->_listPaymentOptions($this->_basket['gateway']);

            // Alternate Checkouts - loaded as hooks
            $load_checkouts = true;
            foreach ($GLOBALS['hooks']->load('class.cubecart.display_basket.alternate') as $hook) {
                include $hook;
            }
            if (is_array($list_checkouts)) {
                ksort($list_checkouts);
            }
            if (isset($list_checkouts) && $load_checkouts==true) {
                $GLOBALS['smarty']->assign('CHECKOUTS', $list_checkouts);
            }
            // Related Products from most recent 30 orders containing this product.
            if (is_array($product_list) && !empty($product_list) && ($related_orders = $GLOBALS['db']->select('CubeCart_order_inventory', array('DISTINCT' => 'cart_order_id'), array('product_id' => $product_list), array('id' => 'DESC'), 30)) !== false) {
                foreach ($related_orders as $key => $data) {
                    $related[] = "'".$data['cart_order_id']."'";
                }
                if (($related_products = $GLOBALS['db']->select('CubeCart_order_inventory', array('DISTINCT' => 'product_id'), array('cart_order_id' => $related, '!product_id' => $product_list), false, 3)) !== false) {
                    foreach ($related_products as $related) {
                        if (!in_array($related['product_id'], $product_list)) {
                            $related = $GLOBALS['catalogue']->getProductData($related['product_id']);
                            $related['img_src'] = $GLOBALS['gui']->getProductImage($related['product_id']);
                            $related['url'] = $GLOBALS['seo']->buildURL('prod', $related['product_id'], '&');
                            
                            $related['ctrl_sale'] = (!$GLOBALS['tax']->salePrice($related['price'], $related['sale_price']) || !$GLOBALS['config']->get('config', 'catalogue_sale_mode')) ? false : true;
                            
                            $GLOBALS['catalogue']->getProductPrice($related);
                            $sale = $GLOBALS['tax']->salePrice($related['price'], $related['sale_price']);
                            
                            $related['price_unformatted']  = $related['price'];
                            $related['sale_price_unformatted'] = ($sale) ? $related['sale_price'] : null;
                            $related['price']  = $GLOBALS['tax']->priceFormat($related['price']);
                            $related['sale_price'] = ($sale) ? $GLOBALS['tax']->priceFormat($related['sale_price']) : null;
                            
                            if ($related['product_id']>0) {
                                $related_list[] = $related;
                            }
                        }
                    }
                    $GLOBALS['smarty']->assign('RELATED', $related_list);
                }
            }
            foreach ($GLOBALS['hooks']->load('class.cubecart.display_basket') as $hook) {
                include $hook;
            }
        }
    }

    /**
     * Display gateways (Semi deprecated)
     */
    private function _displayGateways($name = false)
    {
        $where = array('status' => '1');
        $this->_basket =& $GLOBALS['cart']->basket;

        if ($name) {
            $where['folder'] = $name;
            // Update Order Summary with gateway name
            $GLOBALS['db']->update('CubeCart_order_summary', array('gateway' => $name), array('cart_order_id' => $this->_basket['cart_order_id']));
        } else {
            $where['module'] = 'gateway';
        }
        $gateways = $GLOBALS['db']->select('CubeCart_modules', false, $where, array('position' => 'ASC'));
        // Gateway hooks
        foreach ($GLOBALS['hooks']->load('class.cubecart.display_gateways') as $hook) {
            include $hook;
        }

        if ($gateways) {
            if (count($gateways) == 1) {
                if (!isset($gateways[0])) {
                    sort($gateways);
                }
                // Auto jump to payment gateway
                $module = $GLOBALS['config']->get($gateways[0]['folder']);
                if (!$module) {
                    $module = $GLOBALS['config']->get($gateways[0]['base_folder']);
                }
                
                // Clever exceptions for Gateway Plugins (PayPal Pro, Google Checkout, et al)
                if (isset($gateways[0]['plugin']) && $gateways[0]['plugin']) {
                    $module = array_merge($gateways[0], $module);
                }
                $folder = (isset($gateways[0]['plugin']) && $gateways[0]['plugin']) ? 'plugins' : 'gateway';
                $class_path = CC_ROOT_DIR.'/modules/'.$folder.'/'.$gateways[0]['folder'].'/gateway.class.php';

                if (!file_exists($class_path) && isset($gateways[0]['base_folder'])) {
                    $class_path = CC_ROOT_DIR.'/modules/'.$folder.'/'.$gateways[0]['base_folder'].'/gateway.class.php';
                }

                if (file_exists($class_path)) {
                    include $class_path;
                    $gateway = new Gateway($module, $this->_basket);
                    if (method_exists($gateway, 'transfer')) {
                        $transfer = $gateway->transfer();

                        // Update Order Summary with gateway name
                        $GLOBALS['db']->update('CubeCart_order_summary', array('gateway' => $gateways[0]['folder']), array('cart_order_id' => $this->_basket['cart_order_id']));

                        switch (strtolower($transfer['submit'])) {
                        case 'iframe':
                            $transfer['mode'] = 'iframe';
                            if (method_exists($gateway, 'iframeURL')) {
                                $GLOBALS['smarty']->assign('IFRAME_SRC', $gateway->iframeURL());
                            }
                            if (method_exists($gateway, 'iframeForm')) {
                                $GLOBALS['smarty']->assign('IFRAME_FORM', $gateway->iframeForm());
                            }
                            $build_hidden_vars = false;
                            break;
                        case 'auto':
                        case 'automatic':
                            $transfer['mode'] = 'automatic';
                            $GLOBALS['smarty']->assign('BTN_PROCEED', $GLOBALS['language']->common['proceed']);
                            $build_hidden_vars = true;
                            break;
                        default:
                            if (method_exists($gateway, 'form')) {
                                $GLOBALS['smarty']->assign('LANG_AMOUNT_DUE', sprintf($GLOBALS['language']->checkout['make_payment'], $GLOBALS['tax']->priceFormat($this->_basket['total']), $this->_basket['cart_order_id']));
                                $GLOBALS['smarty']->assign('FORM_TEMPLATE', $gateway->form());
                                $GLOBALS['smarty']->assign('BTN_PROCEED', $GLOBALS['language']->gateway['make_payment']);
                            } else {
                                trigger_error(sprintf("Gateway '%s' has no form method, and can't be loaded.", get_class($gateway)), E_USER_WARNING);
                            }
                            $transfer['mode'] = 'manual';
                            $build_hidden_vars = true;
                            break;
                        }
                        if ($build_hidden_vars) {
                            $methods = array('fixedVariables', 'repeatVariables');
                            foreach ($methods as $method) {
                                if (method_exists($gateway, $method)) {
                                    $variables = $gateway->{$method}();
                                    if (is_array($variables)) {
                                        foreach ($variables as $name => $value) {
                                            $form_vars[$name] = $value;
                                        }
                                    }
                                } else {
                                    trigger_error(sprintf("Gateway '%s' has no %s method.", get_class($gateway), $method), E_USER_NOTICE);
                                }
                            }
                            if (isset($form_vars)) {
                                $GLOBALS['smarty']->assign('FORM_VARS', $form_vars);
                            }
                        }
                        $affiliates = $this->_getAffiliates(self::AFFILIATE_GATEWAY);
                        if ($affiliates) {
                            $GLOBALS['smarty']->assign('AFFILIATES', $affiliates);
                        }
                        $GLOBALS['smarty']->assign('TRANSFER', $transfer);
                    } else {
                        // If there's no transfer method, then it can't be used as a module
                        trigger_error(sprintf("Gateway '%s' has no transfer() method, so it can't be loaded.", get_class($gateway)), E_USER_WARNING);
                        # httpredir(currentPage());
                    }
                } else {
                    // No class found
                }
            } else {
                // List all available and enabled payment gateways
                foreach ($gateways as $gateway) {
                    $gateway_path  = CC_ROOT_DIR.'/modules/gateway/'.$gateway['folder'].'/gateway.class.php';
                    $plugin_path  = CC_ROOT_DIR.'/modules/plugins/'.$gateway['base_folder'].'/gateway.class.php';

                    if (!file_exists($gateway_path) && !file_exists($plugin_path)) {
                        continue;
                    }
                    $module = (isset($gateway['plugin']) && $gateway['plugin']) ? $gateway : $GLOBALS['config']->get($gateway['folder']);

                    $countries = (!empty($module['countries'])) ? unserialize($module['countries']) : false;
                    $disabled_countries = (!empty($module['disabled_countries'])) ? unserialize($module['disabled_countries']) : false;

                    // Check module isn't set for mobile / main only!
                    if (isset($module['scope']) && !empty($module['scope']) && ($module['scope']=='main' && $GLOBALS['gui']->mobile) || ($module['scope']=='mobile' && !$GLOBALS['gui']->mobile)) {
                        continue;
                    }

                    if (is_array($countries) && !in_array($GLOBALS['cart']->basket['delivery_address']['country_id'], $countries) || is_array($disabled_countries) && in_array($GLOBALS['cart']->basket['delivery_address']['country_id'], $disabled_countries)) {
                        continue;
                    }

                    if (preg_match('#\.(gif|jpg|png|jpeg|webp)$#i', strtolower($module['desc']))) {
                        $gateway['description'] = sprintf('<img src="%s" border="0" title="" alt="" />', $module['desc']);
                    } elseif (!empty($module['desc'])) {
                        $gateway['description'] = $module['desc'];
                    } else {
                        $gateway['description'] = $gateway['folder'];
                    }
                    $gateway['checked'] = (isset($gateway['default']) && $gateway['default']) ? 'checked="checked"' : '';
                    $gateway_list[] = $gateway;
                }
                $GLOBALS['smarty']->assign('GATEWAYS', $gateway_list);
            }
        } else {
            // Redirect to completion page, but leave as pending
            // They obviously dont have a payment gateway for some good reason
            // and it's just stupid leaving them on a blank page
            httpredir('index.php?_a=complete');
        }
        return;
    }

    /**
     * 404 Handling
     */
    private function _404()
    {
        foreach ($GLOBALS['hooks']->load('class.cubecart.404') as $hook) include $hook;
        header("HTTP/1.0 404 Not Found");
        $template = 'templates/content.404.php';
        
        if ($content = $GLOBALS['smarty']->templateExists($template)) {
            $content = $GLOBALS['smarty']->fetch($template);
        } else {
            $content = '<h2>'.$GLOBALS['language']->documents['404_title']."</h2>\r\n<p>".$GLOBALS['language']->documents['404_content'].'</p>';
        }
        $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
    }

    /**
     * Download
     */
    private function _download()
    {
        foreach ($GLOBALS['hooks']->load('class.cubecart.construct.download') as $hook) {
            include $hook;
        }
        $filemanager = new FileManager(FileManager::FM_FILETYPE_DL);
        if (isset($_REQUEST['accesskey']) && !empty($_REQUEST['accesskey']) && preg_match('/^[a-f0-9]{32}$/i', $_REQUEST['accesskey'])) {
            // Supress the debugger output
            $GLOBALS['debug']->supress();
            if($_GET['s']=='1') {
                $data = $filemanager->deliverDownload($_REQUEST['accesskey'], $error, true);
                $mime_parts = $filemanager->mimeParts($data['mimetype']);
                $GLOBALS['smarty']->assign('STREAM_URL', '?_a=download&accesskey='.$_REQUEST['accesskey']);
                $GLOBALS['smarty']->assign('DATA', $data);
                $GLOBALS['smarty']->assign('TYPE', $mime_parts['type']);
                foreach ($GLOBALS['hooks']->load('class.cubecart.stream') as $hook) include $hook;
                $GLOBALS['gui']->display('templates/main.stream.php');
            } else if ($filemanager->deliverDownload($_REQUEST['accesskey'], $error)) {
                exit;
            } else {
                if (!empty($error)) {
                    $GLOBALS['gui']->setError($GLOBALS['language']->filemanager['error_dl_'.$error]);
                }
                httpredir(currentPage(array('accesskey'), array('_a' => 'downloads')));
            }
        }

        if ($GLOBALS['user']->is()) {
            $page = (isset($_GET['p'])) ? $_GET['p'] : 1;
            $per_page = 50;
            $where = array('customer_id' => $GLOBALS['user']->getId());
            if (($downloads = $GLOBALS['db']->select('CubeCart_downloads', false, $where, array('digital_id' => 'DESC'), $per_page, $page, false)) !== false) {
                $GLOBALS['smarty']->assign('PAGINATION', $GLOBALS['db']->pagination($GLOBALS['db']->getFoundRows(), $per_page, $page, 5, 'p'));
                $GLOBALS['smarty']->assign('MAX_DOWNLOADS', (int)$GLOBALS['config']->get('config', 'download_count'));
                foreach ($downloads as $download) {
                    if (($product = $GLOBALS['db']->select('`'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_inventory` INNER JOIN `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary` ON `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_inventory`.`cart_order_id` = `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary`.`cart_order_id`', '`'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_inventory`.*, `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary`.`status`', array('id' => $download['order_inv_id']), false, 1, false, false)) !== false) {
                        $download['file_info'] = $filemanager->getFileInfo($download['product_id']);
                        if($download['file_info']['stream']==1) {
                            $type = $filemanager->mimeParts($download['file_info']['mimetype']);
                            $download['action'] = $type['type']=='video' ? $GLOBALS['language']->common['watch'] : $GLOBALS['language']->common['listen'];
                        } else {
                            $download['action'] = $GLOBALS['language']->common['download'];    
                        }
                        $download['expires'] = ($download['expire'] > 0) ? formatTime($download['expire']) : $GLOBALS['language']->common['never'];
                        $download['active'] = (!in_array($product[0]['status'],array(2,3)) || $download['expire'] > 0 && $download['expire'] < time() || (int)$download['downloads'] >= $GLOBALS['config']->get('config', 'download_count') && $GLOBALS['config']->get('config', 'download_count') > 0) ? false : true;
                        $download['deleted'] = false;
                        $download = array_merge($product[0], $download);
                    } else {
                        $download['deleted'] = true;
                    }
                    $download['product_url'] = $GLOBALS['seo']->buildURL('prod', $download['product_id']);
                    $vars['downloads'][] = $download;
                }
            } else {
                $vars['downloads'] = false;
            }
            $GLOBALS['smarty']->assign('DOWNLOADS', $vars['downloads']);
            $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->account['your_account'], 'index.php?_a=account');
            $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->account['your_downloads'], currentPage());
            $content = $GLOBALS['smarty']->fetch('templates/content.downloads.php');
            $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
        } else {
            httpredir('?_a=login');
        }
    }

    /**
     * Gateway
     */
    private function _gateway()
    {
        if (!isset($_REQUEST['gateway']) || (isset($_GET['cart_order_id']) && $_GET['retrieve'])) {
            Order::getInstance()->placeOrder();
        }

        foreach ($GLOBALS['hooks']->load('class.cubecart.construct.gateway') as $hook) {
            include $hook;
        }
                
        if (isset($_REQUEST['gateway']) && !empty($_REQUEST['gateway'])) {
            $gateway = $_REQUEST['gateway'];
        } elseif (!empty($GLOBALS['cart']->basket['gateway'])) {
            $gateway = $GLOBALS['cart']->basket['gateway'];
        } else {
            $gateway = false;
        }
        
        $this->_displayGateways($gateway);
        $this->_checkoutProcess('gateway');

        $content = $GLOBALS['smarty']->fetch('templates/content.gateway.php');
        $GLOBALS['smarty']->assign('SECTION_NAME', 'gateway');
        $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
    }

    /**
     * Get affiliates
     *
     * @param int
     */
    private function _getAffiliates($mode = self::AFFILIATE_COMPLETE)
    {
        $this->_basket =& $GLOBALS['cart']->basket;
        if (!empty($mode)) {
            if (($affiliates = $GLOBALS['db']->select('CubeCart_modules', array('folder'), array('module' => 'affiliate', 'status' => '1'))) !== false) {
                foreach ($affiliates as $affiliate) {
                    $module = $GLOBALS['config']->get($affiliate['folder']);
                    // Default to displaying the affilate trackers on the gateway page
                    if (!isset($module['display'])) {
                        $module['display'] = self::AFFILIATE_GATEWAY;
                    }
                    if ($module['display'] == $mode) {
                        $file_path = CC_ROOT_DIR.'/modules/affiliate/'.$affiliate['folder'].'/tracker.inc.php';
                        if (file_exists($file_path)) {
                            include $file_path;
                            $affiliate_html[] = $affCode;
                        }
                    }
                }
            }
        }
        return (isset($affiliate_html) && is_array($affiliate_html)) ? $affiliate_html : false;
    }

    /**
     * Get live help
     *
     * @param string $section
     * @param string $method
     *
     * @return string/bool
     */
    private function _getLiveHelp()
    {
        if (($livehelp_plugins = $GLOBALS['db']->select('CubeCart_modules', array('folder'), array('module' => 'livehelp', 'status' => '1'))) !== false) {
            foreach ($livehelp_plugins as $plugin) {
                $file_path = CC_ROOT_DIR.'/modules/social/'.$plugin['folder'].'/livehelp.class.php';
                if (file_exists($file_path)) {
                    if (!class_exists($plugin['folder'])) {
                        include $file_path;
                    }
                    $social_plugin = new $plugin['folder']($GLOBALS['config']->get($plugin['folder']), $section);
                    if (method_exists($social_plugin, $method)) {
                        $social_html[] = $social_plugin->$method();
                    }
                }
            }
        }
        return (isset($social_html) && is_array($social_html) && !empty($social_html[0])) ? $social_html : false;
    }

    /**
     * Get social
     *
     * @param string $section
     * @param string $method
     *
     * @return string/bool
     */
    private function _getSocial($section, $method = 'getButtonHTML')
    {
        if (($social_plugins = $GLOBALS['db']->select('CubeCart_modules', array('folder'), array('module' => 'social', 'status' => '1'))) !== false) {
            foreach ($social_plugins as $plugin) {
                $file_path = CC_ROOT_DIR.'/modules/social/'.$plugin['folder'].'/social.class.php';
                if (file_exists($file_path)) {
                    if (!class_exists($plugin['folder'])) {
                        include $file_path;
                    }
                    $social_plugin = new $plugin['folder']($GLOBALS['config']->get($plugin['folder']), $section);
                    if (method_exists($social_plugin, $method)) {
                        $social_html[] = $social_plugin->$method();
                    }
                }
            }
        }
        return (isset($social_html) && is_array($social_html) && !empty($social_html[0])) ? $social_html : false;
    }

    /**
     * List payment gateways
     */
    private function _listPaymentOptions($selected_gateway = '')
    {
        $gateways = $GLOBALS['db']->select('CubeCart_modules', false, array('module' => 'gateway', 'status' => '1'), array('position' => 'ASC'));
        // Gateway hooks
        foreach ($GLOBALS['hooks']->load('class.cubecart.display_gateways') as $hook) {
            include $hook;
        }
        
        // List all available and enabled payment gateways
        foreach ($gateways as $gateway) {
            $gateway_path  = CC_ROOT_DIR.'/modules/gateway/'.$gateway['folder'].'/gateway.class.php';
            $plugin_path  = CC_ROOT_DIR.'/modules/plugins/'.$gateway['base_folder'].'/gateway.class.php';

            if (!file_exists($gateway_path) && !file_exists($plugin_path)) {
                continue;
            }
            $module = (isset($gateway['plugin']) && $gateway['plugin']) ? $gateway : $GLOBALS['config']->get($gateway['folder']);

            $countries = (!empty($module['countries'])) ? unserialize($module['countries']) : false;
            $disabled_countries = (!empty($module['disabled_countries'])) ? unserialize($module['disabled_countries']) : false;

            // Check module isn't set for mobile / main only!
            if (isset($module['scope']) && !empty($module['scope']) && ($module['scope']=='main' && $GLOBALS['gui']->mobile) || ($module['scope']=='mobile' && !$GLOBALS['gui']->mobile)) {
                continue;
            }

            if (is_array($countries) && !in_array($GLOBALS['cart']->basket['delivery_address']['country_id'], $countries) || is_array($disabled_countries) && in_array($GLOBALS['cart']->basket['delivery_address']['country_id'], $disabled_countries)) {
                continue;
            }

            if (preg_match('#\.(gif|jpg|png|jpeg|webp)$#i', strtolower($module['desc']))) {
                $gateway['description'] = sprintf('<img src="%s" border="0" title="" alt="" />', $module['desc']);
            } elseif (!empty($module['desc'])) {
                $gateway['description'] = $module['desc'];
            } else {
                $gateway['description'] = $gateway['folder'];
            }
            $gateway['checked'] = ((isset($gateway['default']) && $gateway['default'] && $selected_gateway=='') || ($selected_gateway == $gateway['folder']) || count($gateways)==1) ? 'checked="checked"' : '';
            $gateway_list[] = $gateway;
        }
        $GLOBALS['smarty']->assign('GATEWAYS', $gateway_list);
    }

    /**
     * Login
     */
    private function _login()
    {
        $GLOBALS['session']->setBack();
        $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->account['login'], $GLOBALS['seo']->buildURL('login'));
        //If there is a cookie for the username then use it in the login
        if (isset($_COOKIE['username']) && !empty($_COOKIE['username'])) {
            $GLOBALS['smarty']->assign('USERNAME', $_COOKIE['username']);
            $GLOBALS['smarty']->assign('REMEMBER', true);
        } else {
            $GLOBALS['smarty']->assign('REMEMBER', false);
        }

        // Login Routines
        $login_html = array();
        foreach ($GLOBALS['hooks']->load('class.cubecart.login') as $hook) {
            include $hook;
        }
        $GLOBALS['smarty']->assign('LOGIN_HTML', $login_html);

        if (!isset($redir) && is_array($GLOBALS['cart']->basket) && is_array($GLOBALS['cart']->basket['contents'])) {
            $redir = 'index.php?_a=basket';
        } elseif (!isset($redir)) {
            $redir = 'index.php?_a=account';
        }
        $GLOBALS['smarty']->assign('REDIRECT_TO', $redir);
        $GLOBALS['smarty']->assign(
            'URL',
            array(
            'register' => $GLOBALS['seo']->buildURL('register'),
            'recover' => $GLOBALS['seo']->buildURL('recover'))
        );

        $content = $GLOBALS['smarty']->fetch('templates/content.login.php');
        $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
    }

    /**
     * Logout
     */
    private function _logout()
    {
        foreach ($GLOBALS['hooks']->load('class.cubecart.construct.logout') as $hook) {
            include $hook;
        }
        $GLOBALS['gui']->setNotify($GLOBALS['language']->account['notify_logged_out']);
        $GLOBALS['user']->logout();
    }

    /**
     * Newsletter
     */
    private function _newsletter()
    {
        $newsletter = Newsletter::getInstance();
        
        if (isset($_GET['do']) && !empty($_GET['do'])) {
            if ($newsletter->doubleOptIn($_GET['do'])) {
                $GLOBALS['gui']->setNotify($GLOBALS['language']->newsletter['dbl_opt_in_success']);
            } else {
                $GLOBALS['gui']->setError($GLOBALS['language']->newsletter['dbl_opt_in_fail']);
            }
            httpredir(currentPage(array('newsletter_id', 'do', '_a')));
        }

        // Newsletters
        $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->account['your_account'], 'index.php?_a=account');
        $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->newsletter['newsletters'], '?_a=newsletter');
        
        // Display Newsletter archive
        if (isset($_GET['newsletter_id']) && is_numeric($_GET['newsletter_id'])) {
            // Show a newsletter from the archive
            if (($content = $GLOBALS['db']->select('CubeCart_newsletter', false, array('newsletter_id' => (int)$_GET['newsletter_id'], 'status' => 1))) !== false) {
                $GLOBALS['gui']->addBreadcrumb($content[0]['subject'], '?_a=newsletter&newsletter_id='.(int)$_GET['newsletter_id']);
                $GLOBALS['smarty']->assign('NEWSLETTER', $content[0]);
                $GLOBALS['smarty']->assign('CTRL_VIEW', true);
            } else {
                httpredir(currentPage(array('newsletter_id')));
            }
        } else {
            if (isset($_POST['subscribe'])) {
                if ($newsletter->subscribe($_POST['subscribe'], $GLOBALS['user']->getId())) {
                    httpredir('?_a=unsubscribe');
                } elseif ($GLOBALS['user']->is()) {
                    $GLOBALS['gui']->setError($GLOBALS['language']->common['error_email_invalid']);
                } else {
                    if ($newsletter->unsubscribe($_POST['subscribe'])) {
                        httpredir('?_a=newsletter');
                    } else {
                        $GLOBALS['gui']->setError($GLOBALS['language']->common['error_email_invalid']);
                    }
                }
                httpredir(currentPage());
            }

            if (isset($_REQUEST['unsubscribe']) && filter_var($_REQUEST['unsubscribe'], FILTER_VALIDATE_EMAIL)) {
                if ($newsletter->unsubscribe($_REQUEST['unsubscribe'])) {
                    httpredir('?_a=newsletter');
                }
            }
            if (isset($_GET['verify'])) {
                if ($newsletter->verify($_GET['verify'])) {
                    $GLOBALS['gui']->setNotify($GLOBALS['language']->newsletter['notify_email_verified']);
                } else {
                    $GLOBALS['gui']->setError($GLOBALS['language']->common['error_email_verified']);
                }
                httpredir(currentPage(array('verify')));
            }

            if ($GLOBALS['user']->is()) {
                if (isset($_GET['action'])) {
                    $newsletter = Newsletter::getInstance();
                    switch (strtolower($_GET['action'])) {
                    case 'subscribe':
                    $newsletter->subscribe($GLOBALS['user']->get('email'), $GLOBALS['user']->get('customer_id'));
                        foreach ($GLOBALS['hooks']->load('class.newsletter.subscribe') as $hook) {
                            include $hook;
                        }
                        break;
                    case 'unsubscribe':
                        $newsletter->unsubscribe($GLOBALS['user']->get('email'), $GLOBALS['user']->get('customer_id'));
                        foreach ($GLOBALS['hooks']->load('class.newsletter.unsubscribe') as $hook) {
                            include $hook;
                        }
                        break;
                    }

                    httpredir(currentPage(array('action')));
                }
                $GLOBALS['smarty']->assign(
                    'URL',
                    array(
                        'subscribe' => $GLOBALS['storeURL'].'/index.php?_a=newsletter&action=subscribe',
                        'unsubscribe' => $GLOBALS['storeURL'].'/index.php?_a=newsletter&action=unsubscribe')
                );
                $where = array('email' => $GLOBALS['user']->get('email'));
                if ((bool)$GLOBALS['config']->get('config', 'dbl_opt')) {
                    $where['dbl_opt'] = '1';
                }
                $GLOBALS['smarty']->assign('SUBSCRIBED', (bool)$GLOBALS['db']->select('CubeCart_newsletter_subscriber', false, $where, false, 1, false, false));
            }
            // Show list of publicly visible newsletters
            if (($archive = $GLOBALS['db']->select('CubeCart_newsletter', false, array('status' => 1))) !== false) {
                foreach ($archive as $content) {
                    $content['view'] = currentPage(array('subscribed','action'), array('newsletter_id' => $content['newsletter_id']));
                    $content['date_sent'] = formatTime(strtotime($content['date_sent']));
                    $vars['newsletters'][] = $content;
                }
                $GLOBALS['smarty']->assign('NEWSLETTERS', $vars['newsletters']);
            }
        }
        
        if ($_GET['_a'] == 'unsubscribe') {
            $form_id 	= 'newsletter_form_unsubscribe';
            $mode 		= 'unsubscribe';
        } else {
            $form_id 	= 'newsletter_form';
            $mode 		= 'subscribe';
        }
        $GLOBALS['smarty']->assign('FORM_ID', $form_id);
        $GLOBALS['smarty']->assign('SUBSCRIBE_MODE', $mode);
        $GLOBALS['smarty']->assign('DISABLE_BOX_NEWSLETTER', true);
        $GLOBALS['smarty']->assign('SECTION_NAME', 'account');
        $content = $GLOBALS['smarty']->fetch('templates/content.newsletter.php');
        $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
    }

    /**
     * Orders
     */
    private function _orders()
    {
        // Order history
        $template = 'templates/content.orders.php';
        if ($GLOBALS['user']->is()) {
            $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->account['your_account'], 'index.php?_a=account');
            $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->account['your_orders'], currentPage(array('cart_order_id'), null, false));
            if (isset($_GET['cart_order_id']) && Order::validOrderId(trim($_GET['cart_order_id']), true)) {
                if (($orders = $GLOBALS['db']->select('CubeCart_order_summary', false, array('customer_id' => $GLOBALS['user']->get('customer_id'), 'cart_order_id' => $_GET['cart_order_id']), false, 1, false, false)) !== false) {
                    $template = 'templates/content.receipt.php';
                    $order = $orders[0];
                    $GLOBALS['gui']->addBreadcrumb(($GLOBALS['config']->get('config', 'oid_mode') == 'i' && !empty($order[$GLOBALS['config']->get('config', 'oid_col')])) ? $order[$GLOBALS['config']->get('config', 'oid_col')] : $order['cart_order_id'], currentPage());
                    if (($items = $GLOBALS['db']->select('CubeCart_order_inventory', false, array('cart_order_id' => $order['cart_order_id']))) !== false) {
                        foreach ($items as $item) {
                            // Do price formatting
                            $item['price_total'] = $GLOBALS['tax']->priceFormat(($item['price'] * $item['quantity']), true);
                            $item['price'] = $GLOBALS['tax']->priceFormat($item['price']);
                            $options = Order::getInstance()->unSerializeOptions($item['product_options']);
                            $item['options'] = implode(' ', $options);
                            $vars['items'][] = $item;
                        }
                        $GLOBALS['smarty']->assign('ITEMS', $vars['items']);
                    }
                    // Taxes
                    if (($taxes = $GLOBALS['db']->select('CubeCart_order_tax', false, array('cart_order_id' => $order['cart_order_id']))) !== false) {
                        $GLOBALS['tax']->loadTaxes(($GLOBALS['config']->get('config', 'basket_tax_by_delivery')) ? $order['country'] : $order['country_d']);
                        foreach ($taxes as $vat) {
                            $detail = $GLOBALS['tax']->fetchTaxDetails($vat['tax_id']);
                            $vars['taxes'][] = array('name' => $detail['name'], 'value' => $GLOBALS['tax']->priceFormat($vat['amount'], true));
                        }
                    } else {
                        $vars['taxes'][] = array('name' => $GLOBALS['language']->basket['total_tax'], 'value' => $GLOBALS['tax']->priceFormat($order['total_tax']));
                    }
                    $GLOBALS['smarty']->assign('TAXES', $vars['taxes']);
                    $order['state']  = getStateFormat($order['state']);
                    $order['country'] = getCountryFormat($order['country']);
                    $order['state_d'] = is_numeric($order['state_d']) ? getStateFormat($order['state_d']) : $order['state_d'];
                    $order['country_d'] = getCountryFormat($order['country_d']);

                    if ($order['discount']>0) {
                        $GLOBALS['smarty']->assign('DISCOUNT', true);
                    }

                    // Loop through price values, and do the formatting
                    foreach (array('discount', 'shipping', 'subtotal', 'total', 'total_tax') as $key) {
                        $order[$key] = $GLOBALS['tax']->priceFormat($order[$key], true);
                    }
                    $order['order_status'] = $GLOBALS['language']->order_state['name_'.$order['status']];
                    $order['order_date_formatted'] = formatTime($order['order_date'], false, true);

                    foreach ($GLOBALS['hooks']->load('class.cubecart.order_summary') as $hook) {
                        include $hook;
                    }
                    $order['basket'] = unserialize($order['basket']);
                    $GLOBALS['smarty']->assign('SUM', $order);
                    $GLOBALS['smarty']->assign('ORDER', $order);
                    $GLOBALS['session']->delete('ghost_customer_id');


                    if (isset($order['ship_method']) && !empty($order['ship_method'])) {
                        $method = str_replace(' ', '_', $order['ship_method']);
                        $ship_class = CC_ROOT_DIR.'/modules/shipping/'.$method.'/'.'shipping.class.php';
                        $ship_class_exists = file_exists($ship_class);
                    } else {
                        $ship_class_exists = false;
                    }
                    
                    if ($ship_class_exists) {
                        include $ship_class;
                        if (class_exists($method) && method_exists((string)$method, 'tracking')) {
                            $shipping = new $method(false);
                            $url = $shipping->tracking($order['ship_tracking']);
                            
                            $url = (empty($url) && filter_var($order['ship_tracking'], FILTER_VALIDATE_URL)) ? $order['ship_tracking'] : $url;

                            $delivery = array(
                                'url'  => $url,
                                'method' => $order['ship_method'],
                                'product' => $order['ship_product'],
                                'tracking' => $order['ship_tracking'],
                                'date'  => (!empty($order['ship_date']) && $order['ship_date']!=='0000-00-00') ? formatDispatchDate($order['ship_date']) : ''
                            );
                        }
                        unset($ship_class);
                    } else {
                        $delivery = array(
                            'url' => filter_var($order['ship_tracking'], FILTER_VALIDATE_URL) ? $order['ship_tracking'] : '',
                            'method' => $order['ship_method'],
                            'product' => $order['ship_product'],
                            'tracking' => $order['ship_tracking'],
                            'date'  => (!empty($order['ship_date']) && $order['ship_date']!=='0000-00-00') ? formatDispatchDate($order['ship_date']) : ''
                        );
                    }
                    $GLOBALS['smarty']->assign('DELIVERY', $delivery);
                } else {
                    httpredir(currentPage(array('cart_order_id')));
                }
            } else {
                if (isset($_GET['cancel']) && Order::validOrderId(trim($_GET['cancel']))) {
                    $order = Order::getInstance();
                    if ($order->orderStatus(Order::ORDER_CANCELLED, $_GET['cancel'])) {
                        // Specify order was cancelled by customer
                        $note	= array(
                            'admin_id'		=> 0,
                            'cart_order_id'	=> $_GET['cancel'],
                            'content'		=> $GLOBALS['language']->orders['cancel_by_customer'],
                        );
                        $GLOBALS['db']->insert('CubeCart_order_notes', $note);
                        $GLOBALS['gui']->setError($GLOBALS['language']->orders['notify_order_cancelled']);
                    }
                    httpredir(currentPage(array('cancel')));
                } elseif (isset($_GET['reorder']) && Order::validOrderId(trim($_GET['reorder']))) {
                    $basket = $GLOBALS['db']->select('CubeCart_order_summary', array('basket'), array('cart_order_id'=>$_GET['reorder'], 'customer_id' => $GLOBALS['user']->get('customer_id')));
                    $past_data = unserialize($basket[0]['basket']);
                    $GLOBALS['cart']->basket['contents'] = $past_data['contents'];
                    $GLOBALS['cart']->save();
                    httpredir('?_a=basket');
                }
                $per_page = 15;
                $page = (isset($_GET['page'])) ? $_GET['page'] : 1;

                if (($paginated_orders = $GLOBALS['db']->select('CubeCart_order_summary', array('custom_oid', 'id', 'cart_order_id', 'ship_tracking', 'order_date', 'status', 'total', 'basket'), array('customer_id' => $GLOBALS['user']->get('customer_id')), array('cart_order_id' => 'DESC'), $per_page, $page, false)) !== false) {
                    $order_count = $GLOBALS['db']->getFoundRows();
                    foreach ($paginated_orders as $i => $order) {
                        $order['time'] = formatTime($order['order_date']);
                        $status = $order['status'];

                        switch ((int)$order['status']) {
                        case 1:  # Pending
                            $icon = 'basket.png';
                            break;
                        case 2:  # Processing
                            $icon = 'clock.png';
                            break;
                        case 3:  # Complete & dispatched
                            $icon = 'lorry.png';
                            break;
                        case 4:  # Declined
                        case 5:  # Fraud
                            $icon = 'error.png';
                            break;
                        case 6:  # Cancelled
                            $icon = 'bin.png';
                            break;
                        }

                        $order['total'] = $GLOBALS['tax']->priceFormat($order['total'], true);
                        $existing_transactions = $GLOBALS['db']->select('CubeCart_transactions', array('id'), array('order_id' => $order['cart_order_id']));
                        $order['make_payment'] = ($order['status'] == 1 && !empty($order['basket']) && !$existing_transactions) ? true : false;
                        $order['cancel'] = ($order['status']==1 && !$existing_transactions) ? true : false;
                        $order['status'] = array('icon' => $icon, 'text' => $GLOBALS['language']->order_state['name_'.(int)$order['status']]);
                        $vars['orders'][] = $order;
                    }
                    foreach ($GLOBALS['hooks']->load('class.cubecart.order_list') as $hook) {
                        include $hook;
                    }
                    $GLOBALS['smarty']->assign('ORDERS', $vars['orders']);

                    $GLOBALS['smarty']->assign('PAGINATION', $GLOBALS['db']->pagination($order_count, $per_page, $page));
                }
            }
        } else {

            // Order lookup for unregistered users
            if (isset($_REQUEST['cart_order_id']) && isset($_REQUEST['email']) && filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL) && Order::validOrderId(trim($_REQUEST['cart_order_id']))) {
                $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->orders['my_order'], currentPage());
                $oid_field = $GLOBALS['config']->get('config', 'oid_col');
                if (($orders = $GLOBALS['db']->select('CubeCart_order_summary', false, array('email' => $_REQUEST['email'], $oid_field => $_REQUEST['cart_order_id']))) !== false) {
                    $template = 'templates/content.receipt.php';
                    $order = $orders[0];
                    $GLOBALS['user']->setGhostId($order['customer_id']);

                    if (($items = $GLOBALS['db']->select('CubeCart_order_inventory', false, array('cart_order_id' => $order['cart_order_id']))) !== false) {
                        foreach ($items as $item) {
                            // Do price formatting
                            $item['price_total'] = $GLOBALS['tax']->priceFormat(($item['price'] * $item['quantity']), true);
                            $item['price'] = $GLOBALS['tax']->priceFormat($item['price']);
                            $item['options'] = Order::getInstance()->unSerializeOptions($item['product_options']);
                            $vars['items'][] = $item;
                        }
                        $GLOBALS['smarty']->assign('ITEMS', $vars['items']);
                    }
                    if (($taxes = $GLOBALS['db']->select('CubeCart_order_tax', false, array('cart_order_id' => $order['cart_order_id']))) !== false) {
                        $GLOBALS['tax']->loadTaxes(($GLOBALS['config']->get('config', 'basket_tax_by_delivery')) ? $order['country'] : $order['country_d']);
                        foreach ($taxes as $vat) {
                            $detail = $GLOBALS['tax']->fetchTaxDetails($vat['tax_id']);
                            $vars['taxes'][] = array('name' => $detail['name'], 'value' => $GLOBALS['tax']->priceFormat($vat['amount'], true));
                        }
                    } else {
                        $vars['taxes'][] = array('name' => $GLOBALS['language']->basket['total_tax'], 'value' => $GLOBALS['tax']->priceFormat($order['total_tax']));
                    }
                    $GLOBALS['smarty']->assign('TAXES', $vars['taxes']);
                    $order['country'] = getCountryFormat($order['country']);
                    $order['country_d'] = getCountryFormat($order['country_d']);
                    $order['state'] = is_numeric($order['state']) ? getStateFormat($order['state']) : $order['state'];
                    $order['state_d'] = is_numeric($order['state_d']) ? getStateFormat($order['state_d']) : $order['state_d'];
                    // Loop through price values, and do the formatting
                    foreach (array('discount', 'shipping', 'subtotal', 'total', 'total_tax') as $key) {
                        $order[$key] = $GLOBALS['tax']->priceFormat($order[$key], true);
                    }
                    $order['order_status'] = $GLOBALS['language']->order_state['name_'.$order['status']];
                    $order['order_date_formatted'] = formatTime($order['order_date'], false, true);

                    foreach ($GLOBALS['hooks']->load('class.cubecart.order_summary') as $hook) {
                        include $hook;
                    }
                    $order['basket'] = unserialize($order['basket']);
                    $GLOBALS['smarty']->assign('SUM', $order);
                    $GLOBALS['smarty']->assign('ORDER', $order);
                } else {
                    $GLOBALS['gui']->setError($GLOBALS['language']->orders['error_search_result']);
                }
            } else {
                if (isset($_REQUEST['cart_order_id']) && isset($_REQUEST['email'])) {
                    $GLOBALS['gui']->setError($GLOBALS['language']->orders['error_search_result']);
                }

                // Display a search page
                $cart_order_id = Order::validOrderId(trim($_GET['cart_order_id'])) ? trim($_GET['cart_order_id']) : '';
                $GLOBALS['smarty']->assign('ORDER_NUMBER', $cart_order_id);
                $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->orders['search'], currentPage());
            }
        }

        $content = $GLOBALS['smarty']->fetch($template);
        $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
    }

    /**
     * Products
     */
    private function _product()
    {
        if (($product = $GLOBALS['catalogue']->getProductData($_GET['product_id'])) === false) {
            return;
        }
        if ($GLOBALS['config']->get('config', 'enable_reviews') && isset($_POST['review']) && is_array($_POST['review'])) {
            $error = false;

            foreach ($GLOBALS['hooks']->load('class.cubecart.review') as $hook) {
                include $hook;
            }
            
            $record = array_map('htmlspecialchars', $_POST['review']);
            if ($GLOBALS['user']->is()) {
                $record['name']   = $GLOBALS['user']->get('first_name').' '.$GLOBALS['user']->get('last_name');
                $record['email']  = $GLOBALS['user']->get('email');
                $record['customer_id'] = $GLOBALS['user']->get('customer_id');
                $record['anon']   = (isset($record['anon'])) ? 1 : 0;
            } else {
                $record['customer_id'] = 0;
                $record['email']  = $_POST['review']['email'];
                $record['anon']   = 0;
                if (!$GLOBALS['session']->isEmpty('error', 'recaptcha')) {
                    $GLOBALS['gui']->setError($GLOBALS['session']->get('error', 'recaptcha'));
                    $error = true;
                }
            }
            $record['rating']   = (isset($_POST['rating'])) ? $_POST['rating'] : 0;
            $record['product_id']  = (int)$_GET['product_id'];
            $record['ip_address']  = get_ip_address();
            $record['time']    = time();

            // Validate array
            $required = array('email', 'name', 'review', 'title');
            foreach ($required as $req) {
                if (!isset($record[$req]) || empty($record[$req])) {
                    $GLOBALS['gui']->setError($GLOBALS['language']->common['error_fields_required']);
                    $error = true;
                    break;
                }
            }
            if ($record['rating']==0) {
                $GLOBALS['gui']->setError($GLOBALS['language']->catalogue['error_rating_required']);
                $error = true;
            }
            if (!filter_var($record['email'], FILTER_VALIDATE_EMAIL)) {
                $GLOBALS['gui']->setError($GLOBALS['language']->common['error_email_invalid']);
                $error = true;
            }

            if (!$error) {
                if (($review_id = $GLOBALS['db']->insert('CubeCart_reviews', $record)) !== false) {
                    foreach ($GLOBALS['hooks']->load('class.cubecart.review.insert') as $hook) {
                        include $hook;
                    }
                    
                    $GLOBALS['gui']->setNotify($GLOBALS['language']->catalogue['notify_review_submit']);
                    $mail     = new Mailer();
                    $record['link']   = $GLOBALS['storeURL'].'/'.$GLOBALS['config']->get('config', 'adminFile').'?_g=products&node=reviews&edit='.$review_id;
                    $record['product_name'] = $product['name'];
                    $content    = $mail->loadContent('admin.review_added', $GLOBALS['language']->current(), $record);
                    if (!empty($content)) {
                        $mail->sendEmail($GLOBALS['config']->get('config', 'email_address'), $content);
                    }
                } else {
                    $GLOBALS['gui']->setError($GLOBALS['language']->catalogue['error_review_submit']);
                }
                httpredir(currentPage(null));
            } else {
                foreach ($_POST['review'] as $key => $value) {
                    $_POST['review'][$key] = htmlspecialchars($value);
                }
                $GLOBALS['smarty']->assign('WRITE', $_POST['review']);
            }
        }

        /* Social Bookmarks */
        $GLOBALS['smarty']->assign('SHARE', $this->_getSocial('product', 'getButtonHTML'));
        /* Social Comments */
        $GLOBALS['smarty']->assign('COMMENTS', $this->_getSocial('product', 'getCommunityHTML'));

        $GLOBALS['catalogue']->displayProduct((int)$_GET['product_id'], !$GLOBALS['user']->isBot());
    }

    /**
     * Profile
     */
    private function _profile()
    {
        $GLOBALS['user']->is(true);

        if ($GLOBALS['session']->get('temp_profile_required')) {
            $GLOBALS['gui']->setError($GLOBALS['language']->account['error_profile_incomplete']);
            $GLOBALS['session']->delete('temp_profile_required');
        }

        $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->account['your_account'], 'index.php?_a=account');
        $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->account['your_details'], currentPage());
        $updated = false;
        $change_pass = false;

        if (isset($_POST['passold']) && isset($_POST['passnew']) && isset($_POST['passconf'])) {
            $change_pass = true;
            foreach (array($_POST['passold'], $_POST['passnew'], $_POST['passconf']) as $pass_value) {
                if (empty($pass_value)) {
                    $change_pass = false;
                }
            }
            if ($change_pass) {
                if ($change_pass = $GLOBALS['user']->changePassword()) {
                    $GLOBALS['gui']->setNotify($GLOBALS['language']->account['password_updated']);
                }
            }
        }

        if (isset($_POST['update'])) {
            if ($updated = $GLOBALS['user']->update()) {
                $GLOBALS['gui']->setNotify($GLOBALS['language']->account['notify_details_updated']);
            } elseif (!$change_pass) {
                $GLOBALS['gui']->setError($GLOBALS['language']->account['error_details_updated']);
            }
        }
            
        if ($updated || $change_pass || isset($_POST['update'])) {
            httpredir('?_a=profile');
        }

        $customer_data = $GLOBALS['user']->get();
        $GLOBALS['smarty']->assign('USER', $customer_data);
        if (!empty($customer_data['password'])) {
            $GLOBALS['smarty']->assign('ACCOUNT_EXISTS', true);
        }
        foreach ($GLOBALS['hooks']->load('class.cubecart.profile') as $hook) {
            include $hook;
        }
        $content = $GLOBALS['smarty']->fetch('templates/content.profile.php');
        $GLOBALS['smarty']->assign('SECTION_NAME', 'account');
        $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
    }

    /**
     * Receipt
     */
    private function _receipt()
    {
        if (isset($_GET['cart_order_id']) && ($GLOBALS['user']->is() || isset($_GET['email']))) {
            $customer_id = $GLOBALS['user']->getId();
            if (!$customer_id) {
                $customer_id = $GLOBALS['user']->getGhostId();
            }
            if (isset($_GET['email']) && filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
                $where =  array(
                    'cart_order_id' => $_GET['cart_order_id'],
                    'email' => $_GET['email'],
                );
            } else {
                $where =  array(
                    'cart_order_id' => $_GET['cart_order_id'],
                    'customer_id' => $customer_id,
                );
            }
            if (($summaries = $GLOBALS['db']->select('CubeCart_order_summary', false, $where)) !== false) {
                $summary = $summaries[0];
                if (($products = $GLOBALS['db']->select('CubeCart_order_inventory', false, array('cart_order_id' => $_GET['cart_order_id']))) !== false) {
                    foreach ($products as $item) {
                        $item['price_total'] = $GLOBALS['tax']->priceFormat(sprintf('%.2F', $item['price'] * $item['quantity']), true);
                        $item['price'] = $GLOBALS['tax']->priceFormat($item['price'], true);
                        $options = Order::getInstance()->unSerializeOptions($item['product_options']);
                        $item['options'] = implode(' ', $options);
                        $summary['items'][] = $item;
                    }
                }
                // Price Formatting
                $format = array('discount', 'shipping', 'subtotal', 'total_tax', 'total');
                foreach ($format as $field) {
                    if (isset($summary[$field])) {
                        $summary[$field] = $GLOBALS['tax']->priceFormat($summary[$field]);
                    }
                }
                // Taxes
                if (($taxes = $GLOBALS['db']->select('CubeCart_order_tax', false, array('cart_order_id' => $summary['cart_order_id']))) !== false) {
                    $GLOBALS['tax']->loadTaxes($summary['country']);
                    foreach ($taxes as $vat) {
                        $detail = $GLOBALS['tax']->fetchTaxDetails($vat['tax_id']);
                        $summary['taxes'][] = array('name' => $detail['name'], 'value' => $GLOBALS['tax']->priceFormat($vat['amount'], true));
                    }
                } else {
                    $summary['taxes'][] = array('name' => $GLOBALS['language']->basket['total_tax'], 'value' => $GLOBALS['tax']->priceFormat($summary['total_tax']));
                }
                // Delivery Address
                $summary['state']  = getStateFormat($summary['state']);
                $summary['country']  = getCountryFormat($summary['country']);
                $summary['state_d']  = is_numeric($summary['state_d']) ? getStateFormat($summary['state_d']) : $summary['state_d'];
                $summary['country_d'] = getCountryFormat($summary['country_d']);
                $summary['order_status'] = $GLOBALS['language']->order_state['name_'.$summary['status']];
                $summary['vat_number'] = $GLOBALS['config']->get('config', 'tax_number');

                $summary['order_date']  = formatTime($summary['order_date'], '%d %B %Y', true);
                $var[] = $summary;
                $GLOBALS['smarty']->assign('LIST_ORDERS', $var);

                $GLOBALS['smarty']->assign('PAGE_TITLE', sprintf($GLOBALS['language']->orders['title_invoice_x'], $summary['cart_order_id']));
                $GLOBALS['smarty']->assign(
                    'STORE',
                    array(
                        'address' => $GLOBALS['config']->get('config', 'store_address'),
                        'county' => getStateFormat($GLOBALS['config']->get('config', 'store_zone')),
                        'country' => getCountryFormat($GLOBALS['config']->get('config', 'store_country')),
                        'postcode' => $GLOBALS['config']->get('config', 'store_postcode'))
                );

                $GLOBALS['smarty']->assign('STORE_LOGO', $GLOBALS['gui']->getLogo(true, 'invoices'));
                foreach ($GLOBALS['hooks']->load('class.cubecart.print.receipt') as $hook) {
                    include $hook;
                }
                $GLOBALS['smarty']->display('templates/print.receipt.php');
            }
            $GLOBALS['debug']->supress();
            exit;
        } else {
            httpredir('?_a=login');
        }
    }

    /**
     * Recover
     */
    private function _recover()
    {
        $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->account['recover_password'], currentPage());

        $GLOBALS['smarty']->assign('SECTION_NAME', 'recover');

        if (isset($_POST['email'])) {
            // Send a recovery email
            if ($GLOBALS['user']->passwordRequest($_POST['email'])) {
                $GLOBALS['gui']->setNotify($GLOBALS['language']->account['notify_password_recovery']);
                // Send them shopping whilst they wait for their email!
                httpredir(currentPage(array('_a')));
            } else {
                $GLOBALS['gui']->setError($GLOBALS['language']->account['error_password_recovery']);
            }
            // Reload the same page so they can try again
            httpredir(currentPage());
        }
        $content = $GLOBALS['smarty']->fetch('templates/content.recover.php');
        $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
    }

    /**
     * Recovery
     */
    private function _recovery()
    {
        $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->account['recover_password'], currentPage());

        $GLOBALS['smarty']->assign('SECTION_NAME', 'recovery');

        if (isset($_POST['email']) && isset($_POST['validate']) && isset($_POST['password'])) {
            $GLOBALS['user']->passwordReset((string)$_POST['email'], (string)$_POST['validate'], $_POST['password']);
        }
        $email  = (isset($_GET['email'])) ? (string)$_GET['email'] : null;
        $validate = (isset($_GET['validate'])) ? (string)$_GET['validate'] : null;
        $GLOBALS['smarty']->assign('DATA', array(
                'email'  => (isset($_POST['email'])) ? (string)$_POST['email'] : $email,
                'validate' => (isset($_POST['validate'])) ? (string)$_POST['validate'] : $validate,
            ));
        $content = $GLOBALS['smarty']->fetch('templates/content.recovery.php');
        $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
    }

    /**
     * Register
     */
    private function _register()
    {
        $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->account['register'], $GLOBALS['seo']->buildURL('register'));

        foreach ($GLOBALS['hooks']->load('class.cubecart.construct.register') as $hook) {
            include $hook;
        }

        $login_html = array();
        foreach ($GLOBALS['hooks']->load('class.cubecart.login') as $hook) {
            include $hook;
        }
        $GLOBALS['smarty']->assign('LOGIN_HTML', $login_html);

        if (isset($_POST['register']) && !empty($_POST['register'])) {
            $GLOBALS['user']->registerUser();
        }

        if (!$GLOBALS['user']->is()) {
            $GLOBALS['smarty']->assign('DATA', $_POST);
            if (($terms = $GLOBALS['db']->select('CubeCart_documents', false, array('doc_terms' => '1'))) !== false) {
                $GLOBALS['smarty']->assign('TERMS_CONDITIONS', $GLOBALS['seo']->buildURL('doc', $terms[0]['doc_id'], '&'));
            } else {
                $GLOBALS['smarty']->assign('TERMS_CONDITIONS', false);
            }

            $content = $GLOBALS['smarty']->fetch('templates/content.register.php');
            $GLOBALS['smarty']->assign('SECTION_NAME', 'register');
            $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
        } else {
            // Already logged in, just redirect
            switch (true) {
            case (isset($_GET['redir']) && !empty($_GET['redir'])):
                $redir = $_GET['redir'];
                break;
            case (isset($_POST['redir']) && !empty($_POST['redir'])):
                $redir = $_POST['redir'];
                break;
            default:
                $redir = '?_a=account';
            }
            httpredir($redir);
        }
    }

    /**
     * Search
     */
    private function _search()
    {
        foreach ($GLOBALS['hooks']->load('class.cubecart.construct.search') as $hook) {
            include $hook;
        }
        $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->common['search'], currentPage());

        $GLOBALS['smarty']->assign('SECTION_NAME', 'search');

        //Manufacturers
        if (($manufacturers = $GLOBALS['db']->select('CubeCart_manufacturers', false, false, array('name' => 'ASC'))) !== false) {
            $GLOBALS['smarty']->assign('MANUFACTURERS', $manufacturers);
        }
        // Sorting
        if (($sorting = $GLOBALS['catalogue']->displaySort(true)) !== false) {
            $GLOBALS['smarty']->assign('SORTERS', $sorting);
        }

        if ($GLOBALS['config']->get('config', 'hide_out_of_stock')) {
            $GLOBALS['smarty']->assign('OUT_OF_STOCK', 1);
        }

        $content = $GLOBALS['smarty']->fetch('templates/content.search.php');
        $GLOBALS['smarty']->assign('PAGE_CONTENT', $content);
    }
}
