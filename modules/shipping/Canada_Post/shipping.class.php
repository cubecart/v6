<?php
class Canada_Post {
	private $_basket;
	private $_settings;

	private $_url		= 'sellonline.canadapost.ca';
	private $_port		= 30000;

	public function __construct($basket = false) {
		## calculate the shipping costs
		$this->_basket	=  $basket;
		$this->_settings 	= $GLOBALS['config']->get(__CLASS__);
	}

	public function calculate() {
		return $this->request();
	}

	private function totalWeight() {
		$weight = $this->_basket['weight'];
		$weight += (isset($this->_settings['packagingWeight']) && $this->_settings['packagingWeight'] > 0) ? $this->_settings['packagingWeight'] : 0;
		return $weight;
	}

	public static function tracking($tracking_id = null) {
		$tracking_id	= preg_replace('#[^a-z0-9]#iU', '', $tracking_id);
		if (!is_null($tracking_id) && preg_match('#^([0-9]{16}|[a-z]{2}[0-9]{9}[a-z]{2})$#iU', $tracking_id)) {
			return sprintf('https://obc.canadapost.ca/emo/basicPin.do?action=query&trackingId=%s', $tracking_id);
		}
		return false;
	}

	################################################

	private function request() {
		$xml	= new XML(false);
		$xml->startElement('eparcel');
			$xml->writeElement('language', 'en');
			$xml->startElement('ratesAndServicesRequest');
				$xml->writeElement('merchantCPCID', $this->_settings['merchant']);
				$xml->writeElement('fromPostalCode', $this->_settings['postcode']);
				$xml->writeElement('itemsPrice', $this->_basket['subtotal']);
					$xml->startElement('lineItems');
						## Loop through basket items?
						$xml->startElement('item');
						$xml->writeElement('quantity', 1);
						$xml->writeElement('weight', $this->totalWeight());
						$xml->writeElement('length', $this->_settings['length']);
						$xml->writeElement('width', $this->_settings['width']);
						$xml->writeElement('height', $this->_settings['height']);
						$xml->writeElement('description', 'Online Order');
					$xml->endElement();
				$xml->endElement();
				$delivery	= (isset($this->_basket['delivery_address'])) ? $this->_basket['delivery_address'] : $this->_basket['billing_address'];
				$xml->writeElement('city', (isset($delivery['town'])) ? $delivery['town'] : '');
				$xml->writeElement('provOrState', $delivery['state']);
				$xml->writeElement('country', $delivery['country_iso']);
				$xml->writeElement('postalCode', $delivery['postcode']);
			$xml->endElement();
		$xml->endElement();

		## Send request
		$data 		= $xml->getDocument();
		$request	= new Request($this->_url, '/', $this->_port);
		$request->setData($data);
		unset($xml);
		$response	= $request->send();

		if ($response) {
			try {
				$xml	= new simpleXMLElement($response);
				if ($xml->error->statusCode){
					trigger_error('Canada Post Error '.$xml->error->statusCode.': '.$xml->error->statusMessage);
				} else {
					foreach ($xml->ratesAndServicesResponse->product as $option) {
						$value = $option->rate;
						if ($this->_settings['handling']>0) $value += $this->_settings['handling'];
						## Make sure the service is enabled
						$service_id = $option->attributes()->id;
						if ($this->_settings['SERVICE'.$service_id]==true){
							$package[]	= array(
								'id'		=> (int)$service_id,
								'name'		=> (string)$option->name,
								'value'		=> sprintf('%.2f',$value),
								'currency'	=> 'CAD',
								'tax_id'	=> (int)$this->_settings['tax'],
								## Delivery times
								'shipping'	=> (string)$option->shippingDate,
								'delivery'	=> (string)$option->deliveryDate,
								'next_day'	=> (string)$option->nextDayAM,
							);
						}
					}
				}
			} catch (Exception $e) {
   				trigger_error('Canada Post Error: Return string is not a valid xml');
			}
		}
		unset($request);
		return (isset($package)) ? $package : false;
	}
}

?>