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
class Cron
{
    public function updateExchangeRates($currency = '', $echo = true) {
        ## European Central Bank
        $output = array();
        if (($request = new Request('www.ecb.europa.eu', '/stats/eurofxref/eurofxref-daily.xml')) !== false) {
            $request->setMethod('get');
            $request->setSSL();
            if(defined('CC_IN_SETUP')) {
                $request->skiplog(true);
            }
            $rates_xml = $request->send();

            if (!empty($rates_xml)) {
                try {
                    $xml  = new SimpleXMLElement($rates_xml);
                    foreach ($xml->Cube->Cube->Cube as $c) {
                        $rate = $c->attributes();
                        $fx[(string)$rate['currency']] = (float)$rate['rate'];
                    }
                    $fx['EUR'] = 1;
                    $updated = strtotime((string)$xml->Cube->Cube->attributes()->time);
                    # Get the divisor
                    if(empty($currency)) {
                        $currency = $GLOBALS['config']->get('config', 'default_currency');
                    }
                    $currency = strtoupper($currency);
                    if (!isset($fx[$currency])) {
                        trigger_error('Default currency '.$currency.' is not available from the ECB exchange rate feed.', E_USER_WARNING);
                        throw new Exception('Default currency '.$currency.' not in ECB feed');
                    }
                    $base  = (1/$fx[$currency]);
                    foreach ($fx as $code => $rate) {
                        $value = ($base/(1/$rate));
                        $output[] = array(
                            'currency' => $code,
                            'rate' => $value,
                            'time' => $updated
                        );
                        $GLOBALS['db']->update('CubeCart_currency', array('value' => $value, 'updated' => $updated), array('code' => $code), true);
                    }
                } catch (Exception $e) {
                    trigger_error($e->getMessage(), E_USER_WARNING);
                }
            }
        }
        if($echo) {
            echo json_encode($output);
        } else {
            return $output;
        }
    }
    public function clearCache() {
        return $GLOBALS['cache']->clear();
    }
    public function rebuildSitemap() {
        return $GLOBALS['seo']->sitemap() ? 'Sitemap rebuilt' : false;
    }
    public function runSnippets() {
        foreach ($GLOBALS['hooks']->load('cron') as $hook) {
            include $hook;
        }
    }

    /**
     * Send cart abandonment notification emails
     */
    public function sendAbandonmentEmails() {
        if (!$GLOBALS['config']->get('config', 'abandoned_cart_enabled')) {
            return 'Disabled';
        }

        $delay = (int)$GLOBALS['config']->get('config', 'abandoned_cart_delay');
        if ($delay < 3600) {
            $delay = 86400; // Default 24 hours
        }

        $cutoff = time() - $delay;

        $notify_cooldown = (int)$GLOBALS['config']->get('config', 'abandoned_cart_notify_cooldown');
        if ($notify_cooldown < 3600) {
            $notify_cooldown = 604800;
        }
        $order_window = (int)$GLOBALS['config']->get('config', 'abandoned_cart_order_window');
        if ($order_window < 3600) {
            $order_window = 259200;
        }
        $notify_cutoff = time() - $notify_cooldown;
        $order_cutoff = time() - $order_window;
        $max_age_cutoff = time() - 604800; // 7 days - ignore carts with no session activity beyond this

        // Find customers with saved carts who have abandoned
        $pfx = $GLOBALS['config']->get('config', 'dbprefix');
        $query = "SELECT sc.customer_id, sc.basket, c.email, c.first_name, c.last_name, c.language
            FROM `{$pfx}CubeCart_saved_cart` sc
            JOIN `{$pfx}CubeCart_customer` c ON sc.customer_id = c.customer_id
            WHERE c.abandon_optout = 0
              AND c.status = 1
              AND NOT EXISTS (
                SELECT 1 FROM `{$pfx}CubeCart_sessions` s
                WHERE s.customer_id = sc.customer_id AND s.session_last >= ".(int)$cutoff."
              )
              AND NOT EXISTS (
                SELECT 1 FROM `{$pfx}CubeCart_cart_abandonment` ca
                WHERE ca.customer_id = sc.customer_id AND ca.notified_at > '".date('Y-m-d H:i:s', $notify_cutoff)."'
              )
              AND NOT EXISTS (
                SELECT 1 FROM `{$pfx}CubeCart_order_summary` os
                WHERE os.customer_id = sc.customer_id AND os.order_date > ".(int)$order_cutoff."
                  AND os.status IN (2, 3)
              )
              AND EXISTS (
                SELECT 1 FROM `{$pfx}CubeCart_sessions` s2
                WHERE s2.customer_id = sc.customer_id AND s2.session_last >= ".(int)$max_age_cutoff."
              )";

        $results = $GLOBALS['db']->query($query);
        if (!$results) {
            return '0 emails sent';
        }

        $sent = 0;
        $mailer = Mailer::getInstance();
        $store_name = $GLOBALS['config']->get('config', 'store_name');

        // Look up configured discount coupon for abandoned cart emails
        $coupon_code = '';
        $coupon_description = '';
        $coupon_id = (int)$GLOBALS['config']->get('config', 'abandoned_cart_coupon');
        if ($coupon_id > 0) {
            $coupon_row = $GLOBALS['db']->select('CubeCart_coupons', array('code', 'discount_percent', 'discount_price'), "`coupon_id` = ".$coupon_id." AND `status` = 1 AND `archived` = 0 AND (`expires` = '0000-00-00' OR `expires` >= CURDATE())", false, 1, false, false);
            if ($coupon_row) {
                $coupon_code = $coupon_row[0]['code'];
                if ($coupon_row[0]['discount_percent'] > 0) {
                    $coupon_description = $coupon_row[0]['discount_percent'].'% off your order';
                } else {
                    $coupon_description = Tax::getInstance()->priceFormat($coupon_row[0]['discount_price']).' off your order';
                }
            }
        }

        foreach ($results as $row) {
            $contents = @unserialize($row['basket']);
            if (empty($contents) || !is_array($contents)) {
                continue;
            }

            // Build product list for email
            $products = array();
            $item_count = 0;
            foreach ($contents as $hash => $item) {
                if (!isset($item['id'])) {
                    continue;
                }
                $product = $GLOBALS['db']->select('CubeCart_inventory', array('name', 'price', 'sale_price', 'product_id'), array('product_id' => (int)$item['id']), false, 1, false, false);
                if (!$product) {
                    continue;
                }
                $p = $product[0];
                $price = ($p['sale_price'] > 0 && $p['sale_price'] < $p['price']) ? $p['sale_price'] : $p['price'];
                $qty = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                $item_count += $qty;

                // Get product options text
                $options_text = '';
                if (!empty($item['options']) && is_array($item['options'])) {
                    $opt_parts = array();
                    foreach ($item['options'] as $opt_id => $opt_val) {
                        $opt_data = $GLOBALS['db']->select('CubeCart_option_value', array('value_name'), array('value_id' => (int)$opt_val), false, 1, false, false);
                        if ($opt_data) {
                            $opt_parts[] = $opt_data[0]['value_name'];
                        }
                    }
                    $options_text = implode(', ', $opt_parts);
                }

                // Get product thumbnail
                $image_url = '';
                $img = $GLOBALS['db']->select('CubeCart_image_index', array('file_id'), array('product_id' => (int)$item['id']), array('main_img' => 'DESC'), 1, false, false);
                if ($img) {
                    $file = $GLOBALS['db']->select('CubeCart_filemanager', array('filepath', 'filename'), array('file_id' => (int)$img[0]['file_id']), false, 1, false, false);
                    if ($file) {
                        $image_url = $GLOBALS['storeURL'].'/images/source/'.$file[0]['filepath'].$file[0]['filename'];
                    }
                }

                $products[] = array(
                    'name' => $p['name'],
                    'price' => Tax::getInstance()->priceFormat($price),
                    'raw_price' => $price,
                    'quantity' => $qty,
                    'options' => $options_text,
                    'image' => $image_url,
                );
            }

            if (empty($products)) {
                continue;
            }

            // Generate recovery token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 604800); // 7 days

            $abandon_data = array(
                'customer_id' => (int)$row['customer_id'],
                'token' => $token,
                'notified_at' => date('Y-m-d H:i:s'),
                'expires_at' => $expires,
            );
            if (!empty($coupon_code)) {
                $abandon_data['coupon_code'] = $coupon_code;
            }
            $GLOBALS['db']->insert('CubeCart_cart_abandonment', $abandon_data);

            $recovery_link = $GLOBALS['storeURL'].'/index.php?_a=recover&token='.$token;
            $optout_link = $GLOBALS['storeURL'].'/index.php?_a=recover&action=optout&token='.$token;

            $data = array(
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'store_name' => $store_name,
                'item_count' => $item_count,
                'recovery_link' => $recovery_link,
                'optout_link' => $optout_link,
                'coupon_code' => $coupon_code,
                'coupon_description' => $coupon_description,
            );

            $GLOBALS['smarty']->assign('PRODUCTS', $products);

            $language = !empty($row['language']) ? $row['language'] : $GLOBALS['config']->get('config', 'default_language');
            $email_content = $mailer->loadContent('cart.abandoned', $language, $data);
            if ($email_content) {
                if ($mailer->sendEmail($row['email'], $email_content)) {
                    $sent++;
                }
            }
        }

        return $sent.' email(s) sent';
    }

    /**
     * Process queued newsletters in throttled batches.
     *
     * Picks the oldest queued/sending newsletter, sends up to per-tick or hourly-remaining
     * (whichever is smaller), and updates the cursor + sent_count. Resumable across ticks:
     * a half-finished newsletter just continues on the next run. Hourly throttle is
     * computed from CubeCart_newsletter_send_log so it covers everything sent in the last
     * 60 minutes regardless of which newsletter it belonged to.
     */
    public function processNewsletters() {
        // Defaults baked in. Override per-install via $glob in includes/global.inc.php
        // (see global.inc.php-dist for the documented example).
        $per_tick     = (int)$GLOBALS['config']->get('config', 'newsletter_per_tick');
        $hourly_limit = (int)$GLOBALS['config']->get('config', 'newsletter_hourly_limit');
        if ($per_tick     <= 0) { $per_tick     = 50;  }
        if ($hourly_limit <= 0) { $hourly_limit = 200; }
        $pfx = $GLOBALS['config']->get('config', 'dbprefix');

        // Prune old send-log rows opportunistically (only the last hour matters for throttling)
        $GLOBALS['db']->misc(sprintf("DELETE FROM `%sCubeCart_newsletter_send_log` WHERE `sent_at` < (NOW() - INTERVAL 7 DAY)", $pfx));

        // Pick the oldest queued/sending newsletter
        $queued = $GLOBALS['db']->select('CubeCart_newsletter', false, "`status` IN (2, 3)", array('date_saved' => 'ASC'), 1, false, false);
        if (!$queued) {
            return 'Idle (no queued newsletters)';
        }
        $newsletter_id = (int)$queued[0]['newsletter_id'];

        // Hourly quota: count every outgoing email (newsletter + transactional) in the
        // last 60 minutes. Host SMTP caps don't distinguish between message types, so the
        // throttle has to consider everything the mailer has sent.
        $rows = $GLOBALS['db']->misc(sprintf("SELECT COUNT(*) AS c FROM `%sCubeCart_email_log` WHERE `date` >= (NOW() - INTERVAL 1 HOUR)", $pfx));
        $sent_last_hour = isset($rows[0]['c']) ? (int)$rows[0]['c'] : 0;
        $remaining_quota = $hourly_limit - $sent_last_hour;
        if ($remaining_quota <= 0) {
            return sprintf('Throttled — %d/%d sent in last hour', $sent_last_hour, $hourly_limit);
        }

        $batch = min($per_tick, $remaining_quota);
        $sent = Newsletter::getInstance()->processQueue($newsletter_id, $batch);
        if ($sent === false) {
            return sprintf('Newsletter %d: send failed', $newsletter_id);
        }
        return sprintf('Newsletter %d: sent %d (hourly used %d/%d)', $newsletter_id, $sent, $sent_last_hour + $sent, $hourly_limit);
    }

    /**
     * Ensure default cron tasks exist in the database
     */
    public static function ensureDefaults() {
        $defaults = array(
            array('method' => 'updateExchangeRates', 'label' => 'Update Exchange Rates', 'enabled' => 1, 'frequency' => 86400),
            array('method' => 'clearCache', 'label' => 'Clear Cache*', 'enabled' => 0, 'frequency' => 21600),
            array('method' => 'runSnippets', 'label' => 'Run Code Snippets / Hooks**', 'enabled' => 0, 'frequency' => 3600),
            array('method' => 'sendAbandonmentEmails', 'label' => 'Send Cart Abandonment Emails', 'enabled' => 0, 'frequency' => 3600),
            array('method' => 'rebuildSitemap', 'label' => 'Rebuild Sitemap', 'enabled' => 1, 'frequency' => 86400),
            array('method' => 'processNewsletters', 'label' => 'Process Newsletter Queue', 'enabled' => 1, 'frequency' => 600),
        );
        foreach ($defaults as $task) {
            $exists = $GLOBALS['db']->select('CubeCart_cron_tasks', 'id', array('method' => $task['method']), false, false, false, false);
            if (!$exists) {
                $GLOBALS['db']->insert('CubeCart_cron_tasks', $task);
            }
        }
    }

    /**
     * Per-task wall-clock budget. Each task gets a fresh allowance via set_time_limit()
     * so one slow job can't burn the whole cron window. Also acts as the staleness
     * threshold for the started_at lock — a task marked running for longer than this
     * is treated as crashed and may be re-claimed.
     */
    const TASK_TIMEOUT = 600;

    /**
     * Unified cron entry point - runs all enabled tasks that are due
     */
    public function run() {
        $tasks = $GLOBALS['db']->select('CubeCart_cron_tasks', false, array('enabled' => 1), false, false, false, false);
        $output = array();
        if ($tasks) {
            $now = time();
            foreach ($tasks as $task) {
                $method = $task['method'];
                if (!method_exists($this, $method)) {
                    continue;
                }

                // Concurrency guard: another invocation already claimed this task.
                // Stale locks (older than TASK_TIMEOUT) are treated as crashed and re-claimable.
                if (!empty($task['started_at'])) {
                    $age = $now - strtotime($task['started_at']);
                    if ($age >= 0 && $age < self::TASK_TIMEOUT) {
                        $output[] = $task['label'] . ': skipped (in progress since ' . $task['started_at'] . ')';
                        continue;
                    }
                }

                // Due check: cadence is keyed off last_run, which we now stamp at start
                // (not completion). A task that crashes still gets its last_run advanced,
                // so the dispatcher won't re-fire it every tick — it'll wait its full frequency.
                if (!empty($task['last_run']) && (int)$task['frequency'] > 0) {
                    if (($now - strtotime($task['last_run'])) < (int)$task['frequency']) {
                        $output[] = $task['label'] . ': skipped (not due)';
                        continue;
                    }
                }

                // Claim the task before invocation. Stamp last_run + started_at together;
                // last_run is the dispatcher cadence anchor, started_at is the running-lock.
                $start_str = date('Y-m-d H:i:s');
                $GLOBALS['db']->update('CubeCart_cron_tasks', array(
                    'last_run'   => $start_str,
                    'started_at' => $start_str,
                ), array('id' => $task['id']));

                @set_time_limit(self::TASK_TIMEOUT);

                try {
                    if ($method === 'updateExchangeRates') {
                        $ret = $this->$method('', false);
                    } else {
                        $ret = $this->$method();
                    }
                    $result = is_string($ret) ? $ret : 'OK';
                } catch (Exception $e) {
                    $result = substr($e->getMessage(), 0, 255);
                }

                $GLOBALS['db']->update('CubeCart_cron_tasks', array(
                    'last_completed' => date('Y-m-d H:i:s'),
                    'started_at'     => 'NULL',
                    'last_result'    => $result,
                ), array('id' => $task['id']));
                $output[] = $task['label'] . ': ' . $result;
            }
        }
        echo implode("\n", $output);
    }
}