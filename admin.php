<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
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

//=====[ Load ]====================================================================================================
include CC_ROOT_DIR.CC_DS.'controllers'.CC_DS.'controller.admin.pre_session.inc.php';

$feed_access_key = $GLOBALS['config']->get('config','feed_access_key');
$feed_access_key = (!$feed_access_key) ? '' : $feed_access_key;

if (Admin::getInstance()->is() || (isset($_GET['_g']) && $_GET['_g']=='products' && $_GET['node']=='export' && !empty($_GET['format']) && $_GET['access']==$feed_access_key && !empty($feed_access_key))) {
	include CC_ROOT_DIR.CC_DS.'controllers'.CC_DS.'controller.admin.session.true.inc.php';
} else {
	include CC_ROOT_DIR.CC_DS.'controllers'.CC_DS.'controller.admin.session.false.inc.php';
	$GLOBALS['smarty']->display('templates/'.$global_template_file['session_false']);
	exit;
}
// Render the completed page
if (!isset($suppress_output) || !$suppress_output) {
	$GLOBALS['gui']->displayCommon(true);
	$GLOBALS['smarty']->display('templates/'.$global_template_file['session_true']);
	exit;
}