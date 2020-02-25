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

/**
 * Tax controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Tax
{
    public $_tax_country;

    public $_tax_table_add = false;
    public $_tax_table_inc = false;
    public $_tax_table_applied = false;
    public $_tax_table = false;

    public $_currency_vars = false;

    public $_total_tax_add = 0;
    public $_total_tax_inc = 0;
    private $_adjust_tax	= 1;

    public $_tax_classes;

    private $_country_id = null;
    private $_old_country_id = null;

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
        if (is_array($this->_tax_table_applied)) {
            foreach ($this->_tax_table_applied as $tax_id => $tax_name) {
                $taxes[$tax_name]['value']+= (float)sprintf(($this->_tax_table_inc[$tax_id]+$this->_tax_table_add[$tax_id])*$this->_adjust_tax);
                $taxes[$tax_name]['tax_id']= $tax_id;
            }

            $total_standard_taxes = 0;
            foreach ($taxes as $tax_name => $tax) {
                if ($tax_name!=='inherited') {
                    $total_standard_taxes += $tax['value'];
                }
            }
            
            if (isset($taxes['inherited'])) {
                if ($taxes['inherited']['value']>0) {
                    foreach ($taxes as $tax_name => $tax) {
                        if ($tax_name!=='inherited') {
                            $inherited_split = ($tax['value']/$total_standard_taxes) * $taxes['inherited']['value'];
                            $tax_value = $tax['value']+$inherited_split;
                            $display_taxes[] = array('name' => $tax_name, 'value' => $this->priceFormat($tax_value));
                            $basket_taxes[] = array('tax_id' => $tax['tax_id'], 'amount' => $tax_value);
                        }
                    }
                }
            } else {
                foreach ($taxes as $tax_name => $tax) {
                    $display_taxes[] = array('name' => $tax_name, 'value' => $this->priceFormat($tax['value']));
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
        if (($rate = $GLOBALS['db']->select('CubeCart_tax_rates', false, array('id' => (int)$tax_id))) !== false) {
            if (($detail = $GLOBALS['db']->select('CubeCart_tax_details', false, array('id' => $rate[0]['details_id']))) !== false) {
                return array_merge($rate[0], $detail[0]);
            }
        }

        return false;
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

            $query	= "SELECT SQL_CACHE T.tax_name AS type_name, D.display, D.name, R.id, R.type_id, R.tax_percent, R.goods, R.shipping, R.county_id FROM ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_rates AS R, ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_details AS D, ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_class AS T, ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_geo_country AS C WHERE D.id = R.details_id AND C.numcode = R.country_id AND R.type_id = T.id AND D.status = 1 AND R.active = 1 AND R.country_id = ".(int)$country_id;
            $taxes	= $GLOBALS['db']->query($query);
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
    public function loadCurrencyVars($code = false)
    {
        if (!$code) {
            if ($GLOBALS['session']->has('currency', 'client')) {
                $code = $GLOBALS['session']->get('currency', 'client');
            } else {
                $code = $GLOBALS['config']->get('config', 'default_currency');
            }
        }
        if($code !== $GLOBALS['config']->get('config', 'default_currency')) {
            header("X-Robots-Tag: noindex");
        }
        if (($result = $GLOBALS['db']->select('CubeCart_currency', '*', array('code' => $code))) !== false) {
            $this->_currency_vars = $result[0];
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
            $this->_country_id = $country_id;

            // Fetch new vars
            $query = "SELECT T.tax_name AS type_name, D.display, D.name, R.id, R.type_id, R.tax_percent, R.goods, R.shipping, R.county_id FROM ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_rates AS R, ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_details AS D, ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_class AS T, ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_geo_country AS C WHERE D.id = R.details_id AND C.numcode = R.country_id AND R.type_id = T.id AND D.status = 1 AND R.active = 1 AND R.country_id = ".$country_id;
            $taxes = $GLOBALS['db']->query($query);
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
                        'county_id' => $tax_group['county_id'],
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
            
                $price = ($this->_currency_vars['value']*$price);
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
    public function productTax(&$price, $tax_type, $tax_inclusive = false, $state = 0, $type = 'goods', $sum = true)
    {
        foreach ($GLOBALS['hooks']->load('class.tax.producttax') as $hook) {
            include $hook;
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
                $amount = sprintf('%.2F', $price - ($price/($percent+1)));
                if ($sum) {
                    $this->_tax_table_inc[$tax_id]		+= $amount;
                    $this->_total_tax_inc				+= $amount;
                }
            } else {
                $amount	= sprintf('%.2F', $price * $percent);
                if ($sum) {
                    if (isset($this->_tax_table_add[$tax_id])) {
                        $this->_tax_table_add[$tax_id]	+= $amount;
                    } else {
                        $this->_tax_table_add[$tax_id]	= $amount;
                    }
                    $this->_total_tax_add				+= $amount;
                }
            }
            return array('tax_id' => $tax_id, 'amount' => $amount, 'tax_inclusive' => $tax_inclusive, 'tax_name' => 'inherited', 'tax_percent' => $percent);
        }
        if (is_array($this->_tax_table) && !empty($this->_tax_table)) {
            foreach ($this->_tax_table as $tax_id => $tax) {
                if ($tax[$type] && $tax['type'] == $tax_type && in_array($tax['county_id'], array($state, 0))) {
                    $tax_name = $tax['name'];
                    $percent = $tax['percent'];
                    switch ($tax_inclusive) {
                        case true:
                            ## Already includes tax - but how much?
                            $amount = sprintf('%.2F', $price - ($price/(($tax['percent']/100)+1)));
                            if ($sum) {
                                $this->_tax_table_applied[$tax_id]	= $tax['name'];
                                $this->_tax_table_inc[$tax_id]		+= $amount;
                                $this->_total_tax_inc				+= $amount;
                            }
                            break;
                        case false:
                        default:
                            ## Excludes tax - lets add it
                            $amount	= sprintf('%.2F', $price*($tax['percent']/100));
                            if ($sum) {
                                $this->_tax_table_applied[$tax_id]	= $tax['name'];
                                if (isset($this->_tax_table_add[$tax_id])) {
                                    $this->_tax_table_add[$tax_id]	+= $amount;
                                } else {
                                    $this->_tax_table_add[$tax_id]	= $amount;
                                }
                                $this->_total_tax_add				+= $amount;
                            }
                            break;
                    }
                }
            }
            return array('tax_id' => $tax_id, 'amount' => $amount, 'tax_inclusive' => $tax_inclusive, 'tax_name' => $tax_name, 'tax_percent' => $percent);
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
                $value = $normal_price * ((100-Config::getInstance()->get('config', 'catalogue_sale_percentage'))/100);
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
        $this->_tax_table   = false;
        $this->_tax_table_add  = false;
        $this->_tax_table_inc  = false;
        $this->_tax_table_applied = false;
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
            if ($item['total_price_each']>0) {
                $subtotal += ($item['total_price_each'] * $item['quantity']);
            }
            if ($item['tax_each']['amount']>0) {
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
            $price = (double)filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        }
        return $price;
    }
}
