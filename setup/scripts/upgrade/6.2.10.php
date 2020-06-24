<?php
$config_string = $db->select('CubeCart_config', array('array'), array('name' => 'gift_certs'));
$config = json_decode(base64_decode($config_string[0]['array']), true);
if(is_numeric($config['product_code'])) {
    $added_config = array('product_code' => 'GC'.$config['product_code']);
    $db->update('CubeCart_config', array('array' => base64_encode(json_encode(array_merge($config, $added_config)))), array('name'=>'gift_certs'));
}
