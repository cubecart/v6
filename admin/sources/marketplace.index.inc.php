<?php
if (!defined('CC_INI_SET')) die('Access Denied');

global $lang, $glob;

$GLOBALS['main']->addTabControl($lang['navigation']['nav_marketplace'], 'marketplace');
$GLOBALS['main']->addTabControl('Installed Plugins', 'plugins');

if(!$modules = $GLOBALS['cache']->read('module_list')) {
	$module_xmls = glob("modules/*/*/config.xml");
	foreach ($module_xmls as $module_xml) {
		$xml   = new SimpleXMLElement(file_get_contents($module_xml));
		$config = $GLOBALS['db']->select('CubeCart_modules','*',array('folder' => (string)$xml->info->name, 'module' => (string)$xml->info->type));
		$modules[] = array(
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
			'config'			=> $config[0]
		);
	}
	$GLOBALS['cache']->write($modules, 'module_list');
}
$GLOBALS['smarty']->assign('MODULES',$modules);
$page_content = $GLOBALS['smarty']->fetch('templates/marketplace.index.php');