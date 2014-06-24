<?php /* File updated: July 22, 2012 */

if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('settings', CC_PERM_READ, true);

$module = new Module(__FILE__, $_GET['module'], 'admin/index.tpl', false, false);

/* LOAD LANGUAGE STRINGS */
$GLOBALS['language']->loadDefinitions('allinoneshipping', CC_ROOT_DIR.'/modules/shipping/'.$_GET['module'].'/language', 'module.definitions.xml');
$lang['allinoneshipping'] = $GLOBALS['language']->getStrings('allinoneshipping');

/* The standard getStateFormat function was failing to handle the same state abbreviation being used by two or more different countries. */

function aiosGetStateFormat($country_id, $state_input, $state_match = 'abbrev', $fetch = 'name') {
	if (($state = $GLOBALS['db']->select('CubeCart_geo_zone', false, array('country_id' => $country_id, $state_match => $state_input))) !== false) {
		return ($fetch == 'abbrev' && empty($state[0][$fetch])) ? $state[0]['name'] : $state[0][$fetch];
	}
	return $state_input;
}

/* DEFAULT SETTINGS */

if (!isset($module->_settings['range_weight'])) {
	$module->_settings['range_weight'] = 1;
	$module->_settings['range_subtotal'] = 1;
	$module->_settings['range_items'] = 0;
	$module->_settings['use_flat'] = 1;
	$module->_settings['use_weight'] = 0;
	$module->_settings['use_percent'] = 0;
	$module->_settings['use_item'] = 0;
	$module->_settings['tax'] = 0;
	$module->_settings['debug'] = 0;
	$module->module_settings_save($module->_settings);
}
/* CHECK THAT SETTINGS ARE ACCEPTABLE */
else if (!$module->_settings['use_flat'] && !$module->_settings['use_weight']
		&& !$module->_settings['use_percent'] && !$module->_settings['use_item']) {
	$module->_settings['use_flat'] = 1;
	$module->module_settings_save($module->_settings);
	$GLOBALS['gui']->setError($lang['allinoneshipping']['error_shipping_price_components']);
}


/* UPGRADE DATA FROM CUBECART 4 AND CUBECART 3 ALL IN ONE SHIPPING MODULE */

if (empty($module->_settings['cc5_data'])) {
	$zone_records = $GLOBALS['db']->select('CubeCart_shipping_zones', false, false, 'sort_order, id');
	$data_upgrade = false;
	$data_upgrade_error = false;
	if ($zone_records) {
		for ($i=0; $i<count($zone_records); $i++) {
			$record = Array();
			if (preg_match_all('/\w+/', $zone_records[$i]['countries'], $matches)) {
				$new = Array();
				$changes = false;
				foreach ($matches[0] as $country_ref) {
					if (is_numeric($country_ref)) {
						$changes = true;
						$new[] = getCountryFormat($country_ref, 'id', 'iso');
					} else {
						$new[] = $country_ref;
					}
				}
				if ($changes) {
					$record['countries'] = implode(',', $new);
				}
			}
			if (preg_match_all('/\w+/', $zone_records[$i]['states'], $matches)) {
				$new = Array();
				$changes = false;
				foreach ($matches[0] as $state_ref) {
					if (is_numeric($state_ref)) {
						$changes = true;
						$new[] = getStateFormat($state_ref, 'id', 'abbrev');
					} else {
						$new[] = $state_ref;
					}
				}
				if ($changes) {
					$record['states'] = implode(',', $new);
				}
			}
			if (preg_match_all('/[^\\|]+/', $zone_records[$i]['postcodes'], $matches)) {
				$new = implode("\r\n", $matches[0]);
				if ($new != $zone_records[$i]['postcodes']) {
					$record['postcodes'] = $new;
				}
			}
			if (!empty($record)) {
				$data_upgrade = true;
				$GLOBALS['db']->update('CubeCart_shipping_zones', $record, array('id' => (int)$zone_records[$i]['id']));
				
				trigger_error(sprintf("Debug note - All In One shipping module has upgraded your shipping zones from CubeCart 4/CubeCart 3 storage format to CubeCart 5 storage format. Zone '%s' countries changed from '%s' to '%s', states changed from '%s' to '%s', postcodes changed from '%s' to '%s'", $zone_records[$i]['zone_name'], $zone_records[$i]['countries'], $record['countries'], $zone_records[$i]['states'], $record['states'], $zone_records[$i]['postcodes'], str_replace("\r\n",',',$record['postcodes'])), E_USER_NOTICE);
			}
		}
	}
	/* Set flag to prevent this code from running next time the page is loaded */
	$module->_settings['cc5_data'] = 1;
	if (!isset($module->_settings['status'])) $module->_settings['status'] = 0;
	if (!isset($module->_settings['default'])) $module->_settings['default'] = 0;
	$module->module_settings_save($module->_settings);
	if ($data_upgrade) {
		$GLOBALS['gui']->setNotify($lang['allinoneshipping']['notify_data_upgrade']);
	}
}

/* ADD/EDIT/DELETE SHIPPING RATES */

if (isset($_POST['add_rates']) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
	$all_rates = array();

	$rate_records = $GLOBALS['db']->select('CubeCart_shipping_rates', false, false, 'id');
	if ($rate_records) {
		foreach ($rate_records as $r) {
			$all_rates[$r['id']] = $r;
		}
	}
	if ($rate_records) {
		$rates = $rate_records;
	} else {
		$rates = $rate_records;
	}

	$ins = $upd = $del = 0;
	$ins_err = $del_err = 0;

	// Delete (overrides any updates)
	foreach($_POST['delete_rates'] as $id => $delete_it) {
		if ($delete_it) {
			$result = $GLOBALS['db']->delete('CubeCart_shipping_rates', array('id' => (int)$id));
			if ($result) $del++;
			else $del_err++;
		}
	}

	// Update / Delete
	if (!empty($_POST['rates']) && is_array($_POST['rates']) && isset($all_rates)) {
		foreach($_POST['rates'] as $id => $rate_record) {
			$delete_it = $_POST['delete_rates'][$id];
			$update_it = $_POST['update_rates'][$id];

			if (!$delete_it && array_key_exists($id, $all_rates)) {
				if (isset($rate_record['method_name']) && empty($rate_record['method_name'])) {
					$result = $GLOBALS['db']->delete('CubeCart_shipping_rates', array('id' => (int)$id));
					if ($result) $del++;
					else $del_err++;
				} else if ($update_it) {
					$result = $GLOBALS['db']->update('CubeCart_shipping_rates', $rate_record, array('id' => (int)$id));
					if ($result) $upd++;
				}
			}
		}
	}

	// Insert
	if (!empty($_POST['add_rates']) && is_array($_POST['add_rates'])) {
		foreach ($_POST['add_rates'] as $zone_id => $values) {
			foreach ($values as $offset => $rate_record) {
				$rate_record['zone_id'] = $zone_id;
				if (!empty($rate_record['method_name'])) {
					$result = $GLOBALS['db']->insert('CubeCart_shipping_rates', $rate_record);
					if ($result) $ins++;
					else $ins_err++;
				}
			}
		}
	}

	// Report results
	if ($ins>0 || $upd>0 || $del>0) {
		$GLOBALS['gui']->setNotify(sprintf($lang['allinoneshipping']['notify_shipping_rate_changes'], $ins, $upd, $del));
	}
	if ($ins_err>0 || $del_err>0) {
		$GLOBALS['gui']->setError(sprintf($lang['allinoneshipping']['error_shipping_rate_changes'], $ins_err, $del_err));
	}

	//httpredir(currentPage(array('tab')));
}

/* REARRANGE (SORT/ORDER) SHIPPING ZONES */

$update = array();
if (isset($_POST['order']) && is_array($_POST['order'])) {
	// Update zone order
	foreach ($_POST['order'] as $key => $zone_id) {
		if ($zone_id != 0) {
			$update[$zone_id]['sort_order'] = $key+1;
		}
	}
}
if (isset($_POST['order']) && is_array($_POST['order']) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
	// Put changes into the database
	$updated = false;
	foreach ($update as $zone_id => $array) {
		if ($GLOBALS['db']->update('CubeCart_shipping_zones', $array, array('id' => $zone_id), true)) $updated = true;
	}
	if ($updated) {
		$GLOBALS['gui']->setNotify($lang['allinoneshipping']['notify_shipping_zone_sort_order']);
	}
	$GLOBALS['cache']->clear();
	// httpredir(currentPage(array('tab')));
}

if (isset($_POST['add_rates']) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
	httpredir(currentPage(array('tab')));
}


/* ADD/EDIT/DELETE SHIPPING ZONE */

if (isset($_GET['delete']) && !empty($_GET['delete']) && Admin::getInstance()->permissions('settings', CC_PERM_DELETE)) {
	if ($GLOBALS['db']->delete('CubeCart_shipping_zones', array('id' => (int)$_GET['delete']))) {
		$GLOBALS['db']->delete('CubeCart_shipping_rates', array('zone_id' => (int)$_GET['delete']));
		$GLOBALS['gui']->setNotify($lang['allinoneshipping']['notify_shipping_zone_delete']);
	} else {
		$GLOBALS['gui']->setError($lang['allinoneshipping']['error_shipping_zone_delete']);
	}
	httpredir(currentPage(array('delete','tab')), 'shipping_zones');
}

if (isset($_POST['zone']) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
	$record = $_POST['zone'];

	if ( ($_POST['zone_type']=='C' && !empty($_POST['zone_countries'])) ||
		 ($_POST['zone_type']=='P' && !empty($_POST['postcode_country']) && !empty($_POST['zone_postcodes'])) ||
		 ($_POST['zone_type']=='S' && !empty($_POST['state_country']) && !empty($_POST['zone_states'])) )
	{

		switch ($_POST['zone_type']) {
		case 'C':
			$record['countries'] = implode(', ', $_POST['zone_countries']);
			$record['states'] = '';
			$record['postcodes'] = '';
			break;
		case 'S':
			$record['countries'] = $_POST['state_country'];
			$record['states'] = implode(', ', $_POST['zone_states']);
			$record['postcodes'] = '';
			break;
		case 'P':
			$record['countries'] = $_POST['postcode_country'];
			$record['states'] = '';
			$record['postcodes'] =  $_POST['zone_postcodes'];
			break;
		}

		if (empty($record['zone_name'])) {
			$record['zone_name'] = $lang['allinoneshipping']['unnamed_zone'];
		}

		if (empty($_GET['zone_id'])) {
			if ($max_sort = $GLOBALS['db']->select('CubeCart_shipping_zones', 'MAX(sort_order) AS max_sort')) {
				$record['sort_order'] = $max_sort[0]['max_sort'] + 1;
			} else {
				$record['sort_order'] = 100;
			}
			// Insert
			if ($GLOBALS['db']->insert('CubeCart_shipping_zones', $record)) {
				$GLOBALS['gui']->setNotify($lang['allinoneshipping']['notify_shipping_zone_add']);
			} else {
				$GLOBALS['gui']->setError($lang['allinoneshipping']['error_shipping_zone_add']);
			}
		} else {
			// Update
			if ($GLOBALS['db']->update('CubeCart_shipping_zones', $record, array('id' => (int)$_GET['zone_id']))) {
				$GLOBALS['gui']->setNotify($lang['allinoneshipping']['notify_shipping_zone_update']);
			}
		}

	} else {
		if ($_POST['zone_type']=='C')
			$GLOBALS['gui']->setError($lang['allinoneshipping']['error_country_zone']);
		if ($_POST['zone_type']=='S')
			$GLOBALS['gui']->setError($lang['allinoneshipping']['error_state_zone']);
		if ($_POST['zone_type']=='P')
			$GLOBALS['gui']->setError($lang['allinoneshipping']['error_postcode_zone']);
	}

	httpredir(currentPage(array('action','zone_id','tab')), 'shipping_zones');
}



#########################################################

$template_vars = array();

if (isset($_GET['action'])) {

	if ($_GET['action'] == 'edit' && isset($_GET['zone_id']) && is_numeric($_GET['zone_id'])) {
		if (($zone = $GLOBALS['db']->select('CubeCart_shipping_zones', false, array('id' => (int)$_GET['zone_id']), false)) !== false) {
			$template_vars['ZONE'] = $zone[0];
			if (preg_match_all('/\w+/', $zone[0]['countries'], $matches)) {
				$template_vars['ZONE_COUNTRIES'] = $matches[0];
			} else {
				$template_vars['ZONE_COUNTRIES'] = array();
			}
			if (preg_match_all('/\w+/', $zone[0]['states'], $matches)) {
				$template_vars['ZONE_STATES'] = $matches[0];
			} else {
				$template_vars['ZONE_STATES'] = array();
			}
			if (!empty($zone[0]['postcodes'])) {
				$template_vars['ZONE_TYPE'] = 'P';
			} else if (!empty($zone[0]['states'])) {
				$template_vars['ZONE_TYPE'] = 'S';
			} else {
				$template_vars['ZONE_TYPE'] = 'C';
			}
		}
	} else {
		$template_vars['ZONE_COUNTRIES'] = array();
		$template_vars['ZONE_STATES'] = array();
	}

	$countries = $GLOBALS['db']->select('CubeCart_geo_country');
	$template_vars['COUNTRIES'] = $countries;

	//$states = $GLOBALS['db']->select('CubeCart_geo_zone', array('name', 'id'));
	$states = $GLOBALS['db']->select('CubeCart_geo_zone');
	$template_vars['STATES'] = $states;

	$template_vars['DISPLAY_FORM'] = true;

} else {

	$template_vars['ZONE'] = array('zone_name' => '');
	$template_vars['ZONE_COUNTRIES'] = array();
	$template_vars['ZONE_STATES'] = array();
	$template_vars['ZONE_TYPE'] = '';

	$zone_records = $GLOBALS['db']->select('CubeCart_shipping_zones', false, false, 'sort_order, id');
	$rest_of_world_zone = array(
		'id' => 0,
		'zone_name' => $lang['allinoneshipping']['rest_of_world'],
		'countries' => '',
		'states' => '',
		'postcodes' => '',
		'display_countries' => $lang['allinoneshipping']['all_countries'],
		'display_states' => '',
		'display_postcodes' => '',
		'sort_order' => '999',
		/* The 'tab' parameter below forces a page reload so that the new tab can be opened */
		'link_rates' => currentPage(null, array('tab' => 'zone_0')).'#zone_0',
		'link_edit' => '',
		'link_delete' => '',
	);

	if ($zone_records) {

		$GLOBALS['main']->addTabControl($lang['allinoneshipping']['tab_shipping_zones'], 'shipping_zones');
		$zones = $zone_records;

		//$countries = $GLOBALS['db']->select('CubeCart_geo_country', false, false, array('name' => 'ASC'));
		$countries = $GLOBALS['db']->select('CubeCart_geo_country');
		$states = $GLOBALS['db']->select('CubeCart_geo_zone');

		for ($i=0; $i<count($zones); $i++) {
			/* The 'tab' parameter forces a page reload so that the new tab can be opened */
			$zones[$i]['link_rates'] = currentPage(null, array('tab' => 'zone_'.$zones[$i]['id'])).'#zone_'.$zones[$i]['id'];
			$zones[$i]['link_edit'] = currentPage(array('tab'), array('action' => 'edit', 'zone_id' => $zones[$i]['id']));
			$zones[$i]['link_delete'] = currentPage(array('tab'), array('delete' => $zones[$i]['id']));
			if (preg_match_all('/\w+/', $zones[$i]['countries'], $matches)) {
				$names = Array();
				foreach ($matches[0] as $iso) {
					if ($name = getCountryFormat($iso, 'iso', 'name')) {
						$names[] = $name;
					} else {
						$names[] = $iso;
					}
					$country_id = getCountryFormat($iso, 'iso', 'id');
				}
				$zones[$i]['display_countries'] = implode('<br/>', $names);
			} else {
				$zones[$i]['display_countries'] = $zones[$i]['countries'];
			}

			if (preg_match_all('/\w+/', $zones[$i]['states'], $matches)) {
				$names = Array();
				foreach ($matches[0] as $abbrev) {
					//if ($name = getStateFormat($abbrev, 'abbrev', 'name')) {
					if ($name = aiosGetStateFormat($country_id, $abbrev, $state_match = 'abbrev', $fetch = 'name')) {
						$names[] = $name;
					} else {
						$names[] = $abbrev;
					}
				}
				$zones[$i]['display_states'] = implode('<br/>', $names);
			} else {
				$zones[$i]['display_states'] = $zones[$i]['states'];
			}

			if (preg_match_all('/[\w \-\*%]+/', $zones[$i]['postcodes'], $matches)) {
				$zones[$i]['display_postcodes'] = implode("<br/>", $matches[0]);
			} else {
				$zones[$i]['display_postcodes'] = $zones[$i]['postcodes'];
			}
		}
		$zones[] = $rest_of_world_zone;

		$multiple_zones = true;
		foreach ($zones as $zone) {
			$GLOBALS['main']->addTabControl(substr($zone['zone_name'],0,30), 'zone_'.$zone['id']);
		}
	} else {
		$zones = array();
		$zones[] = $rest_of_world_zone;
		$multiple_zones = false;
		// If no zones, we don't need any tabs
	}

	$template_vars['LINK_ADD_SHIPPING_ZONE'] = currentPage(array('tab'), array('action' => 'add'));

	$template_vars['ZONES'] = $zones;

	$template_vars['MULTIPLE_ZONES'] = $multiple_zones;

	$rates = $GLOBALS['db']->select('CubeCart_shipping_rates', false, false, 'id');
	// Default values displayed for new rates
	$new_rate = array('method_name' => '', 'flat_rate' => '', 'weight_rate' => '', 'percent_rate' => '', 'item_rate' => '', 'min_weight' => 0, 'max_weight' => 0, 'min_value' => 0, 'max_value' => 0, 'min_items' => 0, 'max_items' => 0);

	$template_vars['RATES'] = $rates;
	$template_vars['NEW_RATE'] = $new_rate;

	$template_vars['DISPLAY_RATES'] = true;

	$GLOBALS['main']->addTabControl($lang['allinoneshipping']['tab_shipping_zone_add'], null, currentPage(array('tab'), array('action' => 'add')), 'A');
}

$module->assign_to_template($template_vars, false);
$module->fetch();
$page_content = $module->display();
?>