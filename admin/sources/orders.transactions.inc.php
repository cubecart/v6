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
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}
Admin::getInstance()->permissions('orders', CC_PERM_READ, true);
$GLOBALS['main']->addTabControl($GLOBALS['language']->orders['title_transaction_logs'], 'logs');
$GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->orders['title_transaction_logs']);

if (isset($_POST['search'])) {
    httpredir(currentPage(null, array('search' => $_POST['search'])));
}

$per_page = 20;
$page  = (isset($_GET['page'])) ? $_GET['page'] : 1;
$oid_col = $GLOBALS['config']->get('config', 'oid_mode') =='i' ?  $GLOBALS['config']->get('config', 'oid_col') : 'order_id';
$table_join = '`'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_transactions` AS `T` INNER JOIN `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary` AS `S` ON `T`.`order_id` = `S`.`cart_order_id`';

if (isset($_GET['order_id'])) {
    $transactions = $GLOBALS['db']->select($table_join, '`T`.*, `S`.`id`, `S`.`custom_oid`', '`T`.`order_id` = "'.$_GET['order_id'].'"', array('time' => 'DESC'));
    if ($transactions) {
        $oid = $transactions[0][$oid_col];
        foreach ($transactions as $transaction) {
            $transaction['time']  = formatTime($transaction['time']);
            $transaction['amount']  = Tax::getInstance()->priceFormat($transaction['amount']);
            $transaction['trans_id'] = empty($transaction['trans_id']) ? $GLOBALS['lang']['common']['null'] : $transaction['trans_id'];
            $smarty_data['transactions'][] = $transaction;
        }
        $GLOBALS['smarty']->assign('ORDER_TRANSACTIONS', $smarty_data['transactions']);
    } else {
        if ($oid = $GLOBALS['db']->select('CubeCart_order_summary', array('id','custom_oid','cart_order_id'), array('cart_order_id' => $_GET['order_id']))) {
            $oid = $oid[0][$oid_col];
        } else {
            $oid = $_GET['order_id'];
        }
    }
    $GLOBALS['smarty']->assign('TRANSACTION_LOGS_TITLE', sprintf($GLOBALS['lang']['orders']['title_transaction_logs_for_order'], $oid));
    $GLOBALS['gui']->addBreadcrumb($oid, currentPage());
} else {
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        if (Order::validOrderId($_GET['search'])) {
            $where[$oid_col] = $_GET['search'];
        } else {
            $where = "`trans_id` LIKE '%".$_GET['search']."%' OR `amount` LIKE '%".$_GET['search']."%' OR `gateway` LIKE '%".$_GET['search']."%'";
        }
    } else {
        $where = false;
    }
    if (($count_rows = $GLOBALS['db']->select('CubeCart_transactions', array('DISTINCT' => 'order_id'), $where)) !== false) {
        $count = count($count_rows);
        if ($count > $per_page) {
            $GLOBALS['smarty']->assign('PAGINATION', $GLOBALS['db']->pagination($count, $per_page, $page, 9));
        }
    }

    if (!isset($_GET['sort']) || !is_array($_GET['sort'])) {
        $_GET['sort'] = array('time' => 'DESC');
    }
    $current_page = currentPage(array('sort'));
    $thead_sort = array(
        'cart_order_id' => $GLOBALS['db']->column_sort($oid_col, $GLOBALS['language']->orders['order_number'], 'sort', $current_page, $_GET['sort']),
        'amount'  => $GLOBALS['db']->column_sort('amount', $GLOBALS['language']->basket['total'], 'sort', $current_page, $_GET['sort']),
        'gateway'   => $GLOBALS['db']->column_sort('gateway', $GLOBALS['language']->orders['gateway_name'], 'sort', $current_page, $_GET['sort']),
        'date'    => $GLOBALS['db']->column_sort('time', $GLOBALS['language']->common['date'], 'sort', $current_page, $_GET['sort'])
    );

    foreach ($GLOBALS['hooks']->load('admin.order.transactions.table_head_sort') as $hook) {
        include $hook;
    }

    $GLOBALS['smarty']->assign('THEAD', $thead_sort);
    foreach ($_GET['sort'] as $key => $value) {
        $sort = "`T`.`$key` $value";
        break;
    }

    if (($transactions = $GLOBALS['db']->select($table_join, "DISTINCT `T`.`order_id`, `T`.`time`, `T`.`amount`, `T`.`gateway`, `T`.`trans_id`, `S`.`id`, `S`.`custom_oid`, `S`.`cart_order_id`", $where, $sort, $per_page, $page)) !== false) {
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $GLOBALS['main']->successMessage(sprintf($GLOBALS['language']->orders['notify_search_logs'], $_GET['search']));
        }
        foreach ($transactions as $transaction) {
            if (!empty($transaction['order_id'])) {
                $transaction['time'] = formatTime($transaction['time']);
                $transaction['amount'] = Tax::getInstance()->priceFormat($transaction['amount']);
                $transaction['link'] = currentPage(array('page', 'sort'), array('order_id' => $transaction['order_id']));
                $smarty_data['transactions'][] = $transaction;
            }
        }
        $GLOBALS['smarty']->assign('ALL_TRANSACTIONS', $smarty_data['transactions']);
    } elseif (isset($_GET['search']) && !empty($_GET['search']) && !$transactions) {
        $GLOBALS['gui']->setError(sprintf($GLOBALS['language']->orders['error_search_logs'], $_GET['search']));
    }
    $GLOBALS['smarty']->assign('DISPLAY_ALL_TRANSACTIONS', true);
}
$page_content = $GLOBALS['smarty']->fetch('templates/orders.transactions.php');
