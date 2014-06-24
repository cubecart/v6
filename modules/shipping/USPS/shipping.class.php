<?php
class USPS {
	private $_basket;
	private $_settings;

	private $_url		= 'production.shippingapis.com';
	private $_path		= '/ShippingAPI.dll';
	private $_api_domestic 			= 'RateV4';
	private $_api_international 	= 'IntlRateV2';

	private $_lbs 		= 0;
	private $_oz 		= 0;

	public function __construct($basket = false) {
		## calculate the shipping costs
		$this->_basket	=  $basket;
		$this->_settings = $GLOBALS['config']->get(__CLASS__);
	}

	################################################
	private function weightLbsOz(){
		$weight = $this->_basket['weight'];
		$weight += ($this->_settings['packagingWeight'] > 0) ? $this->_settings['packagingWeight'] : 0;
		if(strtolower($GLOBALS['config']->get('config','product_weight_unit'))=="kg"){
			$weight *= 2.2046;
		}
		$this->_decimalWeight = $weight;
		$this->_lbs = floor($weight);
		$this->_oz = ceil(($weight - $this->_lbs)*16);
	}

	private function getCountry($iso) {
		$name = getCountryFormat($iso,"iso","name");
		## Just to make life difficult and confusing... United Kingdom is not recognised by USPS, Great Britain is...
		if($name == "United Kingdom") {
			return "Great Britain";
		} else {
			return $name;
		}
	}

	private function plusHandling($value){
		return ($this->_settings['handling']>0) ? ($this->_settings['handling'] + $value) : $value;
	}
	
	private function cleanName($name) {
//		return html_entity_decode(str_replace('*','',$name));
		return html_entity_decode(str_replace(array('*','&lt;sup&gt;&#174;&lt;/sup&gt;'),'',$name));


	}

	################################################

	public function calculate() {
		$delivery	= (isset($this->_basket['delivery_address'])) ? $this->_basket['delivery_address'] : $this->_basket['billing_address'];
		
		// Ahhh...finally some code to fix the weird USPS Country issue we've had for ages.
		// Logged in users have a country set as 'country_id' where guests only have 'country' set.  LAME!
		if(!isset($delivery['country_id'])) $delivery['country_id'] = $delivery['country'];

		$xml	= new XML(false);

		## Set API to national or International
		// the following should be 'country_id' NOT 'country'!  If you think otherwise, talk to Bill!
		$this->_api = ($delivery['country_id']==$GLOBALS['config']->get('config','store_country')) ? $this->_api_domestic : $this->_api_international;

		/* IMPORTANT NOTE - USPS XML Parser is so totally shitty you have to send the XML elements in the correct order!!??!?! */
		$xml->startElement($this->_api.'Request');
		$xml->writeAttribute('USERID',$this->_settings['username']);
		$xml->startElement('Package');
		$xml->writeAttribute('ID',0);

		## Calculate Lbs/Oz
		$this->weightLbsOz();
		
		// the following should be 'country_id' NOT 'country'!  If you think otherwise, talk to Bill!
		if($delivery['country_id']==$GLOBALS['config']->get('config','store_country')) { // National "RateV3Request"
			$xml->writeElement('Service',"ALL");
			$xml->writeElement('ZipOrigination',$this->_settings['ziporigin']);
			
			$delivery['postcode'] = trim($delivery['postcode']);
			
			if(preg_match('/^([0-9]){5}-([0-9]){4}$/',$delivery['postcode'])) { 
				$delivery['postcode'] = substr($delivery['postcode'],0,5);
			} 
		
			$xml->writeElement('ZipDestination',$delivery['postcode']);
			$xml->writeElement('Pounds',$this->_lbs);
			$xml->writeElement('Ounces',$this->_oz);
			$xml->writeElement('Container',$this->_settings['container']);
			$xml->writeElement('Size',$this->_settings['size']);
			if ($this->_settings['size']=="LARGE") {
				if($this->_settings['container']=="NONRECTANGULAR" || $this->_settings['container']=="RECTANGULAR") {
					$xml->writeElement('Width',$this->_settings['width']);
					$xml->writeElement('Length',$this->_settings['length']);
					$xml->writeElement('Height',$this->_settings['height']);
					if($this->_settings['container']=="NONRECTANGULAR") {
						$xml->writeElement('Girth',$this->_settings['girth']);
					} else {
						$xml->writeElement('Girth',null);
					}
					
				} else {
					$xml->writeElement('Container',null);
				}
			}
			$xml->writeElement('Machinable',$this->_settings['machinable'] ? "True": "False");
			$xml->writeElement('ReturnLocations',"TRUE");
			//$xml->writeElement('ShipDate',date("d-M-Y"));
		} else { 
			// International Rates
			$xml->writeElement('Pounds',$this->_lbs);
			$xml->writeElement('Ounces',$this->_oz);
			
			$xml->writeElement('Machinable',$this->_settings['machinable'] ? "true": "false");
			
			$xml->writeElement('MailType', 'Package');
			$xml->writeElement('ValueOfContents', $this->_basket['subtotal']);
			$xml->writeElement('Country', $this->getCountry($delivery['country_iso']));
						
			$xml->writeElement('Container',$this->_settings['container']);
			$xml->writeElement('Size',$this->_settings['size']);
			$xml->writeElement('Width',$this->_settings['width']);
			$xml->writeElement('Length',$this->_settings['length']);
			$xml->writeElement('Height',$this->_settings['height']);
			
			if($this->_settings['container']=="NONRECTANGULAR") {
				$xml->writeElement('Girth',$this->_settings['girth']);
			} else {
				$xml->writeElement('Girth',null);
			}

		}
		$xml->endElement(); ## End Package
		$xml->endElement(); ## End RateV3Request

		## Send request
		$request	= new Request($this->_url, $this->_path);
		$xmlData = $xml->getDocument();
		$request->setData("API=".$this->_api."&XML=".$xmlData);
		$request->cache(false);
		unset($xml);
		$response	= $request->send();
		if ($response) {
			$xml	= new simpleXMLElement($response);
			if($this->_api==$this->_api_domestic) {
				if(isset($xml->Description)){
					trigger_error(sprintf('USPS Error: %s',$xml->Description));
				} else {
					foreach($xml->Package->Postage as $option){
						if($this->_settings['class_id_'.$option['CLASSID']]) {
							$package[]	= array(
								'id'		=> (string)$option['CLASSID'],
								'name'		=> (string)$this->cleanName((string)$option->MailService),
								'value'		=> (string)$this->plusHandling((float)$option->Rate),
								'tax_id'	=> (int)$this->_settings['tax'],
								## Delivery times
								'shipping'	=> "",
								'delivery'	=> "",
								'next_day'	=> "",
							);
						}
					}
				}
			} else {
				if(isset($xml->Description)){
					trigger_error(sprintf('USPS Error: %s',$xml->Description));
				} else {
					foreach($xml->Package->Service as $option){
						if($this->_settings['intl_class_id_'.$option['ID']]) {
							$package[]	= array(
								'id'		=> (string)$option['ID'],
								'name'		=> (string)$this->cleanName($option->SvcDescription),
								'value'		=> (string)$this->plusHandling($option->Postage),
								'tax_id'	=> (int)$this->_settings['tax'],
								## Delivery times
								'shipping'	=> "",
								'delivery'	=> "",
								'next_day'	=> "",
							);
						}
					}
				}
			}
		}
		unset($request,$xml);
		return (isset($package)) ? $package : false;
	}

	public function tracking($tracking_id = false) {
		return false;
	}
}

?>