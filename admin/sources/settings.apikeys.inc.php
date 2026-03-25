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
Admin::getInstance()->permissions('settings', CC_PERM_READ, true);

// Load API auth class for key generation
require_once CC_CLASSES_DIR . 'api/response.class.php';
require_once CC_CLASSES_DIR . 'api/auth.class.php';

$db = $GLOBALS['db'];

// Available API resources for permission assignment
$api_resources = array(
    'products'   => 'Products',
    'categories' => 'Categories',
    'orders'     => 'Orders',
    'customers'  => 'Customers',
    'coupons'    => 'Coupons',
    'shipping'   => 'Shipping',
    'tax'        => 'Tax',
    'settings'   => 'Settings',
    'reviews'    => 'Reviews',
    'files'      => 'File Manager',
);

$max_api_keys = 20;

## Handle POST actions
if (isset($_POST['create_key']) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
    // Enforce maximum key limit
    $keyCount = (int)$db->count('CubeCart_api_keys', 'key_id');
    if ($keyCount >= $max_api_keys) {
        $GLOBALS['main']->errorMessage('Maximum number of API keys ('.$max_api_keys.') reached. Delete unused keys before creating new ones.');
        httpredir('?_g=settings&node=apikeys');
    }

    $label    = isset($_POST['label']) ? trim($_POST['label']) : '';
    $adminId  = isset($_POST['admin_id']) ? (int)$_POST['admin_id'] : (int)Admin::getInstance()->getId();
    $rateLimit = isset($_POST['rate_limit']) ? max(1, (int)$_POST['rate_limit']) : 60;
    $expires  = (!empty($_POST['expires'])) ? strtotime($_POST['expires']) : 0;

    // Build permissions
    $permissions = array();
    if (isset($_POST['perms']) && is_array($_POST['perms'])) {
        foreach ($_POST['perms'] as $resource => $level) {
            if (isset($api_resources[$resource]) && in_array($level, array('r', 'rw', 'rwd'))) {
                $permissions[$resource] = $level;
            }
        }
    }

    // IP whitelist
    $ipWhitelist = null;
    if (!empty($_POST['ip_whitelist'])) {
        $ips = array_filter(array_map('trim', explode("\n", $_POST['ip_whitelist'])));
        if (!empty($ips)) {
            $ipWhitelist = json_encode(array_values($ips));
        }
    }

    // Generate key pair
    $keyPair = ApiAuth::generateKeyPair();

    $db->insert('CubeCart_api_keys', array(
        'admin_id'        => $adminId,
        'api_key'         => $keyPair['api_key'],
        'api_secret_hash' => $keyPair['api_secret_hash'],
        'label'           => $label,
        'permissions'     => json_encode($permissions),
        'rate_limit'      => $rateLimit,
        'status'          => 1,
        'ip_whitelist'    => $ipWhitelist,
        'created'         => time(),
        'expires'         => $expires ?: 0,
    ));

    // Store the secret in session for one-time display
    $GLOBALS['session']->set('api_new_key', $keyPair['api_key'], 'admin');
    $GLOBALS['session']->set('api_new_secret', $keyPair['api_secret'], 'admin');

    $GLOBALS['main']->successMessage('API key created successfully. Copy the secret now - it will not be shown again.');
    httpredir('?_g=settings&node=apikeys');
}

if (isset($_POST['update_key']) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
    $keyId = (int)$_POST['key_id'];

    $permissions = array();
    if (isset($_POST['perms']) && is_array($_POST['perms'])) {
        foreach ($_POST['perms'] as $resource => $level) {
            if (isset($api_resources[$resource]) && in_array($level, array('r', 'rw', 'rwd'))) {
                $permissions[$resource] = $level;
            }
        }
    }

    $ipWhitelist = null;
    if (!empty($_POST['ip_whitelist'])) {
        $ips = array_filter(array_map('trim', explode("\n", $_POST['ip_whitelist'])));
        if (!empty($ips)) {
            $ipWhitelist = json_encode(array_values($ips));
        }
    }

    $update = array(
        'label'       => isset($_POST['label']) ? trim($_POST['label']) : '',
        'permissions' => json_encode($permissions),
        'rate_limit'  => isset($_POST['rate_limit']) ? max(1, (int)$_POST['rate_limit']) : 60,
        'status'      => isset($_POST['status']) ? (int)$_POST['status'] : 1,
        'ip_whitelist' => $ipWhitelist,
        'expires'     => (!empty($_POST['expires'])) ? strtotime($_POST['expires']) : 0,
    );

    $db->update('CubeCart_api_keys', $update, array('key_id' => $keyId));
    $GLOBALS['main']->successMessage('API key updated.');
    httpredir('?_g=settings&node=apikeys');
}

if (isset($_GET['delete_key']) && is_numeric($_GET['delete_key']) && Admin::getInstance()->permissions('settings', CC_PERM_DELETE)) {
    $db->delete('CubeCart_api_keys', array('key_id' => (int)$_GET['delete_key']));
    $GLOBALS['main']->successMessage('API key deleted.');
    httpredir('?_g=settings&node=apikeys');
}

if (isset($_GET['regenerate_key']) && is_numeric($_GET['regenerate_key']) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
    $keyPair = ApiAuth::generateKeyPair();
    $db->update('CubeCart_api_keys', array(
        'api_key'         => $keyPair['api_key'],
        'api_secret_hash' => $keyPair['api_secret_hash'],
    ), array('key_id' => (int)$_GET['regenerate_key']));

    $GLOBALS['session']->set('api_new_key', $keyPair['api_key'], 'admin');
    $GLOBALS['session']->set('api_new_secret', $keyPair['api_secret'], 'admin');

    $GLOBALS['main']->successMessage('API key regenerated. Copy the new secret now - it will not be shown again.');
    httpredir('?_g=settings&node=apikeys');
}

## Check for newly created secret to display once
$newKey    = $GLOBALS['session']->get('api_new_key', 'admin');
$newSecret = $GLOBALS['session']->get('api_new_secret', 'admin');
if ($newKey) {
    $GLOBALS['session']->delete('api_new_key', 'admin');
    $GLOBALS['session']->delete('api_new_secret', 'admin');
    $GLOBALS['smarty']->assign('NEW_API_KEY', $newKey);
    $GLOBALS['smarty']->assign('NEW_API_SECRET', $newSecret);
}

## Load admin list for owner dropdown (needed for both add and edit forms)
$admins = $db->select('CubeCart_admin_users', array('admin_id', 'name', 'username'), array('status' => 1));
$GLOBALS['smarty']->assign('ADMIN_LIST', $admins ?: array());
$GLOBALS['smarty']->assign('API_RESOURCES', $api_resources);
$GLOBALS['smarty']->assign('API_URL', CC_STORE_URL . '/api/v1/');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'edit' && isset($_GET['key_id']) && is_numeric($_GET['key_id'])) {
    ## Edit mode
    $keyData = $db->select('CubeCart_api_keys', false, array('key_id' => (int)$_GET['key_id']));
    if ($keyData) {
        $key = $keyData[0];
        $key['permissions_array'] = json_decode($key['permissions'], true) ?: array();
        $key['ip_whitelist_text'] = '';
        if (!empty($key['ip_whitelist'])) {
            $ips = json_decode($key['ip_whitelist'], true);
            $key['ip_whitelist_text'] = is_array($ips) ? implode("\n", $ips) : '';
        }
        if ($key['expires'] > 0) {
            $key['expires_date'] = date('Y-m-d', $key['expires']);
        }
        $GLOBALS['smarty']->assign('EDIT_KEY', $key);
    }
    $GLOBALS['smarty']->assign('DISPLAY_FORM', true);
    $GLOBALS['main']->addTabControl('Edit API Key', 'apikeys');
    $GLOBALS['gui']->addBreadcrumb('Edit API Key');

} elseif ($action === 'add') {
    ## Create mode
    $GLOBALS['smarty']->assign('DISPLAY_FORM', true);
    $GLOBALS['smarty']->assign('CREATE_MODE', true);
    $GLOBALS['main']->addTabControl('API Keys', false, '?_g=settings&node=apikeys');
    $GLOBALS['main']->addTabControl('Create New', 'apikeys');
    $GLOBALS['gui']->addBreadcrumb('Create API Key');

} else {
    ## List mode
    $keys = $db->select('CubeCart_api_keys', false, false, array('created' => 'DESC'));
    if ($keys) {
        foreach ($keys as &$key) {
            $admin = $db->select('CubeCart_admin_users', array('name'), array('admin_id' => $key['admin_id']));
            $key['admin_name'] = $admin ? $admin[0]['name'] : 'Unknown';
            $key['masked_key'] = substr($key['api_key'], 0, 10) . '...' . substr($key['api_key'], -4);
            $key['created_date'] = date('Y-m-d H:i', $key['created']);
            $key['last_used_date'] = $key['last_used'] > 0 ? date('Y-m-d H:i', $key['last_used']) : 'Never';
            $key['expires_date'] = $key['expires'] > 0 ? date('Y-m-d', $key['expires']) : 'Never';
            $key['permissions_array'] = json_decode($key['permissions'], true) ?: array();
        }
        unset($key);
    }
    $GLOBALS['smarty']->assign('API_KEYS', $keys ?: array());

    $GLOBALS['main']->addTabControl('API Keys', 'apikeys');
    if (Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
        $GLOBALS['main']->addTabControl('Create New', false, currentPage(null, array('action' => 'add')));
    }

    ## API Log tab
    $log_page_var = 'page_log';
    $log_sort_var = 'sort_log';
    $log_anchor   = 'apilog';
    $log_per_page = 20;

    if (!isset($_GET[$log_sort_var]) || !is_array($_GET[$log_sort_var])) {
        $_GET[$log_sort_var] = array('request_time' => 'DESC');
    }

    $log_current_page = currentPage(array($log_sort_var));
    $log_thead = array(
        'method'      => $db->column_sort('method', 'Method', $log_sort_var, $log_current_page, $_GET[$log_sort_var], $log_anchor),
        'endpoint'    => $db->column_sort('endpoint', 'Endpoint', $log_sort_var, $log_current_page, $_GET[$log_sort_var], $log_anchor),
        'status_code' => $db->column_sort('status_code', 'Status', $log_sort_var, $log_current_page, $_GET[$log_sort_var], $log_anchor),
        'ip_address'  => $db->column_sort('ip_address', 'IP Address', $log_sort_var, $log_current_page, $_GET[$log_sort_var], $log_anchor),
        'response_ms' => $db->column_sort('response_ms', 'Time (ms)', $log_sort_var, $log_current_page, $_GET[$log_sort_var], $log_anchor),
        'date'        => $db->column_sort('request_time', 'Date', $log_sort_var, $log_current_page, $_GET[$log_sort_var], $log_anchor),
    );
    $GLOBALS['smarty']->assign('LOG_THEAD', $log_thead);

    $log_page = isset($_GET[$log_page_var]) ? (int)$_GET[$log_page_var] : 1;
    $log_where = array();
    if (isset($_GET['log_key_id']) && is_numeric($_GET['log_key_id'])) {
        $log_where['key_id'] = (int)$_GET['log_key_id'];
    }

    $log_total = (int)$db->count('CubeCart_api_log', 'log_id', $log_where ?: false);
    $GLOBALS['smarty']->assign('API_LOG_TOTAL', $log_total);

    $log_entries = $db->select('CubeCart_api_log', false, $log_where ?: false, $_GET[$log_sort_var], $log_per_page, $log_page);
    if ($log_entries) {
        // Build key_id -> label lookup
        $key_labels = array();
        if ($keys) {
            foreach ($keys as $k) {
                $key_labels[$k['key_id']] = $k['label'] ?: $k['masked_key'];
            }
        }
        foreach ($log_entries as &$entry) {
            $entry['date'] = formatTime($entry['request_time']);
            $entry['key_label'] = isset($key_labels[$entry['key_id']]) ? $key_labels[$entry['key_id']] : '#' . $entry['key_id'];
        }
        unset($entry);
        $GLOBALS['smarty']->assign('API_LOG', $log_entries);
    }
    $GLOBALS['smarty']->assign('PAGINATION_API_LOG', $db->pagination(false, $log_per_page, $log_page, 5, $log_page_var, $log_anchor));
    $GLOBALS['main']->addTabControl('API Log', $log_anchor);

    // Key filter dropdown for log
    $GLOBALS['smarty']->assign('LOG_KEY_FILTER', isset($_GET['log_key_id']) ? (int)$_GET['log_key_id'] : '');
}

$page_content = $GLOBALS['smarty']->fetch('string:' . file_get_contents(CC_ROOT_DIR . '/' . $GLOBALS['config']->get('config', 'adminFolder') . '/skins/default/templates/settings.apikeys.php'));
