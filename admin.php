<?php
## Don't let anything be cached
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: pre-check=0, post-check=0, max-age=0');
header("Expires: -1");
header("Pragma: no-cache");
header('X-Frame-Options: SAME-ORIGIN'); // Do NOT allow iframes

## Include the ini file (required)
require 'ini.inc.php';

define('CC_IN_ADMIN', true);

## Include core functions
require 'includes/functions.inc.php';

include 'controllers/controller.master.inc.php';