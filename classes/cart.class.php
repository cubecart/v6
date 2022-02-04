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
 * Cart controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @author Sir William
 * @since 5.0.0
 */
class Cart
{
    /**
     * Current basket
     *
     * @var array
     */
    public $basket    = null;
    /**
     * Basket data
     *
     * @var array
     */
    public $basket_data   = false;
    /**
     * Digital basket
     *
     * @var bool
     */
    public $basket_digital  = false;

    /**
     * Cart discount
     *
     * @var float
     */
    private $_discount   = 0;
    /**
     * Cart item discount flag
     *
     * @var bool
     */
    private $_item_discount  = false;
    /**
     * Shipping cost
     *
     * @var float
     */
    private $_shipping   = 0;
    /**
     * Shipping discount
     *
     * @var float
     */
    private $_shipping_discount = 0;
    /**
     * Cart subtotal
     *
     * @var float
     */
    private $_subtotal   = 0;
    /**
     * Cart total
     *
     * @var float
     */
    private $_total    = 0;
    /**
     * Cart weight
     *
     * @var float
     */
    private $_weight   = 0;

    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;

    ##############################################

    final protected function __construct()
    {
        foreach ($GLOBALS['hooks']->load('class.cart.construct.pre') as $hook) {
            include $hook;
        }
        if ($GLOBALS['user']->is()) {
            if (($currency = $GLOBALS['user']->get('currency')) !== false) {
                if ($GLOBALS['config']->get('config', 'default_currency') != $currency) {
                    $GLOBALS['tax']->loadCurrencyVars($currency);
                }
            }
        }

        //If the user just logged in try to autoload the cart
        if ($GLOBALS['session']->get('check_autoload')) {
            $GLOBALS['session']->delete('check_autoload');
            $this->autoload();
        }

        $tax_on = ($GLOBALS['config']->get('config', 'basket_tax_by_delivery')) ? 'delivery_address' : 'billing_address';
        if (isset($this->basket[$tax_on])) {
            $tax_country = (int)$this->basket[$tax_on]['country_id'];
        } else {
            $tax_country = $GLOBALS['config']->get('config', 'store_country');
        }

        // Load Basket contents
        $this->load();

        if (!$GLOBALS['config']->get('config', 'basket_allow_non_invoice_address') && isset($_POST['delivery_address']) && is_numeric($_POST['delivery_address'])) {
            $this->basket['delivery_address'] = $GLOBALS['user']->getAddress((int)$_POST['delivery_address']);
        }

        if (isset($_POST['add'])) {
            // Check if productOptions SHOULD be present. i.e. add from category page
            if (!isset($_POST['productOptions'])) {
                if (is_array($_POST['add'])) {
                    foreach ($_POST['add'] as $key => $value) {
                        $required_options = $GLOBALS['catalogue']->getOptionRequired();
                        if ($GLOBALS['catalogue']->getProductOptions($key) && $required_options) {
                            if (is_array($required_options)) {
                                $_POST['productOptions'] = $required_options;
                            } else {
                                $GLOBALS['gui']->setError($GLOBALS['language']->catalogue['error_option_required']);
                                $this->redirectToProductPage($key);
                            }
                        }
                    }
                }
                if (is_int($_POST['add'])) {
                    $key = (int)$_POST['add'];
                    $required_options = $GLOBALS['catalogue']->getOptionRequired();
                    if ($GLOBALS['catalogue']->getProductOptions($key) && $required_options) {
                        if (is_array($required_options)) {
                            $_POST['productOptions'] = $required_options;
                        } else {
                            $GLOBALS['gui']->setError($GLOBALS['language']->catalogue['error_option_required']);
                            $this->redirectToProductPage($key);
                        }
                    }
                }
            }

            // Add item to basket
            if (is_array($_POST['add'])) {
                foreach ($_POST['add'] as $key => $value) {
                    // Multi-product adding from category page
                    if (is_numeric($value['quantity']) && $value['quantity'] > 1) {
                        $quantity = (int)$value['quantity'];
                    } else {
                        $quantity = 1;
                    }

                    $this->add((is_numeric($value)) ? $value : $key, null, $quantity);
                }
            } else {
                $this->add((int)$_POST['add'], isset($_POST['productOptions']) ? $_POST['productOptions'] : null, (int)$_POST['quantity']);
            }
        }

        if (isset($_GET['remove-item']) && !empty($_GET['remove-item'])) {
            // Remove item from basket
            $this->remove($_GET['remove-item']);
            httpredir(currentPage(array('remove-item')));
        }
    }

    public function __destruct()
    {
        $this->save();
    }

    /**
     * Setup the instance (singleton)
     *
     * @return Cart
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
     * Add item to the basket
     *
     * @param int $product_id
     * @param array $optionsArray
     * @param int $quantity
     * @return bool
     */
    public function add($product_id, $optionsArray = null, $quantity = 1, $redirect_enabled = true)
    {
        foreach ($GLOBALS['hooks']->load('class.cart.add.pre') as $hook) {
            include $hook;
        }
        // Prevent quantities of less than one or non numerical user input
        if (!is_numeric($quantity) || $quantity < 1 || $quantity > 999) {
            $quantity = 1;
        }

        // Don't allow products to be added to the basket if prices are hidden AND they're not logged in
        if ($GLOBALS['session']->get('hide_prices')) {
            if (isset($_GET['_g']) && $_GET['_g'] == 'ajaxadd') {
                $path = $GLOBALS['seo']->buildURL('prod', $_POST['add']);
                $GLOBALS['debug']->supress();
                die('Redir:'.$path);
            } else {
                httpredir(currentPage());
            }
        }
        // Handle gift certs
        $gc = $GLOBALS['config']->get('gift_certs');

        if (isset($gc['product_code']) && $product_id == $gc['product_code'] && !empty($optionsArray)) {
            $hash = md5(recursive_implode('{@}', $optionsArray));
            if (isset($this->basket['contents'][$hash])) {
                // Increment quantity
                $this->basket['contents'][$hash]['quantity'] += $quantity;

                $product = $GLOBALS['catalogue']->getProductData($this->basket['contents'][$hash]['id']);
                $this->basket['contents'][$hash]['total_price_each'] = ($product['price']+$this->basket['contents'][$hash]['option_line_price']);
            } else {
                // Add to basket
                $this->basket['contents'][$hash] = array(
                    'id'   => $product_id,
                    'quantity'  => $quantity,
                    'digital'  => ($optionsArray['method'] == 'e') ? true : false,
                    'certificate' => array(
                        'value'   => $optionsArray['value'],
                        'name'   => $optionsArray['name'],
                        'email'   => $optionsArray['email'],
                        'message'  => $optionsArray['message'],
                        'method' => $optionsArray['method']
                    ),
                );
            }
            $this->save();
            if ($redirect_enabled === true) {
                httpredir(($GLOBALS['config']->get('config', 'basket_jump_to')) ? $GLOBALS['rootRel'].'index.php?_a=basket' : currentPage(null));
                return true;
            }
        } elseif (!is_null($product_id) && is_numeric($product_id)) {
            $proceed = true;

            $options_identifier_string = $GLOBALS['catalogue']->defineOptionsIdentifier($optionsArray);

            $product = $GLOBALS['catalogue']->getProductData($product_id, 1, false, 10, 1, false, $options_identifier_string);

            if ($product) {
                foreach ($GLOBALS['hooks']->load('class.cart.add.check') as $hook) {
                    include $hook;
                }
                // Check for options
                $options = $GLOBALS['catalogue']->getProductOptions($product_id);

                $required_options = $GLOBALS['catalogue']->getOptionRequired();

                if ($required_options && ($options && empty($optionsArray))) {
                    // Options needed - Redirect to product page
                    // Set GUI_MESSAGE error, then redirect
                    if (is_array($required_options)) {
                        $this->add($product_id, $required_options, $quantity);
                    } else {
                        $GLOBALS['gui']->setError($GLOBALS['language']->catalogue['error_option_required']);
                        $this->redirectToProductPage($product_id);
                        return true;
                    }
                } else {

                    // Check required options have a value!
                    $quantity = (is_numeric($quantity) && $quantity > 0) ? $quantity : 1;
                    $stock_level = $GLOBALS['catalogue']->getProductStock($product['product_id'], $options_identifier_string, false, ((isset($this->basket['contents']) && is_array($this->basket['contents'])) ? $this->basket['contents'] : false), $quantity);

                    // Check stock level
                    if ($product['use_stock_level'] && !$GLOBALS['config']->get('config', 'basket_out_of_stock_purchase')) {
                        if ($stock_level <= 0) {
                            $max_stock = 0;
                        } else {
                            $max_stock = $stock_level;
                        }
                    }

                    foreach ($GLOBALS['hooks']->load('class.cart.add.max_stock') as $hook) {
                        include $hook;
                    }

                    if (isset($max_stock) && $max_stock <= 0) {
                        if (is_array($optionsArray)) {
                            $stock_note = $GLOBALS['session']->has('restock_note') ? $GLOBALS['session']->get('restock_note') : '';
                            $GLOBALS['session']->delete('restock_note');
                            $GLOBALS['gui']->setError($GLOBALS['language']->catalogue['error_no_stock_available_options'].' '.$stock_note);
                        } else {
                            $GLOBALS['gui']->setError($GLOBALS['language']->catalogue['error_no_stock_available']);
                        }

                        $this->redirectToProductPage($product_id);
                        return false;
                    }

                    $this->checkMinimumProductQuantity($product_id, $quantity, true);

                    // Add item to basket
                    $hash = md5($product['product_id'].((!empty($optionsArray)) ? $product['name'].recursive_implode('{@}', $optionsArray) : $product['name']));
                    if (isset($this->basket['contents'][$hash])) {
                        // Update quantity
                        if (isset($max_stock)) {
                            $current = $this->basket['contents'][$hash]['quantity'];
                            $request = $current + $quantity;
                            if ($request > $max_stock) {
                                $GLOBALS['gui']->setError($GLOBALS['language']->checkout['error_too_many_added']);
                                $quantity = $max_stock-$current;
                                $stock_warning = true;
                            }
                        }
                        $this->basket['contents'][$hash]['quantity'] += $quantity;
                    } else {
                        // Add to basket
                        if (isset($max_stock) && $quantity > $max_stock) {
                            $GLOBALS['gui']->setError($GLOBALS['language']->checkout['error_too_many_added']);
                            $quantity = $max_stock;
                            $stock_warning = true;
                        }
                        $this->basket['contents'][$hash] = array(
                                'id'  => $product_id,
                                'quantity' => $quantity,
                                'digital' => $product['digital'],
                            );
                        if ($options && !empty($optionsArray)) {
                            // Add options to the basket item

                            foreach ($optionsArray as $option_id => $option_value) {
                                $required = $GLOBALS['db']->select('CubeCart_option_group', array('option_type', 'option_required'), array('option_id' => (int)$option_id));
                                $require = ($required) ? (bool)$required[0]['option_required'] : false;
                                $add_option = true;
                                if (is_array($option_value)) {
                                    foreach (array_values($option_value) as $value) {
                                        if ($add_option && !$this->_checkOption($value, $require)) {
                                            $add_option = false;
                                            $proceed  = false;
                                        } elseif (empty($option_value)) {
                                            $add_option = false;
                                        } else {
                                            $imploded = implode('', $option_value);
                                            if (empty($imploded)) {
                                                $add_option = false;
                                            }
                                        }
                                    }
                                } else {
                                    if ($add_option && !$this->_checkOption($option_value, $require)) {
                                        $add_option = false;
                                        $proceed  = false;
                                    } elseif (empty($option_value) && !is_numeric($option_value)) {
                                        $add_option = false;
                                    }
                                }
                                if ($add_option) {
                                    $this->basket['contents'][$hash]['options'][$option_id] = $option_value;
                                } elseif (!$proceed) {
                                    // Product can't be added without required option
                                    unset($this->basket['contents'][$hash]);
                                    break;
                                }
                            }

                            $this->basket['contents'][$hash]['options_identifier'] = $options_identifier_string;

                            if (!$proceed) {
                                // No required options selected
                                if (isset($_GET['_g']) && $_GET['_g'] == 'ajaxadd') {
                                    $GLOBALS['gui']->setError($GLOBALS['language']->catalogue['error_option_required']);
                                    $this->redirectToProductPage($product_id);
                                } else {
                                    httpredir(currentPage(null, array('error' => 'option')));
                                }

                                return false;
                            }
                        }
                    }

                    foreach ($GLOBALS['hooks']->load('class.cart.add.save') as $hook) {
                        include $hook;
                    }

                    //Save before the jump
                    $this->save();

                    foreach ($GLOBALS['hooks']->load('class.cart.add.preredirect') as $hook) {
                        include $hook;
                    }

                    // Jump to basket, or return to product page?
                    $jumpto = ($GLOBALS['config']->get('config', 'basket_jump_to')) ? $GLOBALS['rootRel'].'index.php?_a=basket' : currentPage(null);
                    foreach ($GLOBALS['hooks']->load('class.cart.add.postredirect') as $hook) {
                        include $hook;
                    }
                    if (isset($_GET['_g']) && $_GET['_g'] == 'ajaxadd' && $GLOBALS['config']->get('config', 'basket_jump_to') && $redirect_enabled === true) {
                        $GLOBALS['debug']->supress();
                        die($GLOBALS['seo']->rewriteUrls("Redir:".$jumpto, true));
                    } elseif (isset($_GET['_g']) && $_GET['_g'] == 'ajaxadd' && $redirect_enabled === true) {
                        $GLOBALS['debug']->supress();
                        if ($stock_warning) {
                            die('Redir:'.$GLOBALS['rootRel'].'index.php?_a=basket');
                        }
                    } elseif ($redirect_enabled === true) {
                        httpredir($jumpto);
                    }

                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Autoload saved cart
     *
     * If the cart already has items in it then we will not autoload as we assume
     * they have what they want already
     */
    public function autoload()
    {
        if ($result = $GLOBALS['db']->select('CubeCart_saved_cart', array('basket'), array('customer_id' => $GLOBALS['user']->getId()), false, false, false, false)) {
            $basket = $GLOBALS['session']->get('', 'basket');
            if (empty($basket) || !isset($basket['contents'])) {
                $this->basket['contents'] = unserialize($result[0]['basket']);
                $this->save();
            }
        }
    }

    /**
     * Check - Minimum Quantity
     */
    public function checkMinimumProductQuantity($productID, $quantity, $redirect=true)
    {
        $data = $GLOBALS['catalogue']->getProductData($productID);
        $min_q = (int)$data['minimum_quantity'];
        $max_q = (int)$data['maximum_quantity'];

        if ($min_q && $min_q > $quantity) {
            $GLOBALS['gui']->setError(sprintf($GLOBALS['language']->catalogue['error_minimum_quantity'], $min_q));

            if ($redirect) {
                $this->redirectToProductPage($productID);
            }
        }
        if ($max_q && $max_q < $quantity) {
            $GLOBALS['gui']->setError(sprintf($GLOBALS['language']->catalogue['error_maximum_quantity'], $max_q));
        
            if ($redirect) {
                $this->redirectToProductPage($productID);
            }
        }

        return false;
    }

    /**
     * Check shipping method is allowed for country
     *
     * @return bool
     */
    public function checkShippingModuleCountry($countries, $zone)
    {
        $_country = $country_match = false;

        if (is_array($countries)) {
            foreach ($countries as $country) {
                if ($this->basket['delivery_address']['country_id'] == $country || $this->basket['delivery_address']['country'] == $country) {
                    $country_match = true;
                }
            }
            $_country = (($zone=='enabled' && !$country_match) || ($zone=='disabled' && $country_match)) ? true : false;
        }

        return $_country;
    }

    /**
     * Clear basket
     *
     * @return bool
     */
    public function clear()
    {
        $this->basket = null;
        $GLOBALS['session']->delete('', 'basket');

        $GLOBALS['db']->delete('CubeCart_saved_cart', array('customer_id' => $GLOBALS['user']->getId()));
        foreach ($GLOBALS['hooks']->load('class.cart.clear') as $hook) {
            include $hook;
        }
        return true;
    }

    /**
     * Add a discount to the cart
     *
     * @param int $code
     * @return bool
     */
    public function discountAdd($code)
    {
        if (!is_null($code) && !empty($code)) {
            if (($coupon = $GLOBALS['db']->select('CubeCart_coupons', '*', "`code` = '".preg_replace('/[^\w\-\_]/u', '', $code)."' AND `status` = 1", false, 1, false, false)) !== false) {
                if (!empty($coupon[0]['cart_order_id'])) {
                    $order = $GLOBALS['db']->select('CubeCart_order_summary', 'status', array('cart_order_id' => $coupon[0]['cart_order_id']));
                } else {
                    $order = false;
                }

                $coupon = $coupon[0];

                $customer_id = isset($this->basket['customer']['customer_id']) ? $this->basket['customer']['customer_id'] : $this->basket['billing_address']['customer_id'];
                $email = isset($this->basket['customer']['email']) ? $this->basket['customer']['email'] : $GLOBALS['user']->get('email');
                
                if($coupon['coupon_per_customer']>0) {
                    if (!empty($customer_id) || !empty($email)) {
                        $usage = $GLOBALS['db']->select('CubeCart_customer_coupon', array('used'), "`email` = '$email' OR `customer_id` =  ".(int)$customer_id, false, 1, false, false);
                        if($usage && $usage[0]['used']>= $coupon['coupon_per_customer']) {
                            // Coupon is no longer valid
                            $GLOBALS['gui']->setError($GLOBALS['language']->checkout['error_voucher_exceeded']);
                            return false;
                        }
                    }
                }
                if ($coupon['free_shipping_excluded']=='1' && floatval($this->basket['shipping']['value'])==0) {
                    // Minimum subtotal for voucher has not been met
                    $GLOBALS['gui']->setError($GLOBALS['language']->checkout['error_voucher_free_shipping']);
                    return false;
                }
                if ($coupon['expires']!=='0000-00-00' && (strtotime($coupon['expires']) < time())) {
                    // Coupon is no longer valid
                    $GLOBALS['gui']->setError($GLOBALS['language']->checkout['error_voucher_expired']);
                    return false;
                }
                if ($order && !in_array($order[0]['status'], array(2, 3))) {
                    // Check order is still valid!
                    $GLOBALS['gui']->setError($GLOBALS['language']->checkout['error_voucher_order_status']);
                    return false;
                }
                if ($coupon['allowed_uses'] > 0 && ($coupon['count'] >= $coupon['allowed_uses'])) {
                    // Coupon is no longer valid
                    $GLOBALS['gui']->setError($GLOBALS['language']->checkout['error_voucher_exceeded']);
                    return false;
                }
                if ((float)$coupon['min_subtotal'] > 0 && $this->basket['subtotal'] < (float)$coupon['min_subtotal']) {
                    // Minimum subtotal for voucher has not been met
                    $GLOBALS['gui']->setError($GLOBALS['language']->checkout['error_voucher_product']);
                    return false;
                }

                $proceed = false;

                foreach ($GLOBALS['hooks']->load('class.cart.discount_preadd') as $hook) {
                    include $hook;
                }

                $include = array();

                // Check manufacturer is allowed
                if (!empty($coupon['manufacturer_id'])) {
                    $qualifying_manufacturers = unserialize($coupon['manufacturer_id']);
                    if(count($qualifying_manufacturers)>0) {
                        $proceed = false;
                        $qualifying_manufacturers = array_flip($qualifying_manufacturers);
                        foreach ($this->basket['contents'] as $key => $data) {
                            $m_id = $GLOBALS['db']->select('CubeCart_inventory', 'manufacturer', array('product_id' => $data['id']));
                            if(isset($qualifying_manufacturers[$m_id[0]['manufacturer']])) {
                                $proceed = true;
                                $include[$data['id']] = true;
                            }
                        }
                        if(!$proceed) {
                            $GLOBALS['gui']->setError($GLOBALS['language']->checkout['error_voucher_manufacturer']);
                        }
                    }
                }

                if (!empty($coupon['product_id'])) {
                    $qualifying_products = unserialize($coupon['product_id']);

                    // pull the first item off as it's our orders to be inclusive or exclusive
                    $incexc = array_shift($qualifying_products);
                    // this will handle legacy coupons so we don't lose any products from them
                    if (is_numeric($incexc)) {
                        array_unshift($qualifying_products, $incexc);
                        $incexc = 'include';
                    }
                }

                if ($incexc!=='shipping_only' && is_array($qualifying_products) && count($qualifying_products)>0) {
                    foreach ($qualifying_products as $id) {
                        $product_ids[$id] = true;
                    }

                    if ($incexc == 'include') {
                        // If product IS in qualifying ids coupon is allowed
                        foreach ($this->basket['contents'] as $key => $data) {
                            if ($product_ids[$data['id']]) {
                                $include[$data['id']] = true;
                                $proceed = true;
                            }
                        }
                    } elseif ($incexc == 'exclude') {
                        foreach ($this->basket['contents'] as $key => $data) {
                            if (isset($qualifying_manufacturers) && $product_ids[$data['id']] && isset($include[$data['id']])) {
                                unset($include[$data['id']]);
                            } elseif($product_ids[$data['id']]) {
                                continue;
                            } else {
                                $include[$data['id']] = true;
                                $proceed = true;
                            }
                        }
                    }

                    if (!$proceed || count($include)==0) {
                        $GLOBALS['gui']->setError($GLOBALS['language']->checkout['error_voucher_wrong_product']);
                        return false;
                    }
                } else {
                    $proceed = true;
                }
                foreach ($GLOBALS['hooks']->load('class.cart.discount_add') as $hook) {
                    include $hook;
                }
                if ($proceed) {

                    // only allow multiple discount codes for gift certificates!
                    if (empty($coupon['cart_order_id'])) {
                        if (is_array($this->basket['coupons'])) {
                            foreach ($this->basket['coupons'] as $key => $item) {
                                if (!$item['gc']) {
                                    unset($this->basket['coupons'][$key]);
                                }
                            }
                        }
                    }

                    // Add a coupon to the array
                    $type = ($coupon['discount_percent'] > 0) ? 'percent' : 'fixed';
                    $value = ($coupon['discount_percent'] > 0) ? $coupon['discount_percent'] : $coupon['discount_price'];
                    if ($value>0 || (bool)$coupon['free_shipping']) {
                        $this->basket['coupons'][strtoupper($coupon['code'])] = array(
                            'voucher' => $coupon['code'],
                            'gc'  => (!empty($coupon['cart_order_id'])) ? true : false,
                            'type'  => $type,
                            'value'  => $value,
                            'available' => ($coupon['allowed_uses'] > 0) ? $coupon['allowed_uses']-$coupon['count'] : 0,
                            'include' => $include,
                            'shipping_only' => $incexc == 'shipping_only' ? true : false,
                            'shipping' => (bool)$coupon['shipping'],
                            'free_shipping' => (bool)$coupon['free_shipping']
                        );
                        if ((bool)$coupon['free_shipping']) {
                            // Unset shipping so that free shipping is selected
                            unset($this->basket['shipping'], $this->basket['default_shipping_set']);
                        }
                        $this->basket['free_coupon_shipping'] = (bool)$coupon['free_shipping'];
                        return true;
                    } else {
                        $GLOBALS['gui']->setError($GLOBALS['language']->checkout['error_voucher_expired']);
                        return false;
                    }
                }
            } else {
                $GLOBALS['gui']->setError($GLOBALS['language']->checkout['error_voucher_none']);
            }
        }

        return false;
    }

    /**
     * Remove discount from cart
     *
     * @param int $code
     * @return bool
     */
    public function discountRemove($code)
    {
        if ($code && isset($this->basket['coupons'][strtoupper($code)])) {
            unset($this->basket['coupons'][strtoupper($code)], $this->basket['discount_type']);
            if ($this->basket['free_coupon_shipping']==1) {
                unset($this->basket['shipping']);
            }
            unset($this->basket['free_coupon_shipping']);
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Get the current basket
     *
     * @return basket/false
     */
    public function get()
    {
        if ($GLOBALS['session']->get('hide_prices')) {
            return false;
        }

        if (!empty($this->basket['contents']) && is_array($this->basket['contents'])) {
            $this->_discount = $this->_subtotal = $this->_total_tax_add = $this->_weight = 0;
            // Include inline shipping maths for Per Category Shipping
            $ship_by_cat = $GLOBALS['config']->get('Per_Category');

            $sbc_path = CC_ROOT_DIR.'/modules/shipping/Per_Category/line.inc.php';
            if (file_exists($sbc_path) && isset($ship_by_cat['status']) && $ship_by_cat['status']) {
                require_once $sbc_path;
                $line_shipping = new Per_Category_Line($ship_by_cat, $this->basket);
            } else {
                $ship_by_cat = array('status' => false);
            }

            $tax_on = ($GLOBALS['config']->get('config', 'basket_tax_by_delivery')) ? 'delivery_address' : 'billing_address';
            $tax_country = 0;

            if (isset($this->basket[$tax_on])) {
                $tax_country = (int)$this->basket[$tax_on]['country_id'];
            }

            if (empty($tax_country)) {
                $tax_country = $GLOBALS['config']->get('config', 'store_country');
            }

            $GLOBALS['tax']->loadTaxes($tax_country);

            foreach ($this->basket['contents'] as $hash => $item) {
                if (empty($item) || !is_array($item)) {  ## Keep things tidy
                    unset($this->basket['contents'][$hash]);
                    continue;
                }
                // Basket Contents
                if (is_numeric($item['id'])) {
                    $item['options_identifier'] = isset($item['options_identifier']) ? $item['options_identifier'] : '';
                    $product = $GLOBALS['catalogue']->getProductData($item['id'], $item['quantity'], false, 10, 1, false, $item['options_identifier']);

                    foreach ($GLOBALS['hooks']->load('class.cart.get.product_prices') as $hook) {
                        include $hook;
                    }

                    if (!$product) {
                        // Warn that the product has been removed
                        if (!empty($item['name'])) {
                            $GLOBALS['gui']->setError(sprintf($GLOBALS['language']->checkout['error_item_not_available'], $item['name']));
                        }
                        unset($this->basket['contents'][$hash]);
                        continue;
                    }

                    $product['quantity'] = $item['quantity'];
                    if ($GLOBALS['tax']->salePrice($product['price'], $product['sale_price'])) {
                        $product['price'] = $product['sale_price'];
                    }
                    $product['price_display'] = $product['price'];
                    $product['base_price_display'] = $GLOBALS['tax']->priceFormat($product['price'], true);
                    $product['remove_options_tax'] = false;
                    if ($product['tax_inclusive']) {
                        // Remove tax from the items by default, everything internally should be sans-tax
                        $GLOBALS['tax']->inclusiveTaxRemove($product['price'], $product['tax_type']);
                        $product['tax_inclusive'] = false;
                        $product['remove_options_tax'] = true;
                    }
                    $product['option_line_price'] = $product['option_price_ignoring_tax'] = 0;
                    if (isset($item['options']) && is_array($item['options'])) {
                        foreach ($item['options'] as $option_id => $option_data) {
                            if (is_array($option_data)) {
                                // Text option
                                foreach ($option_data as $trash => $option_value) {
                                    if (($assign_id = $GLOBALS['db']->select('CubeCart_option_assign', false, array('product' => (int)$item['id'], 'option_id' => $option_id))) !== false) {
                                        $assign_id = $assign_id[0]['assign_id'];
                                    } else {
                                        $assign_id = 0;
                                    }
                                    $value = $GLOBALS['catalogue']->getOptionData((int)$option_id, $assign_id);
                                    foreach ($GLOBALS['hooks']->load('class.cart.get.product_option_prices') as $hook) {
                                        include $hook;
                                    }
                                    if ($value) {
                                        Cart::updateProductDataWithOption($product, $value);
                                        $value['value_name'] = $option_value;
                                        $product['options'][] = $value;
                                    }
                                }
                            } elseif (is_numeric($option_data)) {
                                // Select option
                                $value = $GLOBALS['catalogue']->getOptionData((int)$option_id, (int)$option_data);
                                foreach ($GLOBALS['hooks']->load('class.cart.get.product_option_prices') as $hook) {
                                    include $hook;
                                }
                                if ($value) {
                                    Cart::updateProductDataWithOption($product, $value);
                                    $product['options'][] = $value;
                                }
                            }
                        }
                    } else {
                        $product['options'] = false;
                    }

                    $this->basket['contents'][$hash]['digital'] = $product['digital'];

                    // Add the total product price inc options etc for payment gateways
                    $this->basket['contents'][$hash]['cost_price'] = round($product['cost_price']*$item['quantity'],2);
                    $this->basket['contents'][$hash]['option_line_price'] = $product['option_line_price'];
                    $this->basket['contents'][$hash]['total_price_each'] = $product['price'];
                    $this->basket['contents'][$hash]['description']   = substr(strip_tags($product['description']), 0, 255);
                    $this->basket['contents'][$hash]['name']     = $product['name'];
                    $this->basket['contents'][$hash]['product_code']   = $product['product_code'];
                    $this->basket['contents'][$hash]['product_weight']   = $product['product_weight'];
                    $this->basket['contents'][$hash]['product_width']   = $product['product_width'];
                    $this->basket['contents'][$hash]['product_height']   = $product['product_height'];
                    $this->basket['contents'][$hash]['product_depth']   = $product['product_depth'];
                } else {
                    if (!isset($item['certificate'])) {
                        continue;
                    }
                    $gc = $GLOBALS['config']->get('gift_certs');
                    if (isset($item['certificate']['method']) && !empty($item['certificate']['method'])) {
                        switch ($item['certificate']['method']) {
                            case 'm':
                                $method = $GLOBALS['language']->common['postal'];
                            break;
                            case 'e':
                                $method = $GLOBALS['language']->common['email'];
                            break;
                            default:
                                $method = '';
                        }
                    }
                    $product = array(
                        'quantity'  => $item['quantity'],
                        'product_code' => $gc['product_code'],
                        'price'   => $item['certificate']['value'],
                        'name'   => sprintf('%s %s (%s)', $method, $GLOBALS['language']->catalogue['gift_certificate'], $GLOBALS['tax']->priceFormat($item['certificate']['value'], true)),
                        'digital'  => (bool)$item['digital'],
                        'tax_type'  => $gc['taxType'],
                        'tax_inclusive' => 0,
                        'options'  => array(),
                        'option_price_ignoring_tax' => 0,
                    );
                    $product['price_display'] = $product['price'];
                }
                if ($product['digital']) {
                    $this->basket_digital = true;
                }

                if (!empty($product['absolute_price'])) {
                    $product['line_price_display'] = $product['option_price_ignoring_tax'];
                    $product['price_display']  = $product['option_price_ignoring_tax']*$item['quantity'];
                } else {
                    $product['line_price_display'] = $product['price_display']+$product['option_price_ignoring_tax'];
                    $product['price_display']  = ($product['price_display']+$product['option_price_ignoring_tax'])*$item['quantity'];
                }

                ## Update Subtotals
                if ($product['price']<0) {
                    $product['price'] = 0;
                }
                $product['line_price'] = $product['price'];
                $product['price']  = $product['price'] * $item['quantity'];

                $this->_subtotal  += $product['price'];
                $this->_weight   += $product['quantity'] * $product['product_weight'];

                $this->basket_data[$hash] = $product;

                // Calculate Taxes
                $tax_state_id = is_numeric($this->basket[$tax_on]['state_id']) ? $this->basket[$tax_on]['state_id'] : getStateFormat($this->basket[$tax_on]['state_id'], 'name', 'id');

                if (isset($tax_state_id)) {
                    $product_tax =  $GLOBALS['tax']->productTax($product['price'], (int)$product['tax_type'], (bool)$product['tax_inclusive'], $tax_state_id);
                } else {
                    $product_tax =  $GLOBALS['tax']->productTax($product['price'], (int)$product['tax_type'], (bool)$product['tax_inclusive']);
                }

                $this->basket['contents'][$hash]['tax_each'] = $product_tax;
                $this->basket['contents'][$hash]['option_absolute_price'] = (bool)$product['absolute_price'];

                // Calculate Line Shipping Price if enabled
                if (isset($ship_by_cat['status']) && $ship_by_cat['status']) {
                    $assigned_categories = $GLOBALS['catalogue']->getCategoryStatusByProductID($product['product_id']);
                    foreach ($assigned_categories as $assigned_category) {
                        if ($assigned_category['primary']) {
                            $assigned_category_id = $assigned_category['cat_id'];
                            continue;
                        }
                    }
                    $category = $GLOBALS['catalogue']->getCategoryData($assigned_category_id);
                    $line_shipping->lineCalc($product, $category);
                }
            }
            // Put By_Cat shipping prices into basket for calc class
            if (isset($ship_by_cat['status']) && $ship_by_cat['status']) {
                $this->basket['By_Category_Shipping'] =  $line_shipping->_lineShip + $line_shipping->_perShipPrice;
            }
            // Shipping
            $this->_shipping = (isset($this->basket['shipping']) && !empty($this->basket['shipping'])) ? $this->basket['shipping']['value']: 0;

            if (isset($this->basket[$tax_on]['state_id']) && isset($this->basket['shipping'])) {
                $GLOBALS['tax']->productTax($this->_shipping, $this->basket['shipping']['tax_id'], false, $this->basket[$tax_on]['state_id'], 'shipping');
            }

            // Apply Discounts
            $this->_applyDiscounts();

            $this->basket['weight']  = sprintf('%.4F', $this->_weight);
            $this->basket['discount'] = sprintf('%.2F', $this->_discount);
            $this->basket['subtotal'] = sprintf('%.2F', $this->_subtotal);
            $taxes = $GLOBALS['tax']->fetchTaxAmounts();
            foreach ($GLOBALS['hooks']->load('class.cart.get.fetchtaxes') as $hook) {
                include $hook;
            }
            $this->basket['total_tax'] = sprintf('%.2F', $taxes['applied']);

            $this->_total = (($this->_subtotal + $this->_shipping) + $this->basket['total_tax']);
            // if we are using per-product coupon, the prices are already reduced, so the total is fine
            if (!$this->_item_discount) {
                $this->_total -= $this->_discount;
            }

            if ($this->_total < 0) {
                $this->_total = 0;
            }
            if($this->_total == 0) {
                $GLOBALS['smarty']->assign('DISABLE_GATEWAYS', true);
            }
            $this->basket['total'] = sprintf('%.2F', $this->_total);

            foreach ($GLOBALS['hooks']->load('class.cart.get') as $hook) {
                include $hook;
            }

            $this->save();

            return $this->basket_data;
        }

        return false;
    }

    /**
     * Is it a digital basket
     *
     * @return bool
     */
    public function getBasketDigital()
    {
        return $this->basket_digital;
    }

    /**
     * Current subtotal
     *
     * @return float
     */
    public function getSubTotal()
    {
        return $this->_subtotal;
    }

    /**
     * Current total
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->_total;
    }

    /**
     * Current weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->_weight;
    }

    /**
     * Load current basket
     *
     * @return bool
     */
    public function load()
    {
        // Load previously saved basket
        if (($this->basket = $GLOBALS['session']->get('', 'basket')) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Load shipping modules
     *
     * @return array / false
     */
    public function loadShippingModules()
    {
        if (($shipping = $GLOBALS['db']->select('CubeCart_modules', array('folder', 'countries'), array('module' => 'shipping', 'status' => '1'), array('position' => 'asc'))) !== false) {
            $tax_on = ($GLOBALS['config']->get('config', 'basket_tax_by_delivery')) ? 'delivery_address' : 'billing_address';

            // Fetch the basket data
            $basket_data = ($this->basket) ? $this->basket : false;
            if (!isset($basket_data['delivery_address'])) {
                $basket_data['delivery_address'] = $GLOBALS['user']->formatAddress('', false);
                $this->basket['delivery_address'] = $basket_data['delivery_address'];
            }
            // Is this delivery address allowed?
            $country_status = $GLOBALS['db']->select('CubeCart_geo_country', array('status'), array('iso' => $basket_data['delivery_address']['country_iso']));
            $block = false;
            if(!$country_status || $country_status[0]['status']=='0') {
                $block = true;
            }
            if (!isset($basket_data['billing_address'])) {
                $basket_data['billing_address'] = $GLOBALS['user']->formatAddress('', false);
                $this->basket['billing_address'] = $basket_data['billing_address'];
            }
            foreach ($basket_data['contents'] as $hash => $item) {
                if ($item['digital']) {
                    unset($basket_data['contents'][$hash]);
                }
            }
            if (!empty($basket_data['contents']) && $block == false) {
                foreach ($shipping as $module) {
                    $module['countries'] = Config::getInstance()->get($module['folder'], 'countries');
                    $countries = (!empty($module['countries'])) ? unserialize($module['countries']) : false;

                    $module['disabled_countries'] = Config::getInstance()->get($module['folder'], 'disabled_countries');
                    $disabled_countries = (!empty($module['disabled_countries'])) ? unserialize($module['disabled_countries']) : false;

                    if ($this->checkShippingModuleCountry($countries, 'enabled') || $this->checkShippingModuleCountry($disabled_countries, 'disabled')) {
                        continue;
                    }

                    $class = CC_ROOT_DIR.'/modules/shipping/'.$module['folder'].'/shipping.class.php';
                    if (file_exists($class)) {
                        if (!class_exists($module['folder'])) {
                            include $class;
                        }

                        if (class_exists($module['folder']) && method_exists((string)$module['folder'], 'calculate')) {
                            $shippingClass[$module['folder']] = new $module['folder']($basket_data);
                            $packages = $shippingClass[$module['folder']]->calculate();
                            // $group_name will overwrite the folder name to make the shipping group on the dropdown configurable
                            $group_name = method_exists($shippingClass[$module['folder']], 'groupName') ? $shippingClass[$module['folder']]->groupName() : $module['folder'];
                            if ($packages) {
                                uasort($packages, 'price_sort');
                                // work out tax amount on shipping
                                foreach ($packages as $package) {
                                    $package['value'] = sprintf('%.2F', $package['value']);
                                    $packages_with_tax[] = array_merge($package, array('tax' => $GLOBALS['tax']->productTax($package['value'], $package['tax_id'], $package['tax_inclusive'], $this->basket[$tax_on]['state_id'], 'shipping', false)));
                                }

                                $shipArray[$group_name]	= $packages_with_tax;
                                unset($packages_with_tax);
                            }
                        }
                    } else {
                        // Version 4 Shipping Calculators
                        $calculator = CC_ROOT_DIR.'/modules/shipping/'.$module['folder'].'/calc.php';
                        if (file_exists($calculator)) {
                            include $calculator;
                        }
                    }
                }

                foreach ($GLOBALS['hooks']->load('class.cart.load_shipping') as $hook) {
                    include $hook;
                }

                if (isset($shipArray) && is_array($shipArray)) {
                    $this->save();
                    return $shipArray;
                } else {
                    // No shipping option is available due to Allowed/Disabled zones restriction
                    $this->save();
                    return false;
                }
            } else {
                // No shipping is required due to nothing tangible in cart to ship
                $this->save();
                return false;
            }
        } else {
            $GLOBALS['cart']->set('shipping', 0);
            $this->save();
            return false;
        }
    }

    /**
     * Remove an item from the basket
     *
     * @param int $identifier
     * @return bool
     */
    public function remove($identifier)
    {
        // Remove an item from the basket
        if (!is_null($identifier) && isset($this->basket['contents'][$identifier])) {
            unset($this->basket['contents'][$identifier]);
            $this->save();
            return $this->update();
        }
        return false;
    }

    /**
     * Save basket
     */
    public function save()
    {
        Session::getInstance()->set('', $this->basket, 'basket', true);
        //Only care about auto saving the cart if there is something in there
        if (!empty($this->basket) && isset($this->basket['contents'])) {
            if (User::getInstance()->is()) {
                static $old_basket = null;
                $id = User::getInstance()->getId();
                $basket = serialize($this->basket['contents']);
                if (empty($old_basket) || $old_basket != $basket) {
                    $old_basket = $basket;
                    if (Database::getInstance()->select('CubeCart_saved_cart', array('basket'), array('customer_id' => $id), false, false, false, false) !== false) {
                        Database::getInstance()->update('CubeCart_saved_cart', array('basket' => $basket), array('customer_id' => $id));
                    } else {
                        Database::getInstance()->insert('CubeCart_saved_cart', array('customer_id' => $id, 'basket' => $basket));
                    }
                }
            }
        }
    }

    /**
     * Set basket item
     *
     * @param mixed $identifier
     * @param mixed $value
     */
    public function set($identifier, $value)
    {
        $this->basket[$identifier] = $value;
        $this->save();
    }

    /**
     * Update basket
     */
    public function update($verify = array())
    {
        // Update basket values and such - possibly to the database too
        $quantities = isset($_POST['quan']) && is_array($_POST['quan']) ? $_POST['quan'] : $verify;
        if (count($quantities) > 0) {
            $this->_subtotal = 0;
            foreach ($quantities as $hash => $quantity) {

                // We can't update an item that doesn't exist or set imcomplete data
                if (!isset($this->basket['contents'][$hash]['id'])) {
                    continue;
                }

                if ($quantity <= 0) {
                    unset($this->basket['contents'][$hash]);
                } else {
                    $product = $GLOBALS['catalogue']->getProductData($this->basket['contents'][$hash]['id']);

                    $this->checkMinimumProductQuantity($product['product_id'], $quantity, false);

                    $stock_level = $GLOBALS['catalogue']->getProductStock($product['product_id'], $this->basket['contents'][$hash]['options_identifier']);
                    if ($product['use_stock_level'] && !$GLOBALS['config']->get('config', 'basket_out_of_stock_purchase')) {
                        if ($stock_level <= 0) {
                            $max_stock = 0;
                        } else {
                            $max_stock = $stock_level;
                        }
                        foreach ($GLOBALS['hooks']->load('class.cart.update.max_stock') as $hook) {
                            include $hook;
                        }
                        if ($quantity > $max_stock) {
                            if(count($verify)>0) $GLOBALS['gui']->setError($GLOBALS['language']->checkout['stock_availability_changed']);
                            if ($max_stock <=0) {
                                $GLOBALS['gui']->setError(sprintf($GLOBALS['language']->checkout['error_item_not_available'], $this->basket['contents'][$hash]['name']));
                                $this->remove($hash);
                                continue;
                            } else {
                                $GLOBALS['gui']->setError($GLOBALS['language']->checkout['error_too_many_added']);
                                $quantity = $max_stock;
                            }
                        }
                    }
                    $this->basket['contents'][$hash]['quantity'] = (int)$quantity; // or ceil($quantity);
                    $product_data['product_id'] = (int)$this->basket['contents'][$hash]['id'];
                    if($this->basket['contents'][$hash]['option_absolute_price']) {
                        $this->basket['contents'][$hash]['total_price_each'] = $this->basket['contents'][$hash]['option_line_price'];
                    } else {
                        $pprice = $product['ctrl_sale'] ? $product['sale_price'] : $product['price'];
                        $this->basket['contents'][$hash]['total_price_each'] = ($pprice+$this->basket['contents'][$hash]['option_line_price']);
                    }
                    $this->_subtotal += $this->basket['contents'][$hash]['total_price_each'] * $quantity;
                    $this->basket['subtotal'] = $this->_subtotal;
                }
            }
            foreach ($GLOBALS['hooks']->load('class.cart.update') as $hook) {
                include $hook;
            }
            $this->save();

            $this->_applyDiscounts();
        }

        //We need to check the coupons to make sure they are still valid
        if (isset($this->basket['coupons']) && is_array($this->basket['coupons'])) {
            foreach ($this->basket['coupons'] as $key => $data) {
                $this->discountRemove($key);
                $this->discountAdd($key);
            }
            $this->save();
        }

        //If the cart is empty
        if (isset($this->basket['contents']) && is_array($this->basket['contents']) && count($this->basket['contents']) == 0) {
            $this->clear();
        }
    }

    /**
     * Redirect to product page
     */
    public function redirectToProductPage($productID)
    {
        if (isset($_GET['_g']) && $_GET['_g'] == 'ajaxadd') {
            $GLOBALS['debug']->supress();
            die('Redir:'.$GLOBALS['seo']->buildURL('prod', $productID));
        } else {
            httpredir("index.php?_a=product&product_id=$productID");
        }
    }

    //=====[ Private ]=======================================

    /**
     * Apply a discount to the cart
     *
     * @return bool
     */
    private function _applyDiscounts()
    {
        foreach ($GLOBALS['hooks']->load('class.cart.applydiscounts.pre') as $hook) {
            include $hook;
        }

        if (isset($this->basket['coupons']) && count($this->basket['coupons'])>0) {
            $subtotal = $tax_total = 0;
            $coupon = false;

            // COUPONS FIRST!!
            foreach ($this->basket['coupons'] as $key => $data) {
                if (!$data['gc']) {
                    $coupon = true;

                    $all = (count($data['include']) == 0) ? true : false;

                    if(!$data['shipping_only']) {
                        foreach ($this->basket['contents'] as $hash => $item) {
                            if ($all || isset($data['include'][$item['id']])) {
                                if ($item['total_price_each']>0) {
                                    $subtotal += ($item['total_price_each'] * $item['quantity']);
                                }
                                if ($item['tax_each']['amount']>0) {
                                    $tax_total += $item['tax_each']['amount'];
                                }
                            } elseif ($item['total_price_each']>0) { // excluded items CAN be used against gift certificates!!
                                $excluded_products[$hash] = $item;
                            }
                        }
                    }

                    if ($data['shipping'] && $this->basket['shipping']['value']>0) {
                        $subtotal += $this->basket['shipping']['value'];
                        if ($this->basket['shipping']['tax']['amount']>0) {
                            $tax_total += $this->basket['shipping']['tax']['amount'];
                        }
                    } elseif ($this->basket['shipping']['value']>0) {
                        $excluded_shipping = $this->basket['shipping'];
                    }

                    $ave_tax_rate = ($tax_total / $subtotal);

                    $discount = ($data['type']=='percent') ? $subtotal*($data['value']/100) : $data['value'];

                    if ($discount<$subtotal) {
                        $subtotal -= $discount;
                        $this->_discount = $discount;
                        $this->basket['coupons'][$key]['value_display'] = sprintf('%.2F', $discount);
                    } elseif ($discount>=$subtotal) {
                        $this->_discount = $subtotal;
                        $this->basket['coupons'][$key]['value_display'] = sprintf('%.2F', $subtotal);
                        $subtotal = 0;
                        if ((!is_array($excluded_products) && !is_array($excluded_shipping))) {
                            $GLOBALS['tax']->adjustTax(0);
                            foreach ($this->basket['coupons'] as $key => $data) {
                                if ($data['gc']) {
                                    unset($this->basket['coupons'][$key]);
                                }
                            }
                            $this->save();
                            return true; // nothing else to check.. return
                        }
                    }
                }
            }

            if (!$coupon) {
                foreach ($this->basket['contents'] as $hash => $item) {
                    if ($item['total_price_each']>0) {
                        $subtotal += ($item['total_price_each'] * $item['quantity']);
                    }
                    if ($item['tax_each']['amount']>0) {
                        $tax_total += $item['tax_each']['amount'];
                    }
                }

                if ($this->basket['shipping']['value']>0) {
                    $subtotal += $this->basket['shipping']['value'];
                    if ($this->basket['shipping']['tax']['amount']>0) {
                        $tax_total += $this->basket['shipping']['tax']['amount'];
                    }
                }

                $ave_tax_rate = ($tax_total / $subtotal);
            } else {
                if ((is_array($excluded_products) || is_array($excluded_shipping))) {
                    $excluded_subtotal = $excluded_tax_total = 0;
                    if (is_array($excluded_products)) {
                        foreach ($excluded_products as $hash => $item) {
                            if ($item['total_price_each']>0) {
                                $excluded_subtotal += ($item['total_price_each'] * $item['quantity']);
                            }
                            if ($item['tax_each']['amount']>0) {
                                $excluded_tax_total += $item['tax_each']['amount'];
                            }
                        }
                    }
                    if (is_array($excluded_shipping) && $excluded_shipping['value']>0) {
                        $excluded_subtotal += $excluded_shipping['value'];
                        if ($excluded_shipping['tax']['amount']>0) {
                            $excluded_tax_total += $excluded_shipping['tax']['amount'];
                        }
                    }
                    if ($excluded_tax_total>0) {
                        $excluded_ave_tax_rate = ($excluded_tax_total / $excluded_subtotal);
                        $ave_tax_rate = ($ave_tax_rate + $excluded_ave_tax_rate) / 2;
                    }
                    $subtotal += $excluded_subtotal;
                }
            }

            // GIFT CERTS SECOND!!
            foreach ($this->basket['coupons'] as $key => $data) {
                if ($data['gc'] && $subtotal==0) {
                    // Gift cert not needed so remove
                    unset($this->basket['coupons'][$key]);
                } elseif ($data['gc'] && $subtotal>0) {
                    $discount	= $data['value'];

                    if ($discount<$subtotal) {
                        $subtotal -= $discount;
                        $this->_discount += $discount;
                        $this->basket['coupons'][$key]['value_display'] = sprintf('%.2F', $discount);
                        $remainder = 0;
                    } elseif ($discount>=$subtotal) {
                        $remainder = $discount - $subtotal;
                        $this->basket['coupons'][$key]['value_display'] = sprintf('%.2F', $subtotal);
                        $this->_discount += $subtotal;
                        $subtotal = 0;
                    }
                    $this->basket['coupons'][$key]['remainder'] = $remainder;
                }
            }
            $tax = ($subtotal>0) ? ($subtotal*$ave_tax_rate) : 0;
            $GLOBALS['tax']->adjustTax($tax);

            foreach ($GLOBALS['hooks']->load('class.cart.apply_discounts') as $hook) {
                include $hook;
            }

            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Check option choice
     *
     * @param mixed $value
     * @param bool $require
     * return bool
     */
    private function _checkOption($value, $require)
    {
        if (empty($value)) {
            if ($require) {
                return false;
            }
        }
        return true;
    }

    /**
     * Applies option modifiers (e.g. to price, weight, etc) to the product's data array.
     *
     * @param array $product product to modify, as retreived from Catalogue->getProductData
     *        Elements modified:
     *        'price' => option price modifiers are applied, with absolute pricing taking precedence over any previous modifiers
     *        'option_line_price' => calculated the same as 'price'
     *        'option_price_ignoring_tax' => calculated the same as 'price' but does not remove any included tax
     *        'absolute_price' => added and set to true if any option uses absolute pricing
     *        'product_weight' => option modifier (may be negative), if any, is added to product weight
     *
     * @param array $option option to apply, as retrieved from Catalogue->getOptionData; $option['option_price'] should not be negative
     *        Elements modified:
     *        'price_display' => formatted string containing the price of this option, e.g. '-$1.00' or '$5.00'
     */
    public static function updateProductDataWithOption(array &$product, array &$option)
    {
        if ($option['option_price'] > 0) {
            $option['price_display'] = '';
            $display_option_tax = $option['option_price'];
            if (!empty($product['remove_options_tax'])) {
                $GLOBALS['tax']->inclusiveTaxRemove($option['option_price'], $product['tax_type']);
            }
            $price_value = $option['option_price'] * (isset($option['option_negative']) && $option['option_negative'] ? -1 : 1);
            $display_option_tax *= (isset($option['option_negative']) && $option['option_negative'] ? -1 : 1);
            $product['price'] += $price_value;
            $product['option_line_price'] += $price_value;
            $product['option_price_ignoring_tax'] += $display_option_tax;
            if ($option['absolute_price']) {
                $product['price'] -= $product['price_to_pay'];
                $product['option_line_price'] -= $product['price_to_pay'];
                $product['absolute_price'] = true;
            } else {
                $option['price_display'] = ($price_value < 0 ? '-' : '+');
            }
            $option['price_display'] .= $GLOBALS['tax']->priceFormat(abs($display_option_tax), true);
        }
        $product['product_weight'] += (isset($option['option_weight'])) ? $option['option_weight'] : 0;
        if ($option['option_weight']>0) {
            $product['digital'] = false;
        }
    }

    public function verifyBasket() {
        if(isset($_POST['quan'])) return false;
        if(isset($this->basket['contents'])) {
            $verify = array();
            foreach($this->basket['contents'] as $hash => $item_data) {
                $verify[$hash] = $item_data['quantity'];
            }
            $this->update($verify);
        }
        return;
    }
}
