<?php
$config_string = $db->select('CubeCart_config', array('array'), array('name' => 'config'));
$config = json_decode(base64_decode($config_string[0]['array']), true);
$added_config = array('seo_ext' => '.html');
$db->update('CubeCart_config', array('array' => base64_encode(json_encode(array_merge($config, $added_config)))), array('name'=>'config'));

$filename = CC_ROOT_DIR.'/.htaccess';
$content = file_get_contents($filename);
$content_chunks = explode('^(.*)\.html?', $content);
$content = implode('^(.*)?', $content_chunks);
file_put_contents($filename, $content);