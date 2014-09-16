<?php  
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */

$status = $_GET['status'];
$orderid = $_GET['orderid'];

preg_match('#^(.*)/modules#iu', $_SERVER['REQUEST_URI'], $matches);
header('Location: '.$matches[1].'/index.php?_g=rm&type=gateway&cmd=process&module=StrongBox&cart_order_id='.$orderid.'&status='. $status , true, 301);
	die;
?>