<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('CC_INI_SET')) die('Access Denied');
$_GET['_g'] = (isset($_GET['_g'])) ? $_GET['_g'] : 'login';
switch (strtolower($_GET['_g'])) {
case 'recovery':
	if (isset($_POST['email']) && isset($_POST['validate']) && isset($_POST['password'])) {
		if (!Admin::getInstance()->passwordReset($_POST['email'], $_POST['validate'], $_POST['password'])) {
			$GLOBALS['gui']->setError($lang['account']['error_validation']);
		}
	}
	$GLOBALS['smarty']->assign('REQUEST', $_REQUEST);
	$GLOBALS['smarty']->assign('RECOVERY', true);
	break;
case 'password':
	if (isset($_POST['email']) && isset($_POST['username'])) {
		// Send a recovery email
		if (Admin::getInstance()->passwordRequest($_POST['username'], $_POST['email'])) {
			$GLOBALS['gui']->setNotify($lang['account']['notify_password_recovery']);
		} else {
			$GLOBALS['gui']->setError($lang['account']['error_details_wrong']);
		}
	}
	$GLOBALS['smarty']->assign('PASSWORD', true);
	break;
default:
	switch (true) {
	case (isset($_GET['redir']) && !empty($_GET['redir'])):
		$redir = $_GET['redir'];
		break;
	case (isset($_POST['redir']) && !empty($_POST['redir'])):
		$redir = $_POST['redir'];
		break;
	default:
		$redir = currentPage();
	}

	if ((isset($_GET['redir']) && !empty($_GET['redir'])) && preg_match('/^(http(s?)\:\/\/|ftp\:\/\/|\/\/)/i', $redir)) {
		httpredir(currentPage(array('redir')));
	}

	if ($GLOBALS['config']->get('config', 'ssl')) {
		if (CC_SSL) {
			$ssl = array(
				'url'   => currentPage(false, array('ssl_switch' => 0)),
				'icon'  => $GLOBALS['config']->get('config', 'adminFolder').'/skins/'.$GLOBALS['config']->get('config', 'admin_skin').'/images/ssl_true.png'
			);
		} else {
			$ssl = array(
				'url'   => currentPage(false, array('ssl_switch' => 1)),
				'icon'  => $GLOBALS['config']->get('config', 'adminFolder').'/skins/'.$GLOBALS['config']->get('config', 'admin_skin').'/images/ssl_false.png'
			);
		}
		$ssl['state'] = ($GLOBALS['config']->get('config', 'ssl_force')) ? 'forced' : 'mixed';
		$GLOBALS['smarty']->assign('SSL', $ssl);
	}
	if (isset($redir) && !empty($redir)) {
		$GLOBALS['smarty']->assign('REDIRECT_TO', $redir);
	}
}
$GLOBALS['gui']->displayCommon(true);