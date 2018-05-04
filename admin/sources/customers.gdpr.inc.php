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
$del_cid = array();

## Delete customers with order older than x months
if(isset($_POST['customer_purge']) && ctype_digit($_POST['customer_purge'])) {
    if($purge_customers = $GLOBALS['db']->select('CubeCart_order_summary', "DISTINCT `customer_id`", "`order_date` < ".strtotime("-".(string)$_POST['customer_purge']." month"))) {
        foreach($purge_customers as $purge_customer) {
            $del_cid[] = $purge_customer['customer_id'];
        }
    }
    if(count($del_cid) > 0) {
        $GLOBALS['main']->setACPNotify(sprintf($lang['customer']['purge_success'], $_POST['customer_purge']));
    } else {
        $GLOBALS['main']->setACPWarning($lang['customer']['purge_fail']);
    }
}

## Delete customers with no orders
if(isset($_POST['no_order_purge'])) {
    if($purge_customers = $GLOBALS['db']->misc('SELECT DISTINCT `CubeCart_customer`.`customer_id` FROM `CubeCart_customer` LEFT JOIN `CubeCart_order_summary` ON `CubeCart_order_summary`.`customer_id` = `CubeCart_customer`.`customer_id` WHERE `CubeCart_order_summary`.`customer_id` IS NULL')) {
        foreach($purge_customers as $purge_customer) {
            $del_cid[] = $purge_customer['customer_id'];
        }
    }
    if(count($del_cid) > 0) {
        $GLOBALS['main']->setACPNotify(sprintf($lang['customer']['no_order_purge']));
    } else {
        $GLOBALS['main']->setACPWarning($lang['customer']['purge_fail']);
    }
}

if(count($del_cid)>0) {
    foreach($del_cid as $cid) {
        $GLOBALS['db']->delete('CubeCart_customer', array('customer_id' => $cid));
        $GLOBALS['db']->delete('CubeCart_addressbook', array('customer_id' => $cid));
        $GLOBALS['db']->delete('CubeCart_customer_membership', array('customer_id' => $cid));
        $GLOBALS['db']->delete('CubeCart_newsletter_subscriber', array('customer_id' => $cid));
        foreach ($GLOBALS['hooks']->load('admin.customer.delete') as $hook) include $hook;
    }
    httpredir('?_g=customers');
}

if(isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    echo "<html><head><title>GDPR Report - ".$_POST['email']."</title></head><body>";
    $data = array();
    // Subscription consent Log
    $data['consent'] = $GLOBALS['db']->select('CubeCart_newsletter_subscriber_log', false, array('email' => $_POST['email']));
    // Customer Account
    $data['customers'] = $GLOBALS['db']->select('CubeCart_customer', false, array('email' => $_POST['email']));
    // Orders
    $data['orders'] = $GLOBALS['db']->select('CubeCart_order_summary', false, array('email' => $_POST['email']));
    // Subscribers
    $data['subscribers'] = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', false, array('email' => $_POST['email']));
    // Reviews
    $data['reviews'] = $GLOBALS['db']->select('CubeCart_reviews', false, array('email' => $_POST['email']));
    // Email Log
    $data['email'] = $GLOBALS['db']->select('CubeCart_email_log', false, array('to' => $_POST['email']));
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
    echo "</body></html>";
    exit;
}
$GLOBALS['main']->addTabControl($lang['search']['gdpr_tools'], 'general');
$page_content = $GLOBALS['smarty']->fetch('templates/customers.gdpr.php');