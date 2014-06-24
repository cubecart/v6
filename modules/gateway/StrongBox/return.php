<?php  
 $status = $_GET['status'];
  $orderid = $_GET['orderid'];

preg_match('#^(.*)/modules#iu', $_SERVER['REQUEST_URI'], $matches);
header('Location: '.$matches[1].'/index.php?_g=rm&type=gateway&cmd=process&module=StrongBox&cart_order_id='.$orderid.'&status='. $status , true, 301);
	die;

  
?>
