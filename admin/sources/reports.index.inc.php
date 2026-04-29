<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}
Admin::getInstance()->permissions('statistics', CC_PERM_READ, true);


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

$default_date = array('from' => date('Y-m-01'), 'to' => date('Y-m-d'));
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
    'first_name',
    'last_name',
    'company_name',
    'line1',
    'line2',
    'town',
    'state',
    'country',
    'postcode',
    'phone',
    'mobile',
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
    $tax = Tax::getInstance();
    $price_fields = array('subtotal', 'discount', 'shipping', 'total_tax', 'total');
    $xls_rows = array();

    // Accounting bracket rule for export columns: discount > 0 (positive-stored
    // deduction) and any genuinely negative price value get wrapped in (...).
    $bracket_fmt = function ($value, $field) use ($price_fields) {
        if (!in_array($field, $price_fields, true) || !is_numeric($value)) {
            return $value;
        }
        $val = (float)$value;
        $bracket = ($field === 'discount' && $val > 0) || $val < 0;
        $cell = sprintf('%.2F', abs($val));
        return $bracket ? '('.$cell.')' : $cell;
    };

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
        $order_summary['country']	= (is_numeric($order_summary['country'])) ? getCountryFormat($order_summary['country'], 'numcode', 'iso') : (getCountryFormat($order_summary['country'], 'name', 'iso') ?: $order_summary['country']);
        $order_summary['state']	= (is_numeric($order_summary['state'])) ? getStateFormat($order_summary['state']) : $order_summary['state'];
        $raw_order_date         = $order_summary['order_date'];
        $order_summary['date']	= formatTime($order_summary['order_date'], false, true);

        ## Run line of external report data
        if (isset($external_report) && is_object($external_report)) {
            $external_report->report_order_data($order_summary);
        }

        unset($order_summary['order_date'], $values);
        $raw_row = array();
        foreach ($order_summary as $field => $value) {
            if ($i == 0) {
                $headers[] = $field;
            }
            $cell = $bracket_fmt($value, $field);
            // Bracketed values contain parens — quote them so CSV parses cleanly.
            $values[] = (is_numeric($cell) || !preg_match('/[",\(\)]/', (string)$cell)) ? $cell : sprintf('"%s"', addslashes($cell));
            // Keep the xlsx row unformatted so cells become real numbers/dates.
            // Discount > 0 is stored as a deduction; flip its sign for the
            // spreadsheet so SUM() works naturally.
            if (in_array($field, $price_fields, true)) {
                $raw_val = (float)$value;
                if ($field === 'discount' && $raw_val > 0) {
                    $raw_val = -$raw_val;
                }
                $raw_row[$field] = $raw_val;
            } elseif ($field === 'date') {
                $raw_row[$field] = (int)$raw_order_date;
            } else {
                $raw_row[$field] = $value;
            }
        }
        if ($i == 0 && $add_headers) {
            $data[] = implode(',', $headers);
        }
        $data[] = implode(',', $values);
        $xls_rows[] = $raw_row;
        // Format price columns AFTER the CSV is built so the table on screen
        // shows nicely-formatted currency while the CSV stays plain numeric.
        // Accounting convention: discount > 0 (positive-stored deduction) and
        // any genuinely negative value get wrapped in (parentheses) — using
        // the absolute value inside so we don't end up with (-£5.00).
        foreach ($price_fields as $f) {
            if (isset($order_summary[$f])) {
                $val       = (float)$order_summary[$f];
                $bracket   = ($f === 'discount' && $val > 0) || $val < 0;
                $formatted = $tax->priceFormat(abs($val));
                $order_summary[$f] = $bracket ? '('.$formatted.')' : $formatted;
            }
        }
        $smarty_data['report_date'][] = $order_summary;
        $i++;
    }
    $GLOBALS['smarty']->assign('REPORT_DATE', $smarty_data['report_date']);
    if (isset($_POST['download']) || isset($_POST['download_xls']) || (isset($_POST['external_report']) && is_array($_POST['external_report']))) {
        $GLOBALS['debug']->supress(true);

        // Sortable, shell-friendly filename: ISO dates, no parens, hyphens for
        // word separation, status hint appended only when not the default (2,3).
        $iso_from = date('Y-m-d', $dates['from']);
        $iso_to   = date('Y-m-d', $dates['to']);
        $file_name = ($iso_from === $iso_to)
            ? "sales-report_{$iso_from}"
            : "sales-report_{$iso_from}_to_{$iso_to}";
        if (isset($status) && is_array($status)) {
            $sel = array_map('intval', $status);
            sort($sel);
            $defaults = array(2, 3);
            if ($sel !== $defaults) {
                $file_name .= '_status-'.implode('-', $sel);
            }
        }
        $file_ext    = 'csv';
        if (isset($_POST['download_xls'])) {
            require_once CC_CLASSES_DIR.'xlsxwriter.class.php';
            $col_types = array();
            foreach ($headers as $h) {
                if ($h === 'date') {
                    $col_types[] = XLSXWriter::TYPE_DATETIME;
                } elseif (in_array($h, $price_fields, true)) {
                    $col_types[] = XLSXWriter::TYPE_CURRENCY;
                } else {
                    $col_types[] = XLSXWriter::TYPE_STRING;
                }
            }
            $xlsx = new XLSXWriter();
            $xlsx->setSheetName($lang['reports']['sales_data'])
                 ->setHeaders($headers)
                 ->setColumnTypes($col_types);
            foreach ($xls_rows as $row) {
                $ordered = array();
                foreach ($headers as $h) {
                    $ordered[] = $row[$h] ?? '';
                }
                $xlsx->addRow($ordered);
            }
            // Totals row — flip discount sign to match per-row convention.
            $totals = array();
            foreach ($headers as $idx => $field) {
                if (isset($tally[$field])) {
                    $val = (float)$tally[$field];
                    if ($field === 'discount' && $val > 0) {
                        $val = -$val;
                    }
                    $totals[] = $val;
                } elseif ($idx === 0) {
                    $totals[] = 'TOTAL';
                } else {
                    $totals[] = '';
                }
            }
            $xlsx->addRow($totals);
            $file_content = $xlsx->build();
            $file_ext     = 'xlsx';
        } elseif (isset($_POST['download'])) {
            // Append a totals row matching the column order of $headers so the
            // CSV ends with subtotal/discount/shipping/total_tax/total totals.
            $totals_row = array();
            foreach ($headers as $idx => $field) {
                if (isset($tally[$field])) {
                    $cell = $bracket_fmt($tally[$field], $field);
                    // Bracketed values contain parens — quote for clean parsing.
                    $totals_row[] = (is_numeric($cell) || !preg_match('/[",\(\)]/', (string)$cell)) ? $cell : sprintf('"%s"', addslashes($cell));
                } elseif ($idx === 0) {
                    $totals_row[] = 'TOTAL';
                } else {
                    $totals_row[] = '';
                }
            }
            $data[] = implode(',', $totals_row);

            $file_content = implode("\r\n", $data);
        } else {
            $file_content = $external_report->_report_data;
            $file_name    = ucfirst($module_name[0]).' '.$lang['reports']['data'].' '.$download_range;
        }
        deliverFile(false, false, $file_content, $file_name.'.'.$file_ext);
        exit;
    }
    ## Show table footer
    $tally['orders'] = count($orders);
    foreach ($tally as $key => $value) {
        if ($key === 'orders') {
            $tallyformatted[$key] = $value;
        } else {
            $val       = (float)$value;
            $bracket   = ($key === 'discount' && $val > 0) || $val < 0;
            $formatted = $tax->priceFormat(abs($val));
            $tallyformatted[$key] = $bracket ? '('.$formatted.')' : $formatted;
        }
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
