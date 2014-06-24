<?php
	if(!defined('CC_INI_SET')) die('Access Denied');
	require_once('modules/plugins/reviewscouk_export/class.reviewscouk_export.php');
	
	$reviewscouk_export = new reviewscouk_export(null);
	$reviewscouk_export->processInvite($order_id);
?>
