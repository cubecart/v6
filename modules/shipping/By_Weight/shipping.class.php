<?php
class By_Weight {
	private $_basket;
	private $_settings;
	private $_shipZone;
	private $_package;
	private $_countryISO;
	private $_weight;

	public function __construct($basket = false) {
		$this->_db			=& $GLOBALS['db'];
		$this->_basket		= $basket;
		$this->_settings	= $GLOBALS['config']->get(__CLASS__);
	}

	private function totalWeight(){
		$this->_weight = $this->_basket['weight'];
		$this->_weight += ($this->_settings['packagingWeight'] > 0) ? $this->_settings['packagingWeight'] : 0;
	}

	private function cost($class = 1) {
		## Work out cost
		if (!empty($this->_settings['zone'.$this->_shipZone.'RatesClass'.$class])) {
			$bands	= explode(',', str_replace(' ', '', $this->_settings['zone'.$this->_shipZone.'RatesClass'.$class]));
//			natsort($bands);
			if (is_array($bands)) {
				foreach ($bands as $band) {
					$band_parts = explode(':', str_replace(' ', '', $band));
					/*
					$band_parts[0] = Weight
					$band_parts[1] = Cost
					*/
					if ($this->_weight <= $band_parts[0]) {
						$value	= (float)$band_parts[1]; 
						if($this->_settings['zone'.$this->_shipZone.'Handling']>0) $value += $this->_settings['zone'.$this->_shipZone.'Handling'];
						break;
					}
				}

				if (isset($value) && $value>=0) {
					$name = ($class == 1) ? $this->_settings['name_class1'] : $this->_settings['name_class2'];
					$this->_package[]	= array(
						'name'			=> $name,
						'value'			=> $value,
						'tax_id'		=> (int)$this->_settings['tax'],
						'tax_inclusive'	=> (int)$this->_settings['tax_included'],
						## Delivery times not applicable to this module
						'shipping'		=> "",
						'delivery'		=> "",
						'next_day'		=> "",
					);

				}
			}
		}
	}

	public function calculate() {

		$delivery	= (isset($this->_basket['delivery_address'])) ? $this->_basket['delivery_address'] : $this->_basket['billing_address'];
		## Build array of ISO Codes
		for($i=1;$i<5;$i++){
			$zones[$i] = explode(',', str_replace(' ', '', strtoupper($this->_settings['zone'.$i.'Countries'])));
		}
		## Find the country in the zones
		foreach ($zones as $key => $value) {
			foreach ($value as $no => $iso) {
				if ($iso == $delivery['country_iso']) {
					$this->_shipZone = $key;
					break;
				}
			}
		}
		## Calculate total weight including packaging
		$this->totalWeight();
		## Cost First Class
		$this->cost(1);
		## Cost Second Class
		$this->cost(2);
		return is_array($this->_package) ? $this->_package : false;
	}

	public function tracking($tracking_id = false) {
		return false;
	}

}

?>