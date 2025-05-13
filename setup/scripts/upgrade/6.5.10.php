<?php
$cs = $db->select('CubeCart_config', array('array'), array('name' => 'config'));
$c = json_decode(base64_decode($cs[0]['array']), true);
$c['standard_url']  = preg_replace('#^http://#', 'https://', $c['standard_url']);
$db->update('CubeCart_config', array('array' => base64_encode(json_encode($c))), array('name' => 'config'));