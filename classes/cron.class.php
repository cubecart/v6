<?php
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
                    $base  = (1/$fx[strtoupper($currency)]);
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
                    trigger_error('Error parsing ECB Exchange Rates.', E_USER_WARNING);
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
}