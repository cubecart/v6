<?php
if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('users', CC_PERM_READ, true);

global $lang;

$count = $GLOBALS['db']->query('SELECT COUNT(`admin_id`) as count from `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_admin_users` WHERE `super_user` = 1');
$count = $count[0]['count'];

## Update Admin Data
if (isset($_POST['admin']) && is_array($_POST['admin']) && Admin::getInstance()->permissions('users', CC_PERM_EDIT)) {
	$added    = false;
	$updated   = false;

	$record   = $_POST['admin'];
	$record['name'] = ucwords($record['name']);

	if (!empty($_POST['password'])) {
		if ($_POST['password'] === $_POST['passconf']) {
			$record['password'] = $_POST['password'];
		} else {

		}
	}

	## Validate email
	if (!filter_var($_POST['admin']['email'], FILTER_VALIDATE_EMAIL)) {
		$GLOBALS['main']->setACPWarning($lang['common']['error_email_invalid']);
		unset($_POST['admin']['email']);
	}

	if (isset($_POST['admin_id']) && !empty($_POST['admin_id']) && is_numeric($_POST['admin_id'])) {
		## Update existing admin
		if (!empty($record['password'])) {
			if (($user = $GLOBALS['db']->select('CubeCart_admin_users', array('salt'), array('admin_id' => $_POST['admin_id']), null, 1)) !== false) {
				if (empty($user[0]['salt'])) {
					$salt = Password::getInstance()->createSalt();
					$record['salt'] = $salt;
				} else {
					$salt = $user[0]['salt'];
				}
				$record['password'] = Password::getInstance()->getSalted($record['password'], $salt);
			}
		}

		//If there only one super then don't allow demoting
		if ($record['super_user'] == '0' && $count <= 1) {
			$record['super_user'] = '1';
		}
		$record['new_password'] = 1;
		if ($GLOBALS['db']->update('CubeCart_admin_users', $record, array('admin_id' => $_POST['admin_id']))) {
			$updated = true;
		}
		$admin_id = $_POST['admin_id'];
	} else {
		## Create new admin
		if (!empty($record['password'])) {
			$record['salt']  = Password::getInstance()->createSalt();
			$record['password'] = Password::getInstance()->getSalted($record['password'], $record['salt']);
			$record['status'] = 1;
			if ($GLOBALS['db']->insert('CubeCart_admin_users', $record)) {
				$admin_id = $GLOBALS['db']->insertid();
				$added = true;
				$GLOBALS['main']->setACPNotify($lang['admins']['notify_admin_create']);
			} else {
				## no name added as it may be empty
				$GLOBALS['main']->setACPWarning($lang['common']['error_admin_create']);
			}
		}
	}

	## Update Permissions
	$GLOBALS['db']->delete('CubeCart_permissions', array('admin_id' => $admin_id));
	if (isset($_POST['permission']) && is_array($_POST['permission']) && Admin::getInstance()->permissions('users', CC_PERM_FULL)) {
		foreach ($_POST['permission'] as $section => $mask) {
			$status = 0;
			foreach ($mask as $value) {
				$status += $value;
			}
			$record = array(
				'admin_id'  => $admin_id,
				'section_id' => $section,
				'level'   => $status,
			);
			$GLOBALS['db']->insert('CubeCart_permissions', $record);
		}
		$updated = true;
	}

	if ($added) {
		httpredir(currentPage(array('action')));
	} else if ($updated) {
			$GLOBALS['main']->setACPNotify($lang['admins']['notify_admin_update']);
			httpredir(currentPage(array('action', 'admin_id')));
		} else {
		$GLOBALS['main']->setACPWarning($lang['common']['error_no_changes']);
	}
}


## Update status
if (isset($_POST['status']) && is_array($_POST['status']) && Admin::getInstance()->permissions('users', CC_PERM_FULL)) {
	$updated = false;
	foreach ($_POST['status'] as $admin_id => $status) {
		if ($GLOBALS['db']->update('CubeCart_admin_users', array('status' => (int)$status), array('admin_id' => (int)$admin_id))) {
			$updated = true;
		}
	}
	if ($updated) {
		$GLOBALS['main']->setACPNotify($lang['admins']['notify_admin_batch_update']);
	} else {
		$GLOBALS['main']->setACPWarning($lang['admins']['error_admin_batch_update']);
	}
	httpredir(currentPage());
}

$GLOBALS['gui']->addBreadcrumb($GLOBALS['lang']['admins']['title_administrators']);

if (isset($_GET['action']) && (Admin::getInstance()->superUser() || ((int)$_GET['admin_id'] === (int)$session->admin_id || Admin::getInstance()->permissions('users', CC_PERM_FULL)))) {
	if ($_GET['action'] == 'delete' && is_numeric($_GET['admin_id'])) {
		//If there only one super then don't allow deleting
		if ($count > 1 && ($admin_user = $GLOBALS['db']->select('CubeCart_admin_users', false, array('admin_id' => (int)$_GET['admin_id']))) !== false) {
			if ($GLOBALS['db']->delete('CubeCart_admin_users', array('admin_id' => (int)$admin_user[0]['admin_id']))) {
				$GLOBALS['main']->setACPNotify(sprintf($lang['admins']['notify_admin_delete'], $admin_user[0]['username']));
			} else {
				$GLOBALS['main']->setACPWarning($lang['admins']['error_admin_delete']);
			}
		} else {
			$GLOBALS['main']->setACPWarning($lang['admins']['error_admin_exists']);
		}
		httpredir(currentPage(array('action', 'admin_id')));
	}
	if ($_GET['action'] == 'unlink' && isset($_GET['admin_id']) && is_numeric($_GET['admin_id'])) {
		$GLOBALS['db']->update('CubeCart_admin_users', array('customer_id' => null), array('admin_id' => (int)$_GET['admin_id']));
		$GLOBALS['main']->setACPNotify($lang['admins']['notify_admin_unlinked']);
		httpredir(currentPage(null, array('action' => 'edit')));
	}
	##
	$GLOBALS['main']->addTabControl($lang['common']['general'], 'general');
	$GLOBALS['smarty']->assign('IS_SUPER', (bool)Admin::getInstance()->superUser());

	if ($_GET['action'] == 'edit' && isset($_GET['admin_id']) && is_numeric($_GET['admin_id'])) {
		$GLOBALS['smarty']->assign('ADD_EDIT_ADMIN', $lang['admins']['title_admin_edit']);
		if (($admin = $GLOBALS['db']->select('CubeCart_admin_users', false, array('admin_id' => (int)$_GET['admin_id']))) !== false) {
			if (!$admin[0]['super_user']) {
				$GLOBALS['main']->addTabControl($lang['admins']['permission'], 'permissions');
			}

			$admin[0]['last_login'] = formatTime($admin[0]['lastTime']);

			if ($count <= 1 && $admin[0]['super_user'] == 1) {
				unset($admin[0]['super_user']);
			}
			$GLOBALS['smarty']->assign('ADMIN', $admin[0]);
			$GLOBALS['gui']->addBreadcrumb($admin[0]['name']);
			## Load Permissions data
			$permissions = $GLOBALS['db']->select('CubeCart_permissions', false, array('admin_id' => $admin[0]['admin_id']));
			if ($permissions) {
				foreach ($permissions as $perm) {
					$permission[$perm['section_id']] = $perm['level'];
				}
			}
			if (!empty($admin[0]['customer_id'])) {
				$GLOBALS['smarty']->assign('USER', $user[0]);
				$GLOBALS['smarty']->assign('UNLINK', currentPage(null, array('action' => 'unlink')));
				$GLOBALS['smarty']->assign('LINKED', true);
			}
			$GLOBALS['main']->addTabControl($lang['admins']['tab_overview'], 'overview');
		} else {
			$GLOBALS['main']->setACPWarning($lang['admins']['error_admin_exists']);
			httpredir(currentPage(array('action', 'admin_id')));
		}
	} else {
		if (Admin::getInstance()->superUser()) {
			$GLOBALS['smarty']->assign('ADMIN', array('super_user' => false));
		}
		$GLOBALS['main']->addTabControl($lang['admins']['permission'], 'permissions');
		$GLOBALS['smarty']->assign('ADD_EDIT_ADMIN', $lang['admins']['title_admin_add']);
		$GLOBALS['gui']->addBreadcrumb('Create New');
	}
	$GLOBALS['smarty']->assign('DISPLAY_FORM', true);
	$languages = $GLOBALS['language']->listLanguages();
	$comparitor = (isset($admin[0]['language'])) ? $admin[0]['language'] : $GLOBALS['config']->get('config', 'default_language');
	foreach ($languages as $details) {
		$details['selected'] = ($comparitor == $details['code']) ? ' selected="selected"' : '';
		$smarty_data['languages'][] = $details;
	}
	$GLOBALS['smarty']->assign('LANGUAGES', $smarty_data['languages']);

	$sections = array(
		'categories' => 3,
		'customers'  => 5,
		'documents'  => 4,
		'filemanager' => 7,
		'orders'  => 10,
		'products'  => 2,
		'users'   => 1,
		'statistics' => 8,
		'settings'  => 9,
		'reviews'  => 12,
	);
	## Load Sections data
	foreach ($GLOBALS['hooks']->load('admin.settings.admins.sections') as $hook) include $hook;
	foreach ($sections as $name => $section_id) {
		$section['id']  = $section_id;
		$section['info'] = $lang['admins']['perm_'.$name.'_info'];
		$section['name'] = $lang['admins']['perm_'.$name];
		#
		$section['read'] = (isset($permission[$section_id]) && $permission[$section_id] & 1) ? 'checked="checked"' : '';
		$section['edit'] = (isset($permission[$section_id]) && $permission[$section_id] & 2) ? 'checked="checked"' : '';
		$section['delete'] = (isset($permission[$section_id]) && $permission[$section_id] & 4) ? 'checked="checked"' : '';
		#
		$smarty_data['sections'][] = $section;
	}
	$GLOBALS['smarty']->assign('SECTIONS', $smarty_data['sections']);
} else {
	$GLOBALS['main']->addTabControl($lang['admins']['title_administrators'], 'admins');
	if (Admin::getInstance()->permissions('users', CC_PERM_EDIT)) {
		$GLOBALS['main']->addTabControl($lang['admins']['tab_admin_create'], false, currentPage(null, array('action' => 'add')));
	}
	if (($admins = $GLOBALS['db']->select('CubeCart_admin_users')) !== false) {
		$no_delete = false;
		//If there is only one superuser we have to keep him
		if ($GLOBALS['db']->numrows('SELECT `admin_id` from `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_admin_users` WHERE `super_user` = 1')==1) {
			$no_delete = true;
		}
		foreach ($admins as $admin) {
			if (!Admin::getInstance()->superUser() && (int)$admin['admin_id'] !== (int)$session->admin_id) {
				continue;
			}
			if (!$no_delete || $admin['super_user'] != 1) {
				$admin['link_delete'] = currentPage(null, array('action' => 'delete', 'admin_id' => $admin['admin_id']));
			}
			$admin['link_edit'] = currentPage(null, array('action' => 'edit', 'admin_id' => $admin['admin_id']));
			$smarty_data['admins'][] = $admin;
		}
		$GLOBALS['smarty']->assign('ADMINS', $smarty_data['admins']);
	}
}
$page_content = $GLOBALS['smarty']->fetch('templates/settings.admins.php');