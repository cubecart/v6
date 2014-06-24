<?php
if($GLOBALS['session']->get('stage', 'amazon')=='wallet') {
	$status	= 2;
} elseif($GLOBALS['session']->get('stage', 'amazon')=='complete') {
	$status	= 3;
} elseif($GLOBALS['session']->has('purchaseContractId','amazon') && $GLOBALS['session']->get('purchaseContractId', 'amazon')!=='') {
	$status	= 1;
}