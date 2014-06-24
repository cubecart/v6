<?php
if (!defined('CC_INI_SET')) die('Access Denied');

if (Admin::getInstance()->is()) {
	Admin::getInstance()->permissions('products', CC_PERM_READ, true);
}

global $lang;

$seo  = SEO::getInstance();
$catalogue = Catalogue::getInstance();

$per_page = (isset($_GET['per_page'])) ? $_GET['per_page'] : 500;
$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
//$no_rows = $GLOBALS['db']->numrows('SELECT `product_id` FROM '.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_inventory');
$no_rows = $GLOBALS['db']->numrows(sprintf('SELECT I.product_id FROM %1$sCubeCart_inventory AS I LEFT JOIN %1$sCubeCart_category AS C ON I.cat_id = C.cat_id WHERE I.status = 1', $GLOBALS['config']->get('config', 'dbprefix')));

function download_parts($format = 'cubecart', $no_rows, $per_page) {
	$no_pages = ceil($no_rows / $per_page);
	## If there are no pages (less that per page) we need page 1 for an export
	$no_pages = ($no_pages) ? $no_pages : 1;
	$html_out = null;
	for ($i = 1; $i <= $no_pages; ++$i) {
		$html_out .= '<a href="?_g=products&node=export&page='.$i.'&per_page='.$per_page.'&format='.$format.'">'.$i.'</a> ';
	}
	return $html_out;
}

if (isset($_GET['format']) && !empty($_GET['format'])) {
	if ($_GET['format'] == 'cubecart') {
		$query = sprintf('SELECT I.* FROM %1$sCubeCart_inventory AS I INNER JOIN %1$sCubeCart_category_index AS R ON I.product_id = R.product_id LEFT JOIN %1$sCubeCart_category AS C ON R.cat_id = C.cat_id WHERE R.primary = 1 AND I.status = 1', $GLOBALS['config']->get('config', 'dbprefix'));
	} else {
		$query = sprintf('SELECT I.* FROM %1$sCubeCart_inventory AS I LEFT JOIN %1$sCubeCart_category AS C ON I.cat_id = C.cat_id WHERE I.status = 1', $GLOBALS['config']->get('config', 'dbprefix'));
	}

	if ($results = $GLOBALS['db']->query($query, $per_page, $page)) {
		switch (strtolower($_GET['format'])) {
		case 'googlebase':
		case 'storeya':
			$header_fields = array('id', 'product_type', 'google_product_category', 'link', 'title', 'description', 'image_link', 'price', 'condition', 'shipping_weight', 'upc', 'ean', 'jan', 'isbn', 'availability', 'brand', 'gtin', 'mpn', 'identifier_exists');
			$fields  = array('product_id', 'store_category', 'google_category', 'url', 'name', 'description', 'image', 'price', 'condition', 'product_weight', 'upc', 'ean', 'jan', 'isbn', 'availability', 'manufacturer', 'gtin', 'mpn', 'identifier_exists');
			$delimiter = "\t";
			$extension = 'txt';
			$glue  = "\r\n";
			$field_wrapper = '"';
			$field_keys_to_wrap = array('description');
			$image_path = 'url';
			$image_mode = 'medium';
			break;
		case 'shopzilla':
			$header_fields = array('Category', 'Manufacturer', 'Title', 'Product Description', 'Link', 'Image', 'SKU', 'Stock', 'Condition', 'Shipping Weight', 'Shipping Cost', 'Bid', 'Promotional Description', 'EAN/UPC', 'Price');
			$fields  = array('', 'manufacturer', 'name', 'description', 'url', 'image', 'product_code', 'In Stock', 'New', 'weight', '', '', '', 'upc', 'price');
			$delimiter = "\t";
			$extension = 'txt';
			$glue  = "\n";
			$field_wrapper = '"';
			$field_keys_to_wrap = $fields;
			$image_path = 'url';
			$image_mode = 'medium';
			break;
		case 'shopping.com':
			$header_fields = array('condition', 'unique merchant sku', 'mpn', 'upc', 'manufacturer', 'product name', 'product description', 'price', 'stock', 'stock description', 'product url', 'image url', 'category', 'shipping_rate');
			$fields  = array('condition', 'product_code', 'product_code', 'upc', 'manufacturer', 'name', 'description', 'price', 'stock_level', '', 'url', 'image', 'shopping_com_category', '0.00');
			$delimiter = ',';
			$extension = 'txt';
			$glue  = "\n";
			$field_wrapper = '"';
			$field_keys_to_wrap = $fields;
			$image_path = 'url';
			$image_mode = 'medium';
			break;
		case 'cubecart':
			$header_fields = array('Product Name', 'Status', 'Include in latest products', 'Product Code', 'Weight', 'Description', 'Price', 'Sale Price', 'Cost Price', 'Tax Class', 'Tax Inclusive', 'Main Image', 'Stock Level', 'Use Stock Level', 'Stock Level Warning', 'Master Category ID', 'Manufacturer', 'UPC Code', 'EAN Code', 'JAN Code', 'ISBN Code', 'Brand', 'MPN Code', 'GTIN Code', 'Meta Title', 'Meta Keywords', 'Meta Description', 'Condition');
			$fields  = array('name', 'status', 'featured', 'product_code', 'product_weight', 'description', 'price', 'sale_price', 'cost_price', 'tax_type', 'tax_inclusive', 'image', 'stock_level', 'use_stock_level', 'stock_warning', 'cat_id', 'manufacturer', 'upc', 'ean', 'jan', 'isbn', 'brand', 'mpn', 'gtin', 'seo_meta_title', 'seo_meta_description', 'seo_meta_keywords', 'condition');
			$delimiter = ',';
			$extension = 'csv';
			$glue  = "\n";
			$field_wrapper = '"';
			$field_keys_to_wrap = $fields;
			$image_path = 'filename';
			$image_mode = 'source';
			break;
		}
		foreach ($results as $i => $result) {
			# strip tags is plain text file CSV should be good to keep but lose two double quotes
			$stock_level = $GLOBALS['catalogue']->getProductStock($result['product_id']);
			if ($result['use_stock_level'] && !$GLOBALS['config']->get('config', 'basket_out_of_stock_purchase')) {
				if ($stock_level <= 0) {
					$result['availability'] = 'out of stock';
				} else {
					$result['availability'] = 'in stock';
				}
			} else {
				$result['availability'] = 'in stock';
			}
			if ($extension == 'csv') {
				$result['name']   = str_replace('"', '""', $result['name']);
				$result['description'] = str_replace('"', '""', $result['description']);
			} else {
				$result['name']   = preg_replace('#[\s]{2,}#', ' ', str_replace(array("&nbsp;", "\t", "\r", "\n", "\0", "\x0B"), '', strip_tags($result['name'])));
				$result['description'] = preg_replace('#[\s]{2,}#', ' ', str_replace(array("&nbsp;", "\t", "\r", "\n", "\0", "\x0B"), '', strip_tags($result['description'])));
			}

			$result['store_category'] = $GLOBALS['seo']->getDirectory($result['cat_id'], false, ' > ');
			$result['shopping_com_category'] = $GLOBALS['seo']->getDirectory($result['cat_id'], false, ' -> ');
			if (isset($result['mpn']) && empty($result['mpn']) && isset($result['gtin']) && empty($result['gtin'])) {
				$result['identifier_exists'] = 'FALSE';
			} else {
				$result['identifier_exists'] = 'TRUE';
			}

			if (strtolower($_GET['format']) == 'shopping.com') {
				$result['description'] = addslashes(html_entity_decode($result['description'], ENT_QUOTES));
			}

			$result['condition'] = (empty($result['condition'])) ? 'new' : $result['condition'];

			# Manufacturer
			if (!empty($result['manufacturer'])) {
				$result['manufacturer'] = ($manuf = $GLOBALS['db']->select('CubeCart_manufacturers', array('name'), array('id' => (int)$result['manufacturer']))) ? $manuf[0]['name'] : '';
			} else {
				$result['manufacturer'] = '';
			}

			# Price
			$sale    = Tax::getInstance()->salePrice($result['price'], $result['sale_price'], false);
			$result['price'] = ($sale > 0 && strtolower($_GET['format']) != 'cubecart') ? $sale : $result['price'];

			$result['price_formatted'] = Tax::getInstance()->priceFormat($result['price'], true);

			## Generate Product URL
			$url = $seo->generatePath($result['product_id'], 'product', 'product_id', true, true);
			$result['url'] = $seo->fullURL($url, true);

			## Generate Image URL
			if (($images = $GLOBALS['db']->select('CubeCart_image_index', array('file_id'), array('main_img' => 1, 'product_id' => $result['product_id']))) !== false) {
				$result['image'] = $catalogue->imagePath($images[0]['file_id'], $image_mode, $image_path, false);
			} else {
				$result['image'] = '';
			}

			$result['currency'] = $GLOBALS['config']->get('config', 'default_currency');
			//CSV must have double quotes around strings. This is the standard and most spreasheets will behave best this way
			foreach ($fields as $field) {
				// format specialist fields e.g. 'price currency' to '9.99 USD'
				if (stristr($field, " ")) {
					$exploded_fields = explode(' ', $field);
					foreach ($exploded_fields as $part_field) {
						$formatted_field[] = $result[$part_field];
					}
					$result[$field] = implode(' ', $formatted_field);
				}
				unset($formatted_field, $exploded_fields);

				$data_fields[] = (in_array($field, $field_keys_to_wrap) && isset($result[$field])) ? $field_wrapper.$result[$field].$field_wrapper : $result[$field];
			}

			if (isset($header_fields)) {
				$output[] = implode($delimiter, $header_fields);
				unset($header_fields);
			}
			$output[] = implode($delimiter, $data_fields);
			unset($data_fields);
		}
		if (isset($output) && !empty($output)) {
			$filename = $_GET['format'].'_'.date('Ymd').'_'.$_GET['page'].'.'.$extension;
			$output  = (is_array($output)) ? implode($glue, $output) : $output;
			$GLOBALS['debug']->supress();
			if (!isset($_GET['access'])) {
				deliverFile(false, false, $output, $filename);
			} else {
				echo $output;
			}
			exit;
		}
	} else {
		$GLOBALS['main']->setACPWarning($lang['category']['no_products']);
	}
}

$GLOBALS['main']->addTabControl($lang['common']['export'], 'export');

$formats = array (
	'googlebase'  => 'Google Base',
	'shopzilla'  => 'Shopzilla',
	'shopping.com'  => 'Shopping.com',
	'storeya'   => 'StoreYa',
	'cubecart'  => 'CubeCart'
);

$page_limits = array (
	50, 100, 250, 500, 1000, 5000, 10000, 25000
);

foreach ($page_limits as $limit_value) {
	$limit['selected'] = ($limit_value==$per_page) ? 'selected="selected"' : null;
	$limit['per_page'] = $limit_value;
	$smarty_data['limits'][] = $limit;
}
$GLOBALS['smarty']->assign('LIMITS', $smarty_data['limits']);

foreach ($formats as $format_key => $format_name) {
	$format['name']  = $format_name;
	$format['parts']  = download_parts($format_key, $no_rows, $per_page);
	$format['link']  = $GLOBALS['storeURL'].'/'.$GLOBALS['config']->get('config', 'adminFile')."?_g=products&node=export&page=1&per_page=5000&format=$format_key&node=export&access=".$GLOBALS['config']->get('config', 'feed_access_key');
	$smarty_data['formats'][] = $format;
}
$GLOBALS['smarty']->assign('FORMATS', $smarty_data['formats']);

$page_content = $GLOBALS['smarty']->fetch('templates/products.export.php');