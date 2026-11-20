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
 * Merges orders that are paid for and waiting to be dispatched into one
 *
 * A customer who orders again after forgetting something ends up with two orders
 * for one parcel. Merging moves the products and payments from the other orders
 * into the one being kept, adds the amounts together and cancels the orders that
 * were merged in. The same product at the same price becomes one line.
 *
 * Nothing is recalculated. The combined order holds what the customer was charged
 * across the originals, shipping included, so its total still matches what was paid.
 *
 * Only orders at Processing can be merged. An unpaid order is rebuilt from the
 * customer's basket when they return to pay (Order::_updateOrder), which would
 * remove anything that had been merged into it.
 */
class OrderMerge
{
    /**
     * Rows that follow the products to the order being kept, as table => column
     * @var array
     */
    private static $_follow = array(
        'CubeCart_order_inventory' => 'cart_order_id',
        'CubeCart_downloads'       => 'cart_order_id',
        'CubeCart_coupons'         => 'cart_order_id', // gift cards bought on the order
        'CubeCart_transactions'    => 'order_id',
        'CubeCart_order_notes'     => 'cart_order_id',
    );

    /**
     * Amounts on an order that are added together
     * @var array
     */
    private static $_sums = array('subtotal', 'discount', 'shipping', 'shipping_tax', 'total_tax', 'total', 'credit_used');

    /**
     * What makes two orders go to the same place
     * @var array
     */
    private static $_delivery = array('first_name_d', 'last_name_d', 'company_name_d', 'line1_d', 'line2_d', 'town_d', 'state_d', 'postcode_d', 'country_d');

    //=====[ Public ]=======================================

    /**
     * One order's summary
     *
     * @param string $order_id
     * @return array|null
     */
    public static function order($order_id)
    {
        $order_id = is_scalar($order_id) ? (string)$order_id : '';
        if (!preg_match(Order::TRADITIONAL_ORDER_FORMAT, $order_id)) {
            return null;
        }
        $row = $GLOBALS['db']->select('CubeCart_order_summary', false, array('cart_order_id' => $order_id), false, 1, false, false);
        return $row ? $row[0] : null;
    }

    /**
     * An order's number as the store shows it
     *
     * @param array $order
     * @return string
     */
    public static function number($order)
    {
        $column = ($GLOBALS['config']->get('config', 'oid_mode') == 'i') ? (string)$GLOBALS['config']->get('config', 'oid_col') : '';
        return ($column !== '' && !empty($order[$column])) ? (string)$order[$column] : (string)$order['cart_order_id'];
    }

    /**
     * Why one order cannot be merged into another
     *
     * @param array|null $keep  The order being kept
     * @param array|null $from  The order being merged in
     * @return string  '' when it can, otherwise 'missing', 'same', 'status', 'customer', 'currency' or 'address'
     */
    public static function problem($keep, $from)
    {
        if (empty($keep) || empty($from)) {
            return 'missing';
        }
        if ((string)$keep['cart_order_id'] === (string)$from['cart_order_id']) {
            return 'same';
        }
        // Paid for and not yet dispatched, both of them
        if ((int)$keep['status'] !== Order::ORDER_PROCESS || (int)$from['status'] !== Order::ORDER_PROCESS) {
            return 'status';
        }
        if (!self::_sameCustomer($keep, $from)) {
            return 'customer';
        }
        if (strtoupper(trim((string)$keep['currency'])) !== strtoupper(trim((string)$from['currency']))) {
            return 'currency';
        }
        if (self::_deliveryKey($keep) !== self::_deliveryKey($from)) {
            return 'address';
        }
        return '';
    }

    /**
     * The same customer's other orders that can be merged with this one
     *
     * @param string $order_id
     * @param int $limit
     * @return array  Order summaries, oldest first
     */
    public static function candidates($order_id, $limit = 25)
    {
        $order = self::order($order_id);
        if (!$order || (int)$order['status'] !== Order::ORDER_PROCESS) {
            return array();
        }
        $db     = $GLOBALS['db'];
        $prefix = $GLOBALS['config']->get('config', 'dbprefix');

        $who = array();
        if ((int)$order['customer_id'] > 0) {
            $who[] = '`customer_id` = '.(int)$order['customer_id'];
        }
        if (trim((string)$order['email']) !== '') {
            $who[] = '`email` = '.$db->sqlSafe(trim((string)$order['email']), true);
        }
        if (!$who) {
            return array();
        }
        // Everything but the saved basket and any stored card, which can be large
        $columns = '`cart_order_id`, `custom_oid`, `order_date`, `customer_id`, `status`, `subtotal`, `discount`, `shipping`, `total_tax`, `total`, `currency`, `email`, `gateway`, `ship_method`, `first_name`, `last_name`, `'.implode('`, `', self::$_delivery).'`';
        $rows    = $db->misc("SELECT {$columns} FROM `{$prefix}CubeCart_order_summary` WHERE `status` = ".Order::ORDER_PROCESS." AND `cart_order_id` <> ".$db->sqlSafe($order['cart_order_id'], true)." AND (".implode(' OR ', $who).") ORDER BY `order_date` ASC LIMIT 200", false);
        $found   = array();
        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (self::problem($order, $row) === '') {
                    $found[] = $row;
                    if (count($found) >= (int)$limit) {
                        break;
                    }
                }
            }
        }
        return $found;
    }

    /**
     * What merging a set of orders into one would do, without changing anything
     *
     * @param string $keep_id
     * @param array $from_ids
     * @return array
     *   'problem'  '' when the merge can go ahead, otherwise 'missing' or 'status'
     *              (the order being kept) or 'none' (nothing left to merge in)
     *   'keep'     the order being kept
     *   'from'     the orders that would be merged in, oldest first
     *   'refused'  cart_order_id => array('order' => summary or null, 'reason' => as problem())
     *   'lines'    cart_order_id => array('lines' => product lines, 'quantity' => items)
     *   'moving'   how much moves: lines, quantity, downloads, certificates, transactions, notes
     *   'before'   the kept order's amounts now
     *   'after'    the kept order's amounts once merged
     */
    public static function plan($keep_id, array $from_ids)
    {
        $plan = array(
            'problem' => '',
            'keep'    => self::order($keep_id),
            'from'    => array(),
            'refused' => array(),
            'lines'   => array(),
            'moving'  => array('lines' => 0, 'quantity' => 0, 'downloads' => 0, 'certificates' => 0, 'transactions' => 0, 'notes' => 0),
            'before'  => array(),
            'after'   => array(),
        );
        if (!$plan['keep']) {
            $plan['problem'] = 'missing';
            return $plan;
        }
        $keep = $plan['keep'];
        if ((int)$keep['status'] !== Order::ORDER_PROCESS) {
            $plan['problem'] = 'status';
            return $plan;
        }

        $seen = array((string)$keep['cart_order_id'] => true);
        foreach ($from_ids as $from_id) {
            $from_id = is_scalar($from_id) ? (string)$from_id : '';
            if ($from_id === '' || isset($seen[$from_id])) {
                continue;
            }
            $seen[$from_id] = true;
            $from   = self::order($from_id);
            $reason = self::problem($keep, $from);
            if ($reason === '') {
                $plan['from'][] = $from;
            } else {
                $plan['refused'][$from_id] = array('order' => $from, 'reason' => $reason);
            }
        }
        if (!$plan['from']) {
            $plan['problem'] = 'none';
            return $plan;
        }
        usort($plan['from'], function ($a, $b) {
            return ((int)$a['order_date'] <=> (int)$b['order_date']) ?: strcmp((string)$a['cart_order_id'], (string)$b['cart_order_id']);
        });

        $from_ids = array();
        foreach ($plan['from'] as $from) {
            $from_ids[] = (string)$from['cart_order_id'];
        }
        $plan['lines'] = self::lines(array_merge(array((string)$keep['cart_order_id']), $from_ids));
        foreach ($from_ids as $from_id) {
            $plan['moving']['lines']    += $plan['lines'][$from_id]['lines'];
            $plan['moving']['quantity'] += $plan['lines'][$from_id]['quantity'];
        }
        $plan['moving']['downloads']    = self::_rows('CubeCart_downloads', 'cart_order_id', $from_ids);
        $plan['moving']['certificates'] = self::_rows('CubeCart_coupons', 'cart_order_id', $from_ids);
        $plan['moving']['transactions'] = self::_rows('CubeCart_transactions', 'order_id', $from_ids);
        $plan['moving']['notes']        = self::_rows('CubeCart_order_notes', 'cart_order_id', $from_ids);

        $totals         = self::_totals($keep, $plan['from']);
        $plan['before'] = $totals['before'];
        $plan['after']  = $totals['after'];
        return $plan;
    }

    /**
     * Merge orders into the one being kept
     *
     * @param string $keep_id
     * @param array $from_ids
     * @param array $options
     *   'note'        text for the customer, added to the note they see on each of the orders.
     *                 It is stored as given and shown as HTML, so encode it first.
     *   'admin_id'    who is merging, recorded against the notes left on the orders
     *   'admin_name'  their name, for the wording of those notes
     * @return array|false  The plan that was carried out, or false if nothing was changed
     */
    public static function merge($keep_id, array $from_ids, array $options = array())
    {
        $plan = self::plan($keep_id, $from_ids);
        if ($plan['problem'] !== '') {
            return false;
        }
        $db       = $GLOBALS['db'];
        $prefix   = $GLOBALS['config']->get('config', 'dbprefix');
        $keep_id  = (string)$plan['keep']['cart_order_id'];
        $from_ids = array();
        foreach ($plan['from'] as $from) {
            $from_ids[] = (string)$from['cart_order_id'];
        }
        $note     = isset($options['note']) ? trim((string)$options['note']) : '';
        $admin_id = isset($options['admin_id']) ? (int)$options['admin_id'] : 0;
        $who      = isset($options['admin_name']) ? trim((string)$options['admin_name']) : '';
        $when     = formatTime(time(), false, true);

        $db->misc('START TRANSACTION', false);
        try {
            // Hold the orders while they change, then look at them again now that they are held:
            // a payment callback or another administrator may have moved one on since the review
            $quoted = array();
            foreach (array_merge(array($keep_id), $from_ids) as $id) {
                $quoted[] = $db->sqlSafe($id, true);
            }
            $rows = $db->misc("SELECT * FROM `{$prefix}CubeCart_order_summary` WHERE `cart_order_id` IN (".implode(',', $quoted).") FOR UPDATE", false);
            $held = array();
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $held[(string)$row['cart_order_id']] = $row;
                }
            }
            if (!isset($held[$keep_id])) {
                throw new Exception('Order '.$keep_id.' could not be read');
            }
            $keep    = $held[$keep_id];
            $merging = array();
            foreach ($from_ids as $from_id) {
                $reason = self::problem($keep, isset($held[$from_id]) ? $held[$from_id] : null);
                if ($reason !== '') {
                    throw new Exception('Order '.$from_id.' can no longer be merged ('.$reason.')');
                }
                $merging[] = $held[$from_id];
            }
            $totals         = self::_totals($keep, $merging);
            $plan['keep']   = $keep;
            $plan['from']   = $merging;
            $plan['before'] = $totals['before'];
            $plan['after']  = $totals['after'];

            $keep_number = self::number($keep);
            $comments    = trim((string)$keep['customer_comments']);
            $coupons     = self::_coupons($keep['coupon_data']);
            // An order's pages and emails read the price each line was shown at from the basket
            // saved with it, which is what keeps them exact on a tax-inclusive store, and
            // "order again" refills the basket from it. The merged lines join the kept order's copy.
            $basket         = Order::basketRead($keep['basket']);
            $saved          = (is_array($basket) && isset($basket['contents']) && is_array($basket['contents']));
            $basket_changed = false;
            // Hashes already in use on the kept order, by its lines or in its saved basket, and
            // the lines an identical one coming in can be added to
            $taken = $saved ? array_fill_keys(array_map('strval', array_keys($basket['contents'])), true) : array();
            $twins = array();
            $mine  = $db->select('CubeCart_order_inventory', false, array('cart_order_id' => $keep_id), array('id' => 'ASC'), false, false, false);
            if ($mine) {
                foreach ($mine as $line) {
                    $taken[(string)$line['hash']] = true;
                    $key = self::_lineKey($line);
                    if ($key !== '' && !isset($twins[$key])) {
                        $twins[$key] = $line;
                    }
                }
            }

            foreach ($merging as $from) {
                $from_id = (string)$from['cart_order_id'];

                $theirs   = Order::basketRead($from['basket']);
                $theirs   = (is_array($theirs) && isset($theirs['contents']) && is_array($theirs['contents'])) ? $theirs['contents'] : array();
                $incoming = $db->select('CubeCart_order_inventory', false, array('cart_order_id' => $from_id), array('id' => 'ASC'), false, false, false);
                $arrived  = array();
                if ($incoming) {
                    foreach ($incoming as $line) {
                        $hash = (string)$line['hash'];
                        $key  = self::_lineKey($line);

                        // The same product with the same options at the same price becomes one line,
                        // with the quantities added, as if it had been ordered together
                        if ($key !== '' && isset($twins[$key]) && self::_samePrice($twins[$key], $line, $saved ? $basket['contents'] : array(), $theirs)) {
                            $twin     = $twins[$key];
                            $quantity = (int)$twin['quantity'] + (int)$line['quantity'];
                            if ($quantity <= 65535) {
                                $tax = sprintf('%.2F', (float)$twin['tax'] + (float)$line['tax']);
                                $db->update('CubeCart_order_inventory', array('quantity' => $quantity, 'tax' => $tax), array('id' => (int)$twin['id']), false, array('quantity', 'tax'));
                                if ($db->statementErrno() !== 0 || $db->statementAffected() !== 1) {
                                    throw new Exception('Line '.(int)$twin['id'].' could not take the quantity of line '.(int)$line['id']);
                                }
                                $db->delete('CubeCart_order_inventory', array('id' => (int)$line['id'], 'cart_order_id' => $from_id), '', false);
                                self::_check('CubeCart_order_inventory');
                                $twins[$key]['quantity'] = $quantity;
                                $twins[$key]['tax']      = $tax;
                                // Its saved price stays in use only while the quantity beside it agrees
                                if ($saved && isset($basket['contents'][(string)$twin['hash']]) && is_array($basket['contents'][(string)$twin['hash']])) {
                                    $basket['contents'][(string)$twin['hash']]['quantity'] = $quantity;
                                    $basket_changed = true;
                                }
                                continue;
                            }
                        }

                        // A product bought on both orders has the same hash on each. A line coming in
                        // takes a new one, so that it finds its own saved price and never another line's.
                        if ($hash !== '') {
                            $new = isset($taken[$hash]) ? md5($from_id.'|'.$hash) : $hash;
                            if ($new !== $hash) {
                                $db->update('CubeCart_order_inventory', array('hash' => $new), array('id' => (int)$line['id'], 'cart_order_id' => $from_id), false, array('hash'));
                                self::_check('CubeCart_order_inventory');
                                $line['hash'] = $new;
                            }
                            $taken[$new] = true;
                            if ($saved && isset($theirs[$hash])) {
                                $basket['contents'][$new] = $theirs[$hash];
                                $basket_changed = true;
                            }
                        }
                        if ($key !== '' && !isset($arrived[$key])) {
                            $arrived[$key] = $line;
                        }
                    }
                }
                // Lines from the order after this one can be added to these. Two lines that were
                // separate on the same order stay separate.
                $twins += $arrived;

                // The lines that are left move as they are. Each carries its own record of whether
                // its stock has been taken, so nothing is taken or returned a second time.
                foreach (self::$_follow as $table => $column) {
                    $db->update($table, array($column => $keep_id), array($column => $from_id), false, array($column));
                    self::_check($table);
                }

                // Tax is kept per rate, so the same rate on both orders becomes one figure
                $taxes = $db->select('CubeCart_order_tax', false, array('cart_order_id' => $from_id), false, false, false, false);
                if ($taxes) {
                    foreach ($taxes as $their_tax) {
                        $my_tax = $db->select('CubeCart_order_tax', false, array('cart_order_id' => $keep_id, 'tax_id' => $their_tax['tax_id']), false, 1, false, false);
                        if ($my_tax) {
                            $db->update('CubeCart_order_tax', array('amount' => sprintf('%.2F', (float)$my_tax[0]['amount'] + (float)$their_tax['amount'])), array('id' => (int)$my_tax[0]['id']), false, array('amount'));
                            self::_check('CubeCart_order_tax');
                            $db->delete('CubeCart_order_tax', array('id' => (int)$their_tax['id']), '', false);
                        } else {
                            $db->update('CubeCart_order_tax', array('cart_order_id' => $keep_id), array('id' => (int)$their_tax['id']), false, array('cart_order_id'));
                        }
                        self::_check('CubeCart_order_tax');
                    }
                }

                $said = trim((string)$from['customer_comments']);
                if ($said !== '' && strpos($comments, $said) === false) {
                    $comments = ($comments === '') ? $said : $comments."\n\n".$said;
                }
                $coupons = array_merge($coupons, self::_coupons($from['coupon_data']));

                // For extensions that keep their own records against an order: move them from
                // $from_id to $keep_id here, while both orders ($keep and $from) are held
                foreach ($GLOBALS['hooks']->load('class.ordermerge.merge') as $hook) {
                    include $hook;
                }

                // Never cancel an order that still has products on it
                $left = self::lines(array($from_id));
                if ($left[$from_id]['lines'] > 0) {
                    throw new Exception('Products are still attached to order '.$from_id);
                }

                // The order that was merged in is closed without Order::orderStatus(). Its
                // products are being sent, so nothing that reacts to a cancellation should run:
                // no email to the customer, no stock returned, no payment voided by a gateway.
                $record = array('status' => Order::ORDER_CANCELLED, 'dashboard' => 0); // and off the dashboard if it was pinned there
                if ($note !== '') {
                    $record['note_to_customer'] = self::_append($from['note_to_customer'], $note);
                }
                $db->update('CubeCart_order_summary', $record, array('cart_order_id' => $from_id), false, array_keys($record));
                self::_check('CubeCart_order_summary');
                $db->insert('CubeCart_order_history', array('cart_order_id' => $from_id, 'status' => Order::ORDER_CANCELLED, 'updated' => time(), 'initiator' => 'S'), false);
                self::_check('CubeCart_order_history');

                self::_note($from_id, $admin_id, sprintf(self::_phrase('merge_note_from', 'Merged into order %1$s on %2$s by %3$s. Its products and payments are on that order.'), $keep_number, $when, $who));
                self::_note($keep_id, $admin_id, sprintf(self::_phrase('merge_note_kept', 'Order %1$s (placed %2$s, total %3$s) was merged into this order on %4$s by %5$s.'), self::number($from), formatTime((int)$from['order_date'], false, true), sprintf('%.2F', (float)$from['total']), $when, $who));
            }

            // What the kept order gains. Its own details, delivery method and tracking stay as they are.
            $record = $totals['after'];
            if ((float)$record['discount'] !== (float)$keep['discount']) {
                $record['discount_type'] = 'f'; // an amount now, whatever it was entered as
            }
            if ((float)$keep['shipping'] <= 0) {
                foreach ($merging as $other) {
                    if ((float)$other['shipping'] > 0) {
                        $record['shipping_tax_rate'] = $other['shipping_tax_rate'];
                        break;
                    }
                }
            }
            if ((float)$record['credit_used'] > 0) {
                // Store credit was taken when each order was paid, and must not be taken again
                $record['credit_shift'] = 1;
            }
            $record['customer_comments'] = $comments;
            $record['coupon_data']       = $coupons ? json_encode($coupons) : $keep['coupon_data'];
            if ($note !== '') {
                $record['note_to_customer'] = self::_append($keep['note_to_customer'], $note);
            }
            // None of these is a sum for the database to work out
            $db->update('CubeCart_order_summary', $record, array('cart_order_id' => $keep_id), false, array_keys($record));
            self::_check('CubeCart_order_summary');
            if ($basket_changed) {
                Order::saveBasketBlob($keep_id, Order::basketWrite($basket, Order::ORDER_PROCESS));
                self::_check('CubeCart_order_summary');
            }

            $db->misc('COMMIT', false);
        } catch (Throwable $e) {
            $db->misc('ROLLBACK', false);
            trigger_error('Order merge failed: '.$e->getMessage(), E_USER_WARNING);
            return false;
        }
        return $plan;
    }

    /**
     * Product lines and items on each of a set of orders, read live
     *
     * @param array $order_ids
     * @return array  cart_order_id => array('lines' => int, 'quantity' => int)
     */
    public static function lines(array $order_ids)
    {
        $out = array();
        foreach ($order_ids as $order_id) {
            $out[(string)$order_id] = array('lines' => 0, 'quantity' => 0);
        }
        if (!$out) {
            return $out;
        }
        $db     = $GLOBALS['db'];
        $prefix = $GLOBALS['config']->get('config', 'dbprefix');
        $rows   = $db->misc("SELECT `cart_order_id`, COUNT(*) AS `lines`, SUM(`quantity`) AS `quantity` FROM `{$prefix}CubeCart_order_inventory` WHERE `cart_order_id` IN (".self::_in(array_keys($out)).") GROUP BY `cart_order_id`", false);
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $out[(string)$row['cart_order_id']] = array('lines' => (int)$row['lines'], 'quantity' => (int)$row['quantity']);
            }
        }
        return $out;
    }

    //=====[ Private ]=======================================

    /**
     * Were two orders placed by the same person?
     *
     * @param array $a
     * @param array $b
     * @return bool
     */
    private static function _sameCustomer(array $a, array $b)
    {
        if ((int)$a['customer_id'] > 0 && (int)$a['customer_id'] === (int)$b['customer_id']) {
            return true;
        }
        $email = strtolower(trim((string)$a['email']));
        return ($email !== '' && $email === strtolower(trim((string)$b['email'])));
    }

    /**
     * A delivery address reduced to what makes it the same place for the same person
     *
     * @param array $order
     * @return string
     */
    private static function _deliveryKey(array $order)
    {
        $parts = array();
        foreach (self::$_delivery as $field) {
            $value = trim((string)(isset($order[$field]) ? $order[$field] : ''));
            $value = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
            $value = preg_replace('/\s+/', ($field === 'postcode_d') ? '' : ' ', $value);
            $parts[] = $value;
        }
        return implode('|', $parts);
    }

    /**
     * What has to match for two product lines to be the same thing at the same price
     *
     * @param array $line  Order inventory row
     * @return string  '' for a line that always stays on its own
     */
    private static function _lineKey(array $line)
    {
        // A download has its own file link and a gift card its own code, each tied to its line
        if ((int)$line['digital'] === 1 || (int)$line['coupon_id'] > 0 || trim((string)$line['custom']) !== '' || (int)$line['quantity'] < 1) {
            return '';
        }
        return implode('|', array(
            (int)$line['product_id'],
            (string)$line['product_code'],
            (string)$line['name'],
            sprintf('%.2F', (float)$line['price']),
            sprintf('%.4F', (float)$line['tax_percent']),
            sprintf('%.2F', (float)$line['cost_price']),
            (string)$line['options_identifier'],
            md5((string)$line['product_options']),
            md5((string)$line['options_array']),
            (int)$line['stock_updated'], // stock taken for one and not the other is not the same line
        ));
    }

    /**
     * Do two lines that look the same also share the exact price their baskets saved?
     *
     * The price on a line is rounded. On a tax-inclusive store the unrounded one in the
     * saved basket is what the customer was shown, so that has to agree as well.
     *
     * @param array $kept      Line on, or on its way to, the order being kept
     * @param array $incoming  Line on the order being merged in
     * @param array $mine      Saved basket lines of the kept order, by hash
     * @param array $theirs    Saved basket lines of the order being merged in, by hash
     * @return bool
     */
    private static function _samePrice(array $kept, array $incoming, array $mine, array $theirs)
    {
        $a = isset($mine[(string)$kept['hash']]) ? $mine[(string)$kept['hash']] : null;
        $b = isset($theirs[(string)$incoming['hash']]) ? $theirs[(string)$incoming['hash']] : null;
        if (!is_array($a) || !is_array($b) || !isset($a['total_price_each'], $b['total_price_each'])) {
            return true; // nothing more exact than the lines themselves to go by
        }
        return abs((float)$a['total_price_each'] - (float)$b['total_price_each']) < 0.00001;
    }

    /**
     * How many rows a table holds against a set of orders, read live
     *
     * @param string $table
     * @param string $column
     * @param array $order_ids
     * @return int
     */
    private static function _rows($table, $column, array $order_ids)
    {
        if (!$order_ids) {
            return 0;
        }
        $prefix = $GLOBALS['config']->get('config', 'dbprefix');
        $rows   = $GLOBALS['db']->misc("SELECT COUNT(*) AS `rows` FROM `{$prefix}{$table}` WHERE `{$column}` IN (".self::_in($order_ids).")", false);
        return (is_array($rows) && isset($rows[0]['rows'])) ? (int)$rows[0]['rows'] : 0;
    }

    /**
     * Order numbers as a quoted list for an IN clause
     *
     * @param array $order_ids
     * @return string
     */
    private static function _in(array $order_ids)
    {
        $quoted = array();
        foreach ($order_ids as $order_id) {
            $quoted[] = $GLOBALS['db']->sqlSafe((string)$order_id, true);
        }
        return implode(',', $quoted);
    }

    /**
     * The kept order's amounts before and after the others are added to it
     *
     * @param array $keep
     * @param array $from
     * @return array  'before' and 'after', each field => amount
     */
    private static function _totals(array $keep, array $from)
    {
        $before = array();
        foreach (self::$_sums as $field) {
            $before[$field] = (float)$keep[$field];
        }
        $before['weight'] = (float)$keep['weight'];
        $after = $before;
        foreach ($from as $order) {
            foreach ($after as $field => $amount) {
                $after[$field] = $amount + (float)$order[$field];
            }
        }
        foreach ($after as $field => $amount) {
            $format         = ($field === 'weight') ? '%.4F' : '%.2F';
            $before[$field] = sprintf($format, $before[$field]);
            $after[$field]  = sprintf($format, $amount);
        }
        return array('before' => $before, 'after' => $after);
    }

    /**
     * The discount codes and gift cards recorded against an order
     *
     * @param string|null $json
     * @return array
     */
    private static function _coupons($json)
    {
        if (empty($json)) {
            return array();
        }
        $data = json_decode((string)$json, true);
        return is_array($data) ? array_values($data) : array();
    }

    /**
     * Add a sentence to a note unless it already says it
     *
     * @param string|null $existing
     * @param string $text
     * @return string
     */
    private static function _append($existing, $text)
    {
        $existing = trim((string)$existing);
        if ($existing === '') {
            return $text;
        }
        return (strpos($existing, $text) !== false) ? $existing : $existing.' '.$text;
    }

    /**
     * Leave a note for staff on an order
     *
     * @param string $order_id
     * @param int $admin_id
     * @param string $content
     */
    private static function _note($order_id, $admin_id, $content)
    {
        $GLOBALS['db']->insert('CubeCart_order_notes', array(
            'admin_id'      => (int)$admin_id,
            'cart_order_id' => (string)$order_id,
            'time'          => time(),
            'content'       => htmlspecialchars(trim((string)$content), ENT_QUOTES, 'UTF-8', false), // as the order page stores a note
            'print'         => '0',
        ), false);
        self::_check('CubeCart_order_notes');
    }

    /**
     * Stop the merge if the statement just run failed
     *
     * @param string $table
     */
    private static function _check($table)
    {
        if ($GLOBALS['db']->statementErrno() !== 0) {
            throw new Exception('A change to '.$table.' failed');
        }
    }

    /**
     * A language string, with wording to fall back on where it has not been translated
     *
     * @param string $key
     * @param string $fallback
     * @return string
     */
    private static function _phrase($key, $fallback)
    {
        $strings = (isset($GLOBALS['language']) && is_object($GLOBALS['language'])) ? $GLOBALS['language']->orders : null;
        return (is_array($strings) && !empty($strings[$key])) ? $strings[$key] : $fallback;
    }
}
