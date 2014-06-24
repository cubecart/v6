<?php
class AusPost {
	private $_basket;
	private $_settings;

	private $_url		= 'drc.edeliver.com.au';
	private $_path		= '/ratecalc.asp';

	private $_packageWeight = 0;


	public function __construct($basket = false) {
		## calculate the shipping costs
		$this->_db			=& $GLOBALS['db'];
		$this->_basket		=  $basket;
		$this->_settings 	= $GLOBALS['config']->get(__CLASS__);
	}

	public function calculate() {

		$this->weight();
		$delivery	= (isset($this->_basket['delivery_address'])) ? $this->_basket['delivery_address'] : $this->_basket['billing_address'];
		$destCountryCode = $delivery['country_iso'];
		$destPostalCode = $delivery['postcode'];

		$requestData = array(
			'Height'				=> $this->_settings['height']*10,
			'Length'				=> $this->_settings['length']*10,
			'Width'					=> $this->_settings['width']*10,
			'Weight'				=> ($this->_basket['weight']+$this->_packageWeight)*1000,
			'Quantity'				=> 1,
			'Pickup_Postcode'		=> $this->_settings['postcode'],
			'Destination_Postcode'	=> $destPostalCode,
			'Country'				=> $destCountryCode,
		);

		$options = array();

		if ($requestData['Country'] == 'AU') {
			if($this->_settings['SERVICE_STANDARD'])	$options['STANDARD']	= 'Standard Delivery';
			if($this->_settings['SERVICE_EXPRESS']) 	$options['EXPRESS']		= 'Express Delivery';
		} else {
			if($this->_settings['SERVICE_Air']) 		$options['Air']			= 'Air Mail';
			if($this->_settings['SERVICE_Sea']) 		$options['Sea']			= 'Sea Mail';
			if($this->_settings['SERVICE_ECI_D']) 		$options['ECI_D']		= 'Express Courier International (Document)';
			if($this->_settings['SERVICE_ECI_M']) 		$options['ECI_M']		= 'Express Courier International (Mechandise)';
			if($this->_settings['SERVICE_EPI']) 		$options['EPI']			= 'Express Post International';
		}

		if($options) {
			foreach ($options as $option => $name) {
				$requestData['Service_Type'] = $option;

				if (function_exists('http_build_query')) {
					$data = http_build_query($requestData, '', '&');
				} else {
					foreach ($requestData as $key => $val)  {
						$array[] = sprintf('%s=%s', $key, $val);
					}
					$data = implode('&', $array);
					unset($array);
				}

				$request	= new Request($this->_url, $this->_path);
				$request->setMethod("post");
				$request->sendHeaders(false);
				$request->setData($data);
				$request->cache(false);
				$response	= $request->send();

				$response = str_replace("\n", '&', $response);
				parse_str($response, $result);

				if ($response) {
					if($result['charge']>0) {
						$result['charge'] += ($this->_settings['handling']>0) ? $this->_settings['handling'] : 0;
						$package[]	= array(
							'id'			=> (string)"auspost",
							'name'			=> (string)"Australia Post ".$name,
							'value'			=> (string)$result['charge'],
							'tax_id'		=> (int)$this->_settings['tax'],
							'tax_inclusive'	=> (int)$this->_settings['tax_included'],
						);
					} else {
						trigger_error(sprintf('AusPost Error for '.$name.': %s',$result['err_msg']));
					}
				}
				unset($request,$response,$result);
			}

		} else {
			trigger_error(sprintf('AusPost Error: No services have been specified in the admin module configuration.'));
			return false;
		}
		return (isset($package)) ? $package : false;
	}



	public function tracking($tracking_id = false) {
		return false;
	}

	################################################

    private function weight(){
		$weight = $this->_basket['weight'];
		$this->_packageWeight += ($this->_settings['packagingWeight'] > 0) ? $this->_settings['packagingWeight'] : 0;
    }
}