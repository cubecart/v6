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
Admin::getInstance()->permissions('reviews', CC_PERM_READ, true);

global $lang;

## Delete Manufacturer
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && Admin::getInstance()->permissions('products', CC_PERM_DELETE)) {
    if ($GLOBALS['db']->delete('CubeCart_manufacturers', array('id' => (int)$_GET['delete']))) {
        $GLOBALS['main']->successMessage($lang['catalogue']['notify_manufacturer_delete']);
    } else {
        $GLOBALS['main']->errorMessage($lang['catalogue']['error_manufacturer_delete']);
    }
    foreach ($GLOBALS['hooks']->load('admin.product.manufacturers.delete') as $hook) {
        include $hook;
    }
    httpredir(currentPage(array('delete')));
}

## Update Manufacturer
if (isset($_POST['manufacturer']) && is_array($_POST['manufacturer'])) {
    foreach ($GLOBALS['hooks']->load('admin.product.manufacturers.save.pre_process') as $hook) {
        include $hook;
    }
    if (!empty($_POST['manufacturer']['URL'])) {
        $url_parts = parse_url($_POST['manufacturer']['URL']);
        if (!isset($url_parts['scheme']) || empty($url_parts['scheme'])) {
            $_POST['manufacturer']['URL'] = "http://".$_POST['manufacturer']['URL'];
        }
    }
    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
        if ($GLOBALS['db']->update('CubeCart_manufacturers', $_POST['manufacturer'], array('id' => (int)$_GET['edit']))) {
            $GLOBALS['main']->successMessage($lang['catalogue']['notify_manufacturer_update']);
        } else {
            $GLOBALS['main']->errorMessage($lang['catalogue']['error_manufacturer_update']);
        }
    } elseif(isset($_POST['manufacturer']['name']) && !empty($_POST['manufacturer']['name'])) {
        if (!$GLOBALS['db']->select('CubeCart_manufacturers', array('id'), array('name' => $_POST['manufacturer']['name']))) {
            if ($GLOBALS['db']->insert('CubeCart_manufacturers', $_POST['manufacturer'])) {
                $GLOBALS['main']->successMessage($lang['catalogue']['notify_manufacturer_create']);
            } else {
                $GLOBALS['main']->errorMessage($lang['catalogue']['error_manufacturer_create']);
            }
        } else {
            $GLOBALS['main']->errorMessage($lang['catalogue']['error_manufacturer_create']);
        }
    }
    foreach ($GLOBALS['hooks']->load('admin.product.manufacturers.save.post_process') as $hook) {
        include $hook;
    }
    httpredir('?_g=products&node=manufacturers', 'manufacturers');
}
$GLOBALS['gui']->addBreadcrumb($lang['catalogue']['title_manufacturer'], currentPage(array('edit')));

foreach ($GLOBALS['hooks']->load('admin.product.manufacturer.pre_display') as $hook) {
    include $hook;
}

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $GLOBALS['main']->addTabControl($lang['catalogue']['title_manufacturer'], false, currentPage(array('edit')));
    $GLOBALS['main']->addTabControl($lang['catalogue']['title_manufacturer_edit'], 'manu_edit');
    if (($manufacturers = $GLOBALS['db']->select('CubeCart_manufacturers', array('name', 'id', 'URL'), array('id' => (int)$_GET['edit']))) !== false) {
        $GLOBALS['smarty']->assign('EDIT', $manufacturers[0]);
    } else {
        $GLOBALS['main']->errorMessage($lang['catalogue']['error_manufacturer_found']);
        httpredir(currentPage(array('edit')));
    }
    
    foreach ($GLOBALS['hooks']->load('admin.product.manufacturer.tabs') as $hook) {
        include $hook;
    }
    $GLOBALS['smarty']->assign('PLUGIN_TABS', $smarty_data['plugin_tabs']);
    
    $GLOBALS['smarty']->assign('DISPLAY_FORM', true);
} else {
    $GLOBALS['main']->addTabControl($lang['catalogue']['title_manufacturer'], 'manufacturers');
    $GLOBALS['main']->addTabControl($lang['catalogue']['title_manufacturer_add'], 'manu_add');
    $catalogue = Catalogue::getInstance();
    $page  = (isset($_GET['page'])) ? $_GET['page'] : 1;
    $per_page = 10;
    if (($manufacturers = $GLOBALS['db']->select('CubeCart_manufacturers', false, false, 'name', $per_page, $page)) !== false) {
        $GLOBALS['smarty']->assign('PAGINATION', $GLOBALS['db']->pagination(false, $per_page, $page));
        foreach ($manufacturers as $i => $manufacturer) {
            if (filter_var($manufacturer['URL'], FILTER_VALIDATE_URL)) {
                $manufacturers[$i]['name'] = '<a href="'.$manufacturer['URL'].'" target="_blank">'.$manufacturer['name'].'</a>';
            }
        }
        $GLOBALS['smarty']->assign('MANUFACTURERS', $manufacturers);
    }
    $GLOBALS['smarty']->assign('DISPLAY_LIST', true);
}
$page_content = $GLOBALS['smarty']->fetch('templates/products.manufacturers.php');
