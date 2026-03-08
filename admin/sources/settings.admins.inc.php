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

## Helper: generate 8 single-use backup codes (8 uppercase hex characters each)
function _admin2FAGenerateBackupCodes($count = 8)
{
    $codes = array();
    for ($i = 0; $i < $count; $i++) {
        $codes[] = strtoupper(bin2hex(random_bytes(4)));
    }
    return $codes;
}

## Handle 2FA management actions (separate from general admin record update)
if (isset($_POST['twofa_action']) && isset($_POST['admin_id']) && is_numeric($_POST['admin_id'])) {
    $twofa_admin_id = (int)$_POST['admin_id'];
    $is_own = ((int)$twofa_admin_id === (int)Admin::getInstance()->getId());
    $can_manage = ($is_own || Admin::getInstance()->superUser()) && Admin::getInstance()->permissions('users', CC_PERM_EDIT);

    if ($can_manage) {
        $action = $_POST['twofa_action'];

        if ($action === 'enable_email') {
            $backup_codes  = _admin2FAGenerateBackupCodes();
            $hashed_codes  = array_map(function($c) { return password_hash($c, PASSWORD_DEFAULT); }, $backup_codes);
            $GLOBALS['db']->update('CubeCart_admin_users', array(
                'twofa_enabled'      => 1,
                'twofa_method'       => 'email',
                'twofa_secret'       => null,
                'twofa_backup_codes' => json_encode($hashed_codes),
                'twofa_otp_hash'     => null,
                'twofa_otp_expires'  => 0,
            ), array('admin_id' => $twofa_admin_id));
            $GLOBALS['session']->set('twofa_show_backup_codes', $backup_codes, 'client');
            $GLOBALS['main']->successMessage($lang['admins']['notify_twofa_email_enabled']);

        } elseif ($action === 'enable_totp') {
            $secret = $GLOBALS['session']->get('twofa_totp_setup_secret', 'client');
            $code   = preg_replace('/\s+/', '', (string)($_POST['twofa_setup_code'] ?? ''));
            require_once CC_ROOT_DIR.CC_DS.'classes'.CC_DS.'totp.class.php';
            if ($secret && TOTP::verifyCode($secret, $code)) {
                $backup_codes  = _admin2FAGenerateBackupCodes();
                $hashed_codes  = array_map(function($c) { return password_hash($c, PASSWORD_DEFAULT); }, $backup_codes);
                $GLOBALS['db']->update('CubeCart_admin_users', array(
                    'twofa_enabled'      => 1,
                    'twofa_method'       => 'totp',
                    'twofa_secret'       => $secret,
                    'twofa_backup_codes' => json_encode($hashed_codes),
                    'twofa_otp_hash'     => null,
                    'twofa_otp_expires'  => 0,
                ), array('admin_id' => $twofa_admin_id));
                $GLOBALS['session']->delete('twofa_totp_setup_secret', 'client');
                $GLOBALS['session']->set('twofa_show_backup_codes', $backup_codes, 'client');
                $GLOBALS['main']->successMessage($lang['admins']['notify_twofa_totp_enabled']);
            } else {
                $GLOBALS['main']->errorMessage($lang['admins']['error_twofa_totp_invalid']);
            }

        } elseif ($action === 'disable') {
            $GLOBALS['db']->update('CubeCart_admin_users', array(
                'twofa_enabled'      => 0,
                'twofa_method'       => null,
                'twofa_secret'       => null,
                'twofa_backup_codes' => null,
                'twofa_otp_hash'     => null,
                'twofa_otp_expires'  => 0,
            ), array('admin_id' => $twofa_admin_id));
            $GLOBALS['main']->successMessage($lang['admins']['notify_twofa_disabled']);

        } elseif ($action === 'regenerate_backup') {
            $backup_codes = _admin2FAGenerateBackupCodes();
            $hashed_codes = array_map(function($c) { return password_hash($c, PASSWORD_DEFAULT); }, $backup_codes);
            $GLOBALS['db']->update('CubeCart_admin_users',
                array('twofa_backup_codes' => json_encode($hashed_codes)),
                array('admin_id' => $twofa_admin_id));
            $GLOBALS['session']->set('twofa_show_backup_codes', $backup_codes, 'client');
            $GLOBALS['main']->successMessage($lang['admins']['notify_twofa_backup_regen']);
        }
    } else {
        $GLOBALS['main']->errorMessage($lang['common']['error_no_permission'] ?? 'Access denied.');
    }
    httpredir(currentPage());
}

if (isset($_GET['tour_shown']) && is_numeric($_GET['tour_shown'])) {
    $query = "UPDATE `".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_admin_users` SET `tour_shown` = '1' WHERE `admin_id` = ".$_GET['tour_shown'];
    $GLOBALS['db']->misc($query);
    $data = $GLOBALS['session']->set('tour_shown', 1, 'admin_data');
    exit;
}

Admin::getInstance()->permissions('users', CC_PERM_READ, true);


$count = $GLOBALS['db']->query('SELECT COUNT(`admin_id`) as count from `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_admin_users` WHERE `super_user` = 1');
$count = $count[0]['count'];

## Update Admin Data
if (isset($_POST['admin']) && is_array($_POST['admin']) && Admin::getInstance()->permissions('users', CC_PERM_EDIT)) {
    $added    = false;
    $updated   = false;

    $record   = $_POST['admin'];
    $record['name'] = ucwords($record['name']);

    ## Validate new password
    if (!empty($_POST['password']) && $_POST['password'] === $_POST['passconf']) {
        $record['password'] = $_POST['password'];
    }

    ## Validate email
    if (!filter_var($_POST['admin']['email'], FILTER_VALIDATE_EMAIL)) {
        $GLOBALS['main']->errorMessage($lang['common']['error_email_invalid']);
        unset($_POST['admin']['email']);
    }

    $logout = false;
    if (isset($_POST['admin_id']) && !empty($_POST['admin_id']) && is_numeric($_POST['admin_id'])) {
        ## Update existing admin
        if (!empty($record['password'])) {
            $logout = true;
            $record['password'] = Password::getInstance()->hashPassword($record['password']);
            $record['salt'] = '';
        }

        //If there only one super then don't allow demoting
        if ($record['super_user'] == '0' && $count <= 1 && Admin::getInstance()->superUser() && (int)$_POST['admin_id'] === (int)Admin::getInstance()->getId()) {
            $record['super_user'] = '1';
        }
        $record['new_password'] = 1;
        if ($GLOBALS['db']->update('CubeCart_admin_users', $record, array('admin_id' => $_POST['admin_id']))) {
            $admin_id = Admin::getInstance()->get('admin_id');
            if ($_POST['admin_id']==$admin_id) {
                $GLOBALS['session']->set('user_language', $record['language'], 'admin');
                $GLOBALS['session']->set('language', $record['language'], 'admin_data');
            }
            $updated = true;
        }
        $admin_id = $_POST['admin_id'];
    } else {
        ## Create new admin
        if (!empty($record['password'])) {
            $record['salt']  = '';
            $record['password'] = Password::getInstance()->hashPassword($record['password']);
            $record['status'] = 1;
            if ($admin_id = $GLOBALS['db']->insert('CubeCart_admin_users', $record)) {
                $added = true;
                $GLOBALS['main']->successMessage($lang['admins']['notify_admin_create']);
            } else {
                ## no name added as it may be empty
                $GLOBALS['main']->errorMessage($lang['common']['error_admin_create']);
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

    if($logout) {
        httpredir('?_g=logout&token='.SESSION_TOKEN);
    } elseif ($added) {
        httpredir(currentPage(array('action')));
    } elseif ($updated) {
        $GLOBALS['main']->successMessage($lang['admins']['notify_admin_update']);
        httpredir(currentPage(array('action', 'admin_id')));
    } else {
        $GLOBALS['main']->errorMessage($lang['common']['error_no_changes']);
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
        $GLOBALS['main']->successMessage($lang['admins']['notify_admin_batch_update']);
    } else {
        $GLOBALS['main']->errorMessage($lang['admins']['error_admin_batch_update']);
    }
    httpredir(currentPage());
}

$GLOBALS['gui']->addBreadcrumb($GLOBALS['lang']['admins']['title_administrators']);

if (isset($_GET['action']) && (Admin::getInstance()->superUser() || ((int)$_GET['admin_id'] === (int)Admin::getInstance()->getId() || Admin::getInstance()->permissions('users', CC_PERM_FULL)))) {
    if ($_GET['action'] == 'delete' && is_numeric($_GET['admin_id'])) {
        //If there only one super then don't allow deleting
        if ($admin_user = $GLOBALS['db']->select('CubeCart_admin_users', false, array('admin_id' => (int)$_GET['admin_id']))) {
            if ($GLOBALS['db']->delete('CubeCart_admin_users', array('admin_id' => (int)$admin_user[0]['admin_id']))) {
                $GLOBALS['main']->successMessage(sprintf($lang['admins']['notify_admin_delete'], $admin_user[0]['username']));
            } else {
                $GLOBALS['main']->errorMessage($lang['admins']['error_admin_delete']);
            }
        } else {
            $GLOBALS['main']->errorMessage($lang['admins']['error_admin_exists']);
        }
        httpredir(currentPage(array('action', 'admin_id')));
    }
    if ($_GET['action'] == 'unlink' && isset($_GET['admin_id']) && is_numeric($_GET['admin_id'])) {
        $GLOBALS['db']->update('CubeCart_admin_users', array('customer_id' => null), array('admin_id' => (int)$_GET['admin_id']));
        $GLOBALS['main']->successMessage($lang['admins']['notify_admin_unlinked']);
        httpredir(currentPage(null, array('action' => 'edit')));
    }
    ##
    $GLOBALS['main']->addTabControl($lang['common']['general'], 'general');
    $GLOBALS['smarty']->assign('IS_SUPER', (bool)Admin::getInstance()->superUser());

    if ($_GET['action'] == 'edit' && isset($_GET['admin_id']) && is_numeric($_GET['admin_id'])) {
        $GLOBALS['smarty']->assign('ADD_EDIT_ADMIN', $lang['admins']['title_admin_edit']);
        if (($admin = $GLOBALS['db']->select('CubeCart_admin_users', false, array('admin_id' => (int)$_GET['admin_id']))) !== false) {
            if (!$admin[0]['super_user'] && (bool)Admin::getInstance()->superUser()) {
                $GLOBALS['main']->addTabControl($lang['admins']['permission'], 'permissions');
            }

            $admin[0]['last_login'] = formatTime($admin[0]['lastTime']);

            if ($count <= 1 && $admin[0]['super_user'] == 1) {
                unset($admin[0]['super_user']);
            }
            $GLOBALS['smarty']->assign('ADMIN', $admin[0]);
            $GLOBALS['gui']->addBreadcrumb($admin[0]['name'], currentPage());
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

            ## 2FA tab – only when editing an existing admin
            if ($_GET['action'] == 'edit' && isset($admin[0]['admin_id'])) {
                $is_own_account = ((int)$admin[0]['admin_id'] === (int)Admin::getInstance()->getId());
                $can_manage_twofa = $is_own_account || Admin::getInstance()->superUser();
                if ($can_manage_twofa) {
                    $GLOBALS['main']->addTabControl($lang['admins']['tab_twofa'], 'twofa');
                    require_once CC_ROOT_DIR.CC_DS.'classes'.CC_DS.'totp.class.php';
                    // Generate/retrieve a pending TOTP setup secret (kept in session until verified)
                    if (empty($admin[0]['twofa_enabled']) || $admin[0]['twofa_method'] !== 'totp') {
                        $pending_secret = $GLOBALS['session']->get('twofa_totp_setup_secret', 'client');
                        if (!$pending_secret) {
                            $pending_secret = TOTP::generateSecret();
                            $GLOBALS['session']->set('twofa_totp_setup_secret', $pending_secret, 'client');
                        }
                        $store_name = $GLOBALS['config']->get('config', 'store_name');
                        $GLOBALS['smarty']->assign('TOTP_SETUP_SECRET', $pending_secret);
                        $GLOBALS['smarty']->assign('TOTP_SETUP_URI', TOTP::otpauthURI($pending_secret, $admin[0]['email'], $store_name));
                    }
                    $backup_count = 0;
                    if (!empty($admin[0]['twofa_backup_codes'])) {
                        $bc = json_decode($admin[0]['twofa_backup_codes'], true);
                        $backup_count = is_array($bc) ? count($bc) : 0;
                    }
                    $GLOBALS['smarty']->assign('ADMIN_TWOFA', array(
                        'enabled'      => (bool)$admin[0]['twofa_enabled'],
                        'method'       => $admin[0]['twofa_method'],
                        'backup_count' => $backup_count,
                        'is_own'       => $is_own_account,
                    ));
                    // Show backup codes if they were just generated (flash via session)
                    if ($flash_codes = $GLOBALS['session']->get('twofa_show_backup_codes', 'client')) {
                        $GLOBALS['smarty']->assign('TWOFA_BACKUP_CODES', $flash_codes);
                        $GLOBALS['session']->delete('twofa_show_backup_codes', 'client');
                    }
                }
            }
        } else {
            $GLOBALS['main']->errorMessage($lang['admins']['error_admin_exists']);
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
    $enabled = $GLOBALS['config']->get('languages');
    foreach ($languages as $details) {
        if (isset($enabled[$details['code']]) && !$enabled[$details['code']]) continue;
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
    foreach ($GLOBALS['hooks']->load('admin.settings.admins.sections') as $hook) {
        include $hook;
    }
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
            if (!Admin::getInstance()->superUser() && (int)$admin['admin_id'] !== (int)Admin::getInstance()->getId()) {
                continue;
            }
            if (!$no_delete || $admin['super_user'] == 0) {
                $admin['link_delete'] = currentPage(null, array('action' => 'delete', 'admin_id' => $admin['admin_id'], 'token' => SESSION_TOKEN));
            }
            $admin['link_edit'] = currentPage(null, array('action' => 'edit', 'admin_id' => $admin['admin_id']));
            $smarty_data['admins'][] = $admin;
        }
        $GLOBALS['smarty']->assign('ADMINS', $smarty_data['admins']);
    }
}
$page_content = $GLOBALS['smarty']->fetch('templates/settings.admins.php');
