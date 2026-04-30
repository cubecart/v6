<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}
Admin::getInstance()->permissions('settings', CC_PERM_READ, true);


$filemanager = new FileManager(FileManager::FM_FILETYPE_IMG);

if (isset($_POST['gc']) && is_array($_POST['gc']) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
    if (($uploaded = $filemanager->upload()) !== false) {
        foreach ($uploaded as $file_id) {
            $_POST['imageset'][(int)$file_id] = true;
        }
    }

    // Image selection: two-pass so the order of imageset entries doesn't matter.
    // First pass clears the master-image flag if the current one is being deselected;
    // second pass picks the first newly-enabled image as the new master.
    if (isset($_POST['imageset']) && is_array($_POST['imageset'])) {
        $gc = $GLOBALS['config']->get('gift_certs');
        $current_image = isset($gc['image']) ? (int)$gc['image'] : 0;
        foreach ($_POST['imageset'] as $image_id => $enabled) {
            if (!$enabled && (int)$image_id === $current_image) {
                unset($_POST['gc']['image']);
            }
        }
        foreach ($_POST['imageset'] as $image_id => $enabled) {
            if ($enabled) {
                $_POST['gc']['image'] = (int)$image_id;
                break;
            }
        }
    }

    if (isset($_POST['gc']['product_code']) && ctype_digit((string)$_POST['gc']['product_code'])) {
        $_POST['gc']['product_code'] = 'GC'.$_POST['gc']['product_code'];
        $GLOBALS['main']->errorMessage(sprintf($lang['catalogue']['gc_not_numeric'], $_POST['gc']['product_code']));
    }

    // Sanity-check the value range — prevents the storefront silently rejecting every
    // gift-certificate purchase if min/max are entered in the wrong order.
    $gc_min = isset($_POST['gc']['min']) ? (float)$_POST['gc']['min'] : 0;
    $gc_max = isset($_POST['gc']['max']) ? (float)$_POST['gc']['max'] : 0;
    if ($gc_max > 0 && $gc_min > $gc_max) {
        $GLOBALS['main']->errorMessage($lang['settings']['error_gc_min_max']);
    } elseif ($GLOBALS['config']->set('gift_certs', '', $_POST['gc'])) {
        $GLOBALS['main']->successMessage($lang['settings']['notify_settings_update']);
    } else {
        $GLOBALS['main']->errorMessage($lang['settings']['error_settings_update']);
    }
}

$GLOBALS['main']->addTabControl($lang['catalogue']['gift_certificates'], 'Certificates');
$GLOBALS['main']->addTabControl($lang['settings']['title_images'], 'gift_images', null, 'I');
$GLOBALS['main']->addTabControl($lang['settings']['tab_seo'], 'seo');

$GLOBALS['gui']->addBreadcrumb($lang['catalogue']['gift_certificates'], $_GET);

$gc = $GLOBALS['config']->get('gift_certs');
if (isset($gc['image'])) {
    $master_image = $GLOBALS['catalogue']->imagePath((int)$gc['image'], 'small', 'url');
    $gc['master_image'] = !empty($master_image) ? $master_image : 'images/general/px.gif';
    $GLOBALS['smarty']->assign('JSON_IMAGES', json_encode(array($gc['image'])));
}

$GLOBALS['smarty']->assign('GC', $gc);
$select_options = array(
    'delivery' => array(1 => $lang['settings']['gc_type_digital'], 2 => $lang['settings']['gc_type_physical'], 3 => $lang['settings']['gc_type_both']),
    'status' => array(0 => $lang['common']['disabled'], 1 => $lang['settings']['enabled_for_all'], 2 => $lang['settings']['enabled_for_logged_in']),
);
foreach ($select_options as $field => $options) {
    $rendered = array();
    foreach ($options as $value => $title) {
        $selected = (isset($gc[$field]) && $gc[$field] == $value) ? ' selected="selected"' : '';
        $rendered[] = array('value' => $value, 'title' => $title, 'selected' => $selected);
    }
    $GLOBALS['smarty']->assign('OPT_'.strtoupper($field), $rendered);
}
$page_content = $GLOBALS['smarty']->fetch('templates/settings.giftcertificates.php');
