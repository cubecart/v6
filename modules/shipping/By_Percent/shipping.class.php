<?php
class By_Percent {
	private $_basket;
	private $_settings;

	public function __construct($basket = false) {
		$this->_db			=& $GLOBALS['db'];
		$this->_basket		= $basket;
		$this->_settings	= $GLOBALS['config']->get(__CLASS__);
	}

	public function calculate() {
		$value	= sprintf("%.2f", ($this->_basket['subtotal'] - $this->_basket['discount']) * (($this->_settings['percent'])/100));
		if ($this->_settings['handling'] > 0) {
			$value	+= $this->_settings['handling'];
		}

		$package[]	= array(
			//'name'			=> $this->_settings['percent'].'% of Order Total',
			'name'			=> empty($this->_settings['name']) ? '' : $this->_settings['name'],
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

	public function tracking($tracking_id = false) {
		return false;
	}
}

?>