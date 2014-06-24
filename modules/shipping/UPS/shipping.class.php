<?php
class UPS {
	private $_basket;
	private $_settings;

	private $_url		= 'www.ups.com';
	private $_path		= '/using/services/rave/qcostcgi.cgi';

	private $_upsProductName = '';
	private $_upsProductCode = '';
	private $_originCountryCode = '';
	private $_destPostalCode = '';
	private $_destCountryCode = '';
	private $_packageWeight = 0;
	private $_rateCode = '';
	private $_containerCode = '';
	private $_resComCode = '';

	private $_critical_error = false; ## Used to break looping request is major settings are wrong

	public function __construct($basket = false) {
		## calculate the shipping costs
		$this->_db			=& $GLOBALS['db'];
		$this->_basket		=  $basket;
		$this->_settings 	= $GLOBALS['config']->get(__CLASS__);
	}

	public function calculate() {
		return $this->request();
	}

	public function tracking($tracking_id = false) {
		return false;
	}

	################################################

	private function upsProduct(){
		switch ($this->_upsProductCode) {
			case "1DM":
				## Next Day Air Early AM
				$this->_upsProductName = "Next Day Air Early AM";
			break;
			case "1DA":
				## Next Day Air
				$this->_upsProductName = "Next Day Air";
			break;
			case "1DP":
				## Next Day Air Saver
				$this->_upsProductName = "Next Day Air Saver";
			break;
			case "2DM":
				## 2nd Day Air Early AM
				$this->_upsProductName = "2nd Day Air Early AM";
			break;
			case "2DA":
				## 2nd Day Air
				$this->_upsProductName = "2nd Day Air";
			break;
			case "3DS":
				## 3 Day Select
				$this->_upsProductName = "3 Day Select";
			break;
			case "GND":
				## Ground
				$this->_upsProductName = "Ground";
			break;
			case "STD":
				## Canada Standard
				$this->_upsProductName = "Canada Standard";
			break;
			case "XPR":
				## Worldwide Express
				$this->_upsProductName = "Worldwide Express";
			break;
			case "XDM":
				## Worldwide Express Plus
				$this->_upsProductName = "Worldwide Express Plus";
			break;
			case "XPD":
				## Worldwide Expedited
				$this->_upsProductName = "Worldwide Expedited";
			break;
		}
    }

    private function rate(){
		$value = strtoupper($this->_settings['rate']);
		switch($value){
			case "RDP":
			 	## Ragular Daily Pickup
				$this->_rateCode = "Regular+Daily+Pickup";
			break;
			case "OCA":
				## On Call Air
				$this->_rateCode = "On+Call+Air";
			break;
			case "OTP":
				## One Time Pickup
				$this->_rateCode = "One+Time+Pickup";
			break;
			case "LC":
				## Letter Center
				$this->_rateCode = "Letter+Center";
			break;
			//case "CC":
			default:
				## Customer Counter
				$this->_rateCode = "Customer+Counter";
			break;
		}
    }

    private function container(){
    	$value = strtoupper($this->_settings['container']);
    	switch($value){
        	case "ULE":
        		## UPS Letter Envelope
          		$this->_containerCode = "01";
          	break;
        	case "UT":
        		## UPS Tube
         		$this->_containerCode = "03";
          	break;
        	case "UEB":
        		## UPS Express Box
          		$this->_containerCode = "21";
          	break;
        	case "UW25":
        		## UPS Worldwide 25 kilo
          		$this->_containerCode = "24";
          	break;
        	case "UW10":
        		## UPS Worldwide 10 kilo
          		$this->_containerCode = "25";
          	break;
          	//case "CP":
          	default:
          		## Customer Packaging
          		$this->_containerCode = "00";
          	break;
          }
    }

    private function weight(){
		$this->_packageWeight = $this->_basket['weight'];
		$this->_packageWeight += ($this->_settings['packagingWeight'] > 0) ? $this->_settings['packagingWeight'] : 0;
    }

    private function rescom(){
		$value = strtoupper($this->_settings['rescom']);
		switch($value){
			case "COM":
				## Commercial Address
				$this->_resComCode = "0";
 			break;
 			//case "RES";
 			default:
 				## Residential Address
				$this->_resComCode = "1";
			break;
          }
    }


	private function getQuote() {

		$data = array(
			'accept_UPS_license_agreement'=> 'yes',
			'10_action' => 3,
			'13_product' => $this->_upsProductCode,
			'14_origCountry' => $this->_originCountryCode,
			'15_origPostal' => $this->_settings['postcode'],
			'19_destPostal' => $this->_destPostalCode,
			'22_destCountry' => $this->_destCountryCode,
			'23_weight' => $this->_packageWeight,
			'47_rateChart' => $this->_rateCode,
			'48_container' => $this->_containerCode,
			'49_residential' => $this->_resComCode
		);
		## Send request
		$request	= new Request($this->_url, $this->_path);
		$request->setMethod("get");
		$request->sendHeaders(false);
		$request->setData($data);
		$request->cache(true);
		unset($xml);
		$response	= $request->send();

		if ($response) {

			$dataline = explode("\n", $response);
			$value = 0;

			foreach ($dataline as $result) {
				if (strstr($result, 'Origin postal code must have five digits')) {
					$error = 'Origin zip code must have five digits to use UPS of United States origin. If you are a US based store please check the value of the country in your stores general settings.';
					trigger_error(sprintf('UPS Error: %s',$error));
					$this->_critical_error = true;
					break;
				} else if (strstr($result, 'Unsupported country specified')) {
					$error = 'This UPS modules can only be used for quotes with United States shipping origin. If you are a US based store please check the value of the country in your stores general settings.';
					trigger_error(sprintf('UPS Error: %s',$error));
					$this->_critical_error = true;
					break;
				}

				$result = explode('%', $result);
				if(isset($result[1]) && strlen($result[1])>3) {
					trigger_error(sprintf('UPS Error for '.$this->_upsProductName.': %s',$result[1]));
					break;
				}

				$errcode = substr($result[0], -1);

				switch($errcode){
					case 3:
					case 4:
						$value = $result[8];
					break;
					case 5:
					case 6:
						$value = $result[1];
					break;
				}
			}
			if ($value>0) {
				$value += ($this->_settings['handling']>0) ? $this->_settings['handling'] : 0;
				$package[]	= array(
					'id'		=> (string)$this->_upsProductCode,
					'name'		=> (string)'UPS '.$this->_upsProductName,
					'value'		=> (string)$value,
					'tax_id'	=> (int)$this->_settings['tax'],
					## Delivery times
					'shipping'	=> '',
					'delivery'	=> '',
					'next_day'	=> '',
				);
			}
		}
		unset($request);
		return (isset($package)) ? $package[0] : false;
	}

	private function request() {

		$delivery	= (isset($this->_basket['delivery_address'])) ? $this->_basket['delivery_address'] : $this->_basket['billing_address'];
		$this->_originCountryCode = getCountryFormat($GLOBALS['config']->get('config','store_country'),'numcode','iso');
		$this->_destCountryCode = $delivery['country_iso'];
		$this->_destPostalCode = $delivery['postcode'];
	
		if(is_array($this->_settings)){
			foreach($this->_settings as $key => $value) {
				if (strstr($key, 'product') && $value) {
					$this->_upsProductCode = substr($key,7,3);
					$this->upsProduct();
					$this->rate();
					$this->container();
					$this->weight();
					$this->rescom();
					$productQuote = $this->getQuote();
					if($productQuote) $package[] = $productQuote;
					if($this->_critical_error) break;
				}
			}
		} else {
			trigger_error('UPS Error: No settings found in request function!');
		}
	return (isset($package)) ? $package : false;
	}
}

?>