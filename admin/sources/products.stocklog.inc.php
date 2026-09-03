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
Admin::getInstance()->permissions('products', CC_PERM_READ, true);

$pfx = $GLOBALS['config']->get('config', 'dbprefix');

// Filters live in the session so paging keeps them, matching the other log pages.
// A product_id in the query string is how the product page deep links in here.
if (isset($_POST['filter_submit'])) {
    foreach (array('q', 'source', 'date_from', 'date_to') as $field) {
        $value = isset($_POST[$field]) ? trim((string)$_POST[$field]) : '';
        if ($field === 'source' && !in_array($value, StockLog::sources(), true)) {
            $value = '';
        }
        if ($value === '') {
            $GLOBALS['session']->delete('stocklog_'.$field);
        } else {
            $GLOBALS['session']->set('stocklog_'.$field, $value);
        }
    }
    // A free text search replaces a pinned product, otherwise the two fight. Only
    // an actual search unpins: changing the source or dates must keep the product.
    if (isset($_POST['q']) && trim((string)$_POST['q']) !== '') {
        $GLOBALS['session']->delete('stocklog_product_id');
    }
}
if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
    $GLOBALS['session']->set('stocklog_product_id', (int)$_GET['product_id']);
    $GLOBALS['session']->delete('stocklog_q');
}
if (isset($_GET['reset_filter'])) {
    foreach (array('q', 'source', 'date_from', 'date_to', 'product_id') as $field) {
        $GLOBALS['session']->delete('stocklog_'.$field);
    }
    httpredir('?_g=products&node=stocklog');
}

$filter = array(
    'q'          => (string)$GLOBALS['session']->get('stocklog_q'),
    'source'     => (string)$GLOBALS['session']->get('stocklog_source'),
    'date_from'  => (string)$GLOBALS['session']->get('stocklog_date_from'),
    'date_to'    => (string)$GLOBALS['session']->get('stocklog_date_to'),
    'product_id' => (int)$GLOBALS['session']->get('stocklog_product_id'),
);

$where = array();
if ($filter['product_id'] > 0) {
    $where[] = 'L.`product_id` = '.$filter['product_id'];
} elseif ($filter['q'] !== '') {
    // Match the product by name or code, and let an order number match too, since
    // "what happened to order 123456" is the other way merchants come at this.
    $safe = $GLOBALS['db']->sqlSafe($filter['q']);
    $where[] = "(I.`name` LIKE '%".$safe."%' OR I.`product_code` LIKE '%".$safe."%' OR L.`cart_order_id` LIKE '%".$safe."%')";
}
if ($filter['source'] !== '') {
    $where[] = "L.`source` = '".$GLOBALS['db']->sqlSafe($filter['source'])."'";
}
// Dates are inclusive: the "to" day counts up to its last second.
if ($filter['date_from'] !== '' && ($from = strtotime($filter['date_from'].' 00:00:00')) !== false) {
    $where[] = 'L.`time` >= '.(int)$from;
}
if ($filter['date_to'] !== '' && ($to = strtotime($filter['date_to'].' 23:59:59')) !== false) {
    $where[] = 'L.`time` <= '.(int)$to;
}
$where_sql = !empty($where) ? 'WHERE '.implode(' AND ', $where) : '';

$per_page = $GLOBALS['main']->itemsPerPage('stocklog', $_GET['items'] ?? 0, 25);
$page     = (isset($_GET['page']) && is_numeric($_GET['page'])) ? max(1, (int)$_GET['page']) : 1;

$count_row = $GLOBALS['db']->misc(sprintf(
    'SELECT COUNT(*) AS `c` FROM `%1$sCubeCart_stock_log` AS L LEFT JOIN `%1$sCubeCart_inventory` AS I ON I.`product_id` = L.`product_id` %2$s',
    $pfx,
    $where_sql
), false);
$total = (is_array($count_row) && isset($count_row[0]['c'])) ? (int)$count_row[0]['c'] : 0;

$rows = $GLOBALS['db']->misc(sprintf(
    'SELECT L.*, I.`name` AS `product_name`, I.`product_code`, M.`cached_name` AS `variant`, A.`name` AS `admin_name`, A.`username` AS `admin_username`
     FROM `%1$sCubeCart_stock_log` AS L
     LEFT JOIN `%1$sCubeCart_inventory` AS I ON I.`product_id` = L.`product_id`
     LEFT JOIN `%1$sCubeCart_option_matrix` AS M ON M.`matrix_id` = L.`matrix_id`
     LEFT JOIN `%1$sCubeCart_admin_users` AS A ON A.`admin_id` = L.`admin_id`
     %2$s ORDER BY L.`time` DESC, L.`id` DESC LIMIT %3$d OFFSET %4$d',
    $pfx,
    $where_sql,
    (int)$per_page,
    ($page - 1) * (int)$per_page
), false);

$log = array();
if (is_array($rows)) {
    foreach ($rows as $row) {
        $change = (int)$row['change'];
        $log[] = array(
            'id'            => (int)$row['id'],
            'time'          => formatTime($row['time']),
            'product_id'    => (int)$row['product_id'],
            // A deleted product leaves its history behind, so fall back to the id.
            'product_name'  => ($row['product_name'] !== null) ? $row['product_name'] : sprintf($lang['catalogue']['stock_log_deleted_product'] ?? 'Deleted product #%1$s', (int)$row['product_id']),
            'product_code'  => (string)$row['product_code'],
            'product_gone'  => ($row['product_name'] === null),
            // cached_name carries markup for the product page; the log wants plain text.
            'variant'       => trim(strip_tags(str_replace(array('<br>', '<br />'), ', ', (string)$row['variant']))),
            'change'        => ($change > 0) ? '+'.$change : (string)$change,
            'change_class'  => ($change > 0) ? 'green' : 'red',
            'stock_after'   => ($row['stock_after'] === null) ? '&mdash;' : (int)$row['stock_after'],
            'source'        => (string)$row['source'],
            'source_label'  => isset($lang['catalogue']['stock_log_source_'.$row['source']]) ? $lang['catalogue']['stock_log_source_'.$row['source']] : (string)$row['source'],
            'cart_order_id' => (string)$row['cart_order_id'],
            'admin'         => ($row['admin_name'] !== null) ? $row['admin_name'] : (($row['admin_username'] !== null) ? $row['admin_username'] : ''),
            'note'          => (string)$row['note'],
        );
    }
}

if (!empty($log)) {
    $GLOBALS['smarty']->assign('STOCK_LOG', $log);
}

$source_options = array(array('value' => '', 'label' => '--'));
foreach (StockLog::sources() as $source) {
    $source_options[] = array(
        'value' => $source,
        'label' => isset($lang['catalogue']['stock_log_source_'.$source]) ? $lang['catalogue']['stock_log_source_'.$source] : $source,
    );
}
$GLOBALS['smarty']->assign('SOURCE_OPTIONS', $source_options);

// Show which product is pinned when the filter came in as a deep link.
if ($filter['product_id'] > 0) {
    $pinned = $GLOBALS['db']->select('CubeCart_inventory', array('name'), array('product_id' => $filter['product_id']));
    $filter['product_name'] = ($pinned) ? $pinned[0]['name'] : sprintf($lang['catalogue']['stock_log_deleted_product'] ?? 'Deleted product #%1$s', $filter['product_id']);
}
$filter['active'] = ($filter['q'] !== '' || $filter['source'] !== '' || $filter['date_from'] !== '' || $filter['date_to'] !== '' || $filter['product_id'] > 0);

$GLOBALS['smarty']->assign('FILTER', $filter);
$GLOBALS['smarty']->assign('PAGINATION', $GLOBALS['db']->pagination($total, $per_page, $page, 5, 'page', 'stock_log'));

$GLOBALS['main']->addTabControl($lang['catalogue']['stock_log_title'], 'stock_log');
$GLOBALS['gui']->addBreadcrumb($lang['catalogue']['stock_log_title'], currentPage());

$page_content = $GLOBALS['smarty']->fetch('templates/products.stocklog.php');
