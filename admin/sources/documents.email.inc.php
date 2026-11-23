<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}
Admin::getInstance()->permissions('documents', CC_PERM_READ, true);

## What each email can be given, as '{$TAG}' => array('label' => its short name, 'description' => what it is).
## An extension may still give a plain description in place of the array.
$macro = function ($name, $label = null) use ($lang) {
    $label = 'label_'.(is_null($label) ? $name : $label);
    return array(
        'label'       => isset($lang['email'][$label]) ? $lang['email'][$label] : '',
        'description' => isset($lang['email']['macro_'.$name]) ? $lang['email']['macro_'.$name] : '',
    );
};

## The emails about an order are all given the same order
$order_macros = array(
    '{$DATA.first_name}'        => $macro('customer_first_name'),
    '{$DATA.last_name}'         => $macro('customer_last_name'),
    '{$DATA.cart_order_id}'     => $macro('order_number_t'),
    '{$DATA.custom_oid}'        => $macro('order_number_i'),
    '{$DATA.order_date}'        => $macro('order_date'),
    '{$DATA.total}'             => $macro('order_total'),
    '{$DATA.subtotal}'          => $macro('order_subtotal'),
    '{$DATA.discount}'          => $macro('order_discount'),
    '{$DATA.shipping}'          => $macro('order_shipping'),
    '{$DATA.credit_used}'       => $macro('order_credit_used'),
    '{$DATA.gateway}'           => $macro('gateway'),
    '{$DATA.ship_method}'       => $macro('order_shipping_method'),
    '{$DATA.ship_product}'      => $macro('order_shipping_product'),
    '{$DATA.link}'              => $macro('link_order'),
    '{$DATA.customer_comments}' => $macro('customer_comments'),
    '{$DATA.note_to_customer}'  => $macro('note_to_customer'),
    '{$BILLING.first_name}'     => $macro('first_name'),
    '{$BILLING.last_name}'      => $macro('last_name'),
    '{$BILLING.company_name}'   => $macro('company_name'),
    '{$BILLING.line1}'          => $macro('line1'),
    '{$BILLING.line2}'          => $macro('line2'),
    '{$BILLING.town}'           => $macro('town'),
    '{$BILLING.state}'          => $macro('state'),
    '{$BILLING.postcode}'       => $macro('postcode'),
    '{$BILLING.country}'        => $macro('country'),
    '{$BILLING.w3w}'            => $macro('w3w'),
    '{$BILLING.phone}'          => $macro('phone'),
    '{$BILLING.email}'          => $macro('email'),
    '{$SHIPPING.first_name}'    => $macro('first_name_d'),
    '{$SHIPPING.last_name}'     => $macro('last_name_d'),
    '{$SHIPPING.company_name}'  => $macro('company_name_d'),
    '{$SHIPPING.line1}'         => $macro('line1_d'),
    '{$SHIPPING.line2}'         => $macro('line2_d'),
    '{$SHIPPING.town}'          => $macro('town_d'),
    '{$SHIPPING.state}'         => $macro('state_d'),
    '{$SHIPPING.postcode}'      => $macro('postcode_d'),
    '{$SHIPPING.country}'       => $macro('country_d'),
    '{$SHIPPING.w3w}'           => $macro('w3w_d'),
    '{$product.name}'           => $macro('product_name'),
    '{$product.product_code}'   => $macro('product_code'),
    '{$product.product_options}' => $macro('product_options'),
    '{$product.quantity}'       => $macro('product_quantity'),
    '{$product.item_price}'     => $macro('product_item_price'),
    '{$product.price}'          => $macro('product_line_price'),
    '{$tax.tax_name}'           => $macro('tax_name'),
    '{$tax.tax_percent}'        => $macro('tax_rate'),
    '{$tax.tax_amount}'         => $macro('tax_amount'),
);

$email_types = array(
    'account.password_recovery' => array(
        'description' => $lang['email']['title_macro_user_password'],
        'macros' => array(
            '{$DATA.first_name}' => $macro('customer_first_name'),
            '{$DATA.last_name}'  => $macro('customer_last_name'),
            '{$DATA.email}'      => $macro('customer_email'),
            '{$DATA.reset_link}' => $macro('link_password'),
        ),
    ),
    'account.activate' => array(
        'description' => $lang['email']['title_macro_user_activate'],
        'macros' => array(
            '{$DATA.first_name}'    => $macro('customer_first_name'),
            '{$DATA.last_name}'     => $macro('customer_last_name'),
            '{$DATA.email}'         => $macro('customer_email'),
            '{$DATA.activate_link}' => $macro('link_activate'),
        ),
    ),
    'newsletter.verify_email' => array(
        'description' => $lang['email']['title_macro_user_newsletter'],
        'macros' => array(
            '{$DATA.email}' => $macro('customer_email'),
            '{$DATA.link}'  => $macro('link_verify'),
        ),
    ),
    'newsletter.remove_request' => array(
        'description' => $lang['email']['title_macro_user_newsletter_remove'],
        'macros' => array(
            '{$DATA.email}' => $macro('customer_email'),
            '{$DATA.link}'  => $macro('link_verify'),
        ),
    ),
    'admin.order_received' => array(
        'description' => $lang['email']['title_macro_admin_order'],
        'macros' => array_merge($order_macros, array(
            '{$DATA.ip_address}' => $macro('ip_address'),
        )),
    ),
    'admin.password_recovery' => array(
        'description' => $lang['email']['title_macro_admin_password'],
        'macros' => array(
            '{$DATA.name}' => $macro('admin_name'),
            '{$DATA.link}' => $macro('link_password'),
        ),
    ),
    'admin.review_added' => array(
        'description' => $lang['email']['title_macro_admin_review'],
        'macros' => array(
            '{$DATA.name}'         => $macro('review_name'),
            '{$DATA.email}'        => $macro('review_email'),
            '{$DATA.product_name}' => $macro('product_name'),
            '{$DATA.rating}'       => $macro('review_rating'),
            '{$DATA.title}'        => $macro('review_title'),
            '{$DATA.review}'       => $macro('review_copy'),
            '{$DATA.link}'         => $macro('link_review'),
        ),
    ),
    'admin.two_factor_code' => array(
        'description' => $lang['email']['title_macro_admin_two_factor_code'],
        'macros' => array(
            '{$DATA.name}' => $macro('admin_name'),
            '{$DATA.code}' => $macro('twofa_code'),
            '{$DATA.expires_minutes}' => $macro('twofa_expires_minutes'),
        ),
    ),
    'admin.new_device_login' => array(
        'description' => $lang['email']['title_macro_admin_new_device_login'],
        'macros' => array(
            '{$DATA.name}'             => $macro('admin_name'),
            '{$DATA.login_time}'       => $macro('login_time'),
            '{$DATA.new_ip}'           => $macro('new_ip'),
            '{$DATA.previous_ip}'      => $macro('previous_ip'),
            '{$DATA.new_browser}'      => $macro('new_browser'),
            '{$DATA.previous_browser}' => $macro('previous_browser'),
        ),
    ),
    'cart.digital_download' => array(
        'description' => $lang['email']['title_macro_cart_digital'],
        'macros' => array(
            '{$DATA.first_name}'    => $macro('customer_first_name'),
            '{$DATA.last_name}'     => $macro('customer_last_name'),
            '{$DATA.cart_order_id}' => $macro('order_number_t'),
            '{$download.name}'      => $macro('product_name'),
            '{$download.url}'       => $macro('link_download'),
            '{$download.expire}'    => $macro('download_expire'),
            '{$download.stream}'    => $macro('stream'),
        ),
    ),
    'cart.gift_certificate' => array(
        'description' => $lang['email']['title_macro_cart_certificate'],
        'macros' => array(
            '{$DATA.name}'       => $macro('cert_recipient'),
            '{$DATA.first_name}' => $macro('sender_name_first'),
            '{$DATA.last_name}'  => $macro('sender_name_last'),
            '{$DATA.value}'      => $macro('cert_value'),
            '{$DATA.code}'       => $macro('cert_code'),
            '{$DATA.message}'    => $macro('sender_message'),
        ),
    ),
    'cart.order_cancelled' => array(
        'description' => $lang['email']['title_macro_order_cancelled'],
        'macros' => $order_macros,
    ),
    'cart.order_confirmation' => array(
        'description' => $lang['email']['title_macro_order_confirmed'],
        'macros' => $order_macros,
    ),
    'cart.order_complete' => array(
        'description' => $lang['email']['title_macro_order_dispatched'],
        'macros' => array_merge($order_macros, array(
            '{$DATA.ship_tracking}' => $macro('ship_tracking'),
            '{$DATA.ship_date}'     => $macro('ship_date'),
        )),
    ),
    'cart.payment_fraud' => array(
        'description' => $lang['email']['title_macro_order_fraud'],
        'macros' => $order_macros,
    ),
    'cart.payment_request' => array(
        'description' => $lang['email']['title_macro_payment_request'],
        'macros' => array_merge($order_macros, array(
            '{$DATA.pay_url}' => $macro('pay_link'),
        )),
    ),
    'cart.payment_received' => array(
        'description' => $lang['email']['title_macro_order_payment'],
        'macros' => array_merge($order_macros, array(
            '{$DATA.total}' => $macro('payment_amount'),
        )),
    ),
    'cart.abandoned' => array(
        'description' => $lang['email']['title_macro_cart_abandoned'],
        'macros' => array(
            '{$DATA.first_name}'         => $macro('customer_first_name'),
            '{$DATA.last_name}'          => $macro('customer_last_name'),
            '{$DATA.store_name}'         => $macro('store_name'),
            '{$DATA.item_count}'         => $macro('item_count'),
            '{$DATA.recovery_link}'      => $macro('recovery_link'),
            '{$DATA.optout_link}'        => $macro('optout_link'),
            '{$DATA.coupon_code}'        => $macro('coupon_code'),
            '{$DATA.coupon_description}' => $macro('coupon_description'),
            '{$product.name}'            => $macro('product_name'),
            '{$product.options}'         => $macro('product_options'),
            '{$product.quantity}'        => $macro('product_quantity'),
            '{$product.price}'           => $macro('product_price'),
            '{$product.image}'           => $macro('product_image'),
        ),
    ),
    'cart.withdrawal_acknowledgment' => array(
        'description' => $lang['email']['title_macro_cart_withdrawal_acknowledgment'],
        'macros' => array(
            '{$DATA.name}'              => $macro('wd_name'),
            '{$DATA.email}'             => $macro('wd_email'),
            '{$DATA.reference}'         => $macro('wd_reference'),
            '{$DATA.cart_order_id}'     => $macro('wd_cart_order_id'),
            '{$DATA.submitted_at}'      => $macro('wd_submitted_at'),
            '{$DATA.reported_delivery}' => $macro('wd_reported_delivery'),
            '{$DATA.statement}'         => $macro('wd_statement'),
            '{$DATA.reason}'            => $macro('wd_reason'),
            '{$DATA.store_name}'        => $macro('store_name'),
        ),
    ),
    'cart.withdrawal_decision' => array(
        'description' => $lang['email']['title_macro_cart_withdrawal_decision'],
        'macros' => array(
            '{$DATA.name}'          => $macro('wd_name'),
            '{$DATA.reference}'     => $macro('wd_reference'),
            '{$DATA.cart_order_id}' => $macro('wd_cart_order_id'),
            '{$DATA.status}'        => $macro('wd_status'),
            '{$DATA.decision_at}'   => $macro('wd_decision_at'),
            '{$DATA.note}'          => $macro('wd_note'),
            '{$DATA.store_name}'    => $macro('store_name'),
        ),
    ),
    'admin.withdrawal_received' => array(
        'description' => $lang['email']['title_macro_admin_withdrawal_received'],
        'macros' => array(
            '{$DATA.name}'              => $macro('wd_name'),
            '{$DATA.email}'             => $macro('wd_email'),
            '{$DATA.reference}'         => $macro('wd_reference'),
            '{$DATA.cart_order_id}'     => $macro('wd_cart_order_id'),
            '{$DATA.submitted_at}'      => $macro('wd_submitted_at'),
            '{$DATA.reported_delivery}' => $macro('wd_reported_delivery'),
            '{$DATA.statement}'         => $macro('wd_statement'),
            '{$DATA.reason}'            => $macro('wd_reason'),
            '{$DATA.admin_link}'        => $macro('wd_admin_link'),
        ),
    ),
);
## Add hook
foreach ($GLOBALS['hooks']->load('admin.documents.email.macros') as $hook) {
    include $hook;
}

## Every email is given the store's name and address (Mailer::assignStoreValues), an extension's included
$store_macros = array(
    '{$DATA.store_name}' => array_merge($macro('store_name'), array('sample' => (string)$GLOBALS['config']->get('config', 'store_name'))),
    '{$DATA.storeURL}'   => array_merge($macro('store_url'), array('sample' => (string)$GLOBALS['storeURL'])),
);
foreach ($email_types as $email_type => $email_type_data) {
    $email_type_macros = (isset($email_type_data['macros']) && is_array($email_type_data['macros'])) ? $email_type_data['macros'] : array();
    $email_types[$email_type]['macros'] = $email_type_macros + $store_macros;
}
unset($email_type, $email_type_data, $email_type_macros);

## Smarty names a template held in a string by its whole source, and quotes the line at
## fault after it. This says only which line it is and what is wrong with it.
$smarty_error = function ($e, $sources = array()) {
    return EmailEditor::smartyError($e, $sources);
};

## A subject or preview text as typed: one line of plain text that may hold Smarty.
## These are not taken from the sanitised $_POST, because that turns the quotes in
## {if $DATA.status == 'accepted'} into entities, and the email then cannot be sent.
$plain_line = function ($text, $limit = 0) {
    $text = trim(preg_replace('/\s+/', ' ', (string)$text));
    if ($limit > 0) {
        $text = function_exists('mb_substr') ? mb_substr($text, 0, $limit, 'UTF-8') : substr($text, 0, $limit);
    }
    return $text;
};

## Saving strips scripts from an email, so everything that shows one does too
$no_script = function ($html) {
    return preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', (string)$html);
};

## Whether a body, a subject or a template compiles. It is compiled and not run: nothing
## that was posted is executed by being checked, and logic that needs the email's data
## to run (a count of the products, say) is not taken for a mistake.
$compile_check = function ($source) {
    EmailEditor::compile($source);
};

## The visual editor (admin.email.editor.js). 'units' divides a body into wording and
## logic for it to show; 'save' takes back what it holds. Both answer as JSON.
if (isset($_POST['email_editor']) && is_array($_POST['email_editor'])) {
    $request  = $_POST['email_editor'];
    $raw      = (isset($GLOBALS['RAW']['POST']['email_editor']) && is_array($GLOBALS['RAW']['POST']['email_editor'])) ? $GLOBALS['RAW']['POST']['email_editor'] : array();
    $action   = isset($request['action']) ? (string)$request['action'] : '';
    $sample   = (isset($request['sample']) && $request['sample'] === EmailSample::SOURCE_EXAMPLE) ? EmailSample::SOURCE_EXAMPLE : EmailSample::SOURCE_LATEST;
    $response = array('status' => 'error', 'message' => $GLOBALS['language']->notification['error_privileges']);
    $checking = array();

    ob_start();
    try {
        if (!Admin::getInstance()->permissions('documents', CC_PERM_EDIT, false, false)) {
            // Nothing: only someone who may edit emails can have text rendered or saved
        } elseif ($action === 'units') {
            $type     = isset($request['content_type']) ? (string)$request['content_type'] : '';
            $macros   = isset($email_types[$type]['macros']) ? $email_types[$type]['macros'] : array();
            $source   = isset($raw['content_html']) ? (string)$raw['content_html'] : '';
            $checking = array($source);
            if (strlen($source) > 65535) {
                // Longer than can be stored, so there is nothing to be gained by reading it
                throw new Exception($lang['email']['editor_too_long']);
            }
            // It has to compile before it is worth showing
            $compile_check($source);
            $response = array_merge(array('status' => 'ok'), EmailEditor::describe($source, $type, $macros, $sample));
        } elseif ($action === 'save') {
            $content_id = !empty($request['content_id']) ? (int)$request['content_id'] : 0;
            $row    = $content_id ? $GLOBALS['db']->select('CubeCart_email_content', false, array('content_id' => $content_id), false, 1, false, false) : false;
            $row    = $row ? $row[0] : false;
            $type   = $row ? $row['content_type'] : (isset($request['content_type']) ? (string)$request['content_type'] : '');
            $code   = $row ? $row['language'] : (isset($request['language']) ? (string)$request['language'] : '');
            $macros = isset($email_types[$type]['macros']) ? $email_types[$type]['macros'] : array();
            $error  = '';
            $body   = false;

            if ($content_id && !$row) {
                $error = $lang['email']['error_content_update'];
            } elseif (!$row && (!isset($email_types[$type]) || !preg_match(Language::LANG_REGEX, $code))) {
                $error = $lang['email']['error_content_create'];
            } elseif (!$row && $GLOBALS['db']->select('CubeCart_email_content', array('content_id'), array('content_type' => $type, 'language' => $code), false, 1, false, false)) {
                $error = $lang['email']['error_content_create_exists'];
            } elseif ($row && !empty($request['base']) && !hash_equals(md5($row['content_html']), (string)$request['base'])) {
                // Saved by someone else, or in another tab, since this editor was opened
                $error = $lang['email']['editor_changed_elsewhere'];
            } elseif (isset($raw['items'])) {
                // Unit by unit: what was not touched is taken from the stored bytes
                $items = json_decode((string)$raw['items'], true);
                $body  = ($row && is_array($items)) ? EmailEditor::rebuild($row['content_html'], $items) : false;
            } elseif (isset($raw['content_html'])) {
                $body = (string)$raw['content_html'];
                // Where only the line endings differ, nothing was changed
                if ($row && preg_replace('/\r\n?/', "\n", $body) === preg_replace('/\r\n?/', "\n", $row['content_html'])) {
                    $body = $row['content_html'];
                }
            }
            if ($error === '' && $body === false) {
                $error = $lang['email'][$row ? 'error_content_update' : 'error_content_create'];
            }
            if ($error === '') {
                $body      = $no_script($body);
                $subject   = $plain_line(isset($raw['subject']) ? $raw['subject'] : '', 250);
                $preheader = $plain_line(isset($raw['preheader']) ? $raw['preheader'] : '', 255);
                if (trim($body) === '') {
                    $error = $lang['email']['error_html_empty'];
                } elseif (strlen($body) > 65535) {
                    $error = $lang['email']['editor_too_long'];
                }
            }
            if ($error === '') {
                // Each of these goes through Smarty when the email is sent, so each has to compile
                foreach (array(array($lang['email']['title_content_html'], $body), array($lang['common']['subject'], $subject), array($lang['email']['preview_text'], $preheader)) as $part) {
                    if ($part[1] === '') {
                        continue;
                    }
                    try {
                        $compile_check($part[1]);
                    } catch (Throwable $e) {
                        $error = $part[0].': '.$smarty_error($e, array($part[1]));
                        break;
                    }
                }
            }
            if ($error === '') {
                // The database layer strips backslashes from what it is given, so shield the email's own
                $record  = array('subject' => addslashes($subject), 'preheader' => addslashes($preheader));
                $changed = (!$row || $body !== $row['content_html']);
                if ($changed) {
                    $record['content_html'] = addslashes($body);
                }
                if ($row) {
                    $GLOBALS['db']->update('CubeCart_email_content', $record, array('content_id' => $content_id), true, array('subject', 'preheader', 'content_html'));
                } else {
                    $record['content_type'] = $type;
                    $record['language']     = $code;
                    $content_id = (int)$GLOBALS['db']->insert('CubeCart_email_content', $record);
                }
                $kept = $content_id ? $GLOBALS['db']->select('CubeCart_email_content', array('content_html'), array('content_id' => $content_id), false, 1, false, false) : false;
                if (!$kept) {
                    $error = $lang['email'][$row ? 'error_content_update' : 'error_content_create'];
                } elseif ($kept[0]['content_html'] !== $body) {
                    $error = $lang['email']['editor_not_stored'];
                }
            }
            if ($error === '') {
                $described = EmailEditor::describe($body, $type, $macros, $sample);
                $response  = array_merge(array(
                    'status'     => 'ok',
                    'message'    => $lang['email'][$row ? 'notify_content_update' : 'notify_content_create'],
                    'content_id' => $content_id,
                    'base'       => md5($body),
                    'source'     => $body,
                    'changed'    => $changed,
                    'edit_url'   => $row ? '' : '?_g=documents&node=email&type=content&action=edit&content_id='.$content_id,
                ), $described);
            } else {
                $response = array('status' => 'error', 'message' => $error);
            }
        }
    } catch (Throwable $e) {
        $response = array('status' => 'error', 'message' => $smarty_error($e, $checking));
    }
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

## Preview and test send, answered as JSON. Both are built by the mailer exactly as a
## real send is, with sample data standing in for the order or the customer.
if (isset($_POST['email_preview']) && is_array($_POST['email_preview'])) {
    $request  = $_POST['email_preview'];
    $raw      = (isset($GLOBALS['RAW']['POST']['email_preview']) && is_array($GLOBALS['RAW']['POST']['email_preview'])) ? $GLOBALS['RAW']['POST']['email_preview'] : array();
    $can_edit = Admin::getInstance()->permissions('documents', CC_PERM_EDIT, false, false);
    $action   = (isset($request['action']) && $request['action'] === 'test') ? 'test' : 'preview';
    $scope    = (isset($request['scope']) && in_array($request['scope'], array('template', 'design'), true)) ? $request['scope'] : 'content';
    $source   = (isset($request['sample']) && $request['sample'] === EmailSample::SOURCE_EXAMPLE) ? EmailSample::SOURCE_EXAMPLE : EmailSample::SOURCE_LATEST;
    $response = array('status' => 'error', 'message' => '');

    $content     = false;
    $template    = false;
    $template_id = !empty($request['template_id']) ? (int)$request['template_id'] : false;
    if ($scope === 'design') {
        // The email design as it stands on screen, around the order confirmation
        $content  = $GLOBALS['db']->select('CubeCart_email_content', false, array('content_type' => 'cart.order_confirmation', 'language' => $GLOBALS['language']->current()), false, 1, false, false);
        if (!$content) {
            $content = $GLOBALS['db']->select('CubeCart_email_content', false, array('content_type' => 'cart.order_confirmation'), false, 1, false, false);
        }
        $design   = ($can_edit && isset($request['design']) && is_array($request['design'])) ? EmailDesign::clean($request['design']) : EmailDesign::settings();
        $template = array('title' => $lang['email']['design_template_title'], 'content_html' => EmailDesign::render($design));
    } elseif ($scope === 'template') {
        // A template is shown wrapped around the order confirmation, the email most stores send most
        $content = $GLOBALS['db']->select('CubeCart_email_content', false, array('content_type' => 'cart.order_confirmation', 'language' => $GLOBALS['language']->current()), false, 1, false, false);
        if (!$content) {
            $content = $GLOBALS['db']->select('CubeCart_email_content', false, array('content_type' => 'cart.order_confirmation'), false, 1, false, false);
        }
        // Without permission to edit, only what is saved can be looked at
        if ($can_edit && isset($raw['template_html'])) {
            $template = array(
                'title'        => isset($request['template_title']) ? (string)$request['template_title'] : '',
                'content_html' => $no_script($raw['template_html']),
            );
        }
    } elseif (!empty($request['content_id'])) {
        $content = $GLOBALS['db']->select('CubeCart_email_content', false, array('content_id' => (int)$request['content_id']), false, 1, false, false);
    }
    $content = $content ? $content[0] : array('content_type' => '', 'subject' => '', 'content_html' => '');
    if ($scope === 'content') {
        if (empty($content['content_type']) && isset($request['content_type'])) {
            $content['content_type'] = (string)$request['content_type'];
        }
        if ($can_edit) {
            // As typed: the subject and preview text are saved unsanitised too (see the save below)
            if (isset($raw['subject'])) {
                $content['subject'] = $plain_line($raw['subject']);
            }
            if (isset($raw['preheader'])) {
                $content['preheader'] = $plain_line($raw['preheader']);
            }
            if (isset($raw['content_html'])) {
                $content['content_html'] = $no_script($raw['content_html']);
            }
        }
    }
    $macros   = isset($email_types[$content['content_type']]['macros']) ? $email_types[$content['content_type']]['macros'] : array();
    $contents = array('subject' => (string)$content['subject'], 'content_html' => (string)$content['content_html']);
    if (isset($content['preheader']) && $content['preheader'] !== '') {
        $contents['preheader'] = (string)$content['preheader'];
    }

    ob_start();
    try {
        if (strlen($contents['content_html']) > 65535 || (is_array($template) && strlen((string)$template['content_html']) > 65535)) {
            // Longer than can be stored, so it is not rendered either
            throw new Exception($lang['email'][is_array($template) && strlen((string)$template['content_html']) > 65535 ? 'template_too_long' : 'editor_too_long']);
        }
        $used   = EmailSample::assign($content['content_type'], $source, $macros);
        $mailer = new Mailer();
        if (!empty($content['language'])) {
            $mailer->setEmailLanguage($content['language']);
        }
        if ($action === 'preview') {
            $composed = $mailer->previewEmail($content['content_type'], $contents, $template_id, $template);
            $response = array(
                'status'  => 'ok',
                'subject' => (string)$composed['subject'],
                'html'    => (string)$composed['html'],
                'text'    => (string)$composed['text'],
                'sample'  => $used['source'],
                'order'   => (string)$used['order'],
            );
        } else {
            $to = isset($request['to']) ? trim((string)$request['to']) : '';
            if (!$can_edit) {
                $response['message'] = $GLOBALS['language']->notification['error_privileges'];
            } elseif (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $response['message'] = $lang['common']['error_email_invalid'];
            } else {
                $mailer->setContentType($content['content_type']);
                if ($mailer->sendEmail($to, $contents, $template_id)) {
                    $response = array('status' => 'ok', 'message' => sprintf($lang['email']['notify_test_sent'], $to));
                } else {
                    $response['message'] = trim($lang['email']['error_test_send'].' '.strip_tags((string)$mailer->ErrorInfo));
                }
            }
        }
    } catch (Throwable $e) {
        $response = array('status' => 'error', 'message' => $smarty_error($e, array($contents['content_html'], $contents['subject'], is_array($template) ? $template['content_html'] : '')));
    }
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

$GLOBALS['gui']->addBreadcrumb($lang['email']['title_email'], currentPage(array('action', 'content_id', 'content_type', 'template_id')));

// The list is one form: which emails are switched on, and which template is the default.
// Both are saved before the page is shown again. (The first used to send the browser away
// before the second was looked at, so a change of default template was never kept.)
$list_saved = array();
if (isset($_POST['email_enabled']) && isset($_POST['email_types']) && is_array($_POST['email_enabled']) && Admin::getInstance()->permissions('documents', CC_PERM_EDIT)) {
    foreach ($_POST['email_types'] as $i => $content_type) {
        $enabled = isset($_POST['email_enabled'][$i]) ? (int)$_POST['email_enabled'][$i] : 1;
        $GLOBALS['db']->update('CubeCart_email_content', array('enabled' => $enabled), array('content_type' => (string)$content_type));
    }
    $list_saved[] = $lang['email']['notify_content_update'];
}

// Two defaults: the template the store's emails are sent in, and the one a new newsletter starts in
foreach (array('template_default', 'newsletter_default') as $default_field) {
    if (isset($_POST[$default_field]) && is_string($_POST[$default_field]) && ctype_digit($_POST[$default_field]) && Admin::getInstance()->permissions('documents', CC_PERM_EDIT)) {
        $chosen  = (int)$_POST[$default_field];
        $current = $GLOBALS['db']->select('CubeCart_email_template', array('template_id'), array($default_field => '1'), false, 1, false, false);
        // Only a template that exists, and only when it is a change: the choice is sent with every save of the list
        if ((!$current || (int)$current[0]['template_id'] !== $chosen) && $GLOBALS['db']->select('CubeCart_email_template', array('template_id'), array('template_id' => $chosen), false, 1, false, false)) {
            $GLOBALS['db']->update('CubeCart_email_template', array($default_field => '0'));
            $GLOBALS['db']->update('CubeCart_email_template', array($default_field => '1'), array('template_id' => $chosen));
            $list_saved[] = $lang['email']['notify_template_default'];
        }
    }
}
if ($list_saved) {
    foreach (array_unique($list_saved) as $message) {
        $GLOBALS['main']->successMessage($message);
    }
    httpredir(currentPage());
}

if (isset($_POST['template']) && is_array($_POST['template']) && Admin::getInstance()->permissions('documents', CC_PERM_EDIT)) {
    $_POST['template']['content_html'] = $no_script(isset($GLOBALS['RAW']['POST']['template']['content_html']) ? $GLOBALS['RAW']['POST']['template']['content_html'] : '');

    ## Save/Update Template
    $proceed = true;
    $redirect = true;
    $html_error = false;

    try {
        $compile_check($_POST['template']['content_html']);
    } catch (Throwable $e) {
        $error_message = htmlentities($smarty_error($e, array($_POST['template']['content_html'])), ENT_QUOTES);
        $GLOBALS['main']->errorMessage($lang['email']['title_content_html'].': '.$error_message);
        $redirect = false;
        $html_error = true;
        // Every email is wrapped in this, so one that cannot be compiled is not saved.
        // The form is shown again with what was typed, so nothing is lost.
        $proceed = false;
    }

    if (empty($_POST['template']['content_html'])) {
        $GLOBALS['main']->errorMessage($lang['email']['error_html_empty']);
        $proceed = false;
    }

    if (!$html_error && strpos($_POST['template']['content_html'], '$EMAIL_CONTENT') === false) {
        $GLOBALS['main']->errorMessage($lang['email']['error_macro_content']);
        $proceed = false;
    }
    if (strlen($_POST['template']['content_html']) > 65535) {
        // The database would cut it off at that length without a word, and every email with it
        $GLOBALS['main']->errorMessage($lang['email']['template_too_long']);
        $proceed = false;
    }
    
    if (!$proceed) {
        // Shown again as it was typed
        $template_retry = $_POST['template'];
    }
    if ($proceed) {
        $record = $_POST['template'];
        // The database layer strips backslashes from what it is given, so shield the template's own
        $record['content_html'] = addslashes($_POST['template']['content_html']);
        if (isset($_POST['template']['template_id']) && is_numeric($_POST['template']['template_id'])) {
            // A form sends every line ending as CRLF. Where that is all that differs from the
            // saved template, keep the saved bytes: opening a template and saving it changes nothing.
            $saved = $GLOBALS['db']->select('CubeCart_email_template', array('content_html'), array('template_id' => (int)$_POST['template']['template_id']), false, 1, false, false);
            if ($saved && preg_replace('/\r\n?/', "\n", $saved[0]['content_html']) === preg_replace('/\r\n?/', "\n", $_POST['template']['content_html'])) {
                unset($record['content_html']);
            }
            $GLOBALS['db']->update('CubeCart_email_template', $record, array('template_id' => (int)$_POST['template']['template_id']));
            $GLOBALS['main']->successMessage($lang['email']['notify_template_update']);
        } else {
            $template_id  = $GLOBALS['db']->insert('CubeCart_email_template', $record);
            $GLOBALS['main']->successMessage($lang['email']['notify_template_create']);
        }
        if ($redirect) {
            httpredir(currentPage(null));
        }
    }
}

## The email design: a few settings that make the template emails are wrapped in
if (isset($_POST['design']) && is_array($_POST['design']) && Admin::getInstance()->permissions('documents', CC_PERM_EDIT)) {
    EmailDesign::save($_POST['design']);
    if (isset($_POST['design']['active'])) {
        EmailDesign::activate($_POST['design']['active'] == '1');
    }
    $GLOBALS['main']->successMessage($lang['email']['design_saved']);
    httpredir(currentPage());
}

## Put one email, in one language, back to the wording it came with
if (isset($_POST['email_reset']) && ctype_digit((string)$_POST['email_reset']) && Admin::getInstance()->permissions('documents', CC_PERM_EDIT)) {
    $reset = $GLOBALS['db']->select('CubeCart_email_content', array('content_id', 'content_type', 'language'), array('content_id' => (int)$_POST['email_reset']), false, 1, false, false);
    $stock = $reset ? $GLOBALS['language']->stockEmail($reset[0]['content_type'], $reset[0]['language']) : false;
    if ($stock) {
        $GLOBALS['db']->update('CubeCart_email_content', array(
            'subject'      => addslashes($stock['subject']),
            'preheader'    => '',
            'content_html' => addslashes($stock['content_html']),
        ), array('content_id' => (int)$reset[0]['content_id']), true, array('subject', 'preheader', 'content_html'));
        $GLOBALS['main']->successMessage($lang['email']['notify_content_reset']);
    } else {
        $GLOBALS['main']->errorMessage($lang['email']['error_content_reset']);
    }
    httpredir(currentPage());
}

if (isset($_POST['content']) && is_array($_POST['content']) && Admin::getInstance()->permissions('documents', CC_PERM_EDIT)) {
    $_POST['content']['content_html'] = $no_script(isset($GLOBALS['RAW']['POST']['content']['content_html']) ? $GLOBALS['RAW']['POST']['content']['content_html'] : '');
    if (isset($GLOBALS['RAW']['POST']['content']['subject'])) {
        $_POST['content']['subject'] = $plain_line($GLOBALS['RAW']['POST']['content']['subject'], 250);
    }
    if (isset($GLOBALS['RAW']['POST']['content']['preheader'])) {
        $_POST['content']['preheader'] = $plain_line($GLOBALS['RAW']['POST']['content']['preheader'], 255);
    }

    $proceed = true;
    $redirect = true;
    $html_error = false;

    // An email that cannot be compiled cannot be sent, so it is not saved. The form is shown
    // again with what was typed, so nothing is lost.
    try {
        $compile_check($_POST['content']['content_html']);
    } catch (Throwable $e) {
        $error_message = htmlentities($smarty_error($e, array($_POST['content']['content_html'])), ENT_QUOTES);
        $GLOBALS['main']->errorMessage($lang['email']['title_content_html'].': '.$error_message);
        $redirect = false;
        $html_error = true;
        $proceed = false;
    }
    // The subject and the preview text go through Smarty when the email is sent as well
    foreach (array('subject' => $lang['common']['subject'], 'preheader' => $lang['email']['preview_text']) as $text_field => $text_label) {
        if (isset($_POST['content'][$text_field]) && $_POST['content'][$text_field] !== '') {
            try {
                $compile_check($_POST['content'][$text_field]);
            } catch (Throwable $e) {
                $GLOBALS['main']->errorMessage($text_label.': '.htmlentities($smarty_error($e, array($_POST['content'][$text_field])), ENT_QUOTES));
                $redirect = false;
                $html_error = true;
                $proceed = false;
            }
        }
    }
    // The database layer strips backslashes from what it is given, so shield the email's own
    $content_record = $_POST['content'];
    foreach (array('subject', 'preheader', 'content_html') as $text_field) {
        if (isset($content_record[$text_field])) {
            $content_record[$text_field] = addslashes($content_record[$text_field]);
        }
    }

    if (empty($_POST['content']['content_html'])) {
        $GLOBALS['main']->errorMessage($lang['email']['error_html_empty']);
        $proceed = false;
    } elseif (strlen($_POST['content']['content_html']) > 65535) {
        // The database would cut it off at that length without a word
        $GLOBALS['main']->errorMessage($lang['email']['editor_too_long']);
        $proceed = false;
        $redirect = false;
    }

    if ($proceed) {
        ## Save/Update Content
        if (isset($_POST['content']['content_id']) && !empty($_POST['content']['content_id'])) {
            ## remove double encoding in repeat regions required to show them in FCK
            if ($GLOBALS['db']->update('CubeCart_email_content', $content_record, array('content_id' => (int)$_POST['content']['content_id']), true, array('subject', 'preheader', 'content_html'))) {
                $GLOBALS['main']->successMessage($lang['email']['notify_content_update']);
                if ($redirect) {
                    httpredir('?_g=documents&node=email&type=content');
                }
            } else {
                $GLOBALS['main']->errorMessage($lang['email']['error_content_update']);
            }
        } else {
            if (!empty($_POST['content']['content_type']) && !empty($_POST['content']['language'])) {
                $check = $GLOBALS['db']->select('CubeCart_email_content', array('content_id'), array('content_type' => $_POST['content']['content_type'], 'language' => $_POST['content']['language']));
                if ($check) {
                    $GLOBALS['main']->errorMessage($lang['email']['error_content_create_exists']);
                    httpredir('?_g=documents&node=email&type=content&action=edit&content_id='.$check[0]['content_id']);
                } else {
                    if ($GLOBALS['db']->insert('CubeCart_email_content', $content_record)) {
                        $GLOBALS['main']->successMessage('Email content saved.');
                        if ($redirect) {
                            httpredir('?_g=documents&node=email&type=content');
                        }
                    } else {
                        $GLOBALS['main']->errorMessage($lang['email']['error_content_create']);
                        if ($redirect) {
                            httpredir(currentPage());
                        }
                    }
                }
            }
        }
    } else {
        if ($redirect) {
            httpredir(currentPage());
        }
        $content_retry = array_intersect_key($_POST['content'], array('subject' => 1, 'preheader' => 1, 'content_html' => 1));
    }
}

###########################################################
$smarty_data = array();
$tab_label = $lang['common']['general'];
if (isset($_GET['action']) && isset($_GET['type'])) {
    switch (strtolower($_GET['type'])) {
    case 'content':
        ## EMAIL CONTENTS
        switch (strtolower($_GET['action'])) {
        case 'delete':
            ## Delete content
            if (isset($_GET['content_id']) && is_numeric($_GET['content_id']) && Admin::getInstance()->permissions('documents', CC_PERM_DELETE)) {
                ## ONLY allow delete if there is more than one translation in the system
                $content_type = $GLOBALS['db']->select('CubeCart_email_content', array('content_type'), array('content_id' => (int)$_GET['content_id']));
                $count = $GLOBALS['db']->numrows('SELECT * FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_email_content` WHERE `content_type` = \''.(string)$content_type[0]['content_type'].'\'');

                if ($count>1) {
                    if ($GLOBALS['db']->delete('CubeCart_email_content', array('content_id' => (int)$_GET['content_id']))) {
                        $GLOBALS['main']->successMessage($lang['email']['notify_content_delete']);
                        httpredir(currentPage(array('action', 'content_id', 'type')));
                    } else {
                        $GLOBALS['main']->errorMessage($lang['email']['error_content_delete']);
                    }
                } else {
                    $GLOBALS['main']->errorMessage($lang['email']['error_content_single']);
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
                    $stored_body = (string)$data['content_html'];
                    $breadcrumb = (isset($email_types[$data['content_type']]['description']) ? $email_types[$data['content_type']]['description'] : $data['subject']).(!empty($data['language']) ? ' ('.$data['language'].')' : '');
                    if (Admin::getInstance()->permissions('documents', CC_PERM_DELETE, false, false)) {
                        $delete = (bool)$GLOBALS['smarty']->assign('LINK_DELETE', currentPage(null, array('action' => 'delete')));
                    }
                } else {
                    ## redirect
                    httpredir(currentPage(array('action', 'content_id', 'type')));
                }
            } elseif (isset($_GET['content_type']) && array_key_exists($_GET['content_type'], $email_types)) {
                ## Create Content
                # $data  = (isset($_POST['content'])) ? $_POST['content'] : array('content_type' => $_GET['content_type']);
                ## Content to translate content
                $content = $GLOBALS['db']->select('CubeCart_email_content', array('content_type', 'language', 'subject', 'content_html'), array('content_type' => (string)$_GET['content_type'], 'language' => $GLOBALS['config']->get('config', 'default_language')));
                $data  = $content[0];
                $existing = $GLOBALS['db']->select('CubeCart_email_content', array('DISTINCT' => 'language'), array('content_type' => $_GET['content_type']));
                $breadcrumb = $lang['common']['create'].': '.(isset($email_types[$_GET['content_type']]['description']) ? $email_types[$_GET['content_type']]['description'] : $_GET['content_type']);
            } else {
                ## Back to main list
                httpredir(currentPage(array('action', 'content_id', 'content_type', 'type')), 'email_contents');
            }
            $lang_list = $GLOBALS['language']->listLanguages();
            if (is_array($lang_list)) {
                $lang_skip = array();
                if (!empty($existing)) {
                    foreach($existing as $l) {
                        array_push($lang_skip, $l['language']);
                    }
                }
                foreach ($lang_list as $langs) {
                    ## If we are adding a translation don't show if it exists alreadt
                    if (($_GET['action']=='add' && !in_array($langs['code'], $lang_skip)) || $_GET['action']=='edit') {
                        if($langs['code'] == $data['language']) {
                            $GLOBALS['smarty']->assign('LANGUAGES', $langs['title']);
                            $GLOBALS['smarty']->assign('ASSIGNED_LANG', array('name' => $langs['title'], 'code' => $langs['code']));
                            $langs['selected'] = ' selected="selected"';
                        } else {
                            $langs['selected'] = '';
                        }
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
            if (!isset($stored_body)) {
                $stored_body = (string)$data['content_html'];
            }
            if (isset($content_retry)) {
                // A save that was refused: the form comes back as it was typed
                $data = array_merge($data, $content_retry);
            }
            // See GitHub #1511
            $data['content_html'] = str_replace(array('empty({$','})}'), array('empty($',')}'), $data['content_html']);
            // Subjects saved before 6.9.0 were stored with their quotes and ampersands as entities
            $data['subject'] = html_entity_decode((string)$data['subject'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $GLOBALS['smarty']->assign('CONTENT', $data);

            if (is_array($email_types[$data['content_type']]['macros'])) {
                foreach ($email_types[$data['content_type']]['macros'] as $tag => $entry) {
                    // An entry is its description, or an array that also carries a short name
                    $smarty_data['macros'][] = array(
                        'name'        => htmlspecialchars($tag),
                        'label'       => (is_array($entry) && !empty($entry['label'])) ? EmailEditor::macroName($entry['label']) : '',
                        'description' => is_array($entry) ? (isset($entry['description']) ? $entry['description'] : '') : $entry,
                    );
                }
                $GLOBALS['smarty']->assign('CONTENT_MACROS', $smarty_data['macros']);
            }
            $GLOBALS['smarty']->assign('DISPLAY_CONTENT_FORM', true);
            $can_edit     = Admin::getInstance()->permissions('documents', CC_PERM_EDIT, false, false);
            $latest_order = EmailSample::usesOrder($data['content_type']) ? EmailSample::latestOrder() : false;
            $sample       = (isset($_GET['sample']) && $_GET['sample'] === EmailSample::SOURCE_EXAMPLE) ? EmailSample::SOURCE_EXAMPLE : EmailSample::SOURCE_LATEST;
            $GLOBALS['smarty']->assign('EMAIL_PREVIEW', array(
                'latest_order' => $latest_order ? $latest_order['number'] : '',
                'sample'       => $sample,
                'test_to'      => Admin::getInstance()->get('email'),
                'can_edit'     => $can_edit,
            ));

            // What the visual editor starts from. Describing the email assigns sample data to
            // Smarty, which this page's own template must not inherit, so that is put back after.
            $page_vars   = $GLOBALS['smarty']->getTemplateVars();
            $type_macros = isset($email_types[$data['content_type']]['macros']) ? $email_types[$data['content_type']]['macros'] : array();
            $existing_id = !empty($data['content_id']) ? (int)$data['content_id'] : 0;
            try {
                $described = EmailEditor::describe($data['content_html'], $data['content_type'], $type_macros, $sample);
                $editor = array(
                    'contentId'    => $existing_id,
                    'contentType'  => $data['content_type'],
                    'language'     => $existing_id ? $data['language'] : '',
                    // The stored bytes the editor was opened on, and whether its units are those bytes
                    'base'         => $existing_id ? md5($stored_body) : '',
                    'refs'         => ($existing_id && $stored_body === $data['content_html']),
                    'units'        => $described['units'],
                    'placeholders' => $described['placeholders'],
                    'sample'       => $described['sample'],
                    'subject'      => EmailEditor::describeLine($data['subject'], $type_macros),
                    'preheader'    => EmailEditor::describeLine(isset($data['preheader']) ? $data['preheader'] : '', $type_macros),
                    'visual'       => false,
                    'canEdit'      => $can_edit,
                    'storeUrl'     => $GLOBALS['storeURL'].'/',
                    'tinymce'      => $GLOBALS['storeURL'].'/includes/tinymce',
                    'groups'       => array(
                        'DATA'     => $lang['email']['editor_group_data'],
                        'BILLING'  => $lang['email']['editor_group_billing'],
                        'SHIPPING' => $lang['email']['editor_group_shipping'],
                    ),
                    'strings'      => array(),
                );
                // Worth showing visually when there is wording to type in
                foreach ($described['units'] as $unit) {
                    if (in_array($unit['kind'], array(EmailSplitter::EDITABLE, EmailSplitter::INLINE, EmailSplitter::COND), true)) {
                        $editor['visual'] = true;
                        break;
                    }
                }
                foreach ($lang['email'] as $name => $string) {
                    if (strpos($name, 'editor_') === 0) {
                        $editor['strings'][$name] = $string;
                    }
                }
                $editor['strings']['cancel'] = $lang['common']['cancel'];
                // Last, because it assigns the template's own values over the sample data
                $editor['frame'] = EmailEditor::frame($data['content_type'], false, !empty($data['language']) ? $data['language'] : '');
                $editor_json = json_encode($editor, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);
            } catch (Throwable $e) {
                // The page still works as a plain form
                $editor_json = false;
                trigger_error('Email editor: '.$e->getMessage(), E_USER_WARNING);
            }
            foreach (array_diff_key($GLOBALS['smarty']->getTemplateVars(), $page_vars) as $name => $value) {
                $GLOBALS['smarty']->clearAssign($name);
            }
            foreach ($page_vars as $name => $value) {
                $GLOBALS['smarty']->assign($name, $value);
            }
            if (!empty($editor_json)) {
                $GLOBALS['smarty']->assign('EMAIL_EDITOR_JSON', $editor_json);
            }
            if (!empty($data['content_type']) && isset($email_types[$data['content_type']]['description'])) {
                $tab_label = $email_types[$data['content_type']]['description'].(!empty($data['language']) ? ' ('.$data['language'].')' : '');
            }
        }
        break;
    case 'design':
        ## EMAIL DESIGN
        $design  = EmailDesign::settings();
        $default = $GLOBALS['db']->select('CubeCart_email_template', array('template_id', 'title'), array('template_default' => '1'), false, 1, false, false);
        $latest_order = EmailSample::latestOrder();
        $GLOBALS['smarty']->assign('DESIGN', $design);
        $GLOBALS['smarty']->assign('DESIGN_ACTIVE', EmailDesign::isActive());
        $GLOBALS['smarty']->assign('DESIGN_CURRENT', $default ? $default[0]['title'] : '');
        $GLOBALS['smarty']->assign('EMAIL_PREVIEW', array(
            'latest_order' => $latest_order ? $latest_order['number'] : '',
            'can_edit'     => Admin::getInstance()->permissions('documents', CC_PERM_EDIT, false, false),
        ));
        $GLOBALS['smarty']->assign('DISPLAY_DESIGN_FORM', true);
        $breadcrumb = $lang['email']['design_title'];
        $tab_label  = $lang['email']['design_title'];
        break;
    case 'template':
        ## EMAIL TEMPLATES
        switch (strtolower($_GET['action'])) {
        case 'delete':
            if (isset($_GET['template_id']) && is_numeric($_GET['template_id']) && Admin::getInstance()->permissions('documents', CC_PERM_DELETE)) {
                if ($GLOBALS['db']->delete('CubeCart_email_template', array('template_id' => (int)$_GET['template_id']))) {
                    httpredir(currentPage(array('action', 'type', 'template_id')), 'email_templates');
                }
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
                        if (Admin::getInstance()->permissions('documents', CC_PERM_DELETE, false, false)) {
                            $delete = (bool)$GLOBALS['smarty']->assign('LINK_DELETE', currentPage(null, array('action' => 'delete')));
                        }
                    }
                    if (isset($template_retry)) {
                        $data = array_merge($data, $template_retry);
                    }
                }
            } else {
                ## Create Template
                $breadcrumb = $lang['common']['create'].': '.$lang['email']['email_template'];
                $data  = isset($template_retry) ? $template_retry : array();
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
            // A template the email design makes is rewritten whenever the design is saved
            $GLOBALS['smarty']->assign('TEMPLATE_MANAGED', (strtolower($_GET['action']) == 'edit' && !empty($data['template_id']) && in_array((int)$data['template_id'], EmailDesign::managed(), true)));

            $macros = array(
                array('name' => '{$EMAIL_CONTENT}', 'description' => $lang['email']['macro_template_content'], 'required' => 'Yes'),
                array('name' => '{$DATA.logoURL}', 'description' => $lang['email']['macro_template_store_logo'], 'required' => 'No'),
                array('name' => '{$DATA.store_name}', 'description' => $lang['email']['macro_template_store_name'], 'required' => 'No'),
                array('name' => '{$DATA.storeURL}', 'description' => $lang['email']['macro_template_store_url'], 'required' => 'No'),
                array('name' => '{$DATA.unsubscribeURL}', 'description' => $lang['email']['macro_template_unsubscribe'], 'required' => 'No'),
            );
            $GLOBALS['smarty']->assign('TEMPLATE_MACROS', $macros);
            // The template is previewed around the order confirmation, which can use a real order
            $latest_order = EmailSample::latestOrder();
            $GLOBALS['smarty']->assign('EMAIL_PREVIEW', array(
                'latest_order' => $latest_order ? $latest_order['number'] : '',
                'can_edit'     => Admin::getInstance()->permissions('documents', CC_PERM_EDIT, false, false),
            ));
            $GLOBALS['smarty']->assign('DISPLAY_TEMPLATE_FORM', true);
            if (!empty($data['title'])) {
                $tab_label = $data['title'];
            }
        }
        break;
    default:
        httpredir(currentPage(array('action', 'type')));
    }
    ## Tabs
    $GLOBALS['main']->addTabControl($tab_label, 'general');
    if (strtolower($_GET['type']) !== 'design') {
        $GLOBALS['main']->addTabControl($lang['email']['title_macros'], 'macros');
    }
    ## Breadcrumbs
    $GLOBALS['gui']->addBreadcrumb($breadcrumb, currentPage());
    // Delete link
    if (isset($delete)) {
        $GLOBALS['smarty']->assign('DISPLAY_DELETE_LINK', true);
    }
} else {
    $GLOBALS['main']->addTabControl($lang['email']['title_email_contents'], 'email_contents');
    $GLOBALS['main']->addTabControl($lang['email']['title_email_templates'], 'email_templates');
    // List Contents
    if (is_array($email_types)) {
        $lang_list = $GLOBALS['language']->listLanguages();
        $max_translations = is_array($lang_list) ? count($lang_list) : 0;
        $can_translate = false;
        foreach ($email_types as $key => $values) {
            $translations = $GLOBALS['db']->select('CubeCart_email_content', array('description','content_id', 'language', 'enabled'), array('content_type' => $key), array('language' => 'ASC'));
            if ($translations) {
                // check language is installed
                $enabled_translations = 0;
                foreach ($translations as $translation) {
                    // Check the translation exists otherwise it's redundant
                    if (file_exists(CC_ROOT_DIR.'/language/'.$translation['language'].'.xml')) {
                        $enabled_translations++;
                        $translation['edit'] = currentPage(null, array('type' => 'content', 'action' => 'edit', 'content_id' => $translation['content_id']));
                        $content['translations'][] = $translation;
                    }
                    if(empty($translation['description']) || $translation['description']!==$key) {
                        $GLOBALS['db']->update('CubeCart_email_content', array('description' => $values['description']), array('content_type' => $key));
                    }
                }
            }
            if($enabled_translations == $max_translations) {
                $content['translate'] = false;
            } else {
                $content['translate'] = currentPage(null, array('type' => 'content', 'action' => 'add', 'content_type' => $key));
                $can_translate = true;
            }
            $content['type']  = $values['description'];
            $content['content_type'] = $key;
            $content['enabled'] = isset($translations[0]['enabled']) ? $translations[0]['enabled'] : 1;
            $smarty_data['e_contents'][] = $content;
            unset($content);
        }
        $GLOBALS['smarty']->assign('CAN_TRANSLATE', $can_translate);
        $GLOBALS['smarty']->assign('EMAIL_CONTENTS', $smarty_data['e_contents']);
    }
    // List Templates
    if (($templates = $GLOBALS['db']->select('CubeCart_email_template')) !== false) {
        $managed_templates = EmailDesign::managed();
        foreach ($templates as $template) {
            $template['managed'] = in_array((int)$template['template_id'], $managed_templates, true);
            $template['clone'] = currentPage(null, array('action' => 'clone', 'type' => 'template', 'template_id' => $template['template_id']));
            $template['delete'] = currentPage(null, array('action' => 'delete', 'type' => 'template', 'template_id' => $template['template_id'], 'token' => SESSION_TOKEN));
            $template['edit'] = currentPage(null, array('action' => 'edit', 'type' => 'template', 'template_id' => $template['template_id']));
            $smarty_data['e_templates'][] = $template;
        }
        $GLOBALS['smarty']->assign('EMAIL_TEMPLATES', $smarty_data['e_templates']);
    }
    $GLOBALS['smarty']->assign('TEMPLATE_CREATE', currentPage(null, array('action' => 'create', 'type' => 'template')));
    $GLOBALS['smarty']->assign('DESIGN_LINK', currentPage(null, array('action' => 'edit', 'type' => 'design')));
    $GLOBALS['smarty']->assign('DISPLAY_EMAIL_LIST', true);
}
$page_content = $GLOBALS['smarty']->fetch('templates/documents.email.php');
