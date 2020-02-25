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

$add_headers = true;

/* Generate sales reports */
if (isset($_POST['report'])) {
    $report_filter = $_POST['report'];
} elseif (isset($_GET['report'])) {
    $report_filter = $_GET['report'];
}

/* Validate input */
$date_pattern = '/^([0-9]){4}-([0-9]){2}-([0-9]){2}$/';
if (!empty($report_filter['date']['to']) && !preg_match($date_pattern, $report_filter['date']['to'])
    || !empty($report_filter['date']['from']) && !preg_match($date_pattern, $report_filter['date']['from'])
) {
    $GLOBALS['main']->errorMessage($lang['common']['invalid_data']);
    httpredir('?_g=reports');
}

if (!isset($_POST['report']['status']) && !isset($_GET['report']['status'])) {
    $report_filter['status'] = array(0 => 2, 1 => 3);
}

$default_date = array('from' => strftime('%Y-%m-01'), 'to' => strftime('%Y-%m-%d'));
$date_range  = (isset($report_filter['date']) && is_array($report_filter['date'])) ? $report_filter['date'] : $default_date;

// Moved below so suppress/updated/inserted can be affected
foreach ($GLOBALS['hooks']->load('admin.reports.top') as $hook) {
    include $hook;
}

$i = 0;
## Date filtering
foreach ($date_range as $key => $value) {
    $date   = (!empty($value) && preg_match('#^([\d]{2,4}[/-][\d]{1,2}[/-][\d]{1,2})$#', $value)) ? $value : $default_date[$key];
    $parts   = preg_split('#[^\d]#', $date);
    $timestamp  = ($i) ? mktime(23, 59, 59, $parts[1], $parts[2], $parts[0]) : mktime(false, false, false, $parts[1], $parts[2], $parts[0]);
    $dates[$key]  = $timestamp;
    $human_date[]  = date('j M Y', $timestamp);
    ++$i;
}
unset($date, $i, $parts, $timestamp);
$where = sprintf('order_date >= %d AND order_date <= %d', $dates['from'], $dates['to']);

## Status filtering
if (isset($report_filter['status']) && is_array($report_filter['status'])) {
    foreach ($report_filter['status'] as $value) {
        $select_status[(int)$value] = true;
        $status[] = (int)$value;
    }
    $where .= sprintf(' AND `status` IN (%s)', implode(',', $status));
}

$date['from']  = $human_date[0];
if (isset($human_date[1]) && $human_date[0]!==$human_date[1]) {
    $date['to']  = $human_date[1];
    $report_title  = sprintf($lang['reports']['title_reports_from_to'], $date['from'], $date['to']);
    $download_range = "(".$date['from']." - ".$date['to'].")";
} else {
    $report_title = sprintf($lang['reports']['title_reports_from'], $date['from']);
    $download_range = "(".$date['from'].")";
}
$GLOBALS['smarty']->assign('REPORT_TITLE', $report_title);

$GLOBALS['main']->addTabControl($lang['reports']['tab_results'], 'results');
## Fetch data, and display, and/or provide download
$oid_col = $GLOBALS['config']->get('config', 'oid_mode') =='i' ?  $GLOBALS['config']->get('config', 'oid_col') : 'cart_order_id';
$fields = array(
    'order_date',
    $oid_col,
    'cart_order_id',
    'status',
    'subtotal',
    'discount',
    'shipping',
    'total_tax',
    'total',
    'customer_id',
    'title',
    'first_name',
    'last_name',
    'company_name',
    'line1',
    'line2',
    'town',
    'state',
    'country',
    'postcode',
    'title_d',
    'first_name_d',
    'last_name_d',
    'company_name_d',
    'line1_d',
    'line2_d',
    'town_d',
    'state_d',
    'country_d',
    'postcode_d',
    'phone',
    'email',
    'gateway'
);

foreach ($GLOBALS['hooks']->load('admin.reports.order.pre') as $hook) {
    include $hook;
}

$orders = $GLOBALS['db']->select('CubeCart_order_summary', $fields, $where);

foreach ($GLOBALS['hooks']->load('admin.reports.order.post') as $hook) {
    include $hook;
}

if ($orders) {
    ## If we are wanting an external report start new External class
    if (isset($_POST['external_report']) && is_array($_POST['external_report'])) {
        $module_name = array_keys($_POST['external_report']);
        $external_class_path = 'modules/external/'.$module_name[0].'/external.class.php';
        if (file_exists($external_class_path)) {
            include $external_class_path;
            $external_report = new External($GLOBALS['config']->get($module_name[0]));
        }
    }

    ## Tally up totals
    $tally = array();
    $i   = 0;
    foreach ($orders as $order_summary) {
        $order_summary['status'] = $lang['order_state']['name_'.(int)$order_summary['status']];
        foreach ($order_summary as $field => $value) {
            if (in_array($field, array('subtotal', 'discount', 'shipping', 'total_tax', 'total'))) {
                if (!isset($tally[$field])) {
                    $tally[$field] = 0;
                }
                $tally[$field] += $value;
            }
        }
        $order_summary['country']	= (is_numeric($order_summary['country'])) ? getCountryFormat($order_summary['country']) : $order_summary['country'];
        $order_summary['state']	= (is_numeric($order_summary['state'])) ? getStateFormat($order_summary['state']) : $order_summary['state'];
        $order_summary['country_d']	= (is_numeric($order_summary['country_d'])) ? getCountryFormat($order_summary['country_d']) : $order_summary['country_d'];
        $order_summary['state_d']	= (is_numeric($order_summary['state_d'])) ? getStateFormat($order_summary['state_d']) : $order_summary['state_d'];
        $order_summary['date']	= formatTime($order_summary['order_date'], false, true);

        ## Run line of external report data
        if (isset($external_report) && is_object($external_report)) {
            $external_report->report_order_data($order_summary);
        }

        unset($order_summary['order_date'], $values);
        foreach ($order_summary as $field => $value) {
            if ($i == 0) {
                $headers[] = $field;
            }
            $values[] = (is_numeric($value) || !strpos($value, ',')) ? $value : sprintf('"%s"', addslashes($value));
        }
        if ($i == 0 && $add_headers) {
            $data[] = implode(',', $headers);
        }
        $data[] = implode(',', $values);
        $smarty_data['report_date'][] = $order_summary;
        $i++;
    }
    $GLOBALS['smarty']->assign('REPORT_DATE', $smarty_data['report_date']);
    if (isset($_POST['download']) || (isset($_POST['external_report']) && is_array($_POST['external_report']))) {
        $GLOBALS['debug']->supress(true);
        if (isset($_POST['download'])) {
            $file_content  = implode("\r\n", $data);
            $file_name   = $lang['reports']['sales_data'].' '.$download_range;
        } else {
            $file_content  = $external_report->_report_data;
            $file_name   = ucfirst($module_name[0]).' '.$lang['reports']['data'].' '.$download_range;
        }
        deliverFile(false, false, $file_content, $file_name.'.csv');
        exit;
    }
    ## Show table footer
    $tally['orders'] = count($orders);
    foreach ($tally as $key => $value) {
        $tallyformatted[$key] = ($key=='orders') ? $value : sprintf('%.2F', $value);
    }
    $smarty_data['tally']  = $tallyformatted;
    $GLOBALS['smarty']->assign('DOWNLOAD', true);


    ## Get external module export code
    $where  = array('module' => 'external', 'status' => '1');
    ## Start classes for external reports
    if (($module = $GLOBALS['db']->select('CubeCart_modules', 'folder', $where)) !== false) {
        foreach ($module as $module_data) {
            $export_folder = CC_ROOT_DIR.'/modules/external/'.$module_data['folder'];
            $name = '';
            if (file_exists($export_folder)) {
                if(file_exists($export_folder.'/config.xml')) {
                    $xml = simplexml_load_file($export_folder.'/config.xml');
                    $name = (string)$xml->info->name;
                }
                $module_data['description'] = !empty($name) ? $name : ucfirst(str_replace('_', ' ', $module_data['folder']));
                $smarty_data['export'][] = $module_data;
            }
        }
        $GLOBALS['smarty']->assign('EXPORT', $smarty_data['export']);
    }
} else {
    if (isset($_POST['download'])) {
        httpredir(currentPage());
    }
    $smarty_data['tally'] = array('orders' => 0);
}
$GLOBALS['smarty']->assign('TALLY', $smarty_data['tally']);
$GLOBALS['smarty']->assign('POST', $report_filter);

foreach ($GLOBALS['hooks']->load('admin.reports.order.filter') as $hook) {
    include $hook;
}

/* Show report builder options */

$GLOBALS['main']->addTabControl($lang['common']['filter'], 'search');

for ($i = 1; $i <= 6; ++$i) {
    $status = array(
        'value'  => $i,
        'selected' => (!is_array($report_filter['status']) || (isset($select_status[$i]) && $select_status[$i])) ? ' selected="selected"' : '',
        'name'  => $lang['order_state']['name_'.$i],
    );
    $smarty_data['status'][] = $status;
}
$GLOBALS['smarty']->assign('STATUS', $smarty_data['status']);

foreach ($GLOBALS['hooks']->load('admin.reports.final') as $hook) {
    include $hook;
}

$page_content = $GLOBALS['smarty']->fetch('templates/reports.index.php');

foreach ($GLOBALS['hooks']->load('admin.reports.display') as $hook) {
    include $hook;
}
