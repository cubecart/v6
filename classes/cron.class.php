<?php
class Cron
{
    public function updateExchangeRates() {
        ## European Central Bank
        if (($request = new Request('www.ecb.europa.eu', '/stats/eurofxref/eurofxref-daily.xml')) !== false) {
            $request->setMethod('get');
            $request->setSSL();
            $rates_xml = $request->send();

            // If this fails fall back to original file_get_contents, if that failes we have tried all we can
            if (empty($rates_xml)) {
                $rates_xml = file_get_contents('https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
            }

            if (!empty($rates_xml)) {
                $xml  = new SimpleXMLElement($rates_xml);
                foreach ($xml->Cube->Cube->Cube as $currency) {
                    $rate = $currency->attributes();
                    $fx[(string)$rate['currency']] = (float)$rate['rate'];
                }
                $fx['EUR'] = 1;
                $updated = strtotime((string)$xml->Cube->Cube->attributes()->time);
                # Get the divisor
                $base  = (1/$fx[strtoupper($GLOBALS['config']->get('config', 'default_currency'))]);
                foreach ($fx as $code => $rate) {
                    $value = ($base/(1/$rate));
                    $GLOBALS['db']->update('CubeCart_currency', array('value' => $value, 'updated' => $updated), array('code' => $code), true);
                }
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}