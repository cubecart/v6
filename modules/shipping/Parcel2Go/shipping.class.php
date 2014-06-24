<?php
class Parcel2Go {
	private $_basket;
	private $_settings;

	public function __construct($basket) {
		## calculate the shipping costs
		$this->_basket	=  $basket;
		$this->_settings = $GLOBALS['config']->get(__CLASS__);
	}

	public function calculate() {
		
		$client = new SoapClient("https://v3.api.parcel2go.com/ParcelService.asmx?WSDL");
		$delivery	= (isset($this->_basket['delivery_address'])) ? $this->_basket['delivery_address'] : $this->_basket['billing_address'];
		
		$xml	= new XML(false);
		$xml->startElement('Shipment');
			$xml->startElement('CollectionAddress');
				$xml->writeElement('Addressee', $this->_settings['collection_addressee']);
				$xml->writeElement('ContactTelephone', $this->_settings['collection_telephone']);
				$xml->writeElement('CompanyName', $this->_settings['collection_company_name']);
				$xml->writeElement('PropertyNameOrNumber', $this->_settings['collection_property']);
				$xml->writeElement('Street', $this->_settings['collection_street']);
				$xml->writeElement('Locality', $this->_settings['collection_locality']);
				$xml->writeElement('TownOrCity', $this->_settings['collection_town']);
				$xml->writeElement('County', $this->_settings['collection_county']);
				$xml->writeElement('CountryCode', $this->_settings['collection_country']);
				$xml->writeElement('PostalCode', $this->_settings['collection_postcode']);
			$xml->endElement();
			$xml->startElement('DeliveryAddress');
				$xml->writeElement('Addressee', $delivery['title'].' '.$delivery['first_name'].' '.$delivery['last_name']);
				$xml->writeElement('ContactTelephone', $this->_basket['billing_address']['phone']);
				$xml->writeElement('CompanyName', $delivery['company_name']);
				$xml->writeElement('PropertyNameOrNumber', $delivery['line1']);
				$xml->writeElement('Street', $delivery['line2']);
				$xml->writeElement('Locality','');
				$xml->writeElement('TownOrCity', $delivery['town']);
				$xml->writeElement('County', $delivery['state']);
				$xml->writeElement('CountryCode', $delivery['country_iso']);
				$xml->writeElement('PostalCode', $delivery['postcode']);
			$xml->endElement();
			$xml->startElement('Parcels');
				$xml->startElement('Parcel');
					$xml->writeElement('Contents','Unknown');
					$xml->writeElement('Weight', $this->_basket['weight']);
					$xml->writeElement('Length', $this->_settings['length']);
					$xml->writeElement('Width', $this->_settings['width']);
					$xml->writeElement('Height', $this->_settings['height']);
				$xml->endElement();
			$xml->endElement();
			$xml->writeElement('TotalValue', $this->_basket['total']);
		$xml->endElement();
		$rawxml = $xml->getDocument();
	
		$shipobject = new SimpleXMLElement($rawxml);	
		try {
			$response = $client->GetQuotes(array("shipment" => $shipobject, "apiKey" => $this->_settings['api_key']));
		} catch (SoapFault $fault) {
			trigger_error($fault, E_USER_NOTICE);
		}
		
		if($response->GetQuotesResult->Success) {
			if($response->GetQuotesResult->Success) {
				foreach($response->GetQuotesResult->Quotes->Quote as $quote) {
					$value = 0;
					if ($this->_settings['handling'] > 0) $value += $this->_settings['handling'];
					$package[]	= array(
						'id'			=> 0,
						'name'			=> $quote->ServiceName,
						'value'			=> $quote->TotalPrice+=$value,
						//'tax_id'		=> '', // Quoted price includes VAT so this is not used (I think) please tell me otherwise 
						'tax_id'		=> (int)$this->_settings['tax'],
						'tax_inclusive'	=> (int)$this->_settings['tax_included'],
					);
				}
			}
		} else {
			trigger_error($response->GetQuotesResult->ErrorMessage,E_USER_NOTICE);
		}

		return $package;
	}

	public function tracking($tracking_id = null) {
		return false;
	}

}