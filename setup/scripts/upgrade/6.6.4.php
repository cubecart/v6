<?php
// Remove obsolete reCAPTCHA v1 library (no longer referenced)
$recaptcha_dir = CC_INCLUDES_DIR.'lib/recaptcha';
if (is_dir($recaptcha_dir)) {
    recursiveDelete($recaptcha_dir);
}
