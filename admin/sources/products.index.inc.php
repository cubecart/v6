<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('products', CC_PERM_READ, true);

global $lang;

#########################################################
if (isset($_POST['search']) && !empty($_POST['search'])) {
	if (is_numeric($_POST['search']['product_id'])) {
		httpredir('?_g=products&action=edit&product_id='.$_POST['search']['product_id']);
	} else {
		httpredir('?_g=products&q='.$_POST['search']['product']);
	}
}

if (($cat_dropdown = $GLOBALS['cache']->read('products_category_dropdown')) === false || empty($cat_dropdown)) {
	$cat_dropdown = $GLOBALS['catalogue']->buildCategoriesDropDown();
	$GLOBALS['cache']->write($cat_dropdown, 'products_category_dropdown');
}
$GLOBALS['smarty']->assign('CAT_LIST', $cat_dropdown);
$GLOBALS['smarty']->assign('CURRENT_CAT', (isset($_GET['cat_id'])) ? $_GET['cat_id'] : '');

$filemanager = new FileManager(FileManager::FM_FILETYPE_IMG);

if (isset($_POST['save']) && Admin::getInstance()->permissions('products', CC_PERM_EDIT)) {
	// Save Product
	$suppress  = false;
	// updated acts like a switch so that we know if the product has been changed or not.
	$updated = false;
	$inserted = false;

	// Moved below so suppress/updated/inserted can be affected
	foreach ($GLOBALS['hooks']->load('admin.product.save.pre_process') as $hook) include $hook;

	$record = $_POST;
	$record['description'] = $GLOBALS['RAW']['POST']['description'];
	unset($record['categories'], $record['group'], $record['image']);

	if (isset($record['product_code_auto'])) {
		unset($record['product_code']);
		// Generate a new product code automatically (a-la v4)
		$record['product_code'] = generate_product_code($_POST['name']);
		unset($record['product_code_auto']);
	}

	foreach ($record as $key => $value) {
		if (!in_array($key, array('name', 'seo_meta_title', 'seo_meta_description', 'seo_meta_keywords'))) continue;
		$record[$key] = html_entity_decode($value);
	}

	//Need to remove these in some cases to stop SQL errors
	$records = array('product_id', 'manufacturer', 'product_weight', 'stock_level', 'stock_warning');
	foreach ($records as $r) {
		if (empty($record[$r]) && $record[$r]!=='0' && $record[$r]!=='0.0') {
			unset($record[$r]);
		}
	}

	if (!empty($_POST['product_id']) && is_numeric($_POST['product_id'])) {
		$old_product_data = $GLOBALS['db']->select('CubeCart_inventory', array('name', 'digital'), array('product_id' => $_POST['product_id']), false, false, false, false);

		$product_id = $_POST['product_id'];
		// Update product
		if (isset($_POST['download']) || !empty($_POST['digital_path'])) {
			if (!empty($record['digital_path'])) {
				$record['digital'] = 1;
			} else {
				$record['digital'] = 0;
				foreach ($_POST['download'] as $key => $enabled) {
					if ($enabled) {
						$record['digital'] = $key;
						break;
					}
				}
				if (!$record['digital'] && isset($_POST['download'][$old_product_data[0]['digital']]) && !$_POST['download'][$old_product_data[0]['digital']]) {
					$record['digital'] = 0;
				} elseif (!$record['digital']) {
					$record['digital'] = $old_product_data[0]['digital'];
				}

			}
			unset($_POST['download']);
		} else {
			$record['digital'] = 0; // no path nor list of files
		}
		$record['updated'] = date('Y-m-d H:i:s', time());
		if ($GLOBALS['db']->update('CubeCart_inventory', $record, array('product_id' => $_POST['product_id']), true, array('stock_level'))) {
			$product_id = $_POST['product_id'];
			$updated = true;
		}

	} else {
		// Add product
		$date_added = date('Y-m-d H:i:s', time());
		$record['date_added'] = $date_added;
		$record['updated'] = $date_added;
		if ($GLOBALS['db']->insert('CubeCart_inventory', $record)) {
			$product_id = $GLOBALS['db']->insertid();
			$inserted = true;
		}
	}

	unset($record);
	$product_id = (isset($product_id) && !empty($product_id)) ? $product_id : (int)$_POST['product_id']; // do we need this?


	// Option Sets - Assign
	if (isset($_POST['set_assign']) && !empty($_POST['set_assign'])) {
		$set_id  = (int)$_POST['set_assign'];
		$set_search = array('product_id' => $product_id, 'set_id' => $set_id);
		if (!$GLOBALS['db']->select('CubeCart_options_set_product', array('set_product_id'), $set_search)) {
			if ($GLOBALS['db']->insert('CubeCart_options_set_product', $set_search)) {
				// Upgrade existing products, if they are referenced in the new set, but not assigned
				if (($members = $GLOBALS['db']->select('CubeCart_options_set_member', false, array('set_id' => $set_id))) !== false) {
					foreach ($members as $member) {
						$record = array('product' => $product_id, 'option_id' => $member['option_id'], 'value_id' => $member['value_id'], 'set_member_id' => 0);
						$GLOBALS['db']->update('CubeCart_option_assign', array('set_member_id' => $member['set_member_id']), $record);
						unset($record);
					}
					$updated = true;
					$option_update = true;
				}
			}
		}
	}

	// Option Sets - Remove Set, and restore customized options
	if (isset($_POST['set_remove']) && is_array($_POST['set_remove']) && Admin::getInstance()->permissions('products', CC_PERM_DELETE)) {
		foreach ($_POST['set_remove'] as $set_product_id) {
			if (($set_products = $GLOBALS['db']->select('CubeCart_options_set_product', array('set_id'), array('set_product_id' => (int)$set_product_id, 'product_id' => $product_id))) !== false) {
				if (($members = $GLOBALS['db']->select('CubeCart_options_set_member', array('set_member_id'), array('set_id' => (int)$set_products[0]['set_id']))) !== false) {
					foreach ($members as $member) {
						$member_list[] = (int)$member['set_member_id'];
					}
					$GLOBALS['db']->update('CubeCart_option_assign', array('set_member_id' => 0), array('set_member_id' => $member_list, 'product' => $product_id));
					unset($member_list);
				}
				$GLOBALS['db']->delete('CubeCart_options_set_product', array('set_product_id' => (int)$set_product_id));
				$updated = true;
				$option_update = true;
			}
		}
	}
	// Delete an option
	if (isset($_POST['option_remove']) && is_array($_POST['option_remove']) && !empty($_POST['option_remove']) && Admin::getInstance()->permissions('products', CC_PERM_DELETE)) {
		foreach ($_POST['option_remove'] as $assign_id) {
			$GLOBALS['db']->delete('CubeCart_option_assign', array('assign_id' => (int)$assign_id, 'product' => $product_id));
			$updated = true;
		}
	}

	## Create option data from option set data
	if (isset($_POST['option_create']) && is_array($_POST['option_create'])) {
		foreach ($_POST['option_create'] as $set_member_id => $new_option) {
			foreach ($new_option as $key => $value) {
				if ($key == 'set_enabled' && (int)$value == 1) {
					continue;
				}
				if ($key != 'set_enabled' && $value == 0) {
					continue;
				}
				$record[$key] = $value;
			}
			if (isset($record) && $set_member = $GLOBALS['db']->select('CubeCart_options_set_member', false, array('set_member_id' => (int)$set_member_id))) {
				$record['product']  = $product_id;
				$record['option_id'] = $set_member[0]['option_id'];
				$record['value_id']  = $set_member[0]['value_id'];

				$record['set_member_id']= (int)$set_member_id;
				if ($GLOBALS['db']->insert('CubeCart_option_assign', $record)) {
					$updated = true;
				}
			}
			unset($record);
			$updated = true;
		}
	}

	// Update existing options - Inline editor
	if (isset($_POST['option_update']) && is_array($_POST['option_update'])) {
		foreach ($_POST['option_update'] as $assign_id => $values) {
			if (!isset($values['option_negative'])) {
				$values['option_negative'] = 0;
			}
			$GLOBALS['db']->update('CubeCart_option_assign', $values, array('assign_id' => $assign_id));
		}
		unset($values);
		$updated = true;
	}

	// Add New Option
	if (isset($_POST['option_add']) && is_array($_POST['option_add']) && !empty($_POST['option_add'])) {
		foreach ($_POST['option_add']['value'] as $offset => $value) {
			$record = array(
				'product'   => $product_id,
				'option_negative' => (isset($_POST['option_add']['negative'][$offset])) ? $_POST['option_add']['negative'][$offset] : '0',
				'option_price'  => $_POST['option_add']['price'][$offset],
				'option_weight'  => $_POST['option_add']['weight'][$offset],
			);
			if ($value > 0) {
				// get the option id
				if (($group = $GLOBALS['db']->select('CubeCart_option_value', array('option_id', 'value_id'), array('value_id' => abs($value)))) !== false) {
					$record['option_id'] = $group[0]['option_id'];
					$record['value_id']  = $group[0]['value_id'];
				} else {
					continue;
				}
			} else {
				$record['option_id'] = abs($value);
				$record['value_id']  = 0;
			}
			// Already in set?
			$query = sprintf("SELECT set_member_id FROM `%1\$sCubeCart_options_set_product` AS P, `%1\$sCubeCart_options_set_member` AS M WHERE P.product_id = %2\$s AND P.set_id = M.set_id AND option_id = %3\$s AND value_id = %4\$s", $GLOBALS['config']->get('config', 'dbprefix'), $product_id, $record['option_id'], $record['value_id']);

			if (!$GLOBALS['db']->query($query)) {
				if (!$GLOBALS['db']->select('CubeCart_option_assign', array('assign_id'), array('product' => $product_id, 'option_id' => $record['option_id'], 'value_id' => $record['value_id']))) {
					if ($GLOBALS['db']->insert('CubeCart_option_assign', $record)) {
						$updated = true;
					}
				}
			}
		}
	}

	if (is_array($_POST['option_matrix'])) {
		foreach ($_POST['option_matrix'] as $options_identifier => $data) {
			$data['product_id'] = $product_id;
			if ($GLOBALS['db']->select('CubeCart_option_matrix', array('matrix_id'), array('product_id' => $product_id, 'options_identifier' => $options_identifier))) {
				$GLOBALS['db']->update('CubeCart_option_matrix', $data, array('options_identifier' => $options_identifier, 'product_id' => $product_id));
			} else {
				$data['options_identifier'] = $options_identifier;
				$GLOBALS['db']->insert('CubeCart_option_matrix', $data);
			}
		}
	}

	#############################################
	// Price by Quantity
	// Update
	if (isset($_POST['discount']) && is_array($_POST['discount'])) {
		foreach ($_POST['discount'] as $discount_id => $discount_data) {
			if ($GLOBALS['db']->update('CubeCart_pricing_quantity', $discount_data, array('discount_id' => (int)$discount_id, 'product_id' => $product_id))) {
				$updated = true;
			}
		}
	}
	// Add
	if (isset($_POST['discount_add']) && is_array($_POST['discount_add'])) {
		foreach ($_POST['discount_add'] as $group_id => $discounts) {
			foreach ($discounts as $discount) {
				$record = array(
					'product_id' => $product_id,
					'group_id'  => (int)$group_id,
					'quantity'  => (int)$discount['quantity'],
					'price'   => (float)$discount['price']
				);
				if (!$GLOBALS['db']->select('CubeCart_pricing_quantity', array('discount_id'), array('product_id' => $product_id, 'group_id' => (int)$group_id, 'quantity' => (int)$discount['quantity']))) {
					if ($GLOBALS['db']->insert('CubeCart_pricing_quantity', $record)) {
						$updated = true;
					}
				}
			}
		}
	}
	// Remove
	if (isset($_POST['discount_delete']) && is_array($_POST['discount_delete']) && Admin::getInstance()->permissions('products', CC_PERM_DELETE)) {
		foreach ($_POST['discount_delete'] as $discount_id) {
			if ($GLOBALS['db']->delete('CubeCart_pricing_quantity', array('discount_id' => (int)$discount_id, 'product_id' => $product_id))) {
				$updated = true;
			}
		}
	}

	#############################################
	// Pricing by group
	if (isset($_POST['group']) && is_array($_POST['group'])) {
		if (($before = $GLOBALS['db']->select('CubeCart_pricing_group', array('group_id', 'product_id', 'price', 'sale_price', 'tax_type', 'tax_inclusive'), array('product_id' => (int)$product_id))) !== false) {
			$GLOBALS['db']->delete('CubeCart_pricing_group', array('product_id' => (int)$product_id));
			$hash_before = md5(serialize($before));
		} else {
			$hash_before = null;
		}
		$record = array(); //Fixes bug 2457
		foreach ($_POST['group'] as $group_id => $group) {
			foreach ($group as $field => $value) {
				$record[$field] = $value;
			}
			$where = array('group_id' => (int)$group_id, 'product_id' => (int)$product_id);
			if ($GLOBALS['db']->select('CubeCart_pricing_group', array('price_id'), $where)) {
				if (empty($group['price'])) {
					$GLOBALS['db']->delete('CubeCart_pricing_group', $where);
				} else {
					$GLOBALS['db']->update('CubeCart_pricing_group', $record, $where);
				}
			} else {
				if (empty($group['price'])) continue;
				$GLOBALS['db']->insert('CubeCart_pricing_group', array_merge($where, $record));
			}
		}
		if (($after = $GLOBALS['db']->select('CubeCart_pricing_group', array('group_id', 'product_id', 'price', 'sale_price', 'tax_type', 'tax_inclusive'), array('product_id' => (int)$product_id))) !== false) {
			$hash_after = md5(serialize($after));
		} else {
			$hash_after = null;
		}
		if ($hash_before !== $hash_after) {
			$updated = true;
		}
	}

	#############################################
	// Filemanager - Images
	if (($uploaded = $filemanager->upload()) !== false) {
		foreach ($uploaded as $file_id) {
			$_POST['image'][(int)$file_id] = true;
		}
	}

	if (isset($_POST['image']) && is_array($_POST['image'])) {
		// md5 compare of before / after so we know if changes have been made or not
		if (($before = $GLOBALS['db']->select('CubeCart_image_index', array('product_id', 'file_id', 'main_img'), array('product_id' => (int)$product_id))) !== false) {
			$hash_before = md5(serialize($before));
			foreach ($before as $old_img) {
				$old_images[] = $old_img['file_id'];
				if ($old_img['main_img'] == 1) {
					$old_default = $old_img['file_id'];
				}
			}
		}

		foreach ($_POST['image'] as $image_id => $status) {
			if ($status == 0) {
				$removed_images[] = $image_id;
				continue;
			}

			if ($status == 2) {
				$default = $image_id;
			}

			$img_add[] = $image_id;
		}

		foreach ($old_images as $image_id) {
			if (!in_array($image_id, $removed_images)) {
				$img_add[] = $image_id;
				if (isset($old_default) && $image_id == $old_default && !isset($default)) {
					$default = $old_default;
				}
			}
		}

		// If no default image was chose pick last one and let staff member know!
		if (!$default && sizeof($img_add) > 0) {
			$default = (int)$img_add[0];
			// Display warning message if more than one image was chosen
			if (sizeof($img_add) > 1) {
				$GLOBALS['main']->setACPWarning($lang['catalogue']['error_image_defaulted']);
			}
		}

		$GLOBALS['db']->delete('CubeCart_image_index', array('product_id' => (int)$product_id));

		if (isset($img_add) && is_array($img_add)) {
			foreach ($img_add as $image_id) {
				if (($image = $GLOBALS['db']->select('CubeCart_filemanager', false, array('file_id' => (int)$image_id))) !== false) {
					$record = array(
						'file_id'  => (int)$image_id,
						'product_id' => (int)$product_id,
						'main_img'  => ($default == (int)$image_id) ? '1' : '0',
					);
					$GLOBALS['db']->insert('CubeCart_image_index', $record);
				}
			}
		}

		// md5 compare of before / after so we know if changes have been made or not
		if (($after = $GLOBALS['db']->select('CubeCart_image_index', array('product_id', 'file_id', 'main_img'), array('product_id' => (int)$product_id))) !== false) {
			$hash_after = md5(serialize($after));
		}
		if (isset($hash_before, $hash_after) && $hash_before !== $hash_after) $updated = true;
	}

	// Reviews
	if (isset($_POST['review']) && is_array($_POST['review'])) {
		foreach ($_POST['review'] as $review_id => $status) {
			$GLOBALS['db']->update('CubeCart_reviews', array('approved' => (int)$status), array('id' => (int)$review_id, 'product_id' => (int)$product_id));
		}
	}

	// Categories
	if (isset($_POST['categories']) && is_array($_POST['categories'])) {
		// md5 compare of before / after so we know if changes have been made or not
		if (($before = $GLOBALS['db']->select('CubeCart_category_index', array('cat_id', 'product_id', 'primary'), array('product_id' => (int)$product_id))) !== false) {
			$GLOBALS['db']->delete('CubeCart_category_index', array('product_id' => (int)$product_id));
			$hash_before = md5(serialize($before));
		}

		// If they haven't chosen one we can choose the first one which is actually most likely to be top level
		if (empty($_POST['primary_cat'])) {
			$cat_post_keys  = array_keys($_POST['categories']);
			$primary_cat  = $_POST['categories'][$cat_post_keys[0]];
		} else {
			$primary_cat  = $_POST['primary_cat'];
		}
		$category_assigned = false;
		foreach ($_POST['categories'] as $value) {
			$cat_data['product_id'] = (int)$product_id;
			$cat_data['cat_id']  = (int)$value;
			$cat_data['primary'] = ($value==$primary_cat) ? 1 : 0;
			if ($GLOBALS['db']->insert('CubeCart_category_index', $cat_data)) {
				$category_assigned = true;
			}
		}

		$GLOBALS['db']->update('CubeCart_inventory', array('cat_id' => $primary_cat), array('product_id' => $product_id));

		// md5 compare of before / after so we know if changes have been made or not
		if (($after = $GLOBALS['db']->select('CubeCart_category_index', array('cat_id', 'product_id', 'primary'), array('product_id' => (int)$product_id))) !== false) {
			$hash_after = md5(serialize($after));
		}
		if (isset($hash_before, $hash_after) && $hash_before !== $hash_after) $updated = true;
	}
	if (!$category_assigned) {
		$GLOBALS['main']->setACPWarning($lang['catalogue']['no_categories_specified']);
	}

	// SEO
	if (substr($_POST['seo_path'], 0, 1) == '/' || substr($_POST['seo_path'], 0, 1) == '\\') {
		$_POST['seo_path'] = substr($_POST['seo_path'], 1);
	}
	if ($GLOBALS['seo']->setdbPath('prod', $product_id, $_POST['seo_path'])) {
		$updated = true;
	}

	if (empty($_POST['primary_cat']) && count($_POST['categories'])>1) {
		$GLOBALS['main']->setACPWarning($lang['catalogue']['title_category_defaulted']);
		$rem_array = false;
	} else if ($inserted) {
			$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_product_create']);
			$_POST['previous-tab'] = ($_POST['submit_cont']) ? $_POST['previous-tab'] : null;
			$rem_array = array('action');
	} else if ($updated) {
		$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_product_update']);
		if (!isset($option_update)) {
			//$_POST['previous-tab'] = null;
			$rem_array = array('action', 'product_id');
		}
	} else {
		$GLOBALS['main']->setACPWarning($lang['catalogue']['error_product_update']);
		$rem_array = false;
	}

	foreach ($GLOBALS['hooks']->load('admin.product.save.post_process') as $hook) include $hook;

	
	if (isset($_POST['submit_cont'])) {
		httpredir(currentPage(null, array('action' => 'edit', 'product_id' => (int)$product_id)));
	} else {
		httpredir(currentPage($rem_array));
	}
}

if (isset($_GET['delete_review']) && is_numeric($_GET['delete_review']) && Admin::getInstance()->permissions('products', CC_PERM_EDIT)) {
	$GLOBALS['db']->delete('CubeCart_reviews', array('id' => (int)$_GET['delete_review'], 'product_id' => (int)$_GET['product_id']));
	
	httpredir(currentPage(array('delete_review')), 'reviews');
}

if (isset($_POST['translate']) && isset($_POST['product_id']) && is_numeric($_POST['product_id']) && Admin::getInstance()->permissions('products', CC_PERM_EDIT)) {
	
	$_POST['translate']['description'] = $GLOBALS['RAW']['POST']['translate']['description'];
	
	// Insert/Update translation
	if (!empty($_POST['translation_id']) && is_numeric($_POST['translation_id'])) {
		if ($GLOBALS['db']->update('CubeCart_inventory_language', $_POST['translate'], array('translation_id' => (int)$_POST['translation_id'], 'product_id' => (int)$_POST['product_id']))) {
			$GLOBALS['main']->setACPNotify($lang['translate']['notify_translation_update']);
			$rem_array = array('translation_id');
			$add_array = array('action' => 'edit');
		} else {
			$GLOBALS['main']->setACPWarning($lang['translate']['error_translation_update']);
			$rem_array = false;
			$add_array = false;
		}
	} else {
		$_POST['translate']['product_id'] = $_POST['product_id'];
		if ($GLOBALS['db']->insert('CubeCart_inventory_language', $_POST['translate'])) {
			$GLOBALS['main']->setACPNotify($lang['translate']['notify_translation_create']);
			$rem_array = array('translation_id');
			$add_array = array('action' => 'edit');
		} else {
			$GLOBALS['main']->setACPWarning($lang['translate']['error_translation_create']);
			$rem_array = false;
			$add_array = false;
		}
	}
	
	httpredir(currentPage($rem_array, $add_array), 'translate');
}

if (((isset($_GET['delete']) && !empty($_GET['delete'])) || (isset($_POST['delete']) && is_array($_POST['delete']))) && Admin::getInstance()->permissions('products', CC_PERM_DELETE)) {
	// Delete Product
	foreach ($GLOBALS['hooks']->load('admin.product.delete') as $hook) include $hook;

	if (is_array($_POST['delete'])) {
		$delete_array = $_POST['delete'];
	} else {
		$delete_array = array(0 => $_GET['delete']);
	}
	$deleted = false;
	foreach ($delete_array as $delete_id) {
		if ($GLOBALS['db']->delete('CubeCart_inventory', array('product_id' => $delete_id))) {
			// Delete category index
			$GLOBALS['db']->delete('CubeCart_category_index', array('product_id' => $delete_id));
			// Delete product options
			$GLOBALS['db']->delete('CubeCart_option_assign', array('product' => $delete_id));
			// Delete option matrix index
			$GLOBALS['db']->delete('CubeCart_option_matrix', array('product_id' => $delete_id));
			// Delete product reviews
			$GLOBALS['db']->delete('CubeCart_reviews', array('product_id' => $delete_id));
			// Delete image index
			$GLOBALS['db']->delete('CubeCart_image_index', array('product_id' => $delete_id));
			// Delete pricing group index
			$GLOBALS['db']->delete('CubeCart_pricing_group', array('product_id' => $delete_id));
			// Delete pricing quantity index
			$GLOBALS['db']->delete('CubeCart_pricing_quantity', array('product_id' => $delete_id));
			// Delete language index
			$GLOBALS['db']->delete('CubeCart_inventory_language', array('product_id' => $delete_id));
			// Delete option set assign
			$GLOBALS['db']->delete('CubeCart_options_set_product', array('product_id' => $delete_id));
			// Delete SEO value
			$GLOBALS['seo']->delete('prod', $delete_id);
			$deleted = true;
		}
	}

	if (!$deleted) {
		$GLOBALS['main']->setACPWarning($lang['catalogue']['error_product_delete']);
	} else {
		$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_product_delete']);
	}

	
	httpredir(currentPage(array('delete')));
}

if (isset($_POST['status']) && is_array($_POST['status']) && Admin::getInstance()->permissions('products', CC_PERM_EDIT)) {
	// Update Status
	foreach ($_POST['status'] as $product_id => $status) {
		$GLOBALS['db']->update('CubeCart_inventory', array('status' => $status), array('product_id' => $product_id));
	}
	
	httpredir(currentPage());
}

#: Product Clone :#
if (isset($_GET['action']) && strtolower($_GET['action'])=='clone' && isset($_GET['product_id']) && (int)$_GET['product_id']>0 && Admin::getInstance()->permissions('products', CC_PERM_EDIT)) {

	if ($GLOBALS['config']->get('config', 'product_clone')!=1)
		httpredir(sprintf('%s?_g=settings#Extra', $glob['adminFile']));

	$product_id_parent = (int)$_GET['product_id'];

	if ($original_product_data = $GLOBALS['db']->select('CubeCart_inventory', false, array('product_id' => $product_id_parent), false, false, false, false)) {

		$record = $original_product_data[0];

		// Add cloned product
		$date_added = date('Y-m-d H:i:s', time());
		$record['date_added'] = $date_added;
		$record['updated'] = $date_added;

		if ($GLOBALS['config']->get('config', 'product_clone_code') == 1)
			$record['product_code'] = generate_product_code($record['name']);

		unset($record['product_id'], $record['popularity']);

		if ($GLOBALS['db']->insert('CubeCart_inventory', $record)) {
			$product_id = $GLOBALS['db']->insertid();
			$product_id = (int)$product_id;

		}

		if ($product_id && $product_id_parent) {

			// Images
			if ($GLOBALS['config']->get('config', 'product_clone_images') && ($image_i = $GLOBALS['db']->select('CubeCart_image_index', array('file_id', 'main_img'), array('product_id' => $product_id_parent))) !== false) {

				foreach ($image_i as $row_no => $image_index) {
					$image_index['product_id'] = $product_id;
					$GLOBALS['db']->insert('CubeCart_image_index', $image_index);
				}
			}
			// Translations
			if ($GLOBALS['config']->get('config', 'product_clone_translations') && ($translations_i = $GLOBALS['db']->select('CubeCart_inventory_language', array('language', 'name', 'description', 'seo_meta_title', 'seo_meta_description', 'seo_meta_keywords', 'seo_custom_url'), array('product_id' => $product_id_parent))) !== false) {

				foreach ($translations_i as $row_no => $translation) {
					$translation['product_id'] = $product_id;
					$GLOBALS['db']->insert('CubeCart_inventory_language', $translation);
				}
			}
			// Categories
			if (($cat_i = $GLOBALS['db']->select('CubeCart_category_index', array('cat_id', 'primary'), array('product_id' => $product_id_parent))) !== false) {

				foreach ($cat_i as $row_no => $cat_index) {
					$cat_index['product_id'] = $product_id;

					if ($GLOBALS['config']->get('config', 'product_clone_acats') || $cat_index['primary'])
						$GLOBALS['db']->insert('CubeCart_category_index', $cat_index);
				}
			}
			// Pricing quantity
			if (($pricing_d = $GLOBALS['db']->select('CubeCart_pricing_quantity', array('group_id', 'quantity', 'price'), array('product_id' => $product_id_parent))) !== false) {

				foreach ($pricing_d as $row_no => $pricing_discount) {
					$pricing_discount['product_id'] = $product_id;
					$GLOBALS['db']->insert('CubeCart_pricing_quantity', $pricing_discount);
				}
			}
			// pricing group
			if (($pricing_g = $GLOBALS['db']->select('CubeCart_pricing_group', array('group_id', 'price', 'sale_price', 'tax_type', 'tax_inclusive'), array('product_id' => $product_id_parent))) !== false) {

				foreach ($pricing_g as $row_no => $pricing_group) {
					$pricing_group['product_id'] = $product_id;
					$GLOBALS['db']->insert('CubeCart_pricing_group', $pricing_group);
				}
			}
			// Options
			if ($GLOBALS['config']->get('config', 'product_clone_options')) {

				if (($option_a = $GLOBALS['db']->select('CubeCart_option_assign', false, array('product' => $product_id_parent))) !== false) {

					foreach ($option_a as $row_no => $option_assign) {

						unset($option_assign['assign_id']);

						$option_assign['product'] = $product_id;
						$GLOBALS['db']->insert('CubeCart_option_assign', $option_assign);
					}
				}

				if (($option_s = $GLOBALS['db']->select('CubeCart_options_set_product', array('set_id'), array('product_id' => $product_id_parent))) !== false) {

					foreach ($option_s as $row_no => $option_set) {
						$option_set['product_id'] = $product_id;
						$GLOBALS['db']->insert('CubeCart_options_set_product', $option_set);
					}
				}

				// Matrix
				if ($GLOBALS['config']->get('config', 'product_clone_options_matrix')) {

					if (($matrix_a = $GLOBALS['db']->select('CubeCart_option_matrix', false, array('product_id' => $product_id_parent))) !== false) {

						foreach ($matrix_a as $row_no => $matrix_assign) {

							unset($matrix_assign['matrix_id']);

							$matrix_assign['product_id'] = $product_id;
							$GLOBALS['db']->insert('CubeCart_option_matrix', $matrix_assign);
						}
					}
				}

			}

			// SEO
			if (($seo_path = $GLOBALS['db']->select('CubeCart_seo_urls', array('path'), array('item_id' => $product_id_parent, 'type' => 'prod'))) !== false) {

				$GLOBALS['db']->insert('CubeCart_seo_urls', array('type' => 'prod', 'item_id' => $product_id, 'path' => sprintf('%s-p%s', $seo_path[0]['path'], $product_id)));

			} else {

				$GLOBALS['seo']->setdbPath('prod', $product_id, '');
			}

			// Custom clone
			foreach ($GLOBALS['hooks']->load('admin.product.clone') as $hook) include $hook;

			$GLOBALS['session']->set('cloned', 1, 'extra');
			// Redirect to cloned product edit page
			if ($GLOBALS['config']->get('config', 'product_clone_redirect'))
				httpredir(currentPage(null, array('action' => 'edit', 'product_id' => $product_id)));
		}

	}

	httpredir(currentPage(array('action', 'product_id')));

} else if ($GLOBALS['session']->has('cloned', 'extra')) {

		$GLOBALS['session']->delete('cloned', 'extra');
		$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_product_create']);
	}
#########################################################

$page_title = (isset($_GET['action']) && strtolower($_GET['action']) == 'edit') ? $lang['catalogue']['title_product_update'] : $lang['catalogue']['title_product_create'];

foreach ($GLOBALS['hooks']->load('admin.product.pre_display') as $hook) include $hook;

$GLOBALS['smarty']->assign('ADD_EDIT_PRODUCT', $page_title);

if (isset($_GET['action'])) {
	// Display product info
	$GLOBALS['main']->addTabControl($lang['common']['general'], 'general');
	$GLOBALS['main']->addTabControl($lang['common']['description'], 'description');

	if (strtolower($_GET['action']) == 'delete' && Admin::getInstance()->permissions('products', CC_PERM_DELETE)) {
		if (isset($_GET['translation_id']) && is_numeric($_GET['translation_id'])) {
			$GLOBALS['db']->delete('CubeCart_inventory_language', array('translation_id' => (int)$_GET['translation_id']));
			
			httpredir(currentPage(array('translation_id'), array('action' => 'edit')), 'translate');
		}
	} else if (strtolower($_GET['action']) == 'translate' && isset($_GET['product_id'])) {

			// Check to see if translation space is available
			if (!isset($_GET['translation_id']) && $GLOBALS['language']->fullyTranslated('product', (int)$_GET['product_id'])) {
				$GLOBALS['main']->setACPWarning($lang['common']['all_translated']);
				httpredir('?_g=products');
			}

			if (($product = $GLOBALS['db']->select('CubeCart_inventory', array('name'), array('product_id' => (int)$_GET['product_id']))) !== false) {
				$GLOBALS['gui']->addBreadcrumb($product[0]['name'], currentPage(array('translate_id'), array('action' => 'edit')));
			}
			$GLOBALS['gui']->addBreadcrumb($lang['translate']['title_translate'], currentPage());

			if (isset($_GET['translation_id'])) {
				$translation = $GLOBALS['db']->select('CubeCart_inventory_language', false, array('translation_id' => (int)$_GET['translation_id'], 'product_id' => (int)$_GET['product_id']), array('language' => 'ASC'));
				if ($translation) {
					$GLOBALS['smarty']->assign('TRANS', $translation[0]);
				} else {
					httpredir(currentPage(array('translation_id'), array('action' => 'edit')));
				}
			} else {
				$translation[0] = array('language' => '');
				$GLOBALS['smarty']->assign('TRANS', array('product_id' => (int)$_GET['product_id']));
			}
			if (($languages = $GLOBALS['language']->listLanguages()) !== false) {
				foreach ($languages as $option) {
					if ($option['code'] == $GLOBALS['config']->get('config', 'default_language')) continue;
					$option['selected'] = ($option['code'] == $translation[0]['language']) ? ' selected="selected"' : '';
					$smarty_data['list_langs'][] = $option;
				}
				$GLOBALS['smarty']->assign('LANGUAGES', $smarty_data['list_langs']);
			}
			$GLOBALS['smarty']->assign('DISPLAY_TRANSLATE_FORM', true);
		}

		// Add content tabs
		$GLOBALS['main']->addTabControl($lang['catalogue']['title_pricing'], 'pricing');
		$GLOBALS['main']->addTabControl($lang['settings']['title_category'], 'category');
		$GLOBALS['main']->addTabControl($lang['catalogue']['title_options'], 'Options');
		$GLOBALS['main']->addTabControl($lang['settings']['title_images'], 'image');
		$GLOBALS['main']->addTabControl($lang['catalogue']['title_digital'], 'digital');


		$google_cats = false;
		$store_country = $GLOBALS['config']->get('config', 'store_country');
		$taxonomy_lang = ($store_country==826) ? 'en-GB' : 'en-US';

		$request = new Request('www.google.com', '/basepages/producttype/taxonomy.'.$taxonomy_lang.'.txt');
		$request->setMethod('get');
		$request->skiplog(true);
		$request->cache(true);
		$request->setData(array('null'=>true));
		if ($response = $request->send()) {
			$google_cats = explode("\n", $response);
		if($response = $request->send()) {
			$google_cats = explode("\n",$response);
		}
		
		if(strstr($google_cats[0], 'Google_Product_Taxonomy_Version')) { unset($google_cats[0]); }

		$GLOBALS['smarty']->assign("GOOGLE_CATS", $google_cats);

		$GLOBALS['main']->addTabControl($lang['settings']['tab_seo'], 'seo');

		// Generate list of groups and values
		if (($groups = $GLOBALS['db']->select('CubeCart_option_group', false, false, array('priority'=>'ASC'))) !== false) {
			foreach ($groups as $group) {
				$group_list[$group['option_id']] = $group;
			}
			if (($values = $GLOBALS['db']->select('CubeCart_option_value', false, false, array('priority'=>'ASC'))) !== false) {
				foreach ($values as $value) {
					$value_list[$value['option_id']][$value['value_id']] = $value;
				}
			}
		}

		##
		if (($tax_types = $GLOBALS['db']->select('CubeCart_tax_class')) !== false) {
			foreach ($tax_types as $tax_type) {
				$tax_list[$tax_type['id']] = $tax_type;
			}
		}
		$inclusive = array(0 => $lang['common']['no'], 1 => $lang['common']['yes']);

		if (strtolower($_GET['action'])=='edit' && is_numeric($_GET['product_id'])) {
			$product_id = (int)$_GET['product_id'];
			if (($result = $GLOBALS['db']->select('CubeCart_inventory', false, array('product_id'=> $product_id))) !== false) {
				$GLOBALS['main']->addTabControl($lang['translate']['title_translations'], 'translate');
				$translations = $GLOBALS['db']->select('CubeCart_inventory_language', array('translation_id', 'language'), array('product_id' => $product_id));
				if ($translations) {
					foreach ($translations as $translation) {
						$translation['edit'] = currentPage(null, array('action' => 'translate', 'translation_id' => $translation['translation_id']));
						$translation['delete'] = currentPage(null, array('action' => 'delete', 'translation_id' => $translation['translation_id']));

						$info = $GLOBALS['language']->getLanguageInfo($translation['language']);
						if (!empty($info)) {
							$translation['name'] = $info['title'];
						}
						$smarty_data['list_translations'][] = $translation;
					}
				}
				if (isset($smarty_data['list_translations'])) {
					$GLOBALS['smarty']->assign('TRANSLATIONS', $smarty_data['list_translations']);
				}
				$GLOBALS['smarty']->assign('TRANSLATE', currentPage(null, array('action' => 'translate')));
				$GLOBALS['smarty']->assign('DISPLAY_TRANSLATE', true);
			}

			## Product Options (Sets)
			if (($set_products = $GLOBALS['db']->select('CubeCart_options_set_product', false, array('product_id' => $product_id))) !== false) {
				foreach ($set_products as $set_product) {
					if (($members = $GLOBALS['db']->select('CubeCart_options_set_member', false, array('set_id' => $set_product['set_id']))) !== false) {
						foreach ($members as $member) {
							if (($assigned = $GLOBALS['db']->select('CubeCart_option_assign', false, array('set_member_id' => $member['set_member_id'], 'product' => $product_id))) !== false) {
								foreach ($assigned as $assign) {
									$group = (isset($group_list[$assign['option_id']])) ? $group_list[$assign['option_id']] : array();
									$value = (isset($value_list[$assign['option_id']][$assign['value_id']])) ? $value_list[$assign['option_id']][$assign['value_id']] : array();
									$group['display'] = ($group['option_type'] == 0) ? '<strong>'.$group['option_name'].':</strong> '.$value['value_name'] : $group['option_name'];
									$option_list[$member['option_id']][$member['value_id']] = array_merge($member, $assign, $group, $value, array('show_disable' => true));
									$option_list[$assign['option_id']][$assign['value_id']]['from_assigned'] = true;
								}
							} else {
								$group = (isset($group_list[$member['option_id']])) ? $group_list[$member['option_id']] : array();
								$value = ($member['value_id'] > 0 && isset($value_list[$member['option_id']][$member['value_id']])) ? $value_list[$member['option_id']][$member['value_id']] : array();
								$group['display'] = ($group['option_type'] == 0) ? '<strong>'.$group['option_name'].':</strong> '.$value['value_name'] : $group['option_name'];
								$assign = array('set_enabled' => '1', 'option_price' => number_format(0, 2), 'option_weight' => number_format(0, 2));

								$option_list[$member['option_id']][$member['value_id']] = array_merge($member, $group, $value, $assign, array('show_disable' => true));
							}
							$option_list[$member['option_id']]['priority'] = $group['priority'];
						}
					}
				}
			}

			// Product Options (Individuals)
			if (($assigned = $GLOBALS['db']->select('CubeCart_option_assign', false, array('product' => $product_id))) !== false) {
				foreach ($assigned as $assign) {
					if (isset($option_list[$assign['option_id']][$assign['value_id']])) {
						continue;
					}
					$group = (isset($group_list[$assign['option_id']])) ? $group_list[$assign['option_id']] : array();
					$value = (isset($value_list[$assign['option_id']][$assign['value_id']])) ? $value_list[$assign['option_id']][$assign['value_id']] : array();
					$group['display'] = ($group['option_type'] == 0) ? '<strong>'.$group['option_name'].':</strong> '.$value['value_name'] : $group['option_name'];
					$option_list[$assign['option_id']][$assign['value_id']] = array_merge($assign, $group, $value, array('show_disable' => false));
					$option_list[$assign['option_id']][$assign['value_id']]['from_assigned'] = true;
					$option_list[$assign['option_id']]['priority'] = $group['priority'];
				}
			}

			// Sort Options
			if (is_array($option_list)) {
				uasort($option_list, 'cmpmc');
				foreach ($option_list as $oid => $array) {
					uasort($array, 'cmpmc');
					$option_list[$oid] = $array;

					unset($option_list[$oid]['priority']);
				}
			}

			// Display Options
			if (isset($option_list)) {
				$GLOBALS['smarty']->assign('PRODUCT_OPTIONS', $option_list);
			}

			// Breadcrumb
			$GLOBALS['gui']->addBreadcrumb($result[0]['name'], $_GET);

			// Price by Quantity
			if (($quantity_discounts = $GLOBALS['db']->select('CubeCart_pricing_quantity', false, array('product_id' => (int)$product_id, 'group_id' => '0'), array('quantity' => 'ASC'))) !== false) {
				$GLOBALS['smarty']->assign('QUANTITY_DISCOUNTS', $quantity_discounts);

			}

			// Reviews
			if (($reviews = $GLOBALS['db']->select('CubeCart_reviews', false, array('product_id' => (int)$_GET['product_id']), array('time' => 'DESC'))) !== false) {
				$GLOBALS['main']->addTabControl($lang['reviews']['title_reviews'], 'reviews');
				foreach ($reviews as $review) {
					$review['date']  = formatTime($review['time']);
					$review['delete'] = currentPage(false, array('delete_review' => $review['id']));
					$smarty_data['customer_reviews'][] = $review;
				}
				$GLOBALS['smarty']->assign('CUSTOMER_REVIEWS', $smarty_data['customer_reviews']);
			}
			## Images & files
			$file_array = array();

			if (($images = $GLOBALS['db']->misc('SELECT I.file_id, I.main_img, F.filepath, F.filename FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_image_index` AS `I` INNER JOIN `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_filemanager` as `F` ON I.file_id = F.file_id WHERE I.product_id = '.(int)$_GET['product_id'])) !== false) {

				$update_filemanager = false;
				foreach ($images as $image) {
					if ($image['file_id']>0 && !file_exists(CC_ROOT_DIR.'/images/source/'.$image['filepath'].$image['filename'])) {
						$GLOBALS['db']->delete('CubeCart_image_index', array('file_id' => $image['file_id']));
						$update_filemanager = true;
						continue;
					}
					$file_array[$image['file_id']] = $image['file_id'];
					if ($image['main_img'] == '1') {
						$default = $image['file_id'];
					}
				}
				if ($update_filemanager) {
					$filemanager->buildDatabase();
				}
			} else {
				$default = 0;
			}
			if (!empty($result[0]['digital'])) {
				if (empty($result[0]['digital_path'])) {
					$file_array[$result[0]['digital']] = $result[0]['digital'];
				}
			}
		} else {
			// Breadcrumb
			$GLOBALS['gui']->addBreadcrumb($lang['catalogue']['product_add'], $_GET);
			$result[0] = array(
				'featured'   => 1,
				'tax_inclusive'  => 0,
				'use_stock_level' => 1,
			);
			$result[0] = array_merge($result[0], $_POST);
			$file_array = array();
			$default = false;
		}
		$GLOBALS['smarty']->assign('JSON_IMAGES', json_encode($file_array));
		$GLOBALS['smarty']->assign('DEFAULT_IMAGE', (int)$default);

		// Display list of available option sets
		if (($option_sets = $GLOBALS['db']->select('CubeCart_options_set', false, false, array('set_name' => 'ASC'))) !== false) {
			foreach ($option_sets as $option_set) {
				$set_list[$option_set['set_id']] = $option_set;
				$smarty_data['list_option_sets'][] = $option_set;
			}
			$GLOBALS['smarty']->assign('OPTION_SETS', $smarty_data['list_option_sets']);
			if (isset($product_id) && $product_sets = $GLOBALS['db']->select('CubeCart_options_set_product', false, array('product_id' => $product_id))) {
				foreach ($product_sets as $product_set) {
					if (isset($set_list[$product_set['set_id']])) {
						$smarty_data['sets_enabled'][] = array_merge($set_list[$product_set['set_id']], $product_set);
					}
				}
				$GLOBALS['smarty']->assign('OPTION_SETS_ENABLED', $smarty_data['sets_enabled']);
			}
		}

		// Group Pricing - This is where things get confusing...
		if (($groups = $GLOBALS['db']->select('CubeCart_customer_group')) !== false) {
			foreach ($groups as $group) {
				// Quantity discounting
				$tax_type  = null;
				$tax_inclusive = 0;
				if (isset($product_id)) {
					if (($quantities = $GLOBALS['db']->select('CubeCart_pricing_quantity', false, array('group_id' => (int)$group['group_id'], 'product_id' => $product_id), array('quantity' => 'ASC'))) !== false) {
						$group['quantities'] = $quantities;
					}
					// Price/tax override for groups
					if (($price = $GLOBALS['db']->select('CubeCart_pricing_group', false, array('group_id' => (int)$group['group_id'], 'product_id' => $product_id))) !== false) {
						$tax_type    = $price[0]['tax_type'];
						$tax_inclusive   = (int)$price[0]['tax_inclusive'];
						$group['price']   = $price[0]['price'];
						$group['sale_price'] = $price[0]['sale_price'];
					} else {
						$group['price']   = $result[0]['price'];
						$group['sale_price'] = $result[0]['sale_price'];
					}
				}
				foreach ($tax_list as $tax_id => $details) {
					$details['selected'] = ($tax_id == $tax_type) ? 'selected="selected"' : '';
					$group['tax_types'][] = $details;
				}

				$group['tax_inclusive'] = $tax_inclusive;
				$smarty_data['customer_groups'][] = $group;
			}
			$GLOBALS['smarty']->assign('CUSTOMER_GROUPS', $smarty_data['customer_groups']);
		}

		// Get tax classes
		if (($taxes = $GLOBALS['db']->select('CubeCart_tax_class')) !== false) {
			foreach ($taxes as $tax) {
				$tax['selected'] = (isset($result[0]['tax_type']) && $tax['id'] == $result[0]['tax_type']) ? ' selected="selected"' : '';
				$smarty_data['taxes'][] = $tax;
			}
			$GLOBALS['smarty']->assign('TAXES', $smarty_data['taxes']);
		}

		$product_id = (!isset($product_id)) ? 0 : $product_id;
		// Existing Categories
		if (($category_list = $GLOBALS['db']->select('CubeCart_category_index', array('cat_id', 'primary'), array('product_id' => (int)$product_id))) !== false) {
			$cat_id_primary = false;
			foreach ($category_list as $category) {
				if ((int)$category['cat_id'] == 0) continue; // Shouldn't happen, but just in case it does...
				$cats_selected[]  = (int)$category['cat_id'];
				$cat_id_primary  = ($category['primary']) ? $category['cat_id'] : $cat_id_primary;
			}
		}

		$categoryArray = $GLOBALS['db']->select('CubeCart_category', array('cat_name', 'cat_parent_id', 'cat_id'));

		if ($categoryArray) {
			$cat_ist[] = $GLOBALS['config']->get('config', 'default_directory_symbol');
			$seo = SEO::getInstance();
			$seo->setCache(false);
			foreach ($categoryArray as $category) {
				if ($category['cat_parent_id'] == $category['cat_id']) continue;
				$cat_list[$category['cat_id']] = $seo->getDirectory((int)$category['cat_id'], false, $GLOBALS['config']->get('config', 'default_directory_symbol'), false, false);
			}
			$seo->setCache(true);
			natcasesort($cat_list);
			foreach ($cat_list as $cat_id => $cat_name) {
				if (empty($cat_name)) continue;
				$data = array(
					'id'  => $cat_id,
					'name'  => $cat_name,
					'selected' => (isset($cats_selected) && in_array($cat_id, $cats_selected)) ? ' checked="checked"' : '',
					'primary' => (isset($cat_id_primary) && (int)$cat_id == (int)$cat_id_primary) ? ' checked="checked"' : ''
				);
				$smarty_data['categories'][] = $data;
			}
			$GLOBALS['smarty']->assign('CATEGORIES', $smarty_data['categories']);
		}

		// Product Options (Additional)
		if (isset($group_list) && is_array($group_list)) {
			foreach ($group_list as $i => $group) {
				if (isset($value_list[$group['option_id']])) {
					foreach ($value_list[$group['option_id']] as $value) {
						$group['members'][] = $value;
					}
				} else {
					$group['option_id'] = (-1)*$group['option_id'];
				}
				$smarty_data['list_options_select'][$i] = $group;
			}
			$GLOBALS['smarty']->assign('OPTIONS_SELECT', $smarty_data['list_options_select']);
		}

		// Stock for product options
		$options = $GLOBALS['db']->misc('SELECT A.option_id, A.value_id ,V.value_name, G.option_name FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_option_assign` AS `A` INNER JOIN `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_option_value` AS `V` ON A.value_id = V.value_id INNER JOIN `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_option_group` AS `G` ON A.option_id = G.option_id WHERE `product` = '.$product_id.' AND `set_enabled` = 1 AND `matrix_include` = 1 ORDER BY A.option_id, A.value_id ASC');
		/*
			option_id = group name e.g. size
			value_id = value id e.g. 7
			value_name = value name e.g. large

		*/
		$unique_groups = array();
		if ($options) {
			// Work out unique groups
			$key_id = -1;

			foreach ($options as $key => $data) {
				$option[$data['value_id']] = array(
					'option_name' => $data['value_name'],
					'option_group' => $data['option_name'],
					'option_id' => $data['option_id'],
					'value_id' => $data['value_id'],
				);
				if (!isset($unique_keys[$data['option_id']])) {
					$unique_keys[$data['option_id']] = true;
					$key_id++;
				}
				$unique_groups[$key_id][$data['value_id']] = $data['value_id'];
			}
		}

		function option_matrix($unique_groups) {
			$no_groups = count($unique_groups);
			if ($no_groups <= 1) {
				return $no_groups ? array_map(create_function('$v', 'return (array($v));'), $unique_groups[0]) : $unique_groups;
			}

			$last_value = array_pop($unique_groups);
			foreach ($last_value as $value_id) {
				$appends = option_matrix($unique_groups);
				foreach ($appends as $append) {
					$output[] = is_array($append) ? array_merge($append, array($value_id)) : array($append, $value_id);
				}
			}
			return $output;
		}
		$option_matrix = option_matrix($unique_groups);
		$possible = false;

		if (is_array($option_matrix)):
			foreach ($option_matrix as $matrix_values) {
				foreach ($matrix_values as $matrix_value_id) {
					$options_values[] =  '<strong>'.$option[$matrix_value_id]['option_group'].'</strong>: '.$option[$matrix_value_id]['option_name'];
					$options_identifier[]  =  $option[$matrix_value_id]['option_id'].$option[$matrix_value_id]['value_id'];
				}
				$option_identifier_string = md5(implode('', $options_identifier));
				$smarty_data['option_matrix']['all_possible'][] = array(
					'options_identifier' => $option_identifier_string,
					'options_values' => implode(', ', $options_values)
				);
				$possible[] = $option_identifier_string;
				unset($options_identifier, $options_values);
			}
		endif;

		if (is_array($possible)) {
			$delete_query = "UPDATE `".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_option_matrix` SET `status` = 0 WHERE `product_id` = $product_id AND `options_identifier` NOT IN ('".implode("','", $possible)."')";
			$GLOBALS['db']->misc($delete_query);
		}

		// update cached name
		if (isset($smarty_data['option_matrix']['all_possible']) && is_array($smarty_data['option_matrix']['all_possible'])) {
			foreach ($smarty_data['option_matrix']['all_possible'] as $option_group) {
				$GLOBALS['db']->update('CubeCart_option_matrix', array('cached_name' => $option_group['options_values'], 'status' => 1), array('options_identifier' => $option_group['options_identifier']));
			}
		}

		// Get existing
		$smarty_data['option_matrix'] = array();
		if ($existing_matrices = $GLOBALS['db']->select('CubeCart_option_matrix', false, array('product_id'=>$product_id))) {
			foreach ($existing_matrices as $existing_matrix) {
				$smarty_data['option_matrix']['existing'][$existing_matrix['options_identifier']] = $existing_matrix;
			}
		}

		$GLOBALS['smarty']->assign('OPTIONS_MATRIX', $smarty_data['option_matrix']);

		// List Manufacturers
		if (($manufacturers = $GLOBALS['db']->select('CubeCart_manufacturers', false, false, array('name' => 'ASC'))) !== false) {
			foreach ($manufacturers as $manufacturer) {
				$manufacturer['selected'] = ($manufacturer['id'] == $result[0]['manufacturer']) ? ' selected="SELECTED"' : '';
				$smarty_data['list_manufacturers'][] = $manufacturer;
			}
			$GLOBALS['smarty']->assign('MANUFACTURERS', $smarty_data['list_manufacturers']);
		}

		// Set status to 1 if not set
		$result[0]['status'] = !isset($result[0]['status']) ? 1 : $result[0]['status'];
		foreach ($result[0] as $key => $value) {
			if (!in_array($key, array('name', 'seo_meta_title', 'seo_meta_description', 'seo_meta_keywords'))) continue;
			$result[0][$key] = htmlentities($value, ENT_COMPAT, 'UTF-8');
		}
		$result[0]['auto_code_checked'] = (empty($result[0]['product_code'])) ? 'checked="checked"' : '';
		$result[0]['seo_path'] = isset($result[0]['product_id']) ? $GLOBALS['seo']->getdbPath('prod', $result[0]['product_id']) : '';

		$master_image = isset($_GET['product_id']) ? $GLOBALS['gui']->getProductImage((int)$_GET['product_id']) : '';
		$result[0]['master_image'] =  !empty($master_image) ? $master_image : 'images/general/px.gif';

		// Update global stock level when matrix stock level in use
		if ($GLOBALS['config']->get('config', 'update_main_stock')) {
			$options_stock = $GLOBALS['db']->select('CubeCart_option_matrix', 'SUM(stock_level) AS stock', array('product_id' => $product_id, 'status' => 1, 'use_stock' => 1));
			if ($options_stock && is_numeric($options_stock[0]['stock'])) {
				$GLOBALS['db']->update('CubeCart_inventory', array('stock_level' => (int)$options_stock[0]['stock']), array('product_id' => $product_id));
				$result[0]['stock_level'] = $options_stock[0]['stock'];
				$GLOBALS['smarty']->assign('DISPLAY_MATRIX_STOCK_NOTE', true);
			}
		}
		$GLOBALS['smarty']->assign('PRODUCT', $result[0]);

		$selectArray = array(
			'featured',
			'use_stock_level',
		);

		if (isset($select_options)) {
			foreach ($select_options as $field => $options) {
				if (!is_array($options) || empty($options)) {
					$options = array($lang['common']['no'], $lang['common']['yes']);
				}
				foreach ($options as $value => $title) {
					$selected = ($result[0][$field] == $value) ? ' selected="selected"' : '';
					$GLOBALS['smarty']->assign('OPT', array('value' => $value, 'title' => $title, 'selected' => $selected));
				}
			}
		}

		$GLOBALS['smarty']->assign('FORM_HASH', md5(implode('', $result[0])));
		$GLOBALS['smarty']->assign('DISPLAY_PRODUCT_FORM', true);
	}
} else {
	// List all products
	$GLOBALS['main']->addTabControl($lang['catalogue']['title_product_list'], 'general');
	$GLOBALS['main']->addTabControl($lang['catalogue']['product_add'], null, currentPage(null, array('action' => 'add')));
	$GLOBALS['main']->addTabControl($lang['catalogue']['title_category_assign_to'], null, currentPage(null, array('node' => 'assign')));
	$GLOBALS['main']->addTabControl($lang['catalogue']['title_option_set_assign'], null, currentPage(null, array('node' => 'optionsets')));
	$GLOBALS['main']->addTabControl($lang['search']['title_search_products'], 'sidebar');

	// Sorting
	$current_page = currentPage(array('sort'));
	if (!isset($_GET['sort']) || !is_array($_GET['sort'])) {
		$_GET['sort'] = array('updated' => 'DESC');
	}
	$thead_sort = array (
		'status'   => $GLOBALS['db']->column_sort('status', $lang['common']['status'], 'sort', $current_page, $_GET['sort']),
		'digital'   => $GLOBALS['db']->column_sort('digital', $lang['common']['type'], 'sort', $current_page, $_GET['sort']),
		'image'   => $GLOBALS['db']->column_sort('image', $lang['catalogue']['title_image'], 'sort', $current_page, $_GET['sort']),
		'name'    => $GLOBALS['db']->column_sort('name', $lang['catalogue']['product_name'], 'sort', $current_page, $_GET['sort']),
		'product_code'  => $GLOBALS['db']->column_sort('product_code', $lang['catalogue']['product_code'], 'sort', $current_page, $_GET['sort']),
		'price'   => $GLOBALS['db']->column_sort('price', $lang['common']['price'], 'sort', $current_page, $_GET['sort']),
		'stock_level'  => $lang['catalogue']['title_stock'],
		'updated'   => $GLOBALS['db']->column_sort('updated', $lang['catalogue']['title_last_updated'], 'sort', $current_page, $_GET['sort']),
		'translations'  => $lang['translate']['title_translations']
	);
	$GLOBALS['smarty']->assign('THEAD', $thead_sort);

	// Get inventory
	$page  = (isset($_GET['page'])) ? $_GET['page'] : 1;
	$per_page = 20;
	if (isset($_GET['char']) && !empty($_GET['char'])) {
		$where  =  "`name` REGEXP '^[".$_GET['char']."]'";
	} else if (isset($_GET['q']) && !empty($_GET['q'])) {
			$where = "(`name` LIKE '%".$_GET['q']."%' OR `product_code` LIKE '%".$_GET['q']."%')";
		} else {
		$where = false;
	}

	if (isset($_GET['cat_id']) && is_numeric($_GET['cat_id'])) {
		if (!$where) { $where = ''; } else { $where .= ' AND '; } // We got a category - $where is certainly not false anymore.
		if (($cat_products = $GLOBALS['db']->select('CubeCart_category_index', array('product_id'), array('cat_id' => (int)$_GET['cat_id']))) !== false) {
			$where .= '(product_id IN (';
			foreach ($cat_products as $prod) {
				$where .= $prod['product_id'].',';
			}
			$where = substr($where, 0, -1);
			$where .= ') OR cat_id = '.(int)$_GET['cat_id'].')';
		}
	}

	if (($where === false || strlen($where) > 0) && ($results = $GLOBALS['db']->select('CubeCart_inventory', false, $where, $_GET['sort'], $per_page, $page)) !== false) {
		$pagination = $GLOBALS['db']->pagination(false, $per_page, $page, 9);
		// Find fist letters to sort products by
		if (($chars = $GLOBALS['db']->query('SELECT DISTINCT UPPER(LEFT(`name`, 1)) AS `char` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_inventory`')) !== false) {
			$int = false;
			foreach ($chars as $key) {
				// is_int will not work here
				if (preg_match("/[0-9]/", $key['char'])) {
					$int   = true;
				} else {
					$char_list_array[] = $key['char'];
				}
			}
		}
		if ($int) $char_list_array = array_merge(array('0' => '#'), $char_list_array);
		$char_link = currentPage(array('char', 'page'));
		$GLOBALS['smarty']->assign('SORT_CHARS_RESET_LINK', $char_link);
		natcasesort($char_list_array);
		foreach ($char_list_array as $char) {
			$char_get_val  = ($char == '#') ? '0-9' : $char;
			$char_data['link'] = $char_link."&char=".$char_get_val;
			$char_data['char']  = $char;
			$smarty_data['sort_characters'][] = $char_data;
		}

		$GLOBALS['smarty']->assign('SORT_CHARACTERS', $smarty_data['sort_characters']);

		if (isset($_GET['q']) && !empty($_GET['q'])) {
			$GLOBALS['main']->setACPNotify(sprintf($lang['catalogue']['notify_product_search'], $_GET['q']));
		} else if (isset($_GET['char']) && !empty($_GET['char'])) {
				$GLOBALS['main']->setACPNotify(sprintf($lang['catalogue']['notify_product_search'], $_GET['char']));
			}

		$catalogue = Catalogue::getInstance();
		$seo  = SEO::getInstance();
		foreach ($results as $result) {

			if ($result['use_stock_level'] == 0 || $result['digital'] > 0 || !empty($result['digital_path'])) {
				$result['stock_level'] = "&infin;";
			}

			if ($stock_variations = $GLOBALS['db']->select('CubeCart_option_matrix', 'MAX(stock_level) AS max_stock, MIN(stock_level) AS min_stock', array('product_id' => $result['product_id'], 'use_stock' => 1, 'status' => 1), false, 1)) {
				if (is_numeric($stock_variations[0]['min_stock']) && is_numeric($stock_variations[0]['max_stock'])) {
					$result['stock_level'] =  ($stock_variations[0]['min_stock'] == $stock_variations[0]['max_stock']) ? $stock_variations[0]['max_stock'] : $stock_variations[0]['min_stock'].' - '.$stock_variations[0]['max_stock'];
				}
			}

			$result['link_preview'] = "index.php?_a=product&product_id=".$result['product_id'];
			if (!$GLOBALS['config']->get('config', 'product_clone') || $GLOBALS['config']->get('config', 'product_clone')<2) {
				$result['link_clone'] = currentPage(null, array('action' => 'clone', 'product_id' => $result['product_id']));
			}
			$result['link_edit'] = currentPage(null, array('action' => 'edit', 'product_id' => $result['product_id']));
			$result['link_delete'] = currentPage(null, array('delete' => $result['product_id']));
			$result['type_icon'] = $GLOBALS['config']->get('config', 'adminFolder')."/skins/".$GLOBALS['config']->get('config', 'admin_skin')."/images/prod_type_".(int)(bool)$result['digital'].".png";
			$result['type_alt']  = $result['digital'] ? $lang['catalogue']['product_type_digital'] : $lang['catalogue']['product_type_tangible'];
			// Get master category path
			if (($category = $GLOBALS['db']->select('CubeCart_category_index', array('cat_id'), array('primary' => 1, 'product_id' => $result['product_id']))) !== false) {
				$result['category'] = $seo->getDirectory($category[0]['cat_id'], false, $GLOBALS['config']->get('config', 'default_directory_symbol'), false, false);
			}
			// Check for master image
			if (($image = $GLOBALS['db']->select('CubeCart_image_index', 'file_id', array('product_id' => $result['product_id'], 'main_img' => 1))) !== false) {
				$result['image_path_tiny'] = $catalogue->imagePath($image[0]['file_id'], 'tiny');
				$result['image_path_large'] = $catalogue->imagePath($image[0]['file_id'], 'large');
			}
			// Check for languages
			if (($translations = $GLOBALS['db']->select('CubeCart_inventory_language', array('language', 'translation_id'), array('product_id' => $result['product_id']))) !== false) {
				foreach ($translations as $translation) {
					// Display translation icons
					$translation['link'] = currentPage(null, array('action' => 'translate', 'product_id' => $result['product_id'], 'translation_id' => $translation['translation_id']));
					$result['translations'][] = $translation;
				}
			}
			$updated_time  = $result['updated'];
			$result['updated']  = $updated_time ? $updated_time : $lang['common']['unknown'];
			$smarty_data['products'][] = $result;
		}
		$GLOBALS['smarty']->assign('PRODUCTS', $smarty_data['products']);
		$GLOBALS['smarty']->assign('PAGINATION', $pagination);
	} else {
		if (isset($_GET['q']) && !empty($_GET['q'])) {
			$GLOBALS['main']->setACPWarning(sprintf($lang['catalogue']['error_product_search'], $_GET['q']));
		} elseif (isset($_GET['char']) && !empty($_GET['char'])) {
			$GLOBALS['main']->setACPWarning(sprintf($lang['catalogue']['error_products_letter'], $_GET['char']));
		}
	}
	$GLOBALS['smarty']->assign('DISPLAY_PRODUCT_LIST', true);
}
$GLOBALS['smarty']->assign('HOOK_TAB_CONTENT', $GLOBALS['hook_tab_content']);
$page_content = $GLOBALS['smarty']->fetch('templates/products.index.php');