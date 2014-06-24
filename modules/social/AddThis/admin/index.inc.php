<?php
if(!defined('CC_INI_SET')) die('Access Denied');
if(isset($_POST['module']) && (empty($_POST['module']['preferred_count']) || !is_numeric($_POST['module']['preferred_count']) || $_POST['module']['preferred_count']<0 || $_POST['module']['preferred_count'] > 11)) {
	$_POST['module']['preferred_count'] = 7;
}
if(isset($_POST['module']['specific_buttons'])) {
	$_POST['module']['specific_buttons'] = str_replace(' ','',strtolower($_POST['module']['specific_buttons']));
}
$module		= new Module(__FILE__, $_GET['module'], 'admin/index.tpl', true);
$page_content = $module->display();