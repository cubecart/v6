<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}
Admin::getInstance()->permissions('statistics', CC_PERM_READ, true);

global $lang;

if (isset($_POST['select'])) {
    httpredir(currentPage(null, $_POST['select']));
}

$select['year']  = (isset($_GET['year']) && is_numeric($_GET['year'])) ? (int)$_GET['year'] : date('Y');
$select['month'] = (isset($_GET['month']) && in_array($_GET['month'], range(1, 12))) ? str_pad((int)$_GET['month'], 2, '0', STR_PAD_LEFT) : date('m');
$select['day']  = (isset($_GET['day']) && in_array($_GET['day'], range(1, 31))) ? str_pad((int)$_GET['day'], 2, '0', STR_PAD_LEFT) : date('d');

$select['status'] = (isset($_GET['status']) && in_array($_GET['status'], range(1, 6))) ? (int)$_GET['status'] : 3;

// Sales
$GLOBALS['main']->addTabControl($lang['statistics']['title_sales'], 'stats_sales');
$earliest_order = $GLOBALS['db']->select('CubeCart_order_summary', array('MIN' => 'order_date'), array('status' => $select['status']), array('order_date' => 'ASC'));
// $earliest_order will always return true but MIN_order_date may not have a value

$yearly = $monthly = $daily = $hourly = array();

if (!empty($earliest_order[0]['MIN_order_date'])) {
    $earliest = array(
        'year' => date('Y', $earliest_order[0]['MIN_order_date']),
        'month' => date('m', $earliest_order[0]['MIN_order_date']),
        'day' => date('d', $earliest_order[0]['MIN_order_date']),
    );

    $orders_all = $GLOBALS['db']->select('CubeCart_order_summary', array('total', 'cart_order_id', 'order_date'), array('status' => (int)$select['status']));
    if ($orders_all) {
        foreach ($orders_all as $key => $data) {
            $orderdate = array(
                'year' => date('Y', $data['order_date']),
                'month' => date('m', $data['order_date']),
                'day' => date('d', $data['order_date']),
                'hour' => date('H', $data['order_date']),
            );
            if (!isset($yearly[$orderdate['year']])) {
                $yearly[$orderdate['year']] = 0;
            }
            $yearly[$orderdate['year']] += $data['total'];
            if ($orderdate['year'] == $select['year']) {
                // Fetch Months
                if (!isset($monthly[$orderdate['month']])) {
                    $monthly[$orderdate['month']] = 0;
                }
                $monthly[$orderdate['month']] += $data['total'];
                if ($orderdate['month'] == $select['month']) {
                    // Fetch Days
                    if (!isset($daily[$orderdate['day']])) {
                        $daily[$orderdate['day']] = 0;
                    }
                    $daily[$orderdate['day']] += $data['total'];
                    if ($orderdate['day'] == $select['day']) {
                        // Fetch Hours
                        if (!isset($hourly[$orderdate['hour']])) {
                            $hourly[$orderdate['hour']] = 0;
                        }
                        $hourly[$orderdate['hour']] += $data['total'];
                    }
                }
            }
        }
    }

    $now['year'] = date('Y');
    for ($i = $earliest['year'];$i <= $now['year']; ++$i) {
        $selected = ($select['year'] == $i) ? ' selected="selected"' : '';
        $smarty_data['years'][] = array('value' => $i, 'selected' => $selected);
    }
    $GLOBALS['smarty']->assign('YEARS', $smarty_data['years']);

    if (count($yearly) >= 1) {
        $g_graph_data[1]['data'] = "['Year','".sprintf($lang['statistics']['sales_volume'], $GLOBALS['config']->get('config', 'default_currency'))."'],";
        
        for ($i = $earliest['year']; $i <= $now['year']; ++$i) {
            $value = isset($yearly[$i]) ? $yearly[$i] : 0;
            $tmp_col_data[] = "['".$i."',".$value."]";
        }
        $g_graph_data[1]['data'] .= implode(',', $tmp_col_data);
        unset($tmp_col_data);
    
        $g_graph_data[1]['title'] = ($earliest['year'] == $now['year']) ? sprintf($lang['statistics']['sales_in'], $now['year']) : sprintf($lang['statistics']['sales_from_to'], $earliest['year'], $now['year']);
        $g_graph_data[1]['hAxis'] = '';
        $g_graph_data[1]['vAxis'] = '';
    }

    $g_graph_data[2]['data'] = "['Month','".sprintf($lang['statistics']['sales_volume'], $GLOBALS['config']->get('config', 'default_currency'))."'],";

    for ($i = 1; $i <= 12; ++$i) {
        $i    = str_pad($i, 2, '0', STR_PAD_LEFT);
        $value   = isset($monthly[$i]) ? $monthly[$i] : 0;
        $month_text  = date('F', mktime(0, 0, 0, $i, 1));
        $tmp_col_data[] = "['".date('M', mktime(0, 0, 0, $i, 1))."',".$value."]";
        $monthList[$i] = $month_text;
        $selected  = ((int)$select['month'] == (int)$i) ? ' selected="selected"' : '';
        $smarty_data['months'][] = array('value' => $i, 'title' => $month_text, 'selected' => $selected);
    }
    
    $g_graph_data[2]['data'] .= implode(',', $tmp_col_data);
    unset($tmp_col_data);
    
    $GLOBALS['smarty']->assign('MONTHS', $smarty_data['months']);
        
    $g_graph_data[2]['title'] = sprintf($lang['statistics']['sales_in_year'], $select['year']);
    $g_graph_data[2]['hAxis'] = '';
    $g_graph_data[2]['vAxis'] = '';
    
    
    $monthLength = date('t', mktime(0, 0, 0, $select['month'], 1, $select['year']));
    for ($day = 1; $day <= $monthLength; ++$day) {
        $dayList[$day] = $day;
        $selected = ((int)$select['day'] == (int)$day) ? ' selected="selected"' : '';
        $smarty_data['days'][] = array('value' => $day, 'selected' => $selected);
    }
    $GLOBALS['smarty']->assign('DAYS', $smarty_data['days']);

    $g_graph_data[3]['data'] = "['Day','".sprintf($lang['statistics']['sales_volume'], $GLOBALS['config']->get('config', 'default_currency'))."'],";

    for ($i = 1;$i <= $monthLength; ++$i) {
        $i    = str_pad($i, 2, '0', STR_PAD_LEFT);
        $value   = isset($daily[$i]) ? $daily[$i] : 0;
        $tmp_col_data[] = "['".(int)$i."',".$value."]";
    }
    $g_graph_data[3]['data'] .= implode(',', $tmp_col_data);
    unset($tmp_col_data);
    $g_graph_data[3]['title'] = sprintf($lang['statistics']['sales_in_month_year'], $monthList[$select['month']], $select['year']);
    $g_graph_data[3]['hAxis'] = '';
    $g_graph_data[3]['vAxis'] = '';
    

    $g_graph_data[4]['data'] = "['Hour','".sprintf($lang['statistics']['sales_volume'], $GLOBALS['config']->get('config', 'default_currency'))."'],";

    for ($i = 0; $i <= 23; ++$i) {
        $i    = str_pad($i, 2, '0', STR_PAD_LEFT);
        $value   = isset($hourly[$i]) ? $hourly[$i] : 0;
        $tmp_col_data[] = "['".$i.":00',".$value."]";
    }
    $g_graph_data[4]['data'] .= implode(',', $tmp_col_data);
    unset($tmp_col_data);
    $g_graph_data[4]['title'] = sprintf($lang['statistics']['sales_on_dmy'], $select['day'], $monthList[$select['month']], $select['year']);
    $g_graph_data[4]['hAxis'] = '';
    $g_graph_data[4]['vAxis'] = '';

    // Populate dropdowns
    $select_options = array('month' => $monthList);
    $GLOBALS['smarty']->assign('DISPLAY_SALES', true);
}

#############################################
// Percentages

// Product Sales
$per_page = 15;
$page = (isset($_GET['page_sales']) && is_numeric($_GET['page_sales'])) ? $_GET['page_sales'] : 1;
$query = "SELECT sum(O.quantity) AS quan, O.product_id, I.name FROM `".$glob['dbprefix']."CubeCart_order_inventory` AS O INNER JOIN `".$glob['dbprefix']."CubeCart_order_summary` AS S ON S.cart_order_id = O.cart_order_id INNER JOIN `".$glob['dbprefix']."CubeCart_inventory` AS I ON O.product_id = I.product_id WHERE (S.`status` = 2 OR S.`status` = 3) GROUP BY I.product_id DESC ORDER BY `quan` DESC";

if (($results = $GLOBALS['db']->query($query, $per_page, $page)) !== false) {
    $GLOBALS['main']->addTabControl($lang['statistics']['title_popular'], 'stats_prod_sales');
    $numrows = $GLOBALS['db']->numrows($query);
    $divider = $GLOBALS['db']->query("SELECT SUM(quantity) as totalProducts FROM  `".$glob['dbprefix']."CubeCart_order_inventory`");
    
    $g_graph_data[5]['data'] = "['".$lang['statistics']['percentage_of_sales']."','".$lang['common']['percentage']."'],";
    
    $smarty_data[5] = array();
    foreach ($results as $key => $result) {
        $result['key']  = (($page-1)*$per_page)+($key+1);
        $result['percent'] = 100*($result['quan']/$divider[0]['totalProducts']);
        $result['percent'] = number_format($result['percent'], 2);
        $tmp_col_data[] = "['".$result['key'].". ".addslashes($result['name'])."',".$result['percent']."]";
        // Create a product legend
        $smarty_data[5][] = $result;
    }
    
    $g_graph_data[5]['data'] .= isset($tmp_col_data) ? implode(',', $tmp_col_data) : '';
    unset($tmp_col_data);
    
    $g_graph_data[5]['title'] = $lang['statistics']['percentage_of_sales'];
    $g_graph_data[5]['hAxis'] = $lang['dashboard']['inv_products'];
    ;
    $g_graph_data[5]['vAxis'] = $lang['common']['percentage'];
    
    $GLOBALS['smarty']->assign('PRODUCT_SALES', $smarty_data[5]);
    
    $GLOBALS['smarty']->assign('PAGINATION_SALES', $GLOBALS['db']->pagination($numrows, $per_page, $page, 5, 'page_sales', 'stats_prod_sales', ' ', false));
    unset($results,$result,$divider);
}

## Product Views
$per_page = 15;
$page = (isset($_GET['page_views']) && is_numeric($_GET['page_views'])) ? $_GET['page_views'] : 1;
$query  = "SELECT `popularity`, `name` FROM `".$glob['dbprefix']."CubeCart_inventory` WHERE `popularity` > 0 ORDER BY `popularity` DESC ";
$results = $GLOBALS['db']->query($query, $per_page, $page);
if ($results) {
    $GLOBALS['main']->addTabControl($lang['statistics']['title_viewed'], 'stats_prod_views');
    $numrows = $GLOBALS['db']->numrows($query);
    $divider = $GLOBALS['db']->query('SELECT SUM(popularity) as `totalHits` FROM  `'.$glob['dbprefix'].'CubeCart_inventory`');
    $max_percent = 0;
    
    $g_graph_data[6]['data'] = "['".$lang['statistics']['percentage_of_views']."','".$lang['common']['percentage']."'],";
    
    foreach ($results as $key => $result) {
        $result['key']  = (($page-1)*$per_page)+($key+1);
        $result['percent'] = (100*($result['popularity']/$divider[0]['totalHits']));
        $max_percent = ($result['percent']>$max_percent) ? $result['percent'] : $max_percent;
        $result['percent'] = number_format($result['percent'], 2);
        $tmp_col_data[] = "['".$result['key'].". ".addslashes($result['name'])."',".$result['percent']."]";
        // Create a product legend
        $smarty_data['product_views'][] = $result;
    }
    
    $g_graph_data[6]['data'] .= implode(',', $tmp_col_data);
    unset($tmp_col_data);
    $g_graph_data[6]['title'] = $lang['statistics']['percentage_of_views'];
    $g_graph_data[6]['hAxis'] = $lang['dashboard']['inv_products'];
    $g_graph_data[6]['vAxis'] = $lang['common']['percentage'];
    
    $GLOBALS['smarty']->assign('PRODUCT_VIEWS', $smarty_data['product_views']);

    $GLOBALS['smarty']->assign('PAGINATION_VIEWS', $GLOBALS['db']->pagination($numrows, $per_page, $page, 5, 'page_views', 'stats_prod_views', ' ', false));
    unset($results, $result, $divider);
}

## Search Popularity
$per_page = 15;
$page  = (isset($_GET['page_search']) && is_numeric($_GET['page_search'])) ? $_GET['page_search'] : 1;
$query  = 'SELECT * FROM `'.$glob['dbprefix'].'CubeCart_search` ORDER BY hits DESC';
if (($results = $GLOBALS['db']->query($query, $per_page, $page)) !== false) {
    $GLOBALS['main']->addTabControl($lang['statistics']['title_search'], 'stats_search');
    $numrows = $GLOBALS['db']->numrows($query);
    $divider = $GLOBALS['db']->query("SELECT SUM(hits) as `totalHits` FROM  `".$glob['dbprefix']."CubeCart_search`");
    $max_percent = 0;
    
    $g_graph_data[7]['data'] = "['".$lang['statistics']['percentage_of_views']."','".$lang['common']['percentage']."'],";
    
    $smarty_data[7] = array();
    foreach ($results as $key => $result) {
        $result['percent']  = 100*($result['hits']/$divider[0]['totalHits']);
        $max_percent = ($result['percent']>$max_percent) ? $result['percent'] : $max_percent;
        $result['percent'] = number_format($result['percent'], 2);
        $result['key']   = (($page-1)*$per_page)+($key+1);
        $result['searchstr']  = ucfirst(strtolower($result['searchstr']));
        $tmp_col_data[] = "['".$result['key'].". ".addslashes($result['searchstr'])."',".$result['percent']."]";
        $smarty_data['search_terms'][] = $result;
    }
    
    $g_graph_data[7]['data'] .= isset($tmp_col_data) ? implode(',', $tmp_col_data) : '';
    unset($tmp_col_data);
    $g_graph_data[7]['title'] = '';
    $g_graph_data[7]['hAxis'] = $lang['statistics']['search_term'];
    $g_graph_data[7]['vAxis'] = $lang['statistics']['percentage_of_search'];
    
    $GLOBALS['smarty']->assign('SEARCH_TERMS', $smarty_data['search_terms']);
    
    $GLOBALS['smarty']->assign('PAGINATION_SEARCH', $GLOBALS['db']->pagination($numrows, $per_page, $page, 5, 'page_search', 'stats_search', ' ', false));
    unset($results, $result, $divider);
}
// Best Customers
$per_page = 15;
$page = (isset($_GET['page_customers']) && is_numeric($_GET['page_customers'])) ? $_GET['page_customers'] : 1;
$query = "SELECT sum(`total`) as `customer_expenditure`, C.first_name, C.last_name, C.customer_id FROM `".$glob['dbprefix']."CubeCart_order_summary` as O INNER JOIN  `".$glob['dbprefix']."CubeCart_customer` as C on O.customer_id = C.customer_id WHERE O.status = 3 GROUP BY O.customer_id ORDER BY `customer_expenditure` DESC";
if (($results = $GLOBALS['db']->query($query, $per_page, $page)) !== false) {
    $GLOBALS['main']->addTabControl($lang['statistics']['title_customers_best'], 'stats_best_customers');
    $numrows = $GLOBALS['db']->numrows($query);
    $divider = $GLOBALS['db']->query("SELECT sum(`total`) as `total_sales` FROM `".$glob['dbprefix']."CubeCart_order_summary` WHERE `status` = 3");
    
    $g_graph_data[8]['data'] = "['".$lang['statistics']['percentage_of_views']."','".sprintf($lang['statistics']['sales_volume'], $GLOBALS['config']->get('config', 'default_currency'))."'],";
    
    $smarty_data[8] = array();
    foreach ($results as $key => $result) {
        $result['key']  = (($page-1)*$per_page)+($key+1);
        $result['expenditure'] = Tax::getInstance()->priceFormat($result['customer_expenditure']);
        $result['percent'] = $divider[0]['total_sales'] ? number_format(100*($result['customer_expenditure']/$divider[0]['total_sales']), 2) : 0;
        $tmp_col_data[] = "['".$result['key'].". ".addslashes($result['last_name'].", ".$result['first_name'])."',".$result['customer_expenditure']."]";
        // Create a customer legend
        $smarty_data[8][] = $result;
    }
    
    $g_graph_data[8]['data'] .= isset($tmp_col_data) ? implode(',', $tmp_col_data) : '';
    unset($tmp_col_data);
    $g_graph_data[8]['title'] = '';
    $g_graph_data[8]['hAxis'] = $lang['dashboard']['inv_customers'];
    $g_graph_data[8]['vAxis'] = $lang['statistics']['total_expenditure'];
    
    $GLOBALS['smarty']->assign('BEST_CUSTOMERS', $smarty_data[8]);

    $GLOBALS['smarty']->assign('PAGINATION_BEST', $GLOBALS['db']->pagination($numrows, $per_page, $page, 5, 'page_customers', 'stats_best_customers', ' ', false));
    unset($results, $result, $divider);
}

foreach ($GLOBALS['hooks']->load('admin.statistics.tabs') as $hook) {
    include $hook;
}
$GLOBALS['smarty']->assign('PLUGIN_TABS', $smarty_data['plugin_tabs']);

// Customers Online
$timeLimit = time()-1800;  // 30 minutes

if (isset($_GET['bots']) && $_GET['bots']=='false') {
    $filter = '(S.session_last > S.session_start) AND ';
    $GLOBALS['smarty']->assign('BOTS', false);
} else {
    $filter = '';
    $GLOBALS['smarty']->assign('BOTS', true);
}

$query  = sprintf("SELECT S.*, C.first_name, C.last_name FROM %1\$sCubeCart_sessions AS S LEFT JOIN %1\$sCubeCart_customer AS C ON S.customer_id = C.customer_id WHERE S.acp = 0 AND ".$filter."S.session_last>".$timeLimit." ORDER BY S.session_last DESC", $glob['dbprefix']);
if (($results = $GLOBALS['db']->query($query)) !== false) {
    $GLOBALS['main']->addTabControl($lang['statistics']['title_customers_active'], 'stats_online', false, false, count($results));
    $smarty_data['users_online'] = array();
    foreach ($results as $user) {
        $user['is_admin']  = ((int)$user['admin_id'] > 0) ? 1 : 0;
        $user['name']   = ((int)$user['customer_id'] != 0) ? sprintf('%s %s', $user['first_name'], $user['last_name']) : $lang['common']['guest'];
        $user['session_length'] = sprintf('%.2F', ($user['session_last']-$user['session_start'])/60);
        $user['session_start'] = formatTime($user['session_start']);
        $user['session_last'] = formatTime($user['session_last']);
        $smarty_data['users_online'][] = $user;
    }
    $GLOBALS['smarty']->assign('USERS_ONLINE', $smarty_data['users_online']);
}

$GLOBALS['smarty']->assign('GRAPH_DATA', $g_graph_data);

$page_content = $GLOBALS['smarty']->fetch('templates/statistics.index.php');
