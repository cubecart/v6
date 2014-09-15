<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */
function iceTranslate($key) {
    // TO-DO use Cubecart's translate
    $translations = array(
        'AMEX' => 'American Express',
        'VISA' => 'Visa',
        'MASTER' => 'Mastercard',
        'ABNAMRO' => 'ABN AMRO',
        'ASNBANK' => 'ASN Bank',
        'FRIESLAND' => 'Frieslandse Bank',
        'ING' => 'ING',
        'RABOBANK' => 'Rabobank',
        'SNSBANK' => 'SNS Bank',
        'SNSREGIOBANK' => 'SNS Regio Bank',
        'TRIODOSBANK' => 'Triodos Bank',
        'VANLANSCHOT' => 'Van Lanschot'
    );

    if (array_key_exists($key, $translations)) {
        return $translations[$key];
    }

    return $key;
}

if (!defined('CC_INI_SET'))
    die('Access Denied');

require(realpath(dirname(dirname(__FILE__))) . '/api/icepay_api_basic.php');

$module = new Module(__FILE__, $_GET['module'], 'admin/index.tpl', true, false);

$paymentMethods = Icepay_Api_Basic::getInstance()->readFolder()->getObject();

$icepayPaymentMethods = "<option value='icepay_basic'>Basicmode</option>";

foreach ($paymentMethods as $key => $paymentMethod) {
    if ($key == $module->paymentmethod) {
        $icepayPaymentMethods .= "<option value='{$key}' selected>{$paymentMethod->_readable_name}</option>";
    } else {
        $icepayPaymentMethods .= "<option value='{$key}'>{$paymentMethod->_readable_name}</option>";
    }
}

$config = Config::getInstance();
$baseURL = $config->get('config', 'standard_url');


$module->assign_to_template('paymentmethods', $icepayPaymentMethods);

$display = "<span style='padding: 5px 0; display: block;' class='icepay-checkoutname'>{$module->paymentmethodDisplayName}</span>";

if ($module->imageEnabled || strlen($module->paymentmethodDisplayName) == 0) {
    $display .= "<img src='{$baseURL}/modules/gateway/Icepay/images/{$module->paymentmethod}.png' />";
}

$module->_settings['desc'] = "{$display} <input type='hidden' name='icepay_paymentmethod' value='{$module->paymentmethod}' />";

if ($module->paymentmethod && $module->paymentmethod != 'icepay_basic') {
    $paymentMethod = Icepay_Api_Basic::getInstance()->prepareFiltering()->getClassByPaymentMethodCode($module->paymentmethod);
    $issuers = $paymentMethod->getSupportedIssuers();

    if (count($issuers) > 1) {
        $module->_settings['desc'] .= "<select name='icepay_issuer' style='display: block; padding: 5px; width: 163px;'>";
        foreach ($issuers as $issuer) {
            $readableName = iceTranslate($issuer);
            $module->_settings['desc'] .= "<option value='{$issuer}'>{$readableName}</option>";
        }
        $module->_settings['desc'] .= "</select>";
    }
}

if (empty($module->_settings['merchantid']) || empty($module->_settings['secretcode']) ) {
    $module->_settings['status'] = false;
    $GLOBALS['gui']->setError('Merchant ID and Secretcode must be filled in before you can use ICEPAY.');    
}

$module->module_settings_save($module->_settings);
$module->fetch();

$page_content = $module->display();