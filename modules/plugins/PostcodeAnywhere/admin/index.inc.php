<?php
if(isset($_GET['module']['capture_key'])) {
	$_GET['module']['capture_key'] = trim($_GET['module']['capture_key']);
}
if(!defined('CC_INI_SET')) die('Access Denied');
$module	= new Module(__FILE__, $_GET['module'], 'admin/index.tpl', true, false);
$module->assign_to_template($template_vars);
$module->fetch();
$page_content = $module->display();