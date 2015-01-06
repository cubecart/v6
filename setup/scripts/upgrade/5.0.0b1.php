<?php
## Update homepage data
$short_lang_identifier = (empty($_SESSION['setup']['short_lang_identifier'])) ? 'en' : $_SESSION['setup']['short_lang_identifier'];

switch ($short_lang_identifier) {
case 'cn':
	$_SESSION['setup']['long_lang_identifier'] = 'cn-CN';
	break;
case 'de':
	$_SESSION['setup']['long_lang_identifier'] = 'de-DE';
	break;
case 'dk':
	$_SESSION['setup']['long_lang_identifier'] = 'dk-DK';
	break;
case 'en':
	$_SESSION['setup']['long_lang_identifier'] = 'en-GB';
	break;
case 'es':
	$_SESSION['setup']['long_lang_identifier'] = 'es-ES';
	break;
case 'fi':
	$_SESSION['setup']['long_lang_identifier'] = 'fi-FI';
	break;
case 'fr':
	$_SESSION['setup']['long_lang_identifier'] = 'fr-FR';
	break;
case 'it':
	$_SESSION['setup']['long_lang_identifier'] = 'it-IT';
	break;
case 'nl':
	$_SESSION['setup']['long_lang_identifier'] = 'nl-NL';
	break;
case 'no':
	$_SESSION['setup']['long_lang_identifier'] = 'no-NO';
	break;
case 'pl':
	$_SESSION['setup']['long_lang_identifier'] = 'pl-PL';
	break;
case 'pt':
	$_SESSION['setup']['long_lang_identifier'] = 'pt-PT';
	break;
case 'se':
	$_SESSION['setup']['long_lang_identifier'] = 'se-SE';
	break;
case 'sk':
	$_SESSION['setup']['long_lang_identifier'] = 'sk-SK';
	break;
default:
	$_SESSION['setup']['long_lang_identifier'] = 'en-GB';
}

if ($homepage = $db->select('CubeCart_lang', false, array('identifier' => '/'.$short_lang_identifier.'/home.inc.php'))) {
	$data = unserialize($homepage[0]['langArray']);
	$record = array(
		'doc_lang'  => $_SESSION['setup']['long_lang_identifier'],
		'doc_home'  => 1,
		'doc_name'  => $data['title'],
		'doc_content' => str_replace('images/uploads', 'images/source', $data['copy']),
		'seo_meta_title'  => $data['doc_metatitle'],
		'seo_meta_description' => $data['doc_metadesc'],
		'seo_meta_keywords'  => $data['doc_metakeywords'],
	);
	$db->insert('CubeCart_documents', $record);
} else {
	$lang_path = CC_ROOT_DIR.'/language/'.$short_lang_identifier.'/'.'home.inc.php';
	if (file_exists($lang_path)) {
		include $lang_path;
		$record = array(
			'doc_lang'  => $_SESSION['setup']['long_lang_identifier'],
			'doc_home'  => 1,
			'doc_name'  => $home['title'],
			'doc_content' => str_replace('images/uploads', 'images/source', $home['copy']),
		);
		$db->insert('CubeCart_documents', $record);
	}
}

$from = CC_ROOT_DIR.'/images/uploads';
$to  = CC_ROOT_DIR.'/images/source';

$regex_slash_keep = '#[^\w\.\-\_\/]#i';
$regex_slash_remove = '#[^\w\.\-\_]#i';

## Delete the images/source/ folder from the upload (it should be empty)
if (file_exists($to)) {
	recursiveDelete($to);
}

if (file_exists($from) && is_writable(dirname($from))) {
	if (!file_exists($to) && rename($from, $to)) {
		## Delete thumbs/ dir recursively
		recursiveDelete($to.'/'.'thumbs');
	} else {
		## NOTIFY: They need to update manually
		$errors[] = $strings['setup']['error_rename_images'];
	}
}

## rename images and folder acordingly
function update_image_paths($pattern, $flags = 0) {
	global $regex_slash_keep;
	foreach (glob($pattern, $flags) as $filename) {
		rename($filename, preg_replace($regex_slash_keep, '_', $filename));
	}

	foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
		update_image_paths($dir.'/'.basename($pattern), $flags);
	}

}
update_image_paths(CC_ROOT_DIR.'/images/source/*');

## Format filenames
include $global_file; // Just to make sure we have it
$product_files = $db->select('CubeCart_inventory', array('image', 'product_id'));
if ($product_files) {
	foreach ($product_files as $file) {
		$db->misc("UPDATE `".$glob['dbprefix']."CubeCart_inventory` SET `image` = '".preg_replace($regex_slash_keep, '_', $file['image'])."' WHERE `product_id` = '".$file['product_id']."'");
	}
}

$category_files = $db->select('CubeCart_category', array('cat_image', 'cat_id'));
if ($category_files) {
	foreach ($category_files as $file) {
		$db->misc("UPDATE `".$glob['dbprefix']."CubeCart_category` SET `cat_image` = '".preg_replace($regex_slash_keep, '_', $file['cat_image'])."' WHERE `cat_id` = '".$file['cat_id']."'");
	}
}

$filemanager_files = $db->select('CubeCart_filemanager', array('filepath', 'filename', 'file_id'));
if ($filemanager_files) {
	foreach ($filemanager_files as $file) {
		$new_file_path = preg_replace($regex_slash_keep, '_', $file['filepath']);
		$new_file_path = (empty($new_file_path)) ? 'NULL' : "'".$new_file_path."'";
		$db->misc("UPDATE `".$glob['dbprefix']."CubeCart_filemanager` SET `filepath` = ".$new_file_path.", `filename` = '".preg_replace($regex_slash_remove, '_', $file['filename'])."' WHERE `file_id` = '".$file['file_id']."'");
	}
}
unset($product_files, $category_files, $filemanager_files);

## Update FileManager table first
$fm = new FileManager();
$fm->buildDatabase();

$config_string = $db->select('CubeCart_config', array('array'), array('name' => 'config'));
$v4config = json_decode(base64_decode($config_string[0]['array']), true);
foreach (glob('../images/logos/*') as $file) {
	if (!preg_match('/[.][a-z]{3}/', $file, $match)) {
		$mime = $fm->getMimeType($file);
		$size = getimagesize($file);
		if (preg_match('/(png|jpg|jpeg|gif)/', $mime, $match)) {
			$match[0] = ($match[0]=='jpeg') ? 'jpg' : $match[0];
			$new_name = $file.'.'.$match[0];
			chmod($file, chmod_writable());
			rename($file, $new_name);
			chmod($new_name, chmod_writable());
		}
		$file_name = preg_replace('/..\/images\/logos\//', '', $file);
		$new_file_name = preg_replace('/..\/images\/logos\//', '', $new_name);
		if ($file_name==$v4config['skinDir']) {
			$db->insert('CubeCart_logo', array('status' => 1, 'filename' => $new_file_name, 'mimetype' => $mime, 'width' => $size[0], 'height' => $size[1]));
		}
	}
}
build_logos($new_file_name);

## Remap category images
if ($indexes = $db->select('CubeCart_category', array('cat_id', 'cat_image'))) {
	foreach ($indexes as $index) {

		if (empty($index['cat_image'])) continue;

		$filename = basename($index['cat_image']);
		$filepath = str_replace($filename, '', $index['cat_image']);

		unset($where);

		if (empty($filepath)) {
			$where = "`filepath` IS NULL AND `filename` = '$filename'";
		} else {
			$where['filepath'] = $filepath;
			$where['filename'] = $filename;
		}

		if ($reference = $db->select('CubeCart_filemanager', 'file_id', $where)) {
			$db->update('CubeCart_category', array('cat_image' => $reference[0]['file_id']) , array('cat_id' => $index['cat_id']));
		}
	}
}

## Create new image indexes for main images
if ($indexes = $db->select('CubeCart_inventory', array('product_id', 'image'))) {
	foreach ($indexes as $index) {

		if (empty($index['image'])) continue;

		$product_id = (int)$index['product_id'];

		$filename = basename($index['image']);
		$filepath = str_replace($filename, '', $index['image']);

		unset($where);

		if (empty($filepath)) {
			$where = "`filepath` IS NULL AND `filename` = '$filename'";
		} else {
			$where['filepath'] = $filepath;
			$where['filename'] = $filename;
		}

		if ($reference = $db->select('CubeCart_filemanager', 'file_id', $where)) {
			$record = array(
				'file_id'  => $reference[0]['file_id'],
				'product_id' => $product_id,
				'main_img'  => '1'
			);
			if (!$db->select('CubeCart_image_index', false, array('product_id' => $record['product_id'], 'file_id' => $record['file_id']))) {
				$db->insert('CubeCart_image_index', $record);
			}
		}
	}
}
## Insert email templates
$GLOBALS['db']->parseSchema(file_get_contents('db/install/email.sql', false));

## Remap store country from id to numcode
$country = $db->select('CubeCart_geo_country', array('numcode'), array('id' => $v4config['siteCountry']));
Config::getInstance()->set('config', 'store_country', $country[0]['numcode']);