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

global $glob, $lang, $admin_data;

## Acknowledge "New Extensions" dashboard tab — updates per-admin
## last-seen timestamp so items roll off once read.
if (isset($_GET['ack']) && $_GET['ack'] === 'extensions') {
    $ack_admin_id = (int)Admin::getInstance()->getId();
    if ($ack_admin_id > 0) {
        $GLOBALS['db']->update('CubeCart_admin_users', array('extensions_last_seen' => time()), array('admin_id' => $ack_admin_id));
    }
    httpredir('?_g=dashboard');
    exit;
}

## Release Notification
$notification_id = CC_VERSION.'_'.Admin::getInstance()->getId();
$release_notes_path = CC_ROOT_DIR.'/'.$GLOBALS['config']->get('config', 'adminFolder').'/sources/release_notes/'.CC_VERSION.'.inc.php';
if(file_exists($release_notes_path) && !$GLOBALS['config']->has('release_notes', $notification_id)) {
    $GLOBALS['config']->set('release_notes', $notification_id, '1');
    httpredir('?_g=release_notes&node='.CC_VERSION);
}

## Quick tour
$GLOBALS['smarty']->assign('QUICK_TOUR', true);

## Dismiss News banner — stores an MD5 of the announcement's URL so the banner stays
## hidden until a new top story (different URL → different hash) appears.
if (!empty($_POST['dismiss_news']) && isset($_POST['news_link'])) {
    $admin_id = (int)Admin::getInstance()->get('admin_id');
    $hash     = md5((string)$_POST['news_link']);
    $success  = (bool)$GLOBALS['db']->update('CubeCart_admin_users', array('news_dismissed_link' => $hash), array('admin_id' => $admin_id));
    if ($success) {
        $GLOBALS['session']->delete('', 'admin_data');
    }
    header('Content-Type: application/json');
    echo json_encode(array('status' => $success ? 'ok' : 'error'));
    exit;
}

## Save notes
if (isset($_POST['notes']['dashboard_notes'])) {
    $update  = array('dashboard_notes' => $_POST['notes']['dashboard_notes']);
    $success = (bool)$GLOBALS['db']->update('CubeCart_admin_users', $update, array('admin_id' => Admin::getInstance()->get('admin_id')));
    if ($success) {
        $GLOBALS['session']->delete('', 'admin_data');
    }
    if (!empty($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(array('status' => $success ? 'ok' : 'error'));
        exit;
    }
    if ($success) {
        $GLOBALS['main']->successMessage($lang['dashboard']['notice_notes_save']);
    } else {
        $GLOBALS['main']->errorMessage($lang['dashboard']['error_notes_save']);
    }
    httpredir(currentPage());
}

## Delete admin folder if it exists and shouldn't
if ($glob['adminFolder']!=='admin' && file_exists(CC_ROOT_DIR.'/admin')) {
    recursiveDelete(CC_ROOT_DIR.'/admin');
    if (file_exists(CC_ROOT_DIR.'/admin')) {
        $GLOBALS['main']->errorMessage($lang['dashboard']['delete_admin_folder']);
    }
}
## Delete admin file if it exists and shouldn't
if ($glob['adminFile']!=='admin.php' && file_exists(CC_ROOT_DIR.'/admin.php')) {
    unlink(CC_ROOT_DIR.'/admin.php');
    if (file_exists(CC_ROOT_DIR.'/admin.php')) {
        $GLOBALS['main']->errorMessage($lang['dashboard']['delete_admin_file']);
    }
}

## Check if setup folder remains after install/upgrade
if ($glob['installed'] && file_exists(CC_ROOT_DIR.'/setup')) {
    ## Attempt auto delete as we have just upgraded or installed
    if (isset($_COOKIE['cc_delete_setup']) && $_COOKIE['cc_delete_setup']) {
        recursiveDelete(CC_ROOT_DIR.'/setup');
        unlink(CC_ROOT_DIR.'/setup');
        $GLOBALS['session']->set_cookie('cc_delete_setup', '', time()-3600);
    }

    $history = $GLOBALS['db']->misc('SELECT `version` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_history` ORDER BY `time` DESC LIMIT 1');
    if (version_compare(CC_VERSION, $history[0]['version'], '>')) {
        $GLOBALS['main']->errorMessage(sprintf($lang['dashboard']['error_version'], CC_VERSION, $history[0]['version']));
    } elseif (file_exists(CC_ROOT_DIR.'/setup')) {
        $GLOBALS['main']->errorMessage($lang['dashboard']['error_setup_folder']);
    }
}
## Are they using the mysql root user?
if ($glob['dbusername'] == 'root' && !(bool)$GLOBALS['config']->get('config', 'debug')) {
    $GLOBALS['main']->errorMessage($lang['dashboard']['error_mysql_root'], true, false);
}
## Is caching disabled
if (!(bool)$GLOBALS['config']->get('config', 'cache')) {
    $GLOBALS['main']->errorMessage($lang['dashboard']['error_caching_disabled']);
}
## Windows only - Is global.inc.php writable?
if (substr(PHP_OS, 0, 3) !== 'WIN' && is_writable('includes/global.inc.php')) {
    if (!chmod('includes/global.inc.php', 0444)) {
        $GLOBALS['main']->errorMessage($lang['dashboard']['error_global_risk']);
    }
}

$mysql_mode = $GLOBALS['db']->misc('SELECT @@sql_mode;');
if (stristr($mysql_mode[0]['@@sql_mode'], 'strict')) {
    $GLOBALS['main']->errorMessage($lang['setup']['error_strict_mode']);
}

## Check current version via GitHub releases
if (!$GLOBALS['session']->has('version_check') && $request = new Request('version.cubecart.com', '/')) {
    $request->skiplog(true);
    $request->setMethod('get');
    $request->cache(true);
    $request->setSSL();
    $response = $request->send();
    if ($response !== false) {
        if (isset($response) && version_compare($response, CC_VERSION, '>')) {
            $GLOBALS['session']->set('version_available', $response);
        } else {
            $GLOBALS['session']->delete('version_available');
        }
    }
    $GLOBALS['session']->set('version_check', true);
}

## Check for extension updates — auto-install unless the admin has opted that extension out
if (!function_exists('cc_dashboard_auto_install_extension')) {
    /**
     * Download an extension zip from extensions.cubecart.com and extract it in place.
     * Mirrors the install_extension AJAX handler in plugins.index.inc.php but tuned
     * for non-interactive use: returns true/false instead of emitting JSON. Skin
     * auto-updates aren't reachable here (the dashboard only enumerates modules/*).
     */
    function cc_dashboard_auto_install_extension($download_url, $ext_type) {
        if (empty($download_url) || !filter_var($download_url, FILTER_VALIDATE_URL)) return false;
        $parsed = parse_url($download_url);
        if (!isset($parsed['host']) || $parsed['host'] !== 'extensions.cubecart.com') return false;
        if ($ext_type === 'skin') {
            $destination = CC_ROOT_DIR;
        } elseif (!empty($ext_type) && preg_match('/^[a-z0-9_]+$/i', $ext_type)) {
            $destination = CC_ROOT_DIR.'/modules/'.$ext_type;
        } else {
            $destination = CC_ROOT_DIR.'/modules/plugins';
        }
        if (!is_dir($destination) || !is_writable($destination)) return false;
        $tmp_path = CC_BACKUP_DIR.basename($download_url);
        $req = new Request('extensions.cubecart.com', $parsed['path'], 443, false, true, 30);
        $req->setMethod('get'); $req->setSSL(); $req->skiplog(true);
        $data = $req->send();
        if (!$data) $data = @file_get_contents($download_url);
        if (!$data) return false;
        $fp = @fopen($tmp_path, 'w');
        if (!$fp) return false;
        fwrite($fp, $data); fclose($fp);
        $zip = new ZipArchive();
        if ($zip->open($tmp_path) !== true) { @unlink($tmp_path); return false; }
        // Reject the zip if it would overwrite a write-protected file (matches install_extension)
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $file = $zip->statIndex($i);
            $root_path = $destination.'/'.$file['name'];
            if (file_exists($root_path) && !is_writable($root_path)) {
                $zip->close(); @unlink($tmp_path); return false;
            }
        }
        $ok = $zip->extractTo($destination);
        $zip->close();
        @unlink($tmp_path);
        return (bool)$ok;
    }
}

if (!$GLOBALS['session']->has('extension_update_check')) {
    $ext_request = new Request('extensions.cubecart.com', '/api/extensions', 443, false, true, 15);
    $ext_request->setMethod('get');
    $ext_request->setSSL();
    $ext_request->skiplog(true);
    $ext_request->cache(true);
    $ext_json = $ext_request->send();

    if ($ext_json) {
        $ext_data = json_decode($ext_json, true);
        if ($ext_data && !empty($ext_data['success']) && !empty($ext_data['extensions'])) {
            // Build installed modules list from filesystem
            $ext_module_paths = glob(CC_ROOT_DIR.'/modules/*/*/config.xml');
            $ext_installed = array();
            if (is_array($ext_module_paths)) {
                foreach ($ext_module_paths as $ext_mp) {
                    try {
                        $ext_xml = new SimpleXMLElement(file_get_contents($ext_mp));
                    } catch (Exception $e) {
                        continue;
                    }
                    if (is_object($ext_xml)) {
                        $ext_bn = basename(dirname($ext_mp));
                        $ext_type_dir = basename(dirname(dirname($ext_mp))); // modules/<type>/<bn>/config.xml
                        $ext_installed[$ext_bn] = array(
                            'name' => str_replace('_', ' ', (string)$ext_xml->info->name),
                            'version' => (string)$ext_xml->info->version,
                            'basename' => $ext_bn,
                            'type' => $ext_type_dir,
                        );
                        unset($ext_xml);
                    }
                }
            }

            // Compare installed versions against API; auto-install when not opted out
            $ext_auto_installed = array();   // successfully auto-updated
            $ext_auto_failed   = array();    // tried but failed
            $ext_skipped       = array();    // opted out; needs manual update
            foreach ($ext_data['extensions'] as $ext_api) {
                $ext_latest = end($ext_api['versions']);
                $ext_api_name_lower = strtolower($ext_api['name']);
                foreach ($ext_installed as $ext_key => $ext_inst) {
                    $ext_inst_name_lower = strtolower($ext_inst['name']);
                    if ($ext_inst_name_lower === $ext_api_name_lower || $ext_key === strtolower(str_replace(array(' ', '-'), '_', $ext_api['name']))) {
                        if (!version_compare($ext_latest['version'], $ext_inst['version'], '>')) break;

                        $label = $ext_inst['name'].' ('.$ext_inst['version'].' &rarr; '.$ext_latest['version'].')';
                        $opt_out = (string)$GLOBALS['config']->get($ext_inst['basename'], 'autoupdate_disabled') === '1';
                        if ($opt_out) {
                            $ext_skipped[] = $label;
                            break;
                        }
                        if (cc_dashboard_auto_install_extension($ext_latest['download'], $ext_api['type'])) {
                            $ext_auto_installed[] = $label;
                        } else {
                            $ext_auto_failed[] = $label;
                        }
                        break;
                    }
                }
            }

            if ($ext_auto_installed) {
                $GLOBALS['session']->delete('version_check');
                $GLOBALS['main']->successMessage($lang['module']['extensions_updated_desc'].' '.implode(', ', $ext_auto_installed).'.');
            }
            $tail_msgs = array();
            if ($ext_auto_failed) {
                $tail_msgs[] = implode(', ', $ext_auto_failed);
            }
            if ($ext_skipped) {
                $tail_msgs[] = implode(', ', $ext_skipped);
            }
            if ($tail_msgs) {
                $ext_msg = $lang['module']['extensions_available_desc'].' '.implode('; ', $tail_msgs).'. <a href="?_g=plugins">'.$lang['dashboard']['title_extension_updates'].'</a>';
                $GLOBALS['main']->errorMessage($ext_msg);
            }
        }
    }
    $GLOBALS['session']->set('extension_update_check', true);
}

## Check for language updates
if (!$GLOBALS['session']->has('language_update_check')) {
    $lang_request = new Request('extensions.cubecart.com', '/api/languages', 443, false, true, 15);
    $lang_request->setMethod('get');
    $lang_request->setSSL();
    $lang_request->skiplog(true);
    $lang_request->cache(true);
    $lang_json = $lang_request->send();

    if ($lang_json) {
        $lang_data = json_decode($lang_json, true);
        if ($lang_data && !empty($lang_data['languages'])) {
            $installed = $GLOBALS['language']->listLanguages();
            $lang_updates = array();
            if (is_array($installed)) {
                $api_versions = array();
                foreach ($lang_data['languages'] as $api_lang) {
                    $api_versions[$api_lang['code']] = $api_lang['version'];
                }
                foreach ($installed as $code => $info) {
                    if (isset($api_versions[$code]) && !empty($info['version'])
                        && version_compare($api_versions[$code], $info['version'], '>')) {
                        $lang_updates[] = trim((string)$info['title']).' ('.$info['version'].' &rarr; '.$api_versions[$code].')';
                    }
                }
            }
            if (!empty($lang_updates)) {
                $lang_msg = $lang['translate']['language_updates_available'].' ';
                $lang_msg .= implode(', ', $lang_updates).'. ';
                $lang_msg .= '<a href="?_g=settings&node=language#lang_list">'.$lang['translate']['title_installed_languages'].'</a>';
                $GLOBALS['main']->errorMessage($lang_msg);
            }
        }
    }
    $GLOBALS['session']->set('language_update_check', true);
}

$GLOBALS['smarty']->assign('DASH_NOTES', Admin::getInstance()->get('dashboard_notes'));

$GLOBALS['main']->wikiPage('Dashboard');
### Dashboard ###
$GLOBALS['main']->addTabControl($lang['dashboard']['title_dashboard'], 'dashboard');
## Quick Stats
if (Admin::getInstance()->permissions('statistics', CC_PERM_READ, false, false)) {
    if (!$GLOBALS['session']->has('chart_data')) {
        $total_sales = $GLOBALS['db']->query('SELECT SUM(`total`) as `total_sales` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary` WHERE `status` IN (2,3);');
        $quick_stats['total_sales'] = Tax::getInstance()->priceFormat((float)$total_sales[0]['total_sales']);

        $ave_order  = $GLOBALS['db']->query('SELECT AVG(`total`) as `ave_order` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary` WHERE `status` IN (2,3);');
        $quick_stats['ave_order'] = Tax::getInstance()->priceFormat((float)$ave_order[0]['ave_order']);

        $this_year    = date('Y');
        $this_month   = date('m');
        $this_month_start  = mktime(0, 0, 0, $this_month, '01', $this_year);
        ## Work out prev month looks silly but should stop -1 month on 1st March returning January (28 Days in Feb)
        $last_month   = date('m', strtotime("-1 month", mktime(12, 0, 0, $this_month, 15, $this_year)));
        $last_year    = ($last_month < $this_month) ? $this_year : ($this_year - 1);
        $last_month_start  = mktime(0, 0, 0, $last_month, '01', $last_year);
        $last_year_start   = mktime(0, 0, 0, '01', '01', $this_year - 1);

        $last_month_sales  = $GLOBALS['db']->query('SELECT SUM(`total`) as `last_month` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary` WHERE `status` in(2,3) AND `order_date` > '.$last_month_start.' AND `order_date` < '.$this_month_start.';');
        $quick_stats['last_month'] = Tax::getInstance()->priceFormat((float)$last_month_sales[0]['last_month']);

        $this_month_sales  = $GLOBALS['db']->query('SELECT SUM(`total`) as `this_month` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary` WHERE `status` in(2,3) AND `order_date` > '.$this_month_start.';');
        $quick_stats['this_month'] = Tax::getInstance()->priceFormat((float)$this_month_sales[0]['this_month']);

        // Month-over-month delta for the "this month" tile. Cap giant swings at 100% to avoid comical badges.
        $this_month_val = (float)$this_month_sales[0]['this_month'];
        $last_month_val = (float)$last_month_sales[0]['last_month'];
        if ($last_month_val > 0) {
            $delta_pct = (($this_month_val - $last_month_val) / $last_month_val) * 100;
            $abs = abs($delta_pct);
            $quick_stats['this_month_delta'] = array(
                'value'     => $abs >= 100 ? '100%+' : number_format($abs, 1).'%',
                'direction' => $delta_pct >= 0 ? 'up' : 'down',
            );
        }

        $GLOBALS['smarty']->assign('QUICK_STATS', $quick_stats);

        ## Chart + annual totals — one combined day-level query feeds both.
        ## Bucketing is by fiscal year + fiscal-month slot (configurable via
        ## settings.accounting_year_start_month / _day; defaults to calendar year).
        ## Day-level aggregation lets mid-month FY starts (e.g. 6 April) split a
        ## calendar month between two FYs precisely.
        $fy_start_month = fiscalYearStartMonth();
        $fy_start_day   = fiscalYearStartDay();
        $dbp = $GLOBALS['config']->get('config', 'dbprefix');
        $day_rows = $GLOBALS['db']->query(
            'SELECT YEAR(FROM_UNIXTIME(`order_date`)) AS `y`, '.
                   'MONTH(FROM_UNIXTIME(`order_date`)) AS `m`, '.
                   'DAY(FROM_UNIXTIME(`order_date`)) AS `d`, '.
                   'SUM(`total`) AS `total` '.
            'FROM `'.$dbp.'CubeCart_order_summary` '.
            'WHERE `status` IN (2,3) AND `total` > 0 AND `order_date` > 0 '.
            'GROUP BY `y`, `m`, `d` ORDER BY `y`, `m`, `d`'
        );
        // $monthly[$fy][$slot] = revenue total for fiscal-month slot N (0-11) of fiscal year $fy.
        $monthly       = array();
        $annual_totals = array();
        if ($day_rows) {
            foreach ($day_rows as $row) {
                $yr = (int)$row['y'];
                $mo = (int)$row['m'];
                $dy = (int)$row['d'];
                if ($yr < 1970 || $mo < 1 || $mo > 12 || $dy < 1 || $dy > 31) continue;
                $ts   = mktime(12, 0, 0, $mo, $dy, $yr);
                $fy   = fiscalYear($ts, $fy_start_month, $fy_start_day);
                $slot = fiscalSlot($ts, $fy_start_month, $fy_start_day);
                $monthly[$fy][$slot] = ($monthly[$fy][$slot] ?? 0) + (float)$row['total'];
                $annual_totals[$fy]  = ($annual_totals[$fy] ?? 0) + (float)$row['total'];
            }
        }

        // HSL → hex so Google Charts can use the same per-year colour as the year cards.
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

        $annual_display = array();
        $chart_data = array('data' => "['Month']", 'title' => '', 'colors' => '[]');
        // "Current" fiscal year and the rolling 5 we show.
        $current_fy = fiscalYear(time(), $fy_start_month);
        $earliest   = $current_fy - 4;
        for ($y = $earliest; $y <= $current_fy; $y++) {
            if (!isset($annual_totals[$y])) $annual_totals[$y] = 0.0;
        }
        ksort($annual_totals);
        $years_ordered = array();
        foreach ($annual_totals as $yr => $_) {
            if ($yr >= $earliest) $years_ordered[] = $yr;
        }
        $n = count($years_ordered);
        $annual_max = max($annual_totals) ?: 0;
        if ($n > 0) {

            // Golden-angle hue rotation (~137.5°) maximises visual distance between adjacent years.
            // Only real (non-empty) years get chart columns; ghost years are placeholder cards only.
            $chart_years = array();
            $chart_colors = array();
            foreach ($years_ordered as $idx => $yr) {
                $empty = $annual_totals[$yr] <= 0;
                $hue = (int)round(fmod(210 + $idx * 137.5, 360));
                $chart_col = null;
                if (!$empty) {
                    $chart_years[] = $yr;
                    $chart_colors[] = "'".$hsl_to_hex($hue, 0.65, 0.45)."'";
                    $chart_col = count($chart_years); // 1-indexed position after the 'Month' column
                }
                $annual_display[] = array(
                    'year'      => fiscalYearLabel($yr, $fy_start_month),
                    'total'     => Tax::getInstance()->priceFormat($annual_totals[$yr]),
                    'percent'   => ($annual_max > 0) ? round(($annual_totals[$yr] / $annual_max) * 100) : 0,
                    'hue'       => $hue,
                    'empty'     => $empty,
                    'chart_col' => $chart_col,
                );
            }

            if (!empty($chart_years)) {
                $header = "['Month'";
                foreach ($chart_years as $yr) $header .= ", '".fiscalYearLabel($yr, $fy_start_month)."'";
                $header .= "],";
                $chart_data['data'] = $header;

                // Future slots of the current FY get null so the chart doesn't show empty bars
                // for months that haven't started yet.
                $current_slot = fiscalSlot(time(), $fy_start_month, $fy_start_day);
                // Emit 12 columns in fiscal-month order. Slot 0's label is the start
                // month; subsequent slots roll forward by one calendar month. For a
                // January start this is Jan..Dec (= calendar-year behaviour).
                for ($slot = 0; $slot < 12; $slot++) {
                    $month = ((($fy_start_month - 1) + $slot) % 12) + 1;
                    $m_label = date('M', mktime(0, 0, 0, $month, 10));
                    $line = "['".$m_label."'";
                    foreach ($chart_years as $fy) {
                        if ($fy === $current_fy && $slot > $current_slot) {
                            $line .= ", null"; // future slot in the current FY
                        } else {
                            $line .= ", ".number_format($monthly[$fy][$slot] ?? 0, 2, '.', '');
                        }
                    }
                    $line .= "],";
                    $chart_data['data'] .= $line;
                }

                $chart_data['colors'] = '['.implode(',', $chart_colors).']';
                $chart_data['title']  = $lang['dashboard']['title_sales_stats'].': '.fiscalYearLabel(reset($chart_years), $fy_start_month).' - '.fiscalYearLabel(end($chart_years), $fy_start_month);
            }
        }
        $GLOBALS['smarty']->assign('ANNUAL_SALES', $annual_display);

        ## Top 5 search terms
        $top_searches = $GLOBALS['db']->select('CubeCart_search', array('searchstr', 'hits'), false, array('hits' => 'DESC'), 5);
        $GLOBALS['smarty']->assign('TOP_SEARCHES', $top_searches ?: array());

        ## Abandoned carts — last 14 days: daily sparkline + funnel totals.
        $abandon_rows = $GLOBALS['db']->query(
            'SELECT DATE(`notified_at`) AS `d`, '.
                   'COUNT(*) AS `sent`, '.
                   'SUM(`clicked_at` IS NOT NULL) AS `clicked`, '.
                   'SUM(`recovered_at` IS NOT NULL) AS `recovered` '.
            'FROM `'.$dbp.'CubeCart_cart_abandonment` '.
            'WHERE `notified_at` >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) '.
            'GROUP BY `d` ORDER BY `d` ASC'
        );
        $abandon_by_date = array();
        $abandon_totals  = array('sent' => 0, 'clicked' => 0, 'recovered' => 0);
        if ($abandon_rows) {
            foreach ($abandon_rows as $row) {
                $abandon_by_date[$row['d']] = array(
                    'sent'      => (int)$row['sent'],
                    'clicked'   => (int)$row['clicked'],
                    'recovered' => (int)$row['recovered'],
                );
                $abandon_totals['sent']      += (int)$row['sent'];
                $abandon_totals['clicked']   += (int)$row['clicked'];
                $abandon_totals['recovered'] += (int)$row['recovered'];
            }
        }
        $spark_max = 0;
        foreach ($abandon_by_date as $d) {
            if ($d['sent'] > $spark_max) $spark_max = $d['sent'];
        }
        $abandon_spark = array();
        for ($i = 13; $i >= 0; $i--) {
            $ts = strtotime('-'.$i.' days');
            $d  = date('Y-m-d', $ts);
            $day = $abandon_by_date[$d] ?? array('sent' => 0, 'clicked' => 0, 'recovered' => 0);
            $abandon_spark[] = array(
                'date'      => date('D j M', $ts),
                'label'     => date('j', $ts),
                'sent'      => $day['sent'],
                'clicked'   => $day['clicked'],
                'recovered' => $day['recovered'],
                'percent'   => $spark_max > 0 ? round(($day['sent'] / $spark_max) * 100) : 0,
            );
        }
        $abandon_totals['rate'] = $abandon_totals['sent'] > 0
            ? round(($abandon_totals['recovered'] / $abandon_totals['sent']) * 100, 1)
            : 0;
        $GLOBALS['smarty']->assign('ABANDON_SPARK', $abandon_spark);
        $GLOBALS['smarty']->assign('ABANDON_TOTALS', $abandon_totals);
    } else {
        $chart_data = $GLOBALS['session']->get('chart_data');
    }
    $GLOBALS['smarty']->assign('CHART', $chart_data);
}
## Last 5 orders
if (($last_orders = $GLOBALS['db']->select('CubeCart_order_summary', array('custom_oid', 'id', 'cart_order_id', 'first_name', 'last_name', 'name'), false, array('order_date' => 'DESC'), 5)) !== false) {
    $GLOBALS['smarty']->assign('LAST_ORDERS', $last_orders);
}

## Quick Tasks
$date_format = "Y-m-d";
$today   = date($date_format);
$quick_tasks['today']   = urlencode(date($date_format));
$quick_tasks['this_weeks'] = urlencode(date($date_format, strtotime("last monday")));
foreach ($GLOBALS['hooks']->load('admin.dashboard.quick_tasks') as $hook) {
    include $hook;
}
$GLOBALS['smarty']->assign('QUICK_TASKS', $quick_tasks);

## Pending Orders Tab
$page  = (isset($_GET['orders'])) ? $_GET['orders'] : 1;
$unsettled_count  = $GLOBALS['db']->count('CubeCart_order_summary', 'cart_order_id', '`status` IN (1,2) OR `dashboard` = 1');

## Pending Orders Sort
$order_by = '';
if (!isset($_GET['sort']) || !is_array($_GET['sort'])) {
    $_GET['sort'] = array('order_date' => 'ASC');
}
$key = array_keys($_GET['sort'])[0];
$sort = ($_GET['sort'][$key] === 'ASC' ? 'ASC' : 'DESC'); // only allow ASC or DESC sort values
if (!in_array($key, array('cart_order_id','first_name','status','order_date','total'))) {
    $order_by = '`dashboard` DESC, `status` DESC, `order_date` ASC';
}

$current_page = currentPage(array('sort'));
$thead_sort = array(
    'cart_order_id' => $GLOBALS['db']->column_sort('cart_order_id', $lang['orders']['order_number'], 'sort', $current_page, $_GET['sort'], 'orders'),
    'first_name' => $GLOBALS['db']->column_sort('first_name', $lang['common']['name'], 'sort', $current_page, $_GET['sort'], 'orders'),
    'status' => $GLOBALS['db']->column_sort('status', $lang['common']['status'], 'sort', $current_page, $_GET['sort'], 'orders'),
    'order_date' => $GLOBALS['db']->column_sort('order_date', $lang['common']['date'], 'sort', $current_page, $_GET['sort'], 'orders'),
    'total' => $GLOBALS['db']->column_sort('total', $lang['basket']['total'], 'sort', $current_page, $_GET['sort'], 'orders'),
);

$GLOBALS['smarty']->assign('THEAD_ORDERS', $thead_sort);
$order_by = (empty($order_by) ? '`dashboard` DESC, `'.$key.'` '.$sort : $order_by);

$per_page = $GLOBALS['main']->itemsPerPage('dashboard_pending', $_GET['items'] ?? 0, 25);
$page_break_url = currentPage(array('items'));
$GLOBALS['smarty']->assign('PAGE_BREAKS', array(25, 50, 100, 250, 500));
$GLOBALS['smarty']->assign('PAGE_BREAK', $per_page);
$GLOBALS['smarty']->assign('PAGE_BREAK_URL', $page_break_url);
$unsettled_orders = $GLOBALS['db']->select('CubeCart_order_summary', false, '`status` IN (1,2) OR `dashboard` = 1', $order_by, $per_page, $page);

if ($unsettled_orders) {
    $tax = Tax::getInstance();
    $GLOBALS['main']->addTabControl($lang['dashboard']['title_orders_unsettled'], 'orders', null, null, $unsettled_count);
    $customer_ids = array();
    foreach ($unsettled_orders as $order) {
        $customer_ids[$order['customer_id']] = true;
    }
    $customers_in = implode(',', array_keys($customer_ids));
    
    $customer_type = array();
    if($customers = $GLOBALS['db']->select('CubeCart_customer', array('type','customer_id'), 'customer_id IN ('.$customers_in.')')) {
        foreach ($customers as $customer) {
            $customer_type[$customer['customer_id']] = $customer['type'];
        }
    }
    
    $smarty_data['order_tasks'][] = array(
        'opt_group_name' => '', // Leave blank for no option grouping for this group
        'selections' => array(
            array('value' => "", 'string' => $lang['orders']['option_nothing'], 'style' => ""),
            array('value' => "print", 'string' => $lang['orders']['option_print'], 'style' => ""),
            array('value' => "delete", 'string' => $lang['orders']['option_delete'], 'style' => "color: red"),
        )
    );

    for ($i = 1; $i <= 6; ++$i) {
        $smarty_data['order_status'][] = array(
            'id'  => $i,
            'selected' => (isset($summary[0]) && isset($summary[0]['status']) && (int)$summary[0]['status'] === $i) ? ' selected="selected"' : '',
            'string' => $lang['order_state']['name_'.$i],
        );
    }

    foreach ($GLOBALS['hooks']->load('admin.order.index.order_tasks') as $hook) {
        include $hook;
    }
    $GLOBALS['smarty']->assign('LIST_ORDER_TASKS', $smarty_data['order_tasks']);
    $GLOBALS['smarty']->assign('LIST_ORDER_STATUS', $smarty_data['order_status']);

    foreach ($unsettled_orders as $order) {
        $cart_order_ids[] = "'".$order['cart_order_id']."'";
        $cust_type_value  = $customer_type[$order['customer_id']] ?? null;
        $order['icon'] = $cust_type_value == 1 ? 'user_registered' : 'user_ghost'; // deprecated since 6.1.5
        $order['type'] = empty($cust_type_value) ? 2 : $cust_type_value;
        $order['cust_type'] = array("1" => 'title_key_registered', "2" => 'title_key_unregistered');
        $order['date'] = formatTime($order['order_date']);
        $order['total'] = Tax::getInstance()->priceFormat($order['total']);
        $order['status_class']  = 'order_status_'.$order['status'];
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

    foreach ($GLOBALS['hooks']->load('admin.dashboard.unsettled_orders') as $hook) {
        include $hook;
    }
    
    $GLOBALS['smarty']->assign('ORDERS', $orders);
    $GLOBALS['smarty']->assign('ORDER_PAGINATION', $GLOBALS['db']->pagination($unsettled_count, $per_page, $page, $show = 5, 'orders', 'orders', $glue = ' ', $view_all = true));
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
        $review['delete'] = "?_g=products&node=reviews&delete=".(int)$review['id'].'&token='.SESSION_TOKEN;
        $review['edit']  = "?_g=products&node=reviews&edit=".(int)$review['id'];
        $review['stars'] = 5;
        $review_list[] = $review;
    }
    $GLOBALS['smarty']->assign('REVIEWS', $review_list);
    $GLOBALS['smarty']->assign('REVIEW_PAGINATION', $GLOBALS['db']->pagination($reviews_count, 25, $page, $show = 5, 'reviews', 'product_reviews', $glue = ' ', $view_all = true));
}

## Stock Warnings
$page  = (isset($_GET['stock'])) ? $_GET['stock'] : 1;

$per_page = $GLOBALS['main']->itemsPerPage('dashboard_stock', $_GET['items_stock'] ?? 0, 25);
$page_break_url = currentPage(array('items_stock'));
$GLOBALS['smarty']->assign('PAGE_BREAKS_STOCK', array(25, 50, 100, 250, 500));
$GLOBALS['smarty']->assign('PAGE_BREAK_STOCK', $per_page);
$GLOBALS['smarty']->assign('PAGE_BREAK_URL_STOCK', $page_break_url);

$dbprefix = $GLOBALS['config']->get('config', 'dbprefix');
$tables = '`'.$dbprefix.'CubeCart_inventory` AS `I` LEFT JOIN `'.$dbprefix.'CubeCart_option_matrix` AS `M` on `I`.`product_id` = `M`.`product_id`';

$fields = 'I.name, I.product_code, I.stock_level AS I_stock_level, I.stock_warning AS I_stock_warning, I.product_id, M.stock_level AS M_stock_level, M.use_stock as M_use_stock, M.cached_name';
$stock_warn_level = ($GLOBALS['config']->isEmpty('config', 'stock_warn_level')) ? '0' : $GLOBALS['config']->get('config', 'stock_warn_level');
$condition = $GLOBALS['config']->get('config', 'stock_warn_type') == '1' ? 'I.stock_warning' : $stock_warn_level;
$where = "use_stock_level = 1 AND ((M.status = 1 AND M.use_stock = 1 AND M.stock_level <= $condition) OR (I.stock_level <= $condition AND NOT EXISTS (SELECT 1 FROM `".$dbprefix."CubeCart_option_matrix` M2 WHERE M2.product_id = I.product_id AND M2.status = 1 AND M2.use_stock = 1)))";
// Stock Warnings Sort
if (!isset($_GET['sort']) || !is_array($_GET['sort'])) {
    $_GET['sort'] = array('stock_level' => 'DESC');
}
$key = array_keys($_GET['sort'])[0];
$sort = ($_GET['sort'][$key] === 'ASC' ? 'ASC' : 'DESC'); // only allow ASC or DESC sort values
if (!in_array($key, array('name','stock_level','product_code'))) {
    $key = 'stock_level';
    $sort = 'DESC';
}

$current_page = currentPage(array('sort'));
$thead_sort = array(
    'stock_level' => $GLOBALS['db']->column_sort('stock_level', $lang['dashboard']['stock_level'], 'sort', $current_page, $_GET['sort'], 'stock_warnings'),
    'name' => $GLOBALS['db']->column_sort('name', $lang['catalogue']['product_name'], 'sort', $current_page, $_GET['sort'], 'stock_warnings'),
    'product_code' => $GLOBALS['db']->column_sort('product_code', $lang['catalogue']['product_code'], 'sort', $current_page, $_GET['sort'], 'stock_warnings'),
);

$GLOBALS['smarty']->assign('THEAD_STOCK', $thead_sort);
$order_by = 'I.`'.$key.'` '.$sort;

if ($stock_c = $GLOBALS['db']->select($tables, $fields, $where)) {
    $stock_count = count($stock_c);
    $stock = $GLOBALS['db']->select($tables, $fields, $where, $order_by, $per_page, $page);
    $GLOBALS['smarty']->assign('STOCK', $stock);
    $GLOBALS['main']->addTabControl($lang['dashboard']['title_stock_warnings'], 'stock_warnings', null, null, $stock_count);
    $GLOBALS['smarty']->assign('STOCK_PAGINATION', $GLOBALS['db']->pagination($stock_count, $per_page, $page, $show = 5, 'stock', 'stock_warnings', $glue = ' ', $view_all = true));

    foreach ($GLOBALS['hooks']->load('admin.dashboard.stock.post') as $hook) {
        include $hook;
    }
}

## Most recent extensions from the marketplace (shares cache with Plugins page, 1h TTL)
$recent_cache_key = 'extensions_api_list';
$recent_raw       = $GLOBALS['session']->get($recent_cache_key, 'extensions_cache');
$recent_cache_ts  = $GLOBALS['session']->get($recent_cache_key.'_time', 'extensions_cache');

if (!$recent_raw || !$recent_cache_ts || (time() - $recent_cache_ts) > 3600) {
    $recent_req = new Request('extensions.cubecart.com', '/api/extensions', 443, false, true, 10);
    $recent_req->setMethod('get');
    $recent_req->setSSL();
    $recent_req->skiplog(true);
    if (($recent_json = $recent_req->send()) !== false) {
        $recent_data = json_decode($recent_json, true);
        if ($recent_data && !empty($recent_data['success']) && !empty($recent_data['extensions'])) {
            $recent_raw = $recent_data['extensions'];
            $GLOBALS['session']->set($recent_cache_key, $recent_raw, 'extensions_cache');
            $GLOBALS['session']->set($recent_cache_key.'_time', time(), 'extensions_cache');
        }
    }
}

if (is_array($recent_raw) && !empty($recent_raw)) {
    // Build a lightweight installed-name index to mark already-installed items
    $recent_installed = array();
    foreach (glob(CC_ROOT_DIR.'/modules/*/*/config.xml') as $recent_mp) {
        try {
            $recent_xml = new SimpleXMLElement(file_get_contents($recent_mp));
            if (is_object($recent_xml) && !empty($recent_xml->info->name)) {
                $recent_installed[strtolower((string)$recent_xml->info->name)] = true;
            }
        } catch (Exception $e) {
            continue;
        }
    }

    // Per-admin "last seen" timestamp. Baseline of 2026-04-20 00:00:00 local
    // means existing marketplace extensions aren't counted as "new" for a
    // fresh install — only versions released after that point surface here.
    $recent_baseline = mktime(0, 0, 0, 4, 20, 2026);
    $recent_admin_row = $GLOBALS['db']->select('CubeCart_admin_users', array('extensions_last_seen'), array('admin_id' => (int)Admin::getInstance()->getId()));
    $recent_last_seen = (!empty($recent_admin_row[0]['extensions_last_seen'])) ? (int)$recent_admin_row[0]['extensions_last_seen'] : 0;
    $recent_threshold = max($recent_last_seen, $recent_baseline);

    $recent_candidates = array();
    foreach ($recent_raw as $recent_ext) {
        if (empty($recent_ext['versions']) || !is_array($recent_ext['versions'])) {
            continue;
        }
        $recent_latest = end($recent_ext['versions']);
        $recent_ts = !empty($recent_ext['created_at']) ? (int)strtotime($recent_ext['created_at']) : 0;
        if ($recent_ts <= 0 || $recent_ts <= $recent_threshold) {
            continue;
        }
        $recent_candidates[] = array(
            'name'         => $recent_ext['name'],
            'description'  => !empty($recent_ext['description']) ? $recent_ext['description'] : '',
            'type'         => !empty($recent_ext['type']) ? $recent_ext['type'] : '',
            'category'     => !empty($recent_ext['category']) ? ucwords(str_replace('_', ' ', $recent_ext['category'])) : '',
            'version'      => $recent_latest['version'],
            'timestamp'    => $recent_ts,
            'date'         => formatTime($recent_ts),
            'purchase_url' => !empty($recent_ext['purchase_url']) ? $recent_ext['purchase_url'] : '',
            'price'        => !empty($recent_ext['price']) ? $recent_ext['price'] : '',
            'recommended'  => !empty($recent_ext['recommended']),
            'is_installed' => isset($recent_installed[strtolower($recent_ext['name'])]),
        );
    }

    usort($recent_candidates, function ($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
    $recent_candidates = array_slice($recent_candidates, 0, 5);

    if (!empty($recent_candidates)) {
        $GLOBALS['smarty']->assign('RECENT_EXTENSIONS', $recent_candidates);
        $GLOBALS['main']->addTabControl($lang['dashboard']['title_extensions_recent'], 'extensions_recent', null, null, count($recent_candidates));
    }
}

foreach ($GLOBALS['hooks']->load('admin.dashboard.tabs') as $hook) {
    include $hook;
}

$GLOBALS['smarty']->assign('PLUGIN_TABS', ($smarty_data['plugin_tabs'] ?? false));

## Latest News (from RSS) + per-admin dismissed-link
$dismissed_row = $GLOBALS['db']->select('CubeCart_admin_users', array('news_dismissed_link'), array('admin_id' => (int)Admin::getInstance()->get('admin_id')));
$GLOBALS['smarty']->assign('NEWS_DISMISSED_LINK', (!empty($dismissed_row[0]['news_dismissed_link'])) ? $dismissed_row[0]['news_dismissed_link'] : '');

if ($GLOBALS['session']->has('rss_news')) {
    $GLOBALS['smarty']->assign('NEWS', $GLOBALS['session']->get('rss_news'));
} else {
    $request = new Request('community.cubecart.com', '/c/news-announcements/5.rss');
    $request->setSSL();
    $request->cache(true);
    $request->skiplog(true);
    $request->setMethod('get');

    if (($response = $request->send()) !== false) {
        try {
            if (($data = new SimpleXMLElement($response)) !== false) {
                foreach ($data->channel->children() as $key => $value) {
                    if ($key == 'item') {
                        continue;
                    }
                    $news[$key] = (string)$value;
                }
                if (!empty($news['title'])) {
                    $news['title'] = trim(explode('-', $news['title'], 2)[0]);
                }
                if ($data['version'] >= 2) {
                    foreach ($data->channel->item as $item) {
                        $title = (string)$item->title;
                        // Skip Discourse's boilerplate "About the X" category-intro post.
                        if (stripos($title, 'About the ') === 0) {
                            continue;
                        }
                        $news['items'][] = array(
                            'title' => $title,
                            'link'  => (string)$item->link,
                        );
                        if (count($news['items']) >= 5) {
                            break;
                        }
                    }
                }
                $GLOBALS['session']->set('rss_news', $news);
                $GLOBALS['smarty']->assign('NEWS', $news);
            }
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
    }
}
// Pre-hash the top story link so the template can compare it against the stored dismissed hash.
$news_assigned = $GLOBALS['smarty']->getTemplateVars('NEWS');
if (!empty($news_assigned['items'][0]['link'])) {
    $GLOBALS['smarty']->assign('NEWS_TOP_HASH', md5($news_assigned['items'][0]['link']));
}
$GLOBALS['main']->addTabControl($lang['dashboard']['title_store_overview'], 'advanced');

$count = array(
    'products' => number_format((int)$GLOBALS['db']->count('CubeCart_inventory', 'product_id')),
    'categories' => number_format((int)$GLOBALS['db']->count('CubeCart_category', 'cat_id')),
    'orders' => number_format((int)$GLOBALS['db']->count('CubeCart_order_summary', 'cart_order_id')),
    'customers' => number_format((int)$GLOBALS['db']->count('CubeCart_customer', 'customer_id'))
);

$system = array(
    'cc_version' => CC_VERSION,
    'cc_build'  => null,
    'php_version' => PHP_VERSION,
    'mysql_version' => $GLOBALS['db']->serverVersion(),
    'server'  => htmlspecialchars($_SERVER['SERVER_SOFTWARE']),
    'client'  => htmlspecialchars($_SERVER['HTTP_USER_AGENT'])
);

$GLOBALS['smarty']->assign('SYS', $system);
$GLOBALS['smarty']->assign('PHP', ini_get_all());
$GLOBALS['smarty']->assign('COUNT', $count);

$GLOBALS['main']->addTabControl($lang['common']['search'], 'sidebar');

foreach ($GLOBALS['hooks']->load('admin.dashboard.custom_quick_tasks') as $hook) { include $hook; }
if (isset($custom_quick_tasks) && is_array($custom_quick_tasks)) {
    $GLOBALS['smarty']->assign('CUSTOM_QUICK_TASKS', $custom_quick_tasks);
}
$default_quick_tasks = array(
    '?_g=reports&report[date][from]='.$quick_tasks['today'].'&report[date][to]='.$quick_tasks['today'] => $lang['dashboard']['task_orders_view_day'],
    '?_g=reports&report[date][from]='.$quick_tasks['this_weeks'].'&report[date][to]='.$quick_tasks['today'] => $lang['dashboard']['task_orders_view_week'],
    '?_g=reports' => $lang['dashboard']['task_orders_view_month'],
    '?_g=products&action=add' => $lang['catalogue']['product_add'],
    '?_g=categories&action=add' => $lang['catalogue']['category_add']
);
foreach ($GLOBALS['hooks']->load('admin.dashboard.default_quick_tasks') as $hook) { include $hook; }
$GLOBALS['smarty']->assign('DEFAULT_QUICK_TASKS', $default_quick_tasks);

$page_content = $GLOBALS['smarty']->fetch('templates/dashboard.index.php');
