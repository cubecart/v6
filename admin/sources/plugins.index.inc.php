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
Admin::getInstance()->permissions('maintenance', CC_PERM_READ, true);

global $lang, $glob;

$is_ajax = isset($_POST['ajax_action']);

## AJAX: Install or upgrade an extension from the API
if ($is_ajax && $_POST['ajax_action'] === 'install_extension') {
    header('Content-Type: application/json');
    $download_url = isset($_POST['download_url']) ? trim($_POST['download_url']) : '';
    $ext_name = isset($_POST['ext_name']) ? trim($_POST['ext_name']) : '';
    $ext_type = isset($_POST['ext_type']) ? trim($_POST['ext_type']) : '';

    if (empty($download_url) || !filter_var($download_url, FILTER_VALIDATE_URL)) {
        echo json_encode(array('success' => false, 'message' => 'Invalid download URL.'));
        exit;
    }

    // Only allow downloads from extensions.cubecart.com
    $parsed = parse_url($download_url);
    if (!isset($parsed['host']) || $parsed['host'] !== 'extensions.cubecart.com') {
        echo json_encode(array('success' => false, 'message' => 'Downloads are only permitted from extensions.cubecart.com.'));
        exit;
    }

    // Determine destination based on type
    switch ($ext_type) {
        case 'gateway':
            $destination = CC_ROOT_DIR.'/modules/gateway';
            break;
        case 'shipping':
            $destination = CC_ROOT_DIR.'/modules/shipping';
            break;
        case 'plugins':
            $destination = CC_ROOT_DIR.'/modules/plugins';
            break;
        case 'affiliate':
            $destination = CC_ROOT_DIR.'/modules/affiliate';
            break;
        case 'livehelp':
            $destination = CC_ROOT_DIR.'/modules/livehelp';
            break;
        case 'skin':
            $destination = CC_ROOT_DIR;
            break;
        default:
            $destination = CC_ROOT_DIR.'/modules/plugins';
            break;
    }

    if (!file_exists($destination)) {
        echo json_encode(array('success' => false, 'message' => sprintf($lang['module']['not_exist'], $destination)));
        exit;
    }
    if (!is_writable($destination)) {
        echo json_encode(array('success' => false, 'message' => sprintf($lang['module']['not_writable'], $destination)));
        exit;
    }

    // Download the zip file
    $tmp_path = CC_BACKUP_DIR.basename($download_url);

    $request = new Request('extensions.cubecart.com', $parsed['path'], 443, false, true, 30);
    $request->setMethod('get');
    $request->setSSL();
    $request->setUserAgent('CubeCart');
    $request->skiplog(true);
    $file_data = $request->send();

    if (!$file_data) {
        // Fallback to file_get_contents
        $file_data = file_get_contents($download_url);
    }

    if (!$file_data) {
        echo json_encode(array('success' => false, 'message' => $lang['module']['get_file_failed']));
        exit;
    }

    $fp = fopen($tmp_path, 'w');
    fwrite($fp, $file_data);
    fclose($fp);

    if (!file_exists($tmp_path)) {
        echo json_encode(array('success' => false, 'message' => $lang['module']['get_file_failed']));
        exit;
    }

    $zip = new ZipArchive();
    if ($zip->open($tmp_path) !== true) {
        @unlink($tmp_path);
        echo json_encode(array('success' => false, 'message' => sprintf($lang['module']['read_fail'], basename($download_url))));
        exit;
    }

    $extract = true;
    $import_language = false;
    $install_dir = '';
    $has_ioncube = false;
    $ioncube_sample_file = '';

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $file = $zip->statIndex($i);
        if (preg_match('/^email_[a-z]{2}-[A-Z]{2}.xml$/', $file['name'])) {
            $import_language = $file['name'];
        }
        $root_path = $destination.'/'.$file['name'];
        if (basename($file['name']) === 'config.xml') {
            $install_dir = str_replace(array('/config.xml', CC_ROOT_DIR), '', $root_path);
        }
        if (file_exists($root_path) && !is_writable($root_path)) {
            @unlink($tmp_path);
            echo json_encode(array('success' => false, 'message' => sprintf($lang['module']['exists_not_writable'], $root_path)));
            exit;
        }
        // Check PHP files for ionCube encoding
        if (!$has_ioncube && preg_match('/\.php$/i', $file['name'])) {
            $header = $zip->getFromIndex($i, 200);
            if ($header !== false && preg_match('/\<\?php\s+\/\/00[0-9a-f]/i', $header)) {
                $has_ioncube = true;
                $ioncube_sample_file = $file['name'];
            }
        }
    }

    // If ionCube-encoded files detected, check loader availability
    if ($has_ioncube && !extension_loaded('ionCube Loader')) {
        $zip->close();
        @unlink($tmp_path);
        echo json_encode(array('success' => false, 'message' => $lang['module']['ioncube_required']));
        exit;
    }

    $zip->extractTo($destination);
    $zip->close();
    @unlink($tmp_path);

    // If ionCube files detected and loader is present, check PHP version compatibility
    if ($has_ioncube && !empty($ioncube_sample_file)) {
        $test_file = $destination.'/'.$ioncube_sample_file;
        if (file_exists($test_file) && function_exists('ioncube_file_properties')) {
            $props = @ioncube_file_properties($test_file);
            if (is_array($props) && isset($props['php_target_major'])) {
                $current_major = PHP_MAJOR_VERSION;
                $file_major = (int)$props['php_target_major'];
                if ($file_major > 0 && $file_major !== $current_major) {
                    $GLOBALS['gui']->setNotify('ioncube_version', sprintf(
                        $lang['module']['ioncube_php_mismatch'],
                        $file_major,
                        PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION
                    ));
                }
            }
        }
        // Fallback: try ioncube_file_info if ioncube_file_properties is unavailable
        if (file_exists($test_file) && !function_exists('ioncube_file_properties') && function_exists('ioncube_file_info')) {
            $info = @ioncube_file_info($test_file);
            if ($info === false) {
                $GLOBALS['gui']->setNotify('ioncube_version', $lang['module']['ioncube_version_warning']);
            }
        }
    }

    // Import email templates if present
    if ($import_language) {
        $GLOBALS['language']->importEmail($import_language);
    }

    // Store extension info
    if (!empty($install_dir)) {
        $extension_info = array(
            'dir' => $install_dir,
            'file_name' => basename($download_url),
            'file_id' => crc32($ext_name),
            'seller_id' => 1,
            'modified' => time(),
            'name' => $ext_name,
            'keep_current' => 1
        );
        if ($GLOBALS['db']->select('CubeCart_extension_info', 'file_id', array('file_id' => $extension_info['file_id']))) {
            $GLOBALS['db']->update('CubeCart_extension_info', $extension_info, array('file_id' => $extension_info['file_id']));
        } else {
            $GLOBALS['db']->insert('CubeCart_extension_info', $extension_info);
        }
    }

    $GLOBALS['session']->delete('version_check');
    $GLOBALS['session']->delete('extension_update_check');

    echo json_encode(array('success' => true, 'message' => $lang['module']['success_install']));
    exit;
}

## AJAX: Delete an extension
if ($is_ajax && $_POST['ajax_action'] === 'delete_extension') {
    header('Content-Type: application/json');
    $ext_type = isset($_POST['ext_type']) ? basename($_POST['ext_type']) : '';
    $ext_module = isset($_POST['ext_module']) ? basename($_POST['ext_module']) : '';

    if (empty($ext_type) || empty($ext_module)) {
        echo json_encode(array('success' => false, 'message' => 'Invalid extension details.'));
        exit;
    }

    if ($ext_type === 'skins') {
        $dir = CC_ROOT_DIR.'/skins/'.$ext_module;
    } else {
        $dir = CC_ROOT_DIR.'/modules/'.$ext_type.'/'.$ext_module;
    }

    if (file_exists($dir)) {
        recursiveDelete($dir);
        $GLOBALS['db']->delete('CubeCart_config', array('name' => $ext_module));
        $GLOBALS['db']->delete('CubeCart_modules', array('folder' => $ext_module));
        $GLOBALS['db']->delete('CubeCart_hooks', array('plugin' => $ext_module));

        if (file_exists($dir)) {
            echo json_encode(array('success' => false, 'message' => $lang['module']['plugin_still_exists']));
        } else {
            echo json_encode(array('success' => true, 'message' => $lang['module']['plugin_deleted_successfully']));
        }
    } else {
        $GLOBALS['db']->delete('CubeCart_config', array('name' => $ext_module));
        $GLOBALS['db']->delete('CubeCart_modules', array('folder' => $ext_module));
        $GLOBALS['db']->delete('CubeCart_hooks', array('plugin' => $ext_module));
        echo json_encode(array('success' => true, 'message' => $lang['module']['plugin_deleted_already']));
    }
    exit;
}

## Non-AJAX legacy POST handlers

// Delete via GET (legacy)
if (isset($_GET['delete']) && $_GET['delete']==1 && !empty($_GET['type']) && !empty($_GET['module'])) {
    $type = basename($_GET['type']);
    $module = basename($_GET['module']);
    if (!empty($type) && !empty($module)) {
        if ($type === 'skins') {
            $dir = CC_ROOT_DIR.'/skins/'.$module;
        } else {
            $dir = CC_ROOT_DIR.'/modules/'.$type.'/'.$module;
        }
        if (file_exists($dir)) {
            recursiveDelete($dir);
            $GLOBALS['db']->delete('CubeCart_config', array('name' => $module));
            $GLOBALS['db']->delete('CubeCart_modules', array('folder' => $module));
            $GLOBALS['db']->delete('CubeCart_hooks', array('plugin' => $module));
            if (file_exists($dir)) {
                $GLOBALS['main']->errorMessage($lang['module']['plugin_still_exists']);
            } else {
                $GLOBALS['main']->successMessage($lang['module']['plugin_deleted_successfully']);
            }
        } else {
            $GLOBALS['db']->delete('CubeCart_config', array('name' => $module));
            $GLOBALS['db']->delete('CubeCart_modules', array('folder' => $module));
            $GLOBALS['db']->delete('CubeCart_hooks', array('plugin' => $module));
            $GLOBALS['main']->successMessage($lang['module']['plugin_deleted_already']);
        }
    }
    httpredir('?_g=plugins');
}

## Delete any remnant hash verification files
$hash_files = glob(CC_ROOT_DIR.'/'.basename(CC_FILES_DIR).'/hash.*.php');
if (is_array($hash_files)) {
    foreach ($hash_files as $file) {
        unlink($file);
    }
}

$GLOBALS['main']->addTabControl($lang['module']['ext_marketplace'], 'marketplace');
$GLOBALS['gui']->addBreadcrumb($lang['navigation']['nav_modules'], '?_g=plugins', true);

## Fetch available extensions from the API (cached for 1 hour)
$cache_key = 'extensions_api_list';
$force_refresh = isset($_GET['refresh']) && $_GET['refresh'] == 1;

if ($force_refresh) {
    $GLOBALS['session']->delete($cache_key, 'extensions_cache');
    $GLOBALS['session']->delete($cache_key.'_time', 'extensions_cache');
    $api_extensions = false;
    $cache_time = false;
} else {
    $api_extensions = $GLOBALS['session']->get($cache_key, 'extensions_cache');
    $cache_time = $GLOBALS['session']->get($cache_key.'_time', 'extensions_cache');
}

if (!$api_extensions || !$cache_time || (time() - $cache_time) > 3600) {
    $request = new Request('extensions.cubecart.com', '/api/extensions', 443, false, true, 15);
    $request->setMethod('get');
    $request->setSSL();
    $request->setUserAgent('CubeCart');
    $request->skiplog(true);
    $json = $request->send();

    if (!$json) {
        $json = file_get_contents('https://extensions.cubecart.com/api/extensions');
    }

    if ($json) {
        $data = json_decode($json, true);
        if ($data && !empty($data['success']) && !empty($data['extensions'])) {
            $api_extensions = $data['extensions'];
            $GLOBALS['session']->set($cache_key, $api_extensions, 'extensions_cache');
            $GLOBALS['session']->set($cache_key.'_time', time(), 'extensions_cache');
        }
    }
}

if (!is_array($api_extensions)) {
    $api_extensions = array();
}

## Build installed modules list from filesystem
$module_paths = glob("modules/*/*/config.xml");
$installed = array();
$configs = $GLOBALS['db']->select('CubeCart_config');
$config_isset = array();
foreach ($configs as $c) {
    array_push($config_isset, $c['name']);
}
foreach ($module_paths as $module_path) {
    try {
        $xml = new SimpleXMLElement(file_get_contents($module_path));
    } catch (Exception $e) {
        trigger_error($e->getMessage(), E_USER_WARNING);
        continue;
    }
    if (is_object($xml)) {
        $basename = (string)basename(str_replace('config.xml', '', $module_path));
        $dir_type = basename(dirname(dirname($module_path)));
        $module_config = $GLOBALS['db']->select('CubeCart_modules', '*', array('folder' => $basename, 'module' => (string)$xml->info->type));
        $installed[$basename] = array(
            'uid' => (string)$xml->info->uid,
            'type' => (string)$xml->info->type,
            'dir_type' => $dir_type,
            'name' => str_replace('_', ' ', (string)$xml->info->name),
            'description' => (string)$xml->info->description,
            'version' => (string)$xml->info->version,
            'minVersion' => (string)$xml->info->minVersion,
            'maxVersion' => (string)$xml->info->maxVersion,
            'creator' => (string)$xml->info->creator,
            'homepage' => (string)$xml->info->homepage,
            'basename' => $basename,
            'config' => (is_array($module_config)) ? $module_config[0] : array('status' => 0),
            'configured' => in_array((string)$basename, $config_isset),
            'edit_url' => '?_g=plugins&type='.(string)$xml->info->type.'&module='.$basename,
            'delete_url' => '?_g=plugins&type='.$dir_type.'&module='.$basename.'&delete=1&token='.SESSION_TOKEN
        );
        unset($xml);
    }
}

// Also scan installed skins
$skin_paths = glob("skins/*/config.xml");
foreach ($skin_paths as $skin_path) {
    try {
        $xml = new SimpleXMLElement(file_get_contents($skin_path));
    } catch (Exception $e) {
        continue;
    }
    if (is_object($xml)) {
        $basename = (string)basename(str_replace('/config.xml', '', $skin_path));
        $installed['skin_'.$basename] = array(
            'type' => 'skin',
            'dir_type' => 'skins',
            'name' => (string)$xml->info->display,
            'description' => (string)$xml->info->description,
            'version' => (string)$xml->info->version,
            'basename' => $basename,
            'creator' => (string)$xml->info->creator,
        );
        unset($xml);
    }
}

ksort($installed);

## Build categories from the API data
$categories = array();
foreach ($api_extensions as $ext) {
    $cat = $ext['category'];
    if (empty($cat)) {
        // Derive category from type
        switch ($ext['type']) {
            case 'gateway':   $cat = 'payment'; break;
            case 'shipping':  $cat = 'shipping'; break;
            case 'affiliate': $cat = 'affiliate'; break;
            case 'skin':      $cat = 'skin'; break;
            default:          $cat = 'other'; break;
        }
    }
    if (!isset($categories[$cat])) {
        $categories[$cat] = 0;
    }
    $categories[$cat]++;
}

## Build the combined extensions list for the marketplace tab
## Cross-reference API extensions with installed modules to determine status
$marketplace = array();
$matched_installed = array(); // Track which installed modules matched an API extension
foreach ($api_extensions as $ext) {
    $latest = end($ext['versions']);
    $ext_name_lower = strtolower($ext['name']);

    // Try to match with installed modules
    $is_installed = false;
    $installed_version = '';
    $installed_basename = '';
    $installed_dir_type = '';
    $has_upgrade = false;
    $configured = false;
    $edit_url = '';

    foreach ($installed as $key => $inst) {
        $inst_name_lower = strtolower($inst['name']);
        if ($inst_name_lower === $ext_name_lower || $key === strtolower(str_replace(array(' ', '-'), '_', $ext['name']))) {
            $is_installed = true;
            $installed_version = $inst['version'];
            $installed_basename = $inst['basename'];
            $installed_dir_type = $inst['dir_type'];
            $configured = !empty($inst['configured']);
            $edit_url = !empty($inst['edit_url']) ? $inst['edit_url'] : '';
            if (version_compare($latest['version'], $installed_version, '>')) {
                $has_upgrade = true;
            }
            $matched_installed[$key] = true;
            break;
        }
    }

    // Derive category from type if API category is empty
    $ext_category = $ext['category'];
    if (empty($ext_category)) {
        switch ($ext['type']) {
            case 'gateway':   $ext_category = 'payment'; break;
            case 'shipping':  $ext_category = 'shipping'; break;
            case 'affiliate': $ext_category = 'affiliate'; break;
            case 'skin':      $ext_category = 'skin'; break;
            default:          $ext_category = 'other'; break;
        }
    }

    // If installed locally, prefer name and description from config.xml
    $display_name = $ext['name'];
    $display_desc = !empty($ext['description']) ? $ext['description'] : '';
    if ($is_installed) {
        foreach ($installed as $key => $inst) {
            if (!empty($matched_installed[$key]) && (strtolower($inst['name']) === $ext_name_lower || $key === strtolower(str_replace(array(' ', '-'), '_', $ext['name'])))) {
                if (!empty($inst['name'])) {
                    $display_name = $inst['name'];
                }
                if (!empty($inst['description'])) {
                    $display_desc = $inst['description'];
                }
                break;
            }
        }
    }

    $marketplace[] = array(
        'name' => $display_name,
        'type' => $ext['type'],
        'category' => $ext_category,
        'category_label' => ucwords(str_replace('_', ' ', $ext_category)),
        'description' => $display_desc,
        'latest_version' => $latest['version'],
        'download_url' => $latest['download'],
        'versions' => array_reverse($ext['versions']),
        'images' => !empty($ext['images']) ? $ext['images'] : array(),
        'recommended' => !empty($ext['recommended']),
        'is_installed' => $is_installed,
        'installed_version' => $installed_version,
        'installed_basename' => $installed_basename,
        'installed_dir_type' => $installed_dir_type,
        'has_upgrade' => $has_upgrade,
        'configured' => $configured,
        'edit_url' => $edit_url,
        'third_party' => false,
    );
}

// Add installed modules not found in the API as 3rd party
foreach ($installed as $key => $inst) {
    if (isset($matched_installed[$key])) continue;
    // Derive category from type
    switch ($inst['type']) {
        case 'gateway':   $derived_cat = 'payment'; break;
        case 'shipping':  $derived_cat = 'shipping'; break;
        case 'affiliate': $derived_cat = 'affiliate'; break;
        case 'skin':      $derived_cat = 'skin'; break;
        default:          $derived_cat = 'other'; break;
    }
    if (!isset($categories[$derived_cat])) {
        $categories[$derived_cat] = 0;
    }
    $categories[$derived_cat]++;
    $marketplace[] = array(
        'name' => $inst['name'],
        'type' => $inst['type'],
        'category' => $derived_cat,
        'category_label' => ucwords($derived_cat),
        'description' => !empty($inst['description']) ? $inst['description'] : '',
        'latest_version' => $inst['version'],
        'download_url' => '',
        'versions' => array(),
        'is_installed' => true,
        'installed_version' => $inst['version'],
        'installed_basename' => $inst['basename'],
        'installed_dir_type' => $inst['dir_type'],
        'has_upgrade' => false,
        'configured' => !empty($inst['configured']),
        'edit_url' => !empty($inst['edit_url']) ? $inst['edit_url'] : '',
        'third_party' => (empty($inst['creator']) || stripos($inst['creator'], 'cubecart') === false),
    );
}

ksort($categories);
// Move 'other' to end
if (isset($categories['other'])) {
    $other_count = $categories['other'];
    unset($categories['other']);
    $categories['other'] = $other_count;
}

// Sort marketplace: installed first, then alphabetical
usort($marketplace, function($a, $b) {
    if ($a['is_installed'] && !$b['is_installed']) return -1;
    if (!$a['is_installed'] && $b['is_installed']) return 1;
    return strcasecmp($a['name'], $b['name']);
});

$GLOBALS['smarty']->assign('MARKETPLACE', $marketplace);
$GLOBALS['smarty']->assign('CATEGORIES', $categories);
$GLOBALS['smarty']->assign('EXT_COUNT', count($api_extensions));
$GLOBALS['smarty']->assign('SESSION_TOKEN', SESSION_TOKEN);

$page_content = $GLOBALS['smarty']->fetch('templates/plugins.index.php');
