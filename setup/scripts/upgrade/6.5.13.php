<?php
/**
 * CubeCart v6.5.13 Upgrade Script
 *
 * Removes deprecated jQuery Cookie Plugin files that have been replaced
 * with native JavaScript cookie utility (cookies.min.js)
 */

// Old jQuery Cookie Plugin files to remove
$files_to_remove = array(
    // Admin panel
    CC_ROOT_DIR.'/'.$glob['adminFolder'].'/skins/default/js/plugins/jquery.cookie.js',
    // Frontend
    CC_ROOT_DIR.'/skins/foundation/js/vendor/jquery.cookie.js'
);

// Attempt to delete each file
foreach ($files_to_remove as $file) {
    @unlink($file);
}