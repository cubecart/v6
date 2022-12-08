<?php
$config_string = $db->select('CubeCart_config', array('array'), array('name' => 'config'));
$config = json_decode(base64_decode($config_string[0]['array']), true);
if($config['seo_ext']=='') {
    $db->insert('CubeCart_seo_urls', array(`path` => 'contact-us.html', `type` => 'contact', 'item_id' => 0, 'redirect' => '301'));
    $db->insert('CubeCart_seo_urls', array(`path` => 'gift-certificates.html', `type` => 'certificates', 'item_id' => 0, 'redirect' => '301'));
    $db->insert('CubeCart_seo_urls', array(`path` => 'login.html', `type` => 'login', 'item_id' => 0, 'redirect' => '301'));
    $db->insert('CubeCart_seo_urls', array(`path` => 'recover.html', `type` => 'recover', 'item_id' => 0, 'redirect' => '301'));
    $db->insert('CubeCart_seo_urls', array(`path` => 'register.html', `type` => 'register', 'item_id' => 0, 'redirect' => '301'));
    $db->insert('CubeCart_seo_urls', array(`path` => 'search.html', `type` => 'search', 'item_id' => 0, 'redirect' => '301'));
}