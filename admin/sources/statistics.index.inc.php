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

// Active tab + request mode (AJAX-per-tab loading).
// Plugins may push their own tab IDs into $valid_tabs via this hook so that
// `?tab=plugin_x` is accepted by the active-tab gate. Plugin content itself
// is rendered via the existing `admin.statistics.tabs` hook below — plugins
// gate their own SQL on $active_tab to opt into lazy loading.
$valid_tabs  = array('stats_sales', 'stats_prod_sales', 'stats_prod_views', 'stats_search', 'stats_best_customers', 'stats_online');
foreach ($GLOBALS['hooks']->load('admin.statistics.tabs.register') as $hook) {
    include $hook;
}
$active_tab  = (isset($_GET['tab']) && in_array($_GET['tab'], $valid_tabs, true)) ? $_GET['tab'] : 'stats_sales';
$is_fragment = isset($_GET['format']) && $_GET['format'] === 'fragment';

if ($is_fragment) {
    // Strip the fragment marker out of REQUEST_URI so pagination links (built
    // from it) don't carry format=fragment back into user-facing navigation.
    $_SERVER['REQUEST_URI'] = preg_replace('/([?&])format=fragment(&|$)/', '$1', $_SERVER['REQUEST_URI']);
    $_SERVER['REQUEST_URI'] = preg_replace('/[?&]$/', '', $_SERVER['REQUEST_URI']);
}

$select['year']   = (isset($_GET['year']) && is_numeric($_GET['year'])) ? (int)$_GET['year'] : date('Y');
$select['month']  = (isset($_GET['month']) && in_array($_GET['month'], range(1, 12))) ? str_pad((int)$_GET['month'], 2, '0', STR_PAD_LEFT) : date('m');
$select['day']    = (isset($_GET['day']) && in_array($_GET['day'], range(1, 31))) ? str_pad((int)$_GET['day'], 2, '0', STR_PAD_LEFT) : date('d');
$select['status'] = (isset($_GET['status']) && in_array($_GET['status'], range(1, 6))) ? (int)$_GET['status'] : 3;

// Online users filter applies to both the badge count and the full table.
$timeLimit = time() - 1800; // 30 minutes
if (isset($_GET['bots']) && $_GET['bots'] == 'false') {
    $online_filter = '(S.session_last > S.session_start) AND ';
    $GLOBALS['smarty']->assign('BOTS', false);
} else {
    $online_filter = '';
    $GLOBALS['smarty']->assign('BOTS', true);
}

// Cheap count for the stats_online tab badge.
$online_count_q = $GLOBALS['db']->query(sprintf(
    "SELECT COUNT(*) AS c FROM `%1\$sCubeCart_sessions` AS S WHERE S.acp = 0 AND %2\$sS.session_last > %3\$d",
    $glob['dbprefix'], $online_filter, $timeLimit
));
$online_count = $online_count_q ? (int)$online_count_q[0]['c'] : 0;

// Register every tab button up front so the strip is consistent regardless of which tab is active.
$GLOBALS['main']->addTabControl($lang['statistics']['title_sales'],            'stats_sales');
$GLOBALS['main']->addTabControl($lang['statistics']['title_popular'],          'stats_prod_sales');
$GLOBALS['main']->addTabControl($lang['statistics']['title_viewed'],           'stats_prod_views');
$GLOBALS['main']->addTabControl($lang['statistics']['title_search'],           'stats_search');
$GLOBALS['main']->addTabControl($lang['statistics']['title_customers_best'],   'stats_best_customers');
$GLOBALS['main']->addTabControl($lang['statistics']['title_customers_active'], 'stats_online', false, false, $online_count);

$g_graph_data = array();
$smarty_data  = array();

// Run only the active tab's SQL.
switch ($active_tab) {

case 'stats_sales':
    // Order-status checkboxes. Default to Processing+Complete.
    $default_statuses = array(2, 3);
    if (isset($_GET['s']) && is_array($_GET['s'])) {
        $statuses = array_values(array_unique(array_filter(array_map('intval', $_GET['s']), function ($s) {
            return $s >= 1 && $s <= 6;
        })));
    } else {
        $statuses = $default_statuses;
    }
    if (empty($statuses)) $statuses = $default_statuses;
    sort($statuses);
    $status_in = '(' . implode(',', $statuses) . ')';

    $status_options = array();
    for ($i = 1; $i <= 6; $i++) {
        $status_options[] = array(
            'id'      => $i,
            'name'    => $lang['order_state']['name_'.$i] ?? ('Status '.$i),
            'checked' => in_array($i, $statuses, true) ? ' checked="checked"' : '',
        );
    }
    $GLOBALS['smarty']->assign('SALES_STATUSES', $status_options);

    $earliest_order = $GLOBALS['db']->query(sprintf(
        "SELECT MIN(`order_date`) AS `MIN_order_date` FROM `%sCubeCart_order_summary` WHERE `status` IN %s",
        $glob['dbprefix'], $status_in
    ));
    $yearly = $monthly_curr = $monthly_prior = $daily = $hourly = array();
    $totals = array(1 => array(0,0), 2 => array(0,0), 3 => array(0,0), 4 => array(0,0));

    if (!empty($earliest_order[0]['MIN_order_date'])) {
        $earliest = array(
            'year'  => (int)date('Y', $earliest_order[0]['MIN_order_date']),
            'month' => date('m', $earliest_order[0]['MIN_order_date']),
            'day'   => date('d', $earliest_order[0]['MIN_order_date']),
        );
        $now_year = (int)date('Y');

        // Per-chart filter selectors. Each chart owns its own URL params so
        // changing one chart's filter doesn't bleed into the others.
        $sf = array(
            'm_year'  => (isset($_GET['m_year'])  && is_numeric($_GET['m_year']))                          ? (int)$_GET['m_year']                                  : $now_year,
            'm_month' => (isset($_GET['m_month']) && in_array((int)$_GET['m_month'], range(1,12)))         ? str_pad((int)$_GET['m_month'], 2, '0', STR_PAD_LEFT)  : date('m'),
            'd_year'  => (isset($_GET['d_year'])  && is_numeric($_GET['d_year']))                          ? (int)$_GET['d_year']                                  : $now_year,
            'd_month' => (isset($_GET['d_month']) && in_array((int)$_GET['d_month'], range(1,12)))         ? str_pad((int)$_GET['d_month'], 2, '0', STR_PAD_LEFT)  : date('m'),
            'h_year'  => (isset($_GET['h_year'])  && is_numeric($_GET['h_year']))                          ? (int)$_GET['h_year']                                  : $now_year,
            'h_month' => (isset($_GET['h_month']) && in_array((int)$_GET['h_month'], range(1,12)))         ? str_pad((int)$_GET['h_month'], 2, '0', STR_PAD_LEFT)  : date('m'),
            'h_day'   => (isset($_GET['h_day'])   && in_array((int)$_GET['h_day'],   range(1,31)))         ? str_pad((int)$_GET['h_day'],   2, '0', STR_PAD_LEFT)  : date('d'),
        );

        // Date-range bounds per chart.
        $month_year_start = mktime(0, 0, 0, 1, 1, (int)$sf['m_year']);
        $month_year_end   = mktime(0, 0, 0, 1, 1, (int)$sf['m_year'] + 1);
        $prior_year_start = mktime(0, 0, 0, 1, 1, (int)$sf['m_year'] - 1);
        $day_month_start  = mktime(0, 0, 0, (int)$sf['d_month'], 1, (int)$sf['d_year']);
        $day_month_end    = mktime(0, 0, 0, (int)$sf['d_month'] + 1, 1, (int)$sf['d_year']);
        $hour_day_start   = mktime(0, 0, 0, (int)$sf['h_month'], (int)$sf['h_day'], (int)$sf['h_year']);
        $hour_day_end     = mktime(0, 0, 0, (int)$sf['h_month'], (int)$sf['h_day'] + 1, (int)$sf['h_year']);

        // Yearly + chart 1 totals.
        $yearly_rows = $GLOBALS['db']->query(sprintf(
            "SELECT FROM_UNIXTIME(`order_date`, '%%Y') AS bucket, SUM(`total`) AS s, COUNT(*) AS c FROM `%sCubeCart_order_summary` WHERE `status` IN %s GROUP BY bucket",
            $glob['dbprefix'], $status_in
        ));
        if ($yearly_rows) {
            foreach ($yearly_rows as $row) {
                $yearly[$row['bucket']] = (float)$row['s'];
                $totals[1][0] += (float)$row['s'];
                $totals[1][1] += (int)$row['c'];
            }
        }

        // Monthly query covers BOTH selected year and the prior year for YoY overlay.
        $monthly_rows = $GLOBALS['db']->query(sprintf(
            "SELECT FROM_UNIXTIME(`order_date`, '%%Y') AS y, FROM_UNIXTIME(`order_date`, '%%m') AS m, SUM(`total`) AS s, COUNT(*) AS c FROM `%sCubeCart_order_summary` WHERE `status` IN %s AND `order_date` >= %d AND `order_date` < %d GROUP BY y, m",
            $glob['dbprefix'], $status_in, $prior_year_start, $month_year_end
        ));
        if ($monthly_rows) {
            $sf_my   = (int)$sf['m_year'];
            $sf_my_p = $sf_my - 1;
            foreach ($monthly_rows as $row) {
                $yr = (int)$row['y'];
                if ($yr === $sf_my) {
                    $monthly_curr[$row['m']] = (float)$row['s'];
                    $totals[2][0] += (float)$row['s'];
                    $totals[2][1] += (int)$row['c'];
                } elseif ($yr === $sf_my_p) {
                    $monthly_prior[$row['m']] = (float)$row['s'];
                }
            }
        }

        $daily_rows = $GLOBALS['db']->query(sprintf(
            "SELECT FROM_UNIXTIME(`order_date`, '%%d') AS bucket, SUM(`total`) AS s, COUNT(*) AS c FROM `%sCubeCart_order_summary` WHERE `status` IN %s AND `order_date` >= %d AND `order_date` < %d GROUP BY bucket",
            $glob['dbprefix'], $status_in, $day_month_start, $day_month_end
        ));
        if ($daily_rows) {
            foreach ($daily_rows as $row) {
                $daily[$row['bucket']] = (float)$row['s'];
                $totals[3][0] += (float)$row['s'];
                $totals[3][1] += (int)$row['c'];
            }
        }

        $hourly_rows = $GLOBALS['db']->query(sprintf(
            "SELECT FROM_UNIXTIME(`order_date`, '%%H') AS bucket, SUM(`total`) AS s, COUNT(*) AS c FROM `%sCubeCart_order_summary` WHERE `status` IN %s AND `order_date` >= %d AND `order_date` < %d GROUP BY bucket",
            $glob['dbprefix'], $status_in, $hour_day_start, $hour_day_end
        ));
        if ($hourly_rows) {
            foreach ($hourly_rows as $row) {
                $hourly[$row['bucket']] = (float)$row['s'];
                $totals[4][0] += (float)$row['s'];
                $totals[4][1] += (int)$row['c'];
            }
        }

        // Per-year colours, golden-angle hue rotation — matches dashboard year cards.
        $hsl_to_hex = function ($h, $s, $l) {
            $h = (($h % 360) + 360) % 360 / 360;
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $hue = function ($t) use ($p, $q) {
                if ($t < 0) $t += 1;
                if ($t > 1) $t -= 1;
                if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
                if ($t < 1/2) return $q;
                if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
                return $p;
            };
            return sprintf('#%02x%02x%02x', (int)round($hue($h + 1/3) * 255), (int)round($hue($h) * 255), (int)round($hue($h - 1/3) * 255));
        };
        $year_color = array();
        $idx = 0;
        for ($i = $earliest['year']; $i <= $now_year; ++$i) {
            $year_color[$i] = $hsl_to_hex((int)round(fmod(210 + $idx * 137.5, 360)), 0.65, 0.45);
            $idx++;
        }
        $color_for = function ($yr) use ($year_color) {
            return $year_color[(int)$yr] ?? reset($year_color);
        };

        // Build per-chart year/month/day option lists. Future periods (relative
        // to today) are flagged as `disabled` so impossible selections are greyed.
        $now_month = (int)date('m');
        $now_day   = (int)date('d');
        $year_options = function ($selected_year) use ($earliest, $now_year) {
            $out = array();
            for ($i = $earliest['year']; $i <= $now_year; ++$i) {
                $out[] = array('value' => $i, 'selected' => ($selected_year == $i) ? ' selected="selected"' : '');
            }
            return $out;
        };
        $month_options = function ($selected_month, $year_for_bounds) use ($now_year, $now_month) {
            $out = array();
            $cap = ((int)$year_for_bounds === $now_year) ? $now_month : 12;
            for ($i = 1; $i <= 12; ++$i) {
                $padded = str_pad($i, 2, '0', STR_PAD_LEFT);
                $out[] = array(
                    'value'    => $padded,
                    'title'    => date('F', mktime(0, 0, 0, $i, 1)),
                    'selected' => ((int)$selected_month == $i) ? ' selected="selected"' : '',
                    'disabled' => ($i > $cap) ? ' disabled="disabled"' : '',
                );
            }
            return $out;
        };
        $day_options = function ($selected_day, $year, $month) use ($now_year, $now_month, $now_day) {
            $out = array();
            $len = date('t', mktime(0, 0, 0, (int)$month, 1, (int)$year));
            $cap = ((int)$year === $now_year && (int)$month === $now_month) ? $now_day : $len;
            for ($d = 1; $d <= $len; ++$d) {
                $out[] = array(
                    'value'    => $d,
                    'selected' => ((int)$selected_day == $d) ? ' selected="selected"' : '',
                    'disabled' => ($d > $cap) ? ' disabled="disabled"' : '',
                );
            }
            return $out;
        };
        $GLOBALS['smarty']->assign('M_YEARS',  $year_options($sf['m_year']));
        $GLOBALS['smarty']->assign('M_MONTHS', $month_options($sf['m_month'], $sf['m_year']));
        $GLOBALS['smarty']->assign('D_YEARS',  $year_options($sf['d_year']));
        $GLOBALS['smarty']->assign('D_MONTHS', $month_options($sf['d_month'], $sf['d_year']));
        $GLOBALS['smarty']->assign('H_YEARS',  $year_options($sf['h_year']));
        $GLOBALS['smarty']->assign('H_MONTHS', $month_options($sf['h_month'], $sf['h_year']));
        $GLOBALS['smarty']->assign('H_DAYS',   $day_options($sf['h_day'], $sf['h_year'], $sf['h_month']));
        $GLOBALS['smarty']->assign('SALES_FILTER', $sf);

        // Y-axis currency format: extract symbol from a sample priceFormat call.
        $_sample = Tax::getInstance()->priceFormat(0);
        $_sym    = '';
        if (preg_match('/^([^\d\s\-\.]+)/u', $_sample, $_m)) {
            $_sym = $_m[1];
        }
        $y_axis_format = ($_sym ? $_sym : '') . '#,###';
        $tax = Tax::getInstance();

        // Chart 1: Yearly. Each bar gets its own year colour.
        if (count($yearly) >= 1) {
            $g_graph_data[1]['data'] = "['Year','".sprintf($lang['statistics']['sales_volume'], $GLOBALS['config']->get('config', 'default_currency'))."',{role:'style'}],";
            $tmp = array();
            for ($i = $earliest['year']; $i <= $now_year; ++$i) {
                $value = isset($yearly[$i]) ? $yearly[$i] : 0;
                $tmp[] = "['".$i."',".$value.",'color: ".$year_color[$i]."']";
            }
            $g_graph_data[1]['data']    .= implode(',', $tmp);
            $g_graph_data[1]['title']    = ($earliest['year'] == $now_year) ? sprintf($lang['statistics']['sales_in'], $now_year) : sprintf($lang['statistics']['sales_from_to'], $earliest['year'], $now_year);
            $g_graph_data[1]['hAxis']    = '';
            $g_graph_data[1]['vAxis']    = '';
            $g_graph_data[1]['legend']   = 'none';
            $g_graph_data[1]['y_format'] = $y_axis_format;
            $g_graph_data[1]['drill']    = array('type' => 'year', 'years' => range($earliest['year'], $now_year));
        }
        $g_graph_data[1]['total_sum']    = $tax->priceFormat((float)$totals[1][0]);
        $g_graph_data[1]['total_count']  = number_format((int)$totals[1][1]);

        // Chart 2: Monthly current vs prior year (YoY overlay).
        // Drop the prior-year series when it's before the store's earliest order;
        // there's no data and the colour-fallback makes the two legends collide.
        $sf_my_p     = (int)$sf['m_year'] - 1;
        $show_prior  = $sf_my_p >= $earliest['year'];
        if ($show_prior) {
            $g_graph_data[2]['data'] = "['Month','".$sf['m_year']."','".$sf_my_p."'],";
        } else {
            $g_graph_data[2]['data'] = "['Month','".$sf['m_year']."'],";
        }
        $tmp = array();
        for ($i = 1; $i <= 12; ++$i) {
            $padded = str_pad($i, 2, '0', STR_PAD_LEFT);
            $cv = $monthly_curr[$padded] ?? 0;
            $row = "['".date('M', mktime(0, 0, 0, $i, 1))."',".$cv;
            if ($show_prior) {
                $pv = $monthly_prior[$padded] ?? 0;
                $row .= ",".$pv;
            }
            $tmp[] = $row . "]";
        }
        $g_graph_data[2]['data']      .= implode(',', $tmp);
        $g_graph_data[2]['title']      = sprintf($lang['statistics']['sales_in_year'], $sf['m_year']);
        $g_graph_data[2]['hAxis']      = '';
        $g_graph_data[2]['vAxis']      = '';
        $g_graph_data[2]['colors']     = $show_prior
            ? "['".$color_for($sf['m_year'])."','".$color_for($sf_my_p)."']"
            : "['".$color_for($sf['m_year'])."']";
        $g_graph_data[2]['y_format']   = $y_axis_format;
        $g_graph_data[2]['drill']      = array('type' => 'month', 'year' => (int)$sf['m_year']);
        $g_graph_data[2]['total_sum']  = $tax->priceFormat((float)$totals[2][0]);
        $g_graph_data[2]['total_count']= number_format((int)$totals[2][1]);

        // Chart 3: Daily for $sf['d_year']/$sf['d_month'].
        $d_month_length = date('t', mktime(0, 0, 0, (int)$sf['d_month'], 1, (int)$sf['d_year']));
        $g_graph_data[3]['data'] = "['Day','".sprintf($lang['statistics']['sales_volume'], $GLOBALS['config']->get('config', 'default_currency'))."'],";
        $tmp = array();
        for ($i = 1; $i <= $d_month_length; ++$i) {
            $padded = str_pad($i, 2, '0', STR_PAD_LEFT);
            $value  = isset($daily[$padded]) ? $daily[$padded] : 0;
            $tmp[]  = "['".(int)$padded."',".$value."]";
        }
        $g_graph_data[3]['data']      .= implode(',', $tmp);
        $g_graph_data[3]['title']      = sprintf($lang['statistics']['sales_in_month_year'], date('F', mktime(0, 0, 0, (int)$sf['d_month'], 1)), $sf['d_year']);
        $g_graph_data[3]['hAxis']      = '';
        $g_graph_data[3]['vAxis']      = '';
        $g_graph_data[3]['colors']     = "['".$color_for($sf['d_year'])."']";
        $g_graph_data[3]['y_format']   = $y_axis_format;
        $g_graph_data[3]['drill']      = array('type' => 'day', 'year' => (int)$sf['d_year'], 'month' => $sf['d_month']);
        $g_graph_data[3]['total_sum']  = $tax->priceFormat((float)$totals[3][0]);
        $g_graph_data[3]['total_count']= number_format((int)$totals[3][1]);

        // Chart 4: Hourly for $sf['h_year']/$sf['h_month']/$sf['h_day'].
        $g_graph_data[4]['data'] = "['Hour','".sprintf($lang['statistics']['sales_volume'], $GLOBALS['config']->get('config', 'default_currency'))."'],";
        $tmp = array();
        for ($i = 0; $i <= 23; ++$i) {
            $padded = str_pad($i, 2, '0', STR_PAD_LEFT);
            $value  = isset($hourly[$padded]) ? $hourly[$padded] : 0;
            $tmp[]  = "['".$padded.":00',".$value."]";
        }
        $g_graph_data[4]['data']      .= implode(',', $tmp);
        $g_graph_data[4]['title']      = sprintf($lang['statistics']['sales_on_dmy'], (int)$sf['h_day'], date('F', mktime(0, 0, 0, (int)$sf['h_month'], 1)), $sf['h_year']);
        $g_graph_data[4]['hAxis']      = '';
        $g_graph_data[4]['vAxis']      = '';
        $g_graph_data[4]['colors']     = "['".$color_for($sf['h_year'])."']";
        $g_graph_data[4]['y_format']   = $y_axis_format;
        $g_graph_data[4]['total_sum']  = $tax->priceFormat((float)$totals[4][0]);
        $g_graph_data[4]['total_count']= number_format((int)$totals[4][1]);

        $GLOBALS['smarty']->assign('DISPLAY_SALES', true);
    }
    break;

case 'stats_prod_sales':
    // Filter: year (or 'all'). Default all-time.
    $ps_year_raw = isset($_GET['ps_year']) ? $_GET['ps_year'] : 'all';
    $ps_year     = ($ps_year_raw === 'all' || !is_numeric($ps_year_raw)) ? 'all' : (int)$ps_year_raw;

    $per_page = 15;
    $page     = (isset($_GET['page_sales']) && is_numeric($_GET['page_sales'])) ? (int)$_GET['page_sales'] : 1;
    $offset   = ($page - 1) * $per_page;

    $now_year = (int)date('Y');
    $where_date  = '';
    $prior_start = null;
    $prior_end   = null;
    if ($ps_year !== 'all') {
        $year_start = mktime(0, 0, 0, 1, 1, $ps_year);
        if ($ps_year === $now_year) {
            $year_end  = time();
            $prior_end = strtotime('-1 year', $year_end);
        } else {
            $year_end  = mktime(0, 0, 0, 1, 1, $ps_year + 1);
            $prior_end = $year_start;
        }
        $prior_start = mktime(0, 0, 0, 1, 1, $ps_year - 1);
        $where_date  = " AND `S`.`order_date` >= ".$year_start." AND `S`.`order_date` < ".$year_end;
    }

    $order_by = '`t`.`quan` DESC';

    // Year filter options always built so the filter form keeps showing
    // even when the active year has no results (otherwise the user has no way back).
    $earliest_q = $GLOBALS['db']->query("SELECT MIN(`order_date`) AS `m` FROM `".$glob['dbprefix']."CubeCart_order_summary` WHERE `status` IN (2,3)");
    $earliest_year = ($earliest_q && !empty($earliest_q[0]['m'])) ? (int)date('Y', $earliest_q[0]['m']) : $now_year;
    $ps_year_options = array(
        array('value' => 'all', 'label' => 'All time', 'selected' => $ps_year === 'all' ? ' selected="selected"' : ''),
    );
    for ($yr = $now_year; $yr >= $earliest_year; $yr--) {
        $ps_year_options[] = array(
            'value'    => $yr,
            'label'    => $yr,
            'selected' => ($ps_year === $yr) ? ' selected="selected"' : '',
        );
    }

    // Per-year colour (same golden-angle hue rotation as the Sales tab so
    // 2024=blue, 2025=red, 2026=green stay consistent across the page).
    $hsl_to_hex = function ($h, $s, $l) {
        $h = (($h % 360) + 360) % 360 / 360;
        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;
        $hue = function ($t) use ($p, $q) {
            if ($t < 0) $t += 1;
            if ($t > 1) $t -= 1;
            if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
            if ($t < 1/2) return $q;
            if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
            return $p;
        };
        return sprintf('#%02x%02x%02x', (int)round($hue($h + 1/3) * 255), (int)round($hue($h) * 255), (int)round($hue($h - 1/3) * 255));
    };
    if ($ps_year === 'all') {
        // All-time gets the next slot in the hue rotation so it stays distinct
        // from any per-year bar.
        $idx_for_color = ($now_year - $earliest_year) + 1;
    } else {
        $idx_for_color = max(0, $ps_year - $earliest_year);
    }
    $ps_chart_color = $hsl_to_hex((int)round(fmod(210 + $idx_for_color * 137.5, 360)), 0.65, 0.45);

    $GLOBALS['smarty']->assign('PS_YEAR_OPTIONS', $ps_year_options);
    $GLOBALS['smarty']->assign('PS_YEAR',         $ps_year);
    $GLOBALS['smarty']->assign('PS_HAS_TREND',    $prior_start !== null);

    $query = "SELECT `t`.`quan`, `t`.`revenue`, `t`.`product_id`, `I`.`name`, `I`.`stock_level`, `I`.`use_stock_level` "
           . "FROM (SELECT SUM(`O`.`quantity`) AS `quan`, SUM(`O`.`price` * `O`.`quantity`) AS `revenue`, `O`.`product_id` "
           .       "FROM `".$glob['dbprefix']."CubeCart_order_inventory` AS `O` "
           .       "INNER JOIN `".$glob['dbprefix']."CubeCart_order_summary` AS `S` ON `S`.`cart_order_id` = `O`.`cart_order_id` "
           .       "WHERE `S`.`status` IN (2,3)".$where_date." "
           .       "GROUP BY `O`.`product_id`) AS `t` "
           . "INNER JOIN `".$glob['dbprefix']."CubeCart_inventory` AS `I` ON `I`.`product_id` = `t`.`product_id` "
           . "ORDER BY ".$order_by." LIMIT ".(int)$per_page." OFFSET ".(int)$offset;

    if (($results = $GLOBALS['db']->query($query)) !== false && !empty($results)) {
        $numrows_result = $GLOBALS['db']->query("SELECT COUNT(DISTINCT `O`.`product_id`) AS `c` FROM `".$glob['dbprefix']."CubeCart_order_inventory` AS `O` INNER JOIN `".$glob['dbprefix']."CubeCart_order_summary` AS `S` ON `S`.`cart_order_id` = `O`.`cart_order_id` WHERE `S`.`status` IN (2,3)".$where_date);
        $numrows        = $numrows_result ? (int)$numrows_result[0]['c'] : 0;

        $divider = $GLOBALS['db']->query("SELECT SUM(`O`.`quantity`) as `totalProducts`, SUM(`O`.`price` * `O`.`quantity`) as `totalRevenue` FROM `".$glob['dbprefix']."CubeCart_order_inventory` AS `O` INNER JOIN `".$glob['dbprefix']."CubeCart_order_summary` AS `S` ON `S`.`cart_order_id` = `O`.`cart_order_id` WHERE `S`.`status` IN (2,3)".$where_date);
        $total_qty = (float)($divider[0]['totalProducts'] ?? 0);
        $total_rev = (float)($divider[0]['totalRevenue'] ?? 0);

        // Prior-period quantities for the products we're displaying — for the trend column.
        $prior_qty = array();
        if ($prior_start !== null && !empty($results)) {
            $ids = array_map('intval', array_column($results, 'product_id'));
            $ids_in = '(' . implode(',', $ids) . ')';
            $prior_q = $GLOBALS['db']->query("SELECT `O`.`product_id`, SUM(`O`.`quantity`) AS `quan` FROM `".$glob['dbprefix']."CubeCart_order_inventory` AS `O` INNER JOIN `".$glob['dbprefix']."CubeCart_order_summary` AS `S` ON `S`.`cart_order_id` = `O`.`cart_order_id` WHERE `S`.`status` IN (2,3) AND `O`.`product_id` IN ".$ids_in." AND `S`.`order_date` >= ".$prior_start." AND `S`.`order_date` < ".$prior_end." GROUP BY `O`.`product_id`");
            if ($prior_q) {
                foreach ($prior_q as $row) {
                    $prior_qty[(int)$row['product_id']] = (int)$row['quan'];
                }
            }
        }

        $tax = Tax::getInstance();
        $g_graph_data[5]['data'] = "['".$lang['statistics']['percentage_of_sales']."','".$lang['common']['percentage']."'],";
        $smarty_data[5] = array();
        $product_ids_for_chart = array();
        foreach ($results as $key => $result) {
            $result['key']     = (($page - 1) * $per_page) + ($key + 1);
            $result['percent'] = $total_qty ? number_format(100 * ($result['quan'] / $total_qty), 2) : 0;
            $tmp_col_data[]    = "['".$result['key'].". ".addslashes($result['name'])."',".$result['percent']."]";
            $product_ids_for_chart[] = (int)$result['product_id'];

            $result['revenue_formatted'] = $tax->priceFormat((float)$result['revenue']);
            $result['quan']              = number_format((int)$result['quan']);
            $result['stock_display']     = ((int)$result['use_stock_level'] === 1) ? number_format((int)$result['stock_level']) : '&mdash;';
            $result['stock_low']         = ((int)$result['use_stock_level'] === 1) && (int)$result['stock_level'] < 10;

            // Trend
            $current = (int)str_replace(',', '', $result['quan']);
            $prior   = $prior_qty[(int)$result['product_id']] ?? 0;
            if ($prior_start === null) {
                $result['trend'] = null;
            } elseif ($prior === 0 && $current === 0) {
                $result['trend'] = null;
            } elseif ($prior === 0) {
                $result['trend'] = array('dir' => 'up', 'label' => 'NEW');
            } else {
                $pct = (int)round((($current - $prior) / $prior) * 100);
                $result['trend'] = array(
                    'dir'   => $pct >= 0 ? 'up' : 'down',
                    'label' => ($pct >= 0 ? '+' : '').$pct.'%',
                );
            }

            $smarty_data[5][] = $result;
        }

        $g_graph_data[5]['data'] .= isset($tmp_col_data) ? implode(',', $tmp_col_data) : '';
        unset($tmp_col_data);

        $g_graph_data[5]['title']        = $lang['statistics']['percentage_of_sales'];
        $g_graph_data[5]['hAxis']        = $lang['dashboard']['inv_products'];
        $g_graph_data[5]['vAxis']        = $lang['common']['percentage'];
        $g_graph_data[5]['colors']       = "['".$ps_chart_color."']";
        $g_graph_data[5]['drill']        = array('type' => 'product', 'product_ids' => $product_ids_for_chart);
        $g_graph_data[5]['total_sum']    = $tax->priceFormat($total_rev);
        $g_graph_data[5]['total_count']  = number_format((int)$numrows);

        $GLOBALS['smarty']->assign('PRODUCT_SALES', $smarty_data[5]);
        $GLOBALS['smarty']->assign('PAGINATION_SALES', $GLOBALS['db']->pagination($numrows, $per_page, $page, 5, 'page_sales', 'stats_prod_sales', ' ', false));

        unset($results, $result, $divider);
    }
    break;

case 'stats_prod_views':
    $per_page = 15;
    $page     = (isset($_GET['page_views']) && is_numeric($_GET['page_views'])) ? (int)$_GET['page_views'] : 1;
    $offset   = ($page - 1) * $per_page;

    $query   = "SELECT `product_id`, `popularity`, `name`, `stock_level`, `use_stock_level` FROM `".$glob['dbprefix']."CubeCart_inventory` WHERE `popularity` > 0 ORDER BY `popularity` DESC LIMIT ".(int)$per_page." OFFSET ".(int)$offset;
    $results = $GLOBALS['db']->query($query);
    if ($results !== false && !empty($results)) {
        $numrows_q = $GLOBALS['db']->query("SELECT COUNT(*) AS `c` FROM `".$glob['dbprefix']."CubeCart_inventory` WHERE `popularity` > 0");
        $numrows   = $numrows_q ? (int)$numrows_q[0]['c'] : 0;

        $divider    = $GLOBALS['db']->query("SELECT SUM(`popularity`) AS `totalHits` FROM `".$glob['dbprefix']."CubeCart_inventory` WHERE `popularity` > 0");
        $total_hits = (int)($divider[0]['totalHits'] ?? 0);

        // Year-colour: same palette as the Sales tab. No per-period filter
        // here so we use the current year's slot.
        $hsl_to_hex = function ($h, $s, $l) {
            $h = (($h % 360) + 360) % 360 / 360;
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $hue = function ($t) use ($p, $q) {
                if ($t < 0) $t += 1;
                if ($t > 1) $t -= 1;
                if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
                if ($t < 1/2) return $q;
                if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
                return $p;
            };
            return sprintf('#%02x%02x%02x', (int)round($hue($h + 1/3) * 255), (int)round($hue($h) * 255), (int)round($hue($h - 1/3) * 255));
        };
        $earliest_q    = $GLOBALS['db']->query("SELECT MIN(`order_date`) AS `m` FROM `".$glob['dbprefix']."CubeCart_order_summary` WHERE `status` IN (2,3)");
        $earliest_year = ($earliest_q && !empty($earliest_q[0]['m'])) ? (int)date('Y', $earliest_q[0]['m']) : (int)date('Y');
        $idx           = max(0, (int)date('Y') - $earliest_year);
        $pv_color      = $hsl_to_hex((int)round(fmod(210 + $idx * 137.5, 360)), 0.65, 0.45);

        $g_graph_data[6]['data'] = "['".$lang['statistics']['percentage_of_views']."','".$lang['common']['percentage']."'],";
        $product_ids_for_chart = array();
        foreach ($results as $key => $result) {
            $result['key']     = (($page - 1) * $per_page) + ($key + 1);
            $result['percent'] = $total_hits ? number_format(100 * ($result['popularity'] / $total_hits), 2) : 0;
            $tmp_col_data[]    = "['".$result['key'].". ".addslashes($result['name'])."',".$result['percent']."]";
            $product_ids_for_chart[] = (int)$result['product_id'];

            $result['popularity']    = number_format((int)$result['popularity']);
            $result['stock_display'] = ((int)$result['use_stock_level'] === 1) ? number_format((int)$result['stock_level']) : '&mdash;';
            $result['stock_low']     = ((int)$result['use_stock_level'] === 1) && (int)$result['stock_level'] < 10;

            $smarty_data['product_views'][] = $result;
        }

        $g_graph_data[6]['data'] .= implode(',', $tmp_col_data);
        unset($tmp_col_data);
        $g_graph_data[6]['title']        = $lang['statistics']['percentage_of_views'];
        $g_graph_data[6]['hAxis']        = $lang['dashboard']['inv_products'];
        $g_graph_data[6]['vAxis']        = $lang['common']['percentage'];
        $g_graph_data[6]['colors']       = "['".$pv_color."']";
        $g_graph_data[6]['drill']        = array('type' => 'product', 'product_ids' => $product_ids_for_chart);
        $g_graph_data[6]['total_sum']    = number_format($total_hits) . ' views';
        $g_graph_data[6]['total_count']  = number_format($numrows);

        $GLOBALS['smarty']->assign('PRODUCT_VIEWS', $smarty_data['product_views']);
        $GLOBALS['smarty']->assign('PAGINATION_VIEWS', $GLOBALS['db']->pagination($numrows, $per_page, $page, 5, 'page_views', 'stats_prod_views', ' ', false));
        unset($results, $result, $divider);
    }
    break;

case 'stats_search':
    $per_page = 15;
    $page     = (isset($_GET['page_search']) && is_numeric($_GET['page_search'])) ? (int)$_GET['page_search'] : 1;
    $query    = 'SELECT * FROM `'.$glob['dbprefix'].'CubeCart_search` ORDER BY hits DESC';
    if (($results = $GLOBALS['db']->query($query, $per_page, $page)) !== false && !empty($results)) {
        $numrows    = $GLOBALS['db']->numrows($query);
        $divider    = $GLOBALS['db']->query("SELECT SUM(hits) as `totalHits` FROM  `".$glob['dbprefix']."CubeCart_search`");
        $total_hits = (int)($divider[0]['totalHits'] ?? 0);

        // Year-colour palette, current year's slot.
        $hsl_to_hex = function ($h, $s, $l) {
            $h = (($h % 360) + 360) % 360 / 360;
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $hue = function ($t) use ($p, $q) {
                if ($t < 0) $t += 1;
                if ($t > 1) $t -= 1;
                if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
                if ($t < 1/2) return $q;
                if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
                return $p;
            };
            return sprintf('#%02x%02x%02x', (int)round($hue($h + 1/3) * 255), (int)round($hue($h) * 255), (int)round($hue($h - 1/3) * 255));
        };
        $earliest_q    = $GLOBALS['db']->query("SELECT MIN(`order_date`) AS `m` FROM `".$glob['dbprefix']."CubeCart_order_summary` WHERE `status` IN (2,3)");
        $earliest_year = ($earliest_q && !empty($earliest_q[0]['m'])) ? (int)date('Y', $earliest_q[0]['m']) : (int)date('Y');
        $idx           = max(0, (int)date('Y') - $earliest_year);
        $search_color  = $hsl_to_hex((int)round(fmod(210 + $idx * 137.5, 360)), 0.65, 0.45);

        $g_graph_data[7]['data'] = "['".$lang['statistics']['percentage_of_views']."','".$lang['common']['percentage']."'],";

        $smarty_data['search_terms'] = array();
        $search_terms_for_chart = array();
        foreach ($results as $key => $result) {
            $result['percent']    = $total_hits ? number_format(100 * ($result['hits'] / $total_hits), 2) : 0;
            $result['key']        = (($page - 1) * $per_page) + ($key + 1);
            $result['searchstr']  = ucfirst(strtolower($result['searchstr']));
            $tmp_col_data[]       = "['".$result['key'].". ".addslashes($result['searchstr'])."',".$result['percent']."]";
            $search_terms_for_chart[] = $result['searchstr'];
            $result['hits']       = number_format((int)$result['hits']);
            $result['search_url'] = $GLOBALS['storeURL'].'/index.php?_a=search&search[keywords]='.urlencode($result['searchstr']);
            $smarty_data['search_terms'][] = $result;
        }

        $g_graph_data[7]['data'] .= isset($tmp_col_data) ? implode(',', $tmp_col_data) : '';
        unset($tmp_col_data);
        $g_graph_data[7]['title']        = '';
        $g_graph_data[7]['hAxis']        = $lang['statistics']['search_term'];
        $g_graph_data[7]['vAxis']        = $lang['statistics']['percentage_of_search'];
        $g_graph_data[7]['colors']       = "['".$search_color."']";
        $g_graph_data[7]['drill']        = array('type' => 'search', 'terms' => $search_terms_for_chart, 'storeURL' => $GLOBALS['storeURL']);
        $g_graph_data[7]['total_sum']    = number_format($total_hits) . ' searches';
        $g_graph_data[7]['total_count']  = number_format((int)$numrows);

        $GLOBALS['smarty']->assign('SEARCH_TERMS', $smarty_data['search_terms']);
        $GLOBALS['smarty']->assign('PAGINATION_SEARCH', $GLOBALS['db']->pagination($numrows, $per_page, $page, 5, 'page_search', 'stats_search', ' ', false));
        unset($results, $result, $divider);
    }
    break;

case 'stats_best_customers':
    $per_page = 15;
    $page     = (isset($_GET['page_customers']) && is_numeric($_GET['page_customers'])) ? $_GET['page_customers'] : 1;
    $query    = "SELECT sum(`total`) as `customer_expenditure`, C.first_name, C.last_name, C.customer_id FROM `".$glob['dbprefix']."CubeCart_order_summary` as O INNER JOIN  `".$glob['dbprefix']."CubeCart_customer` as C on O.customer_id = C.customer_id WHERE O.status = 3 GROUP BY O.customer_id ORDER BY `customer_expenditure` DESC";
    if (($results = $GLOBALS['db']->query($query, $per_page, $page)) !== false) {
        $numrows = $GLOBALS['db']->numrows($query);
        $divider = $GLOBALS['db']->query("SELECT sum(`total`) as `total_sales` FROM `".$glob['dbprefix']."CubeCart_order_summary` WHERE `status` = 3");

        $g_graph_data[8]['data'] = "['".$lang['statistics']['percentage_of_views']."','".sprintf($lang['statistics']['sales_volume'], $GLOBALS['config']->get('config', 'default_currency'))."'],";

        $smarty_data[8] = array();
        foreach ($results as $key => $result) {
            $result['key']         = (($page - 1) * $per_page) + ($key + 1);
            $result['expenditure'] = Tax::getInstance()->priceFormat($result['customer_expenditure']);
            $result['percent']     = (float)$divider[0]['total_sales'] ? number_format(100 * ($result['customer_expenditure'] / $divider[0]['total_sales']), 2) : 0;
            $tmp_col_data[]        = "['".$result['key'].". ".addslashes($result['last_name'].", ".$result['first_name'])."',".$result['customer_expenditure']."]";
            $smarty_data[8][]      = $result;
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
    break;

case 'stats_online':
    $query = sprintf("SELECT S.*, C.first_name, C.last_name FROM %1\$sCubeCart_sessions AS S LEFT JOIN %1\$sCubeCart_customer AS C ON S.customer_id = C.customer_id WHERE S.acp = 0 AND ".$online_filter."S.session_last>".$timeLimit." ORDER BY S.session_last DESC", $glob['dbprefix']);
    if (($results = $GLOBALS['db']->query($query)) !== false) {
        $smarty_data['users_online'] = array();
        foreach ($results as $user) {
            $user['is_admin']       = ((int)$user['admin_id'] > 0) ? 1 : 0;
            $user['name']           = ((int)$user['customer_id'] != 0) ? sprintf('%s %s', $user['first_name'], $user['last_name']) : $lang['common']['guest'];
            $user['session_length'] = sprintf('%.2F', ($user['session_last'] - $user['session_start']) / 60);
            $user['session_start']  = formatTime($user['session_start']);
            $user['session_last']   = formatTime($user['session_last']);
            $smarty_data['users_online'][] = $user;
        }
        $GLOBALS['smarty']->assign('USERS_ONLINE', $smarty_data['users_online']);
    }
    break;
}

// Plugin-supplied tabs run on every load so the strip stays consistent;
// plugins manage their own SQL gating if they want lazy loading.
$smarty_data['plugin_tabs'] = array();
foreach ($GLOBALS['hooks']->load('admin.statistics.tabs') as $hook) {
    include $hook;
}
$GLOBALS['smarty']->assign('PLUGIN_TABS', ($smarty_data['plugin_tabs'] ?? false));

$GLOBALS['smarty']->assign('GRAPH_DATA',  $g_graph_data);
$GLOBALS['smarty']->assign('ACTIVE_TAB',  $active_tab);
$GLOBALS['smarty']->assign('IS_FRAGMENT', $is_fragment);

if ($is_fragment) {
    // AJAX-per-tab: emit just the tab body and skip the admin layout wrapper.
    $suppress_output = true;
    @ob_end_clean();
    echo $GLOBALS['smarty']->fetch('templates/statistics.tabs.php');
    return;
}

$page_content = $GLOBALS['smarty']->fetch('templates/statistics.index.php');
