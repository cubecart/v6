<?php
if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('products', CC_PERM_READ, true);

global $lang;

if (!empty($_POST) && Admin::getInstance()->permissions('products', CC_PERM_EDIT)) {
	$changes = false;
	## edit option group
	if (isset($_POST['edit_group']) && is_array($_POST['edit_group'])) {
		$updated = false;
		foreach ($_POST['edit_group'] as $key => $value) {
			if ($GLOBALS['db']->update('CubeCart_option_group', $value, array('option_id' => (int)$key))) {
				$updated = true;
				$changes = true;
			}
		}
		if ($updated) {
			$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_option_group_update']);
		}
	}

	## edit attributes
	if (isset($_POST['edit_attribute']) && is_array($_POST['edit_attribute'])) {
		$updated = false;
		foreach ($_POST['edit_attribute'] as $key => $value) {
			if ($GLOBALS['db']->update('CubeCart_option_value', $value, array('value_id' => (int)$key))) {
				$updated = true;
				$changes = true;
			}
		}
		if ($updated) {
			$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_option_attrib_update']);
		}
	}

	## Add options to a set
	if (!empty($_POST['set_id']) && !empty($_POST['add_to_set'])) {
		$set_id = (int)$_POST['set_id'];
		$added = false;
		foreach ($_POST['add_to_set'] as $value) {
			if ($value{0} == 'g') {
				$value = substr($value, 1);
				list($option, $value) = explode('-', $value);
			} else {
				$option = $value;
				$value  = 0;
			}
			$record = array('set_id' => $set_id, 'option_id' => (int)$option, 'value_id' => (int)$value);
			if (isset($record) && !$GLOBALS['db']->select('CubeCart_options_set_member', array('member_id'), $record)) {
				if ($GLOBALS['db']->insert('CubeCart_options_set_member', $record)) {
					$added = true;
					$changes = true;
				}

			}
		}
		if ($added) {
			$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_option_group_attrib_added']);
		}
	}
	## Remove options from a set
	if (isset($_POST['member_delete']) && Admin::getInstance()->permissions('products', CC_PERM_DELETE)) {
		$deleted = false;
		foreach ($_POST['member_delete'] as $set_member_id) {
			if ($GLOBALS['db']->delete('CubeCart_options_set_member', array('set_member_id' => (int)$set_member_id))) {
				$deleted = true;
				$changes = true;
			}
		}
		if ($deleted) {
			$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_option_group_attrib_delete']);
		} else {
			$GLOBALS['main']->setACPWarning($lang['catalogue']['error_option_group_attrib_delete']);
		}
	}

	## Group
	if (isset($_POST['add-group']) && !empty($_POST['add-group']['option_name'])) {
		if ($GLOBALS['db']->insert('CubeCart_option_group', $_POST['add-group'])) {
			$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_option_group_create']);
			$changes = true;
		} else {
			$GLOBALS['main']->setACPWarning($lang['catalogue']['error_option_group_create']);
		}
	}
	## Value
	if (isset($_POST['add_attr'])) {
		$attributes_added = false;
		foreach ($_POST['add_attr'] as $option_id => $values) {
			foreach ($values as $offset => $data) {
				$record = array(
					'value_name' => $data['attr_name'],
					'option_id'  => $option_id,
				);
				if ($GLOBALS['db']->insert('CubeCart_option_value', $record)) {
					$changes = true;
					$attributes_added = true;
				}
			}
			if ($attributes_added) {
				$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_option_attrib_create']);
			} else {
				$GLOBALS['main']->setACPWarning($lang['catalogue']['error_option_attrib_create']);
			}
		}
	}
	## Create Set
	if (isset($_POST['set_create']) && !empty($_POST['set_create']['set_name'])) {
		if ($GLOBALS['db']->insert('CubeCart_options_set', $_POST['set_create'])) {
			$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_option_set_create']);
			$changes = true;
		} else {
			$GLOBALS['main']->setACPWarning($lang['catalogue']['error_option_set_create']);
		}
	}
	// Set/Update option priority
	$priority_target = array(
		'group_priority' => array('CubeCart_option_group', 'option_id'),
		'attr_priority'  => array('CubeCart_option_value', 'value_id'),
	);

	foreach ($priority_target as $name => $table) {

		if (isset($_POST[$name]) && is_array($_POST[$name])) {

			$update = array();

			foreach ($_POST[$name] as $key => $id) {
				$update[$id]['priority'] = $key+1;
			}

			foreach ($update as $id => $array) {
				$GLOBALS['db']->update($table[0], $array, array($table[1] => $id), true);
			}
		}
	}
	if (!$changes) {
		$GLOBALS['main']->setACPWarning($lang['catalogue']['error_option_no_change']);
	}
	httpredir(currentPage());
}

## Delete group/value/set
if (isset($_GET['delete']) && is_numeric($_GET['id']) && Admin::getInstance()->permissions('products', CC_PERM_DELETE)) {
	switch (strtolower($_GET['delete'])) {
	case 'group':
		## remove dependancies
		$GLOBALS['db']->delete('CubeCart_options_set_member', array('option_id' => $_GET['id']));
		$GLOBALS['db']->delete('CubeCart_option_assign', array('option_id' => $_GET['id']));
		$GLOBALS['db']->delete('CubeCart_option_value', array('option_id' => $_GET['id']));

		## remove itself
		if ($GLOBALS['db']->delete('CubeCart_option_group', array('option_id' => $_GET['id']))) {
			$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_option_group_delete']);
		} else {
			$GLOBALS['main']->setACPWarning($lang['catalogue']['error_option_group_delete']);
		}
		$anchor = 'groups';
		break;
	case 'attribute':
		## remove dependancies
		$GLOBALS['db']->delete('CubeCart_options_set_member', array('value_id' => $_GET['id']));
		$GLOBALS['db']->delete('CubeCart_option_assign', array('value_id' => $_GET['id']));
		## remove itself
		if ($GLOBALS['db']->delete('CubeCart_option_value', array('value_id' => $_GET['id']))) {
			$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_option_attrib_delete']);
		} else {
			$GLOBALS['main']->setACPWarning($lang['catalogue']['error_option_attrib_delete']);
		}
		$anchor = 'attributes';
		break;
	case 'set':
		if (($members = $GLOBALS['db']->select('CubeCart_options_set_member', array('set_member_id'), array('set_id' => $_GET['id']))) !== false) {
			foreach ($members as $member) {
				$member_list[] = $member['set_member_id'];
			}
			$GLOBALS['db']->update('CubeCart_option_assign', array('set_member_id' => 0), array('set_member_id' => $member_list));
		}
		## remove dependancies
		$GLOBALS['db']->delete('CubeCart_options_set_member', array('set_id' => $_GET['id']));
		$GLOBALS['db']->delete('CubeCart_options_set_product', array('set_id' => $_GET['id']));
		## remove itself
		if ($GLOBALS['db']->delete('CubeCart_options_set', array('set_id' => $_GET['id']))) {
			$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_option_set_delete']);
		} else {
			$GLOBALS['main']->setACPWarning($lang['catalogue']['error_option_set_delete']);
		}

		$anchor = 'sets';
	}
	httpredir(currentPage(array('delete', 'id')), $anchor);
}

## Update groups/values/sets
if (isset($_POST['group']) && Admin::getInstance()->permissions('products', CC_PERM_EDIT)) {
	$updated = false;
	foreach ($_POST['group'] as $id => $data) {
		if (is_array($data) && is_numeric($id)) {
			if ($GLOBALS['db']->update('CubeCart_option_group', $data, array('option_id' => $id))) {
				$updated = true;
			}
		}
	}
	if ($updated) {
		$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_option_group_update']);
	}
	httpredir(currentPage(), 'groups');
}
if (isset($_POST['value']) && Admin::getInstance()->permissions('products', CC_PERM_EDIT)) {
	foreach ($_POST['value'] as $id => $data) {
		# if (!empty($name) && is_numeric($id)) {
		#  $GLOBALS['db']->update('CubeCart_option_group', array('option_name' => $name), array('option_id' => $id));
		# }
	}
	$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_option_attrib_update']);
	httpredir(currentPage(), 'attributes');
}


##########################################################

$GLOBALS['main']->addTabControl($lang['catalogue']['title_option_groups'], 'groups');
$GLOBALS['main']->addTabControl($lang['catalogue']['title_option_attributes'], 'attributes');
$GLOBALS['main']->addTabControl($lang['catalogue']['title_option_sets'], 'sets');
$GLOBALS['gui']->addBreadcrumb($lang['catalogue']['title_product_options'], currentPage());

## Get all categories (top)
$sort_group = array('priority' => 'ASC', 'option_type' => 'ASC', 'option_name' => 'ASC');
$sort = array('priority' => 'ASC');
if (($categories = $GLOBALS['db']->select('CubeCart_option_group', false, false, $sort_group)) !== false) {
	foreach ($categories as $option) {
		$optionArray[$option['option_id']] = array(
			'id'   => $option['option_id'],
			'name'   => htmlentities($option['option_name'], ENT_COMPAT, 'UTF-8'),
			'type'   => $option['option_type'],
			'description' => empty($option['option_description']) ? '-' : $option['option_description'],
			'required'  => (int)$option['option_required'],
			'priority'  => (int)$option['priority'],
		);
	}
	## Get all values (middle)
	$values = $GLOBALS['db']->select('CubeCart_option_value', false, false, $sort);
	if ($values) {
		foreach ($values as $value) {
			if (isset($optionArray[$value['option_id']])) {
				$optionArray[$value['option_id']]['options'][$value['value_id']] = $value['value_name'];
				$optionArray[$value['option_id']]['values_priority'][$value['value_id']] = $value['priority'];
				//natcasesort($optionArray[$value['option_id']]['options']);
			} else {
				## Kill the orphans!
				$GLOBALS['db']->delete('CubeCart_option_value', array('value_id' => $value['value_id']));
			}
		}
	}
}

$optionTypes = array(
	0 => $lang['catalogue']['option_type_select'],
	1 => $lang['catalogue']['option_type_textbox'],
	2 => $lang['catalogue']['option_type_textarea'],
	# 3 => $lang['catalogue']['option_type_checkbox'],
);
$GLOBALS['smarty']->assign('OPTION_TYPES', $optionTypes);
$GLOBALS['smarty']->assign('OPTION_TYPE_JSON', json_encode($optionTypes));

if (isset($optionArray) && !empty($optionArray)) {
	foreach ($optionArray as $option_id => $option) {
		$option['type_name']= $optionTypes[$option['type']];
		$option['delete'] = currentPage(null, array('delete' => 'group', 'id' => $option_id));
		$groups_list[]  = $option;
		$smarty_data['option_name'][$option_id] = $optionArray[$option_id]['name'];
	}
	$GLOBALS['smarty']->assign('OPTION_NAME', $smarty_data['option_name']);
	$GLOBALS['smarty']->assign('GROUPS', $groups_list);
}

if (($optionsets = $GLOBALS['db']->select('CubeCart_options_set')) !== false) {
	foreach ($optionsets as $set) {
		$set_data[$set['set_id']] = $set;
		$set_sort = array('set_member_id' => 'ASC'); // array('group_id' => 'ASC')
		if (($set_values = $GLOBALS['db']->select('CubeCart_options_set_member', false, array('set_id' => $set['set_id']), $set_sort)) !== false) {
			foreach ($set_values as $set_value) {
				$set_value = array_merge($set_value, array('display' => ($set_value['value_id'] > 0) ? $optionArray[$set_value['option_id']]['options'][$set_value['value_id']] : $optionArray[$set_value['option_id']]['name']));
				$set_data[$set['set_id']]['members'][$set_value['option_id']][$set_value['value_id']] = $set_value;
				$set_data[$set['set_id']]['members'][$set_value['option_id']]['priority'] = $optionArray[$set_value['option_id']]['priority'];
				$set_data[$set['set_id']]['members'][$set_value['option_id']][$set_value['value_id']]['priority'] = $optionArray[$set_value['option_id']]['values_priority'][$set_value['value_id']];
			}
		}
	}
	foreach ($set_data as $set_id => $set) {
		uasort($set['members'], 'cmpmc');

		foreach ($set['members'] as $oid => $array) {
			uasort($array, 'cmpmc');

			if (is_array($array)) $set['members'][$oid] = $array;

			unset($set['members'][$oid]['priority']);
		}
		$set['delete'] = currentPage(null, array('delete' => 'set', 'id' => (int)$set['set_id']));
		$smarty_data['list_sets'][$set_id] = $set;
	}
	$GLOBALS['smarty']->assign('SETS', $smarty_data['list_sets']);
}
$page_content = $GLOBALS['smarty']->fetch('templates/products.options.php');