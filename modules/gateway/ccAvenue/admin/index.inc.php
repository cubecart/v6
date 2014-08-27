<?php
if(!defined('CC_DS')) die('Access Denied');
$module		= new Module(__FILE__, $_GET['module'], 'admin/index.tpl', true);
$page_content = $module->display();