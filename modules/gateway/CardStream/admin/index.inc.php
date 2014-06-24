<?php
if(isset($_GET['variant'])) { 
	$module_name = $_GET['variant'];
	/* Add variant to the modules config so we can define which one we are using */
	$_POST['module'] = array_merge($_POST['module'],array('variant' => $_GET['variant']));
} else {
	$module_name = $_GET['module'];
}
$module		= new Module(__FILE__, $module_name, 'admin/index.tpl', true);
$page_content = $module->display();