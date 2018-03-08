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
if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('customers', CC_PERM_READ, true);
if(isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $data = array();
    // Customer Account
    $data['customers'] = $GLOBALS['db']->select('CubeCart_customer', false, array('email' => $_POST['email']));
    // Orders
    $data['orders'] = $GLOBALS['db']->select('CubeCart_order_summary', false, array('email' => $_POST['email']));
    // Subscribers
    $data['subscribers'] = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', false, array('email' => $_POST['email']));
    // Reviews
    $data['reviews'] = $GLOBALS['db']->select('CubeCart_reviews', false, array('email' => $_POST['email']));
    foreach ($GLOBALS['hooks']->load('admin.customer.gdpr.list') as $hook) include $hook;
    foreach($data as $key => $row) {
        echo "<h1>$key</h1>";
        if(is_array($row)) {
            echo '<table cellspacing="0" cellpadding="3" border="1"><thead><tr>';
            foreach($row[0] as $key => $value) {
                echo "<th>".$key."</th>";
            }
            echo "</tr></thead><tbody>";
            foreach($row as $key => $value) {
                echo "<tr>";
                foreach($value as $col => $v) {
                    echo "<td>".$v."</td>";
                }
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "No data";
        }
    }
    exit;
}
$GLOBALS['main']->addTabControl($lang['search']['title_gdpr_report'], 'general');
$page_content = $GLOBALS['smarty']->fetch('templates/customers.gdpr.php');