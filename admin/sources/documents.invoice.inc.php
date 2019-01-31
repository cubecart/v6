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
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}
Admin::getInstance()->permissions('documents', CC_PERM_EDIT, true);
global $lang;

$current = $GLOBALS['db']->select('CubeCart_invoice_template', array('hash', 'content'), false, 'id DESC', 1);
$filename = CC_ROOT_DIR.'/'.$GLOBALS['config']->get('config', 'adminFolder').'/skins/'.$GLOBALS['config']->get('config', 'admin_skin').'/templates/orders.print.php';
$handle = fopen($filename, "rb");
$default = fread($handle, filesize($filename));

$updated = false;

if (isset($_GET['restore']) && $_GET['restore']==1) {
    if ($GLOBALS['db']->insert('CubeCart_invoice_template', array('content' => $default, 'hash' => md5($default)))) {
        $GLOBALS['main']->successMessage($lang['settings']['notify_default_restore']);
    }
    $updated = true;
} elseif (isset($GLOBALS['RAW']['POST']['content']) && !empty($GLOBALS['RAW']['POST']['content'])) {
    $syntax_error = false;
    try {
        $GLOBALS['smarty']->fetch('string:'.$GLOBALS['RAW']['POST']['content']);
    } catch (Exception $e) {
        $GLOBALS['main']->errorMessage(str_replace('string:', '', htmlentities($e->getMessage(), ENT_QUOTES)));
        $syntax_error = true;
    }

    if (!$syntax_error) {
        $hash = md5($GLOBALS['RAW']['POST']['content']);
        if (!$current || $current[0]['hash']!==$hash) {
            $GLOBALS['db']->insert('CubeCart_invoice_template', array('content' => $GLOBALS['RAW']['POST']['content'], 'hash' => $hash));
            $current[0]['content'] = $GLOBALS['RAW']['POST']['content'];
        }
        $GLOBALS['main']->successMessage($lang['settings']['notify_invoice_updated']);
        $updated = true;
    }
}
if ($updated == true) {
    httpredir('?_g=documents&node=invoice');
}

$GLOBALS['main']->addTabControl($lang['orders']['invoice_editor'], 'general');
$GLOBALS['gui']->addBreadcrumb($lang['orders']['invoice_editor'], currentPage());

if ($current && !empty($current[0]['content'])) {
    $content = $current[0]['content'];
} else {
    $content = $default;
}
$GLOBALS['smarty']->assign('INVOICE_HTML', $content);

$page_content = $GLOBALS['smarty']->fetch('templates/documents.invoice.php');
