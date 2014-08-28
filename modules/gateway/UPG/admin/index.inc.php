<?php
if(!defined('CC_DS')) die('Access Denied');
if(isset($_GET['sample'])) {
	deliverFile(CC_ROOT_DIR.'/modules/gateway/UPG/sample/payment.html');
	exit;
}
$module		= new Module(__FILE__, $_GET['module'], 'admin/index.tpl', true);
$page_content = $module->display();