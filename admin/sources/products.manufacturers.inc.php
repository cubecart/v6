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
Admin::getInstance()->permissions('products', CC_PERM_READ, true);


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
    // URL hygiene: only http/https are allowed. Anything else (javascript:, data:,
    // vbscript:) gets the http:// prefix prepended so the URL can't carry an active
    // payload when the admin clicks through from the manufacturer list.
    if (!empty($_POST['manufacturer']['URL'])) {
        $raw_url = (string)$_POST['manufacturer']['URL'];
        $url_parts = @parse_url($raw_url);
        $scheme = (is_array($url_parts) && !empty($url_parts['scheme'])) ? strtolower($url_parts['scheme']) : '';
        if (!in_array($scheme, array('http', 'https'), true)) {
            $_POST['manufacturer']['URL'] = 'http://'.$raw_url;
        }
    }
    $name = isset($_POST['manufacturer']['name']) ? trim($_POST['manufacturer']['name']) : '';
    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
        $edit_id = (int)$_GET['edit'];
        // Prevent renaming to a name already in use by a different manufacturer.
        $existing = $name !== '' ? $GLOBALS['db']->select('CubeCart_manufacturers', array('id'), array('name' => $name)) : false;
        $duplicate = false;
        if ($existing) {
            foreach ($existing as $row) {
                if ((int)$row['id'] !== $edit_id) {
                    $duplicate = true;
                    break;
                }
            }
        }
        if ($duplicate) {
            $GLOBALS['main']->errorMessage($lang['catalogue']['error_manufacturer_duplicate']);
        } elseif ($GLOBALS['db']->update('CubeCart_manufacturers', $_POST['manufacturer'], array('id' => $edit_id))) {
            $GLOBALS['main']->successMessage($lang['catalogue']['notify_manufacturer_update']);
        } else {
            $GLOBALS['main']->errorMessage($lang['catalogue']['error_manufacturer_update']);
        }
    } elseif ($name !== '') {
        if (!$GLOBALS['db']->select('CubeCart_manufacturers', array('id'), array('name' => $name))) {
            if ($GLOBALS['db']->insert('CubeCart_manufacturers', $_POST['manufacturer'])) {
                $GLOBALS['main']->successMessage($lang['catalogue']['notify_manufacturer_create']);
            } else {
                $GLOBALS['main']->errorMessage($lang['catalogue']['error_manufacturer_create']);
            }
        } else {
            $GLOBALS['main']->errorMessage($lang['catalogue']['error_manufacturer_duplicate']);
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
$smarty_data = array();
if (($countries = $GLOBALS['db']->select('CubeCart_geo_country', array('id', 'numcode', 'name'), false, array('name' => 'ASC'))) !== false) {
    $smarty_data = array();
    $store_country = $GLOBALS['config']->get('config', 'store_country');
    if (isset($_GET['edit']) && is_numeric($_GET['edit']) && ($geo = $GLOBALS['db']->select('CubeCart_manufacturers', array('country', 'eu_country'), array('id' => (int)$_GET['edit']))) !== false) {
        $sel_country = $geo[0]['country'];
        $sel_eu_country = $geo[0]['eu_country'];
    } else {
        $sel_country = $store_country;
        $sel_eu_country = $store_country;
    }
    foreach ($countries as $country) {
        $array = array(
            'selected' => (!empty($sel_country) && $country['numcode'] == $sel_country) ? 'selected="selected"' : '',
            'id'  => $country['numcode'],
            'name'  => $country['name'],
        );
        $smarty_data['countries'][] = $array;
    }
    $GLOBALS['smarty']->assign('COUNTRIES', $smarty_data['countries']);

    foreach ($countries as $country) {
        $array = array(
            'selected' => (!empty($sel_eu_country) && $country['numcode'] == $sel_eu_country) ? 'selected="selected"' : '',
            'id'  => $country['numcode'],
            'name'  => $country['name'],
        );
        $smarty_data['eu_countries'][] = $array;
    }
    $GLOBALS['smarty']->assign('EU_COUNTRIES', $smarty_data['eu_countries']);
    $GLOBALS['smarty']->assign('JSON_STATE', state_json());
}

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $GLOBALS['main']->addTabControl($lang['catalogue']['title_manufacturer'], false, currentPage(array('edit')));
    $GLOBALS['main']->addTabControl($lang['catalogue']['title_manufacturer_edit'], 'manu_edit');
    if (($manufacturers = $GLOBALS['db']->select('CubeCart_manufacturers', false, array('id' => (int)$_GET['edit']))) !== false) {
        $GLOBALS['smarty']->assign('EDIT', $manufacturers[0]);
    } else {
        $GLOBALS['main']->errorMessage($lang['catalogue']['error_manufacturer_found']);
        httpredir(currentPage(array('edit')));
    }
    
    foreach ($GLOBALS['hooks']->load('admin.product.manufacturer.tabs') as $hook) {
        include $hook;
    }
    $GLOBALS['smarty']->assign('PLUGIN_TABS', ($smarty_data['plugin_tabs'] ?? false));
    
    $GLOBALS['smarty']->assign('DISPLAY_FORM', true);
} else {
    $GLOBALS['smarty']->assign('EDIT', array());
    $GLOBALS['main']->addTabControl($lang['catalogue']['title_manufacturer'], 'manufacturers');
    $GLOBALS['main']->addTabControl($lang['catalogue']['title_manufacturer_add'], 'manu_add');
    $catalogue = Catalogue::getInstance();
    $page  = (isset($_GET['page'])) ? $_GET['page'] : 1;
    $per_page = 10;
    if (($manufacturers = $GLOBALS['db']->select('CubeCart_manufacturers', false, false, 'name', $per_page, $page)) !== false) {
        $GLOBALS['smarty']->assign('PAGINATION', $GLOBALS['db']->pagination(false, $per_page, $page));
        foreach ($manufacturers as $i => $manufacturer) {
            $manufacturers[$i]['has_url'] = filter_var($manufacturer['URL'], FILTER_VALIDATE_URL) !== false;
        }
        $GLOBALS['smarty']->assign('MANUFACTURERS', $manufacturers);
    }
    $GLOBALS['smarty']->assign('DISPLAY_LIST', true);
}
$page_content = $GLOBALS['smarty']->fetch('templates/products.manufacturers.php');
