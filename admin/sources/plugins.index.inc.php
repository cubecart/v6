<?php
if (!defined('CC_INI_SET')) die('Access Denied');

global $lang, $glob;

$GLOBALS['main']->addTabControl('Installed Plugins', 'plugins');

if(!$modules = $GLOBALS['cache']->read('module_list')) {
	$module_paths = glob("modules/*/*/config.xml");
	$i=0;
	foreach ($module_paths as $module_path) {
	
		$xml   = new SimpleXMLElement(file_get_contents($module_path));
		$config = $GLOBALS['db']->select('CubeCart_modules','*',array('folder' => (string)$xml->info->name, 'module' => (string)$xml->info->type));
		
		$key = trim((string)$xml->info->name.$i);
		$modules[$key] = array(
			'uid' 				=> (string)$xml->info->uid,
			'type' 				=> (string)$xml->info->type,
			'mobile_optimized' 	=> (string)$xml->info->mobile_optimized,
			'name' 				=> (string)$xml->info->name,
			'description' 		=> (string)$xml->info->description,
			'version' 			=> (string)$xml->info->version,
			'minVersion' 		=> (string)$xml->info->minVersion,
			'maxVersion' 		=> (string)$xml->info->maxVersion,
			'creator' 			=> (string)$xml->info->creator,
			'homepage' 			=> (string)$xml->info->homepage,
			'block' 			=> (string)$xml->info->block,
			'basename' 			=> (string)basename(str_replace('config.xml','', $module_path)),
			'config'			=> $config[0]
		);
		
		$i++;
		
	}
	ksort($modules);
	$GLOBALS['cache']->write($modules, 'module_list');
}
$GLOBALS['smarty']->assign('MODULES',$modules);
$page_content = $GLOBALS['smarty']->fetch('templates/plugins.index.php');