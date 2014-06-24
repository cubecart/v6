<?php
if(!defined('CC_INI_SET')) die('Access Denied');
$customer_id = $GLOBALS['session']->get('customer_id', 'amazon');
if($customer_id > 0) {
	$GLOBALS['db']->update('CubeCart_sessions', array('customer_id' => $customer_id), array('session_id' => $GLOBALS['session']->getId()));
}
$GLOBALS['session']->delete('', 'amazon');