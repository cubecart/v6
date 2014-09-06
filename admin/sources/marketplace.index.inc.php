<?php
if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('settings', CC_PERM_FULL, true);
global $lang, $glob;
$hash = randomString();
$file = CC_ROOT_DIR.'/files/hash.'.$hash.'.php';
$fp = fopen($file, 'w'); fwrite($fp, '<?php echo "'.$hash.'"; unlink("'.$file.'"); ?>'); fclose($fp);
httpredir('https://www2.cubecart.com/store/auth/?hash='.$hash.'&amp;url='.urlencode(CC_STORE_URL));