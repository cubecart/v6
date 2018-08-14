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
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}
Admin::getInstance()->permissions('settings', CC_PERM_FULL, true);
global $lang, $glob;
$hash = randomString();
$file = CC_ROOT_DIR.'/'.basename(CC_FILES_DIR).'/hash.'.$hash.'.php';
$fp = fopen($file, 'w'); fwrite($fp, '<?php echo "'.$hash.'"; unlink("'.$file.'"); ?>'); fclose($fp);
$url = 'https://www.cubecart.com/store/auth/?hash='.$hash.'&amp;url='.urlencode(CC_STORE_URL);
if (isset($_GET['eurl']) && !empty($_GET['eurl'])) {
    $url_parts = parse_url($_GET['eurl']);
    $url .= '&amp;eurl='.urlencode($url_parts['path']);
}
httpredir($url);
