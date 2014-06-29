<?php
if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('settings', CC_PERM_FULL, true);
global $lang, $glob;

function generateStoreId() {
	$length	= 30;
	while (strlen($store_id) < ($length-1)) {
		$store_id .= mt_rand(0,9);
	}
	$sum = $pos = 0;
	$reversed	= strrev($store_id);
	while ($pos < $length-1) {
		$odd = $reversed[$pos] * 2;
		if ($odd > 9) $odd -= 9;
		$sum += $odd;
		if ($pos != ($length-2)) {
			$sum += $reversed[$pos+1];
		}
		$pos += 2;
	}
	return md5(time().((floor($sum/10)+1)*10-$sum)%10);
}

$store_id = $GLOBALS['config']->get('marketplace','store_id');
if(empty($store_id)) {
	$store_id = generateStoreId();
	$GLOBALS['config']->set('marketplace','store_id',$store_id);
}
$hash = generateStoreId();
$file = CC_ROOT_DIR.'/files/hash.'.$hash.'.php';
$fp = fopen($file, 'w');
fwrite($fp, '<?php echo "'.$store_id.'"; unlink("'.$file.'"); ?>');
fclose($fp);
httpredir('http://marketplace.cubecart.com/auth?hash='.$hash.'&store_id='.$store_id.'&store_url='.urlencode(CC_STORE_URL));