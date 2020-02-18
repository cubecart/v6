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
Admin::getInstance()->permissions('maintenance', CC_PERM_READ, true);

global $lang, $glob;

$default_apps = array(
    'gateway' => '1|452',
    'shipping' => '1|44'
);

foreach($default_apps as $extension_type => $token) {

    if($GLOBALS['config']->has('default_apps', $token) || $GLOBALS['db']->select('CubeCart_modules', false, array('module' => $extension_type))) {
        $GLOBALS['config']->set('default_apps', $token, 1); continue;
    }

    $request = new Request('www.cubecart.com', '/extensions/token/'.$token.'/get', 443, false, true, 10);
    $request->setMethod('get');
    $request->setSSL();
    $request->setUserAgent('CubeCart');
    $request->skiplog(true);

    $json = $request->send();
    $data = json_decode($json, true);

    $destination = CC_ROOT_DIR.'/'.$data['path'];
    $tmp_path = CC_BACKUP_DIR.$data['file_name'];
    $fp = fopen($tmp_path, 'w');
    fwrite($fp, hex2bin($data['file_data']));
    fclose($fp);

    $zip = new ZipArchive();
    $zip->open($tmp_path);

    $root_path = $destination.'/'.$file['name'];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $file = $zip->statIndex($i);
        if (basename($file['name'])=="config.xml") {
            $install_dir = str_replace(array('/config.xml', CC_ROOT_DIR), '', $root_path);
            break;
        }
    }

    if($zip->extractTo($destination)) {
        $extension_info = array(
            'dir' => $install_dir,
            'file_name' => $data['file_name'],
            'file_id' => $data['file_id'],
            'seller_id' => $data['seller_id'],
            'modified'	=> $data['modified'],
            'name'	=> $data['name']
        );
        // Required for extension updates
        $GLOBALS['db']->insert('CubeCart_extension_info', $extension_info);
        $GLOBALS['config']->set('default_apps', $token, 1);
    }
    $zip->close();

    unlink($tmp_path);
    if($data['file_id']==44) {
        // Free Shipping to get us started
        $config_data = array(
            "module_id" => "7",
            "module" => "shipping",
            "folder" =>  "All_In_One_Shipping",
            "status" =>  "1",
            "default" =>  "0",
            "position" =>  "0",
            "range_weight" =>  "1",
            "tax" =>  "999999",
            "multiple_zones" =>  "first",
            "debug" =>  "0",
            "use_flat" =>  "1",
            "cc5_data" => 1
        );
        $GLOBALS['config']->set("All_In_One_Shipping", '', $config_data);
        $GLOBALS['db']->insert('CubeCart_shipping_rates', array('method_name' => 'Free Shipping', 'max_weight' => 9999, 'item_rate' => 0));
        $GLOBALS['db']->insert('CubeCart_modules', array('module' => 'shipping', 'folder' => "All_In_One_Shipping", 'status' => 1, 'default' => 1));
    }
}

## Delete any remnant hash verification files
$hash_files = glob(CC_ROOT_DIR.'/'.basename(CC_FILES_DIR).'/hash.*.php');
if (is_array($hash_files)) {
    foreach ($hash_files as $file) {
        unlink($file);
    }
}

$GLOBALS['main']->addTabControl($lang['navigation']['nav_plugins'], 'plugins');
$GLOBALS['gui']->addBreadcrumb($lang['navigation']['nav_modules'], '?_g=plugins', true);
if (isset($_GET['delete']) && $_GET['delete']==1 && !empty($_GET['type']) && !empty($_GET['module'])) {
    $dir = CC_ROOT_DIR.'/modules/'.$_GET['type'].'/'.$_GET['module'];
    if (file_exists($dir)) {
        recursiveDelete($dir);
        $GLOBALS['db']->delete('CubeCart_config', array('name' => $_GET['module']));
        $GLOBALS['db']->delete('CubeCart_modules', array('folder' => $_GET['module']));
        $GLOBALS['db']->delete('CubeCart_hooks', array('plugin' => $_GET['module']));
       
        if (file_exists($dir)) {
            $GLOBALS['main']->errorMessage($lang['module']['plugin_still_exists']);
        } else {
            $GLOBALS['main']->successMessage($lang['module']['plugin_deleted_successfully']);
        }
    } else {
        $GLOBALS['db']->delete('CubeCart_config', array('name' => $_GET['module']));
        $GLOBALS['db']->delete('CubeCart_modules', array('folder' => $_GET['module']));
        $GLOBALS['db']->delete('CubeCart_hooks', array('plugin' => $_GET['module']));
        $GLOBALS['main']->successMessage($lang['module']['plugin_deleted_already']);
    }
    httpredir('?_g=plugins');
}
if (isset($_POST['plugin_token']) && !empty($_POST['plugin_token'])) {
    $token = str_replace('-', '', $_POST['plugin_token']);
    
    $json 	= false;
    $cc_domain = 'www.cubecart.com';
    $cc_get_path 	= '/extensions/token/'.$token.'/get';
    $cc_conf_path 	= '/extensions/token/'.$token.'/confirm';
    
    $request = new Request($cc_domain, $cc_get_path, 443, false, true, 10);
    $request->setMethod('get');
    $request->setSSL();
    $request->setUserAgent('CubeCart');
    $request->skiplog(true);

    if (!$json = $request->send()) {
        $json = file_get_contents('https://'.$cc_domain.$cc_get_path);
    }

    if ($json && !empty($json)) {
        $data = json_decode($json, true);
        if ($data && !empty($data['file_name']) && !empty($data['file_data'])) {
            $destination = CC_ROOT_DIR.'/'.$data['path'];
            if (file_exists($destination)) {
                if (is_writable($destination)) {
                    $tmp_path = CC_BACKUP_DIR.$data['file_name'];
                    $fp = fopen($tmp_path, 'w');
                    fwrite($fp, hex2bin($data['file_data']));
                    fclose($fp);
                    if (!file_exists($tmp_path)) {
                        $GLOBALS['main']->errorMessage($lang['module']['get_file_failed']);
                    }
                    $zip = new ZipArchive();

                    if ($zip->open($tmp_path)===true) {
                        $extract = true;
                        $backup = false;
                        $import_language = false;

                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            $file = $zip->statIndex($i);
                            if (preg_match('/^email_[a-z]{2}-[A-Z]{2}.xml$/', $file['name'])) {
                                $import_language = $file['name'];
                                $install_dir = str_replace('email_', '', $file['name']);
                            }

                            $root_path = $destination.'/'.$file['name'];
                            
                            if (file_exists($root_path) && basename($file['name'])=="config.xml") {
                                // backup existing
                                $backup = str_replace('config.xml', '', $file['name'])."*";
                            }
                            if (basename($file['name'])=="config.xml") {
                                $install_dir = str_replace(array('/config.xml', CC_ROOT_DIR), '', $root_path);
                            }

                            if (file_exists($root_path) && !is_writable($root_path)) {
                                $GLOBALS['main']->errorMessage(sprintf($lang['module']['exists_not_writable'], $root_path));
                                $extract = false;
                            }
                        }
        
                        if ($_POST['backup']=='1' && $backup) {
                            $destination_filepath = CC_BACKUP_DIR.rtrim($data['file_name'], '.zip').'_'.date("dMy-His").'.zip';
                            $zip_backup = new ZipArchive();
                            if ($zip_backup->open($destination_filepath, ZipArchive::CREATE)===true) {
                                chdir($destination);
                                $files = glob_recursive($backup);
                                foreach ($files as $file) {
                                    if (is_dir($file)) {
                                        $zip_backup->addEmptyDir($file);
                                    } else {
                                        $zip_backup->addFile($file);
                                    }
                                }
                                $zip_backup->close();
                                if (file_exists($destination_filepath)) {
                                    $GLOBALS['main']->successMessage($lang['module']['backup_created']);
                                } else {
                                    if ($_POST['abort']=='1') {
                                        $extract = false;
                                        $GLOBALS['main']->errorMessage(sprintf($lang['module']['exists_not_writable'], $destination_filepath).' '.$lang['module']['process_aborted']);
                                    } else {
                                        $GLOBALS['main']->errorMessage(sprintf($lang['module']['exists_not_writable'], $destination_filepath));
                                    }
                                }
                            } else {
                                $GLOBALS['main']->errorMessage(sprintf($lang['module']['exists_not_writable'], $destination_filepath));
                            }
                        }
                        if ($extract) {
                            $zip->extractTo($destination);
                            $zip->close();
                            // Attempt email template install
                            if ($import_language) {
                                $GLOBALS['language']->importEmail($import_language);
                            }

                            if (!empty($install_dir)) {
                                $extension_info = array(
                                    'dir' => $install_dir,
                                    'file_name' => $data['file_name'],
                                    'file_id' => $data['file_id'],
                                    'seller_id' => $data['seller_id'],
                                    'modified'	=> $data['modified'],
                                    'name'	=> $data['name']
                                );

                                if ($GLOBALS['db']->select('CubeCart_extension_info', 'file_id', array('file_id' => $extension_info['file_id']))) {
                                    $GLOBALS['db']->update('CubeCart_extension_info', $extension_info, array('file_id' => $extension_info['file_id']));
                                } else {
                                    $GLOBALS['db']->insert('CubeCart_extension_info', $extension_info);
                                }
                            }

                            $GLOBALS['session']->delete('version_check');

                            $GLOBALS['main']->successMessage($lang['module']['success_install']);
                            
                            $request = new Request($cc_domain, $cc_conf_path, 80, false, true, 10);
                            $request->setMethod('get');
                            $request->setSSL();
                            $request->setUserAgent('CubeCart');
                            $request->skiplog(true);
                        }
                    } else {
                        $GLOBALS['main']->errorMessage(sprintf($lang['module']['read_fail'], $data['file_name']));
                    }
                } else {
                    $GLOBALS['main']->errorMessage(sprintf($lang['module']['not_writable'], $destination));
                }
            } else {
                $GLOBALS['main']->errorMessage(sprintf($lang['module']['not_exist'], $destination));
            }
        } else {
            $GLOBALS['main']->errorMessage($lang['module']['package_fail']);
        }
    } else {
        $GLOBALS['main']->errorMessage($lang['module']['token_unknown']);
    }
    httpredir('?_g=plugins');
}

if (isset($_POST['status'])) {
    $before = md5(serialize($GLOBALS['db']->select('CubeCart_modules')));

    foreach ($_POST['status'] as $module_name => $status) {
        $module_type = $_POST['type'][$module_name];
        
        if ($module_type=='plugins') {
            if ($status) {
                $GLOBALS['hooks']->install($module_name);
            } else {
                $GLOBALS['hooks']->uninstall($module_name);
            }
        }
        // Make any changes
        if ($GLOBALS['db']->select('CubeCart_modules', array('module_id'), array('folder' => $module_name, 'module' => $module_type))) {
            $GLOBALS['db']->update('CubeCart_modules', array('status' => (int)$status), array('folder' => $module_name, 'module' => $module_type));
        } else {
            $GLOBALS['db']->insert('CubeCart_modules', array('status' => (int)$status, 'folder' => $module_name, 'module' => $module_type));
        }
        
        // Update config
        $GLOBALS['config']->set($module_name, 'status', $status);
    }
    $after = md5(serialize($GLOBALS['db']->select('CubeCart_modules')));
    if ($before !== $after) {
        $GLOBALS['gui']->setNotify($lang['module']['notify_module_status']);
    }
    
    httpredir('?_g=plugins');
}



$module_paths = glob("modules/*/*/config.xml");
$i=0;
$modules = false;
foreach ($module_paths as $module_path) {
    try {
        $xml   = new SimpleXMLElement(file_get_contents($module_path));
    } catch (Exception $e) {
        trigger_error($e, E_USER_WARNING);
    }
    if (is_object($xml)) {
        $basename = (string)basename(str_replace('config.xml', '', $module_path));
        $key = trim((string)$xml->info->name.$i);
        
        $module_config = $GLOBALS['db']->select('CubeCart_modules', '*', array('folder' => $basename, 'module' => (string)$xml->info->type));

        $modules[$key] = array(
            'uid' 				=> (string)$xml->info->uid,
            'type' 				=> (string)$xml->info->type,
            'mobile_optimized' 	=> (string)$xml->info->mobile_optimized,
            'name' 				=> str_replace('_', ' ', (string)$xml->info->name),
            'description' 		=> (string)$xml->info->description,
            'version' 			=> (string)$xml->info->version,
            'minVersion' 		=> (string)$xml->info->minVersion,
            'maxVersion' 		=> (string)$xml->info->maxVersion,
            'creator' 			=> (string)$xml->info->creator,
            'homepage' 			=> (string)$xml->info->homepage,
            'block' 			=> (string)$xml->info->block,
            'basename' 			=> $basename,
            'config'			=> $module_config[0],
            'edit_url'			=> '?_g=plugins&type='.(string)$xml->info->type.'&module='.$basename,
            'delete_url'		=> '?_g=plugins&type='.(string)$xml->info->type.'&module='.$basename.'&delete=1&token='.SESSION_TOKEN

        );
        $i++;
        unset($xml);
    }
}

if (is_array($modules)) {
    ksort($modules);
}
    
$GLOBALS['smarty']->assign('MODULES', $modules);

$languages_installed = $GLOBALS['language']->listLanguages();

if (is_array($languages_installed)) {
    $language_modules = array();
    $enabled = $GLOBALS['config']->get('languages');
    foreach ($languages_installed as $key => $value) {
        $language_modules[$key] = array(
            'status' 		=> (isset($enabled[$key])) ? (int)$enabled[$key] : 1,
            'lang_code' 	=> $key,
            'name' 			=> $value['title'],
            'type' 			=> 'language',
            'edit_url'		=> '?_g=settings&node=language&language='.$key,
            'delete_url' 	=> '?_g=settings&node=language&delete='.$key.'&token='.SESSION_TOKEN
        );
        $i++;
    }
    $GLOBALS['smarty']->assign('LANGUAGES', $language_modules);
}

$skins = $GLOBALS['gui']->listSkins();

if (is_array($skins)) {
    $GLOBALS['smarty']->assign('SKINS', $skins);
}

$page_content = $GLOBALS['smarty']->fetch('templates/plugins.index.php');
