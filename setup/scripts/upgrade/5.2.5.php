<?php
## Fix digital download thanks to Brian Smither (Smither Consulting, LLC)
$database_download_repairs = $db->select('CubeCart_downloads', array('digital_id', 'cart_order_id', 'product_id'), array('order_inv_id' => 0) );

if ($database_download_repairs !== false ) {
	foreach ($database_download_repairs as $repair) {
		$database_order_inventory_id = $db->select('CubeCart_order_inventory', array('id'), array('cart_order_id' => $repair['cart_order_id'], 'product_id' => $repair['product_id']));
		$download_repaired = $GLOBALS['db']->update('CubeCart_downloads', array('order_inv_id' => $database_order_inventory_id[0]['id']), array('digital_id' => $repair['digital_id']));
	}
}