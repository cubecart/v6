<?php

// The line below prevents direct access to this file which may lead to a path disclosure vulnerability

if(!defined('CC_INI_SET')) die('Access Denied');


require_once('modules/plugins/reviewscouk_export/class.reviewscouk_export.php');

$reviewscouk_export = new reviewscouk_export(null);
$module_config = $GLOBALS['config']->get('reviewscouk_export');

if (!$module_config) {	

	$curl = curl_init();
	$url = 'http://dash.reviews.co.uk/api/appinstall?url='.urlencode($_SERVER['HTTP_HOST']);
	$resp = $reviewscouk_export->reviewsCall($url);

	if (!is_object($resp)) {
		$resp = json_decode($resp);
	}

	if ($resp && $resp->success) {
		$module_config = array (
			'apiKey' 			=> $resp->apiKey,
			'has_product_reviews'=> $resp->hasProduct,
			'enable_product'	=> $resp->hasProduct,
			'enable_merchant'	=> $resp->hasMerchant
		);

	} else {

		$module_config = array (
			'apiKey' 			=> '',
			'has_product_reviews'=> 0,
			'enable_product'	=> 0,
			'enable_merchant'	=> 0
		);

	}

	$GLOBALS['config']->set('reviewscouk_export','', $module_config);
}



if ($_POST['apiKey']) {
	$module_config['apiKey'] = $_POST['apiKey'];
	$url = 'http://dash.reviews.co.uk/api/appinstall?apiKey='.urlencode($_POST['apiKey']);
	$resp = $reviewscouk_export->reviewsCall($url);

	if (!is_object($resp)) {
		$resp = json_decode($resp);
	}

	

	if ($resp && $resp->success) {
		$module_config['has_product_reviews']= $resp->hasProduct;
		$module_config['enable_product'] =  $resp->hasProduct?$_POST['enable_product']:0;
		$module_config['enable_merchant'] = $_POST['enable_merchant'];

	} else {

		$module_config = array (
			'apiKey' 			=> '',
			'has_product_reviews'=> 0,
			'enable_product'	=> 0,
			'enable_merchant'	=> 0
		);

	}

	$module_config['enable_product'] = $_POST['enable_product'];
	$module_config['enable_merchant'] = $_POST['enable_merchant'];
	$GLOBALS['config']->set('reviewscouk_export','', $module_config);
}

$module	= new Module(__FILE__, $_GET['module'], 'admin/index.tpl', true, false);
$module->fetch();

$page_content = $module->display();