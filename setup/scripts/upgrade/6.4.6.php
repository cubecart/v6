<?php
$config_string = $db->select('CubeCart_config', array('array'), array('name' => 'config'));
$config = json_decode(base64_decode($config_string[0]['array']), true);
$config['time_format'] = 'j M Y, H:i';
$config['fuzzy_time_format'] = 'H:i';
$config['dispatch_date_format'] = 'M d Y';
$db->update('CubeCart_config', array('array' => base64_encode(json_encode(array_merge($config, $added_config)))), array('name'=>'config'));