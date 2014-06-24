<?php
if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('customers', CC_PERM_READ, true);

global $lang;

if (isset($GLOBALS['RAW']['POST']['maillist_format'])) {
	if (empty($GLOBALS['RAW']['POST']['maillist_format'])) {
		$GLOBALS['RAW']['POST']['maillist_format'] = '{$EMAIL_ADDRESS}';
	}
	if (($maillist = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', array('customer_id', 'email'), array('status' => 1))) !== false) {
		// Set initial variables
		$file_data = null;
		$find  = array (
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
			if ($_POST['maillist_extension']=="txt") {
				$file_data .= str_replace($find, $replace, $GLOBALS['RAW']['POST']['maillist_format']).",";
			}else {
				$file_data .= str_replace($find, $replace, $GLOBALS['RAW']['POST']['maillist_format'])."\n";
			}
			/* End Fixing Bug 2884 */
			unset($customer, $replace, $member, $long_name, $short_name);
		}
		$GLOBALS['debug']->supress(true);
		deliverFile(false, false, $file_data, $lang['email']['export_filename'].'.'.$_POST['maillist_extension']);
		exit;
	} else {
		$GLOBALS['main']->setACPWarning($lang['email']['error_news_export_empty']);
	}
}
$GLOBALS['gui']->addBreadcrumb($lang['email']['title_newsletters'], currentPage(array('action', 'newsletter_id')));

$seo  = SEO::getInstance();
$newsletter = Newsletter::getInstance();

if (isset($_POST['newsletter']) && !empty($_POST['newsletter'])) {
	$redirect = false;
	if ($newsletter->saveNewsletter($_POST['newsletter'])) {
		$redirect = true;
		$GLOBALS['main']->setACPNotify($lang['email']['notify_news_save']);
	} else {
		$GLOBALS['main']->setACPWarning($lang['email']['error_news_save']);
	}
	if (isset($_POST['newsletter']['test_email']) && !empty($_POST['newsletter']['test_email'])) {
		if ($newsletter->sendNewsletter($_POST['newsletter']['newsletter_id'], false, $_POST['newsletter']['test_email'])) {
			$GLOBALS['main']->setACPNotify($lang['email']['notify_news_test_sent']);
		}
	}
	if ($redirect) {
		httpredir('?_g=customers&node=email');
	}
}

if (isset($_GET['action']) && strtolower($_GET['action']) == 'delete') {
	if (Admin::getInstance()->permissions('customers', CC_PERM_DELETE) && $newsletter->deleteNewsletter($_GET['newsletter_id'])) {
		$GLOBALS['main']->setACPNotify($lang['email']['notify_news_delete']);
	} else {
		$GLOBALS['main']->setACPWarning($lang['email']['error_news_delete']);
	}
	httpredir(currentPage(array('newsletter_id', 'action')));
} elseif (isset($_GET['action']) && strtolower($_GET['action']) == 'send') {
	if (isset($_GET['newsletter_id']) && is_numeric($_GET['newsletter_id'])) {
		$GLOBALS['main']->setACPNotify($lang['email']['notify_news_sent']);
		$GLOBALS['gui']->addBreadcrumb($lang['email']['title_sending'], currentPage());
		$GLOBALS['smarty']->assign('NEWSLETTER_ID', (int)$_GET['newsletter_id']);
	}
	$GLOBALS['smarty']->assign('DISPLAY_SEND', true);
} else if (isset($_GET['action']) && in_array(strtolower($_GET['action']), array('add', 'edit'))) {
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
	$GLOBALS['main']->addTabControl($lang['email']['title_list_export'], 'export_mailing_list');
	// List newsletters, reverse chronology
	if (($contents = $GLOBALS['db']->select('CubeCart_newsletter', false)) !== false) {
		foreach ($contents as $content) {
			$content['edit'] = currentPage(null, array('action' => 'edit', 'newsletter_id' => $content['newsletter_id']));
			$content['send'] = currentPage(null, array('action' => 'send', 'newsletter_id' => $content['newsletter_id']));
			$content['delete'] = currentPage(null, array('action' => 'delete', 'newsletter_id' => $content['newsletter_id']));
			$smarty_data['newsletters'][] = $content;
		}
		$GLOBALS['smarty']->assign('NEWSLETTERS', $smarty_data['newsletters']);
	}
	$GLOBALS['smarty']->assign('DISPLAY_LIST', true);
}
$page_content = $GLOBALS['smarty']->fetch('templates/customers.email.php');
