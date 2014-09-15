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

require 'modules/gateway/PayVector/PayVector/Core/PayVector.php';

if (!$GLOBALS['db'] -> misc(PayVectorSQL::TableExists(PayVectorSQL::tblGEP_EntryPoints)))
{
	$GLOBALS['db'] -> misc(PayVectorSQL::createGEP_EntryPoints());
	$GLOBALS['db'] -> misc(PayVectorSQL::insertGEP_EntryPointsPlaceholder());
}
$GLOBALS['db'] -> misc(PayVectorSQL::createCRT_CrossReference());


$module		= new Module(__FILE__, $_GET['module'], 'admin/index.tpl', true);
$page_content = $module->display();