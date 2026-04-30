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
Admin::getInstance()->permissions('statistics', CC_PERM_READ, true);
$GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->statistics['product_stats']);
$GLOBALS['main']->addTabControl($GLOBALS['language']->statistics['product_stats'], 'general');

if (!function_exists('secondsToTime')) {
    function secondsToTime($seconds) {
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        return $dtF->diff($dtT)->format($GLOBALS['language']->statistics['dhms']);
    }
}

$product_id = (int)$_GET['product_id'];
$product = $GLOBALS['catalogue']->getProductData($product_id, 1, false, 10, 1, false, null, false);
if($product) {
    $master_image = $GLOBALS['gui']->getProductImage($product_id);
    $product['image'] = $master_image;

    $prefix = $GLOBALS['config']->get('config', 'dbprefix');
    $tax = Tax::getInstance();

    $sales_statuses = Order::ORDER_PROCESS.','.Order::ORDER_COMPLETE;
    $lost_statuses  = Order::ORDER_DECLINED.','.Order::ORDER_CANCELLED;

    $redirect = '?_g=statistics&node=product&product_id='.$product_id;

    $range_presets = array('7d' => 7, '30d' => 30, '90d' => 90);
    $range = isset($_REQUEST['range']) ? (string)$_REQUEST['range'] : '';
    $has_explicit_dates = !empty($_REQUEST['from']) && !empty($_REQUEST['to']);
    $is_custom = false;

    $period_from = null;
    $period_to = null;
    $where_date = '';

    if($has_explicit_dates) {
        $is_custom = true;
        $from_parts = explode('-', (string)$_REQUEST['from']);
        $to_parts = explode('-', (string)$_REQUEST['to']);

        if(count($from_parts) !== 3 || !checkdate((int)$from_parts[1], (int)$from_parts[2], (int)$from_parts[0])) {
            $GLOBALS['main']->errorMessage($GLOBALS['language']->statistics['invalid_date_from']);
            httpredir($redirect);
            exit;
        }
        if(count($to_parts) !== 3 || !checkdate((int)$to_parts[1], (int)$to_parts[2], (int)$to_parts[0])) {
            $GLOBALS['main']->errorMessage($GLOBALS['language']->statistics['invalid_date_to']);
            httpredir($redirect);
            exit;
        }

        $period_from = strtotime($_REQUEST['from']);
        $period_to = strtotime($_REQUEST['to'].' 23:59:59');

        if($period_from >= $period_to) {
            $GLOBALS['main']->errorMessage($GLOBALS['language']->statistics['date_range_error']);
            httpredir($redirect);
            exit;
        }
        $where_date = ' AND (`S`.`order_date` BETWEEN '.$period_from.' AND '.$period_to.')';
        $range = 'custom';
    } elseif($range === 'all') {
        // no date filter
    } else {
        // default preset window
        if(!isset($range_presets[$range])) {
            $range = '30d';
        }
        $days = $range_presets[$range];
        $period_to = strtotime(date('Y-m-d').' 23:59:59');
        $period_from = strtotime(date('Y-m-d', strtotime('-'.$days.' days')));
        $where_date = ' AND (`S`.`order_date` BETWEEN '.$period_from.' AND '.$period_to.')';
    }
    $GLOBALS['smarty']->assign('RANGE', $range);
    $GLOBALS['smarty']->assign('IS_CUSTOM', $is_custom);

    // Single aggregate query for completed sales in window
    $base_join = '`'.$prefix.'CubeCart_order_inventory` AS `I` INNER JOIN `'.$prefix.'CubeCart_order_summary` AS `S` ON `I`.`cart_order_id` = `S`.`cart_order_id`';
    $sales_cols = 'MIN(`S`.`order_date`) AS `first_sale`, MAX(`S`.`order_date`) AS `last_sale`, COALESCE(SUM(`I`.`quantity`),0) AS `units`, COUNT(DISTINCT `S`.`cart_order_id`) AS `orders`, COALESCE(SUM(`I`.`price` * `I`.`quantity`),0) AS `revenue`, GROUP_CONCAT(DISTINCT `S`.`id`) AS `order_ids`';
    $sales_where = '`I`.`product_id` = '.$product_id.' AND `S`.`status` IN ('.$sales_statuses.')'.$where_date;
    $sales = $GLOBALS['db']->select($base_join, $sales_cols, $sales_where);
    $sales = $sales ? $sales[0] : array('first_sale'=>null,'last_sale'=>null,'units'=>0,'orders'=>0,'revenue'=>0,'order_ids'=>'');

    // Lost (refunded / cancelled) in same window
    $lost_where = '`I`.`product_id` = '.$product_id.' AND `S`.`status` IN ('.$lost_statuses.')'.$where_date;
    $lost = $GLOBALS['db']->select($base_join, 'COALESCE(SUM(`I`.`quantity`),0) AS `units`, COUNT(DISTINCT `S`.`cart_order_id`) AS `orders`, COALESCE(SUM(`I`.`price` * `I`.`quantity`),0) AS `revenue`', $lost_where);
    $lost = $lost ? $lost[0] : array('units'=>0,'orders'=>0,'revenue'=>0);

    // Prior period of equal length, only when a date filter is active
    $prior_units = null;
    $prior_revenue = null;
    if($period_from !== null && $period_to !== null) {
        $span = $period_to - $period_from;
        $prior_to = $period_from - 1;
        $prior_from = $prior_to - $span;
        $prior_where = '`I`.`product_id` = '.$product_id.' AND `S`.`status` IN ('.$sales_statuses.') AND (`S`.`order_date` BETWEEN '.$prior_from.' AND '.$prior_to.')';
        $prior = $GLOBALS['db']->select($base_join, 'COALESCE(SUM(`I`.`quantity`),0) AS `units`, COALESCE(SUM(`I`.`price` * `I`.`quantity`),0) AS `revenue`', $prior_where);
        if($prior) {
            $prior_units = (int)$prior[0]['units'];
            $prior_revenue = (float)$prior[0]['revenue'];
        }
    }

    // Top variants (only when product has options recorded)
    $variants_sql = 'SELECT `I`.`options_identifier`, SUM(`I`.`quantity`) AS `units`, SUM(`I`.`price` * `I`.`quantity`) AS `revenue` FROM '.$base_join.' WHERE '.$sales_where.' AND `I`.`options_identifier` IS NOT NULL AND `I`.`options_identifier` <> \'\' GROUP BY `I`.`options_identifier` ORDER BY `units` DESC LIMIT 5';
    $variants = $GLOBALS['db']->query($variants_sql);
    if($variants) {
        $matrix_lookup = $GLOBALS['db']->select('CubeCart_option_matrix', array('options_identifier','product_code'), array('product_id' => $product_id), false, false, false, false);
        $matrix_codes = array();
        if($matrix_lookup) {
            foreach($matrix_lookup as $m) {
                $matrix_codes[$m['options_identifier']] = $m['product_code'];
            }
        }
        foreach($variants as &$v) {
            $v['product_code'] = isset($matrix_codes[$v['options_identifier']]) ? $matrix_codes[$v['options_identifier']] : $v['options_identifier'];
            $v['revenue_fmt'] = $tax->priceFormat((float)$v['revenue']);
        }
        unset($v);
    }
    $GLOBALS['smarty']->assign('VARIANTS', $variants);

    // Days of stock remaining
    $days_of_stock = null;
    if(!empty($product['use_stock_level']) && (int)$sales['units'] > 0) {
        $period_seconds = ($period_from !== null && $period_to !== null) ? ($period_to - $period_from) : (time() - strtotime($product['date_added']));
        $period_days = max(1, $period_seconds / 86400);
        $daily_rate = (int)$sales['units'] / $period_days;
        if($daily_rate > 0 && isset($product['stock_level'])) {
            $days_of_stock = (int)floor((int)$product['stock_level'] / $daily_rate);
        }
    }

    $sale_interval = '-';
    if((int)$sales['orders'] > 1 && $sales['first_sale'] && $sales['last_sale']) {
        $sale_interval = secondsToTime((int)ceil(((int)$sales['last_sale'] - (int)$sales['first_sale']) / ((int)$sales['orders'] - 1)));
    }

    if($period_from !== null && $period_to !== null) {
        $from_default = date('Y-m-d', $period_from);
        $to_default   = date('Y-m-d', $period_to);
    } else {
        $from_default = $sales['first_sale'] ? date('Y-m-d', $sales['first_sale']) : date('Y-m-d');
        $to_default   = date('Y-m-d');
    }
    $GLOBALS['smarty']->assign('FROM_DATE', $from_default);
    $GLOBALS['smarty']->assign('TO_DATE', $to_default);

    $cost_price = isset($product['cost_price']) ? (float)$product['cost_price'] : 0;
    $profit = $cost_price > 0 ? (float)$sales['revenue'] - ($cost_price * (int)$sales['units']) : null;

    $data = array(
        'date_added_fmt' => formatTime(strtotime($product['date_added'])),
        'updated_fmt'    => !empty($product['updated']) ? formatTime(strtotime($product['updated'])) : '-',
        'first_sale'     => $sales['first_sale'] ? formatTime($sales['first_sale']) : '-',
        'last_sale'      => $sales['last_sale']  ? formatTime($sales['last_sale'])  : '-',
        'total_sales'    => (int)$sales['units'],
        'total_orders'   => (int)$sales['orders'],
        'avg_per_order'  => ((int)$sales['orders'] > 0) ? round((int)$sales['units'] / (int)$sales['orders'], 1) : 0,
        'order_ids'      => $sales['order_ids'] ? urlencode($sales['order_ids']) : '',
        'revenue'        => $tax->priceFormat((float)$sales['revenue']),
        'profit'         => $profit !== null ? $tax->priceFormat($profit) : null,
        'lost_units'     => (int)$lost['units'],
        'lost_orders'    => (int)$lost['orders'],
        'lost_revenue'   => $tax->priceFormat((float)$lost['revenue']),
        'prior_units'    => $prior_units,
        'prior_revenue'  => $prior_revenue,
        'units_delta_pct'   => ($prior_units !== null && $prior_units > 0) ? round((((int)$sales['units'] - $prior_units) / $prior_units) * 100, 1) : null,
        'revenue_delta_pct' => ($prior_revenue !== null && $prior_revenue > 0) ? round(((float)$sales['revenue'] - $prior_revenue) / $prior_revenue * 100, 1) : null,
        'days_of_stock'  => $days_of_stock,
        'stock_level'    => isset($product['stock_level']) ? (int)$product['stock_level'] : null,
        'sale_interval'  => $sale_interval,
    );

    $GLOBALS['smarty']->assign('PRODUCT', array_merge($product, $data));

    $per_page = 25;
    $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;
    $query = 'SELECT `C`.`customer_id`, `C`.`first_name`, `C`.`last_name`, `C`.`email`, SUM(`I`.`quantity`) AS `purchases` FROM `'.$prefix.'CubeCart_order_inventory` AS `I` INNER JOIN `'.$prefix.'CubeCart_order_summary` AS `S` ON `I`.`cart_order_id` = `S`.`cart_order_id` INNER JOIN `'.$prefix.'CubeCart_customer` AS `C` ON `S`.`customer_id` = `C`.`customer_id` WHERE `S`.`status` IN('.$sales_statuses.') AND `I`.`product_id` = '.$product_id.$where_date.' GROUP BY `C`.`customer_id`, `C`.`first_name`, `C`.`last_name`, `C`.`email` ORDER BY SUM(`I`.`quantity`) DESC';
    $customers = $GLOBALS['db']->query($query, $per_page, $page);

    $GLOBALS['smarty']->assign('CUSTOMERS', $customers);
    $GLOBALS['smarty']->assign('PAGINATION', $GLOBALS['db']->pagination(false, $per_page, $page, 5, 'page'));

} else {
    $GLOBALS['smarty']->assign('PRODUCT', false);
}

$page_content = $GLOBALS['smarty']->fetch('templates/statistics.product.php');
?>