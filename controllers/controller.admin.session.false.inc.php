<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2025. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}

// If a 2FA challenge is pending, force the twofa page regardless of _g parameter
if ($GLOBALS['session']->get('twofa_pending_admin_id', 'client')) {
    $_GET['_g'] = 'twofa';
}

$_GET['_g'] = (isset($_GET['_g'])) ? $_GET['_g'] : 'login';
switch (strtolower($_GET['_g'])) {
case 'twofa':
    $pending_id = (int)$GLOBALS['session']->get('twofa_pending_admin_id', 'client', 0);
    if (!$pending_id) {
        httpredir($GLOBALS['rootRel'].$GLOBALS['config']->get('config', 'adminFile'));
    }
    if (isset($_POST['twofa_code'])) {
        Admin::getInstance()->verify2FA($_POST['twofa_code']);
        // Returns false on failure (error already set); redirects on success
    } elseif (isset($_GET['resend'])) {
        Admin::getInstance()->resend2FACode();
    }
    // Load admin info for display (method, resend eligibility, masked email)
    $pending_admin = $GLOBALS['db']->select('CubeCart_admin_users',
        array('twofa_method', 'twofa_otp_expires', 'email'),
        array('admin_id' => $pending_id, 'status' => 1),
        false, 1, false, false);
    if (!$pending_admin) {
        // Admin disabled or removed while challenge was open
        $GLOBALS['session']->delete('twofa_pending_admin_id', 'client');
        httpredir($GLOBALS['rootRel'].$GLOBALS['config']->get('config', 'adminFile'));
    }
    $pa = $pending_admin[0];
    $sent_at   = (int)$pa['twofa_otp_expires'] - 600;
    $can_resend = ($pa['twofa_method'] === 'email' && (time() - $sent_at) >= 60);
    $at = strpos($pa['email'], '@');
    $local  = substr($pa['email'], 0, $at);
    $domain = substr($pa['email'], $at + 1);
    $visible = min(3, max(1, (int)floor(strlen($local) / 2)));
    $email_hint = substr($local, 0, $visible).'***@'.$domain;
    $GLOBALS['smarty']->assign('TWOFA', array(
        'method'     => $pa['twofa_method'],
        'can_resend' => $can_resend,
        'email_hint' => $email_hint,
        'resend_url' => currentPage(null, array('resend' => '1')),
    ));
    break;
case 'recovery':
    if (isset($_POST['email']) && isset($_POST['validate']) && isset($_POST['password'])) {
        if (!Admin::getInstance()->passwordReset($_POST['email'], $_POST['validate'], $_POST['password'])) {
            $GLOBALS['gui']->setError($GLOBALS['language']->account['error_validation']);
        }
    }
    $GLOBALS['smarty']->assign('REQUEST', $_REQUEST);
    $GLOBALS['smarty']->assign('RECOVERY', true);
    break;
case 'password':
    if (isset($_POST['email'])) {
        // Send a recovery email
        Admin::getInstance()->passwordRequest($_POST['email']);
        $GLOBALS['gui']->setNotify($GLOBALS['language']->account['notify_password_recovery']);
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

    if (isset($redir) && !empty($redir)) {
        $GLOBALS['smarty']->assign('REDIRECT_TO', $redir);
    }
}
$GLOBALS['gui']->displayCommon();
