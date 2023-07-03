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
Admin::getInstance()->permissions('settings', CC_PERM_READ, true);
$GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->statistics['product_stats']);
$GLOBALS['main']->addTabControl($GLOBALS['language']->statistics['product_stats'], 'general');

$product = $GLOBALS['catalogue']->getProductData((int)$_GET['product_id']);
if($product) {
    $master_image = isset($_GET['product_id']) ? $GLOBALS['gui']->getProductImage((int)$_GET['product_id']) : '';
    $product['image'] = $master_image;

    $join = "`CubeCart_order_inventory` AS `I` INNER JOIN `CubeCart_order_summary` AS `S` ON `I`.`cart_order_id` = `S`.`cart_order_id`";
    $columns = '`S`.`order_date`, `S`.`id`, `I`.`quantity`';
    $where = '`I`.`product_id` = '.(string)$_GET['product_id'].' AND `S`.`status` IN(2, 3)';

    $first_sale = $GLOBALS['db']->select($join, $columns, $where, '`S`.`order_date` ASC', 1);
    $last_sale = $GLOBALS['db']->select($join, $columns, $where, '`S`.`order_date` DESC', 1);
    $all_sales = $GLOBALS['db']->select($join, $columns, $where);

    function secondsToTime($seconds) {
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        return $dtF->diff($dtT)->format($GLOBALS['language']->statistics['dhms']);
    }
    $ids = array();
    $total_sales = 0;
    $total_orders = 0;
    foreach($all_sales as $s) {
        array_push($ids, $s['id']);
        $total_sales += (int)$s['quantity'];
        $total_orders++;
    }
    $product['date_added'] = formatTime(strtotime($product['date_added']));
    $product['updated'] = formatTime(strtotime($product['updated']));

    $data = array(
        'first_sale' => !$first_sale ? '-' : formatTime($first_sale[0]['order_date']),
        'last_sale' => !$last_sale ? '-' : formatTime($last_sale[0]['order_date']),
        'total_sales' => $total_sales,
        'total_orders' => $total_orders,
        'avg_per_order' => ($total_orders > 0) ? round($total_sales/$total_orders, 1) : 0,
        'order_ids' => urlencode(implode(',',$ids)),
        'sale_interval' => is_array($all_sales) ? secondsToTime(ceil((time() - strtotime($product['date_added'])) / count($all_sales))) : '-'
    );

    $GLOBALS['smarty']->assign('PRODUCT', array_merge($product, $data));
    
    $per_page = 25;
    $page  = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
    
    $customers = $GLOBALS['db']->query('SELECT `C`.`customer_id`, `C`.`first_name`, `C`.`last_name`, `C`.`email`, SUM(`I`.`quantity`) AS `purchases` FROM `'.$glob['dbprefix'].'CubeCart_order_inventory` AS `I` INNER JOIN `'.$glob['dbprefix'].'CubeCart_order_summary` AS `S` ON `I`.`cart_order_id` = `S`.`cart_order_id` INNER JOIN `'.$glob['dbprefix'].'CubeCart_customer` AS `C` ON `S`.`customer_id` = `C`.`customer_id` WHERE `S`.`status` IN(2,3) AND`I`.`product_id` = '.(int)$_GET['product_id'].' GROUP BY `S`.`customer_id` ORDER BY SUM(`I`.`quantity`) DESC', $per_page, $page);
    
    $GLOBALS['smarty']->assign('CUSTOMERS', $customers);
    $GLOBALS['smarty']->assign('PAGINATION', $GLOBALS['db']->pagination(false, $per_page, $page, 5, 'page'));

} else {
    $GLOBALS['smarty']->assign('PRODUCT', false);
}

$page_content = $GLOBALS['smarty']->fetch('templates/statistics.product.php');
?>