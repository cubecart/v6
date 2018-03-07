<?php
if (is_array($languages)) {
    foreach ($languages as $code => $lang) {
        $language->importEmail('email_'.$code.'.xml', CC_LANGUAGE_DIR, 'newsletter.verify_email');
    }
}