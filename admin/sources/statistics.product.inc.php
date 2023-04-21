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
$GLOBALS['gui']->addBreadcrumb('Product');
$GLOBALS['main']->addTabControl('Product', 'general');

$product = $GLOBALS['catalogue']->getProductData((int)$_GET['product_id']);
if($product) {
    $master_image = isset($_GET['product_id']) ? $GLOBALS['gui']->getProductImage((int)$_GET['product_id']) : '';
    $product['image'] = $master_image;

    $join = "`CubeCart_order_inventory` AS `I` INNER JOIN `CubeCart_order_summary` AS `S` ON `I`.`cart_order_id` = `S`.`Cart_order_id`";
    $columns = 'order_date';
    $where = '`I`.`product_id` = '.(string)$_GET['product_id'].' AND `S`.`status` IN(2, 3)';

    $first_sale = $GLOBALS['db']->select($join, $columns, $where, '`S`.`order_date` ASC', 1);
    $last_sale = $GLOBALS['db']->select($join, $columns, $where, '`S`.`order_date` DESC', 1);
    $all_sales = $GLOBALS['db']->select($join, $columns, $where);

    function secondsToTime($seconds) {
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        return $dtF->diff($dtT)->format($GLOBALS['language']->statistics['dhms']);
    }
    $product['date_added'] = formatTime(strtotime($product['date_added']));
    $product['updated'] = formatTime(strtotime($product['updated']));
    $data = array(
        'first_sale' => !$first_sale ? '-' : formatTime($first_sale[0]['order_date']),
        'last_sale' => !$last_sale ? '-' : formatTime($last_sale[0]['order_date']),
        'total_sales' => is_array($all_sales) ? count($all_sales) : 0,
        'sale_interval' => is_array($all_sales) ? secondsToTime(ceil((time() - strtotime($product['date_added'])) / count($all_sales))) : '-'
    );

    $GLOBALS['smarty']->assign('PRODUCT', array_merge($product, $data));
} else {
    $GLOBALS['smarty']->assign('PRODUCT', false);
}

$page_content = $GLOBALS['smarty']->fetch('templates/statistics.product.php');
?>