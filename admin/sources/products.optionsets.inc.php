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

## Option Sets - Assign
if (isset($_POST['set']) && is_array($_POST['set']) && !empty($_POST['product']) && is_array($_POST['product'])) {
    $updated = false;
    foreach ($_POST['set'] as $set_id) {
        if (!is_numeric($set_id)) continue;
        foreach ($_POST['product'] as $product_id) {
            if (!is_numeric($product_id)) continue;
            $set_search = array('product_id' => (int)$product_id, 'set_id' => (int)$set_id);
            if (!$GLOBALS['db']->select('CubeCart_options_set_product', array('set_product_id'), $set_search)) {
                if ($GLOBALS['db']->insert('CubeCart_options_set_product', $set_search)) {
                    $updated = true;
                }
            }
        }
    }
    foreach ($GLOBALS['hooks']->load('admin.optionsets.post_assign') as $hook) {
        include $hook;
    }
    if ($updated) {
        $GLOBALS['main']->successMessage($lang['catalogue']['notify_option_sets_updated']);
    } else {
        $GLOBALS['main']->errorMessage($lang['catalogue']['notify_option_sets_already_assigned']);
    }
    httpredir(currentPage());
}

#############################################
$GLOBALS['main']->addTabControl($lang['catalogue']['title_product_list'], null, currentPage(array('node')));
$GLOBALS['main']->addTabControl($lang['catalogue']['product_add'], null, currentPage(array('node'), array('action' => 'add')));
$GLOBALS['main']->addTabControl($lang['catalogue']['title_category_assigned'], null, currentPage(null, array('node' => 'assign')));
$GLOBALS['main']->addTabControl($lang['catalogue']['title_option_set_assign'], 'assign');
$GLOBALS['gui']->addBreadcrumb($lang['catalogue']['title_option_set_assign'], currentPage());

## List option sets
if (($option_sets = $GLOBALS['db']->select('CubeCart_options_set')) !== false) {
    $GLOBALS['smarty']->assign('OPTION_SETS', $option_sets);
}

## Pre-selected products carried over from products.index "Assign Option Sets" multi-action.
## One-shot delivery - the client merges into a localStorage list so multiple
## round-trips accumulate.
$preselected_json = '[]';
$preselected_ids = $GLOBALS['session']->get('preselected', 'option_sets');
if (is_array($preselected_ids) && $preselected_ids) {
    $GLOBALS['session']->delete('preselected', 'option_sets');
    $ids = array_values(array_filter(array_map('intval', $preselected_ids)));
    if ($ids) {
        if (($rows = $GLOBALS['db']->select('CubeCart_inventory', array('product_id', 'name', 'product_code'), array('product_id' => $ids))) !== false) {
            $payload = array();
            foreach ($rows as $r) {
                $payload[] = array(
                    'id'   => (int)$r['product_id'],
                    'name' => (string)$r['name'],
                    'code' => (string)$r['product_code'],
                );
            }
            $preselected_json = json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        }
    }
}
$GLOBALS['smarty']->assign('PRESELECTED_PRODUCTS_JSON', $preselected_json);

$page_content = $GLOBALS['smarty']->fetch('templates/products.optionsets.php');
