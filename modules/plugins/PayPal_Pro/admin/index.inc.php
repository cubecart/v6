<?php
if(!defined('CC_INI_SET')) die('Access Denied');
$module	= new Module(__FILE__, $_GET['module'], 'admin/index.tpl', true, false);

$store_country = $GLOBALS['config']->get('config', 'store_country');
if($store_country==840) {
	$GLOBALS['smarty']->assign('BML', true);
	$country_iso = "US";
} elseif($store_country==826) {
	$country_iso = "UK";
}

$script_file = CC_ROOT_DIR.'/includes/extra/PayPal_acceptance.js';

if($module->acceptance_mark=='1' &&  in_array($store_country, array(840,826))) {
	
	$store_url = str_replace(array('http://','https://'),'//',CC_STORE_URL);
	
	$script_data = <<<END
jQuery(document).ready(function() {
	var pp_acceptance = "<div style=\"text-align:center\"><a href=\"https://www.paypal.com/uk/webapps/mpp/paypal-popup\" title=\"How PayPal Works\" onclick=\"javascript:window.open('https://www.paypal.com/uk/webapps/mpp/paypal-popup','WIPaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700'); return false;\"><img src=\"$store_url/modules/plugins/PayPal_Pro/images/acceptance_marks_$country_iso.png\" border=\"0\" alt=\"Now accepting PayPal\"></a></div>";
	$("body").append(pp_acceptance);
});
END;
	
	$fp = fopen($script_file, 'w');
	fwrite($fp, $script_data);
	fclose($fp);
} else {
	unlink($script_file);
}

## Modes
$modes	= array(
	'4'	=> $GLOBALS['language']->paypal_pro['mode_pp_hosted'],
	'3'	=> $GLOBALS['language']->paypal_pro['mode_pp'],
	'2'	=> $GLOBALS['language']->paypal_pro['mode_ec'],
	'1'	=> $GLOBALS['language']->paypal_pro['mode_dp'],
);

foreach ($modes as $value => $title) {
	if ($value == '1' && $store_country != 840) continue; // Direct Payment for US Only
	if ($value == '4' && $store_country != 826) continue; // PayPal Pro Hosted for UK Only
	$mode_list[]		= array(
		'value'		=> $value,
		'title'		=> $title,
		'selected'	=> ($value == $module->mode) ? ' selected="selected"' : '',
	);
}

## Gateways
$gateways	= array(
	'0' => $GLOBALS['language']->paypal_pro['gateway_sandbox'],
	'1' => $GLOBALS['language']->paypal_pro['gateway_live'],
);
foreach ($gateways as $value => $title) {
	$gateway_list[]		= array(
		'value'		=> $value,
		'title'		=> $title,
		'selected'	=> ($value == $module->gateway) ? ' selected="selected"' : '',
	);
}

## Payment Actions
$actions	= array(
	'Sale'			=> $GLOBALS['language']->paypal_pro['payment_sale'],
	'Authorization'	=> $GLOBALS['language']->paypal_pro['payment_auth'],
	//'Order'			=> $GLOBALS['language']->paypal_pro['payment_order'],
);
foreach ($actions as $value => $title) {
	$action_list[]		= array(
		'value'		=> $value,
		'title'		=> $title,
		'selected'	=> ($value == $module->paymentAction) ? ' selected="selected"' : '',
	);
}

## Require a confirmed address
$confirmed	= array($GLOBALS['language']->common['no'], $GLOBALS['language']->common['yes']);
foreach ($confirmed as $value => $title) {
	$confirm_list[]	= array(
		'value'		=> $value,
		'title'		=> $title,
		'selected'	=> ($value == $module->confAddress) ? ' selected="selected"' : '',
	);
}

$ec_modes	= array('inline' => 'Inline (Lightbox Overlay)', 'redirect' => 'Redirect to PayPal');
foreach ($ec_modes as $value => $title) {
	$ec_mode_list[]	= array(
		'value'		=> $value,
		'title'		=> $title,
		'selected'	=> ($value == $module->ec_mode) ? ' selected="selected"' : '',
	);
}

$template_vars = array (
	'ec_modes'	=> $ec_mode_list,
	'confirmed' => $confirm_list,
	'actions'	=> $action_list,
	'gateways'	=> $gateway_list,
	'modes'		=> $mode_list,
	'country'	=> $store_country,
	'paypal_ipn_url' => $GLOBALS['storeURL'].'/index.php?_g=rm&amp;type=gateway&amp;cmd=call&amp;module=PayPal'
);

$module->assign_to_template($template_vars);
$module->fetch();
$page_content = $module->display();