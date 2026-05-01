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

$email_types = array(
    'account.password_recovery' => array(
        'description' => $lang['email']['title_macro_user_password'],
        'macros' => array(
            '{$DATA.first_name}' => $lang['email']['macro_first_name'],
            '{$DATA.last_name}'  => $lang['email']['macro_last_name'],
            '{$DATA.reset_link}'  => $lang['email']['macro_link_password'],
        ),
    ),
    'newsletter.verify_email' => array(
        'description' => $lang['email']['title_macro_user_newsletter'],
        'macros' => array(
            '{$DATA.email}' => $lang['common']['email'],
            '{$DATA.link}'  => $lang['email']['macro_link_verify']
        ),
    ),
    'newsletter.remove_request' => array(
        'description' => $lang['email']['title_macro_user_newsletter_remove'],
        'macros' => array(
            '{$DATA.email}' => $lang['common']['email'],
            '{$DATA.link}'  => $lang['email']['macro_link_verify']
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
            '{$SHIPPING.w3w}'   => $lang['email']['macro_w3w_d'],
            '{$BILLING.first_name}'  => $lang['email']['macro_first_name'],
            '{$BILLING.last_name}'  => $lang['email']['macro_first_name'],
            '{$BILLING.company_name}'  => $lang['email']['macro_company_name'],
            '{$BILLING.line1}'    => $lang['email']['macro_line1'],
            '{$BILLING.line2}'    => $lang['email']['macro_line2'],
            '{$BILLING.town}'    => $lang['email']['macro_town'],
            '{$BILLING.state}'    => $lang['email']['macro_state'],
            '{$BILLING.postcode}'   => $lang['email']['macro_postcode'],
            '{$BILLING.country}'   => $lang['email']['macro_country'],
            '{$BILLING.w3w}' => $lang['email']['macro_w3w'],
            '{$BILLING.phone}'    => $lang['email']['macro_phone'],
            '{$BILLING.email}'    => $lang['email']['macro_email'],
            '{$DATA.cart_order_id}'  => $lang['email']['macro_order_number_t'],
            '{$DATA.custom_oid}'  => $lang['email']['macro_order_number_i'],
            '{$DATA.order_date}'   => $lang['email']['macro_order_date'],
            '{$DATA.ship_method}'  => $lang['email']['macro_order_shipping_method'],
            '{$DATA.ship_product'  => $lang['email']['macro_order_shipping_product'],
            '{$DATA.shipping}'   => $lang['email']['macro_order_shipping'],
            '{$DATA.discount}'   => $lang['email']['macro_order_discount'],
            '{$DATA.credit_used}' => $lang['email']['macro_order_credit_used'],
            '{$DATA.subtotal}'   => $lang['email']['macro_order_subtotal'],
            '{$DATA.total}'    => $lang['email']['macro_order_total'],
            '{$DATA.link}'    => $lang['email']['macro_link_order'],
            '{$DATA.gateway}'   => $lang['email']['macro_gateway'],
            '{$DATA.customer_comments}' => $lang['email']['customer_comments'],
            '{$product.name}'   => $lang['email']['macro_product_name'],
            '{$product.product_code}' => $lang['email']['macro_product_code'],
            '{$product.quantity}'  => $lang['email']['macro_product_quantity'],
            '{$product.price}'   => $lang['email']['macro_product_price'],
            '{$product.product_options}' => $lang['email']['macro_product_options'],
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
    'admin.two_factor_code' => array(
        'description' => $lang['email']['title_macro_admin_two_factor_code'],
        'macros' => array(
            '{$DATA.name}' => $lang['email']['macro_admin_name'],
            '{$DATA.code}' => $lang['email']['macro_twofa_code'],
        ),
    ),
    'admin.new_device_login' => array(
        'description' => $lang['email']['title_macro_admin_new_device_login'],
        'macros' => array(
            '{$DATA.name}' => $lang['email']['macro_admin_name'],
            '{$DATA.new_ip}' => $lang['email']['macro_new_ip'],
            '{$DATA.previous_ip}' => $lang['email']['macro_previous_ip'],
            '{$DATA.new_browser}' => $lang['email']['macro_new_browser'],
            '{$DATA.login_time}' => $lang['email']['macro_login_time'],
        ),
    ),
    'cart.digital_download' => array(
        'description' => $lang['email']['title_macro_cart_digital'],
        'macros' => array(
            '{$DATA.first_name}' => $lang['email']['macro_first_name'],
            '{$DATA.last_name}' => $lang['email']['macro_last_name'],
            '{$download.name}' => $lang['email']['macro_product_name'],
            '{$download.expire}' => $lang['email']['macro_download_expire'],
            '{$download.url}'  => $lang['email']['macro_link_download'],
            '{$download.stream}'  => $lang['email']['macro_stream']
        ),
    ),
    'cart.gift_certificate' => array(
        'description' => $lang['email']['title_macro_cart_certificate'],
        'macros' => array(
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
        'macros' => array(
            '{$SHIPPING.first_name}' => $lang['email']['macro_first_name_d'],
            '{$SHIPPING.last_name}' => $lang['email']['macro_last_name_d'],
            '{$SHIPPING.company_name}' => $lang['email']['macro_company_name_d'],
            '{$SHIPPING.line1}' => $lang['email']['macro_line1_d'],
            '{$SHIPPING.line2}' => $lang['email']['macro_line2_d'],
            '{$SHIPPING.town}' => $lang['email']['macro_town_d'],
            '{$SHIPPING.state}' => $lang['email']['macro_state_d'],
            '{$SHIPPING.postcode}' => $lang['email']['macro_postcode_d'],
            '{$SHIPPING.country}' => $lang['email']['macro_country_d'],
            '{$SHIPPING.w3w}' => $lang['email']['macro_w3w_d'],
            '{$BILLING.first_name}' => $lang['email']['macro_first_name'],
            '{$BILLING.last_name}' => $lang['email']['macro_last_name'],
            '{$BILLING.company_name}' => $lang['email']['macro_company_name'],
            '{$BILLING.line1}' => $lang['email']['macro_line1'],
            '{$BILLING.line2}' => $lang['email']['macro_line2'],
            '{$BILLING.town}' => $lang['email']['macro_town'],
            '{$BILLING.state}' => $lang['email']['macro_state'],
            '{$BILLING.postcode}' => $lang['email']['macro_postcode'],
            '{$BILLING.country}' => $lang['email']['macro_country'],
            '{$BILLING.w3w}' => $lang['email']['macro_w3w'],
            '{$BILLING.phone}' => $lang['email']['macro_phone'],
            '{$BILLING.email}' => $lang['email']['macro_email'],
            '{$DATA.first_name}' => $lang['email']['macro_first_name'],
            '{$DATA.last_name}' => $lang['email']['macro_last_name'],
            '{$DATA.cart_order_id}' => $lang['email']['macro_order_number_t'],
            '{$DATA.custom_oid}' => $lang['email']['macro_order_number_i'],
            '{$DATA.order_date}' => $lang['email']['macro_order_date'],
            '{$DATA.ship_method}' => $lang['email']['macro_order_shipping_method'],
            '{$DATA.ship_product}' => $lang['email']['macro_order_shipping_product'],
            '{$DATA.shipping}' => $lang['email']['macro_order_shipping'],
            '{$DATA.discount}' => $lang['email']['macro_order_discount'],
            '{$DATA.credit_used}' => $lang['email']['macro_order_credit_used'],
            '{$DATA.subtotal}' => $lang['email']['macro_order_subtotal'],
            '{$DATA.total}' => $lang['email']['macro_order_total'],
            '{$DATA.link}' => $lang['email']['macro_link_order'],
            '{$DATA.gateway}' => $lang['email']['macro_gateway'],
            '{$DATA.customer_comments}' => $lang['email']['customer_comments'],
            '{$product.name}' => $lang['email']['macro_product_name'],
            '{$product.product_code}' => $lang['email']['macro_product_code'],
            '{$product.quantity}' => $lang['email']['macro_product_quantity'],
            '{$product.price}' => $lang['email']['macro_product_price'],
            '{$product.product_options}' => $lang['email']['macro_product_options'],
            '{$tax.tax_name}' => $lang['email']['macro_tax_name'],
            '{$tax.tax_percent}' => $lang['email']['macro_tax_rate'],
            '{$tax.tax_amount}' => $lang['email']['macro_tax_amount']
        ),
    ),
    'cart.order_confirmation' => array(
        'description' => $lang['email']['title_macro_order_confirmed'],
        'macros' => array(
            '{$SHIPPING.first_name}' => $lang['email']['macro_first_name_d'],
            '{$SHIPPING.last_name}' => $lang['email']['macro_last_name_d'],
            '{$SHIPPING.company_name}' => $lang['email']['macro_company_name_d'],
            '{$SHIPPING.line1}' => $lang['email']['macro_line1_d'],
            '{$SHIPPING.line2}' => $lang['email']['macro_line2_d'],
            '{$SHIPPING.town}' => $lang['email']['macro_town_d'],
            '{$SHIPPING.state}' => $lang['email']['macro_state_d'],
            '{$SHIPPING.postcode}' => $lang['email']['macro_postcode_d'],
            '{$SHIPPING.country}' => $lang['email']['macro_country_d'],
            '{$SHIPPING.w3w}'   => $lang['email']['macro_w3w_d'],
            '{$BILLING.first_name}' => $lang['email']['macro_first_name'],
            '{$BILLING.last_name}' => $lang['email']['macro_first_name'],
            '{$BILLING.company_name}' => $lang['email']['macro_company_name'],
            '{$BILLING.line1}' => $lang['email']['macro_line1'],
            '{$BILLING.line2}' => $lang['email']['macro_line2'],
            '{$BILLING.town}' => $lang['email']['macro_town'],
            '{$BILLING.state}' => $lang['email']['macro_state'],
            '{$BILLING.postcode}' => $lang['email']['macro_postcode'],
            '{$BILLING.country}' => $lang['email']['macro_country'],
            '{$BILLING.w3w}' => $lang['email']['macro_w3w'],
            '{$BILLING.phone}' => $lang['email']['macro_phone'],
            '{$BILLING.email}' => $lang['email']['macro_email'],
            '{$DATA.cart_order_id}'  => $lang['email']['macro_order_number_t'],
            '{$DATA.custom_oid}'  => $lang['email']['macro_order_number_i'],
            '{$DATA.order_date}' => $lang['email']['macro_order_date'],
            '{$DATA.ship_method}'=> $lang['email']['macro_order_shipping_method'],
            '{$DATA.ship_product'  => $lang['email']['macro_order_shipping_product'],
            '{$DATA.shipping}' => $lang['email']['macro_order_shipping'],
            '{$DATA.discount}' => $lang['email']['macro_order_discount'],
            '{$DATA.credit_used}' => $lang['email']['macro_order_credit_used'],
            '{$DATA.subtotal}' => $lang['email']['macro_order_subtotal'],
            '{$DATA.total}'  => $lang['email']['macro_order_total'],
            '{$DATA.link}'  => $lang['email']['macro_link_order'],
            '{$DATA.gateway}' => $lang['email']['macro_gateway'],
            '{$DATA.customer_comments}' => $lang['email']['customer_comments'],
            '{$product.name}'  => $lang['email']['macro_product_name'],
            '{$product.product_code}' => $lang['email']['macro_product_code'],
            '{$product.quantity}' => $lang['email']['macro_product_quantity'],
            '{$product.price}'  => $lang['email']['macro_product_price'],
            '{$product.product_options}' => $lang['email']['macro_product_options'],
            '{$tax.tax_name}' => $lang['email']['macro_tax_name'],
            '{$tax.tax_percent}' => $lang['email']['macro_tax_rate'],
            '{$tax.tax_amount}' => $lang['email']['macro_tax_amount']
        ),
    ),
    'cart.order_complete' => array(
        'description' => $lang['email']['title_macro_order_dispatched'],
        'macros' => array(
            '{$SHIPPING.first_name}' => $lang['email']['macro_first_name_d'],
            '{$SHIPPING.last_name}' => $lang['email']['macro_last_name_d'],
            '{$SHIPPING.company_name}' => $lang['email']['macro_company_name_d'],
            '{$SHIPPING.line1}' => $lang['email']['macro_line1_d'],
            '{$SHIPPING.line2}' => $lang['email']['macro_line2_d'],
            '{$SHIPPING.town}' => $lang['email']['macro_town_d'],
            '{$SHIPPING.state}' => $lang['email']['macro_state_d'],
            '{$SHIPPING.postcode}' => $lang['email']['macro_postcode_d'],
            '{$SHIPPING.country}' => $lang['email']['macro_country_d'],
            '{$SHIPPING.w3w}'   => $lang['email']['macro_w3w_d'],
            '{$BILLING.first_name}' => $lang['email']['macro_first_name'],
            '{$BILLING.last_name}' => $lang['email']['macro_first_name'],
            '{$BILLING.company_name}' => $lang['email']['macro_company_name'],
            '{$BILLING.line1}' => $lang['email']['macro_line1'],
            '{$BILLING.line2}' => $lang['email']['macro_line2'],
            '{$BILLING.town}' => $lang['email']['macro_town'],
            '{$BILLING.state}' => $lang['email']['macro_state'],
            '{$BILLING.postcode}' => $lang['email']['macro_postcode'],
            '{$BILLING.country}' => $lang['email']['macro_country'],
            '{$BILLING.w3w}' => $lang['email']['macro_w3w'],
            '{$BILLING.phone}' => $lang['email']['macro_phone'],
            '{$BILLING.email}' => $lang['email']['macro_email'],
            '{$DATA.cart_order_id}'  => $lang['email']['macro_order_number_t'],
            '{$DATA.custom_oid}'  => $lang['email']['macro_order_number_i'],
            '{$DATA.order_date}' => $lang['email']['macro_order_date'],
            '{$DATA.ship_method}'=> $lang['email']['macro_order_shipping_method'],
            '{$DATA.ship_product'  => $lang['email']['macro_order_shipping_product'],
            '{$DATA.shipping}' => $lang['email']['macro_order_shipping'],
            '{$DATA.discount}' => $lang['email']['macro_order_discount'],
            '{$DATA.credit_used}' => $lang['email']['macro_order_credit_used'],
            '{$DATA.subtotal}' => $lang['email']['macro_order_subtotal'],
            '{$DATA.total}'  => $lang['email']['macro_order_total'],
            '{$DATA.link}'  => $lang['email']['macro_link_order'],
            '{$DATA.gateway}' => $lang['email']['macro_gateway'],
            '{$DATA.ship_tracking}'  => $lang['email']['macro_ship_tracking'],
            '{$DATA.ship_date}'  => $lang['email']['macro_ship_date'],
            '{$DATA.first_name}' => $lang['email']['macro_first_name'], // back compatibility help
            '{$DATA.last_name}' => $lang['email']['macro_first_name'], // back compatibility help
            '{$DATA.customer_comments}' => $lang['email']['customer_comments'],
            '{$product.name}'  => $lang['email']['macro_product_name'],
            '{$product.product_code}' => $lang['email']['macro_product_code'],
            '{$product.quantity}' => $lang['email']['macro_product_quantity'],
            '{$product.price}'  => $lang['email']['macro_product_price'],
            '{$product.product_options}' => $lang['email']['macro_product_options'],
            '{$tax.tax_name}' => $lang['email']['macro_tax_name'],
            '{$tax.tax_percent}' => $lang['email']['macro_tax_rate'],
            '{$tax.tax_amount}' => $lang['email']['macro_tax_amount']
        ),
    ),
    'cart.payment_fraud' => array(
        'description' => $lang['email']['title_macro_order_fraud'],
        'macros' => array(
            '{$SHIPPING.first_name}' => $lang['email']['macro_first_name_d'],
            '{$SHIPPING.last_name}' => $lang['email']['macro_last_name_d'],
            '{$SHIPPING.company_name}' => $lang['email']['macro_company_name_d'],
            '{$SHIPPING.line1}' => $lang['email']['macro_line1_d'],
            '{$SHIPPING.line2}' => $lang['email']['macro_line2_d'],
            '{$SHIPPING.town}' => $lang['email']['macro_town_d'],
            '{$SHIPPING.state}' => $lang['email']['macro_state_d'],
            '{$SHIPPING.postcode}' => $lang['email']['macro_postcode_d'],
            '{$SHIPPING.country}' => $lang['email']['macro_country_d'],
            '{$SHIPPING.w3w}' => $lang['email']['macro_w3w_d'],
            '{$BILLING.first_name}' => $lang['email']['macro_first_name'],
            '{$BILLING.last_name}' => $lang['email']['macro_last_name'],
            '{$BILLING.company_name}' => $lang['email']['macro_company_name'],
            '{$BILLING.line1}' => $lang['email']['macro_line1'],
            '{$BILLING.line2}' => $lang['email']['macro_line2'],
            '{$BILLING.town}' => $lang['email']['macro_town'],
            '{$BILLING.state}' => $lang['email']['macro_state'],
            '{$BILLING.postcode}' => $lang['email']['macro_postcode'],
            '{$BILLING.country}' => $lang['email']['macro_country'],
            '{$BILLING.w3w}' => $lang['email']['macro_w3w'],
            '{$BILLING.phone}' => $lang['email']['macro_phone'],
            '{$BILLING.email}' => $lang['email']['macro_email'],
            '{$DATA.first_name}' => $lang['email']['macro_first_name'],
            '{$DATA.last_name}' => $lang['email']['macro_last_name'],
            '{$DATA.cart_order_id}' => $lang['email']['macro_order_number_t'],
            '{$DATA.custom_oid}' => $lang['email']['macro_order_number_i'],
            '{$DATA.order_date}' => $lang['email']['macro_order_date'],
            '{$DATA.ship_method}' => $lang['email']['macro_order_shipping_method'],
            '{$DATA.ship_product}' => $lang['email']['macro_order_shipping_product'],
            '{$DATA.shipping}' => $lang['email']['macro_order_shipping'],
            '{$DATA.discount}' => $lang['email']['macro_order_discount'],
            '{$DATA.credit_used}' => $lang['email']['macro_order_credit_used'],
            '{$DATA.subtotal}' => $lang['email']['macro_order_subtotal'],
            '{$DATA.total}' => $lang['email']['macro_order_total'],
            '{$DATA.link}' => $lang['email']['macro_link_order'],
            '{$DATA.gateway}' => $lang['email']['macro_gateway'],
            '{$DATA.customer_comments}' => $lang['email']['customer_comments'],
            '{$product.name}' => $lang['email']['macro_product_name'],
            '{$product.product_code}' => $lang['email']['macro_product_code'],
            '{$product.quantity}' => $lang['email']['macro_product_quantity'],
            '{$product.price}' => $lang['email']['macro_product_price'],
            '{$product.product_options}' => $lang['email']['macro_product_options'],
            '{$tax.tax_name}' => $lang['email']['macro_tax_name'],
            '{$tax.tax_percent}' => $lang['email']['macro_tax_rate'],
            '{$tax.tax_amount}' => $lang['email']['macro_tax_amount']
        ),
    ),
    'cart.payment_received' => array(
        'description' => $lang['email']['title_macro_order_payment'],
        'macros' => array(
            '{$SHIPPING.first_name}' => $lang['email']['macro_first_name_d'],
            '{$SHIPPING.last_name}' => $lang['email']['macro_last_name_d'],
            '{$SHIPPING.company_name}' => $lang['email']['macro_company_name_d'],
            '{$SHIPPING.line1}' => $lang['email']['macro_line1_d'],
            '{$SHIPPING.line2}' => $lang['email']['macro_line2_d'],
            '{$SHIPPING.town}' => $lang['email']['macro_town_d'],
            '{$SHIPPING.state}' => $lang['email']['macro_state_d'],
            '{$SHIPPING.postcode}' => $lang['email']['macro_postcode_d'],
            '{$SHIPPING.country}' => $lang['email']['macro_country_d'],
            '{$SHIPPING.w3w}' => $lang['email']['macro_w3w_d'],
            '{$BILLING.first_name}' => $lang['email']['macro_first_name'],
            '{$BILLING.last_name}' => $lang['email']['macro_last_name'],
            '{$BILLING.company_name}' => $lang['email']['macro_company_name'],
            '{$BILLING.line1}' => $lang['email']['macro_line1'],
            '{$BILLING.line2}' => $lang['email']['macro_line2'],
            '{$BILLING.town}' => $lang['email']['macro_town'],
            '{$BILLING.state}' => $lang['email']['macro_state'],
            '{$BILLING.postcode}' => $lang['email']['macro_postcode'],
            '{$BILLING.country}' => $lang['email']['macro_country'],
            '{$BILLING.w3w}' => $lang['email']['macro_w3w'],
            '{$BILLING.phone}' => $lang['email']['macro_phone'],
            '{$BILLING.email}' => $lang['email']['macro_email'],
            '{$DATA.first_name}' => $lang['email']['macro_first_name'],
            '{$DATA.last_name}' => $lang['email']['macro_last_name'],
            '{$DATA.cart_order_id}' => $lang['email']['macro_order_number_t'],
            '{$DATA.custom_oid}' => $lang['email']['macro_order_number_i'],
            '{$DATA.order_date}' => $lang['email']['macro_order_date'],
            '{$DATA.ship_method}' => $lang['email']['macro_order_shipping_method'],
            '{$DATA.ship_product}' => $lang['email']['macro_order_shipping_product'],
            '{$DATA.shipping}' => $lang['email']['macro_order_shipping'],
            '{$DATA.discount}' => $lang['email']['macro_order_discount'],
            '{$DATA.credit_used}' => $lang['email']['macro_order_credit_used'],
            '{$DATA.subtotal}' => $lang['email']['macro_order_subtotal'],
            '{$DATA.total}' => $lang['email']['macro_payment_amount'],
            '{$DATA.link}' => $lang['email']['macro_link_order'],
            '{$DATA.gateway}' => $lang['email']['macro_gateway'],
            '{$DATA.customer_comments}' => $lang['email']['customer_comments'],
            '{$product.name}' => $lang['email']['macro_product_name'],
            '{$product.product_code}' => $lang['email']['macro_product_code'],
            '{$product.quantity}' => $lang['email']['macro_product_quantity'],
            '{$product.price}' => $lang['email']['macro_product_price'],
            '{$product.product_options}' => $lang['email']['macro_product_options'],
            '{$tax.tax_name}' => $lang['email']['macro_tax_name'],
            '{$tax.tax_percent}' => $lang['email']['macro_tax_rate'],
            '{$tax.tax_amount}' => $lang['email']['macro_tax_amount']
        ),
    ),
    'cart.abandoned' => array(
        'description' => $lang['email']['title_macro_cart_abandoned'],
        'macros' => array(
            '{$DATA.first_name}' => $lang['email']['macro_first_name'],
            '{$DATA.last_name}' => $lang['email']['macro_last_name'],
            '{$DATA.store_name}' => $lang['email']['macro_store_name'],
            '{$DATA.recovery_link}' => $lang['email']['macro_recovery_link'],
            '{$DATA.optout_link}' => $lang['email']['macro_optout_link'],
            '{$product.name}' => $lang['email']['macro_product_name'],
            '{$product.quantity}' => $lang['email']['macro_product_quantity'],
            '{$product.price}' => $lang['email']['macro_product_price'],
            '{$product.options}' => $lang['email']['macro_product_options'],
            '{$product.image}' => $lang['email']['macro_product_image'],
        ),
    ),
    'catalogue.tell_friend' => array(
        'description' => $lang['email']['title_macro_tell_friend'],
        'macros'  => array(
            '{$DATA.to}'  => $lang['email']['macro_tell_friend'],
            '{$DATA.from}' => $lang['email']['macro_sender_name'],
            '{$DATA.name}' => $lang['email']['macro_product_name'],
            '{$DATA.link}' => $lang['email']['macro_link_product'],
            '{$DATA.message}'=> $lang['email']['macro_tell_message'],
        ),
    ),
);
## Add hook
foreach ($GLOBALS['hooks']->load('admin.documents.email.macros') as $hook) {
    include $hook;
}

$GLOBALS['gui']->addBreadcrumb($lang['email']['title_email'], currentPage(array('action', 'content_id', 'content_type', 'template_id')));

if (isset($_POST['email_enabled']) && isset($_POST['email_types']) && is_array($_POST['email_enabled']) && Admin::getInstance()->permissions('documents', CC_PERM_EDIT)) {
    foreach ($_POST['email_types'] as $i => $content_type) {
        $enabled = isset($_POST['email_enabled'][$i]) ? (int)$_POST['email_enabled'][$i] : 1;
        $GLOBALS['db']->update('CubeCart_email_content', array('enabled' => $enabled), array('content_type' => (string)$content_type));
    }
    $GLOBALS['main']->successMessage($lang['common']['save_success']);
    httpredir(currentPage());
}

if (isset($_POST['template_default']) && ctype_digit($_POST['template_default']) && Admin::getInstance()->permissions('documents', CC_PERM_EDIT)) {
    $GLOBALS['db']->update('CubeCart_email_template', array('template_default' => '0'));
    $GLOBALS['db']->update('CubeCart_email_template', array('template_default' => '1'), array('template_id' => (int)$_POST['template_default']));

    ## Update default template
    $GLOBALS['main']->successMessage($lang['email']['notify_template_default']);
    httpredir(currentPage());
}

if (isset($_POST['template'])) {
    $_POST['template']['content_html'] = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $GLOBALS['RAW']['POST']['template']['content_html']);

    ## Save/Update Template
    $proceed = true;
    $redirect = true;
    $html_error = false;

    try {
        $GLOBALS['smarty']->fetch('string:'.$_POST['template']['content_html']);
    } catch (Exception $e) {
        $error_message = str_replace('string:', '', htmlentities($e->getMessage(), ENT_QUOTES));
        $GLOBALS['main']->errorMessage($lang['email']['title_content_html'].': '.$error_message);
        $redirect = false;
        $html_error = true;
    }

    if (empty($_POST['template']['content_html'])) {
        $GLOBALS['main']->errorMessage($lang['email']['error_html_empty']);
        $proceed = false;
    }

    if (!$html_error && strpos($_POST['template']['content_html'], '$EMAIL_CONTENT') === false) {
        $GLOBALS['main']->errorMessage($lang['email']['error_macro_content']);
        $proceed = false;
    }
    
    if ($proceed && Admin::getInstance()->permissions('documents', CC_PERM_EDIT)) {
        if (isset($_POST['template']['template_id']) && is_numeric($_POST['template']['template_id'])) {
            $GLOBALS['db']->update('CubeCart_email_template', $_POST['template'], array('template_id' => (int)$_POST['template']['template_id']));
            $GLOBALS['main']->successMessage($lang['email']['notify_template_update']);
        } else {
            $template_id  = $GLOBALS['db']->insert('CubeCart_email_template', $_POST['template']);
            $GLOBALS['main']->successMessage($lang['email']['notify_template_create']);
        }
        if ($redirect) {
            httpredir(currentPage(null));
        }
    }
}

if (isset($_POST['content']) && Admin::getInstance()->permissions('documents', CC_PERM_EDIT)) {
    $_POST['content']['content_html'] = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $GLOBALS['RAW']['POST']['content']['content_html']);

    $proceed = true;
    $redirect = true;
    $html_error = false;

    try {
        $GLOBALS['smarty']->fetch('string:'.$_POST['content']['content_html']);
    } catch (Exception $e) {
        $error_message = str_replace('string:', '', strip_tags($e->getMessage()));
        $GLOBALS['main']->errorMessage($lang['email']['title_content_html'].': '.$error_message);
        $redirect = false;
        $html_error = true;
    }

    if (empty($_POST['content']['content_html'])) {
        $GLOBALS['main']->errorMessage($lang['email']['error_html_empty']);
        $proceed = false;
    }

    if ($proceed) {
        ## Save/Update Content
        if (isset($_POST['content']['content_id']) && !empty($_POST['content']['content_id'])) {
            ## remove double encoding in repeat regions required to show them in FCK
            if ($GLOBALS['db']->update('CubeCart_email_content', $_POST['content'], array('content_id' => (int)$_POST['content']['content_id']))) {
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
                    if ($GLOBALS['db']->insert('CubeCart_email_content', $_POST['content'])) {
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
                    $breadcrumb = (isset($email_types[$data['content_type']]['description']) ? $email_types[$data['content_type']]['description'] : $data['subject']).(!empty($data['language']) ? ' ('.$data['language'].')' : '');
                    $delete = (bool)$GLOBALS['smarty']->assign('LINK_DELETE', currentPage(null, array('action' => 'delete')));
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
            // See GitHub #1511
            $data['content_html'] = str_replace(array('empty({$','})}'), array('empty($',')}'), $data['content_html']);
            $GLOBALS['smarty']->assign('CONTENT', $data);

            if (is_array($email_types[$data['content_type']]['macros'])) {
                foreach ($email_types[$data['content_type']]['macros'] as $macro => $desc) {
                    $macro_data['name'] = htmlspecialchars($macro);
                    $macro_data['description'] = $desc;

                    $smarty_data['macros'][] = $macro_data;
                }
                $GLOBALS['smarty']->assign('CONTENT_MACROS', $smarty_data['macros']);
            }
            $default_template = $GLOBALS['db']->select('CubeCart_email_template', array('content_html'), array('template_default' => '1'));
            if (is_array($default_template) && !empty($default_template[0]['content_html'])) {
                $tmpl_json = json_encode($default_template[0]['content_html'], JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
                if ($tmpl_json !== false) {
                    $GLOBALS['smarty']->assign('DEFAULT_TEMPLATE_JSON', $tmpl_json);
                }
            }
            $preview_macros = array(
                'logoURL'        => $GLOBALS['gui']->getLogo(true, 'emails'),
                'store_name'     => $GLOBALS['config']->get('config', 'store_name'),
                'storeName'      => $GLOBALS['config']->get('config', 'store_name'),
                'storeURL'       => $GLOBALS['storeURL'],
                'unsubscribeURL' => $GLOBALS['storeURL'].'/index.php?_a=unsubscribe',
                'jsonLd'         => '',
            );
            $macros_json = json_encode($preview_macros, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
            if ($macros_json !== false) {
                $GLOBALS['smarty']->assign('PREVIEW_MACROS_JSON', $macros_json);
            }
            $GLOBALS['smarty']->assign('DISPLAY_CONTENT_FORM', true);
            if (!empty($data['content_type']) && isset($email_types[$data['content_type']]['description'])) {
                $tab_label = $email_types[$data['content_type']]['description'].(!empty($data['language']) ? ' ('.$data['language'].')' : '');
            }
        }
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
                        $delete = (bool)$GLOBALS['smarty']->assign('LINK_DELETE', currentPage(null, array('action' => 'delete')));
                    }
                    if (isset($_POST['template'])) {
                        $data = array_merge($data, $_POST['template']);
                    }
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
            $preview_macros = array(
                'logoURL'        => $GLOBALS['gui']->getLogo(true, 'emails'),
                'store_name'     => $GLOBALS['config']->get('config', 'store_name'),
                'storeName'      => $GLOBALS['config']->get('config', 'store_name'),
                'storeURL'       => $GLOBALS['storeURL'],
                'unsubscribeURL' => $GLOBALS['storeURL'].'/index.php?_a=unsubscribe',
                'jsonLd'         => '',
            );
            $macros_json = json_encode($preview_macros, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
            if ($macros_json !== false) {
                $GLOBALS['smarty']->assign('PREVIEW_MACROS_JSON', $macros_json);
            }
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
    $GLOBALS['main']->addTabControl($lang['email']['title_macros'], 'macros');
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
        $template_html_map = array();
        foreach ($templates as $template) {
            $template['clone'] = currentPage(null, array('action' => 'clone', 'type' => 'template', 'template_id' => $template['template_id']));
            $template['delete'] = currentPage(null, array('action' => 'delete', 'type' => 'template', 'template_id' => $template['template_id'], 'token' => SESSION_TOKEN));
            $template['edit'] = currentPage(null, array('action' => 'edit', 'type' => 'template', 'template_id' => $template['template_id']));
            $template_html_map[(int)$template['template_id']] = (string)$template['content_html'];
            $smarty_data['e_templates'][] = $template;
        }
        $GLOBALS['smarty']->assign('EMAIL_TEMPLATES', $smarty_data['e_templates']);
        $GLOBALS['smarty']->assign('EMAIL_TEMPLATE_HTML_JSON', json_encode($template_html_map, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));
        $list_preview_macros = array(
            'logoURL'        => $GLOBALS['gui']->getLogo(true, 'emails'),
            'store_name'     => $GLOBALS['config']->get('config', 'store_name'),
            'storeName'      => $GLOBALS['config']->get('config', 'store_name'),
            'storeURL'       => $GLOBALS['storeURL'],
            'unsubscribeURL' => $GLOBALS['storeURL'].'/index.php?_a=unsubscribe',
            'jsonLd'         => '',
        );
        $GLOBALS['smarty']->assign('PREVIEW_MACROS_JSON', json_encode($list_preview_macros, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));
    }
    $GLOBALS['smarty']->assign('TEMPLATE_CREATE', currentPage(null, array('action' => 'create', 'type' => 'template')));
    $GLOBALS['smarty']->assign('DISPLAY_EMAIL_LIST', true);
}
$page_content = $GLOBALS['smarty']->fetch('templates/documents.email.php');
