<?php
if(isset($_GET['purchaseContractId']) && !empty($_GET['purchaseContractId']) && $GLOBALS['user']->getId()>0) {
	$GLOBALS['session']->set('customer_id', $GLOBALS['user']->getId(), 'amazon');
	$GLOBALS['db']->update('CubeCart_sessions', array('customer_id' => 0), array('session_id' => $GLOBALS['session']->getId()));
	httpredir('index.php?_a=basket&purchaseContractId='.$_GET['purchaseContractId']);
}