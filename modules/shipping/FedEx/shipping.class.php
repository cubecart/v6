<?php
class FedEx {
	private $_basket;
	private $_settings;
	private $_client;

	private $_weight 		= 0;

	public function __construct($basket = false) {
		## calculate the shipping costs
		$this->_db			=& $GLOBALS['db'];
		$this->_basket		=  $basket;
		$this->_settings 	= $GLOBALS['config']->get(__CLASS__);
	}


	private function setWeightUnit(){
		if(strtolower($GLOBALS['config']->get('config','product_weight_unit'))=="kg"){
			return 'KG';
		} else {
			return 'LB';
		}
	}

	private function totalWeight() {
		$weight = $this->_basket['weight'];
		$weight += ($this->_settings['packagingWeight'] > 0) ? $this->_settings['packagingWeight'] : 0;
		return sprintf("%.1f",$weight);
	}

	private function setPayorType(){
		## Setting should be SENDER, RECIPIENT, THIRDPARTY, CREDITCARD, COLLECT, CASH
		$this->_payorType = $this->_settings['storeOwnerPays'] ? "RECIPIENT" : "SENDER";
	}

	private function friendlyServiceName($service_name) {
		if(empty($service_name)) return '';

		switch($service_name){
			case "EUROPE_FIRST_INTERNATIONAL_PRIORITY":
				return 'Europe First International Priority';
			break;
			case "FEDEX_1_DAY_FREIGHT":
				return '1 Day Freight';
			break;
			case "FEDEX_2_DAY":
				return '2 Day';
			break;
			case "FEDEX_2_DAY_AM":
				return '2 Day AM';
			break;
			case "FEDEX_2_DAY_FREIGHT":
				return '2 Day Freight';
			break;
			case "FEDEX_3_DAY_FREIGHT":
				return '3 Day Freight';
			break;
			case "FEDEX_EXPRESS_SAVER":
				return 'Express Saver';
			break;
			case "FEDEX_FIRST_FREIGHT":
				return 'First Freight';
			break;
			case "FEDEX_FREIGHT_ECONOMY":
				return 'Freight Economy';
			break;
			case "FEDEX_FREIGHT_PRIORITY":
				return 'Freight Priority';
			break;
			case "FEDEX_GROUND":
				return 'Ground';
			break;
			case "FIRST_OVERNIGHT":
				return 'First Overnight';
			break;
			case "GROUND_HOME_DELIVERY":
				return 'Ground Home Delivery';
			break;
			case "INTERNATIONAL_ECONOMY":
				return 'International Economy';
			break;
			case "INTERNATIONAL_ECONOMY_FREIGHT":
				return 'International Economy Freight';
			break;
			case "INTERNATIONAL_FIRST":
				return 'International First';
			break;
			case "INTERNATIONAL_PRIORITY":
				return 'International Priority';
			break;
			case "INTERNATIONAL_PRIORITY_FREIGHT":
				return 'International Priority Freight';
			break;
			case "PRIORITY_OVERNIGHT":
				return 'Priority Overnight';
			break;
			case "SMART_POST":
				return 'Smart Post';
			break;
			case "STANDARD_OVERNIGHT":
				return 'Standard Overnight';
			break;

		}

	}
	
	public static function tracking() {
		return false;
	}

	public function calculate() {

		$path_to_wsdl = CC_ROOT_DIR.'/modules/shipping/FedEx/wsdl/RateService_v13.wsdl';
		
		ini_set("soap.wsdl_cache_enabled", "0");
		 
		$this->_client = new SoapClient($path_to_wsdl, array('trace' => 1));	
		$request['WebAuthenticationDetail'] = array('UserCredential' =>
		                                      array('Key' => $this->_settings['key'], 'Password' => $this->_settings['password'])); 
		$request['ClientDetail'] = array('AccountNumber' => $this->_settings['accNo'], 'MeterNumber' => $this->_settings['meterNo']);
		$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Rate Available Services Request v13 using PHP ***');
		$request['Version'] = array('ServiceId' => 'crs', 'Major' => '13', 'Intermediate' => '0', 'Minor' => '0');
		$request['returnTransitAndCommit'] = true;
		$request['RequestedShipment']['DropoffType'] = $this->_settings['dropoffType']; 
		$request['RequestedShipment']['ShipTimestamp'] = date('c');
		// Service Type and Packaging Type are not passed in the request
		$request['RequestedShipment']['Shipper'] = array('Address'=>array('StreetLines' => array($this->_settings['line1']),
                                          'City' => $this->_settings['city'],
                                          'StateOrProvinceCode' => $this->_settings['state'],
                                          'PostalCode' => $this->_settings['postcode'],
                                          'CountryCode' => $this->_settings['country']));
		
		if (in_array($this->_basket['delivery_address']['country_iso'], array('US','CA'))) {
			$delivery_state = $this->_basket['delivery_address']['state_abbrev'];
			if(strlen($delivery_state)>2) {
				$delivery_state = getStateFormat($delivery_state, 'name', 'abbrev');
			}
		}
		
		
		
		$request['RequestedShipment']['Recipient'] = array('Address'=>array('StreetLines' => array($this->_basket['delivery_address']['line1'].' '.$this->_basket['delivery_address']['line2']),
                                          'City' => $this->_basket['delivery_address']['town'],
                                          'StateOrProvinceCode' => $delivery_state,
                                          'PostalCode' => $this->_basket['delivery_address']['postcode'],
                                          'CountryCode' => $this->_basket['delivery_address']['country_iso']));
		
		$request['RequestedShipment']['ShippingChargesPayment'] = array('PaymentType' => 'SENDER',
		                                                        'Payor' => array(
																	'ResponsibleParty' => array(
																		'AccountNumber' => $this->_settings['accNo'],
																		'Contact' => null,
																		'Address' => array('CountryCode' => 'US'))));
																		
		$request['RequestedShipment']['RateRequestType'] = 'ACCOUNT'; 
		$request['RequestedShipment']['RateRequestType'] = 'LIST'; 
		$request['RequestedShipment']['PackageCount'] = '1';
		$request['RequestedShipment']['RequestedPackageLineItems'] = array(
		'0' => array(
			'SequenceNumber' => 1,
			'GroupPackageCount' => 1,
			'Weight' => array('Value' => $this->totalWeight(),
		    'Units' => $this->setWeightUnit()),
		    'Dimensions' => array('Length' => $this->_settings['length'],
		       'Width' => $this->_settings['width'],
		       'Height' => $this->_settings['height'],
		       'Units' => 'IN')));
		
		try 
		{
			if($this->_setEndpoint('changeEndpoint'))
			{
				$newLocation = $this->_client->__setLocation($this->_setEndpoint('endpoint'));
			}
			
			$response = $this->_client->getRates($request);
		   
		    if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR')
		    {
				if(is_array($response->RateReplyDetails)){
					foreach ($response->RateReplyDetails as $rateReply)
					{
						if($this->_settings['FDXG_'.$rateReply->ServiceType]==1) {
							$value = $rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount;
							$value += ($this->_settings['handling']>0) ? $this->_settings['handling'] : 0;
							
							$package[]	= array(
								'id'		=> (string)$rateReply->ServiceType,
								'name'		=> (string)"FedEx ".$this->friendlyServiceName($rateReply->ServiceType),
								'value'		=> (string)$value,
								'tax_id'	=> (int)$this->_settings['tax'],
								## Delivery times
								'shipping'	=> "",
								'delivery'	=> "",
								'next_day'	=> "",
							);
							
						}
					}
				} else {
					trigger_error('FedEx Error: No shipping quotes could be obtained for '.$this->_basket['delivery_address']['postcode'].', '.$this->_basket['delivery_address']['country_iso']);
				}
		      
		    } else {
		       trigger_error('FedEx Error: '.$response->Notifications->Message);

		    } 
		     
		
		} catch (SoapFault $exception) {
		  trigger_error('FedEx Error: invalid request');       
		}
		
		return (isset($package)) ? $package : false;
	}
	
	private function _setEndpoint($var){
		if($var == 'changeEndpoint') return false;
		if($var == 'endpoint') return '';
	}
	
}

?>