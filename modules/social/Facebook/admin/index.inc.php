<?php
if(!defined('CC_INI_SET')) die('Access Denied');
if(isset($_POST['module']['button_width']) && (empty($_POST['module']['button_width']) || !is_numeric($_POST['module']['button_width']) ) ) {
	// BUG 2879 FIXED
        $_POST['module']['button_width'] = 230;
}
if(isset($_POST['module']['comments_width']) && (empty($_POST['module']['comments_width']) || !is_numeric($_POST['module']['comments_width']) ) ) {
	$_POST['module']['comments_width'] = 425;
}
if(isset($_POST['module']['comments_numposts']) && (empty($_POST['module']['comments_numposts']) || !is_numeric($_POST['module']['comments_numposts']) ) ) {
	$_POST['module']['comments_numposts'] = 10;
}
if( isset($_POST['module']) && isset($_POST['module']['appid']) && !$_POST['module']['like_status'] && !$_POST['module']['comments_status']) {
	$_POST['module']['status'] = 0;
}
$module		= new Module(__FILE__, $_GET['module'], 'admin/index.tpl', true);
$page_content = $module->display();