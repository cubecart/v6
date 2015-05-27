<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2015. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('documents', CC_PERM_READ, true);

global $lang;

$email_types = array(
	'account.password_recovery' => array(
		'description' => $lang['email']['title_macro_user_password'],
		'macros' => array(
			'{$DATA.first_name}' => $lang['email']['macro_first_name'],
			'{$DATA.last_name}'  => $lang['email']['macro_last_name'],
			'{$DATA.reset_link}'  => $lang['email']['macro_link_password'],
		),
	),
	'admin.order_received' => array(
		'description' => $lang['email']['title_macro_admin_order'],
		'macros' => array(
			'{$SHIPPING.first_name}'  => $lang['email']['macro_first_name_d'],
			'{$SHIPPING.last_name}'  => $lang['email']['macro_last_name_d'],
			'{$SHIPPING.company_name}'  => $lang['email']['macro_company_name_d'],
			'{$SHIPPING.line1}'   => $lang['email']['macro_line1_d'],
			'{$SHIPPING.line2}'   => $lang['email']['macro_line2_d'],
			'{$SHIPPING.town}'    => $lang['email']['macro_town_d'],
			'{$SHIPPING.state}'   => $lang['email']['macro_state_d'],
			'{$SHIPPING.postcode}'   => $lang['email']['macro_postcode_d'],
			'{$SHIPPING.country}'   => $lang['email']['macro_country_d'],
			'{$BILLING.first_name}'  => $lang['email']['macro_first_name'],
			'{$BILLING.last_name}'  => $lang['email']['macro_first_name'],
			'{$BILLING.company_name}'  => $lang['email']['macro_company_name'],
			'{$BILLING.line1}'    => $lang['email']['macro_line1'],
			'{$BILLING.line2}'    => $lang['email']['macro_line2'],
			'{$BILLING.town}'    => $lang['email']['macro_town'],
			'{$BILLING.state}'    => $lang['email']['macro_state'],
			'{$BILLING.postcode}'   => $lang['email']['macro_postcode'],
			'{$BILLING.country}'   => $lang['email']['macro_country'],
			'{$BILLING.phone}'    => $lang['email']['macro_phone'],
			'{$BILLING.email}'    => $lang['email']['macro_email'],
			'{$DATA.cart_order_id}'  => $lang['email']['macro_order_number'],
			'{$DATA.order_date}'   => $lang['email']['macro_order_date'],
			'{$DATA.ship_method}'  => $lang['email']['macro_order_shipping_method'],
			'{$DATA.shipping}'   => $lang['email']['macro_order_shipping'],
			'{$DATA.discount}'   => $lang['email']['macro_order_discount'],
			'{$DATA.subtotal}'   => $lang['email']['macro_order_subtotal'],
			'{$DATA.total}'    => $lang['email']['macro_order_total'],
			'{$DATA.link}'    => $lang['email']['macro_link_order'],
			'{$DATA.gateway}'   => $lang['email']['macro_gateway'],
			'{$product.name}'   => $lang['email']['macro_product_name'],
			'{$product.product_code}' => $lang['email']['macro_product_code'],
			'{$product.quantity}'  => $lang['email']['macro_product_quantity'],
			'{$product.price}'   => $lang['email']['macro_product_price'],
			'{$tax.tax_name}'   => $lang['email']['macro_tax_name'],
			'{$tax.tax_percent}'  => $lang['email']['macro_tax_rate'],
			'{$tax.tax_amount}'   => $lang['email']['macro_tax_amount']

		),
	),
	'admin.password_recovery' => array(
		'description' => $lang['email']['title_macro_admin_password'],
		'macros' => array(
			'{$DATA.name}' => $lang['email']['macro_admin_name'],
			'{$DATA.link}' => $lang['email']['macro_link_password'],
		),
	),
	'admin.review_added' => array(
		'description' => $lang['email']['title_macro_admin_review'],
		'macros' => array(
			'{$DATA.name}' => $lang['email']['macro_review_name'],
			'{$DATA.product_name}' => $lang['email']['macro_product_name'],
			'{$DATA.link}' => $lang['email']['macro_link_review'],
			'{$DATA.review}' => $lang['email']['macro_review_copy'],
		),
	),
	'cart.digital_download' => array(
		'description' => $lang['email']['title_macro_cart_digital'],
		'macros' => array (
			'{$DATA.first_name}' => $lang['email']['macro_first_name'],
			'{$DATA.last_name}' => $lang['email']['macro_last_name'],
			'{$download.name}' => $lang['email']['macro_product_name'],
			'{$download.expire}' => $lang['email']['macro_download_expire'],
			'{$download.url}'  => $lang['email']['macro_link_download'],
		),
	),
	'cart.gift_certificate' => array(
		'description' => $lang['email']['title_macro_cart_certificate'],
		'macros' => array (
			'{$DATA.name}'  => $lang['email']['macro_cert_recipient'],
			'{$DATA.first_name}' => $lang['email']['macro_sender_name_first'],
			'{$DATA.last_name}'  => $lang['email']['macro_sender_name_last'],
			'{$DATA.value}'  => $lang['email']['macro_cert_value'],
			'{$DATA.code}'  => $lang['email']['macro_cert_code'],
			'{$DATA.message}' => $lang['email']['macro_sender_message'],
		),
	),
	'cart.order_cancelled' => array(
		'description' => $lang['email']['title_macro_order_cancelled'],
		'macros' => array (
			'{$DATA.first_name}' => $lang['email']['macro_first_name'],
			'{$DATA.last_name}' => $lang['email']['macro_last_name'],
			'{$DATA.cart_order_id}' => $lang['email']['macro_order_number'],
		),
	),
	'cart.order_confirmation' => array(
		'description' => $lang['email']['title_macro_order_confirmed'],
		'macros' => array (
			'{$SHIPPING.first_name}' => $lang['email']['macro_first_name_d'],
			'{$SHIPPING.last_name}' => $lang['email']['macro_last_name_d'],
			'{$SHIPPING.company_name}' => $lang['email']['macro_company_name_d'],
			'{$SHIPPING.line1}' => $lang['email']['macro_line1_d'],
			'{$SHIPPING.line2}' => $lang['email']['macro_line2_d'],
			'{$SHIPPING.town}' => $lang['email']['macro_town_d'],
			'{$SHIPPING.state}' => $lang['email']['macro_state_d'],
			'{$SHIPPING.postcode}' => $lang['email']['macro_postcode_d'],
			'{$SHIPPING.country}' => $lang['email']['macro_country_d'],
			'{$BILLING.first_name}' => $lang['email']['macro_first_name'],
			'{$BILLING.last_name}' => $lang['email']['macro_first_name'],
			'{$BILLING.company_name}' => $lang['email']['macro_company_name'],
			'{$BILLING.line1}' => $lang['email']['macro_line1'],
			'{$BILLING.line2}' => $lang['email']['macro_line2'],
			'{$BILLING.town}' => $lang['email']['macro_town'],
			'{$BILLING.state}' => $lang['email']['macro_state'],
			'{$BILLING.postcode}' => $lang['email']['macro_postcode'],
			'{$BILLING.country}' => $lang['email']['macro_country'],
			'{$BILLING.phone}' => $lang['email']['macro_phone'],
			'{$BILLING.email}' => $lang['email']['macro_email'],
			'{$DATA.cart_order_id}' => $lang['email']['macro_order_number'],
			'{$DATA.order_date}' => $lang['email']['macro_order_date'],
			'{$DATA.ship_method}'=> $lang['email']['macro_order_shipping_method'],
			'{$DATA.shipping}' => $lang['email']['macro_order_shipping'],
			'{$DATA.discount}' => $lang['email']['macro_order_discount'],
			'{$DATA.subtotal}' => $lang['email']['macro_order_subtotal'],
			'{$DATA.total}'  => $lang['email']['macro_order_total'],
			'{$DATA.link}'  => $lang['email']['macro_link_order'],
			'{$DATA.gateway}' => $lang['email']['macro_gateway'],
			'{$product.name}'  => $lang['email']['macro_product_name'],
			'{$product.product_code}' => $lang['email']['macro_product_code'],
			'{$product.quantity}' => $lang['email']['macro_product_quantity'],
			'{$product.price}'  => $lang['email']['macro_product_price'],
			'{$tax.tax_name}' => $lang['email']['macro_tax_name'],
			'{$tax.tax_percent}' => $lang['email']['macro_tax_rate'],
			'{$tax.tax_amount}' => $lang['email']['macro_tax_amount']
		),
	),
	'cart.order_complete' => array(
		'description' => $lang['email']['title_macro_order_dispatched'],
		'macros' => array (
			'{$SHIPPING.first_name}' => $lang['email']['macro_first_name_d'],
			'{$SHIPPING.last_name}' => $lang['email']['macro_last_name_d'],
			'{$SHIPPING.company_name}' => $lang['email']['macro_company_name_d'],
			'{$SHIPPING.line1}' => $lang['email']['macro_line1_d'],
			'{$SHIPPING.line2}' => $lang['email']['macro_line2_d'],
			'{$SHIPPING.town}' => $lang['email']['macro_town_d'],
			'{$SHIPPING.state}' => $lang['email']['macro_state_d'],
			'{$SHIPPING.postcode}' => $lang['email']['macro_postcode_d'],
			'{$SHIPPING.country}' => $lang['email']['macro_country_d'],
			'{$BILLING.first_name}' => $lang['email']['macro_first_name'],
			'{$BILLING.last_name}' => $lang['email']['macro_first_name'],
			'{$BILLING.company_name}' => $lang['email']['macro_company_name'],
			'{$BILLING.line1}' => $lang['email']['macro_line1'],
			'{$BILLING.line2}' => $lang['email']['macro_line2'],
			'{$BILLING.town}' => $lang['email']['macro_town'],
			'{$BILLING.state}' => $lang['email']['macro_state'],
			'{$BILLING.postcode}' => $lang['email']['macro_postcode'],
			'{$BILLING.country}' => $lang['email']['macro_country'],
			'{$BILLING.phone}' => $lang['email']['macro_phone'],
			'{$BILLING.email}' => $lang['email']['macro_email'],
			'{$DATA.cart_order_id}' => $lang['email']['macro_order_number'],
			'{$DATA.order_date}' => $lang['email']['macro_order_date'],
			'{$DATA.ship_method}'=> $lang['email']['macro_order_shipping_method'],
			'{$DATA.shipping}' => $lang['email']['macro_order_shipping'],
			'{$DATA.discount}' => $lang['email']['macro_order_discount'],
			'{$DATA.subtotal}' => $lang['email']['macro_order_subtotal'],
			'{$DATA.total}'  => $lang['email']['macro_order_total'],
			'{$DATA.link}'  => $lang['email']['macro_link_order'],
			'{$DATA.gateway}' => $lang['email']['macro_gateway'],
			'{$DATA.ship_tracking}'  => $lang['email']['macro_ship_tracking'],
			'{$DATA.ship_date}'  => $lang['email']['macro_ship_date'],
			'{$DATA.first_name}' => $lang['email']['macro_first_name'], // back compatibility help
			'{$DATA.last_name}' => $lang['email']['macro_first_name'], // back compatibility help
			'{$product.name}'  => $lang['email']['macro_product_name'],
			'{$product.product_code}' => $lang['email']['macro_product_code'],
			'{$product.quantity}' => $lang['email']['macro_product_quantity'],
			'{$product.price}'  => $lang['email']['macro_product_price'],
			'{$tax.tax_name}' => $lang['email']['macro_tax_name'],
			'{$tax.tax_percent}' => $lang['email']['macro_tax_rate'],
			'{$tax.tax_amount}' => $lang['email']['macro_tax_amount']
		),
	),
	'cart.payment_fraud' => array(
		'description' => $lang['email']['title_macro_order_fraud'],
		'macros' => array (
			'{$DATA.first_name}' => $lang['email']['macro_first_name'],
			'{$DATA.last_name}' => $lang['email']['macro_last_name'],
			'{$DATA.cart_order_id}' => $lang['email']['macro_order_number'],
		),
	),
	'cart.payment_received' => array(
		'description' => $lang['email']['title_macro_order_payment'],
		'macros' => array (
			'{$DATA.first_name}'  => $lang['email']['macro_first_name'],
			'{$DATA.last_name}'  => $lang['email']['macro_last_name'],
			'{$DATA.cart_order_id}' => $lang['email']['macro_order_number'],
			'{$DATA.total}'   => $lang['email']['macro_payment_amount'],
		),
	),
	'catalogue.tell_friend' => array(
		'description' => $lang['email']['title_macro_tell_friend'],
		'macros'  => array (
			'{$DATA.to}'  => $lang['email']['macro_tell_friend'],
			'{$DATA.from}' => $lang['email']['macro_sender_name'],
			'{$DATA.name}' => $lang['email']['macro_product_name'],
			'{$DATA.link}' => $lang['email']['macro_link_product'],
			'{$DATA.message}'=> $lang['email']['macro_tell_message'],
		),
	),
);
## Add hook
#foreach ($hooks->() as $hook) include $hook

$GLOBALS['gui']->addBreadcrumb($lang['email']['title_email'], currentPage(array('action', 'content_id', 'content_type', 'template_id')));

if (isset($_POST['import']) && !empty($_POST['import'])) {
	if (preg_match(Language::EMAIL_FILE, $_POST['import']) && $GLOBALS['language']->importEmail($_POST['import'])) {
		$GLOBALS['main']->setACPNotify($lang['email']['notify_import']);
		httpredir(currentPage());
	}
} else if (isset($_POST['export']) && !empty($_POST['export'])) {
		if (preg_match(Language::LANG_REGEX, $_POST['export'])) {
			## Export language to XML...
			if (($emails = $GLOBALS['db']->select('CubeCart_email_content', false, array('language' => $_POST['export']))) !== false) {
				$xml = new XML();
				$xml->startElement('emails', array('version' => '1.0', 'language' => $_POST['export']));
				$content_types = array('html', 'text');
				foreach ($emails as $email) {
					$xml->startElement('email', array('name' => $email['content_type']));
					foreach ($content_types as $type) {
						if (!empty($email['content_'.$type])) {
							$xml->setElement('content', $email['content_'.$type], array('type' => $type));
						}
					}
					$xml->endElement();
				}
				$xml->endElement();
				$data = $xml->getDocument();
				$file = CC_ROOT_DIR.'/language/email_'.$_POST['export'].'-custom.xml';
				if (isset($_POST['export_compress'])) {
					$data = gzencode($data, 9, FORCE_GZIP);
					$file = $file.'.gz';
				}
				if (file_put_contents($file, $data)) {
					$GLOBALS['main']->setACPNotify($lang['email']['notify_export']);
				} else {
					$GLOBALS['main']->setACPWarning($lang['email']['error_export']);
				}
			} else {
				$GLOBALS['main']->setACPWarning($lang['email']['error_export']);
			}
			httpredir(currentPage());
		}
	}

if (isset($_POST['template_default']) && is_array($_POST['template_default']) && Admin::getInstance()->permissions('documents', CC_PERM_EDIT)) {
	$GLOBALS['db']->update('CubeCart_email_template', array('template_default' => '0'), '1');
	foreach ($_POST['template_default'] as $template_id => $status) {
		if ($status != 1) continue;
		$GLOBALS['db']->update('CubeCart_email_template', array('template_default' => (int)$status), array('template_id' => (int)$template_id));
		break;
	}
	## Update default template
	$GLOBALS['main']->setACPNotify($lang['email']['notify_template_default']);
	httpredir(currentPage());
}

if (isset($_POST['template'])) {
	
	$_POST['template']['content_html'] = $GLOBALS['RAW']['POST']['template']['content_html'];
	$_POST['template']['content_text'] = $GLOBALS['RAW']['POST']['template']['content_text'];

	## Save/Update Template
	$proceed = true;

	if(empty($_POST['template']['content_html'])) {
		$GLOBALS['main']->setACPWarning($lang['email']['error_html_empty']);
		$proceed = false;
	}

	if(empty($_POST['template']['content_text']) && !empty($_POST['template']['content_html'])) {
		$GLOBALS['main']->setACPNotify($lang['email']['error_plain_empty']);
		$_POST['template']['content_text'] = strip_tags($_POST['template']['content_html']);	
	}

	foreach (array('content_html', 'content_text') as $key) {
		if (!preg_match("/{$EMAIL_CONTENT}/", $_POST['template'][$key])) {
			$GLOBALS['main']->setACPWarning($lang['email']['error_macro_content']);
			$proceed = false;
		}
	}
	
	if ($proceed && Admin::getInstance()->permissions('documents', CC_PERM_EDIT)) {
		if (isset($_POST['template']['template_id']) && is_numeric($_POST['template']['template_id'])) {
			$GLOBALS['db']->update('CubeCart_email_template', $_POST['template'], array('template_id' => (int)$_POST['template']['template_id']));
			$GLOBALS['main']->setACPNotify($lang['email']['notify_template_update']);
		} else {
			$GLOBALS['db']->insert('CubeCart_email_template', $_POST['template']);
			$template_id  = $GLOBALS['db']->insertid();
			$GLOBALS['main']->setACPNotify($lang['email']['notify_template_create']);
		}
		httpredir(currentPage(null));
	}
}

if (isset($_POST['content']) && Admin::getInstance()->permissions('documents', CC_PERM_EDIT)) {
	
	$_POST['content']['content_html'] = $GLOBALS['RAW']['POST']['content']['content_html'];
	$_POST['content']['content_text'] = $GLOBALS['RAW']['POST']['content']['content_text'];
	

	$proceed = true;

	if(empty($_POST['content']['content_html'])) {
		$GLOBALS['main']->setACPWarning($lang['email']['error_html_empty']);
		$proceed = false;
	}

	if(empty($_POST['content']['content_text']) && !empty($_POST['content']['content_html'])) {
		$GLOBALS['main']->setACPNotify($lang['email']['error_plain_empty']);
		$_POST['content']['content_text'] = strip_tags($_POST['content']['content_html']);	
	}

	if($proceed) {
		## Save/Update Content
		if (isset($_POST['content']['content_id']) && !empty($_POST['content']['content_id'])) {
			## remove double encoding in repeat regions required to show them in FCK
			if ($GLOBALS['db']->update('CubeCart_email_content', $_POST['content'], array('content_id' => (int)$_POST['content']['content_id']))) {
				$GLOBALS['main']->setACPNotify($lang['email']['notify_content_update']);
				httpredir('?_g=documents&node=email&type=content');
			} else {
				$GLOBALS['main']->setACPWarning($lang['email']['error_content_update']);
			}
		} else {
			if (!empty($_POST['content']['content_type']) && !empty($_POST['content']['language'])) {
				$check = $GLOBALS['db']->select('CubeCart_email_content', array('content_id'), array('content_type' => $_POST['content']['content_type'], 'language' => $_POST['content']['language']));
				if ($check) {
					# $GLOBALS['db']->update('CubeCart_email_content', $_POST['content'], array('content_id' => (int)$check[0]['content_id']));
				} else {
					if ($GLOBALS['db']->insert('CubeCart_email_content', $_POST['content'])) {
						$GLOBALS['main']->setACPNotify('Email content saved.');
						httpredir('?_g=documents&node=email&type=content');
					} else {
						$GLOBALS['main']->setACPWarning($lang['email']['error_content_create']);
						httpredir(currentPage());
					}
				}
			}
		}
	} else {
		httpredir(currentPage());
	}
}

###########################################################

if (isset($_GET['action']) && isset($_GET['type'])) {
	switch (strtolower($_GET['type'])) {
	case 'content':
		## EMAIL CONTENTS
		switch (strtolower($_GET['action'])) {
		case 'delete':
			## Delete content
			if (isset($_GET['content_id'])) {
				## ONLY allow delete if there is more than one translation in the system
				$content_type = $GLOBALS['db']->select('CubeCart_email_content', array('content_type'), array('content_id' => (int)$_GET['content_id']));
				$count = $GLOBALS['db']->numrows('SELECT * FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_email_content` WHERE `content_type` = \''.(string)$content_type[0]['content_type'].'\'');

				if ($count>1) {
					if ($GLOBALS['db']->delete('CubeCart_email_content', array('content_id' => (int)$_GET['content_id']))) {
						$GLOBALS['main']->setACPNotify($lang['email']['notify_content_delete']);
						httpredir(currentPage(array('action', 'content_id', 'type')));
					} else {
						$GLOBALS['main']->setACPWarning($lang['email']['error_content_delete']);
					}
				} else {
					$GLOBALS['main']->setACPWarning($lang['email']['error_content_single']);
					httpredir('?_g=documents&node=email&type=content');
				}
			}
			break;
		default:
			if (strtolower($_GET['action']) == 'edit' && isset($_GET['content_id'])) {
				## Edit content
				$content = $GLOBALS['db']->select('CubeCart_email_content', false, array('content_id' => (int)$_GET['content_id']));
				if ($content) {
					$data = $content[0];
					$breadcrumb = $data['subject'];
					$delete = (bool)$GLOBALS['smarty']->assign('LINK_DELETE', currentPage(null, array('action' => 'delete')));
				} else {
					## redirect
					httpredir(currentPage(array('action', 'content_id', 'type')));
				}
			} else if (isset($_GET['content_type']) && array_key_exists($_GET['content_type'], $email_types)) {
					## Create Content
					# $data  = (isset($_POST['content'])) ? $_POST['content'] : array('content_type' => $_GET['content_type']);
					## Content to translate content
					$content = $GLOBALS['db']->select('CubeCart_email_content', array('content_type', 'language', 'subject', 'content_html', 'content_text'), array('content_type' => (string)$_GET['content_type'], 'language' => $GLOBALS['config']->get('config', 'default_language')));
					$data  = $content[0];
					$breadcrumb = $lang['common']['create'].': '.$_GET['content_type'];
				} else {
				## Back to main list
				httpredir(currentPage(array('action', 'content_id', 'content_type', 'type')), 'email_contents');
			}
			$lang_list = $GLOBALS['language']->listLanguages();
			if (is_array($lang_list)) {
				foreach ($lang_list as $langs) {
					## If we are adding a translation don't show default language
					if (($_GET['action']=='add' && $langs['code']!==$GLOBALS['config']->get('config', 'default_language')) || $_GET['action']=='edit') {
						$langs['selected'] = ($langs['code'] == $data['language']) ? ' selected="selected"' : '';
						$smarty_data['languages'][] = $langs;
					}
				}
				$GLOBALS['smarty']->assign('LANGUAGES', $smarty_data['languages']);
			}
			if ($_GET['content_id'] > 0) {
				$page_title = $lang['email']['title_content_update'];
			} else {
				$page_title = $lang['email']['title_content_create'];
			}
			$GLOBALS['smarty']->assign('ADD_EDIT_CONTENT', $page_title);
			$data['content_text'] = htmlentities($data['content_text'], ENT_QUOTES, 'UTF-8');

			$GLOBALS['smarty']->assign('CONTENT', $data);

			if (is_array($email_types[$data['content_type']]['macros'])) {
				foreach ($email_types[$data['content_type']]['macros'] as $macro => $desc) {
					$macro_data['name'] = htmlspecialchars($macro);
					$macro_data['description'] = $desc;

					$smarty_data['macros'][] = $macro_data;
				}
				$GLOBALS['smarty']->assign('CONTENT_MACROS', $smarty_data['macros']);
			}
			$GLOBALS['smarty']->assign('DISPLAY_CONTENT_FORM', true);
		}
		break;
	case 'template':
		## EMAIL TEMPLATES
		switch (strtolower($_GET['action'])) {
		case 'delete':
			if (isset($_GET['template_id']) && is_numeric($_GET['template_id']) && Admin::getInstance()->permissions('documents', CC_PERM_DELETE)) {
				if ($GLOBALS['db']->delete('CubeCart_email_template', array('template_id' => (int)$_GET['template_id'])))
					httpredir(currentPage(array('action', 'type', 'template_id')), 'email_templates');
			}
			break;
		default:
			if (in_array(strtolower($_GET['action']), array('clone', 'edit')) && isset($_GET['template_id']) && is_numeric($_GET['template_id'])) {
				## Edit Template
				$template = $GLOBALS['db']->select('CubeCart_email_template', false, array('template_id' => (int)$_GET['template_id']));
				if ($template) {
					$data = $template[0];
					if (strtolower($_GET['action']) == 'clone') {
						unset($data['template_id']);
					} else {
						$breadcrumb = $data['title'];
						$delete = (bool)$GLOBALS['smarty']->assign('LINK_DELETE', currentPage(null, array('action' => 'delete')));
					}
					if (isset($_POST['template'])) $data = array_merge($data, $_POST['template']);
				}
			} else {
				## Create Template
				$breadcrumb = $lang['common']['create'].': '.$lang['email']['email_template'];
				$data  = (isset($_POST['template'])) ? $_POST['template'] : array();
			}
			if ($_GET['action'] == 'edit') {
				$page_title = $lang['email']['title_template_update'];
			} elseif ($_GET['action'] == 'clone') {
				$page_title = $lang['email']['title_template_clone'];
			} else {
				$page_title = $lang['email']['title_template_create'];
			}
			$GLOBALS['smarty']->assign('ADD_EDIT_TEMPLATE', $page_title);
			$GLOBALS['smarty']->assign('TEMPLATE', $data);

			$macros = array(
				array('name' => '{$EMAIL_CONTENT}', 'description' => $lang['email']['macro_template_content'], 'required' => 'Yes'),
				array('name' => '{$DATA.logoURL}', 'description' => $lang['email']['macro_template_store_logo'], 'required' => 'No'),
				array('name' => '{$DATA.store_name}', 'description' => $lang['email']['macro_template_store_name'], 'required' => 'No'),
				array('name' => '{$DATA.storeURL}', 'description' => $lang['email']['macro_template_store_url'], 'required' => 'No'),
				array('name' => '{$DATA.unsubscribeURL}', 'description' => $lang['email']['macro_template_unsubscribe'], 'required' => 'No'),
			);
			$GLOBALS['smarty']->assign('TEMPLATE_MACROS', $macros);
			$GLOBALS['smarty']->assign('DISPLAY_TEMPLATE_FORM', true);
		}
		break;
	default:
		httpredir(currentPage(array('action', 'type')));
	}
	## Tabs
	$GLOBALS['main']->addTabControl($lang['common']['general'], 'general');
	$GLOBALS['main']->addTabControl($lang['email']['title_content_html'], 'email_html');
	$GLOBALS['main']->addTabControl($lang['email']['title_content_text'], 'email_text');
	## Breadcrumbs
	$GLOBALS['gui']->addBreadcrumb($breadcrumb, currentPage());
	// Delete link
	if (isset($delete)) $GLOBALS['smarty']->assign('DISPLAY_DELETE_LINK', true);

} else {
	$GLOBALS['main']->addTabControl($lang['email']['title_email_contents'], 'email_contents');
	$GLOBALS['main']->addTabControl($lang['email']['title_email_templates'], 'email_templates');
	$GLOBALS['main']->addTabControl($lang['common']['import'], 'email_import');
	// List Contents
	if (is_array($email_types)) {
		foreach ($email_types as $key => $values) {
			$translations = $GLOBALS['db']->select('CubeCart_email_content', array('content_id', 'language'), array('content_type' => $key), array('language' => 'ASC'));
			if ($translations) {
				// check language is installed
				foreach ($translations as $translation) {
					// Check the translation exists otherwise it's redundant
					if(file_exists(CC_ROOT_DIR.'/language/'.$translation['language'].'.xml')) {
						$translation['edit'] = currentPage(null, array('type' => 'content', 'action' => 'edit', 'content_id' => $translation['content_id']));
						$content['translations'][] = $translation;
					}
				}
			}
			$content['translate'] = currentPage(null, array('type' => 'content', 'action' => 'add', 'content_type' => $key));
			$content['type']  = $values['description'];
			$smarty_data['e_contents'][] = $content;
			unset($content);
		}
		$GLOBALS['smarty']->assign('EMAIL_CONTENTS', $smarty_data['e_contents']);
	}
	// List Templates
	if (($templates = $GLOBALS['db']->select('CubeCart_email_template')) !== false) {
		foreach ($templates as $template) {
			$template['clone'] = currentPage(null, array('action' => 'clone', 'type' => 'template', 'template_id' => $template['template_id']));
			$template['delete'] = currentPage(null, array('action' => 'delete', 'type' => 'template', 'template_id' => $template['template_id']));
			$template['edit'] = currentPage(null, array('action' => 'edit', 'type' => 'template', 'template_id' => $template['template_id']));
			$smarty_data['e_templates'][] = $template;
		}
		$GLOBALS['smarty']->assign('EMAIL_TEMPLATES', $smarty_data['e_templates']);
	}
	$GLOBALS['smarty']->assign('TEMPLATE_CREATE', currentPage(null, array('action' => 'create', 'type' => 'template')));
	// Importer
	$import = glob(CC_ROOT_DIR.'/language/email_*');
	if ($import) {
		foreach ($import as $source) {
			if (preg_match(Language::EMAIL_FILE, basename($source), $match)) {
				$emails[$match[1]] = basename($source);
			}
		}
		if (isset($emails)) {
			ksort($emails);
			foreach ($emails as $code => $file) {
				$smarty_data['imports'][] = array('code' => $code, 'file' => $file);
			}
		}
		$GLOBALS['smarty']->assign('EMAIL_IMPORT', $smarty_data['imports']);
	}
	// Exporter
	if (($export = $GLOBALS['db']->select('CubeCart_email_content', array('DISTINCT' => 'language'))) !== false) {
		foreach ($export as $row) {
			$distinct[$row['language']] = $row['language'];
		}
		if ($distinct) {
			$GLOBALS['smarty']->assign('EMAIL_EXPORTS', ksort($distinct));
		}
	}
	$GLOBALS['smarty']->assign('DISPLAY_EMAIL_LIST', true);
}
$page_content = $GLOBALS['smarty']->fetch('templates/documents.email.php');