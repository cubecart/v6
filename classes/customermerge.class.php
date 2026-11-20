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
 * Merges one customer account into another
 *
 * Someone who registers twice, or checks out as a guest under a second email address,
 * ends up with their orders split across accounts. Merging moves everything that
 * belongs to the duplicate into the account being kept, then deletes the duplicate.
 *
 * The kept account always wins: its email address, password, name, language and
 * newsletter setting are never changed. It only gains what the duplicate had.
 */
class CustomerMerge
{
    const REGISTERED = 1;
    const GUEST      = 2;

    /**
     * Tables whose rows only need a new owner, keyed by the name used in a plan
     * @var array
     */
    private static $_owned = array(
        'orders'       => 'CubeCart_order_summary',
        'transactions' => 'CubeCart_transactions',
        'downloads'    => 'CubeCart_downloads',
        'reviews'      => 'CubeCart_reviews',
        'withdrawals'  => 'CubeCart_withdrawal_requests',
        'abandonment'  => 'CubeCart_cart_abandonment',
    );

    //=====[ Public ]=======================================

    /**
     * One customer's record
     *
     * @param int $customer_id
     * @return array|null
     */
    public static function customer($customer_id)
    {
        $customer_id = (int)$customer_id;
        if ($customer_id < 1) {
            return null;
        }
        $row = $GLOBALS['db']->select('CubeCart_customer', false, array('customer_id' => $customer_id), false, 1, false, false);
        return $row ? $row[0] : null;
    }

    /**
     * Why two accounts cannot be merged this way round
     *
     * @param array|null $keep  The account being kept
     * @param array|null $from  The account being merged in and deleted
     * @return string  '' when they can, otherwise 'missing', 'same' or 'guest'
     */
    public static function problem($keep, $from)
    {
        if (empty($keep) || empty($from)) {
            return 'missing';
        }
        if ((int)$keep['customer_id'] === (int)$from['customer_id']) {
            return 'same';
        }
        // A guest account has no login, so a registered account merged into one would lose its own
        if ((int)$keep['type'] === self::GUEST && (int)$from['type'] === self::REGISTERED) {
            return 'guest';
        }
        return '';
    }

    /**
     * Accounts that look like the same person
     *
     * An email address can only belong to one account, so duplicates always differ
     * there. They are found by what they share instead: the same name, the same
     * billing address or the same phone number.
     *
     * @param int $keep_id
     * @param int $limit
     * @return array  Customer records, each with 'reasons' (name, address, phone) and 'orders'
     */
    public static function candidates($keep_id, $limit = 25)
    {
        $keep = self::customer($keep_id);
        if (!$keep) {
            return array();
        }
        $db     = $GLOBALS['db'];
        $prefix = $GLOBALS['config']->get('config', 'dbprefix');
        $id     = (int)$keep['customer_id'];
        $found  = array();

        // Same name
        $first = trim((string)$keep['first_name']);
        $last  = trim((string)$keep['last_name']);
        if ($first !== '' && $last !== '') {
            $rows = $db->misc("SELECT `customer_id` FROM `{$prefix}CubeCart_customer` WHERE `customer_id` <> {$id} AND `first_name` = ".$db->sqlSafe($first, true)." AND `last_name` = ".$db->sqlSafe($last, true)." LIMIT 100", false);
            self::_note($found, $rows, 'name');
        }

        // Same billing address
        $rows = $db->misc("SELECT DISTINCT b.`customer_id` FROM `{$prefix}CubeCart_addressbook` AS a INNER JOIN `{$prefix}CubeCart_addressbook` AS b ON REPLACE(b.`postcode`, ' ', '') = REPLACE(a.`postcode`, ' ', '') AND b.`line1` = a.`line1` WHERE a.`customer_id` = {$id} AND b.`customer_id` <> {$id} AND a.`billing` = '1' AND b.`billing` = '1' AND a.`postcode` <> '' AND a.`line1` <> '' LIMIT 100", false);
        self::_note($found, $rows, 'address');

        // Same phone number. Anything too short to be one ("0", "n/a") is ignored.
        $numbers = array();
        foreach (array('phone', 'mobile') as $field) {
            $number = trim((string)$keep[$field]);
            if (strlen(preg_replace('/\D/', '', $number)) >= 6) {
                $numbers[$number] = $db->sqlSafe($number, true);
            }
        }
        if ($numbers) {
            $in   = implode(',', $numbers);
            $rows = $db->misc("SELECT `customer_id` FROM `{$prefix}CubeCart_customer` WHERE `customer_id` <> {$id} AND (`phone` IN ({$in}) OR `mobile` IN ({$in})) LIMIT 100", false);
            self::_note($found, $rows, 'phone');
        }

        if (!$found) {
            return array();
        }

        $ids  = implode(',', array_map('intval', array_keys($found)));
        $rows = $db->misc("SELECT * FROM `{$prefix}CubeCart_customer` WHERE `customer_id` IN ({$ids})", false);
        if (!is_array($rows)) {
            return array();
        }
        $orders = array();
        $counts = $db->misc("SELECT `customer_id`, COUNT(*) AS `orders` FROM `{$prefix}CubeCart_order_summary` WHERE `customer_id` IN ({$ids}) GROUP BY `customer_id`", false);
        if (is_array($counts)) {
            foreach ($counts as $count) {
                $orders[(int)$count['customer_id']] = (int)$count['orders'];
            }
        }
        foreach ($rows as $key => $row) {
            $rows[$key]['reasons'] = $found[(int)$row['customer_id']];
            $rows[$key]['orders']  = isset($orders[(int)$row['customer_id']]) ? $orders[(int)$row['customer_id']] : 0;
        }
        // The more it has in common the higher it goes, then the newest first
        usort($rows, function ($a, $b) {
            if (count($a['reasons']) !== count($b['reasons'])) {
                return count($b['reasons']) - count($a['reasons']);
            }
            return (int)$b['registered'] - (int)$a['registered'];
        });
        return array_slice($rows, 0, max(1, (int)$limit));
    }

    /**
     * What merging one account into another would do, without doing it
     *
     * @param int $keep_id  The account being kept
     * @param int $from_id  The account being merged in and deleted
     * @return array  'keep', 'from' and 'problem', then what would move
     */
    public static function plan($keep_id, $from_id)
    {
        $db   = $GLOBALS['db'];
        $keep = self::customer($keep_id);
        $from = self::customer($from_id);
        $plan = array('keep' => $keep, 'from' => $from, 'problem' => self::problem($keep, $from));
        if ($plan['problem'] === 'missing' || $plan['problem'] === 'same') {
            return $plan;
        }
        $keep_id = (int)$keep['customer_id'];
        $from_id = (int)$from['customer_id'];

        foreach (self::$_owned as $name => $table) {
            $plan[$name] = self::_count($table, $from_id);
        }

        // Addresses the kept account does not already have
        $sorted = self::_sortAddresses($keep_id, $from_id);
        $plan['addresses']           = count($sorted['move']);
        $plan['addresses_duplicate'] = count($sorted['duplicate']);

        // Customer groups the kept account is not already in
        $plan['groups'] = array();
        $groups = self::_sortGroups($keep_id, $from_id);
        if ($groups['move']) {
            $names = $db->select('CubeCart_customer_group', array('group_id', 'group_name'), array('group_id' => array_keys($groups['move'])), false, false, false, false);
            if ($names) {
                foreach ($names as $group) {
                    $plan['groups'][] = $group['group_name'];
                }
            }
        }

        $plan['coupons'] = self::_count('CubeCart_customer_coupon', $from_id);
        $plan['credit']  = round((float)$from['credit'], 2);
        $plan['notes']   = (trim((string)$from['notes']) !== '');

        // A customer has one saved basket, so the kept account's own one wins
        $plan['basket'] = '';
        if (self::_count('CubeCart_saved_cart', $from_id) > 0) {
            $plan['basket'] = (self::_count('CubeCart_saved_cart', $keep_id) > 0) ? 'discard' : 'move';
        }

        // The mailing list goes by email address, and the duplicate's address is going
        $plan['newsletter']      = self::_subscribed($from);
        $plan['newsletter_keep'] = self::_subscribed($keep);

        return $plan;
    }

    /**
     * Merge one account into another and delete it
     *
     * @param int $keep_id     The account being kept
     * @param int $from_id     The account being merged in and deleted
     * @param string $note     Plain text added to the kept account's notes, to record the merge
     * @return array|false     The plan that was carried out, or false if nothing was changed
     */
    public static function merge($keep_id, $from_id, $note = '')
    {
        $plan = self::plan($keep_id, $from_id);
        if ($plan['problem'] !== '') {
            return false;
        }
        $db      = $GLOBALS['db'];
        $keep    = $plan['keep'];
        $from    = $plan['from'];
        $keep_id = (int)$keep['customer_id'];
        $from_id = (int)$from['customer_id'];

        // Everything moves before the duplicate is deleted, so a merge that stops part way
        // can simply be run again. Where the tables allow it, it is all or nothing as well.
        $db->misc('START TRANSACTION', false);
        try {
            // Orders, payments, downloads, reviews and the like only change owner
            foreach (self::$_owned as $table) {
                $db->update($table, array('customer_id' => $keep_id), array('customer_id' => $from_id), false);
            }

            // Addresses: the kept account's billing and default delivery addresses stay as they are
            $sorted      = self::_sortAddresses($keep_id, $from_id);
            $has_billing = $sorted['has_billing'];
            $has_default = $sorted['has_default'];
            foreach ($sorted['move'] as $address) {
                $billing = ($address['billing'] == '1' && !$has_billing) ? '1' : '0';
                $default = ($address['default'] == '1' && !$has_default) ? '1' : '0';
                $has_billing = $has_billing || $billing === '1';
                $has_default = $has_default || $default === '1';
                $db->update('CubeCart_addressbook', array('customer_id' => $keep_id, 'billing' => $billing, 'default' => $default), array('address_id' => (int)$address['address_id']), false);
            }
            foreach ($sorted['duplicate'] as $address) {
                $db->delete('CubeCart_addressbook', array('address_id' => (int)$address['address_id']), '', false);
            }

            // Customer groups
            $groups = self::_sortGroups($keep_id, $from_id);
            foreach ($groups['move'] as $membership_id) {
                $db->update('CubeCart_customer_membership', array('customer_id' => $keep_id), array('membership_id' => (int)$membership_id), false);
            }
            $db->delete('CubeCart_customer_membership', array('customer_id' => $from_id), '', false);

            // Coupon use is counted per customer, so the counts are added together
            $used = $db->select('CubeCart_customer_coupon', false, array('customer_id' => $from_id), false, false, false, false);
            if ($used) {
                foreach ($used as $theirs) {
                    $mine = $db->select('CubeCart_customer_coupon', false, array('customer_id' => $keep_id, 'coupon' => $theirs['coupon']), false, 1, false, false);
                    if ($mine) {
                        $db->update('CubeCart_customer_coupon', array('used' => (int)$mine[0]['used'] + (int)$theirs['used']), array('id' => (int)$mine[0]['id']), false);
                        $db->delete('CubeCart_customer_coupon', array('id' => (int)$theirs['id']), '', false);
                    } else {
                        $db->update('CubeCart_customer_coupon', array('customer_id' => $keep_id, 'email' => $keep['email']), array('id' => (int)$theirs['id']), false);
                    }
                }
            }

            // Saved basket
            if ($plan['basket'] === 'move') {
                $db->update('CubeCart_saved_cart', array('customer_id' => $keep_id), array('customer_id' => $from_id), false);
            } else {
                $db->delete('CubeCart_saved_cart', array('customer_id' => $from_id), '', false);
            }

            // The duplicate's email address leaves the mailing list with its account
            $db->delete('CubeCart_newsletter_subscriber', '`customer_id` = '.$from_id.' OR `email` = '.$db->sqlSafe($from['email'], true), '', false);

            // Its sessions end, its past logins stay on record, and a linked administrator follows
            $db->delete('CubeCart_sessions', array('customer_id' => $from_id), '', false);
            $db->update('CubeCart_access_log', array('user_id' => $keep_id), array('type' => 'F', 'user_id' => $from_id), false);
            $db->update('CubeCart_admin_users', array('customer_id' => $keep_id), array('customer_id' => $from_id), false);

            // What the kept account gains. Nothing it already has is replaced.
            $record = array(
                'credit'      => sprintf('%.2F', min(999999.99, (float)$keep['credit'] + (float)$from['credit'])),
                'order_count' => (int)$keep['order_count'] + (int)$from['order_count'],
            );
            if ((int)$from['abandon_optout'] === 1) {
                $record['abandon_optout'] = 1; // an opt-out is respected whichever account made it
            }
            foreach (array('phone', 'mobile') as $field) {
                if (trim((string)$keep[$field]) === '' && trim((string)$from[$field]) !== '') {
                    $record[$field] = $from[$field];
                }
            }
            $notes = array();
            foreach (array($keep['notes'], $from['notes']) as $text) {
                if (trim((string)$text) !== '') {
                    $notes[] = trim((string)$text);
                }
            }
            if (trim((string)$note) !== '') {
                // Notes are stored the way the customer form saves them
                $notes[] = htmlspecialchars(trim((string)$note));
            }
            $record['notes'] = implode("\n\n", $notes);
            // None of these is a sum for the database to work out
            $db->update('CubeCart_customer', $record, array('customer_id' => $keep_id), false, array_keys($record));

            // For extensions that keep their own records against a customer:
            // move them from $from_id to $keep_id here, before the duplicate goes
            foreach ($GLOBALS['hooks']->load('class.customermerge.merge') as $hook) {
                include $hook;
            }

            // Never delete an account that still has orders
            if (self::_count('CubeCart_order_summary', $from_id) > 0) {
                throw new Exception('Orders are still attached to customer '.$from_id);
            }
            $db->delete('CubeCart_customer', array('customer_id' => $from_id), '', false);
            $db->misc('COMMIT', false);
        } catch (Throwable $e) {
            $db->misc('ROLLBACK', false);
            trigger_error('Customer merge failed: '.$e->getMessage(), E_USER_WARNING);
            return false;
        }
        return $plan;
    }

    //=====[ Private ]=======================================

    /**
     * How many rows a table holds for a customer, read live and never from the cache
     *
     * @param string $table
     * @param int $customer_id
     * @return int
     */
    private static function _count($table, $customer_id)
    {
        $prefix = $GLOBALS['config']->get('config', 'dbprefix');
        $rows   = $GLOBALS['db']->misc("SELECT COUNT(*) AS `rows` FROM `{$prefix}{$table}` WHERE `customer_id` = ".(int)$customer_id, false);
        return (is_array($rows) && isset($rows[0]['rows'])) ? (int)$rows[0]['rows'] : 0;
    }

    /**
     * Record why each customer in a result was found
     *
     * @param array $found
     * @param array|bool $rows
     * @param string $reason
     */
    private static function _note(array &$found, $rows, $reason)
    {
        if (!is_array($rows)) {
            return;
        }
        foreach ($rows as $row) {
            $id = (int)$row['customer_id'];
            if ($id > 0 && (!isset($found[$id]) || !in_array($reason, $found[$id], true))) {
                $found[$id][] = $reason;
            }
        }
    }

    /**
     * Is this customer's email address on the mailing list?
     *
     * @param array $customer
     * @return bool
     */
    private static function _subscribed(array $customer)
    {
        $db = $GLOBALS['db'];
        return (bool)$db->select('CubeCart_newsletter_subscriber', array('subscriber_id'), '`status` = 1 AND (`customer_id` = '.(int)$customer['customer_id'].' OR `email` = '.$db->sqlSafe($customer['email'], true).')', false, 1, false, false);
    }

    /**
     * Split the duplicate's addresses into those to move and those the kept account already has
     *
     * @param int $keep_id
     * @param int $from_id
     * @return array  'move', 'duplicate', 'has_billing', 'has_default'
     */
    private static function _sortAddresses($keep_id, $from_id)
    {
        $db   = $GLOBALS['db'];
        $out  = array('move' => array(), 'duplicate' => array(), 'has_billing' => false, 'has_default' => false);
        $seen = array();
        $mine = $db->select('CubeCart_addressbook', false, array('customer_id' => (int)$keep_id), false, false, false, false);
        if ($mine) {
            foreach ($mine as $address) {
                $seen[self::_addressKey($address)] = true;
                $out['has_billing'] = $out['has_billing'] || $address['billing'] == '1';
                $out['has_default'] = $out['has_default'] || $address['default'] == '1';
            }
        }
        $theirs = $db->select('CubeCart_addressbook', false, array('customer_id' => (int)$from_id), array('address_id' => 'ASC'), false, false, false);
        if ($theirs) {
            foreach ($theirs as $address) {
                $key = self::_addressKey($address);
                if (isset($seen[$key])) {
                    $out['duplicate'][] = $address;
                } else {
                    $seen[$key]    = true;
                    $out['move'][] = $address;
                }
            }
        }
        return $out;
    }

    /**
     * An address reduced to what makes it the same place for the same person
     *
     * @param array $address
     * @return string
     */
    private static function _addressKey(array $address)
    {
        $parts = array();
        foreach (array('first_name', 'last_name', 'company_name', 'line1', 'line2', 'town', 'state', 'postcode', 'country') as $field) {
            $value = trim((string)(isset($address[$field]) ? $address[$field] : ''));
            $value = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
            $value = preg_replace('/\s+/', ($field === 'postcode') ? '' : ' ', $value);
            $parts[] = $value;
        }
        return implode('|', $parts);
    }

    /**
     * The duplicate's group memberships that the kept account does not have
     *
     * @param int $keep_id
     * @param int $from_id
     * @return array  'move' => array(group_id => membership_id)
     */
    private static function _sortGroups($keep_id, $from_id)
    {
        $db   = $GLOBALS['db'];
        $out  = array('move' => array());
        $held = array();
        $mine = $db->select('CubeCart_customer_membership', false, array('customer_id' => (int)$keep_id), false, false, false, false);
        if ($mine) {
            foreach ($mine as $membership) {
                $held[(int)$membership['group_id']] = true;
            }
        }
        $theirs = $db->select('CubeCart_customer_membership', false, array('customer_id' => (int)$from_id), false, false, false, false);
        if ($theirs) {
            foreach ($theirs as $membership) {
                $group_id = (int)$membership['group_id'];
                if (!isset($held[$group_id])) {
                    $held[$group_id]        = true;
                    $out['move'][$group_id] = (int)$membership['membership_id'];
                }
            }
        }
        return $out;
    }
}
