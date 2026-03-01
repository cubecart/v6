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
                    trigger_error($e->getMessage());
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
    public function runSnippets() {
        foreach ($GLOBALS['hooks']->load('cron') as $hook) {
            include $hook;
        }
    }

    /**
     * Ensure default cron tasks exist in the database
     */
    public static function ensureDefaults() {
        $defaults = array(
            array('method' => 'updateExchangeRates', 'label' => 'Update Exchange Rates', 'enabled' => 1, 'frequency' => 86400),
            array('method' => 'clearCache', 'label' => 'Clear Cache', 'enabled' => 0, 'frequency' => 21600),
            array('method' => 'runSnippets', 'label' => 'Run Code Snippets / Hooks*', 'enabled' => 0, 'frequency' => 3600),
        );
        foreach ($defaults as $task) {
            $exists = $GLOBALS['db']->select('CubeCart_cron_tasks', 'id', array('method' => $task['method']), false, false, false, false);
            if (!$exists) {
                $GLOBALS['db']->insert('CubeCart_cron_tasks', $task);
            }
        }
    }

    /**
     * Unified cron entry point - runs all enabled tasks that are due
     */
    public function run() {
        $tasks = $GLOBALS['db']->select('CubeCart_cron_tasks', false, array('enabled' => 1), false, false, false, false);
        $output = array();
        if ($tasks) {
            foreach ($tasks as $task) {
                $method = $task['method'];
                if (!method_exists($this, $method)) {
                    continue;
                }
                $due = false;
                if (empty($task['last_run'])) {
                    $due = true;
                } else {
                    $elapsed = time() - strtotime($task['last_run']);
                    if ($elapsed >= (int)$task['frequency']) {
                        $due = true;
                    }
                }
                if ($due) {
                    try {
                        if ($method === 'updateExchangeRates') {
                            $this->$method('', false);
                        } else {
                            $this->$method();
                        }
                        $result = 'OK';
                    } catch (Exception $e) {
                        $result = substr($e->getMessage(), 0, 255);
                    }
                    $GLOBALS['db']->update('CubeCart_cron_tasks', array('last_run' => date('Y-m-d H:i:s'), 'last_result' => $result), array('id' => $task['id']));
                    $output[] = $task['label'] . ': ' . $result;
                } else {
                    $output[] = $task['label'] . ': skipped (not due)';
                }
            }
        }
        echo implode("\n", $output);
    }
}