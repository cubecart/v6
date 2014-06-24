<?php
class Gateway {
	private $_config;
	private $_module;
	private $_basket;
	private $_result_message = '';
	private $_validate_card_data = array();
	private $_validate_valid = false;
	private $_encryption;

	public function __construct($module = false, $basket = false) {
		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
	}

	public function transfer() {
		$transfer	= array(
			'action'	=> currentPage(),
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> 'manual',
		);
		return $transfer;
	}

	public function repeatVariables() {
		return false;
	}

	public function fixedVariables() {
		$hidden['gateway']	= basename(dirname(__FILE__));
		return (isset($hidden)) ? $hidden : false;
	}

	public function call() {
		return false;
	}

	public function process() {
		$order				= Order::getInstance();
		$order_summary		= $order->getSummary($this->_basket['cart_order_id']);

		$cardNo			= trim($_POST['cardNumber']);
		$issueNo		= trim($_POST['issue']);
		if(!empty($_POST['startYear']) && !empty($_POST['startMonth'])) {
			$issueDate	= $_POST['startYear'].str_pad($_POST['startMonth'], 2, '0', STR_PAD_LEFT);
		} else {
			$issueDate				= null;
			// Set to null to stop drop downs being selected if only one of the other has a value
			$_POST['startYear']		= null;
			$_POST['startMonth']	= null;
		}
		$expireDate		= $_POST['expirationYear'].str_pad($_POST['expirationMonth'], 2, '0', STR_PAD_LEFT);
		$cvc2			= trim($_POST['cvc2']);
		$securityCode	= (!empty($cvc2)) ? $cvc2 : false;

		$this->check($cardNo, $issueNo, $issueDate, $expireDate, $securityCode);

		$transData['customer_id'] 	= $order_summary['customer_id'];
		$transData['order_id'] 		= $this->_basket['cart_order_id'];
		$transData['amount'] 		= $order_summary['total'];
		$transData['gateway'] 		= "Card Capture";

		if ($this->_module['validation'] && $this->_validate_card_data['fail']) {
			if(is_array($this->_validate_card_data['error'])){
				foreach ($this->_validate_card_data['error'] as $val) {
					$this->_result_message[] = $val;
				}
			} else {
				$this->_result_message[] = 'Undefined Error.';
			}
			$transData['trans_id'] 	= false;
			$transData['status'] 	= 'Fail';
			$transData['notes'] 	= $this->_result_message;
			$order->logTransaction($transData);
		} else {
			## store card details
			$card_valid = (!empty($_POST['startMonth']) && !empty($_POST['startYear'])) ? str_pad($_POST['startMonth'], 2, '0', STR_PAD_LEFT)."/".$_POST['startYear'] : null;
			$cardData = array(
				'card_type'		=> $this->_validate_card_data['cardType'],
				'card_number'	=> $cardNo,
				'card_expire'	=> str_pad($_POST['expirationMonth'], 2, '0', STR_PAD_LEFT)."/".$_POST['expirationYear'],
				'card_valid'	=> $card_valid,
				'card_issue'	=> $issueNo,
				'card_cvv'		=> $securityCode
			);

            $error = false;
			if (extension_loaded('mcrypt') && function_exists('mcrypt_module_open')) {
				$this->_encryption	= Encryption::getInstance();
				$this->_encryption->setup(false, $order_summary['cart_order_id']);
				$record['offline_capture'] = $this->_encryption->encrypt(serialize($cardData));
				$GLOBALS['db']->update('CubeCart_order_summary', $record, array('customer_id' => $order_summary['customer_id'], 'cart_order_id' => $order_summary['cart_order_id']));
			} else {
			    $error = 'Card Capture Error: mcrypt library missing from server required to encrypt credit card data.';
				trigger_error($error);
			}
			// log trans details
			$transData['trans_id'] = false;
			$transData['status'] = "Success";
			$transData['notes'] = $error ? $error : "Card Details captured ready for processing offline.";
			$order->logTransaction($transData);

			if($this->_module['confirmation_email']) {
				
				$inventory = $GLOBALS['db']->select('CubeCart_order_inventory', false, array('cart_order_id' => $order_summary['cart_order_id']));

				// Compose the Order Confirmation email to the customer
				if ($content = Mailer::getInstance()->loadContent('cart.order_confirmation', $order_summary['lang'])) {
	
						// Put in items
						foreach ($inventory as $item) {
							if($item['product_id']>0){
								$product			= array_merge($GLOBALS['catalogue']->getProductData($item['product_id']),$item);
								$product['item_price']	= Tax::getInstance()->priceFormat($product['price']);
								$product['price'] 	= Tax::getInstance()->priceFormat($product['price']*$product['quantity']);
								if (!empty($item['product_options'])) $product['product_options'] = implode(' ',unserialize($item['product_options']));
								$vars['products'][]	= $product;
							} else {
								$item['price']	= Tax::getInstance()->priceFormat($item['price']);
								$vars['products'][]	= $item;
							}
						}
						
						if (isset($vars['products']) && !empty($vars['products'])) {
							$GLOBALS['smarty']->assign('PRODUCTS', $vars['products']);
						}
	
						// Taxes
						$taxes	= $GLOBALS['db']->select('CubeCart_order_tax', false, array('cart_order_id' => $order_summary['cart_order_id']));

						// Put tax in
						if ($taxes) {
							foreach($taxes as $order_tax) {
								$tax_data = Tax::getInstance()->fetchTaxDetails($order_tax['tax_id']);
								$tax['tax_name'] 	= $tax_data['name'];
								$tax['tax_percent'] = sprintf('%.3f',$tax_data['tax_percent']);
								$tax['tax_amount'] 	= Tax::getInstance()->priceFormat($order_tax['amount']);
								$vars['taxes'][]	= $tax;
							}
							if (isset($vars['taxes']) && !empty($vars['taxes'])) {
								$GLOBALS['smarty']->assign('TAXES', $vars['taxes']);
							}
						}
						
						$billing = array (
							'first_name' 	=> $order_summary['first_name'],
							'last_name' 	=> $order_summary['last_name'],
							'company_name' 	=> $order_summary['company_name'],
							'line1' 		=> $order_summary['line1'],
							'line2' 		=> $order_summary['line2'],
							'town' 			=> $order_summary['town'],
							'state' 		=> getStateFormat($order_summary['state']),
							'postcode' 		=> $order_summary['postcode'],
							'country' 		=> getCountryFormat($order_summary['country']),
							'phone' 		=> $order_summary['phone'],
							'email' 		=> $order_summary['email']
						);
						$shipping = array (
							'first_name' 	=> $order_summary['first_name_d'],
							'last_name' 	=> $order_summary['last_name_d'],
							'company_name' 	=> $order_summary['company_name_d'],
							'line1' 		=> $order_summary['line1_d'],
							'line2' 		=> $order_summary['line2_d'],
							'town' 			=> $order_summary['town_d'],
							'state' 		=> getStateFormat($order_summary['state_d']),
							'postcode' 		=> $order_summary['postcode_d'],
							'country' 		=> getCountryFormat($order_summary['country_d'])
						);	

						// Format data
						$order_summary['order_date'] = formatTime($order_summary['order_date'],false,true);
						
						$order_summary['link'] 		= $GLOBALS['storeURL'].'/index.php?_a=vieworder&cart_order_id='.$order_summary['cart_order_id'];
						$GLOBALS['smarty']->assign('DATA', $order_summary);
						$GLOBALS['smarty']->assign('BILLING', $billing);
						$GLOBALS['smarty']->assign('SHIPPING', $shipping);
						$GLOBALS['smarty']->assign('TAXES', $vars['taxes']);
						$GLOBALS['smarty']->assign('PRODUCTS', $vars['products']);
						Mailer::getInstance()->sendEmail($order_summary['email'], $content);
				}
				
			}

			httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
		}
	}

	private function formatMonth($val) {
		return $val." - ".strftime("%b", mktime(0,0,0,$val,1 ,2009));
	}

	public function form() {
		
		// Process transaction
		if (isset($_POST['cardNumber'])) {
			$this->process();
		}
		// Display payment result message
		if (!empty($this->_result_message))	{
			$GLOBALS['gui']->setError($this->_result_message);
		}

		$vars = array();
		if(is_array($this->_module['cards'])) {
			foreach($this->_module['cards'] as $key => $value) {
				if($value) {
					$card_name = str_replace("_"," ",$key);
					$smarty_data['cards'][] = array (
						'selected' 	=> ($_POST['cardType']==$key) ? 'selected="selected"' : '',
						'value'		=> $card_name,
						'display'	=> $card_name
					);
					$GLOBALS['smarty']->assign('CARDS', $smarty_data['cards']);
				}
			}
		}

		if($this->_module['cvv']) {
			$smarty_data['cvv'] = array (
				'enabled' 	=> true,
				'length'	=> $this->_module['cards']['Amex'] ? 4 : 3
			);
		}
		$GLOBALS['smarty']->assign('CVV', $smarty_data['cvv']);

		if($this->_module['issue_info']) {

			// Show Start Months
			$selectedMonth	= (isset($_POST['startMonth'])) ? $_POST['startMonth'] : '';
			$smarty_data['start']['months'][]	= array(
				'selected'	=> '',
				'value'		=> '',
				'display'	=> '',
			);
			for($i = 1; $i <= 12; ++$i) {
				$val = sprintf('%02d',$i);
				$smarty_data['start']['months'][]	= array(
					'selected'	=> ($val == $selectedMonth) ? 'selected="selected"' : '',
					'value'		=> $val,
					'display'	=> $this->formatMonth($val),
				);
			}

			// Show Start Years
			$thisYear 	= date("Y");
			$maxYear 	= $thisYear-5;
			$selectedYear = isset($_POST['startYear']) ? $_POST['startYear'] : ($thisYear+2);
			$smarty_data['start']['years'][]	= array(
				'selected'	=> '',
				'value'		=> '',
			);
			for($i = $thisYear; $i >= $maxYear; $i--) {
				$smarty_data['start']['years'][]	= array(
					'selected'	=> ($i == $selectedYear) ? 'selected="selected"' : '',
					'value'		=> str_pad($i, 2, '0', STR_PAD_LEFT),
				);
			}
			$GLOBALS['smarty']->assign('START', $smarty_data['start']);
		}

		// Show Expire Months
		$selectedMonth = (isset($_POST['expirationMonth'])) ? $_POST['expirationMonth'] : date('m');
		for($i = 1; $i <= 12; ++$i) {
			$val = sprintf('%02d',$i);
			$smarty_data['expire_months'][]	= array(
				'selected'	=> ($val == $selectedMonth) ? 'selected="selected"' : '',
				'value'		=> $val,
				'display'	=> $this->formatMonth($val),
			);
		}
		$GLOBALS['smarty']->assign('EXPIRE_MONTHS', $smarty_data['expire_months']);

		// Show Expire Years
		$thisYear = date("Y");
		$maxYear = $thisYear + 10;
		$selectedYear = isset($_POST['expirationYear']) ? $_POST['expirationYear'] : ($thisYear+2);
		for($i = $thisYear; $i <= $maxYear; ++$i) {
			$smarty_data['expire_years'][]	= array(
				'selected'	=> ($i == $selectedYear) ? 'selected="selected"' : '',
				'value'		=> $i,
			);
		}
		$GLOBALS['smarty']->assign('EXPIRE_YEARS', $smarty_data['expire_years']);
		
		$smarty_data['customer'] = array(
			'first_name' => isset($_POST['firstName']) ? $_POST['firstName'] : $this->_basket['billing_address']['first_name'],
			'last_name'	 => isset($_POST['lastName']) ? $_POST['lastName'] : $this->_basket['billing_address']['last_name'],
			'email'      => isset($_POST['emailAddress']) ? $_POST['emailAddress'] : $this->_basket['billing_address']['email'],
			'add1'		 => isset($_POST['addr1']) ? $_POST['addr1'] : $this->_basket['billing_address']['line1'],
			'add2'		 => isset($_POST['addr2']) ? $_POST['addr2'] : $this->_basket['billing_address']['line2'],
			'city'		 => isset($_POST['city']) ? $_POST['city'] : $this->_basket['billing_address']['town'],
			'state'		 => isset($_POST['state']) ? $_POST['state'] : $this->_basket['billing_address']['state'],
			'postcode'	 => isset($_POST['postcode']) ? $_POST['postcode'] : $this->_basket['billing_address']['postcode'],
			'issue'	 	 => isset($_POST['issue']) ? $_POST['issue'] : ''
		);

		$GLOBALS['smarty']->assign('CUSTOMER', $smarty_data['customer']);

		// Country list
		if (($countries	= $GLOBALS['db']->select('CubeCart_geo_country', false, false, array('name' => 'ASC'))) !== false) {
			$currentIso = isset($_POST['country']) ? $_POST['country'] : $this->_basket['billing_address']['country_iso'];
			foreach ($countries as $country) {
				$country['selected']	= ($country['iso'] == $currentIso) ? 'selected="selected"' : '';
				$smarty_data['country'][]	= $country;
			}
			$GLOBALS['smarty']->assign('COUNTRY', $smarty_data['country']);
		}
		## Check for custom template for module in skin folder
		$file_name = 'form.tpl';
		$form_file = $GLOBALS['gui']->getCustomModuleSkin('gateway', dirname(__FILE__), $file_name);
		$GLOBALS['gui']->changeTemplateDir($form_file);
		$ret = $GLOBALS['smarty']->fetch($file_name);
		$GLOBALS['gui']->changeTemplateDir();
		return $ret;
	}

	## Start Card Validation Functions
	private function check($cardNo, $issueNo=null, $issueDate=null, $expireDate=null, $securityCode=null) {
		// Card Validation RegEx
		$cardArray = array(
			'AMERICAN EXPRESS'		=> '#^3([0-9]{15}|[0-9]{14}|[0-9]{13})$#', // #^[34|37|47]([0-9]{14}|[0-9]{13}|[0-9]{12})$#
			'MASTERCARD'			=> '#^5[1-5][0-9]{14}$#',
			'VISA'					=> '#^4[0-9]{12}([0-9]{3})?$#',

			'AUSTRALIAN BANK CARD'	=> '#^5610([0-9]{12})?$#',
			'DELTA'					=> '#^(41373[3-7]{1}|4462[0-9]{2}|45397[8-9]{1}|454313|45443[2-5]{1}|454742|45672[5-9]{1}|45673[0-9]{1}|45674[0-5]{1}|4658[3-7]{1}[0-9]{1}|4659[0-5]{1}[0-9]{1}|4609[6-7]{1}[0-9]{1}|49218[1-2]{1}|498824)([0-9]{10})?$#',
			'DINERS'				=> '#^3(0[0-5]|[68][0-9])[0-9]{11}$#',
			'DISCOVER'				=> '#^6011[0-9]{12}$#',
			'ELECTRON'				=> '#^(450875|48440[6-9]{1}|4844[1-4]{1}[0-9]{1}|48445[0-5]{1}|4917[3-5]{1}[0-9]{1}|491880|5[1-5]{1})([0-9]{10}|[0-9]{14})?$#',

			'ENROUTE'				=> '#^(2014|2149)([0-9]{11})?$#',

			'JCB'					=> '#^(3[0-9]{4}|2131|1800)[0-9]{11}$#',
			'LASER'					=> '#^(6304|6706|6771|6709)([0-9]{12,15})$#',
			'MAESTRO'				=> '#^(5000[0-9]{2}|5[6-8]{1}|6[0-9]{5})([0-9]{10}|[0-9]{14})?$#',
			'SOLO'					=> '#^(6334[5-9]{1}[0-9]{1}|6767[0-9]{2}|3528[0-9]{2})([0-9]{10})?$#',
			'SWITCH'				=> '#^(49030[2-9]{1}|49033[5-9]{1}|49110[1-2]{1}|49117[4-9]{1}|49118[0-2]{1}|4936[0-9]{2}|564182|6333[1-4]{1}[0-9]{1}|6759[0-9]{2})([0-9]{10}|[0-9]{12}|[0-9]{13})?$#'

		);
		// List of cards requiring issue dates/numbers
		$issueDateArray = array(
			'MAESTRO',
			'SOLO',
			'SWITCH'
		);
		// List of card that DON'T need to be mod10'd
		$noChecksum = array(
			'AUSTRALIAN BANK CARD',
			'DELTA',
			'ELECTRON',
			'ENROUTE'
		);
		// Strip everything that isn't numeric
		$cardNo = trim(preg_replace('#[^0-9]#', '', $cardNo));
		// Assume success unless a rule is broken
		$this->_validate_card_data['response'] = 'SUCCESS';
		// Check expire date (always required)
		$this->expireDate($expireDate);

		if (empty($cardNo)) {
			$this->error(6);
			return false;
		} else {
			foreach ($cardArray as $type => $regex) {
				if (preg_match($regex, $cardNo)) {
					if (!in_array($type, $noChecksum) && strlen($cardNo) != 13) {
						$this->mod10($cardNo);
					}
					// Check start date/issue date
					if (in_array($type, $issueDateArray)) {
						if(!empty($issueNo) && !empty($issueDate)) {
							$this->error(7);
						} elseif(empty($issueNo)){
							$this->issueDate($issueDate);
						} else {
							$this->issueNo($issueNo);
						}
					}
					$this->_validate_card_data['cardType']	= $type;
					$this->_validate_valid					= true;
					break;
				}
			}
		}
		
		// Check the security code if required
		// Moved here becase of AMEX security code lenght check [unknown card type]
		if ($this->_module['cvv_req']) {
			$this->securityCode($securityCode);
		}

		if (!$this->_validate_valid) {
			$this->error(1);
		}
		return $this->_validate_card_data;
	}

	private function mod10($cardnumber) {
		$cardnumber	= preg_replace("#[^0-9]#", "", $cardnumber);  # strip any non-digits
		$cardlength	= strlen($cardnumber);
		$parity		= $cardlength % 2;
		$sum		= 0;

		for ($i = 0; $i < $cardlength;++ $i) {
			$digit = $cardnumber[$i];
			if ($i%2 == $parity) {
				$digit = $digit*2;
			}
			if ($digit>9) {
				$digit -= 9;
			}
			$sum += $digit;
		}
		if ($sum%10) {
			$this->error(5);
			return false;
		}
		return true;
	}

	private function expireDate($expireDate){
		if (strlen($expireDate) !== 6) {
			$this->error(2);
			return false;
		} else if ($expireDate < date("Ym")) {
			$this->error(2);
			return false;
		}
		return true;
	}

	private function issueDate($issueDate) {
		if (strlen($issueDate) !== 6) {
			$this->error(3);
			return false;
		} elseif ($issueDate > date("Ym")) {
			$this->error(3);
			return false;
		}
		return true;
	}

	private function issueNo($issueNo) {
		if(is_numeric($issueNo) && $issueNo>0){
			return true;
		} else {
			$this->error(3);
			return false;
		}
	}

	private function securityCode($securityCode) {
		## Only American Express allows 4 digit security codes
		if(is_numeric($securityCode) && (strlen($securityCode) == 3 || (strlen($securityCode) == 4 && $this->_validate_card_data['cardType']=="AMERICAN EXPRESS"))) {
			return true;
		} else {
			$this->error(4);
			return false;
		}
	}

	private function error($errorCode) {
		$this->_validate_card_data['fail'] = true;
		switch($errorCode) {
			case 1:
				$this->_validate_card_data['error'][1] = "Sorry but the card was not recognised.";
				break;
			case 2:
				$this->_validate_card_data['error'][2] = "Please check the expiry date is valid.";
				break;
			case 3:
				$this->_validate_card_data['error'][3] = "Please enter a valid issue number or start date.";
				break;
			case 4:
				$this->_validate_card_data['error'][4] = "Please enter a valid CVV2 security code. This is a three or four digit code normally found on the rear of the card.";
				break;
			case 5:
				$this->_validate_card_data['error'][5] = "Credit card number is invalid.";
				break;
			case 6:
				$this->_validate_card_data['error'][6] = "Invalid card number.";
			break;
			case 7:
				$this->_validate_card_data['error'][7] = "Please enter either a start date or issue number not both.";
		}
	}
}