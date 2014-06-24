<?php
class Store_Collection {
	private $_basket;
	private $_settings;

	public function __construct($basket) {
		## calculate the shipping costs
		$this->_db			=& $GLOBALS['db'];
		$this->_basket		=  $basket;
		$this->_settings 	= $GLOBALS['config']->get(__CLASS__);
	}
	
	public function calculate() {
		$basket_value = ($this->_basket['subtotal'] - $this->_basket['discount']);
		if(($basket_value>=$this->_settings['trigger']) || (!is_numeric($this->_settings['trigger']) || $this->_settings['trigger']==0)) {
			$package[]	= array(
				'id'		=> 0,
				'name'		=> empty($this->_settings['name']) ? '' : $this->_settings['name'],
				'value'		=> 0,
				'tax_id'	=> 0,
				## Delivery times not applicable to this module
				'shipping'	=> '',
				'delivery'	=> '',
				'next_day'	=> '',
			);
			return $package;
		} else {
			return false;
		}
	}

	public function tracking($tracking_id = null) {
		return false;
	}

}

?>