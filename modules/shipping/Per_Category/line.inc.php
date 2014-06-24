<?php
class Per_Category_Line {

	private $_shipZone 		= false;
	public $_lineShip 		= 0; ## Shipping cost per item
	public $_perShipPrice 	= 0; ## Shipping cost per shipment

	public function __construct($ship_by_cat, $basket) {
		$this->_basket	=  $basket;
		
		$this->_settings = $ship_by_cat;
		## build array of ISO Code from shipping module
		$zones['national'] = explode(",",str_replace(" ","",strtoupper($this->_settings['national'])));
		$zones['international'] = explode(",",str_replace(" ","",strtoupper($this->_settings['international'])));
		## find the country to see if destination is national/international
		foreach ($zones as $key => $value) {
			foreach ($zones[$key] as $no => $iso) {
				if ($iso == $this->_basket['delivery_address']['country_iso']) {
					## Set shipping zone as either national or international
					$this->_shipZone = $key;
					break;
				}
			}
		}

		// allow wildcard for ALL that's not national to be International
		if ($this->_shipZone == false && $zones['international'][0] == '*') {
			$this->_shipZone = 'international';
		}
	}
	
	public function lineCalc($product,$category) {
		if(!$this->_shipZone) return false;
		if($this->_shipZone == "national"){
			## Add to shipping price
			$this->_lineShip += ($category['item_ship'] * $product['quantity']);
			## If per shipment price is higher than last product increase it
			if(!$this->_perShipPrice || $this->_perShipPrice < $category['per_ship']) {
				$this->_perShipPrice = $category['per_ship'];
			}
			
		} elseif($this->_shipZone == "international") {
			## Add to shipping price
			$this->_lineShip += ($category['item_int_ship'] * $product['quantity']);
			## If per shipment price is higher than last product increase it
			if(!$this->_perShipPrice || $this->_perShipPrice < $category['per_int_ship']) {
				$this->_perShipPrice = $category['per_int_ship'];
			}
		} else {
			return false;
		}
	}
} 
?>