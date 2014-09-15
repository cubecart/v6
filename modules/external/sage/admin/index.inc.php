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
if(!defined('CC_INI_SET')) die('Access Denied');

$module		= new Module(__FILE__, $_GET['module'], 'admin/index.tpl', true, false);

## Add Country Tax Code
if (isset($_POST['newTaxCodeCountry']) && !empty($_POST['newTaxCodeCountry']) && isset($_POST['newTaxCode']) && !empty($_POST['newTaxCode'])){
	$newTaxCode['taxCode_'.$_POST['newTaxCodeCountry']] = $_POST['newTaxCode'];	
	$module->module_settings_save(array_merge($module->_settings, $newTaxCode));
	$updated = true;
}
## Delete Country Tax Code
if(isset($_GET['rmTaxCode']) && !empty($_GET['rmTaxCode'])) {
	unset($module->_settings['taxCode_'.$_GET['rmTaxCode']]);
	$module->module_settings_save($module->_settings);
	$updated = true;
}
if ($updated) httpredir(currentPage(array('rmTaxCode')));


## List Payment Gateways & Assign Nominal Code
if ($payment_modules = $GLOBALS['db']->select('CubeCart_modules', array('folder'), array('module' => 'gateway', 'status' => 1), array('folder' => 'ASC'))) {
	foreach ($payment_modules as $payment_module) {
		$payment_module['desc'] = str_replace("_"," ", $payment_module['folder']);
		$payment_module['value'] = $module->_settings['pymtNominal_'.$payment_module['folder']];
		$nominals[]	= $payment_module;
	}
}
## List Module Country Tax Codes 
if (is_array($module->_settings)) {
	foreach ($module->_settings as $key => $value) {
		if (preg_match('/taxCode_/', $key)) { 
			if ($results = $GLOBALS['db']->select('CubeCart_geo_country', array('name'), array('id' => str_replace('taxCode_', '', $key)))) {
				$country['name'] 		= $results[0]['name'];
	     		$country['tax_code'] 	= $value; 
	     		$country['module_key'] 	= $key; 
	     		$country['link'] 		= '?_g=modules&type=external&module=sage&rmTaxCode='.str_replace('taxCode_', '', $key); 
				$list_enabled[] = $country;
			}
		}
	}
}
## List All Countries
$countries = $GLOBALS['db']->select('CubeCart_geo_country', array('name', 'id'), false, array('name' => 'ASC'));
## Generate Tax Codes
for ($i=0;$i<20; $i++) {
	$tax_codes[]	= 'T'.(int)$i;
}
$template_vars = array(
	'nominals' 		=> $nominals,
	'list_enabled'	=> $list_enabled,
	'countries'		=> $countries,
	'tax_codes'		=> $tax_codes
);
$module->assign_to_template($template_vars);
$module->fetch();
$page_content = $module->display();