<?php
class Per_Item {

	private $_basket;
	private $_settings;

	public function __construct($basket) {
		## calculate the shipping costs
		$this->_basket	=  $basket;
		$this->_settings = $GLOBALS['config']->get(__CLASS__);
	}

	public function calculate() {
		$value  = 0;
		foreach ($this->_basket['contents'] as $item) {
			if ($item['digital']) continue;
			$value += ($item['quantity'] * $this->_settings['cost']);
		}
		$value += ($this->_settings['handling']>0) ? $this->_settings['handling'] : 0;
		$package[]	= array(
			'id'			=> 0,
			'name'			=> null,
			'value'			=> $value,
			'tax_id'		=> (int)$this->_settings['tax'],
			'tax_inclusive'	=> (int)$this->_settings['tax_included'],
			## Delivery times not applicable to this module
			'shipping'		=> '',
			'delivery'		=> '',
			'next_day'		=> '',
		);
		return $package;
	}

	public function tracking($tracking_id = null) {
		return false;
	}

}

?>