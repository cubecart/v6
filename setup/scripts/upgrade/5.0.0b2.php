<?php
## Split order name into title, first & last
if ($order_names = $db->select('CubeCart_order_summary', array('name', 'cart_order_id'))) {

	foreach ($order_names as $order_name) {
		if (empty($order_name['name'])) {
			continue;
		} else {
			$name_parts = explode(' ', $order_name['name']);
		}

		if (count($name_parts) == 3) {
			$record =  array(
				'title' => $name_parts[0],
				'first_name' => $name_parts[1],
				'last_name' => $name_parts[2]
			);
		} else {
			$record =  array(
				'title' => '',
				'first_name' => $name_parts[0],
				'last_name' => $name_parts[1]
			);
		}
		$db->update('CubeCart_order_summary', $record, array('cart_order_id' => $order_name['cart_order_id']));
		unset($record, $name_parts);
	}
}
## Get SEO paths from seo_custom_url into new SEO DB
if ($products = $db->select('CubeCart_inventory', array('product_id', 'seo_custom_url'))) {
	foreach ($products as $item) {
		if (empty($item['seo_custom_url'])) continue;
		$db->insert('CubeCart_seo_urls', array('path' => sanitizeSEOPath($item['seo_custom_url']), 'item_id' => $item['product_id'], 'type' => 'prod'));
	}
}

if ($categories = $db->select('CubeCart_category', array('cat_id', 'seo_custom_url'))) {
	foreach ($categories as $item) {
		if (empty($item['seo_custom_url'])) continue;
		$db->insert('CubeCart_seo_urls', array('path'=> sanitizeSEOPath($item['seo_custom_url']), 'item_id' => $item['cat_id'], 'type' => 'cat'));
	}
}
## Sort out taxes
if ($tax_rates = $db->select('CubeCart_tax_rates', array('country_id', 'id'))) {
	foreach ($tax_rates as $tax_rate) {
		$country = $db->select('CubeCart_geo_country', array('numcode'), array('id' => $tax_rate['country_id']));
		if ($country[0]['numcode']>0) {
			$db->update('CubeCart_tax_rates', array('country_id' => $country[0]['numcode']), array('id' => $tax_rate['id']));
		}
	}
}