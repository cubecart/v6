<?php
if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('settings', CC_PERM_READ, true);

global $lang;

## Delete Coupon
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && Admin::getInstance()->permissions('products', CC_PERM_DELETE)) {
	if ($GLOBALS['db']->delete('CubeCart_coupons', array('coupon_id' => (int)$_GET['delete']))) {
		$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_coupon_deleted']);
	} else {
		$GLOBALS['main']->setACPWarning($lang['catalogue']['error_coupon_delete']);
	}
	foreach ($GLOBALS['hooks']->load('admin.product.coupons.delete') as $hook) include $hook;
	httpredir(currentPage(array('delete')));
}

if (isset($_POST['status']) && is_array($_POST['status'])) {
	Admin::getInstance()->permissions('settings', CC_PERM_EDIT, true);
	foreach ($_POST['status'] as $id => $status) {
		$GLOBALS['db']->update('CubeCart_coupons', array('status' => $status), array('coupon_id' => $id));
	}
	$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_coupon_update']);
	foreach ($GLOBALS['hooks']->load('admin.product.coupons.status') as $hook) include $hook;
	httpredir(currentPage());
}

if (isset($_POST['coupon']) && is_array($_POST['coupon'])) {
	Admin::getInstance()->permissions('settings', CC_PERM_EDIT, true);

	foreach ($GLOBALS['hooks']->load('admin.product.coupons.save.pre_process') as $hook) include $hook;

	$coupon_id = (isset($_POST['coupon']['coupon_id'])) ? (int)$_POST['coupon']['coupon_id'] : null;
	$record  = array(
		# 'status'  => (isset($_POST['coupon']['status'])) ? 1 : 0,
		# 'archived'  => (isset($_POST['coupon']['archived'])) ? 1 : 0,
		'code'   => $_POST['coupon']['code'],
		'product_id' => null,
		'expires'  => $_POST['coupon']['expires'],
		'allowed_uses' => (int)$_POST['coupon']['allowed_uses'],
		'min_subtotal' => $_POST['coupon']['min_subtotal'],
		'shipping'  => $_POST['coupon']['shipping'],
		'subtotal'  => $_POST['coupon']['subtotal'],
		'description' => $_POST['coupon']['description'],
		## Temporary reset
		'discount_percent' => 0,
		'discount_price' => 0,
	);
	if (isset($_POST['product']) && is_array($_POST['product'])) {
		foreach ($_POST['product'] as $key => $value) {
			if (empty($value)) unset($_POST['product'][$key]);
		}
		array_unshift($_POST['product'], $_POST['incexc']);
		$record['product_id'] = serialize($_POST['product']);
	}
	switch (strtolower($_POST['discount_type'])) {
	case 'fixed':
		$record['discount_price'] = $_POST['discount_value'];
		break;
	case 'percent':
	default:
		$record['discount_percent'] = $_POST['discount_value'];
	}

	if (!empty($coupon_id) && is_numeric($coupon_id)) {
		if ($GLOBALS['db']->update('CubeCart_coupons', $record, array('coupon_id' => (int)$coupon_id))) {
			$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_coupon_update']);
		}
	} else {
		if ($GLOBALS['db']->insert('CubeCart_coupons', $record)) {
			$GLOBALS['main']->setACPNotify($lang['catalogue']['notify_coupon_create']);
		}
	}
	foreach ($GLOBALS['hooks']->load('admin.product.coupons.save.post_process') as $hook) include $hook;
	httpredir(currentPage(array('action', 'coupon_id')));
}

###########################################

$GLOBALS['gui']->addBreadcrumb($lang['catalogue']['title_coupons']);

foreach ($GLOBALS['hooks']->load('admin.product.coupons.pre_display') as $hook) include $hook;

if (isset($_GET['action'])) {
	$GLOBALS['main']->addTabControl($lang['common']['general'], 'edit-coupon');
	$GLOBALS['main']->addTabControl($lang['catalogue']['title_products_assigned'], 'edit-products');
	if ($_GET['action'] == 'edit' && isset($_GET['coupon_id']) && is_numeric($_GET['coupon_id'])) {
		if (($coupon = $GLOBALS['db']->select('CubeCart_coupons', false, array('coupon_id' => (int)$_GET['coupon_id']), array('archived' => 'ASC'))) !== false) {
			$GLOBALS['gui']->addBreadcrumb($coupon[0]['code'], currentPage());
			$coupon[0]['discount_value'] = ($coupon[0]['discount_price'] > 0) ? $coupon[0]['discount_price'] : $coupon[0]['discount_percent'];
			$GLOBALS['smarty']->assign('COUPON', $coupon[0]);
			$GLOBALS['smarty']->assign('LEGEND', $lang['catalogue']['title_coupon_edit']);
			if (!empty($coupon[0]['product_id'])) {
				$product_id = unserialize($coupon[0]['product_id']);
				// pull the first item off as it's our orders to be inclusive or exclusive
				$incexc = array_shift($product_id);
				// this will handle legacy coupons so we don't lose any products from them
				if (is_numeric($incexc)) {
					array_unshift($product_id, $incexc);
					$incexc = 'include';
				}

				if ($product_id && ($products = $GLOBALS['db']->select('CubeCart_inventory', array('name', 'product_id'), array('product_id' => $product_id))) !== false) {
					$smarty_data['products'] = $products;
				}
			}
			if (isset($smarty_data['products'])) {
				$GLOBALS['smarty']->assign('PRODUCTS', $smarty_data['products']);
			}
			$select_type = ($coupon[0]['discount_price'] > 0) ? 'fixed' : 'percent';
			$GLOBALS['smarty']->assign('DISPLAY_TIMES_USED', true);
		}
	} else {
		$GLOBALS['gui']->addBreadcrumb($lang['catalogue']['title_coupon_add'], currentPage());
		$GLOBALS['smarty']->assign('LEGEND', $lang['catalogue']['title_coupon_add']);
		$select_type = 'percent';
	}

	$discounts = array('fixed' => 'discount_price', 'percent' => 'discount_percent');
	foreach ($discounts as $index => $discount_type) {
		$smarty_data['discounts'][] = array(
			'index'  => $index,
			'selected' => ($select_type == $index) ? 'selected="selected"' : '',
			'title'  => $lang['catalogue'][$discount_type]
		);
	}
	$GLOBALS['smarty']->assign('DISCOUNTS', $smarty_data['discounts']);

	$incexc_choices = array('include' => 'coupon_include', 'exclude' => 'coupon_exclude');
	foreach ($incexc_choices as $index => $incexc_type) {
		$smarty_data['incexc'][] = array(
			'index'  => $index,
			'selected' => ($incexc == $index) ? 'selected="selected"' : '',
			'title'  => $lang['catalogue'][$incexc_type]
		);
	}
	$GLOBALS['smarty']->assign('INCEXC', $smarty_data['incexc']);

	$GLOBALS['smarty']->assign('DISPLAY_FORM', true);
} else {
	$GLOBALS['main']->addTabControl($lang['catalogue']['title_coupons'], 'coupons', null, 'C');
	$GLOBALS['main']->addTabControl($lang['catalogue']['title_coupon_create'], null, currentPage(null, array('action' => 'add')), 'A');
	$GLOBALS['main']->addTabControl($lang['catalogue']['gift_certificates'], 'certificates', null, 'G');

	$certificate_sort_key = 'gc_sort';
	$coupon_sort_key = 'c_sort';

	if (!isset($_GET[$certificate_sort_key]) || !is_array($_GET[$certificate_sort_key])) {
		$_GET[$certificate_sort_key] = array('order_date' => 'DESC');
	}
	$current_page = currentPage(array($coupon_sort_key, $certificate_sort_key));
	$thead_sort = array (
		'status'   => $GLOBALS['db']->column_sort('status', $lang['common']['status'], $certificate_sort_key, $current_page, $_GET[$certificate_sort_key], 'certificates'),
		'code'    => $GLOBALS['db']->column_sort('code', $lang['catalogue']['title_coupon_code'], $certificate_sort_key, $current_page, $_GET[$certificate_sort_key], 'certificates'),
		'value'   => $GLOBALS['db']->column_sort('discount_price', $lang['catalogue']['title_value_remaining'], $certificate_sort_key, $current_page, $_GET[$certificate_sort_key], 'certificates'),
		'expires'   => $GLOBALS['db']->column_sort('expires', $lang['catalogue']['title_coupon_expires'], $certificate_sort_key, $current_page, $_GET[$certificate_sort_key], 'certificates'),
		'cart_order_id' => $GLOBALS['db']->column_sort('cart_order_id', $lang['orders']['order_number'], $certificate_sort_key, $current_page, $_GET[$certificate_sort_key], 'certificates'),
	);
	$GLOBALS['smarty']->assign('THEAD_CERTIFICATE', $thead_sort);
	unset($thead_sort);

	$per_page  = 20;
	$page_var  = 'gc_page';
	$page  = (isset($_GET[$page_var])) ? $_GET[$page_var] : 1;
	$certificates = $GLOBALS['db']->select('CubeCart_coupons', false, '`cart_order_id` IS NOT NULL', $_GET[$certificate_sort_key], $per_page, $page);
	$pagination = $GLOBALS['db']->pagination(false, $per_page, $page, 5, $page_var, 'certificates');
	if ($certificates) {
		foreach ($certificates as $certificate) {
			$certificate['expires'] = ($certificate['expires']>0) ? formatTime(strtotime($certificate['expires'])) : $GLOBALS['lang']['common']['never'];
			if ($certificate['allowed_uses'] == 0) {
				$certificate['allowed_uses'] = '&infin;';
			} else {
				$certificate['allowed_uses'] = $certificate['allowed_uses'];
			}
			$certificate['value']  = ($certificate['discount_percent'] > 0) ? $certificate['discount_percent'].'%' : Tax::getInstance()->priceFormat($certificate['discount_price']);

			$certificate['link_edit'] = currentPage(null, array('action' => 'edit', 'coupon_id' => $certificate['coupon_id']));
			$certificate['link_delete'] = currentPage(null, array('delete' => $certificate['coupon_id']));
			$smarty_data['list_cert'][] = $certificate;
		}
		$GLOBALS['smarty']->assign('CERTIFICATES', $smarty_data['list_cert']);
		$GLOBALS['smarty']->assign('PAGINATION_CERTIFICATES', $pagination);
	}

	if (!isset($_GET[$coupon_sort_key]) || !is_array($_GET[$coupon_sort_key])) {
		$_GET[$coupon_sort_key] = array('expires' => 'DESC');
	}
	$current_page = currentPage(array($coupon_sort_key, $certificate_sort_key));
	$thead_sort = array (
		'status'  => $GLOBALS['db']->column_sort('status', $lang['common']['status'], $coupon_sort_key, $current_page, $_GET[$coupon_sort_key], 'coupons'),
		'code'   => $GLOBALS['db']->column_sort('code', $lang['catalogue']['title_coupon_code'], $coupon_sort_key, $current_page, $_GET[$coupon_sort_key], 'coupons'),
		'value'  => $GLOBALS['db']->column_sort('discount_price', $lang['catalogue']['discount_value'], $coupon_sort_key, $current_page, $_GET[$coupon_sort_key], 'coupons'),
		'expires'  => $GLOBALS['db']->column_sort('expires', $lang['catalogue']['title_coupon_expires'], $coupon_sort_key, $current_page, $_GET[$coupon_sort_key], 'coupons'),
		'time_used' => $GLOBALS['db']->column_sort('count', $lang['catalogue']['title_coupon_count'], $coupon_sort_key, $current_page, $_GET[$coupon_sort_key], 'coupons'),
	);
	$GLOBALS['smarty']->assign('THEAD_COUPON', $thead_sort);


	$per_page = 20;
	$page_var  = 'c_page';
	$page  = (isset($_GET[$page_var])) ? $_GET[$page_var] : 1;
	$coupons  = $GLOBALS['db']->select('CubeCart_coupons', false, '`cart_order_id` IS NULL', $_GET[$coupon_sort_key], $per_page, $page);
	$pagination = $GLOBALS['db']->pagination(false, $per_page, $page, 5, $page_var, 'coupons');
	if ($coupons) {
		foreach ($coupons as $coupon) {
			$coupon['expires'] = ($coupon['expires']>0) ? formatTime(strtotime($coupon['expires'])) : $GLOBALS['lang']['common']['never'];
			if ($coupon['allowed_uses'] == 0) {
				$coupon['allowed_uses'] = '&infin;';
			} else {
				$coupon['allowed_uses'] = $coupon['allowed_uses'];
			}
			$coupon['value']  = ($coupon['discount_percent'] > 0) ? $coupon['discount_percent'].'%' : Tax::getInstance()->priceFormat($coupon['discount_price']);
			$coupon['link_edit'] = currentPage(null, array('action' => 'edit', 'coupon_id' => $coupon['coupon_id']));
			$coupon['link_delete'] = currentPage(null, array('delete' => $coupon['coupon_id']));
			$smarty_data['list_coupon'][] = $coupon;
		}
		$GLOBALS['smarty']->assign('COUPONS', $smarty_data['list_coupon']);
		$GLOBALS['smarty']->assign('PAGINATION_COUPONS', $pagination);
	}
	$GLOBALS['smarty']->assign('DISPLAY_COUPONS', true);

}
$page_content = $GLOBALS['smarty']->fetch('templates/products.coupons.php');