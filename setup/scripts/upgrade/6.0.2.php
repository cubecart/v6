<?php
## Delete "unordered" js files to prevent duplication
$js_path = CC_ROOT_DIR.'/skins/foundation/js/';

$files = array('foundation.min.js', 'cubecart.js', 'cubecart.validate.js');
foreach ($files as $file) {
    @unlink($js_path.$file);
}
foreach ($files as $file) {
    if (file_exists($js_path.$file)) {
        $errors[] = 'Please delete the file skins/foundation/js/'.$file;
    }
}
