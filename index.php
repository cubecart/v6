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
require dirname(__FILE__).DIRECTORY_SEPARATOR.'ini.inc.php';
define('CC_IN_ADMIN', false);

header('X-Frame-Options: SAMEORIGIN'); // do not allow iframes

global $config_default;
// Include external controllers
$allowed_ec = array('es'); // Only Elastic Search right now
if(isset($_GET['_e']) && in_array($_GET['_e'],$allowed_ec)) {
    $ec_path = CC_ROOT_DIR.'/controllers/controller.'.$_GET['_e'].'.inc.php';
    if(file_exists($ec_path)) {
        require_once($ec_path);
    }
} 
include CC_ROOT_DIR.CC_DS.'controllers'.CC_DS.'controller.index.inc.php';
$GLOBALS['gui']->display('templates/'.$global_template_file);