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
Admin::getInstance()->permissions('customers', CC_PERM_READ, true);
$del_cid = array();

## Delete customers with order older than x months
if (isset($_POST['customer_purge']) && ctype_digit($_POST['customer_purge'])) {
    if ($purge_customers = $GLOBALS['db']->select('CubeCart_order_summary', "DISTINCT `customer_id`", "`order_date` < ".strtotime("-".(string)$_POST['customer_purge']." month"))) {
        foreach ($purge_customers as $purge_customer) {
            $del_cid[] = $purge_customer['customer_id'];
        }
    }
    if (count($del_cid) > 0) {
        $GLOBALS['main']->successMessage(sprintf($lang['customer']['purge_success'], $_POST['customer_purge']));
    } else {
        $GLOBALS['main']->errorMessage($lang['customer']['purge_fail']);
    }
}

## Delete customers with no orders
if (isset($_POST['no_order_purge'])) {
    if ($purge_customers = $GLOBALS['db']->misc('SELECT DISTINCT `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_customer`.`customer_id` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_customer` LEFT JOIN `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary` ON `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary`.`customer_id` = `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_customer`.`customer_id` WHERE `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary`.`customer_id` IS NULL')) {
        foreach ($purge_customers as $purge_customer) {
            $del_cid[] = $purge_customer['customer_id'];
        }
    }
    if (count($del_cid) > 0) {
        $GLOBALS['main']->successMessage(sprintf($lang['customer']['no_order_purge']));
    } else {
        $GLOBALS['main']->errorMessage($lang['customer']['purge_fail']);
    }
}

## Delete guest accounts
if (isset($_POST['delete_guests'])) {
    if ($purge_customers = $GLOBALS['db']->select('CubeCart_customer', 'customer_id', array('type' => 2))) {
        foreach ($purge_customers as $purge_customer) {
            $del_cid[] = $purge_customer['customer_id'];
        }
    }
    if (count($del_cid) > 0) {
        $GLOBALS['main']->successMessage(sprintf($lang['customer']['delete_guests_success']));
    } else {
        $GLOBALS['main']->errorMessage($lang['customer']['delete_guests_fail']);
    }
}

if (count($del_cid)>0) {
    foreach ($del_cid as $cid) {
        $GLOBALS['db']->delete('CubeCart_customer', array('customer_id' => $cid));
        $GLOBALS['db']->delete('CubeCart_addressbook', array('customer_id' => $cid));
        $GLOBALS['db']->delete('CubeCart_customer_membership', array('customer_id' => $cid));
        $GLOBALS['db']->delete('CubeCart_newsletter_subscriber', array('customer_id' => $cid));
        foreach ($GLOBALS['hooks']->load('admin.customer.delete') as $hook) {
            include $hook;
        }
    }
    httpredir('?_g=customers');
}

if (isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
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
    foreach ($GLOBALS['hooks']->load('admin.customer.gdpr.list') as $hook) {
        include $hook;
    }
    $excluded = array(
        'customers.new_password',
        'customers.password',
        'customers.salt',
        'customers.verify',
        'customers.status',
        'customers.type',
        'orders.offline_capture',
        'orders.basket',
        'orders.dashboard',
        'orders.discount_type',
        'subscribers.validation',
        'subscribers.subscriber_id',
        'subscribers.customer_id',
        'subscribers.status',
        'subscribers.imported',
        'subscribers.dbl_opt',
        'email.email_content_id',
        'email.fail_reason',
        'email.result',
        'email.id'
    );
    foreach ($data as $type => $data) {
        echo "<h1>$type</h1>";
        if (is_array($data)) {
            echo '<table cellspacing="0" cellpadding="3" border="1"><thead><tr>';
            foreach ($data[0] as $col_name => $value) {
                if(in_array($type.'.'.$col_name, $excluded)) continue;
                echo "<th>".$col_name."</th>";
            }
            echo "</tr></thead><tbody>";
            foreach ($data as $k => $value) {
                echo "<tr>";
                foreach ($value as $col => $v) {
                    if(in_array($type.'.'.$col, $excluded)) continue;
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
