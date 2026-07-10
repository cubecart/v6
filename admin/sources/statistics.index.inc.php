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
$valid_tabs  = array('stats_sales', 'stats_funnel', 'stats_abandoned', 'stats_country', 'stats_prod_sales', 'stats_prod_views', 'stats_search', 'stats_best_customers', 'stats_online');
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

// Bot identification reuses Session::botSignatures() so per-row labels and
// SQL-side bucket counts stay in sync with the runtime _isBot() blacklist.
// The previous heuristic (session_last == session_start) flagged any bounce
// visit as a bot, which mislabelled real customers who only viewed one page.
$bot_sigs = Session::botSignatures();
$bot_match_parts = array();
foreach ($bot_sigs as $sig) {
    $bot_match_parts[] = "LOWER(useragent) LIKE '%" . $GLOBALS['db']->sqlSafe($sig) . "%'";
}
$bot_sql_match = !empty($bot_match_parts) ? '(' . implode(' OR ', $bot_match_parts) . ')' : '0';

if (isset($_GET['bots']) && $_GET['bots'] == 'false') {
    $online_filter = "NOT $bot_sql_match AND ";
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
$GLOBALS['main']->addTabControl($lang['statistics']['title_funnel'],            'stats_funnel');
$GLOBALS['main']->addTabControl($lang['statistics']['title_abandoned'],         'stats_abandoned');
$GLOBALS['main']->addTabControl($lang['statistics']['title_country'],           'stats_country');
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
        $fy_start_month = fiscalYearStartMonth();
        $fy_start_day   = fiscalYearStartDay();
        $earliest = array(
            'year'  => (int)date('Y', $earliest_order[0]['MIN_order_date']),
            'month' => date('m', $earliest_order[0]['MIN_order_date']),
            'day'   => date('d', $earliest_order[0]['MIN_order_date']),
        );
        $now_year    = (int)date('Y');
        $earliest_fy = fiscalYear((int)$earliest_order[0]['MIN_order_date'], $fy_start_month, $fy_start_day);
        $current_fy  = fiscalYear(time(), $fy_start_month, $fy_start_day);

        // Per-chart filter selectors. Each chart owns its own URL params so
        // changing one chart's filter doesn't bleed into the others.
        // m_year is a fiscal-year starting year (Chart 2 is FY-aware). d_year
        // and h_year stay calendar — Charts 3 and 4 are within-period drills.
        $sf = array(
            'm_year'  => (isset($_GET['m_year'])  && is_numeric($_GET['m_year']))                          ? (int)$_GET['m_year']                                  : $current_fy,
            'm_month' => (isset($_GET['m_month']) && in_array((int)$_GET['m_month'], range(1,12)))         ? str_pad((int)$_GET['m_month'], 2, '0', STR_PAD_LEFT)  : date('m'),
            'd_year'  => (isset($_GET['d_year'])  && is_numeric($_GET['d_year']))                          ? (int)$_GET['d_year']                                  : $now_year,
            'd_month' => (isset($_GET['d_month']) && in_array((int)$_GET['d_month'], range(1,12)))         ? str_pad((int)$_GET['d_month'], 2, '0', STR_PAD_LEFT)  : date('m'),
            'h_year'  => (isset($_GET['h_year'])  && is_numeric($_GET['h_year']))                          ? (int)$_GET['h_year']                                  : $now_year,
            'h_month' => (isset($_GET['h_month']) && in_array((int)$_GET['h_month'], range(1,12)))         ? str_pad((int)$_GET['h_month'], 2, '0', STR_PAD_LEFT)  : date('m'),
            'h_day'   => (isset($_GET['h_day'])   && in_array((int)$_GET['h_day'],   range(1,31)))         ? str_pad((int)$_GET['h_day'],   2, '0', STR_PAD_LEFT)  : date('d'),
        );

        // Date-range bounds per chart.
        $month_year_start = fiscalYearStart((int)$sf['m_year'],     $fy_start_month, $fy_start_day);
        $month_year_end   = fiscalYearStart((int)$sf['m_year'] + 1, $fy_start_month, $fy_start_day);
        $prior_year_start = fiscalYearStart((int)$sf['m_year'] - 1, $fy_start_month, $fy_start_day);
        $day_month_start  = mktime(0, 0, 0, (int)$sf['d_month'], 1, (int)$sf['d_year']);
        $day_month_end    = mktime(0, 0, 0, (int)$sf['d_month'] + 1, 1, (int)$sf['d_year']);
        $hour_day_start   = mktime(0, 0, 0, (int)$sf['h_month'], (int)$sf['h_day'], (int)$sf['h_year']);
        $hour_day_end     = mktime(0, 0, 0, (int)$sf['h_month'], (int)$sf['h_day'] + 1, (int)$sf['h_year']);

        // Chart 1 totals — day-level so we can bucket by fiscal year in PHP.
        $yearly_rows = $GLOBALS['db']->query(sprintf(
            "SELECT FROM_UNIXTIME(`order_date`, '%%Y-%%m-%%d') AS d, SUM(`total`) AS s, COUNT(*) AS c FROM `%sCubeCart_order_summary` WHERE `status` IN %s GROUP BY d",
            $glob['dbprefix'], $status_in
        ));
        if ($yearly_rows) {
            foreach ($yearly_rows as $row) {
                $parts = explode('-', $row['d']);
                if (count($parts) !== 3) continue;
                $ts = mktime(12, 0, 0, (int)$parts[1], (int)$parts[2], (int)$parts[0]);
                $fy = fiscalYear($ts, $fy_start_month, $fy_start_day);
                $yearly[$fy] = ($yearly[$fy] ?? 0) + (float)$row['s'];
                $totals[1][0] += (float)$row['s'];
                $totals[1][1] += (int)$row['c'];
            }
        }

        // Chart 2: monthly query covers BOTH selected FY and prior FY for YoY overlay.
        // Day-level so fiscal-month slots can split a calendar month between FYs.
        $monthly_rows = $GLOBALS['db']->query(sprintf(
            "SELECT FROM_UNIXTIME(`order_date`, '%%Y-%%m-%%d') AS d, SUM(`total`) AS s, COUNT(*) AS c FROM `%sCubeCart_order_summary` WHERE `status` IN %s AND `order_date` >= %d AND `order_date` < %d GROUP BY d",
            $glob['dbprefix'], $status_in, $prior_year_start, $month_year_end
        ));
        if ($monthly_rows) {
            $sf_my   = (int)$sf['m_year'];
            $sf_my_p = $sf_my - 1;
            foreach ($monthly_rows as $row) {
                $parts = explode('-', $row['d']);
                if (count($parts) !== 3) continue;
                $ts   = mktime(12, 0, 0, (int)$parts[1], (int)$parts[2], (int)$parts[0]);
                $fy   = fiscalYear($ts, $fy_start_month, $fy_start_day);
                $slot = fiscalSlot($ts, $fy_start_month, $fy_start_day);
                if ($fy === $sf_my) {
                    $monthly_curr[$slot] = ($monthly_curr[$slot] ?? 0) + (float)$row['s'];
                    $totals[2][0] += (float)$row['s'];
                    $totals[2][1] += (int)$row['c'];
                } elseif ($fy === $sf_my_p) {
                    $monthly_prior[$slot] = ($monthly_prior[$slot] ?? 0) + (float)$row['s'];
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
        // Per-year colours keyed by fiscal-year starting year. Charts 1 & 2 are
        // FY-driven; Charts 3 & 4 receive calendar years from drill-down but
        // their colours come from the FY containing that calendar period.
        $year_color = array();
        $idx = 0;
        for ($i = $earliest_fy; $i <= $current_fy; ++$i) {
            $year_color[$i] = $hsl_to_hex((int)round(fmod(210 + $idx * 137.5, 360)), 0.65, 0.45);
            $idx++;
        }
        $color_for_fy = function ($fy) use ($year_color) {
            return $year_color[(int)$fy] ?? reset($year_color);
        };
        // For Charts 3/4: map a calendar (year, month) to its fiscal-year colour.
        $color_for_ym = function ($yr, $mo = 1) use ($color_for_fy, $fy_start_month, $fy_start_day) {
            $ts = mktime(12, 0, 0, (int)$mo, 1, (int)$yr);
            return $color_for_fy(fiscalYear($ts, $fy_start_month, $fy_start_day));
        };

        // Build per-chart year/month/day option lists. Future periods (relative
        // to today) are flagged as `disabled` so impossible selections are greyed.
        $now_month = (int)date('m');
        $now_day   = (int)date('d');
        // Calendar year options — used by Chart 3 (d_year) and Chart 4 (h_year).
        $year_options = function ($selected_year) use ($earliest, $now_year) {
            $out = array();
            for ($i = $earliest['year']; $i <= $now_year; ++$i) {
                $out[] = array(
                    'value'    => $i,
                    'label'    => (string)$i,
                    'selected' => ($selected_year == $i) ? ' selected="selected"' : '',
                );
            }
            return $out;
        };
        // Fiscal-year options — used by Chart 2 (m_year).
        $fy_year_options = function ($selected_fy) use ($earliest_fy, $current_fy, $fy_start_month) {
            $out = array();
            for ($i = $earliest_fy; $i <= $current_fy; ++$i) {
                $out[] = array(
                    'value'    => $i,
                    'label'    => fiscalYearLabel($i, $fy_start_month),
                    'selected' => ($selected_fy == $i) ? ' selected="selected"' : '',
                );
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
        $GLOBALS['smarty']->assign('M_YEARS',  $fy_year_options($sf['m_year']));
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

        // Chart 1: Fiscal-yearly bars. Each bar gets its own FY colour.
        if (count($yearly) >= 1) {
            $g_graph_data[1]['data'] = "['Year','".sprintf($lang['statistics']['sales_volume'], $GLOBALS['config']->get('config', 'default_currency'))."',{role:'style'}],";
            $tmp = array();
            for ($fy = $earliest_fy; $fy <= $current_fy; ++$fy) {
                $value = $yearly[$fy] ?? 0;
                $label = fiscalYearLabel($fy, $fy_start_month);
                $tmp[] = "['".$label."',".$value.",'color: ".$year_color[$fy]."']";
            }
            $g_graph_data[1]['data']    .= implode(',', $tmp);
            $earliest_label = fiscalYearLabel($earliest_fy, $fy_start_month);
            $current_label  = fiscalYearLabel($current_fy,  $fy_start_month);
            $g_graph_data[1]['title']    = ($earliest_fy == $current_fy)
                ? sprintf($lang['statistics']['sales_in'], $current_label)
                : sprintf($lang['statistics']['sales_from_to'], $earliest_label, $current_label);
            $g_graph_data[1]['hAxis']    = '';
            $g_graph_data[1]['vAxis']    = '';
            $g_graph_data[1]['legend']   = 'none';
            $g_graph_data[1]['y_format'] = $y_axis_format;
            $g_graph_data[1]['drill']    = array(
                'type'        => 'year',
                'years'       => range($earliest_fy, $current_fy),
                'start_month' => $fy_start_month,
            );
        }
        $g_graph_data[1]['total_sum']    = $tax->priceFormat((float)$totals[1][0]);
        $g_graph_data[1]['total_count']  = number_format((int)$totals[1][1]);

        // Chart 2: Fiscal-monthly bars for $sf['m_year'] vs prior FY (YoY overlay).
        // Bars are in fiscal-slot order; the leftmost slot is the FY start month.
        // Drop the prior-year series when it's before the store's earliest FY;
        // there's no data and the colour-fallback makes the two legends collide.
        $sf_my_p      = (int)$sf['m_year'] - 1;
        $show_prior   = $sf_my_p >= $earliest_fy;
        $cur_label    = fiscalYearLabel((int)$sf['m_year'], $fy_start_month);
        $prior_label  = fiscalYearLabel($sf_my_p,           $fy_start_month);
        if ($show_prior) {
            $g_graph_data[2]['data'] = "['Month','".$cur_label."','".$prior_label."'],";
        } else {
            $g_graph_data[2]['data'] = "['Month','".$cur_label."'],";
        }
        $tmp = array();
        for ($slot = 0; $slot < 12; ++$slot) {
            $month   = ((($fy_start_month - 1) + $slot) % 12) + 1;
            $m_label = date('M', mktime(0, 0, 0, $month, 1));
            $cv      = $monthly_curr[$slot] ?? 0;
            $row     = "['".$m_label."',".$cv;
            if ($show_prior) {
                $pv  = $monthly_prior[$slot] ?? 0;
                $row .= ",".$pv;
            }
            $tmp[]   = $row . "]";
        }
        $g_graph_data[2]['data']      .= implode(',', $tmp);
        $g_graph_data[2]['title']      = sprintf($lang['statistics']['sales_in_year'], $cur_label);
        $g_graph_data[2]['hAxis']      = '';
        $g_graph_data[2]['vAxis']      = '';
        $g_graph_data[2]['colors']     = $show_prior
            ? "['".$color_for_fy($sf['m_year'])."','".$color_for_fy($sf_my_p)."']"
            : "['".$color_for_fy($sf['m_year'])."']";
        $g_graph_data[2]['y_format']   = $y_axis_format;
        $g_graph_data[2]['drill']      = array(
            'type'        => 'month',
            'year'        => (int)$sf['m_year'],
            'start_month' => $fy_start_month,
        );
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
        $g_graph_data[3]['colors']     = "['".$color_for_ym($sf['d_year'], (int)$sf['d_month'])."']";
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
        $g_graph_data[4]['colors']     = "['".$color_for_ym($sf['h_year'], (int)$sf['h_month'])."']";
        $g_graph_data[4]['y_format']   = $y_axis_format;
        $g_graph_data[4]['total_sum']  = $tax->priceFormat((float)$totals[4][0]);
        $g_graph_data[4]['total_count']= number_format((int)$totals[4][1]);

        $GLOBALS['smarty']->assign('DISPLAY_SALES', true);
    }
    break;

case 'stats_funnel':
    // Conversion funnel — last 7 days (sessions table is purged after that).
    $window     = 7 * 86400;
    $since      = time() - $window;
    $tax_inst   = Tax::getInstance();

    // Stage 1: sessions in window.
    $sess_q = $GLOBALS['db']->query(sprintf(
        "SELECT COUNT(*) AS c FROM `%sCubeCart_sessions` WHERE acp = 0 AND session_last >= %d",
        $glob['dbprefix'], $since
    ));
    $sessions_count = $sess_q ? (int)$sess_q[0]['c'] : 0;

    // Stage 2: sessions with at least one cart item. Cheapest viable
    // detection: serialised "__basket" with non-empty contents leaves a
    // recognisable substring in the blob.
    $cart_q = $GLOBALS['db']->query(sprintf(
        "SELECT COUNT(*) AS c FROM `%sCubeCart_sessions` WHERE acp = 0 AND session_last >= %d AND session_data LIKE '%%\"__basket\"%%' AND session_data LIKE '%%\"contents\";a:%%' AND session_data NOT LIKE '%%\"contents\";a:0:{}%%'",
        $glob['dbprefix'], $since
    ));
    $cart_count = $cart_q ? (int)$cart_q[0]['c'] : 0;

    // Stage 3: orders submitted (any non-zero status) in window.
    $sub_q = $GLOBALS['db']->query(sprintf(
        "SELECT COUNT(*) AS c, COALESCE(SUM(`total`), 0) AS s FROM `%sCubeCart_order_summary` WHERE `order_date` >= %d",
        $glob['dbprefix'], $since
    ));
    $submitted_count = $sub_q ? (int)$sub_q[0]['c'] : 0;
    $submitted_value = $sub_q ? (float)$sub_q[0]['s'] : 0;

    // Stage 4: orders paid (status 2 or 3) in window.
    $paid_q = $GLOBALS['db']->query(sprintf(
        "SELECT COUNT(*) AS c, COALESCE(SUM(`total`), 0) AS s FROM `%sCubeCart_order_summary` WHERE `order_date` >= %d AND `status` IN (2,3)",
        $glob['dbprefix'], $since
    ));
    $paid_count = $paid_q ? (int)$paid_q[0]['c'] : 0;
    $paid_value = $paid_q ? (float)$paid_q[0]['s'] : 0;

    $base = max($sessions_count, 1);
    $stages = array(
        array('name' => $lang['statistics']['funnel_sessions'],  'count' => $sessions_count,  'pct_total' => 100,                                                            'pct_prev' => null),
        array('name' => $lang['statistics']['funnel_carts'],     'count' => $cart_count,      'pct_total' => round($cart_count      / $base * 100, 1),                       'pct_prev' => $sessions_count  ? round($cart_count      / $sessions_count  * 100, 1) : 0),
        array('name' => $lang['statistics']['funnel_submitted'], 'count' => $submitted_count, 'pct_total' => round($submitted_count / $base * 100, 1),                       'pct_prev' => $cart_count      ? round($submitted_count / $cart_count      * 100, 1) : 0),
        array('name' => $lang['statistics']['funnel_paid'],      'count' => $paid_count,      'pct_total' => round($paid_count      / $base * 100, 1),                       'pct_prev' => $submitted_count ? round($paid_count      / $submitted_count * 100, 1) : 0),
    );

    $g_graph_data[10]['data']        = "['Stage','Count'],";
    $tmp = array();
    foreach ($stages as $s) {
        $tmp[] = "['".addslashes($s['name'])."',".$s['count']."]";
    }
    $g_graph_data[10]['data']       .= implode(',', $tmp);
    $g_graph_data[10]['title']       = $lang['statistics']['last_7_days'];
    $g_graph_data[10]['hAxis']       = '';
    $g_graph_data[10]['vAxis']       = '';
    $g_graph_data[10]['legend']      = 'none';

    $GLOBALS['smarty']->assign('FUNNEL_STAGES',     $stages);
    $GLOBALS['smarty']->assign('FUNNEL_PAID_VALUE', sprintf($lang['statistics']['funnel_paid_value'],      $tax_inst->priceFormat($paid_value)));
    $GLOBALS['smarty']->assign('FUNNEL_SUB_VALUE',  sprintf($lang['statistics']['funnel_submitted_value'], $tax_inst->priceFormat($submitted_value)));
    break;

case 'stats_abandoned':
    // Active sessions that have a non-empty basket but no recent paid order
    // for the same session_id linkage.
    $window = 24 * 3600; // last 24h
    $since  = time() - $window;
    $tax_inst = Tax::getInstance();

    $rows_q = $GLOBALS['db']->query(sprintf(
        "SELECT S.session_id, S.session_start, S.session_last, S.customer_id, S.location, S.ip_address, S.session_data, C.first_name, C.last_name, C.email FROM `%1\$sCubeCart_sessions` AS S LEFT JOIN `%1\$sCubeCart_customer` AS C ON S.customer_id = C.customer_id WHERE S.acp = 0 AND S.session_last >= %2\$d AND S.session_data LIKE '%%\"__basket\"%%' AND S.session_data LIKE '%%\"contents\";a:%%' AND S.session_data NOT LIKE '%%\"contents\";a:0:{}%%' ORDER BY S.session_last DESC",
        $glob['dbprefix'], $since
    ));

    $abandoned = array();
    $total_value = 0;
    if ($rows_q) {
        $now_t = time();
        foreach ($rows_q as $row) {
            $data = @unserialize($row['session_data'], ['allowed_classes' => false]);
            if (!is_array($data)) continue;
            $basket = $data['__basket'] ?? null;
            if (!is_array($basket) || empty($basket['contents'])) continue;
            $value = (float)($basket['total'] ?? 0);
            if ($value <= 0) continue;
            $total_value += $value;

            $rel = max(0, $now_t - (int)$row['session_last']);
            $idle = ($rel < 60) ? $rel.'s' : (($rel < 3600) ? floor($rel/60).'m' : floor($rel/3600).'h');

            $abandoned[] = array(
                'session_id'    => $row['session_id'],
                'name'          => ((int)$row['customer_id'] > 0)
                    ? trim($row['first_name'].' '.$row['last_name'])
                    : $lang['common']['guest'],
                'email'         => $row['email'] ?? '',
                'customer_id'   => (int)$row['customer_id'],
                'item_count'    => count($basket['contents']),
                'cart_value'    => $tax_inst->priceFormat($value),
                'cart_value_raw'=> $value,
                'last_idle'     => $idle . ' ago',
                'ip_address'    => $row['ip_address'],
                'location'      => $row['location'],
            );
        }
    }
    // Sort by basket value DESC.
    usort($abandoned, function ($a, $b) { return $b['cart_value_raw'] <=> $a['cart_value_raw']; });

    $GLOBALS['smarty']->assign('ABANDONED_CARTS',  $abandoned);
    $GLOBALS['smarty']->assign('ABANDONED_TOTAL',  $tax_inst->priceFormat($total_value));
    $GLOBALS['smarty']->assign('ABANDONED_COUNT',  number_format(count($abandoned)));
    break;

case 'stats_country':
    // Fiscal-year filter ('all' or FY starting year).
    $fy_start_month = fiscalYearStartMonth();
    $fy_start_day   = fiscalYearStartDay();
    $cn_year_raw = isset($_GET['cn_year']) ? $_GET['cn_year'] : 'all';
    $cn_year     = ($cn_year_raw === 'all' || !is_numeric($cn_year_raw)) ? 'all' : (int)$cn_year_raw;

    $now_year   = (int)date('Y');
    $current_fy = fiscalYear(time(), $fy_start_month, $fy_start_day);
    $where_date = '';
    if ($cn_year !== 'all') {
        $year_start = fiscalYearStart($cn_year,     $fy_start_month, $fy_start_day);
        $next_start = fiscalYearStart($cn_year + 1, $fy_start_month, $fy_start_day);
        $year_end   = ($cn_year === $current_fy) ? time() : $next_start;
        $where_date = " AND `O`.`order_date` >= ".$year_start." AND `O`.`order_date` < ".$year_end;
    }

    $earliest_q    = $GLOBALS['db']->query("SELECT MIN(`order_date`) AS `m` FROM `".$glob['dbprefix']."CubeCart_order_summary` WHERE `status` IN (2,3)");
    $earliest_fy   = ($earliest_q && !empty($earliest_q[0]['m']))
        ? fiscalYear((int)$earliest_q[0]['m'], $fy_start_month, $fy_start_day)
        : $current_fy;
    $cn_year_options = array(
        array('value' => 'all', 'label' => $lang['statistics']['all_time'], 'selected' => $cn_year === 'all' ? ' selected="selected"' : ''),
    );
    for ($yr = $current_fy; $yr >= $earliest_fy; $yr--) {
        $cn_year_options[] = array(
            'value'    => $yr,
            'label'    => fiscalYearLabel($yr, $fy_start_month),
            'selected' => ($cn_year === $yr) ? ' selected="selected"' : '',
        );
    }

    // Year-colour palette, same rules as Best Customers / Best Selling.
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
    if ($cn_year === 'all') {
        $idx = ($current_fy - $earliest_fy) + 1;
    } else {
        $idx = max(0, $cn_year - $earliest_fy);
    }
    $cn_color = $hsl_to_hex((int)round(fmod(210 + $idx * 137.5, 360)), 0.65, 0.45);

    $GLOBALS['smarty']->assign('CN_YEAR_OPTIONS', $cn_year_options);
    $GLOBALS['smarty']->assign('CN_YEAR',         $cn_year);

    // LEFT JOIN with `G.numcode <> 0` so orders with empty/invalid country
    // do not collide with Kosovo (seeded with numcode 000); collapse all
    // unmatched orders into a single "Unknown / not set" bucket.
    $rows_q = $GLOBALS['db']->query(sprintf(
        "SELECT G.iso, G.name, COUNT(*) AS orders, SUM(O.total) AS revenue FROM `%1\$sCubeCart_order_summary` AS O LEFT JOIN `%1\$sCubeCart_geo_country` AS G ON G.numcode = O.country AND G.numcode <> 0 WHERE O.status IN (2,3)".$where_date." GROUP BY G.id ORDER BY revenue DESC",
        $glob['dbprefix']
    ));

    $countries = array();
    $total_revenue = 0;
    if ($rows_q) {
        foreach ($rows_q as $row) {
            $total_revenue += (float)$row['revenue'];
        }
    }

    $tax_inst = Tax::getInstance();
    $flag_for = function ($iso) {
        if (!$iso || strlen($iso) !== 2) return '';
        $iso = strtoupper($iso);
        $base = 0x1F1E6;
        $a = ord('A');
        return mb_chr($base + ord($iso[0]) - $a) . mb_chr($base + ord($iso[1]) - $a);
    };

    $unknown_label = $lang['common']['unknown'];
    $g_graph_data[11]['data'] = "['Country','".sprintf($lang['statistics']['sales_volume'], $GLOBALS['config']->get('config', 'default_currency'))."'],";
    $tmp = array();
    if ($rows_q) {
        $rank = 0;
        foreach ($rows_q as $row) {
            $rank++;
            $rev   = (float)$row['revenue'];
            $is_unknown = ($row['name'] === null || $row['name'] === '');
            $name  = $is_unknown ? $unknown_label : $row['name'];
            $iso   = ($row['iso'] !== null) ? $row['iso'] : '';
            $flag  = $is_unknown ? mb_chr(0x1F310) : $flag_for($iso);
            if ($rank <= 10) {
                $tmp[] = "['".addslashes(($flag ? $flag.' ' : '').$name)."',".$rev."]";
            }
            $countries[] = array(
                'rank'    => $rank,
                'flag'    => $flag,
                'iso'     => $iso,
                'name'    => $name,
                'orders'  => number_format((int)$row['orders']),
                'revenue' => $tax_inst->priceFormat($rev),
                'percent' => $total_revenue > 0 ? number_format($rev / $total_revenue * 100, 1) : 0,
            );
        }
    }
    $g_graph_data[11]['data']       .= implode(',', $tmp);
    $g_graph_data[11]['title']       = $lang['statistics']['country_top_revenue'];
    $g_graph_data[11]['hAxis']       = '';
    $g_graph_data[11]['vAxis']       = '';
    $g_graph_data[11]['colors']      = "['".$cn_color."']";
    $g_graph_data[11]['legend']      = 'none';
    $g_graph_data[11]['total_sum']   = $tax_inst->priceFormat($total_revenue);
    $g_graph_data[11]['total_count'] = number_format(count($countries));

    $GLOBALS['smarty']->assign('COUNTRY_ROWS', $countries);
    break;

case 'stats_prod_sales':
    // Filter: fiscal year (or 'all') with optional fiscal-month narrowing. Default all-time.
    // ps_month is a 1-12 fiscal slot (= slot+1), where 1 is the FY start month.
    $fy_start_month = fiscalYearStartMonth();
    $fy_start_day   = fiscalYearStartDay();
    $ps_year_raw  = isset($_GET['ps_year'])  ? $_GET['ps_year']  : 'all';
    $ps_year      = ($ps_year_raw === 'all' || !is_numeric($ps_year_raw)) ? 'all' : (int)$ps_year_raw;
    $ps_month_raw = isset($_GET['ps_month']) ? $_GET['ps_month'] : '';
    // Month only applies when a specific year is chosen.
    $ps_month     = ($ps_year !== 'all' && is_numeric($ps_month_raw) && in_array((int)$ps_month_raw, range(1, 12)))
                  ? str_pad((int)$ps_month_raw, 2, '0', STR_PAD_LEFT)
                  : '';

    $per_page = 15;
    $page     = (isset($_GET['page_sales']) && is_numeric($_GET['page_sales'])) ? (int)$_GET['page_sales'] : 1;
    $offset   = ($page - 1) * $per_page;

    $now_year   = (int)date('Y');
    $current_fy = fiscalYear(time(), $fy_start_month, $fy_start_day);
    $where_date  = '';
    $prior_start = null;
    $prior_end   = null;
    if ($ps_year !== 'all') {
        if ($ps_month !== '') {
            // Fiscal-slot window: a single FY "month" (start_day to next start_day).
            // Compare against the same slot in the prior FY.
            $slot       = (int)$ps_month - 1;
            $cal_month  = (($fy_start_month - 1 + $slot) % 12) + 1;
            $cal_year   = ($cal_month >= $fy_start_month) ? $ps_year : $ps_year + 1;
            $year_start  = mktime(0, 0, 0, $cal_month,     $fy_start_day, $cal_year);
            $year_end    = mktime(0, 0, 0, $cal_month + 1, $fy_start_day, $cal_year);
            $prior_start = mktime(0, 0, 0, $cal_month,     $fy_start_day, $cal_year - 1);
            $prior_end   = mktime(0, 0, 0, $cal_month + 1, $fy_start_day, $cal_year - 1);
        } else {
            // Whole-FY window: compare against the prior FY.
            $year_start  = fiscalYearStart($ps_year, $fy_start_month, $fy_start_day);
            $next_start  = fiscalYearStart($ps_year + 1, $fy_start_month, $fy_start_day);
            $prior_start = fiscalYearStart($ps_year - 1, $fy_start_month, $fy_start_day);
            if ($ps_year === $current_fy) {
                $year_end  = time();
                $prior_end = strtotime('-1 year', $year_end);
            } else {
                $year_end  = $next_start;
                $prior_end = $year_start;
            }
        }
        $where_date  = " AND `S`.`order_date` >= ".$year_start." AND `S`.`order_date` < ".$year_end;
    }

    $order_by = '`t`.`quan` DESC';

    // Year filter options always built so the filter form keeps showing
    // even when the active year has no results (otherwise the user has no way back).
    $earliest_q  = $GLOBALS['db']->query("SELECT MIN(`order_date`) AS `m` FROM `".$glob['dbprefix']."CubeCart_order_summary` WHERE `status` IN (2,3)");
    $earliest_fy = ($earliest_q && !empty($earliest_q[0]['m']))
        ? fiscalYear((int)$earliest_q[0]['m'], $fy_start_month, $fy_start_day)
        : $current_fy;
    $ps_year_options = array(
        array('value' => 'all', 'label' => $lang['statistics']['all_time'], 'selected' => $ps_year === 'all' ? ' selected="selected"' : ''),
    );
    for ($yr = $current_fy; $yr >= $earliest_fy; $yr--) {
        $ps_year_options[] = array(
            'value'    => $yr,
            'label'    => fiscalYearLabel($yr, $fy_start_month),
            'selected' => ($ps_year === $yr) ? ' selected="selected"' : '',
        );
    }

    // Fiscal-slot month options: "all months" plus the 12 slots of the selected FY.
    // Slots are labelled with their calendar month name. Future slots of the
    // current FY are disabled so impossible selections grey out.
    $current_slot = fiscalSlot(time(), $fy_start_month, $fy_start_day);
    $ps_month_options = array(
        array('value' => '', 'title' => $lang['statistics']['all_months'], 'selected' => $ps_month === '' ? ' selected="selected"' : '', 'disabled' => ''),
    );
    for ($slot = 0; $slot < 12; ++$slot) {
        $cal_month = ((($fy_start_month - 1) + $slot) % 12) + 1;
        $padded    = str_pad($slot + 1, 2, '0', STR_PAD_LEFT);
        $ps_month_options[] = array(
            'value'    => $padded,
            'title'    => date('F', mktime(0, 0, 0, $cal_month, 1)),
            'selected' => ($ps_month === $padded) ? ' selected="selected"' : '',
            'disabled' => ($ps_year === $current_fy && $slot > $current_slot) ? ' disabled="disabled"' : '',
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
        $idx_for_color = ($current_fy - $earliest_fy) + 1;
    } else {
        $idx_for_color = max(0, $ps_year - $earliest_fy);
    }
    $ps_chart_color = $hsl_to_hex((int)round(fmod(210 + $idx_for_color * 137.5, 360)), 0.65, 0.45);

    $GLOBALS['smarty']->assign('PS_YEAR_OPTIONS',  $ps_year_options);
    $GLOBALS['smarty']->assign('PS_MONTH_OPTIONS', $ps_month_options);
    $GLOBALS['smarty']->assign('PS_YEAR',          $ps_year);
    $GLOBALS['smarty']->assign('PS_MONTH',         $ps_month);
    $GLOBALS['smarty']->assign('PS_HAS_TREND',     $prior_start !== null);

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
                $result['trend'] = array('dir' => 'up', 'label' => $lang['statistics']['trend_new']);
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
    // Fiscal-year filter (or 'all').
    $fy_start_month = fiscalYearStartMonth();
    $fy_start_day   = fiscalYearStartDay();
    $bc_year_raw = isset($_GET['bc_year']) ? $_GET['bc_year'] : 'all';
    $bc_year     = ($bc_year_raw === 'all' || !is_numeric($bc_year_raw)) ? 'all' : (int)$bc_year_raw;

    $per_page = 15;
    $page     = (isset($_GET['page_customers']) && is_numeric($_GET['page_customers'])) ? (int)$_GET['page_customers'] : 1;
    $offset   = ($page - 1) * $per_page;

    $now_year   = (int)date('Y');
    $current_fy = fiscalYear(time(), $fy_start_month, $fy_start_day);
    $where_date = '';
    if ($bc_year !== 'all') {
        $year_start = fiscalYearStart($bc_year,     $fy_start_month, $fy_start_day);
        $next_start = fiscalYearStart($bc_year + 1, $fy_start_month, $fy_start_day);
        $year_end   = ($bc_year === $current_fy) ? time() : $next_start;
        $where_date = " AND `O`.`order_date` >= ".$year_start." AND `O`.`order_date` < ".$year_end;
    }

    // Year filter options (always built so the form survives empty results).
    $earliest_q  = $GLOBALS['db']->query("SELECT MIN(`order_date`) AS `m` FROM `".$glob['dbprefix']."CubeCart_order_summary` WHERE `status` IN (2,3)");
    $earliest_fy = ($earliest_q && !empty($earliest_q[0]['m']))
        ? fiscalYear((int)$earliest_q[0]['m'], $fy_start_month, $fy_start_day)
        : $current_fy;
    $bc_year_options = array(
        array('value' => 'all', 'label' => $lang['statistics']['all_time'], 'selected' => $bc_year === 'all' ? ' selected="selected"' : ''),
    );
    for ($yr = $current_fy; $yr >= $earliest_fy; $yr--) {
        $bc_year_options[] = array(
            'value'    => $yr,
            'label'    => fiscalYearLabel($yr, $fy_start_month),
            'selected' => ($bc_year === $yr) ? ' selected="selected"' : '',
        );
    }

    // Year-colour palette, same as other tabs.
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
    if ($bc_year === 'all') {
        $idx = ($current_fy - $earliest_fy) + 1;
    } else {
        $idx = max(0, $bc_year - $earliest_fy);
    }
    $bc_color = $hsl_to_hex((int)round(fmod(210 + $idx * 137.5, 360)), 0.65, 0.45);

    $GLOBALS['smarty']->assign('BC_YEAR_OPTIONS', $bc_year_options);
    $GLOBALS['smarty']->assign('BC_YEAR',         $bc_year);

    $query = "SELECT SUM(`O`.`total`) AS `customer_expenditure`, COUNT(*) AS `order_count`, `C`.`first_name`, `C`.`last_name`, `C`.`customer_id` FROM `".$glob['dbprefix']."CubeCart_order_summary` AS `O` INNER JOIN `".$glob['dbprefix']."CubeCart_customer` AS `C` ON `O`.`customer_id` = `C`.`customer_id` WHERE `O`.`status` IN (2,3)".$where_date." GROUP BY `O`.`customer_id` ORDER BY `customer_expenditure` DESC LIMIT ".(int)$per_page." OFFSET ".(int)$offset;

    if (($results = $GLOBALS['db']->query($query)) !== false && !empty($results)) {
        $numrows_q = $GLOBALS['db']->query("SELECT COUNT(DISTINCT `O`.`customer_id`) AS `c` FROM `".$glob['dbprefix']."CubeCart_order_summary` AS `O` WHERE `O`.`status` IN (2,3)".$where_date);
        $numrows   = $numrows_q ? (int)$numrows_q[0]['c'] : 0;

        $divider       = $GLOBALS['db']->query("SELECT SUM(`total`) AS `total_sales` FROM `".$glob['dbprefix']."CubeCart_order_summary` AS `O` WHERE `O`.`status` IN (2,3)".$where_date);
        $total_revenue = (float)($divider[0]['total_sales'] ?? 0);

        $tax = Tax::getInstance();
        $g_graph_data[8]['data'] = "['".$lang['statistics']['percentage_of_views']."','".sprintf($lang['statistics']['sales_volume'], $GLOBALS['config']->get('config', 'default_currency'))."'],";

        $smarty_data[8] = array();
        $customer_ids_for_chart = array();
        foreach ($results as $key => $result) {
            $result['key']         = (($page - 1) * $per_page) + ($key + 1);
            $result['expenditure'] = $tax->priceFormat($result['customer_expenditure']);
            $result['percent']     = $total_revenue ? number_format(100 * ($result['customer_expenditure'] / $total_revenue), 2) : 0;
            $result['order_count'] = number_format((int)$result['order_count']);
            $tmp_col_data[]        = "['".$result['key'].". ".addslashes($result['last_name'].", ".$result['first_name'])."',".$result['customer_expenditure']."]";
            $customer_ids_for_chart[] = (int)$result['customer_id'];
            $smarty_data[8][]      = $result;
        }

        $g_graph_data[8]['data'] .= isset($tmp_col_data) ? implode(',', $tmp_col_data) : '';
        unset($tmp_col_data);
        $g_graph_data[8]['title']        = '';
        $g_graph_data[8]['hAxis']        = $lang['dashboard']['inv_customers'];
        $g_graph_data[8]['vAxis']        = $lang['statistics']['total_expenditure'];
        $g_graph_data[8]['colors']       = "['".$bc_color."']";
        $g_graph_data[8]['drill']        = array('type' => 'customer', 'customer_ids' => $customer_ids_for_chart);
        $g_graph_data[8]['total_sum']    = $tax->priceFormat($total_revenue);
        $g_graph_data[8]['total_count']  = number_format((int)$numrows);

        $GLOBALS['smarty']->assign('BEST_CUSTOMERS', $smarty_data[8]);
        $GLOBALS['smarty']->assign('PAGINATION_BEST', $GLOBALS['db']->pagination($numrows, $per_page, $page, 5, 'page_customers', 'stats_best_customers', ' ', false));
        unset($results, $result, $divider);
    }
    break;

case 'stats_online':
    $tax_inst = Tax::getInstance();

    // Counts split by type for the card footer. Bots are excluded entirely —
    // Session::_isBot() rejects them before a sessions row is created, so the
    // bucket would be ~0 anyway and was just adding visual noise.
    $split_q = $GLOBALS['db']->query(sprintf(
        "SELECT SUM(CASE WHEN customer_id > 0 THEN 1 ELSE 0 END) AS signed_in, SUM(CASE WHEN customer_id = 0 AND NOT %3\$s THEN 1 ELSE 0 END) AS guests FROM `%1\$sCubeCart_sessions` WHERE acp = 0 AND session_last > %2\$d",
        $glob['dbprefix'], $timeLimit, $bot_sql_match
    ));
    $split = array('signed_in' => 0, 'guests' => 0);
    if ($split_q && !empty($split_q[0])) {
        $split['signed_in'] = (int)$split_q[0]['signed_in'];
        $split['guests']    = (int)$split_q[0]['guests'];
    }
    $split['total'] = $split['signed_in'] + $split['guests'];
    $GLOBALS['smarty']->assign('USERS_SPLIT', $split);

    // Helpers: friendly location label, bot identification, geo guess.
    $is_bot_ua = function ($ua) use ($bot_sigs) {
        $ua = strtolower($ua ?? '');
        if ($ua === '') return false;
        foreach ($bot_sigs as $sig) {
            if (strpos($ua, $sig) !== false) return true;
        }
        return false;
    };
    $bot_name_for = function ($ua) {
        $ua = strtolower($ua ?? '');
        if (strpos($ua, 'googlebot') !== false)            return 'Googlebot';
        if (strpos($ua, 'bingbot') !== false)              return 'Bingbot';
        if (strpos($ua, 'ahrefsbot') !== false)            return 'AhrefsBot';
        if (strpos($ua, 'semrushbot') !== false)           return 'SemrushBot';
        if (strpos($ua, 'facebookexternalhit') !== false)  return 'Facebook';
        if (strpos($ua, 'twitterbot') !== false)           return 'Twitter';
        if (strpos($ua, 'duckduckbot') !== false)          return 'DuckDuckGo';
        if (strpos($ua, 'yandexbot') !== false)            return 'Yandex';
        if (strpos($ua, 'applebot') !== false)             return 'Applebot';
        if (strpos($ua, 'slurp') !== false)                return 'Yahoo';
        $generic = $GLOBALS['language']->statistics['bot_generic'] ?? 'Bot';
        if (strpos($ua, 'bot') !== false || strpos($ua, 'spider') !== false || strpos($ua, 'crawl') !== false) return $generic;
        return $generic;
    };
    $loc_strings = $lang['statistics'];
    $location_label = function ($loc) use ($loc_strings) {
        $loc = (string)$loc;
        // Special-case the 404 marker before stripping tags, so the
        // <br><strike> markup CubeCart bakes in is not exposed.
        if (strpos($loc, '_a=404') !== false) {
            return array('label' => $loc_strings['loc_404'], 'is_checkout' => false);
        }
        $loc = trim(strip_tags($loc));
        if ($loc === '' || $loc === '/' || $loc === 'index.html')    return array('label' => $loc_strings['loc_home'],     'is_checkout' => false);
        if (strpos($loc, 'cart.html') === 0)                          return array('label' => $loc_strings['loc_cart'],     'is_checkout' => true);
        if (strpos($loc, 'checkout.html') === 0)                      return array('label' => $loc_strings['loc_checkout'], 'is_checkout' => true);
        if (strpos($loc, 'account') === 0)                            return array('label' => $loc_strings['loc_account'],  'is_checkout' => false);
        if (strpos($loc, 'login.html') === 0)                         return array('label' => $loc_strings['loc_login'],    'is_checkout' => false);
        if (strpos($loc, 'register.html') === 0)                      return array('label' => $loc_strings['loc_register'], 'is_checkout' => false);
        if (preg_match('#^category/(.+?)\.html#', $loc, $m))          return array('label' => sprintf($loc_strings['loc_browsing'], ucwords(str_replace(array('-','_'), ' ', $m[1]))), 'is_checkout' => false);
        if (preg_match('#^product/(.+?)\.html#', $loc, $m))           return array('label' => sprintf($loc_strings['loc_viewing'],  ucwords(str_replace(array('-','_'), ' ', $m[1]))), 'is_checkout' => false);
        if (preg_match('#search.*[?&]search%5Bkeywords%5D=([^&]+)#', $loc, $m)) return array('label' => sprintf($loc_strings['loc_searching'], urldecode($m[1])), 'is_checkout' => false);
        if (preg_match('#search.*[?&]search\[keywords\]=([^&]+)#', $loc, $m))  return array('label' => sprintf($loc_strings['loc_searching'], urldecode($m[1])), 'is_checkout' => false);
        // Bare SEO slug (no slash/query): treat as a product/category page.
        if (preg_match('#^[a-z0-9][a-z0-9_\-]*(?:\.html)?$#i', $loc)) {
            $slug = preg_replace('/\.html$/', '', $loc);
            return array('label' => sprintf($loc_strings['loc_viewing'], ucwords(str_replace(array('-','_'), ' ', $slug))), 'is_checkout' => false);
        }
        return array('label' => $loc, 'is_checkout' => false);
    };
    $ip_local_label = $lang['statistics']['ip_local'];
    $ip_test_label  = $lang['statistics']['ip_test'];
    $country_for = function ($ip) use ($ip_local_label, $ip_test_label) {
        // Hook for a real geo lookup (MaxMind GeoLite2 / ipapi). For now we
        // just flag local/test IP ranges so seeded data renders cleanly.
        if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1')   return $ip_local_label;
        if (preg_match('#^(10\.|192\.168\.|169\.254\.|172\.(1[6-9]|2\d|3[01])\.)#', $ip)) return $ip_local_label;
        if (preg_match('#^(192\.0\.2\.|198\.51\.100\.|203\.0\.113\.)#', $ip)) return $ip_test_label;
        return '';
    };

    $query = sprintf(
        "SELECT S.*, C.first_name, C.last_name FROM %1\$sCubeCart_sessions AS S LEFT JOIN %1\$sCubeCart_customer AS C ON S.customer_id = C.customer_id WHERE S.acp = 0 AND %2\$sS.session_last > %3\$d ORDER BY S.session_last DESC",
        $glob['dbprefix'], $online_filter, $timeLimit
    );
    if (($results = $GLOBALS['db']->query($query)) !== false) {
        $now_t = time();
        $smarty_data['users_online'] = array();
        foreach ($results as $user) {
            $user['is_admin']    = ((int)$user['admin_id'] > 0) ? 1 : 0;
            $user['is_bot']      = $is_bot_ua($user['useragent']);
            $user['bot_name']    = $user['is_bot'] ? $bot_name_for($user['useragent']) : '';
            $user['name']        = ((int)$user['customer_id'] != 0) ? sprintf('%s %s', $user['first_name'], $user['last_name']) : $lang['common']['guest'];
            $user['session_length_min'] = (int)round(($user['session_last'] - $user['session_start']) / 60);
            $user['country']     = $country_for($user['ip_address']);

            $loc = $location_label($user['location'] ?? '');
            $user['location_label'] = $loc['label'];
            $user['is_checkout']    = $loc['is_checkout'];

            // Relative timestamps.
            $rel_last  = max(0, $now_t - (int)$user['session_last']);
            $rel_start = max(0, $now_t - (int)$user['session_start']);
            $user['last_relative']  = ($rel_last  < 60) ? sprintf($lang['statistics']['time_ago_seconds'], $rel_last)
                                     : (($rel_last  < 3600) ? sprintf($lang['statistics']['time_ago_minutes'], floor($rel_last/60))
                                     : sprintf($lang['statistics']['time_ago_hours'], floor($rel_last/3600)));
            $user['active_for']     = ($rel_start < 60) ? sprintf($lang['statistics']['duration_seconds'], $rel_start)
                                     : (($rel_start < 3600) ? sprintf($lang['statistics']['duration_minutes'], floor($rel_start/60))
                                     : sprintf($lang['statistics']['duration_hours'], floor($rel_start/3600), floor(($rel_start%3600)/60)));

            // Cart value: try to extract from serialised session_data. CubeCart
            // namespaces the basket under "__basket" (Session::set('', x, 'basket')).
            $user['cart_value'] = '';
            if (!empty($user['session_data'])) {
                $data = @unserialize($user['session_data'], ['allowed_classes' => false]);
                if (is_array($data)) {
                    $basket = $data['__basket'] ?? ($data['basket'] ?? null);
                    if (is_array($basket)) {
                        $total = $basket['total'] ?? ($basket['gross_total'] ?? null);
                        if ($total !== null && (float)$total > 0) {
                            $user['cart_value'] = $tax_inst->priceFormat((float)$total);
                        }
                    }
                }
            }

            unset($user['session_data']); // never expose serialised blob to the template
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
