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

if (Admin::getInstance()->is()) {
    Admin::getInstance()->permissions('products', CC_PERM_READ, true);
}


$seo  = SEO::getInstance();
$catalogue = Catalogue::getInstance();

$per_page = (isset($_GET['per_page'])) ? $_GET['per_page'] : 500;
$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
//$no_rows = $GLOBALS['db']->numrows('SELECT `product_id` FROM '.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_inventory');
$no_rows = $GLOBALS['db']->numrows(sprintf('SELECT I.product_id FROM %1$sCubeCart_inventory AS I LEFT JOIN %1$sCubeCart_category AS C ON I.cat_id = C.cat_id WHERE I.status = 1', $GLOBALS['config']->get('config', 'dbprefix')));

function download_parts($format = 'cubecart', $no_rows = '', $per_page = '')
{
    $no_pages = ceil($no_rows / $per_page);
    ## If there are no pages (less that per page) we need page 1 for an export
    $no_pages = ($no_pages) ? $no_pages : 1;
    $html_out = null;
    for ($i = 1; $i <= $no_pages; ++$i) {
        $html_out .= '<a href="?_g=products&node=export&page='.$i.'&per_page='.$per_page.'&format='.$format.'">'.$i.'</a> ';
    }
    return $html_out;
}

foreach ($GLOBALS['hooks']->load('admin.product.export') as $hook) {
    include $hook;
}

if (isset($_GET['format']) && !empty($_GET['format'])) {
    if ($_GET['format'] == 'cubecart') {
        $query = sprintf('SELECT I.* FROM %1$sCubeCart_inventory AS I INNER JOIN %1$sCubeCart_category_index AS R ON I.product_id = R.product_id LEFT JOIN %1$sCubeCart_category AS C ON R.cat_id = C.cat_id WHERE R.primary = 1 AND I.status = 1 AND C.status =1', $GLOBALS['config']->get('config', 'dbprefix'));
    } else {
        $query = sprintf('SELECT I.* FROM %1$sCubeCart_inventory AS I LEFT JOIN %1$sCubeCart_category AS C ON I.cat_id = C.cat_id WHERE I.status = 1 AND C.status = 1', $GLOBALS['config']->get('config', 'dbprefix'));
    }

    if ($results = $GLOBALS['db']->query($query, $per_page, $page)) {
        $header_fields = array('Product Name', 'Status', 'Include in featured products', 'Include in latest products', 'Product Code', 'Weight', 'Description', 'Short Description', 'Price', 'Sale Price', 'Cost Price', 'Tax Class', 'Tax Inclusive', 'Images', 'Stock Level', 'Use Stock Level', 'Stock Level Warning', 'Master Category ID', 'Manufacturer', 'UPC Code', 'EAN Code', 'JAN Code', 'ISBN Code', 'Brand', 'MPN Code', 'GTIN Code', 'Meta Title', 'Meta Description', 'Condition', 'Digital', 'Digital Path (Legacy)', 'Product Width', 'Product Height', 'Product Depth', 'Dimension Unit');
        $fields  = array('name', 'status', 'featured', 'latest', 'product_code', 'product_weight', 'description', 'description_short', 'price', 'sale_price', 'cost_price', 'tax_type', 'tax_inclusive', 'image', 'stock_level', 'use_stock_level', 'stock_warning', 'cat_id', 'manufacturer', 'upc', 'ean', 'jan', 'isbn', 'brand', 'mpn', 'gtin', 'seo_meta_title', 'seo_meta_description', 'condition', 'digital', 'digital_path', 'product_width', 'product_height', 'product_depth', 'dimension_unit');
        $delimiter = ',';
        $extension = 'csv';
        $glue  = "\n";
        $field_wrapper = '"';
        $field_keys_to_wrap = $fields;
        $image_path = 'filename';
        $image_mode = 'source';

        foreach ($GLOBALS['hooks']->load('admin.product.import.format') as $hook) {
            include $hook;
        }

        // ---- Bulk pre-fetch to avoid per-row N+1 queries ----------------------
        // Previously each iteration of this loop fired ~5 SELECTs (manufacturer,
        // category_index, image_index, seo_urls via generatePath, filemanager via
        // imagePath). On a 240k-product store that meant >1M round-trips per export.
        // Now we resolve everything once per page in O(constant) queries keyed by id.
        $prefix = $GLOBALS['config']->get('config', 'dbprefix');
        $product_ids  = array();
        $manuf_ids    = array();
        foreach ($results as $r) {
            if (!empty($r['product_id'])) $product_ids[] = (int)$r['product_id'];
            if (!empty($r['manufacturer'])) $manuf_ids[] = (int)$r['manufacturer'];
        }
        $product_ids = array_values(array_unique($product_ids));
        $manuf_ids   = array_values(array_unique($manuf_ids));
        $pid_in = implode(',', array_map('intval', $product_ids));

        $cat_index_by_pid = array();
        $image_files_by_pid = array();
        $manuf_name_by_id = array();
        $filemanager_by_id = array();

        if (!empty($product_ids)) {
            // Categories per product, primary first.
            if (($rows = $GLOBALS['db']->misc("SELECT `product_id`, `cat_id` FROM `{$prefix}CubeCart_category_index` WHERE `product_id` IN ($pid_in) ORDER BY `primary` DESC")) !== false && is_array($rows)) {
                foreach ($rows as $row) {
                    $cat_index_by_pid[(int)$row['product_id']][] = (int)$row['cat_id'];
                }
            }
            // Image file_ids per product, main_img first.
            $all_file_ids = array();
            if (($rows = $GLOBALS['db']->misc("SELECT `product_id`, `file_id` FROM `{$prefix}CubeCart_image_index` WHERE `product_id` IN ($pid_in) ORDER BY `main_img` DESC")) !== false && is_array($rows)) {
                foreach ($rows as $row) {
                    $image_files_by_pid[(int)$row['product_id']][] = (int)$row['file_id'];
                    $all_file_ids[(int)$row['file_id']] = true;
                }
            }
            // Resolve filemanager rows in one shot (used by imagePath()).
            if (!empty($all_file_ids)) {
                $fid_in = implode(',', array_map('intval', array_keys($all_file_ids)));
                if (($rows = $GLOBALS['db']->misc("SELECT `file_id`, `filepath`, `filename` FROM `{$prefix}CubeCart_filemanager` WHERE `file_id` IN ($fid_in)")) !== false && is_array($rows)) {
                    foreach ($rows as $row) {
                        $filemanager_by_id[(int)$row['file_id']] = $row['filepath'].$row['filename'];
                    }
                }
            }
            // SEO product paths — bulk-loaded once and seeded into the SEO
            // class cache so generatePath() skips its own per-row SELECT.
            if (($rows = $GLOBALS['db']->misc("SELECT `type`, `item_id`, `path` FROM `{$prefix}CubeCart_seo_urls` WHERE `type`='prod' AND `redirect`=0 AND `item_id` IN ($pid_in)")) !== false && is_array($rows)) {
                $seo->primeSeoUrls($rows);
            }
        }
        if (!empty($manuf_ids)) {
            $mid_in = implode(',', array_map('intval', $manuf_ids));
            if (($rows = $GLOBALS['db']->misc("SELECT `id`, `name` FROM `{$prefix}CubeCart_manufacturers` WHERE `id` IN ($mid_in)")) !== false && is_array($rows)) {
                foreach ($rows as $row) {
                    $manuf_name_by_id[(int)$row['id']] = $row['name'];
                }
            }
        }
        $store_url = $GLOBALS['storeURL'];
        $oos_purchase = $GLOBALS['config']->get('config', 'basket_out_of_stock_purchase');
        $tax  = Tax::getInstance();

        foreach ($results as $i => $result) {
            # strip tags is plain text file CSV should be good to keep but lose two double quotes
            // The result row already has stock_level from CubeCart_inventory; no need
            // to re-query via getProductStock for the simple availability flag.
            $stock_level = $result['stock_level'];
            if ($result['use_stock_level'] && !$oos_purchase) {
                $result['availability'] = ($stock_level <= 0) ? 'out of stock' : 'in stock';
            } else {
                $result['availability'] = 'in stock';
            }
            if ($extension == 'csv') {
                $result['name']   = str_replace('"', '""', $result['name']);
                $result['description'] = str_replace('"', '""', $result['description']);
            } else {
                $result['name']   = preg_replace('#[\s]{2,}#', ' ', str_replace(array("&nbsp;", "\t", "\r", "\n", "\0", "\x0B"), '', strip_tags($result['name'])));
                $result['description'] = preg_replace('#[\s]{2,}#', ' ', str_replace(array("&nbsp;", "\t", "\r", "\n", "\0", "\x0B"), '', strip_tags($result['description'])));
            }

            // getDirectory() uses an internal in-process cache of the full category
            // tree (built once from one query), so these calls are PHP-only.
            $result['store_category'] = $GLOBALS['seo']->getDirectory($result['cat_id'], false, ' > ');
            $result['shopping_com_category'] = $GLOBALS['seo']->getDirectory($result['cat_id'], false, ' -> ');
            if (isset($result['mpn']) && empty($result['mpn']) && isset($result['gtin']) && empty($result['gtin'])) {
                $result['identifier_exists'] = 'FALSE';
            } else {
                $result['identifier_exists'] = 'TRUE';
            }

            $result['condition'] = (empty($result['condition'])) ? 'new' : $result['condition'];

            $pid = (int)$result['product_id'];
            if (!empty($cat_index_by_pid[$pid])) {
                $result['cat_id'] = implode(',', $cat_index_by_pid[$pid]);
            }

            # Manufacturer
            if (!empty($result['manufacturer'])) {
                $result['manufacturer'] = isset($manuf_name_by_id[(int)$result['manufacturer']]) ? $manuf_name_by_id[(int)$result['manufacturer']] : '';
            } else {
                $result['manufacturer'] = '';
            }

            # Price
            $sale    = $tax->salePrice($result['price'], $result['sale_price'], false);
            $result['price'] = ($sale > 0 && strtolower($_GET['format']) != 'cubecart') ? $sale : $result['price'];

            $result['price_formatted'] = $tax->priceFormat($result['price'], true);

            ## Generate Product URL — generatePath now hits the cache primed above (no per-row SELECT).
            $url = $seo->generatePath($pid, 'product', 'product_id', true);
            $result['url'] = $seo->fullURL($url, true);

            ## Generate Image URL — resolve via the pre-fetched filemanager map.
            if (!empty($image_files_by_pid[$pid])) {
                $image_array = array();
                foreach ($image_files_by_pid[$pid] as $file_id) {
                    if (isset($filemanager_by_id[$file_id])) {
                        // mode='source', path='filename' just yields the bare filename;
                        // we replicate that without a CubeCart_filemanager round-trip.
                        $image_array[] = basename($filemanager_by_id[$file_id]);
                    }
                }
                $result['image'] = implode(',', $image_array);
            } else {
                $result['image'] = '';
            }

            $result['currency'] = $GLOBALS['config']->get('config', 'default_currency');
            //CSV must have double quotes around strings. This is the standard and most spreasheets will behave best this way
            foreach ($fields as $field) {
                // format specialist fields e.g. 'price currency' to '9.99 USD'
                if (stristr($field, " ")) {
                    $exploded_fields = explode(' ', $field);
                    foreach ($exploded_fields as $part_field) {
                        $formatted_field[] = $result[$part_field];
                    }
                    $result[$field] = implode(' ', $formatted_field);
                }
                unset($formatted_field, $exploded_fields);

                $data_fields[] = (in_array($field, $field_keys_to_wrap) && isset($result[$field])) ? $field_wrapper.$result[$field].$field_wrapper : $result[$field];
            }

            if (isset($header_fields)) {
                $output[] = implode($delimiter, $header_fields);
                unset($header_fields);
            }
            $output[] = implode($delimiter, $data_fields);
            unset($data_fields);
        }
        if (isset($output) && !empty($output)) {
            $filename = $_GET['format'].'_'.date('Ymd').'_'.$_GET['page'].'.'.$extension;
            $output  = (is_array($output)) ? implode($glue, $output) : $output;
            $GLOBALS['debug']->supress();
            if (!isset($_GET['access'])) {
                deliverFile(false, false, $output, $filename);
            } else {
                $method = $path = '';
				foreach ($GLOBALS['hooks']->load('admin.product.export.method') as $hook) include $hook;
				if($method == 'write' && !empty($path)) {
					$fp = fopen($path, 'w');
					fwrite($fp, $output);
					fclose($fp);	
				} else {
					echo $output;
				}
            }
            exit;
        }
    } else {
        $GLOBALS['main']->errorMessage($lang['category']['no_products']);
    }
}

$GLOBALS['main']->addTabControl($lang['common']['export'], 'export');

$formats = array('cubecart'  => 'CubeCart');

foreach ($GLOBALS['hooks']->load('admin.product.import.list') as $hook) {
    include $hook;
}

$page_limits = array(
    50, 100, 250, 500, 1000, 5000, 10000, 25000
);

foreach ($page_limits as $limit_value) {
    $limit['selected'] = ($limit_value==$per_page) ? 'selected="selected"' : null;
    $limit['per_page'] = $limit_value;
    $smarty_data['limits'][] = $limit;
}
$GLOBALS['smarty']->assign('LIMITS', $smarty_data['limits']);

foreach ($formats as $format_key => $format_name) {
    $format['name']  = $format_name;
    $format['parts']  = download_parts($format_key, $no_rows, $per_page);
    $format['link']  = $GLOBALS['storeURL'].'/'.$GLOBALS['config']->get('config', 'adminFile')."?_g=products&node=export&page=1&per_page=1000000&format=$format_key&node=export&access=".urlencode($GLOBALS['config']->get('config', 'feed_access_key'));
    $smarty_data['formats'][] = $format;
}
$GLOBALS['smarty']->assign('FORMATS', $smarty_data['formats']);

$page_content = $GLOBALS['smarty']->fetch('templates/products.export.php');
