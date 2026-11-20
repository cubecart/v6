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
// A merge changes orders. Nothing is deleted: the orders merged in are kept, cancelled.
Admin::getInstance()->permissions('orders', CC_PERM_EDIT, true);

// Order numbers as they arrive in a request: the store's own format only, each once
$merge_ids = function ($value) {
    $out = array();
    foreach ((array)$value as $id) {
        if (is_scalar($id) && preg_match(Order::TRADITIONAL_ORDER_FORMAT, (string)$id) && !in_array((string)$id, $out, true)) {
            $out[] = (string)$id;
        }
    }
    return $out;
};
$merge_url = function (array $ids, array $extra = array()) {
    return '?'.http_build_query(array_merge(array('_g' => 'orders', 'node' => 'merge', 'oid' => array_values($ids)), $extra));
};
// Customer details are stored encoded by the forms that save them. Anything that
// arrived another way (an import, an extension) is encoded here, and nothing twice.
$merge_safe = function ($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8', false);
};
$merge_plain = function ($text) {
    return html_entity_decode((string)$text, ENT_QUOTES, 'UTF-8');
};
$merge_money = function ($amount) {
    // The plain amount the order holds, as the order page shows it, not converted for display
    return number_format((float)$amount, 2, '.', '');
};
$merge_reason = function ($reason) use ($lang) {
    return isset($lang['orders']['merge_reason_'.$reason]) ? $lang['orders']['merge_reason_'.$reason] : $lang['orders']['merge_reason_missing'];
};
$merge_edit = function ($order_id) {
    return '?_g=orders&action=edit&order_id='.rawurlencode((string)$order_id);
};
// The oldest of a set of orders that is still waiting to be dispatched
$merge_oldest = function (array $ids) {
    $oldest = null;
    foreach ($ids as $id) {
        $order = OrderMerge::order($id);
        if ($order && (int)$order['status'] === Order::ORDER_PROCESS && ($oldest === null || (int)$order['order_date'] < (int)$oldest['order_date'])) {
            $oldest = $order;
        }
    }
    return $oldest ? (string)$oldest['cart_order_id'] : '';
};

if (isset($_POST['merge']) && is_array($_POST['merge'])) {
    $ids  = $merge_ids(isset($_POST['merge']['orders']) ? $_POST['merge']['orders'] : array());
    $keep = (isset($_POST['merge']['keep']) && is_scalar($_POST['merge']['keep']) && in_array((string)$_POST['merge']['keep'], $ids, true)) ? (string)$_POST['merge']['keep'] : '';

    if (!empty($_POST['merge']['confirm'])) {
        // The review step was accepted
        $from = array_values(array_diff($ids, array($keep)));
        $plan = ($keep !== '') ? OrderMerge::plan($keep, $from) : array('problem' => 'missing', 'refused' => array());
        if ($plan['problem'] !== '' || !empty($plan['refused'])) {
            // Something changed between the review and the click. Show the review again as it now stands.
            $GLOBALS['main']->errorMessage($lang['orders']['merge_changed']);
            httpredir($merge_url($ids, ($keep !== '') ? array('keep' => $keep, 'review' => 1) : array()));
        }
        // What the customer reads is plain text, like the note an order already has
        $note = isset($_POST['merge']['note']) && is_scalar($_POST['merge']['note']) ? trim(preg_replace('/\s+/', ' ', strip_tags($merge_plain($_POST['merge']['note'])))) : '';
        $done = OrderMerge::merge($keep, $from, array(
            'note'       => $merge_safe($note),
            'admin_id'   => (int)Admin::getInstance()->get('admin_id'),
            'admin_name' => $merge_plain(Admin::getInstance()->get('name')),
        ));
        if ($done === false) {
            $GLOBALS['main']->errorMessage($lang['orders']['merge_failed']);
            httpredir($merge_url($ids, array('keep' => $keep, 'review' => 1)));
        }
        $numbers = array();
        foreach ($done['from'] as $order) {
            $numbers[] = $merge_safe(OrderMerge::number($order));
        }
        $GLOBALS['main']->successMessage(sprintf($lang['orders']['merge_done'], implode(', ', $numbers)));

        if (!empty($_POST['merge']['email'])) {
            // The usual order confirmation, now listing everything, with the note above in it
            $sent      = false;
            $order_obj = Order::getInstance();
            $summary   = $order_obj->getSummary($keep);
            if ($summary && filter_var($summary['email'], FILTER_VALIDATE_EMAIL)) {
                $mailer = new Mailer();
                if (($content = $mailer->loadContent('cart.order_confirmation', $summary['lang'])) !== false) {
                    $order_obj->getOrderDetails($keep);
                    $order_obj->assignOrderDetails();
                    $sent = (bool)$mailer->sendEmail($summary['email'], $content);
                }
            }
            if ($sent) {
                $GLOBALS['main']->successMessage($lang['orders']['merge_emailed']);
            } else {
                $GLOBALS['main']->errorMessage($lang['orders']['merge_email_failed']);
            }
        }
        httpredir($merge_edit($keep));
    }

    // Orders were ticked on the first step
    if (count($ids) < 2) {
        $GLOBALS['main']->errorMessage($lang['orders']['merge_error_choose']);
        $anchor = $merge_ids(isset($_POST['merge']['anchor']) ? $_POST['merge']['anchor'] : array());
        httpredir($merge_url($ids ? $ids : $anchor));
    }
    httpredir($merge_url($ids, array('keep' => ($keep !== '') ? $keep : $merge_oldest($ids), 'review' => 1)));
}

$ids = array_slice($merge_ids(isset($_GET['oid']) ? $_GET['oid'] : array()), 0, 25);
// The order everything is measured against. Orders ticked in a list arrive in no useful
// order, so it is the one that most of the others can join, and the oldest of those.
$anchor = null;
$joined = -1;
foreach ($ids as $id) {
    if (($order = OrderMerge::order($id)) === null) {
        continue;
    }
    $join = 0;
    if (count($ids) > 1) {
        foreach (OrderMerge::candidates($id, 50) as $candidate) {
            $join += in_array((string)$candidate['cart_order_id'], $ids, true) ? 1 : 0;
        }
    }
    if ($anchor === null || $join > $joined || ($join === $joined && (int)$order['order_date'] < (int)$anchor['order_date'])) {
        $anchor = $order;
        $joined = $join;
    }
}
if (!$anchor) {
    $GLOBALS['main']->errorMessage($lang['orders']['merge_reason_missing']);
    httpredir('?_g=orders');
}

$merge_describe = function ($order, array $lines = array()) use ($merge_safe, $merge_money, $merge_edit) {
    $id = (string)$order['cart_order_id'];
    return array(
        'id'       => $id,
        'number'   => $merge_safe(OrderMerge::number($order)),
        'date'     => formatTime((int)$order['order_date'], false, true),
        'name'     => $merge_safe(trim($order['first_name'].' '.$order['last_name'])),
        'delivery' => $merge_safe(implode(', ', array_filter(array(trim($order['first_name_d'].' '.$order['last_name_d']), $order['line1_d'], $order['town_d'], $order['postcode_d']), 'strlen'))),
        'method'   => $merge_safe((string)$order['ship_method']),
        'gateway'  => $merge_safe(str_replace('_', ' ', (string)$order['gateway'])),
        'items'    => isset($lines[$id]) ? (int)$lines[$id]['quantity'] : 0,
        'shipping' => $merge_money($order['shipping']),
        'total'    => $merge_money($order['total']),
        'edit'     => $merge_edit($id),
    );
};

$GLOBALS['gui']->addBreadcrumb($lang['orders']['merge_title'], $merge_url(array($anchor['cart_order_id'])));
$GLOBALS['main']->addTabControl($lang['orders']['merge_title'], 'general');
$GLOBALS['smarty']->assign('MERGE_BACK', $merge_edit($anchor['cart_order_id']));

if (isset($_GET['review'])) {
    // Review: what merging these orders would do
    $keep_id = (isset($_GET['keep']) && is_scalar($_GET['keep']) && in_array((string)$_GET['keep'], $ids, true)) ? (string)$_GET['keep'] : $merge_oldest($ids);
    $plan    = ($keep_id !== '') ? OrderMerge::plan($keep_id, array_values(array_diff($ids, array($keep_id)))) : array('problem' => 'status');
    if ($plan['problem'] !== '') {
        // Nothing to merge after all: the first step says why for each order
        if ($plan['problem'] === 'none') {
            $GLOBALS['main']->errorMessage($lang['orders']['merge_error_choose']);
        }
        httpredir($merge_url($ids));
    }
    $keep     = $plan['keep'];
    $merging  = array();
    $numbers  = array($merge_safe(OrderMerge::number($keep)));
    $from_ids = array();
    $postage  = array($merge_money($keep['shipping']));
    $methods  = array();
    foreach ($plan['from'] as $order) {
        $merging[]  = $merge_describe($order, $plan['lines']);
        $numbers[]  = $merge_safe(OrderMerge::number($order));
        $from_ids[] = (string)$order['cart_order_id'];
        $postage[]  = $merge_money($order['shipping']);
        // Paying for a faster service on one order is worth knowing before they become one parcel
        if (trim((string)$order['ship_method']) !== '' && trim((string)$order['ship_method']) !== trim((string)$keep['ship_method'])) {
            $methods[] = sprintf($lang['orders']['merge_method_note'], $merge_safe(OrderMerge::number($order)), $merge_safe($order['ship_method']), (trim((string)$keep['ship_method']) !== '') ? $merge_safe($keep['ship_method']) : $lang['common']['null']);
        }
    }
    $refused = array();
    foreach ($plan['refused'] as $id => $why) {
        $refused[] = array(
            'number' => $why['order'] ? $merge_safe(OrderMerge::number($why['order'])) : $merge_safe($id),
            'edit'   => $why['order'] ? $merge_edit($id) : '',
            'reason' => $merge_reason($why['reason']),
        );
    }

    $totals = array();
    foreach (array('subtotal' => $lang['basket']['total_sub'], 'discount' => $lang['basket']['total_discount'], 'shipping' => $lang['basket']['shipping'], 'total_tax' => $lang['basket']['total_tax'], 'total' => $lang['basket']['total_grand']) as $field => $label) {
        if ($field === 'discount' && (float)$plan['after']['discount'] <= 0) {
            continue;
        }
        $totals[] = array('label' => $label, 'before' => $merge_money($plan['before'][$field]), 'after' => $merge_money($plan['after'][$field]), 'strong' => ($field === 'total'));
    }
    $moving = array();
    $moving[] = array('label' => $lang['orders']['merge_products'], 'value' => sprintf($lang['orders']['merge_products_value'], number_format($plan['moving']['quantity']), $numbers[0]));
    foreach (array('transactions', 'downloads', 'certificates') as $item) {
        if ($plan['moving'][$item] > 0) {
            $moving[] = array('label' => $lang['orders']['merge_'.$item], 'value' => number_format($plan['moving'][$item]));
        }
    }
    $moving[] = array('label' => $lang['basket']['shipping'], 'value' => sprintf($lang['orders']['merge_shipping_note'], implode(' + ', $postage), $merge_money($plan['after']['shipping'])));
    foreach ($methods as $text) {
        $moving[] = array('label' => $lang['orders']['shipping_method'], 'value' => $text);
    }
    $merged_numbers = array_slice($numbers, 1);
    $moving[] = array('label' => $lang['orders']['merge_cancelled'], 'value' => sprintf($lang['orders']['merge_cancelled_value'], implode(', ', $merged_numbers), $numbers[0]));

    $GLOBALS['smarty']->assign('MERGE_STEP', 'review');
    $GLOBALS['smarty']->assign('MERGE_KEEP', $merge_describe($keep, $plan['lines']));
    $GLOBALS['smarty']->assign('MERGE_FROM', $merging);
    $GLOBALS['smarty']->assign('MERGE_REFUSED', $refused);
    $GLOBALS['smarty']->assign('MERGE_TOTALS', $totals);
    $GLOBALS['smarty']->assign('MERGE_TOTALS_TITLE', sprintf($lang['orders']['merge_totals'], $numbers[0]));
    $GLOBALS['smarty']->assign('MERGE_MOVING', $moving);
    $GLOBALS['smarty']->assign('MERGE_ORDERS', array_merge(array((string)$keep['cart_order_id']), $from_ids));
    $GLOBALS['smarty']->assign('MERGE_NOTE', sprintf($lang['orders']['merge_customer_note_default'], implode(', ', $numbers), $numbers[0]));
    $GLOBALS['smarty']->assign('MERGE_EMAIL', $merge_safe($keep['email']));
    $GLOBALS['smarty']->assign('MERGE_WARNING', sprintf($lang['orders']['merge_warning'], implode(', ', $merged_numbers), $numbers[0]));
    $GLOBALS['smarty']->assign('MERGE_CHOOSE', $merge_url(array_merge(array((string)$keep['cart_order_id']), $from_ids)));
} else {
    // Choose: this customer's orders that can become one
    $rows    = array();
    $refused = array();
    $problem = '';
    if ((int)$anchor['status'] !== Order::ORDER_PROCESS) {
        $problem = sprintf($lang['orders']['merge_anchor_status'], $merge_safe(OrderMerge::number($anchor)));
    } else {
        $orders = array_merge(array($anchor), OrderMerge::candidates($anchor['cart_order_id'], 50));
        usort($orders, function ($a, $b) {
            return ((int)$a['order_date'] <=> (int)$b['order_date']) ?: strcmp((string)$a['cart_order_id'], (string)$b['cart_order_id']);
        });
        $listed = array();
        foreach ($orders as $order) {
            $listed[] = (string)$order['cart_order_id'];
        }
        $lines  = OrderMerge::lines($listed);
        // Ticked to begin with: whatever was asked for. Kept to begin with: the oldest of those.
        $ticked = array_values(array_intersect($listed, $ids));
        $keep   = $merge_oldest($ticked);
        foreach ($orders as $order) {
            $row           = $merge_describe($order, $lines);
            $row['ticked'] = in_array($row['id'], $ticked, true);
            $row['keep']   = ($row['id'] === $keep);
            $rows[]        = $row;
        }
        // Asked for, but not something that can join the first order
        foreach (array_diff($ids, $listed) as $id) {
            $order     = OrderMerge::order($id);
            $refused[] = array(
                'number' => $order ? $merge_safe(OrderMerge::number($order)) : $merge_safe($id),
                'edit'   => $order ? $merge_edit($id) : '',
                'reason' => $merge_reason(OrderMerge::problem($anchor, $order)),
            );
        }
        if (count($rows) < 2) {
            $problem = sprintf($lang['orders']['merge_none'], $merge_safe(OrderMerge::number($anchor)));
        }
    }
    $GLOBALS['smarty']->assign('MERGE_STEP', 'choose');
    $GLOBALS['smarty']->assign('MERGE_ROWS', $rows);
    $GLOBALS['smarty']->assign('MERGE_CAN_CHOOSE', count($rows) > 1);
    $GLOBALS['smarty']->assign('MERGE_REFUSED', $refused);
    $GLOBALS['smarty']->assign('MERGE_PROBLEM', $problem);
    $GLOBALS['smarty']->assign('MERGE_ANCHOR', $merge_describe($anchor));
}

$page_content = $GLOBALS['smarty']->fetch('templates/orders.merge.php');
