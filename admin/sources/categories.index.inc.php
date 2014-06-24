<?php
if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('categories', CC_PERM_READ, true);

global $lang;

###########################################
## Update/Insert a category
if (isset($_POST['cat']) && is_array($_POST['cat']) && Admin::getInstance()->permissions('categories', CC_PERM_EDIT)) {

	foreach ($GLOBALS['hooks']->load('admin.category.save.pre_process') as $hook) include $hook;

	$redirect  = true;
	$keys_remove = null;
	$keys_add  = null;

	$filemanager = new FileManager(FileManager::FM_FILETYPE_IMG);
	if (($uploaded = $filemanager->upload()) !== false) {
		foreach ($uploaded as $file_id) {
			$_POST['image'][(int)$file_id] = true;
		}
	}
	foreach ($_POST['cat'] as $key => $value) {
		if (!in_array($key, array('cat_name'))) continue;
		$_POST['cat'][$key] = html_entity_decode($value);
	}

	$_POST['cat']['hide'] = (int)$_POST['cat']['visible'] && (int)$_POST['cat']['status'] ? 0 : 1;

	$_POST['cat']['cat_desc'] = $GLOBALS['RAW']['POST']['cat']['cat_desc'];
	
	if (is_numeric($_POST['cat']['cat_id'])) {
		$cat_id = $_POST['cat']['cat_id'];
		$old_image = $GLOBALS['db']->select('CubeCart_category', array('cat_image'), array('cat_id' => $_POST['cat']['cat_id']));
		$_POST['cat']['cat_image'] = $old_image[0]['cat_image'];
		if (isset($_POST['image']) && is_array($_POST['image'])) {
			foreach ($_POST['image'] as $image_id => $enabled) {
				if ($enabled == 0) {
					if ($image_id == $old_image[0]['cat_image']) {
						$_POST['cat']['cat_image'] = '';
					}
					continue;
				}
				$_POST['cat']['cat_image'] = (int)$image_id;
				break;
			}
		}

		if ((!empty($_POST['cat']['cat_name']) && $GLOBALS['db']->update('CubeCart_category', $_POST['cat'], array('cat_id' => $_POST['cat']['cat_id']), true))) {
			if (isset($_POST['gen_seo']) && $_POST['gen_seo'] == 1) {
				$GLOBALS['seo']->delete('cat', $cat_id);
				$GLOBALS['seo']->rebuildCategoryList();
				$GLOBALS['seo']->setdbPath('cat', $cat_id, '', false, false);
			}
			$GLOBALS['main']->setACPNotify($lang['settings']['notify_category_update']);
			$keys_remove = array('action', 'cat_id');
		} else if (!isset($_POST['seo_path'])) {
				$GLOBALS['main']->setACPWarning($lang['settings']['error_category_update']);
				$redirect = false;
			}

		if (isset($_POST['seo_path']) && (!isset($_POST['gen_seo']) || $_POST['gen_seo'] != 1)) {
			if ($_POST['seo_path'] != $GLOBALS['seo']->getdbPath('cat', $cat_id)) {
				if (substr($_POST['seo_path'], 0, 1) == '/' || substr($_POST['seo_path'], 0, 1) == '\\') {
					$_POST['seo_path'] = substr($_POST['seo_path'], 1);
				}
				$GLOBALS['seo']->setdbPath('cat', $cat_id, $_POST['seo_path'], false, false);
				$GLOBALS['seo']->rebuildCategoryList();
			}
		}
	} else {
		if (!empty($_POST['cat']['cat_name']) && $GLOBALS['db']->insert('CubeCart_category', $_POST['cat'])) {
			$cat_id = $GLOBALS['db']->insertid();
			$path = empty($_POST['seo_path']) ? $_POST['cat']['cat_name'] : $_POST['seo_path'];
			$GLOBALS['seo']->setdbPath('cat', $cat_id, $path);
			$GLOBALS['main']->setACPNotify($lang['settings']['notify_category_create']);
			$keys_remove = array('action', 'cat_id');
		} else {
			$GLOBALS['main']->setACPWarning($lang['settings']['error_category_create']);
			$redirect = false;
		}
	}

	foreach ($GLOBALS['hooks']->load('admin.category.save.post_process') as $hook) include $hook;

	$GLOBALS['cache']->clear();
	if ($redirect) {
		httpredir(currentPage($keys_remove, $keys_add));
	}
}

// Delete a category
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && Admin::getInstance()->permissions('categories', CC_PERM_DELETE)) {
	foreach ($GLOBALS['hooks']->load('admin.category.delete') as $hook) include $hook;
	// Get category name for informative messages
	$category = $GLOBALS['db']->select('CubeCart_category', 'cat_name', array('cat_id' => $_GET['delete']));
	// Detect sub categories
	if (!$subcats = $GLOBALS['db']->select('CubeCart_category', array('cat_id'), array('cat_parent_id' => (int)$_GET['delete']))) {
		// Detect products
		if (!$products = $GLOBALS['db']->select('CubeCart_category_index', array('id'), array('cat_id' => (int)$_GET['delete']))) {
			if ($GLOBALS['db']->delete('CubeCart_category', array('cat_id' => (int)$_GET['delete']))) {
				$GLOBALS['seo']->delete('cat', $_GET['delete']);
				$GLOBALS['main']->setACPNotify($lang['settings']['notify_category_delete']);
			} else {
				$GLOBALS['main']->setACPWarning($lang['settings']['error_category_delete']);
			}
		} else {
			$GLOBALS['main']->setACPWarning($lang['settings']['error_category_delete_prod']);
		}
	} else {
		$GLOBALS['main']->setACPWarning($lang['settings']['error_category_delete_cats']);
	}
	$GLOBALS['cache']->clear();
	httpredir(currentPage(array('delete')));
}

if (isset($_POST['translate']) && isset($_POST['cat_id']) && is_numeric($_POST['cat_id'])) {

	foreach ($GLOBALS['hooks']->load('admin.category.translate.save.pre_process') as $hook) include $hook;

	$addarray  = false;
	$remarray  = false;
	$anchor  = false;

	// Check for existing translations and replace if doing more than once
	if (($duplicates = $GLOBALS['db']->select('CubeCart_category_language', array('translation_id'), array('language' => $_POST['translate']['language'], 'cat_id' => (int)$_POST['cat_id']))) !== false) {
		$_POST['translation_id'] = $duplicates[0]['translation_id'];
	}

	// Get original category name
	$master_cat_name = $GLOBALS['db']->select('CubeCart_category', array('cat_name'), array('cat_id' => (int)$_POST['cat_id']));

	if (!empty($_POST['translation_id']) && is_numeric($_POST['translation_id']) && Admin::getInstance()->permissions('categories', CC_PERM_EDIT)) {
		if ($GLOBALS['db']->update('CubeCart_category_language', $_POST['translate'], array('translation_id' => (int)$_POST['translation_id'], 'cat_id' => (int)$_POST['cat_id']))) {
			$GLOBALS['main']->setACPNotify($lang['translate']['notify_translation_update']);
			$remarray = array('action', 'cat_id', 'translation_id');
		} else {
			$GLOBALS['main']->setACPWarning($lang['translate']['error_translation_update']);
		}
	} else {
		$_POST['translate']['cat_id'] = $_POST['cat_id'];
		if ($GLOBALS['db']->insert('CubeCart_category_language', $_POST['translate'])) {
			$GLOBALS['main']->setACPNotify($lang['translate']['notify_translation_create']);
			$remarray = array('action', 'cat_id', 'translation_id');
		} else {
			$GLOBALS['main']->setACPWarning($lang['translate']['error_translation_create']);
		}
	}

	foreach ($GLOBALS['hooks']->load('admin.category.translate.save.post_process') as $hook) include $hook;

	$GLOBALS['cache']->clear();
	httpredir(currentPage($remarray, $addarray, $anchor));
}

###########################################
## Update stuff from the list page
$update = array();
if (isset($_POST['status']) && is_array($_POST['status'])) {
	// Update category status
	foreach ($_POST['status'] as $cat_id => $status) {
		$update[$cat_id]['status']  = $status;
	}
}
if (isset($_POST['order']) && is_array($_POST['order'])) {
	// Update category order
	foreach ($_POST['order'] as $key => $cat_id) {
		$update[$cat_id]['priority'] = $key+1;
	}
}
if (isset($_POST['visible']) && is_array($_POST['visible'])) {
	// Update category visibility only
	foreach ($_POST['visible'] as $cat_id => $visible) {
		$update[$cat_id]['hide'] = $visible && $_POST['status'][$cat_id]  ? 0 : 1;
	}
	$GLOBALS['cache']->clear();
}

if (!empty($update) && is_array($update) && Admin::getInstance()->permissions('categories', CC_PERM_EDIT)) {
	// Put changes into the database
	$updated = false;
	foreach ($update as $cat_id => $array) {
		if ($GLOBALS['db']->update('CubeCart_category', $array, array('cat_id' => $cat_id), true)) $updated = true;
	}
	if ($updated) {
		$GLOBALS['main']->setACPNotify($lang['settings']['notify_category_status']);
	} else {
		$GLOBALS['main']->setACPWarning($lang['settings']['error_category_status']);
	}
	$GLOBALS['cache']->clear();
	httpredir(currentPage());
}

###########################################

###########################################
## Build the breadcrumbs for the current category
if (isset($_GET['parent'])) {
	$cats = $GLOBALS['main']->getCategoryPath($_GET['parent']);
	if (is_array($cats)) {
		foreach ($cats as $cat) {
			$GLOBALS['gui']->addBreadcrumb($cat['cat_name'], array('_g' => $_GET['_g'], 'parent' => $cat['cat_id']));
		}
	}
}

###########################################
$filemanager = new FileManager(FileManager::FM_FILETYPE_IMG);

foreach ($GLOBALS['hooks']->load('admin.category.pre_display') as $hook) include $hook;

if (isset($_GET['action'])) {
	if (strtolower($_GET['action']) == 'delete') {
		if (isset($_GET['translation_id']) && is_numeric($_GET['translation_id'])) {
			if (Admin::getInstance()->permissions('categories', CC_PERM_DELETE) && $GLOBALS['db']->delete('CubeCart_category_language', array('translation_id' => (int)$_GET['translation_id'], 'cat_id' => (int)$_GET['cat_id']))) {
				$GLOBALS['main']->setACPNotify($lang['translate']['notify_translation_delete']);
			} else {
				$GLOBALS['main']->setACPWarning($lang['translate']['error_translation_delete']);
			}
		}
		httpredir(currentPage(array('translation_id'), array('action' =>'edit')), 'cat_translate');

	} else if (strtolower($_GET['action']) == 'translate') {

			// Check to see if translation space is available
			if (!isset($_GET['translation_id']) && $GLOBALS['language']->fullyTranslated('category', (int)$_GET['cat_id'])) {
				$GLOBALS['main']->setACPWarning($lang['common']['all_translated']);
				httpredir('?_g=categories');
			}


			if (($category = $GLOBALS['db']->select('CubeCart_category', array('cat_name'), array('cat_id' => (int)$_GET['cat_id']))) !== false) {
				$GLOBALS['gui']->addBreadcrumb($category[0]['cat_name'], currentPage(array('translate_id'), array('action' => 'edit')));
			}
			$GLOBALS['gui']->addBreadcrumb($lang['translate']['title_translate'], currentPage());
			$GLOBALS['main']->addTabControl($lang['common']['general'], 'general');
			$GLOBALS['main']->addTabControl($lang['common']['description'], 'description');
			$GLOBALS['main']->addTabControl($lang['settings']['tab_seo'], 'seo');

			if (isset($_GET['translation_id'])) {
				if (($translation = $GLOBALS['db']->select('CubeCart_category_language', false, array('translation_id' => (int)$_GET['translation_id'], 'cat_id' => (int)$_GET['cat_id']), array('language' => 'ASC'))) !== false) {
					$GLOBALS['smarty']->assign('TRANS', $translation[0]);
				} else {
					httpredir(currentPage(array('translation_id'), array('action' => 'edit')), 'translate');
				}
			} else {
				$translation[0] = array('language' => '');
				$GLOBALS['smarty']->assign('TRANS', array('cat_id' => (int)$_GET['cat_id']));
			}
			if (($languages = $GLOBALS['language']->listLanguages()) !== false) {
				foreach ($languages as $option) {
					if ($option['code'] == $GLOBALS['config']->get('config', 'default_language')) continue;
					$option['selected'] = ($option['code'] == $translation[0]['language']) ? ' selected="selected"' : '';
					$smarty_data['languages'][] = $option;
				}
			}
			$GLOBALS['smarty']->assign('LANGUAGES', $smarty_data['languages']);
			$GLOBALS['smarty']->assign('MODE_TRANSLATE', true);

		} else if (in_array(strtolower($_GET['action']), array('edit', 'add'))) {
			// Display the add/edit category page
			$GLOBALS['main']->addTabControl($lang['common']['general'], 'cat_general', null, 'G');
			$GLOBALS['main']->addTabControl($lang['common']['description'], 'cat_description', null, 'D');
			$GLOBALS['main']->addTabControl($lang['settings']['title_images'], 'cat_images', null, 'I');
			$GLOBALS['main']->addTabControl($lang['settings']['tab_seo'], 'seo');
			// Add shipping tab if shipping by category is enabled
			$ship_by_cat = $GLOBALS['config']->get('Per_Category');
			if (isset($ship_by_cat['status']) && $ship_by_cat['status']) {
				$GLOBALS['main']->addTabControl($lang['settings']['title_shipping'], 'cat_shipping', null, 'S');
				$GLOBALS['smarty']->assign('DISPLAY_SHIPPING', true);
			}
			if (isset($_GET['cat_id']) && is_numeric($_GET['cat_id'])) {
				// Load from db, and assign
				if (($category = $GLOBALS['db']->select('CubeCart_category', false, array('cat_id' => (int)$_GET['cat_id']))) !== false) {
					$catData = $category[0];
					$category[0]['visible'] = $category[0]['hide'] ? 0 : 1;
					$GLOBALS['gui']->addBreadcrumb($category[0]['cat_name']);
					// Translations
					$GLOBALS['main']->addTabControl($lang['translate']['title_translate'], 'cat_translate', null, 'T');
					if (($translations = $GLOBALS['db']->select('CubeCart_category_language', array('translation_id', 'language', 'cat_name'), array('cat_id' => $category[0]['cat_id']), array('language' => 'ASC'))) !== false) {
						foreach ($translations as $translation) {
							$translation['edit'] = currentPage(null, array('action' => 'translate', 'translation_id' => $translation['translation_id']));
							$translation['delete'] = currentPage(null, array('action' => 'delete', 'translation_id' => $translation['translation_id']));
							$category_translations[] = $translation;
						}
					}
					$GLOBALS['smarty']->assign('TRANSLATE', currentPage(null, array('action' => 'translate')));
					$GLOBALS['smarty']->assign('TRANSLATIONS', (isset($category_translations)) ? $category_translations : null);
					$GLOBALS['smarty']->assign('DISPLAY_TRANSLATIONS', true);
				}
			} else {
				if (isset($_GET['parent'])) {
					$catData['cat_parent_id'] = (int)$_GET['parent'];
				} else {
					$catData['cat_parent_id'] = 0;
				}
			}

			$cat_display_data = (isset($_POST['cat'])) ? $_POST['cat'] : (isset($category[0])) ? $category[0] : '';
			if (is_array($cat_display_data)) {
				foreach ($cat_display_data as $key => $value) {
					if (!in_array($key, array('cat_name'))) {
						continue;
					}
					$cat_display_data[$key] = htmlentities($value, ENT_COMPAT, 'UTF-8');
				}
				$cat_display_data['seo_path'] = $GLOBALS['seo']->getdbPath('cat', $cat_display_data['cat_id']);

				$GLOBALS['smarty']->assign('CATEGORY', $cat_display_data);
			}

			// Add parent category here before query
			$catList[0] = $GLOBALS['config']->get('config', 'default_directory_symbol');
			if (($categories = $GLOBALS['db']->select('CubeCart_category', array('cat_name', 'cat_parent_id', 'cat_id'))) !== false) {
				$seo = SEO::getInstance();
				$seo->setCache(false);
				foreach ($categories as $category) {
					// Prevent adding to self, or a child

					//If there is a cat_id we are editing a category
					if (isset($category['cat_id']) && isset($catData['cat_id'])) {
						if (($catData['cat_id'] == $category['cat_id']) ||
							(!empty($category['cat_parent_id']) && $category['cat_parent_id'] == $catData['cat_id'])) {
							continue;
						}
					}
					if ($cat_path = $seo->getDirectory($category['cat_id'], false, $GLOBALS['config']->get('config', 'default_directory_symbol'), false, false)) {
						$category['display'] = $GLOBALS['config']->get('config', 'default_directory_symbol').$cat_path;
						$catList[$category['cat_id']] = $category['display'];
					} else {
						continue;
					}
				}
				$seo->setCache(true);
			}
			natcasesort($catList);
			foreach ($catList as $id => $display) {
				$selected = (isset($catData['cat_parent_id']) && $catData['cat_parent_id'] == $id) ? ' selected="selected"' : '';
				$select_categories[] = array('id' => $id, 'display' => $display, 'selected' => $selected);
			}

			// Stuff
			if (isset($catData['cat_image'])) {
				$GLOBALS['smarty']->assign('JSON_IMAGES', json_encode(array($catData['cat_image'])));
			}
			$GLOBALS['smarty']->assign('SELECT_CATEGORIES', $select_categories);
			$GLOBALS['smarty']->assign('MODE_ADDEDIT', true);
			foreach ($GLOBALS['hooks']->load('admin.category.addedit_display') as $hook) include $hook;

		}
} else {
	$GLOBALS['main']->addTabControl($lang['settings']['title_category'], 'categories');
	$GLOBALS['main']->addTabControl($lang['settings']['title_category_add'], null, currentPage(null, array('action' => 'add')));
	if (($categories = $GLOBALS['db']->select('CubeCart_category', false, array('cat_parent_id' => (isset($_GET['parent'])) ? (int)$_GET['parent'] : 0), array('priority' => 'ASC'))) !== false) {
		$i = 1;
		foreach ($categories as $category) {
			if ($category['priority'] != $i) {
				// Automatically update the priority
				$GLOBALS['db']->update('CubeCart_category', array('priority' => $i), array('cat_id' => $category['cat_id']), true);
			}
			// Check for translations
			if (($translations = $GLOBALS['db']->select('CubeCart_category_language', array('translation_id', 'language'), array('cat_id' => $category['cat_id']))) !== false) {
				foreach ($translations as $translation) {
					// Display translation icons
					$translation['edit'] = currentPage(false, array('action' => 'translate', 'cat_id' => $category['cat_id'], 'translation_id' => $translation['translation_id']));
					$category['translations'][] = $translation;
				}
			}
			$category['children'] = currentPage(null, array('parent' => $category['cat_id']));
			$category['translate'] = currentPage(null, array('action' => 'translate', 'cat_id' => $category['cat_id']));
			$category['edit']  = currentPage(null, array('action' => 'edit', 'cat_id' => $category['cat_id']));
			$category['delete']  = currentPage(null, array('delete' => $category['cat_id']));
			$children = false;
			$children = $GLOBALS['db']->count('CubeCart_category', 'cat_id', array('cat_parent_id' => $category['cat_id']));
			$category['no_children'] = $children;
			$category['alt_text'] = sprintf(((int)$children == 1) ? $lang['settings']['category_has_subcat'] : $lang['settings']['category_has_subcats'], (int)$children);
			$category['visible'] = $category['hide'] ? 0 : 1;
			$category_list[] = $category;
			++$i;
		}
	}
	$GLOBALS['smarty']->assign('LIST_CATEGORIES', true);
	$GLOBALS['smarty']->assign('CATEGORIES', $category_list);
}
$page_content = $GLOBALS['smarty']->fetch('templates/categories.index.php');