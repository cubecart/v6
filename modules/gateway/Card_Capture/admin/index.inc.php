<?php
if(!defined('CC_INI_SET')) die('Access Denied');
$module		= new Module(__FILE__, $_GET['module'], 'admin/index.tpl', true);
$page_content = $module->display();

if (!extension_loaded('mcrypt') || !function_exists('mcrypt_module_open')) {
	$GLOBALS['main']->setACPWarning('Mcrypt library missing from server required to encrypt credit card data!');
}