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


$GLOBALS['main']->addTabControl($lang['settings']['title_email_log'], 'email_log');
$GLOBALS['gui']->addBreadcrumb($lang['settings']['title_email_log'], currentPage());

$per_page = 25;
$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
$email_logs = $GLOBALS['db']->select('CubeCart_email_log', false, false, array('date' => 'DESC'), $per_page, $page, false);
if($email_logs!==false) {
	foreach($email_logs as $row) {
		$row['to'] = explode(',', $row['to']);
		$email_log[] = $row;
	}	
}

$GLOBALS['smarty']->assign('EMAIL_LOG', $email_log);
$count = $GLOBALS['db']->count('CubeCart_email_log', 'id');
$GLOBALS['smarty']->assign('PAGINATION_EMAIL_LOG', $GLOBALS['db']->pagination($count, $per_page, $page, 5, 'page', 'email_log'));

$page_content = $GLOBALS['smarty']->fetch('templates/statistics.emaillog.php');