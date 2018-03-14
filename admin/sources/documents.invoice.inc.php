<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('documents', CC_PERM_EDIT, true);
global $lang;

$GLOBALS['main']->addTabControl($lang['orders']['invoice_editor'], 'general');
$GLOBALS['gui']->addBreadcrumb($lang['orders']['invoice_editor'], currentPage());
$filename = CC_ROOT_DIR.'/'.$GLOBALS['config']->get('config', 'adminFolder').'/skins/'.$GLOBALS['config']->get('config', 'admin_skin').'/templates/orders.print.php';
$handle = fopen($filename, "rb");
$contents = fread($handle, filesize($filename));
$GLOBALS['smarty']->assign('INVOICE_HTML', $contents);

$page_content = $GLOBALS['smarty']->fetch('templates/documents.invoice.php');