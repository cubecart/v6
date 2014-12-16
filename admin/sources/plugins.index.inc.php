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
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */
if (!defined('CC_INI_SET')) die('Access Denied');

global $lang, $glob;

$GLOBALS['main']->addTabControl($lang['navigation']['nav_plugins'], 'plugins');
if(isset($_GET['delete']) && $_GET['delete']==1) {
	$dir = CC_ROOT_DIR.'/modules/'.$_GET['type'].'/'.$_GET['module'];
	if(file_exists($dir)) {
	   recursiveDelete($dir);
	   $GLOBALS['db']->delete('CubeCart_config',array('name' => $_GET['module']));
	   $GLOBALS['db']->delete('CubeCart_modules',array('folder' => $_GET['module']));
	   $GLOBALS['db']->delete('CubeCart_hooks',array('plugin' => $_GET['module']));
	   

	   if(file_exists($dir)) {
	   	$GLOBALS['main']->setACPWarning($lang['module']['plugin_still_exists']);
	   } else {
	   	$GLOBALS['main']->setACPNotify($lang['module']['plugin_deleted_successfully']);
	   }
	} else {
		$GLOBALS['main']->setACPNotify($lang['module']['plugin_deleted_already']);
	}
   httpredir('?_g=plugins');
}
if(isset($_POST['plugin_token']) && !empty($_POST['plugin_token'])) {
	$token = str_replace('-','',$_POST['plugin_token']);
	
	$json 	= false;
	$cc_domain = 'www.cubecart.com';
	$cc_get_path 	= '/extensions/token/'.$token.'/get';
	$cc_conf_path 	= '/extensions/token/'.$token.'/confirm';
	
	$request = new Request($cc_domain, $cc_get_path, 80, false, true, 10);
	$request->setMethod('get');
	$request->setSSL();
	$request->setData(array('null'=>0));
	$request->setUserAgent('CubeCart');
	$request->skiplog(true);

	if (!$json = $request->send()) {
		$json = file_get_contents('https://'.$cc_domain.$cc_get_path);
	}

	if($json && !empty($json)) {
		$data = json_decode($json, true);
		$destination = CC_ROOT_DIR.'/'.$data['path'];
		if(file_exists($destination)) {
			if(is_writable($destination)) {
				$tmp_path = CC_ROOT_DIR.'/cache/'.$data['file_name'];
				$fp = fopen($tmp_path, 'w');
				fwrite($fp, hex2bin($data['file_data']));
				fclose($fp);
				if(!file_exists($tmp_path)) {
					$GLOBALS['main']->setACPWarning($lang['module']['get_file_failed']);
				}
				// Read the zip
				require_once CC_INCLUDES_DIR.'lib/pclzip/pclzip.lib.php';
				$source = new PclZip($tmp_path);
				$files = $source->listContent();
				if(is_array($files)) {
					$extract = true;
					$backup = false;
					foreach($files as $file) {
						$root_path = $destination.'/'.$file['filename'];
						
						if(file_exists($root_path) && basename($file['filename'])=="config.xml") {
							// backup existing
							$backup = str_replace('config.xml','',$file['filename'])."*";
						}

						if(file_exists($root_path) && !is_writable($root_path)) {
							$error_path = $data['path'].'/'.$file;
							$GLOBALS['main']->setACPWarning(sprintf($lang['module']['exists_not_writable'],$error_path));
							$extract = false;
						}
					}
	
					if($_POST['backup']=='1' && $backup) {
						$destination_filepath = CC_ROOT_DIR.'/backup/'.$data['file_name'].'_'.date("dMy-His").'.zip';
						$archive = new PclZip($destination_filepath);
						chdir($destination);
						$files = glob($backup);
						foreach ($files as $file) {
							$backup_list[] = $file;
						}
						if ($archive->create($backup_list) == 0) {
							if($_POST['abort']=='1') {
								$extract = false;
								$GLOBALS['main']->setACPWarning($lang['module']['exists_not_writable'].' '.$lang['module']['process_aborted']);
							} else {
								$GLOBALS['main']->setACPWarning($lang['module']['exists_not_writable']);
							}
						} else {
							$GLOBALS['main']->setACPNotify($lang['module']['backup_created']);
						}
					}
					if($extract) {
						if ($source->extract(PCLZIP_OPT_PATH, $destination, PCLZIP_OPT_REPLACE_NEWER) == 0) {
							$GLOBALS['main']->setACPWarning($lang['module']['failed_install']);	
						} else {
							$GLOBALS['main']->setACPNotify($lang['module']['success_install']);
							
							$request = new Request($cc_domain, $cc_conf_path, 80, false, true, 10);
							$request->setMethod('get');
							$request->setSSL();
							$request->setData(array('null'=>0));
							$request->setUserAgent('CubeCart');
							$request->skiplog(true);
							if(!$request->send()) {
								file_get_contents($cc_domain.$cc_conf_path);
							}
						}
					}
				} else {
					$GLOBALS['main']->setACPWarning(sprintf($lang['module']['read_fail'],$data['file_name']));
				}
			} else {
				$GLOBALS['main']->setACPWarning(sprintf($lang['module']['not_writable'], $destination));
			}
		} else {
			$GLOBALS['main']->setACPWarning(sprintf($lang['module']['not_exist'], $destination));
		}
	} else {
		$GLOBALS['main']->setACPWarning($lang['module']['token_unknown']);
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
		// Delete to prevent potential duplicate nightmare
		$GLOBALS['db']->delete('CubeCart_modules',array('folder' => $module_name, 'module' => $module_type)); 
		$GLOBALS['db']->insert('CubeCart_modules', array('status' => (int)$status, 'folder' => $module_name, 'module' => $module_type));
		
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

	$xml   = new SimpleXMLElement(file_get_contents($module_path));
	
	$basename = (string)basename(str_replace('config.xml','', $module_path));
	$key = trim((string)$xml->info->name.$i);
	
	$module_config = $GLOBALS['db']->select('CubeCart_modules','*',array('folder' => $basename, 'module' => (string)$xml->info->type));

	$modules[$key] = array(
		'uid' 				=> (string)$xml->info->uid,
		'type' 				=> (string)$xml->info->type,
		'mobile_optimized' 	=> (string)$xml->info->mobile_optimized,
		'name' 				=> str_replace('_',' ',(string)$xml->info->name),
		'description' 		=> (string)$xml->info->description,
		'version' 			=> (string)$xml->info->version,
		'minVersion' 		=> (string)$xml->info->minVersion,
		'maxVersion' 		=> (string)$xml->info->maxVersion,
		'creator' 			=> (string)$xml->info->creator,
		'homepage' 			=> (string)$xml->info->homepage,
		'block' 			=> (string)$xml->info->block,
		'basename' 			=> $basename,
		'config'			=> $module_config[0]
	);
	$i++;
}

if(is_array($modules)) {
	ksort($modules);
}
	
$GLOBALS['smarty']->assign('MODULES',$modules);
$page_content = $GLOBALS['smarty']->fetch('templates/plugins.index.php');