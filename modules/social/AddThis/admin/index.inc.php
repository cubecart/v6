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
if(!defined('CC_INI_SET')) die('Access Denied');
if(isset($_POST['module']) && (empty($_POST['module']['preferred_count']) || !is_numeric($_POST['module']['preferred_count']) || $_POST['module']['preferred_count']<0 || $_POST['module']['preferred_count'] > 11)) {
	$_POST['module']['preferred_count'] = 7;
}
if(isset($_POST['module']['specific_buttons'])) {
	$_POST['module']['specific_buttons'] = str_replace(' ','',strtolower($_POST['module']['specific_buttons']));
}
$module		= new Module(__FILE__, $_GET['module'], 'admin/index.tpl', true);
$page_content = $module->display();