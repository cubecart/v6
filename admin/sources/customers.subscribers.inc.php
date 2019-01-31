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

global $lang;

if (isset($_GET['reset']) && !empty($_GET['reset'])) {
    $GLOBALS['session']->delete('email_filter');
    httpredir('?_g=customers&node=subscribers');
}

if (isset($GLOBALS['RAW']['POST']['maillist_format'])) {
    if (empty($GLOBALS['RAW']['POST']['maillist_format'])) {
        $GLOBALS['RAW']['POST']['maillist_format'] = '{$EMAIL_ADDRESS}';
    }
    $where = array('status' => 1);
    if (isset($_POST['export_dbl_opt']) && $_POST['export_dbl_opt']=='1') {
        $where['dbl_opt'] = 1;
    }
    if (($maillist = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', array('customer_id', 'email'), $where)) !== false) {
        // Set initial variables
        $file_data = null;
        $find  = array(
            '{$EMAIL_ADDRESS}',
            '{$FULL_NAME_LONG}',
            '{$FULL_NAME_SHORT}',
            '{$TITLE}',
            '{$FIRST_NAME}',
            '{$LAST_NAME}'
        );
        // Loop through
        foreach ($maillist as $member) {
            if ($member['customer_id']) {
                $customer = $GLOBALS['db']->select('CubeCart_customer', array('title', 'first_name', 'last_name'), array('customer_id' => $member['customer_id']));
                if ($customer) {
                    $member = array_merge($member, $customer[0]);
                    if (!empty($member['title'])) {
                        $long_name[]  = $member['title'];
                    }
                    if (!empty($member['first_name'])) {
                        $long_name[]  = $member['first_name'];
                        $short_name[]  = $member['first_name'];
                    }
                    if (!empty($member['last_name'])) {
                        $long_name[]  = $member['last_name'];
                        $short_name[]  = $member['last_name'];
                    }
                    $member['long_name'] = implode(' ', $long_name);
                    $member['short_name'] = implode(' ', $short_name);
                }
            }

            $replace  = array(
                $member['email'],
                $member['long_name'],
                $member['short_name'],
                $member['title'],
                $member['first_name'],
                $member['last_name']
            );
            /* Start Fixing Bug 2884 */
            $sep = $_POST['maillist_extension']=="txt" ? "," : "\n";
            $file_data .= str_replace($find, $replace, $GLOBALS['RAW']['POST']['maillist_format']).$sep;
            /* End Fixing Bug 2884 */
            unset($customer, $replace, $member, $long_name, $short_name);
        }
        $GLOBALS['debug']->supress(true);
        $file_data = preg_replace('#\{.*?\}#s', '', $file_data);
        $file_data = rtrim($file_data, $sep);
        deliverFile(false, false, $file_data, $lang['email']['export_filename'].'.'.$_POST['maillist_extension']);
        exit;
    } else {
        $GLOBALS['main']->errorMessage($lang['email']['error_news_export_empty']);
    }
}

$redirect = false;

if (isset($_GET['del_single_opt'])) {
    $redirect = true;
    $GLOBALS['db']->delete('CubeCart_newsletter_subscriber', array('dbl_opt' => '0'));
}

if (isset($_GET['delete_log']) && !empty($_GET['delete_log'])) {
    if ($redirect = $GLOBALS['db']->delete('CubeCart_newsletter_subscriber_log', array('email' => $_GET['delete_log']))) {
        $GLOBALS['gui']->setNotify($lang['newsletter']['log_delete_success']);
    } else {
        $GLOBALS['gui']->setError($lang['newsletter']['log_delete_fail']);
    }
}

if (isset($_POST['email_filter'])) {
    if (empty($_POST['email_filter'])) {
        $GLOBALS['session']->delete('email_filter');
    } elseif (preg_match('/[a-z0-9\._-]/i', $_POST['email_filter'])) {
        $GLOBALS['session']->set('email_filter', $_POST['email_filter']);
    }
}

if (isset($_POST['subscribers']) && !empty($_POST['subscribers'])) {
    $added = false;
    $j = 0;

    $emails = preg_replace('/\s+/', '', $_POST['subscribers']);
    $emails = explode(',', $emails);
    foreach ($emails as $email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = strtolower($email);
            if (!$GLOBALS['db']->select('CubeCart_newsletter_subscriber', 'subscriber_id', array('email' => $email))) {
                $where = array('email' => $email);
                if ($existing_customer = $GLOBALS['db']->select('CubeCart_customer', 'customer_id', array('email' => $email))) {
                    if ($existing_customer[0]['customer_id']>0) {
                        $where['customer_id'] = $existing_customer[0]['customer_id'];
                    }
                }
                $where['status'] = 1;
                $where['imported'] = 1;
                $where['ip_address'] = get_ip_address();
                $where['date'] = date('c');
                if ($GLOBALS['db']->insert('CubeCart_newsletter_subscriber', $where)) {
                    foreach ($GLOBALS['hooks']->load('admin.customer.subscribers.subscribe') as $hook) {
                        include $hook;
                    }
                    $added = true;
                    $j++;
                }
            }
        } elseif (!empty($email)) {
            $GLOBALS['gui']->setError(sprintf($lang['newsletter']['email_invalid'], $email));
        }
    }

    if ($added) {
        if ($j==1) {
            $GLOBALS['gui']->setNotify($lang['newsletter']['subscriber_added']);
        } else {
            $GLOBALS['gui']->setNotify(sprintf($lang['newsletter']['subscribers_added'], $j));
        }
    } else {
        $GLOBALS['gui']->setError($lang['newsletter']['subscribers_not_added']);
    }
    
    $redirect = true;
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    foreach ($GLOBALS['hooks']->load('admin.customer.subscribers.unsubscribe') as $hook) {
        include $hook;
    }
    if ($GLOBALS['db']->delete('CubeCart_newsletter_subscriber', array('subscriber_id'=>(int)$_GET['delete']))) {
        $GLOBALS['gui']->setNotify($lang['newsletter']['subscriber_removed']);
    } else {
        $GLOBALS['gui']->setError($lang['newsletter']['subscriber_not_removed']);
    }
    $redirect = true;
}

if (isset($_POST['rem_subscriber']) && is_array($_POST['rem_subscriber'])) {
    $removed = false;
    $i = 0;
    foreach ($_POST['rem_subscriber'] as $key => $value) {
        foreach ($GLOBALS['hooks']->load('admin.customer.subscribers.unsubscribe') as $hook) {
            include $hook;
        }
        if ($GLOBALS['db']->delete('CubeCart_newsletter_subscriber', array('subscriber_id'=>$key))) {
            $removed = true;
            $i++;
        }
    }
    
    if ($removed) {
        if ($i==1) {
            $GLOBALS['gui']->setNotify($lang['newsletter']['subscriber_removed']);
        } else {
            $GLOBALS['gui']->setNotify(sprintf($lang['newsletter']['subscribers_removed'], $i));
        }
    } else {
        if ($i==1) {
            $GLOBALS['gui']->setError($lang['newsletter']['subscriber_not_removed']);
        } else {
            $GLOBALS['gui']->setError($lang['newsletter']['subscribers_not_removed']);
        }
    }
    $redirect = true;
}

if ($redirect) {
    httpredir('?_g=customers&node=subscribers', 'general');
}

$per_page  = 20;

$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
if ($GLOBALS['session']->has('email_filter') && $email_filter = $GLOBALS['session']->get('email_filter')) {
    $GLOBALS['smarty']->assign('EMAIL_FILTER', $email_filter);
    if (filter_var($email_filter, FILTER_VALIDATE_EMAIL)) {
        $where = array('email' => $email_filter);
    } else {
        $where = "`email` LIKE '%$email_filter%'";
    }
} else {
    $where = false;
}
if($subscriber_count = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', false, $where)) {
    $count   = count($subscriber_count);
    if ($count > $per_page) {
        $GLOBALS['smarty']->assign('PAGINATION', $GLOBALS['db']->pagination($count, $per_page, $page, 9, 'page', 'subscribers'));
    }

    $subscribers = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', false, $where, array('email' => 'ASC'), $per_page, $page);

    $GLOBALS['smarty']->assign('SUBSCRIBERS', $subscribers);
}

$GLOBALS['gui']->addBreadcrumb($lang['navigation']['nav_subscribers']);
$GLOBALS['main']->addTabControl($lang['navigation']['nav_subscribers'], 'general');
$GLOBALS['main']->addTabControl($lang['newsletter']['import_subscribers'], 'import');
$GLOBALS['main']->addTabControl($lang['email']['title_list_export'], 'export_mailing_list');
$GLOBALS['main']->addTabControl($lang['search']['gdpr_tools'], 'gdpr');
$page_content = $GLOBALS['smarty']->fetch('templates/customers.subscribers.php');
