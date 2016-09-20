<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2015. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

if (!defined('CC_INI_SET')) die('Access Denied');

global $glob, $lang, $admin_data;

## Quick tour
$GLOBALS['smarty']->assign('QUICK_TOUR', true);

## Save notes
if (isset($_POST['notes']['dashboard_notes']) && !empty($_POST['notes']['dashboard_notes'])) {
	$update = array('dashboard_notes' => $_POST['notes']['dashboard_notes']);
	if ($GLOBALS['db']->update('CubeCart_admin_users', $update, array('admin_id' => Admin::getInstance()->get('admin_id')))) {
		$GLOBALS['session']->delete('', 'admin_data');
		$GLOBALS['main']->setACPNotify($lang['dashboard']['notice_notes_save']);
	} else {
		$GLOBALS['main']->setACPWarning($lang['dashboard']['error_notes_save']);
	}
	httpredir(currentPage());
}

## Check if setup folder remains after install/upgrade
if ($glob['installed'] && file_exists(CC_ROOT_DIR.'/setup')) {
	## Attempt auto delete as we have just upgraded or installed
	if($_COOKIE['delete_setup']) {
		recursiveDelete(CC_ROOT_DIR.'/setup');
		unlink(CC_ROOT_DIR.'/setup');
		setcookie('delete_setup', '', time()-3600);
	}

	$history = $GLOBALS['db']->misc('SELECT `version` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_history` ORDER BY `time` DESC LIMIT 1');
	if (version_compare(CC_VERSION, $history[0]['version'], '>')) {
		$GLOBALS['main']->setACPWarning(sprintf($lang['dashboard']['error_version'], CC_VERSION, $history[0]['version']));
	} elseif (file_exists(CC_ROOT_DIR.'/setup')) {
		$GLOBALS['main']->setACPWarning($lang['dashboard']['error_setup_folder']);
	}
}
## Are they using the mysql root user?
if ($glob['dbusername'] == 'root' && !(bool)$GLOBALS['config']->get('config', 'debug')) {
	$GLOBALS['main']->setACPWarning($lang['dashboard']['error_mysql_root'], true, false);
}
## Is caching disabled
if (!(bool)$GLOBALS['config']->get('config', 'cache')) {
	$GLOBALS['main']->setACPWarning($lang['dashboard']['error_caching_disabled']);
}
## Windows only - Is global.inc.php writable?
if (substr(PHP_OS, 0, 3) !== 'WIN' && is_writable('includes/global.inc.php')) {
	if (!chmod('includes/global.inc.php', 0444)) {
		$GLOBALS['main']->setACPWarning($lang['dashboard']['error_global_risk']);
	}
}

$mysql_mode = $GLOBALS['db']->misc('SELECT @@sql_mode;');
if (stristr($mysql_mode[0]['@@sql_mode'], 'strict')) {
	$GLOBALS['main']->setACPWarning($lang['setup']['error_strict_mode']);
}

## Get recent extensions
$request = new Request('www.cubecart.com', '/extensions/json');
$request->skiplog(true);
$request->setMethod('get');
$request->cache(true);
$request->setSSL(true);
$request->setData(array('null' => 0));
$request->setUserAgent('CubeCart');
$response = $request->send();
if($response) {
	$GLOBALS['smarty']->assign("RECENT_EXTENSIONS", json_decode($response, true));
}

## Check current version
if (!$GLOBALS['session']->has('version_check') && $request = new Request('www.cubecart.com', '/version-check/'.CC_VERSION)) {
	$request->skiplog(true);
	$request->setMethod('get');
	$request->cache(true);
	$request->setSSL(true);
	$request->setUserAgent('CubeCart');
	
	$request_data = array('version' => CC_VERSION);

	$extension_versions = $GLOBALS['db']->select('CubeCart_extension_info');
	if(is_array($extension_versions)) {
		$extension_check = array();
		foreach($extension_versions as $v) {
			if(file_exists(CC_ROOT_DIR.$v['dir'])) {
				$extension_check[$v['file_id']] = $v['modified'];
			} else {
				$GLOBALS['db']->delete('CubeCart_extension_info', array('file_id' => $v['file_id']));
			}
		}
		if(count($extension_check)>0) {
			$request_data['extensions'] = $extension_check;
		}
	}

	$request->setData($request_data);
	$response = $request->send();
	
	if ($response !== false) {
		
		$response_array = json_decode($response, true);

		if (version_compare($response_array['version'], CC_VERSION, '>')) {
			$GLOBALS['main']->setACPWarning(sprintf($lang['dashboard']['error_version_update'], $response_array['version'], CC_VERSION).' <a href="?_g=maintenance&node=index#upgrade">'.$lang['maintain']['upgrade_now'].'</a>');
		}
		if(isset($response_array['updates']) && is_array($response_array['updates'])) {
			$version_check = $response_array['updates'];
		} else {
			$version_check = true;
		}
		$GLOBALS['session']->set('version_check', $version_check);
	}
}

$GLOBALS['smarty']->assign('DASH_NOTES', Admin::getInstance()->get('dashboard_notes'));

$GLOBALS['main']->wikiPage('Dashboard');
### Dashboard ###
$GLOBALS['main']->addTabControl($lang['dashboard']['title_dashboard'], 'dashboard');
## Quick Stats
if (Admin::getInstance()->permissions('statistics', CC_PERM_READ, false, false)) {
	$total_sales = $GLOBALS['db']->query('SELECT SUM(`total`) as `total_sales` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary` WHERE `status` = 3;');
	$quick_stats['total_sales'] = Tax::getInstance()->priceFormat((float)$total_sales[0]['total_sales']);

	$ave_order  = $GLOBALS['db']->query('SELECT AVG(`total`) as `ave_order` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary` WHERE `status` = 3;');
	$quick_stats['ave_order'] = Tax::getInstance()->priceFormat((float)$ave_order[0]['ave_order']);

	$this_year    = date('Y');
	$this_month   = date('m');
	$this_month_start  = mktime(0, 0, 0, $this_month, '01', $this_year);
	## Work out prev month looks silly but should stop -1 month on 1st March returning January (28 Days in Feb)
	$last_month   = date('m', strtotime("-1 month", mktime(12, 0, 0, $this_month, 15, $this_month)));
	$last_year    = ($last_month < $this_month) ? $this_year : ($this_year - 1);
	$last_month_start  = mktime(0, 0, 0, $last_month, '01', $last_year);

	$last_month_sales  = $GLOBALS['db']->query('SELECT SUM(`total`) as `last_month` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary` WHERE `status` = 3 AND `order_date` > '.$last_month_start.' AND `order_date` < '.$this_month_start.';');
	$quick_stats['last_month'] = Tax::getInstance()->priceFormat((float)$last_month_sales[0]['last_month']);

	$this_month_sales  = $GLOBALS['db']->query('SELECT SUM(`total`) as `this_month` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary` WHERE `status` = 3 AND `order_date` > '.$this_month_start.';');
	$quick_stats['this_month'] = Tax::getInstance()->priceFormat((float)$this_month_sales[0]['this_month']);

	$GLOBALS['smarty']->assign('QUICK_STATS', $quick_stats);
}
## Last 5 orders
if (($last_orders = $GLOBALS['db']->select('CubeCart_order_summary', array('cart_order_id', 'first_name', 'last_name', 'name'), false, array('order_date' => 'DESC'), 5)) !== false) {
	$GLOBALS['smarty']->assign('LAST_ORDERS', $last_orders);
}

## Quick Tasks
$date_format = "Y-m-d";
$today   = date($date_format);
$quick_tasks['today']   = urlencode(date($date_format));
$quick_tasks['this_weeks'] = urlencode(date($date_format, strtotime("last monday")));
foreach ($GLOBALS['hooks']->load('admin.dashboard.quick_tasks') as $hook) include $hook;
$GLOBALS['smarty']->assign('QUICK_TASKS', $quick_tasks);

## Statistics (Google Charts)
$sales = $GLOBALS['db']->select('CubeCart_order_summary', array('order_date', 'total'), array('order_date' => '>='.mktime(0, 0, 0, date('m', $last_year), 1, date('Y', $last_year)), 'status' => array(3), 'total' => '>0'));
$data= array();
if ($sales) { ## Get data to put in chart
	foreach ($sales as $sale) {
		$year = date('Y', $sale['order_date']);
		$month = date('M', $sale['order_date']);
		if (isset($data[$year][$month])) {
			$data[$year][$month] += sprintf('%0.2f', $sale['total']);
		} else {
			$data[$year][$month] = sprintf('%0.2f', $sale['total']);
		}
	}
}

$this_year = date('Y');
$last_year = $this_year - 1;

$chart_data['data'] = "['Month', '$this_year', '$last_year'],";

for ($month = 1; $month <= 12; $month++) {
	$m = date("M", mktime(0, 0, 0, $month, 10));
	$last_year_month = (isset($data[$last_year][$m]) && $data[$last_year][$m]>0) ? $data[$last_year][$m] : 0;
	$this_year_month = (isset($data[$this_year][$m]) && $data[$this_year][$m]>0) ? $data[$this_year][$m] : 0;
	$chart_data['data'] .= "['$m',  $this_year_month, $last_year_month],";
}

$chart_data['title'] = $lang['dashboard']['title_sales_stats'].': '.$last_year.' - '.$this_year;
$GLOBALS['smarty']->assign('CHART', $chart_data);

## Pending Orders Tab
$page  = (isset($_GET['orders'])) ? $_GET['orders'] : 1;
$unsettled_count  = $GLOBALS['db']->count('CubeCart_order_summary', 'cart_order_id', array('status' => array(1, 2)));
$results_per_page = 25;
$unsettled_orders = $GLOBALS['db']->select('CubeCart_order_summary', array('cart_order_id', 'name', 'first_name', 'last_name', 'order_date', 'customer_id', 'total', 'status'), 'status IN (1,2) OR `dashboard` = 1', '`dashboard` DESC, `status` DESC,`order_date` ASC', $results_per_page, $page);

if ($unsettled_orders) {
	$tax = Tax::getInstance();
	$GLOBALS['main']->addTabControl($lang['dashboard']['title_orders_unsettled'], 'orders', null, null, $unsettled_count);
	
	foreach($unsettled_orders as $order) {
		$customer_ids[$order['customer_id']] = true;
	}
	$customers_in = implode(array_keys($customer_ids),',');
	
	$customers = $GLOBALS['db']->select('CubeCart_customer', array('type','customer_id'), 'customer_id IN ('.$customers_in.')');
	foreach($customers as $customer) {
		$customer_type[$customer['customer_id']] = $customer['type'];
	}

	for ($i = 1; $i <= 6; ++$i) {
		$smarty_data['order_status'][] = array(
			'id'  => $i,
			'selected' => (isset($summary[0]) && isset($summary[0]['status']) && (int)$summary[0]['status'] === $i) ? ' selected="selected"' : '',
			'string' => $lang['order_state']['name_'.$i],
		);
	}
	$GLOBALS['smarty']->assign('LIST_ORDER_STATUS', $smarty_data['order_status']);

	foreach ($unsettled_orders as $order) {
		$cart_order_ids[] = "'".$order['cart_order_id']."'";
		$order['icon'] = $customer_type[$order['customer_id']]==1 ? 'user_registered' : 'user_ghost';
		$order['date'] = formatTime($order['order_date']);
		$order['total'] = Tax::getInstance()->priceFormat($order['total']);
		$order['status'] = $lang['order_state']['name_'.$order['status']];
		$order['link_print'] = '?_g=orders&print%5B0%5D='.$order['cart_order_id'];
		$orders[$order['cart_order_id']] = $order;
	}
	if (($notes = $GLOBALS['db']->select('CubeCart_order_notes', '`cart_order_id`,`time`,`content`', array('cart_order_id' => $cart_order_ids))) !== false) {
		foreach ($notes as $note) {
			$order_notes[$note['cart_order_id']]['notes'][] = $note;
		}
		$orders = merge_array($orders, $order_notes);
	}

	$GLOBALS['smarty']->assign('ORDERS', $orders);
	$GLOBALS['smarty']->assign('ORDER_PAGINATION', $GLOBALS['db']->pagination($unsettled_count, $results_per_page, $page, $show = 5, 'orders', 'orders', $glue = ' ', $view_all = true));
}

## Product Reviews Tab
$page  = (isset($_GET['reviews'])) ? $_GET['reviews'] : 1;
if (($reviews = $GLOBALS['db']->select('CubeCart_reviews', false, array('approved' => '0'), false, 25, $page)) !== false) {
	$reviews_count = $GLOBALS['db']->getFoundRows();

	$GLOBALS['main']->addTabControl($lang['dashboard']['title_reviews_pending'], 'product_reviews', null, null, $reviews_count);
	foreach ($reviews as $review) {
		$product   = $GLOBALS['db']->select('CubeCart_inventory', array('name'), array('product_id' => (int)$review['product_id']));
		$review['product'] = $product[0];
		$review['date']  = formatTime($review['time']);
		$review['delete'] = "?_g=products&node=reviews&delete=".(int)$review['id'];
		$review['edit']  = "?_g=products&node=reviews&edit=".(int)$review['id'];
		$review['stars'] = 5;
		$review_list[] = $review;
	}
	$GLOBALS['smarty']->assign('REVIEWS', $review_list);
	$GLOBALS['smarty']->assign('REVIEW_PAGINATION', $GLOBALS['db']->pagination($reviews_count, 25, $page, $show = 5, 'reviews', 'product_reviews', $glue = ' ', $view_all = true));
}

## Stock Warnings
$page  = (isset($_GET['stock'])) ? $_GET['stock'] : 1;

$tables = '`'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_inventory` AS `I` LEFT JOIN `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_option_matrix` AS `M` on `I`.`product_id` = `M`.`product_id`';

$fields = 'I.name ,I.stock_level AS I_stock_level, I.stock_warning AS I_stock_warning, I.product_id, M.stock_level AS M_stock_level, M.use_stock as M_use_stock, M.cached_name';

$where = 'use_stock_level = 1';
$where .= ' AND (';
//$where .= '(M.use_stock = 1 AND M.status = 1 AND M.stock_level <= '.(int)$GLOBALS['config']->get('config', 'stock_warn_level').')';
$where .= '((I.stock_warning > 0 AND M.stock_level <= I.stock_warning AND M.status = 1 AND M.use_stock = 1) OR (I.stock_warning <= 0 AND M.status = 1 AND M.use_stock = 1 AND M.stock_level <= '.(int)$GLOBALS['config']->get('config', 'stock_warn_level').'))';
$where .= ' OR ';
$where .= '((I.stock_warning > 0 AND I.stock_level <= I.stock_warning) OR (I.stock_warning <= 0 AND I.stock_level <= '.(int)$GLOBALS['config']->get('config', 'stock_warn_level').'))';
$where .= ')';

$order_by = 'I.stock_level ASC';

$result_limit = 20;

if ($stock_c = $GLOBALS['db']->select($tables, $fields, $where)) {
	$stock_count = count($stock_c);
	$stock = $GLOBALS['db']->select($tables, $fields, $where, $order_by, $result_limit, $page);
	$GLOBALS['smarty']->assign('STOCK', $stock);
	$GLOBALS['main']->addTabControl($lang['dashboard']['title_stock_warnings'], 'stock_warnings', null, null, $stock_count);
	$GLOBALS['smarty']->assign('STOCK_PAGINATION', $GLOBALS['db']->pagination($stock_count, $result_limit, $page, $show = 5, 'stock', 'stock_warnings', $glue = ' ', $view_all = true));

	foreach ($GLOBALS['hooks']->load('admin.dashboard.stock.post') as $hook) include $hook;
}

if($GLOBALS['session']->has('version_check')) {
	$extension_updates = $GLOBALS['session']->get('version_check');
	$extension_updates = $GLOBALS['db']->select('CubeCart_extension_info', false, array('file_id' => array_keys($extension_updates)));
	if($extension_updates) {
		$GLOBALS['main']->addTabControl($lang['dashboard']['title_extension_updates'], 'extension_updates', null, null, count($extension_updates));
		$GLOBALS['smarty']->assign('EXTENSION_UPDATES', $extension_updates);
	}
}

foreach ($GLOBALS['hooks']->load('admin.dashboard.tabs') as $hook) include $hook;
$GLOBALS['smarty']->assign('PLUGIN_TABS', $smarty_data['plugin_tabs']);

## Latest News (from RSS)
if ($GLOBALS['config']->has('config', 'default_rss_feed') && !$GLOBALS['config']->isEmpty('config', 'default_rss_feed') && filter_var($GLOBALS['config']->get('config', 'default_rss_feed'), FILTER_VALIDATE_URL)) {

	$default_rss_feed = $GLOBALS['config']->get('config', 'default_rss_feed');

	$url = (preg_match('/(act=rssout&id=1|1-cubecart-news-announcements)/', $default_rss_feed)) ? 'https://forums.cubecart.com/forum/1-news-announcements.xml' : $default_rss_feed;

	$url = parse_url($url);
	$path = (isset($url['query'])) ? $url['path'].'?'.$url['query'] : $url['path'];
	$request = new Request($url['host'], $path);
	$request->cache(true);
	$request->skiplog(true);
	$request->setMethod('post');
	$request->setData('Null');

	if (($response = $request->send()) !== false) {
		try {
			if (($data = new SimpleXMLElement($response)) !== false) {
				foreach ($data->channel->children() as $key => $value) {
					if ($key == 'item') continue;
					$news[$key] = (string)$value;
				}
				if ($data['version'] >= 2) {
					$i = 1;
					foreach ($data->channel->item as $item) {
						$news['items'][] = array(
							'title'   => (string)$item->title,
							'link'   => (string)$item->link,
						);
						if($i==5) break;
						$i++;
					}
				}
				$GLOBALS['smarty']->assign('NEWS', $news);
			}
		} catch (Exception $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
		}
	}
}
$GLOBALS['main']->addTabControl($lang['dashboard']['title_store_overview'], 'advanced');

$count = array(
	'products' => (int)$GLOBALS['db']->count('CubeCart_inventory', 'product_id'),
	'orders' => (int)$GLOBALS['db']->count('CubeCart_order_summary', 'cart_order_id'),
	'customers' => (int)$GLOBALS['db']->count('CubeCart_customer', 'customer_id'),
);

$tmp1 = 0;
$tmp2 = 0;

$system = array(
	'cc_version' => CC_VERSION,
	'cc_build'  => null,
	'php_version' => PHP_VERSION,
	'mysql_version' => $GLOBALS['db']->serverVersion(),
	'server'  => htmlspecialchars($_SERVER['SERVER_SOFTWARE']),
	'client'  => htmlspecialchars($_SERVER['HTTP_USER_AGENT']),
	'dir_images' => dirsize(CC_ROOT_DIR.'/images', $tmp1),
	'dir_files'  => dirsize(CC_ROOT_DIR.'/files', $tmp2),
);

$GLOBALS['smarty']->assign('SYS', $system);
$GLOBALS['smarty']->assign('PHP', ini_get_all());
$GLOBALS['smarty']->assign('COUNT', $count);

$GLOBALS['main']->addTabControl($lang['common']['search'], 'sidebar');

foreach ($GLOBALS['hooks']->load('admin.dashboard.custom_quick_tasks') as $hook) include $hook;
if(isset($custom_quick_tasks) && is_array($custom_quick_tasks)) {
	$GLOBALS['smarty']->assign('CUSTOM_QUICK_TASKS', $custom_quick_tasks);
}

$page_content = $GLOBALS['smarty']->fetch('templates/dashboard.index.php');
