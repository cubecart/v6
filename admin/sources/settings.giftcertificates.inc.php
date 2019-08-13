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
Admin::getInstance()->permissions('settings', CC_PERM_READ, true);

global $lang;

$filemanager = new FileManager(FileManager::FM_FILETYPE_IMG);

if (isset($_POST['gc']) && is_array($_POST['gc']) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
    if (($uploaded = $filemanager->upload()) !== false) {
        foreach ($uploaded as $file_id) {
            $_POST['imageset'][(int)$file_id] = true;
        }
    }

    if (isset($_POST['imageset']) && is_array($_POST['imageset'])) {
        $gc = $GLOBALS['config']->get('gift_certs');

        foreach ($_POST['imageset'] as $image_id => $enabled) {
            if ($enabled == 0) {
                if ($image_id == $gc['image']) {
                    $_POST['gc']['image'] = '';
                }
                continue;
            }
            $_POST['gc']['image'] = (int)$image_id;
            break;
        }
    }

    if ($GLOBALS['config']->set('gift_certs', '', $_POST['gc'])) {
        $GLOBALS['main']->successMessage($lang['settings']['notify_settings_update']);
    } else {
        $GLOBALS['main']->errorMessage($lang['settings']['error_settings_update']);
    }
}

$GLOBALS['main']->addTabControl($lang['catalogue']['gift_certificates'], 'Certificates');
$GLOBALS['main']->addTabControl($lang['settings']['title_images'], 'gift_images', null, 'I');

$GLOBALS['gui']->addBreadcrumb($lang['catalogue']['gift_certificates'], $_GET);

$gc = $GLOBALS['config']->get('gift_certs');
if (isset($gc['image'])) {
    $master_image = $GLOBALS['catalogue']->imagePath((int)$gc['image'], 'small', 'url');
    $gc['master_image'] = !empty($master_image) ? $master_image : 'images/general/px.gif';
    $GLOBALS['smarty']->assign('JSON_IMAGES', json_encode(array($gc['image'])));
}

if (($taxes = $GLOBALS['db']->select('CubeCart_tax_class')) !== false) {
    foreach ($taxes as $tax) {
        $tax['selected'] = (isset($gc['taxType']) && $gc['taxType'] == $tax['id'])? ' selected="selected"' : '';
        $smarty_data['taxs'][] = $tax;
    }
    $GLOBALS['smarty']->assign('TAXES', $smarty_data['taxs']);
}
$GLOBALS['smarty']->assign('GC', $gc);
$select_options = array(
    'delivery' => array(1 => $lang['settings']['gc_type_digital'], 2 => $lang['settings']['gc_type_physical'], 3 => $lang['settings']['gc_type_both']),
    'status' => array(0 => $lang['common']['disabled'], 1 => $lang['settings']['enabled_for_all'], 2 => $lang['settings']['enabled_for_logged_in']),
);
if (isset($select_options)) {
    foreach ($select_options as $field => $options) {
        if (!is_array($options) || empty($options)) {
            $options = array($lang['common']['no'], $lang['common']['yes']);
        }
        foreach ($options as $value => $title) {
            $selected = (isset($gc[$field]) && $gc[$field] == $value) ? ' selected="selected"' : '';
            $smarty_data['options'][] = array('value' => $value, 'title' => $title, 'selected' => $selected);
        }
        $GLOBALS['smarty']->assign('OPT_'.strtoupper($field), $smarty_data['options']);
        unset($smarty_data['options']);
    }
}
$page_content = $GLOBALS['smarty']->fetch('templates/settings.giftcertificates.php');
