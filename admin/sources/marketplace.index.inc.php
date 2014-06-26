<?php
if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('settings', CC_PERM_FULL, true);
global $lang, $glob;
$store_id = $GLOBALS['config']->get('marketplace','store_id');
if(empty($store_id)) {
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
	$store_id	= md5(time().((floor($sum/10)+1)*10-$sum)%10);
	$GLOBALS['config']->set('marketplace','store_id',$store_id);
}
$url_parts = parse_url(CC_STORE_URL);
httpredir('https://marketplace.cubecart.com/index.php?store_id='.$store_id.'&domain='.$url_parts['host']);