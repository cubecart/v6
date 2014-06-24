<?php
/*-----------------------------------------------------------------------------
 * All In One Shipping with Postcodes CC5
 *-----------------------------------------------------------------------------
 * Author:   Estelle Winterflood
 * Email:    cubecart@expandingbrain.com
 * Store:    http://cubecart.expandingbrain.com
 * 
 * Date:     July 22, 2012
 * Updated:  n/a
 * Compatible with CubeCart Version:  5.x.x
 *-----------------------------------------------------------------------------
 */

class All_In_One_Shipping {
	private $_basket;
	private $_settings;
	private $_config;
	private $_package;

	private $_weight;
	private $_value;
	private $_item_count;

	private $_all_zones;
	private $_all_rates;

	private $_debug_lines;


	public function __construct($basket = false) {
		$this->_db			=& $GLOBALS['db'];
		$this->_basket		= $basket;
		$this->_settings	= $GLOBALS['config']->get(__CLASS__);
		$this->_config		= $GLOBALS['config'];

		$this->_weight = (float)$this->_basket['weight'];
		// XXX May want to add some weight for packaging, i.e.
		// $this->_weight += ($this->_settings['packagingWeight'] > 0) ? $this->_settings['packagingWeight'] : 0;

		$this->_value = (float)$this->_basket['subtotal'];
		// XXX May want to remove coupon discount, i.e.
		// $this->_value -= $this->_basket['discount'];

		$this->_item_count = 0;
		foreach ($this->_basket['contents'] as $item) {
			// XXX May want to exclude digital products from the item count, i.e.
			// if ($item['digital']) continue;
			$this->_item_count += $item['quantity'];
		}

		$this->_all_zones = $this->_db->select('CubeCart_shipping_zones', false, false, 'sort_order, id');
		$this->_all_rates = $this->_db->select('CubeCart_shipping_rates', false, false, 'zone_id, id');
		$this->_debug_lines = array();
	}


	private function debug($debug, $debug_level = 1) {
		if ($this->_settings['debug'] >= $debug_level) {
			$this->_debug_lines[] = $debug;
		}
	}
	private function debugH($debug, $debug_level = 1) {
		if ($this->_settings['debug'] >= $debug_level) {
			$this->_debug_lines[] = '';
			$this->_debug_lines[] = $debug;
			$this->_debug_lines[] = '';
		}
	}
	private function echo_debug() {
		if ($this->_settings['debug']) {
			echo '<div style=\'border: 1px solid red; color: red; background: white; margin: 1em; padding: 0.5em;\'>';
			echo 'THIS INFORMATION IS BEING PRINTED BECAUSE : The "All In One Shipping Module" has debug mode enabled.<br/>';
			echo '<br/>TO HIDE THIS INFORMATION : Log into Admin and go to Shipping Methods &gt; All In One Shipping then disable the debug setting.<br/>';
			echo implode('<br/>', $this->_debug_lines);
			echo '</pre></div>';
		}
	}

	private function matching_zone_ids() {
		$delivery	= (isset($this->_basket['delivery_address'])) ? $this->_basket['delivery_address'] : $this->_basket['billing_address'];
		// Crazy bug work around
		if($delivery['state_abbrev']==$delivery['state'] && $delivery['state_abbrev']=$delivery['state_id']) {
			$delivery['state_abbrev'] = getStateFormat($delivery['state'], 'name', 'abbrev');
		} elseif(strlen($delivery['state_abbrev'])>4) {
			$delivery['state_abbrev'] = getStateFormat($delivery['state_id'], 'id', 'abbrev');
		}
		
		if (! $this->_all_zones) {
			$this->debugH('SHIPPING ZONES');
			$this->debug('No shipping zones setup. Same shipping rates will be used regardless of delivery destination.');
			return array(0);
		}
		$this->debugH('ADDRESS SUMMARY (*** Note: May be shop location if customer has not provided an address)');
		$this->debug(sprintf('Country: %s', $delivery['country_iso']));
		$this->debug(sprintf('State: %s', $delivery['state_abbrev']));
		$this->debug(sprintf('Postcode: %s', $delivery['postcode']));

		$country_iso = $delivery['country_iso'];
		$state_id = is_numeric($delivery['state_id']) ? $delivery['state_id'] : 0;
		$state_abbrev = sprintf('%s',$delivery['state_abbrev']);
		// postcodes
		$del_postcode = $delivery['postcode'];
		$del_postcode = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $del_postcode));

		if ($country_iso == 'GB' && strlen($del_postcode) >= 5) {
			$del_postcode_district = substr($del_postcode,0,-3);
			$del_postcode_area = '';
			if (preg_match('/^([a-zA-Z]{1,2})/', $del_postcode_district, $matches)) {
				$del_postcode_area = $matches[1];
			}
			$this->debug('(UK only) Delivery postcode split into area/district/rest: '.$del_postcode_area.' '.str_replace($del_postcode_area, '', $del_postcode_district).' '.substr($del_postcode,-3,3));
		} elseif ($country_iso == 'US') {
			// Force US zip codes to 5 digit
			$del_postcode = substr($del_postcode,0 , 5);
		}
		
		$this->debugH('SHIPPING ZONES');
		$this->debug('All zones:', $debug_level=2);
		$this->debug('<pre>'.print_r($this->_all_zones, true).'</pre>', $debug_level=2);

		$done = false;

		for ($i=0; !$done && $i<count($this->_all_zones); $i++) {

			$debug_zone = sprintf('%s [Zone ID %s]', $this->_all_zones[$i]['zone_name'], $this->_all_zones[$i]['id']);

			if (preg_match(sprintf('/\b%s\b/',$country_iso), $this->_all_zones[$i]['countries'])) {
				// postcodes
				if (!empty($this->_all_zones[$i]['postcodes'])) {
					if (empty($del_postcode)) {
						$this->debug($debug_zone.' --- Country matched but this is a postcode zone and no postcode was provided - incomplete match.');
						continue;
					}
					$res = false;
					$pc_debug = array();
					$postcodes = str_replace(array("\r\n", "\n"), '|', $this->_all_zones[$i]['postcodes']);
					$postcodes = preg_replace('/[^a-zA-Z0-9\*\-\|%_]/', '', $postcodes);
					if (is_numeric($del_postcode) && preg_match_all('/([0-9]+)[\-]([0-9]+)/', $postcodes, $matches, PREG_SET_ORDER)) {
						for ($m=0; !$res && $m<count($matches); $m++) {
							if ($matches[$m][1] <= $del_postcode && $del_postcode <= $matches[$m][2]) {
								$res = true;
							}
							$pc_debug[] = '- Postcode range search: '.$matches[$m][1].' - '.$matches[$m][2].' &nbsp; '.($res?'(MATCHED!)':'(Didn\'t match)');
						}
					}
					if (!$res) {
						$postcodes = preg_replace('/\|?([0-9]+)[\-]([0-9]+)/', '', $postcodes);
						$postcodes = preg_replace('/^(\|)|(\|)$|[-]/', '', $postcodes);
						$postcodes = strtoupper($postcodes);
						if (!empty($postcodes)) {
							$search = '/^'.str_replace(array('|','*','%','_'),array('$|^','.*','.*','.'),$postcodes).'$/';
							$res = preg_match($search, $del_postcode);
							if (!empty($del_postcode_district) && !empty($del_postcode_area)) {
								$res = $res || preg_match($search, $del_postcode_district);
								$res = $res || preg_match($search, $del_postcode_area);
							}
							$pc_debug[] = '- Postcode string search: '.$search.' &nbsp; '.($res?'(MATCHED!)':'(Didn\'t match)');
						}
					}
					if ($res == false) {
						$this->debug($debug_zone.' --- Country matched but postcode search didn\'t match...');
						for ($j=0; $pc_debug && $j<count($pc_debug); $j++) {
							$this->debug($pc_debug[$j]);
						}
						continue;
					}
					$this->debug($debug_zone.' --- Country matched and postcode matched...');
					for ($j=0; $pc_debug && $j<count($pc_debug); $j++) {
						$this->debug($pc_debug[$j]);
					}

				} else if (!empty($this->_all_zones[$i]['states'])) {
					if (empty($state_abbrev)) {
						$this->debug($debug_zone.' --- Country matched but this is a state/province zone and no state/province was provided - incomplete match.');
						continue;
					}

					if (!preg_match('/\b'.$state_abbrev.'\b/', $this->_all_zones[$i]['states'])) {
						$this->debug($debug_zone.sprintf(' --- Country matched but state/province didn\'t match [%s]', $this->_all_zones[$i]['states']));
						continue;
					}
					$this->debug($debug_zone.sprintf(' --- Country matched and state/province matched! [%s]', $this->_all_zones[$i]['states']));
				} else {
					$this->debug($debug_zone.sprintf(' --- Country matched! [%s]', $this->_all_zones[$i]['countries']));
				}

				// If made it this far, we have found a matching zone!
				if (!isset($zone_ids)) $zone_ids = array();
				$zone_ids[] = $this->_all_zones[$i]['id'];

				$this->debug(sprintf('<strong>&gt;&gt;&gt; Shipping zone [ID %s] matches the delivery address! Use this zone for shipping calculations.</strong>', $this->_all_zones[$i]['id']));

				if ($this->_settings['multiple_zones'] == 'first') {
					$this->debug('Stopping at first matching zone (instead of searching for all matching zones - see AIOS module settings)');
					$done = true;
				} else {
					$this->debug('Searching for all matching shipping zones (instead of stopping at first matching zone - see AIOS module settings)');
				}
			}
			else $this->debug($debug_zone.sprintf(' --- Country didn\'t match [%s]', $this->_all_zones[$i]['countries']));
		}

		if (!isset($zone_ids)) {
			$this->debug('<strong>&gt;&gt;&gt; Use Rest of World zone for shipping calculations.</strong>');
			$zone_ids = array(0);
		}

		return $zone_ids;
	}


	private function matching_rates($zone_ids) {

		if (!is_array($zone_ids)) {
			return array();
		}

		$this->debugH('SHIPPING RATES');
		$this->debug('All rates:', $debug_level=2);
		$this->debug('<pre>'.print_r($this->_all_rates, true).'</pre>', $debug_level=2);
		$this->debug(sprintf('Looking at the shipping rates for zone(s) [ID %s]', implode(', ',$zone_ids)));

		// resulting matching rates (if any)
		$rates = array();

		$count = count($this->_all_rates);
		for ($i=0; !empty($this->_all_rates) && $i<$count; $i++) {

			$r = $this->_all_rates[$i];

			if (in_array($r['zone_id'], $zone_ids)) {

				$ok = true;

				if ($this->_settings['range_weight']) {
					if ($this->_weight <= $r['min_weight'] && $r['min_weight'] > 0) {
						$ok = false;
					} else if ($this->_weight > $r['max_weight'] && $r['max_weight'] > 0) {
						$ok = false;
					}
					if (!$ok) $this->debug(sprintf('Rate [ID: %d] [%s] --- Weight range [%s &lt; weight &lt;= %s] doesn\'t match basket weight [%s]', $r['id'], $r['method_name'], $r['min_weight'], $r['max_weight'], $this->_weight));
				}
				if ($ok && $this->_settings['range_subtotal']) {
					if ($this->_value <= $r['min_value'] && $r['min_value'] > 0) {
						$ok = false;
					} else if ($this->_value > $r['max_value'] && $r['max_value'] > 0) {
						$ok = false;
					}
					if (!$ok) $this->debug(sprintf('Rate [ID: %d] [%s] --- Subtotal range [%s &lt; subtotal &lt;= %s] doesn\'t match basket subtotal [%s]', $r['id'], $r['method_name'], $r['min_value'], $r['max_value'], $this->_value));
				}
				if ($ok && $this->_settings['range_items']) {
					if ($this->_item_count < $r['min_items'] && $r['min_items'] > 0) {
						$ok = false;
					} else if ($this->_item_count > $r['max_items'] && $r['max_items'] > 0) {
						$ok = false;
					}
					if (!$ok) $this->debug(sprintf('Rate [ID: %d] [%s] --- Total Quantity range [%s &lt; total quantity &lt; %s] doesn\'t match basket total quantity [%s]', $r['id'], $r['method_name'], $r['min_items'], $r['max_items'], $this->_item_count));
				}

				if ($ok) {
					$this->debug(sprintf('Rate [ID: %d] [%s] --- <strong> Shipping rate is valid for this basket!</strong>', $r['id'], $r['method_name']));
					$rates[] = $r;
				}
			}
		}

		return $rates;
	}


	public function calculate() {

		$this->debugH('BASKET TOTALS');
		$this->debug(sprintf('Basket weight: %.3f', $this->_weight));
		$this->debug(sprintf('Basket value: %.2f', $this->_value));
		$this->debug(sprintf('Basket item count: %d', $this->_item_count));

		$zone_ids = $this->matching_zone_ids();

		$rates = $this->matching_rates($zone_ids);

		// CALCULATE PRICE FOR EACH SHIPPING METHOD

		$this->_package = array();

		for ($i=0; !empty($rates) && $i<count($rates); $i++) {
			$price = 0.0;
			if ($this->_settings['use_flat']) $price += $rates[$i]['flat_rate'];
			if ($this->_settings['use_weight']) $price += $rates[$i]['weight_rate'] * $this->_weight;
			if ($this->_settings['use_item']) $price += $rates[$i]['item_rate'] * $this->_item_count;
			if ($this->_settings['use_percent']) $price += $rates[$i]['percent_rate']/100 * $this->_value;

			$this->_package[] = array(
				'name'      => $rates[$i]['method_name'],
				'value'     => sprintf('%.2f', $price),
				'tax_id'    => (int)$this->_settings['tax'],
				'tax_inclusive'	=> (int)$this->_settings['tax_included'],
				## Delivery times not applicable to this module
				'shipping'  => '',
				'delivery'  => '',
				'next_day'  => ''
			);
		}

		if (!empty($this->_package)) {
			$this->debugH('FINAL SHIPPING OPTIONS FOR THIS ADDRESS AND BASKET');
			foreach ($this->_package as $p)
				$this->debug(sprintf('%s: %s', $p['name'], $p['value']));
		} else {
			$this->debugH('<strong>All In One Shipping module has no shipping options for this address and basket!</strong>');
		}

		$this->echo_debug();

		return !empty($this->_package) ? $this->_package : false;
	}


	public function tracking($tracking_id = false) {
		return false;
	}

}

?>