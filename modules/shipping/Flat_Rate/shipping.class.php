<?php
class Flat_Rate {
	private $_basket;
	private $_settings;

	public function __construct($basket = false) {
		$this->_db			=& $GLOBALS['db'];
		$this->_basket		= $basket;
		$this->_settings	= $GLOBALS['config']->get(__CLASS__);
	}

	public function calculate() {

		$value		= $this->_settings['cost'];
		if ($this->_settings['handling'] > 0) {
			$value	+= $this->_settings['handling'];
		}
		$package[]	= array(
			'name'			=> empty($this->_settings['name']) ? '' : $this->_settings['name'],
			'value'			=> $value,
			'tax_id'		=> (int)$this->_settings['tax'],
			'tax_inclusive'	=> (int)$this->_settings['tax_included'],
		);
		return $package;

	}

	public function tracking($tracking_id = false) {
		return false;
	}
}

?>