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
if(isset($_GET['variant'])) { 
	$module_name = $_GET['variant'];
	/* Add variant to the modules config so we can define which one we are using */
	$_POST['module'] = array_merge($_POST['module'],array('variant' => $_GET['variant']));
} else {
	$module_name = $_GET['module'];
}
$module		= new Module(__FILE__, $module_name, 'admin/index.tpl', true);
$page_content = $module->display();