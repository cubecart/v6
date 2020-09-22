<?php
$config_string = $db->select('CubeCart_config', array('array'), array('name' => 'config'));
$config = json_decode(base64_decode($config_string[0]['array']), true);
$added_config = array('seo_ext' => '.html');
$db->update('CubeCart_config', array('array' => base64_encode(json_encode(array_merge($config, $added_config)))), array('name'=>'config'));

$htaccess_path = CC_ROOT_DIR.'.htaccess';
$content = file_get_contents($htaccess_path);
$content = str_replace('^(.*)\.html?$', '^(.*)?$', $content);
file_put_contents($htaccess_path, $content);