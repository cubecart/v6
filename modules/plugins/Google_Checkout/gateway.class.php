<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */
class Gateway {

	private $_module;
	private $_basket;

	##################################################

	public function __construct($module = false, $basket = false) {
		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
	}

	public function __destruct() {
		## Nowt
	}

	##################################################

	public function transfer() {
		switch ($this->_module['mode']) {
			case 'live':
			case 'production':
				$action	= 'https://checkout.google.com/api/checkout/v2/checkout/Merchant/%s';
				break;
			case 'sandbox':
			default:
				$action	= 'https://sandbox.google.com/checkout/api/checkout/v2/checkout/Merchant/%s';
		}
		$transfer	= array(
			'action'	=> sprintf($action, $this->_module['merchId']),
			'method'	=> 'post',
			'submit'	=> 'automatic',
			'target'	=> '_self',
		);
		return $transfer;
	}

	public function repeatVariables() {
		return false;
	}

	public function fixedVariables() {
		## This functionality has been moved to 'cart.gateways' hook
		return false;
	}

	##################################################

	public function call() {
		
		include CC_ROOT_DIR.'/modules/plugins/Google_Checkout/library/googleresponse.php';
		include CC_ROOT_DIR.'/modules/plugins/Google_Checkout/library/googlemerchantcalculations.php';
		include CC_ROOT_DIR.'/modules/plugins/Google_Checkout/library/googlerequest.php';
		include CC_ROOT_DIR.'/modules/plugins/Google_Checkout/library/googlenotificationhistory.php';
		  
		//Create the response object
		$Gresponse = new GoogleResponse($this->_module['merchId'], $this->_module['merchKey']);
		
		//Retrieve the XML sent in the HTTP POST request to the ResponseHandler
		$xml_response = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : file_get_contents("php://input");
		  
		//If serial-number-notification pull serial number and request xml
		if(strpos($xml_response, "xml") == FALSE){
		  //Find serial-number ack notification
		  $serial_array = array();
		  parse_str($xml_response, $serial_array);
		  $serial_number = $serial_array["serial-number"];
		    
		  //Request XML notification
		  $Grequest = new GoogleNotificationHistoryRequest($this->_module['merchId'], $this->_module['merchKey'], $this->_module['mode']);
		  $raw_xml_array = $Grequest->SendNotificationHistoryRequest($serial_number);
		  if ($raw_xml_array[0] != 200){
		      //Add code here to retry with exponential backoff
		  } else {
		      $raw_xml = $raw_xml_array[1];
		  }
		  $Gresponse->SendAck($serial_number, false);
		} else {
		  //Else assume pre 2.5 XML notification
		  //Check Basic Authentication
		  $Gresponse->SetMerchantAuthentication($this->_module['merchId'], $this->_module['merchKey']);
		  $status = $Gresponse->HttpAuthentication();
		  if(! $status) {
		    die('authentication failed');
		  }
		  $raw_xml = $xml_response;
		  $Gresponse->SendAck(null, false);
		}
		
		if (get_magic_quotes_gpc()) {
		  $raw_xml = stripslashes($raw_xml);
		}
		  
		//Parse XML to array
		//list($root, $data) = $Gresponse->GetParsedXML($raw_xml);
		  
		/* Commands to send the various order processing APIs
		 * Send charge order : $Grequest->SendChargeOrder($data[$root]
		 *    ['google-order-number']['VALUE'], <amount>);
		 * Send process order : $Grequest->SendProcessOrder($data[$root]
		 *    ['google-order-number']['VALUE']);
		 * Send deliver order: $Grequest->SendDeliverOrder($data[$root]
		 *    ['google-order-number']['VALUE'], <carrier>, <tracking-number>,
		 *    <send_mail>);
		 * Send archive order: $Grequest->SendArchiveOrder($data[$root]
		 *    ['google-order-number']['VALUE']);
		 *
		 */
  		
		$xml 	= new SimpleXMLElement($raw_xml);
		$order 	= Order::getInstance();
		
		switch($xml->getName()){
		#	case 'request-received':
		#	case 'error':
		#	case 'diagnosis':
		#		break;
		#	case 'checkout-redirect':
		#		## For server-to-server requests - possibly integrate? will be better than the autosubmit
		#		break;
			case 'charge-amount-notification':
				//$Gresponse->SendAck($xml['serial-number']);
				if ($previous = $GLOBALS['db']->select('CubeCart_transactions', false, array('trans_id' => (string)$xml->{'google-order-number'}, 'gateway' => 'Google_Checkout'), false, 1)) {
					$order_id	= $previous[0]['order_id'];
					$log	= array(
						'gateway'	=> 'Google_Checkout',
						'trans_id'	=> (string)$xml->{'google-order-number'},
						'order_id'	=> $order_id,
						'status'	=> 'CHARGED',
						'amount'	=> (float)$xml->{'latest-charge-amount'},
						'captured'	=> (float)$xml->{'total-charge-amount'},
					);
				#	if ((float)$xml->{'total-charge-amount'} == (float)$this->_basket['total']) {
				#		## Order complete!
				#	}
					## Update the transaction log record(s)
					$record	= array('captured' => (float)$xml->{'total-charge-amount'});
					$GLOBALS['db']->update('CubeCart_transactions', $record, array('trans_id' => (string)$xml->{'google-order-number'}));
				}
				break;
			case 'refund-amount-notification':
				//$Gresponse->SendAck($xml['serial-number']);
				if ($previous = $GLOBALS['db']->select('CubeCart_transactions', false, array('trans_id' => (string)$xml->{'google-order-number'}, 'gateway' => 'Google_Checkout'), false, 1)) {
					$order_id	= $previous[0]['order_id'];
					$log	= array(
						'gateway'	=> 'Google_Checkout',
						'trans_id'	=> (string)$xml->{'google-order-number'},
						'order_id'	=> $order_id,
						'status'	=> 'REFUNDED',
						'amount'	=> (float)$xml->{'latest-refund-amount'},
					);
				}
				break;
			case 'chargeback-amount-notification':
				## Chargeback - should we cancel the order?
				//$Gresponse->SendAck($xml['serial-number']);
				break;
			case 'risk-information-notification':
				## Send Acknowledgement



				//$Gresponse->SendAck($xml['serial-number']);
				break;
			case 'merchant-calculation-callback':
				## Merchant Calculations API
				$calc	= new GoogleMerchantCalculations($GLOBALS['config']->get('config','default_currency'));
				## Products
				$total	= 0;
				foreach ($xml->{'shooping-cart'}->items->item as $item) {
					$product_id	= (int)$item->{'merchant-item-id'};
					$price		= (float)$item->{'unit-price'};

					$subtotal	+= $price;
					$products[$product_id] = $price;
				}

				foreach ($xml->calculate->addresses->{'anonymous-address'} as $address) {
					$address_id	= (string)$address->attributes()->id;
					$order_id	= (string)$xml->{'shopping-cart'}->{'merchant-private-data'}->{'cart-order-id'};
					##
					$region_id	= (int)getStateFormat((string)$address->region, 'abbrev', 'id');
					$country_id	= (int)getCountryFormat((string)$address->{'country-code'}, 'iso', 'numcode');

					## Gift Certs & Coupons
					if (isset($xml->calculate->{'merchant-code-strings'})) {
						foreach ($xml->calculate->{'merchant-code-strings'}->{'merchant-code-string'} as $code_string) {
							$code	= (string)$code_string->attributes()->code;
							if ($codes = $GLOBALS['db']->select('CubeCart_coupons', false, array('code' => '~'.$code))) {
								foreach ($codes as $coupon) {
									$valid = false;
									## Check if it's product-specific
									if (!empty($coupon['product_id']) && ($allowed_id = unserialize($coupon['product_id']))) {
										foreach ($products as $product_id => $value) {
											if (in_array($product_id, $allowed_id)) {
												$valid = true;
												break;
											}
										}
									} else {
										$valid = true;
									}
									## Has it reached maximum use?
									if ($coupon['allowed_uses'] > 0 && $coupon['count'] >= $coupon['allowed_uses']) $valid = false;
									## Has it expired?
									if ($coupon['expires'] < date('Y-m-d')) $valid = false;
									## Is it enabled at all?
									$valid	= ($coupon['status']) ? $valid : false;
									$status	= ($valid) ? 'true' : 'false';
									## What type is it?
									if (!empty($coupon['cart_order_id'])) {
										## Gift Cert
										if ($coupon['discount_price'] <= 0) $status = 'false';
										$cert_list[]	= new GoogleGiftcerts($status, $code, $coupon['discount_price'], $coupon['description']);
									} else {
										## Coupon
										if ($coupon['discount_price'] > 0) {
											$discount	= $coupon['discount_price'];
										} else {
											## Percentage discounts...
											if (!empty($coupon['product_id']) && isset($allowed_id) && isset($products)) {
												$discount	= 0;
												foreach ($products as $product_id => $value) {
													if (in_array($product_id, $allowed_id)) {
														$discount += (($value/100)*$coupon['discount_percent']);
													}
												}
											} else {
												$discount = (($subtotal/100)*$coupon['discount_percent']);
											}
										}
										$coupon_list[]	= new GoogleCoupons($status, $code, $discount, $coupon['description']);
									}
								}
							} else {
								$coupon_list[]	= new GoogleCoupons('false', $code, '0.00', null);
							}
						}
					}
					## Tax Calculation
					if (isset($xml->calculate->tax) && (string)$xml->calculate->tax == 'true') {
						//$catalogue	= new Catalogue();
						$GLOBALS['tax']->loadTaxes($country_id);
						if ($products = $GLOBALS['db']->select('CubeCart_order_inventory', array('product_id', 'quantity'), array('cart_order_id' => $order_id))) {
							foreach ($products as $product) {
								$data		= $GLOBALS['catalogue']->getProductData($product['product_id'], $product['quantity']);
								$item_price	= ($GLOBALS['tax']->salePrice($data['price'], $data['sale_price'], false)) ? $data['sale_price'] : $data['price'];

								$GLOBALS['tax']->productTax($item_price, $data['tax_type'], (bool)$data['tax_inclusive']);
							}
							$tax	= $GLOBALS['tax']->fetchTaxAmounts();
							$product_tax	= $tax['applied']+$tax['included'];
						}
					}

					## Shipping Calculation
					if (isset($xml->calculate->shipping)) {
						foreach ($xml->calculate->shipping->method as $method) {
							$method		= str_replace(' ', '_', (string)$method->attributes()->name);
							$ship_class	= CC_ROOT_DIR.'/modules/shipping/'.$method.'/'.'shipping.class.php';
							if (file_exists($ship_class)) {
								require $ship_class;
								if (class_exists($method) && method_exists((string)$method, 'calculate')) {
									$shipping	= new $method(false);
									$packages[]	= $shipping->calculate();
								}
							}
						}
						if (isset($packages) && is_array($packages)) {
							foreach ($packages as $package) {
								foreach ($package as $shipping) {
									$result	= new GoogleResult($address_id);
									$result->SetShippingDetails($shipping['name'], $shipping['value']);

									$GLOBALS['tax']->loadTaxes($country_id);
									$GLOBALS['tax']->productTax($shipping['value'], $shipping['tax_id'], false, $region_id, 'shipping');
									$add	= $GLOBALS['tax']->fetchTaxAmounts();
								#	if ($this->_config['debug']) file_put_contents(CC_ROOT_DIR.'/logs/google.log', print_r($add, true), FILE_APPEND);
									$result->SetTaxDetails($product_tax+$add['applied']);

									if (isset($cert_list) && is_array($cert_list)) {
										foreach ($cert_list as $cert) $result->AddGiftCertificates($cert);
									}
									if (isset($coupon_list) && is_array($coupon_list)) {
										foreach ($coupon_list as $coupon) $result->AddCoupons($coupon);
									}
									$calc->AddResult($result);
									unset($result, $shipping);
								}
							}
						}
					}
				}
				$Gresponse->ProcessMerchantCalculations($calc);
				break;
			case 'new-order-notification':
	
				## Update the customer's order with the billing and delivery details			
				$billing_name_parts = explode(' ',$xml->{'buyer-billing-address'}->{'contact-name'});
				$shipping_name_parts = explode(' ',$xml->{'buyer-shipping-address'}->{'contact-name'});
				
				$first_name 	= (!empty($xml->{'buyer-billing-address'}->{'structured-name'}->{'first-name'})) ? $xml->{'buyer-billing-address'}->{'structured-name'}->{'first-name'} : $billing_name_parts[0];
				$last_name 		= (!empty($xml->{'buyer-billing-address'}->{'structured-name'}->{'last-name'})) ? $xml->{'buyer-billing-address'}->{'structured-name'}->{'last-name'} : $billing_name_parts[1].' '.$billing_name_parts[2].' '.$billing_name_parts[3].' '.$billing_name_parts[4];
				$first_name_d 	= (!empty($xml->{'buyer-shipping-address'}->{'structured-name'}->{'first-name'})) ? $xml->{'buyer-shipping-address'}->{'structured-name'}->{'first-name'} : $shipping_name_parts[0];
				$last_name_d	= (!empty($xml->{'buyer-shipping-address'}->{'structured-name'}->{'last-name'})) ? $xml->{'buyer-shipping-address'}->{'structured-name'}->{'last-name'} : $shipping_name_parts[1].' '.$shipping_name_parts[2].' '.$shipping_name_parts[3].' '.$shipping_name_parts[4];
				
						
				$update	= array(
					## Billing address
					'company_name'	=> (string)$xml->{'buyer-billing-address'}->{'company-name'},
					'title'			=> '',
					'first_name'	=> (string)$first_name,
					'last_name'		=> (string)$last_name,
					'line1'			=> (string)$xml->{'buyer-billing-address'}->address1,
					'line2'			=> (string)$xml->{'buyer-billing-address'}->address2,
					'town'			=> (string)$xml->{'buyer-billing-address'}->city,
					'state'			=> (string)$xml->{'buyer-billing-address'}->region,
					'country'		=> getCountryFormat((string)$xml->{'buyer-billing-address'}->{'country-code'}, 'iso', 'numcode'),
					'postcode'		=> (string)$xml->{'buyer-billing-address'}->{'postal-code'},
					## Delivery address
					'company_name'	=> (string)$xml->{'buyer-shipping-address'}->{'company-name'},
					'title_d'		=> '',
					'first_name_d'	=> (string)$first_name_d,
					'last_name_d'	=> (string)$last_name_d,
					'line1_d'		=> (string)$xml->{'buyer-shipping-address'}->address1,
					'line2_d'		=> (string)$xml->{'buyer-shipping-address'}->address2,
					'town_d'		=> (string)$xml->{'buyer-shipping-address'}->city,
					'state_d'		=> (string)$xml->{'buyer-shipping-address'}->region,
					'country_d'		=> getCountryFormat((string)$xml->{'buyer-shipping-address'}->{'country-code'}, 'iso', 'numcode'),
					'postcode_d'	=> (string)$xml->{'buyer-shipping-address'}->{'postal-code'},
					## Misc
					'email'			=> (string)$xml->{'buyer-billing-address'}->email,
					'phone'			=> (string)$xml->{'buyer-billing-address'}->phone,
				);
				## Were any gift certificates or coupons used?
				if (isset($xml->{'order-adjustment'})) {
					## Discounts
					if (isset($xml->{'order-adjustment'}->{'merchant-codes'})) {
						$discount	= 0;
						if (isset($xml->{'order-adjustment'}->{'merchant-codes'}->{'coupon-adjustment'})) {
							## Update coupons
							foreach ($xml->{'order-adjustment'}->{'merchant-codes'}->{'coupon-adjustment'} as $coupon) {
								$code	= (string)$coupon->code;
							#	$this->update();
								$discount += (float)$coupon->{'applied-amount'};
							}
						}
						if (isset($xml->{'order-adjustment'}->{'merchant-codes'}->{'gift-certificate-adjustment'})) {
							## Update gift certificates
							foreach ($xml->{'order-adjustment'}->{'merchant-codes'}->{'gift-certificate-adjustment'} as $cert) {
								$used	= (float)$cert->{'applied-amount'};
								$discount += $used;
								$remain	= (float)$cert->{'calculated-amount'} - (float)$cert->{'applied-amount'};
							#	$this->update('CubeCart_coupons', array('discount_price' => $remain), array('code' => (string)$cert->code));
							}
						}
						$update['discount'] = $discount;
						$update['total']	= (float)$xml->{'order-total'};
					}
					## Shipping
					if (isset($xml->{'order-adjustment'}->shipping->{'merchant-calculated-shipping-adjustment'})) {
						$shipping	= $xml->{'order-adjustment'}->shipping->{'merchant-calculated-shipping-adjustment'};
						$update['shipping']		= (string)$shipping->{'shipping-cost'};
						$update['ship_method']	= str_replace(' ', '_', (string)$shipping->{'shipping-name'});
					}
					## Total Tax
					if (isset($xml->{'order-adjustment'}->{'total-tax'})) $update['total_tax'] = (float)$xml->{'order-adjustment'}->{'total-tax'};
				}
				$order_id	= (string)$xml->{'shopping-cart'}->{'merchant-private-data'}->{'cart-order-id'};
				$GLOBALS['db']->update('CubeCart_order_summary', $update, array('cart_order_id' => $order_id));
				## Transaction log
				$log	= array(
					'gateway'	=> 'Google_Checkout',
					'trans_id'	=> (string)$xml->{'google-order-number'},
					'order_id'	=> $order_id,
					'status'	=> (string)$xml->{'financial-order-state'},
					'amount'	=> (string)$xml->{'order-total'},
				);
				## Send internal order number to Google
				$request	= new GoogleRequest($this->_module['merchId'], $this->_module['merchKey'], $this->_module['mode'], $GLOBALS['config']->get('config','default_currency'));
				$request->SendMerchantOrderNumber((string)$xml->{'google-order-number'}, $order_id);
				$request->SendProcessOrder((string)$xml->{'google-order-number'});	## Being deprecated in favour of line-item shipping
				break;
			case 'order-state-change-notification':
				//$Gresponse->SendAck($xml['serial-number']);
				if ($previous = $GLOBALS['db']->select('CubeCart_transactions', false, array('trans_id' => (string)$xml->{'google-order-number'}, 'gateway' => 'Google_Checkout'), false, 1)) {
					$order_id	= $previous[0]['order_id'];
					switch ((string)$xml->{'new-financial-order-state'}) {
						case 'REVIEWING':
						case 'CHARGING':
						case 'CHARGED':
							break 2;
						case 'CHARGEABLE':
							$order->orderStatus(Order::ORDER_PROCESS, $order_id);
							break;
						case 'PAYMENT_DECLINED':
							$order->orderStatus(Order::ORDER_DECLINED, $order_id);
							break;
						case 'CANCELLED_BY_GOOGLE':
							## Get the reason
							$note	= (string)$xml->reason;
						case 'CANCELLED':
							$order->orderStatus(Order::ORDER_CANCELLED, $order_id);
							break;
						default:
							break;
					}
					switch ((string)$xml->{'new-fulfillment-order-state'}) {
						case 'NEW':
						case 'PROCESSING':
							break;
						case 'DELIVERED':
							$order->orderStatus(Order::ORDER_COMPLETE, $order_id);
							break;
						case 'WILL_NOT_DELIVER':
							$order->orderStatus(Order::ORDER_CANCELLED, $order_id);
							break;
						default:
							break;
					}
					## Update transaction log
					$log	= array(
						'gateway'	=> 'Google Checkout',
						'trans_id'	=> (string)$xml->{'google-order-number'},
						'order_id'	=> $order_id,
						'status'	=> (string)$xml->{'new-financial-order-state'},
						'amount'	=> $previous[0]['amount'],
						'note'		=> (isset($note)) ? $note : null,
					);
				}
				break;
			default:
				$Gresponse->SendBadRequestStatus('Invalid or not supported Message');
		}
		if (isset($log) && is_array($log)) {
			$order->logTransaction($log, true);
		}
		return false;
	}

	public function process() {
		return false;
	}

	public function form() {
		return false;
	}
}