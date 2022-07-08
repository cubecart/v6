<?php
$config_string = $db->select('CubeCart_config', array('array'), array('name' => 'config'));
$config = json_decode(base64_decode($config_string[0]['array']), true);
$config['time_format'] = 'j M Y, H:i';
$config['fuzzy_time_format'] = 'H:i';
$config['dispatch_date_format'] = 'M d Y';
$db->update('CubeCart_config', array('array' => base64_encode(json_encode(array_merge($config, $added_config)))), array('name'=>'config'));
# This code is duplicated in admin/sources/categories.inc.php
function updateCatsWithHierPosition($cat_id = 0, $position = 0){
    global $db;
    if($cat_id == 0){
        $db->update('CubeCart_category', array('cat_hier_position' => 0));
        $cats = $db->select('CubeCart_category', array('cat_id'), array('cat_parent_id' => 0), array('priority' => 'ASC'));
    } else {
        $cats = $db->select('CubeCart_category', array('cat_id'), array('cat_parent_id' => $cat_id), array('priority' => 'ASC'));
    }
    if(isset($cats) && is_array($cats) && !empty($cats)){
        foreach($cats as $cat){
            if($position > 0){
                $db->update('CubeCart_category', array('cat_hier_position' => $position), array('cat_id' => $cat['cat_id']));
            }
            updateCatsWithHierPosition($cat['cat_id'], ($position+1));
        }
    }
}
updateCatsWithHierPosition();