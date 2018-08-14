<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
## Don't let anything be cached
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: pre-check=0, post-check=0, max-age=0');
header("Expires: -1");
header("Pragma: no-cache");
header('X-Frame-Options: SAMEORIGIN'); // Do NOT allow iframes

## Include the ini file (required)
require 'ini.inc.php';

if (basename(__FILE__)!==$glob['adminFile']) {
    header('HTTP/1.1 404 Not Found');
    exit;
}

define('CC_IN_ADMIN', true);

## Include core functions
require 'includes/functions.inc.php';

## Include admin presession controller
include CC_ROOT_DIR.CC_DS.'controllers'.CC_DS.'controller.admin.pre_session.inc.php';

$feed_access_key = $GLOBALS['config']->get('config', 'feed_access_key');
$feed_access_key = (!$feed_access_key) ? '' : $feed_access_key;

if (Admin::getInstance()->is() || (isset($_GET['_g']) && $_GET['_g']=='products' && $_GET['node']=='export' && !empty($_GET['format']) && $_GET['access']==$feed_access_key && !empty($feed_access_key))) {
    error_reporting(E_ALL ^ (E_NOTICE | E_DEPRECATED | E_USER_DEPRECATED));
    include CC_ROOT_DIR.CC_DS.'controllers'.CC_DS.'controller.admin.session.true.inc.php';
} else {
    include CC_ROOT_DIR.CC_DS.'controllers'.CC_DS.'controller.admin.session.false.inc.php';
    $GLOBALS['gui']->display('templates/'.$global_template_file['session_false']);
    exit;
}
if (isset($_GET['response']) && $_GET['response'] == 'token') {
    $GLOBALS['debug']->supress(true);
    die(SESSION_TOKEN);
}
// Render the completed page
if (!isset($suppress_output) || !$suppress_output) {
    $GLOBALS['gui']->displayCommon();
    $GLOBALS['gui']->display('templates/'.$global_template_file['session_true']);
}
