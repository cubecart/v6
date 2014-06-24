<?php
if(!defined('CC_INI_SET')) die('Access Denied');

if(isset($_POST['module']['trigger'])) {
	$_POST['module']['trigger'] = preg_replace("/[^0-9.]/","",$_POST['module']['trigger']);
}

$module		= new Module(__FILE__, $_GET['module'], 'admin/index.tpl', true);
$page_content = $module->display();