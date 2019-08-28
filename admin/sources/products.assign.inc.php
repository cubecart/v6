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
Admin::getInstance()->permissions('products', CC_PERM_READ, true);

$GLOBALS['smarty']->assign('MODE', isset($_GET['prices']) ? 'prices' : 'assign_only');

### handle post and save
if (Admin::getInstance()->permissions('products', CC_PERM_EDIT, true) && isset($_POST) && is_array($_POST) && count($_POST)>0) {

    ## Assign products to categories
    $products_assigned = false;
    if (is_array($_POST['category']) && is_array($_POST['product'])) {
        foreach ($_POST['product'] as $product_id) {
            if (!is_numeric($product_id) || !is_array($_POST['category'])) {
                continue;
            }
            //Delete all the category related to comming product id  to fix bug 2840
            $GLOBALS['db']->delete('CubeCart_category_index', array('product_id' => (int)$product_id));
            foreach ($_POST['category'] as $category_id) {
                if (!is_numeric($category_id)) {
                    continue;
                }
                if($GLOBALS['db']->insert('CubeCart_category_index', array('cat_id' => (int)$category_id, 'product_id' => (int)$product_id))){
                    $products_assigned = true;
                }
            }
        }
    }

    if ($_POST['price']['what']=='products') {
        $product_ids = $_POST['product'];
    } elseif (array_map('ctype_digit', $_POST['category'])) {
        if ($category_products = $GLOBALS['db']->select('CubeCart_category_index', array('DISTINCT' => 'product_id'), array('cat_id' => $_POST['category']))) {
            foreach ($category_products as $category_product) {
                $product_ids[] = $category_product['product_id'];
            }
        }
    }

    if (is_array($product_ids) && isset($_POST['price']) && is_array($_POST['price']) && Admin::getInstance()->permissions('products', CC_PERM_EDIT)) {
        $prices_updated = false;
        if (!empty($_POST['price']['value']) && is_numeric($_POST['price']['value'])) {
            ## Update prices by x amount/percent
            foreach ($product_ids as $product_id) {
                if (!is_numeric($product_id)) {
                    continue;
                }

                $fields = ($_POST['price']['field'] == 'all') ? array('price', 'sale_price', 'cost_price','quantity_discounts', 'product_options') : array($_POST['price']['field']);

                $action	= $_POST['price']['action'];
                $value	= $_POST['price']['value'];
                $shift	= ($action) ? 1 : 0;
                
                foreach ($fields as $field) {
                    switch ($field) {
                        case 'quantity_discounts':
                            $table = 'CubeCart_pricing_quantity';
                            $price_column = 'price';
                            $id_column = 'discount_id';
                            $product_id_column = 'product_id';
                        break;
                        case 'product_options':
                            $table = 'CubeCart_option_assign';
                            $price_column = 'option_price';
                            $id_column = 'assign_id';
                            $product_id_column = 'product';
                        break;
                        case 'price':
                        case 'sale_price':
                        case 'cost_price':
                            $table = 'CubeCart_inventory';
                            $price_column = $field;
                            $id_column = 'product_id';
                            $product_id_column = 'product_id';
                        break;
                    }

                    if (($price_rows = $GLOBALS['db']->select($table, array($price_column,$id_column), array($product_id_column => (int)$product_id))) !== false) {
                        foreach ($price_rows as $price_row) {
                            $price	= $price_row[$price_column];
                            switch (strtolower($_POST['price']['method'])) {
                                case 'percent':
                                    $price	= $price_row[$price_column] * (($value/100)+(int)$shift);
                                    break;
                                default:
                                    if ($action === '2') {
                                        $price	= $value;
                                    } else {
                                        $price	+= ($action) ? $value : $value-($value*2);
                                    }
                            }
                            if($GLOBALS['db']->update($table, array($price_column => $price), array($id_column => (int)$price_row[$id_column]))) {
                                $prices_updated = true;
                            }
                        }
                    }
                }
            }
        }
    }
    if (isset($_GET['prices']) && $prices_updated) {
        $GLOBALS['main']->successMessage($lang['catalogue']['notify_prices_updates']);
    } elseif($products_assigned) {
        $GLOBALS['main']->successMessage($lang['catalogue']['notify_assign_update']);
    } else {
        $GLOBALS['main']->errorMessage($lang['common']['error_no_changes']);
    }
    httpredir(currentPage());
} elseif (isset($_POST['price'])) {
    $GLOBALS['main']->errorMessage($lang['common']['error_no_changes']);
}

if (!isset($_GET['prices'])) {
    $GLOBALS['main']->addTabControl($lang['catalogue']['title_product_list'], null, currentPage(array('node')));
    $GLOBALS['main']->addTabControl($lang['catalogue']['product_add'], null, currentPage(array('node'), array('action' => 'add')));
    $GLOBALS['main']->addTabControl($lang['catalogue']['title_category_assigned'], 'assign');
    $GLOBALS['main']->addTabControl($lang['catalogue']['title_option_set_assign'], null, currentPage(null, array('node' => 'optionsets')));
    $GLOBALS['gui']->addBreadcrumb($lang['catalogue']['title_category_assigned'], currentPage());
} else {
    $GLOBALS['main']->addTabControl($lang['catalogue']['title_bulk_prices'], 'assign');
    $GLOBALS['gui']->addBreadcrumb($lang['catalogue']['title_bulk_prices'], currentPage());
}


## Product list
if (($products = $GLOBALS['db']->select('CubeCart_inventory', array('product_id', 'name', 'product_code'), false, array('name' => 'ASC'))) !== false) {
    $GLOBALS['smarty']->assign('PRODUCTS', $products);
}
## Category list
if (($category_array = $GLOBALS['db']->select('CubeCart_category', array('cat_name', 'cat_parent_id', 'cat_id'))) !== false) {
    $cat_list[] = '/';
    $seo  = SEO::getInstance();
    foreach ($category_array as $category) {
        if ($category['cat_id'] == $category['cat_parent_id']) {
            continue;
        }
        $cat_list[$category['cat_id']] = '/'.$seo->getDirectory($category['cat_id'], false, '/', false, false);
    }
    natcasesort($cat_list);
    foreach ($cat_list as $cat_id => $cat_name) {
        if (empty($cat_name) || $cat_id==0) {
            continue;
        }
        $data = array(
            'id'  => $cat_id,
            'name'  => $cat_name,
            'selected' => (isset($cats_selected) && in_array($cat_id, $cats_selected)) ? ' checked="checked"' : '',
        );
        $smarty_data['categories'][] = $data;
    }
    $GLOBALS['smarty']->assign('CATEGORIES', $smarty_data['categories']);
}

$page_content = $GLOBALS['smarty']->fetch('templates/products.assign.php');
