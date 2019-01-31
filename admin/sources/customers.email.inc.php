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

$GLOBALS['gui']->addBreadcrumb($lang['email']['title_newsletters'], currentPage(array('action', 'newsletter_id')));

$seo  = SEO::getInstance();
$newsletter = Newsletter::getInstance();

if (isset($_POST['newsletter']) && !empty($_POST['newsletter'])) {
    $redirect = false;
    $proceed = true;

    if (empty($_POST['newsletter']['subject'])) {
        $proceed = false;
        $GLOBALS['main']->errorMessage($lang['email']['error_no_subject']);
    }
    if (empty($_POST['newsletter']['content_html']) && empty($_POST['newsletter']['content_text'])) {
        $proceed = false;
        $GLOBALS['main']->errorMessage($lang['email']['error_no_message']);
    }
    if ($proceed) {
        $_POST['newsletter']['content_html'] = $GLOBALS['RAW']['POST']['newsletter']['content_html'];
        if ($newsletter->saveNewsletter($_POST['newsletter'])) {
            $redirect = true;
            $_POST['newsletter']['newsletter_id'] = (!empty($_POST['newsletter']['newsletter_id'])) ? $_POST['newsletter']['newsletter_id'] : $newsletter->_newsletter_id;
            $GLOBALS['main']->successMessage($lang['email']['notify_news_save']);
        } else {
            $GLOBALS['main']->errorMessage($lang['email']['error_news_save']);
        }
        if (isset($_POST['newsletter']['test_email']) && !empty($_POST['newsletter']['test_email'])) {
            if ($newsletter->sendNewsletter($_POST['newsletter']['newsletter_id'], false, $_POST['newsletter']['test_email'])) {
                $GLOBALS['main']->successMessage($lang['email']['notify_news_test_sent']);
            }
        }
        if ($redirect) {
            httpredir('?_g=customers&node=email');
        }
    }
}

if (isset($_GET['action']) && strtolower($_GET['action']) == 'delete') {
    if (Admin::getInstance()->permissions('customers', CC_PERM_DELETE) && $newsletter->deleteNewsletter($_GET['newsletter_id'])) {
        $GLOBALS['main']->successMessage($lang['email']['notify_news_delete']);
    } else {
        $GLOBALS['main']->errorMessage($lang['email']['error_news_delete']);
    }
    httpredir(currentPage(array('newsletter_id', 'action')));
} elseif (isset($_GET['action']) && strtolower($_GET['action']) == 'send') {
    if (isset($_GET['newsletter_id']) && is_numeric($_GET['newsletter_id'])) {
        $GLOBALS['main']->addTabControl($lang['email']['title_sending'], 'newsletter_send');
        $GLOBALS['gui']->addBreadcrumb($lang['email']['title_sending'], currentPage());
        $GLOBALS['smarty']->assign('NEWSLETTER_ID', (int)$_GET['newsletter_id']);
    }
    $GLOBALS['smarty']->assign('DISPLAY_SEND', true);
} elseif (isset($_GET['action']) && in_array(strtolower($_GET['action']), array('add', 'edit'))) {
    Admin::getInstance()->permissions('customers', CC_PERM_EDIT, true);

    $GLOBALS['main']->addTabControl($lang['common']['general'], 'general');
    $GLOBALS['main']->addTabControl($lang['email']['title_content_html'], 'email_html');
    $GLOBALS['main']->addTabControl($lang['email']['title_content_text'], 'email_text');
    $GLOBALS['main']->addTabControl($lang['email']['title_send_test'], 'send_test');
    if (isset($_GET['newsletter_id']) && is_numeric($_GET['newsletter_id'])) {
        if (($content = $GLOBALS['db']->select('CubeCart_newsletter', false, array('newsletter_id' => (int)$_GET['newsletter_id']))) !== false) {
            // Render editor window
            $GLOBALS['gui']->addBreadcrumb($content[0]['subject'], currentPage());
            $GLOBALS['smarty']->assign('NEWSLETTER', $content[0]);
        } else {
            httpredir(currentPage(array('newsletter_id')));
        }
    }
    // Get template list
    if (($templates = $GLOBALS['db']->select('CubeCart_email_template', array('template_default', 'template_id', 'title'))) !== false) {
        foreach ($templates as $template) {
            if (isset($content)) {
                $template['selected'] = ($template['template_id'] == $content[0]['template_id']) ? ' selected="selected"' : '';
            } else {
                $template['selected'] = '';
            }
            $existing_templates[] = $template;
        }
        $GLOBALS['smarty']->assign('EXISTING_TEMPLATES', $existing_templates);
    }
    $GLOBALS['smarty']->assign('DISPLAY_FORM', true);
} else {
    $GLOBALS['main']->addTabControl($lang['email']['title_newsletters'], 'newsletter-list');
    $GLOBALS['main']->addTabControl($lang['email']['title_news_create'], false, currentPage(null, array('action' => 'add')));
    // List newsletters, reverse chronology
    if (($contents = $GLOBALS['db']->select('CubeCart_newsletter', false)) !== false) {
        foreach ($contents as $content) {
            $content['edit'] = currentPage(null, array('action' => 'edit', 'newsletter_id' => $content['newsletter_id']));
            $content['send'] = currentPage(null, array('action' => 'send', 'newsletter_id' => $content['newsletter_id'], 'token' => SESSION_TOKEN));
            $content['delete'] = currentPage(null, array('action' => 'delete', 'newsletter_id' => $content['newsletter_id'], 'token' => SESSION_TOKEN));
            $smarty_data['newsletters'][] = $content;
        }
        $GLOBALS['smarty']->assign('NEWSLETTERS', $smarty_data['newsletters']);
    }
    $GLOBALS['smarty']->assign('DISPLAY_LIST', true);
}
$page_content = $GLOBALS['smarty']->fetch('templates/customers.email.php');
