<?php
// Smarty moved to includes/smarty; reCAPTCHA v1 lib removed. Drop the now-empty includes/lib dir.
$old_lib_dir = CC_INCLUDES_DIR.'lib';
if (is_dir($old_lib_dir)) {
    recursiveDelete($old_lib_dir);
}
