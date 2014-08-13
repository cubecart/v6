<?php
/**
 * CubeCart v5
 * ========================================
 * CubeCart is a registered trade mark of Devellion Limited
 * Copyright Devellion Limited 2010. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  http://www.cubecart.com/v5-software-license
 * ========================================
 * CubeCart is NOT Open Source.
 * Unauthorized reproduction is not allowed.
 */

/**
 * Tax controller
 *
 * @author Technocrat
 * @version 1.1.0
 * @since 5.0.0
 */
class Tax {
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

	#####################################################

	final protected function __construct() {
		$cache = Cache::getInstance();
		// Should we be showing prices?
		if (Config::getInstance()->get('config', 'catalogue_hide_prices') && !User::getInstance()->is() && !CC_IN_ADMIN) {
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
	public static function getInstance() {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	//=====[ Public ]====================================================================================================
	public function displayTaxes() {
		// Display applied taxes
		$GLOBALS['cart']->set('order_taxes', false);
		if (is_array($this->_tax_table_applied)) {
			
			foreach ($this->_tax_table_applied as $tax_id => $tax_name) {
				$taxes[$tax_name]['value']+= sprintf("%0.2f",(($this->_tax_table_inc[$tax_id]+$this->_tax_table_add[$tax_id])*$this->_adjust_tax));
				$taxes[$tax_name]['tax_id']= $tax_id;
			}

			$total_standard_taxes = 0;
			foreach($taxes as $tax_name => $tax) {
				if($tax_name!=='inherited') {
					$total_standard_taxes += $tax['value'];
				}
			}
			
			if(isset($taxes['inherited'])) {
				if($taxes['inherited']['value']>0) {
					foreach($taxes as $tax_name => $tax) {
						if($tax_name!=='inherited') {
							$inherited_split = ($tax['value']/$total_standard_taxes) * $taxes['inherited']['value'];
							$tax_value = $tax['value']+$inherited_split;
							$display_taxes[] = array('name' => $tax_name, 'value' => $this->priceFormat($tax_value));
							$basket_taxes[] = array('tax_id' => $tax['tax_id'], 'amount' => $tax_value);
						}
					}
				}
				unset($tax_data['inherited']);
			} else {
				foreach($taxes as $tax_name => $tax) {
					$display_taxes[] = array('name' => $tax_name, 'value' => $this->priceFormat($tax['value']));
					$basket_taxes[] = array('tax_id' => $tax['tax_id'], 'amount' => $tax['value']);
				}
			}

			$GLOBALS['cart']->set('order_taxes', $basket_taxes);
			$GLOBALS['smarty']->assign('TAXES', $display_taxes);
		}
		$GLOBALS['smarty']->assign('TOTAL_TAX', $this->priceFormat($this->_total_tax_add + $this->_total_tax_inc));
	}

	public function adjustTax($total_tax) {
		$reduction = $total_tax/$this->totalTax();
		$this->_adjust_tax = $reduction;
	}

	public function exchangeRate(&$price, $from = false) {
		if (!empty($from) && $from != $GLOBALS['config']->get('config', 'default_currency')) {
			$currency = $GLOBALS['db']->select('CubeCart_currency', array('value'), array('code' => $from));
			if ($currency) {
				$price = $price/$currency[0]['value'];
			}
		}
		return true;
	}

	public function fetchTaxAmounts() {
		return array(
			'applied'	=> $this->_total_tax_add*$this->_adjust_tax,
			'included'	=> $this->_total_tax_inc*$this->_adjust_tax
		);
	}

	public function fetchTaxDetails($tax_id) {
		if (($rate = $GLOBALS['db']->select('CubeCart_tax_rates', false, array('id' => (int)$tax_id))) !== false) {
			if (($detail = $GLOBALS['db']->select('CubeCart_tax_details', false, array('id' => $rate[0]['details_id']))) !== false) {
				return array_merge($rate[0], $detail[0]);
			}
		}

		return false;
	}

	// Remove inclusive tax
	public function inclusiveTaxRemove(&$price, $tax_type, $type = 'goods') {
		$tax_total = 0;
		$country_id = $GLOBALS['config']->get('config', 'store_country');

		$query = "SELECT SQL_CACHE T.tax_name AS type_name, D.display, D.name, R.id, R.type_id, R.tax_percent, R.goods, R.shipping, R.county_id FROM ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_rates AS R, ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_details AS D, ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_class AS T, ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_geo_country AS C WHERE D.id = R.details_id AND C.numcode = R.country_id AND R.type_id = T.id AND D.status = 1 AND R.active = 1 AND R.country_id = ".(int)$country_id;
		$taxes = $GLOBALS['db']->query($query);
		if (is_array($taxes)) {
			foreach ($taxes as $i => $tax_group) {

				$tax_table[$tax_group['id']] = array(
					'goods'  => (bool)$tax_group['goods'],
					'shipping' => (bool)$tax_group['shipping'],
					'type'  => $tax_group['type_id'],
					'name'		=> (!empty($tax_group['display'])) ? $tax_group['display'] : $tax_group['name'],
					'percent' => $tax_group['tax_percent'],
					'county_id' => $tax_group['county_id'],
				);
			}
		}

		if (is_array($tax_table)) {
			foreach ($tax_table as $tax_id => $tax) {
				if ($tax[$type] && $tax['type'] == $tax_type && in_array($tax['county_id'], array($GLOBALS['config']->get('config', 'store_zone'), 0))) {
					$tax_total += sprintf('%.2f', $price - ($price/(($tax['percent']/100)+1)));
				}
			}
			$price -= $tax_total;
		}
		return $price;
	}

	public function listTaxClasses() {
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

	public function loadCurrencyVars($code = false) {
		if (!$code) {
			if ($GLOBALS['session']->has('currency', 'client')) {
				$code = $GLOBALS['session']->get('currency', 'client');
			} else {
				$code = $GLOBALS['config']->get('config', 'default_currency');
			}
		}
		if (($result = $GLOBALS['db']->select('CubeCart_currency', '*', array('code' => $code))) !== false) {
			$this->_currency_vars = $result[0];
			return true;
		}

		return false;
	}

	public function loadTaxes($country_id) {

		if (!empty($country_id)) {
			$this->_country_id = $country_id;

			// Fetch new vars
			$query = "SELECT SQL_CACHE T.tax_name AS type_name, D.display, D.name, R.id, R.type_id, R.tax_percent, R.goods, R.shipping, R.county_id FROM ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_rates AS R, ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_details AS D, ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_tax_class AS T, ".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_geo_country AS C WHERE D.id = R.details_id AND C.numcode = R.country_id AND R.type_id = T.id AND D.status = 1 AND R.active = 1 AND R.country_id = ".$country_id;
			$taxes = $GLOBALS['db']->query($query);
			if (is_array($taxes)) {
				foreach ($taxes as $i => $tax_group) {
					
					$name = (!empty($tax_group['display'])) ? $tax_group['display'] : $tax_group['name'];
					$name .= ' ('.$tax_group['type_name'].' '.sprintf('%.2f',$tax_group['tax_percent']).'%)';

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

	public function priceConvertFX($price) {
		return ($price / $this->_currency_vars['value']);
	}


	## Price Correction - Prevent negative-value items
	public function priceCorrection($price) {
		return $price;
	}

	public function priceFormat($price, $display_null = true, $default_currency = false) {

		if ($default_currency) {
			$this->loadCurrencyVars($GLOBALS['config']->get('config', 'default_currency'));
		}

		$price = $this->_removeSymbol($price);

		if ($display_null && is_numeric($price)) {
			if ($GLOBALS['session']->get('hide_prices')) {
				## Hide the price, but create a string that is representative of the currency formating for the current locale
				return $this->priceFormatHidden();
			} else {
				$price = ($this->_currency_vars['value']*$price);				
				$symbol_d = ($this->_currency_vars['symbol_decimal']) ? ',' : '.';
				$symbol_t = (!$this->_currency_vars['symbol_decimal']) ? ',' : '.';
				return $this->_currency_vars['symbol_left'].number_format($this->priceCorrection($price), $this->_currency_vars['decimal_places'], $symbol_d, $symbol_t).$this->_currency_vars['symbol_right'];
			}
		}
		return false;
	}

	public function priceFormatHidden() {
		return $this->_currency_vars['symbol_left'].$GLOBALS['language']->catalogue['price_hidden'].$this->_currency_vars['symbol_right'];
	}

	## Calculate tax per item
	public function productTax(&$price, $tax_type, $tax_inclusive = false, $state = 0, $type = 'goods', $sum = true) {
		if($price<=0) return false; 

		if($tax_type == 999999 && $sum) {
			
			$this->_tax_table_applied[$tax_id]	= 'inherited';
			
			$last_inherited = $GLOBALS['session']->get('last_inherited');
		
			$total_tax = ($last_inherited>0) ? $GLOBALS['cart']->basket['total_tax'] - $last_inherited : $GLOBALS['cart']->basket['total_tax'];

			$percent = $total_tax / $GLOBALS['cart']->basket['subtotal'];

			if($tax_inclusive) {
				$amount = $price - sprintf('%.2f', $price/($percent+1), 2);
				$this->_tax_table_applied[$tax_id]	= $tax['name'];
				$this->_tax_table_inc[$tax_id]		+= $amount;
				$this->_total_tax_inc				+= $amount;
			} else {
				$amount	= $price * sprintf('%.2f',$percent);
				if (isset($this->_tax_table_add[$tax_id])) {
					$this->_tax_table_add[$tax_id]	+= $amount;
				} else {
					$this->_tax_table_add[$tax_id]	= $amount;
				}
				$this->_total_tax_add				+= $amount;	
			}
			$GLOBALS['session']->set('last_inherited', $amount);
		}

		if (is_array($this->_tax_table) && !empty($this->_tax_table)) {
			foreach ($this->_tax_table as $tax_id => $tax) {
				if ($tax[$type] && $tax['type'] == $tax_type && in_array($tax['county_id'], array($state, 0))) {
					switch ($tax_inclusive) {
					case true:
						## Already includes tax - but how much?
						$amount = $price - sprintf('%.2f', $price/(($tax['percent']/100)+1), 2);
						if($sum) {
							$this->_tax_table_applied[$tax_id] = $tax['name'];
							$this->_tax_table_inc[$tax_id]  += $amount;
							$this->_total_tax_inc    += $amount;
						}
						break;
					case false:
					default:
						## Excludes tax - lets add it
						$amount = $price*($tax['percent']/100);
						if($sum) {
							$this->_tax_table_applied[$tax_id] = $tax['name'];
							if (isset($this->_tax_table_add[$tax_id])) {
								$this->_tax_table_add[$tax_id] += $amount;
							} else {
								$this->_tax_table_add[$tax_id] = $amount;
							}
							$this->_total_tax_add    += $amount;
						}
						break;
					}
				}
			}
			return array('amount' => sprintf('%.2f', $amount), 'tax_inclusive' => $tax_inclusive, 'tax_name' => $tax['name']);
		}
		return false;
	}

	## Check the sale price of an item
	public function salePrice($normal_price = null, $sale_price = null, $format = true) {
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
			default:
				return false;
			}
		}
		return false;
	}

	public function totalTax() {
		return round(($this->_total_tax_add + $this->_total_tax_inc), 2);
	}

	public function taxReset() {
		// Reset tax vars
		$this->_tax_table   = false;
		$this->_tax_table_add  = false;
		$this->_tax_table_inc  = false;
		$this->_tax_table_applied = false;
		$this->_total_tax_add  = 0;
		$this->_total_tax_inc  = 0;
	}

	private function _removeSymbol($price) {
		//Just in case we have a currency symbol
		if ($price && is_string($price)) {
			if (!ctype_digit($price{0})) {
				$price = substr($price, 1);
			}
			$price = (double)$price;
		}
		return $price;
	}
}