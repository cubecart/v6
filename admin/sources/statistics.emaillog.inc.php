<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
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

if (isset($_GET['reset']) && !empty($_GET['reset'])) {
    $GLOBALS['session']->delete('email_filter');
    httpredir('?_g=statistics&node=emaillog');
}

if (isset($_GET['resend']) && $_GET['resend']>0) {
    $email_data = $GLOBALS['db']->select('CubeCart_email_log', false, array('id' => (int)$_GET['resend']));

    if ($email_data) {
        $mailer = new Mailer();

        $mailer->Subject = $email_data[0]['subject'];

        if (empty($email_data[0]['content_html'])) {
            $mailer->IsHTML(false);
            $mailer->Body = $email_data[0]['content_text'];
        } else {
            $mailer->Body = $email_data[0]['content_html'];
            $mailer->AltBody = $email_data[0]['content_text'];
        }

        $recipients = explode(',', $email_data[0]['to']);
        foreach ($recipients as $recipient) {
            $recipient = User::getEmailAddressParts($recipient);
            $mailer->AddAddress($recipient['email']);
        }
    
        $from = User::getEmailAddressParts($email_data[0]['from']);
        $mailer->Sender = $from['email'];
        
        $email_data[0]['result'] = $mailer->Send();
        unset($email_data[0]['date'], $email_data[0]['id']);

        if ($email_data[0]['result']) {
            $GLOBALS['main']->successMessage(sprintf($lang['statistics']['email_resent'], $mailer->Subject, htmlspecialchars($email_data[0]['to'])));
        } else {
            $GLOBALS['main']->errorMessage($lang['statistics']['email_not_resent']);
        }
        $email_data[0]['fail_reason'] = !empty($mailer->ErrorInfo) ? htmlentities($mailer->ErrorInfo, ENT_QUOTES) : '';
        $GLOBALS['db']->insert('CubeCart_email_log', $email_data[0]);
        httpredir(currentPage(array('resend')));
    }
}

if (isset($_POST['email_filter'])) {
    if (empty($_POST['email_filter'])) {
        $GLOBALS['session']->delete('email_filter');
    } elseif (preg_match('/[a-z0-9\._-]/i', $_POST['email_filter'])) {
        $GLOBALS['session']->set('email_filter', $_POST['email_filter']);
    }
}

$GLOBALS['main']->addTabControl($lang['settings']['title_email_log'], 'email_log');
$GLOBALS['gui']->addBreadcrumb($lang['settings']['title_email_log'], currentPage());

if ($GLOBALS['session']->has('email_filter') && $email_filter = $GLOBALS['session']->get('email_filter')) {
    $GLOBALS['smarty']->assign('EMAIL_FILTER', $email_filter);
    if (filter_var($email_filter, FILTER_VALIDATE_EMAIL)) {
        $where = array('to' => $email_filter);
    } else {
        $where = "`to` LIKE '%$email_filter%'";
    }
} else {
    $where = false;
}

$per_page = 25;
$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
$email_logs = $GLOBALS['db']->select('CubeCart_email_log', false, $where, array('date' => 'DESC'), $per_page, $page, false);
$count = $GLOBALS['db']->getFoundRows();
if ($email_logs!==false) {
    $row['to_email'] = array();
    foreach ($email_logs as $row) {
        $row['to'] = explode(',', $row['to']);
        foreach($row['to'] as $value) {
            if($to = User::getEmailAddressParts($value)) {
                $row['to_email'][] = array(
                    'email' => $to['email'],
                    'name' => $to['name']
                );
            }
        }
        if($from = User::getEmailAddressParts($row['from'])) {
            $row['from_name'] = $from['name'];
            $row['from_email'] = $from['email'];
            $email_log[] = $row;
        }
    }
}

$GLOBALS['smarty']->assign('EMAIL_LOG', $email_log);
$GLOBALS['smarty']->assign('PAGINATION_EMAIL_LOG', $GLOBALS['db']->pagination($count, $per_page, $page, 5, 'page', 'email_log'));

$page_content = $GLOBALS['smarty']->fetch('templates/statistics.emaillog.php');
