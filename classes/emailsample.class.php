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
 * Sample data for email previews and test sends
 *
 * Every email is sent with a set of Smarty variables that the sending code assigns
 * just beforehand. To show an email without sending it, the same variables have to
 * be assigned first. This class assigns them, either from the store's most recent
 * order or from a built-in example, in the shapes the real senders use.
 */
class EmailSample
{
    const SOURCE_LATEST  = 'latest';
    const SOURCE_EXAMPLE = 'example';

    /**
     * Emails that are built from an order, and so can be shown with a real one
     * @var array
     */
    private static $_order_types = array(
        'cart.order_confirmation', 'cart.order_complete', 'admin.order_received',
        'cart.order_cancelled', 'cart.payment_fraud', 'cart.payment_received',
        'cart.digital_download', 'cart.gift_certificate', 'cart.abandoned',
    );

    //=====[ Public ]=======================================

    /**
     * Whether an email is built from an order
     *
     * @param string $content_type
     * @return bool
     */
    public static function usesOrder($content_type)
    {
        return in_array($content_type, self::$_order_types, true);
    }

    /**
     * The store's most recent order worth showing: the latest one that is Processing
     * or Complete, or failing that the latest of any status.
     *
     * @return array|false 'cart_order_id' and the order number as the store displays it
     */
    public static function latestOrder()
    {
        $columns = array('cart_order_id', 'custom_oid', 'id');
        $order   = $GLOBALS['db']->select('CubeCart_order_summary', $columns, array('status' => array(Order::ORDER_PROCESS, Order::ORDER_COMPLETE)), array('order_date' => 'DESC'), 1, false, false);
        if (!$order) {
            $order = $GLOBALS['db']->select('CubeCart_order_summary', $columns, false, array('order_date' => 'DESC'), 1, false, false);
        }
        if (!$order) {
            return false;
        }
        $field = self::_orderNumberField();
        return array(
            'cart_order_id' => $order[0]['cart_order_id'],
            'number'        => !empty($order[0][$field]) ? $order[0][$field] : $order[0]['cart_order_id'],
        );
    }

    /**
     * Assign the Smarty variables that an email of this type is sent with
     *
     * @param string $content_type e.g. cart.order_confirmation
     * @param string $source SOURCE_LATEST to use the most recent order where the email has one
     * @param array $macros The email's macro list, used for emails this class has no data for
     * @return array 'source' (what was used) and 'order' (its number, when a real order was used)
     */
    public static function assign($content_type, $source = self::SOURCE_LATEST, $macros = array())
    {
        $used  = array('source' => self::SOURCE_EXAMPLE, 'order' => '');
        $admin = ($content_type === 'admin.order_received') ? true : null;
        $order = false;

        if ($source === self::SOURCE_LATEST && self::usesOrder($content_type) && ($latest = self::latestOrder()) !== false) {
            try {
                $order = Order::getInstance()->getOrderDetails($latest['cart_order_id']);
            } catch (Throwable $e) {
                $order = false;
            }
            if (is_array($order) && !empty($order['order_summary']) && !empty($order['products'])) {
                $used = array('source' => self::SOURCE_LATEST, 'order' => $latest['number']);
            } else {
                $order = false;
            }
        }
        // The row as stored: the download and gift card emails are given this as DATA
        $stored = ($order !== false) ? Order::getInstance()->getSummary($latest['cart_order_id']) : false;
        if ($order === false) {
            $order = self::_exampleOrder();
        }
        if (!is_array($stored)) {
            $stored = $order['order_summary'];
        }
        $store_url  = $GLOBALS['storeURL'];
        $store_name = $GLOBALS['config']->get('config', 'store_name');
        $admin_url  = $store_url.'/'.$GLOBALS['config']->get('config', 'adminFile');
        $admin_name = Admin::getInstance()->get('name');
        $admin_name = !empty($admin_name) ? $admin_name : 'Sam Taylor';
        $order_number = $order['order_summary'][self::_orderNumberField()];
        $now = time();

        switch ($content_type) {
            case 'cart.order_confirmation':
            case 'cart.order_complete':
            case 'admin.order_received':
            case 'cart.order_cancelled':
            case 'cart.payment_fraud':
            case 'cart.payment_received':
                // Exactly what Order::orderStatus() and paymentStatus() do before sending these
                Order::getInstance()->assignOrderDetails($order, $admin);
                break;

            case 'cart.digital_download':
                // Sent while the order's details are still assigned, with the order row as DATA
                Order::getInstance()->assignOrderDetails($order);
                $GLOBALS['smarty']->assign('DATA', $stored);
                $GLOBALS['smarty']->assign('DOWNLOADS', array(array(
                    'name'   => $order['products'][0]['name'],
                    'stream' => 0,
                    'url'    => $store_url.'/index.php?_a=download&s=0&accesskey=EXAMPLE',
                    'expire' => formatTime($now + 86400, false, true),
                )));
                break;

            case 'cart.gift_certificate':
                Order::getInstance()->assignOrderDetails($order);
                $GLOBALS['smarty']->assign('DATA', array_merge($stored, array(
                    'name'     => 'Alex Morgan',
                    'email'    => 'alex.morgan@example.com',
                    'message'  => 'Happy birthday!',
                    'value'    => Tax::getInstance()->priceFormat(25),
                    'code'     => 'GC-EXAMPLE-1234',
                    'storeURL' => $store_url,
                )));
                break;

            case 'cart.abandoned':
                $products = array();
                foreach ($order['products'] as $product) {
                    $image = '';
                    if (isset($product['image'])) {
                        $image = $product['image'];
                    } elseif (!empty($product['images'][0]['source'])) {
                        $image = $product['images'][0]['source'];
                    }
                    $products[] = array(
                        'name'      => $product['name'],
                        'price'     => isset($product['item_price']) ? $product['item_price'] : $product['price'],
                        'raw_price' => isset($product['raw_price']) ? $product['raw_price'] : 0,
                        'quantity'  => $product['quantity'],
                        'options'   => isset($product['product_options']) ? $product['product_options'] : '',
                        'image'     => $image,
                    );
                }
                $GLOBALS['smarty']->assign('PRODUCTS', $products);
                $GLOBALS['smarty']->assign('DATA', array(
                    'first_name'         => $order['billing']['first_name'],
                    'last_name'          => $order['billing']['last_name'],
                    'store_name'         => $store_name,
                    'item_count'         => count($products),
                    'recovery_link'      => $store_url.'/index.php?_a=recover&token=EXAMPLE',
                    'optout_link'        => $store_url.'/index.php?_a=recover&action=optout&token=EXAMPLE',
                    'coupon_code'        => 'EXAMPLE10',
                    'coupon_description' => '10% off your order',
                ));
                break;

            case 'cart.withdrawal_acknowledgment':
            case 'admin.withdrawal_received':
                $GLOBALS['smarty']->assign('DATA', array(
                    'name'              => 'Sam Taylor',
                    'email'             => 'sam.taylor@example.com',
                    'reference'         => 'W-00001',
                    'submitted_at'      => formatTime($now),
                    'cart_order_id'     => $order_number,
                    'reported_delivery' => date('Y-m-d', $now - 432000),
                    'statement'         => 'I withdraw from my contract for the purchase of the goods in this order.',
                    'reason'            => 'The item is not what I expected.',
                    'store_name'        => $store_name,
                    'admin_link'        => $admin_url.'?_g=customers&node=withdrawals',
                ));
                break;

            case 'cart.withdrawal_decision':
                $GLOBALS['smarty']->assign('DATA', array(
                    'name'          => 'Sam Taylor',
                    'reference'     => 'W-00001',
                    'cart_order_id' => $order_number,
                    'status'        => 'accepted',
                    'decision_at'   => formatTime($now),
                    'note'          => 'Your refund will be with you within 14 days.',
                    'store_name'    => $store_name,
                ));
                break;

            case 'account.password_recovery':
                $GLOBALS['smarty']->assign('DATA', array(
                    'first_name' => 'Sam',
                    'last_name'  => 'Taylor',
                    'email'      => 'sam.taylor@example.com',
                    'reset_link' => $store_url.'/index.php?_a=recovery&validate=EXAMPLE&email=sam.taylor%40example.com',
                ));
                break;

            case 'account.activate':
                $GLOBALS['smarty']->assign('DATA', array(
                    'first_name'    => 'Sam',
                    'last_name'     => 'Taylor',
                    'email'         => 'sam.taylor@example.com',
                    'activate_link' => $store_url.'/index.php?_a=activate&validate=EXAMPLE',
                ));
                break;

            case 'admin.password_recovery':
                $GLOBALS['smarty']->assign('DATA', array(
                    'name' => $admin_name,
                    'link' => $admin_url.'?_g=recovery&email=you%40example.com&validate=EXAMPLE',
                ));
                break;

            case 'admin.two_factor_code':
                $GLOBALS['smarty']->assign('DATA', array(
                    'name'    => $admin_name,
                    'code'    => '123456',
                    'expires' => '10 minutes',
                ));
                break;

            case 'admin.new_device_login':
                $browser = isset($_SERVER['HTTP_USER_AGENT']) ? (string)$_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0';
                $GLOBALS['smarty']->assign('DATA', array(
                    'name'             => $admin_name,
                    'new_ip'           => '203.0.113.24',
                    'previous_ip'      => '198.51.100.7',
                    'new_browser'      => $browser,
                    'previous_browser' => $browser,
                    'login_time'       => date('Y-m-d H:i:s T', $now),
                    'ip_changed'       => true,
                    'browser_changed'  => true,
                ));
                break;

            case 'admin.review_added':
                $GLOBALS['smarty']->assign('DATA', array(
                    'name'         => 'Sam Taylor',
                    'email'        => 'sam.taylor@example.com',
                    'title'        => 'Just what I needed',
                    'review'       => 'Arrived quickly and works exactly as described.',
                    'rating'       => 5,
                    'product_name' => $order['products'][0]['name'],
                    'link'         => $admin_url.'?_g=products&node=reviews',
                ));
                break;

            case 'newsletter.verify_email':
                $GLOBALS['smarty']->assign('DATA', array(
                    'email' => 'sam.taylor@example.com',
                    'link'  => $store_url.'?_a=newsletter&do=EXAMPLE',
                ));
                break;

            case 'newsletter.remove_request':
                $GLOBALS['smarty']->assign('DATA', array(
                    'email' => 'sam.taylor@example.com',
                    'link'  => $store_url.'/index.php?_a=unsubscribe&unsubscribe=sam.taylor%40example.com&rt=EXAMPLE',
                ));
                break;

            case 'catalogue.tell_friend':
                $GLOBALS['smarty']->assign('DATA', array(
                    'to'      => 'Alex',
                    'from'    => 'Sam Taylor',
                    'name'    => $order['products'][0]['name'],
                    'link'    => $store_url,
                    'message' => 'I thought you might like this.',
                ));
                break;

            default:
                // An email this class knows nothing about, usually one added by an extension.
                // Its macro list says which variables it reads; show each as its own tag
                // unless the list supplies a sample value.
                $used['source'] = 'macros';
                foreach (self::_fromMacros($macros) as $name => $value) {
                    $GLOBALS['smarty']->assign($name, $value);
                }
        }
        return $used;
    }

    //=====[ Private ]=======================================

    /**
     * Which column holds the order number the store shows its customers
     *
     * @return string
     */
    private static function _orderNumberField()
    {
        $field = ($GLOBALS['config']->get('config', 'oid_mode') == 'i') ? $GLOBALS['config']->get('config', 'oid_col') : 'cart_order_id';
        return !empty($field) ? $field : 'cart_order_id';
    }

    /**
     * Turn a macro list into variables: '{$REWARDS.points}' => 'x' becomes REWARDS['points'] = 'x'
     *
     * @param array $macros
     * @return array
     */
    private static function _fromMacros($macros)
    {
        $variables = array();
        if (!is_array($macros)) {
            return $variables;
        }
        foreach ($macros as $tag => $entry) {
            if (!preg_match('/^\{\$([A-Za-z_]\w*)((?:\.\w+)*)\}$/', (string)$tag, $match)) {
                continue;
            }
            $value = (is_array($entry) && isset($entry['sample'])) ? $entry['sample'] : (string)$tag;
            if ($match[2] === '') {
                $variables[$match[1]] = $value;
                continue;
            }
            if (!isset($variables[$match[1]]) || !is_array($variables[$match[1]])) {
                $variables[$match[1]] = array();
            }
            $target = &$variables[$match[1]];
            $keys   = explode('.', ltrim($match[2], '.'));
            $last   = array_pop($keys);
            foreach ($keys as $key) {
                if (!isset($target[$key]) || !is_array($target[$key])) {
                    $target[$key] = array();
                }
                $target = &$target[$key];
            }
            $target[$last] = $value;
            unset($target);
        }
        return $variables;
    }

    /**
     * A made-up order in the shape Order::getOrderDetails() returns
     *
     * @return array
     */
    private static function _exampleOrder()
    {
        $tax = Tax::getInstance();
        $now = time();
        $id  = date('ymd-His', $now).'-0000';
        $address = array(
            'first_name'   => 'Sam',
            'last_name'    => 'Taylor',
            'company_name' => '',
            'line1'        => '14 Mill Lane',
            'line2'        => '',
            'town'         => 'Bristol',
            'state'        => '',
            'postcode'     => 'BS1 4DJ',
            'country'      => 'United Kingdom',
            'w3w'          => '',
        );
        $summary = array(
            'id'                => 1001,
            'cart_order_id'     => $id,
            'custom_oid'        => '1001',
            'status'            => Order::ORDER_PROCESS,
            'order_date'        => formatTime($now, false, true),
            'raw_order_date'    => $now,
            'lang'              => $GLOBALS['language']->current(),
            'email'             => 'sam.taylor@example.com',
            'phone'             => '0117 496 0123',
            'gateway'           => 'Card',
            'ship_method'       => 'Standard delivery',
            'ship_product'      => '',
            'ship_tracking'     => 'AB123456789GB',
            'ship_date'         => formatDispatchDate(date('Y-m-d', $now)),
            'customer_comments' => '',
            'note_to_customer'  => 'A note you add to the order is shown here.',
            'subtotal'          => $tax->priceFormat(59, true),
            'raw_subtotal'      => 59,
            'shipping'          => $tax->priceFormat(3.95, true),
            'raw_shipping'      => 3.95,
            'discount'          => $tax->priceFormat(0, true),
            'raw_discount'      => 0,
            'total'             => $tax->priceFormat(75.54, true),
            'raw_total'         => 75.54,
            'show_credit'       => false,
            'credit_used'       => $tax->priceFormat(0, true),
        );
        foreach ($address as $key => $value) {
            $summary[$key] = $value;
            $summary[$key.'_d'] = $value;
        }
        $products = array(
            array(
                'name'            => 'Example product',
                'product_code'    => 'EXAMPLE-1',
                'quantity'        => 1,
                'price'           => $tax->priceFormat(42),
                'item_price'      => $tax->priceFormat(42),
                'raw_price'       => 42,
                'raw_line_total'  => 42,
                'product_options' => 'Colour: Blue',
            ),
            array(
                'name'            => 'Another example product',
                'product_code'    => 'EXAMPLE-2',
                'quantity'        => 2,
                'price'           => $tax->priceFormat(17),
                'item_price'      => $tax->priceFormat(8.5),
                'raw_price'       => 8.5,
                'raw_line_total'  => 17,
                'product_options' => '',
            ),
        );
        return array(
            'order_summary' => $summary,
            'billing'       => array_merge($address, array('phone' => $summary['phone'], 'email' => $summary['email'])),
            'shipping'      => $address,
            'taxes'         => array(array('tax_name' => 'VAT', 'tax_percent' => 20, 'tax_amount' => $tax->priceFormat(12.59))),
            'products'      => $products,
        );
    }
}
