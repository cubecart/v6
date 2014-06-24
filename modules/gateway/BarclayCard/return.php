<?php
/* Barclaycard doesn't allow return to URL containing GET variables. So we have to trick it... */ 
if (isset($_GET['oid'])) {
	preg_match('#^(.*)/modules#iu', $_SERVER['REQUEST_URI'], $matches);
	header('Location: '.$matches[1].'/index.php?_g=rm&type=gateway&cmd=process&module=BarclayCard&cart_order_id='.$_GET['oid'], true, 301);
	die;
}
?>