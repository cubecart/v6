<?php
if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('orders', CC_PERM_READ, true);
$GLOBALS['main']->addTabControl($GLOBALS['language']->orders['title_transaction_logs'], 'logs');
$GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->orders['title_transaction_logs']);

if (isset($_POST['search'])) {
	httpredir(currentPage(null, array('search' => $_POST['search'])));
}

$per_page = 20;
$page  = (isset($_GET['page'])) ? $_GET['page'] : 1;

if (isset($_GET['order_id'])) {
	$GLOBALS['smarty']->assign('TRANSACTION_LOGS_TITLE', sprintf($GLOBALS['lang']['orders']['title_transaction_logs_for_order'], $_GET['order_id']));
	if (($transactions = $GLOBALS['db']->select('CubeCart_transactions', false, array('order_id' => $_GET['order_id']), array('time' => 'DESC'))) !== false) {
		$GLOBALS['gui']->addBreadcrumb($transactions[0]['order_id'], currentPage());
		foreach ($transactions as $transaction) {
			$transaction['time']  = formatTime($transaction['time']);
			$transaction['amount']  = Tax::getInstance()->priceFormat($transaction['amount']);
			$transaction['trans_id'] = empty($transaction['trans_id']) ? $GLOBALS['lang']['common']['null'] : $transaction['trans_id'];
			$smarty_data['transactions'][] = $transaction;
		}
		$GLOBALS['smarty']->assign('ORDER_TRANSACTIONS', $smarty_data['transactions']);
	}
	$GLOBALS['smarty']->assign('DISPLAY_ORDER_TRANSACTIONS', true);
} else {
	if (isset($_GET['search']) && !empty($_GET['search'])) {
		if (preg_match('#^[0-9]{6}-[0-9]{6}-[0-9]{4}$#', $_GET['search'])) {
			$where['order_id'] = $_GET['search'];
		} else {
			$where = "`trans_id` LIKE '%".$_GET['search']."%' OR `amount` LIKE '%".$_GET['search']."%' OR `gateway` LIKE '%".$_GET['search']."%'";
		}
	} else {
		$where = false;
	}
	if (($count_rows = $GLOBALS['db']->select('CubeCart_transactions', array('DISTINCT' => 'order_id'), $where)) !== false) {
		$count = count($count_rows);
		if ($count > $per_page) {
			$GLOBALS['smarty']->assign('PAGINATION', $GLOBALS['db']->pagination($count, $per_page, $page, 9));
		}
	}

	if (!isset($_GET['sort']) || !is_array($_GET['sort'])) {
		$_GET['sort'] = array('time' => 'DESC');
	}
	$current_page = currentPage(array('sort'));
	$thead_sort = array (
		'cart_order_id' => $GLOBALS['db']->column_sort('cart_order_id', $GLOBALS['language']->orders['order_number'], 'sort', $current_page, $_GET['sort']),
		'amount'  => $GLOBALS['db']->column_sort('amount', $GLOBALS['language']->basket['total'], 'sort', $current_page, $_GET['sort']),
		'gateway'   => $GLOBALS['db']->column_sort('gateway', $GLOBALS['language']->orders['gateway_name'], 'sort', $current_page, $_GET['sort']),
		'date'    => $GLOBALS['db']->column_sort('time', $GLOBALS['language']->common['date'], 'sort', $current_page, $_GET['sort'])
	);
	$GLOBALS['smarty']->assign('THEAD', $thead_sort);

	if (($transactions = $GLOBALS['db']->select('CubeCart_transactions', array('DISTINCT' => 'order_id', 'time', 'amount', 'gateway', 'trans_id'), $where, $_GET['sort'], $per_page, $page)) !== false) {
		if (isset($_GET['search']) && !empty($_GET['search'])) {
			$GLOBALS['main']->setACPNotify(sprintf($GLOBALS['language']->orders['notify_search_logs'], $_GET['search']));
		}
		foreach ($transactions as $transaction) {
			if (!empty($transaction['order_id'])) {
				$transaction['time'] = formatTime($transaction['time']);
				$transaction['amount'] = Tax::getInstance()->priceFormat($transaction['amount']);
				$transaction['link'] = currentPage(array('page', 'sort'), array('order_id' => $transaction['order_id']));
				$smarty_data['transactions'][] = $transaction;
			}
		}
		$GLOBALS['smarty']->assign('ALL_TRANSACTIONS', $smarty_data['transactions']);
	} else if (isset($_GET['search']) && !empty($_GET['search']) && !$transactions) {
			$GLOBALS['gui']->setError(sprintf($GLOBALS['language']->orders['error_search_logs'], $_GET['search']));
		}
	$GLOBALS['smarty']->assign('DISPLAY_ALL_TRANSACTIONS', true);
}
$page_content = $GLOBALS['smarty']->fetch('templates/orders.transactions.php');