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

/**
 * Tax controller
 */
class Tax
{
    public $_tax_country;
    public $_tax_table_add = array();
    public $_tax_table_inc = array();
    public $_tax_table_applied = array();
    public $_tax_table = array();
    public $_tariff_table = array();
    public $_currency_vars = array();
    public $_total_tax_add = 0;
    public $_total_tax_inc = 0;
    private $_adjust_tax	= 1;
    public $_tax_classes;
    public static $_instance;

    ##############################################

    final protected function __construct()
    {
        $cache = Cache::getInstance();
        // Should we be showing prices?
        if (Config::getInstance()->get('config', 'catalogue_hide_prices') && !User::getInstance()->is() && !CC_IN_ADMIN && !$GLOBALS['session']->has('admin_id', 'admin_data')) {
            Session::getInstance()->set('hide_prices', true);
        } else {
            Session::getInstance()->delete('hide_prices');
        }

        // Switch Currency
        if (isset($_POST['set_currency']) && !empty($_POST['set_currency']) && ($switch = $_POST['set_currency']) || isset($_GET['set_currency']) && !empty($_GET['set_currency']) && ($switch = $_GET['set_currency'])) {
            if (preg_match('#^[A-Z]{3}$#i', $switch) && $currency = $GLOBALS['db']->select('CubeCart_currency', array('updated'), array('code' => (string)$switch, 'active' => 1))) {
                if(User::getInstance()->is()) {
                    $GLOBALS['db']->update('CubeCart_customer',array('currency' => (string)$switch), array('customer_id' => User::getInstance()->get('customer_id')));
                }
                $GLOBALS['session']->set('currency', $switch, 'client');
            }
            httpredir(currentPage(array('set_currency')));
        }
        // Autoload tax tables
        $this->loadCurrencyVars();
    }

    /**
     * Setup the instance (singleton)
     *
     * @return Tax
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    //=====[ Public ]=======================================

    /**
     * Adjust tax
     *
     * @param string $total_tax
     * @return float/false
     */
    public function adjustTax($total_tax)
    {
        if ($this->totalTax()<=0) {
            return false;
        }
        $reduction = $total_tax / $this->totalTax();
        return $this->_adjust_tax = $reduction;
    }
    
    /**
     * Display all taxes
     */
    public function displayTaxes()
    {
        // Display applied taxes
        $GLOBALS['cart']->set('order_taxes', false);
        $taxes = array();
        $taxes_included = !empty($GLOBALS['cart']->basket['has_inclusive_tax']);
        // Always defined so the set()/assign() below are safe even when every
        // applied tax rounds to zero and the loops below add nothing.
        $display_taxes = $basket_taxes = array();
        if (!empty($this->_tax_table_applied)) {
            foreach ($this->_tax_table_applied as $tax_id => $tax_name) {
                if(isset($taxes[$tax_name])) {
                    $taxes[$tax_name]['value'] += (float)($this->_tax_table_inc[$tax_id]+$this->_tax_table_add[$tax_id])*$this->_adjust_tax;
                    $taxes[$tax_name]['tax_id'] .= "|".$tax_id;
                } else {
                    $taxes[$tax_name]['value'] = $taxes[$tax_name]['value']??0 + (float)($this->_tax_table_inc[$tax_id]??0+$this->_tax_table_add[$tax_id]??0)*$this->_adjust_tax;
                    $taxes[$tax_name]['tax_id'] = $tax_id;
                }
            }
            $total_standard_taxes = 0;
            foreach ($taxes as $tax_name => $tax) {
                if ($tax_name!=='inherited') {
                    $total_standard_taxes += $tax['value'];
                }
            }
            
            if (isset($taxes['inherited']) && $taxes['inherited']['value'] > 0 && $total_standard_taxes > 0) {
                // Distribute the inherited tax proportionally across the standard taxes.
                foreach ($taxes as $tax_name => $tax) {
                    if ($tax_name!=='inherited') {
                        $inherited_split = ($tax['value']/$total_standard_taxes) * $taxes['inherited']['value'];
                        $tax_value = $tax['value']+$inherited_split;
                        if (round($tax_value, 2) == 0) continue;
                        $display_taxes[] = array('name' => $tax_name, 'value' => $this->priceFormat($tax_value), 'included' => ($taxes_included && strpos((string)$tax['tax_id'], 'i') !== 0));
                        $basket_taxes[] = array('tax_id' => $tax['tax_id'], 'amount' => $tax_value);
                    }
                }
            } else {
                // No (positive) inherited tax to distribute — display each tax as-is.
                // A zero-value 'inherited' entry is skipped by the round() guard below,
                // so a tariff (or any standard tax) sitting alongside it still shows.
                foreach ($taxes as $tax_name => $tax) {
                    if ($tax_name==='inherited') continue;
                    if (round($tax['value'], 2) == 0) continue;
                    $display_taxes[] = array('name' => $tax_name, 'value' => $this->priceFormat($tax['value']), 'included' => ($taxes_included && strpos((string)$tax['tax_id'], 'i') !== 0));
                    $basket_taxes[] = array('tax_id' => $tax['tax_id'], 'amount' => $tax['value']);
                }
            }

            $GLOBALS['cart']->set('order_taxes', $basket_taxes);
            $GLOBALS['smarty']->assign('TAXES', $display_taxes);
        }
        $GLOBALS['smarty']->assign('TOTAL_TAX', $this->priceFormat($this->_total_tax_add + $this->_total_tax_inc));
    }

    /**
     * Calculate price from exchange rate
     *
     * @param float $price
     * @param bool $from
     */
    public function exchangeRate(&$price, $from = false)
    {
        if (!empty($from) && $from != $GLOBALS['config']->get('config', 'default_currency')) {
            $currency = $GLOBALS['db']->select('CubeCart_currency', array('value'), array('code' => $from));
            if ($currency) {
                $price = $price/$currency[0]['value'];
            }
        }
        return true;
    }

    /**
     * Fetch tax exclusive and inclusive tax amount
     *
     * @return array
     */
    public function fetchTaxAmounts()
    {
        return array(
            'applied'	=> $this->_total_tax_add*$this->_adjust_tax,
            'included'	=> $this->_total_tax_inc*$this->_adjust_tax
        );
    }

    /**
     * Fetch tax details from tax ID number
     *
     * @param int $tax_id
     * @return array/false
     */
    public function fetchTaxDetails($tax_id)
    {
        // Grouped tariffs this will get the right name but the percent may later be wrong
        $grouped = false;
        if (strpos($tax_id, '|') !== false) { 
            $tax_id = explode('|', $tax_id)[0]; 
            $grouped = true;   
        }
        if(substr($tax_id, 0, 1) === 'i') { // import tariff
            $tax_id = (int)substr($tax_id, 1);
            if (($tariff = $GLOBALS['db']->select('CubeCart_tariff', false, array('id' => $tax_id))) !== false) {
                return array('name' => $this->tariffName($tariff[0]), 'display' => $this->tariffName($tariff[0]), 'tax_percent' => $grouped ? null : $tariff[0]['percent']);
            }    
        }
        if (($rate = $GLOBALS['db']->select('CubeCart_tax_rates', false, array('id' => (int)$tax_id))) !== false) {
            if (($detail = $GLOBALS['db']->select('CubeCart_tax_details', false, array('id' => $rate[0]['details_id']))) !== false) {
                return array('name' => $detail[0]['name'], 'display' => $detail[0]['display'], 'tax_percent' => $rate[0]['tax_percent'], 'display' => $detail[0]['display']);
            }
        }  
    }
    /**
     * Fetch tariff name
     *
     * @return string
     */
    public function tariffName($tariff) {
        if(!empty($tariff['display'])) {
            return $tariff['display']; // Work around to preserve duplicate display names
        }
        $tariff_on = $tariff['tariff']=='M' ? 'Manufacture': 'Dispatch';
        return sprintf($GLOBALS['language']->checkout['import_tariff'], $tariff['destination'], $tariff['source'], $tariff['percent']+0, $tariff_on);
    }

    /**
     * Remove tax from tax inclusive price
     *
     * @param float $price
     * @param int $tax_type
     * @param string $type
     * @return float
     */
    public function inclusiveTaxRemove(&$price, $tax_type, $type = 'goods')
    {
        $tax_total	= 0;
        
        if ($tax_type==999999) {
            $percent = $this->_getInheritedTax();
            $price = $price/($percent+1);
        } else {
            $country_id = $GLOBALS['config']->get('config', 'store_country');

            $query	= "SELECT `T`.`tax_name` AS `type_name`, `D`.`display`, `D`.`name`, `R`.`id`, `R`.`type_id`, `R`.`tax_percent`, `R`.`goods`, `R`.`shipping`, `R`.`county_id` FROM `".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_rates` AS `R`, `".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_details` AS `D`, `".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_class` AS `T`, `".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_geo_country` AS `C` WHERE `D`.`id` = `R`.`details_id` AND `C`.`numcode` = `R`.`country_id` AND `R`.`type_id` = `T`.`id` AND `D`.`status` = 1 AND `R`.`active` = 1 AND `R`.`country_id` = ".(int)$country_id;
            $taxes	= $GLOBALS['db']->query($query);
            $tax_table = array();
            if (is_array($taxes)) {
                foreach ($taxes as $i => $tax_group) {
                    $tax_table[$tax_group['id']] = array(
                        'goods'		=> (bool)$tax_group['goods'],
                        'shipping'	=> (bool)$tax_group['shipping'],
                        'type'		=> $tax_group['type_id'],
                        'name'		=> (!empty($tax_group['display'])) ? $tax_group['display'] : $tax_group['name'],
                        'percent'	=> $tax_group['tax_percent'],
                        'county_id'	=> $tax_group['county_id'],
                    );
                }
            }

            if (is_array($tax_table)) {
                foreach ($tax_table as $tax_id => $tax) {
                    if ($tax[$type] && $tax['type'] == $tax_type && in_array($tax['county_id'], array($GLOBALS['config']->get('config', 'store_zone'), 0))) {
                        $tax_total	+= $price - ($price/(($tax['percent']/100)+1));
                    }
                }
                $price	-= $tax_total;
            }
        }
        return $price;
    }

    /**
     * List all tax classes
     *
     * @return array/false
     */
    public function listTaxClasses()
    {
        if (!empty($this->_tax_classes)) {
            return $this->_tax_classes;
        } else {
            if (($taxes = $GLOBALS['db']->select('CubeCart_tax_class')) !== false) {
                foreach ($taxes as $tax) {
                    $this->_tax_classes[$tax['id']] = $tax['tax_name'];
                }
                return $this->_tax_classes;
            }
        }
        return false;
    }

    /**
     * Load all currency values for specific currency code
     *
     * @param string $code
     * @return bool
     */
    public function loadCurrencyVars($code = '')
    {
        if (empty($code) || strlen($code) !== 3) {
            if ($GLOBALS['session']->has('currency', 'client')) {
                $code = $GLOBALS['session']->get('currency', 'client');
                if (empty($code) || strlen($code) !== 3) {
                    $code = $GLOBALS['config']->get('config', 'default_currency');
                }
            } else {
                $code = $GLOBALS['config']->get('config', 'default_currency');
            }
        }
        if($code !== $GLOBALS['config']->get('config', 'default_currency')) {
            header("X-Robots-Tag: noindex");
        }
        if (($result = $GLOBALS['db']->select('CubeCart_currency', '*', array('code' => $code))) !== false) {
            $this->_currency_vars = $result[0];
            // Persist to session so currency survives page loads (#3477)
            if (!$GLOBALS['session']->has('currency', 'client')) {
                $GLOBALS['session']->set('currency', $code, 'client');
            }
            return true;
        }
        // Session currency not found in DB - fall back to default (#3477)
        $default = $GLOBALS['config']->get('config', 'default_currency');
        if ($code !== $default && ($result = $GLOBALS['db']->select('CubeCart_currency', '*', array('code' => $default))) !== false) {
            $this->_currency_vars = $result[0];
            $GLOBALS['session']->set('currency', $default, 'client');
            return true;
        }

        return false;
    }

    /**
     * Load tax tables from country numcode
     *
     * @param int $country_id
     */
    public function loadTaxes($country_id)
    {
        if (!empty($country_id)) {
            // Fetch new vars
            $query = "SELECT T.tax_name AS type_name, D.display, D.name, R.id, R.type_id, R.tax_percent, R.goods, R.shipping, R.county_id FROM ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_rates AS R, ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_details AS D, ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_class AS T, ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_geo_country AS C WHERE D.id = R.details_id AND C.numcode = R.country_id AND R.type_id = T.id AND D.status = 1 AND R.active = 1 AND R.country_id = ".$country_id;
            $taxes = $GLOBALS['db']->query($query);
            if(!$taxes) { // But ... do we have a Rest of World tax?
                $query = "SELECT T.tax_name AS type_name, D.display, D.name, R.id, R.type_id, R.tax_percent, R.goods, R.shipping, R.county_id FROM ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_rates AS R, ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_details AS D, ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_class AS T WHERE D.id = R.details_id AND R.type_id = T.id AND D.status = 1 AND R.active = 1 AND R.country_id = 999";
                $taxes = $GLOBALS['db']->query($query);
            }
            if (is_array($taxes)) {
                foreach ($taxes as $i => $tax_group) {
                    $name = (!empty($tax_group['display'])) ? $tax_group['display'] : $tax_group['name'];
                    $name .= ' ('.$tax_group['type_name'].' '.(float)$tax_group['tax_percent'].'%)';

                    $this->_tax_table[$tax_group['id']] = array(
                        // What is is applied to?
                        'goods'  => (int)$tax_group['goods'],
                        'shipping' => (int)$tax_group['shipping'],
                        // Details
                        'type'  => $tax_group['type_id'],
                        'name'  => $name,
                        'percent' => $tax_group['tax_percent'],
                        'county_id' => $tax_group['county_id']
                    );
                }
            }
            // Get tariffs — skip when delivery address isn't yet populated (early
            // basket reads can hit this before checkout has resolved an address).
            $delivery_iso = $GLOBALS['cart']->basket['delivery_address']['country_iso'] ?? null;
            if(!empty($delivery_iso) && $tariffs = $GLOBALS['db']->select('CubeCart_tariff', false, array('destination' => $delivery_iso))) {
                foreach($tariffs as $tariff) {
                    $this->_tariff_table[$tariff['id']] = array(
                        'goods'  => 1,
                        'shipping' => 0,
                        'tariff'  => $tariff['tariff'],
                        'source'  => $tariff['source'],
                        'destination'  => $tariff['destination'],
                        'percent' => $tariff['percent'],
                        'display' => $tariff['display']
                    );
                }
            }
        }
    }

    /**
     * Convert price
     *
     * @return float
     */
    public function priceConvertFX($price)
    {
        return ($price / $this->_currency_vars['value']);
    }


    /**
     * Correct price (unused) but kept for legacy
     *
     * @return float
     */
    public function priceCorrection($price)
    {
        return $price;
    }

    /**
     * Format price to display including currency symbol etc
     *
     * @param float $price
     * @param bool $display_null
     * @param bool $default_currency
     * @return string/false
     */
    public function priceFormat($price, $display_null = true, $default_currency = false, $override_hide = false)
    {
        if ($default_currency) {
            $this->loadCurrencyVars($GLOBALS['config']->get('config', 'default_currency'));
        }

        $price = $this->_removeSymbol($price);

        if ($display_null && is_numeric($price)) {
            if ($override_hide == false && $GLOBALS['session']->get('hide_prices')) {
                ## Hide the price, but create a string that is representative of the currency formating for the current locale
                return $this->priceFormatHidden();
            } else {
                $decimal_places = $this->_currency_vars['decimal_places'];

                if ($decimal_places == '0') {
                    $decimal_places = 0;
                } elseif (empty($decimal_places) || !is_numeric($this->_currency_vars['decimal_places'])) {
                    $decimal_places = 2;
                }
                if(isset($this->_currency_vars['adjustment']) && $this->_currency_vars['adjustment']>0) {
                    $price = ($this->_currency_vars['value'] + ($this->_currency_vars['value'] * ($this->_currency_vars['adjustment'] / 100))) * $price;
                } else {
                    $price = ($this->_currency_vars['value'] * $price);
                }
                $string = $this->_currency_vars['symbol_left'].
                        number_format(
                            $price,
                            $decimal_places,
                            empty($this->_currency_vars['symbol_decimal']) ? '.' : $this->_currency_vars['symbol_decimal'],
                            empty($this->_currency_vars['symbol_thousand']) ? ',' : $this->_currency_vars['symbol_thousand']
                        ).
                        $this->_currency_vars['symbol_right'];
                return str_replace(' ', '&nbsp;', $string);
            }
        }
        return false;
    }

    /**
     * Format price for hidden fields
     *
     * @return string
     */
    public function priceFormatHidden()
    {
        return $this->_currency_vars['symbol_left'].$GLOBALS['language']->catalogue['price_hidden'].$this->_currency_vars['symbol_right'];
    }

    /**
     * Calculate tax per line item
     *
     * @param float $price
     * @param int $tax_type
     * @param bool $tax_inclusive
     * @param int $state
     * @param string $type
     * @param bool $sum
     * @return foat/false
     */
    public function productTax(&$price, $tax_type, $tax_inclusive = false, $state = 0, $type = 'goods', $sum = true, $is_digital = '0')
    {
        foreach ($GLOBALS['hooks']->load('class.tax.producttax') as $hook) {
            include $hook;
        }
        $check_tariff = false;
        if(is_array($tax_type)) {
            $manufacture_country = $tax_type['manufacture'] ?? '';
            $tax_type = $tax_type['tax_type'] ?? 0;
            $check_tariff = true;
        } else {
            $manufacture_country = '';
        }

        if(!empty($is_digital)) {
            $check_tariff = false; // No tariffs on digital goods (field is 0=no, 1=yes-no-file, >1=download file id)
        }

        // Allows a hook to trigger a RETURN of this function by setting a variable
        if(isset($classTaxProducttaxReturn) && $classTaxProducttaxReturn === true){
            return array('tax_id' => $tax_id, 'amount' => $amount, 'amount_raw' => $amount, 'tax_inclusive' => $tax_inclusive, 'tax_name' => $tax_name, 'tax_percent' => $percent);
        }
        
        if ($price<=0) {
            return false;
        }

        if ($tax_type == 999999) {
            $tax_id = $tax_type; // see issue cubecart/v6#385
            
            $this->_tax_table_applied[$tax_id]	= 'inherited';
            
            $percent = $this->_getInheritedTax();

            if ($tax_inclusive) {
                // if tax inclusive we need to remove tax and flag it as done!
                $amount_raw = $price - ($price/($percent+1));
                $amount = sprintf('%.2F', $amount_raw);
                if ($sum) {
                    $this->_tax_table_inc[$tax_id]		+= $amount_raw;
                    $this->_total_tax_inc				+= $amount_raw;
                }
            } else {
                $amount_raw = $price * $percent;
                $amount	= sprintf('%.2F', $amount_raw);
                if ($sum) {
                    if (isset($this->_tax_table_add[$tax_id])) {
                        $this->_tax_table_add[$tax_id]	+= $amount_raw;
                    } else {
                        $this->_tax_table_add[$tax_id]	= $amount_raw;
                    }
                    $this->_total_tax_add				+= $amount_raw;
                }
            }
            return array('tax_id' => $tax_id, 'amount' => $amount, 'amount_raw' => $amount_raw, 'tax_inclusive' => $tax_inclusive, 'tax_name' => 'inherited', 'tax_percent' => $percent);
        }
        if ($check_tariff && is_array($this->_tariff_table) && !empty($this->_tariff_table)) {
            $store_country_iso = getCountryFormat($GLOBALS['config']->get('config', 'store_country'), 'numcode', 'iso');
            foreach ($this->_tariff_table as $tariff_id => $tariff) {
                if(
                    ($tariff['tariff']=='M' && $manufacture_country == $tariff['source'] && $GLOBALS['cart']->basket['delivery_address']['country_iso'] == $tariff['destination']) 
                        ||
                    ($tariff['tariff']=='D' && $store_country_iso == $tariff['source'] && $GLOBALS['cart']->basket['delivery_address']['country_iso'] == $tariff['destination'])
                ) {
                    $percent = $tariff['percent'];
                    $amount_raw = $price*($tariff['percent']/100);
                    $amount	= sprintf('%.2F', $amount_raw);
                    $tariff_id = 'i'.$tariff_id;
                    if ($sum) {
                        $this->_tax_table_applied[$tariff_id] = $this->tariffName($tariff);
                        if (isset($this->_tax_table_add[$tariff_id])) {
                            $this->_tax_table_add[$tariff_id]	+= $amount_raw;
                        } else {
                            $this->_tax_table_add[$tariff_id]	= $amount_raw;
                        }
                        $this->_total_tax_add				+= $amount_raw;
                    }
                }   
            }
        }
        if (is_array($this->_tax_table) && !empty($this->_tax_table)) {
            $tax_id = $amount = $amount_raw = $percent = 0;
            $tax_name = '';
            foreach ($this->_tax_table as $tax_id => $tax) {
                if ($tax[$type] && $tax['type'] == $tax_type && in_array($tax['county_id'], array($state, 0))) {
                    $tax_name = $tax['name'];
                    $percent = $tax['percent'];
                    switch ($tax_inclusive) {
                        case true:
                            ## Already includes tax - but how much?
                            $amount_raw = $price - ($price/(($tax['percent']/100)+1));
                            $amount = sprintf('%.2F', $amount_raw);
                            if ($sum) {
                                $this->_tax_table_applied[$tax_id]	= $tax['name'];
                                $this->_tax_table_inc[$tax_id]		+= $amount_raw;
                                $this->_total_tax_inc				+= $amount_raw;
                            }
                            break;
                        case false:
                        default:
                            ## Excludes tax - lets add it
                            $amount_raw = $price*($tax['percent']/100);
                            $amount	= sprintf('%.2F', $amount_raw);
                            if ($sum) {
                                $this->_tax_table_applied[$tax_id]	= $tax['name'];
                                if (isset($this->_tax_table_add[$tax_id])) {
                                    $this->_tax_table_add[$tax_id]	+= $amount_raw;
                                } else {
                                    $this->_tax_table_add[$tax_id]	= $amount_raw;
                                }
                                $this->_total_tax_add				+= $amount_raw;
                            }
                            break;
                    }
                }
            }
            return array('tax_id' => $tax_id, 'amount' => $amount, 'amount_raw' => $amount_raw, 'tax_inclusive' => $tax_inclusive, 'tax_name' => $tax_name, 'tax_percent' => $percent);
        }
        return false;
    }

    /**
     * Check the sale price of an item
     *
     * @param float $normal_price
     * @param float $sale_price
     * @param bool $format
     * @return string/bool
     */
    public function salePrice($normal_price = null, $sale_price = null, $format = true)
    {
        if (Config::getInstance()->has('config', 'catalogue_sale_mode')) {
            switch (Config::getInstance()->get('config', 'catalogue_sale_mode')) {
            case 1:  ## Fixed value per item
                if (!empty($sale_price) && $sale_price > 0 && ($sale_price != $normal_price)) {
                    return ($format) ? $this->priceFormat($sale_price) : $sale_price;
                }
                return false;
            case 2:  ## Percentage off all stock
                $value = $normal_price * ((100-(float)Config::getInstance()->get('config', 'catalogue_sale_percentage'))/100);
                if (is_numeric($value) && $value < $normal_price) {
                    return ($format) ? $this->priceFormat($value) : $value;
                }
                // no break
            default:
                return false;
            }
        }
        return false;
    }

    /**
     * Reset all tax parameters
     */
    public function taxReset()
    {
        // Reset tax vars
        $this->_tax_table   = array();
        $this->_tariff_table   = array();
        $this->_tax_table_add  = array();
        $this->_tax_table_inc  = array();
        $this->_tax_table_applied = array();
        $this->_total_tax_add  = 0;
        $this->_total_tax_inc  = 0;
    }

    /**
     * Add up total tax
     *
     * @return float
     */

    public function totalTax()
    {
        return round(($this->_total_tax_add + $this->_total_tax_inc), 2);
    }

    //=====[ Private ]=======================================

    /**
     * Calculate inherited tax based on ratios
     *
     * This is used to calculate tax on shipping of a combined
     * rate of different product taxes... Enter if you dare!!
     *
     * @return float
     */
    private function _getInheritedTax()
    {
        $subtotal = $tax_total = 0;
        foreach ($GLOBALS['cart']->basket['contents'] as $hash => $item) {
            if (!empty($item['total_price_each']) && $item['total_price_each']>0) {
                $subtotal += ($item['total_price_each'] * $item['quantity']);
            }
            if (!empty($item['tax_each']['amount']) && $item['tax_each']['amount']>0) {
                $tax_total += $item['tax_each']['amount'];
            }
        }

        return $tax_total / $subtotal;
    }

    /**
     * Remove symbol from price
     *
     * @return float
     */
    private function _removeSymbol($price)
    {
        //Just in case we have a currency symbol, keeps negative sign, hoping not to have scientific notation
        if ($price && is_string($price)) {
            $price = (float)filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        }
        return $price;
    }
}
