<?php
if(!defined('CC_INI_SET')) die('Access Denied');
$urls = array(
	'referring' => $GLOBALS['storeURL'].'/index.php(.*)',
	'response' => $GLOBALS['storeURL'].'/modules/gateway/Realex/return.php'
);
$GLOBALS['smarty']->assign('URL',$urls);
$module		= new Module(__FILE__, $_GET['module'], 'admin/index.tpl', true);
$page_content = $module->display();