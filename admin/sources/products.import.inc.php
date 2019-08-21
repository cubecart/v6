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
Admin::getInstance()->permissions('products', CC_PERM_EDIT, true);

global $lang;

$dir 			= CC_ROOT_DIR.CC_DS.'includes'.CC_DS.'extra'.CC_DS;
$source			= $dir.'importdata.tmp';
$import_source	= $dir.'importdata_%s.tmp';
$splitSize 		= 50;

$delimiter	= (isset($_POST['delimiter']) && !empty($_POST['delimiter'])) ? $_POST['delimiter'] : ',';

$GLOBALS['main']->addTabControl($lang['common']['import'], 'general');
if (isset($_POST['process']) || isset($_GET['cycle'])) {
    ## This will (theoretically) prevent a partial import
    ignore_user_abort(true);
    set_time_limit(0);
    ini_set('max_execution_time', '0');
    
    ## Truncate?
    if (isset($_POST['option']['truncate'])) {
        $tables = array(
            'CubeCart_inventory',
            'CubeCart_image_index',
            'CubeCart_option_assign',
            'CubeCart_reviews',
            'CubeCart_category_index',
            'CubeCart_inventory_language',
            'CubeCart_options_set_product',
            'CubeCart_pricing_quantity',
            'CubeCart_pricing_group',
        );
        $GLOBALS['db']->truncate($tables);
        $GLOBALS['db']->delete('CubeCart_seo_urls', array('type' => 'prod'));
    }

    $column		= 0;
    
    if (isset($_POST['map']) && is_array($_POST['map'])) {
        $GLOBALS['session']->set('map', $_POST['map'], 'import');
        
        $delimiter	= (isset($_POST['delimiter']) && !empty($delimiter)) ? $_POST['delimiter'] : ',';
        $GLOBALS['session']->set('delimiter', $delimiter, 'import');
        
        $has_header	= (isset($_POST['option']['headers'])) ? true : false;
        $GLOBALS['session']->set('headers', $has_header, 'import');
    }

    if ($GLOBALS['session']->has('map', 'import')) {
        ## Use the user defined mapping
        foreach ($GLOBALS['session']->get('map', 'import') as $col => $value) {
            $map[$column++]	= (string)$value;
        }
        $delimiter	= $GLOBALS['session']->get('delimiter', 'import');
        $has_header	= $GLOBALS['session']->get('headers', 'import');
        if (!isset($_GET['cycle'])) {
            $cycle = 1;
        } else {
            $cycle = $_GET['cycle'];
        }
    }
    $this_import = sprintf($import_source, $cycle);
    if (isset($map) && file_exists($this_import)) {

        ## Load source data
        $fp	= fopen($this_import, 'r');
        if ($fp) {
            $row	= 0;
            $insert	= 0;
            if ($cycle == 1) {
                $GLOBALS['session']->set('date_added', date('Y-m-d H:i:s', time()), 'import');
            }
            while (($data = fgetcsv($fp, false, str_replace('tab', "\t", $delimiter))) !== false) {
                $row++;
                if ($cycle == 1 && $has_header && $row == 1) {
                    $headers	= $data;
                    continue;
                }
                foreach ($data as $offset => $value) {
                    $field_name	= ($polymorph) ? $map[$headers[$offset]] : $map[$offset];
                    
                    if (in_array($field_name, array('price','sale_price','cost_price'))) {
                        $value = preg_replace("/[^0-9\.]/", "", $value);
                    } elseif ($field_name == 'manufacturer' && !empty($value) && !is_numeric($value)) {
                        if (($manufacturer = $GLOBALS['db']->select('CubeCart_manufacturers', false, array('name' => $value), false, 1)) !== false) {
                            $value	= $manufacturer[0]['id'];
                        } else {
                            ## Insert new manufacturer?
                            if ($GLOBALS['db']->insert('CubeCart_manufacturers', array('name' => $value))) {
                                $value	= (int)$GLOBALS['db']->insertid();
                            }
                        }
                    } elseif ($field_name == 'image' && !empty($value) && !is_numeric($value)) {
                        foreach ($GLOBALS['hooks']->load('admin.product.import.image.pre_process') as $hook) {
                            include $hook;
                        }
                        
                        $image_splits = explode(',', $value);
                    
                        foreach ($image_splits as $value) {
                            $image_name = basename(trim($value));
                            $image_path = preg_replace('/^[.\/]/', '', dirname($value)); // lose first slash to match DB storage but add end slash
                            if (!empty($image_path)) {
                                $image_path .= '/';
                            }
                            $image = $GLOBALS['db']->select('CubeCart_filemanager', array('file_id'), array('filename' => $image_name, 'type' => 1, 'filepath' => empty($image_path) ? 'NULL' : $image_path), false, 1);
                            
                            if (!$image) {
                                $root_image_path = CC_ROOT_DIR.'/images/source/'.$image_path.$image_name;
                                if (file_exists($root_image_path)) {
                                    $finfo = (extension_loaded('fileinfo')) ? new finfo(FILEINFO_SYMLINK | FILEINFO_MIME) : false;
                                    if ($finfo && $finfo instanceof finfo) {
                                        preg_match('#([\w\-\.]+)/([\w\-\.]+)$#iU', $finfo->file($root_image_path), $match);
                                        $mime	= $match[0];
                                    } elseif (function_exists('mime_content_type')) {
                                        $mime	= mime_content_type($root_image_path);
                                    } else {
                                        $img_info	= getimagesize($root_image_path);
                                        $mime	= $img_info['mime'];
                                    }
                                    $filesize = filesize($root_image_path);
                                    $filesize = ($filesize > 0)? $filesize : 0;
                                }

                                if ($GLOBALS['db']->insert('CubeCart_filemanager', array('type' => 1, 'filepath' => empty($image_path) ? 'NULL' : $image_path, 'filename' => $image_name, 'filesize' => $filesize, 'mimetype' => $mime, 'md5hash' => md5($root_image_path)))) {
                                    $images[]	= $GLOBALS['db']->insertid();
                                }
                            } else {
                                $images[] = $image[0]['file_id'];
                            }
                        }
                    }
                    if ($polymorph) {
                        if (isset($map[$headers[$offset]])) {
                            $product_record[$map[$headers[$offset]]] = $value;
                        }
                    } else {
                        $product_record[$map[$offset]] = $value;
                    }
                }
                // Insert if we have a product record with at minimum a value for the product name
                if (isset($product_record) && !empty($product_record) && !empty($product_record['name'])) {
                    $product_record['date_added']	= $GLOBALS['session']->get('date_added', 'import');
                    // Insert product
                    if (!isset($product_record['product_code']) || empty($product_record['product_code'])) {
                        $product_record['product_code'] = generate_product_code($product_record['name']);
                    }

                    if (!isset($product_record['latest']) || !in_array((string)$product_record['latest'], array('1','0'))) {
                        $product_record['latest'] = '1';
                    }
                    if (!isset($product_record['featured']) || !in_array((string)$product_record['featured'], array('1','0'))) {
                        $product_record['featured'] = '1';
                    }
                    // If no stock level is set we assume no stock control is used
                    if (isset($product_record['use_stock_level']) && ($product_record['use_stock_level']==1 || strtolower($product_record['use_stock_level'])=='true')) {
                        $product_record['use_stock_level'] = 1;
                    } elseif (!isset($product_record['stock_level']) || empty($product_record['stock_level'])) {
                        $product_record['use_stock_level'] = 0;
                    }

                    if ($GLOBALS['db']->insert('CubeCart_inventory', $product_record)) {
                        $insert++;
                    }
                    // Insert primary category
                    $product_id = $GLOBALS['db']->insertid();
                    if (isset($product_record['cat_id']) && !empty($product_record['cat_id'])) {
                        $cats = explode(',', $product_record['cat_id']);
                        $primary = 1;
                        foreach($cats as $cat) {
							$cat = trim($cat);
							if(!is_numeric($cat)) {
								$breadcrumbs = explode("/", $cat);
								$cat = 0;
								if(!empty($breadcrumbs[0])) {
									foreach($breadcrumbs as $key => $breadcrumb) {
										$breadcrumb = trim($breadcrumb);
										if($key===0) {
											if($existing = $GLOBALS['db']->select('CubeCart_category', array('cat_id'), array('cat_name' => $breadcrumb, 'cat_parent_id' => $cat), false, false, false, false)) {
												$cat = $existing[0]['cat_id'];
											} else {
												$cat = $GLOBALS['db']->insert('CubeCart_category', array('cat_name' => $breadcrumb, 'cat_parent_id' => $cat));
											}
										} else {
											if($existing = $GLOBALS['db']->select('CubeCart_category', array('cat_id'), array('cat_name' => $breadcrumb, 'cat_parent_id' => $cat), false, false, false, false)) {
												$cat = $existing[0]['cat_id'];
											} else {
												$cat = $GLOBALS['db']->insert('CubeCart_category', array('cat_name' => $breadcrumb, 'cat_parent_id' => $cat));
											}
										}
									}
								}
							}

							if(is_numeric($cat) && $cat>0) {
								$category_record = array (
									'product_id' => $product_id,
									'cat_id'  => $cat,
									'primary'  => $primary
								);
								if($primary==1) { 
									$primary_category = $cat;
								}
								$primary = 0;
								$GLOBALS['db']->insert('CubeCart_category_index', $category_record);
							}
						}
                        $product_record['cat_id'] = $primary_category;
                    }
                    if (is_array($images)) {
                        $primary = 1;
                        foreach ($images as $file_id) {
                            $image_record = array(
                                'product_id'	=> $product_id,
                                'file_id'		=> $file_id,
                                'main_img'		=> $primary
                            );
                            $GLOBALS['db']->insert('CubeCart_image_index', $image_record);
                            $primary = 0;
                        }
                    }
                    // Insert SEO custom URL
                    if (empty($product_record['seo_path'])) {
                        $product_record['seo_path'] = $GLOBALS['seo']->generatePath($product_id, 'prod');
                    }
                    $GLOBALS['db']->insert('CubeCart_seo_urls', array('path'=> SEO::sanitizeSEOPath($product_record['seo_path']), 'item_id' => $product_id, 'type' => 'prod'));
                }
                unset($product_record, $category_record, $image_record, $image, $images);
            }
            fclose($fp);
        }
        unlink($this_import);
    }
    $next_cycle = $cycle+1;
    if (file_exists(sprintf($import_source, $next_cycle))) {
        $data = array(
            'next_cycle' => $next_cycle,
            'total' => ($has_header) ? $GLOBALS['session']->get('columns', 'import')-1 : $GLOBALS['session']->get('columns', 'import'),
            'imported' => $cycle * $splitSize
        );

        $GLOBALS['smarty']->assign('DATA', $data);
        
        $page_content = $GLOBALS['smarty']->fetch('templates/products.importing.php');
    } else {
        $GLOBALS['main']->successMessage($lang['catalogue']['notify_import_complete']);
        $GLOBALS['session']->delete('', 'import');
        httpredir('?_g=products&node=import');
    }
} elseif (isset($_POST['upload'])) {
    ## Remove previous import data
    if (isset($_POST['revert']) && is_array($_POST['revert'])) {
        foreach ($_POST['revert'] as $revert) {
            $products = $GLOBALS['db']->select('CubeCart_inventory', array('product_id'), array('date_added' => (string)$revert));
            if ($products) {
                foreach ($products as $product) {
                    $GLOBALS['db']->delete('CubeCart_category_index', array('product_id' => $product['product_id']));
                    $GLOBALS['db']->delete('CubeCart_image_index', array('product_id' => $product['product_id']));
                    $GLOBALS['db']->delete('CubeCart_option_assign', array('product' => $product['product_id']));
                    $GLOBALS['db']->delete('CubeCart_reviews', array('product_id' => $product['product_id']));
                    $GLOBALS['db']->delete('CubeCart_inventory_language', array('product_id' => $product['product_id']));
                    $GLOBALS['db']->delete('CubeCart_options_set_product', array('product_id' => $product['product_id']));
                    $GLOBALS['db']->delete('CubeCart_pricing_quantity', array('product_id' => $product['product_id']));
                    $GLOBALS['db']->delete('CubeCart_pricing_group', array('product_id' => $product['product_id']));
                    $GLOBALS['db']->delete('CubeCart_seo_urls', array('type' => 'prod', 'item_id' => $product['product_id']));
                }
                $GLOBALS['db']->delete('CubeCart_inventory', array('date_added' => (string)$revert));
                $GLOBALS['main']->successMessage($lang['catalogue']['notify_import_removed']);
            } else {
                $GLOBALS['main']->errorMessage($lang['catalogue']['notify_import_removed_fail']);
            }
        }
        httpredir(currentPage());
    } elseif (is_uploaded_file($_FILES['source']['tmp_name']) && move_uploaded_file($_FILES['source']['tmp_name'], $source)) {

        // Split source file into 50 rows at a time
        $outputFile = $dir.'importdata_';
        $in = fopen($source, 'r');

        $rowCount = 0;
        $fileCount = 1;
        while (!feof($in)) {
            if (($rowCount % $splitSize) == 0) {
                if ($rowCount > 0) {
                    fclose($out);
                }
                $out = fopen($outputFile . $fileCount++ . '.tmp', 'w');
            }
            $data = fgetcsv($in);
            if ($data) {
                fputcsv($out, $data);
            }
            $rowCount++;
        }
        $GLOBALS['session']->set('columns', $rowCount, 'import');
        fclose($in);
        fclose($out);

        ## Display interstitial page before actually importing, either displaying example data from source, or a means to map the CSV to the database columns
        $delimiter	= (isset($_POST['delimiter']) && !empty($_POST['delimiter'])) ? $_POST['delimiter'] : ',';
        ## No format map available, so give them a manual assignment form
            $fields	= array(	# Update for language strings
                'available'			=> $lang['catalogue']['available_for_purchase'],
                'status'			=> $lang['common']['status'],
                'name'				=> $lang['catalogue']['product_name'],
                'image'				=> $lang['catalogue']['image_comma'],
                'product_code'		=> $lang['catalogue']['product_code'],
                'cat_id'			=> $lang['catalogue']['master_caregory_id'],
                'description'		=> $lang['common']['description'],
                'description_short'		=> $lang['common']['description_short'],
                'manufacturer'		=> $lang['catalogue']['manufacturer'],
                'price'				=> $lang['common']['price'],
                'sale_price'		=> $lang['common']['price_sale'],
                'cost_price'		=> $lang['common']['price_cost'],
                'product_weight'	=> $lang['common']['weight'],
                'use_stock_level'	=> $lang['catalogue']['stock_level_use'],
                'stock_level'		=> $lang['catalogue']['stock_level'],
                'stock_warning'		=> $lang['catalogue']['stock_level_warn'],
                'digital'			=> $lang['catalogue']['is_digital'],
                'digital_path'		=> $lang['catalogue']['file_path'],
                'tax_type'			=> $lang['catalogue']['tax_class'],
                'tax_inclusive'		=> $lang['catalogue']['tax_inclusive'],
                'featured'			=> $lang['catalogue']['product_featured'],
                'latest'			=> $lang['catalogue']['product_latest'],
                'seo_path'			=> $lang['settings']['seo_path'],
                'seo_meta_title'		=> $lang['settings']['seo_meta_title'],
                'seo_meta_description'	=> $lang['settings']['seo_meta_description'],
                'condition'			=> $lang['catalogue']['condition'],
                'upc'				=> $lang['catalogue']['product_upc'],
                'ean'				=> $lang['catalogue']['product_ean'],
                'jan'				=> $lang['catalogue']['product_jan'],
                'isbn'				=> $lang['catalogue']['product_isbn'],
                'brand'				=> $lang['catalogue']['product_brand'],
                'gtin'				=> $lang['catalogue']['product_gtin'],
                'mpn'				=> $lang['catalogue']['product_mpn'],
                'condition'			=> $lang['catalogue']['condition'],
                'product_width'			=> $lang['catalogue']['product_width'],
                'product_height'			=> $lang['catalogue']['product_height'],
                'product_depth'			=> $lang['catalogue']['product_depth'],
                'dimension_unit'			=> $lang['catalogue']['dimension_unit']
            );
        $fp		= fopen($source, 'r');
        $data	= fgetcsv($fp, null, str_replace('tab', "\t", $delimiter));
        fclose($fp);
        unlink($source);

        if (is_array($data)) {
            foreach ($data as $offset => $value) {
                $smarty_data['maps'][]	= array('offset' => (int)$offset, 'example' => $value);
            }
            foreach ($fields as $column => $title) {
                $smarty_data['columns'][] = array('column' => $column, 'title' => $title);
            }
            $GLOBALS['smarty']->assign('COLUMNS', $smarty_data['columns']);
            $GLOBALS['smarty']->assign('MAPS', $smarty_data['maps']);
            $GLOBALS['smarty']->assign('IMPORT', array('delimiter' => $_POST['delimiter']));

            $smarty_data['map']	= '';
        } else {
            $GLOBALS['main']->errorMessage($lang['catalogue']['error_import_empty']);
            httpredir(currentPage());
        }
        $GLOBALS['smarty']->assign('DISPLAY_CONFIRMATION', true);
    } else {
        $GLOBALS['main']->errorMessage($lang['catalogue']['error_import_upload']);
        httpredir(currentPage());
    }
    $page_content = $GLOBALS['smarty']->fetch('templates/products.import.php');
} else {
    ## Find previous imports, and list
    if (($reverts = $GLOBALS['db']->query(sprintf("SELECT COUNT(product_id) AS Count, date_added FROM %sCubeCart_inventory WHERE 1 GROUP BY date_added ORDER BY date_added ASC", $GLOBALS['config']->get('config', 'dbprefix')))) !== false) {
        foreach ($reverts as $revert) {
            if ($revert['date_added'] == 0 || $revert['Count'] == 1) {
                continue;
            }
            $revert['date_added_fuzzy'] = formatTime(strtotime($revert['date_added']));
            $smarty_data['reverts'][]	= $revert;
            $block	= true;
        }
        if (isset($smarty_data['reverts'])) {
            $GLOBALS['smarty']->assign('REVERTS', $smarty_data['reverts']);
        }
        if (isset($block)) {
            $GLOBALS['main']->addTabControl($lang['catalogue']['tab_import_revert'], 'revert');
        }
    }
    $GLOBALS['smarty']->assign('DISPLAY_FORM', true);
    $GLOBALS['smarty']->assign('UPLOAD_LIMIT', ini_get('upload_max_filesize'));
    $page_content = $GLOBALS['smarty']->fetch('templates/products.import.php');
}
